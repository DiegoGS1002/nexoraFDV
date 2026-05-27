<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'erp_code', 'razao_social', 'nome_fantasia', 'cnpj_cpf', 'ie', 'tipo',
        'email', 'phone', 'cep', 'logradouro', 'numero', 'complemento',
        'bairro', 'cidade', 'estado', 'pais', 'limite_credito', 'saldo_disponivel',
        'status', 'motivo_bloqueio', 'tabela_preco_id', 'vendedor_id',
        'latitude', 'longitude', 'dados_extras', 'last_sync_at',
    ];

    protected $casts = [
        'limite_credito' => 'decimal:2',
        'saldo_disponivel' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'dados_extras' => 'array',
        'last_sync_at' => 'datetime',
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function tabelaPreco()
    {
        return $this->belongsTo(TabelaPreco::class, 'tabela_preco_id');
    }

    public function contatos()
    {
        return $this->hasMany(Contato::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function oportunidades()
    {
        return $this->hasMany(Oportunidade::class);
    }

    public function agendaVisitas()
    {
        return $this->hasMany(AgendaVisita::class);
    }

    public function titulosFinanceiros()
    {
        return $this->hasMany(TituloFinanceiro::class);
    }

    public function isBloqueado(): bool
    {
        return $this->status === 'bloqueado';
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeDoVendedor($query, int $vendedorId)
    {
        return $query->where('vendedor_id', $vendedorId);
    }
}
