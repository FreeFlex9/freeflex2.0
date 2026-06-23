<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestador;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestadoresController extends Controller
{
    public function index()
    {
        $prestadores = Prestador::where('status_aprovacao', 'pendente')
            ->with('documentos')
            ->orderBy('data_cadastro')
            ->get([
                'id', 'nome', 'cpf', 'email', 'telefone',
                'possui_cnh', 'cnh_digital', 'numero_cnh', 'cnpj_mei',
                'cnh_frente_path', 'cnh_verso_path',
                'rg_frente_path', 'rg_verso_path', 'ccmei_path',
                'foto_perfil', 'data_cadastro',
            ]);

        return Inertia::render('Admin/Prestadores/Index', [
            'prestadores' => $prestadores,
        ]);
    }

    public function aprovar(Request $request, Prestador $prestador)
    {
        abort_if($prestador->status_aprovacao !== 'pendente', 422, 'Status inválido.');

        if ($prestador->possui_cnh) {
            if ($prestador->cnh_digital) {
                abort_if(empty($prestador->cnh_frente_path), 422, 'CNH digital não enviada.');
            } else {
                abort_if(
                    empty($prestador->cnh_frente_path) || empty($prestador->cnh_verso_path),
                    422, 'CNH (frente e verso) não enviada.'
                );
            }
        } else {
            abort_if(
                empty($prestador->rg_frente_path) || empty($prestador->rg_verso_path),
                422, 'RG (frente e verso) não enviado.'
            );
        }

        if (!empty($prestador->cnpj_mei) && empty($prestador->ccmei_path)) {
            abort(422, 'CCMEI não enviado para MEI.');
        }

        $prestador->update(['status_aprovacao' => 'aprovado', 'motivo_rejeicao' => null]);

        return back()->with('success', "Prestador {$prestador->nome} aprovado com sucesso!");
    }

    public function rejeitar(Request $request, Prestador $prestador)
    {
        abort_if($prestador->status_aprovacao !== 'pendente', 422, 'Status inválido.');

        $request->validate(['motivo' => 'required|string|max:1000']);

        $prestador->update([
            'status_aprovacao' => 'rejeitado',
            'motivo_rejeicao'  => $request->motivo,
        ]);

        return back()->with('success', "Prestador {$prestador->nome} rejeitado.");
    }
}
