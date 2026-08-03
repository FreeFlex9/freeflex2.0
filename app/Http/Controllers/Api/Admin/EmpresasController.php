<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Concerns\ApprovesCompanies;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class EmpresasController extends Controller
{
    use ApprovesCompanies;

    public function index()
    {
        $companies = Company::where('status', 'pending')
            ->orderBy('created_at')
            ->get(['id', 'trade_name', 'cnpj', 'email', 'phone', 'cnpj_card_path', 'address_proof_path', 'created_at']);

        return response()->json([
            'empresas' => $companies->map(fn (Company $c) => [
                'id' => $c->id,
                'nome_fantasia' => $c->trade_name,
                'cnpj' => $c->cnpj,
                'email' => $c->email,
                'telefone' => $c->phone,
                'criado_em' => $c->created_at?->toISOString(),
                'documentos' => [
                    'cnpj_card_path' => !empty($c->cnpj_card_path),
                    'address_proof_path' => !empty($c->address_proof_path),
                ],
            ]),
        ]);
    }

    public function aprovar(Request $request, Company $empresa)
    {
        $this->assertCompanyApprovable($empresa);

        $empresa->update(['status' => 'approved', 'approved_at' => now(), 'rejection_reason' => null]);

        return response()->json(['message' => "Empresa {$empresa->trade_name} aprovada com sucesso!"]);
    }

    public function rejeitar(Request $request, Company $empresa)
    {
        abort_if($empresa->status !== 'pending', 422, 'Status inválido.');

        $request->validate(['motivo' => 'required|string|max:1000']);

        $empresa->update(['status' => 'rejected', 'rejection_reason' => $request->motivo]);

        return response()->json(['message' => "Empresa {$empresa->trade_name} rejeitada."]);
    }
}
