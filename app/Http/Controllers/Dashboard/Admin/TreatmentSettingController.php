<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTreatmentCategoryRequest;
use App\Http\Requests\Admin\StoreTreatmentKatalogRequest;
use App\Http\Requests\Admin\UpdateTreatmentCategoryRequest;
use App\Http\Requests\Admin\UpdateTreatmentKatalogRequest;
use App\Models\TreatmentCategory;
use App\Models\TreatmentKatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TreatmentSettingController extends Controller
{
    public function index(): View
    {
        $this->authorize('treatment.view');

        $categories = TreatmentCategory::query()
            ->with(['treatments' => fn ($q) => $q->orderBy('sort_order')->orderBy('nama_jasa')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('dashboard.admin.treatment-settings.index', compact('categories'));
    }

    /* =========================
     | CATEGORIES
     |=========================*/

    public function storeCategory(StoreTreatmentCategoryRequest $request): JsonResponse
    {
        $maxOrder = (int) TreatmentCategory::max('sort_order');

        $category = TreatmentCategory::create(array_merge($request->validated(), [
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $maxOrder + 1,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.',
            'data'    => $category,
        ]);
    }

    public function updateCategory(UpdateTreatmentCategoryRequest $request, TreatmentCategory $category): JsonResponse
    {
        $category->update(array_merge($request->validated(), [
            'is_active' => $request->boolean('is_active', $category->is_active),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => $category->fresh(),
        ]);
    }

    public function destroyCategory(TreatmentCategory $category): JsonResponse
    {
        $this->authorize('treatment.delete');

        // Guard: kategori dengan treatment di dalamnya tidak bisa dihapus.
        if ($category->treatments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori masih punya treatment di dalamnya. Pindahkan atau hapus dulu treatment-nya.',
            ], 422);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
    }

    public function reorderCategories(Request $request): JsonResponse
    {
        $this->authorize('treatment.update');

        $ids = (array) $request->input('ids', []);

        foreach ($ids as $idx => $id) {
            TreatmentCategory::where('id', $id)->update(['sort_order' => $idx + 1]);
        }

        return response()->json(['success' => true]);
    }

    /* =========================
     | TREATMENTS
     |=========================*/

    public function storeTreatment(StoreTreatmentKatalogRequest $request): JsonResponse
    {
        $data = $request->validated();
        $maxOrder = (int) TreatmentKatalog::where('category_id', $data['category_id'])->max('sort_order');

        if ($data['price_type'] === 'fixed') {
            $data['price_max'] = null;
        }

        $treatment = TreatmentKatalog::create(array_merge($data, [
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $maxOrder + 1,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Treatment berhasil ditambahkan.',
            'data'    => $treatment->load('category'),
        ]);
    }

    public function updateTreatment(UpdateTreatmentKatalogRequest $request, TreatmentKatalog $treatment): JsonResponse
    {
        $data = $request->validated();

        if ($data['price_type'] === 'fixed') {
            $data['price_max'] = null;
        }

        // Kalau pindah kategori, set sort_order paling akhir di kategori baru.
        if ($data['category_id'] !== $treatment->category_id) {
            $data['sort_order'] = ((int) TreatmentKatalog::where('category_id', $data['category_id'])->max('sort_order')) + 1;
        }

        $treatment->update(array_merge($data, [
            'is_active' => $request->boolean('is_active', $treatment->is_active),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Treatment berhasil diperbarui.',
            'data'    => $treatment->fresh()->load('category'),
        ]);
    }

    public function destroyTreatment(TreatmentKatalog $treatment): JsonResponse
    {
        $this->authorize('treatment.delete');

        // Guard: treatment yang sudah ada reservasi tidak boleh dihapus (jaga history).
        if ($treatment->reservations()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment ini sudah pernah dipakai pada reservasi. Nonaktifkan saja, jangan dihapus.',
            ], 422);
        }

        $treatment->delete();

        return response()->json(['success' => true, 'message' => 'Treatment berhasil dihapus.']);
    }

    public function reorderTreatments(Request $request): JsonResponse
    {
        $this->authorize('treatment.update');

        $ids = (array) $request->input('ids', []);
        $categoryId = $request->input('category_id');

        foreach ($ids as $idx => $id) {
            TreatmentKatalog::where('id', $id)->update([
                'sort_order'  => $idx + 1,
                'category_id' => $categoryId,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
