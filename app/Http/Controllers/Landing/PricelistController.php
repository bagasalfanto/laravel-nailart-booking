<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\TreatmentCategory;
use Illuminate\Contracts\View\View;

class PricelistController extends Controller
{
    public function index(): View
    {
        $categories = TreatmentCategory::query()
            ->where('is_active', true)
            ->with(['activeTreatments'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('pages.landing.pricelist', compact('categories'));
    }
}
