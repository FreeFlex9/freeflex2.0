<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $company = Auth::guard('company')->user();

        $stats = [
            'open_demands'      => Demand::where('company_id', $company->id)
                                    ->whereIn('status', ['open', 'partially_scheduled'])
                                    ->count(),
            'pending_proposals' => Proposal::whereHas('demand', fn ($q) => $q->where('company_id', $company->id))
                                    ->where('status', 'pending')
                                    ->count(),
            'scheduled'         => Schedule::whereHas('demand', fn ($q) => $q->where('company_id', $company->id))
                                    ->where('status', 'scheduled')
                                    ->whereDate('date', '>=', now())
                                    ->count(),
            'completed'         => Demand::where('company_id', $company->id)
                                    ->where('status', 'completed')
                                    ->count(),
        ];

        $recentDemands = Demand::where('company_id', $company->id)
            ->with(['service:id,name', 'proposals' => fn ($q) => $q->where('status', 'pending')])
            ->withCount(['proposals', 'schedules'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'title', 'date', 'start_time', 'end_time', 'slots_needed', 'slots_confirmed', 'status', 'service_id', 'created_at']);

        $upcomingSchedules = Schedule::whereHas('demand', fn ($q) => $q->where('company_id', $company->id))
            ->with(['provider:id,name,phone,profile_photo_path', 'demand:id,title,service_id'])
            ->where('status', 'scheduled')
            ->whereDate('date', '>=', now())
            ->orderBy('date')->orderBy('start_time')
            ->limit(5)
            ->get();

        return Inertia::render('Empresa/Dashboard', [
            'company'           => $company->only('id', 'trade_name', 'status', 'cnpj_card_path'),
            'stats'             => $stats,
            'recentDemands'     => $recentDemands,
            'upcomingSchedules' => $upcomingSchedules,
        ]);
    }
}
