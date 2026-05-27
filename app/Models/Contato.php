<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contato extends Model
{
    protected $fillable = ['nome', 'cargo', 'email', 'phone', 'principal'];

    protected $casts = ['principal' => 'boolean'];

    public function contatavel()
    {
        return $this->morphTo();
    }
}
