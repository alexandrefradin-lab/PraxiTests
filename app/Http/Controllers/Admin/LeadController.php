<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $q = Lead::query()->latest();

        if ($request->filled('status')) {
            $validStatuses = ['new', 'contacted', 'qualified', 'converted', 'lost'];
            $statusFilter = $request->string('status')->toString();
            if (in_array($statusFilter, $validStatuses, true)) {
                $q->where('status', $statusFilter);
            }
        }
        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(fn ($x) => $x->where('email', 'like', "%{$s}%")->orWhere('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%"));
        }

        return Inertia::render('Admin/Leads/Index', [
            'leads'   => $q->paginate(50)->withQueryString(),
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(Lead $lead)
    {
        return Inertia::render('Admin/Leads/Show', ['lead' => $lead]);
    }

    public function update(Request $request, Lead $lead)
    {
        $lead->update($request->validate([
            'status' => ['required', 'in:new,contacted,qualified,converted,lost'],
            'score'  => ['nullable', 'integer', 'min:0', 'max:100'],
        ]));
        return back();
    }

    public fu