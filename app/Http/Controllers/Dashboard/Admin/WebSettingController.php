<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWebSettingRequest;
use App\Http\Requests\Admin\UpdateWebSettingRequest;
use App\Models\WebSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class WebSettingController extends Controller
{
    /**
     * Slug grup → label tampil. Urutan key = urutan tab di halaman.
     */
    public const GROUPS = [
        'identity' => 'Identitas & Brand',
        'home_hero' => 'Home · Hero',
        'home_sections' => 'Home · Sections',
        'nailist' => 'Halaman Nail Artist',
        'footer' => 'Lokasi, Kontak & Footer',
        'policy' => 'Kebijakan',
        'general' => 'Lainnya',
    ];

    /**
     * Key system-level (dikelola di tempat lain, mis. System Settings superadmin)
     * — tidak ditampilkan di halaman Web Settings.
     */
    public const SYSTEM_KEYS = ['email_domain'];

    public function index(): View
    {
        $this->authorize('web-setting.view');

        $all = WebSetting::query()
            ->whereNotIn('key', self::SYSTEM_KEYS)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->groupBy('group');

        // Urutkan grup sesuai GROUPS, hanya grup yang ada isinya.
        $groups = collect(self::GROUPS)
            ->filter(fn ($label, $slug) => $all->has($slug))
            ->map(fn ($label, $slug) => [
                'slug' => $slug,
                'label' => $label,
                'settings' => $all->get($slug),
            ])
            ->values();

        return view('dashboard.admin.web-settings.index', [
            'groups' => $groups,
            'groupLabels' => self::GROUPS,
        ]);
    }

    public function store(StoreWebSettingRequest $request): RedirectResponse
    {
        WebSetting::create($request->validated());

        return back()->with('toast', ['type' => 'success', 'title' => 'Setting Ditambahkan', 'message' => 'Web setting baru berhasil disimpan.']);
    }

    public function update(UpdateWebSettingRequest $request): RedirectResponse
    {
        foreach ($request->validated()['settings'] as $key => $value) {
            WebSetting::query()
                ->where('key', $key)
                ->update(['value' => $value]);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Perubahan Disimpan', 'message' => 'Web settings berhasil diperbarui.']);
    }

    public function destroy(WebSetting $webSetting): RedirectResponse
    {
        $this->authorize('web-setting.delete');

        $webSetting->delete();

        return back()->with('toast', ['type' => 'success', 'title' => 'Penghapusan Berhasil', 'message' => "Setting {$webSetting->key} berhasil dihapus."]);
    }
}
