<x-app-layout>
    <x-slot name="header">Review Moderation</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Reviews</h1>
                <p class="text-slate-500 mt-1">Pilih review yang ingin ditampilkan di landing page.</p>
            </div>
            <x-export-dropdown
                csv-url="{{ route('dashboard.reviews.export', request()->query()) }}"
                pdf-url="{{ route('dashboard.reviews.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
            />
        </div>

        {{-- Tabs filter --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            @php($tabs = ['all' => 'Semua', 'featured' => 'Ditampilkan', 'pending' => 'Belum'])
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
                <div class="md:col-span-4">
                    <label class="block text-xs text-slate-500 mb-1">User (nama / email / username)</label>
                    <input type="text" id="f_user" placeholder="Cari user…"
                           class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">Rating</label>
                    <select id="f_rating" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        <option value="">Semua</option>
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ $i }} ★</option>
                        @endfor
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs text-slate-500 mb-1">Treatment</label>
                    <input type="text" id="f_treatment" placeholder="Filter treatment…"
                           class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs text-slate-500 mb-1">Isi review</label>
                    <input type="text" id="f_content" placeholder="Cari kata…"
                           class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                </div>
            </div>
            <div class="flex justify-end mt-3">
                <button type="button" id="resetFilter"
                        class="rounded-lg border border-[#eedde2] px-3 py-1.5 text-sm text-slate-600 hover:bg-[#fdf2f5]">Reset</button>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden">
            <div class="overflow-x-auto dt-yajra-wrap">
                <table id="reviewsTable" class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th>User</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Treatment</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @include('partials.yajra-assets')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

            const table = $('#reviewsTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [],
                ajax: {
                    url: @json(route('dashboard.reviews.data')),
                    data: function (d) {
                        d.f_filter    = activeFilter;
                        d.f_user      = $('#f_user').val();
                        d.f_rating    = $('#f_rating').val();
                        d.f_treatment = $('#f_treatment').val();
                        d.f_content   = $('#f_content').val();
                    },
                    error: function (xhr) {
                        const table = $(this).DataTable();
                        table.processing(false);
                        if (xhr.status === 401) { window.location.reload(); return; }
                        window.toast?.('error', 'Gagal Memuat', 'Server error — coba refresh halaman.');
                    },
                },
                columns: [
                    { data: 'user_label',     name: 'customer.user.full_name', orderable: false },
                    { data: 'rating_stars',   name: 'rating' },
                    { data: 'content_short',  name: 'content' },
                    { data: 'treatment_name', name: 'reservasi.treatment.nama_jasa', orderable: false },
                    { data: 'actions',        name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' },
                ],
                language: {
                    search: 'Cari global:',
                    lengthMenu: 'Tampilkan _MENU_ entri',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ entri',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Tidak ada review yang cocok.',
                    processing: '<span class="text-slate-500">Memuat…</span>',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });
            window.reviewsTable = table;

            // Tabs filter
            $('.filter-tab').on('click', function () {
                $('.filter-tab').attr('data-active', '0');
                $(this).attr('data-active', '1');
                activeFilter = this.dataset.filter || '';
                table.ajax.reload(null, false);
            });

            let timer;
            const reloadDebounced = () => { clearTimeout(timer); timer = setTimeout(() => table.ajax.reload(null, false), 300); };
            $('#f_user, #f_treatment, #f_content').on('keyup', reloadDebounced);
            $('#f_rating').on('change', () => table.ajax.reload(null, false));
            $('#resetFilter').on('click', () => {
                $('#f_user, #f_treatment, #f_content').val('');
                $('#f_rating').val('');
                $('.filter-tab').attr('data-active', '0');
                $('.filter-tab[data-filter=""]').attr('data-active', '1');
                activeFilter = '';
                table.ajax.reload(null, false);
            });

            // Action: toggle feature
            $('#reviewsTable tbody').on('click', '[data-feature-id]', async function () {
                const id = this.dataset.featureId;
                try {
                    const res = await fetch(@json(url('dashboard/reviews')) + '/' + id + '/feature', {
                        method: 'PATCH',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) {
                        window.toast('error', 'Gagal', json.message || 'Terjadi kesalahan.');
                        return;
                    }
                    table.ajax.reload(null, false);
                    window.toast?.('success', json.is_featured ? 'Review Ditampilkan' : 'Review Disembunyikan', json.message);
                } catch (e) {
                    window.toast('error', 'Network Error', e.message);
                }
            });

            // Action: delete
            $('#reviewsTable tbody').on('click', '[data-delete-id]', async function () {
                const id = this.dataset.deleteId;
                const result = await Swal.fire({
                    title: 'Hapus Review?',
                    text: 'Review akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Hapus',
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#94a3b8',
                    reverseButtons: true,
                });
                if (!result.isConfirmed) return;

                try {
                    const res = await fetch(@json(url('dashboard/reviews')) + '/' + id, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) {
                        window.toast('error', 'Gagal', json.message || 'Terjadi kesalahan.');
                        return;
                    }
                    table.ajax.reload(null, false);
                    window.toast?.('success', 'Penghapusan Berhasil', json.message);
                } catch (e) {
                    window.toast('error', 'Network Error', e.message);
                }
            });
        });
    </script>
</x-app-layout>
