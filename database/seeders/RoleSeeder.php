<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $nailist    = Role::firstOrCreate(['name' => 'nailist', 'guard_name' => 'web']);
        $customer   = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Bersihkan role lama yang salah penamaannya bila ada.
        Role::query()->where('guard_name', 'web')->where('name', 'customers')->delete();

        $allPermissions = Permission::query()->where('guard_name', 'web')->pluck('name')->all();

        // Superadmin: semua permission (Gate::before juga me-bypass, ini sebagai fallback eksplisit).
        $superadmin->syncPermissions($allPermissions);

        // Admin: kelola operasional, tidak boleh kelola role/permission/sidebar/web-setting kritis.
        $adminPermissions = array_values(array_filter($allPermissions, function (string $name) {
            return ! str_starts_with($name, 'role.')
                && ! str_starts_with($name, 'permission.')
                && ! str_starts_with($name, 'sidebar-menu.');
        }));
        $admin->syncPermissions($adminPermissions);

        // Nailist: lihat dashboard & data terkait jadwal/treatment, kelola portfolio sendiri.
        $nailist->syncPermissions(array_intersect($allPermissions, [
            'dashboard.view',
            'reservasi.view', 'reservasi.update', 'reservasi.confirm', 'reservasi.cancel',
            'treatment.view',
            'charm.view',
            'portfolio.view', 'portfolio.create', 'portfolio.update', 'portfolio.delete',
            'customer.view',
            'pembayaran.view',
            'faq.view',
            'review.view',
        ]));

        // Customer: hanya akses fitur booking & melihat data publik.
        $customer->syncPermissions(array_intersect($allPermissions, [
            'reservasi.view', 'reservasi.create', 'reservasi.cancel',
            'treatment.view',
            'charm.view',
            'portfolio.view',
            'pembayaran.view', 'pembayaran.create',
            'faq.view',
            'review.view', 'review.create', 'review.update', 'review.delete',
        ]));

        // Sinkronisasi role berdasarkan profil user yang sudah ada.
        // User dengan kode_admin berawalan "SADM" diperlakukan sebagai superadmin.
        User::with('admin')->whereHas('admin')->get()->each(function (User $user) use ($admin, $superadmin) {
            $isSuperadmin = $user->admin && str_starts_with((string) $user->admin->kode_admin, 'SADM');
            $user->syncRoles([$isSuperadmin ? $superadmin : $admin]);
        });
        User::whereHas('nailist')->get()->each(fn (User $user) => $user->syncRoles([$nailist]));
        User::whereHas('customer')->get()->each(fn (User $user) => $user->syncRoles([$customer]));

        Artisan::call('permission:cache-reset');

        $this->command->info('Roles seeded successfully (superadmin, admin, nailist, customer).');
    }
}
