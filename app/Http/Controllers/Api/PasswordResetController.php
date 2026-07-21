<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\PasswordResetCode;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');

        $userType = null;
        if (Provider::where('email', $email)->exists()) {
            $userType = 'prestador';
        } elseif (Company::where('email', $email)->exists()) {
            $userType = 'empresa';
        }

        $response = ['message' => 'Se o e-mail estiver cadastrado, um código foi enviado.'];

        if ($userType) {
            $code = (string) random_int(100000, 999999);

            PasswordResetCode::where('email', $email)->delete();
            PasswordResetCode::create([
                'email' => $email,
                'user_type' => $userType,
                'code' => $code,
                'expires_at' => now()->addMinutes(30),
            ]);

            try {
                Mail::raw("Seu código de redefinição de senha FreeFlex é: {$code}\nVálido por 30 minutos.", function ($m) use ($email) {
                    $m->to($email)->subject('Redefinição de senha - FreeFlex');
                });
            } catch (\Exception $e) {
                Log::warning('Falha ao enviar e-mail de redefinição de senha: ' . $e->getMessage());
            }

            if (app()->environment('local')) {
                $response['debug_code'] = $code;
            }
        }

        return response()->json($response);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
            'senha' => ['required', 'string', Password::min(8)],
            'senha_confirmation' => 'required|string|same:senha',
        ]);

        $reset = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->where('expires_at', '>=', now())
            ->latest('id')
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'Código inválido ou expirado.'], 422);
        }

        $account = $reset->user_type === 'prestador'
            ? Provider::where('email', $reset->email)->first()
            : Company::where('email', $reset->email)->first();

        if (!$account) {
            return response()->json(['message' => 'Conta não encontrada.'], 404);
        }

        $account->password = Hash::make($request->senha);
        $account->save();

        $account->tokens()->delete();
        PasswordResetCode::where('email', $reset->email)->delete();

        return response()->json(['message' => 'Senha redefinida com sucesso!']);
    }
}
