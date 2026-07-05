<?php

namespace Database\Seeders;

use App\Models\WebSetting;
use Illuminate\Database\Seeder;

class WebSettingSeeder extends Seeder
{
    public function run(): void
    {
        // [key => [value, group, label, type]]. sort_order di-generate otomatis per grup.
        $settings = [
            // --- Identitas & Brand ---
            'site_title' => ['nailby.hilda', 'identity', 'Judul Situs', 'text'],
            'site_tagline' => ['Where premium nail care meets intricate artistry.', 'identity', 'Tagline', 'text'],
            'logo_url' => ['/images/logo-nailart.png', 'identity', 'Logo (URL/Path)', 'image'],

            // --- Home · Hero ---
            'hero_title' => ['Nail Art Premium dengan Nailist Profesional', 'home_hero', 'Hero Title', 'text'],
            'hero_eyebrow' => ['Hello Beauty', 'home_hero', 'Hero Eyebrow', 'text'],
            'hero_caption' => ['Confidence in your fingers.', 'home_hero', 'Hero Caption', 'text'],
            'hero_quote' => ['Your Nails Are The Smallest Canvas, Yet They Hold The Loudest Statement.', 'home_hero', 'Hero Quote', 'textarea'],
            'hero_subtitle' => ['Welcome To A Space Where Premium Care Meets Intricate Artistry.', 'home_hero', 'Hero Subtitle', 'textarea'],
            'hero_cta_label' => ['Book Appointment', 'home_hero', 'Hero Tombol (CTA)', 'text'],

            // --- Home · Sections ---
            'styles_title' => ['Styles that speak for you', 'home_sections', 'Styles — Judul', 'text'],
            'styles_subtitle' => ['Setiap kuku adalah kanvas. Lihat karya terbaru kami.', 'home_sections', 'Styles — Subjudul', 'textarea'],
            'services_title' => ['Our Nail Services', 'home_sections', 'Services — Judul', 'text'],
            'services_subtitle' => ['Discover your nail style — kami punya ragam treatment dari basic sampai bridal premium.', 'home_sections', 'Services — Subjudul', 'textarea'],
            'ready_title' => ['Ready for a Fresh Set?', 'home_sections', 'Ready — Judul', 'text'],
            'ready_subtitle' => ['Pilih nailist, pilih jadwal, dan dapatkan kuku impianmu.', 'home_sections', 'Ready — Subjudul', 'textarea'],
            'ready_cta_text' => ['Real-time Availability', 'home_sections', 'Ready — Teks CTA', 'text'],
            'faq_title' => ['Frequently Asked Questions', 'home_sections', 'FAQ — Judul', 'text'],
            'faq_subtitle' => ['Pertanyaan yang sering ditanyakan client kami.', 'home_sections', 'FAQ — Subjudul', 'textarea'],
            'banner_title' => ['Ready For Prettier Nails?', 'home_sections', 'Banner — Judul', 'text'],
            'banner_subtitle' => ['Let our most skilled artists transform your nails. Book your slot today.', 'home_sections', 'Banner — Subjudul', 'textarea'],
            'testimonial_title' => ['What Our Clients Say', 'home_sections', 'Testimonial — Judul', 'text'],
            'testimonial_subtitle' => ['Cerita dari customer yang sudah mencoba layanan kami.', 'home_sections', 'Testimonial — Subjudul', 'textarea'],
            'about_text' => ['Studio nail art modern dengan spesialis desain custom.', 'home_sections', 'About — Teks', 'textarea'],

            // --- Halaman Nail Artist ---
            'nailist_hero_eyebrow' => ['Meet The Artists', 'nailist', 'Hero Eyebrow', 'text'],
            'nailist_hero_title' => ['Crafting Beauty, One Nail at a Time', 'nailist', 'Hero Judul', 'text'],
            'nailist_hero_subtitle' => ['Setiap nailist punya keahlian unik. Pilih artist yang paling cocok dengan style kamu.', 'nailist', 'Hero Subjudul', 'textarea'],

            // --- Lokasi, Kontak & Footer ---
            'location_title' => ["We're Located At", 'footer', 'Lokasi — Judul', 'text'],
            'location_address' => ['GRIYA MULAWARMAN INDAH, BLOK G12-A', 'footer', 'Alamat', 'text'],
            'contact_phone' => ['+62 812-3456-7890', 'footer', 'Nomor Telepon', 'text'],
            'contact_email' => ['hello@nailbyhilda.com', 'footer', 'Email', 'text'],
            'location_hours' => ['EVERYDAY 09.00 - 20.00', 'footer', 'Jam Operasional', 'text'],
            'location_map_embed' => ['https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15825.434311100332!2d109.2294155128479!3d-7.425501867160751!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e655e638b9d0315%3A0xcb1b11ec917db4e4!2sGriya%20Mulawarman%20Indah!5e0!3m2!1sen!2sid!4v1718285512822!5m2!1sen!2sid', 'footer', 'Google Maps Embed URL', 'textarea'],
            'contact_whatsapp' => ['0822-2395-8248', 'footer', 'Nomor WhatsApp', 'text'],
            'whatsapp_url' => ['https://wa.me/6282223958248', 'footer', 'Link WhatsApp', 'url'],
            'instagram_handle' => ['@nailby.hilda', 'footer', 'Handle Instagram', 'text'],
            'instagram_url' => ['https://instagram.com/nailby.hilda', 'footer', 'Link Instagram', 'url'],

            // --- Kebijakan ---
            'booking_policy' => ['Harap hadir 10 menit sebelum jadwal booking. Pembatalan maksimal H-1.', 'policy', 'Kebijakan Booking', 'textarea'],
        ];

        $orderPerGroup = [];

        foreach ($settings as $key => [$value, $group, $label, $type]) {
            $orderPerGroup[$group] = ($orderPerGroup[$group] ?? 0) + 1;

            WebSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $group,
                    'label' => $label,
                    'type' => $type,
                    'sort_order' => $orderPerGroup[$group],
                ]
            );
        }

        $this->command->info('WebSetting seeded successfully ('.count($settings).' keys).');
    }
}
