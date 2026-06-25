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

        // Demandas disponíveis para o prestador (matching seus serviços)
        $serviceIds = $provider->services()->pluck('services.id');
        $availableDemands = Demand::whereIn('service_id', $serviceIds)
            ->whereIn('status', ['open', 'partially_scheduled'])
            ->whereDoesntHave('proposals', fn ($q) => $q->where('provider_id', $provider->id))
            ->with(['service:id,name', 'company:id,trade_name', 'company:id,trade_name,city,state'])
            ->orderBy('date')
            ->limit(3)
            ->get(['id', 'title', 'date', 'start_time', 'end_time', 'slots_needed', 'slots_confirmed', 'service_id', 'company_id', 'city', 'state', 'total_value']);

        return Inertia::render('Prestador/Dashboard', [
            'provider'          => $provider->only('id', 'name', 'status', 'profile_photo_path'),
            'stats'             => $stats,
            'upcomingSchedules' => $upcomingSchedules,
            'recentProposals'   => $recentProposals,
            'availableDemands'  => $availableDemands,
        ]);
    }
}
