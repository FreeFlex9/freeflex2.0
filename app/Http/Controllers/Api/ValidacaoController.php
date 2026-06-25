<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidacaoController extends Controller
{
    public function cpf(string $cpf)
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return response()->json(['valido' => false, 'mensagem' => 'CPF inválido.']);
        }

        try {
            $response = Http::timeout(10)
                ->withUserAgent('FreeFlex/2.0')
                ->get("https://scpa-backend.saude.gov.br/public/scpa-usuario/validacao-cpf/{$cpf}");

            if (!$response->successful()) {
                return response()->json(['valido' => false, 'mensagem' => 'Serviço de validação indisponível. Tente novamente.']);
            }

            $body = trim(strtolower($response->body()));

            if ($body === 'true') {
                return response()->json(['valido' => true, 'mensagem' => 'CPF válido.']);
            }

            $decoded = $response->json();
            if (is_array($decoded) && isset($decoded[0]['error']) && $decoded[0]['error'] === 'cpf-nao-encotrado-receita-federal') {
                return response()->json(['valido' => false, 'mensagem' => 'CPF não encontrado na base da Receita Federal.']);
            }

            return response()->json(['valido' => false, 'mensagem' => 'CPF inválido.']);

        } catch (\Exception $e) {
            Log::warning('API CPF Saude.gov indisponível: ' . $e->getMessage());
            return response()->json(['valido' => null, 'mensagem' => 'Serviço temporariamente indisponível. O CPF será verificado pelo administrador.']);
        }
    }
}
