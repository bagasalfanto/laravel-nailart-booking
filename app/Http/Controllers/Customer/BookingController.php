<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\BookingStep1Request;
use App\Http\Requests\Customer\BookingStep2Request;
use App\Http\Requests\Customer\BookingStep3Request;
use App\Models\DataCharm;
use App\Models\Nailist;
use App\Models\Pembayaran;
use App\Models\PenggunaanCharm;
use App\Models\Reservasi;
use App\Models\StatusBooking;
use App\Models\TreatmentCategory;
use App\Models\TreatmentKatalog;
use App\Services\MidtransService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    private const SESSION_KEY = 'booking_draft';

    private const TREATMENT_DURATION_MINUTES = 120;

    public function __construct(private readonly MidtransService $midtrans) {}

    public function step1(): View
    {
        $nailists = Nailist::query()
            ->with('user:id,full_name,avatar')
            ->active()
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'name' => $n->user?->full_name ?? '—',
                'avatar' => $n->user?->avatar_url,
            ])->values();

        $draft = session(self::SESSION_KEY.'.step1', []);

        return view('pages.landing.booking.step1', compact('nailists', 'draft'));
    }

    public function submitStep1(BookingStep1Request $request): RedirectResponse
    {
        $data = $request->validated();
        $tanggal = \Illuminate\Support\Carbon::parse($data['tanggal']);
        $jam = $data['jam'];
        $nailistId = $data['nailist_id'];

        // Slot overlap check
        $jamEnd = \Illuminate\Support\Carbon::parse($jam)->addMinutes(self::TREATMENT_DURATION_MINUTES)->format('H:i');
        $conflict = Reservasi::query()
            ->where('nailist_id', $nailistId)
            ->whereDate('tanggal', $tanggal)
            ->whereHas('status', fn ($q) => $q->whereIn('nama_status', ['Pending', 'Dikonfirmasi', 'Diproses', 'Sukses']))
            ->where(function ($q) use ($jam, $jamEnd) {
                $q->whereBetween('jam', [$jam.':00', $jamEnd.':00'])
                    ->orWhereRaw("jam < ? AND DATE_ADD(jam, INTERVAL 120 MINUTE) > ?", [$jam.':00', $jam.':00']);
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'jam' => 'Slot pada tanggal & jam tersebut sudah dibooking atau tumpang tindih. Pilih slot lain.',
            ])->withInput();
        }

        session([self::SESSION_KEY.'.step1' => $data]);

        return redirect()->route('booking.step2');
    }

    public function step2(): View|RedirectResponse
    {
        if (! session()->has(self::SESSION_KEY.'.step1')) {
            return redirect()->route('booking.step1')->with('error', 'Pilih jadwal terlebih dahulu.');
        }

        $categories = TreatmentCategory::query()
            ->with('activeTreatments')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $charms = DataCharm::query()->where('is_active', true)->orderBy('nama_charm')->get();
        $draft = session(self::SESSION_KEY.'.step2', []);
        $summary = $this->buildSummary();

        return view('pages.landing.booking.step2', compact('categories', 'charms', 'draft', 'summary'));
    }

    public function submitStep2(BookingStep2Request $request): RedirectResponse
    {
        if (! session()->has(self::SESSION_KEY.'.step1')) {
            return redirect()->route('booking.step1')->with('error', 'Pilih jadwal terlebih dahulu.');
        }

        $data = $request->validated();

        // Filter charm rows dengan qty=0 — itu artinya tidak diambil.
        $charms = collect($data['charms'] ?? [])
            ->filter(fn ($c) => (int) ($c['qty'] ?? 0) > 0)
            ->values()
            ->all();

        // Simpan upload referensi (kalau ada) sebagai path relatif di storage/app/public/booking-refs.
        $refPath = null;
        if ($request->hasFile('referensi_desain')) {
            try {
                $refPath = $request->file('referensi_desain')->store('booking-refs', 'public');
            } catch (\Throwable $e) {
                return back()->withErrors([
                    'referensi_desain' => 'Gagal mengupload gambar: '.$e->getMessage(),
                ])->withInput();
            }
        }

        session([
            self::SESSION_KEY.'.step2' => [
                'treatments' => $data['treatments'],
                'charms' => $charms,
                'referensi_desain' => $refPath ?: (session(self::SESSION_KEY.'.step2')['referensi_desain'] ?? null),
            ],
        ]);

        return redirect()->route('booking.step3');
    }

    public function step3(): View|RedirectResponse
    {
        if (! session()->has(self::SESSION_KEY.'.step1') || ! session()->has(self::SESSION_KEY.'.step2')) {
            return redirect()->route('booking.step1')->with('error', 'Lengkapi step sebelumnya dulu.');
        }

        $user = auth()->user();
        $fullName = (string) ($user?->full_name ?? '');
        $parts = preg_split('/\s+/', trim($fullName), 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        $summary = $this->buildSummary();
        $draft = session(self::SESSION_KEY.'.step3', []);

        return view('pages.landing.booking.step3', [
            'summary' => $summary,
            'draft' => $draft,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $user?->email,
            'phone' => $user?->phone_number,
            'dpAmount' => (int) config('services.midtrans.dp_amount'),
        ]);
    }

    /**
     * Final: validasi customer details, create Reservasi + Pembayaran,
     * hit Midtrans Snap, redirect ke payment waiting.
     */
    public function submitStep3(BookingStep3Request $request): RedirectResponse
    {
        if (! session()->has(self::SESSION_KEY.'.step1') || ! session()->has(self::SESSION_KEY.'.step2')) {
            return redirect()->route('booking.step1')->with('error', 'Lengkapi step sebelumnya dulu.');
        }

        $customerInput = $request->validated();
        session([self::SESSION_KEY.'.step3' => $customerInput]);

        $step1 = session(self::SESSION_KEY.'.step1');
        $step2 = session(self::SESSION_KEY.'.step2');
        $user = auth()->user();

        $customerProfile = $user?->customer;
        if (! $customerProfile) {
            return redirect()->route('booking.step1')->with('error', 'Profil customer tidak ditemukan.');
        }

        $statusPending = StatusBooking::where('nama_status', 'Pending')->first();
        if (! $statusPending) {
            return redirect()->route('booking.step1')->with('error', 'Master data status booking belum di-seed.');
        }

        $treatmentIds = $step2['treatments'];
        $treatments = TreatmentKatalog::whereIn('id', $treatmentIds)->get();
        if ($treatments->isEmpty()) {
            return redirect()->route('booking.step1')->with('error', 'Treatment yang dipilih tidak ditemukan.');
        }

        $primaryTreatment = $treatments->first();

        // Hitung total (semua treatment + charms) — disimpan ke Reservasi.total_harga_final.
        $treatmentSum = (float) $treatments->sum(fn ($t) => (float) $t->price_min);

        $charmSum = 0.0;
        $charmRows = [];
        foreach ($step2['charms'] ?? [] as $c) {
            $charm = DataCharm::find($c['id']);
            if (! $charm) {
                continue;
            }
            $subtotal = (float) $charm->harga * (int) $c['qty'];
            $charmSum += $subtotal;
            $charmRows[] = [
                'charm_id' => $charm->id,
                'jumlah_dipakai' => (int) $c['qty'],
                'subtotal' => $subtotal,
            ];
        }

        // Re-check nailist masih aktif di step3
        $nailist = \App\Models\Nailist::active()->find($step1['nailist_id']);
        if (! $nailist) {
            return redirect()->route('booking.step1')->with('error', 'Nailist yang dipilih sudah tidak tersedia. Silakan pilih nailist lain.');
        }

        // Validate charm stock
        foreach ($step2['charms'] ?? [] as $c) {
            $charm = DataCharm::find($c['id']);
            if ($charm && (int) $charm->stok < (int) ($c['qty'] ?? 0)) {
                return back()->withErrors([
                    'charms' => 'Stok charm "'.$charm->nama_charm.'" tidak mencukupi (tersedia: '.$charm->stok.').',
                ])->withInput();
            }
        }

        $totalHarga = $treatmentSum + $charmSum;
        $dpAmount = (int) config('services.midtrans.dp_amount');

        $tanggal = $step1['tanggal'];
        $jam = $step1['jam'].':00';
        $waktuMulai = Carbon::parse($tanggal.' '.$step1['jam']);
        $waktuSelesai = $waktuMulai->copy()->addMinutes($primaryTreatment->durasi_menit ?? self::TREATMENT_DURATION_MINUTES);

        try {
            // Step 1: Create reservation + charm usage inside DB transaction
            $reservasi = DB::transaction(function () use (
                $step1, $step2, $customerProfile, $statusPending,
                $primaryTreatment, $charmRows, $totalHarga,
                $tanggal, $jam, $waktuMulai, $waktuSelesai
            ) {
                $reservasi = Reservasi::create([
                    'customer_id'       => $customerProfile->id,
                    'nailist_id'        => $step1['nailist_id'],
                    'treatment_id'      => $primaryTreatment->id,
                    'status_id'         => $statusPending->id,
                    'tanggal'           => $tanggal,
                    'jam'               => $jam,
                    'waktu_mulai'       => $waktuMulai,
                    'waktu_selesai'     => $waktuSelesai,
                    'referensi_desain'  => $step2['referensi_desain'] ?? null,
                    'total_harga_final' => $totalHarga,
                    'booking_notified_at' => now(),
                ]);

                foreach ($charmRows as $row) {
                    PenggunaanCharm::create([
                        'reservasi_id'   => $reservasi->id,
                        'charm_id'       => $row['charm_id'],
                        'jumlah_dipakai' => $row['jumlah_dipakai'],
                        'subtotal'       => $row['subtotal'],
                    ]);
                    DataCharm::where('id', $row['charm_id'])->decrement('stok', $row['jumlah_dipakai']);
                }

                return $reservasi;
            });

            // Step 2: Call Midtrans API outside DB transaction
            $snap = $this->midtrans->createSnapTransaction($reservasi, $user, [
                'first_name'     => $customerInput['first_name'],
                'last_name'      => $customerInput['last_name'],
                'email'          => $customerInput['email'],
                'phone'          => preg_replace('/[^0-9+]/', '', $customerInput['phone']),
                'treatment_name' => $primaryTreatment->nama_jasa,
            ]);

            // Step 3: Create payment record
            $pembayaran = Pembayaran::create([
                'reservasi_id'       => $reservasi->id,
                'order_id'           => $snap['order_id'],
                'payment_url'        => $snap['redirect_url'],
                'payment_token'      => $snap['token'],
                'nominal'            => $dpAmount,
                'status_pembayaran'  => 'pending',
                'batas_waktu_bayar'  => $snap['expiry'],
            ]);

            $result = [$reservasi, $pembayaran];
        } catch (\Throwable $e) {
            Log::channel('midtrans')->error('Gagal create reservasi+pembayaran+snap', [
                'user_id' => $user?->id,
                'message' => $e->getMessage(),
            ]);

            // Cleanup: if reservation was created but Midtrans/Payment failed,
            // mark the reservation as cancelled
            if (isset($reservasi)) {
                $dibatalkan = StatusBooking::where('nama_status', 'Dibatalkan')->value('id');
                if ($dibatalkan) {
                    $reservasi->update(['status_id' => $dibatalkan]);
                }
            }

            return back()->withErrors([
                'snap' => 'Gagal memproses pembayaran: '.$e->getMessage(),
            ])->withInput();
        }

        [$reservasi, $pembayaran] = $result;

        // Clear draft setelah sukses create — agar refresh tidak duplicate.
        session()->forget([
            self::SESSION_KEY.'.step1',
            self::SESSION_KEY.'.step2',
            self::SESSION_KEY.'.step3',
            self::SESSION_KEY,
        ]);

        return redirect()
            ->route('booking.payment.waiting', ['order_id' => $pembayaran->order_id])
            ->with('open_payment', true);
    }

    public function paymentWaiting(Request $request): View|RedirectResponse
    {
        $pembayaran = $this->findOwnedPembayaran($request->query('order_id'));

        if (! $pembayaran) {
            return redirect()->route('customer.appointments.index')->with('error', 'Pembayaran tidak ditemukan.');
        }

        return view('pages.landing.booking.payment-waiting', [
            'pembayaran' => $pembayaran,
            'clientKey' => (string) config('services.midtrans.client_key'),
            'snapJsUrl' => (string) config('services.midtrans.snap_js_url'),
            'autoOpen' => (bool) session('open_payment', false),
        ]);
    }

    /**
     * Endpoint setelah user kembali dari Snap popup / Midtrans redirect.
     * Re-query status ke Midtrans agar update real-time (kalau-kalau webhook telat).
     */
    public function paymentFinish(Request $request): RedirectResponse
    {
        $pembayaran = $this->findOwnedPembayaran($request->query('order_id'));
        if (! $pembayaran) {
            return redirect()->route('customer.appointments.index')->with('error', 'Order tidak ditemukan.');
        }

        try {
            $status = $this->midtrans->requeryStatus($pembayaran->order_id);

            $applied = $this->midtrans->applyNotificationToPembayaran($pembayaran, [
                'transaction_status' => $status['transaction_status'] ?? 'pending',
                'fraud_status' => $status['fraud_status'] ?? null,
                'payment_type' => $status['payment_type'] ?? null,
                'transaction_id' => $status['transaction_id'] ?? null,
                'transaction_time' => $status['transaction_time'] ?? null,
                'va_numbers' => $status['va_numbers'] ?? [],
                'raw' => $status,
            ], $status);

            if ($applied) {
                $reservasiStatusName = $this->midtrans->reservasiStatusFor($pembayaran->fresh()->status_pembayaran);
                if ($reservasiStatusName) {
                    $newStatus = StatusBooking::where('nama_status', $reservasiStatusName)->first();
                    if ($newStatus && $pembayaran->reservasi) {
                        $pembayaran->reservasi->update(['status_id' => $newStatus->id]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::channel('midtrans')->warning('paymentFinish requery failed', [
                'order_id' => $pembayaran->order_id,
                'message' => $e->getMessage(),
            ]);
            // Fall through — pakai status DB existing.
        }

        $latest = $pembayaran->fresh()->status_pembayaran;

        return match ($latest) {
            'settlement' => redirect()->route('booking.payment.success', ['order_id' => $pembayaran->order_id]),
            'pending' => redirect()->route('booking.payment.waiting', ['order_id' => $pembayaran->order_id]),
            default => redirect()->route('booking.payment.failed', ['order_id' => $pembayaran->order_id]),
        };
    }

    public function paymentSuccess(Request $request): View|RedirectResponse
    {
        $pembayaran = $this->findOwnedPembayaran($request->query('order_id'));
        if (! $pembayaran) {
            return redirect()->route('customer.appointments.index')
                ->with('warning', 'Order tidak ditemukan atau bukan milik anda.');
        }

        return view('pages.landing.booking.payment-success', [
            'pembayaran' => $pembayaran,
        ]);
    }

    public function paymentFailed(Request $request): View|RedirectResponse
    {
        $pembayaran = $this->findOwnedPembayaran($request->query('order_id'));
        if (! $pembayaran) {
            return redirect()->route('customer.appointments.index')
                ->with('warning', 'Order tidak ditemukan atau bukan milik anda.');
        }

        return view('pages.landing.booking.payment-failed', [
            'pembayaran' => $pembayaran,
        ]);
    }

    private function findOwnedPembayaran(?string $orderId): ?Pembayaran
    {
        if (! $orderId) {
            return null;
        }

        $user = auth()->user();
        if (! $user?->customer) {
            return null;
        }

        return Pembayaran::query()
            ->where('order_id', $orderId)
            ->whereHas('reservasi', fn ($q) => $q->where('customer_id', $user->customer->id))
            ->with('reservasi.nailist.user', 'reservasi.treatment')
            ->first();
    }

    /**
     * Build summary array dari session step1 + step2 untuk dipakai step3 view.
     */
    private function buildSummary(): array
    {
        $step1 = session(self::SESSION_KEY.'.step1', []);
        $step2 = session(self::SESSION_KEY.'.step2', []);

        $nailist = ! empty($step1['nailist_id']) ? Nailist::with('user')->find($step1['nailist_id']) : null;
        $treatments = ! empty($step2['treatments']) ? TreatmentKatalog::whereIn('id', $step2['treatments'])->get() : collect();

        $charmLines = [];
        $charmSum = 0.0;
        foreach ($step2['charms'] ?? [] as $c) {
            $charm = DataCharm::find($c['id']);
            if (! $charm) {
                continue;
            }
            $subtotal = (float) $charm->harga * (int) $c['qty'];
            $charmSum += $subtotal;
            $charmLines[] = [
                'name' => $charm->nama_charm,
                'qty' => (int) $c['qty'],
                'price' => (float) $charm->harga,
                'subtotal' => $subtotal,
            ];
        }

        $treatmentSum = (float) $treatments->sum(fn ($t) => (float) $t->price_min);

        return [
            'tanggal' => $step1['tanggal'] ?? null,
            'jam' => $step1['jam'] ?? null,
            'nailist' => $nailist,
            'treatments' => $treatments,
            'charms' => $charmLines,
            'treatmentSum' => $treatmentSum,
            'charmSum' => $charmSum,
            'total' => $treatmentSum + $charmSum,
        ];
    }
}
