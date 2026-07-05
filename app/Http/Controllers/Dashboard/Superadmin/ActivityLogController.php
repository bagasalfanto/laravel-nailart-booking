<?php

namespace App\Http\Controllers\Dashboard\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        // Daftar value distinct untuk filter dropdown.
        $events = Activity::query()
            ->whereNotNull('event')
            ->distinct()->orderBy('event')->pluck('event');

        $subjectTypes = Activity::query()
            ->whereNotNull('subject_type')
            ->distinct()->orderBy('subject_type')->pluck('subject_type')
            ->map(fn ($t) => ['value' => $t, 'label' => class_basename($t)])
            ->values();

        return view('dashboard.superadmin.activity-log.index', compact('events', 'subjectTypes'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = Activity::query()
            ->with(['causer'])
            ->select('activity_log.*');

        // Per-column filter
        if ($request->filled('f_user')) {
            $term = $request->input('f_user');
            $query->whereHas('causer', function ($q) use ($term) {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%");
            });
        }

        if ($request->filled('f_event')) {
            $query->where('event', $request->input('f_event'));
        }

        if ($request->filled('f_subject_type')) {
            $query->where('subject_type', $request->input('f_subject_type'));
        }

        if ($request->filled('f_description')) {
            $query->where('description', 'like', '%'.$request->input('f_description').'%');
        }

        if ($request->filled('f_date_from')) {
            $query->whereDate('created_at', '>=', $request->input('f_date_from'));
        }
        if ($request->filled('f_date_to')) {
            $query->whereDate('created_at', '<=', $request->input('f_date_to'));
        }

        return DataTables::of($query)
            ->addColumn('causer_label', function (Activity $a) {
                if (! $a->causer) {
                    return '<span class="text-slate-400 italic">system</span>';
                }
                $name = e($a->causer->full_name ?: $a->causer->username ?: $a->causer->email);
                $email = e($a->causer->email);
                return "<div><p class=\"font-medium text-slate-800\">{$name}</p><p class=\"text-xs text-slate-500\">{$email}</p></div>";
            })
            ->addColumn('event_badge', function (Activity $a) {
                $event = $a->event ?: 'unknown';
                $palette = [
                    'created'             => 'bg-emerald-50 text-emerald-700',
                    'updated'             => 'bg-amber-50 text-amber-700',
                    'deleted'             => 'bg-rose-50 text-rose-700',
                    'login'               => 'bg-sky-50 text-sky-700',
                    'logout'              => 'bg-slate-100 text-slate-600',
                    'login_failed'        => 'bg-rose-100 text-rose-700',
                    'password_reset'      => 'bg-violet-50 text-violet-700',
                    'password_regenerated'=> 'bg-violet-100 text-violet-800',
                ];
                $cls = $palette[$event] ?? 'bg-slate-100 text-slate-600';
                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium '.$cls.'">'.e($event).'</span>';
            })
            ->addColumn('subject_label', function (Activity $a) {
                if (! $a->subject_type) return '<span class="text-slate-400">—</span>';
                $base = class_basename($a->subject_type);
                $id = $a->subject_id ? substr((string) $a->subject_id, 0, 8) : '?';
                return e($base).' <code class="text-xs text-slate-400">#'.e($id).'</code>';
            })
            ->addColumn('ip_address', function (Activity $a) {
                $ip = $a->properties['ip'] ?? null;
                return $ip ? '<code class="text-xs text-slate-600">'.e($ip).'</code>' : '<span class="text-slate-400">—</span>';
            })
            ->addColumn('time_label', function (Activity $a) {
                if (! $a->created_at) return '—';
                return '<div><p class="text-slate-700">'.$a->created_at->format('d M Y, H:i').'</p><p class="text-xs text-slate-400">'.$a->created_at->diffForHumans().'</p></div>';
            })
            ->addColumn('actions', function (Activity $a) {
                return '<button type="button" data-detail="'.e($a->id).'" class="text-xs font-medium text-[#a23f66] hover:underline">Detail</button>';
            })
            ->rawColumns(['causer_label', 'event_badge', 'subject_label', 'ip_address', 'time_label', 'actions'])
            ->orderColumn('time_label', 'created_at $1')
            ->orderColumn('event_badge', 'event $1')
            ->orderColumn('subject_label', 'subject_type $1')
            ->orderColumn('causer_label', 'causer_id $1')
            ->make(true);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Activity::query()->with('causer');

        if ($request->filled('f_user')) {
            $query->where(function ($q) use ($request) {
                $q->where('causer_id', $request->input('f_user'))
                    ->orWhereHas('causer', fn ($qu) => $qu->where('full_name', 'like', '%'.$request->input('f_user').'%'));
            });
        }
        if ($request->filled('f_event')) {
            $query->where('event', $request->input('f_event'));
        }
        if ($request->filled('f_subject_type')) {
            $query->where('subject_type', $request->input('f_subject_type'));
        }
        if ($request->filled('f_desc')) {
            $query->where('description', 'like', '%'.$request->input('f_desc').'%');
        }
        if ($request->filled('f_from')) {
            $query->where('created_at', '>=', $request->input('f_from'));
        }
        if ($request->filled('f_to')) {
            $query->where('created_at', '<=', $request->input('f_to').' 23:59:59');
        }

        $activities = $query->orderBy('created_at', 'desc')->limit(5000)->get();

        $columns = ['Waktu', 'User', 'Event', 'Subject', 'Deskripsi', 'IP'];

        $rows = $activities->map(fn (Activity $a) => [
            $a->created_at?->format('d M Y H:i') ?? '—',
            $a->causer?->full_name ?? 'System',
            $a->event ?? '—',
            ($a->subject_type ? class_basename($a->subject_type) : '').($a->subject_id ? ' #'.$a->subject_id : ''),
            $a->description ?? '—',
            $a->properties['ip'] ?? $a->properties['ip_address'] ?? '—',
        ])->all();

        return (new \App\Services\DataExportService)->csv('activity-log', $columns, $rows);
    }

    public function show(Activity $activity): JsonResponse
    {
        $activity->load(['causer', 'subject']);

        $properties = $activity->properties;
        if ($properties instanceof \Illuminate\Support\Collection) {
            $properties = $properties->toArray();
        }

        return response()->json([
            'id'           => $activity->id,
            'event'        => $activity->event,
            'description'  => $activity->description,
            'log_name'     => $activity->log_name,
            'created_at'   => $activity->created_at?->format('d M Y, H:i:s'),
            'created_diff' => $activity->created_at?->diffForHumans(),
            'causer'       => $activity->causer ? [
                'name'  => $activity->causer->full_name ?: $activity->causer->username,
                'email' => $activity->causer->email,
                'role'  => method_exists($activity->causer, 'getRoleNames')
                    ? $activity->causer->getRoleNames()->first()
                    : null,
            ] : null,
            'subject' => $activity->subject_type ? [
                'type' => class_basename($activity->subject_type),
                'id'   => $activity->subject_id,
            ] : null,
            'properties' => $properties,
        ]);
    }
}
