<nav class="h-16 bg-white border-b border-[#efe4e7] px-4 sm:px-6 lg:px-8">
    <div class="h-full flex items-center justify-between gap-3">
        @php
            $displayName = Auth::user()->full_name ?? Auth::user()->username ?? Auth::user()->email;
            $avatarUrl = Auth::user()->avatar
                ? asset('storage/' . Auth::user()->avatar)
                : 'https://i.pravatar.cc/80?u=' . urlencode((string) Auth::id());
        @endphp
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden inline-flex items-center justify-center h-10 w-10 rounded-lg text-slate-600 hover:bg-[#f9f3f5]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div>
                <p class="text-sm font-semibold text-[#a23f66]">Nailist Dashboard</p>
                @isset($header)
                    <div class="hidden md:block text-xs text-[#8f87a2] leading-none">{{ strip_tags($header) }}</div>
                @endisset
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <div class="hidden md:flex items-center rounded-full border border-[#ece4e8] bg-white px-4 py-2 w-80">
                <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8" />
                    <path d="M21 21l-4.35-4.35" />
                </svg>
                <input
                    type="text"
                    placeholder="Search appointments..."
                    class="ml-2 w-full bg-transparent p-0 text-sm text-slate-600 placeholder:text-slate-300 border-0 focus:ring-0"
                >
            </div>

            <button class="h-10 w-10 inline-flex items-center justify-center rounded-full text-[#54627a] hover:bg-[#f9f3f5]" aria-label="Notifications">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5" />
                    <path d="M9 17a3 3 0 0 0 6 0" />
                </svg>
            </button>

            <button class="h-10 w-10 inline-flex items-center justify-center rounded-full text-[#54627a] hover:bg-[#f9f3f5]" aria-label="Settings">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="3" />
                    <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.04 1.56V21a2 2 0 0 1-4 0v-.09a1.7 1.7 0 0 0-1.04-1.56 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.56-1.04H3a2 2 0 0 1 0-4h.09A1.7 1.7 0 0 0 4.65 8.9a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.87.34h.01A1.7 1.7 0 0 0 10.09 3V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1.04 1.56 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.87v.01A1.7 1.7 0 0 0 21 10.09H21a2 2 0 1 1 0 4h-.09A1.7 1.7 0 0 0 19.35 15z" />
                </svg>
            </button>

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
