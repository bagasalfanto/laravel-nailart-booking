<x-guest-layout>
    <div class="space-y-4 sm:space-y-6">
        <div class="space-y-2 text-center">
            <h1 class="text-3xl sm:text-4xl" style="font-family: 'Cormorant Garamond', serif;">Welcome Back!</h1>
            <p class="text-base sm:text-lg text-[#b55a75]">Enter credentials to access your account.</p>
        </div>

        <div class="mx-auto w-full max-w-4xl rounded-2xl border border-white/80 bg-white/95 px-6 py-7 shadow-[0_18px_50px_rgba(205,163,173,0.18)] sm:px-10 sm:py-10">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#a05a73]">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5 text-left">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="block text-lg font-medium text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="hello@example.com" class="block w-full rounded-md border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-4">
                        <label for="password" class="block text-lg font-medium text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">Password</label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-[#ea7c9d] transition hover:text-[#d96b8d]">Forgot Password?</a>
                        @endif
                    </div>

                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="********" class="block w-full rounded-md border border-[#efd1d6] bg-[#fdf7f8] px-4 py-3 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <button type="submit" class="mt-1 flex w-full items-center justify-center rounded-md bg-[#e8a9c2] px-4 py-3.5 text-base font-semibold text-white shadow-[0_10px_24px_rgba(232,169,194,0.35)] transition hover:bg-[#df97b3] focus:outline-none focus:ring-2 focus:ring-[#e8a9c2]/70 focus:ring-offset-2 focus:ring-offset-white">Login</button>

                <p class="text-center text-sm text-[#2d2527]">
                    Don’t have account?
                    <a href="{{ route('register') }}" class="text-[#ea7c9d] transition hover:text-[#d96b8d]">Register here</a>
                </p>
            </form>
        </div>
    </div>
</x-guest-layout>
