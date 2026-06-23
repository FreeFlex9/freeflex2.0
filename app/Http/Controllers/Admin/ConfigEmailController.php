<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ConfigEmailController extends Controller
{
    private function cfg(): array
    {
        return DB::table('smtp_settings')->where('id', 1)->first((array)[
            'smtp_host', 'smtp_port', 'smtp_secure', 'smtp_username',
            'smtp_password', 'email_from', 'email_from_name',
        ]) ? (array) DB::table('smtp_settings')->where('id', 1)->first() : [
            'smtp_host' => '', 'smtp_port' => 587, 'smtp_secure' => 'tls',
            'smtp_username' => '', 'smtp_password' => '',
            'email_from' => '', 'email_from_name' => 'FreeFlex Notificações',
        ];
    }

    public function index()
    {
        $cfg = (array) DB::table('smtp_settings')->where('id', 1)->first();

        return Inertia::render('Admin/ConfigEmail/Index', [
            'config' => array_merge($cfg, ['smtp_password' => '']),
        ]);
    }

    public function salvar(Request $request)
    {
        $request->validate([
            'smtp_host'       => 'required|string',
            'smtp_port'       => 'required|integer|min:1|max:65535',
            'smtp_secure'     => 'required|in:tls,ssl',
            'smtp_username'   => 'required|string',
            'email_from'      => 'required|email',
            'email_from_name' => 'nullable|string|max:100',
        ]);

        $data = $request->only(['smtp_host', 'smtp_port', 'smtp_secure', 'smtp_username', 'email_from', 'email_from_name']);

        if ($request->filled('smtp_password')) {
            $data['smtp_password'] = $request->smtp_password;
        }

        DB::table('smtp_settings')->where('id', 1)->update($data);

        return back()->with('success', 'Configurações de e-mail salvas com sucesso.');
    }
}
