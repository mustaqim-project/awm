<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PlatformChartOfAccount;
use Illuminate\Database\Seeder;

class PlatformCoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // 1. ASET (1-XXXX)
            [
                'code' => '1110',
                'name' => 'Kas Tunai & Operasional Platform',
                'type' => PlatformChartOfAccount::TYPE_ASSET,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'kas_bank',
                'description' => 'Kas tunai kantor pusat platform Cooca',
                'is_system' => true,
            ],
            [
                'code' => '1120',
                'name' => 'Bank BCA Platform (Rekening Resmi)',
                'type' => PlatformChartOfAccount::TYPE_ASSET,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'kas_bank',
                'description' => 'Rekening giro BCA penampungan pembayaran langganan SaaS',
                'is_system' => true,
            ],
            [
                'code' => '1130',
                'name' => 'Bank Mandiri Platform',
                'type' => PlatformChartOfAccount::TYPE_ASSET,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'kas_bank',
                'description' => 'Rekening Bank Mandiri operasional & payroll platform',
                'is_system' => true,
            ],
            [
                'code' => '1140',
                'name' => 'Saldo Escrow Payment Gateway (TriPay / QRIS)',
                'type' => PlatformChartOfAccount::TYPE_ASSET,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'kas_bank',
                'description' => 'Saldo pending pada payment gateway sebelum ditarik ke rekening bank',
                'is_system' => true,
            ],
            [
                'code' => '1150',
                'name' => 'Piutang Langganan & Tagihan Tenant',
                'type' => PlatformChartOfAccount::TYPE_ASSET,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'piutang',
                'description' => 'Tagihan langganan invoice jatuh tempo yang belum dibayar tenant',
                'is_system' => true,
            ],
            [
                'code' => '1210',
                'name' => 'Aset Perangkat Keras & Infrastruktur IT',
                'type' => PlatformChartOfAccount::TYPE_ASSET,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'aset_tetap',
                'description' => 'Peralatan kantor, server workstation, dan perangkat pengembang',
                'is_system' => false,
            ],

            // 2. KEWAJIBAN / LIABILITAS (2-XXXX)
            [
                'code' => '2110',
                'name' => 'Hutang Titipan Saldo Merchant (Settlement Payable)',
                'type' => PlatformChartOfAccount::TYPE_LIABILITY,
                'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                'category_group' => 'hutang_lancar',
                'description' => 'Saldo dompet tenant hasil penjualan QRIS/Online yang menunggu dicairkan',
                'is_system' => true,
            ],
            [
                'code' => '2120',
                'name' => 'Pendapatan Diterima Dimuka (SaaS Unearned Revenue)',
                'type' => PlatformChartOfAccount::TYPE_LIABILITY,
                'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                'category_group' => 'hutang_lancar',
                'description' => 'Pembayaran paket tahunan yang belum teramortisasi penuh per bulan',
                'is_system' => true,
            ],
            [
                'code' => '2130',
                'name' => 'Hutang Biaya Operasional & Vendor',
                'type' => PlatformChartOfAccount::TYPE_LIABILITY,
                'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                'category_group' => 'hutang_lancar',
                'description' => 'Tagihan API, server, atau vendor pihak ketiga yang belum jatuh tempo bayar',
                'is_system' => false,
            ],

            // 3. EKUITAS / MODAL (3-XXXX)
            [
                'code' => '3110',
                'name' => 'Modal Disetor Pendiri Platform',
                'type' => PlatformChartOfAccount::TYPE_EQUITY,
                'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                'category_group' => 'modal',
                'description' => 'Modal awal pendirian dan suntikan modal operasional platform Cooca',
                'is_system' => true,
            ],
            [
                'code' => '3210',
                'name' => 'Laba Ditahan (Retained Earnings)',
                'type' => PlatformChartOfAccount::TYPE_EQUITY,
                'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                'category_group' => 'modal',
                'description' => 'Akumulasi laba bersih tahun-tahun sebelumnya yang diinvestasikan kembali',
                'is_system' => true,
            ],

            // 4. PENDAPATAN (4-XXXX)
            [
                'code' => '4110',
                'name' => 'Pendapatan Paket Langganan SaaS',
                'type' => PlatformChartOfAccount::TYPE_REVENUE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                'category_group' => 'pendapatan_usaha',
                'description' => 'Pendapatan dari aktivasi & perpanjangan paket Starter, Pro, Scale, Enterprise',
                'is_system' => true,
            ],
            [
                'code' => '4120',
                'name' => 'Pendapatan Top-up Kuota Token AI',
                'type' => PlatformChartOfAccount::TYPE_REVENUE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                'category_group' => 'pendapatan_usaha',
                'description' => 'Pendapatan dari penjualan kuota token AI (Cooca AI Assistant)',
                'is_system' => true,
            ],
            [
                'code' => '4130',
                'name' => 'Pendapatan Biaya Setup, Onboarding & Pelatihan',
                'type' => PlatformChartOfAccount::TYPE_REVENUE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                'category_group' => 'pendapatan_usaha',
                'description' => 'Biaya implementasi sistem ERP dan migrasi data awal tenant enterprise',
                'is_system' => false,
            ],
            [
                'code' => '4190',
                'name' => 'Pendapatan Lain-lain & Cashback Gateway',
                'type' => PlatformChartOfAccount::TYPE_REVENUE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                'category_group' => 'pendapatan_lain',
                'description' => 'Bunga bank platform, reward, dan penerimaan non-operasional lainnya',
                'is_system' => false,
            ],

            // 5. BEBAN POKOK & OPERASIONAL (5-XXXX)
            [
                'code' => '5110',
                'name' => 'Beban Server, Cloud VPS & Hosting (Hostinger / AWS)',
                'type' => PlatformChartOfAccount::TYPE_EXPENSE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'beban_pokok',
                'description' => 'Biaya server production, Redis, database replication, dan domain',
                'is_system' => true,
            ],
            [
                'code' => '5120',
                'name' => 'Beban API LLM AI (Google Gemini / Anthropic)',
                'type' => PlatformChartOfAccount::TYPE_EXPENSE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'beban_pokok',
                'description' => 'Biaya token inference API LLM untuk fitur cerdas Cooca AI',
                'is_system' => true,
            ],
            [
                'code' => '5130',
                'name' => 'Beban WhatsApp Cloud API Gateway (Meta OTP & Notif)',
                'type' => PlatformChartOfAccount::TYPE_EXPENSE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'beban_pokok',
                'description' => 'Biaya per-percakapan Meta WhatsApp Business Cloud API',
                'is_system' => true,
            ],
            [
                'code' => '5140',
                'name' => 'Beban Fee Payment Gateway & Merchant Discount Rate (MDR)',
                'type' => PlatformChartOfAccount::TYPE_EXPENSE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'beban_pokok',
                'description' => 'Potongan biaya transaksi payment gateway saat pembayaran langganan',
                'is_system' => true,
            ],
            [
                'code' => '5210',
                'name' => 'Beban Gaji & Honorarium Tim Developer / Staf',
                'type' => PlatformChartOfAccount::TYPE_EXPENSE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'beban_operasional',
                'description' => 'Gaji insinyur software, tim customer support, dan staf administrasi',
                'is_system' => true,
            ],
            [
                'code' => '5220',
                'name' => 'Beban Pemasaran, Iklan & Event Komunitas',
                'type' => PlatformChartOfAccount::TYPE_EXPENSE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'beban_operasional',
                'description' => 'Biaya Google Ads, Meta Ads, sponsorship, dan workshop UMKM',
                'is_system' => false,
            ],
            [
                'code' => '5230',
                'name' => 'Beban Operasional Kantor, Sewa & Legalitas',
                'type' => PlatformChartOfAccount::TYPE_EXPENSE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'beban_operasional',
                'description' => 'Sewa co-working space, utilitas, biaya notaris, dan legalitas PT',
                'is_system' => false,
            ],
            [
                'code' => '5290',
                'name' => 'Beban Administrasi Bank & Lainnya',
                'type' => PlatformChartOfAccount::TYPE_EXPENSE,
                'normal_balance' => PlatformChartOfAccount::BALANCE_DEBIT,
                'category_group' => 'beban_operasional',
                'description' => 'Biaya admin bulanan rekening bank dan pengeluaran insidentil',
                'is_system' => false,
            ],
        ];

        foreach ($accounts as $acc) {
            PlatformChartOfAccount::updateOrCreate(
                ['code' => $acc['code']],
                $acc
            );
        }
    }
}
