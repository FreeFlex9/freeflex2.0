<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SuporteController extends Controller
{
    public function __construct(private ChatService $chat) {}

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

        $partyId  = $this->partyId($proposal, $threadType);
        $messages = $this->chat->threadMessages($proposal->demand_id, $threadType, $partyId, 'admin');

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

        $partyId = $this->partyId($proposal, $threadType);
        $message = $this->chat->send(
            $proposal->demand_id, $threadType, $partyId,
            'admin', $admin->id, 'Suporte FreeFlex', $request->body,
        );

        return response()->json([
            'id'          => $message->id,
            'body'        => $message->body,
            'sender_type' => 'admin',
            'sender_id'   => $admin->id,
            'sender_name' => 'Suporte FreeFlex',
            'created_at'  => $message->created_at->toISOString(),
        ]);
    }

    private function partyId(Proposal $proposal, string $threadType): int
    {
        $proposal->loadMissing('demand');

        return $threadType === 'provider' ? $proposal->provider_id : $proposal->demand->company_id;
    }
}
