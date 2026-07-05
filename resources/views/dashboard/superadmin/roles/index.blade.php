<x-app-layout>
    <x-slot name="header">Role Management</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Roles</h1>
                <p class="text-slate-500 mt-1">Kelola role dan permission-nya.</p>
            </div>
            <div class="flex items-center gap-3">
                <x-export-dropdown
                    csv-url="{{ route('dashboard.roles.export', request()->query()) }}"
                    pdf-url="{{ route('dashboard.roles.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                />
                <a href="{{ route('dashboard.roles.create') }}" class="rounded-lg bg-[#a23f66] px-4 py-2 text-sm font-medium text-white hover:opacity-90">+ Role Baru</a>
            </div>
        </div>

        {{-- Filter --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] p-4 mb-4">
            <label class="block text-xs text-slate-500 mb-1">Cari Role</label>
            <input type="text" id="f_name" placeholder="Filter nama role…"
                   class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
        </div>

        {{-- Tabel --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden">
            <div class="overflow-x-auto dt-yajra-wrap">
                <table id="rolesTable" class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th>Role</th>
                            <th>Permissions</th>
                            <th>Users</th>
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
            const table = $('#rolesTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'asc']],
                ajax: {
                    url: @json(route('dashboard.roles.data')),
                    data: function (d) { d.f_name = $('#f_name').val(); },
                    error: function (xhr) {
                        const table = $(this).DataTable();
                        table.processing(false);
                        if (xhr.status === 401) { window.location.reload(); return; }
                        window.toast?.('error', 'Gagal Memuat', 'Server error — coba refresh halaman.');
                    },
                },
                    error: function (xhr) {
                        const table = $(this).DataTable();
                        table.processing(false);
                        if (xhr.status === 401) { window.location.reload(); return; }
                        window.toast?.('error', 'Gagal Memuat', 'Server error — coba refresh halaman.');
                    },
                columns: [
                    { data: 'name_label',       name: 'name' },
                    { data: 'permissions_count',name: 'permissions_count', searchable: false },
                    { data: 'users_count',      name: 'users_count', searchable: false },
                    { data: 'actions',          name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' },
                ],
                language: {
                    search: 'Cari global:',
                    lengthMenu: 'Tampilkan _MENU_ entri',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ entri',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Tidak ada role yang cocok.',
                    processing: '<span class="text-slate-500">Memuat…</span>',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });

            let timer;
            $('#f_name').on('keyup', () => { clearTimeout(timer); timer = setTimeout(() => table.ajax.reload(null, false), 300); });

            $('#rolesTable tbody').on('click', '[data-delete-id]', async function () {
                const id = this.dataset.deleteId;
                const name = this.dataset.name;
                const result = await Swal.fire({
                    title: 'Hapus Role?',
                    html: `Role <b>${name}</b> akan dihapus permanen.`,
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
                    const res = await fetch(@json(url('dashboard/roles')) + '/' + id, {
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
