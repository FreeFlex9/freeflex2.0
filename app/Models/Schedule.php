<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'provider_id', 'demand_id', 'date', 'start_time', 'end_time', 'description', 'status',
        'check_in_at', 'check_in_lat', 'check_in_lng', 'check_in_distance_m',
        'check_out_at', 'check_out_lat', 'check_out_lng', 'check_out_distance_m',
    ];

    protected function casts(): array
    {
        return [
            'date'         => 'date',
            'check_in_at'  => 'datetime',
            'check_out_at' => 'datetime',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function demand()
    {
        return $this->belongsTo(Demand::class);
    }
}
