<?php

namespace Database\Seeders;

use App\Models\Reservasi;
use App\Models\Review;
use App\Models\StatusBooking;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $successId = StatusBooking::query()
            ->whereRaw('LOWER(nama_status) = ?', ['sukses'])
            ->value('id');

        if (! $successId) {
            $this->command->warn('Status "Sukses" tidak ditemukan, ReviewSeeder dilewati.');
            return;
        }

        // Untuk dummy data: pakai semua reservasi yang ada agar data demo lebih kaya.
        // Di flow real, customer hanya bisa review saat status Sukses (lihat ReviewController).
        $reservasis = Reservasi::query()->limit(6)->get();

        if ($reservasis->isEmpty()) {
            $this->command->warn('Belum ada reservasi, ReviewSeeder dilewati.');
            return;
        }

        $samples = [
            [
                'rating' => 5,
                'content' => 'Hasilnya sangat memuaskan, kuku saya jadi cantik dan rapi. Nailist-nya ramah dan teliti, pasti akan booking lagi!',
                'is_featured' => true,
            ],
            [
                'rating' => 5,
                'content' => 'Tempatnya bersih dan nyaman, hasil nail art sesuai dengan referensi yang saya kasih. Recommended banget!',
                'is_featured' => true,
            ],
            [
                'rating' => 4,
                'content' => 'Pelayanan cepat dan ramah, hasilnya bagus. Hanya saja sedikit menunggu di awal karena rame.',
                'is_featured' => false,
            ],
            [
                'rating' => 5,
                'content' => 'Sudah langganan di sini. Kualitasnya konsisten, charm-nya banyak pilihan, dan tahan lama.',
                'is_featured' => true,
            ],
            [
                'rating' => 4,
                'content' => 'Suasananya cozy, harga sebanding dengan hasil. Akan kembali untuk treatment berikutnya.',
                'is_featured' => false,
            ],
            [
                'rating' => 5,
                'content' => 'Detail desainnya rapi banget! Nailist-nya juga sabar nanyain preferensi saya.',
                'is_featured' => false,
            ],
        ];

        foreach ($reservasis as $i => $reservasi) {
            $sample = $samples[$i % count($samples)];

            Review::updateOrCreate(
                ['reservasi_id' => $reservasi->id],
                [
                    'customer_id' => $reservasi->customer_id,
                    'rating'      => $sample['rating'],
                    'content'     => $sample['content'],
                    'is_featured' => $sample['is_featured'],
                    'featured_at' => $sample['is_featured'] ? now() : null,
                ]
            );
        }

        $this->command->info('Reviews seeded successfully.');
    }
}
