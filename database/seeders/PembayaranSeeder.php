<?php

namespace Database\Seeders;

use App\Models\Pembayaran;
use App\Models\Reservasi;
use Illuminate\Database\Seeder;

class PembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reservasis = Reservasi::query()
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->get();

        if ($reservasis->isEmpty()) {
            $this->command->error('No reservations found. Please run ReservasiSeeder first.');
            return;
        }

        foreach ($reservasis as $index => $reservasi) {
            $isPaid = ($index % 3) === 1;
            $isCancelled = ($index % 5) === 4;

            $orderId = 'ORDER-' . strtoupper(substr($reservasi->id, 0, 8));
            $nominal = (float) ($reservasi->total_harga_final ?? 0);

            if ($nominal <= 0) {
                $nominal = 150000 + ($index * 25000);
            }

            $batasWaktu = now()->addDays(1);

            $paymentData = [
                'order_id' => $orderId,
                'nominal' => $nominal,
                'status_pembayaran' => $isPaid ? 'settlement' : ($isCancelled ? 'expire' : 'pending'),
                'batas_waktu_bayar' => $isPaid ? now()->subDays(2) : $batasWaktu,
                'waktu_pembayaran' => $isPaid ? now()->subDay() : null,
                'payment_url' => "https://app.midtrans.com/snap/v1/payment/{$orderId}",
                'payment_token' => "snap_token_{$orderId}",
                'gateway_transaction_id' => $isPaid ? 'MID-' . strtoupper(substr($reservasi->id, 0, 12)) : null,
                'jenis_pembayaran' => $isPaid ? 'bank_transfer' : null,
                'bank' => $isPaid ? 'bca' : null,
                'raw_response' => [
                    'order_id' => $orderId,
                    'transaction_status' => $isPaid ? 'settlement' : ($isCancelled ? 'expire' : 'pending'),
                    'payment_type' => $isPaid ? 'bank_transfer' : null,
                ],
            ];

            Pembayaran::updateOrCreate(
                ['reservasi_id' => $reservasi->id],
                $paymentData
            );
        }

        $this->command->info('Pembayaran seeded successfully.');
    }
}
