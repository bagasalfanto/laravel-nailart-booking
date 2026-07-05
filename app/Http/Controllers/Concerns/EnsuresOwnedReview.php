<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Reservasi;
use App\Models\Review;
use App\Models\StatusBooking;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

/**
 * Otorisasi tambahan untuk fitur review pada sisi customer.
 */
trait EnsuresOwnedReview
{
    /**
     * Pastikan reservasi milik user yang sedang login dan statusnya "Sukses".
     */
    protected function ensureReservasiCanBeReviewed(Reservasi $reservasi): void
    {
        $customerId = Auth::user()->customer?->id;

        if (! $customerId || $reservasi->customer_id !== $customerId) {
            throw new AuthorizationException('Reservasi ini bukan milik Anda.');
        }

        $isCompleted = StatusBooking::query()
            ->whereKey($reservasi->status_id)
            ->whereRaw('LOWER(nama_status) = ?', ['sukses'])
            ->exists();

        if (! $isCompleted) {
            throw new AuthorizationException('Review hanya bisa dibuat setelah reservasi selesai.');
        }
    }

    /**
     * Pastikan review milik user yang sedang login.
     */
    protected function ensureReviewIsOwned(Review $review): void
    {
        $customerId = Auth::user()->customer?->id;

        if (! $customerId || $review->customer_id !== $customerId) {
            throw new AuthorizationException('Review ini bukan milik Anda.');
        }
    }
}
