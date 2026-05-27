<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promocao extends Model
{
    protected $fillable = [
        'erp_code', 'nome', 'descricao', 'tipo', 'valor', 'vigencia_inicio',
        'vigencia_fim', 'active', 'qtd_minima',
    ];

    protected $casts = [
        'vigencia_inicio' => 'datetime',
        'vigencia_fim' => 'datetime',
        'active' => 'boolean',
        'valor' => 'decimal:2',
    ];

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'promocoes_produtos');
    }

    public function scopeAtivas($query)
    {
        return $query->where('active', true)
            ->where('vigencia_inicio', '<=', now())
            ->where('vigencia_fim', '>=', now());
    }
}
