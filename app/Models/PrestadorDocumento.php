<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestadorDocumento extends Model
{
    protected $table = 'prestador_documentos';

    const CREATED_AT = 'data_upload';
    const UPDATED_AT = null;

    protected $fillable = ['prestador_id', 'nome_arquivo', 'caminho_arquivo'];

    public function prestador()
    {
        return $this->belongsTo(Prestador::class, 'prestador_id');
    }
}
