<?php

namespace App\Http\Controllers\Dashboard\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superadmin\StoreUserRequest;
use App\Http\Requests\Superadmin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'total'    => User::query()->count(),
            'admin'    => User::query()->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'superadmin']))->count(),
            'nailist'  => User::query()->whereHas('roles', fn ($q) => $q->where('name', 'nailist'))->count(),
            'customer' => User::query()->whereHas('roles', fn ($q) => $q->where('name', 'customer'))->count(),
        ];

        $roles        = Role::query()->orderBy('name')->pluck('name');
        $emailDomain  = \App\Support\SystemSetting::emailDomain();

        return view('dashboard.superadmin.users.index', compact('stats', 'roles', 'emailDomain'));
    }

    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $callerIsSuperadmin = $request->user()->hasRole('superadmin');

        $query = User::query()
            ->with('roles')
            ->when($request->filled('f_name'),  fn ($q) => $q->where('full_name',    'like', '%'.$request->input('f_name').'%'))
            ->when($request->filled('f_email'), fn ($q) => $q->where('email',        'like', '%'.$request->input('f_email').'%'))
            ->when($request->filled('f_phone'), fn ($q) => $q->where('phone_number', 'like', '%'.$request->input('f_phone').'%'))
            ->when(in_array($request->input('f_status'), ['active', 'inactive'], true),
                fn ($q) => $q->where('status', $request->input('f_status')))
            ->when($request->filled('f_role'),
                fn ($q) => $q->whereHas('roles', fn ($qr) => $qr->where('name', $request->input('f_role'))));

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addColumn('user_label', function (User $u) {
                $name = e($u->full_name ?: '—');
                $username = e('@'.($u->username ?: ''));
                $avatar = e($u->avatar_url);
                return '<div class="flex items-center gap-3">'
                    . '<img src="'.$avatar.'" alt="" class="w-9 h-9 rounded-full object-cover border border-[#f1e4e8]">'
                    . '<div class="min-w-0"><p class="font-medium text-slate-800 truncate">'.$name.'</p>'
                    . '<p class="text-xs text-slate-500 truncate">'.$username.'</p></div></div>';
            })
            ->addColumn('phone_label', fn (User $u) => e($u->phone_number ?: '—'))
            ->addColumn('status_badge', function (User $u) {
                if ($u->status === 'active') {
                    return '<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">'
                        . '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active</span>';
                }
                return '<span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">'
                    . '<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive</span>';
            })
            ->addColumn('role_badges', function (User $u) {
                if ($u->roles->isEmpty()) return '<span class="text-xs text-slate-400 italic">no role</span>';
                return $u->roles->map(fn ($r) =>
                    '<span class="inline-flex items-center rounded-full bg-[#fdf2f5] px-2.5 py-1 text-xs font-medium text-[#a23f66] capitalize">'
                    . e($r->name).'</span>'
                )->implode(' ');
            })
            ->addColumn('actions', function (User $u) use ($callerIsSuperadmin, $request) {
                $targetRole = $u->roles->pluck('name')->first();
                $allowedRegen = $callerIsSuperadmin ? ['admin', 'nailist'] : ['nailist'];
                $canRegen = $request->user()->id !== $u->id && in_array($targetRole, $allowedRegen, true);

                // Sama logic dengan controller@destroy: superadmin bisa hapus admin+nailist+customer;
                // admin bisa hapus nailist+customer; tidak bisa hapus diri sendiri / superadmin.
                $allowedDelete = $callerIsSuperadmin ? ['admin', 'nailist', 'customer'] : ['nailist', 'customer'];
                $canDelete = $request->user()->id !== $u->id && in_array($targetRole, $allowedDelete, true);

                $detail = '<a href="'.route('dashboard.users.show', $u).'" class="inline-flex items-center gap-1 rounded-md border border-[#eedde2] px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-[#fdf2f5] hover:text-[#a23f66]">Detail</a>';

                $regen = '';
                if ($canRegen) {
                    $name = e($u->full_name ?: $u->email);
                    $email = e($u->email);
                    $regen = '<button type="button" data-regen-id="'.e($u->id).'" data-regen-name="'.$name.'" data-regen-email="'.$email.'"
                              class="inline-flex items-center gap-1 rounded-md border border-amber-300 bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100 ml-1">Reset PW</button>';
                }

                $edit = '';
                if ($callerIsSuperadmin) {
                    $edit = '<a href="'.route('dashboard.users.edit', $u).'" class="inline-flex items-center gap-1 rounded-md bg-[#a23f66] px-2.5 py-1.5 text-xs font-medium text-white hover:opacity-90 ml-1">Edit</a>';
                }

                $delete = '';
                if ($canDelete) {
                    $name = e($u->full_name ?: $u->email);
                    $email = e($u->email);
                    $delete = '<button type="button" data-del-id="'.e($u->id).'" data-del-name="'.$name.'" data-del-email="'.$email.'" data-del-role="'.e($targetRole).'"
                               class="inline-flex items-center gap-1 rounded-md border border-rose-300 bg-rose-50 px-2.5 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-100 ml-1">Hapus</button>';
                }

                return $detail.$regen.$edit.$delete;
            })
            ->orderColumn('user_label', 'full_name $1')
            ->orderColumn('phone_label', 'phone_number $1')
            ->orderColumn('status_badge', 'status $1')
            ->rawColumns(['user_label', 'status_badge', 'role_badges', 'actions'])
            ->make(true);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        $query = User::query()->with('roles');

        if ($request->filled('f_name')) {
            $query->where('full_name', 'like', '%'.$request->input('f_name').'%');
        }
        if ($request->filled('f_email')) {
            $query->where('email', 'like', '%'.$request->input('f_email').'%');
        }
        if ($request->filled('f_status')) {
            $query->where('status', $request->input('f_status'));
        }
        if ($request->filled('f_role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->input('f_role')));
        }

        $users = $query->orderBy('full_name')->get();

        $columns = ['Nama', 'Username', 'Email', 'Phone', 'Status', 'Roles', 'Terdaftar'];

        $rows = $users->map(fn (User $u) => [
            $u->full_name,
            $u->username,
            $u->email,
            $u->phone_number ?? '—',
            $u->status,
            $u->getRoleNames()->implode(', '),
            $u->created_at?->format('d M Y H:i') ?? '—',
        ])->all();

        $export = new \App\Services\DataExportService;

        if ($request->get('format') === 'pdf') {
            return $export->pdf('User Report', 'exports.pdf-layout', [
                'columns' => $columns,
                'rows'    => $rows,
            ]);
        }

        return $export->csv('user-report', $columns, $rows);
    }

    public function store(StoreUserRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $password = $this->generateRandomPassword(12);

        $user = User::create([
            'email'                 => $data['email'],
            'password'              => Hash::make($password),
            'status'                => 'active',
            'email_verified_at'     => now(),
            'must_complete_profile' => true,
            'full_name'             => '',
            'username'              => 'user_'.Str::lower(Str::random(8)),
        ]);

        $user->assignRole($data['role']);

        match ($data['role']) {
            'admin'    => $user->admin()->create(['kode_admin' => 'ADM-'.strtoupper(Str::random(5))]),
            'nailist'  => $user->nailist()->create(['title' => null, 'bio' => null]),
            'customer' => $user->customer()->create(),
            default    => null,
        };

        // Password hanya dikirim via session flash — tidak lewat JSON response
        return back()->with('credentials', [
            'email'    => $user->email,
            'password' => $password,
            'role'     => $data['role'],
        ]);
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'admin', 'nailist.specialties', 'nailist.portfolios', 'customer']);

        return view('dashboard.superadmin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('dashboard.superadmin.users.edit', [
            'user'          => $user,
            'roles'         => Role::query()->orderBy('name')->get(),
            'assignedRoles' => $user->roles->pluck('name')->all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->syncRoles($request->validated()['roles'] ?? []);

        return redirect()
            ->route('dashboard.users.index')
            ->with('toast', ['type' => 'success', 'title' => 'Role Diperbarui', 'message' => "Role untuk {$user->full_name} berhasil diperbarui."]);
    }

    /**
     * Soft-delete user dari halaman User Management.
     *
     * Role rules:
     * - target customer → admin & superadmin boleh (selain self-delete via profile)
     * - target nailist  → admin & superadmin boleh
     * - target admin    → hanya superadmin
     * - target superadmin → tidak boleh dihapus sama sekali
     * - tidak boleh hapus diri sendiri
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $caller = $request->user();
        $isSuperadmin = $caller->hasRole('superadmin');

        if ($caller->id === $user->id) {
            return response()->json(['success' => false, 'message' => 'Tidak boleh menghapus akun sendiri dari halaman ini.'], 403);
        }

        $targetRole = $user->roles->pluck('name')->first();

        if ($targetRole === 'superadmin') {
            return response()->json(['success' => false, 'message' => 'Akun superadmin tidak bisa dihapus.'], 403);
        }

        $allowedTargetRoles = $isSuperadmin
            ? ['admin', 'nailist', 'customer']
            : ['nailist', 'customer'];

        if (! in_array($targetRole, $allowedTargetRoles, true)) {
            return response()->json(['success' => false, 'message' => 'Anda tidak diizinkan menghapus user dengan role ini.'], 403);
        }

        DB::transaction(function () use ($user, $caller, $targetRole) {
            // Hapus avatar dari storage publik kalau bukan URL eksternal.
            if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
                try {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
                } catch (\Throwable $e) {
                    // file tidak ada atau driver berbeda — abaikan.
                }
            }

            // Scramble identitas agar email/phone bisa dipakai ulang untuk register.
            $deletedToken = (string) Str::uuid();
            $originalEmail = $user->email;
            $user->forceFill([
                'email'        => 'deleted-'.$deletedToken.'@deleted.local',
                'username'     => 'deleted-'.$deletedToken,
                'phone_number' => null,
                'google_id'    => null,
                'avatar'       => null,
                'password'     => Hash::make(Str::random(40)),
                'status'       => 'inactive',
            ])->save();

            // Force logout di semua device.
            try {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            } catch (\Throwable $e) {
                // ignore
            }

            // Audit log.
            activity('auth')
                ->causedBy($caller)
                ->performedOn($user)
                ->withProperties([
                    'target_email' => $originalEmail,
                    'target_role'  => $targetRole,
                    'deleted_token'=> $deletedToken,
                ])
                ->event('user_deleted')
                ->log("User {$originalEmail} ({$targetRole}) was deleted by {$caller->email}");

            $user->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dihapus.',
        ]);
    }

    public function regeneratePassword(Request $request, User $user): JsonResponse
    {
        $caller = $request->user();

        // Cegah regenerate akun sendiri.
        if ($caller->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak boleh regenerate password akun sendiri.',
            ], 403);
        }

        // Aturan role: admin → hanya nailist; superadmin → admin & nailist.
        $targetRole = $user->roles->pluck('name')->first();
        $isSuperadmin = $caller->hasRole('superadmin');

        $allowedTargetRoles = $isSuperadmin ? ['admin', 'nailist'] : ['nailist'];

        if (! in_array($targetRole, $allowedTargetRoles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak diizinkan regenerate password user dengan role ini.',
            ], 403);
        }

        $password = $this->generateRandomPassword(12);

        $user->forceFill([
            'password'             => Hash::make($password),
            'must_change_password' => true,
        ])->save();

        // Force logout: hapus semua session user dari tabel sessions (DB driver).
        try {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        } catch (\Throwable $e) {
            // Kalau driver bukan DB / tabel tidak ada, abaikan — efek logout opsional.
        }

        activity('auth')
            ->causedBy($caller)
            ->performedOn($user)
            ->withProperties(['target_email' => $user->email, 'target_role' => $targetRole])
            ->event('password_regenerated')
            ->log("Password for {$user->email} was regenerated by admin");

        return back()->with('credentials', [
            'email'    => $user->email,
            'password' => $password,
            'role'     => $targetRole,
        ]);
    }

    /**
     * Generate password 12 char: huruf besar, huruf kecil, angka.
     * Dijamin minimal 1 dari masing-masing kategori.
     */
    private function generateRandomPassword(int $length = 12): string
    {
        $upper  = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower  = 'abcdefghijkmnpqrstuvwxyz';
        $digits = '23456789';
        $all    = $upper.$lower.$digits;

        $chars = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
        ];

        for ($i = count($chars); $i < $length; $i++) {
            $chars[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($chars);

        return implode('', $chars);
    }
}
