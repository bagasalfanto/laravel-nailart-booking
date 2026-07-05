<x-guest-layout>
    <div class="space-y-4 sm:space-y-6">
        <div class="space-y-2 text-center">
            <h1 class="text-3xl sm:text-4xl" style="font-family: 'Cormorant Garamond', serif;">Reset Password</h1>
            <p class="text-base sm:text-lg text-[#b55a75]">
                Buat password baru untuk akunmu.
            </p>
        </div>

        <div class="mx-auto w-full max-w-2xl rounded-2xl border border-white/80 bg-white/95 px-6 py-7 shadow-[0_18px_50px_rgba(205,163,173,0.18)] sm:px-10 sm:py-10">
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <p class="font-medium mb-1">Gagal reset password:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="space-y-5 text-left">
                @csrf

                {{-- Password Reset Token --}}
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="space-y-2">
                    <label for="email" class="block text-lg font-medium text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                           placeholder="hello@example.com"
                           class="block w-full rounded-md border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-lg font-medium text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">Password Baru</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="********"
                           class="block w-full rounded-md border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-lg font-medium text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">Konfirmasi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="********"
                           class="block w-full rounded-md border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('password_confirmation')" />
                </div>

                <button type="submit"
                        class="mt-1 flex w-full items-center justify-center rounded-md bg-[#e8a9c2] px-4 py-3.5 text-base font-semibold text-white shadow-[0_10px_24px_rgba(232,169,194,0.35)] transition hover:bg-[#df97b3] focus:outline-none focus:ring-2 focus:ring-[#e8a9c2]/70 focus:ring-offset-2 focus:ring-offset-white">
                    Simpan Password Baru
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
