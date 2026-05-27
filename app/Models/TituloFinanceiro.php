<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TituloFinanceiro extends Model
{
    protected $fillable = [
        'erp_code', 'cliente_id', 'numero_titulo', 'parcela',
        'valor', 'valor_pago', 'multa', 'juros',
        'data_emissao', 'data_vencimento', 'data_pagamento',
        'status', 'forma_pagamento', 'observacoes', 'last_sync_at',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'multa' => 'decimal:2',
        'juros' => 'decimal:2',
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'last_sync_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function isVencido(): bool
    {
        return $this->status === 'aberto' && $this->data_vencimento->isPast();
    }

    public function scopeVencidos($query)
    {
        return $query->where('status', 'aberto')->where('data_vencimento', '<', now()->toDateString());
    }

    public function scopeAbertos($query)
    {
        return $query->where('status', 'aberto');
    }
}
