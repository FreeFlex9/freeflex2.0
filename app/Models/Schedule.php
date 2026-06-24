<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'provider_id', 'demand_id', 'date', 'start_time', 'end_time', 'description', 'status',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
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
