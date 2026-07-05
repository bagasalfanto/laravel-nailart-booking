@csrf

<div class="mb-6">
    <label class="block text-sm font-medium text-slate-700 mb-2">Nama Role</label>
    <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}"
           {{ ($role->name ?? '') === 'superadmin' ? 'readonly' : '' }}
           class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
    @error('name')
        <p class="text-sm text-rose-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<h2 class="text-lg font-semibold text-slate-900 mb-3">Permissions</h2>

<div class="space-y-4">
    @foreach ($permissions as $group => $items)
        <div class="rounded-xl border border-[#f1e4e8] p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-700 capitalize">{{ str_replace('-', ' ', $group) }}</h3>
                <label class="text-xs text-slate-500 inline-flex items-center gap-1.5">
                    <input type="checkbox" class="rounded border-slate-300 text-[#a23f66] focus:ring-[#a23f66]"
                           onclick="this.closest('div').parentElement.querySelectorAll('input[name=&quot;permissions[]&quot;]').forEach(cb => cb.checked = this.checked)">
                    Pilih semua
                </label>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                @foreach ($items as $permission)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                               {{ in_array($permission->name, $assignedPermissions ?? [], true) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-[#a23f66] focus:ring-[#a23f66]">
                        <span class="text-slate-700">{{ $permission->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<div class="flex justify-end gap-3 mt-6">
    <a href="{{ route('dashboard.roles.index') }}" class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Batal</a>
    <button type="submit" class="rounded-lg bg-[#a23f66] px-5 py-2 text-sm font-medium text-white hover:opacity-90">Simpan</button>
</div>
