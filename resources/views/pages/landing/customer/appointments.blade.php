<x-layouts.landing title="My Appointments">
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $tabLabels = ['upcoming' => 'Upcoming', 'completed' => 'Completed', 'canceled' => 'Canceled'];

        $statusInfo = function ($name) {
            return match (strtolower((string) $name)) {
                'pending'      => ['text' => 'text-amber-600', 'label' => 'Payment Pending'],
                'dikonfirmasi' => ['text' => 'text-emerald-600', 'label' => 'Confirmed'],
                'diproses'     => ['text' => 'text-sky-600', 'label' => 'In Progress'],
                'sukses'       => ['text' => 'text-emerald-600', 'label' => 'Completed'],
                'dibatalkan'   => ['text' => 'text-rose-600', 'label' => 'Canceled'],
                default        => ['text' => 'text-slate-600', 'label' => $name],
            };
        };
    @endphp

    {{-- Tambahan CSS agar background pink merata sampai ke belakang navbar --}}
    <style>
        body { background-color: #FFF4F2 !important; }
    </style>

    <main class="min-h-screen pb-20">
        <div class="max-w-4xl mx-auto px-4 pt-10 md:pt-12">
            {{-- Title --}}
            <h1 class="text-center text-3xl md:text-4xl text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                My Appointments
            </h1>

            {{-- Tabs --}}
            <div class="flex items-center justify-start gap-2 mt-8 mb-6">
                @foreach ($tabLabels as $key => $label)
                    @php $isActive = $activeTab === $key; @endphp
                    <a href="{{ route('customer.appointments.index', ['tab' => $key]) }}"
                       class="rounded-full px-5 py-2 text-sm font-medium transition
                              {{ $isActive
                                 ? 'bg-[#fbd6df] text-[#a23f66]'
                                 : 'text-slate-700 hover:bg-white/50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Empty state --}}
            @if ($appointments->isEmpty())
                <div class="rounded-2xl bg-white py-16 md:py-20 text-center shadow-[0_4px_20px_rgba(205,163,173,0.1)]">
                    <h2 class="text-2xl font-semibold text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                        No appointments yet.
                    </h2>
                    <p class="text-sm text-slate-500 mt-2">
                        Start booking your nail session today
                        <span class="text-amber-400">✨</span>
                    </p>
                    <a href="{{ route('booking.step1') }}"
                       class="inline-flex items-center gap-1.5 mt-6 rounded-full bg-[#DF7D9E] px-6 py-3 text-sm font-medium text-white hover:bg-[#df8da0] transition shadow-sm">
                        Book Appointment
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                    </a>
                </div>
            @else
                <div class="space-y-5">
                    @foreach ($appointments as $a)
                        @php
                            $statusName = $a->status?->nama_status ?? 'Unknown';
                            $sc = $statusInfo($statusName);
                            $treatment = $a->treatment;
                            $resvId = 'PL'.strtoupper(substr($a->id, 0, 4));
                            $isPaymentPending = strtolower($statusName) === 'pending';
                            $isExpired = $a->tanggal && $a->tanggal->isPast() && $isPaymentPending;
                            $isCanceled = strtolower($statusName) === 'dibatalkan';
                            $totalHarga = (float) ($a->total_harga_final ?: ($treatment?->price_min ?? 0));
                        @endphp

                        <article class="rounded-2xl bg-white shadow-[0_4px_15px_rgba(205,163,173,0.1)] overflow-hidden">
                            <div class="px-6 py-4 border-b border-[#f5e7ec] flex items-center justify-between">
                                <h3 class="font-semibold text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                                    Appointment Details
                                </h3>
                                <p class="text-xs text-slate-500">Reservation ID: <span class="font-mono text-slate-700 font-semibold">{{ $resvId }}</span></p>
                            </div>

                            <div class="px-6 py-4 grid grid-cols-2 md:grid-cols-4 gap-4 border-b border-[#f5e7ec]">
                                <div>
                                    <p class="text-xs text-slate-500">Date and Time :</p>
                                    <p class="text-sm text-slate-900 font-semibold mt-0.5">
                                        {{ $a->tanggal?->isoFormat('dddd, D MMMM Y') }} • {{ $a->jam ? \Illuminate\Support\Carbon::parse($a->jam)->format('H.i') : '—' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Nail Artist :</p>
                                    <p class="text-sm text-slate-900 font-semibold mt-0.5">{{ $a->nailist?->user?->full_name ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500">Status :</p>
                                    <p class="text-sm font-semibold mt-0.5 {{ $sc['text'] }}">
                                        @if ($isExpired) Expired @else {{ $sc['label'] }} @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    @if ($isPaymentPending && ! $isExpired)
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('booking.payment.waiting', ['order_id' => $a->pembayaran?->order_id]) }}"
                                               class="rounded-md bg-[#e8a3b3] px-4 py-1.5 text-xs font-medium text-white hover:bg-[#df8da0] transition">
                                                Pay Now
                                            </a>
                                        </div>
                                    @elseif ($isExpired || $isCanceled)
                                        <a href="{{ route('booking.step1') }}"
                                           class="inline-flex items-center justify-center rounded-md bg-[#e8a3b3] px-4 py-1.5 text-xs font-medium text-white hover:bg-[#df8da0] transition">
                                            Book Again
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="px-6 py-5">
                                <p class="font-semibold text-slate-900 mb-4" style="font-family: 'Cormorant Garamond', serif;">
                                    Treatment Details
                                </p>

                                <div class="space-y-3 text-sm">
                                    <div class="grid grid-cols-12 gap-3 items-start">
                                        <div class="col-span-12 sm:col-span-7">
                                            <p class="text-xs text-slate-500">Treatment</p>
                                            <p class="text-slate-900 mt-0.5">{{ $treatment?->nama_jasa ?? '—' }}</p>
                                        </div>
                                        <div class="col-span-6 sm:col-span-2">
                                            <p class="text-xs text-slate-500">Qty</p>
                                            <p class="text-slate-700 mt-0.5">1x</p>
                                        </div>
                                        <div class="col-span-6 sm:col-span-3 text-right">
                                            <p class="text-slate-900 font-medium">{{ $rupiah($treatment?->price_min ?? 0) }}</p>
                                        </div>
                                    </div>

                                    @if ($a->referensi_desain)
                                        <div class="grid grid-cols-12 gap-3 pt-3 border-t border-[#f5e7ec]">
                                            <div class="col-span-12">
                                                <p class="text-xs text-slate-500">Referensi Desain</p>
                                                <a href="{{ $a->referensi_desain }}" target="_blank" rel="noopener"
                                                   class="text-xs text-[#a23f66] hover:underline break-all">{{ $a->referensi_desain }}</a>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center justify-end gap-4 mt-5 pt-4 border-t border-[#f5e7ec]">
                                    <span class="text-sm text-slate-500">Total :</span>
                                    <span class="font-semibold text-slate-900">{{ $rupiah($totalHarga) }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
</x-layouts.landing>