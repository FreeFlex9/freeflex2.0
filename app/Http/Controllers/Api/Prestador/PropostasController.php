<?php

namespace App\Http\Controllers\Api\Prestador;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;

class PropostasController extends Controller
{
    public function aceitar(Request $request, Proposal $proposal)
    {
        $provider = $request->user();

        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if($proposal->status !== 'direct_pending', 422, 'Este convite não pode ser aceito.');

        $proposal->update(['status' => 'pending_admin_approval']);

        return response()->json(['message' => 'Convite aceito! Aguardando aprovação do administrador.']);
    }

    public function recusar(Request $request, Proposal $proposal)
    {
        $provider = $request->user();

        abort_if($proposal->provider_id !== $provider->id, 403);
        abort_if($proposal->status !== 'direct_pending', 422, 'Este convite já foi processado.');

        $proposal->update(['status' => 'rejected_provider']);

        return response()->json(['message' => 'Convite recusado.']);
    }
}
