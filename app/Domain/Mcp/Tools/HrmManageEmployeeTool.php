<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Mcp\Tools\Traits\HandlesCrudPermissions;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Location;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class HrmManageEmployeeTool implements McpToolInterface
{
    use HandlesCrudPermissions;

    public function getName(): string
    {
        return 'hrm_manage_employee';
    }

    public function getDescription(): string
    {
        return 'Mengelola karyawan, absensi, dan data payroll penggajian di menu HRM COOCA: melihat daftar staf, mendaftarkan karyawan baru (posisi, jabatan, gaji pokok), mencatat presensi/absensi, serta rekap slip gaji.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:hrm:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'create', 'update', 'record_attendance', 'get_payroll'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar karyawan), create (tambah staf baru), update (ubah jabatan/gaji/status), record_attendance (catat absensi masuk/pulang), get_payroll (rekap data slip gaji).',
                ],
                'employee_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau email karyawan (wajib untuk update atau record_attendance).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Nama lengkap karyawan.',
                ],
                'email' => [
                    'type' => 'string',
                    'description' => 'Email karyawan untuk login/notifikasi.',
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Nomor WhatsApp / telepon karyawan.',
                ],
                'role' => [
                    'type' => 'string',
                    'enum' => ['staff', 'cashier', 'kitchen', 'warehouse', 'manager'],
                    'default' => 'staff',
                    'description' => 'Peran staf di operasional.',
                ],
                'job_title' => [
                    'type' => 'string',
                    'description' => 'Jabatan formal (misal: "Barista Senior", "Kepala Gudang", "Kasir").',
                ],
                'base_salary' => [
                    'type' => 'number',
                    'description' => 'Gaji pokok per bulan (Rp).',
                ],
                'location_id' => [
                    'type' => 'string',
                    'description' => 'UUID lokasi penempatan cabang / outlet.',
                ],
                'attendance_status' => [
                    'type' => 'string',
                    'enum' => ['present', 'sick', 'leave', 'permit', 'overtime'],
                    'default' => 'present',
                    'description' => 'Status absensi saat record_attendance.',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Keterangan absensi atau catatan karyawan.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list'));

        return match ($action) {
            'create'            => $this->createEmployee($business, $user, $arguments),
            'update'            => $this->updateEmployee($business, $user, $arguments),
            'record_attendance' => $this->recordAttendance($business, $user, $arguments),
            'get_payroll'       => $this->getPayroll($business, $user, $arguments),
            default             => $this->listEmployees($business, $user, $arguments),
        };
    }

    private function listEmployees(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('hrm', 'read', $business, $user);

        $memberships = BusinessMembership::where('business_id', $business->id)
            ->with(['user'])
            ->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar karyawan dan tim.',
            'count' => $memberships->count(),
            'employees' => $memberships->map(fn (BusinessMembership $m) => [
                'id' => $m->id,
                'user_id' => $m->user_id,
                'name' => $m->user?->name,
                'email' => $m->user?->email,
                'phone' => $m->user?->phone,
                'role' => $m->role,
                'job_title' => $m->job_title ?? ucfirst($m->role ?? 'staff'),
                'base_salary' => (float) ($m->base_salary ?? 0),
                'is_active' => (bool) $m->is_active,
            ])->all(),
        ];
    }

    private function createEmployee(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('hrm', 'create', $business, $user);

        $name = trim((string) ($args['name'] ?? ''));
        $email = trim((string) ($args['email'] ?? ''));

        if (empty($name) || empty($email)) {
            throw new InvalidArgumentException('Nama karyawan (name) dan email wajib diisi.');
        }

        $targetUser = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $args['phone'] ?? null,
                'password' => Hash::make(Str::random(16)),
            ]
        );

        $membership = BusinessMembership::updateOrCreate(
            [
                'business_id' => $business->id,
                'user_id' => $targetUser->id,
            ],
            [
                'role' => $args['role'] ?? 'staff',
                'job_title' => $args['job_title'] ?? ucfirst((string) ($args['role'] ?? 'Staff')),
                'base_salary' => (float) ($args['base_salary'] ?? 0),
                'is_active' => true,
            ]
        );

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_create_employee',
            'auditable_type' => User::class,
            'auditable_id' => $targetUser->id,
            'new_values' => [
                'name' => $targetUser->name,
                'role' => $membership->role,
                'job_title' => $membership->job_title,
                'base_salary' => $membership->base_salary,
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Karyawan [{$targetUser->name}] ({$membership->job_title}) berhasil didaftarkan ke sistem.",
            'employee' => [
                'id' => $targetUser->id,
                'user_id' => $targetUser->id,
                'membership_id' => $membership->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'phone' => $targetUser->phone,
                'role' => $membership->role,
                'job_title' => $membership->job_title,
                'base_salary' => (float) $membership->base_salary,
            ],
        ];
    }

    private function updateEmployee(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('hrm', 'update', $business, $user);

        $empId = $args['employee_id'] ?? null;
        if (! $empId) {
            throw new InvalidArgumentException('employee_id wajib diisi untuk mengubah data karyawan.');
        }

        $targetUser = User::where('id', $empId)->orWhere('email', $empId)->first();
        if (! $targetUser) {
            throw new InvalidArgumentException("Karyawan [{$empId}] tidak ditemukan.");
        }

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $targetUser->id)
            ->firstOrFail();

        if (isset($args['role'])) {
            $membership->role = (string) $args['role'];
        }
        if (isset($args['job_title'])) {
            $membership->job_title = (string) $args['job_title'];
        }
        if (isset($args['base_salary'])) {
            $membership->base_salary = (float) $args['base_salary'];
        }
        if (isset($args['is_active'])) {
            $membership->is_active = (bool) $args['is_active'];
        }

        $membership->save();

        return [
            'success' => true,
            'message' => "Data karyawan [{$targetUser->name}] berhasil diperbarui.",
            'employee' => [
                'user_id' => $targetUser->id,
                'name' => $targetUser->name,
                'role' => $membership->role,
                'job_title' => $membership->job_title,
                'base_salary' => (float) $membership->base_salary,
                'is_active' => (bool) $membership->is_active,
            ],
        ];
    }

    private function recordAttendance(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('hrm', 'update', $business, $user);

        $empId = $args['employee_id'] ?? null;
        if (! $empId) {
            throw new InvalidArgumentException('employee_id wajib diisi untuk mencatat absensi.');
        }

        $targetUser = User::where('id', $empId)->orWhere('email', $empId)->firstOrFail();
        $locationId = $args['location_id'] ?? Location::where('business_id', $business->id)->where('is_primary', true)->value('id');

        $status = $args['status'] ?? $args['attendance_status'] ?? 'present';
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::updateOrCreate(
            [
                'business_id' => $business->id,
                'user_id' => $targetUser->id,
                'date' => $today,
            ],
            [
                'location_id' => $locationId,
                'clock_in_at' => now(),
                'status' => $status,
                'notes' => $args['notes'] ?? 'Presensi via MCP Agent',
            ]
        );

        return [
            'success' => true,
            'message' => "Absensi [{$targetUser->name}] tanggal {$today} berhasil dicatat (Status: {$status}).",
            'attendance' => [
                'employee' => $targetUser->name,
                'date' => $attendance->date,
                'status' => $attendance->status,
                'clock_in_at' => $attendance->clock_in_at?->toDateTimeString(),
            ],
        ];
    }

    private function getPayroll(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('hrm', 'read', $business, $user);

        $payrolls = Payroll::where('business_id', $business->id)
            ->with(['items'])
            ->latest()
            ->take(5)
            ->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil ringkasan payroll penggajian.',
            'count' => $payrolls->count(),
            'payrolls' => $payrolls->map(fn (Payroll $p) => [
                'id' => $p->id,
                'payroll_number' => $p->payroll_number ?? 'PR-' . $p->id,
                'period_month' => $p->period_month ?? null,
                'period_year' => $p->period_year ?? null,
                'status' => $p->status ?? 'completed',
                'total_amount' => (float) ($p->total_amount ?? 0),
                'employees_count' => $p->items->count(),
            ])->all(),
        ];
    }
}
