<?php

namespace App\Console\Commands;

use App\Models\Pembayaran;
use App\Models\StatusBooking;
use Illuminate\Console\Command;

class ExpirePayments extends Command
{
    protected $signature = 'payments:expire';

    protected $description = 'Mark pending payments as expired and cancel the related booking.';

    public function handle(): int
    {
        $payments = Pembayaran::query()
            ->with('reservasi')
            ->where('status_pembayaran', 'pending')
            ->whereNotNull('batas_waktu_bayar')
            ->where('batas_waktu_bayar', '<', now())
            ->get();

        if ($payments->isEmpty()) {
            $this->info('No expired payments found.');

            return self::SUCCESS;
        }

        $dibatalkanId = StatusBooking::where('nama_status', 'Dibatalkan')->value('id');
        $count = 0;

        foreach ($payments as $payment) {
            $payment->update(['status_pembayaran' => 'expire']);

            // Cancel the reservation if still pending
            if ($payment->reservasi && $dibatalkanId) {
                $pendingStatus = StatusBooking::where('nama_status', 'Pending')->value('id');
                if ($payment->reservasi->status_id === $pendingStatus) {
                    $payment->reservasi->update(['status_id' => $dibatalkanId]);
                }
            }

            $count++;
        }

        $this->info("{$count} payment(s) expired, reservation(s) cancelled.");

        return self::SUCCESS;
    }
}
