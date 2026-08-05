<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ApprovesProviders;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestadoresController extends Controller
{
    use ApprovesProviders;

    public function index()
    {
        $providers = Provider::where('status', 'pending')
            ->with('documents')
            ->orderBy('name')
            ->get([
                'id', 'name', 'cpf', 'email', 'phone',
                'has_license', 'is_digital_license', 'license_number', 'mei_cnpj',
                'license_front_path', 'license_back_path',
                'rg_front_path', 'rg_back_path', 'ccmei_path', 'address_proof_path', 'ctps_path',
                'profile_photo_path', 'created_at',
            ]);

        $cnhPendentes = Provider::where('status', 'approved')
            ->where('cnh_status', 'pending')
            ->orderBy('name')
            ->get([
                'id', 'name', 'email', 'phone',
                'is_digital_license', 'license_front_path', 'license_back_path',
            ]);

        return Inertia::render('Admin/Prestadores/Index', [
            'prestadores'  => $providers,
            'cnhPendentes' => $cnhPendentes,
        ]);
    }

    public function aprovar(Request $request, Provider $prestador)
    {
        $this->assertProviderApprovable($prestador);

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
        $this->assertProviderCnhApprovable($prestador);

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
