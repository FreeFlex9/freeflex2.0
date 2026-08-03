<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ApprovesCompanies;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmpresasController extends Controller
{
    use ApprovesCompanies;

    public function index()
    {
        $companies = Company::where('status', 'pending')
            ->orderBy('trade_name')
            ->get(['id', 'trade_name', 'cnpj', 'email', 'phone', 'cnpj_card_path', 'address_proof_path', 'created_at']);

        return Inertia::render('Admin/Empresas/Index', [
            'empresas' => $companies,
        ]);
    }

    public function aprovar(Request $request, Company $empresa)
    {
        $this->assertCompanyApprovable($empresa);

        $empresa->update(['status' => 'approved', 'approved_at' => now(), 'rejection_reason' => null]);

        return back()->with('success', "Empresa {$empresa->trade_name} aprovada com sucesso!");
    }

    public function rejeitar(Request $request, Company $empresa)
    {
        abort_if($empresa->status !== 'pending', 422, 'Status inválido.');

        $request->validate(['motivo' => 'required|string|max:1000']);

        $empresa->update(['status' => 'rejected', 'rejection_reason' => $request->motivo]);

        return back()->with('success', "Empresa {$empresa->trade_name} rejeitada.");
    }
}
