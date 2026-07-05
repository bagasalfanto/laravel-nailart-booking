<x-app-layout>
    <x-slot name="header">Portofolio Moderation</x-slot>

    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Portfolio Moderation</h1>
                <p class="text-slate-500 mt-1">Pilih portfolio yang ditampilkan di halaman home (homepage gallery).</p>
            </div>
            <div class="flex items-center gap-3">
                <x-export-dropdown
                    csv-url="{{ route('dashboard.portfolio-moderation.export', request()->query()) }}"
                    pdf-url="{{ route('dashboard.portfolio-moderation.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                />
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="rounded-2xl bg-white border border-[#eedde2] p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#fdf2f5] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Total Portfolio</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
            <div class="rounded-2xl bg-white border border-[#eedde2] p-5 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2l2.5 5 5.5.8-4 3.9 1 5.5L10 14.7 4.9 17.2l1-5.5L2 7.8l5.5-.8L10 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Featured (di Home)</p>
                    <p class="text-2xl font-semibold text-slate-900">{{ number_format($stats['featured']) }}</p>
                </div>
            </div>
        </div>

        {{-- Tabs filter status --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            @php($tabs = ['all' => 'Semua', 'featured' => 'Featured', 'not_featured' => 'Belum Featured'])
            @foreach ($tabs as $key => $label)
                <button type="button" data-filter="{{ $key === 'all' ? '' : $key }}"
                        class="filter-tab rounded-full px-4 py-1.5 text-sm font-medium border bg-white text-slate-600 border-[#eedde2] hover:bg-[#fdf2f5]"
                        data-active="{{ $key === 'all' ? '1' : '0' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Filter toolbar --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-5">
                    <label class="block text-xs text-slate-500 mb-1">Nailist</label>
                    <select id="f_nailist" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        <option value="">Semua Nailist</option>
                        @foreach ($nailists as $n)
                            <option value="{{ $n['id'] }}">{{ $n['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs text-slate-500 mb-1">Judul</label>
                    <input type="text" id="f_judul" placeholder="Cari judul…"
                           class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="button" id="resetFilter"
                            class="w-full rounded-lg border border-[#eedde2] px-3 py-1.5 text-sm text-slate-600 hover:bg-[#fdf2f5]">Reset</button>
                </div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden">
            <div class="overflow-x-auto dt-yajra-wrap">
                <table id="portfolioTable" class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Nailist</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @include('partials.yajra-assets')

    <style>
        .filter-tab[data-active="1"] { background: #a23f66; color: #fff; border-color: #a23f66; }

        /* 1. Mencegat DataTables versi 1.x (Select di dalam Label) */
        .dataTables_length label {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            white-space: nowrap !important;
            width: max-content !important; /* Paksa agar tidak menyusut turun */
        }

        /* 2. Mencegat DataTables versi 2.x (Select dan Label bersebelahan) */
        .dt-length {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            white-space: nowrap !important;
            width: max-content !important;
        }
        .dt-length label { margin: 0 !important; }

        /* 3. Paksa ukuran kotak dropdown dan ikon panahnya agar lega */
        .dataTables_length select,
        .dt-length select {
            width: 75px !important;
            min-width: 75px !important;
            padding: 4px 30px 4px 12px !important; /* Padding kanan 30px khusus untuk panah */
            background-position: right 8px center !important; /* Dorong panah ke ujung kanan */
            border-radius: 6px !important;
            box-sizing: border-box !important;
        }
    </style>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        $(function () {
            let activeFilter = '';

            const table = $('#portfolioTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [],
                ajax: {
                    url: @json(route('dashboard.portfolio-moderation.data')),
                    data: function (d) {
                        d.f_filter  = activeFilter;
                        d.f_nailist = $('#f_nailist').val();
                        d.f_judul   = $('#f_judul').val();
                    },
                    error: function (xhr) {
                        const table = $(this).DataTable();
                        table.processing(false);
                        if (xhr.status === 401) { window.location.reload(); return; }
                        window.toast?.('error', 'Gagal Memuat', 'Server error — coba refresh halaman.');
                    },
                },
                columns: [
                    { data: 'thumb',        name: 'gambar',              orderable: false, searchable: false },
                    { data: 'judul_label',  name: 'judul' },
                    { data: 'nailist_name', name: 'nailist.user.full_name', orderable: false },
                    { data: 'status_badge', name: 'is_featured' },
                    { data: 'created',      name: 'created_at' },
                    { data: 'actions',      name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' },
                ],
                language: {
                    search: 'Cari global:',
                    lengthMenu: 'Tampilkan _MENU_ entri',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ entri',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Tidak ada portfolio yang cocok.',
                    processing: '<span class="text-slate-500">Memuat…</span>',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });

            // Tabs filter
            $('.filter-tab').on('click', function () {
                $('.filter-tab').attr('data-active', '0');
                $(this).attr('data-active', '1');
                activeFilter = this.dataset.filter || '';
                table.ajax.reload(null, false);
            });

            let timer;
            const reloadDebounced = () => { clearTimeout(timer); timer = setTimeout(() => table.ajax.reload(null, false), 300); };
            $('#f_judul').on('keyup', reloadDebounced);
            $('#f_nailist').on('change', () => table.ajax.reload(null, false));
            $('#resetFilter').on('click', () => {
                $('#f_judul').val('');
                $('#f_nailist').val('');
                $('.filter-tab').attr('data-active', '0');
                $('.filter-tab[data-filter=""]').attr('data-active', '1');
                activeFilter = '';
                table.ajax.reload(null, false);
            });

            // Action: toggle feature
            $('#portfolioTable tbody').on('click', '[data-feature-id]', async function () {
                const id = this.dataset.featureId;
                this.disabled = true;
                try {
                    const res = await fetch(@json(url('dashboard/portfolio-moderation')) + '/' + id + '/toggle-feature', {
                        method: 'PATCH',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) {
                        window.toast?.('error', 'Gagal', json.message || 'Terjadi kesalahan.');
                        return;
                    }
                    table.ajax.reload(null, false);
                    window.toast?.('success', json.is_featured ? 'Ditampilkan di Home' : 'Dilepas dari Home', json.message);
                } catch (e) {
                    window.toast?.('error', 'Network Error', e.message);
                } finally {
                    this.disabled = false;
                }
            });
        });
    </script>
</x-app-layout>
