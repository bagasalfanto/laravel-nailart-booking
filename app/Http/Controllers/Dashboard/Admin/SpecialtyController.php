<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSpecialtyRequest;
use App\Http\Requests\Admin\UpdateSpecialtyRequest;
use App\Models\Specialty;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SpecialtyController extends Controller
{
    public function index(): View
    {
        $this->authorize('specialty.view');

        return view('dashboard.admin.specialties.index');
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('specialty.view');

        $query = Specialty::query()->withCount('nailists');

        if ($request->filled('f_name')) {
            $query->where('name', 'like', '%'.$request->input('f_name').'%');
        }
        if ($request->filled('f_status')) {
            $query->where('is_active', $request->input('f_status') === 'active');
        }

        return DataTables::of($query)
            ->addColumn('name_label', fn (Specialty $s) =>
                '<p class="font-medium text-slate-800">'.e($s->name).'</p>')
            ->addColumn('nailists_label', fn (Specialty $s) => $s->nailists_count.' nailist')
            ->addColumn('status_badge', function (Specialty $s) {
                return $s->is_active
                    ? '<span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-xs text-emerald-700">Aktif</span>'
                    : '<span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-500">Nonaktif</span>';
            })
            ->addColumn('actions', function (Specialty $s) {
                $edit = '<button type="button" data-edit-id="'.e($s->id).'" data-name="'.e($s->name).'" data-active="'.($s->is_active ? '1' : '0').'" class="inline-flex items-center gap-1 rounded-md border border-[#eedde2] px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-[#fdf2f5] hover:text-[#a23f66]">Edit</button>';
                $del = '';
                if (auth()->user()->can('specialty.delete')) {
                    $del = '<button type="button" data-delete-id="'.e($s->id).'" data-name="'.e($s->name).'" class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-100 ml-1">Hapus</button>';
                }
                return $edit.$del;
            })
            ->orderColumn('name_label', 'name $1')
            ->orderColumn('status_badge', 'is_active $1')
            ->rawColumns(['name_label', 'status_badge', 'actions'])
            ->make(true);
    }

    public function store(StoreSpecialtyRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        $specialty = Specialty::create([
            'name'      => $data['name'],
            'slug'      => Specialty::uniqueSlug($data['name']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Specialty berhasil ditambahkan.']);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Specialty Ditambahkan', 'message' => 'Specialty berhasil disimpan.']);
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        $specialty->update([
            'name'      => $data['name'],
            'slug'      => Specialty::uniqueSlug($data['name'], $specialty->id),
            'is_active' => $request->boolean('is_active', $specialty->is_active),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Specialty berhasil diperbarui.']);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Perubahan Disimpan', 'message' => 'Specialty berhasil diperbarui.']);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        $this->authorize('specialty.view');

        $query = Specialty::query()->withCount('nailists');

        if ($request->filled('f_name')) {
            $query->where('name', 'like', '%'.$request->input('f_name').'%');
        }
        if ($request->filled('f_status')) {
            $query->where('is_active', $request->input('f_status') === 'active');
        }

        $items = $query->orderBy('name')->get();

        $columns = ['Nama', 'Slug', 'Nailists', 'Status'];

        $rows = $items->map(fn (Specialty $s) => [
            $s->name,
            $s->slug,
            (string) $s->nailists_count,
            $s->is_active ? 'Aktif' : 'Nonaktif',
        ])->all();

        $export = new \App\Services\DataExportService;

        if ($request->get('format') === 'pdf') {
            return $export->pdf('Specialty Report', 'exports.pdf-layout', [
                'columns' => $columns,
                'rows'    => $rows,
            ]);
        }

        return $export->csv('specialty-report', $columns, $rows);
    }

    public function destroy(Specialty $specialty): RedirectResponse|JsonResponse
    {
        $this->authorize('specialty.delete');

        $specialty->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Specialty berhasil dihapus.']);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Penghapusan Berhasil', 'message' => 'Specialty berhasil dihapus.']);
    }
}
