<?php

namespace App\Http\Controllers\Provider;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropostasController extends Controller
{
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
            'proposals' => $proposals,
            'filters'   => $request->only(['status']),
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

    // ── Chat ────────────────────────────────────────────────────────────────────

    public function mensagens(Proposal $proposal)
    {
        $provider = Auth::guard('provider')->user();
        abort_if($proposal->provider_id !== $provider->id, 403);

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
        $provider = Auth::guard('provider')->user();
        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if(
            !in_array($proposal->status, ['pending', 'pending_company_accept', 'pending_admin_approval', 'accepted']),
            422,
            'Chat não disponível para esta proposta.'
        );

        $request->validate(['body' => 'required|string|max:2000']);

        $message = Message::create([
            'demand_id'   => $proposal->demand_id,
            'sender_type' => 'provider',
            'sender_id'   => $provider->id,
            'body'        => $request->body,
        ]);

        broadcast(new MessageSent($message, $proposal->id, $provider->name));

        return response()->json([
            'id'          => $message->id,
            'body'        => $message->body,
            'sender_type' => 'provider',
            'sender_id'   => $provider->id,
            'sender_name' => $provider->name,
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
