<nav class="h-16 bg-white border-b border-[#efe4e7] px-4 sm:px-6 lg:px-8">
    <div class="h-full flex items-center justify-between gap-3">
        @php
            $displayName = Auth::user()->full_name ?? Auth::user()->username ?? Auth::user()->email;
            $avatarUrl = Auth::user()->avatar_url;
        @endphp
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden inline-flex items-center justify-center h-10 w-10 rounded-lg text-slate-600 hover:bg-[#f9f3f5]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div>
                @isset($header)
                    <p class="text-base font-semibold text-[#D56B8D]">{{ strip_tags($header) }}</p>
                @else
                    <p class="text-base font-semibold text-[#D56B8D]">Dashboard</p>
                @endisset
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            {{-- Kembali ke landing page — tersedia untuk semua role --}}
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-1.5 rounded-full border border-[#efd1d6] bg-white px-3 sm:px-4 h-10 text-sm font-medium text-[#a23f66] hover:bg-[#fdf2f5] transition">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11l9-8 9 8" /><path d="M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
                </svg>
                <span class="hidden sm:inline">Beranda</span>
            </a>

            <x-dropdown align="right" width="56" contentClasses="py-0 bg-white border border-[#ede4e6] shadow-lg overflow-hidden">
                <x-slot name="trigger">
                    <button class="h-10 w-10 rounded-full overflow-hidden ring-2 ring-white/70 shadow-sm focus:outline-none focus:ring-[#d98aa9]">
                        <img
                            src="{{ $avatarUrl }}"
                            alt="{{ $displayName }}"
                            class="h-full w-full object-cover"
                        >
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="px-4 py-3 border-b border-[#ede4e6] bg-[#f9f5f7]">
                        <p class="text-sm font-semibold text-[#221d1f]">{{ $displayName }}</p>
                        <p class="text-xs text-[#8f87a2]">{{ Auth::user()->email }}</p>
                    </div>

                    <a href="{{ route('home') }}" class="block px-4 py-2.5 text-sm text-[#221d1f] hover:bg-[#f9f5f7] transition">
                        {{ __('Kembali ke Beranda') }}
                    </a>

                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-[#221d1f] hover:bg-[#f9f5f7] transition">
                        {{ __('Profile') }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}"
                                class="block px-4 py-2.5 text-sm text-[#221d1f] hover:bg-[#f9f5f7] transition"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </a>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</nav>
