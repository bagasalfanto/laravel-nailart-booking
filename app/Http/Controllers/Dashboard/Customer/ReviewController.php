<?php

namespace App\Http\Controllers\Dashboard\Customer;

use App\Http\Controllers\Concerns\EnsuresOwnedReview;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReviewRequest;
use App\Http\Requests\Customer\UpdateReviewRequest;
use App\Models\Reservasi;
use App\Models\Review;
use App\Models\StatusBooking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use EnsuresOwnedReview;

    public function index(): View
    {
        $this->authorize('review.view');

        $customer = Auth::user()->customer;
        $successId = StatusBooking::query()
            ->whereRaw('LOWER(nama_status) = ?', ['sukses'])
            ->value('id');

        $reservasis = $customer && $successId
            ? Reservasi::query()
                ->with(['treatment', 'nailist.user', 'review'])
                ->where('customer_id', $customer->id)
                ->where('status_id', $successId)
                ->latest('tanggal')
                ->get()
            : collect();

        $reviews = $customer
            ? Review::query()->where('customer_id', $customer->id)->latest()->get()
            : collect();

        return view('dashboard.customer.reviews.index', compact('reservasis', 'reviews'));
    }

    public function create(Reservasi $reservasi): View
    {
        $this->authorize('review.create');
        $this->ensureReservasiCanBeReviewed($reservasi);
        abort_if($reservasi->review()->exists(), 409, 'Reservasi ini sudah memiliki review.');

        return view('dashboard.customer.reviews.create', compact('reservasi'));
    }

    public function store(StoreReviewRequest $request, Reservasi $reservasi): RedirectResponse
    {
        $this->ensureReservasiCanBeReviewed($reservasi);

        if ($reservasi->review()->exists()) {
            return redirect()
                ->route('dashboard.my-reviews.index')
                ->with('toast', ['type' => 'error', 'title' => 'Tidak Bisa Membuat Review', 'message' => 'Reservasi ini sudah memiliki review.']);
        }

        Review::create([
            'reservasi_id' => $reservasi->id,
            'customer_id'  => $reservasi->customer_id,
            ...$request->validated(),
        ]);

        return redirect()
            ->route('dashboard.my-reviews.index')
            ->with('toast', ['type' => 'success', 'title' => 'Review Terkirim', 'message' => 'Terima kasih! Review Anda berhasil dikirim.']);
    }

    public function edit(Review $review): View
    {
        $this->authorize('review.update');
        $this->ensureReviewIsOwned($review);

        $review->load('reservasi.treatment');

        return view('dashboard.customer.reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->ensureReviewIsOwned($review);

        $review->update($request->validated());

        return redirect()
            ->route('dashboard.my-reviews.index')
            ->with('toast', ['type' => 'success', 'title' => 'Perubahan Disimpan', 'message' => 'Review Anda berhasil diperbarui.']);
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('review.delete');
        $this->ensureReviewIsOwned($review);

        $review->delete();

        return redirect()
            ->route('dashboard.my-reviews.index')
            ->with('toast', ['type' => 'success', 'title' => 'Penghapusan Berhasil', 'message' => 'Review berhasil dihapus.']);
    }
}
