<section>
    <header>
        <h2 class="text-xl font-semibold text-slate-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-slate-600 mb-1">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="w-full rounded-xl border border-[#eadce0] bg-[#fbf5f7] px-4 py-2.5 text-slate-800 focus:border-[#d98aa9] focus:ring-[#d98aa9]" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-slate-600 mb-1">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="w-full rounded-xl border border-[#eadce0] bg-[#fbf5f7] px-4 py-2.5 text-slate-800 focus:border-[#d98aa9] focus:ring-[#d98aa9]" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-600 mb-1">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-xl border border-[#eadce0] bg-[#fbf5f7] px-4 py-2.5 text-slate-800 focus:border-[#d98aa9] focus:ring-[#d98aa9]" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#a6456a] to-[#d86e94] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95 transition">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
