<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\Nailist;
use App\Models\Reservasi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ScheduleController extends Controller
{
    /**
     * Role yang boleh lihat detail privat (customer info, harga, catatan).
     */
    private const PRIVILEGED_ROLES = ['admin', 'superadmin', 'nailist'];

    /**
     * Pastel palette untuk color-per-nailist (di-hash dari nailist_id).
     */
    private const COLOR_PALETTE = [
        ['bg' => '#fde2e8', 'fg' => '#9d3057'],
        ['bg' => '#e9e1fb', 'fg' => '#5b3aa8'],
        ['bg' => '#fff0d4', 'fg' => '#9c6700'],
        ['bg' => '#dff5e8', 'fg' => '#1f7a4d'],
        ['bg' => '#fde4cf', 'fg' => '#9c4a14'],
        ['bg' => '#e0f2fe', 'fg' => '#0c5d8d'],
        ['bg' => '#fce7f3', 'fg' => '#9d174d'],
        ['bg' => '#f4e8d0', 'fg' => '#7a5c1c'],
        ['bg' => '#dcfce7', 'fg' => '#166534'],
        ['bg' => '#ede9fe', 'fg' => '#5b21b6'],
        ['bg' => '#fef3c7', 'fg' => '#92400e'],
        ['bg' => '#dbeafe', 'fg' => '#1e40af'],
    ];

    public function index(): View
    {
        $nailists = Nailist::query()->with('user:id,full_name,username')->active()->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'name' => $n->user?->full_name ?? $n->user?->username ?? '—',
                'color' => $this->colorFor($n->id),
            ])
            ->sortBy('name')
            ->values();

        return view('pages.landing.schedule', compact('nailists'));
    }

    /**
     * Endpoint JSON untuk FullCalendar.
     * Customer info HANYA muncul kalau user yang login adalah admin/superadmin/nailist.
     */
    public function events(Request $request): JsonResponse
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $query = Reservasi::query()
            ->with([
                'nailist.user:id,full_name,username',
                'treatment:id,nama_jasa',
                'status:id,nama_status',
            ])
            ->whereHas('status', fn ($q) => $q->whereNotIn('nama_status', ['Dibatalkan']))
            ->whereNotNull('waktu_mulai')
            ->whereNotNull('waktu_selesai');

        if ($start) {
            $query->where('waktu_mulai', '>=', $start);
        }
        if ($end) {
            $query->where('waktu_mulai', '<=', $end);
        }

        if ($request->filled('nailist_id')) {
            $query->where('nailist_id', $request->input('nailist_id'));
        }

        $isPrivileged = $this->isCurrentUserPrivileged();
        $authNailistId = (auth()->check() && auth()->user()->hasRole('nailist'))
            ? auth()->user()->nailist?->id
            : null;

        if ($isPrivileged) {
            $query->with(['customer.user:id,full_name,username,phone_number']);
        }

        $events = $query->get()->map(function (Reservasi $r) use ($isPrivileged, $authNailistId) {
            $nailistName = $r->nailist?->user?->full_name ?? $r->nailist?->user?->username ?? '—';
            $treatmentName = $r->treatment?->nama_jasa ?? '—';
            $statusName = $r->status?->nama_status ?? '—';
            $color = $this->colorFor((string) $r->nailist_id);

            $base = [
                'id' => $r->id,
                'title' => $nailistName,
                'start' => $r->waktu_mulai?->toIso8601String(),
                'end' => $r->waktu_selesai?->toIso8601String(),
                'backgroundColor' => $color['bg'],
                'borderColor' => $color['bg'],
                'textColor' => $color['fg'],
                'extendedProps' => [
                    'nailist_name' => $nailistName,
                    'treatment_name' => $treatmentName,
                    'status' => $statusName,
                    'time_label' => $r->waktu_mulai?->format('H:i').' – '.$r->waktu_selesai?->format('H:i'),
                    'date_label' => $r->waktu_mulai?->isoFormat('dddd, D MMMM Y'),
                    'has_private' => false,
                ],
            ];

            // Privacy gate: detail customer hanya untuk role privileged.
            // Nailist hanya boleh lihat detail booking sendiri, admin/superadmin lihat semua.
            $canSeePrivate = $isPrivileged && (
                auth()->user()->hasAnyRole(['admin', 'superadmin']) ||
                ($authNailistId !== null && $r->nailist_id === $authNailistId)
            );

            if ($canSeePrivate) {
                $base['extendedProps']['has_private'] = true;
                $base['extendedProps']['customer'] = $r->customer?->user?->full_name ?? '—';
                $base['extendedProps']['phone'] = $r->customer?->user?->phone_number ?? '—';
                $base['extendedProps']['total'] = $r->total_harga_final
                    ? 'Rp '.number_format((float) $r->total_harga_final, 0, ',', '.')
                    : '—';
                $base['extendedProps']['referensi'] = $r->referensi_desain;
            }

            return $base;
        });

        return response()->json($events);
    }

    /**
     * Export schedule CSV. HANYA admin/superadmin/nailist.
     */
    public function export(Request $request): StreamedResponse
    {
        if (! $this->isCurrentUserPrivileged()) {
            throw new AccessDeniedHttpException('Hanya admin, superadmin, atau nailist yang bisa export.');
        }

        $start = $request->query('start');
        $end = $request->query('end');

        $query = Reservasi::query()
            ->with([
                'nailist.user:id,full_name,username',
                'customer.user:id,full_name,phone_number',
                'treatment:id,nama_jasa',
                'status:id,nama_status',
            ])
            ->whereHas('status', fn ($q) => $q->whereNotIn('nama_status', ['Dibatalkan']))
            ->whereNotNull('waktu_mulai');

        if ($start) {
            $query->where('waktu_mulai', '>=', $start);
        }
        if ($end) {
            $query->where('waktu_mulai', '<=', $end);
        }

        if ($request->filled('nailist_id')) {
            $query->where('nailist_id', $request->input('nailist_id'));
        }

        // Nailist hanya boleh export milik sendiri (kalau bukan juga admin).
        if (auth()->user()->hasRole('nailist') && ! auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            $query->where('nailist_id', auth()->user()->nailist?->id);
        }

        $rows = $query->orderBy('waktu_mulai')->get();
        $filename = 'schedule-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM agar Excel render UTF-8 dengan benar.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Jam Mulai', 'Jam Selesai', 'Nailist', 'Customer', 'No. HP', 'Treatment', 'Status', 'Total']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->waktu_mulai?->format('Y-m-d') ?? $r->tanggal,
                    $r->waktu_mulai?->format('H:i'),
                    $r->waktu_selesai?->format('H:i'),
                    $r->nailist?->user?->full_name ?? '—',
                    $r->customer?->user?->full_name ?? '—',
                    $r->customer?->user?->phone_number ?? '—',
                    $r->treatment?->nama_jasa ?? '—',
                    $r->status?->nama_status ?? '—',
                    $r->total_harga_final ? 'Rp '.number_format((float) $r->total_harga_final, 0, ',', '.') : '—',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function isCurrentUserPrivileged(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(self::PRIVILEGED_ROLES);
    }

    private function colorFor(string $id): array
    {
        $idx = hexdec(substr(md5($id), 0, 6)) % count(self::COLOR_PALETTE);

        return self::COLOR_PALETTE[$idx];
    }
}
