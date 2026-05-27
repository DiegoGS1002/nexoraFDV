<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicaoPagamento extends Model
{
    protected $table = 'condicoes_pagamento';

    protected $fillable = ['erp_code', 'nome', 'descricao', 'prazo_medio', 'acrescimo', 'active'];

    protected $casts = [
        'active' => 'boolean',
        'acrescimo' => 'decimal:2',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'condicao_pagamento_id');
    }
}
