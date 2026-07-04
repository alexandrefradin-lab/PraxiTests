<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Journal d'audit — consultation des AuditLog (réservé aux admins).
 *
 * Les logs étaient écrits (leads, tests, campagnes…) mais aucune interface
 * ne permettait de les lire. Lecture seule : le journal ne se modifie pas.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q = AuditLog::query()->with('user:id,name,email');

        if ($request->filled('action')) {
            $q->where('action', 'like', $request->string('action')->toString() . '%');
        }

        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(function ($x) use ($s) {
                $x->whereHas('user', fn ($u) => $u->where('email', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%"))
                  ->orWhere('resource_type', 'like', "%{$s}%");
            });
        }

        $logs = $q->latest()->paginate(50)->withQueryString()
            ->through(fn (AuditLog $log) => [
                'id'            => $log->id,
                'action'        => $log->action,
                'user'          => $log->user ? "{$log->user->name} ({$log->user->email})" : 'Système',
                'resource_type' => $log->resource_type,
                'resource_id'   => $log->resource_id,
                'metadata'      => $log->metadata,
                'ip_address'    => $log->ip_address,
                'created_at'    => $log->created_at?->format('d/m/Y H:i:s'),
            ]);

        // Familles d'actions présentes en base (pour le filtre déroulant)
        $actionPrefixes = AuditLog::query()
            ->selectRaw("DISTINCT SUBSTRING_INDEX(action, '.', 1) as prefix")
            ->orderBy('prefix')
            ->pluck('prefix');

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs'           => $logs,
            'actionPrefixes' => $actionPrefixes,
            'filters'        => $request->only(['action', 'search']),
        ]);
    }
}
