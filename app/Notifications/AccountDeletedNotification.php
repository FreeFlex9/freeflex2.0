<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AccountDeletedNotification extends Notification
{
    public function __construct(
        private readonly string $tipo,
        private readonly string $nome,
        private readonly string $email,
        private readonly ?string $adminEmail,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $tipoLabel = $this->tipo === 'empresa' ? 'Empresa' : 'Prestador';

        return [
            'tipo'        => $this->tipo,
            'nome'        => $this->nome,
            'email'       => $this->email,
            'admin_email' => $this->adminEmail,
            'mensagem'    => "{$tipoLabel} \"{$this->nome}\" ({$this->email}) teve a conta excluída"
                . ($this->adminEmail ? " por {$this->adminEmail}." : '.'),
        ];
    }
}
