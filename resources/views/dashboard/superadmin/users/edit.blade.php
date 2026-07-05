<x-app-layout>
    <x-slot name="header">Edit User Role</x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('dashboard.users.index') }}" class="text-sm text-slate-500 hover:text-[#a23f66]">&larr; Kembali</a>
            <h1 class="text-3xl font-semibold text-slate-900 mt-2">{{ $user->full_name }}</h1>
            <p class="text-slate-500 mt-1">{{ $user->email }}</p>
        </div>

        <div class="rounded-2xl bg-white border border-[#eedde2] p-6">
            <form method="POST" action="{{ route('dashboard.users.update', $user) }}">
                @csrf
                @method('PUT')

                <h2 class="text-lg font-semibold text-slate-900 mb-4">Pilih Role</h2>

                <div class="space-y-3">
                    @foreach ($roles as $role)
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-[#f1e4e8] hover:bg-[#fdf2f5] cursor-pointer">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                   {{ in_array($role->name, $assignedRoles, true) ? 'checked' : '' }}
                                   class="mt-1 rounded border-slate-300 text-[#a23f66] focus:ring-[#a23f66]">
                            <div>
                                <p class="text-sm font-medium text-slate-800 capitalize">{{ $role->name }}</p>
                                <p class="text-xs text-slate-500">{{ $role->permissions->count() }} permission</p>
                            </div>
                        </label>
                    @endforeach
                </div>

                @error('roles')
                    <p class="text-sm text-rose-600 mt-3">{{ $message }}</p>
                @enderror

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('dashboard.users.index') }}" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Batal</a>
                    <button type="submit" class="rounded-lg bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
