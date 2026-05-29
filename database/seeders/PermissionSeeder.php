<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Senarai kebenaran sistem
        $permissions = [
            ['name' => 'Urus Ahli',          'slug' => 'manage-users',     'description' => 'Tambah, sunting, padam ahli'],
            ['name' => 'Mohon Pinjaman',     'slug' => 'apply-loan',       'description' => 'Hantar permohonan pinjaman'],
            ['name' => 'Lulus Pinjaman',     'slug' => 'approve-loan',     'description' => 'Lulus atau tolak permohonan pinjaman'],
            ['name' => 'Urus Simpanan',      'slug' => 'manage-savings',   'description' => 'Rekod simpanan & saham'],
            ['name' => 'Urus Mesyuarat',     'slug' => 'manage-meetings',  'description' => 'Cipta & urus mesyuarat'],
            ['name' => 'Lihat Mesyuarat',    'slug' => 'view-meetings',    'description' => 'Pantau mesyuarat & minit'],
            ['name' => 'Lihat Audit',        'slug' => 'view-audit',       'description' => 'Semak laporan audit kewangan'],
            ['name' => 'Urus Tetapan',       'slug' => 'manage-settings',  'description' => 'Urus peranan & kebenaran sistem'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // Pemetaan peranan → kebenaran (ikut Module Access Matrix, Seksyen 5)
        $map = [
            'admin'    => ['manage-users', 'manage-savings', 'manage-meetings', 'view-meetings'],
            'pengurus' => ['approve-loan', 'manage-savings', 'manage-meetings', 'view-meetings'],
            'kerani'   => ['manage-users', 'manage-savings'],
            'jk'       => ['view-meetings'],
            'auditor'  => ['view-audit'],
            'ahli'     => ['apply-loan'],
        ];

        foreach ($map as $roleSlug => $permSlugs) {
            $role = Role::where('slug', $roleSlug)->first();
            if (! $role) {
                continue;
            }

            $ids = Permission::whereIn('slug', $permSlugs)->pluck('id');
            $role->permissions()->syncWithoutDetaching($ids);
        }

        // Super User dapat SEMUA kebenaran
        $superUser = Role::where('slug', 'super-user')->first();
        if ($superUser) {
            $superUser->permissions()->sync(Permission::pluck('id'));
        }
    }
}
