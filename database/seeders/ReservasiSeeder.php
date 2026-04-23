<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Nailist;
use App\Models\Reservasi;
use App\Models\StatusBooking;
use App\Models\TreatmentKatalog;
use Illuminate\Database\Seeder;

class ReservasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $nailists = Nailist::all();
        $treatments = TreatmentKatalog::all();
        $statuses = StatusBooking::all();

        if ($customers->isEmpty() || $nailists->isEmpty() || $treatments->isEmpty() || $statuses->isEmpty()) {
            $this->command->error('Missing required data. Please run UserSeeder, TreatmentKatalogSeeder, and StatusBookingSeeder first.');
            return;
        }

        $statusMap = $statuses->keyBy(fn (StatusBooking $status) => strtolower($status->nama_status));
        $defaultStatusId = $statuses->first()->id;
        $resolveStatusId = fn (string $status) => $statusMap->get(strtolower($status))?->id ?? $defaultStatusId;

        $bookingPlans = [
            [
                'customer_index' => 0,
                'nailist_index' => 0,
                'treatment_index' => 0,
                'status' => 'Pending',
                'start_at' => now()->addDays(2)->setTime(10, 0),
                'duration_minutes' => 120,
                'referensi_desain' => 'https://via.placeholder.com/600x400.png?text=Classic+French+Inspo',
                'total_harga_final' => 180000,
                'booking_notified_at' => null,
            ],
            [
                'customer_index' => 1,
                'nailist_index' => 1,
                'treatment_index' => 1,
                'status' => 'Sukses',
                'start_at' => now()->subDays(1)->setTime(11, 30),
                'duration_minutes' => 90,
                'referensi_desain' => 'https://via.placeholder.com/600x400.png?text=Luxury+Pedicure+Inspo',
                'total_harga_final' => 240000,
                'booking_notified_at' => now()->subDays(1)->setTime(9, 45),
            ],
            [
                'customer_index' => 2,
                'nailist_index' => 0,
                'treatment_index' => 2,
                'status' => 'Dikonfirmasi',
                'start_at' => now()->addDays(4)->setTime(14, 0),
                'duration_minutes' => 120,
                'referensi_desain' => 'https://via.placeholder.com/600x400.png?text=3D+Glam+Inspo',
                'total_harga_final' => 345000,
                'booking_notified_at' => now()->subHours(6),
            ],
            [
                'customer_index' => 3,
                'nailist_index' => 2,
                'treatment_index' => 3,
                'status' => 'Pending',
                'start_at' => now()->addDays(6)->setTime(9, 0),
                'duration_minutes' => 150,
                'referensi_desain' => 'https://via.placeholder.com/600x400.png?text=Acrylic+Extension+Inspo',
                'total_harga_final' => 295000,
                'booking_notified_at' => null,
            ],
            [
                'customer_index' => 4,
                'nailist_index' => 1,
                'treatment_index' => 4,
                'status' => 'Dibatalkan',
                'start_at' => now()->addDays(1)->setTime(16, 0),
                'duration_minutes' => 90,
                'referensi_desain' => 'https://via.placeholder.com/600x400.png?text=Floral+Pastel+Inspo',
                'total_harga_final' => 210000,
                'booking_notified_at' => null,
            ],
            [
                'customer_index' => 0,
                'nailist_index' => 2,
                'treatment_index' => 5,
                'status' => 'Diproses',
                'start_at' => now()->addDays(8)->setTime(13, 30),
                'duration_minutes' => 180,
                'referensi_desain' => 'https://via.placeholder.com/600x400.png?text=Bridal+Nail+Inspo',
                'total_harga_final' => 390000,
                'booking_notified_at' => now()->subHours(2),
            ],
        ];

        foreach ($bookingPlans as $plan) {
            if (
                $customers->get($plan['customer_index']) === null
                || $nailists->get($plan['nailist_index']) === null
                || $treatments->get($plan['treatment_index']) === null
            ) {
                continue;
            }

            $startAt = $plan['start_at']->copy();
            $endAt = $startAt->copy()->addMinutes($plan['duration_minutes']);
            $tanggal = $startAt->toDateString();
            $jam = $startAt->format('H:i:s');

            Reservasi::updateOrCreate(
                [
                    'nailist_id' => $nailists->get($plan['nailist_index'])->id,
                    'tanggal' => $tanggal,
                    'jam' => $jam,
                ],
                [
                    'customer_id' => $customers->get($plan['customer_index'])->id,
                    'treatment_id' => $treatments->get($plan['treatment_index'])->id,
                    'status_id' => $resolveStatusId($plan['status']),
                    'waktu_mulai' => $startAt,
                    'waktu_selesai' => $endAt,
                    'referensi_desain' => $plan['referensi_desain'],
                    'total_harga_final' => $plan['total_harga_final'],
                    'booking_notified_at' => $plan['booking_notified_at'],
                ]
            );
        }

        $this->command->info('Reservasi seeded successfully.');
    }
}
