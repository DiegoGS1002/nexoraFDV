<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $fillable = ['vendedor_id', 'tipo', 'descricao', 'mes', 'ano', 'valor_meta', 'valor_realizado'];

    protected $casts = [
        'valor_meta' => 'decimal:2',
        'valor_realizado' => 'decimal:2',
    ];

    protected $appends = ['percentual_atingido'];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function getPercentualAtingidoAttribute(): float
    {
        if ((float) $this->valor_meta == 0) return 0;
        return round((float) $this->valor_realizado / (float) $this->valor_meta * 100, 2);
    }
}
