<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataCharm;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CharmManagementController extends Controller
{
    public function index(): View
    {
        $this->authorize('charm.view');

        $charms = DataCharm::query()->orderBy('nama_charm')->paginate(12)->withQueryString();

        $stats = [
            'total'      => DataCharm::query()->count(),
            'low_stock'  => DataCharm::query()->where('stok', '<=', 5)->count(),
            'out_stock'  => DataCharm::query()->where('stok', 0)->count(),
        ];

        return view('dashboard.admin.charm-management.index', compact('charms', 'stats'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('charm.create');

        $data = $request->validate([
            'nama_charm' => ['required', 'string', 'max:80'],
            'stok'       => ['required', 'integer', 'min:0'],
            'harga'      => ['required', 'numeric', 'min:0'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);

        $charm = DataCharm::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Charm berhasil ditambahkan.',
            'data'    => $charm,
        ]);
    }

    public function update(Request $request, DataCharm $charm): JsonResponse
    {
        $this->authorize('charm.update');

        $data = $request->validate([
            'nama_charm' => ['required', 'string', 'max:80'],
            'stok'       => ['required', 'integer', 'min:0'],
            'harga'      => ['required', 'numeric', 'min:0'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);

        $charm->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Charm berhasil diperbarui.',
            'data'    => $charm->fresh(),
        ]);
    }

    public function destroy(DataCharm $charm): JsonResponse
    {
        $this->authorize('charm.delete');

        $charm->delete();

        return response()->json(['success' => true, 'message' => 'Charm berhasil dihapus.']);
    }
}
