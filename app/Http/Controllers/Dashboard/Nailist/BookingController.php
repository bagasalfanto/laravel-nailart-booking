<?php

namespace App\Http\Controllers\Dashboard\Nailist;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use App\Models\StatusBooking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Yajra\DataTables\Facades\DataTables;

class BookingController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $nailist = $request->user()->nailist;

        if (! $nailist) {
            return redirect()->route('dashboard.home')
                ->with('toast', ['type' => 'warning', 'title' => 'Profil Belum Lengkap', 'message' => 'Profil nailist belum tersedia.']);
        }

        $base = Reservasi::query()->where('nailist_id', $nailist->id);

        // Resolve status IDs from DB (consistent with DashboardController pattern)
        $statusMap = StatusBooking::query()->get()->keyBy(fn (StatusBooking $s) => strtolower($s->nama_status));
        $pendingId = $statusMap->get('pending')?->id;
        $dikonfirmasiId = $statusMap->get('dikonfirmasi')?->id;
        $diprosesId = $statusMap->get('diproses')?->id;
        $suksesId = $statusMap->get('sukses')?->id;

        // Stats
        $stats = [
            'pending' => $pendingId ? (clone $base)->where('status_id', $pendingId)->count() : 0,
            'confirmed' => (clone $base)->whereIn('status_id', array_filter([$dikonfirmasiId, $diprosesId]))->count(),
            'completed' => $suksesId ? (clone $base)->where('status_id', $suksesId)->count() : 0,
        ];

        return view('dashboard.nailist.bookings.index', compact('stats'));
    }

    public function data(Request $request): JsonResponse
    {
        $nailist = $request->user()->nailist;

        if (! $nailist) {
            return response()->json(['error' => 'Nailist profile not found'], 404);
        }

        $query = Reservasi::query()
            ->where('nailist_id', $nailist->id)
            ->with(['customer.user:id,full_name,avatar', 'treatment:id,nama_jasa', 'status:id,nama_status'])
            ->select('reservasis.*');

        if ($request->filled('f_status')) {
            $statusName = strtolower($request->input('f_status'));
            $query->whereHas('status', fn ($q) => $q->whereRaw('LOWER(nama_status) = ?', [$statusName]));
        }

        return DataTables::of($query)
            ->addColumn('customer_label', function (Reservasi $r) {
                $name = e($r->customer?->user?->full_name ?? '—');
                $avatar = e($r->customer?->user?->avatar_url ?? '');
                return '<div class="flex items-center gap-3">'
                    .($avatar ? '<img src="'.$avatar.'" alt="" class="w-9 h-9 rounded-full object-cover">' : '<div class="w-9 h-9 rounded-full bg-slate-100"></div>')
                    .'<div><p class="font-medium text-slate-800">'.$name.'</p><p class="text-xs text-slate-400">Customer</p></div></div>';
            })
            ->addColumn('treatment_label', fn (Reservasi $r) => e($r->treatment?->nama_jasa ?? '—'))
            ->addColumn('schedule_label', function (Reservasi $r) {
                $date = $r->tanggal?->isoFormat('D MMM') ?? '—';
                $time = $r->waktu_mulai?->format('H:i') ?? ($r->jam ? \Illuminate\Support\Carbon::parse($r->jam)->format('H:i') : '—');
                return '<div><p class="text-slate-700">'.$date.'</p><p class="text-xs text-slate-400">'.$time.'</p></div>';
            })
            ->addColumn('status_badge', function (Reservasi $r) {
                $name = strtolower($r->status?->nama_status ?? '');
                $map = [
                    'pending'      => 'bg-amber-50 text-amber-600',
                    'dikonfirmasi' => 'bg-emerald-50 text-emerald-600',
                    'diproses'     => 'bg-sky-50 text-sky-600',
                    'sukses'       => 'bg-emerald-100 text-emerald-700',
                    'dibatalkan'   => 'bg-rose-50 text-rose-600',
                ];
                $cls = $map[$name] ?? 'bg-slate-100 text-slate-600';
                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium '.$cls.'">'.e(ucfirst($r->status?->nama_status ?? 'Unknown')).'</span>';
            })
            ->addColumn('actions', function (Reservasi $r) {
                $detail = '<a href="'.route('dashboard.nailist-bookings.show', $r).'" class="inline-flex items-center gap-1 rounded-md border border-[#eedde2] px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-[#fdf2f5] hover:text-[#a23f66]">Detail</a>';
                $delete = '<button type="button" data-delete-booking data-action="'.route('dashboard.nailist-bookings.destroy', $r).'" data-name="'.e($r->customer?->user?->full_name ?? 'Booking').'" class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-100 ml-1">Delete</button>';
                return $detail.$delete;
            })
            ->orderColumn('treatment_label', 'treatment_id $1')
            ->orderColumn('schedule_label', 'waktu_mulai $1')
            ->orderColumn('status_badge', 'status_id $1')
            ->rawColumns(['customer_label', 'schedule_label', 'status_badge', 'actions'])
            ->make(true);
    }

    public function show(Reservasi $booking): View
    {
        $this->ensureOwner($booking);

        $booking->load([
            'customer.user:id,full_name,phone_number,avatar',
            'treatment',
            'status',
            'pembayaran',
        ]);

        return view('dashboard.nailist.bookings.show', compact('booking'));
    }

    /**
     * Start Session: Dikonfirmasi → Diproses (DP sudah lunas).
     */
    public function startSession(Reservasi $booking): RedirectResponse
    {
        $this->ensureOwner($booking);

        if (strtolower($booking->status?->nama_status ?? '') !== 'dikonfirmasi') {
            return back()->with('toast', ['type' => 'error', 'title' => 'Tidak Bisa Memulai', 'message' => 'Sesi hanya bisa dimulai setelah booking dikonfirmasi (DP lunas).']);
        }

        $diprosesId = StatusBooking::where('nama_status', 'Diproses')->value('id');
        if (! $diprosesId) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Gagal', 'message' => 'Status "Diproses" tidak ditemukan.']);
        }

        $booking->update(['status_id' => $diprosesId]);

        activity('reservasi')
            ->causedBy(auth()->user())
            ->performedOn($booking)
            ->event('session_started')
            ->log('Nailist memulai sesi booking');

        return back()->with('toast', ['type' => 'success', 'title' => 'Sesi Dimulai', 'message' => 'Sesi treatment sedang berjalan.']);
    }

    /**
     * Selesai Session: Diproses → Sukses (membuka review customer).
     */
    public function finishSession(Reservasi $booking): RedirectResponse
    {
        $this->ensureOwner($booking);

        if (strtolower($booking->status?->nama_status ?? '') !== 'diproses') {
            return back()->with('toast', ['type' => 'error', 'title' => 'Tidak Bisa Menyelesaikan', 'message' => 'Sesi hanya bisa diselesaikan saat status Diproses.']);
        }

        $suksesId = StatusBooking::where('nama_status', 'Sukses')->value('id');
        if (! $suksesId) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Gagal', 'message' => 'Status "Sukses" tidak ditemukan.']);
        }

        $booking->update(['status_id' => $suksesId]);

        activity('reservasi')
            ->causedBy(auth()->user())
            ->performedOn($booking)
            ->event('session_finished')
            ->log('Nailist menyelesaikan sesi booking');

        return back()->with('toast', ['type' => 'success', 'title' => 'Sesi Selesai', 'message' => 'Sesi selesai. Customer kini bisa memberi review.']);
    }

    public function destroy(Reservasi $booking): RedirectResponse
    {
        $this->ensureOwner($booking);

        // Prevent deletion if payment exists (cascade would delete financial record)
        if ($booking->pembayaran) {
            return back()->with('toast', [
                'type' => 'error',
                'title' => 'Tidak Bisa Dihapus',
                'message' => 'Booking ini memiliki data pembayaran. Batalkan booking melalui update status, bukan hapus.',
            ]);
        }

        // Delete orphaned design reference file
        if ($booking->referensi_desain && \Illuminate\Support\Facades\Storage::disk('public')->exists($booking->referensi_desain)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($booking->referensi_desain);
        }

        $booking->delete();

        return redirect()
            ->route('dashboard.nailist-bookings.index')
            ->with('toast', ['type' => 'success', 'title' => 'Penghapusan Berhasil', 'message' => 'Booking berhasil dihapus.']);
    }

    private function ensureOwner(Reservasi $booking): void
    {
        $nailistId = request()->user()->nailist?->id;

        if (! $nailistId || $booking->nailist_id !== $nailistId) {
            throw new NotFoundHttpException;
        }
    }
}
