<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmpresasController extends Controller
{
    public function index()
    {
        $companies = Company::where('status', 'pending')
            ->orderBy('created_at')
            ->get(['id', 'trade_name', 'cnpj', 'email', 'phone', 'cnpj_card_path', 'address_proof_path', 'created_at']);

        return Inertia::render('Admin/Empresas/Index', [
            'empresas' => $companies,
        ]);
    }

    public function aprovar(Request $request, Company $empresa)
    {
        abort_if($empresa->status !== 'pending', 422, 'Status inválido.');
        abort_if(empty($empresa->cnpj_card_path), 422, 'Cartão CNPJ não enviado.');
        abort_if(empty($empresa->address_proof_path), 422, 'Comprovante de residência não enviado.');

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
