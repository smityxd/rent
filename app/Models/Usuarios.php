<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuarios extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios';
    protected $fillable = ['nombres', 'email', 'telefono', 'password', 'version', 'configuracion', 'estado'];

    protected $hidden = ['password', 'remember_token'];
}
