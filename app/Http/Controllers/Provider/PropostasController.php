<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\Schedule;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PropostasController extends Controller
{
    public function __construct(private ChatService $chat) {}

    public function index(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $proposals = Proposal::with([
            'demand.company:id,trade_name',
            'demand.service:id,name',
        ])
            ->where('provider_id', $provider->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->get();

        return inertia('Prestador/MinhasPropostas', [
            'proposals'              => $proposals,
            'filters'                => $request->only(['status']),
            'janelaDesistenciaHoras' => (int) config('faltas.janela_desistencia_horas'),
        ]);
    }

    public function aceitar(Proposal $proposal)
    {
        $provider = Auth::guard('provider')->user();

        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if($proposal->status !== 'direct_pending', 422, 'Este convite não pode ser aceito.');

        $proposal->update(['status' => 'pending_admin_approval']);

        return back()->with('success', 'Convite aceito! Aguardando aprovação do administrador.');
    }

    public function recusar(Proposal $proposal)
    {
        $provider = Auth::guard('provider')->user();

        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if($proposal->status !== 'direct_pending', 422, 'Este convite já foi processado.');

        $proposal->update(['status' => 'rejected_provider']);

        return back()->with('success', 'Convite recusado.');
    }

    public function cancelar(Proposal $proposal)
    {
        $provider = Auth::guard('provider')->user();

        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if(
            !in_array($proposal->status, ['pending', 'pending_company_accept']),
            422,
            'Não é possível cancelar esta proposta.'
        );

        $proposal->update(['status' => 'rejected_provider']);
        return back()->with('success', 'Proposta cancelada.');
    }

    public function desistir(Proposal $proposal)
    {
        $provider = Auth::guard('provider')->user();

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

        return back()->with('success', 'Você desistiu da demanda dentro do prazo permitido. Nenhuma penalidade foi aplicada.');
    }

    public function destroy(Proposal $proposal)
    {
        $provider = Auth::guard('provider')->user();
        abort_if($proposal->provider_id !== $provider->id, 403);

        $proposal->loadMissing('demand');
        $vinculoAtivo = $proposal->status === 'accepted'
            || in_array($proposal->demand?->status, ['scheduled', 'in_progress']);

        abort_if($vinculoAtivo, 422, 'Não é possível excluir uma proposta vinculada a uma demanda ativa.');

        $proposal->delete();
        return back()->with('success', 'Proposta excluída.');
    }

    // ── Chat ────────────────────────────────────────────────────────────────────

    public function mensagens(Proposal $proposal)
    {
        $provider = Auth::guard('provider')->user();
        abort_if($proposal->provider_id !== $provider->id, 403);

        $messages = $this->chat->threadMessages($proposal->demand_id, 'provider', $provider->id, 'provider');

        return response()->json($messages->map(fn ($m) => [
            'id'          => $m->id,
            'body'        => $m->body,
            'sender_type' => $m->sender_type,
            'sender_id'   => $m->sender_id,
            'created_at'  => $m->created_at->toISOString(),
        ]));
    }

    public function enviarMensagem(Request $request, Proposal $proposal)
    {
        $provider = Auth::guard('provider')->user();
        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if(
            !in_array($proposal->status, ChatService::PROVIDER_ELIGIBLE_STATUSES),
            422,
            'Chat não disponível para esta proposta.'
        );

        $request->validate(['body' => 'required|string|max:2000']);

        $message = $this->chat->send(
            $proposal->demand_id, 'provider', $provider->id,
            'provider', $provider->id, $provider->name, $request->body,
        );

        return response()->json([
            'id'          => $message->id,
            'body'        => $message->body,
            'sender_type' => 'provider',
            'sender_id'   => $provider->id,
            'sender_name' => $provider->name,
            'created_at'  => $message->created_at->toISOString(),
        ]);
    }
}
