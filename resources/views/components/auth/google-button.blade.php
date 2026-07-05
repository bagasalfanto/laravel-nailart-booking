@props(['label' => 'Continue with Google'])

<div class="space-y-4">
    <a href="{{ route('auth.google.redirect') }}"
       class="flex w-full items-center justify-center gap-3 rounded-md border border-[#efd1d6] bg-white px-4 py-3 text-sm font-medium text-[#3a3133] shadow-sm transition hover:bg-[#fdf7f8] focus:outline-none focus:ring-2 focus:ring-[#f0c5d3]/70">
        <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.6-6 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.6 6.1 29.6 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/>
            <path fill="#FF3D00" d="M6.3 14.1l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.6 6.1 29.6 4 24 4 16.3 4 9.7 8.3 6.3 14.1z"/>
            <path fill="#4CAF50" d="M24 44c5.5 0 10.4-2.1 14.1-5.5l-6.5-5.5c-2 1.4-4.6 2.3-7.6 2.3-5.3 0-9.7-3.4-11.3-8l-6.6 5.1C9.5 39.6 16.2 44 24 44z"/>
            <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.1-4 5.4l6.5 5.5c-.5.5 7.2-5.3 7.2-14.9 0-1.3-.1-2.4-.4-3.5z"/>
        </svg>
        <span>{{ $label }}</span>
    </a>

    <div class="relative">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#efd1d6]"></div></div>
        <div class="relative flex justify-center"><span class="bg-white/95 px-3 text-xs uppercase tracking-wider text-[#b55a75]">atau</span></div>
    </div>
</div>
