<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    protected $table = 'agendamentos';

    const CREATED_AT = 'criado_em';
    const UPDATED_AT = null;

    protected $fillable = [
        'prestador_id', 'demanda_id', 'data', 'hora_inicio', 'hora_fim',
        'tipo', 'descricao', 'origem',
    ];

    protected function casts(): array
    {
        return [
            'data'      => 'date',
            'criado_em' => 'datetime',
        ];
    }

    public function prestador()
    {
        return $this->belongsTo(Prestador::class, 'prestador_id');
    }

    public function demanda()
    {
        return $this->belongsTo(Demanda::class);
    }
}
