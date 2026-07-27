<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SuporteController extends Controller
{
    public function index(Request $request)
    {
        $query = Proposal::with([
            'provider:id,name',
            'demand:id,title,company_id',
            'demand.company:id,trade_name',
        ]);

        if ($request->filled('prestador')) {
            $query->whereHas('provider', fn ($q) => $q->where('name', 'like', '%' . $request->prestador . '%'));
        }
        if ($request->filled('empresa')) {
            $query->whereHas('demand.company', fn ($q) => $q->where('trade_name', 'like', '%' . $request->empresa . '%'));
        }
        if ($request->filled('demanda')) {
            $query->whereHas('demand', fn ($q) => $q->where('title', 'like', '%' . $request->demanda . '%'));
        }

        $propostas = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Suporte/Index', [
            'propostas' => $propostas,
            'filters'   => $request->only(['prestador', 'empresa', 'demanda']),
        ]);
    }

    public function mensagens(Proposal $proposal, string $threadType)
    {
        abort_unless(in_array($threadType, ['provider', 'company']), 404);

        $messages = $this->scopedMessages($proposal, $threadType)->get();

        return response()->json($messages->map(fn ($m) => [
            'id'          => $m->id,
            'body'        => $m->body,
            'sender_type' => $m->sender_type,
            'sender_id'   => $m->sender_id,
            'created_at'  => $m->created_at->toISOString(),
        ]));
    }

    public function enviarMensagem(Request $request, Proposal $proposal, string $threadType)
    {
        abort_unless(in_array($threadType, ['provider', 'company']), 404);

        $admin = Auth::guard('admin')->user();

        $request->validate(['body' => 'required|string|max:2000']);

        $proposal->loadMissing('demand');
        $partyId = $threadType === 'provider' ? $proposal->provider_id : $proposal->demand->company_id;

        $message = Message::create([
            'demand_id'         => $proposal->demand_id,
            'sender_type'       => 'admin',
            'sender_id'         => $admin->id,
            'thread_party_type' => $threadType,
            'thread_party_id'   => $partyId,
            'body'              => $request->body,
        ]);

        broadcast(new MessageSent($message, $proposal->id, 'Suporte FreeFlex', $threadType));

        return response()->json([
            'id'          => $message->id,
            'body'        => $message->body,
            'sender_type' => 'admin',
            'sender_id'   => $admin->id,
            'sender_name' => 'Suporte FreeFlex',
            'created_at'  => $message->created_at->toISOString(),
        ]);
    }

    private function scopedMessages(Proposal $proposal, string $threadType)
    {
        $proposal->loadMissing('demand');
        $partyId = $threadType === 'provider' ? $proposal->provider_id : $proposal->demand->company_id;

        return Message::where('demand_id', $proposal->demand_id)
            ->where('thread_party_type', $threadType)
            ->where('thread_party_id', $partyId)
            ->orderBy('created_at');
    }
}
