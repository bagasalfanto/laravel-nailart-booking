<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Nails by Hilda') }}</title>

        {{-- Favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('images/icons/favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&family=cormorant-garamond:400,500,600,700&family=poppins:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased text-slate-800" style="font-family: 'Poppins', sans-serif;">
        <div class="min-h-screen overflow-hidden bg-[#fdeae6]" style="position: relative;">
            <div class="absolute inset-0" aria-hidden="true" style="pointer-events: none; z-index: 0;">
                <div class="absolute left-[-8rem] top-[-7rem] h-64 w-64 rounded-full bg-[#f1c1d0]/30 blur-3xl"></div>
                <div class="absolute right-[-6rem] bottom-[-8rem] h-72 w-72 rounded-full bg-[#f2d7db]/40 blur-3xl"></div>
            </div>

            <div class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8" style="position: relative; z-index: 10;">
                <div class="w-full max-w-3xl text-center">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @include('partials.toast')
    </body>
</html>
