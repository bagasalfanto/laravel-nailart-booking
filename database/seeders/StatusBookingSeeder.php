<?php

namespace Database\Seeders;

use App\Models\StatusBooking;
use Illuminate\Database\Seeder;

class StatusBookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Pending',
            'Dikonfirmasi',
            'Diproses',
            'Sukses',
            'Dibatalkan',
        ];

        foreach ($statuses as $status) {
            StatusBooking::firstOrCreate(['nama_status' => $status]);
        }

        $this->command->info('StatusBooking seeded successfully.');
    }
}
