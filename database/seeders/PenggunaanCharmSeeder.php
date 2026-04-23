<?php

namespace Database\Seeders;

use App\Models\DataCharm;
use App\Models\PenggunaanCharm;
use App\Models\Reservasi;
use Illuminate\Database\Seeder;

class PenggunaanCharmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reservasis = Reservasi::all();
        $charms = DataCharm::all();

        if ($reservasis->isEmpty() || $charms->isEmpty()) {
            $this->command->error('Missing required data. Please run ReservasiSeeder and DataCharmSeeder first.');
            return;
        }

        $usagePatterns = [
            [[0, 1], [2, 1]],
            [[1, 2], [3, 1], [4, 1]],
            [[2, 1], [5, 1]],
            [[6, 1], [0, 2]],
            [[3, 1]],
            [[7, 1], [1, 1], [4, 1]],
        ];

        foreach ($reservasis as $index => $reservasi) {
            $pattern = $usagePatterns[$index % count($usagePatterns)];

            PenggunaanCharm::query()
                ->where('reservasi_id', $reservasi->id)
                ->delete();

            foreach ($pattern as [$charmIndex, $qty]) {
                $charm = $charms->get($charmIndex % $charms->count());

                if (!$charm) {
                    continue;
                }

                PenggunaanCharm::create([
                    'reservasi_id' => $reservasi->id,
                    'charm_id' => $charm->id,
                    'jumlah_dipakai' => $qty,
                    'subtotal' => (float) $charm->harga * $qty,
                ]);
            }
        }

        $this->command->info('PenggunaanCharm seeded successfully.');
    }
}
