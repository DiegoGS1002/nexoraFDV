<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Pedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero', 'erp_code', 'cliente_id', 'vendedor_id', 'condicao_pagamento_id',
        'tabela_preco_id', 'data_pedido', 'data_entrega_prevista', 'status', 'status_erp',
        'subtotal', 'desconto_total', 'acrescimo_total', 'total',
        'observacoes', 'obs_interna', 'enviado_erp_at', 'sincronizado_at', 'erp_response',
    ];

    protected $casts = [
        'data_pedido' => 'date',
        'data_entrega_prevista' => 'date',
        'subtotal' => 'decimal:2',
        'desconto_total' => 'decimal:2',
        'acrescimo_total' => 'decimal:2',
        'total' => 'decimal:2',
        'erp_response' => 'array',
        'enviado_erp_at' => 'datetime',
        'sincronizado_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Pedido $pedido) {
            if (empty($pedido->numero)) {
                $pedido->numero = 'PED-' . strtoupper(Str::random(8));
            }
        });
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function condicaoPagamento()
    {
        return $this->belongsTo(CondicaoPagamento::class, 'condicao_pagamento_id');
    }

    public function tabelaPreco()
    {
        return $this->belongsTo(TabelaPreco::class, 'tabela_preco_id');
    }

    public function itens()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function comissoes()
    {
        return $this->hasMany(Comissao::class);
    }

    public function oportunidade()
    {
        return $this->hasOne(Oportunidade::class);
    }

    public function recalcularTotais(): void
    {
        $subtotal = $this->itens->sum('total');
        $this->update(['subtotal' => $subtotal, 'total' => $subtotal + $this->acrescimo_total - $this->desconto_total]);
    }

    public function scopeDoVendedor($query, int $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }
}
