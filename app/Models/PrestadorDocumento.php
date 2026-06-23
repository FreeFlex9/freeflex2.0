<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestadorDocumento extends Model
{
    protected $fillable = ['prestador_id', 'nome_arquivo', 'caminho_arquivo'];

    public function prestador()
    {
        return $this->belongsTo(User::class, 'prestador_id');
    }
}
