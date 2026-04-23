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
    </head>
    <body>
        <x-landing.navbar />

        <div data-bs-spy="scroll" class="scrollspy-example">
            {{ $slot }}
        </div>

        <x-landing.footer />

        <x-utils.noscript />

    </body>
</html>
