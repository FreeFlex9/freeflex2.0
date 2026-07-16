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
                'rg_front_path', 'rg_back_path', 'ccmei_path', 'address_proof_path',
                'profile_photo_path', 'document_validation', 'created_at',
            ]);

        $cnhPendentes = Provider::where('status', 'approved')
            ->where('cnh_status', 'pending')
            ->orderBy('updated_at')
            ->get([
                'id', 'name', 'email', 'phone',
                'is_digital_license', 'license_front_path', 'license_back_path', 'document_validation',
            ]);

        return Inertia::render('Admin/Prestadores/Index', [
            'prestadores'  => $providers,
            'cnhPendentes' => $cnhPendentes,
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

        abort_if(empty($prestador->address_proof_path), 422, 'Comprovante de residência não enviado.');

        $prestador->update(['status' => 'approved', 'approved_at' => now(), 'rejection_reason' => null]);

        return back()->with('success', "Prestador {$prestador->name} aprovado com sucesso!");
    }

    public function rejeitar(Request $request, Provider $prestador)
    {
        abort_if($prestador->status !== 'pending', 422, 'Status inválido.');

        $request->validate(['motivo' => 'required|string|max:1000']);

        $prestador->update(['status' => 'rejected', 'rejection_reason' => $request->motivo]);

        return back()->with('success', "Prestador {$prestador->name} rejeitado.");
    }

    public function aprovarCnh(Provider $prestador)
    {
        abort_if($prestador->cnh_status !== 'pending', 422, 'CNH não está pendente.');

        $docsOk = $prestador->is_digital_license
            ? !empty($prestador->license_front_path)
            : !empty($prestador->license_front_path) && !empty($prestador->license_back_path);

        abort_if(!$docsOk, 422, 'Documentos de CNH não enviados.');

        $prestador->update([
            'has_license'          => true,
            'cnh_status'           => 'approved',
            'cnh_rejection_reason' => null,
        ]);

        return back()->with('success', "CNH de {$prestador->name} aprovada!");
    }

    public function rejeitarCnh(Request $request, Provider $prestador)
    {
        abort_if($prestador->cnh_status !== 'pending', 422, 'CNH não está pendente.');

        $request->validate(['motivo' => 'required|string|max:1000']);

        $prestador->update([
            'has_license'          => false,
            'cnh_status'           => 'rejected',
            'cnh_rejection_reason' => $request->motivo,
        ]);

        return back()->with('success', "CNH de {$prestador->name} rejeitada.");
    }
}
