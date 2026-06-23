<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposta extends Model
{
    protected $table = 'propostas';

    const CREATED_AT = 'enviado_em';
    const UPDATED_AT = null;

    protected $fillable = [
        'demanda_id', 'prestador_id', 'mensagem', 'valor_total', 'status', 'enviado_em',
    ];

    protected function casts(): array
    {
        return [
            'valor_total' => 'decimal:2',
            'enviado_em'  => 'datetime',
        ];
    }

    public function demanda()
    {
        return $this->belongsTo(Demanda::class);
    }

    public function prestador()
    {
        return $this->belongsTo(Prestador::class, 'prestador_id');
    }
}
