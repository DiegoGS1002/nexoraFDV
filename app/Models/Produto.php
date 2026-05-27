<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'erp_code', 'sku', 'nome', 'descricao_curta', 'descricao', 'unidade',
        'categoria', 'marca', 'preco_base', 'preco_minimo', 'estoque_atual',
        'estoque_reservado', 'peso', 'imagem_url', 'active', 'atributos', 'last_sync_at',
    ];

    protected $casts = [
        'atributos' => 'array',
        'active' => 'boolean',
        'last_sync_at' => 'datetime',
        'preco_base' => 'decimal:2',
        'preco_minimo' => 'decimal:2',
        'estoque_atual' => 'decimal:3',
        'estoque_reservado' => 'decimal:3',
    ];

    public function tabelasPreco()
    {
        return $this->belongsToMany(TabelaPreco::class, 'tabelas_preco_itens')
            ->withPivot('preco', 'desconto_maximo')
            ->withTimestamps();
    }

    public function tabelasPrecoItens()
    {
        return $this->hasMany(TabelaPrecoItem::class);
    }

    public function promocoes()
    {
        return $this->belongsToMany(Promocao::class, 'promocoes_produtos');
    }

    public function getEstoqueDisponivelAttribute(): float
    {
        return (float) $this->estoque_atual - (float) $this->estoque_reservado;
    }

    public function scopeAtivos($query)
    {
        return $query->where('active', true);
    }
}
