<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\AccountDeletionLog;
use App\Models\Company;
use App\Models\Demand;
use App\Models\Provider;
use App\Notifications\AccountDeletedNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class AccountDeletionService
{
    /**
     * Motivos disponíveis no questionário de autoexclusão de conta.
     */
    public const MOTIVOS = [
        'preco'                  => 'O valor não compensa',
        'pouco_uso'              => 'Não estou usando a plataforma',
        'dificuldade_uso'        => 'Tive dificuldade para usar',
        'mudou_para_concorrente' => 'Estou usando outra plataforma',
        'experiencia_ruim'       => 'Tive uma experiência ruim',
        'outro'                  => 'Outro motivo',
    ];

    /**
     * Exclui a conta (prestador ou empresa), registra a auditoria e notifica os admins.
     * Usado tanto pela exclusão feita pelo admin quanto pela autoexclusão do usuário.
     */
    public static function excluir(
        Provider|Company $usuario,
        string $tipo,
        ?int $adminId = null,
        ?string $adminEmail = null,
        ?string $motivo = null,
        ?string $motivoDetalhes = null,
    ): void {
        $modelClass = $tipo === 'empresa' ? Company::class : Provider::class;
        $nome  = $tipo === 'empresa' ? $usuario->trade_name : $usuario->name;
        $email = $usuario->email;
        $id    = $usuario->id;

        DB::transaction(function () use ($usuario, $modelClass, $tipo, $nome, $email, $adminId, $adminEmail, $motivo, $motivoDetalhes) {
            if ($tipo === 'prestador') {
                self::liberarVagasDoPrestador($usuario);
            }

            DatabaseNotification::where('notifiable_type', $modelClass)
                ->where('notifiable_id', $usuario->id)
                ->delete();

            $usuario->delete();

            AccountDeletionLog::create([
                'tipo'            => $tipo,
                'usuario_id'      => $usuario->id,
                'nome'            => $nome,
                'email'           => $email,
                'admin_id'        => $adminId,
                'motivo'          => $motivo,
                'motivo_detalhes' => $motivoDetalhes,
                'deleted_at'      => now(),
            ]);

            Notification::send(Admin::all(), new AccountDeletedNotification($tipo, $nome, $email, $adminEmail, $motivo, $motivoDetalhes));
        });

        Storage::disk('public')->deleteDirectory(
            $tipo === 'empresa' ? "companies/{$id}" : "providers/{$id}"
        );
    }

    /**
     * Ao excluir um prestador com propostas aceitas/agendadas, o cascade de FK
     * remove proposals/schedules, mas demands.slots_confirmed e status ficam
     * desatualizados — reabre a vaga para a empresa encontrar outro prestador.
     */
    private static function liberarVagasDoPrestador(Provider $provider): void
    {
        $demandIds = $provider->schedules()->pluck('demand_id')->unique();

        $demands = Demand::whereIn('id', $demandIds)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->get();

        foreach ($demands as $demand) {
            $demand->slots_confirmed = max(0, $demand->slots_confirmed - 1);
            if ($demand->status !== 'open') {
                $demand->status = 'open';
            }
            $demand->save();
        }
    }
}
