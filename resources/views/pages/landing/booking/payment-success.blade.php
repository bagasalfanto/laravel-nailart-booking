<x-layouts.landing title="Booking Successful">
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $reservasi = $pembayaran?->reservasi;
        $tanggalLabel = $reservasi?->tanggal ? \Carbon\Carbon::parse($reservasi->tanggal)->translatedFormat('d F Y') : '—';
        $jamLabel     = $reservasi?->jam ? \Carbon\Carbon::parse($reservasi->jam)->format('H.i') : '—';
    @endphp

    <main class="bg-[#e8a3b3] min-h-screen flex items-center justify-center py-10 px-4">
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl relative overflow-hidden">
            <div class="absolute left-[-12px] top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-[#e8a3b3]"></div>
            <div class="absolute right-[-12px] top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-[#e8a3b3]"></div>

            <a href="{{ route('home') }}" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>

            <div class="px-8 py-10 md:px-12 md:py-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-500 text-white mb-4 shadow-lg">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h1 class="text-2xl md:text-3xl font-semibold text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                    Booking kamu sudah terkonfirmasi
                    <span class="text-amber-400">✨</span>
                </h1>
                <p class="text-slate-500 mt-2 text-sm">
                    Pembayaran DP berhasil. Kami nantikan kedatangan kamu!
                </p>

                @if ($pembayaran)
                    <div class="mt-6 space-y-1.5 text-sm text-slate-700">
                        <p><span class="text-slate-400">Order ID:</span> <span class="font-semibold font-mono">{{ $pembayaran->order_id }}</span></p>
                        <p><span class="text-slate-400">Tanggal:</span> <span class="font-semibold">{{ $tanggalLabel }}</span></p>
                        <p><span class="text-slate-400">Jam:</span> <span class="font-semibold">{{ $jamLabel }}</span></p>
                        <p><span class="text-slate-400">Nail Artist:</span> <span class="font-semibold">{{ $reservasi?->nailist?->user?->full_name ?? '—' }}</span></p>
                        <p><span class="text-slate-400">Treatment:</span> <span class="font-semibold">{{ $reservasi?->treatment?->nama_jasa ?? '—' }}</span></p>
                        @if ($pembayaran->jenis_pembayaran)
                            <p><span class="text-slate-400">Metode:</span> <span class="font-semibold capitalize">{{ str_replace('_', ' ', $pembayaran->jenis_pembayaran) }}</span></p>
                        @endif
                    </div>

                    <div class="my-6 border-t-2 border-dashed border-[#f1e4e8]"></div>

                    <p class="text-xs uppercase tracking-wide text-slate-400">Down Payment Terbayar</p>
                    <p class="text-2xl md:text-3xl font-bold text-[#a23f66] mt-2" style="font-family: 'Cormorant Garamond', serif;">
                        {{ $rupiah($pembayaran->nominal) }}
                    </p>
                    <p class="text-xs text-slate-400 italic mt-1">*Sisa pembayaran dilakukan setelah service selesai.</p>
                @endif

                <a href="{{ route('customer.appointments.index') }}"
                   class="block w-full mt-8 rounded-lg bg-[#e8a3b3] text-white font-semibold py-3.5 hover:bg-[#df8da0] transition shadow-md"
                   style="font-family: 'Cormorant Garamond', serif;">
                    Lihat Booking Saya
                </a>
            </div>
        </div>
    </main>
</x-layouts.landing>
