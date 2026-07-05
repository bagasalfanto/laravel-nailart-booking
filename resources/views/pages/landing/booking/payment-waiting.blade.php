<x-layouts.landing title="Menunggu Pembayaran">
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $reservasi = $pembayaran->reservasi;
        $tanggalLabel = $reservasi?->tanggal ? \Carbon\Carbon::parse($reservasi->tanggal)->translatedFormat('d F Y') : '—';
        $jamLabel     = $reservasi?->jam ? \Carbon\Carbon::parse($reservasi->jam)->format('H.i') : '—';
        $deadline     = $pembayaran->batas_waktu_bayar ? $pembayaran->batas_waktu_bayar->toIso8601String() : null;
    @endphp

    <style>
        body { background-color: #FFF4F2 !important; }
    </style>

    <main class=" min-h-screen pb-20">
        <div class="max-w-3xl mx-auto px-4 pt-10">
            <div class="bg-white rounded-2xl p-8 md:p-12 shadow-sm relative"
                 x-data="paymentWaitingApp({
                     snapToken: @js($pembayaran->payment_token),
                     orderId:   @js($pembayaran->order_id),
                     status:    @js($pembayaran->status_pembayaran),
                     finishUrl: @js(route('booking.payment.finish', ['order_id' => $pembayaran->order_id])),
                     waitingUrl:@js(route('booking.payment.waiting', ['order_id' => $pembayaran->order_id])),
                     failedUrl: @js(route('booking.payment.failed', ['order_id' => $pembayaran->order_id])),
                     deadline:  @js($deadline),
                     autoOpen:  @js($autoOpen),
                 })"
                 x-init="init()">

                <a href="{{ route('customer.appointments.index') }}" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>

                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-[#fdf2f5] text-[#a23f66] mb-3">
                        <svg class="w-7 h-7 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl md:text-4xl text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                        Menunggu Pembayaran
                    </h1>
                    <p class="text-slate-500 mt-3 max-w-2xl mx-auto text-sm">
                        Selesaikan pembayaran <span class="text-[#a23f66] font-semibold">{{ $rupiah($pembayaran->nominal) }}</span>
                        (Down Payment) melalui halaman Midtrans untuk mengamankan booking kamu.
                    </p>
                </div>

                @if ($deadline)
                    <div class="flex items-center justify-between border-y border-[#f1e4e8] py-4 mb-6">
                        <p class="text-sm font-semibold text-slate-700">Batas Waktu Pembayaran:</p>
                        <p class="text-[#a23f66] font-bold text-lg tabular-nums">
                            <span x-text="hh"></span> hrs
                            <span x-text="mm"></span> mnt
                            <span x-text="ss"></span> scs
                        </p>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Order ID</p>
                            <p class="font-mono text-slate-700 font-semibold">{{ $pembayaran->order_id }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Nominal DP</p>
                            <p class="text-[#a23f66] font-bold text-lg">{{ $rupiah($pembayaran->nominal) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Status</p>
                            <p class="font-medium text-slate-800 capitalize" x-text="status"></p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Jadwal</p>
                            <p class="text-slate-800 font-medium">{{ $tanggalLabel }} · {{ $jamLabel }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Nailist</p>
                            <p class="text-slate-800 font-medium">{{ $reservasi?->nailist?->user?->full_name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Treatment</p>
                            <p class="text-slate-800 font-medium">{{ $reservasi?->treatment?->nama_jasa ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-[#f1e4e8] flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-slate-400">
                        Halaman ini akan refresh otomatis ketika pembayaran terdeteksi.
                    </p>
                    <button type="button" @click="openSnap()"
                            class="rounded-lg bg-[#e8a3b3] px-6 py-3 text-sm font-semibold text-white hover:bg-[#df8da0] transition">
                        Buka Halaman Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </main>

    @push('scripts')
        <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
        <script>
            function paymentWaitingApp(cfg) {
                return {
                    snapToken: cfg.snapToken,
                    orderId:   cfg.orderId,
                    status:    cfg.status,
                    finishUrl: cfg.finishUrl,
                    waitingUrl:cfg.waitingUrl,
                    failedUrl: cfg.failedUrl,
                    deadline:  cfg.deadline ? new Date(cfg.deadline) : null,
                    autoOpen:  cfg.autoOpen,
                    hh: '--', mm: '--', ss: '--',
                    pollTimer: null,

                    init() {
                        if (this.status === 'settlement') {
                            location.href = '{{ route('booking.payment.success') }}?order_id=' + encodeURIComponent(this.orderId);
                            return;
                        }
                        if (['pending'].includes(this.status)) {
                            // Auto-open Snap hanya saat first arrival (langsung setelah submit booking).
                            // Kunjungan/refresh berikutnya: user klik tombol "Buka Halaman Pembayaran".
                            if (this.autoOpen) setTimeout(() => this.openSnap(), 400); // delay untuk Snap.js selesai load
                            this.startPolling();
                        }
                        if (this.deadline) this.startCountdown();
                    },

                    openSnap() {
                        if (!window.snap || !this.snapToken) {
                            console.warn('Snap.js belum siap atau token kosong.');
                            return;
                        }
                        window.snap.pay(this.snapToken, {
                            onSuccess: (result) => { location.href = this.finishUrl; },
                            onPending: (result) => { location.href = this.waitingUrl; },
                            onError:   (result) => { console.error('Snap onError', result); location.href = this.failedUrl; },
                            onClose:   () => { /* stay — user mungkin masih mau bayar lagi */ },
                        });
                    },

                    startPolling() {
                        // Setiap 8 detik cek status server (webhook bisa update sebelum user klik finish).
                        // paymentFinish SELALU redirect: pending -> waiting, settlement -> success, lainnya -> failed.
                        // Jadi kita ikuti redirect-nya dan hanya pindah halaman bila landing-nya success/failed.
                        // (Kalau masih pending, landing-nya kembali ke waiting => jangan navigasi, supaya tidak reload terus.)
                        this.pollTimer = setInterval(async () => {
                            try {
                                const res = await fetch(this.finishUrl, {
                                    headers: { 'Accept': 'text/html' },
                                });
                                const landedUrl = res.url || '';
                                if (landedUrl.includes('/payment/success') || landedUrl.includes('/payment/failed')) {
                                    clearInterval(this.pollTimer);
                                    location.href = landedUrl;
                                }
                            } catch (e) {
                                // Polling silent fail; user bisa refresh manual.
                            }
                        }, 8000);
                    },

                    startCountdown() {
                        const tick = () => {
                            if (!this.deadline) return;
                            const diff = Math.max(0, this.deadline.getTime() - Date.now());
                            const totalSec = Math.floor(diff / 1000);
                            this.hh = String(Math.floor(totalSec / 3600)).padStart(2, '0');
                            this.mm = String(Math.floor((totalSec % 3600) / 60)).padStart(2, '0');
                            this.ss = String(totalSec % 60).padStart(2, '0');
                            if (diff <= 0) {
                                clearInterval(this.pollTimer);
                                location.href = this.failedUrl;
                            }
                        };
                        tick();
                        setInterval(tick, 1000);
                    },
                }
            }
        </script>
    @endpush
</x-layouts.landing>
