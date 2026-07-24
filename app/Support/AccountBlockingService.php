<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Provider;
use App\Models\UserBlockLog;

class AccountBlockingService
{
    /**
     * Se o bloqueio temporário já expirou, reativa a conta e registra o
     * desbloqueio automático. Chamado sob demanda (login, middleware, comando
     * agendado) em vez de depender de um job/queue dedicado.
     */
    public static function liftIfExpired(Provider|Company $account): bool
    {
        if ($account->active || $account->blocked_until === null || $account->blocked_until->isFuture()) {
            return false;
        }

        $tipo = $account instanceof Company ? 'empresa' : 'prestador';
        $nome = $account instanceof Company ? $account->trade_name : $account->name;

        $account->update([
            'active'               => true,
            'blocked_at'           => null,
            'blocked_until'        => null,
            'block_reason'         => null,
            'blocked_by_admin_id'  => null,
        ]);

        UserBlockLog::create([
            'tipo'          => $tipo,
            'usuario_id'    => $account->id,
            'nome'          => $nome,
            'email'         => $account->email,
            'acao'          => 'desbloqueio',
            'motivo'        => null,
            'blocked_until' => null,
            'admin_id'      => null,
            'created_at'    => now(),
        ]);

        return true;
    }

    /**
     * Mensagem exibida quando uma conta bloqueada tenta acessar a plataforma.
     * Assume que liftIfExpired() já foi chamado (ou seja, se chegou aqui o
     * bloqueio ainda está em vigor).
     */
    public static function mensagemBloqueio(Provider|Company $account): string
    {
        if ($account->blocked_until === null) {
            return 'Sua conta foi bloqueada permanentemente. Motivo: ' . ($account->block_reason ?? 'não informado.');
        }

        return 'Sua conta está bloqueada até ' . $account->blocked_until->format('d/m/Y \à\s H:i')
            . '. Motivo: ' . ($account->block_reason ?? 'não informado.');
    }
}
