<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MensagensController extends Controller
{
    public function __construct(private ChatService $chat) {}

    public function index()
    {
        $provider = Auth::guard('provider')->user();

        $conversas = $this->chat->inboxForProvider($provider->id)->map(fn ($proposal) => [
            'proposal_id'       => $proposal->id,
            'demand_id'         => $proposal->demand_id,
            'demand_title'      => $proposal->demand?->title,
            'company_name'      => $proposal->demand?->company?->trade_name,
            'status'            => $proposal->status,
            'last_message_body' => $proposal->last_message_body,
            'last_message_at'   => $proposal->last_message_at,
            'unread_count'      => (int) $proposal->unread_count,
        ]);

        return Inertia::render('Prestador/Mensagens/Index', [
            'conversas' => $conversas,
        ]);
    }
}
