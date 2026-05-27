<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Oportunidade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'titulo', 'cliente_id', 'prospect_id', 'vendedor_id', 'valor_estimado',
        'estagio', 'probabilidade', 'data_previsao_fechamento', 'motivo_perda',
        'observacoes', 'pedido_id',
    ];

    protected $casts = [
        'data_previsao_fechamento' => 'date',
        'valor_estimado' => 'decimal:2',
        'probabilidade' => 'integer',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
