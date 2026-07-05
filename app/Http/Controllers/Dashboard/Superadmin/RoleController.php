<?php

namespace App\Http\Controllers\Dashboard\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\StoreRoleRequest;
use App\Http\Requests\Superadmin\UpdateRoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    private const PROTECTED_ROLES = ['superadmin', 'admin', 'nailist', 'customer'];

    public function index(): View
    {
        return view('dashboard.superadmin.roles.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Role::query()->withCount(['permissions', 'users']);

        if ($request->filled('f_name')) {
            $query->where('name', 'like', '%'.$request->input('f_name').'%');
        }

        return DataTables::of($query)
            ->addColumn('name_label', fn (Role $r) =>
                '<span class="font-medium text-slate-800 capitalize">'.e($r->name).'</span>')
            ->addColumn('actions', function (Role $r) {
                $isProtected = in_array($r->name, self::PROTECTED_ROLES, true);
                $edit = '<a href="'.route('dashboard.roles.edit', $r).'" class="inline-flex items-center gap-1 rounded-md border border-[#eedde2] px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-[#fdf2f5] hover:text-[#a23f66]">Edit</a>';
                $del = $isProtected
                    ? ''
                    : '<button type="button" data-delete-id="'.e($r->id).'" data-name="'.e($r->name).'" class="inline-flex items-center gap-1 rounded-md border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-100 ml-1">Hapus</button>';
                return $edit.$del;
            })
            ->orderColumn('name_label', 'name $1')
            ->rawColumns(['name_label', 'actions'])
            ->make(true);
    }

    public function create(): View
    {
        return view('dashboard.superadmin.roles.create', [
            'permissions'         => $this->groupedPermissions(),
            'assignedPermissions' => [],
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('dashboard.roles.index')
            ->with('toast', ['type' => 'success', 'title' => 'Role Dibuat', 'message' => "Role {$role->name} berhasil dibuat."]);
    }

    public function edit(Role $role): View
    {
        return view('dashboard.superadmin.roles.edit', [
            'role'                => $role,
            'permissions'         => $this->groupedPermissions(),
            'assignedPermissions' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        if ($role->name !== 'superadmin') {
            $role->update(['name' => $data['name']]);
        }

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('dashboard.roles.index')
            ->with('toast', ['type' => 'success', 'title' => 'Perubahan Disimpan', 'message' => "Role {$role->name} berhasil diperbarui."]);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        $query = Role::query()->withCount(['permissions', 'users']);

        if ($request->filled('f_name')) {
            $query->where('name', 'like', '%'.$request->input('f_name').'%');
        }

        $items = $query->orderBy('name')->get();

        $columns = ['Role', 'Permissions', 'Users'];

        $rows = $items->map(fn (Role $r) => [
            $r->name,
            (string) $r->permissions_count,
            (string) $r->users_count,
        ])->all();

        $export = new \App\Services\DataExportService;

        if ($request->get('format') === 'pdf') {
            return $export->pdf('Role Report', 'exports.pdf-layout', [
                'columns' => $columns,
                'rows'    => $rows,
            ]);
        }

        return $export->csv('role-report', $columns, $rows);
    }

    public function destroy(Role $role): RedirectResponse|JsonResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            $message = "Role {$role->name} tidak boleh dihapus.";
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('toast', ['type' => 'error', 'title' => 'Tidak Bisa Dihapus', 'message' => $message]);
        }

        $role->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Role berhasil dihapus.']);
        }

        return redirect()
            ->route('dashboard.roles.index')
            ->with('toast', ['type' => 'success', 'title' => 'Penghapusan Berhasil', 'message' => 'Role berhasil dihapus.']);
    }

    /**
     * @return Collection<string, Collection<int, Permission>>
     */
    private function groupedPermissions(): Collection
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $p) => explode('.', $p->name)[0] ?? 'lainnya');
    }
}
