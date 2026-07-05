<x-guest-layout>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Lora:wght@400;500&display=swap" rel="stylesheet">
    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }
        .font-lora { font-family: 'Lora', serif; }
    </style>

    <div class="space-y-4 sm:space-y-6">
        <div class="space-y-2 text-center">
            <h1 class="text-3xl sm:text-4xl" style="font-family: 'Cormorant Garamond', serif;">Welcome to nailby.hilda</h1>
            <p class="text-base sm:text-lg text-[#b55a75]">Fill in your personal data to create an account</p>
        </div>

        <div class="mx-auto w-full max-w-2xl rounded-2xl border border-white/80 bg-white/95 px-6 py-7 shadow-[0_18px_50px_rgba(205,163,173,0.18)] sm:px-10 sm:py-10">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#a05a73]">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-5 text-left">
                @csrf

                {{-- Full Name --}}
                <div class="space-y-2">
                    <label for="full_name" class="block text-lg font-medium text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">Full Name</label>
                    <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}"
                           required autofocus autocomplete="name"
                           maxlength="255"
                           pattern="[A-Za-z .\-]+"
                           title="Hanya huruf, spasi, titik, dan strip"
                           placeholder="John Doe"
                           class="block w-full rounded-md border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('full_name')" />
                </div>

                {{-- Email --}}
                <div class="space-y-2">
                    <label for="email" class="block text-lg font-medium text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           required autocomplete="email"
                           maxlength="255"
                           pattern="[A-Za-z0-9.\-@]+"
                           title="Hanya huruf, angka, titik, strip, dan @"
                           placeholder="hello@example.com"
                           class="block w-full rounded-md border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                {{-- Phone Number (opsional) --}}
                <div class="space-y-2">
                    <label for="phone_number" class="block text-lg font-medium text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">
                        Phone Number
                        <span class="text-xs font-normal text-[#9b8a8e]">(opsional)</span>
                    </label>
                    <input id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number') }}"
                           autocomplete="tel"
                           maxlength="20"
                           inputmode="numeric"
                           pattern="[0-9]+"
                           title="Hanya angka"
                           placeholder="081234567890"
                           class="block w-full rounded-md border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('phone_number')" />
                </div>

                {{-- Password --}}
                <div class="space-y-2">
                    <label for="password" class="block text-lg font-medium text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">Password</label>
                    <input id="password" type="password" name="password"
                           required autocomplete="new-password"
                           maxlength="72"
                           placeholder="********"
                           class="block w-full rounded-md border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                {{-- Password Confirmation (rule `confirmed` di RegisterRequest butuh field ini) --}}
                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-lg font-medium text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">Konfirmasi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           required autocomplete="new-password"
                           maxlength="72"
                           placeholder="********"
                           class="block w-full rounded-md border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('password_confirmation')" />
                </div>

                <button type="submit"
                        class="mt-1 flex w-full items-center justify-center rounded-md bg-[#DF7D9E] px-4 py-3.5 text-base font-semibold text-white shadow-[0_10px_24px_rgba(232,169,194,0.35)] transition hover:bg-[#df97b3] focus:outline-none focus:ring-2 focus:ring-[#e8a9c2]/70 focus:ring-offset-2 focus:ring-offset-white">
                    Register
                </button>

                {{-- Divider --}}
                <div class="flex items-center gap-3 text-xs text-[#c9bcc0]">
                    <span class="flex-1 h-px bg-[#efd1d6]"></span>
                    <span>atau</span>
                    <span class="flex-1 h-px bg-[#efd1d6]"></span>
                </div>

                {{-- Daftar dengan Google (di bawah tombol Register) --}}
                <a href="{{ route('auth.google.redirect') }}"
                   class="flex w-full items-center justify-center gap-3 rounded-md border border-[#efd1d6] bg-white px-4 py-3 text-sm font-medium text-[#3a3133] transition hover:bg-[#fdf7f8] hover:border-[#e4a9ba]">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google Logo" width="18" height="18">
                    Daftar dengan Google
                </a>

                <p class="text-center text-sm text-[#2d2527]">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-[#ea7c9d] transition hover:text-[#d96b8d]">Login here</a>
                </p>
            </form>
        </div>
    </div>
</x-guest-layout>
