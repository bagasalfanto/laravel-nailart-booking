<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nailist;
use App\Models\Portfolio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PortfolioModerationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('portfolio.view');

        // Hanya nailist aktif (user belum di-soft-delete) yang muncul di filter & dihitung.
        $nailists = Nailist::query()
            ->active()
            ->with('user:id,full_name')
            ->get()
            ->map(fn ($n) => ['id' => $n->id, 'name' => $n->user?->full_name ?? '—'])
            ->sortBy('name')
            ->values();

        $stats = [
            'total' => $this->baseQuery()->count(),
            'featured' => $this->baseQuery()->where('is_featured', true)->count(),
        ];

        return view('dashboard.admin.portfolio-moderation.index', compact('nailists', 'stats'));
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('portfolio.view');

        $query = $this->baseQuery()
            ->with('nailist.user:id,full_name')
            ->select('portfolios.*');

        if ($request->filled('f_nailist')) {
            $query->where('nailist_id', $request->input('f_nailist'));
        }

        if ($request->input('f_filter') === 'featured') {
            $query->where('is_featured', true);
        } elseif ($request->input('f_filter') === 'not_featured') {
            $query->where('is_featured', false);
        }

        if ($request->filled('f_judul')) {
            $query->where('judul', 'like', '%'.$request->input('f_judul').'%');
        }

        return DataTables::of($query)
            ->addColumn('thumb', function (Portfolio $p) {
                $src = $p->gambar_full_url ?? '';
                if ($src === '') {
                    return '<div class="h-12 w-16 rounded-lg bg-[#fdf2f5]"></div>';
                }
                $src = str_replace('"', '&quot;', $src);

                return '<img src="'.$src.'" alt="" loading="lazy" class="h-12 w-16 rounded-lg object-cover border border-[#f1e4e8]">';
            })
            ->addColumn('judul_label', function (Portfolio $p) {
                $judul = e($p->judul ?? '—');
                $desc = e(\Illuminate\Support\Str::limit((string) $p->deskripsi, 80));

                return '<p class="font-medium text-slate-800">'.$judul.'</p>'
                    .($desc !== '' ? '<p class="text-xs text-slate-500">'.$desc.'</p>' : '');
            })
            ->addColumn('nailist_name', fn (Portfolio $p) => e($p->nailist?->user?->full_name ?? '—'))
            ->addColumn('status_badge', function (Portfolio $p) {
                return $p->is_featured
                    ? '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium text-amber-700 bg-amber-50">★ Featured</span>'
                    : '<span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium text-slate-600 bg-slate-50">☆ Belum</span>';
            })
            ->addColumn('created', fn (Portfolio $p) => e($p->created_at?->isoFormat('D MMM Y') ?? '—'))
            ->addColumn('actions', function (Portfolio $p) {
                if (! auth()->user()->can('portfolio.update')) {
                    return '';
                }

                $label = $p->is_featured ? '★ Lepas dari Home' : 'Tampilkan di Home';
                $cls = $p->is_featured ? 'bg-amber-100 text-amber-700' : 'bg-[#a23f66] text-white';

                return '<button type="button" data-feature-id="'.e($p->id).'" class="rounded-lg px-3 py-1.5 text-xs font-medium '.$cls.' hover:opacity-90 whitespace-nowrap">'.$label.'</button>';
            })
            ->orderColumn('created', 'created_at $1')
            ->rawColumns(['thumb', 'judul_label', 'status_badge', 'actions'])
            ->make(true);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        $this->authorize('portfolio.view');

        $query = $this->baseQuery()->with('nailist.user:id,full_name');

        if ($request->filled('f_nailist')) {
            $query->where('nailist_id', $request->input('f_nailist'));
        }
        if ($request->input('f_filter') === 'featured') {
            $query->where('is_featured', true);
        } elseif ($request->input('f_filter') === 'not_featured') {
            $query->where('is_featured', false);
        }
        if ($request->filled('f_judul')) {
            $query->where('judul', 'like', '%'.$request->input('f_judul').'%');
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        $columns = ['Judul', 'Nailist', 'Deskripsi', 'Featured', 'Dibuat'];

        $rows = $items->map(fn (Portfolio $p) => [
            $p->judul,
            $p->nailist?->user?->full_name ?? '—',
            \Illuminate\Support\Str::limit((string) $p->deskripsi, 200),
            $p->is_featured ? 'Yes' : 'No',
            $p->created_at?->format('d M Y') ?? '—',
        ])->all();

        $export = new \App\Services\DataExportService;

        if ($request->get('format') === 'pdf') {
            return $export->pdf('Portfolio Report', 'exports.pdf-layout', [
                'columns' => $columns,
                'rows'    => $rows,
            ]);
        }

        return $export->csv('portfolio-report', $columns, $rows);
    }

    public function toggleFeature(Portfolio $portfolio): JsonResponse
    {
        $this->authorize('portfolio.update');

        $portfolio->update([
            'is_featured' => ! $portfolio->is_featured,
            'featured_at' => ! $portfolio->is_featured ? now() : null,
        ]);

        $message = $portfolio->is_featured
            ? 'Portfolio ditampilkan di home.'
            : 'Portfolio dilepas dari home.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_featured' => $portfolio->is_featured,
        ]);
    }

    /**
     * Base query: hanya portfolio milik nailist yang masih aktif (user belum di-soft-delete).
     * Portfolio dari nailist yang sudah dihapus tidak relevan untuk moderasi home.
     */
    private function baseQuery()
    {
        return Portfolio::query()->whereHas('nailist', fn ($q) => $q->active());
    }
}
