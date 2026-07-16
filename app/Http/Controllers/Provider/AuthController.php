<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Concerns\StoresOptimizedUploads;
use App\Http\Controllers\Concerns\ValidatesDocumentType;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Rules\Cpf;
use App\Services\DocumentTypeClassifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    use StoresOptimizedUploads, ValidatesDocumentType;

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
        $hasLicense = $request->boolean('has_license');
        $isDigital  = $request->boolean('is_digital_license');
        $docRules   = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'];

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
            'rg_front'           => [Rule::requiredIf(!$hasLicense), ...$docRules],
            'rg_back'            => [Rule::requiredIf(!$hasLicense), ...$docRules],
            'license_front'      => [Rule::requiredIf($hasLicense), ...$docRules],
            'license_back'       => [Rule::requiredIf($hasLicense && !$isDigital), ...$docRules],
        ], [
            'rg_front.required'      => 'Envie a foto da frente do RG.',
            'rg_back.required'       => 'Envie a foto do verso do RG.',
            'license_front.required' => 'Envie a foto da CNH.',
            'license_back.required'  => 'Envie a foto do verso da CNH.',
            'rg_front.mimes'         => 'Somente JPG, PNG, WebP ou PDF.',
            'rg_back.mimes'          => 'Somente JPG, PNG, WebP ou PDF.',
            'license_front.mimes'    => 'Somente JPG, PNG, WebP ou PDF.',
            'license_back.mimes'     => 'Somente JPG, PNG, WebP ou PDF.',
            'rg_front.max'           => 'Arquivo muito grande. Máximo 5 MB.',
            'rg_back.max'            => 'Arquivo muito grande. Máximo 5 MB.',
            'license_front.max'      => 'Arquivo muito grande. Máximo 5 MB.',
            'license_back.max'       => 'Arquivo muito grande. Máximo 5 MB.',
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

        $expectedTypes = $hasLicense
            ? ['license_front' => DocumentTypeClassifier::CNH, 'license_back' => DocumentTypeClassifier::CNH]
            : ['rg_front' => DocumentTypeClassifier::RG, 'rg_back' => DocumentTypeClassifier::RG];

        $validacoes = [];
        foreach ($expectedTypes as $campo => $tipoEsperado) {
            if (!$request->hasFile($campo)) {
                continue;
            }
            $resultado = $this->validarTipoDocumento($request->file($campo), $tipoEsperado);
            if (!$resultado['ok']) {
                return back()->withErrors([$campo => $resultado['message']])->withInput();
            }
            $validacoes = $this->registrarValidacaoDocumento($validacoes, $campo, $resultado);
        }

        $provider = Provider::create([
            'name'               => $data['name'],
            'cpf'                => $cpf,
            'email'              => $data['email'],
            'phone'              => preg_replace('/\D/', '', $data['phone']),
            'birth_date'         => $data['birth_date'] ?? null,
            'has_license'        => $hasLicense,
            'is_digital_license' => $isDigital,
            'license_number'     => $data['license_number'] ?? null,
            'bio'                => $data['bio'] ?? null,
            'password'           => Hash::make($data['password']),
            'status'             => 'pending',
            'active'             => true,
            'document_validation' => $validacoes,
        ]);

        $dir = "providers/{$provider->id}";
        if ($hasLicense) {
            $provider->license_front_path = $this->storeOptimized($request->file('license_front'), $dir);
            if (!$isDigital) {
                $provider->license_back_path = $this->storeOptimized($request->file('license_back'), $dir);
            }
        } else {
            $provider->rg_front_path = $this->storeOptimized($request->file('rg_front'), $dir);
            $provider->rg_back_path  = $this->storeOptimized($request->file('rg_back'), $dir);
        }
        $provider->save();

        Auth::guard('provider')->login($provider);
        $request->session()->regenerate();

        return redirect()->route('prestador.dashboard')
            ->with('success', 'Cadastro realizado e documentos enviados com sucesso! Seu perfil está em análise.');
    }

    public function logout(Request $request)
    {
        Auth::guard('provider')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('prestador.login');
    }
}
