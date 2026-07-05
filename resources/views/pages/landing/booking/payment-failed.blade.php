<x-layouts.landing title="Payment Failed">
    <main class="bg-[#df6e8c] min-h-screen flex items-center justify-center py-10 px-4">
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl px-8 py-12 text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 mb-4">
                <svg class="w-20 h-20 text-[#ef6e8c]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </div>

            <h1 class="text-2xl md:text-3xl font-semibold text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                Pembayaran tidak berhasil
            </h1>
            <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">
                Kami tidak dapat memproses pembayaran kamu. Status saat ini:
                <span class="font-semibold capitalize">{{ $pembayaran?->status_pembayaran ?? 'unknown' }}</span>.
            </p>

            @if ($pembayaran)
                <div class="mt-4 text-xs text-slate-500">
                    Order ID: <span class="font-mono font-semibold">{{ $pembayaran->order_id }}</span>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8 max-w-md mx-auto">
                @if ($pembayaran)
                    <a href="{{ route('booking.payment.waiting', ['order_id' => $pembayaran->order_id]) }}"
                       class="w-full sm:w-1/2 rounded-lg bg-[#df6e8c] text-white font-semibold py-3 hover:bg-[#cf5879] transition shadow-md"
                       style="font-family: 'Cormorant Garamond', serif;">
                        Coba Bayar Ulang
                    </a>
                @endif
                <a href="{{ route('customer.appointments.index') }}"
                   class="w-full sm:w-1/2 rounded-lg border border-[#df6e8c] bg-white text-[#df6e8c] font-medium py-3 hover:bg-[#fdf2f5] transition"
                   style="font-family: 'Cormorant Garamond', serif;">
                    Kembali ke Booking
                </a>
            </div>

            <p class="text-xs text-slate-400 italic mt-6">
                *Jika masalah berlanjut, hubungi support.
            </p>
        </div>
    </main>
</x-layouts.landing>
