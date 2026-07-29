<?php

namespace App\Support;

use App\Models\Schedule;
use App\Models\UserBlockLog;
use Illuminate\Support\Facades\DB;

class FaltaPenaltyService
{
    /**
     * Aplica o bloqueio de candidatura ao prestador por falta não justificada
     * (prazo de justificativa expirado, ou justificativa rejeitada pelo admin)
     * e registra a penalidade no histórico. $adminId nulo indica aplicação automática.
     */
    public static function aplicarPenalidade(Schedule $schedule, ?int $adminId = null): void
    {
        DB::transaction(function () use ($schedule, $adminId) {
            $provider = $schedule->provider;
            $ate      = now()->addDays((int) config('faltas.duracao_bloqueio_dias'));
            $motivo   = "Falta não justificada na demanda #{$schedule->demand_id} em " . $schedule->date->format('d/m/Y') . '.';

            $provider->update([
                'application_blocked_until' => $ate,
                'application_block_reason'  => $motivo,
            ]);

            $schedule->update([
                'no_show_status'               => 'unjustified',
                'no_show_reviewed_at'          => now(),
                'no_show_reviewed_by_admin_id' => $adminId,
                'no_show_penalty_applied'      => true,
            ]);

            UserBlockLog::create([
                'tipo'          => 'prestador',
                'usuario_id'    => $provider->id,
                'nome'          => $provider->name,
                'email'         => $provider->email,
                'acao'          => 'bloqueio_falta',
                'motivo'        => $motivo,
                'blocked_until' => $ate,
                'admin_id'      => $adminId,
                'created_at'    => now(),
            ]);
        });
    }
}
