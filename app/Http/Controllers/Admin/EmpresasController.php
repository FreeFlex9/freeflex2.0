<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmpresasController extends Controller
{
    public function index()
    {
        $empresas = User::where('role', 'empresa')
            ->where('status_aprovacao', 'pendente')
            ->orderBy('created_at')
            ->get(['id', 'name', 'nome_fantasia', 'cnpj', 'email', 'telefone', 'cartao_cnpj_path', 'created_at']);

        return Inertia::render('Admin/Empresas/Index', [
            'empresas' => $empresas,
        ]);
    }

    public function aprovar(Request $request, User $empresa)
    {
        abort_if($empresa->role !== 'empresa', 404);
        abort_if($empresa->status_aprovacao !== 'pendente', 422, 'Status inválido.');

        $empresa->update(['status_aprovacao' => 'aprovado', 'motivo_rejeicao' => null]);

        return back()->with('success', "Empresa {$empresa->nome_fantasia} aprovada com sucesso!");
    }

    public function rejeitar(Request $request, User $empresa)
    {
        abort_if($empresa->role !== 'empresa', 404);
        abort_if($empresa->status_aprovacao !== 'pendente', 422, 'Status inválido.');

        $request->validate(['motivo' => 'required|string|max:1000']);

        $empresa->update([
            'status_aprovacao' => 'rejeitado',
            'motivo_rejeicao'  => $request->motivo,
        ]);

        return back()->with('success', "Empresa {$empresa->nome_fantasia} rejeitada.");
    }
}
