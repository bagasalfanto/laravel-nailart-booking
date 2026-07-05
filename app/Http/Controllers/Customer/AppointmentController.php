<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Tab → status mapping.
     * Upcoming  : Pending, Dikonfirmasi, Diproses (belum selesai, belum batal)
     * Completed : Sukses
     * Canceled  : Dibatalkan
     */
    private const TAB_MAP = [
        'upcoming'  => ['Pending', 'Dikonfirmasi', 'Diproses'],
        'completed' => ['Sukses'],
        'canceled'  => ['Dibatalkan'],
    ];

    public function index(Request $request): View
    {
        $tab      = in_array($request->query('tab'), array_keys(self::TAB_MAP), true)
                    ? $request->query('tab')
                    : 'upcoming';

        $customer = $request->user()->customer;

        $appointments = collect();
        if ($customer) {
            $appointments = Reservasi::query()
                ->with(['nailist.user:id,full_name', 'treatment:id,nama_jasa,price_min,kode_jasa', 'status:id,nama_status', 'pembayaran'])
                ->where('customer_id', $customer->id)
                ->whereHas('status', fn ($q) => $q->whereIn('nama_status', self::TAB_MAP[$tab]))
                ->latest('tanggal')
                ->get();
        }

        return view('pages.landing.customer.appointments', [
            'appointments' => $appointments,
            'activeTab'    => $tab,
            'tabs'         => self::TAB_MAP,
        ]);
    }
}
