<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PerfilController extends Controller
{
    public function edit()
    {
        $admin = Auth::guard('admin')->user();

        return Inertia::render('Admin/Perfil/Edit', [
            'admin' => ['id' => $admin->id, 'email' => $admin->email],
        ]);
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'senha_atual'             => 'required',
            'nova_senha'              => 'required|min:6|confirmed',
            'nova_senha_confirmation' => 'required',
        ]);

        if (!Hash::check($request->senha_atual, $admin->senha)) {
            return back()->withErrors(['senha_atual' => 'Senha atual incorreta.']);
        }

        $admin->senha = Hash::make($request->nova_senha);
        $admin->save();

        return back()->with('success', 'Senha alterada com sucesso!');
    }
}
