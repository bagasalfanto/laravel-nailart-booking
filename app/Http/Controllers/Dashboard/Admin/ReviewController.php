<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('review.view');

        return view('dashboard.admin.reviews.index');
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('review.view');

        $query = Review::query()
            ->with(['customer.user', 'reservasi.treatment'])
            ->select('reviews.*');

        if ($request->filled('f_filter')) {
            $f = $request->input('f_filter');
            if ($f === 'featured')    $query->where('is_featured', true);
            elseif ($f === 'pending') $query->where('is_featured', false);
        }

        if ($request->filled('f_user')) {
            $term = $request->input('f_user');
            $query->whereHas('customer.user', function ($q) use ($term) {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%");
            });
        }

        if ($request->filled('f_rating')) {
            $query->where('rating', (int) $request->input('f_rating'));
        }

        if ($request->filled('f_content')) {
            $query->where('content', 'like', '%'.$request->input('f_content').'%');
        }

        if ($request->filled('f_treatment')) {
            $term = $request->input('f_treatment');
            $query->whereHas('reservasi.treatment', fn ($q) => $q->where('nama_jasa', 'like', "%{$term}%"));
        }

        return DataTables::of($query)
            ->addColumn('user_label', function (Review $r) {
                $u = $r->customer?->user;
                $name = e($u->full_name ?? '-');
                $username = e('@'.($u->username ?? '-'));
                $avatar = e($u?->avatar_url ?? 'https://i.pravatar.cc/80?u='.urlencode((string) $r->id));
                return '<div class="flex items-center gap-3">'
                    . '<img src="'.$avatar.'" alt="" class="h-9 w-9 rounded-full object-cover border border-[#f1e4e8]">'
                    . '<div class="min-w-0"><p class="font-medium text-slate-800 truncate">'.$name.'</p>'
                    . '<p class="text-xs text-slate-500 truncate">'.$username.'</p></div></div>';
            })
            ->addColumn('rating_stars', function (Review $r) {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    $stars .= $i <= (int) $r->rating ? '★' : '☆';
                }
                return '<span class="text-amber-500 whitespace-nowrap">'.$stars.'</span>';
            })
            ->addColumn('content_short', fn (Review $r) => e(\Illuminate\Support\Str::limit((string) $r->content, 140)))
            ->addColumn('treatment_name', fn (Review $r) => e($r->reservasi?->treatment?->nama_jasa ?? '-'))
            ->addColumn('actions', function (Review $r) {
                $featBtn = '';
                if (auth()->user()->can('review.feature')) {
                    $label = $r->is_featured ? '★ Ditampilkan' : 'Tampilkan';
                    $cls   = $r->is_featured ? 'bg-emerald-100 text-emerald-700' : 'bg-[#a23f66] text-white';
                    $featBtn = '<button type="button" data-feature-id="'.e($r->id).'" class="rounded-lg px-3 py-1.5 text-xs font-medium '.$cls.' hover:opacity-90">'.$label.'</button>';
                }

                $delBtn = '';
                if (auth()->user()->can('review.delete')) {
                    $delBtn = '<button type="button" data-delete-id="'.e($r->id).'" class="text-xs font-medium text-rose-600 hover:underline ml-2">Hapus</button>';
                }

                return $featBtn.$delBtn;
            })
            ->orderColumn('rating_stars', 'rating $1')
            ->rawColumns(['user_label', 'rating_stars', 'actions'])
            ->make(true);
    }

    public function toggleFeature(Review $review): RedirectResponse|JsonResponse
    {
        $this->authorize('review.feature');

        $review->update([
            'is_featured' => ! $review->is_featured,
            'featured_at' => ! $review->is_featured ? now() : null,
        ]);

        $message = $review->is_featured
            ? 'Review ditampilkan di landing page.'
            : 'Review dilepas dari landing page.';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => $message, 'is_featured' => $review->is_featured]);
        }

        $title = $review->is_featured ? 'Review Ditampilkan' : 'Review Disembunyikan';
        return back()->with('toast', ['type' => 'success', 'title' => $title, 'message' => $message]);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        $this->authorize('review.view');

        $query = Review::query()
            ->with(['customer.user', 'reservasi.treatment']);

        if ($request->filled('f_filter')) {
            $filter = $request->input('f_filter');
            if ($filter === 'featured') {
                $query->where('is_featured', true);
            } elseif ($filter === 'not_featured') {
                $query->where('is_featured', false);
            }
        }

        $reviews = $query->orderBy('created_at', 'desc')->get();

        $columns = ['Customer', 'Treatment', 'Rating', 'Review', 'Featured', 'Tanggal'];

        $rows = $reviews->map(fn (Review $r) => [
            $r->customer?->user?->full_name ?? '—',
            $r->reservasi?->treatment?->nama_jasa ?? '—',
            $r->rating.' / 5',
            \Illuminate\Support\Str::limit($r->content, 300),
            $r->is_featured ? 'Yes' : 'No',
            $r->created_at?->format('d M Y H:i') ?? '—',
        ])->all();

        $export = new \App\Services\DataExportService;

        if ($request->get('format') === 'pdf') {
            return $export->pdf('Review Report', 'exports.pdf-layout', [
                'columns' => $columns,
                'rows'    => $rows,
            ]);
        }

        return $export->csv('review-report', $columns, $rows);
    }

    public function destroy(Review $review): RedirectResponse|JsonResponse
    {
        $this->authorize('review.delete');

        $review->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Review berhasil dihapus.']);
        }

        return redirect()
            ->route('dashboard.reviews.index')
            ->with('toast', ['type' => 'success', 'title' => 'Penghapusan Berhasil', 'message' => 'Review berhasil dihapus.']);
    }
}
