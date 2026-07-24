<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\StoresOptimizedUploads;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Rules\Cnpj;
use App\Support\AccountBlockingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    use StoresOptimizedUploads;

    public function showLogin()
    {
        if (Auth::guard('company')->check()) {
            return redirect()->route('empresa.dashboard');
        }
        return Inertia::render('Empresa/Auth/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Permite login por email ou CNPJ
        $field = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'cnpj';
        $credentials = [$field => $request->email, 'password' => $request->password];

        if (Auth::guard('company')->attempt($credentials, $request->boolean('remember'))) {
            $company = Auth::guard('company')->user();
            AccountBlockingService::liftIfExpired($company);

            if (!$company->active) {
                Auth::guard('company')->logout();
                return back()->withErrors(['email' => AccountBlockingService::mensagemBloqueio($company)])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('empresa.dashboard'));
        }

        return back()->withErrors(['email' => 'E-mail/CNPJ ou senha incorretos.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return Inertia::render('Empresa/Auth/Register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'trade_name'   => 'required|string|max:255',
            'legal_name'   => 'nullable|string|max:255',
            'cnpj'         => ['required', 'string', new Cnpj],
            'email'        => 'required|email|max:255|unique:companies,email',
            'phone'        => ['required', 'string', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
            'zip_code'     => 'nullable|string|max:10',
            'street'       => 'nullable|string|max:255',
            'number'       => 'nullable|string|max:20',
            'complement'   => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:2',
            'password'     => ['required', 'confirmed', Password::min(8)],
            'cnpj_card'    => 'required|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
        ], [
            'cnpj_card.required' => 'Envie o Cartão CNPJ.',
            'cnpj_card.mimes'    => 'Somente JPG, PNG, WebP ou PDF.',
            'cnpj_card.max'      => 'Arquivo muito grande. Máximo 5 MB.',
        ]);

        $cnpj = preg_replace('/\D/', '', $data['cnpj']);

        if (Company::where('cnpj', $cnpj)->exists()) {
            return back()->withErrors(['cnpj' => 'CNPJ já cadastrado.']);
        }

        // Verifica situação na Receita Federal via BrasilAPI
        try {
            $response = Http::timeout(6)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");
            if ($response->ok()) {
                $situacao = $response->json('descricao_situacao_cadastral', '');
                if ($situacao !== 'ATIVA') {
                    return back()->withErrors([
                        'cnpj' => "CNPJ com situação \"{$situacao}\" na Receita Federal. Apenas CNPJs ativos são aceitos.",
                    ]);
                }
                // Preenche razão social se não informada
                if (empty($data['legal_name'])) {
                    $data['legal_name'] = $response->json('razao_social');
                }
            } else {
                return back()->withErrors(['cnpj' => 'CNPJ não encontrado na Receita Federal.']);
            }
        } catch (\Exception $e) {
            Log::warning('BrasilAPI CNPJ indisponível: ' . $e->getMessage());
            // API fora do ar — permite continuar, admin vai verificar manualmente
        }

        $company = Company::create([
            'trade_name'   => $data['trade_name'],
            'legal_name'   => $data['legal_name'] ?? null,
            'cnpj'         => $cnpj,
            'email'        => $data['email'],
            'phone'        => preg_replace('/\D/', '', $data['phone']),
            'zip_code'     => $data['zip_code'] ?? null,
            'street'       => $data['street'] ?? null,
            'number'       => $data['number'] ?? null,
            'complement'   => $data['complement'] ?? null,
            'neighborhood' => $data['neighborhood'] ?? null,
            'city'         => $data['city'] ?? null,
            'state'        => $data['state'] ?? null,
            'password'     => Hash::make($data['password']),
            'status'       => 'pending',
            'active'       => true,
        ]);

        $company->cnpj_card_path = $this->storeOptimized($request->file('cnpj_card'), "companies/{$company->id}");
        $company->save();

        Auth::guard('company')->login($company);
        $request->session()->regenerate();

        return redirect()->route('empresa.dashboard')
            ->with('success', 'Cadastro realizado e documento enviado com sucesso! Seu perfil está em análise.');
    }

    public function logout(Request $request)
    {
        Auth::guard('company')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('empresa.login');
    }
}
