<?php

namespace Database\Seeders;

use App\Models\WebSetting;
use Illuminate\Database\Seeder;

class WebSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'site_title' => 'NailArt Booking Studio',
            'site_tagline' => 'Booking nail art favoritmu jadi lebih cepat dan rapi.',
            'logo_url' => '/images/logo-nailart.png',
            'hero_title' => 'Nail Art Premium dengan Nailist Profesional',
            'hero_subtitle' => 'Pilih jadwal, pilih nailist, dan bayar online lewat Midtrans.',
            'about_text' => 'Studio nail art modern dengan spesialis desain custom, bridal, dan korean style.',
            'contact_phone' => '+62 812-3456-7890',
            'contact_email' => 'hello@nailartbooking.id',
            'instagram_url' => 'https://instagram.com/nailartbooking.id',
            'booking_policy' => 'Harap hadir 10 menit sebelum jadwal booking. Pembatalan maksimal H-1.',
        ];

        foreach ($settings as $key => $value) {
            WebSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $this->command->info('WebSetting seeded successfully.');
    }
}
