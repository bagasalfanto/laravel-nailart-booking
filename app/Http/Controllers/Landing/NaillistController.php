<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\Nailist;
use App\Models\WebSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NaillistController extends Controller
{
    public function index(Request $request): View
    {
        $settings = WebSetting::query()->pluck('value', 'key');

        $nailists = Nailist::query()
            ->with([
                'user:id,full_name,username,avatar',
                'specialties:id,name',
            ])
            ->active()
            ->get()
            ->sortBy(fn ($n) => $n->user?->full_name ?? '')
            ->values();

        return view('pages.landing.naillist', compact('nailists', 'settings'));
    }

    public function show(Nailist $nailist): View
    {
        // Profil nailist yang user-nya sudah dihapus/non-aktif tidak boleh diakses publik.
        if (! $nailist->user || $nailist->user->trashed() || $nailist->user->status !== 'active') {
            abort(404);
        }

        $nailist->load([
            'user:id,full_name,username,avatar',
            'specialties:id,name',
            'portfolios' => fn ($q) => $q->latest(),
        ]);

        // Reviews dari customer untuk reservasi nailist ini
        $reviews = \App\Models\Review::query()
            ->whereHas('reservasi', fn ($q) => $q->where('nailist_id', $nailist->id))
            ->with('customer.user:id,full_name,avatar')
            ->latest()
            ->limit(6)
            ->get();

        $settings = WebSetting::query()->pluck('value', 'key');

        return view('pages.landing.naillist-detail', compact('nailist', 'reviews', 'settings'));
    }
}
