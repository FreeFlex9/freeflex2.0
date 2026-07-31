<?php

namespace App\Http\Controllers\Api\Prestador;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Services\ChatService;
use Illuminate\Http\Request;

class MensagensController extends Controller
{
    public function __construct(private ChatService $chat) {}

    public function index(Request $request)
    {
        $provider = $request->user();

        $conversas = $this->chat->inboxForProvider($provider->id)->map(fn (Proposal $p) => [
            'proposal_id'       => $p->id,
            'demand_id'         => $p->demand_id,
            'demand_title'      => $p->demand?->title,
            'company_name'      => $p->demand?->company?->trade_name,
            'status'            => $p->status,
            'last_message_body' => $p->last_message_body,
            'last_message_at'   => $p->last_message_at,
            'unread_count'      => (int) $p->unread_count,
        ]);

        return response()->json(['conversas' => $conversas]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json(['unread_count' => $this->chat->totalUnreadForProvider($request->user()->id)]);
    }

    public function show(Request $request, Proposal $proposal)
    {
        $provider = $request->user();
        abort_if($proposal->provider_id !== $provider->id, 403);

        $messages = $this->chat->threadMessages($proposal->demand_id, 'provider', $provider->id, 'provider');

        return response()->json(['mensagens' => $messages->map(fn ($m) => [
            'id'          => $m->id,
            'body'        => $m->body,
            'sender_type' => $m->sender_type,
            'sender_id'   => $m->sender_id,
            'created_at'  => $m->created_at->toISOString(),
        ])]);
    }

    public function store(Request $request, Proposal $proposal)
    {
        $provider = $request->user();
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

    public function markRead(Request $request, Proposal $proposal)
    {
        $provider = $request->user();
        abort_if($proposal->provider_id !== $provider->id, 403);

        $this->chat->markRead($proposal->demand_id, 'provider', $provider->id, 'provider');

        return response()->json(['message' => 'Mensagens marcadas como lidas.']);
    }
}
