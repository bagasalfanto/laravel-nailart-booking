<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'pertanyaan' => 'Bagaimana cara booking treatment?',
                'jawaban' => 'Pilih layanan, tentukan nailist, pilih tanggal dan jam, lalu selesaikan pembayaran.',
                'status' => 'active',
            ],
            [
                'pertanyaan' => 'Metode pembayaran apa yang tersedia?',
                'jawaban' => 'Pembayaran dilakukan melalui Midtrans seperti VA bank transfer, e-wallet, dan kartu kredit.',
                'status' => 'active',
            ],
            [
                'pertanyaan' => 'Apakah saya bisa reschedule jadwal?',
                'jawaban' => 'Bisa, reschedule dapat dilakukan maksimal H-1 dari waktu booking.',
                'status' => 'active',
            ],
            [
                'pertanyaan' => 'Apakah desain custom bisa request sendiri?',
                'jawaban' => 'Bisa, Anda dapat unggah referensi desain pada saat membuat reservasi.',
                'status' => 'active',
            ],
            [
                'pertanyaan' => 'Apakah ada refund jika batal?',
                'jawaban' => 'Refund mengikuti kebijakan studio dan status pembayaran di dashboard booking.',
                'status' => 'inactive',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['pertanyaan' => $faq['pertanyaan']],
                [
                    'jawaban' => $faq['jawaban'],
                    'status' => $faq['status'],
                ]
            );
        }

        $this->command->info('Faq seeded successfully.');
    }
}
