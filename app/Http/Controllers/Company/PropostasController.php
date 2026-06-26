<?php

namespace App\Http\Controllers\Company;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropostasController extends Controller
{
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

    public function mensagens(Proposal $proposal)
    {
        $company = Auth::guard('company')->user();
        abort_if($proposal->demand->company_id !== $company->id, 403);

        $messages = $this->scopedMessages($proposal)->get();

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
        $company = Auth::guard('company')->user();
        abort_if($proposal->demand->company_id !== $company->id, 403);
        abort_if(
            !in_array($proposal->status, ['pending', 'pending_admin_approval', 'accepted']),
            422,
            'Chat não disponível para esta proposta.'
        );

        $request->validate(['body' => 'required|string|max:2000']);

        $message = Message::create([
            'demand_id'   => $proposal->demand_id,
            'sender_type' => 'company',
            'sender_id'   => $company->id,
            'body'        => $request->body,
        ]);

        broadcast(new MessageSent($message, $proposal->id, $company->trade_name));

        return response()->json([
            'id'          => $message->id,
            'body'        => $message->body,
            'sender_type' => 'company',
            'sender_id'   => $company->id,
            'sender_name' => $company->trade_name,
            'created_at'  => $message->created_at->toISOString(),
        ]);
    }

    private function scopedMessages(Proposal $proposal)
    {
        $proposal->loadMissing('demand');
        return Message::where('demand_id', $proposal->demand_id)
            ->where(function ($q) use ($proposal) {
                $q->where(fn ($q2) => $q2->where('sender_type', 'provider')->where('sender_id', $proposal->provider_id))
                  ->orWhere(fn ($q2) => $q2->where('sender_type', 'company')->where('sender_id', $proposal->demand->company_id));
            })
            ->orderBy('created_at');
    }
}
