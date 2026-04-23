<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $nailistRole = Role::firstOrCreate(['name' => 'nailist', 'guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customers', 'guard_name' => 'web']);

        Role::query()->where('name', 'customer')->where('guard_name', 'web')->delete();

        User::whereHas('admin')->get()->each(fn (User $user) => $user->syncRoles([$adminRole]));
        User::whereHas('nailist')->get()->each(fn (User $user) => $user->syncRoles([$nailistRole]));
        User::whereHas('customer')->get()->each(fn (User $user) => $user->syncRoles([$customerRole]));

        $this->command->info('Roles seeded successfully (admin, nailist, customers).');
    }
}
