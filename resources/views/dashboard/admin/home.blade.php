<x-app-layout>
    <x-slot name="header">Overview</x-slot>

    @php
        $maxMonthly = max(array_map(fn ($m) => $m['count'], $monthlyTrend)) ?: 1;
        $maxWeekly  = max(array_map(fn ($w) => $w['count'], $weeklyTrend))  ?: 1;
        $diff = round($revenueDiffPercent, 1);
        $diffPositive = $diff >= 0;

        $rupiahFull = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
        $rupiahShort = function ($n) {
            if ($n >= 1_000_000) return 'Rp '.rtrim(rtrim(number_format($n / 1_000_000, 1, '.', ''), '0'), '.').'M';
            if ($n >= 1_000)     return 'Rp '.rtrim(rtrim(number_format($n / 1_000, 0, '.', ''), '0'), '.').'k';
            return 'Rp '.number_format((float) $n, 0, ',', '.');
        };
    @endphp

    <div class="max-w-7xl mx-auto" x-data="{ trend: 'monthly' }">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-semibold text-slate-900">Atelier Overview</h1>
                <p class="text-slate-500 mt-1">High-level metrics and performance monitoring.</p>
            </div>
            <button class="rounded-full bg-[#D56B8D] hover:bg-[#c45a7d] px-5 py-2.5 text-sm font-medium text-white shadow-md shadow-pink-200/60 transition-all">
                Generate Report
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- LEFT: Total Revenue + Booking Trend --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Total Revenue card --}}
                <article class="rounded-2xl bg-white border border-[#f5e0e6] p-6 shadow-[0_4px_18px_-10px_rgba(213,107,141,0.18)]">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-[#FFF0F4]">
                                <svg class="w-5 h-5 text-[#D56B8D]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="6" width="18" height="13" rx="2"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h2"/>
                                </svg>
                            </span>
                            <p class="text-base font-medium text-slate-700">Total Revenue</p>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full {{ $diffPositive ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} px-2.5 py-1 text-xs font-semibold">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $diffPositive ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                            </svg>
                            {{ $diffPositive ? '+' : '' }}{{ $diff }}%
                        </span>
                    </div>

                    <p class="mt-5 text-4xl md:text-5xl font-bold text-slate-900 tracking-tight">{{ $rupiahFull($totalRevenue) }}</p>
                    <p class="mt-2 text-xs text-slate-500">vs last month ({{ $rupiahShort($lastMonthRevenue) }})</p>
                </article>

                {{-- Booking Trend chart --}}
                <article class="rounded-2xl bg-white border border-[#f5e0e6] p-6 shadow-[0_4px_18px_-10px_rgba(213,107,141,0.18)]">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-slate-900">Booking Trend</h2>
                        <div class="inline-flex rounded-full bg-[#FFF0F4] p-1 text-xs font-medium">
                            <button type="button" data-trend-tab="weekly" @click="trend = 'weekly'"
                                    :class="trend === 'weekly' ? 'bg-[#D56B8D] text-white shadow-sm' : 'text-slate-500'"
                                    class="px-3 py-1.5 rounded-full transition">Weekly</button>
                            <button type="button" data-trend-tab="monthly" @click="trend = 'monthly'"
                                    :class="trend === 'monthly' ? 'bg-[#D56B8D] text-white shadow-sm' : 'text-slate-500'"
                                    class="px-3 py-1.5 rounded-full transition">Monthly</button>
                        </div>
                    </div>

                    <div class="relative" style="height: 280px;">
                        <canvas id="bookingTrendChart"></canvas>
                    </div>
                </article>
            </div>

            {{-- RIGHT: Metric cards --}}
            <div class="lg:col-span-1 space-y-4">
                @php
                    $metrics = [
                        [
                            'label'    => 'Total Booking',
                            'value'    => number_format($totalBooking),
                            'subtitle' => 'This month',
                            'icon'     => '<rect x="3" y="5" width="18" height="16" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 3v4M8 3v4M3 10h18"/>',
                        ],
                        [
                            'label'    => 'Total Customer',
                            'value'    => number_format($totalCustomer),
                            'subtitle' => 'Active accounts',
                            'icon'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/>',
                        ],
                        [
                            'label'       => 'Total Nailist',
                            'value'       => number_format($totalNailist),
                            'subtitle'    => $activeNailistsToday.' Active today',
                            'subtitleDot' => true,
                            'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/>',
                        ],
                        [
                            'label'    => 'Total User',
                            'value'    => number_format($totalUser),
                            'subtitle' => 'System administrators & staff',
                            'icon'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 11c2.21 0 4-1.79 4-4S14.21 3 12 3 8 4.79 8 7s1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4z"/>',
                        ],
                    ];
                @endphp

                @foreach ($metrics as $m)
                    <article class="rounded-2xl bg-white border border-[#f5e0e6] p-5 shadow-[0_4px_18px_-10px_rgba(213,107,141,0.18)]">
                        <div class="flex items-center gap-2 text-slate-700">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#FFF0F4]">
                                <svg class="w-4 h-4 text-[#D56B8D]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    {!! $m['icon'] !!}
                                </svg>
                            </span>
                            <p class="text-sm font-medium">{{ $m['label'] }}</p>
                        </div>
                        <p class="mt-3 text-3xl font-bold text-slate-900">{{ $m['value'] }}</p>
                        <p class="mt-1 text-xs text-slate-500 flex items-center gap-1.5">
                            @if (!empty($m['subtitleDot']))
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            @endif
                            {{ $m['subtitle'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>

    <style>[x-cloak]{display:none !important}</style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const datasets = {
                monthly: @json($monthlyTrend),
                weekly:  @json($weeklyTrend),
            };

            const ctx = document.getElementById('bookingTrendChart').getContext('2d');

            const buildBackgroundColors = (data) => {
                const max = Math.max(...data.map(d => d.count));
                return data.map(d => d.count === max && max > 0 ? '#D56B8D' : '#F5C8D4');
            };

            const initial = datasets.monthly;
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: initial.map(d => d.label),
                    datasets: [{
                        label: 'Bookings',
                        data: initial.map(d => d.count),
                        backgroundColor: buildBackgroundColors(initial),
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 48,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 600 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#D56B8D',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                title: (items) => items[0].label,
                                label: (item) => ` ${item.parsed.y} booking${item.parsed.y > 1 ? 's' : ''}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { color: '#94a3b8', font: { size: 12 } },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f5e0e6', drawBorder: false },
                            ticks: { color: '#94a3b8', font: { size: 11 }, precision: 0 },
                        },
                    },
                },
            });

            const switchTo = (key) => {
                const d = datasets[key];
                chart.data.labels = d.map(x => x.label);
                chart.data.datasets[0].data = d.map(x => x.count);
                chart.data.datasets[0].backgroundColor = buildBackgroundColors(d);
                chart.update();
            };

            document.querySelectorAll('[data-trend-tab]').forEach(btn => {
                btn.addEventListener('click', () => switchTo(btn.dataset.trendTab));
            });
        })();
    </script>
</x-app-layout>
