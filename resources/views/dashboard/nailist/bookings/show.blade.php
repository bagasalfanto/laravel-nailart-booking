<x-app-layout>
    <x-slot name="header">Booking Details</x-slot>

    @php
        $u = $booking->customer?->user;
        $treatment = $booking->treatment;
        $statusName = $booking->status?->nama_status ?? 'Unknown';
        $totalEst = (float) ($booking->total_harga_final ?: ($treatment?->price_min ?? 0));
        $dpAmount = (float) ($booking->pembayaran?->nominal ?? 0);
        $remaining = max(0, $totalEst - $dpAmount);
        $isPaid = $booking->pembayaran && $booking->pembayaran->status_pembayaran === 'settlement';
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $startTime = $booking->waktu_mulai ?? ($booking->tanggal && $booking->jam ? \Illuminate\Support\Carbon::parse($booking->tanggal->format('Y-m-d').' '.$booking->jam) : null);
        $endTime = $booking->waktu_selesai;

        // Estimate breakdown (schema only has 1 treatment)
        $estimateLines = [
            ['label' => 'Base: '.($treatment?->nama_jasa ?? 'Treatment'), 'amount' => $treatment?->price_min ?? 0],
        ];
    @endphp

    <div class="max-w-7xl mx-auto">
        {{-- Back link --}}
        <a href="{{ route('dashboard.nailist-bookings.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-[#a23f66] mb-4">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Bookings
        </a>

        {{-- Header --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-semibold text-slate-900">{{ $u?->full_name ?? '—' }}</h1>
                <p class="text-slate-500 mt-1 inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="16" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M16 3v4M8 3v4"/></svg>
                    {{ $booking->tanggal?->isoFormat('D MMM Y') }}
                    @if ($startTime)
                        · {{ $startTime->format('H:i') }}
                        @if ($endTime) – {{ $endTime->format('H:i') }} @endif
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @php $statusLower = strtolower($statusName); @endphp
                @if ($statusLower === 'dikonfirmasi')
                    <button type="button" data-action="start"
                            class="inline-flex items-center gap-1.5 rounded-full bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        Start Session
                    </button>
                @elseif ($statusLower === 'diproses')
                    <button type="button" data-action="finish"
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:opacity-90 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Selesai Session
                    </button>
                @elseif ($statusLower === 'sukses')
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-5 py-2 text-sm font-medium text-emerald-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Sesi Selesai
                    </span>
                @elseif ($statusLower === 'dibatalkan')
                    <span class="inline-flex items-center rounded-full bg-rose-50 px-5 py-2 text-sm font-medium text-rose-600">Dibatalkan</span>
                @else
                    <button type="button" disabled
                            class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-5 py-2 text-sm font-medium text-slate-500 cursor-not-allowed"
                            title="DP belum lunas">
                        Menunggu Pembayaran DP
                    </button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT: Info kolom kiri --}}
            <div class="lg:col-span-1 space-y-5">
                {{-- Treatment info --}}
                <article class="rounded-2xl bg-[#fdf2f5] p-5">
                    <p class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                        Treatment Info
                    </p>
                    <p class="text-xs uppercase text-slate-400 tracking-wide">Service</p>
                    <p class="text-slate-800 font-medium mt-0.5">{{ $treatment?->nama_jasa ?? '—' }}</p>

                    <div class="mt-4">
                        <p class="text-xs uppercase text-slate-400 tracking-wide">Client Notes</p>
                        <p class="text-slate-700 text-sm mt-1 italic leading-relaxed">
                            {{ $booking->referensi_desain ? '"'.\Illuminate\Support\Str::limit($booking->referensi_desain, 200).'"' : 'Belum ada catatan dari customer.' }}
                        </p>
                    </div>

                    @if ($u?->phone_number)
                        <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5">
                            <svg class="w-4 h-4 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.28a2 2 0 011.94 1.515l.69 2.76a2 2 0 01-1.135 2.36l-1.32.66a11 11 0 005.516 5.516l.66-1.32a2 2 0 012.36-1.135l2.76.69A2 2 0 0121 16.72V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="text-sm text-slate-700">{{ $u->phone_number }}</span>
                        </div>
                    @endif
                </article>

                {{-- Estimate --}}
                <article class="rounded-2xl bg-white border border-[#eedde2] p-5">
                    <p class="font-semibold text-slate-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6M9 11h6M9 15h4M5 5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/></svg>
                        Estimate
                    </p>
                    @foreach ($estimateLines as $line)
                        <div class="flex items-center justify-between py-1.5 text-sm">
                            <span class="text-slate-600">{{ $line['label'] }}</span>
                            <span class="text-slate-800 font-medium">{{ $rupiah($line['amount']) }}</span>
                        </div>
                    @endforeach

                    <div class="border-t border-[#f1e4e8] mt-3 pt-3 flex items-center justify-between">
                        <span class="font-semibold text-slate-800">Total Est.</span>
                        <span class="font-bold text-[#a23f66] text-lg">{{ $rupiah($totalEst) }}</span>
                    </div>

                    {{-- DP info --}}
                    <div class="mt-4 rounded-xl bg-[#fdf2f5] p-3 text-sm">
                        @if ($dpAmount > 0)
                            <p class="text-[#a23f66] font-medium">
                                DP {{ $rupiah($dpAmount) }}
                                <span class="ml-1 text-xs {{ $isPaid ? 'text-emerald-600' : 'text-amber-600' }}">
                                    ({{ $isPaid ? 'Paid' : 'Pending' }})
                                </span>
                            </p>
                            <p class="text-slate-700 mt-0.5">Remaining: <strong>{{ $rupiah($remaining) }}</strong> <span class="text-xs text-slate-500">(Pay after service)</span></p>
                        @else
                            <p class="text-amber-600 font-medium">Belum ada pembayaran DP</p>
                            <p class="text-slate-500 mt-0.5 text-xs">Total tagihan: <strong>{{ $rupiah($totalEst) }}</strong></p>
                        @endif
                    </div>

                    {{-- Pelunasan info --}}
                    @php $pembayaran = $booking->pembayaran; @endphp
                    @if ($pembayaran && $pembayaran->is_lunas)
                        <div class="mt-3 rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-sm">
                            <p class="text-emerald-700 font-medium flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                Lunas
                            </p>
                            <p class="text-emerald-600 text-xs mt-1">
                                Pelunasan {{ $rupiah($pembayaran->pelunasan_nominal) }}
                                via {{ strtoupper($pembayaran->pelunasan_jenis ?? '—') }}
                                · {{ $pembayaran->pelunasan_waktu?->format('d M Y H:i') ?? '' }}
                            </p>
                        </div>
                    @elseif ($pembayaran && $isPaid && $statusLower === 'sukses')
                        <div class="mt-3 rounded-xl bg-amber-50 border border-amber-200 p-3 text-sm">
                            <p class="text-amber-700 font-medium flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2 2"/></svg>
                                Menunggu Pelunasan
                            </p>
                            <p class="text-amber-600 text-xs mt-1">Sisa pembayaran: <strong>{{ $rupiah($remaining) }}</strong> — dibayar di counter</p>
                        </div>
                    @endif
                </article>
            </div>

            {{-- RIGHT: Client References --}}
            <div class="lg:col-span-2 space-y-5">
                {{-- Client References --}}
                <article class="rounded-2xl bg-white border border-[#eedde2] p-5">
                    <p class="font-semibold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21"/></svg>
                        Client References
                    </p>

                    @php
                        $refUrl = null;
                        if ($booking->referensi_desain) {
                            if (filter_var($booking->referensi_desain, FILTER_VALIDATE_URL)) {
                                $refUrl = $booking->referensi_desain;
                            } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($booking->referensi_desain)) {
                                $refUrl = asset('storage/' . $booking->referensi_desain);
                            }
                        }
                    @endphp
                    @if ($refUrl)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="md:col-span-2 aspect-square overflow-hidden rounded-xl bg-slate-100">
                                <img src="{{ $refUrl }}" alt="Reference"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="md:col-span-1 grid grid-rows-2 gap-3">
                                <div class="aspect-square overflow-hidden rounded-xl bg-amber-100/40 flex items-center justify-center text-amber-400">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                                </div>
                                <div class="aspect-square overflow-hidden rounded-xl bg-slate-100 flex items-center justify-center text-slate-300">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl bg-[#fdf8fa] py-12 px-6 text-center">
                            <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21"/></svg>
                            <p class="text-sm text-slate-400 italic mt-3">Customer belum upload referensi desain.</p>
                        </div>
                    @endif
                </article>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        document.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', async function () {
                const action = this.dataset.action;
                const isStart = action === 'start';

                const iconSvg = isStart
                    ? '<div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#fdf2f5]"><svg class="w-7 h-7 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg></div>'
                    : '<div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50"><svg class="w-7 h-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>';

                const title = isStart ? 'Mulai Sesi Treatment?' : 'Selesaikan Sesi?';
                const description = isStart
                    ? 'Status booking akan berubah dari <b>Dikonfirmasi</b> ke <b>Diproses</b>. Sesi treatment akan dimulai.'
                    : 'Status booking akan berubah dari <b>Diproses</b> ke <b>Sukses</b>. Customer akan bisa memberikan review setelahnya.';

                const result = await Swal.fire({
                    title: `<span class="text-slate-800 text-lg font-semibold">${title}</span>`,
                    html: `
                        ${iconSvg}
                        <p class="text-sm text-slate-600 leading-relaxed">${description}</p>
                        <div class="mt-3 rounded-lg bg-slate-50 px-4 py-3 text-sm text-left border border-slate-100">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Customer</span>
                                <span class="font-medium text-slate-800">{{ $u?->full_name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between mt-1.5">
                                <span class="text-slate-500">Treatment</span>
                                <span class="font-medium text-slate-800">{{ $treatment?->nama_jasa ?? '—' }}</span>
                            </div>
                        </div>
                    `,
                    icon: null,
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    confirmButtonText: isStart ? 'Ya, Mulai Sesi' : 'Ya, Selesaikan',
                    confirmButtonColor: isStart ? '#a23f66' : '#059669',
                    cancelButtonColor: '#94a3b8',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'rounded-lg px-5 py-2.5 text-sm font-medium',
                        cancelButton: 'rounded-lg px-5 py-2.5 text-sm font-medium',
                    },
                    width: '26rem',
                    didOpen: () => {
                        // Focus cancel button by default (safer)
                        Swal.getCancelButton()?.focus();
                    },
                });

                if (!result.isConfirmed) return;

                const url = isStart
                    ? '{{ route('dashboard.nailist-bookings.start', $booking) }}'
                    : '{{ route('dashboard.nailist-bookings.finish', $booking) }}';

                // Submit via hidden form — controller returns back()->with('toast')
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.style.display = 'none';
                form.innerHTML = '<input type="hidden" name="_token" value="' + csrfToken + '">'
                    + '<input type="hidden" name="_method" value="PATCH">';
                document.body.appendChild(form);
                form.submit();
            });
        });
    </script>
</x-app-layout>
