<x-layouts.landing title="Pricelist">
    @php
        // Format angka ke gaya "50k", "1.5k", atau "150" (untuk < 1000)
        $rupiah = function ($n) {
            $n = (float) $n;
            if ($n >= 1000) {
                $val = $n / 1000;
                $str = $val == (int) $val
                    ? (string) (int) $val
                    : rtrim(rtrim(number_format($val, 1, '.', ''), '0'), '.');
                return $str.'k';
            }
            return (string) (int) $n;
        };

        // Format range pintar:
        //   - kalau min & max keduanya kelipatan 1000 → "1-5k"
        //   - else → "1.5k-5k" (atau campuran)
        $rangeLabel = function ($min, $max) use ($rupiah) {
            $min = (float) $min;
            $max = (float) $max;
            $bothK = $min >= 1000 && $max >= 1000 && fmod($min, 1000) === 0.0 && fmod($max, 1000) === 0.0;
            if ($bothK) {
                return ((int) ($min / 1000)).'-'.((int) ($max / 1000)).'k';
            }
            return $rupiah($min).'-'.$rupiah($max);
        };
    @endphp

    {{-- Google Fonts: Poppins (sans) & Playfair Display (serif) --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Custom token (mirror tailwind.config dari file HTML) — discoped ke .pricelist-page --}}
    <style>
        .pricelist-page              { font-family: 'Poppins', sans-serif; color: #1f2937; }
        .pricelist-page .font-sans   { font-family: 'Poppins', sans-serif; }
        .pricelist-page .font-serif  { font-family: 'Playfair Display', serif; }
        
        body { background-color: #FFF4F2 !important; }
        
        .pricelist-page .bg-card-bg  { background-color: #FFFFFF; }
        .pricelist-page .text-brand-pink { color: #D56B8D; }
        
        .pricelist-page .card-pricelist-glow {
            background-color: #FFFFFF;
            border: 2px solid #FFFFFF; /* Garis batas putih */
            box-shadow: 0 10px 40px rgba(219, 112, 148, 0.25); /* Bayangan pink soft */
        }
    </style>

    <section class="pricelist-page font-sans text-gray-800 min-h-screen py-6 px-4 md:px-8">

        {{-- === HEADER PRICELIST === --}}
        <header class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-serif font-semibold text-gray-900 mb-3 tracking-wide">
                Pricelist
            </h1>
            <p class="text-[#C17A8E] text-[15px]">Explore our nail treatment options.</p>
        </header>

        {{-- === KONTEN PRICELIST CARDS === --}}
        <main class="max-w-3xl mx-auto space-y-6 pb-20">

            @forelse ($categories as $category)
                @continue($category->activeTreatments->isEmpty())

                <div class="card-pricelist-glow rounded-3xl p-7 md:p-8">
                    {{-- Header card --}}
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-brand-pink font-serif text-xl font-semibold uppercase tracking-wider">
                            {{ $category->name }}
                        </h2>
                        <a href="{{ auth()->check() && auth()->user()->hasRole('customer') ? route('booking.step1') : route('login') }}" 
                           class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:border-[#D56B8D] hover:text-[#D56B8D] transition-all duration-300 cursor-pointer">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="7" y1="17" x2="17" y2="7"></line>
                                <polyline points="7 7 17 7 17 17"></polyline>
                            </svg>
                        </a>
                    </div>

                    <hr class="border-gray-100 mb-5">

                    {{-- Items --}}
                    <ul class="space-y-4 text-[13px] md:text-[14px]">
                        @foreach ($category->activeTreatments as $t)
                            @php
                                $isRange = $t->price_type === 'range' && $t->price_max !== null;
                                // "Start from" hanya untuk range dgn nilai >= 100k (treatment besar yg fluktuatif)
                                $useStartFrom = $isRange && (float) $t->price_min >= 100000;
                            @endphp
                            <li class="flex justify-between items-center gap-4">
                                <span class="uppercase text-gray-700 tracking-wide">{{ $t->nama_jasa }}</span>

                                @if ($useStartFrom)
                                    <div class="text-right flex flex-col shrink-0">
                                        <span class="font-semibold text-black text-[15px] leading-none">{{ $rupiah($t->price_min) }}</span>
                                        <span class="text-[10px] text-gray-500 leading-none mb-0.5">Start from</span>
                                    </div>
                                @elseif ($isRange)
                                    <span class="font-semibold text-black text-[15px] shrink-0">{{ $rangeLabel($t->price_min, $t->price_max) }}</span>
                                @else
                                    <span class="font-semibold text-black text-[15px] shrink-0">{{ $rupiah($t->price_min) }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <div class="text-center py-16">
                    <p class="text-gray-500">Pricelist belum tersedia.</p>
                </div>
            @endforelse

        </main>
    </section>
</x-layouts.landing>
