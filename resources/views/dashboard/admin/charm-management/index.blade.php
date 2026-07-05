<x-app-layout>
    <x-slot name="header">Charm Management</x-slot>

    <div class="max-w-7xl mx-auto" x-data="charmPage()">
        {{-- Header --}}
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Charm Management</h1>
                <p class="text-slate-500 mt-1">Kelola katalog charm — nama, stok, dan harga.</p>
            </div>
            @can('charm.create')
                <button type="button" @click="openCreate()"
                        class="inline-flex items-center gap-2 rounded-full bg-[#a23f66] px-4 py-2.5 text-sm font-medium text-white hover:opacity-90 shadow-sm self-start md:self-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>
                    Tambah Charm
                </button>
            @endcan
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <article class="rounded-2xl bg-white border border-[#eedde2] p-5">
                <p class="text-xs uppercase text-slate-400 tracking-wide">Total Charm</p>
                <p class="text-3xl font-semibold text-slate-900 mt-2">{{ number_format($stats['total']) }}</p>
            </article>
            <article class="rounded-2xl bg-amber-50 border border-amber-200 p-5">
                <p class="text-xs uppercase text-amber-600 tracking-wide">Low Stock (≤5)</p>
                <p class="text-3xl font-semibold text-amber-700 mt-2">{{ number_format($stats['low_stock']) }}</p>
            </article>
            <article class="rounded-2xl bg-rose-50 border border-rose-200 p-5">
                <p class="text-xs uppercase text-rose-600 tracking-wide">Out of Stock</p>
                <p class="text-3xl font-semibold text-rose-700 mt-2">{{ number_format($stats['out_stock']) }}</p>
            </article>
        </div>

        {{-- Grid charms --}}
        @if ($charms->isEmpty())
            <div class="rounded-2xl bg-white border border-[#eedde2] py-16 px-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#fdf2f5] mb-4">
                    <svg class="w-8 h-8 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-slate-900">Belum ada charm</h2>
                <p class="text-sm text-slate-500 mt-1">Tambah katalog charm untuk dipakai pada nail art.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach ($charms as $charm)
                    @php
                        $payload = [
                            'id' => $charm->id,
                            'nama_charm' => $charm->nama_charm,
                            'stok' => (int) $charm->stok,
                            'harga' => (float) $charm->harga,
                            'is_active' => (bool) $charm->is_active,
                        ];
                        $stockClass = $charm->stok === 0
                            ? 'bg-rose-50 text-rose-700'
                            : ($charm->stok <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700');
                    @endphp
                    <article class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden hover:shadow-md transition flex flex-col">
                        <div class="aspect-square bg-gradient-to-br from-[#fdf2f5] to-[#fde4ea] flex items-center justify-center">
                            <svg class="w-16 h-16 text-[#a23f66] opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="3"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4l2 4-2 1-2-1 2-4zM12 20l-2-4 2-1 2 1-2 4zM4 12l4-2 1 2-1 2-4-2zM20 12l-4 2-1-2 1-2 4 2z"/>
                            </svg>
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="font-semibold text-slate-900 truncate">{{ $charm->nama_charm }}</h3>

                            <div class="mt-3 flex items-center justify-between gap-2 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $stockClass }}">
                                    Stok {{ $charm->stok }}
                                </span>
                                <span class="font-semibold text-[#a23f66]">Rp {{ number_format((float) $charm->harga, 0, ',', '.') }}</span>
                            </div>

                            <div class="mt-4 pt-3 border-t border-[#f1e4e8] flex items-center gap-2">
                                @can('charm.update')
                                    <button type="button" @click="openEdit({{ \Illuminate\Support\Js::from($payload) }})"
                                            class="flex-1 rounded-lg border border-[#eedde2] px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-[#fdf2f5] hover:text-[#a23f66]">
                                        Edit
                                    </button>
                                @endcan
                                @can('charm.delete')
                                    <button type="button" @click="deleteCharm({{ \Illuminate\Support\Js::from($charm->id) }}, {{ \Illuminate\Support\Js::from($charm->nama_charm) }})"
                                            class="flex-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-100">
                                        Hapus
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $charms->onEachSide(1)->links() }}
            </div>
        @endif

        {{-- Modal Create/Edit --}}
        <div x-show="modalOpen" x-cloak x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
             @keydown.escape.window="modalOpen = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.outside="modalOpen = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-[#f1e4e8]">
                    <h3 class="text-lg font-semibold text-slate-900" x-text="mode === 'create' ? 'Tambah Charm' : 'Edit Charm'"></h3>
                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nama Charm <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="form.nama_charm" required maxlength="80"
                               placeholder="Contoh: Crystal Diamond"
                               class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        <p x-show="errors.nama_charm" x-text="errors.nama_charm" class="mt-1 text-xs text-rose-600"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Stok <span class="text-rose-500">*</span></label>
                            <input type="number" x-model="form.stok" required min="0" step="1"
                                   class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            <p x-show="errors.stok" x-text="errors.stok" class="mt-1 text-xs text-rose-600"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Harga (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" x-model="form.harga" required min="0" step="500"
                                   class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            <p x-show="errors.harga" x-text="errors.harga" class="mt-1 text-xs text-rose-600"></p>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-slate-600 mt-2">
                        <input type="checkbox" x-model="form.is_active" class="rounded border-slate-300 text-[#a23f66] focus:ring-[#a23f66]">
                        Active
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

        function charmPage() {
            return {
                modalOpen: false,
                mode: 'create',
                editingId: null,
                loading: false,
                form: { nama_charm: '', stok: 0, harga: 0, is_active: true },
                errors: {},

                openCreate() {
                    this.mode = 'create';
                    this.editingId = null;
                    this.form = { nama_charm: '', stok: 0, harga: 0 };
                    this.errors = {};
                    this.modalOpen = true;
                },

                openEdit(data) {
                    this.mode = 'edit';
                    this.editingId = data.id;
                    this.form = { nama_charm: data.nama_charm, stok: data.stok, harga: data.harga, is_active: !!data.is_active };
                    this.errors = {};
                    this.modalOpen = true;
                },

                async submit() {
                    this.errors = {};
                    this.loading = true;
                    const url = this.mode === 'create'
                        ? @json(route('dashboard.charm-management.store'))
                        : @json(url('dashboard/charm-management')) + '/' + this.editingId;
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
                            body: JSON.stringify(this.form),
                        });
                        const json = await res.json();
                        if (! res.ok) {
                            if (res.status === 422 && json.errors) {
                                Object.entries(json.errors).forEach(([k, v]) => { this.errors[k] = v[0]; });
                            } else {
                                window.toast?.('error', 'Gagal', json.message || 'Terjadi kesalahan.');
                            }
                            return;
                        }
                        this.modalOpen = false;
                        window.toast?.('success', this.mode === 'create' ? 'Charm Ditambahkan' : 'Perubahan Disimpan', json.message);
                        setTimeout(() => window.location.reload(), 700);
                    } catch (e) {
                        window.toast?.('error', 'Network Error', e.message);
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteCharm(id, name) {
                    const result = await Swal.fire({
                        title: 'Hapus Charm?',
                        html: `Charm <b>${name}</b> akan dihapus permanen.`,
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, Hapus',
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#94a3b8',
                        reverseButtons: true,
                    });
                    if (! result.isConfirmed) return;

                    try {
                        const url = @json(url('dashboard/charm-management')) + '/' + id;
                        const res = await fetch(url, {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const json = await res.json();
                        if (! res.ok || ! json.success) {
                            window.toast?.('error', 'Gagal', json.message || 'Terjadi kesalahan.');
                            return;
                        }
                        window.toast?.('success', 'Penghapusan Berhasil', json.message);
                        setTimeout(() => window.location.reload(), 700);
                    } catch (e) {
                        window.toast?.('error', 'Network Error', e.message);
                    }
                },
            }
        }
    </script>
</x-app-layout>
