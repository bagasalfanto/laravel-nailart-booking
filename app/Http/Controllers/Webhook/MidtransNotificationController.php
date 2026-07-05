<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\StatusBooking;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransNotificationController extends Controller
{
    public function __construct(private readonly MidtransService $midtrans)
    {
    }

    /**
     * Webhook endpoint untuk Midtrans HTTP Notification.
     * Set di dashboard.sandbox.midtrans.com → Settings → Configuration → Payment Notification URL.
     *
     * Tetap return 200 di akhir untuk error apapun selain signature/payload invalid,
     * supaya Midtrans gak retry-forever untuk bug aplikasi kita.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::channel('midtrans')->info('Webhook received', [
            'order_id'           => $payload['order_id']           ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'fraud_status'       => $payload['fraud_status']       ?? null,
            'ip'                 => $request->ip(),
        ]);

        // 1) Verify signature_key sebagai fast-reject untuk request palsu.
        if (! $this->midtrans->verifyWebhookSignature($payload)) {
            Log::channel('midtrans')->warning('Webhook signature invalid', [
                'order_id' => $payload['order_id'] ?? null,
                'ip'       => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        if ($orderId === '') {
            return response()->json(['message' => 'Missing order_id'], 422);
        }

        // 2) Find Pembayaran.
        $pembayaran = Pembayaran::query()
            ->where('order_id', $orderId)
            ->with('reservasi')
            ->first();

        if (! $pembayaran) {
            Log::channel('midtrans')->warning('Webhook order_id tidak dikenal', ['order_id' => $orderId]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        try {
            // 3) Re-query Midtrans untuk anti-spoof. Kalau gagal (network error),
            //    tetap proses notifikasi dari payload — signature sudah diverifikasi.
            $statusFromRequery = null;
            try {
                $statusFromRequery = $this->midtrans->requeryStatus($orderId);
            } catch (\Throwable $e) {
                Log::channel('midtrans')->warning('Webhook re-query failed, falling back to payload', [
                    'order_id' => $orderId,
                    'error'    => $e->getMessage(),
                ]);
            }

            // 4) Apply update (idempotent).
            $applied = $this->midtrans->applyNotificationToPembayaran(
                $pembayaran,
                [
                    'transaction_status' => $payload['transaction_status'] ?? '',
                    'fraud_status'       => $payload['fraud_status']       ?? null,
                    'payment_type'       => $payload['payment_type']       ?? null,
                    'transaction_id'     => $payload['transaction_id']     ?? null,
                    'transaction_time'   => $payload['transaction_time']   ?? null,
                    'va_numbers'         => $payload['va_numbers']         ?? [],
                    'raw'                => $payload,
                ],
                $statusFromRequery
            );

            if ($applied) {
                $reservasiStatusName = $this->midtrans->reservasiStatusFor(
                    $pembayaran->fresh()->status_pembayaran
                );

                if ($reservasiStatusName && $pembayaran->reservasi) {
                    $newStatus = StatusBooking::where('nama_status', $reservasiStatusName)->first();
                    if ($newStatus) {
                        $pembayaran->reservasi->update(['status_id' => $newStatus->id]);
                    }
                }

                Log::channel('midtrans')->info('Webhook applied', [
                    'order_id'              => $orderId,
                    'new_status_pembayaran' => $pembayaran->fresh()->status_pembayaran,
                    'reservasi_status'      => $reservasiStatusName,
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('midtrans')->error('Webhook processing failed', [
                'order_id' => $orderId,
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            // Return non-200 so Midtrans retries this notification
            return response()->json(['message' => 'Internal error'], 500);
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
