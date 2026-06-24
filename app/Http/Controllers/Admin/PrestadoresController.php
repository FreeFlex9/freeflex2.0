<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestadoresController extends Controller
{
    public function index()
    {
        $providers = Provider::where('status', 'pending')
            ->with('documents')
            ->orderBy('created_at')
            ->get([
                'id', 'name', 'cpf', 'email', 'phone',
                'has_license', 'is_digital_license', 'license_number', 'mei_cnpj',
                'license_front_path', 'license_back_path',
                'rg_front_path', 'rg_back_path', 'ccmei_path',
                'profile_photo_path', 'created_at',
            ]);

        return Inertia::render('Admin/Prestadores/Index', [
            'prestadores' => $providers,
        ]);
    }

    public function aprovar(Request $request, Provider $prestador)
    {
        abort_if($prestador->status !== 'pending', 422, 'Status inválido.');

        if ($prestador->has_license) {
            if ($prestador->is_digital_license) {
                abort_if(empty($prestador->license_front_path), 422, 'CNH digital não enviada.');
            } else {
                abort_if(
                    empty($prestador->license_front_path) || empty($prestador->license_back_path),
                    422, 'CNH (frente e verso) não enviada.'
                );
            }
        } else {
            abort_if(
                empty($prestador->rg_front_path) || empty($prestador->rg_back_path),
                422, 'RG (frente e verso) não enviado.'
            );
        }

        if (!empty($prestador->mei_cnpj) && empty($prestador->ccmei_path)) {
            abort(422, 'CCMEI não enviado para MEI.');
        }

        $prestador->update(['status' => 'approved', 'rejection_reason' => null]);

        return back()->with('success', "Prestador {$prestador->name} aprovado com sucesso!");
    }

    public function rejeitar(Request $request, Provider $prestador)
    {
        abort_if($prestador->status !== 'pending', 422, 'Status inválido.');

        $request->validate(['motivo' => 'required|string|max:1000']);

        $prestador->update(['status' => 'rejected', 'rejection_reason' => $request->motivo]);

        return back()->with('success', "Prestador {$prestador->name} rejeitado.");
    }
}
