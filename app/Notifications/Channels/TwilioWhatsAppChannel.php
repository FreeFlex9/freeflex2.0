<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioWhatsAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');

        if (! $sid || ! $token || ! $from) {
            Log::info('TwilioWhatsAppChannel: credenciais não configuradas, notificação por WhatsApp ignorada.');

            return;
        }

        $to = method_exists($notifiable, 'whatsappNumber') ? $notifiable->whatsappNumber() : null;

        if (! $to) {
            Log::info('TwilioWhatsAppChannel: destinatário sem telefone válido para WhatsApp.', [
                'notifiable' => get_class($notifiable) . '#' . $notifiable->getKey(),
            ]);

            return;
        }

        $body = $notification->toWhatsApp($notifiable);

        if (! $body) {
            return;
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->timeout(10)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => str_starts_with($from, 'whatsapp:') ? $from : "whatsapp:{$from}",
                    'To' => "whatsapp:{$to}",
                    'Body' => $body,
                ]);

            if (! $response->successful()) {
                Log::warning('TwilioWhatsAppChannel: falha ao enviar mensagem.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('TwilioWhatsAppChannel: exceção ao enviar mensagem.', ['message' => $e->getMessage()]);
        }
    }
}
