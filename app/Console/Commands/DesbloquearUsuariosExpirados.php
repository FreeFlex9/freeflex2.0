<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Provider;
use App\Support\AccountBlockingService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:desbloquear-usuarios-expirados')]
#[Description('Reativa prestadores/empresas cujo bloqueio temporário já expirou')]
class DesbloquearUsuariosExpirados extends Command
{
    public function handle(): void
    {
        $candidatos = Provider::where('active', false)->whereNotNull('blocked_until')->where('blocked_until', '<=', now())->get()
            ->concat(Company::where('active', false)->whereNotNull('blocked_until')->where('blocked_until', '<=', now())->get());

        $total = 0;
        foreach ($candidatos as $conta) {
            if (AccountBlockingService::liftIfExpired($conta)) {
                $total++;
            }
        }

        $this->info("{$total} conta(s) desbloqueada(s) automaticamente.");
    }
}
