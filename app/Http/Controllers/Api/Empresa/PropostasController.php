<?php

namespace App\Http\Controllers\Api\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;

class PropostasController extends Controller
{
    public function aceitar(Request $request, Proposal $proposal)
    {
        $company = $request->user();
        $proposal->loadMissing('demand');

        abort_if($proposal->demand->company_id !== $company->id, 403);
        abort_if($proposal->status !== 'pending', 422, 'Proposta não pode ser aceita neste estado.');

        $demand = $proposal->demand;
        if ($demand->slots_confirmed >= $demand->slots_needed) {
            return response()->json(['message' => 'Todas as vagas já foram preenchidas.'], 422);
        }

        $proposal->update(['status' => 'pending_admin_approval']);

        return response()->json(['message' => 'Proposta aceita! Aguardando aprovação do administrador.']);
    }

    public function rejeitar(Request $request, Proposal $proposal)
    {
        $company = $request->user();
        $proposal->loadMissing('demand');

        abort_if($proposal->demand->company_id !== $company->id, 403);
        abort_if($proposal->status !== 'pending', 422, 'Proposta não pode ser recusada neste estado.');

        $proposal->update(['status' => 'rejected']);

        return response()->json(['message' => 'Proposta recusada.']);
    }
}
