<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestador extends Model
{
    protected $table = 'prestadores';

    public $timestamps = false;

    protected $fillable = [
        'nome', 'email', 'cpf', 'telefone', 'senha',
        'foto_perfil', 'cep', 'endereco', 'numero', 'complemento',
        'bairro', 'cidade', 'estado',
        'possui_cnh', 'cnh_digital', 'numero_cnh', 'cnpj_mei',
        'cnh_frente_path', 'cnh_verso_path',
        'rg_frente_path', 'rg_verso_path', 'ccmei_path',
        'status_aprovacao', 'motivo_rejeicao', 'ativo',
    ];

    protected $hidden = ['senha'];

    protected function casts(): array
    {
        return [
            'criado_em'     => 'datetime',
            'data_cadastro' => 'datetime',
            'possui_cnh'    => 'boolean',
            'cnh_digital'   => 'boolean',
            'ativo'         => 'boolean',
        ];
    }

    public function documentos()
    {
        return $this->hasMany(PrestadorDocumento::class, 'prestador_id');
    }

    public function propostas()
    {
        return $this->hasMany(Proposta::class, 'prestador_id');
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'prestador_id');
    }
}
