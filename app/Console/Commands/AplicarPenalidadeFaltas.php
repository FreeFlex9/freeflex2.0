<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Support\FaltaPenaltyService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:aplicar-penalidade-faltas')]
#[Description('Aplica bloqueio automático de candidatura para faltas cujo prazo de justificativa expirou sem resposta')]
class AplicarPenalidadeFaltas extends Command
{
    public function handle(): void
    {
        $schedules = Schedule::where('no_show_status', 'pending_justification')
            ->whereNotNull('no_show_justification_deadline')
            ->where('no_show_justification_deadline', '<=', now())
            ->whereNull('no_show_justification')
            ->get();

        foreach ($schedules as $schedule) {
            FaltaPenaltyService::aplicarPenalidade($schedule);
        }

        $this->info("{$schedules->count()} penalidade(s) aplicada(s) automaticamente.");
    }
}
