<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demanda extends Model
{
    protected $fillable = [
        'empresa_id', 'servico_id', 'data', 'hora_inicio', 'hora_fim',
        'local', 'descricao', 'quantidade_necessaria', 'quantidade_confirmada',
        'valor_total', 'status',
    ];

    protected function casts(): array
    {
        return [
            'data'                  => 'date',
            'valor_total'           => 'decimal:2',
            'quantidade_necessaria' => 'integer',
            'quantidade_confirmada' => 'integer',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(User::class, 'empresa_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function propostas()
    {
        return $this->hasMany(Proposta::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }
}
