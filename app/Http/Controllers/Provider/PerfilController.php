<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Concerns\StoresOptimizedUploads;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    use StoresOptimizedUploads;

    public function index()
    {
        return inertia('Prestador/Perfil', [
            'provider' => Auth::guard('provider')->user(),
        ]);
    }

    public function updateInfo(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $data = $request->validate([
            'phone'       => 'nullable|string|max:20',
            'bio'         => 'nullable|string|max:1000',
            'has_license' => 'boolean',
            'is_pcd'      => 'boolean',
            'pcd_type'    => 'nullable|string|max:255',
        ]);

        $wantsCnh   = (bool) $data['has_license'];
        $isApproved = $provider->status === 'approved';
        $isPcd      = (bool) ($data['is_pcd'] ?? false);

        $update = [
            'phone'    => $data['phone'] ?? null,
            'bio'      => $data['bio']   ?? null,
            'is_pcd'   => $isPcd,
            'pcd_type' => $isPcd ? ($data['pcd_type'] ?? null) : null,
        ];

        if ($isApproved) {
            if (!$wantsCnh) {
                // Prestador quer remover CNH (ou cancelar solicitação pendente)
                if ($provider->has_license) {
                    $cnhIds = \App\Models\Service::where('requires_license', true)->pluck('id');
                    $provider->services()->detach($cnhIds);
                }
                $update['has_license']          = false;
                $update['cnh_status']           = null;
                $update['cnh_rejection_reason'] = null;
            } elseif (!$provider->has_license && $provider->cnh_status !== 'pending') {
                // Nova solicitação de CNH (ou reenvio após rejeição)
                $update['cnh_status']           = 'pending';
                $update['cnh_rejection_reason'] = null;
                $provider->update($update);
                return back()->with('cnh_notice', true);
            }
            // Se wantsCnh && (has_license || cnh_status=pending): sem mudança na CNH
        } else {
            // Prestador em cadastro inicial: salva has_license diretamente
            $hadLicense          = (bool) $provider->has_license;
            $update['has_license'] = $wantsCnh;

            if ($hadLicense && !$wantsCnh) {
                $cnhIds = \App\Models\Service::where('requires_license', true)->pluck('id');
                $provider->services()->detach($cnhIds);
            }

            if (!$hadLicense && $wantsCnh) {
                $provider->update($update);
                return back()->with('cnh_notice', true);
            }
        }

        $provider->update($update);
        return back()->with('success', 'Informações atualizadas!');
    }

    public function updatePassword(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $request->validate([
            'current_password'  => 'required|string',
            'password'          => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Informe a senha atual.',
            'password.confirmed'        => 'A nova senha não confere.',
            'password.min'              => 'A nova senha deve ter pelo menos 8 caracteres.',
        ]);

        if (!Hash::check($request->current_password, $provider->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta.']);
        }

        $provider->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Senha alterada com sucesso!');
    }

    public function uploadDocument(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        // Quando o arquivo excede post_max_size, o PHP descarta o upload inteiro
        // antes do Laravel processá-lo: $_FILES fica vazio e a regra "required"
        // falharia com uma mensagem genérica, escondendo a causa real.
        if (!$request->hasFile('arquivo') && (int) $request->server('CONTENT_LENGTH') > 0) {
            return back()->withErrors([
                'arquivo' => 'O arquivo enviado é muito grande para o servidor processar. Tente um arquivo menor (máx. 5 MB).',
            ]);
        }

        $request->validate([
            'tipo'    => 'required|in:profile_photo,rg_front,rg_back,license_front,license_back,ccmei,address_proof',
            'arquivo' => 'required|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ], [
            'arquivo.required' => 'Nenhum arquivo foi recebido pelo servidor. Tente novamente.',
            'arquivo.max'      => 'Arquivo muito grande. Máximo 5 MB.',
            'arquivo.mimes'    => 'Somente JPG, PNG, WebP ou PDF.',
        ]);

        $campoMap = [
            'profile_photo'  => 'profile_photo_path',
            'rg_front'       => 'rg_front_path',
            'rg_back'        => 'rg_back_path',
            'license_front'  => 'license_front_path',
            'license_back'   => 'license_back_path',
            'ccmei'          => 'ccmei_path',
            'address_proof'  => 'address_proof_path',
        ];

        $campo = $campoMap[$request->tipo];

        if ($provider->$campo) {
            Storage::disk('public')->delete($provider->$campo);
        }

        $path = $this->storeOptimized($request->file('arquivo'), "providers/{$provider->id}");
        $provider->update([$campo => $path]);

        return back()->with('success', 'Documento enviado com sucesso!');
    }

    public function updateAddress(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $data = $request->validate([
            'zip_code'     => 'nullable|string|max:10',
            'street'       => 'nullable|string|max:255',
            'number'       => 'nullable|string|max:20',
            'complement'   => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:2',
        ]);

        $provider->update($data);

        return back()->with('success', 'Endereço atualizado!');
    }

    public function removeDocument(Request $request)
    {
        $provider = Auth::guard('provider')->user();
        $request->validate(['tipo' => 'required|in:profile_photo,rg_front,rg_back,license_front,license_back,ccmei,address_proof']);

        $campoMap = [
            'profile_photo'  => 'profile_photo_path',
            'rg_front'       => 'rg_front_path',
            'rg_back'        => 'rg_back_path',
            'license_front'  => 'license_front_path',
            'license_back'   => 'license_back_path',
            'ccmei'          => 'ccmei_path',
            'address_proof'  => 'address_proof_path',
        ];

        $campo = $campoMap[$request->tipo];
        if ($provider->$campo) {
            Storage::disk('public')->delete($provider->$campo);
            $provider->update([$campo => null]);
        }

        return back()->with('success', 'Documento removido.');
    }
}
