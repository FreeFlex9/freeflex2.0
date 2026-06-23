<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'cpf', 'cnpj', 'nome_fantasia', 'telefone',
        'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
        'is_mei', 'status_aprovacao', 'motivo_rejeicao',
        'possui_cnh', 'cnh_digital', 'numero_cnh', 'cnpj_mei',
        'cartao_cnpj_path', 'cnh_frente_path', 'cnh_verso_path',
        'rg_frente_path', 'rg_verso_path', 'ccmei_path',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_recovery_codes', 'two_factor_secret',
    ];

    protected $appends = ['profile_photo_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_mei'            => 'boolean',
            'possui_cnh'        => 'boolean',
            'cnh_digital'       => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmpresa(): bool
    {
        return $this->role === 'empresa';
    }

    public function isPrestador(): bool
    {
        return $this->role === 'prestador';
    }

    // Relacionamentos
    public function demandas()
    {
        return $this->hasMany(Demanda::class, 'empresa_id');
    }

    public function propostas()
    {
        return $this->hasMany(Proposta::class, 'prestador_id');
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'prestador_id');
    }

    public function documentos()
    {
        return $this->hasMany(PrestadorDocumento::class, 'prestador_id');
    }
}
