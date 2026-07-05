<x-app-layout>
    <x-slot name="header">Treatment Management</x-slot>

    @php
        // Flatten all treatments + map per category for filter
        $allTreatments = collect();
        foreach ($categories as $cat) {
            foreach ($cat->treatments as $t) {
                $t->setRelation('category', $cat);
                $allTreatments->push($t);
            }
        }
    @endphp

    <div class="max-w-7xl mx-auto" x-data="treatmentSettings()">
        {{-- Header --}}
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">
                    Treatment Catalog
                </h1>
                <p class="text-slate-500 mt-1 text-sm">Curate and manage your atelier's service offerings, pricing, and duration blocks.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="openCategoryModal()"
                        class="inline-flex items-center gap-1.5 rounded-full border border-[#a23f66] bg-white px-4 py-2 text-sm font-medium text-[#a23f66] hover:bg-[#fdf2f5]">
                    + Category
                </button>
                <button type="button" @click="openTreatmentModal()"
                        class="inline-flex items-center gap-1.5 rounded-full bg-[#a23f66] px-4 py-2 text-sm font-medium text-white hover:opacity-90 shadow-sm">
                    + Add New Treatment
                </button>
            </div>
        </div>

        {{-- Tabs filter --}}
        <div class="flex items-center gap-6 border-b border-[#eedde2] mb-6 overflow-x-auto">
            <button type="button" @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'text-[#a23f66] border-[#a23f66] font-semibold' : 'text-slate-600 border-transparent hover:text-[#a23f66]'"
                    class="px-1 py-3 text-sm border-b-2 transition whitespace-nowrap">
                All Services
            </button>
            @foreach ($categories as $cat)
                <button type="button" @click="activeTab = '{{ $cat->id }}'"
                        :class="activeTab === '{{ $cat->id }}' ? 'text-[#a23f66] border-[#a23f66] font-semibold' : 'text-slate-600 border-transparent hover:text-[#a23f66]'"
                        class="px-1 py-3 text-sm border-b-2 transition whitespace-nowrap">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        {{-- Grid card treatments --}}
        @if ($allTreatments->isEmpty())
            <div class="rounded-2xl bg-white border border-[#eedde2] py-16 text-center">
                <p class="text-slate-500">Belum ada treatment.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($allTreatments as $t)
                    @php
                        $catName = $t->category?->name ?? '—';
                        $tPayload = [
                            'id' => $t->id,
                            'category_id' => $t->category_id,
                            'nama_jasa' => $t->nama_jasa,
                            'kode_jasa' => $t->kode_jasa,
                            'deskripsi' => $t->deskripsi,
                            'price_type' => $t->price_type,
                            'price_min' => (float) $t->price_min,
                            'price_max' => $t->price_max !== null ? (float) $t->price_max : null,
                            'durasi_menit' => (int) $t->durasi_menit,
                            'is_active' => (bool) $t->is_active,
                        ];
                    @endphp
                    <article class="bg-white rounded-2xl p-5 border border-[#eedde2] shadow-[0_4px_15px_rgba(205,163,173,0.08)] hover:shadow-md transition flex flex-col"
                             x-show="activeTab === 'all' || activeTab === '{{ $t->category_id }}'">

                        {{-- Icon top-left --}}
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-[#fdf2f5] mb-3">
                            <svg class="w-5 h-5 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m4-12v12m-8-8v8M4 21h16"/>
                            </svg>
                        </div>

                        {{-- Category label uppercase --}}
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#a23f66] mb-1">{{ $catName }}</p>

                        {{-- Treatment name --}}
                        <h3 class="font-semibold text-slate-900 text-base">{{ $t->nama_jasa }}</h3>

                        {{-- Description --}}
                        <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                            {{ $t->deskripsi ?? '—' }}
                        </p>

                        {{-- Duration + Price --}}
                        <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-[#f5e7ec]">
                            <div>
                                <p class="text-[9px] uppercase tracking-wide text-slate-400">Code</p>
                                <p class="text-xs text-slate-700 font-mono mt-0.5">{{ $t->kode_jasa }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] uppercase tracking-wide text-slate-400">Price</p>
                                <p class="text-sm font-semibold text-[#a23f66] mt-0.5">{{ $t->price_label }}</p>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-[#f5e7ec]">
                            <button type="button" @click="openTreatmentModal({{ \Illuminate\Support\Js::from($tPayload) }})"
                                    class="flex-1 rounded-lg border border-[#eedde2] px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-[#fdf2f5] hover:text-[#a23f66]">
                                Edit
                            </button>
                            <button type="button" @click="deleteTreatment({{ \Illuminate\Support\Js::from($t->id) }}, {{ \Illuminate\Support\Js::from($t->nama_jasa) }})"
                                    class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-100">
                                Delete
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        {{-- ============== Modal: Category ============== --}}
        <div x-show="catModal.open" x-cloak x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
             @keydown.escape.window="catModal.open = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.outside="catModal.open = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-[#f1e4e8]">
                    <h3 class="text-lg font-semibold text-slate-900" x-text="catModal.id ? 'Edit Category' : 'Add Category'"></h3>
                    <button type="button" @click="catModal.open = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="submitCategory" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Category Name <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="catModal.form.name" required maxlength="80"
                               placeholder="e.g. Manicure"
                               class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        <p x-show="catModal.errors.name" x-text="catModal.errors.name" class="mt-1 text-xs text-rose-600"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Description (optional)</label>
                        <textarea x-model="catModal.form.description" rows="2" maxlength="255"
                                  class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]"></textarea>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" x-model="catModal.form.is_active" class="rounded border-slate-300 text-[#a23f66] focus:ring-[#a23f66]">
                        Active
                    </label>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="catModal.open = false" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Cancel</button>
                        <button type="submit" :disabled="catModal.loading" class="rounded-lg bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90 disabled:opacity-50">
                            <span x-text="catModal.loading ? 'Saving…' : 'Save'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============== Modal: Treatment ============== --}}
        <div x-show="tModal.open" x-cloak x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
             @keydown.escape.window="tModal.open = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg" @click.outside="tModal.open = false">
                <div class="flex items-center justify-between px-6 py-4 border-b border-[#f1e4e8]">
                    <h3 class="text-lg font-semibold text-slate-900" x-text="tModal.id ? 'Edit Treatment' : 'Add New Treatment'"></h3>
                    <button type="button" @click="tModal.open = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form @submit.prevent="submitTreatment" class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Category <span class="text-rose-500">*</span></label>
                            <select x-model="tModal.form.category_id" required class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                                <option value="">Select category…</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <p x-show="tModal.errors.category_id" x-text="tModal.errors.category_id" class="mt-1 text-xs text-rose-600"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Name <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="tModal.form.nama_jasa" required maxlength="120" class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            <p x-show="tModal.errors.nama_jasa" x-text="tModal.errors.nama_jasa" class="mt-1 text-xs text-rose-600"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Code <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="tModal.form.kode_jasa" required maxlength="32" placeholder="MNC-001" class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm font-mono focus:border-[#a23f66] focus:ring-[#a23f66]">
                            <p x-show="tModal.errors.kode_jasa" x-text="tModal.errors.kode_jasa" class="mt-1 text-xs text-rose-600"></p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Description (optional)</label>
                            <textarea x-model="tModal.form.deskripsi" rows="2" maxlength="500" class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Price Type <span class="text-rose-500">*</span></label>
                            <div class="flex gap-2">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" x-model="tModal.form.price_type" value="fixed" class="sr-only peer">
                                    <div class="rounded-lg border-2 border-slate-200 peer-checked:border-[#a23f66] peer-checked:bg-[#fdf2f5] px-3 py-2 text-center text-sm font-medium text-slate-600 peer-checked:text-[#a23f66] transition">
                                        Fixed
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" x-model="tModal.form.price_type" value="range" class="sr-only peer">
                                    <div class="rounded-lg border-2 border-slate-200 peer-checked:border-[#a23f66] peer-checked:bg-[#fdf2f5] px-3 py-2 text-center text-sm font-medium text-slate-600 peer-checked:text-[#a23f66] transition">
                                        Range
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" x-text="tModal.form.price_type === 'range' ? 'Min Price (Rp) *' : 'Price (Rp) *'"></label>
                            <input type="number" x-model="tModal.form.price_min" required min="0" step="1000" class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            <p x-show="tModal.errors.price_min" x-text="tModal.errors.price_min" class="mt-1 text-xs text-rose-600"></p>
                        </div>
                        <div x-show="tModal.form.price_type === 'range'">
                            <label class="block text-sm font-medium text-slate-700">Max Price (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" x-model="tModal.form.price_max" :required="tModal.form.price_type === 'range'" min="0" step="1000" class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            <p x-show="tModal.errors.price_max" x-text="tModal.errors.price_max" class="mt-1 text-xs text-rose-600"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Duration (minutes)</label>
                            <input type="number" x-model="tModal.form.durasi_menit" min="15" step="15" class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            <p x-show="tModal.errors.durasi_menit" x-text="tModal.errors.durasi_menit" class="mt-1 text-xs text-rose-600"></p>
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" x-model="tModal.form.is_active" class="rounded border-slate-300 text-[#a23f66] focus:ring-[#a23f66]">
                        Active
                    </label>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="tModal.open = false" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Cancel</button>
                        <button type="submit" :disabled="tModal.loading" class="rounded-lg bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90 disabled:opacity-50">
                            <span x-text="tModal.loading ? 'Saving…' : 'Save'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>[x-cloak]{display:none !important}</style>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        async function api(url, method = 'POST', body = null) {
            const res = await fetch(url, {
                method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: body ? JSON.stringify(body) : null,
            });
            const json = await res.json().catch(() => ({}));
            return { ok: res.ok, status: res.status, json };
        }

        function treatmentSettings() {
            return {
                activeTab: 'all',
                catModal: {
                    open: false, loading: false, id: null,
                    form: { name: '', description: '', is_active: true },
                    errors: {},
                },
                tModal: {
                    open: false, loading: false, id: null,
                    form: { category_id: '', nama_jasa: '', kode_jasa: '', deskripsi: '', price_type: 'fixed', price_min: 0, price_max: null, durasi_menit: 60, is_active: true },
                    errors: {},
                },

                openCategoryModal(data = null) {
                    this.catModal.errors = {};
                    if (data) {
                        this.catModal.id = data.id;
                        this.catModal.form = { name: data.name, description: data.description || '', is_active: !!data.is_active };
                    } else {
                        this.catModal.id = null;
                        this.catModal.form = { name: '', description: '', is_active: true };
                    }
                    this.catModal.open = true;
                },

                async submitCategory() {
                    this.catModal.errors = {};
                    this.catModal.loading = true;
                    const url = this.catModal.id
                        ? @json(url('dashboard/treatments/categories')) + '/' + this.catModal.id
                        : @json(route('dashboard.treatments.categories.store'));
                    const method = this.catModal.id ? 'PUT' : 'POST';

                    const { ok, status, json } = await api(url, method, this.catModal.form);
                    this.catModal.loading = false;

                    if (!ok) {
                        if (status === 422 && json.errors) {
                            Object.entries(json.errors).forEach(([k, v]) => { this.catModal.errors[k] = v[0]; });
                        } else {
                            window.toast?.('error', 'Failed', json.message || 'An error occurred.');
                        }
                        return;
                    }
                    this.catModal.open = false;
                    window.toast?.('success', this.catModal.id ? 'Category Updated' : 'Category Added', json.message);
                    setTimeout(() => window.location.reload(), 300);
                },

                openTreatmentModal(data = null, categoryId = null) {
                    this.tModal.errors = {};
                    if (data) {
                        this.tModal.id = data.id;
                        this.tModal.form = {
                            category_id: data.category_id, nama_jasa: data.nama_jasa, kode_jasa: data.kode_jasa,
                            deskripsi: data.deskripsi || '', price_type: data.price_type,
                            price_min: data.price_min, price_max: data.price_max, durasi_menit: data.durasi_menit, is_active: !!data.is_active,
                        };
                    } else {
                        this.tModal.id = null;
                        this.tModal.form = {
                            category_id: categoryId || '', nama_jasa: '', kode_jasa: '', deskripsi: '',
                            price_type: 'fixed', price_min: 0, price_max: null, durasi_menit: 60, is_active: true,
                        };
                    }
                    this.tModal.open = true;
                },

                async submitTreatment() {
                    this.tModal.errors = {};
                    this.tModal.loading = true;
                    const payload = { ...this.tModal.form };
                    if (payload.price_type === 'fixed') payload.price_max = null;

                    const url = this.tModal.id
                        ? @json(url('dashboard/treatments/items')) + '/' + this.tModal.id
                        : @json(route('dashboard.treatments.items.store'));
                    const method = this.tModal.id ? 'PUT' : 'POST';

                    const { ok, status, json } = await api(url, method, payload);
                    this.tModal.loading = false;

                    if (!ok) {
                        if (status === 422 && json.errors) {
                            Object.entries(json.errors).forEach(([k, v]) => { this.tModal.errors[k] = v[0]; });
                        } else {
                            window.toast?.('error', 'Failed', json.message || 'An error occurred.');
                        }
                        return;
                    }
                    this.tModal.open = false;
                    window.toast?.('success', this.tModal.id ? 'Treatment Updated' : 'Treatment Added', json.message);
                    setTimeout(() => window.location.reload(), 300);
                },

                async deleteTreatment(id, name) {
                    const result = await Swal.fire({
                        title: 'Delete Treatment?',
                        html: `Treatment <b>${name}</b> akan dihapus permanen.`,
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Cancel',
                        confirmButtonText: 'Yes, Delete',
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#94a3b8',
                        reverseButtons: true,
                    });
                    if (!result.isConfirmed) return;

                    const url = @json(url('dashboard/treatments/items')) + '/' + id;
                    const { ok, json } = await api(url, 'DELETE');
                    if (!ok) {
                        window.toast?.('error', 'Failed', json.message || 'An error occurred.');
                        return;
                    }
                    window.toast?.('success', 'Deleted', json.message);
                    setTimeout(() => window.location.reload(), 300);
                },
            }
        }
    </script>
</x-app-layout>
