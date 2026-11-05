<?php

declare(strict_types=1);

namespace App\Domain\Ai\Chatbot;

use App\Domain\Ai\Providers\AiProviderInterface;
use App\Domain\Ai\Providers\AiProviderManager;
use App\Models\Attendance;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\Context;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class BusinessAssistantAiService
 *
 * Layanan COOCA AI Business Assistant resmi.
 * Bertugas menganalisis data riil operasional dan finansial bisnis (penjualan, omzet,
 * stok menipis, arus kas, absensi karyawan, performa cabang).
 *
 * INTEGRASI ROLE & PERMISSION KETAT (BAGIAN 7):
 * - DILARANG menampilkan data apa pun jika pengguna tidak memiliki izin (permission).
 * - Identitas diverifikasi dari sesi login & active business context (Anti-IDOR).
 * - Jika izin ditolak, kembalikan teks penolakan standar mutlak:
 *   "Maaf, Anda tidak memiliki izin untuk mengakses informasi tersebut. Silakan hubungi administrator bisnis Anda untuk mendapatkan akses."
 */
final class BusinessAssistantAiService
{
    public function __construct(
        private readonly AiProviderManager $providerManager = new AiProviderManager()
    ) {}

    /**
     * Dapatkan tautan kontak tim Support resmi COOCA.
     *
     * @return array<int, array{label: string, url: string, icon?: string}>
     */
    public static function getSupportContactLinks(): array
    {
        return HelpCenterAiService::getSupportContactLinks();
    }

    /**
     * Pesan standar kegagalan AI provider (Sesuai Mandat Bagian 3).
     *
     * @return array{
     *     reply: string,
     *     intent: string,
     *     is_data_query: bool,
     *     permission_granted: bool,
     *     quick_links: array<int, array{label: string, url: string, icon?: string}>,
     *     status: string
     * }
     */
    public static function buildSystemBusyResponse(): array
    {
        return HelpCenterAiService::buildSystemBusyResponse('business_ai_failure');
    }

    /**
     * Pesan penolakan hak akses standar (Sesuai Mandat Bagian 7).
     *
     * @return array{
     *     reply: string,
     *     intent: string,
     *     is_data_query: bool,
     *     permission_granted: bool,
     *     quick_links: array<int, array{label: string, url: string, icon?: string}>,
     *     status: string
     * }
     */
    public static function buildUnauthorizedResponse(): array
    {
        return [
            'reply' => "🔒 **Akses Ditolak:**\n\nMaaf, Anda tidak memiliki izin untuk mengakses informasi tersebut. Silakan hubungi administrator bisnis Anda untuk mendapatkan akses.",
            'intent' => 'unauthorized',
            'is_data_query' => true,
            'permission_granted' => false,
            'quick_links' => [],
            'status' => 'forbidden',
        ];
    }

    /**
     * Proses pertanyaan Business Assistant AI.
     *
     * @param array<int, array{sender: string, text: string}> $history
     * @return array{
     *     reply: string,
     *     intent: string,
     *     is_data_query: bool,
     *     permission_granted: bool,
     *     quick_links: array<int, array{label: string, url: string, icon?: string}>,
     *     ai_model?: string,
     *     provider?: string,
     *     status?: string
     * }
     */
    public function answer(Business $business, ?User $user, string $query, array $history = []): array
    {
        $cleanQuery = trim($query);
        $lowerQuery = mb_strtolower($cleanQuery);
        $userName = $user?->name ?? 'Pengguna';

        // 1. Validasi Multi-Tenant Context Isolation (Anti-IDOR)
        $contextBusinessId = Context::businessId();
        if ($contextBusinessId !== null && $contextBusinessId !== $business->id) {
            Log::channel('daily')->warning("[BusinessAssistant] Cross-tenant mismatch: active context={$contextBusinessId}, target={$business->id}");
            return self::buildUnauthorizedResponse();
        }

        // 2. Guardrail: Deteksi dan blokir prompt injection / manipulasi
        if ($this->detectPromptInjection($cleanQuery)) {
            Log::channel('daily')->warning("[BusinessAssistant] Prompt injection blocked for user {$user?->id} on business {$business->id}: '{$cleanQuery}'");
            return [
                'reply' => "🔒 **Permintaan Tidak Diizinkan:**\n\nHalo {$userName}, sistem mendeteksi instruksi khusus yang tidak diizinkan oleh protokol keamanan COOCA. Silakan ajukan pertanyaan bisnis seputar omzet, penjualan, stok produk, atau absensi tim.",
                'intent' => 'guardrail_rejection',
                'is_data_query' => true,
                'permission_granted' => false,
                'quick_links' => [],
                'status' => 'rejected',
            ];
        }

        // 3. Klasifikasi Intent & Evaluasi Izin Akses RBAC (Bagian 7)
        $intentAnalysis = $this->analyzeIntentAndPermissions($lowerQuery);
        $intent = $intentAnalysis['intent'];
        $requiredPermissions = $intentAnalysis['required_permissions'];

        // Cek izin: Owner selalu berhak penuh; staf dicek izinnya via Context::hasPermission()
        $isAuthorized = Context::isOwner();
        if (! $isAuthorized) {
            foreach ($requiredPermissions as $perm) {
                if (Context::hasPermission($perm)) {
                    $isAuthorized = true;
                    break;
                }
            }
        }

        if (! $isAuthorized) {
            Log::channel('daily')->info("[BusinessAssistant] Akses ditolak untuk user {$user?->id} ({$user?->email}) pada bisnis {$business->id} dengan intent {$intent}");
            return self::buildUnauthorizedResponse();
        }

        // 4. Pengambilan Data Riil Faktual (Database-grounded)
        $businessData = $this->fetchVerifiedBusinessData($business, $intent);

        // 5. Sintesis Jawaban Menggunakan Live AI Provider
        return $this->synthesizeResponseWithAi(
            $business,
            $user,
            $cleanQuery,
            $history,
            $intent,
            $businessData
        );
    }

    /**
     * Analisa intent dan daftar permission yang dibutuhkan.
     *
     * @return array{intent: string, required_permissions: array<int, string>}
     */
    private function analyzeIntentAndPermissions(string $lower): array
    {
        // A. Karyawan, Gaji, Absensi
        if (Str::contains($lower, ['karyawan', 'pegawai', 'gaji', 'payroll', 'absensi', 'absen', 'terlambat', 'kehadiran', 'staf', 'staff'])) {
            return [
                'intent' => 'hr_attendance',
                'required_permissions' => ['users.view', 'users.manage', 'users.show'],
            ];
        }

        // B. Stok, Inventori, Bahan Baku, Reorder
        if (Str::contains($lower, ['stok', 'stock', 'inventori', 'inventory', 'gudang', 'habis', 'menipis', 'sisa stok', 'reorder'])) {
            return [
                'intent' => 'inventory_stock',
                'required_permissions' => ['inventory.view', 'products.view', 'inventory.manage'],
            ];
        }

        // C. Keuangan, Pengeluaran, Beban, Biaya, Kas, Arus Kas
        if (Str::contains($lower, ['biaya', 'beban', 'pengeluaran', 'expense', 'kas', 'arus kas', 'cash flow', 'bank', 'laba bersih', 'laba kotor'])) {
            return [
                'intent' => 'finance_expenses',
                'required_permissions' => ['reports.financial', 'finance.cash_bank', 'accounting.view', 'expenses.view'],
            ];
        }

        // D. Pembelian, Vendor, Supplier, PO
        if (Str::contains($lower, ['supplier', 'vendor', 'pembelian', 'purchase', 'po'])) {
            return [
                'intent' => 'purchasing_suppliers',
                'required_permissions' => ['purchasing.view', 'purchasing.manage', 'master_data.suppliers.view'],
            ];
        }

        // E. Pelanggan, Member, CRM
        if (Str::contains($lower, ['pelanggan', 'customer', 'member', 'loyalitas', 'crm'])) {
            return [
                'intent' => 'crm_customers',
                'required_permissions' => ['customers.view', 'crm.view'],
            ];
        }

        // F. Penjualan, Omzet, Transaksi, Produk Terlaris (Default Penjualan / Laporan)
        if (Str::contains($lower, ['omzet', 'omset', 'keuntungan', 'laba', 'penjualan', 'laku', 'terlaris', 'transaksi', 'sales', 'pendapatan', 'struk', 'pesanan'])) {
            return [
                'intent' => 'sales_omzet',
                'required_permissions' => ['reports.view', 'reports.financial', 'pos.reports'],
            ];
        }

        // G. Ringkasan performa umum / General Performance Overview
        return [
            'intent' => 'business_overview',
            'required_permissions' => ['reports.view', 'dashboard.view'],
        ];
    }

    /**
     * Ambil data riil faktual dari database bisnis aktif.
     *
     * @return array<string, mixed>
     */
    private function fetchVerifiedBusinessData(Business $business, string $intent): array
    {
        $bizId = $business->id;
        $now = Context::now();
        $todayStr = Context::today();
        $startOfWeek = $now->copy()->startOfWeek()->toDateString();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();

        $data = [
            'business_name' => $business->name,
            'today_date' => $todayStr,
        ];

        try {
            // Data Penjualan & Omzet
            if (in_array($intent, ['sales_omzet', 'business_overview', 'finance_expenses'], true)) {
                $todaySalesQuery = PosOrder::where('business_id', $bizId)
                    ->whereDate('order_date', $todayStr)
                    ->whereNotIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED]);

                $todayTotal = (float) $todaySalesQuery->sum('total_amount');
                $todayCount = (int) $todaySalesQuery->count();

                $monthSalesQuery = PosOrder::where('business_id', $bizId)
                    ->whereDate('order_date', '>=', $startOfMonth)
                    ->whereNotIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED]);

                $monthTotal = (float) $monthSalesQuery->sum('total_amount');
                $monthCount = (int) $monthSalesQuery->count();

                // Top 5 Produk Terlaris Bulan Ini
                $topProducts = PosOrderItem::whereHas('order', function ($q) use ($bizId, $startOfMonth) {
                    $q->where('business_id', $bizId)
                        ->whereDate('order_date', '>=', $startOfMonth)
                        ->whereNotIn('status', [PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED]);
                })
                    ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
                    ->groupBy('product_name')
                    ->orderByDesc('total_qty')
                    ->limit(5)
                    ->get()
                    ->toArray();

                $data['sales'] = [
                    'today_omzet' => $todayTotal,
                    'today_transactions' => $todayCount,
                    'month_omzet' => $monthTotal,
                    'month_transactions' => $monthCount,
                    'top_selling_products' => $topProducts,
                ];
            }

            // Data Stok & Inventori
            if (in_array($intent, ['inventory_stock', 'business_overview'], true)) {
                $products = Product::where('business_id', $bizId)
                    ->where('is_active', true)
                    ->get();

                $lowStockItems = [];
                $outOfStockItems = [];

                foreach ($products as $p) {
                    $stock = (float) $p->total_stock;
                    $minStock = (float) ($p->min_stock ?? 0);

                    if ($stock <= 0) {
                        $outOfStockItems[] = [
                            'name' => $p->name,
                            'stock' => $stock,
                            'min_stock' => $minStock,
                        ];
                    } elseif ($minStock > 0 && $stock <= $minStock) {
                        $lowStockItems[] = [
                            'name' => $p->name,
                            'stock' => $stock,
                            'min_stock' => $minStock,
                        ];
                    }
                }

                $data['inventory'] = [
                    'total_active_products' => $products->count(),
                    'out_of_stock_count' => count($outOfStockItems),
                    'out_of_stock_items' => array_slice($outOfStockItems, 0, 8),
                    'low_stock_count' => count($lowStockItems),
                    'low_stock_items' => array_slice($lowStockItems, 0, 8),
                ];
            }

            // Data Pengeluaran / Beban
            if (in_array($intent, ['finance_expenses', 'business_overview'], true)) {
                $monthExpenses = Expense::where('business_id', $bizId)
                    ->whereDate('expense_date', '>=', $startOfMonth)
                    ->sum('amount');

                $recentExpenses = Expense::where('business_id', $bizId)
                    ->orderByDesc('expense_date')
                    ->limit(5)
                    ->get(['title', 'amount', 'expense_date'])
                    ->toArray();

                $data['finance'] = [
                    'month_total_expenses' => (float) $monthExpenses,
                    'recent_expenses' => $recentExpenses,
                ];
            }

            // Data Karyawan & Absensi
            if (in_array($intent, ['hr_attendance', 'business_overview'], true)) {
                $totalEmployees = User::whereHas('businessUsers', function ($q) use ($bizId) {
                    $q->where('business_id', $bizId)->where('is_active', true);
                })->count();

                $todayAttendances = Attendance::where('business_id', $bizId)
                    ->whereDate('date', $todayStr)
                    ->get();

                $data['hr'] = [
                    'total_employees' => $totalEmployees,
                    'present_today' => $todayAttendances->where('status', 'present')->count(),
                    'late_today' => $todayAttendances->where('status', 'late')->count(),
                ];
            }

            // Data Pelanggan
            if ($intent === 'crm_customers') {
                $totalCustomers = Customer::where('business_id', $bizId)->count();
                $data['crm'] = [
                    'total_customers' => $totalCustomers,
                ];
            }

            // Data Pembelian / PO
            if ($intent === 'purchasing_suppliers') {
                $recentPos = PurchaseOrder::where('business_id', $bizId)
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get(['po_number', 'total_amount', 'status'])
                    ->toArray();

                $data['purchasing'] = [
                    'recent_purchase_orders' => $recentPos,
                ];
            }
        } catch (Throwable $e) {
            Log::channel('daily')->error("[BusinessAssistant] Error querying business data: {$e->getMessage()}");
        }

        return $data;
    }

    /**
     * Sintesis jawaban melalui AI Provider dengan verified database grounding.
     *
     * @param array<int, array{sender: string, text: string}> $history
     * @param array<string, mixed> $businessData
     * @return array{
     *     reply: string,
     *     intent: string,
     *     is_data_query: bool,
     *     permission_granted: bool,
     *     quick_links: array<int, array{label: string, url: string, icon?: string}>,
     *     ai_model?: string,
     *     provider?: string,
     *     status?: string
     * }
     */
    private function synthesizeResponseWithAi(
        Business $business,
        ?User $user,
        string $query,
        array $history,
        string $intent,
        array $businessData
    ): array {
        $quickLinks = $this->resolveQuickLinksForIntent($intent);

        // Siapkan AI Provider
        try {
            $resolved = $this->providerManager->resolveForBusiness($business);
            /** @var AiProviderInterface $provider */
            $provider = $resolved['provider'];
            $apiKey = (string) $resolved['api_key'];
            $model = (string) $resolved['model'];
            $endpoint = (string) ($resolved['endpoint'] ?? '');

            // Fallback offline jika provider adalah fallback engine
            if ($provider->getProviderName() === 'fallback') {
                return $this->buildFactualFallbackSummary($query, $intent, $businessData, $quickLinks);
            }

            $systemPrompt = $this->buildSystemPrompt($business, $user, $intent, $businessData);

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            // Tambahkan konteks riwayat percakapan sebelumnya (maks 4 putaran)
            foreach (array_slice($history, -4) as $h) {
                $role = ($h['sender'] ?? 'user') === 'user' ? 'user' : 'assistant';
                $messages[] = ['role' => $role, 'content' => (string) ($h['text'] ?? '')];
            }

            $messages[] = ['role' => 'user', 'content' => $query];

            $options = [
                'temperature' => 0.4,
                'max_tokens' => 900,
            ];
            if ($endpoint !== '') {
                $options['endpoint'] = $endpoint;
            }

            $response = $provider->chat($apiKey, $model, $messages, $options);
            $rawReply = trim((string) ($response['text'] ?? ($response['content'] ?? '')));

            if ($rawReply === '') {
                return $this->buildFactualFallbackSummary($query, $intent, $businessData, $quickLinks);
            }

            return [
                'reply' => $rawReply,
                'intent' => $intent,
                'is_data_query' => true,
                'permission_granted' => true,
                'quick_links' => $quickLinks,
                'ai_model' => $model,
                'provider' => $provider->getProviderName(),
                'status' => 'completed',
            ];
        } catch (Throwable $e) {
            Log::channel('daily')->warning("[BusinessAssistant] AI Provider execution failed: {$e->getMessage()}. Beralih ke ringkasan data faktual.");
            // Berikan ringkasan data faktual terverifikasi agar pengguna tetap mendapatkan data akurat
            return $this->buildFactualFallbackSummary($query, $intent, $businessData, $quickLinks);
        }
    }

    /**
     * Susun System Prompt berlandaskan data faktual (Grounding).
     *
     * @param array<string, mixed> $data
     */
    private function buildSystemPrompt(Business $business, ?User $user, string $intent, array $data): array|string
    {
        $userName = $user?->name ?? 'Pemilik Usaha';
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Anda adalah COOCA AI Business Assistant, konsultan analitik bisnis cerdas untuk usaha "{$business->name}".
Pengguna aktif yang sedang bertanya: {$userName}.

PEDOMAN KETAT:
1. JAWAB HANYA BERDASARKAN DATA FAKTUAL BISNIS DI BAWAH INI. JANGAN MENGARANG DATA ATAU ANGKA.
2. Format seluruh angka mata uang dalam format Rupiah Indonesia (contoh: Rp 1.500.000).
3. Berikan analisis ringkas, tajam, dan actionable (rekomendasi taktis) untuk pemilik bisnis/manajer.
4. Jika data bernilai 0 atau belum ada transaksi tercatat, jelaskan secara jujur dan ramah bahwa belum ada transaksi/data pada periode tersebut.
5. Gunakan bahasa Indonesia profesional yang santun, jelas, dan percaya diri.

DATA TERVERIFIKASI DATABASE AKTIF SAAT INI:
```json
{$jsonData}
```
PROMPT;
    }

    /**
     * Fallback ringkasan faktual jika provider adalah fallback engine offline.
     *
     * @param array<string, mixed> $data
     * @param array<int, array{label: string, url: string, icon?: string}> $links
     * @return array{
     *     reply: string,
     *     intent: string,
     *     is_data_query: bool,
     *     permission_granted: bool,
     *     quick_links: array<int, array{label: string, url: string, icon?: string}>,
     *     status: string
     * }
     */
    private function buildFactualFallbackSummary(string $query, string $intent, array $data, array $links): array
    {
        $lines = ["📊 **Ringkasan Data Bisnis Aktual ({$data['business_name']}):**\n"];

        if (isset($data['sales'])) {
            $todayOmzet = number_format((float) $data['sales']['today_omzet'], 0, ',', '.');
            $todayCount = (int) $data['sales']['today_transactions'];
            $monthOmzet = number_format((float) $data['sales']['month_omzet'], 0, ',', '.');
            $lines[] = "• **Ringkasan Penjualan & Omzet Hari Ini:** Rp {$todayOmzet} ({$todayCount} transaksi)";
            $lines[] = "• **Omzet Bulan Ini:** Rp {$monthOmzet}";

            if (! empty($data['sales']['top_selling_products'])) {
                $lines[] = "\n🔥 **Produk Terlaris Bulan Ini:**";
                foreach ($data['sales']['top_selling_products'] as $item) {
                    $lines[] = "- {$item['product_name']}: {$item['total_qty']} terjual (Rp " . number_format((float) $item['total_revenue'], 0, ',', '.') . ")";
                }
            }
        }

        if (isset($data['inventory'])) {
            $lines[] = "\n📦 **Status Inventori & Stok:**";
            $lines[] = "• Produk Habis (Stok 0): " . $data['inventory']['out_of_stock_count'] . " produk";
            $lines[] = "• Produk Menipis (<= Min Stok): " . $data['inventory']['low_stock_count'] . " produk";

            if (! empty($data['inventory']['low_stock_items'])) {
                $lines[] = "Rincian produk menipis:";
                foreach ($data['inventory']['low_stock_items'] as $item) {
                    $lines[] = "- {$item['name']}: sisa {$item['stock']} (min: {$item['min_stock']})";
                }
            }
        }

        if (isset($data['finance'])) {
            $monthExp = number_format((float) $data['finance']['month_total_expenses'], 0, ',', '.');
            $lines[] = "\n💳 **Pengeluaran Bulan Ini:** Rp {$monthExp}";
        }

        if (isset($data['hr'])) {
            $lines[] = "\n👥 **Kehadiran Karyawan Hari Ini:** {$data['hr']['present_today']} hadir dari {$data['hr']['total_employees']} karyawan ({$data['hr']['late_today']} terlambat)";
        }

        return [
            'reply' => implode("\n", $lines),
            'intent' => $intent,
            'is_data_query' => true,
            'permission_granted' => true,
            'quick_links' => $links,
            'status' => 'completed',
        ];
    }

    /**
     * Resolusi quick links relevan per intent bisnis.
     *
     * @return array<int, array{label: string, url: string, icon?: string}>
     */
    private function resolveQuickLinksForIntent(string $intent): array
    {
        $links = [];

        if (in_array($intent, ['sales_omzet', 'business_overview'], true)) {
            if (\Illuminate\Support\Facades\Route::has('reports.index')) {
                $links[] = ['label' => 'Buka Laporan Penjualan', 'url' => route('reports.index'), 'icon' => 'reports'];
            }
            if (\Illuminate\Support\Facades\Route::has('pos.reports.index')) {
                $links[] = ['label' => 'Laporan Kasir POS', 'url' => route('pos.reports.index'), 'icon' => 'pos'];
            }
        }

        if (in_array($intent, ['inventory_stock', 'business_overview'], true)) {
            if (\Illuminate\Support\Facades\Route::has('inventory.stocks')) {
                $links[] = ['label' => 'Cek Stok Gudang', 'url' => route('inventory.stocks'), 'icon' => 'inventory'];
            }
            if (\Illuminate\Support\Facades\Route::has('products.index')) {
                $links[] = ['label' => 'Katalog Produk', 'url' => route('products.index'), 'icon' => 'products'];
            }
        }

        if (in_array($intent, ['finance_expenses'], true)) {
            if (\Illuminate\Support\Facades\Route::has('finance.expenses.index')) {
                $links[] = ['label' => 'Catatan Pengeluaran', 'url' => route('finance.expenses.index'), 'icon' => 'finance'];
            }
        }

        return $links;
    }

    /**
     * Deteksi potensi prompt injection / bypass instructions.
     */
    private function detectPromptInjection(string $query): bool
    {
        $suspiciousPatterns = [
            'ignore previous instructions',
            'ignore all previous',
            'bypass permission',
            'you are now in developer mode',
            'tampilkan password',
            'tampilkan api key',
            'bocorkan data',
            'tampilkan secret',
            'dump database',
            'system prompt',
            'format c:',
            'drop table',
        ];

        $lower = mb_strtolower($query);
        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
