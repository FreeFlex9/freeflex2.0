<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PerfilController extends Controller
{
    public function index()
    {
        return Inertia::render('Empresa/Perfil', [
            'company' => Auth::guard('company')->user(),
        ]);
    }

    public function updateInfo(Request $request)
    {
        $company = Auth::guard('company')->user();

        $data = $request->validate([
            'phone'        => 'nullable|string|max:20',
            'trade_name'   => 'required|string|max:255',
            'legal_name'   => 'nullable|string|max:255',
            'zip_code'     => 'nullable|string|max:10',
            'street'       => 'nullable|string|max:255',
            'number'       => 'nullable|string|max:20',
            'complement'   => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:2',
        ]);

        $company->update($data);
        return back()->with('success', 'Informações atualizadas!');
    }

    public function uploadDocument(Request $request)
    {
        $company = Auth::guard('company')->user();

        $request->validate([
            'arquivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'arquivo.max'   => 'Arquivo muito grande. Máximo 5 MB.',
            'arquivo.mimes' => 'Somente JPG, PNG ou PDF.',
        ]);

        if ($company->cnpj_card_path) {
            Storage::disk('public')->delete($company->cnpj_card_path);
        }

        $path = $request->file('arquivo')->store("companies/{$company->id}", 'public');
        $company->update(['cnpj_card_path' => $path]);

        return back()->with('success', 'Cartão CNPJ enviado com sucesso!');
    }
}
