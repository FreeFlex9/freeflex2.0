<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Concerns\ApprovesProviders;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;

class PrestadoresController extends Controller
{
    use ApprovesProviders;

    public function index()
    {
        $providers = Provider::where('status', 'pending')
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

        return response()->json([
            'prestadores' => $providers->map(fn (Provider $p) => [
                'id' => $p->id,
                'nome' => $p->name,
                'cpf' => $p->cpf,
                'email' => $p->email,
                'telefone' => $p->phone,
                'tem_cnh' => (bool) $p->has_license,
                'cnh_digital' => (bool) $p->is_digital_license,
                'numero_cnh' => $p->license_number,
                'mei_cnpj' => $p->mei_cnpj,
                'criado_em' => $p->created_at?->toISOString(),
                'documentos' => [
                    'license_front_path' => !empty($p->license_front_path),
                    'license_back_path' => !empty($p->license_back_path),
                    'rg_front_path' => !empty($p->rg_front_path),
                    'rg_back_path' => !empty($p->rg_back_path),
                    'ccmei_path' => !empty($p->ccmei_path),
                    'address_proof_path' => !empty($p->address_proof_path),
                    'ctps_path' => !empty($p->ctps_path),
                    'profile_photo_path' => !empty($p->profile_photo_path),
                ],
            ]),
            'cnh_pendentes' => $cnhPendentes->map(fn (Provider $p) => [
                'id' => $p->id,
                'nome' => $p->name,
                'email' => $p->email,
                'telefone' => $p->phone,
                'cnh_digital' => (bool) $p->is_digital_license,
                'documentos' => [
                    'license_front_path' => !empty($p->license_front_path),
                    'license_back_path' => !empty($p->license_back_path),
                ],
            ]),
        ]);
    }

    public function aprovar(Request $request, Provider $prestador)
    {
        $this->assertProviderApprovable($prestador);

        $prestador->update(['status' => 'approved', 'approved_at' => now(), 'rejection_reason' => null]);

        return response()->json(['message' => "Prestador {$prestador->name} aprovado com sucesso!"]);
    }

    public function rejeitar(Request $request, Provider $prestador)
    {
        abort_if($prestador->status !== 'pending', 422, 'Status inválido.');

        $request->validate(['motivo' => 'required|string|max:1000']);

        $prestador->update(['status' => 'rejected', 'rejection_reason' => $request->motivo]);

        return response()->json(['message' => "Prestador {$prestador->name} rejeitado."]);
    }

    public function aprovarCnh(Provider $prestador)
    {
        $this->assertProviderCnhApprovable($prestador);

        $prestador->update([
            'has_license' => true,
            'cnh_status' => 'approved',
            'cnh_rejection_reason' => null,
        ]);

        return response()->json(['message' => "CNH de {$prestador->name} aprovada!"]);
    }

    public function rejeitarCnh(Request $request, Provider $prestador)
    {
        abort_if($prestador->cnh_status !== 'pending', 422, 'CNH não está pendente.');

        $request->validate(['motivo' => 'required|string|max:1000']);

        $prestador->update([
            'has_license' => false,
            'cnh_status' => 'rejected',
            'cnh_rejection_reason' => $request->motivo,
        ]);

        return response()->json(['message' => "CNH de {$prestador->name} rejeitada."]);
    }
}
