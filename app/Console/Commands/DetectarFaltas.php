<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:detectar-faltas')]
#[Description('Detecta agendamentos sem check-in cujo horário já passou e marca como falta pendente de justificativa')]
class DetectarFaltas extends Command
{
    public function handle(): void
    {
        $horasParaNoShow = (int) config('faltas.horas_para_no_show');
        $prazoHoras       = (int) config('faltas.prazo_justificativa_horas');
        $limite           = now()->subHours($horasParaNoShow);

        $schedules = Schedule::where('status', 'scheduled')
            ->where('no_show_status', 'none')
            ->whereNull('check_in_at')
            ->whereRaw('TIMESTAMP(date, start_time) <= ?', [$limite->format('Y-m-d H:i:s')])
            ->get();

        $total = 0;
        foreach ($schedules as $schedule) {
            $schedule->update([
                'no_show_status'                  => 'pending_justification',
                'no_show_detected_at'              => now(),
                'no_show_justification_deadline'   => now()->addHours($prazoHoras),
            ]);
            $total++;
        }

        $this->info("{$total} falta(s) detectada(s).");
    }
}
