<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospect extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'razao_social', 'nome_fantasia', 'cnpj_cpf', 'tipo', 'email', 'phone',
        'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado',
        'status', 'observacoes', 'vendedor_id', 'cliente_id', 'latitude', 'longitude',
        'sincronizado_erp',
    ];

    protected $casts = [
        'sincronizado_erp' => 'boolean',
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function contatos()
    {
        return $this->morphMany(Contato::class, 'contatavel');
    }

    public function oportunidades()
    {
        return $this->hasMany(Oportunidade::class);
    }

    public function agendaVisitas()
    {
        return $this->hasMany(AgendaVisita::class);
    }
}
