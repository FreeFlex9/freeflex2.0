<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestadoresController extends Controller
{
    public function index()
    {
        $prestadores = User::where('role', 'prestador')
            ->where('status_aprovacao', 'pendente')
            ->with('documentos')
            ->orderBy('created_at')
            ->get([
                'id', 'name', 'cpf', 'email', 'telefone',
                'possui_cnh', 'cnh_digital', 'numero_cnh', 'cnpj_mei',
                'cnh_frente_path', 'cnh_verso_path',
                'rg_frente_path', 'rg_verso_path',
                'ccmei_path', 'profile_photo_path', 'created_at',
            ]);

        return Inertia::render('Admin/Prestadores/Index', [
            'prestadores' => $prestadores,
        ]);
    }

    public function aprovar(Request $request, User $prestador)
    {
        abort_if($prestador->role !== 'prestador', 404);
        abort_if($prestador->status_aprovacao !== 'pendente', 422, 'Status inválido.');

        // Validação de documentos obrigatórios
        if ($prestador->possui_cnh) {
            if ($prestador->cnh_digital) {
                abort_if(empty($prestador->cnh_frente_path), 422, 'CNH digital não enviada.');
            } else {
                abort_if(
                    empty($prestador->cnh_frente_path) || empty($prestador->cnh_verso_path),
                    422,
                    'CNH (frente e verso) não enviada.'
                );
            }
        } else {
            abort_if(
                empty($prestador->rg_frente_path) || empty($prestador->rg_verso_path),
                422,
                'RG (frente e verso) não enviado.'
            );
        }

        if (!empty($prestador->cnpj_mei)) {
            abort_if(empty($prestador->ccmei_path), 422, 'CCMEI não enviado.');
        }

        $prestador->update(['status_aprovacao' => 'aprovado', 'motivo_rejeicao' => null]);

        return back()->with('success', "Prestador {$prestador->name} aprovado com sucesso!");
    }

    public function rejeitar(Request $request, User $prestador)
    {
        abort_if($prestador->role !== 'prestador', 404);
        abort_if($prestador->status_aprovacao !== 'pendente', 422, 'Status inválido.');

        $request->validate(['motivo' => 'required|string|max:1000']);

        $prestador->update([
            'status_aprovacao' => 'rejeitado',
            'motivo_rejeicao'  => $request->motivo,
        ]);

        return back()->with('success', "Prestador {$prestador->name} rejeitado.");
    }
}
