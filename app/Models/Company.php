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
        'cnpj_card_path', 'address_proof_path', 'status', 'approved_at', 'rejection_reason', 'active',
        'blocked_at', 'blocked_until', 'block_reason', 'blocked_by_admin_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'active'            => 'boolean',
            'email_verified_at' => 'datetime',
            'approved_at'       => 'datetime',
            'blocked_at'        => 'datetime',
            'blocked_until'     => 'datetime',
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
