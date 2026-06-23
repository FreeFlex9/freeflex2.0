<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    protected $table = 'servicos';

    public $timestamps = false;

    protected $fillable = ['nome', 'valor_hora', 'valor_repasse', 'precisa_cnh'];

    protected function casts(): array
    {
        return [
            'valor_hora'    => 'decimal:2',
            'valor_repasse' => 'decimal:2',
            'precisa_cnh'   => 'boolean',
        ];
    }

    public function demandas()
    {
        return $this->hasMany(Demanda::class);
    }
}
