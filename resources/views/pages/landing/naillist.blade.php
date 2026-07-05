<x-layouts.landing title="Profil Nail Artist">

    {{-- Google Fonts: Poppins (sans) & Playfair Display (serif) --}}
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

        {{-- Header --}}
        <header class="text-center pt-10 md:pt-14 mb-10">
            <h1 class="text-3xl md:text-4xl font-serif font-semibold text-gray-900 mb-2">
                Profil Nail Artist
            </h1>
            <p class="text-[#C17A8E] text-[15px]">These are the people behind our impressive designs.</p>
        </header>

        {{-- Grid cards --}}
        <main class="max-w-4xl mx-auto">
            @if ($nailists->isEmpty())
                <div class="text-center py-16">
                    <p class="text-gray-500">Belum ada nailist tersedia.</p>
                </div>
            @else
                <div class="grid grid-cols-3 gap-5 md:gap-6">
                    @foreach ($nailists as $nailist)
                        @php
                            $u = $nailist->user;
                            $specialties = $nailist->specialties->pluck('name');
                            $primarySpecialty = $specialties->first();
                            $extraCount = max(0, $specialties->count() - 1);
                        @endphp
                        <a href="{{ route('naillist.show', $nailist) }}"
                           class="group block bg-card-bg rounded-2xl overflow-hidden shadow-[0_12px_30px_-8px_rgba(213,107,141,0.30),0_4px_12px_-4px_rgba(0,0,0,0.06)] hover:shadow-[0_20px_44px_-10px_rgba(213,107,141,0.45),0_6px_16px_-4px_rgba(0,0,0,0.10)] hover:-translate-y-1 transition-all duration-300">
                            {{-- Photo: square --}}
                            <div class="aspect-square overflow-hidden bg-[#FCEAEE]">
                                <img src="{{ $u?->avatar_url }}" alt="{{ $u?->full_name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>

                            {{-- Info --}}
                            <div class="p-4 text-center">
                                <p class="font-serif text-lg font-semibold text-gray-900 truncate">
                                    {{ $u?->full_name ?? '—' }}
                                </p>

                                @if ($primarySpecialty)
                                    <div class="mt-2 flex flex-wrap items-center justify-center gap-1.5">
                                        <span class="inline-flex items-center rounded-full bg-[#FBD6DF] px-2.5 py-1 text-[11px] font-medium text-[#A23F66]">
                                            {{ $primarySpecialty }}
                                        </span>
                                        @if ($extraCount > 0)
                                            <span class="inline-flex items-center rounded-full bg-[#FFF0F4] px-2.5 py-1 text-[11px] font-medium text-[#A23F66]">
                                                +{{ $extraCount }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <p class="mt-2 text-[12px] text-gray-400">No specialty</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </main>
    </section>
</x-layouts.landing>
