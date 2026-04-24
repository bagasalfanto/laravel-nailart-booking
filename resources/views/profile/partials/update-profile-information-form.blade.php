<section>
    @php
        $avatarUrl = $user->avatar ? asset('storage/' . $user->avatar) : 'https://i.pravatar.cc/300?u=' . urlencode((string) $user->id);
        $nameParts = preg_split('/\s+/', trim((string) ($user->full_name ?? '')), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        $studioTitle = old('specialty', optional($user->nailist)->specialty) ?: 'Senior Nail Artist';
        $dummySpecialties = ['3D Art', 'Gel Extensions', 'Minimalist'];
        $styleOptions = ['3D Art', 'Minimalist', 'Gel Extensions', 'Luxury Embellishments', 'Natural Care'];
    @endphp

    <form
        method="post"
        action="{{ route('profile.update') }}"
        class="space-y-6"
        enctype="multipart/form-data"
        x-data="{
            firstName: @js(old('first_name', $firstName)),
            lastName: @js(old('last_name', $lastName)),
            selectedStyles: @js($dummySpecialties),
            toggleStyle(style) {
                if (this.selectedStyles.includes(style)) {
                    this.selectedStyles = this.selectedStyles.filter((item) => item !== style);
                    return;
                }

                if (this.selectedStyles.length < 3) {
                    this.selectedStyles.push(style);
                }
            },
        }"
    >
        @csrf
        @method('patch')

        <input type="hidden" name="full_name" :value="[firstName, lastName].filter(Boolean).join(' ').trim()">
        <input type="hidden" name="username" value="{{ old('username', $user->username) }}">
        <input type="hidden" name="email" value="{{ old('email', $user->email) }}">
        <input type="hidden" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}">

        <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-8">
            <aside class="rounded-3xl bg-gradient-to-b from-[#f8eef1] to-white p-8 text-center">
                <div class="relative mx-auto w-fit">
                    <div class="mx-auto h-32 w-32 rounded-full p-2 bg-white shadow-md ring-2 ring-white">
                        <img src="{{ $avatarUrl }}" alt="{{ $user->full_name ?? $user->email }}" class="h-full w-full rounded-full object-cover" />
                    </div>
                    <label for="avatar" class="absolute bottom-1 right-1 h-8 w-8 rounded-full bg-white shadow-md border border-[#f0c5d3] flex items-center justify-center cursor-pointer hover:bg-[#f9f5f7] transition">
                        <svg class="h-4 w-4 text-[#a23f66]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                            <circle cx="12" cy="13" r="4" />
                        </svg>
                    </label>
                    <input id="avatar" name="avatar" type="file" class="hidden" accept="image/*" />
                </div>

                <div class="mt-6">
                    <p class="text-2xl font-bold text-slate-900" x-text="firstName || '{{ $firstName ?: 'Artist' }}'"></p>
                    <p class="mt-1 text-sm text-[#a23f66]">{{ $studioTitle }}</p>
                </div>

                <button type="button" class="mt-4 w-full inline-flex items-center justify-center rounded-lg bg-[#f4e6ea] px-6 py-2.5 text-sm font-semibold text-[#a23f66] hover:bg-[#efdae1] transition">
                    Change Photo
                </button>
                <x-input-error class="mt-2 text-left text-xs" :messages="$errors->get('avatar')" />

                @if ($user->avatar)
                    <label class="mt-2 w-full inline-flex items-center justify-center rounded-lg bg-white border border-[#e8dfe0] px-6 py-2.5 text-sm font-semibold text-slate-500 hover:bg-[#f9f5f7] transition cursor-pointer">
                        <input type="checkbox" name="remove_avatar" value="1" class="hidden" />
                        Remove Photo
                    </label>
                @endif

                <div class="mt-8 text-left rounded-2xl bg-[#f2e8ea] p-5">
                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Current Specialties</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($dummySpecialties as $item)
                            <span class="px-3 py-2 rounded-lg text-xs bg-white text-slate-600 border border-[#e8dfe0]">{{ $item }}</span>
                        @endforeach
                    </div>
                </div>
            </aside>

            <div class="rounded-3xl bg-white p-8 space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Personal Details</h2>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-slate-600 mb-2">First Name</label>
                        <input id="first_name" name="first_name" type="text" x-model="firstName" class="w-full rounded-lg border border-[#e8dfe0] bg-[#faf8f9] px-4 py-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-[#d98aa9] focus:ring-1 focus:ring-[#f0c5d3]" />
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-slate-600 mb-2">Last Name</label>
                        <input id="last_name" name="last_name" type="text" x-model="lastName" class="w-full rounded-lg border border-[#e8dfe0] bg-[#faf8f9] px-4 py-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-[#d98aa9] focus:ring-1 focus:ring-[#f0c5d3]" />
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="specialty" class="block text-sm font-medium text-slate-600 mb-2">Studio Title</label>
                        <input id="specialty" name="specialty" type="text" value="{{ $studioTitle }}" class="w-full rounded-lg border border-[#e8dfe0] bg-[#faf8f9] px-4 py-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-[#d98aa9] focus:ring-1 focus:ring-[#f0c5d3]" />
                        <x-input-error class="mt-2 text-xs" :messages="$errors->get('specialty')" />
                    </div>

                    <div>
                        <label for="artist_bio_dummy" class="block text-sm font-medium text-slate-600 mb-2">Artist Bio</label>
                        <textarea id="artist_bio_dummy" rows="4" class="w-full rounded-lg border border-[#e8dfe0] bg-[#faf8f9] px-4 py-3 text-sm text-slate-700 placeholder:text-slate-300 focus:border-[#d98aa9] focus:ring-1 focus:ring-[#f0c5d3] resize-none">{{ old('artist_bio_dummy', 'Specializing in sculptural 3D art and elegant minimalist designs. Bringing 5 years of editorial experience to the studio canvas.') }}</textarea>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Craft &amp; Specialties</h3>
                        <p class="text-sm text-slate-500 mb-4">Select Primary Styles (Max 3)</p>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach ($styleOptions as $style)
                                <button
                                    type="button"
                                    @click="toggleStyle(@js($style))"
                                    :class="selectedStyles.includes(@js($style))
                                        ? 'border-[#d98aa9] bg-[#fff5f9] text-[#a23f66] font-semibold'
                                        : 'border-[#e8dfe0] bg-white text-slate-600'"
                                    class="rounded-lg border px-4 py-4 text-sm transition"
                                >
                                    <span>{{ $style }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <x-input-error class="mt-2 text-xs" :messages="$errors->get('full_name')" />
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-6">
            <button type="reset" class="px-6 py-2.5 text-sm text-slate-600 hover:text-slate-700 font-medium transition">
                Discard Changes
            </button>

            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#e8a9c2] px-8 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-[#df97b3] transition">
                Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-emerald-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
