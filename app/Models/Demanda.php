<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demanda extends Model
{
    protected $table = 'demandas';

    // Usa criado_em em vez de created_at, sem updated_at
    const CREATED_AT = 'criado_em';
    const UPDATED_AT = null;

    protected $fillable = [
        'empresa_id', 'servico_id', 'data', 'hora_inicio', 'hora_fim',
        'local', 'descricao', 'status', 'valor_total',
        'quantidade_necessaria', 'quantidade_confirmada',
        'cep', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
        'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'data'                  => 'date',
            'criado_em'             => 'datetime',
            'valor_total'           => 'decimal:2',
            'quantidade_necessaria' => 'integer',
            'quantidade_confirmada' => 'integer',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
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
