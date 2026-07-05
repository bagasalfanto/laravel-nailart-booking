<?php

namespace Database\Seeders;

use App\Models\TreatmentCategory;
use App\Models\TreatmentKatalog;
use Illuminate\Database\Seeder;

class TreatmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'category' => [
                    'name'        => 'Manicure',
                    'description' => 'Perawatan kuku tangan dengan finishing rapi & glossy.',
                    'sort_order'  => 1,
                ],
                'treatments' => [
                    [
                        'kode_jasa' => 'MNC-001',
                        'nama_jasa' => 'Manicure Basic',
                        'deskripsi' => 'Cleaning kutikula, shaping kuku, dan polish standar.',
                        'price_type'=> 'fixed',
                        'price_min' => 75000,
                    ],
                    [
                        'kode_jasa' => 'MNC-002',
                        'nama_jasa' => 'Manicure Gel Polish',
                        'deskripsi' => 'Polish gel tahan 2-3 minggu dengan beragam pilihan warna.',
                        'price_type'=> 'fixed',
                        'price_min' => 150000,
                    ],
                    [
                        'kode_jasa' => 'MNC-003',
                        'nama_jasa' => 'French Manicure',
                        'deskripsi' => 'Tampilan klasik elegan dengan ujung kuku putih natural.',
                        'price_type'=> 'range',
                        'price_min' => 120000,
                        'price_max' => 180000,
                    ],
                ],
            ],
            [
                'category' => [
                    'name'        => 'Pedicure',
                    'description' => 'Perawatan kuku kaki dengan eksfoliasi & relaxation.',
                    'sort_order'  => 2,
                ],
                'treatments' => [
                    [
                        'kode_jasa' => 'PDC-001',
                        'nama_jasa' => 'Pedicure Basic',
                        'deskripsi' => 'Soaking, cleaning, dan polish kaki.',
                        'price_type'=> 'fixed',
                        'price_min' => 100000,
                    ],
                    [
                        'kode_jasa' => 'PDC-002',
                        'nama_jasa' => 'Pedicure Spa',
                        'deskripsi' => 'Pedicure premium dengan scrub, masker, dan massage kaki.',
                        'price_type'=> 'range',
                        'price_min' => 200000,
                        'price_max' => 280000,
                    ],
                ],
            ],
            [
                'category' => [
                    'name'        => 'Nail Art',
                    'description' => 'Desain custom dari simple sampai 3D eksklusif.',
                    'sort_order'  => 3,
                ],
                'treatments' => [
                    [
                        'kode_jasa' => 'ART-001',
                        'nama_jasa' => 'Simple Art Per Nail',
                        'deskripsi' => 'Desain ringan seperti dot, line, atau warna gradasi.',
                        'price_type'=> 'fixed',
                        'price_min' => 15000,
                    ],
                    [
                        'kode_jasa' => 'ART-002',
                        'nama_jasa' => 'Korean Floral Nail',
                        'deskripsi' => 'Hand-paint floral ala Korea dengan sentuhan pastel & glossy.',
                        'price_type'=> 'range',
                        'price_min' => 200000,
                        'price_max' => 300000,
                    ],
                    [
                        'kode_jasa' => 'ART-003',
                        'nama_jasa' => '3D Diamond Glitter',
                        'deskripsi' => 'Aksesoris 3D dengan diamond, glitter, dan tekstur eksklusif.',
                        'price_type'=> 'range',
                        'price_min' => 280000,
                        'price_max' => 450000,
                    ],
                    [
                        'kode_jasa' => 'ART-004',
                        'nama_jasa' => 'Bridal Nail Set',
                        'deskripsi' => 'Paket desain kuku premium untuk pengantin & engagement.',
                        'price_type'=> 'fixed',
                        'price_min' => 500000,
                    ],
                ],
            ],
        ];

        foreach ($data as $group) {
            $category = TreatmentCategory::updateOrCreate(
                ['name' => $group['category']['name']],
                array_merge($group['category'], ['is_active' => true])
            );

            foreach ($group['treatments'] as $idx => $t) {
                TreatmentKatalog::updateOrCreate(
                    ['kode_jasa' => $t['kode_jasa']],
                    array_merge($t, [
                        'category_id' => $category->id,
                        'sort_order'  => $idx + 1,
                        'is_active'   => true,
                    ])
                );
            }
        }

        $this->command->info('Treatment categories & catalog seeded successfully.');
    }
}
