<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class EmpresaAuthController extends Controller
{
    public function showRegister()
    {
        return Inertia::render('Auth/RegisterEmpresa');
    }

    public function register(Request $request)
    {
        $data = Validator::make($request->all(), [
            'cnpj'         => ['required', 'string'],
            'nome_fantasia' => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users'],
            'telefone'     => ['required', 'string'],
            'cep'          => ['nullable', 'string'],
            'logradouro'   => ['nullable', 'string', 'max:255'],
            'numero'       => ['nullable', 'string', 'max:20'],
            'complemento'  => ['nullable', 'string', 'max:100'],
            'bairro'       => ['nullable', 'string', 'max:100'],
            'cidade'       => ['nullable', 'string', 'max:100'],
            'estado'       => ['nullable', 'string', 'max:2'],
            'password'     => ['required', 'confirmed', 'min:8'],
        ])->validate();

        $cnpjRaw     = preg_replace('/\D/', '', $data['cnpj']);
        $telefoneRaw = preg_replace('/\D/', '', $data['telefone']);
        $cepRaw      = preg_replace('/\D/', '', $data['cep'] ?? '');

        if (User::where('cnpj', $cnpjRaw)->exists()) {
            return back()->withErrors(['cnpj' => 'CNPJ já cadastrado.']);
        }

        $user = User::create([
            'name'          => $data['nome_fantasia'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'role'          => 'empresa',
            'cnpj'          => $cnpjRaw,
            'nome_fantasia' => $data['nome_fantasia'],
            'telefone'      => $telefoneRaw,
            'cep'           => $cepRaw ?: null,
            'logradouro'    => $data['logradouro'] ?? null,
            'numero'        => $data['numero'] ?? null,
            'complemento'   => $data['complemento'] ?? null,
            'bairro'        => $data['bairro'] ?? null,
            'cidade'        => $data['cidade'] ?? null,
            'estado'        => $data['estado'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
