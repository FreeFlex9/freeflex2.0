<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBlockLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['tipo', 'usuario_id', 'nome', 'email', 'acao', 'motivo', 'blocked_until', 'admin_id', 'created_at'];

    protected function casts(): array
    {
        return [
            'blocked_until' => 'datetime',
            'created_at'    => 'datetime',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
