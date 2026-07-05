<x-layouts.landing>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Red+Hat+Display:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Red Hat Display', system-ui, sans-serif;
            background: #FFF4F2;
            color: #202020;
            -webkit-font-smoothing: antialiased;
        }
        .font-serif-eb { font-family: 'EB Garamond', Georgia, serif; }
        [x-cloak] { display: none !important; }
        .shadow-coquette    { box-shadow: 0 8px 32px rgba(219, 112, 148, 0.12); }
        .shadow-coquette-lg { box-shadow: 0 12px 40px rgba(219, 112, 148, 0.18); }
    </style>

    @php
        $get = fn ($key, $default = '') => $settings[$key] ?? $default;
        // Inline rectangle-nails SVG decorative — varying gradient
        $rect = function ($variant = 1) {
            $grads = [
                1 => ['from' => '#F8C5D2', 'to' => '#DB7094'],
                2 => ['from' => '#FFE0D6', 'to' => '#F8A7BD'],
                3 => ['from' => '#FCD4DD', 'to' => '#E892A9'],
                4 => ['from' => '#F8B7C8', 'to' => '#DB7094'],
            ];
            $g = $grads[$variant] ?? $grads[1];
            return '<span class="inline-block align-middle mx-1 w-14 h-7 rounded-full" style="background:linear-gradient(135deg,'.$g['from'].','.$g['to'].');box-shadow:0 4px 12px rgba(219,112,148,0.25)"></span>';
        };
    @endphp


    <main>
        {{-- ============== HERO ============== --}}
        <section class="max-w-6xl mx-auto px-4 pt-12 md:pt-16 pb-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                {{-- LEFT --}}
                <div>
                    {{-- Eyebrow + caption --}}
                    <p class="text-xs uppercase tracking-[0.3em] text-[#DB7094] font-medium font-serif-eb">
                        {{ $get('hero_eyebrow', 'Hello Beauty') }}
                    </p>
                    <p class="text-sm italic text-slate-500 mt-1">{{ $get('hero_caption', 'Confidence in your fingers.') }}</p>

                    {{-- 5-star rating --}}
                    <div class="flex items-center gap-1 text-[#DB7094] text-sm mt-4">
                        ★ ★ ★ ★ ★
                    </div>

                    {{-- Headline --}}
                    <h1 class="font-serif-eb text-4xl md:text-5xl lg:text-6xl text-[#202020] leading-tight mt-5">
                        More Than <em class="text-[#DB7094] not-italic font-normal">Beauty</em>,<br>
                        Confidence on Your Finger
                    </h1>

                    {{-- Stats --}}
                    <div class="flex flex-wrap items-center gap-6 mt-7">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-[#FFE5DD]">
                                <svg class="w-4 h-4 text-[#DB7094]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </span>
                            <div>
                                <p class="text-base font-semibold text-[#202020] leading-none">100+</p>
                                <p class="text-xs text-slate-500 mt-0.5">Clients</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-[#FFE5DD]">
                                <svg class="w-4 h-4 text-[#DB7094]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                            </span>
                            <div>
                                <p class="text-base font-semibold text-[#202020] leading-none">300+</p>
                                <p class="text-xs text-slate-500 mt-0.5">Hours</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-[#FFE5DD]">
                                <svg class="w-4 h-4 text-[#DB7094]" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1l2.6 5.3 5.9.85-4.25 4.15 1 5.85L10 14.4l-5.25 2.75 1-5.85L1.5 7.15l5.9-.85L10 1z"/></svg>
                            </span>
                            <div>
                                <p class="text-base font-semibold text-[#202020] leading-none">5 Star</p>
                                <p class="text-xs text-slate-500 mt-0.5">Satisfied</p>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex flex-wrap items-center gap-3 mt-7">
                        <a href="{{ auth()->check() && auth()->user()->hasRole('customer') ? route('booking.step1') : route('login') }}"
                           class="inline-flex items-center gap-2 rounded-full bg-[#DB7094] px-6 py-3 text-sm font-medium text-white hover:bg-[#c45f81] shadow-coquette transition">
                            Book Appointment
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                        </a>
                        @if ($get('instagram_url'))
                            <a href="{{ $get('instagram_url') }}" target="_blank" rel="noopener"
                               class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white border border-[#FFE5DD] hover:bg-[#FFF4F2] transition shadow-sm">
                                <img src="{{ asset('images/icons/icon-instagram-black.svg') }}" alt="Instagram" class="w-5 h-5">
                            </a>
                        @endif
                        @if ($get('whatsapp_url'))
                            <a href="{{ $get('whatsapp_url') }}" target="_blank" rel="noopener"
                               class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white border border-[#FFE5DD] hover:bg-[#FFF4F2] transition shadow-sm">
                                <img src="{{ asset('images/icons/icon-whatsapp-black.svg') }}" alt="WhatsApp" class="w-5 h-5">
                            </a>
                        @endif
                    </div>
                </div>

                {{-- RIGHT — hero photo --}}
                <div class="relative">
                    <div class="relative aspect-[4/5] rounded-[2.5rem] overflow-hidden shadow-coquette-lg">
                        <img src="https://images.unsplash.com/photo-1604654894610-df63bc536371?w=800&h=1000&fit=crop"
                             alt="Nail art showcase"
                             class="w-full h-full object-cover"
                             loading="eager">
                        {{-- Pink gradient fade overlay bottom --}}
                        <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-[#FFF4F2] via-[#FFF4F2]/40 to-transparent"></div>
                    </div>
                    {{-- Decorative floating element --}}
                    <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-[#FFE5DD] -z-10"></div>
                </div>
            </div>

            {{-- Tagline paragraph dengan rectangle-nails inline --}}
            <div class="text-center mt-16 max-w-4xl mx-auto">
                <p class="font-serif-eb text-2xl md:text-3xl lg:text-4xl text-[#202020] leading-relaxed">
                    Your Nails {!! $rect(1) !!} Are The Smallest Canvas, Yet They Hold The {!! $rect(2) !!} Loudest Statement. Welcome To A Space Where Premium Carenets {!! $rect(3) !!} Intricate Artistry {!! $rect(4) !!}
                </p>
            </div>
        </section>

        {{-- ============== CURVY DIVIDER (PANJANG) ============== --}}
        <div class="text-center text-[#DB7094] py-8 select-none" aria-hidden="true">
            <svg class="inline-block w-[350px] md:w-[450px] h-4 text-[#DB7094]" viewBox="0 0 400 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M0 8 Q 12.5 0, 25 8 T 50 8 T 75 8 T 100 8 T 125 8 T 150 8 T 175 8 T 200 8 T 225 8 T 250 8 T 275 8 T 300 8 T 325 8 T 350 8 T 375 8 T 400 8" stroke-linecap="round"/>
            </svg>
        </div>

        {{-- ============== PORTFOLIO HIGHLIGHTS ============== --}}
        @if ($portfolios->isNotEmpty())
            <section class="max-w-6xl mx-auto px-4 py-12 md:py-16">
                <div class="text-center mb-10">
                    <h2 class="font-serif-eb text-3xl md:text-4xl text-[#202020]">{{ $get('styles_title', 'Styles that speak for you') }}</h2>
                    <p class="text-sm text-slate-500 mt-2">{{ $get('styles_subtitle', 'How we help you take steps into the setting.') }}</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-5">
                    @foreach ($portfolios->take(4) as $portfolio)
                        <figure class="aspect-square overflow-hidden rounded-3xl bg-white shadow-coquette group hover:shadow-coquette-lg transition">
                            <img src="{{ Str::startsWith($portfolio->gambar_url, ['http', '/']) ? $portfolio->gambar_url : asset('storage/'.$portfolio->gambar_url) }}"
                                 alt="{{ $portfolio->judul ?? 'Portfolio' }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============== CURVY DIVIDER (PANJANG) ============== --}}
        <div class="text-center text-[#DB7094] py-8 select-none" aria-hidden="true">
            <svg class="inline-block w-[350px] md:w-[450px] h-4 text-[#DB7094]" viewBox="0 0 400 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M0 8 Q 12.5 0, 25 8 T 50 8 T 75 8 T 100 8 T 125 8 T 150 8 T 175 8 T 200 8 T 225 8 T 250 8 T 275 8 T 300 8 T 325 8 T 350 8 T 375 8 T 400 8" stroke-linecap="round"/>
            </svg>
        </div>

        {{-- ============== OUR SERVICES (leaf/petal cards) ============== --}}
        @if ($treatments->isNotEmpty())
            <section class="max-w-6xl mx-auto px-4 py-12 md:py-16">
                <div class="text-center mb-10">
                    <h2 class="font-serif-eb text-3xl md:text-4xl text-[#202020]">{{ $get('services_title', 'Our Nail Services') }}</h2>
                    <p class="text-sm text-slate-500 mt-2">{{ $get('services_subtitle', 'Discover your perfect nail style.') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($treatments as $idx => $t)
                        @php
                            // Organic leaf/petal shape variations
                            $petalRadii = [
                                0 => '70% 30% 60% 40% / 60% 30% 70% 40%',
                                1 => '40% 60% 30% 70% / 40% 70% 30% 60%',
                                2 => '60% 40% 70% 30% / 70% 40% 60% 30%',
                            ];
                            $r = $petalRadii[$idx % 3];
                        @endphp
                        <article class="bg-white p-8 text-center shadow-coquette hover:shadow-coquette-lg transition"
                                 style="border-radius: {{ $r }};">
                            {{-- Flower icon --}}
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-[#FFF4F2] mb-4">
                                <svg class="w-7 h-7 text-[#DB7094]" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="2.5"/>
                                    <ellipse cx="12" cy="6" rx="2.5" ry="4" opacity="0.6"/>
                                    <ellipse cx="12" cy="18" rx="2.5" ry="4" opacity="0.6"/>
                                    <ellipse cx="6" cy="12" rx="4" ry="2.5" opacity="0.6"/>
                                    <ellipse cx="18" cy="12" rx="4" ry="2.5" opacity="0.6"/>
                                </svg>
                            </div>
                            <h3 class="font-serif-eb text-xl text-[#202020] mb-2">{{ $t->nama_jasa }}</h3>
                            <p class="text-xs text-slate-500 line-clamp-2 min-h-[2.5em]">{{ $t->deskripsi ?? '—' }}</p>
                            <p class="mt-4 text-lg font-semibold text-[#DB7094] font-serif-eb">{{ $t->price_label }}</p>
                        </article>
                    @endforeach
                </div>

                <div class="text-center mt-8">
                    <a href="{{ route('pricelist') }}" class="inline-flex items-center gap-1.5 text-sm text-[#DB7094] font-medium hover:underline">
                        See all services
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                    </a>
                </div>
            </section>
        @endif

        {{-- ============== SCHEDULE PREVIEW ============== --}}
        <section class="max-w-6xl mx-auto px-4 py-16 md:py-24">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16 md:gap-8 items-center 
            ">

                {{-- KIRI: Judul, List, & Tombol --}}
                <div class="max-w-md mx-auto md:mx-0">
                    <h2 class="font-serif-eb text-4xl md:text-5xl text-[#202020] mb-8 leading-tight">
                        {{ $get('ready_title', 'Ready for a Fresh Set?') }}
                    </h2>

                    {{-- List dengan Checkmark --}}
                    <div class="space-y-4 mb-10 text-[#A25872] text-lg font-medium">
                        <div class="flex items-center gap-4">
                            <div class="w-7 h-7 rounded-full bg-white flex items-center justify-center shadow-sm shrink-0">
                                <svg class="w-4 h-4 text-[#DB7094]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span>Fills Up Quickly</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-7 h-7 rounded-full bg-white flex items-center justify-center shadow-sm shrink-0">
                                <svg class="w-4 h-4 text-[#DB7094]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span>Real-Time Availability</span>
                        </div>
                    </div>

                    {{-- Tombol Putih --}}
                    <a href="{{ route('schedule') }}"
                            class="inline-flex items-center gap-2 rounded-full bg-[#DB7094] px-8 py-3.5 text-[15px] font-semibold text-white hover:bg-[#c95f83] shadow-coquette transition">    See Schedule
                        <svg class="w-4 h-4 text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M7 17L17 7M17 7H7M17 7v10"/>
                        </svg>                    </a>
                </div>

                {{-- KANAN: Kalender & Floating Badges --}}
                <div class="relative w-full max-w-sm mx-auto md:ml-auto mt-8 md:mt-0">
                    
                    {{-- Kotak Kalender Utama --}}
                    <div class="rounded-2xl bg-white p-7 shadow-coquette-lg border border-pink-50 relative z-10">
                        @php
                            $now = now();
                            $monthName = $now->isoFormat('MMMM Y');
                            // Ubah agar kalender dimulai dari hari Senin (Monday) seperti di gambar 2
                            $startCal = $now->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
                            $endCal   = $now->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
                            $days = [];
                            for ($d = $startCal->copy(); $d <= $endCal; $d->addDay()) $days[] = $d->copy();
                        @endphp
                        
                        <p class="font-medium text-[#202020] mb-5 text-lg text-left">{{ $monthName }}</p>
                        
                        <div class="grid grid-cols-7 gap-y-3 gap-x-2 text-center">
                            @foreach (['Mo','Tu','We','Th','Fr','Sa','Su'] as $d)
                                <p class="text-[13px] text-[#202020] font-semibold py-1">{{ $d }}</p>
                            @endforeach
                            @foreach ($days as $day)
                                @php
                                    $isToday = $day->isSameDay($now);
                                    $isCurMonth = $day->month === $now->month;
                                @endphp
                                <div class="w-8 h-8 mx-auto flex items-center justify-center text-[14px] rounded-full
                                            {{ $isToday ? 'bg-[#DB7094] text-white font-bold' : '' }}
                                            {{ ! $isCurMonth ? 'text-slate-300' : ($isToday ? '' : 'text-slate-700') }}">
                                    {{ $day->day }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </section>

        {{-- ============== FAQ ============== --}}
        @if ($faqs->isNotEmpty())
            <section class="max-w-6xl mx-auto px-4 py-12 md:py-20" x-data="{ open: null }">
            {{-- 1. Ganti items-start jadi items-center di sini --}}
            <div class="flex flex-col md:flex-row items-center gap-10">

                {{-- KIRI: Judul --}}
                <div class="w-full md:w-[30%]">
                    <h2 class="text-4xl md:text-5xl text-[#202020] font-medium leading-tight tracking-tight">
                        Frequently Asked <br>
                        <span class="font-serif-eb text-[#DB7094] text-5xl md:text-6xl italic mt-1 inline-block">
                            Questions
                        </span>
                    </h2>

                    <p class="text-slate-600 mt-5 leading-relaxed text-sm md:text-base">
                        {{ $get('faq_subtitle', "Have questions about nail care or our services? We've compiled the most frequently asked questions to help you.") }}
                    </p>
                </div>

                {{-- KANAN: FAQ --}}
                <div class="w-full md:w-[70%] space-y-4">
                    @foreach ($faqs as $i => $faq)
                        <div
                            class="w-full bg-white border border-[#F8B7C8] hover:border-[#E4A0B7] transition-colors shadow-sm overflow-hidden rounded-3xl">

                            <button
                                type="button"
                                @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                                class="w-full flex items-center justify-between px-6 py-5 text-left cursor-pointer">

                                <span class="flex-1 pr-6 font-medium text-gray-800 text-[15px]">
                                    {{ $faq->pertanyaan }}
                                </span>

                                {{-- 2. Pakai inline style untuk warna background agar tidak hilang --}}
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-all duration-300"
                                    :class="open === {{ $i }} ? 'rotate-180' : ''"
                                    :style="open === {{ $i }} ? 'background-color: #DB7094;' : 'background-color: #ECAFC1;'">

                                    <svg
                                        class="w-5 h-5 text-white"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2.5"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>

                            </button>

                            <div
                                x-show="open === {{ $i }}"
                                x-collapse
                                x-cloak
                                class="px-6 pb-5 text-[14px] text-slate-600 leading-relaxed">

                                <div class="py-4 border-t border-pink-100/60">
                                    {{ $faq->jawaban }}
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>
            </section>
        @endif

        {{-- ============== BOOKING CTA ============== --}}
        <section class="max-w-5xl mx-auto px-4 py-12 md:py-16">
            <div class="relative rounded-[2.5rem] bg-gradient-to-r from-[#FCD4DD] via-[#FFE5DD] to-[#FCD4DD] p-8 md:p-14 text-center overflow-hidden">
                {{-- Decorative photo left --}}
                {{-- <img src="https://images.unsplash.com/photo-1632345031435-8727f6897d53?w=200&h=200&fit=crop"
                     alt="" class="hidden md:block absolute left-6 top-1/2 -translate-y-1/2 w-24 h-24 rounded-2xl object-cover shadow-coquette opacity-90">
                <img src="https://images.unsplash.com/photo-1604654894611-fed10b9ee1bb?w=200&h=200&fit=crop"
                     alt="" class="hidden md:block absolute right-6 top-1/2 -translate-y-1/2 w-24 h-24 rounded-2xl object-cover shadow-coquette opacity-90"> --}}

                <h2 class="relative font-serif-eb text-3xl md:text-4xl text-[#202020]">
                    {{ $get('banner_title', 'Ready For Prettier Nails?') }}
                </h2>
                <p class="relative text-sm text-slate-700 mt-3 max-w-md mx-auto">
                    {{ $get('banner_subtitle', 'Let our most skilled artists transform your nails. Book your slot today.') }}
                </p>
                <a href="{{ auth()->check() && auth()->user()->hasRole('customer') ? route('booking.step1') : route('login') }}"
                   class="relative inline-flex items-center gap-2 mt-6 rounded-full bg-[#DB7094] px-8 py-3 text-sm font-medium text-white hover:bg-[#c45f81] shadow-coquette transition">
                    Book Appointment
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                </a>
            </div>
        </section>

        {{-- ============== TESTIMONIALS (carousel) ============== --}}
        @if ($reviews->isNotEmpty())
            <section class="max-w-5xl mx-auto px-4 py-12 md:py-16"
                     x-data="{ i: 0, total: {{ $reviews->count() }} }">
                <div class="text-center mb-8">
                    <h2 class="font-serif-eb text-3xl md:text-4xl text-[#202020]">
                        {{ $get('testimonial_title', 'What Our Clients Say') }}
                    </h2>
                    <p class="text-sm text-slate-500 mt-2">
                        {{ $get('testimonial_subtitle', 'See why they keep coming back to their monthly pamper sessions.') }}
                    </p>
                </div>

                <div class="relative flex items-center gap-3">
                    {{-- Prev arrow --}}
                    <button type="button" @click="i = (i - 1 + total) % total"
                            class="hidden sm:inline-flex w-10 h-10 items-center justify-center rounded-full bg-white border border-[#FFE5DD] hover:bg-[#FFF4F2] shadow-sm shrink-0">
                        <img src="{{ asset('images/icons/icon-arrow-west-black.svg') }}" alt="prev" class="w-4 h-4">
                    </button>

                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($reviews as $idx => $review)
                            @php($u = $review->customer?->user)
                            <article x-show="window.innerWidth >= 768 || i === {{ $idx }}"
                                     class="bg-white rounded-3xl p-6 shadow-coquette text-center">
                                <img src="{{ $u?->avatar_url ?? 'https://i.pravatar.cc/100?u='.urlencode((string) $review->id) }}"
                                     alt="" class="w-14 h-14 rounded-full mx-auto object-cover">
                                <div class="flex items-center justify-center gap-0.5 text-amber-400 text-sm mt-3">
                                    @for ($s = 1; $s <= 5; $s++){{ $s <= $review->rating ? '★' : '☆' }}@endfor
                                </div>
                                <p class="text-xs text-slate-600 mt-3 leading-relaxed line-clamp-4 italic">"{{ $review->content }}"</p>
                                <p class="text-sm font-semibold text-[#202020] mt-3 font-serif-eb">— {{ $u->full_name ?? 'Customer' }}</p>
                            </article>
                        @endforeach
                    </div>

                    {{-- Next arrow --}}
                    <button type="button" @click="i = (i + 1) % total"
                            class="hidden sm:inline-flex w-10 h-10 items-center justify-center rounded-full bg-white border border-[#FFE5DD] hover:bg-[#FFF4F2] shadow-sm shrink-0">
                        <img src="{{ asset('images/icons/icon-arrow-east-black.svg') }}" alt="next" class="w-4 h-4">
                    </button>
                </div>
            </section>
        @endif
    </main>

</x-layouts.landing>
