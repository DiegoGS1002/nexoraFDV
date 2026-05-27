<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgendaVisita extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'agenda_visitas';

    protected $fillable = [
        'vendedor_id', 'cliente_id', 'prospect_id', 'titulo', 'descricao',
        'data_hora_inicio', 'data_hora_fim', 'tipo', 'status', 'resultado',
        'latitude', 'longitude', 'check_in_at', 'check_out_at',
    ];

    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim' => 'datetime',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
