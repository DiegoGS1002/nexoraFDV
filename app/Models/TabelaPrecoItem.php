<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabelaPrecoItem extends Model
{
    protected $table = 'tabelas_preco_itens';

    protected $fillable = ['tabela_preco_id', 'produto_id', 'preco', 'desconto_maximo'];

    protected $casts = [
        'preco' => 'decimal:2',
        'desconto_maximo' => 'decimal:2',
    ];

    public function tabelaPreco()
    {
        return $this->belongsTo(TabelaPreco::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
