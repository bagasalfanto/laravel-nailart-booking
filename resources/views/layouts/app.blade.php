<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@isset($header){{ strip_tags($header) }} — @endisset{{ config('app.name', 'Nails by Hilda') }}</title>

        
        {{-- Favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('images/icons/favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#fcf6f5] text-slate-800" style="font-family: 'Poppins', sans-serif;">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                class="fixed inset-y-0 left-0 z-40 w-60 bg-[#fbeae8] transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 lg:flex lg:flex-col"
            >
                <div class="px-6 py-7">
                    <a href="{{ route('dashboard.home') }}" class="block">
                        @php
                            $isAdmin = auth()->check() && auth()->user()->hasAnyRole(['admin', 'superadmin']);
                            $sidebarTagline = $isAdmin ? 'Management Atelier' : (auth()->check() && auth()->user()->hasRole('nailist') ? 'Independent Nail Artist' : 'Luxury Nail Studio');
                        @endphp
                        <p class="text-xl font-semibold text-[#D56B8D] tracking-tight">nailby.hilda</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $sidebarTagline }}</p>
                    </a>
                </div>

                <nav class="px-4 space-y-2">
                    <a href="{{ route('dashboard.home') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.home') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                            <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                            <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                            <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                        </svg>
                        Overview
                    </a>

                    @role('admin|superadmin')
                        <p class="px-4 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Master Data</p>
                        <a href="{{ route('dashboard.users.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.users.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            User Management
                        </a>
                    @endrole

                    @role('superadmin')
                        <a href="{{ route('dashboard.roles.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.roles.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M12 2l9 4v6c0 5-4 9-9 10-5-1-9-5-9-10V6l9-4z" />
                                <path d="M9 12l2 2 4-4" />
                            </svg>
                            Roles &amp; Permissions
                        </a>
                        <a href="{{ route('dashboard.activity-log.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.activity-log.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
                                <rect x="9" y="3" width="6" height="4" rx="1" />
                                <path stroke-linecap="round" d="M9 12h6M9 16h4" />
                            </svg>
                            Activity Log
                        </a>
                        <a href="{{ route('dashboard.system-settings.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.system-settings.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="3" />
                                <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.04 1.56V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-1.04-1.56 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.7 1.7 0 0 0 .34-1.87 1.7 1.7 0 0 0-1.56-1.04H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.65 8.9a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.87.34h.01A1.7 1.7 0 0 0 10.09 3V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1.04 1.56 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.87v.01A1.7 1.7 0 0 0 21 10.09H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.56 1.04z" />
                            </svg>
                            System Settings
                        </a>
                    @endrole

                    @role('nailist')
                        <a href="{{ route('dashboard.nailist-bookings.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.nailist-bookings.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="16" rx="2" />
                                <path d="M16 3v4M8 3v4M3 10h18" />
                            </svg>
                            Booking
                        </a>
                    @endrole

                    @role('nailist')
                        <a href="{{ route('dashboard.portfolio.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.portfolio.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <path d="M21 15l-5-5L5 21" />
                            </svg>
                            Portfolio
                        </a>
                    @endrole

                    @role('admin|superadmin')
                        <a href="{{ route('dashboard.reviews.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.reviews.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z" />
                            </svg>
                            Reviews
                        </a>
                        <a href="{{ route('dashboard.specialties.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.specialties.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                                <line x1="7" y1="7" x2="7.01" y2="7" />
                            </svg>
                            Specialties
                        </a>
                        <a href="{{ route('dashboard.treatments.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.treatments.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M9 11l3 3L22 4" />
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                            </svg>
                            Treatment Settings
                        </a>
                        <a href="{{ route('dashboard.portfolio-moderation.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.portfolio-moderation.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M10 2l2.5 5 5.5.8-4 3.9 1 5.5L10 14.7 4.9 17.2l1-5.5L2 7.8l5.5-.8L10 2z" fill="currentColor" fill-opacity="0.1"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 2l2.5 5 5.5.8-4 3.9 1 5.5L10 14.7 4.9 17.2l1-5.5L2 7.8l5.5-.8L10 2z"/>
                            </svg>
                            Portfolio Moderation
                        </a>
                        <a href="{{ route('dashboard.payment-monitoring.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.payment-monitoring.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="2" y="6" width="20" height="13" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2 11h20M6 15h2M12 15h4" />
                            </svg>
                            Payment Monitoring
                        </a>
                        <a href="{{ route('dashboard.charm-management.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.charm-management.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="3" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4l2 4-2 1-2-1 2-4zM12 20l-2-4 2-1 2 1-2 4zM4 12l4-2 1 2-1 2-4-2zM20 12l-4 2-1-2 1-2 4 2z" />
                            </svg>
                            Charm Management
                        </a>
                        <a href="{{ route('dashboard.web-settings.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.web-settings.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="3" />
                                <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.04 1.56V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-1.04-1.56 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.7 1.7 0 0 0 .34-1.87 1.7 1.7 0 0 0-1.56-1.04H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.65 8.9a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.87.34h.01A1.7 1.7 0 0 0 10.09 3V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1.04 1.56 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.87v.01A1.7 1.7 0 0 0 21 10.09H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.56 1.04z" />
                            </svg>
                            Web Settings
                        </a>
                    @endrole

                    @role('customer')
                        <a href="{{ route('dashboard.my-reviews.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-[#a23f66] transition {{ request()->routeIs('dashboard.my-reviews.*') ? 'bg-white text-[#a23f66] shadow-sm font-semibold' : '' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z" />
                            </svg>
                            Review Saya
                        </a>
                    @endrole
                </nav>

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

        @include('partials.toast')
    </body>
</html>
