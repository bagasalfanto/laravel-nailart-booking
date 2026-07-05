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
                ['judul' => 'Korean Floral Pastel',  'gambar_url' => '/storage/dummy/portfolio/korean-floral-set.jpg',     'deskripsi' => 'Desain floral spring collection dengan sentuhan Korea.'],
                ['judul' => '3D Diamond Glitter',    'gambar_url' => '/storage/dummy/portfolio/3d-diamond-nails.jpg',      'deskripsi' => 'Nail art 3D dengan tekstur diamond dan glitter halus.'],
                ['judul' => 'Pastel Gradient',       'gambar_url' => '/storage/dummy/portfolio/pastel-gradient-set.jpg',   'deskripsi' => 'Gradient warna pastel dengan finishing glossy.'],
            ],
            [
                ['judul' => 'Geometric Minimalist',  'gambar_url' => '/storage/dummy/portfolio/geometric-minimalist.jpg',  'deskripsi' => 'Pola geometric tegas untuk tampilan clean dan modern.'],
                ['judul' => 'Monochrome Glossy',     'gambar_url' => '/storage/dummy/portfolio/monochrome-glossy.jpg',     'deskripsi' => 'Desain monochrome elegan dengan kombinasi matte dan gloss.'],
                ['judul' => 'Chrome Accent',         'gambar_url' => '/storage/dummy/portfolio/chrome-accent.jpg',         'deskripsi' => 'Look chrome accent dengan tone silver dan pink.'],
            ],
            [
                ['judul' => 'Bridal Soft Glam',      'gambar_url' => '/storage/dummy/portfolio/bridal-soft-glam.jpg',      'deskripsi' => 'Desain bridal nude glam dengan detail crystal accent.'],
                ['judul' => 'Modern French Luxe',    'gambar_url' => '/storage/dummy/portfolio/french-luxe.jpg',           'deskripsi' => 'Modern french tip dengan detail emas tipis.'],
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
