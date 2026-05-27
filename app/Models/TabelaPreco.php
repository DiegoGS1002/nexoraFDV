<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabelaPreco extends Model
{
    protected $table = 'tabelas_preco';

    protected $fillable = ['erp_code', 'nome', 'padrao', 'active', 'vigencia_inicio', 'vigencia_fim'];

    protected $casts = [
        'padrao' => 'boolean',
        'active' => 'boolean',
        'vigencia_inicio' => 'datetime',
        'vigencia_fim' => 'datetime',
    ];

    public function itens()
    {
        return $this->hasMany(TabelaPrecoItem::class);
    }

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'tabelas_preco_itens')
            ->withPivot('preco', 'desconto_maximo')
            ->withTimestamps();
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}
