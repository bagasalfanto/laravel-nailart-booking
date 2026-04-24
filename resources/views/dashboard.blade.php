<x-app-layout>
    <x-slot name="header">Studio Activity Overview</x-slot>

    @php
        $todayStats = $todayStats ?? [];
        $bookingStatus = $bookingStatus ?? [];
        $upNextData = $upNextData ?? [
            'client' => 'No upcoming client',
            'service' => 'No service scheduled',
            'time' => '-',
            'price' => 'Rp 0',
            'avatar' => null,
        ];
        $displayName = Auth::user()->full_name ?? Auth::user()->username ?? Auth::user()->email;
    @endphp

    <div class="max-w-7xl mx-auto">
        <div class="flex items-start justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Good morning, {{ $displayName }}</h1>
                <p class="text-slate-500 mt-1">Here's what's happening at the studio today.</p>
            </div>
            <p class="hidden md:block text-sm tracking-wide text-slate-500 uppercase">{{ $dateLabel ?? now()->format('l, M d') }}</p>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-8">
            @foreach ($todayStats as $stat)
                <article class="relative rounded-2xl bg-white/80 border border-[#eedde2] p-5 overflow-hidden">
                    @if (!empty($stat['highlight']))
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#a23f66]"></div>
                    @endif
                    <div class="absolute -top-8 -right-6 h-24 w-24 rounded-full bg-[#f3e6ea]"></div>
                    <p class="text-slate-600 text-sm">{{ $stat['label'] }}</p>
                    <p class="mt-4 text-4xl leading-none font-semibold text-slate-900">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm {{ $stat['accent'] }}">{{ $stat['meta'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-semibold text-slate-900">Booking Status</h2>
                    <a href="#" class="text-sm text-[#a23f66] hover:underline">View All</a>
                </div>

                <div class="space-y-3">
                    @foreach ($bookingStatus as $index => $status)
                        @php
                            $barColors = [
                                'bg-[#ea7c9d]',
                                'bg-[#d96b8d]',
                                'bg-emerald-400',
                            ];
                            $barColor = $barColors[$index] ?? 'bg-slate-300';
                        @endphp
                        <article class="bg-white/85 border border-[#eedde2] rounded-2xl p-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <span class="h-12 w-1 rounded-full {{ $barColor }}"></span>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $status['title'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $status['subtitle'] }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="h-8 min-w-8 px-2 inline-flex items-center justify-center rounded-full bg-[#f4dde4] text-[#a23f66] text-sm font-semibold">
                                    {{ $status['count'] }}
                                </span>
                                <button class="h-8 w-8 rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100">&rsaquo;</button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-2xl bg-white/85 border border-[#eedde2] p-5">
                    <p class="text-xs font-medium tracking-wider text-[#a23f66] uppercase">Up Next</p>

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
