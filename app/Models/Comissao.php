<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comissao extends Model
{
    protected $fillable = [
        'vendedor_id', 'pedido_id', 'valor_base', 'percentual', 'valor_comissao',
        'status', 'mes', 'ano', 'pago_em', 'observacoes',
    ];

    protected $casts = [
        'valor_base' => 'decimal:2',
        'percentual' => 'decimal:2',
        'valor_comissao' => 'decimal:2',
        'pago_em' => 'datetime',
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
