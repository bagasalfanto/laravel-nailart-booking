<?php

namespace Database\Seeders;

use App\Models\SidebarMenu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SidebarMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requiredRoles = ['admin', 'nailist', 'customers'];
        $rolesByName = Role::query()
            ->whereIn('name', $requiredRoles)
            ->pluck('id', 'name');

        foreach ($requiredRoles as $roleName) {
            if (!$rolesByName->has($roleName)) {
                $this->command->error("Role {$roleName} tidak ditemukan. Jalankan RoleSeeder terlebih dahulu.");
                return;
            }
        }

        if ($rolesByName->isEmpty()) {
            $this->command->error('Role data not found. Please run RoleSeeder first.');
            return;
        }

        $dashboard = SidebarMenu::updateOrCreate([
            'title' => 'Dashboard',
            'parent_id' => null,
        ], [
            'icon' => 'layout-dashboard',
            'route_name' => 'dashboard.index',
            'permission_name' => null,
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $reservasi = SidebarMenu::updateOrCreate([
            'title' => 'Reservasi',
            'parent_id' => null,
        ], [
            'icon' => 'calendar-days',
            'route_name' => 'reservasi.index',
            'permission_name' => 'reservasi.view',
            'sort_order' => 20,
            'is_active' => true,
        ]);

        $portfolio = SidebarMenu::updateOrCreate([
            'title' => 'Portfolio',
            'parent_id' => null,
        ], [
            'icon' => 'images',
            'route_name' => 'portfolio.index',
            'permission_name' => 'portfolio.view',
            'sort_order' => 30,
            'is_active' => true,
        ]);

        $pembayaran = SidebarMenu::updateOrCreate([
            'title' => 'Pembayaran',
            'parent_id' => null,
        ], [
            'icon' => 'credit-card',
            'route_name' => 'pembayaran.index',
            'permission_name' => 'pembayaran.view',
            'sort_order' => 40,
            'is_active' => true,
        ]);

        $masterData = SidebarMenu::updateOrCreate([
            'title' => 'Master Data',
            'parent_id' => null,
        ], [
            'icon' => 'database',
            'sort_order' => 50,
            'is_active' => true,
        ]);

        $users = SidebarMenu::updateOrCreate([
            'parent_id' => $masterData->id,
            'title' => 'Users',
        ], [
            'icon' => 'users',
            'route_name' => 'users.index',
            'permission_name' => 'users.view',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $treatments = SidebarMenu::updateOrCreate([
            'parent_id' => $masterData->id,
            'title' => 'Treatment',
        ], [
            'icon' => 'sparkles',
            'route_name' => 'treatment.index',
            'permission_name' => 'treatment.view',
            'sort_order' => 20,
            'is_active' => true,
        ]);

        $dataCharms = SidebarMenu::updateOrCreate([
            'parent_id' => $masterData->id,
            'title' => 'Data Charm',
        ], [
            'icon' => 'gem',
            'route_name' => 'data-charm.index',
            'permission_name' => 'datacharm.view',
            'sort_order' => 30,
            'is_active' => true,
        ]);

        $faq = SidebarMenu::updateOrCreate([
            'title' => 'FAQ',
            'parent_id' => null,
        ], [
            'icon' => 'circle-help',
            'route_name' => 'faq.index',
            'permission_name' => null,
            'sort_order' => 55,
            'is_active' => true,
        ]);

        $settings = SidebarMenu::updateOrCreate([
            'title' => 'Pengaturan Website',
            'parent_id' => null,
        ], [
            'icon' => 'settings',
            'route_name' => 'web-setting.index',
            'permission_name' => 'websetting.view',
            'sort_order' => 60,
            'is_active' => true,
        ]);

        $allRoles = $rolesByName->values()->all();
        $adminOnly = [$rolesByName['admin']];
        $adminAndNailist = [$rolesByName['admin'], $rolesByName['nailist']];
        $adminAndCustomer = [$rolesByName['admin'], $rolesByName['customers']];

        $dashboard->roles()->sync($allRoles);
        $reservasi->roles()->sync($allRoles);
        $portfolio->roles()->sync($adminAndNailist);
        $pembayaran->roles()->sync($adminAndCustomer);
        $masterData->roles()->sync($adminOnly);
        $users->roles()->sync($adminOnly);
        $treatments->roles()->sync($adminOnly);
        $dataCharms->roles()->sync($adminOnly);
        $faq->roles()->sync($allRoles);
        $settings->roles()->sync($adminOnly);

        $this->command->info('Sidebar menus seeded successfully.');
    }
}
