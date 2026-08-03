<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MensagensController extends Controller
{
    public function __construct(private ChatService $chat) {}

    public function index()
    {
        $company = Auth::guard('company')->user();

        $conversas = $this->chat->inboxForCompany($company->id)->map(fn ($demand) => [
            'demand_id'         => $demand->id,
            'demand_title'      => $demand->title,
            'status'            => $demand->status,
            'last_message_body' => $demand->last_message_body,
            'last_message_at'   => $demand->last_message_at,
            'unread_count'      => (int) $demand->unread_count,
        ]);

        return Inertia::render('Empresa/Mensagens/Index', [
            'conversas' => $conversas,
        ]);
    }
}
