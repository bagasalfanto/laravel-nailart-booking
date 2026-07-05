@php
    $value = old("settings.{$setting->key}", $setting->value);
    $label = $setting->label ?: ucwords(str_replace(['_', '-'], ' ', $setting->key));
    $type  = $setting->type ?: 'text';
@endphp

<div class="{{ $type === 'textarea' ? 'md:col-span-2' : '' }}">
    <div class="flex items-center justify-between mb-1.5">
        <label for="setting-{{ $setting->key }}" class="block text-sm font-medium text-slate-700">{{ $label }}</label>
        @can('web-setting.delete')
            <button type="button"
                    data-delete-key="{{ $setting->key }}"
                    data-delete-url="{{ route('dashboard.web-settings.destroy', $setting) }}"
                    data-csrf="{{ csrf_token() }}"
                    class="text-xs text-rose-600 hover:underline">Hapus</button>
        @endcan
    </div>

    @if ($type === 'textarea')
        <textarea id="setting-{{ $setting->key }}" name="settings[{{ $setting->key }}]" rows="4"
                  class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">{{ $value }}</textarea>
    @elseif ($type === 'image')
        <input id="setting-{{ $setting->key }}" type="text" name="settings[{{ $setting->key }}]" value="{{ $value }}"
               placeholder="/images/... atau https://..."
               class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
        @if ($value)
            <img src="{{ $value }}" alt="preview" class="mt-2 h-12 rounded-lg border border-[#f1e4e8] bg-[#fdf2f5] object-contain"
                 onerror="this.style.display='none'">
        @endif
    @elseif ($type === 'url')
        <input id="setting-{{ $setting->key }}" type="url" name="settings[{{ $setting->key }}]" value="{{ $value }}"
               class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
    @else
        <input id="setting-{{ $setting->key }}" type="text" name="settings[{{ $setting->key }}]" value="{{ $value }}"
               class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
    @endif

    <p class="text-xs text-slate-400 mt-1 font-mono">{{ $setting->key }}</p>
    @error("settings.{$setting->key}")
        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
    @enderror
</div>
