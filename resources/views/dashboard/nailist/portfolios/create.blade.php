<x-app-layout>
    <x-slot name="header">Tambah Portfolio</x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('dashboard.portfolio.index') }}" class="text-sm text-slate-500 hover:text-[#a23f66]">&larr; Kembali ke Portfolio</a>
            <h1 class="text-3xl font-semibold text-slate-900 mt-2">Tambah Portfolio</h1>
            <p class="text-slate-500 mt-1">Upload karya nail art Anda dengan judul dan deskripsi singkat.</p>
        </div>

        <form method="POST" action="{{ route('dashboard.portfolio.store') }}" enctype="multipart/form-data"
              class="rounded-2xl bg-white border border-[#eedde2] p-6 space-y-5"
              x-data="portfolioForm()">
            @csrf

            {{-- Image upload + preview --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Gambar Portfolio <span class="text-rose-500">*</span></label>

                <div class="relative rounded-xl border-2 border-dashed border-[#eedde2] hover:border-[#a23f66] bg-[#fdf8fa] transition cursor-pointer overflow-hidden"
                     :class="preview ? 'p-0' : 'p-8'"
                     @click="$refs.fileInput.click()">

                    <template x-if="!preview">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto text-[#d8b8c2]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="mt-3 text-sm font-medium text-slate-700">Klik untuk pilih gambar</p>
                            <p class="text-xs text-slate-500 mt-1">JPG, PNG, atau WebP (maks. 2 MB)</p>
                        </div>
                    </template>

                    <template x-if="preview">
                        <div class="relative">
                            <img :src="preview" class="w-full max-h-[420px] object-contain bg-slate-50" alt="Preview">
                            <button type="button"
                                    @click.stop="clearImage"
                                    class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-white shadow">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Hapus
                            </button>
                        </div>
                    </template>

                    <input x-ref="fileInput" type="file" name="gambar" accept="image/jpeg,image/jpg,image/png,image/webp"
                           class="hidden" @change="handleChange">
                </div>
                @error('gambar') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Judul --}}
            <div>
                <label for="judul" class="block text-sm font-medium text-slate-700">Judul <span class="text-rose-500">*</span></label>
                <input id="judul" name="judul" type="text" required maxlength="100" value="{{ old('judul') }}"
                       placeholder="Contoh: Korean Floral Pastel"
                       class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                @error('judul') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            {{-- Deskripsi --}}
            <div>
                <label for="deskripsi" class="block text-sm font-medium text-slate-700">Deskripsi <span class="text-slate-400 text-xs font-normal">(opsional)</span></label>
                <textarea id="deskripsi" name="deskripsi" rows="4" maxlength="1000"
                          placeholder="Ceritakan tentang karya ini — gaya, teknik, atau inspirasi…"
                          class="mt-1 w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">{{ old('deskripsi') }}</textarea>
                @error('deskripsi') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <a href="{{ route('dashboard.portfolio.index') }}" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Batal</a>
                <button type="submit" class="rounded-lg bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90">
                    Simpan Portfolio
                </button>
            </div>
        </form>
    </div>

    <script>
        function portfolioForm() {
            return {
                preview: null,
                handleChange(e) {
                    const file = e.target.files[0];
                    if (!file) { this.preview = null; return; }
                    const reader = new FileReader();
                    reader.onload = (ev) => { this.preview = ev.target.result; };
                    reader.readAsDataURL(file);
                },
                clearImage() {
                    this.preview = null;
                    this.$refs.fileInput.value = '';
                },
            }
        }
    </script>
</x-app-layout>
