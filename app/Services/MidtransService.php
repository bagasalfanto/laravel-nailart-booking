<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = (string) config('services.midtrans.server_key');
        Config::$clientKey = (string) config('services.midtrans.client_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized');
        Config::$is3ds = (bool) config('services.midtrans.is_3ds');
    }

    /**
     * Build Snap payload, hit Midtrans, return ['token' => ..., 'redirect_url' => ...].
     *
     * @param  array{first_name:string,last_name:string,email:string,phone:string,treatment_name:string}  $customer
     */
    public function createSnapTransaction(Reservasi $reservasi, User $user, array $customer): array
    {
        $orderId = $this->generateOrderId($reservasi);
        $dpAmount = (int) config('services.midtrans.dp_amount');

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $dpAmount,
            ],
            'item_details' => [[
                'id' => 'DP-BOOKING',
                'price' => $dpAmount,
                'quantity' => 1,
                'name' => Str::limit('DP - '.$customer['treatment_name'], 50, ''),
                'category' => 'Beauty Service',
            ]],
            'customer_details' => [
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'hours',
                'duration' => 24,
            ],
            'callbacks' => [
                'finish' => route('booking.payment.finish', ['order_id' => $orderId]),
            ],
        ];

        try {
            $response = Snap::createTransaction($payload);
        } catch (\Throwable $e) {
            Log::channel('midtrans')->error('Snap createTransaction failed', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
            ]);
            throw new RuntimeException('Gagal menghubungi gateway pembayaran. Coba lagi nanti.', 0, $e);
        }

        $token = isset($response->token) ? (string) $response->token : '';
        $redirectUrl = isset($response->redirect_url) ? (string) $response->redirect_url : '';

        if ($token === '') {
            Log::channel('midtrans')->error('Snap token kosong', ['order_id' => $orderId, 'response' => $response]);
            throw new RuntimeException('Gateway tidak mengembalikan token Snap.');
        }

        Log::channel('midtrans')->info('Snap token generated', [
            'order_id' => $orderId,
            'reservasi_id' => $reservasi->id,
        ]);

        return [
            'order_id' => $orderId,
            'token' => $token,
            'redirect_url' => $redirectUrl,
            'expiry' => now()->addHours(24),
        ];
    }

    /**
     * Verifikasi signature Midtrans dari webhook payload.
     * Formula: SHA512(order_id + status_code + gross_amount + ServerKey).
     */
    public function verifyWebhookSignature(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signature === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.(string) config('services.midtrans.server_key'));

        return hash_equals($expected, $signature);
    }

    /**
     * Re-query status transaksi langsung ke Midtrans (anti-spoof).
     * Return array status object dari Midtrans.
     */
    public function requeryStatus(string $orderId): array
    {
        try {
            $result = Transaction::status($orderId);

            return is_array($result) ? $result : json_decode(json_encode($result), true);
        } catch (\Throwable $e) {
            Log::channel('midtrans')->warning('Transaction::status failed', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
            ]);
            throw new RuntimeException('Gagal cek status ke Midtrans: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Parse incoming webhook via Notification helper (auto-fetch + re-query internal).
     */
    public function parseNotification(): array
    {
        $notif = new Notification;

        return [
            'order_id' => (string) $notif->order_id,
            'transaction_id' => (string) ($notif->transaction_id ?? ''),
            'transaction_status' => (string) $notif->transaction_status,
            'fraud_status' => (string) ($notif->fraud_status ?? ''),
            'payment_type' => (string) ($notif->payment_type ?? ''),
            'status_code' => (string) $notif->status_code,
            'gross_amount' => (string) $notif->gross_amount,
            'signature_key' => (string) $notif->signature_key,
            'transaction_time' => (string) ($notif->transaction_time ?? ''),
            'va_numbers' => isset($notif->va_numbers) ? json_decode(json_encode($notif->va_numbers), true) : [],
            'raw' => json_decode(json_encode($notif), true),
        ];
    }

    /**
     * Translate Midtrans transaction_status (+ fraud_status untuk credit_card)
     * ke enum status_pembayaran internal: pending|settlement|expire|deny|cancel|failure.
     */
    public function mapStatus(string $transactionStatus, ?string $fraudStatus = null): string
    {
        $transactionStatus = strtolower($transactionStatus);
        $fraudStatus = $fraudStatus ? strtolower($fraudStatus) : null;

        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'settlement' : 'pending',
            'settlement' => 'settlement',
            'pending' => 'pending',
            'deny' => 'deny',
            'cancel' => 'cancel',
            'expire' => 'expire',
            'failure' => 'failure',
            'refund', 'partial_refund', 'chargeback', 'partial_chargeback' => 'refund',
            default => 'pending',
        };
    }

    /**
     * Mapping status_pembayaran → nama_status di status_bookings table.
     * Return null kalau status pembayaran tidak meng-trigger update Reservasi.
     */
    public function reservasiStatusFor(string $statusPembayaran): ?string
    {
        return match ($statusPembayaran) {
            'settlement' => 'Dikonfirmasi',
            'expire', 'cancel', 'deny', 'failure' => 'Dibatalkan',
            default => null,
        };
    }

    /**
     * Generate order_id unik & deterministis utk Midtrans.
     * Format: NB-{first8charsResvId}-{microtime}-{rand4}, max 50 char.
     */
    public function generateOrderId(Reservasi $reservasi): string
    {
        $short = substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $reservasi->id), 0, 8);
        $ts = (string) (int) (microtime(true) * 1000);
        $rand = strtoupper(Str::random(4));

        $orderId = 'NB-'.$short.'-'.$ts.'-'.$rand;

        return substr($orderId, 0, 50);
    }

    /**
     * Apply hasil notifikasi ke row Pembayaran. Idempotent — tidak akan downgrade settlement.
     * Caller bertanggung jawab handle Reservasi.status_id update (lihat reservasiStatusFor()).
     */
    public function applyNotificationToPembayaran(Pembayaran $pembayaran, array $notif, array $statusFromRequery): bool
    {
        $finalStatusSource = ! empty($statusFromRequery['transaction_status'])
            ? $statusFromRequery['transaction_status']
            : $notif['transaction_status'];

        $fraud = $statusFromRequery['fraud_status'] ?? ($notif['fraud_status'] ?? null);

        $mappedStatus = $this->mapStatus((string) $finalStatusSource, $fraud);

        // Idempotency: jangan downgrade final status.
        if ($pembayaran->status_pembayaran === 'settlement' && $mappedStatus !== 'settlement') {
            Log::channel('midtrans')->info('Idempotency skip: payment already settled', [
                'order_id' => $pembayaran->order_id,
                'incoming' => $mappedStatus,
            ]);

            return false;
        }

        $paymentType = $statusFromRequery['payment_type'] ?? $notif['payment_type'] ?? null;
        $vaNumbers = $statusFromRequery['va_numbers'] ?? $notif['va_numbers'] ?? [];
        $bank = is_array($vaNumbers) && isset($vaNumbers[0]['bank']) ? (string) $vaNumbers[0]['bank'] : null;

        $updates = [
            'status_pembayaran' => $mappedStatus,
            'gateway_transaction_id' => $statusFromRequery['transaction_id'] ?? $notif['transaction_id'] ?? $pembayaran->gateway_transaction_id,
            'jenis_pembayaran' => $paymentType ?: $pembayaran->jenis_pembayaran,
            'bank' => $bank ?: $pembayaran->bank,
            'raw_response' => $statusFromRequery ?: $notif['raw'] ?? $notif,
        ];

        if ($mappedStatus === 'settlement') {
            $txTime = $statusFromRequery['settlement_time']
                ?? $statusFromRequery['transaction_time']
                ?? $notif['transaction_time']
                ?? null;
            $updates['waktu_pembayaran'] = $txTime ? \Illuminate\Support\Carbon::parse($txTime) : now();
        }

        $pembayaran->fill($updates)->save();

        return true;
    }
}
