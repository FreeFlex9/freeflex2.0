<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = [
        'demand_id', 'provider_id', 'message', 'status', 'is_direct',
        'had_recent_surgery', 'surgery_description', 'health_consent',
        'accepted_at', 'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'is_direct'          => 'boolean',
            'had_recent_surgery' => 'boolean',
            'health_consent'     => 'boolean',
            'accepted_at'        => 'datetime',
            'withdrawn_at'       => 'datetime',
        ];
    }

    public function desistenciaPrazo(): ?\Illuminate\Support\Carbon
    {
        return $this->accepted_at?->copy()->addHours((int) config('faltas.janela_desistencia_horas'));
    }

    public function podeDesistir(): bool
    {
        return $this->status === 'accepted'
            && $this->accepted_at !== null
            && $this->desistenciaPrazo()->isFuture();
    }

    public function demand()
    {
        return $this->belongsTo(Demand::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
