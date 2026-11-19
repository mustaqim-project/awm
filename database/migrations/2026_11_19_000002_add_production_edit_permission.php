<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $perm = Permission::firstOrCreate(
            ['slug' => 'production.edit'],
            [
                'name' => 'Edit Perintah Produksi',
                'category' => 'production',
                'description' => 'Mengubah data dan rincian alokasi bahan baku SPKP produksi.',
            ]
        );

        // Assign to all roles that currently have production.create
        $rolesWithCreate = Role::whereHas('permissions', function ($q) {
            $q->where('slug', 'production.create');
        })->get();

        foreach ($rolesWithCreate as $role) {
            $role->permissions()->syncWithoutDetaching([$perm->id]);
        }
    }

    public function down(): void
    {
        Permission::where('slug', 'production.edit')->delete();
    }
};
