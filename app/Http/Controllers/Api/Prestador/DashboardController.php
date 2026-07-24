<?php

namespace App\Http\Controllers\Api\Prestador;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Rating;
use App\Models\Schedule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $provider = $request->user();

        $avgRating = Rating::where('provider_id', $provider->id)->avg('score');

        $stats = [
            'proposals_sent' => Proposal::where('provider_id', $provider->id)->count(),
            'proposals_pending' => Proposal::where('provider_id', $provider->id)
                ->whereIn('status', ['pending', 'pending_admin_approval'])
                ->count(),
            'scheduled' => Schedule::where('provider_id', $provider->id)
                ->where('status', 'scheduled')
                ->whereDate('date', '>=', now())
                ->count(),
            'avg_rating' => $avgRating ? round($avgRating, 1) : null,
            'ratings_count' => Rating::where('provider_id', $provider->id)->count(),
        ];

        $upcomingSchedules = Schedule::where('provider_id', $provider->id)
            ->with(['demand:id,title,service_id,company_id', 'demand.service:id,name', 'demand.company:id,trade_name'])
            ->where('status', 'scheduled')
            ->whereDate('date', '>=', now())
            ->orderBy('date')->orderBy('start_time')
            ->limit(5)
            ->get()
            ->map(fn (Schedule $s) => [
                'id' => $s->id,
                'data' => $s->date?->toDateString(),
                'hora_inicio' => $s->start_time,
                'hora_fim' => $s->end_time,
                'demanda_titulo' => $s->demand?->title,
                'servico' => $s->demand?->service?->name,
                'empresa' => $s->demand?->company?->trade_name,
            ]);

        $recentProposals = Proposal::where('provider_id', $provider->id)
            ->with(['demand:id,title,date,start_time,end_time,status,service_id', 'demand.service:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'demand_id', 'status', 'message', 'created_at'])
            ->map(fn (Proposal $p) => [
                'id' => $p->id,
                'status' => $p->status,
                'mensagem' => $p->message,
                'criado_em' => $p->created_at?->toISOString(),
                'demanda' => $p->demand ? [
                    'id' => $p->demand->id,
                    'titulo' => $p->demand->title,
                    'data' => $p->demand->date?->toDateString(),
                    'hora_inicio' => $p->demand->start_time,
                    'hora_fim' => $p->demand->end_time,
                    'servico' => $p->demand->service?->name,
                ] : null,
            ]);

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
            ->get()
            ->map(fn (Demand $d) => [
                'id' => $d->id,
                'titulo' => $d->title,
                'data' => $d->date?->toDateString(),
                'hora_inicio' => $d->start_time,
                'hora_fim' => $d->end_time,
                'servico' => $d->service?->name,
                'valor_hora' => $d->service?->provider_rate,
                'empresa' => $d->company?->trade_name,
            ]);

        return response()->json([
            'provider' => [
                'id' => $provider->id,
                'nome' => $provider->name,
                'status_aprovacao' => $provider->status,
            ],
            'stats' => $stats,
            'proximos_agendamentos' => $upcomingSchedules,
            'propostas_recentes' => $recentProposals,
            'demandas_disponiveis' => $availableDemands,
        ]);
    }
}
