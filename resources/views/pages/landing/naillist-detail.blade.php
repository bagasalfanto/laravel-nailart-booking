<x-layouts.landing title="{{ $nailist->user?->full_name ?? 'Nail Artist' }}">
    @php
        $u = $nailist->user;
        $totalClients = $nailist->total_clients ?? 0;
        $servedLabel = $totalClients > 0 ? "Served {$totalClients} clients" : 'New Artist';
        $portfolios = $nailist->portfolios;
        $totalPortfolio = $portfolios->count();
    @endphp

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        .nailist-page              { font-family: 'Poppins', sans-serif; color: #1f2937; }
        .nailist-page .font-sans   { font-family: 'Poppins', sans-serif; }
        .nailist-page .font-serif  { font-family: 'Playfair Display', serif; }
        .nailist-page .bg-brand-bg { background-color: #FFF4F2; }
        .nailist-page .bg-card-bg  { background-color: #FFFFFF; }
        .nailist-page .text-brand-pink { color: #D56B8D; }
        body { background-color: #FFF4F2 !important; }
    </style>

    <section class="nailist-page font-sans text-gray-800 min-h-screen pb-20 px-4 md:px-8">

        {{-- Title --}}
        <header class="text-center pt-10 md:pt-14">
            <h1 class="text-2xl md:text-3xl font-serif font-semibold text-gray-900">
                Profil Nail Artist
            </h1>
        </header>

        {{-- Profile section --}}
        <section class="max-w-4xl mx-auto mt-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">

                {{-- Photo card (rotated) --}}
                <div class="flex justify-center md:justify-end">
                    <div class="relative">
                        <div class="bg-white rounded-2xl p-3 shadow-[0_10px_30px_-10px_rgba(0,0,0,0.12)] rotate-[-5deg]">
                            <img src="{{ $u?->avatar_url }}" alt="{{ $u?->full_name }}"
                                 class="w-60 h-72 md:w-64 md:h-80 object-cover rounded-xl">
                        </div>
                    </div>
                </div>

                {{-- Info --}}
                <div class="text-center md:text-left">
                    <h2 class="text-3xl md:text-4xl font-serif font-semibold text-gray-900 mb-4">
                        {{ $u?->full_name ?? '—' }}
                    </h2>

                    <ul class="space-y-2 text-gray-700 text-[14px]">
                        <li class="inline-flex items-center justify-center md:justify-start gap-2 w-full">
                            <svg class="w-4 h-4 text-brand-pink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>{{ $servedLabel }}</span>
                        </li>
                        <li class="inline-flex items-center justify-center md:justify-start gap-2 w-full">
                            <svg class="w-4 h-4 text-brand-pink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21"/></svg>
                            <span>{{ $totalPortfolio }} Works Served</span>
                        </li>
                        @if ($nailist->instagram_handle)
                            <li class="inline-flex items-center justify-center md:justify-start gap-2 w-full">
                                <svg class="w-4 h-4 text-brand-pink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="3" width="18" height="18" rx="5"/>
                                    <circle cx="12" cy="12" r="4"/>
                                    <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/>
                                </svg>
                                <a href="{{ $nailist->instagram_url }}" target="_blank" rel="noopener" class="hover:text-brand-pink">{{ $nailist->instagram_handle }}</a>
                            </li>
                        @endif
                    </ul>

                    {{-- Specialty --}}
                    @if ($nailist->specialties->isNotEmpty())
                        <div class="mt-6">
                            <p class="text-xl font-serif font-semibold text-gray-900 mb-3">Specialty</p>
                            <div class="flex flex-wrap justify-center md:justify-start gap-2">
                                @foreach ($nailist->specialties as $sp)
                                    <span class="inline-flex items-center rounded-full bg-[#FBD6DF] px-4 py-1.5 text-[13px] font-medium text-[#A23F66]">
                                        {{ $sp->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Portfolio section --}}
        <section class="max-w-4xl mx-auto mt-14">
            <h3 class="text-center text-2xl md:text-3xl font-serif font-semibold text-gray-900 mb-6">
                {{ $u?->full_name }}'s Portfolio
            </h3>

            @if ($portfolios->isEmpty())
                <div class="bg-card-bg rounded-2xl py-12 text-center">
                    <p class="text-sm text-gray-400">Belum ada karya yang diunggah.</p>
                </div>
            @else
                <div x-data="{ open: false, src: '', title: '', desc: '', shown: 9 }">
                    <div class="grid grid-cols-3 gap-2 md:gap-3">
                        @foreach ($portfolios as $i => $p)
                            <figure x-show="{{ $i }} < shown"
                                    class="aspect-square overflow-hidden rounded-xl bg-white cursor-pointer group"
                                    @click="open = true; src = '{{ $p->gambar_full_url }}'; title = @js($p->judul); desc = @js($p->deskripsi)">
                                <img src="{{ $p->gambar_full_url }}" alt="{{ $p->judul }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </figure>
                        @endforeach
                    </div>

                    {{-- Load More --}}
                    @if ($totalPortfolio > 9)
                        <div class="mt-6 text-center" x-show="shown < {{ $totalPortfolio }}">
                            <button type="button" @click="shown += 9"
                                    class="rounded-full border border-[#eedde2] bg-card-bg px-8 py-2.5 text-sm font-medium text-gray-600 hover:bg-[#FFF5F6] hover:text-brand-pink transition-colors">
                                Load More
                            </button>
                        </div>
                    @endif

                    {{-- Lightbox --}}
                    <div x-show="open" x-cloak x-transition.opacity
                         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4"
                         @click="open = false"
                         @keydown.escape.window="open = false">
                        <div class="relative max-w-3xl w-full" @click.stop>
                            <button type="button" @click="open = false"
                                    class="absolute -top-10 right-0 text-white hover:text-rose-300">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <img :src="src" alt="" class="w-full max-h-[80vh] object-contain rounded-2xl bg-white">
                            <div class="mt-3 text-white text-center" x-show="title">
                                <p class="font-semibold text-lg" x-text="title"></p>
                                <p class="text-sm text-white/70 mt-1" x-text="desc"></p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- Testimonials --}}
        @if ($reviews->isNotEmpty())
            <section class="max-w-5xl mx-auto mt-16">
                <h3 class="text-center text-2xl md:text-3xl font-serif font-semibold text-gray-900 mb-8">
                    What Our Clients Say About {{ $u?->full_name }}
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($reviews->take(3) as $review)
                        @php($cu = $review->customer?->user)
                        <article class="bg-card-bg rounded-2xl p-5 shadow-[0_4px_15px_-6px_rgba(213,107,141,0.18)] text-center">
                            <img src="{{ $cu?->avatar_url ?? 'https://i.pravatar.cc/80?u='.urlencode((string) $review->id) }}"
                                 alt="" class="w-12 h-12 rounded-full mx-auto object-cover">
                            <div class="flex items-center justify-center gap-1 text-amber-400 text-sm mt-3">
                                @for ($i = 1; $i <= 5; $i++){{ $i <= $review->rating ? '★' : '☆' }}@endfor
                            </div>
                            <p class="text-xs text-gray-600 mt-3 leading-relaxed line-clamp-5">{{ $review->content }}</p>
                            <p class="text-xs font-semibold text-gray-700 mt-3">{{ $cu?->full_name ?? 'Customer' }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </section>
</x-layouts.landing>
