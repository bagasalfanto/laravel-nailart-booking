<x-app-layout>
    <x-slot name="header">Specialties</x-slot>

    <div class="max-w-5xl mx-auto" x-data="specialtyPage()">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Specialties</h1>
                <p class="text-slate-500 mt-1">Daftar pilihan specialty untuk profile nailist.</p>
            </div>
            <div class="flex items-center gap-3">
                <x-export-dropdown
                    csv-url="{{ route('dashboard.specialties.export', request()->query()) }}"
                    pdf-url="{{ route('dashboard.specialties.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                />
                @can('specialty.create')
                <button type="button" @click="openCreate"
                        class="inline-flex items-center gap-2 rounded-full bg-[#a23f66] px-4 py-2.5 text-sm font-medium text-white hover:opacity-90">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>
                    Tambah Specialty
                </button>
            @endcan
        </div>
        </div>


        {{-- Filter --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] p-4 mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-500 mb-1">Cari nama</label>
                <input type="text" id="f_name" placeholder="Filter nama specialty…"
                       class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Status</label>
                <select id="f_status" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                    <option value="">Semua</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden">
            <div class="overflow-x-auto dt-yajra-wrap">
                <table id="specialtiesTable" class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th>Nama</th>
                            <th>Slug</th>
                            <th>Dipakai</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        {{-- Modal Form (Create/Edit) --}}
        <div x-show="modalOpen" x-cloak x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
             @keydown.escape.window="modalOpen = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.outside="modalOpen = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-[#f1e4e8]">
                    <h3 class="text-lg font-semibold text-slate-900" x-text="mode === 'create' ? 'Tambah Specialty' : 'Edit Specialty'"></h3>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nama <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="form.name" required maxlength="80"
                               class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-rose-600"></p>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" x-model="form.is_active" class="rounded border-slate-300 text-[#a23f66] focus:ring-[#a23f66]">
                        Aktif
                    </label>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="modalOpen = false" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" :disabled="loading"
                                class="rounded-lg bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90 disabled:opacity-50">
                            <span x-text="loading ? 'Menyimpan…' : 'Simpan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('partials.yajra-assets')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }

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

        function specialtyPage() {
            return {
                modalOpen: false,
                mode: 'create',
                editingId: null,
                loading: false,
                form: { name: '', is_active: true },
                errors: {},

                openCreate() {
                    this.mode = 'create';
                    this.editingId = null;
                    this.form = { name: '', is_active: true };
                    this.errors = {};
                    this.modalOpen = true;
                },

                openEdit(id, name, active) {
                    this.mode = 'edit';
                    this.editingId = id;
                    this.form = { name, is_active: active };
                    this.errors = {};
                    this.modalOpen = true;
                },

                async submit() {
                    this.errors = {};
                    this.loading = true;
                    const url = this.mode === 'create'
                        ? @json(route('dashboard.specialties.store'))
                        : @json(url('dashboard/specialties')) + '/' + this.editingId;
                    const method = this.mode === 'create' ? 'POST' : 'PUT';

                    try {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ name: this.form.name, is_active: this.form.is_active ? 1 : 0 }),
                        });
                        const json = await res.json();
                        if (!res.ok) {
                            if (res.status === 422 && json.errors) {
                                Object.entries(json.errors).forEach(([k, v]) => { this.errors[k] = v[0]; });
                            } else {
                                window.toast('error', 'Gagal', json.message || 'Terjadi kesalahan.');
                            }
                            return;
                        }
                        this.modalOpen = false;
                        if (window.specialtiesTable) window.specialtiesTable.ajax.reload(null, false);
                        const title = this.mode === 'create' ? 'Specialty Ditambahkan' : 'Perubahan Disimpan';
                        window.toast?.('success', title, json.message);
                    } catch (e) {
                        window.toast('error', 'Network Error', e.message);
                    } finally {
                        this.loading = false;
                    }
                },
            }
        }

        $(function () {
            const table = $('#specialtiesTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'asc']],
                ajax: {
                    url: @json(route('dashboard.specialties.data')),
                    data: function (d) {
                        d.f_name   = $('#f_name').val();
                        d.f_status = $('#f_status').val();
                    },
                    error: function (xhr) {
                        const table = $(this).DataTable();
                        table.processing(false);
                        if (xhr.status === 401) { window.location.reload(); return; }
                        window.toast?.('error', 'Gagal Memuat', 'Server error — coba refresh halaman.');
                    },
                },
                columns: [
                    { data: 'name_label',     name: 'name' },
                    { data: 'slug',           name: 'slug' },
                    { data: 'nailists_label', name: 'nailists_count', orderable: false, searchable: false },
                    { data: 'status_badge',   name: 'is_active' },
                    { data: 'actions',        name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' },
                ],
                language: {
                    search: 'Cari global:',
                    lengthMenu: 'Tampilkan _MENU_ entri',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ entri',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Tidak ada specialty yang cocok.',
                    processing: '<span class="text-slate-500">Memuat…</span>',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });
            window.specialtiesTable = table;

            let timer;
            $('#f_name').on('keyup', () => { clearTimeout(timer); timer = setTimeout(() => table.ajax.reload(null, false), 300); });
            $('#f_status').on('change', () => table.ajax.reload(null, false));

            // Edit
            $('#specialtiesTable tbody').on('click', '[data-edit-id]', function () {
                // Cari instance Alpine pada container utama dan panggil openEdit().
                const root = document.querySelector('[x-data="specialtyPage()"]');
                if (root && Alpine.$data(root)) {
                    const data = Alpine.$data(root);
                    data.openEdit(this.dataset.editId, this.dataset.name, this.dataset.active === '1');
                }
            });

            // Delete
            $('#specialtiesTable tbody').on('click', '[data-delete-id]', async function () {
                const id = this.dataset.deleteId;
                const name = this.dataset.name;
                const result = await Swal.fire({
                    title: 'Hapus Specialty?',
                    html: `<b>${name}</b> akan dihapus permanen.`,
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
                    const res = await fetch(@json(url('dashboard/specialties')) + '/' + id, {
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
