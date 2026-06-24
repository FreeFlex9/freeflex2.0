<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderDocument extends Model
{
    protected $fillable = ['provider_id', 'file_name', 'file_path', 'document_type'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
