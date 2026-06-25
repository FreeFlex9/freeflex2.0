<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Rules\Cpf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('provider')->check()) {
            return redirect()->route('prestador.dashboard');
        }
        return Inertia::render('Prestador/Auth/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Permite login por email ou CPF
        $isCpf = preg_match('/^\d{11}$/', preg_replace('/\D/', '', $request->email));
        $field  = $isCpf ? 'cpf' : 'email';
        $value  = $isCpf ? preg_replace('/\D/', '', $request->email) : $request->email;

        if (Auth::guard('provider')->attempt([$field => $value, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('prestador.dashboard'));
        }

        return back()->withErrors(['email' => 'E-mail/CPF ou senha incorretos.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return Inertia::render('Prestador/Auth/Register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'cpf'                => ['required', 'string', new Cpf],
            'email'              => 'required|email|max:255|unique:providers,email',
            'phone'              => ['required', 'string', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
            'birth_date'         => 'nullable|date',
            'has_license'        => 'boolean',
            'is_digital_license' => 'boolean',
            'license_number'     => 'nullable|string|max:20',
            'bio'                => 'nullable|string|max:1000',
            'password'           => ['required', 'confirmed', Password::min(8)],
        ]);

        $cpf = preg_replace('/\D/', '', $data['cpf']);

        if (Provider::where('cpf', $cpf)->exists()) {
            return back()->withErrors(['cpf' => 'CPF já cadastrado.']);
        }

        // Valida CPF na base da Receita Federal via API do Ministério da Saúde
        try {
            $resp = Http::timeout(10)
                ->withUserAgent('FreeFlex/2.0')
                ->get("https://scpa-backend.saude.gov.br/public/scpa-usuario/validacao-cpf/{$cpf}");

            if ($resp->successful()) {
                $body = trim(strtolower($resp->body()));
                if ($body !== 'true') {
                    $decoded = $resp->json();
                    $erro = (is_array($decoded) && isset($decoded[0]['error']))
                        ? 'CPF não encontrado na base da Receita Federal.'
                        : 'CPF inválido segundo a Receita Federal.';
                    return back()->withErrors(['cpf' => $erro])->withInput();
                }
            }
            // Se API retornar erro HTTP ou timeout, permite continuar (admin verifica)
        } catch (\Exception $e) {
            Log::warning('API CPF Saude.gov indisponível no registro: ' . $e->getMessage());
        }

        $provider = Provider::create([
            'name'               => $data['name'],
            'cpf'                => $cpf,
            'email'              => $data['email'],
            'phone'              => preg_replace('/\D/', '', $data['phone']),
            'birth_date'         => $data['birth_date'] ?? null,
            'has_license'        => $data['has_license'] ?? false,
            'is_digital_license' => $data['is_digital_license'] ?? false,
            'license_number'     => $data['license_number'] ?? null,
            'bio'                => $data['bio'] ?? null,
            'password'           => Hash::make($data['password']),
            'status'             => 'pending',
            'active'             => true,
        ]);

        Auth::guard('provider')->login($provider);
        $request->session()->regenerate();

        return redirect()->route('prestador.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('provider')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('prestador.login');
    }
}
