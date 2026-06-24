<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = ['demand_id', 'provider_id', 'message', 'status'];

    public function demand()
    {
        return $this->belongsTo(Demand::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
