<?php

namespace Database\Seeders;

use App\Models\Nailist;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nailists = Nailist::query()->orderBy('created_at')->get();

        if ($nailists->isEmpty()) {
            $this->command->error('No nailists found. Please run UserSeeder first.');
            return;
        }

        $portfolioSets = [
            [
                [
                    'gambar_url' => '/storage/dummy/portfolio/korean-floral-set.jpg',
                    'deskripsi' => 'Desain floral spring collection dengan sentuhan Korea.',
                ],
                [
                    'gambar_url' => '/storage/dummy/portfolio/3d-diamond-nails.jpg',
                    'deskripsi' => 'Nail art 3D dengan tekstur diamond dan glitter halus.',
                ],
                [
                    'gambar_url' => '/storage/dummy/portfolio/pastel-gradient-set.jpg',
                    'deskripsi' => 'Gradient warna pastel dengan finishing glossy.',
                ],
            ],
            [
                [
                    'gambar_url' => '/storage/dummy/portfolio/geometric-minimalist.jpg',
                    'deskripsi' => 'Pola geometric tegas untuk tampilan clean dan modern.',
                ],
                [
                    'gambar_url' => '/storage/dummy/portfolio/monochrome-glossy.jpg',
                    'deskripsi' => 'Desain monochrome elegan dengan kombinasi matte dan gloss.',
                ],
                [
                    'gambar_url' => '/storage/dummy/portfolio/chrome-accent.jpg',
                    'deskripsi' => 'Look chrome accent dengan tone silver dan pink.',
                ],
            ],
            [
                [
                    'gambar_url' => '/storage/dummy/portfolio/bridal-soft-glam.jpg',
                    'deskripsi' => 'Desain bridal nude glam dengan detail crystal accent.',
                ],
                [
                    'gambar_url' => '/storage/dummy/portfolio/french-luxe.jpg',
                    'deskripsi' => 'Modern french tip dengan detail emas tipis.',
                ],
            ],
        ];

        foreach ($nailists as $index => $nailist) {
            $selectedSet = $portfolioSets[$index % count($portfolioSets)];

            $nailist->portfolios()->delete();
            $nailist->portfolios()->createMany($selectedSet);
        }

        $this->command->info('Portfolio seeded successfully.');
    }
}
