<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Rating;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $provider = Auth::guard('provider')->user();

        $avgRating = Rating::where('provider_id', $provider->id)->avg('score');

        $stats = [
            'proposals_sent'    => Proposal::where('provider_id', $provider->id)->count(),
            'proposals_pending' => Proposal::where('provider_id', $provider->id)
                                    ->whereIn('status', ['pending', 'pending_admin_approval'])
                                    ->count(),
            'scheduled'         => Schedule::where('provider_id', $provider->id)
                                    ->where('status', 'scheduled')
                                    ->whereDate('date', '>=', now())
                                    ->count(),
            'avg_rating'        => $avgRating ? round($avgRating, 1) : null,
            'ratings_count'     => Rating::where('provider_id', $provider->id)->count(),
        ];

        $upcomingSchedules = Schedule::where('provider_id', $provider->id)
            ->with(['demand:id,title,service_id,company_id', 'demand.service:id,name', 'demand.company:id,trade_name'])
            ->where('status', 'scheduled')
            ->whereDate('date', '>=', now())
            ->orderBy('date')->orderBy('start_time')
            ->limit(5)
            ->get();

        $recentProposals = Proposal::where('provider_id', $provider->id)
            ->with(['demand:id,title,date,start_time,end_time,status,service_id', 'demand.service:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'demand_id', 'status', 'message', 'created_at']);

        // Demandas disponíveis (mesma lógica do BuscarDemandas — sem filtro por serviços do prestador)
        $availableDemands = Demand::with(['service:id,name,requires_license,provider_rate', 'company:id,trade_name'])
            ->whereIn('status', ['open', 'partially_scheduled'])
            ->whereDate('date', '>=', now()->toDateString())
            ->when(!$provider->has_license, fn ($q) =>
                $q->whereHas('service', fn ($s) => $s->where('requires_license', false))
            )
            ->whereNotExists(fn ($sub) =>
                $sub->from('proposals')
                    ->whereColumn('proposals.demand_id', 'demands.id')
                    ->where('proposals.provider_id', $provider->id)
                    ->whereNotIn('proposals.status', ['rejected_provider'])
            )
            ->orderBy('date')
            ->limit(3)
            ->get();

        return Inertia::render('Prestador/Dashboard', [
            'provider'          => $provider->only('id', 'name', 'status', 'profile_photo_path'),
            'stats'             => $stats,
            'upcomingSchedules' => $upcomingSchedules,
            'recentProposals'   => $recentProposals,
            'availableDemands'  => $availableDemands,
        ]);
    }
}
