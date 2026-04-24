<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\StatusBooking;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now();
        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();

        $clientsToday = Reservasi::query()
            ->whereDate('tanggal', $today)
            ->count();

        $clientsYesterday = Reservasi::query()
            ->whereDate('tanggal', $yesterday)
            ->count();

        $clientsDiff = $clientsToday - $clientsYesterday;

        $customDesignsThisWeek = Reservasi::query()
            ->whereBetween('tanggal', [
                $now->copy()->startOfWeek()->toDateString(),
                $now->copy()->endOfWeek()->toDateString(),
            ])
            ->count();

        $todayEarningsRaw = (float) Pembayaran::query()
            ->where('status_pembayaran', 'settlement')
            ->whereDate('waktu_pembayaran', $today)
            ->sum('nominal');

        $projectedEarningsRaw = (float) Reservasi::query()
            ->whereDate('tanggal', $today)
            ->sum('total_harga_final');

        $statusMap = StatusBooking::query()
            ->get()
            ->keyBy(fn (StatusBooking $status) => strtolower($status->nama_status));

        $pendingStatusId = $statusMap->get('pending')?->id;
        $successStatusId = $statusMap->get('sukses')?->id;

        $pendingConfirmations = $pendingStatusId
            ? Reservasi::query()->where('status_id', $pendingStatusId)->count()
            : 0;

        $upcomingAppointments = Reservasi::query()
            ->whereNotNull('waktu_mulai')
            ->whereBetween('waktu_mulai', [$now, $now->copy()->addDay()])
            ->count();

        $completedToday = $successStatusId
            ? Reservasi::query()
                ->where('status_id', $successStatusId)
                ->whereDate('tanggal', $today)
                ->count()
            : 0;

        $upNext = Reservasi::query()
            ->with(['customer.user', 'treatment', 'pembayaran'])
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '>=', $now)
            ->orderBy('waktu_mulai')
            ->first();

        $todayStats = [
            [
                'label' => 'Clients Today',
                'value' => $clientsToday,
                'meta' => $clientsDiff >= 0
                    ? sprintf('+%d from yesterday', $clientsDiff)
                    : sprintf('%d from yesterday', $clientsDiff),
                'accent' => $clientsDiff >= 0 ? 'text-emerald-500' : 'text-rose-500',
            ],
            [
                'label' => 'Custom Designs',
                'value' => $customDesignsThisWeek,
                'meta' => 'This week',
                'accent' => 'text-slate-500',
            ],
            [
                'label' => "Today's Earnings",
                'value' => $this->formatRupiah($todayEarningsRaw),
                'meta' => 'Projected: ' . $this->formatRupiah($projectedEarningsRaw) . ' by EOD',
                'accent' => 'text-[#a23f66]',
                'highlight' => true,
            ],
        ];

        $bookingStatus = [
            ['title' => 'Pending Confirmations', 'subtitle' => 'Clients awaiting reply', 'count' => $pendingConfirmations, 'color' => 'bg-pink-400'],
            ['title' => 'Upcoming Appointments', 'subtitle' => 'Next 24 hours', 'count' => $upcomingAppointments, 'color' => 'bg-rose-400'],
            ['title' => 'Completed Today', 'subtitle' => 'Successfully serviced', 'count' => $completedToday, 'color' => 'bg-emerald-500'],
        ];

        $upNextData = [
            'client' => $upNext?->customer?->user?->full_name ?? 'No upcoming client',
            'service' => $upNext?->treatment?->nama_jasa ?? 'No service scheduled',
            'time' => $upNext?->waktu_mulai
                ? $upNext->waktu_mulai->format('h:i A') . ' - ' . optional($upNext->waktu_selesai)->format('h:i A')
                : '-',
            'price' => $this->formatRupiah((float) ($upNext?->pembayaran?->nominal ?? $upNext?->total_harga_final ?? 0)),
            'avatar' => $upNext?->customer?->user?->avatar,
        ];

        return view('dashboard', [
            'todayStats' => $todayStats,
            'bookingStatus' => $bookingStatus,
            'upNextData' => $upNextData,
            'dateLabel' => $now->format('l, M d'),
        ]);
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
