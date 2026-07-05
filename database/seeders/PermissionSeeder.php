<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * List permission per resource.
     *
     * @var array<string, array<int, string>>
     */
    protected array $resources = [
        'dashboard'    => ['view'],
        'user'         => ['view', 'create', 'update', 'delete'],
        'admin'        => ['view', 'create', 'update', 'delete'],
        'nailist'      => ['view', 'create', 'update', 'delete'],
        'customer'     => ['view', 'create', 'update', 'delete'],
        'treatment'    => ['view', 'create', 'update', 'delete'],
        'charm'        => ['view', 'create', 'update', 'delete'],
        'portfolio'    => ['view', 'create', 'update', 'delete'],
        'reservasi'    => ['view', 'create', 'update', 'delete', 'confirm', 'cancel'],
        'pembayaran'   => ['view', 'create', 'update', 'delete'],
        'status-booking' => ['view', 'create', 'update', 'delete'],
        'web-setting'  => ['view', 'create', 'update', 'delete'],
        'faq'          => ['view', 'create', 'update', 'delete'],
        'sidebar-menu' => ['view', 'create', 'update', 'delete'],
        'role'         => ['view', 'create', 'update', 'delete'],
        'permission'   => ['view', 'create', 'update', 'delete'],
        'activity-log' => ['view'],
        'review'       => ['view', 'create', 'update', 'delete', 'feature'],
        'specialty'    => ['view', 'create', 'update', 'delete'],
    ];

    public function run(): void
    {
        foreach ($this->resources as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$resource}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        Artisan::call('permission:cache-reset');

        $this->command->info('Permissions seeded successfully.');
    }

    /**
     * Helper to expose all permission names defined in this seeder.
     *
     * @return array<int, string>
     */
    public static function allPermissionNames(): array
    {
        $seeder = new self();
        $names = [];

        foreach ($seeder->resources as $resource => $actions) {
            foreach ($actions as $action) {
                $names[] = "{$resource}.{$action}";
            }
        }

        return $names;
    }
}
