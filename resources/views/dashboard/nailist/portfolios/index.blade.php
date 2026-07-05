<x-app-layout>
    <x-slot name="header">Portfolio</x-slot>

    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Portfolio</h1>
                <p class="text-slate-500 mt-1">Showcase karya nail art Anda untuk dilihat customer.</p>
            </div>
            <a href="{{ route('dashboard.portfolio.create') }}"
               class="inline-flex items-center gap-2 rounded-full bg-[#a23f66] px-4 py-2.5 text-sm font-medium text-white hover:opacity-90 shadow-sm self-start md:self-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>
                Tambah Portfolio
            </a>
        </div>

        @if ($portfolios->isEmpty())
            {{-- Empty state --}}
            <div class="rounded-2xl bg-white border border-[#eedde2] py-16 px-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#fdf2f5] mb-4">
                    <svg class="w-8 h-8 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-slate-900">Belum ada portfolio</h2>
                <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">Mulai upload karya pertama Anda — gambar dan deskripsi singkat akan tampil sebagai showcase.</p>
                <a href="{{ route('dashboard.portfolio.create') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-[#a23f66] px-5 py-2.5 text-sm font-medium text-white hover:opacity-90 shadow-sm mt-5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>
                    Tambah Portfolio
                </a>
            </div>
        @else
            {{-- Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($portfolios as $p)
                    <article class="group rounded-2xl bg-white border border-[#eedde2] overflow-hidden hover:shadow-md transition">
                        <div class="relative aspect-[4/3] bg-[#fdf2f5] overflow-hidden">
                            @if ($p->gambar_full_url)
                                <img src="{{ $p->gambar_full_url }}" alt="{{ $p->judul }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                     loading="lazy">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-slate-300">
                                    <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21"/></svg>
                                </div>
                            @endif
                        </div>

                        <div class="p-4">
                            <h3 class="font-semibold text-slate-900 truncate" title="{{ $p->judul }}">{{ $p->judul }}</h3>
                            <p class="text-sm text-slate-500 mt-1 line-clamp-2 min-h-[2.5em]">
                                {{ $p->deskripsi ?: '— Tidak ada deskripsi —' }}
                            </p>

                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-[#f1e4e8]">
                                <span class="text-xs text-slate-400">{{ $p->created_at?->diffForHumans() }}</span>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('dashboard.portfolio.edit', $p) }}"
                                       class="inline-flex items-center gap-1 rounded-md border border-[#eedde2] px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-[#fdf2f5] hover:text-[#a23f66]">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7m-1.5-9.5a2.121 2.121 0 113 3L12 19l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </a>
                                    <button type="button"
                                            data-delete-portfolio
                                            data-action="{{ route('dashboard.portfolio.destroy', $p) }}"
                                            data-name="{{ $p->judul }}"
                                            class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-100">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7m5 4v6m4-6v6M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M3 7h18"/></svg>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Hidden delete form (di-isi via JS) --}}
    <form id="delete-portfolio-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('[data-delete-portfolio]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const result = await Swal.fire({
                    title: 'Hapus Portfolio?',
                    html: `Portfolio <b>${btn.dataset.name}</b> akan dihapus permanen.`,
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Hapus',
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#94a3b8',
                    reverseButtons: true,
                });

                if (result.isConfirmed) {
                    const form = document.getElementById('delete-portfolio-form');
                    form.action = btn.dataset.action;
                    form.submit();
                }
            });
        });
    </script>
</x-app-layout>

