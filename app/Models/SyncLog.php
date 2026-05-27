<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $fillable = [
        'user_id', 'entity', 'direction', 'status',
        'registros_processados', 'registros_erro',
        'mensagem', 'detalhes', 'iniciado_em', 'finalizado_em',
    ];

    protected $casts = [
        'detalhes' => 'array',
        'iniciado_em' => 'datetime',
        'finalizado_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
