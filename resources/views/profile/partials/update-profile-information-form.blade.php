<section>
    {{-- Tambahkan library Cropper.js via CDN --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <style>
        /* CSS Wajib untuk Cropper */
        img#cropper-image {
            display: block;
            max-width: 100%;
        }
        /* Membuat area potong menjadi bulat */
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%;
        }
    </style>

    @php
        $avatarUrl = $user->avatar_url;

        $nameParts = preg_split('/\s+/', trim((string) ($user->full_name ?? '')), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        $nailist        = $user->nailist;
        $studioTitle    = old('title', $nailist?->title ?? '');
        $artistBio      = old('bio', $nailist?->bio ?? '');
        $selectedIds    = old('specialties', $selectedSpecialty ?? []);
        $assignedNames  = $nailist
            ? $nailist->specialties()->orderBy('name')->pluck('name')->all()
            : [];
    @endphp

    <form
        method="post"
        action="{{ route('profile.update') }}"
        class="space-y-6"
        enctype="multipart/form-data"
        x-data="{
            firstName: @js(old('first_name', $firstName)),
            lastName:  @js(old('last_name', $lastName)),
            selectedStyles: @js($selectedIds),
            
            // State untuk Preview & Cropper Modal
            avatarPreview: null,
            showCropModal: false,
            cropImageUrl: '',
            cropperInstance: null,

            toggleStyle(id) {
                if (this.selectedStyles.includes(id)) {
                    this.selectedStyles = this.selectedStyles.filter((item) => item !== id);
                    return;
                }
                if (this.selectedStyles.length < 3) {
                    this.selectedStyles.push(id);
                }
            },

            // 1. Munculkan Modal saat file dipilih
            onAvatarChange(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Tampilkan Modal
                this.showCropModal = true;

                // Buat URL sementara untuk di-crop
                if (this.cropImageUrl) URL.revokeObjectURL(this.cropImageUrl);
                this.cropImageUrl = URL.createObjectURL(file);

                // Inisialisasi Cropper.js (tunggu gambar ter-render di DOM)
                this.$nextTick(() => {
                    if (this.cropperInstance) this.cropperInstance.destroy();
                    const imgEl = document.getElementById('cropper-image');
                    this.cropperInstance = new Cropper(imgEl, {
                        aspectRatio: 1, // Paksa kotak 1:1
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                    });
                });
            },

            // 2. Simpan hasil potong (Crop)
            saveCrop() {
                if (!this.cropperInstance) return;

                // Ambil hasil crop jadi Blob (resolusi 500x500px)
                this.cropperInstance.getCroppedCanvas({
                    width: 500,
                    height: 500,
                    fillColor: '#fff'
                }).toBlob((blob) => {
                    // Update preview bulat di luar
                    if (this.avatarPreview) URL.revokeObjectURL(this.avatarPreview);
                    this.avatarPreview = URL.createObjectURL(blob);

                    // AJAIB: Timpa file asli dengan hasil crop agar form mengirim yang sudah di-crop!
                    const file = new File([blob], 'avatar_cropped.jpg', { type: 'image/jpeg' });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    document.getElementById('avatar').files = dataTransfer.files;

                    this.closeCropModal();
                }, 'image/jpeg', 0.9);
            },

            // 3. Tutup Modal & Batal
            closeCropModal() {
                this.showCropModal = false;
                if (this.cropperInstance) {
                    this.cropperInstance.destroy();
                    this.cropperInstance = null;
                }
                // Bersihkan input jika user membatalkan
                if (!this.avatarPreview) {
                    document.getElementById('avatar').value = '';
                }
            }
        }"
    >
        @csrf
        @method('patch')

        <input type="hidden" name="full_name" :value="[firstName, lastName].filter(Boolean).join(' ').trim()">

        <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-8">
            {{-- Sidebar avatar --}}
            <aside class="rounded-3xl bg-gradient-to-b from-[#f8eef1] to-white p-8 text-center">
                <div class="relative mx-auto w-fit">
                    <div class="mx-auto h-32 w-32 rounded-full p-2 bg-white shadow-md ring-2 ring-white">
                        <img :src="avatarPreview || '{{ $avatarUrl }}'" alt="{{ $user->full_name ?? $user->email }}" class="h-full w-full rounded-full object-cover" />
                    </div>
                    <label for="avatar" class="absolute bottom-1 right-1 h-8 w-8 rounded-full bg-white shadow-md border border-[#f0c5d3] flex items-center justify-center cursor-pointer hover:bg-[#f9f5f7] transition">
                        <svg class="h-4 w-4 text-[#a23f66]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                            <circle cx="12" cy="13" r="4" />
                        </svg>
                    </label>
                    <input id="avatar" name="avatar" type="file" class="hidden" accept="image/*" @change="onAvatarChange($event)" />
                </div>

                <div class="mt-6">
                    <p class="text-2xl font-bold text-slate-900" x-text="firstName || '{{ $firstName ?: 'User' }}'"></p>
                    @if ($isNailist)
                        <p class="mt-1 text-sm text-[#a23f66]">{{ $studioTitle ?: 'Nail Artist' }}</p>
                    @else
                        <p class="mt-1 text-sm text-slate-500">{{ $user->roles->pluck('name')->join(', ') }}</p>
                    @endif
                </div>

                <x-input-error class="mt-2 text-left text-xs" :messages="$errors->get('avatar')" />

                @if ($isNailist)
                    <div class="mt-8 text-left rounded-2xl bg-[#f2e8ea] p-5">
                        <p class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Current Specialties</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse ($assignedNames as $name)
                                <span class="px-3 py-2 rounded-lg text-xs bg-white text-slate-600 border border-[#e8dfe0]">{{ $name }}</span>
                            @empty
                                <span class="text-xs text-slate-400 italic">Belum dipilih</span>
                            @endforelse
                        </div>
                    </div>
                @endif
            </aside>

            {{-- Main form --}}
            <div class="rounded-3xl bg-white p-8 space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Personal Details</h2>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-slate-600 mb-2">First Name</label>
                        <input id="first_name" name="first_name" type="text" x-model="firstName" class="w-full rounded-lg border border-[#e8dfe0] bg-[#faf8f9] px-4 py-3 text-sm" />
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-slate-600 mb-2">Last Name</label>
                        <input id="last_name" name="last_name" type="text" x-model="lastName" class="w-full rounded-lg border border-[#e8dfe0] bg-[#faf8f9] px-4 py-3 text-sm" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-600 mb-2">Username</label>
                        <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" class="w-full rounded-lg border border-[#e8dfe0] bg-[#faf8f9] px-4 py-3 text-sm" />
                        <x-input-error class="mt-1 text-xs" :messages="$errors->get('username')" />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-600 mb-2">
                            Email
                            <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500 align-middle">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                Read-only
                            </span>
                        </label>
                        <input id="email" type="email" value="{{ $user->email }}" readonly disabled
                               class="w-full rounded-lg border border-[#e8dfe0] bg-slate-50 px-4 py-3 text-sm text-slate-500 cursor-not-allowed" />
                        <p class="mt-1 text-xs text-slate-500">Email tidak bisa diubah dari sini. Hubungi superadmin jika perlu mengganti.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label for="phone_number" class="block text-sm font-medium text-slate-600 mb-2">Phone Number</label>
                        <input id="phone_number" name="phone_number" type="text" value="{{ old('phone_number', $user->phone_number) }}" inputmode="numeric" pattern="[0-9]*" maxlength="20" autocomplete="tel" placeholder="08888888888" oninput="this.value=this.value.replace(/[^0-9]/g,'')" onkeypress="return /[0-9]/.test(event.key)" onpaste="event.preventDefault(); this.value=((event.clipboardData||window.clipboardData).getData('text')||'').replace(/[^0-9]/g,'').slice(0,20);" class="w-full rounded-lg border border-[#e8dfe0] bg-[#faf8f9] px-4 py-3 text-sm" />
                        <x-input-error class="mt-1 text-xs" :messages="$errors->get('phone_number')" />
                    </div>
                </div>

                {{-- Nailist-only fields --}}
                @if ($isNailist)
                    <div class="space-y-5 pt-6 border-t border-[#f1e4e8]">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Artist Profile</h3>
                            <p class="text-xs text-slate-500 mt-1">Khusus role Nailist.</p>
                        </div>

                        <div>
                            <label for="title" class="block text-sm font-medium text-slate-600 mb-2">Studio Title</label>
                            <input id="title" name="title" type="text" value="{{ $studioTitle }}" placeholder="contoh: Senior Nail Artist"
                                   class="w-full rounded-lg border border-[#e8dfe0] bg-[#faf8f9] px-4 py-3 text-sm">
                            <x-input-error class="mt-1 text-xs" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <label for="bio" class="block text-sm font-medium text-slate-600 mb-2">Artist Bio</label>
                            <textarea id="bio" name="bio" rows="4"
                                      placeholder="Ceritakan pengalaman & gaya kamu..."
                                      class="w-full rounded-lg border border-[#e8dfe0] bg-[#faf8f9] px-4 py-3 text-sm resize-none">{{ $artistBio }}</textarea>
                            <x-input-error class="mt-1 text-xs" :messages="$errors->get('bio')" />
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-slate-900 mb-1">Craft &amp; Specialties</h3>
                            <p class="text-sm text-slate-500 mb-4">Pilih primary styles (max 3)</p>

                            @if ($specialtyOptions->isEmpty())
                                <p class="text-xs text-slate-400 italic">Belum ada specialty tersedia. Hubungi admin untuk menambahkan.</p>
                            @else
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    @foreach ($specialtyOptions as $opt)
                                        <label
                                            :class="selectedStyles.includes(@js($opt->id))
                                                ? 'border-[#d98aa9] bg-[#fff5f9] text-[#a23f66] font-semibold'
                                                : 'border-[#e8dfe0] bg-white text-slate-600'"
                                            class="rounded-lg border px-4 py-3 text-sm transition cursor-pointer text-center"
                                        >
                                            <input
                                                type="checkbox"
                                                name="specialties[]"
                                                value="{{ $opt->id }}"
                                                class="hidden"
                                                x-bind:checked="selectedStyles.includes(@js($opt->id))"
                                                @change="toggleStyle(@js($opt->id))"
                                            >
                                            {{ $opt->name }}
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            <x-input-error class="mt-2 text-xs" :messages="$errors->get('specialties')" />
                        </div>
                    </div>
                @endif

                <x-input-error class="mt-2 text-xs" :messages="$errors->get('full_name')" />
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-6">
            <a href="{{ route('profile.edit') }}" class="px-6 py-2.5 text-sm text-slate-600 hover:text-slate-700 font-medium transition">Discard Changes</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#e8a9c2] px-8 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-[#df97b3] transition">Save Changes</button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm font-medium text-emerald-600">{{ __('Saved.') }}</p>
            @endif
        </div>

        {{-- ================= MODAL CROPPER ================= --}}
        <div x-show="showCropModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm" @keydown.escape.window="closeCropModal()">
            <div class="bg-white rounded-3xl w-full max-w-lg p-6 md:p-8 shadow-2xl" @click.outside="closeCropModal()">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">Sesuaikan Foto</h3>
                        <p class="text-sm text-slate-500 mt-1">Geser dan perbesar untuk menyesuaikan avatar.</p>
                    </div>
                    <button type="button" @click="closeCropModal()" class="text-slate-400 hover:text-rose-500 transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                {{-- Area Kerja Cropper --}}
                <div class="w-full h-72 md:h-80 bg-slate-100 rounded-2xl overflow-hidden relative border border-slate-200">
                    <img id="cropper-image" :src="cropImageUrl" class="opacity-0"> {{-- Opacity 0 agar tidak kedip sebelum cropper load --}}
                </div>
                
                <div class="mt-6 flex flex-col md:flex-row justify-end gap-3">
                    <button type="button" @click="closeCropModal()" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 rounded-full hover:bg-slate-200 transition">Batal</button>
                    <button type="button" @click="saveCrop()" class="px-5 py-2.5 text-sm font-semibold text-white bg-[#a23f66] rounded-full shadow-md hover:bg-[#8e3558] transition inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Simpan Potongan
                    </button>
                </div>
            </div>
        </div>

    </form>
</section>