<?php

namespace Database\Seeders;

use App\Models\DataCharm;
use Illuminate\Database\Seeder;

class DataCharmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $charms = [
            ['nama_charm' => 'Pita Silver 3D', 'stok' => 50, 'harga' => 5000],
            ['nama_charm' => 'Kristal Emas Bulat', 'stok' => 75, 'harga' => 7500],
            ['nama_charm' => 'Bunga Rhinestone', 'stok' => 100, 'harga' => 10000],
            ['nama_charm' => 'Kupu-kupu Hologram', 'stok' => 30, 'harga' => 12000],
            ['nama_charm' => 'Pearl Mini White', 'stok' => 120, 'harga' => 6000],
            ['nama_charm' => 'Star Chrome Pink', 'stok' => 90, 'harga' => 8500],
            ['nama_charm' => 'Heart Crystal Clear', 'stok' => 65, 'harga' => 11000],
            ['nama_charm' => 'Snowflake Glitter', 'stok' => 80, 'harga' => 9000],
        ];

        foreach ($charms as $charm) {
            DataCharm::updateOrCreate(
                ['nama_charm' => $charm['nama_charm']],
                $charm
            );
        }

        $this->command->info('DataCharm seeded successfully.');
    }
}
