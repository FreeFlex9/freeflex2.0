<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = [
        'demand_id', 'provider_id', 'message', 'status', 'is_direct',
        'had_recent_surgery', 'surgery_description', 'health_consent',
    ];

    protected function casts(): array
    {
        return [
            'is_direct'          => 'boolean',
            'had_recent_surgery' => 'boolean',
            'health_consent'     => 'boolean',
        ];
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
