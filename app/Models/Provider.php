<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Provider extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'cpf', 'email', 'password', 'phone', 'birth_date',
        'has_license', 'is_digital_license', 'license_number',
        'license_front_path', 'license_back_path',
        'rg_front_path', 'rg_back_path',
        'mei_cnpj', 'ccmei_path', 'profile_photo_path', 'bio',
        'status', 'rejection_reason', 'active',
        'zip_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
        'latitude', 'longitude', 'start_hour', 'end_hour',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'has_license'       => 'boolean',
            'is_digital_license'=> 'boolean',
            'active'            => 'boolean',
            'email_verified_at' => 'datetime',
            'birth_date'        => 'date',
        ];
    }

    public function documents()
    {
        return $this->hasMany(ProviderDocument::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'provider_services');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
