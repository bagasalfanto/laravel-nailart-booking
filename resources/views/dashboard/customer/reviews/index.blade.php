<x-app-layout>
    <x-slot name="header">My Reviews</x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-semibold text-slate-900">Review Saya</h1>
            <p class="text-slate-500 mt-1">Berikan ulasan untuk reservasi yang sudah selesai.</p>
        </div>

        <div class="rounded-2xl bg-white border border-[#eedde2] p-6 mb-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Reservasi Selesai</h2>
            @if ($reservasis->isEmpty())
                <p class="text-sm text-slate-400 italic">Belum ada reservasi yang selesai.</p>
            @else
                <ul class="divide-y divide-[#f1e4e8]">
                    @foreach ($reservasis as $r)
                        <li class="py-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ $r->treatment->nama_jasa ?? '-' }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ \Illuminate\Support\Carbon::parse($r->tanggal)->format('d M Y') }}
                                    &middot; Nailist: {{ $r->nailist->user->full_name ?? '-' }}
                                </p>
                            </div>
                            @if ($r->review)
                                <span class="text-xs text-emerald-600 font-medium">Sudah direview &check;</span>
                            @else
                                <a href="{{ route('dashboard.my-reviews.create', $r) }}" class="rounded-lg bg-[#a23f66] px-4 py-2 text-xs font-medium text-white hover:opacity-90">+ Beri Review</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Review yang Sudah Saya Tulis</h2>
            @if ($reviews->isEmpty())
                <p class="text-sm text-slate-400 italic">Belum ada review.</p>
            @else
                <ul class="space-y-4">
                    @foreach ($reviews as $rv)
                        <li class="rounded-xl border border-[#f1e4e8] p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-1 text-amber-500">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= $rv->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                                <div class="flex items-center gap-3 text-xs">
                                    @if ($rv->is_featured)
                                        <span class="text-emerald-600 font-medium">Featured di Landing</span>
                                    @endif
                                    <a href="{{ route('dashboard.my-reviews.edit', $rv) }}" class="text-[#a23f66] hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('dashboard.my-reviews.destroy', $rv) }}" class="inline" onsubmit="return confirm('Hapus review ini?');">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-600 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </div>
                            <p class="text-sm text-slate-700">{{ $rv->content }}</p>
                            <p class="text-xs text-slate-400 mt-2">{{ $rv->created_at->diffForHumans() }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
