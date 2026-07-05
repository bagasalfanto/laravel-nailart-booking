<x-app-layout>
    <x-slot name="header">Superadmin Control Panel</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-slate-900">Superadmin Dashboard</h1>
            <p class="text-slate-500 mt-1">Kelola seluruh user, role, dan permission sistem.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
                <p class="text-sm text-slate-500">Total Users</p>
                <p class="text-3xl font-semibold text-[#a23f66] mt-2">{{ $totalUsers }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
                <p class="text-sm text-slate-500">Total Roles</p>
                <p class="text-3xl font-semibold text-[#a23f66] mt-2">{{ $totalRoles }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
                <p class="text-sm text-slate-500">Total Permissions</p>
                <p class="text-3xl font-semibold text-[#a23f66] mt-2">{{ $totalPermissions }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Users per Role</h2>
                    <a href="{{ route('dashboard.roles.index') }}" class="text-sm text-[#a23f66] hover:underline">Kelola Roles &rarr;</a>
                </div>
                <ul class="divide-y divide-[#f1e4e8]">
                    @foreach ($usersPerRole as $role)
                        <li class="flex items-center justify-between py-3">
                            <span class="text-sm font-medium text-slate-700 capitalize">{{ $role->name }}</span>
                            <span class="text-sm text-slate-500">{{ $role->users_count }} user</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Recent Users</h2>
                    <a href="{{ route('dashboard.users.index') }}" class="text-sm text-[#a23f66] hover:underline">Lihat Semua &rarr;</a>
                </div>
                <ul class="divide-y divide-[#f1e4e8]">
                    @foreach ($recentUsers as $user)
                        <li class="flex items-center justify-between py-3">
                            <div>
                                <p class="text-sm font-medium text-slate-700">{{ $user->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                            </div>
                            <div class="flex flex-wrap gap-1 justify-end">
                                @forelse ($user->roles as $role)
                                    <span class="inline-flex items-center rounded-full bg-[#fdf2f5] px-2.5 py-1 text-xs font-medium text-[#a23f66]">{{ $role->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-400 italic">no role</span>
                                @endforelse
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('dashboard.users.index') }}" class="rounded-2xl bg-gradient-to-r from-[#a6456a] to-[#d86e94] p-6 text-white hover:opacity-95 transition">
                <p class="text-sm uppercase tracking-wide opacity-80">Kelola User</p>
                <p class="text-xl font-semibold mt-1">Daftar User &amp; Assign Role</p>
            </a>
            <a href="{{ route('dashboard.roles.index') }}" class="rounded-2xl bg-white border border-[#eedde2] p-6 hover:bg-[#fdf2f5] transition">
                <p class="text-sm uppercase tracking-wide text-slate-400">Kelola Role</p>
                <p class="text-xl font-semibold text-slate-800 mt-1">Daftar Role &amp; Permission</p>
            </a>
        </div>
    </div>
</x-app-layout>
