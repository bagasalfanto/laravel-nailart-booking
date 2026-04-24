<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#f5edee] text-slate-800">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-40 w-64 bg-[#ede4e6] border-r border-[#e4d6da] transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 lg:flex lg:flex-col"
            >
                <div class="px-6 py-6">
                    <a href="{{ route('dashboard') }}" class="text-2xl font-semibold text-[#a23f66] tracking-tight">nailby.bilda</a>
                    <p class="text-xs text-slate-500 mt-1">Luxury Nail Studio</p>
                </div>

                <nav class="px-4 space-y-2">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-[#a23f66] bg-white/60">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 4h8v4h-8v-4z" />
                        </svg>
                        Overview
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-600 hover:bg-white/60 transition">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2" />
                            <path d="M16 3v4M8 3v4M3 10h18" />
                        </svg>
                        Booking
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-600 hover:bg-white/60 transition">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M7 12h10M12 7v10" />
                        </svg>
                        Portfolio
                    </a>
                </nav>

                <div class="mt-auto p-4">
                    <button class="w-full rounded-full bg-gradient-to-r from-[#a6456a] to-[#d86e94] py-3 text-sm font-medium text-white shadow-sm hover:opacity-95">
                        New Appointment
                    </button>
                </div>
            </aside>

            <div
                x-show="sidebarOpen"
                x-transition.opacity
                @click="sidebarOpen = false"
                class="fixed inset-0 z-30 bg-black/30 lg:hidden"
            ></div>

            <div class="flex-1 min-h-screen">
                @include('layouts.navigation')

                <main class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
