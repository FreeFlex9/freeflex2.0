<?php

namespace App\Http\Controllers\Api\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Demand;
use App\Services\ChatService;
use Illuminate\Http\Request;

class MensagensController extends Controller
{
    public function __construct(private ChatService $chat) {}

    public function index(Request $request)
    {
        $company = $request->user();

        $conversas = $this->chat->inboxForCompany($company->id)->map(fn (Demand $d) => [
            'demand_id'         => $d->id,
            'demand_title'      => $d->title,
            'status'            => $d->status,
            'last_message_body' => $d->last_message_body,
            'last_message_at'   => $d->last_message_at,
            'unread_count'      => (int) $d->unread_count,
        ]);

        return response()->json(['conversas' => $conversas]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json(['unread_count' => $this->chat->totalUnreadForCompany($request->user()->id)]);
    }

    public function show(Request $request, Demand $demand)
    {
        $company = $request->user();
        abort_if($demand->company_id !== $company->id, 403);

        $messages = $this->chat->threadMessages($demand->id, 'company', $company->id, 'company');

        return response()->json(['mensagens' => $messages->map(fn ($m) => [
            'id'          => $m->id,
            'body'        => $m->body,
            'sender_type' => $m->sender_type,
            'sender_id'   => $m->sender_id,
            'created_at'  => $m->created_at->toISOString(),
        ])]);
    }

    public function store(Request $request, Demand $demand)
    {
        $company = $request->user();
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

    public function markRead(Request $request, Demand $demand)
    {
        $company = $request->user();
        abort_if($demand->company_id !== $company->id, 403);

        $this->chat->markRead($demand->id, 'company', $company->id, 'company');

        return response()->json(['message' => 'Mensagens marcadas como lidas.']);
    }
}
