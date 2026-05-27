<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $fillable = [
        'pedido_id', 'produto_id', 'produto_erp_code', 'produto_nome', 'unidade',
        'quantidade', 'preco_unitario', 'desconto_percentual', 'desconto_valor',
        'acrescimo_percentual', 'total', 'observacoes',
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'preco_unitario' => 'decimal:2',
        'desconto_percentual' => 'decimal:2',
        'desconto_valor' => 'decimal:2',
        'acrescimo_percentual' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (PedidoItem $item) {
            $subtotal = $item->quantidade * $item->preco_unitario;
            $desconto = $item->desconto_valor > 0 ? $item->desconto_valor : ($subtotal * $item->desconto_percentual / 100);
            $acrescimo = $subtotal * $item->acrescimo_percentual / 100;
            $item->total = $subtotal - $desconto + $acrescimo;
        });
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
