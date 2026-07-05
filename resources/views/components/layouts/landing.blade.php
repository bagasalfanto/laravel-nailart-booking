<!DOCTYPE html>
<html dir="ltr" data-assets-path="{{ config('app.url') }}/" class="layout-navbar-fixed layout-wide layout-menu-100vh"
    lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark" data-template="front-pages" data-skin="default">
    <head>
        <meta charset="utf-8">
        <meta name="coverage" content="Worldwide">
        <meta name="distribution" content="Global">
        <meta name="robots" content="index, follow">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <link rel="canonical" href="{{ config('app.url') }}">

        <title>nailart booking</title>

        {{-- Favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('images/icons/favicon.png') }}">

        {{-- Vite assets (Tailwind + Alpine.js) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body>
        <x-landing.navbar />

        <div data-bs-spy="scroll" class="scrollspy-example">
            {{ $slot }}
        </div>

        {{-- Cek apakah sedang berada di route 'home' --}}
        @if(request()->routeIs('home'))
            <x-landing.footer />
        @endif

        <x-utils.noscript />

        @stack('scripts')
    </body>
</html>