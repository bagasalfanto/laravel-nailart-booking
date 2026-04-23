<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     *
     */
    public function run(): void
    {
        // Disable activity logging during seeding
        config(['activitylog.enabled' => false]);

        $this->command->newLine();

        // 1. Seed Master Data
        $this->call(StatusBookingSeeder::class);
        $this->call(TreatmentKatalogSeeder::class);
        $this->call(DataCharmSeeder::class);
        $this->call(WebSettingSeeder::class);
        $this->call(FaqSeeder::class);
        $this->command->newLine();

        // 2. Seed Users & Profiles
        $this->call(UserSeeder::class);
        $this->call(RoleSeeder::class);
        $this->command->newLine();

        // 3. Seed Sidebar Menu
        $this->call(SidebarMenuSeeder::class);
        $this->command->newLine();

        // 4. Seed Portfolio
        $this->call(PortfolioSeeder::class);
        $this->command->newLine();

        // 5. Seed Transactions
        $this->call(ReservasiSeeder::class);
        $this->call(PenggunaanCharmSeeder::class);
        $this->call(PembayaranSeeder::class);
        $this->command->newLine();

        // Re-enable activity logging
        config(['activitylog.enabled' => true]);

        $this->command->info('Database seeding completed successfully!');
    }
}
