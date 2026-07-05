<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Portfolio;
use App\Models\Review;
use App\Models\TreatmentKatalog;
use App\Models\WebSetting;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $settings = WebSetting::query()->pluck('value', 'key');

        // Section "Styles that speak for you": HANYA portfolio yang featured (max 4).
        // Kalau belum ada yang di-feature, section akan kosong (auto-hidden via @if di view).
        $portfolios = Portfolio::query()
            ->featured()
            ->whereHas('nailist', fn ($q) => $q->active())
            ->with('nailist.user')
            ->latest('featured_at')
            ->limit(4)
            ->get();

        // Treatment unggulan untuk section "Our Nail Services" (3 service teratas).
        $treatments = TreatmentKatalog::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('nama_jasa')
            ->limit(3)
            ->get();

        // FAQ aktif.
        $faqs = Faq::query()
            ->where('status', 'active')
            ->orderBy('created_at')
            ->limit(6)
            ->get();

        // Featured reviews untuk testimonial.
        $reviews = Review::query()
            ->featured()
            ->with('customer.user')
            ->latest('featured_at')
            ->limit(3)
            ->get();

        return view('pages.landing.home', compact(
            'settings',
            'portfolios',
            'treatments',
            'faqs',
            'reviews',
        ));
    }
}
