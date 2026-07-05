<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentMonitoringController extends Controller
{
    public function index(): View
    {
        // Auto-expire: bulk update payment status, then cancel related reservations
        try {
            $expiredIds = Pembayaran::query()
                ->where('status_pembayaran', 'pending')
                ->whereNotNull('batas_waktu_bayar')
                ->where('batas_waktu_bayar', '<', now())
                ->pluck('id');

            if ($expiredIds->isNotEmpty()) {
                Pembayaran::whereIn('id', $expiredIds)->update(['status_pembayaran' => 'expire']);

                $dibatalkanId = \App\Models\StatusBooking::where('nama_status', 'Dibatalkan')->value('id');
                $pendingId = \App\Models\StatusBooking::where('nama_status', 'Pending')->value('id');

                if ($dibatalkanId && $pendingId) {
                    \App\Models\Reservasi::query()
                        ->whereHas('pembayaran', fn ($q) => $q->whereIn('id', $expiredIds))
                        ->where('status_id', $pendingId)
                        ->update(['status_id' => $dibatalkanId]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Auto-expire payments failed: '.$e->getMessage());
        }

        $stats = [
            'total'    => Pembayaran::query()->count(),
            'paid'     => Pembayaran::query()->where('status_pembayaran', 'settlement')->count(),
            'pending'  => Pembayaran::query()->where('status_pembayaran', 'pending')->count(),
            'expired'  => Pembayaran::query()->where('status_pembayaran', 'expire')->count(),
            'revenue'  => (float) Pembayaran::query()->where('status_pembayaran', 'settlement')->sum('nominal'),
        ];

        return view('dashboard.admin.payment-monitoring.index', compact('stats'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = Pembayaran::query()
            ->with(['reservasi.customer.user:id,full_name', 'reservasi.treatment:id,nama_jasa', 'reservasi.nailist.user:id,full_name', 'reservasi.status:id,nama_status'])
            ->select('pembayarans.*');

        if ($request->filled('f_status')) {
            $query->where('status_pembayaran', $request->input('f_status'));
        }

        if ($request->filled('f_search')) {
            $term = $request->input('f_search');
            $query->where(function ($q) use ($term) {
                $q->where('order_id', 'like', "%{$term}%")
                    ->orWhereHas('reservasi.customer.user', fn ($qu) => $qu->where('full_name', 'like', "%{$term}%"));
            });
        }

        return DataTables::of($query)
            ->addColumn('order_label', fn (Pembayaran $p) =>
                '<code class="text-xs text-slate-700">'.e($p->order_id ?: substr($p->id, 0, 8)).'</code>')
            ->addColumn('customer_label', function (Pembayaran $p) {
                $name = e($p->reservasi?->customer?->user?->full_name ?? '—');
                $treatment = e($p->reservasi?->treatment?->nama_jasa ?? '');
                return '<div><p class="font-medium text-slate-800">'.$name.'</p><p class="text-xs text-slate-500">'.$treatment.'</p></div>';
            })
            ->addColumn('nailist_label', fn (Pembayaran $p) =>
                e($p->reservasi?->nailist?->user?->full_name ?? '—'))
            ->addColumn('amount_label', fn (Pembayaran $p) =>
                '<p class="font-semibold text-[#a23f66]">Rp '.number_format((float) $p->nominal, 0, ',', '.').'</p>')
            ->addColumn('status_badge', function (Pembayaran $p) {
                $palette = [
                    'settlement' => ['bg' => 'bg-emerald-50', 'fg' => 'text-emerald-700', 'label' => 'Paid'],
                    'pending'    => ['bg' => 'bg-amber-50',  'fg' => 'text-amber-700',  'label' => 'Pending'],
                    'expire'     => ['bg' => 'bg-rose-50',   'fg' => 'text-rose-700',   'label' => 'Expired'],
                    'cancel'     => ['bg' => 'bg-slate-100', 'fg' => 'text-slate-600',  'label' => 'Cancelled'],
                ];
                $s = $palette[$p->status_pembayaran] ?? ['bg' => 'bg-slate-100', 'fg' => 'text-slate-600', 'label' => $p->status_pembayaran];
                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium '.$s['bg'].' '.$s['fg'].'">'.$s['label'].'</span>';
            })
            ->addColumn('time_label', function (Pembayaran $p) {
                $t = $p->waktu_pembayaran ?? $p->created_at;
                if (! $t) return '—';
                return '<div><p class="text-slate-700 text-sm">'.$t->format('d M Y').'</p><p class="text-xs text-slate-400">'.$t->format('H:i').'</p></div>';
            })
            ->addColumn('remaining_label', function (Pembayaran $p) {
                $total = (float) ($p->reservasi?->total_harga_final ?? 0);
                $dp = (float) ($p->nominal ?? 0);
                $sisa = max(0, $total - $dp);
                if ($p->is_lunas) {
                    return '<span class="text-xs text-emerald-600 font-medium">Lunas</span>';
                }
                return '<span class="text-xs text-slate-600">Rp '.number_format($sisa, 0, ',', '.').'</span>';
            })
            ->addColumn('actions', function (Pembayaran $p) {
                $bookingStatus = strtolower($p->reservasi?->status?->nama_status ?? '');

                // DP settled + booking Sukses → urusan pelunasan
                if ($p->status_pembayaran === 'settlement') {
                    if ($bookingStatus === 'sukses') {
                        if ($p->is_lunas) {
                            $jenis = $p->pelunasan_jenis ? strtoupper($p->pelunasan_jenis) : '';
                            return '<span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium">✓ Lunas'.($jenis ? ' <span class="text-slate-400">('.$jenis.')</span>' : '').'</span>';
                        }
                        $sisa = (float) $p->sisa_pembayaran;
                        return '<button type="button" data-pelunasan-id="'.e($p->id).'" data-order="'.e($p->order_id ?: substr($p->id, 0, 8)).'" data-sisa="'.e($sisa).'" data-customer="'.e($p->reservasi?->customer?->user?->full_name ?? '—').'" data-treatment="'.e($p->reservasi?->treatment?->nama_jasa ?? '—').'" class="rounded-md border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100">Input Pelunasan</button>';
                    }
                    return '<span class="text-xs text-emerald-600">✓ Confirmed</span>';
                }
                if ($p->status_pembayaran === 'pending') {
                    return '<button type="button" data-confirm-id="'.e($p->id).'" data-order="'.e($p->order_id).'" class="rounded-md border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100">Confirm Paid</button>';
                }
                return '<span class="text-xs text-slate-400">—</span>';
            })
            ->rawColumns(['order_label', 'customer_label', 'amount_label', 'remaining_label', 'status_badge', 'time_label', 'actions'])
            ->orderColumn('time_label', 'created_at $1')
            ->orderColumn('amount_label', 'nominal $1')
            ->make(true);
    }

    public function storePelunasan(Request $request, Pembayaran $payment): JsonResponse
    {
        $validated = $request->validate([
            'pelunasan_nominal' => ['required', 'numeric', 'min:1'],
            'pelunasan_jenis'   => ['required', 'string', 'in:cash,debit,qris,transfer'],
        ]);

        if ($payment->is_lunas) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran sudah dilunasi sebelumnya.',
            ], 422);
        }

        if ($payment->status_pembayaran !== 'settlement') {
            return response()->json([
                'success' => false,
                'message' => 'Pelunasan hanya bisa dilakukan setelah DP dikonfirmasi.',
            ], 422);
        }

        $payment->update([
            'pelunasan_nominal' => $validated['pelunasan_nominal'],
            'pelunasan_jenis'   => $validated['pelunasan_jenis'],
            'pelunasan_waktu'   => now(),
        ]);

        activity('payment')
            ->causedBy(auth()->user())
            ->performedOn($payment)
            ->withProperties([
                'order_id'          => $payment->order_id,
                'pelunasan_nominal' => $validated['pelunasan_nominal'],
                'pelunasan_jenis'   => $validated['pelunasan_jenis'],
            ])
            ->event('pelunasan_recorded')
            ->log('Pelunasan dicatat oleh admin/kasir');

        return response()->json([
            'success' => true,
            'message' => 'Pelunasan berhasil dicatat.',
        ]);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        $query = Pembayaran::query()
            ->with(['reservasi.customer.user:id,full_name', 'reservasi.treatment:id,nama_jasa', 'reservasi.nailist.user:id,full_name', 'reservasi.status:id,nama_status']);

        if ($request->filled('f_status')) {
            $query->where('status_pembayaran', $request->input('f_status'));
        }
        if ($request->filled('f_search')) {
            $term = $request->input('f_search');
            $query->where(function ($q) use ($term) {
                $q->where('order_id', 'like', "%{$term}%")
                    ->orWhereHas('reservasi.customer.user', fn ($qu) => $qu->where('full_name', 'like', "%{$term}%"));
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $columns = ['Order ID', 'Customer', 'Treatment', 'Nailist', 'DP Nominal', 'Status DP', 'Sisa', 'Status Pelunasan', 'Tanggal'];

        $rows = $payments->map(function (Pembayaran $p) {
            $total = (float) ($p->reservasi?->total_harga_final ?? 0);
            $dp = (float) ($p->nominal ?? 0);
            $sisa = max(0, $total - $dp);
            return [
                $p->order_id ?: substr($p->id, 0, 8),
                $p->reservasi?->customer?->user?->full_name ?? '—',
                $p->reservasi?->treatment?->nama_jasa ?? '—',
                $p->reservasi?->nailist?->user?->full_name ?? '—',
                'Rp '.number_format($dp, 0, ',', '.'),
                $p->status_pembayaran,
                $p->is_lunas ? 'Lunas' : 'Rp '.number_format($sisa, 0, ',', '.'),
                $p->is_lunas ? ($p->pelunasan_jenis ? strtoupper($p->pelunasan_jenis) : 'Lunas') : 'Belum',
                ($p->waktu_pembayaran ?? $p->created_at)?->format('d M Y H:i') ?? '—',
            ];
        })->all();

        $export = new \App\Services\DataExportService;

        if ($request->get('format') === 'pdf') {
            return $export->pdf('Payment Report', 'exports.pdf-layout', [
                'columns' => $columns,
                'rows'    => $rows,
            ]);
        }

        return $export->csv('payment-report', $columns, $rows);
    }

    public function confirmPayment(Pembayaran $payment): JsonResponse
    {
        if ($payment->status_pembayaran === 'settlement') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran sudah dikonfirmasi sebelumnya.',
            ], 422);
        }

        $payment->update([
            'status_pembayaran' => 'settlement',
            'waktu_pembayaran'  => now(),
        ]);

        // Update reservasi status ke "Dikonfirmasi" kalau masih Pending
        if ($payment->reservasi) {
            $pendingStatus = \App\Models\StatusBooking::where('nama_status', 'Pending')->value('id');
            $confirmedStatus = \App\Models\StatusBooking::where('nama_status', 'Dikonfirmasi')->value('id');

            if ($pendingStatus && $confirmedStatus && $payment->reservasi->status_id === $pendingStatus) {
                $payment->reservasi->update(['status_id' => $confirmedStatus]);
            }
        }

        activity('payment')
            ->causedBy(auth()->user())
            ->performedOn($payment)
            ->withProperties(['order_id' => $payment->order_id, 'nominal' => $payment->nominal])
            ->event('payment_confirmed')
            ->log('Payment manually confirmed by admin');

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dikonfirmasi.',
        ]);
    }
}
