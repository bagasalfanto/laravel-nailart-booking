<x-layouts.landing title="Book — Payment Details">
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $treatmentSum = (float) ($summary['treatmentSum'] ?? 0);
        $charmSum     = (float) ($summary['charmSum']     ?? 0);
        $total        = (float) ($summary['total']        ?? 0);
    @endphp

    <style>
        body { background-color: #FFF4F2 !important; }
    </style>

    <main class="min-h-screen pb-20">
        <div class="max-w-6xl mx-auto px-4 pt-10">

            {{-- Title --}}
            <h1 class="text-center text-3xl md:text-4xl text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                Payment Details
            </h1>

            {{-- Stepper --}}
            <div class="mt-6 mb-10">
                <x-booking.stepper :current="3" />
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('booking.step3.submit') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @csrf

                {{-- LEFT: Forms --}}
                <div class="lg:col-span-2 space-y-5">
                    {{-- Booking Information --}}
                    <div class="bg-white rounded-2xl p-6 shadow-[0_4px_15px_rgba(205,163,173,0.1)]">
                        <p class="font-semibold text-slate-900 mb-4" style="font-family: 'Cormorant Garamond', serif;">
                            Booking Information
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-slate-500">First Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="first_name"
                                       value="{{ old('first_name', $draft['first_name'] ?? $firstName) }}"
                                       required maxlength="50"
                                       class="mt-1 w-full rounded-lg border-[#ece4e8] bg-[#faf3ed] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">Last Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="last_name"
                                       value="{{ old('last_name', $draft['last_name'] ?? $lastName) }}"
                                       required maxlength="50"
                                       class="mt-1 w-full rounded-lg border-[#ece4e8] bg-[#faf3ed] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">Phone Number <span class="text-rose-500">*</span></label>
                                <input type="tel" name="phone"
                                       value="{{ old('phone', $draft['phone'] ?? $phone) }}"
                                       required
                                       class="mt-1 w-full rounded-lg border-[#ece4e8] bg-[#faf3ed] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            </div>
                            <div>
                                <label class="text-xs text-slate-500">Email <span class="text-rose-500">*</span></label>
                                <input type="email" name="email"
                                       value="{{ old('email', $draft['email'] ?? $email) }}"
                                       required
                                       class="mt-1 w-full rounded-lg border-[#ece4e8] bg-[#faf3ed] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method (Midtrans Snap handles all channels) --}}
                    <div class="bg-white rounded-2xl p-6 shadow-[0_4px_15px_rgba(205,163,173,0.1)]">
                        <p class="font-semibold text-slate-900 mb-4" style="font-family: 'Cormorant Garamond', serif;">
                            Payment Method
                        </p>
                        <div class="rounded-lg border border-[#a23f66] bg-[#fdf2f5] px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-12 h-8 rounded bg-white border border-slate-200 text-[10px] font-bold text-[#a23f66]">PAY</span>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">Midtrans Secure Checkout</p>
                                    <p class="text-xs text-slate-500">Bayar via QRIS, GoPay, ShopeePay, VA, atau kartu kredit. Metode dipilih di halaman berikutnya.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Order Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl p-6 shadow-[0_4px_15px_rgba(205,163,173,0.1)] sticky top-6">
                        <p class="font-semibold text-slate-900 mb-4" style="font-family: 'Cormorant Garamond', serif;">Your Order</p>

                        {{-- Booking info --}}
                        <div class="space-y-2 text-sm border-b border-[#f5e7ec] pb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Selected Date</span>
                                <span class="text-slate-800 font-medium">
                                    {{ $summary['tanggal'] ? \Carbon\Carbon::parse($summary['tanggal'])->translatedFormat('d/m/Y') : '—' }}
                                    {{ $summary['jam'] ? '· '.str_replace(':', '.', $summary['jam']) : '' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Nail Artist</span>
                                <span class="text-slate-800 font-medium">{{ $summary['nailist']?->user?->full_name ?? '—' }}</span>
                            </div>
                        </div>

                        {{-- Items --}}
                        <div class="space-y-1.5 text-sm border-b border-[#f5e7ec] py-3">
                            @forelse ($summary['treatments'] as $t)
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-700">{{ $t->nama_jasa }}</span>
                                    <span class="text-slate-800">{{ $rupiah($t->price_min) }}</span>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 italic">Tidak ada treatment dipilih.</p>
                            @endforelse

                            @foreach ($summary['charms'] as $c)
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ $c['name'] }} ({{ $c['qty'] }}x)</span>
                                    <span>{{ $rupiah($c['subtotal']) }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Totals --}}
                        <div class="space-y-1.5 text-sm py-3 border-b border-[#f5e7ec]">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Subtotal Treatment</span>
                                <span class="text-[#a23f66] font-semibold">{{ $rupiah($treatmentSum) }}</span>
                            </div>
                            @if ($charmSum > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Charms</span>
                                    <span class="text-[#a23f66] font-semibold">{{ $rupiah($charmSum) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between py-3">
                            <span class="font-semibold text-slate-900">Total Estimasi</span>
                            <span class="font-bold text-[#a23f66]">{{ $rupiah($total) }}</span>
                        </div>

                        {{-- DP highlight --}}
                        <div class="rounded-xl bg-[#fdf2f5] p-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-[#a23f66] font-medium">Down Payment (DP)</span>
                                <span class="font-bold text-[#a23f66]">{{ $rupiah($dpAmount) }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Sisa dibayar setelah service selesai.</p>
                        </div>

                        <button type="submit" class="w-full mt-4 rounded-lg bg-[#e8a3b3] text-white font-semibold px-4 py-3 hover:bg-[#df8da0] transition shadow-sm"
                                style="font-family: 'Cormorant Garamond', serif;">
                            Pay {{ $rupiah($dpAmount) }} (DP)
                        </button>

                        <a href="{{ route('booking.step2') }}"
                           class="mt-2 flex items-center justify-center gap-1.5 w-full rounded-lg border border-[#eedde2] bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-[#fdf2f5] transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                            Back
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </main>
</x-layouts.landing>
