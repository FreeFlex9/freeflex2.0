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
        $hasLicense = $request->boolean('tem_cnh');
        $isDigital = $request->boolean('cnh_digital');
        $docRules = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'];

        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => ['required', 'string', new Cpf],
            'email' => 'required|email|max:255|unique:providers,email',
            'telefone' => ['required', 'string', 'regex:/^\d{10,11}$/'],
            'senha' => ['required', 'string', Password::min(8)],
            'senha_confirmation' => 'required|string|same:senha',
            'tem_cnh' => 'boolean',
            'cnh_digital' => 'boolean',
            'numero_cnh' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'rg_frente' => [Rule::requiredIf(!$hasLicense), ...$docRules],
            'rg_verso' => [Rule::requiredIf(!$hasLicense), ...$docRules],
            'cnh_frente' => [Rule::requiredIf($hasLicense), ...$docRules],
            'cnh_verso' => [Rule::requiredIf($hasLicense && !$isDigital), ...$docRules],
        ], [
            'rg_frente.required' => 'Envie a foto da frente do RG.',
            'rg_verso.required' => 'Envie a foto do verso do RG.',
            'cnh_frente.required' => 'Envie a foto da CNH.',
            'cnh_verso.required' => 'Envie a foto do verso da CNH.',
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
            'name' => $data['nome'],
            'cpf' => $cpf,
            'email' => $data['email'],
            'phone' => preg_replace('/\D/', '', $data['telefone']),
            'has_license' => $hasLicense,
            'is_digital_license' => $isDigital,
            'license_number' => $data['numero_cnh'] ?? null,
            'password' => Hash::make($data['senha']),
            'zip_code' => isset($data['cep']) ? preg_replace('/\D/', '', $data['cep']) : null,
            'street' => $data['endereco'] ?? null,
            'number' => $data['numero'] ?? null,
            'complement' => $data['complemento'] ?? null,
            'neighborhood' => $data['bairro'] ?? null,
            'city' => $data['cidade'] ?? null,
            'state' => $data['estado'] ?? null,
            'status' => 'pending',
            'active' => true,
        ]);

        $dir = "providers/{$provider->id}";
        if ($hasLicense) {
            $provider->license_front_path = $this->storeOptimized($request->file('cnh_frente'), $dir);
            if (!$isDigital) {
                $provider->license_back_path = $this->storeOptimized($request->file('cnh_verso'), $dir);
            }
        } else {
            $provider->rg_front_path = $this->storeOptimized($request->file('rg_frente'), $dir);
            $provider->rg_back_path = $this->storeOptimized($request->file('rg_verso'), $dir);
        }
        $provider->save();

        return response()->json([
            'message' => 'Cadastro realizado com sucesso! Seu perfil está em análise.',
        ], 201);
    }

    public function empresa(Request $request)
    {
        $data = $request->validate([
            'nome_fantasia' => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'cnpj' => ['required', 'string', new Cnpj],
            'email' => 'required|email|max:255|unique:companies,email',
            'telefone' => ['nullable', 'string', 'regex:/^\d{10,11}$/'],
            'senha' => ['required', 'string', Password::min(8)],
            'senha_confirmation' => 'required|string|same:senha',
            'cep' => 'nullable|string|max:10',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'cartao_cnpj' => 'required|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ], [
            'cartao_cnpj.required' => 'Envie o Cartão CNPJ.',
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
                if (empty($data['razao_social'])) {
                    $data['razao_social'] = $response->json('razao_social');
                }
            } else {
                return response()->json(['message' => 'CNPJ não encontrado na Receita Federal.'], 422);
            }
        } catch (\Exception $e) {
            Log::warning('BrasilAPI CNPJ indisponível (mobile): ' . $e->getMessage());
        }

        $company = Company::create([
            'trade_name' => $data['nome_fantasia'],
            'legal_name' => $data['razao_social'] ?? null,
            'cnpj' => $cnpj,
            'email' => $data['email'],
            'phone' => isset($data['telefone']) ? preg_replace('/\D/', '', $data['telefone']) : null,
            'zip_code' => isset($data['cep']) ? preg_replace('/\D/', '', $data['cep']) : null,
            'street' => $data['endereco'] ?? null,
            'number' => $data['numero'] ?? null,
            'complement' => $data['complemento'] ?? null,
            'neighborhood' => $data['bairro'] ?? null,
            'city' => $data['cidade'] ?? null,
            'state' => $data['estado'] ?? null,
            'password' => Hash::make($data['senha']),
            'status' => 'pending',
            'active' => true,
        ]);

        $company->cnpj_card_path = $this->storeOptimized($request->file('cartao_cnpj'), "companies/{$company->id}");
        $company->save();

        return response()->json([
            'message' => 'Cadastro realizado com sucesso! Seu perfil está em análise.',
        ], 201);
    }
}
