<x-app-layout>
    <x-slot name="header">Nailist Dashboard</x-slot>

    @php
        $displayName = explode(' ', trim((string) (Auth::user()->full_name ?? Auth::user()->username ?? 'Nailist')))[0];
        $iconMap = [
            'clients'  => '<svg class="w-5 h-5 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
            'designs'  => '<svg class="w-5 h-5 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l7-7 3 3-7 7-3-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>',
            'earnings' => '<svg class="w-5 h-5 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="13" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2"/></svg>',
        ];
    @endphp

    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-semibold text-slate-900">Good morning, {{ $displayName }}</h1>
                <p class="text-slate-500 mt-1">Here's what's happening at the studio today.</p>
            </div>
            <p class="hidden md:block text-xs tracking-widest text-slate-400 uppercase mt-2">{{ $dateLabel }}</p>
        </div>

        {{-- Top stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            @foreach ($todayStats as $stat)
                <article class="relative rounded-2xl bg-white border border-[#eedde2] p-5 overflow-hidden">
                    @if (!empty($stat['highlight']))
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#a23f66]"></div>
                    @endif
                    <div class="absolute -top-10 -right-8 h-28 w-28 rounded-full bg-[#fbe9ee] opacity-70"></div>

                    <div class="relative flex items-center gap-2 text-slate-700">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#fdf2f5]">
                            {!! $iconMap[$stat['icon']] ?? '' !!}
                        </span>
                        <p class="text-sm font-medium">{{ $stat['label'] }}</p>
                    </div>

                    <p class="relative mt-4 text-4xl leading-none font-semibold {{ !empty($stat['highlight']) ? 'text-[#a23f66]' : 'text-slate-900' }}">
                        {{ $stat['value'] }}
                    </p>
                    <p class="relative mt-2 text-xs {{ $stat['accent'] }}">{{ $stat['meta'] }}</p>
                </article>
            @endforeach
        </div>

        {{-- Booking Status + Up Next --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-slate-900">Booking Status</h2>
                    <a href="#" class="text-sm text-[#a23f66] hover:underline">View All</a>
                </div>

                <div class="space-y-3">
                    @foreach ($bookingStatus as $status)
                        <article class="bg-white border border-[#eedde2] rounded-2xl p-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <span class="h-12 w-1 rounded-full {{ $status['color'] }}"></span>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $status['title'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $status['subtitle'] }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                @php
                                    $countBg = str_contains($status['color'], 'emerald')
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-[#f4dde4] text-[#a23f66]';
                                @endphp
                                <span class="h-8 min-w-8 px-2 inline-flex items-center justify-center rounded-full {{ $countBg }} text-sm font-semibold">
                                    {{ $status['count'] }}
                                </span>
                                <button class="h-8 w-8 rounded-full text-slate-400 hover:bg-slate-100">&rsaquo;</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-2xl bg-white border border-[#eedde2] p-5">
                    <p class="text-[10px] font-semibold tracking-widest text-[#a23f66] uppercase">Up Next</p>

                    <div class="mt-4 flex items-center gap-3">
                        <img src="{{ $upNextData['avatar'] ?: 'https://i.pravatar.cc/100?u=up-next' }}" alt="{{ $upNextData['client'] }}" class="h-12 w-12 rounded-full object-cover" />
                        <div>
                            <p class="font-semibold text-slate-900">{{ $upNextData['client'] }}</p>
                            <p class="text-sm text-slate-500">{{ $upNextData['service'] }}</p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl bg-[#f3e8eb] px-3 py-3 flex items-center justify-between text-sm">
                        <span class="text-slate-600">{{ $upNextData['time'] }}</span>
                        <span class="font-semibold text-[#a23f66]">{{ $upNextData['price'] }}</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
