<div
    x-data="{ open: false }"
    class="relative"
    @click.away="open = false"
>
    <button
        @click="open = !open"
        type="button"
        class="inline-flex items-center gap-2 rounded-full border border-[#eedde2] bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-[#fdf2f5] hover:text-[#a23f66] transition"
    >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        <span>{{ $label ?? 'Export' }}</span>
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-44 origin-top-right rounded-xl bg-white border border-[#eedde2] shadow-lg shadow-slate-200/50 z-50 py-1"
        style="display: none;"
    >
        @if (!empty($csvUrl))
            <a href="{{ $csvUrl }}"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 hover:bg-[#fdf8fa] hover:text-[#a23f66] transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                CSV
            </a>
        @endif

        @if (!empty($pdfUrl))
            <a href="{{ $pdfUrl }}"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-600 hover:bg-[#fdf8fa] hover:text-[#a23f66] transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        @endif

        @if (empty($csvUrl) && empty($pdfUrl))
            <p class="px-4 py-2.5 text-sm text-slate-400 italic">No export options</p>
        @endif
    </div>
</div>
