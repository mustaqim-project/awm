<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Mcp\Tools\Traits\HandlesCrudPermissions;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use InvalidArgumentException;

final class CrmManageCustomerTool implements McpToolInterface
{
    use HandlesCrudPermissions;

    public function getName(): string
    {
        return 'crm_manage_customer';
    }

    public function getDescription(): string
    {
        return 'Mengelola data pelanggan (CRM & Member) di menu Pelanggan COOCA: melihat daftar pelanggan, membuat pelanggan baru, memperbarui kontak/poin loyalitas, serta menghapus data pelanggan.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:crm:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'get', 'create', 'update', 'delete'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar pelanggan), get (detail pelanggan & riwayat), create (tambah pelanggan baru), update (ubah data), delete (hapus pelanggan).',
                ],
                'customer_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nomor telepon pelanggan (wajib untuk get, update, delete).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Nama lengkap pelanggan / member.',
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Nomor WhatsApp / telepon pelanggan.',
                ],
                'email' => [
                    'type' => 'string',
                    'description' => 'Email pelanggan.',
                ],
                'company_name' => [
                    'type' => 'string',
                    'description' => 'Nama perusahaan / instansi pelanggan (untuk klien B2B).',
                ],
                'address' => [
                    'type' => 'string',
                    'description' => 'Alamat pengiriman / domisili pelanggan.',
                ],
                'points' => [
                    'type' => 'number',
                    'description' => 'Poin loyalitas member.',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Catatan CRM (preferensi, komplain, atau histori khusus).',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list'));

        return match ($action) {
            'create' => $this->createCustomer($business, $user, $arguments),
            'update' => $this->updateCustomer($business, $user, $arguments),
            'delete' => $this->deleteCustomer($business, $user, $arguments),
            'get'    => $this->getCustomer($business, $user, $arguments),
            default  => $this->listCustomers($business, $user, $arguments),
        };
    }

    private function listCustomers(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('crm', 'read', $business, $user);

        $query = Customer::where('business_id', $business->id)->latest()->take(25);

        if (! empty($args['name'])) {
            $query->where('name', 'like', '%' . $args['name'] . '%');
        }

        $customers = $query->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar pelanggan.',
            'count' => $customers->count(),
            'customers' => $customers->map(fn (Customer $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'email' => $c->email,
                'company_name' => $c->company_name,
                'points' => (int) ($c->points ?? 0),
                'created_at' => $c->created_at?->format('Y-m-d'),
            ])->all(),
        ];
    }

    private function getCustomer(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('crm', 'read', $business, $user);

        $id = $args['customer_id'] ?? null;
        if (! $id) {
            throw new InvalidArgumentException('customer_id wajib diisi untuk melihat profil pelanggan.');
        }

        $customer = Customer::where('business_id', $business->id)
            ->where(fn ($q) => $q->where('id', $id)->orWhere('phone', $id)->orWhere('name', 'like', "%{$id}%"))
            ->firstOrFail();

        return [
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'address' => $customer->address,
                'company_name' => $customer->company_name,
                'points' => (int) ($customer->points ?? 0),
                'notes' => $customer->notes,
            ],
        ];
    }

    private function createCustomer(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('crm', 'create', $business, $user);

        $name = trim((string) ($args['name'] ?? ''));
        if (empty($name)) {
            throw new InvalidArgumentException('Nama pelanggan (name) wajib diisi.');
        }

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => $name,
            'phone' => $args['phone'] ?? null,
            'email' => $args['email'] ?? null,
            'company_name' => $args['company_name'] ?? null,
            'address' => $args['address'] ?? null,
            'points' => (int) ($args['points'] ?? 0),
            'notes' => $args['notes'] ?? null,
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_create_customer',
            'auditable_type' => Customer::class,
            'auditable_id' => $customer->id,
            'new_values' => ['name' => $customer->name, 'phone' => $customer->phone],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Pelanggan [{$customer->name}] berhasil ditambahkan ke CRM.",
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'points' => (int) $customer->points,
            ],
        ];
    }

    private function updateCustomer(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('crm', 'update', $business, $user);

        $id = $args['customer_id'] ?? null;
        if (! $id) {
            throw new InvalidArgumentException('customer_id wajib diisi untuk mengubah data pelanggan.');
        }

        $customer = Customer::where('business_id', $business->id)
            ->where(fn ($q) => $q->where('id', $id)->orWhere('phone', $id))
            ->firstOrFail();

        if (isset($args['name']) && trim((string) $args['name']) !== '') {
            $customer->name = trim((string) $args['name']);
        }
        if (isset($args['phone'])) {
            $customer->phone = trim((string) $args['phone']);
        }
        if (isset($args['email'])) {
            $customer->email = trim((string) $args['email']);
        }
        if (isset($args['company_name'])) {
            $customer->company_name = trim((string) $args['company_name']);
        }
        if (isset($args['address'])) {
            $customer->address = trim((string) $args['address']);
        }
        if (isset($args['points'])) {
            $customer->points = (int) $args['points'];
        }
        if (isset($args['notes'])) {
            $customer->notes = (string) $args['notes'];
        }

        $customer->save();

        return [
            'success' => true,
            'message' => "Data pelanggan [{$customer->name}] berhasil diperbarui.",
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'points' => (int) $customer->points,
            ],
        ];
    }

    private function deleteCustomer(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('crm', 'delete', $business, $user);

        $id = $args['customer_id'] ?? null;
        if (! $id) {
            throw new InvalidArgumentException('customer_id wajib diisi untuk menghapus pelanggan.');
        }

        $customer = Customer::where('business_id', $business->id)
            ->where(fn ($q) => $q->where('id', $id)->orWhere('phone', $id))
            ->firstOrFail();

        $name = $customer->name;
        $customer->delete();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_delete_customer',
            'auditable_type' => Customer::class,
            'auditable_id' => $id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Pelanggan [{$name}] berhasil dihapus dari sistem.",
        ];
    }
}
