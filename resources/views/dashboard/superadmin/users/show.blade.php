<x-app-layout>
    <x-slot name="header">Detail User</x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ url()->previous() }}" class="text-sm text-slate-500 hover:text-[#a23f66]">&larr; Kembali</a>
            @role('superadmin')
                <a href="{{ route('dashboard.users.edit', $user) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-[#a23f66] px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7m-1.5-9.5a2.121 2.121 0 113 3L12 19l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Role
                </a>
            @endrole
        </div>

        {{-- Profile header card --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden mb-6">
            <div class="h-24 bg-gradient-to-r from-[#fdf2f5] via-[#f7e2ea] to-[#fdf2f5]"></div>
            <div class="px-6 pb-6 -mt-12">
                <div class="flex flex-col md:flex-row md:items-end gap-4">
                    <img src="{{ $user->avatar_url }}" alt="" class="w-24 h-24 rounded-2xl border-4 border-white shadow object-cover">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-semibold text-slate-900 truncate">{{ $user->full_name }}</h1>
                            @if ($user->status === 'active')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500">{{ '@'.$user->username }}</p>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach ($user->roles as $role)
                                <span class="inline-flex items-center rounded-full bg-[#fdf2f5] px-2.5 py-1 text-xs font-medium text-[#a23f66] capitalize">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Account info --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Informasi Akun</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs text-slate-500 uppercase tracking-wide">Email</dt>
                    <dd class="text-slate-800 mt-1">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500 uppercase tracking-wide">No. HP</dt>
                    <dd class="text-slate-800 mt-1">{{ $user->phone_number ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500 uppercase tracking-wide">Email Verified</dt>
                    <dd class="text-slate-800 mt-1">
                        {{ $user->email_verified_at ? $user->email_verified_at->format('d M Y, H:i') : 'Belum diverifikasi' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500 uppercase tracking-wide">Login dengan Google</dt>
                    <dd class="text-slate-800 mt-1">{{ $user->google_id ? 'Ya' : 'Tidak' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500 uppercase tracking-wide">Terdaftar</dt>
                    <dd class="text-slate-800 mt-1">{{ $user->created_at?->format('d M Y, H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500 uppercase tracking-wide">Update Terakhir</dt>
                    <dd class="text-slate-800 mt-1">{{ $user->updated_at?->format('d M Y, H:i') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Profile by role --}}
        @if ($user->admin)
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Profile Admin</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide">Kode Admin</dt>
                        <dd class="text-slate-800 mt-1 font-mono">{{ $user->admin->kode_admin ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        @endif

        @if ($user->nailist)
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Profile Nailist</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm mb-4">
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide">Title</dt>
                        <dd class="text-slate-800 mt-1">{{ $user->nailist->title ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide">Total Portfolio</dt>
                        <dd class="text-slate-800 mt-1">{{ $user->nailist->portfolios->count() }} karya</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-xs text-slate-500 uppercase tracking-wide">Bio</dt>
                        <dd class="text-slate-800 mt-1 whitespace-pre-line">{{ $user->nailist->bio ?: '—' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-xs text-slate-500 uppercase tracking-wide">Specialties</dt>
                        <dd class="mt-1 flex flex-wrap gap-1.5">
                            @forelse ($user->nailist->specialties as $sp)
                                <span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700">{{ $sp->name }}</span>
                            @empty
                                <span class="text-xs text-slate-400 italic">Belum ada specialty</span>
                            @endforelse
                        </dd>
                    </div>
                </dl>
            </div>
        @endif

        @if ($user->customer)
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6 mb-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Profile Customer</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide">Customer ID</dt>
                        <dd class="text-slate-800 mt-1 font-mono text-xs">{{ $user->customer->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 uppercase tracking-wide">Bergabung Sejak</dt>
                        <dd class="text-slate-800 mt-1">{{ $user->customer->created_at?->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>
        @endif
    </div>
</x-app-layout>
