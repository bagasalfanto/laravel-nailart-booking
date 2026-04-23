<?php

namespace Database\Seeders;

use App\Models\TreatmentKatalog;
use Illuminate\Database\Seeder;

class TreatmentKatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $treatments = [
            [
                'kode_jasa' => 'TRM-001',
                'nama_jasa' => 'Manicure Gel Premium',
                'deskripsi' => 'Perawatan kuku lengkap dengan gel polish tahan lama.',
                'estimasi_harga' => 150000,
            ],
            [
                'kode_jasa' => 'TRM-002',
                'nama_jasa' => 'Pedicure Luxury',
                'deskripsi' => 'Perawatan kaki premium dengan desain eksklusif.',
                'estimasi_harga' => 200000,
            ],
            [
                'kode_jasa' => 'TRM-003',
                'nama_jasa' => 'Nail Art 3D Eksklusif',
                'deskripsi' => 'Desain custom dengan variasi 3D dan dekorasi.',
                'estimasi_harga' => 300000,
            ],
            [
                'kode_jasa' => 'TRM-004',
                'nama_jasa' => 'Acrylic Extension Basic',
                'deskripsi' => 'Pemasangan extension acrylic natural look untuk kuku pendek.',
                'estimasi_harga' => 275000,
            ],
            [
                'kode_jasa' => 'TRM-005',
                'nama_jasa' => 'Korean Blossom Art',
                'deskripsi' => 'Nail art floral ala Korea dengan kombinasi warna pastel.',
                'estimasi_harga' => 225000,
            ],
            [
                'kode_jasa' => 'TRM-006',
                'nama_jasa' => 'Bridal Nail Set',
                'deskripsi' => 'Paket desain kuku premium untuk acara pernikahan dan engagement.',
                'estimasi_harga' => 350000,
            ],
        ];

        foreach ($treatments as $treatment) {
            TreatmentKatalog::updateOrCreate(
                ['kode_jasa' => $treatment['kode_jasa']],
                $treatment
            );
        }

        $this->command->info('TreatmentKatalog seeded successfully.');
    }
}
