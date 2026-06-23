<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    protected $fillable = [
        'prestador_id', 'demanda_id', 'data', 'hora_inicio', 'hora_fim', 'descricao', 'origem',
    ];

    protected function casts(): array
    {
        return ['data' => 'date'];
    }

    public function prestador()
    {
        return $this->belongsTo(User::class, 'prestador_id');
    }

    public function demanda()
    {
        return $this->belongsTo(Demanda::class);
    }
}
