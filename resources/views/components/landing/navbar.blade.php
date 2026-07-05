@php
    $user = auth()->user();
    $isCustomer = $user && $user->hasRole('customer');
    $isStaff    = $user && $user->hasAnyRole(['admin', 'superadmin', 'nailist']);

    $siteTitle = \App\Models\WebSetting::query()->where('key', 'site_title')->value('value') ?: 'nailby.hilda';

    $menus = [
        ['label' => 'Home',        'route' => 'home',      'pattern' => 'home'],
        ['label' => 'Pricelist',   'route' => 'pricelist', 'pattern' => 'pricelist'],
        ['label' => 'Nail Artist', 'route' => 'naillist',  'pattern' => 'naillist*'],
        ['label' => 'Schedule',    'route' => 'schedule',  'pattern' => 'schedule'],
    ];
    if ($isCustomer) {
        $menus[] = ['label' => 'Appointment', 'route' => 'customer.appointments.index', 'pattern' => 'customer.appointments.*'];
    }
@endphp

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    .nailby-nav { font-family: 'Poppins', system-ui, sans-serif; }
    .nailby-nav .active-link { color: #D56B8D; font-weight: 500; }
    .nailby-nav .active-link::after {
        content: '';
        display: block;
        width: 18px;
        height: 3px;
        background-color: #D56B8D;
        border-radius: 99px;
        margin: 3px auto 0;
    }
</style>

<header x-data="{ mobileOpen: false, profileOpen: false }"
        class="nailby-nav sticky top-0 z-50 px-4 sm:px-6 pt-4">

    <nav class="max-w-5xl mx-auto bg-white/70 backdrop-blur-md px-6 md:px-8 py-3 rounded-full flex items-center justify-between shadow-sm border border-white/50">

        {{-- Logo --}}
        <div class="flex-1 flex items-center">
            <a href="{{ route('home') }}" class="text-[#D56B8D] text-lg md:text-xl font-semibold tracking-tight">
                {{ $siteTitle }}
            </a>
        </div>

        {{-- Menu Tengah (desktop) --}}
        <ul class="hidden md:flex flex-[2] justify-center items-center gap-8 text-[15px]">
            @foreach ($menus as $menu)
                <li class="{{ request()->routeIs($menu['pattern']) ? 'active-link' : '' }}">
                    <a href="{{ route($menu['route']) }}"
                       class="{{ request()->routeIs($menu['pattern']) ? 'text-[#D56B8D]' : 'text-gray-600 hover:text-[#D56B8D]' }} transition-colors">
                        {{ $menu['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Bagian Kanan (desktop) --}}
        <div class="hidden md:flex flex-1 items-center justify-end gap-3 text-[15px]">
            @guest
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-[#D56B8D] transition-colors px-2">Login</a>
                <a href="{{ route('register') }}"
                   class="bg-[#DF7D9E] text-white px-6 py-2 rounded-full flex items-center gap-2 hover:bg-[#d46a8d] transition-all shadow-md shadow-pink-200/50 whitespace-nowrap">
                    Register
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="7" y1="17" x2="17" y2="7"></line>
                        <polyline points="7 7 17 7 17 17"></polyline>
                    </svg>
                </a>
            @endguest

            @auth
                @if ($isCustomer)
                    <a href="{{ route('booking.step1') }}"
                    class="bg-[#DF7D9E] text-white px-4 py-2 rounded-full inline-flex items-center gap-1.5 hover:bg-[#d46a8d] transition-all shadow-md shadow-pink-200/50 text-[14px] whitespace-nowrap flex-none w-max">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        New Appointment
                    </a>
                @endif

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center justify-center rounded-full transition-transform hover:scale-105 focus:outline-none">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}"
                            class="w-10 h-10 rounded-full object-cover shadow-sm border-2 border-transparent hover:border-pink-200 transition-colors">
                    </button>
                    <div x-show="open" x-transition @click.outside="open = false" x-cloak
                         class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-lg border border-gray-100 py-2 text-[14px]">
                        <div class="px-4 py-2 border-b border-gray-100 mb-1">
                            <p class="text-gray-800 font-medium truncate">{{ $user->full_name }}</p>
                            <p class="text-gray-400 text-[12px] truncate">{{ $user->email }}</p>
                        </div>
                        @if ($isStaff)
                            <a href="{{ route('dashboard.home') }}" class="block px-4 py-2 text-gray-700 hover:bg-pink-50 hover:text-[#D56B8D]">Dashboard</a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-pink-50 hover:text-[#D56B8D]">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-pink-50 hover:text-[#D56B8D]">Logout</button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>

        {{-- Burger (mobile) --}}
        <button type="button" @click="mobileOpen = true"
                class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-full text-[#D56B8D] hover:bg-pink-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </nav>

    {{-- Mobile slide-in panel --}}
    <div x-show="mobileOpen" x-transition.opacity x-cloak
         class="fixed inset-0 z-40 md:hidden bg-black/30"
         @click="mobileOpen = false"></div>

    <aside x-show="mobileOpen" x-cloak
           x-transition:enter="transition transform ease-out duration-200"
           x-transition:enter-start="translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition transform ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="translate-x-full"
           class="fixed top-0 right-0 z-50 h-full w-4/5 max-w-xs bg-white shadow-xl md:hidden flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <span class="text-[#D56B8D] font-semibold">{{ $siteTitle }}</span>
            <button @click="mobileOpen = false" class="w-9 h-9 rounded-full hover:bg-pink-50 text-gray-500 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <nav class="flex-1 px-5 py-4 flex flex-col gap-1 text-[15px]">
            @foreach ($menus as $menu)
                <a href="{{ route($menu['route']) }}"
                   class="px-3 py-2 rounded-lg {{ request()->routeIs($menu['pattern']) ? 'bg-pink-50 text-[#D56B8D] font-medium' : 'text-gray-700 hover:bg-pink-50 hover:text-[#D56B8D]' }}">
                    {{ $menu['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="px-5 py-4 border-t border-gray-100 flex flex-col gap-2 text-[14px]">
            @guest
                <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-pink-50 hover:text-[#D56B8D]">Login</a>
                <a href="{{ route('register') }}"
                   class="bg-[#DF7D9E] text-white text-center px-5 py-2 rounded-full hover:bg-[#d46a8d] transition-all">
                    Register
                </a>
            @endguest

            @auth
                <div class="flex items-center gap-3 px-2 py-2">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}"
                         class="w-10 h-10 rounded-full object-cover border border-white shadow-sm">
                    <div class="leading-tight">
                        <div class="text-gray-800 font-medium">{{ $user->full_name }}</div>
                        <div class="text-gray-400 text-[12px]">{{ $user->email }}</div>
                    </div>
                </div>
                @if ($isStaff)
                    <a href="{{ route('dashboard.home') }}" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-pink-50 hover:text-[#D56B8D]">Dashboard</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="px-3 py-2 rounded-lg text-gray-700 hover:bg-pink-50 hover:text-[#D56B8D]">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-gray-700 hover:bg-pink-50 hover:text-[#D56B8D]">Logout</button>
                </form>
            @endauth
        </div>
    </aside>
</header>
