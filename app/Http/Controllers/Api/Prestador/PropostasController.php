<?php

namespace App\Http\Controllers\Api\Prestador;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropostasController extends Controller
{
    public function index(Request $request)
    {
        $provider = $request->user();

        $proposals = Proposal::with([
            'demand.company:id,trade_name',
            'demand.service:id,name',
        ])
            ->where('provider_id', $provider->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Proposal $p) => [
                'id' => $p->id,
                'status' => $p->status,
                'mensagem' => $p->message,
                'criado_em' => $p->created_at?->toISOString(),
                'aceito_em' => $p->accepted_at?->toISOString(),
                'prazo_desistencia' => $p->desistenciaPrazo()?->toISOString(),
                'pode_desistir' => $p->podeDesistir(),
                'demanda' => $p->demand ? [
                    'id' => $p->demand->id,
                    'titulo' => $p->demand->title,
                    'data' => $p->demand->date?->toDateString(),
                    'hora_inicio' => $p->demand->start_time,
                    'hora_fim' => $p->demand->end_time,
                    'servico' => $p->demand->service?->name,
                    'empresa' => $p->demand->company?->trade_name,
                ] : null,
            ]);

        return response()->json(['propostas' => $proposals]);
    }

    public function cancelar(Request $request, Proposal $proposal)
    {
        $provider = $request->user();

        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if(
            !in_array($proposal->status, ['pending', 'pending_company_accept']),
            422,
            'Não é possível cancelar esta proposta.'
        );

        $proposal->update(['status' => 'rejected_provider']);

        return response()->json(['message' => 'Proposta cancelada.']);
    }

    public function desistir(Request $request, Proposal $proposal)
    {
        $provider = $request->user();

        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if($proposal->status !== 'accepted', 422, 'Esta proposta não está confirmada.');
        abort_if(!$proposal->podeDesistir(), 422, 'O prazo de ' . config('faltas.janela_desistencia_horas') . ' horas para desistir desta demanda já expirou.');

        DB::transaction(function () use ($proposal) {
            $proposal->update(['status' => 'withdrawn_by_provider', 'withdrawn_at' => now()]);

            Schedule::where('demand_id', $proposal->demand_id)
                ->where('provider_id', $proposal->provider_id)
                ->where('status', 'scheduled')
                ->update([
                    'status'           => 'cancelled',
                    'cancelled_at'     => now(),
                    'cancelled_reason' => 'Desistência do prestador dentro do prazo de ' . config('faltas.janela_desistencia_horas') . 'h.',
                ]);

            $demanda = $proposal->demand;
            $demanda->decrement('slots_confirmed');
            if ($demanda->status === 'scheduled') {
                $demanda->update(['status' => 'open']);
            }
        });

        return response()->json(['message' => 'Você desistiu da demanda dentro do prazo permitido. Nenhuma penalidade foi aplicada.']);
    }

    public function aceitar(Request $request, Proposal $proposal)
    {
        $provider = $request->user();

        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if($proposal->status !== 'direct_pending', 422, 'Este convite não pode ser aceito.');

        $proposal->update(['status' => 'pending_admin_approval']);

        return response()->json(['message' => 'Convite aceito! Aguardando aprovação do administrador.']);
    }

    public function recusar(Request $request, Proposal $proposal)
    {
        $provider = $request->user();

        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if($proposal->status !== 'direct_pending', 422, 'Este convite já foi processado.');

        $proposal->update(['status' => 'rejected_provider']);

        return response()->json(['message' => 'Convite recusado.']);
    }
}
