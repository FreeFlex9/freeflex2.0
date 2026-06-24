<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'hourly_rate', 'provider_rate', 'requires_license'];

    protected function casts(): array
    {
        return [
            'hourly_rate'      => 'decimal:2',
            'provider_rate'    => 'decimal:2',
            'requires_license' => 'boolean',
        ];
    }

    public function demands()
    {
        return $this->hasMany(Demand::class);
    }

    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'provider_services');
    }
}
