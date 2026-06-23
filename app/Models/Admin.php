<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table = 'admins';

    public $timestamps = false;

    protected $fillable = ['email', 'senha'];

    protected $hidden = ['senha'];

    // A coluna de senha no banco é 'senha', não 'password'
    public function getAuthPasswordName(): string
    {
        return 'senha';
    }
}
