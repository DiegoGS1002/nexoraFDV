<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roteiro extends Model
{
    protected $fillable = [
        'vendedor_id', 'nome', 'dia_semana', 'data_especifica', 'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'data_especifica' => 'date',
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'roteiros_clientes')
            ->withPivot('ordem', 'observacoes')
            ->orderByPivot('ordem');
    }
}
