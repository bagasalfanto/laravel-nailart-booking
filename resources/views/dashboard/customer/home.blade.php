<x-app-layout>
    <x-slot name="header">Customer Dashboard</x-slot>

    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');

        $statusInfo = fn ($name) => match (strtolower((string) $name)) {
            'pending'      => ['class' => 'text-amber-600 bg-amber-50',    'label' => 'Menunggu'],
            'dikonfirmasi' => ['class' => 'text-emerald-600 bg-emerald-50','label' => 'Dikonfirmasi'],
            'diproses'     => ['class' => 'text-sky-600 bg-sky-50',        'label' => 'Diproses'],
            'sukses'       => ['class' => 'text-emerald-700 bg-emerald-50','label' => 'Selesai'],
            'dibatalkan'   => ['class' => 'text-rose-600 bg-rose-50',      'label' => 'Dibatalkan'],
            default        => ['class' => 'text-slate-600 bg-slate-50',    'label' => $name],
        };

        // Status pembayaran sesuai aturan DB (tidak ada field "lunas" eksplisit).
        $payInfo = function ($b) {
            $status = strtolower($b->status?->nama_status ?? '');
            $pay    = strtolower($b->pembayaran?->status_pembayaran ?? '');
            $total  = (float) ($b->total_harga_final ?: 0);
            $dp     = (float) ($b->pembayaran?->nominal ?? 0);

            if ($status === 'dibatalkan') {
                return ['label' => 'Dibatalkan', 'class' => 'text-rose-600 bg-rose-50', 'sisa' => 0.0];
            }
            if ($status === 'sukses') {
                return ['label' => 'Lunas', 'class' => 'text-emerald-700 bg-emerald-50', 'sisa' => 0.0];
            }
            if ($pay === 'settlement') {
                return ['label' => 'DP Dibayar', 'class' => 'text-sky-600 bg-sky-50', 'sisa' => max(0.0, $total - $dp)];
            }

            return ['label' => 'Menunggu DP', 'class' => 'text-amber-600 bg-amber-50', 'sisa' => $total];
        };
    @endphp

    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-semibold text-slate-900">Halo, {{ Auth::user()->full_name }}</h1>
        <p class="text-slate-500 mt-1">Selamat datang di nailby.hilda.</p>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-8 mb-8">
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
                <p class="text-sm text-slate-500">Total Booking</p>
                <p class="text-3xl font-semibold text-[#a23f66] mt-2">{{ $totalBookings }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
                <p class="text-sm text-slate-500">Menunggu Pembayaran</p>
                <p class="text-3xl font-semibold text-amber-600 mt-2">{{ $pendingPayment }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
                <p class="text-sm text-slate-500">Selesai</p>
                <p class="text-3xl font-semibold text-emerald-600 mt-2">{{ $completed }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
                <p class="text-sm text-slate-500">Total Dibayar</p>
                <p class="text-2xl font-semibold text-[#a23f66] mt-2">{{ $rupiah($totalPaid) }}</p>
            </div>
        </div>

        {{-- Daftar booking --}}
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Booking Saya</h2>

        @if ($bookings->isEmpty())
            <div class="rounded-2xl bg-white border border-[#eedde2] py-16 text-center">
                <p class="text-slate-700 font-medium">Belum ada booking.</p>
                <p class="text-sm text-slate-500 mt-1">Yuk mulai booking sesi nail art kamu.</p>
                <a href="{{ route('booking.step1') }}"
                   class="inline-flex items-center gap-1.5 mt-5 rounded-full bg-[#e8a3b3] px-6 py-3 text-sm font-medium text-white hover:bg-[#df8da0] transition shadow-sm">
                    Book Appointment
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($bookings as $b)
                    @php
                        $statusName = $b->status?->nama_status ?? 'Unknown';
                        $sc  = $statusInfo($statusName);
                        $pi  = $payInfo($b);
                        $total = (float) ($b->total_harga_final ?: 0);
                        $dp    = (float) ($b->pembayaran?->nominal ?? 0);
                        $isPendingPay = strtolower($statusName) === 'pending';
                        $isExpired = $b->tanggal && $b->tanggal->isPast() && $isPendingPay;
                    @endphp

                    <article class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden">
                        {{-- Header --}}
                        <div class="px-6 py-4 border-b border-[#f1e4e8] flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $b->nailist?->user?->avatar_url }}" alt=""
                                     class="w-10 h-10 rounded-full object-cover border border-[#f1e4e8]">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $b->treatment?->nama_jasa ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">dengan {{ $b->nailist?->user?->full_name ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sc['class'] }}">
                                    {{ $isExpired ? 'Kedaluwarsa' : $sc['label'] }}
                                </span>
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pi['class'] }}">
                                    {{ $pi['label'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <p class="text-xs text-slate-500">Tanggal &amp; Jam</p>
                                <p class="text-sm text-slate-900 font-medium mt-0.5">
                                    {{ $b->tanggal?->isoFormat('dddd, D MMMM Y') ?? '—' }}
                                    • {{ $b->jam ? \Illuminate\Support\Carbon::parse($b->jam)->format('H.i') : '—' }}
                                </p>
                            </div>

                            <div class="md:text-right space-y-1">
                                <div class="flex items-center justify-between md:justify-end md:gap-3">
                                    <span class="text-xs text-slate-500">Total</span>
                                    <span class="text-sm font-semibold text-slate-900">{{ $rupiah($total) }}</span>
                                </div>
                                <div class="flex items-center justify-between md:justify-end md:gap-3">
                                    <span class="text-xs text-slate-500">DP</span>
                                    <span class="text-sm text-slate-700">{{ $rupiah($dp) }}</span>
                                </div>
                                <div class="flex items-center justify-between md:justify-end md:gap-3">
                                    <span class="text-xs text-slate-500">Sisa</span>
                                    <span class="text-sm font-medium {{ $pi['sisa'] > 0 ? 'text-[#a23f66]' : 'text-emerald-600' }}">{{ $rupiah($pi['sisa']) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Footer aksi --}}
                        @if ($isPendingPay && ! $isExpired && $b->pembayaran?->order_id)
                            <div class="px-6 py-3 border-t border-[#f1e4e8] flex justify-end">
                                <a href="{{ route('booking.payment.waiting', ['order_id' => $b->pembayaran->order_id]) }}"
                                   class="rounded-md bg-[#e8a3b3] px-5 py-1.5 text-xs font-medium text-white hover:bg-[#df8da0] transition">
                                    Bayar DP
                                </a>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
