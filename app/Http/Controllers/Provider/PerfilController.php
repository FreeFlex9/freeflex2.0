<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PerfilController extends Controller
{
    public function index()
    {
        return Inertia::render('Prestador/Perfil', [
            'provider' => Auth::guard('provider')->user(),
        ]);
    }

    public function updateInfo(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $data = $request->validate([
            'phone' => 'nullable|string|max:20',
            'bio'   => 'nullable|string|max:1000',
        ]);

        $provider->update($data);
        return back()->with('success', 'Informações atualizadas!');
    }

    public function uploadDocument(Request $request)
    {
        $provider = Auth::guard('provider')->user();

        $request->validate([
            'tipo'    => 'required|in:profile_photo,rg_front,rg_back,license_front,license_back,ccmei',
            'arquivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'arquivo.max'   => 'Arquivo muito grande. Máximo 5 MB.',
            'arquivo.mimes' => 'Somente JPG, PNG ou PDF.',
        ]);

        $campoMap = [
            'profile_photo' => 'profile_photo_path',
            'rg_front'      => 'rg_front_path',
            'rg_back'       => 'rg_back_path',
            'license_front' => 'license_front_path',
            'license_back'  => 'license_back_path',
            'ccmei'         => 'ccmei_path',
        ];

        $campo = $campoMap[$request->tipo];

        if ($provider->$campo) {
            Storage::disk('public')->delete($provider->$campo);
        }

        $path = $request->file('arquivo')->store("providers/{$provider->id}", 'public');
        $provider->update([$campo => $path]);

        return back()->with('success', 'Documento enviado com sucesso!');
    }

    public function removeDocument(Request $request)
    {
        $provider = Auth::guard('provider')->user();
        $request->validate(['tipo' => 'required|in:profile_photo,rg_front,rg_back,license_front,license_back,ccmei']);

        $campoMap = [
            'profile_photo' => 'profile_photo_path',
            'rg_front'      => 'rg_front_path',
            'rg_back'       => 'rg_back_path',
            'license_front' => 'license_front_path',
            'license_back'  => 'license_back_path',
            'ccmei'         => 'ccmei_path',
        ];

        $campo = $campoMap[$request->tipo];
        if ($provider->$campo) {
            Storage::disk('public')->delete($provider->$campo);
            $provider->update([$campo => null]);
        }

        return back()->with('success', 'Documento removido.');
    }
}
