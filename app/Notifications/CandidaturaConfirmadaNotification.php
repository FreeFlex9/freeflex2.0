<?php

namespace App\Notifications;

use App\Notifications\Channels\TwilioWhatsAppChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class CandidaturaConfirmadaNotification extends Notification
{
    public function __construct(
        private readonly int $demandId,
        private readonly string $demandTitle,
        private readonly ?string $companyName,
        private readonly Carbon $prazoDesistencia,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', TwilioWhatsAppChannel::class];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo'              => 'candidatura_confirmada',
            'demand_id'         => $this->demandId,
            'demand_title'      => $this->demandTitle,
            'company_name'      => $this->companyName,
            'prazo_desistencia' => $this->prazoDesistencia->toISOString(),
            'mensagem'          => $this->mensagem(),
        ];
    }

    public function toWhatsApp(object $notifiable): string
    {
        return $this->mensagem();
    }

    private function mensagem(): string
    {
        $prazoFormatado = $this->prazoDesistencia->format('d/m/Y H:i');

        return "Sua candidatura para \"{$this->demandTitle}\""
            . ($this->companyName ? " ({$this->companyName})" : '')
            . " foi confirmada. Você pode desistir sem penalidade até {$prazoFormatado}.";
    }
}
