<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Company extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'trade_name', 'legal_name', 'cnpj', 'email', 'password', 'phone',
        'zip_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
        'cnpj_card_path', 'status', 'rejection_reason', 'active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'active'            => 'boolean',
            'email_verified_at' => 'datetime',
        ];
    }

    public function demands()
    {
        return $this->hasMany(Demand::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
