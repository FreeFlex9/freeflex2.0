<?php

namespace App\Http\Controllers\Api\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Proposal;
use App\Models\Schedule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user();

        $stats = [
            'open_demands' => Demand::where('company_id', $company->id)
                ->whereIn('status', ['open', 'partially_scheduled'])
                ->count(),
            'pending_proposals' => Proposal::whereHas('demand', fn ($q) => $q->where('company_id', $company->id))
                ->where('status', 'pending')
                ->count(),
            'scheduled' => Schedule::whereHas('demand', fn ($q) => $q->where('company_id', $company->id))
                ->where('status', 'scheduled')
                ->whereDate('date', '>=', now())
                ->count(),
            'completed' => Demand::where('company_id', $company->id)
                ->where('status', 'completed')
                ->count(),
        ];

        $recentDemands = Demand::where('company_id', $company->id)
            ->with(['service:id,name'])
            ->withCount(['proposals', 'schedules'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'title', 'date', 'start_time', 'end_time', 'slots_needed', 'slots_confirmed', 'status', 'service_id', 'created_at'])
            ->map(fn (Demand $d) => [
                'id' => $d->id,
                'titulo' => $d->title,
                'data' => $d->date?->toDateString(),
                'hora_inicio' => $d->start_time,
                'hora_fim' => $d->end_time,
                'vagas_necessarias' => $d->slots_needed,
                'vagas_confirmadas' => $d->slots_confirmed,
                'status' => $d->status,
                'servico' => $d->service?->name,
                'propostas_count' => $d->proposals_count,
                'agendamentos_count' => $d->schedules_count,
            ]);

        $upcomingSchedules = Schedule::whereHas('demand', fn ($q) => $q->where('company_id', $company->id))
            ->with(['provider:id,name,phone', 'demand:id,title,service_id'])
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
                'prestador' => $s->provider?->name,
                'prestador_telefone' => $s->provider?->phone,
            ]);

        $pendingProposals = Proposal::whereHas('demand', fn ($q) => $q->where('company_id', $company->id))
            ->where('status', 'pending')
            ->with(['provider:id,name', 'demand:id,title'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Proposal $p) => [
                'id' => $p->id,
                'mensagem' => $p->message,
                'criado_em' => $p->created_at?->toISOString(),
                'prestador' => $p->provider?->name,
                'demanda_id' => $p->demand_id,
                'demanda_titulo' => $p->demand?->title,
            ]);

        return response()->json([
            'company' => [
                'id' => $company->id,
                'nome' => $company->trade_name,
                'status_aprovacao' => $company->status,
            ],
            'stats' => $stats,
            'demandas_recentes' => $recentDemands,
            'proximos_agendamentos' => $upcomingSchedules,
            'propostas_pendentes' => $pendingProposals,
        ]);
    }
}
