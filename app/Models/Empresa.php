<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresas';

    public $timestamps = false;

    protected $fillable = [
        'nome_fantasia', 'email', 'cnpj', 'senha', 'telefone',
        'cep', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
        'status_aprovacao', 'motivo_rejeicao', 'cartao_cnpj_path', 'ativo',
    ];

    protected $hidden = ['senha'];

    protected function casts(): array
    {
        return [
            'criado_em'     => 'datetime',
            'data_cadastro' => 'datetime',
            'ativo'         => 'boolean',
        ];
    }

    public function demandas()
    {
        return $this->hasMany(Demanda::class, 'empresa_id');
    }
}
