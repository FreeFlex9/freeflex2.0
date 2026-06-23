<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PerfilController extends Controller
{
    public function edit()
    {
        return Inertia::render('Admin/Perfil/Edit', [
            'admin' => auth()->user()->only(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $rules = ['email' => 'required|email|unique:users,email,' . $user->id];

        if ($request->filled('nova_senha')) {
            $rules['senha_atual']       = 'required';
            $rules['nova_senha']        = 'required|min:6|confirmed';
        }

        $request->validate($rules);

        if ($request->filled('nova_senha')) {
            if (!Hash::check($request->senha_atual, $user->password)) {
                return back()->withErrors(['senha_atual' => 'Senha atual incorreta.']);
            }
            $user->password = Hash::make($request->nova_senha);
        }

        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }
}
