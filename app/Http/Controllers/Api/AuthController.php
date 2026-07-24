<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'senha' => 'required|string',
        ]);

        $login = trim($request->input('login'));
        $senha = $request->input('senha');
        $digits = preg_replace('/\D/', '', $login);
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;

        $account = null;
        $userType = null;

        if ($isEmail) {
            if ($account = Provider::where('email', $login)->first()) {
                $userType = 'prestador';
            } elseif ($account = Company::where('email', $login)->first()) {
                $userType = 'empresa';
            }
        } elseif (strlen($digits) === 11) {
            $account = Provider::where('cpf', $digits)->first();
            $userType = 'prestador';
        } elseif (strlen($digits) === 14) {
            $account = Company::where('cnpj', $digits)->first();
            $userType = 'empresa';
        }

        if (!$account || !Hash::check($senha, $account->password)) {
            return response()->json(['message' => 'Login ou senha incorretos.'], 422);
        }

        $token = $account->createToken('freeflex-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user_type' => $userType,
            'user' => $this->formatUser($account),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessão encerrada.']);
    }

    public function me(Request $request)
    {
        $account = $request->user();
        $userType = $account instanceof Provider ? 'prestador' : 'empresa';

        return response()->json([
            'user_type' => $userType,
            'user' => $this->formatUser($account),
        ]);
    }

    private function formatUser(Provider|Company $account): array
    {
        return [
            'id' => $account->id,
            'nome' => $account instanceof Provider ? $account->name : $account->trade_name,
            'email' => $account->email,
            'telefone' => $account->phone,
            'status_aprovacao' => $account->status,
        ];
    }
}
