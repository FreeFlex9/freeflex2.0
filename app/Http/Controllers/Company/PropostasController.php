<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Models\Proposal;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropostasController extends Controller
{
    public function __construct(private ChatService $chat) {}

    public function aceitar(Proposal $proposal)
    {
        $company = Auth::guard('company')->user();
        abort_if($proposal->demand->company_id !== $company->id, 403);
        abort_if($proposal->status !== 'pending', 422, 'Proposta não pode ser aceita neste estado.');

        // Verifica se ainda há vagas disponíveis
        $demand = $proposal->demand;
        if ($demand->slots_confirmed >= $demand->slots_needed) {
            return back()->withErrors(['msg' => 'Todas as vagas já foram preenchidas.']);
        }

        $proposal->update(['status' => 'pending_admin_approval']);

        return back()->with('success', 'Proposta aceita! Aguardando aprovação do administrador.');
    }

    public function rejeitar(Request $request, Proposal $proposal)
    {
        $company = Auth::guard('company')->user();
        abort_if($proposal->demand->company_id !== $company->id, 403);
        abort_if($proposal->status !== 'pending', 422, 'Proposta não pode ser recusada neste estado.');

        $proposal->update(['status' => 'rejected']);

        return back()->with('success', 'Proposta recusada.');
    }

    // ── Chat ────────────────────────────────────────────────────────────────────
    // A conversa da empresa é por demanda (todas as propostas de uma mesma demanda
    // compartilham a mesma thread empresa↔suporte) — a rota recebe a demanda diretamente.

    public function mensagens(Demand $demand)
    {
        $company = Auth::guard('company')->user();
        abort_if($demand->company_id !== $company->id, 403);

        $messages = $this->chat->threadMessages($demand->id, 'company', $company->id, 'company');

        return response()->json($messages->map(fn ($m) => [
            'id'          => $m->id,
            'body'        => $m->body,
            'sender_type' => $m->sender_type,
            'sender_id'   => $m->sender_id,
            'created_at'  => $m->created_at->toISOString(),
        ]));
    }

    public function enviarMensagem(Request $request, Demand $demand)
    {
        $company = Auth::guard('company')->user();
        abort_if($demand->company_id !== $company->id, 403);
        abort_if(
            !$demand->proposals()->whereIn('status', ChatService::COMPANY_ELIGIBLE_STATUSES)->exists(),
            422,
            'Chat não disponível para esta demanda.'
        );

        $request->validate(['body' => 'required|string|max:2000']);

        $message = $this->chat->send(
            $demand->id, 'company', $company->id,
            'company', $company->id, $company->trade_name, $request->body,
        );

        return response()->json([
            'id'          => $message->id,
            'body'        => $message->body,
            'sender_type' => 'company',
            'sender_id'   => $company->id,
            'sender_name' => $company->trade_name,
            'created_at'  => $message->created_at->toISOString(),
        ]);
    }
}
