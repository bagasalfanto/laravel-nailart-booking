<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\StatusBooking;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Single entrypoint /dashboard — render view sesuai role user.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        return match (true) {
            $user->hasRole('superadmin') => $this->superadmin(),
            $user->hasRole('admin') => $this->admin(),
            $user->hasRole('nailist') => $this->nailist(),
            $user->hasRole('customer') => $this->customer(),
            default => redirect()->route('profile.edit'),
        };
    }

    private function superadmin(): View
    {
        return view('dashboard.superadmin.home', [
            'totalUsers' => User::query()->count(),
            'totalRoles' => Role::query()->count(),
            'totalPermissions' => Permission::query()->count(),
            'usersPerRole' => Role::query()->withCount('users')->orderBy('name')->get(),
            'recentUsers' => User::query()->with('roles')->latest()->limit(5)->get(),
        ]);
    }

    /**
     * Admin dashboard: Atelier Overview — high-level metrics & monitoring.
     */
    private function admin(): View
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $totalRevenue = (float) Pembayaran::query()
            ->where('status_pembayaran', 'settlement')
            ->whereBetween('waktu_pembayaran', [$startOfMonth, $now])
            ->sum('nominal');

        $lastMonthRevenue = (float) Pembayaran::query()
            ->where('status_pembayaran', 'settlement')
            ->whereBetween('waktu_pembayaran', [$startLastMonth, $endLastMonth])
            ->sum('nominal');

        $revenueDiffPercent = $lastMonthRevenue > 0
            ? (($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : ($totalRevenue > 0 ? 100 : 0);

        $totalBooking = Reservasi::query()
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $now->toDateString()])
            ->count();

        $totalCustomer = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'customer'))->count();
        $totalNailist = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'nailist'))->count();
        $totalUser = User::query()->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'superadmin']))->count();

        $activeNailistsToday = (int) Reservasi::query()
            ->whereDate('tanggal', $now->toDateString())
            ->distinct('nailist_id')
            ->count('nailist_id');

        // Booking trend: 6 bulan terakhir
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthlyTrend[] = [
                'label' => $month->format('M'),
                'count' => Reservasi::query()
                    ->whereYear('tanggal', $month->year)
                    ->whereMonth('tanggal', $month->month)
                    ->count(),
            ];
        }

        // Booking trend: 7 hari terakhir
        $weeklyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $weeklyTrend[] = [
                'label' => $day->format('D'),
                'count' => Reservasi::query()->whereDate('tanggal', $day->toDateString())->count(),
            ];
        }

        return view('dashboard.admin.home', compact(
            'totalRevenue', 'lastMonthRevenue', 'revenueDiffPercent',
            'totalBooking', 'totalCustomer', 'totalNailist', 'totalUser',
            'activeNailistsToday', 'monthlyTrend', 'weeklyTrend'
        ));
    }

    /**
     * Nailist dashboard: studio activity overview untuk nailist tsb.
     */
    private function nailist(): View
    {
        $user = Auth::user();
        $nailist = $user->nailist;
        $now = now();
        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();

        if (! $nailist) {
            return view('dashboard.nailist.home', $this->emptyNailistData($now));
        }

        $base = Reservasi::query()->where('nailist_id', $nailist->id);

        $clientsToday = (clone $base)->whereDate('tanggal', $today)->count();
        $clientsYesterday = (clone $base)->whereDate('tanggal', $yesterday)->count();
        $clientsDiff = $clientsToday - $clientsYesterday;

        $customDesignsThisWeek = (clone $base)
            ->whereBetween('tanggal', [
                $now->copy()->startOfWeek()->toDateString(),
                $now->copy()->endOfWeek()->toDateString(),
            ])
            ->count();

        $todayEarnings = (float) Pembayaran::query()
            ->where('status_pembayaran', 'settlement')
            ->whereDate('waktu_pembayaran', $today)
            ->whereHas('reservasi', fn ($q) => $q->where('nailist_id', $nailist->id))
            ->sum('nominal');

        $projectedEarnings = (float) (clone $base)
            ->whereDate('tanggal', $today)
            ->sum('total_harga_final');

        $statusMap = StatusBooking::query()->get()->keyBy(fn (StatusBooking $s) => strtolower($s->nama_status));
        $pendingId = $statusMap->get('pending')?->id;
        $successId = $statusMap->get('sukses')?->id;

        $pendingConfirmations = $pendingId
            ? (clone $base)->where('status_id', $pendingId)->count() : 0;

        $upcomingAppointments = (clone $base)
            ->whereNotNull('waktu_mulai')
            ->whereBetween('waktu_mulai', [$now, $now->copy()->addDay()])
            ->count();

        $completedToday = $successId
            ? (clone $base)->where('status_id', $successId)->whereDate('tanggal', $today)->count()
            : 0;

        $upNext = (clone $base)
            ->with(['customer.user', 'treatment', 'pembayaran'])
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '>=', $now)
            ->orderBy('waktu_mulai')
            ->first();

        return view('dashboard.nailist.home', [
            'todayStats' => [
                [
                    'label' => 'Clients Today',
                    'value' => $clientsToday,
                    'meta' => ($clientsDiff >= 0 ? '+' : '').$clientsDiff.' from yesterday',
                    'accent' => $clientsDiff >= 0 ? 'text-emerald-500' : 'text-rose-500',
                    'icon' => 'clients',
                ],
                [
                    'label' => 'Custom Designs',
                    'value' => $customDesignsThisWeek,
                    'meta' => 'This week',
                    'accent' => 'text-slate-500',
                    'icon' => 'designs',
                ],
                [
                    'label' => "Today's Earnings",
                    'value' => $this->rupiah($todayEarnings),
                    'meta' => 'Projected: '.$this->rupiah($projectedEarnings).' by EOD',
                    'accent' => 'text-[#a23f66]',
                    'highlight' => true,
                    'icon' => 'earnings',
                ],
            ],
            'bookingStatus' => [
                ['title' => 'Pending Confirmations', 'subtitle' => 'Clients awaiting reply', 'count' => $pendingConfirmations, 'color' => 'bg-[#ea7c9d]'],
                ['title' => 'Upcoming Appointments', 'subtitle' => 'Next 24 hours',          'count' => $upcomingAppointments, 'color' => 'bg-[#d96b8d]'],
                ['title' => 'Completed Today',       'subtitle' => 'Successfully serviced',  'count' => $completedToday,       'color' => 'bg-emerald-500'],
            ],
            'upNextData' => [
                'client' => $upNext?->customer?->user?->full_name ?? 'No upcoming client',
                'service' => $upNext?->treatment?->nama_jasa ?? 'No service scheduled',
                'time' => $upNext?->waktu_mulai
                    ? $upNext->waktu_mulai->format('h:i A').' - '.optional($upNext->waktu_selesai)->format('h:i A')
                    : '-',
                'price' => $this->rupiah((float) ($upNext?->pembayaran?->nominal ?? $upNext?->total_harga_final ?? 0)),
                'avatar' => $upNext?->customer?->user?->avatar_url,
            ],
            'dateLabel' => strtoupper($now->format('l, M d')),
        ]);
    }

    private function emptyNailistData(\Illuminate\Support\Carbon $now): array
    {
        return [
            'todayStats' => [
                ['label' => 'Clients Today',    'value' => 0, 'meta' => '+0 from yesterday', 'accent' => 'text-slate-500', 'icon' => 'clients'],
                ['label' => 'Custom Designs',   'value' => 0, 'meta' => 'This week',         'accent' => 'text-slate-500', 'icon' => 'designs'],
                ['label' => "Today's Earnings", 'value' => 'Rp 0', 'meta' => 'Projected: Rp 0 by EOD', 'accent' => 'text-[#a23f66]', 'highlight' => true, 'icon' => 'earnings'],
            ],
            'bookingStatus' => [
                ['title' => 'Pending Confirmations', 'subtitle' => 'Clients awaiting reply', 'count' => 0, 'color' => 'bg-[#ea7c9d]'],
                ['title' => 'Upcoming Appointments', 'subtitle' => 'Next 24 hours',          'count' => 0, 'color' => 'bg-[#d96b8d]'],
                ['title' => 'Completed Today',       'subtitle' => 'Successfully serviced',  'count' => 0, 'color' => 'bg-emerald-500'],
            ],
            'upNextData' => [
                'client' => 'No upcoming client', 'service' => 'No service scheduled',
                'time' => '-', 'price' => 'Rp 0', 'avatar' => null,
            ],
            'dateLabel' => strtoupper($now->format('l, M d')),
        ];
    }

    private function customer(): View
    {
        $customer = Auth::user()->customer;

        $bookings = $customer
            ? Reservasi::query()
                ->with([
                    'nailist.user:id,full_name,avatar',
                    'treatment:id,nama_jasa,price_min',
                    'status:id,nama_status',
                    'pembayaran',
                ])
                ->where('customer_id', $customer->id)
                ->latest('tanggal')
                ->get()
            : collect();

        $pendingPayment = $bookings->filter(
            fn ($b) => strtolower($b->status?->nama_status ?? '') === 'pending'
        )->count();

        $completed = $bookings->filter(
            fn ($b) => strtolower($b->status?->nama_status ?? '') === 'sukses'
        )->count();

        // Total dibayar: booking Sukses → harga penuh, pembayaran settlement → nominal DP.
        $totalPaid = $bookings->sum(function ($b) {
            $status = strtolower($b->status?->nama_status ?? '');
            if ($status === 'sukses') {
                return (float) $b->total_harga_final;
            }
            if (strtolower($b->pembayaran?->status_pembayaran ?? '') === 'settlement') {
                return (float) ($b->pembayaran?->nominal ?? 0);
            }

            return 0;
        });

        return view('dashboard.customer.home', [
            'bookings' => $bookings,
            'totalBookings' => $bookings->count(),
            'pendingPayment' => $pendingPayment,
            'completed' => $completed,
            'totalPaid' => (float) $totalPaid,
        ]);
    }

    private function rupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    /**
     * Short rupiah: Rp 1.5M / Rp 450k / Rp 200.
     */
    private function shortRupiah(float $amount): string
    {
        if ($amount >= 1_000_000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1_000_000, 1, '.', ''), '0'), '.').'M';
        }
        if ($amount >= 1_000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1_000, 0, '.', ''), '0'), '.').'k';
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
