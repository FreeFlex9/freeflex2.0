<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountDeletionLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['tipo', 'usuario_id', 'nome', 'email', 'admin_id', 'motivo', 'motivo_detalhes', 'deleted_at'];

    protected function casts(): array
    {
        return ['deleted_at' => 'datetime'];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
