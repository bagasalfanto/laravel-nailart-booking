<x-guest-layout>
    <div class="space-y-6 sm:space-y-8">
        {{-- Header --}}
        <div class="space-y-3 text-center">
            <h1 class="text-4xl sm:text-5xl text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                Reset Password
            </h1>
            <p class="text-base sm:text-lg text-[#a8526b] max-w-md mx-auto leading-relaxed">
                Enter the email address you want to change the password for.
            </p>
        </div>

        {{-- Card --}}
        <div class="mx-auto w-full max-w-2xl rounded-2xl bg-white px-6 py-8 shadow-[0_18px_50px_rgba(205,163,173,0.18)] sm:px-10 sm:py-10">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6 text-left">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="block text-lg font-semibold text-[#221d1f]" style="font-family: 'Cormorant Garamond', serif;">
                        Email
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                           placeholder="Enter your email"
                           class="block w-full rounded-lg border border-[#efd1d6] bg-[#faf3ed] px-4 py-3.5 text-sm text-[#3a3133] outline-none transition placeholder:text-[#c9bcc0] focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <button type="submit"
                        class="flex w-full items-center justify-center rounded-lg bg-[#e8a3b3] px-4 py-3.5 text-base font-semibold text-white shadow-sm transition hover:bg-[#df8da0] focus:outline-none focus:ring-2 focus:ring-[#e8a3b3]/70 focus:ring-offset-2 focus:ring-offset-white"
                        style="font-family: 'Cormorant Garamond', serif;">
                    Next
                </button>

                <p class="text-center text-sm text-[#3a3133]">
                    Remember your password?
                    <a href="{{ route('login') }}" class="text-[#ea7c9d] font-medium hover:text-[#d96b8d] hover:underline">Login here</a>
                </p>
            </form>
        </div>
    </div>
</x-guest-layout>
