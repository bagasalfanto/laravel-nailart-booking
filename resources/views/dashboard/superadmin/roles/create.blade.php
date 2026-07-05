<x-app-layout>
    <x-slot name="header">Role Baru</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('dashboard.roles.index') }}" class="text-sm text-slate-500 hover:text-[#a23f66]">&larr; Kembali</a>
            <h1 class="text-3xl font-semibold text-slate-900 mt-2">Buat Role Baru</h1>
        </div>

        <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
            <form method="POST" action="{{ route('dashboard.roles.store') }}">
                @include('dashboard.superadmin.roles._form')
            </form>
        </div>
    </div>
</x-app-layout>
