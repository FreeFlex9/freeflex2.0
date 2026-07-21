<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\StoresOptimizedUploads;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Provider;
use App\Rules\Cnpj;
use App\Rules\Cpf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    use StoresOptimizedUploads;

    public function prestador(Request $request)
    {
        $hasLicense = $request->boolean('has_license');
        $isDigital = $request->boolean('is_digital_license');
        $docRules = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'];

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => ['required', 'string', new Cpf],
            'email' => 'required|email|max:255|unique:providers,email',
            'phone' => ['required', 'string', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
            'birth_date' => 'nullable|date',
            'has_license' => 'boolean',
            'is_digital_license' => 'boolean',
            'license_number' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'senha' => ['required', 'string', Password::min(8)],
            'senha_confirmation' => 'required|string|same:senha',
            'rg_front' => [Rule::requiredIf(!$hasLicense), ...$docRules],
            'rg_back' => [Rule::requiredIf(!$hasLicense), ...$docRules],
            'license_front' => [Rule::requiredIf($hasLicense), ...$docRules],
            'license_back' => [Rule::requiredIf($hasLicense && !$isDigital), ...$docRules],
        ]);

        $cpf = preg_replace('/\D/', '', $data['cpf']);

        if (Provider::where('cpf', $cpf)->exists()) {
            return response()->json(['message' => 'CPF já cadastrado.'], 422);
        }

        try {
            $resp = Http::timeout(10)
                ->withUserAgent('FreeFlex/2.0')
                ->get("https://scpa-backend.saude.gov.br/public/scpa-usuario/validacao-cpf/{$cpf}");

            if ($resp->successful()) {
                $body = trim(strtolower($resp->body()));
                if ($body !== 'true') {
                    return response()->json(['message' => 'CPF inválido segundo a Receita Federal.'], 422);
                }
            }
        } catch (\Exception $e) {
            Log::warning('API CPF Saude.gov indisponível no registro (mobile): ' . $e->getMessage());
        }

        $provider = Provider::create([
            'name' => $data['name'],
            'cpf' => $cpf,
            'email' => $data['email'],
            'phone' => preg_replace('/\D/', '', $data['phone']),
            'birth_date' => $data['birth_date'] ?? null,
            'has_license' => $hasLicense,
            'is_digital_license' => $isDigital,
            'license_number' => $data['license_number'] ?? null,
            'bio' => $data['bio'] ?? null,
            'password' => Hash::make($data['senha']),
            'status' => 'pending',
            'active' => true,
        ]);

        $dir = "providers/{$provider->id}";
        if ($hasLicense) {
            $provider->license_front_path = $this->storeOptimized($request->file('license_front'), $dir);
            if (!$isDigital) {
                $provider->license_back_path = $this->storeOptimized($request->file('license_back'), $dir);
            }
        } else {
            $provider->rg_front_path = $this->storeOptimized($request->file('rg_front'), $dir);
            $provider->rg_back_path = $this->storeOptimized($request->file('rg_back'), $dir);
        }
        $provider->save();

        return response()->json([
            'message' => 'Cadastro realizado com sucesso! Seu perfil está em análise.',
        ], 201);
    }

    public function empresa(Request $request)
    {
        $data = $request->validate([
            'trade_name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'cnpj' => ['required', 'string', new Cnpj],
            'email' => 'required|email|max:255|unique:companies,email',
            'phone' => ['required', 'string', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
            'zip_code' => 'nullable|string|max:10',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'senha' => ['required', 'string', Password::min(8)],
            'senha_confirmation' => 'required|string|same:senha',
            'cnpj_card' => 'required|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ]);

        $cnpj = preg_replace('/\D/', '', $data['cnpj']);

        if (Company::where('cnpj', $cnpj)->exists()) {
            return response()->json(['message' => 'CNPJ já cadastrado.'], 422);
        }

        try {
            $response = Http::timeout(6)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");
            if ($response->ok()) {
                $situacao = $response->json('descricao_situacao_cadastral', '');
                if ($situacao !== 'ATIVA') {
                    return response()->json([
                        'message' => "CNPJ com situação \"{$situacao}\" na Receita Federal. Apenas CNPJs ativos são aceitos.",
                    ], 422);
                }
                if (empty($data['legal_name'])) {
                    $data['legal_name'] = $response->json('razao_social');
                }
            } else {
                return response()->json(['message' => 'CNPJ não encontrado na Receita Federal.'], 422);
            }
        } catch (\Exception $e) {
            Log::warning('BrasilAPI CNPJ indisponível (mobile): ' . $e->getMessage());
        }

        $company = Company::create([
            'trade_name' => $data['trade_name'],
            'legal_name' => $data['legal_name'] ?? null,
            'cnpj' => $cnpj,
            'email' => $data['email'],
            'phone' => preg_replace('/\D/', '', $data['phone']),
            'zip_code' => $data['zip_code'] ?? null,
            'street' => $data['street'] ?? null,
            'number' => $data['number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'neighborhood' => $data['neighborhood'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'password' => Hash::make($data['senha']),
            'status' => 'pending',
            'active' => true,
        ]);

        $company->cnpj_card_path = $this->storeOptimized($request->file('cnpj_card'), "companies/{$company->id}");
        $company->save();

        return response()->json([
            'message' => 'Cadastro realizado com sucesso! Seu perfil está em análise.',
        ], 201);
    }
}
