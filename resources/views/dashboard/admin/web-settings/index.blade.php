<x-app-layout>
    <x-slot name="header">Web Settings</x-slot>

    <div class="max-w-6xl mx-auto" x-data="{ tab: '{{ $groups->first()['slug'] ?? '' }}' }">
        <div class="mb-6">
            <h1 class="text-3xl font-semibold text-slate-900">Web Settings</h1>
            <p class="text-slate-500 mt-1">Atur konten landing page, dikelompokkan per halaman/section.</p>
        </div>

        {{-- Tambah setting baru --}}
        @can('web-setting.create')
            <details class="rounded-2xl bg-white border border-[#eedde2] mb-6 group" {{ $errors->has('key') || $errors->has('value') ? 'open' : '' }}>
                <summary class="flex items-center justify-between cursor-pointer px-6 py-4 list-none">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#fdf2f5] text-[#a23f66] font-semibold">+</span>
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">Tambah Setting Baru</h2>
                            <p class="text-xs text-slate-500">Klik untuk membuka form</p>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-slate-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </summary>
                <form method="POST" action="{{ route('dashboard.web-settings.store') }}"
                      class="px-6 pb-6 pt-2 border-t border-[#f1e4e8] grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Key</label>
                        <input type="text" name="key" value="{{ old('key') }}" required
                               placeholder="contoh: footer_text"
                               class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        @error('key') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-slate-400 mt-1">Dipakai di view sebagai <code class="text-slate-600">$settings['key']</code></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Label</label>
                        <input type="text" name="label" value="{{ old('label') }}"
                               placeholder="Nama ramah, mis. Footer Text"
                               class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Grup / Halaman</label>
                        <select name="group" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            @foreach ($groupLabels as $slug => $label)
                                <option value="{{ $slug }}" @selected(old('group') === $slug)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipe Input</label>
                        <select name="type" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                            @foreach (['text' => 'Teks pendek', 'textarea' => 'Teks panjang', 'url' => 'URL/Link', 'image' => 'Gambar (URL)'] as $val => $txt)
                                <option value="{{ $val }}" @selected(old('type') === $val)>{{ $txt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Value</label>
                        <input type="text" name="value" value="{{ old('value') }}"
                               class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        @error('value') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-2">
                        <button type="reset" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Reset</button>
                        <button type="submit" class="rounded-lg bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90">Tambah Setting</button>
                    </div>
                </form>
            </details>
        @endcan

        @if ($groups->isEmpty())
            <div class="rounded-2xl bg-white border border-[#eedde2] p-10 text-center">
                <p class="text-slate-400 italic">Belum ada setting. Tambahkan di atas untuk memulai.</p>
            </div>
        @else
            {{-- Tab per grup --}}
            <div class="flex flex-wrap items-center gap-2 mb-4">
                @foreach ($groups as $g)
                    <button type="button" @click="tab = '{{ $g['slug'] }}'"
                            :class="tab === '{{ $g['slug'] }}'
                                ? 'bg-[#a23f66] text-white border-[#a23f66]'
                                : 'bg-white text-slate-600 border-[#eedde2] hover:bg-[#fdf2f5]'"
                            class="rounded-full px-4 py-1.5 text-sm font-medium border transition">
                        {{ $g['label'] }}
                        <span class="ml-1 text-xs opacity-70">({{ $g['settings']->count() }})</span>
                    </button>
                @endforeach
            </div>

            <form method="POST" action="{{ route('dashboard.web-settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm">
                        <p class="font-semibold text-rose-700 mb-1">Gagal menyimpan. Periksa kembali:</p>
                        <ul class="list-disc list-inside text-rose-600 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Semua panel dirender; disembunyikan via x-show agar field tetap ikut submit --}}
                @foreach ($groups as $g)
                    <div x-show="tab === '{{ $g['slug'] }}'" x-cloak class="rounded-2xl bg-white border border-[#eedde2] p-6">
                        <h2 class="text-lg font-semibold text-slate-900 mb-5">{{ $g['label'] }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                            @foreach ($g['settings'] as $setting)
                                @include('dashboard.admin.web-settings._field', ['setting' => $setting])
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex items-center justify-between rounded-2xl bg-white border border-[#eedde2] p-4 sticky bottom-4 shadow-sm">
                    <p class="text-xs text-slate-500">{{ $groups->sum(fn ($g) => $g['settings']->count()) }} setting tersimpan</p>
                    <div class="flex gap-2">
                        <a href="{{ route('dashboard.web-settings.index') }}" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Batal</a>
                        <button type="submit" class="rounded-lg bg-[#a23f66] px-6 py-2 text-sm font-medium text-white hover:opacity-90">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <script>
        document.querySelectorAll('[data-delete-key]').forEach(btn => {
            btn.addEventListener('click', async function () {
                if (!confirm('Hapus setting ' + this.dataset.deleteKey + '?')) return;

                try {
                    const res = await fetch(this.dataset.deleteUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.dataset.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ _method: 'DELETE' }),
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        window.toast?.('error', 'Gagal', json.message || 'Gagal menghapus.');
                        return;
                    }
                    window.toast?.('success', 'Dihapus', json.message || 'Setting dihapus.');
                    window.location.reload();
                } catch (e) {
                    window.toast?.('error', 'Error', e.message);
                }
            });
        });
    </script>
</x-app-layout>
