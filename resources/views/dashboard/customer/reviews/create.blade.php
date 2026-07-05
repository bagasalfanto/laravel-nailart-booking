<x-app-layout>
    <x-slot name="header">Beri Review</x-slot>

    <div class="max-w-2xl mx-auto">
        <a href="{{ route('dashboard.my-reviews.index') }}" class="text-sm text-slate-500 hover:text-[#a23f66]">&larr; Kembali</a>
        <h1 class="text-3xl font-semibold text-slate-900 mt-2">Review untuk {{ $reservasi->treatment->nama_jasa ?? 'Treatment' }}</h1>
        <p class="text-slate-500 mt-1 mb-6">{{ \Illuminate\Support\Carbon::parse($reservasi->tanggal)->format('d M Y') }}</p>

        <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
            <form method="POST" action="{{ route('dashboard.my-reviews.store', $reservasi) }}">
                @include('dashboard.customer.reviews._form')
            </form>
        </div>
    </div>
</x-app-layout>
