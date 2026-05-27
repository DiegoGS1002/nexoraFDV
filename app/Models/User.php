<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 'perfil', 'ativo',
        'supervisor_id', 'phone', 'cpf',
    ];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ativo' => 'boolean',
        ];
    }

    // Perfis: vendedor, supervisor, gerente, backoffice, financeiro, admin

    public function isAdmin(): bool
    {
        return $this->perfil === 'admin';
    }

    public function isGerente(): bool
    {
        return in_array($this->perfil, ['gerente', 'admin']);
    }

    public function isSupervisor(): bool
    {
        return in_array($this->perfil, ['supervisor', 'gerente', 'admin']);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function subordinados()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'vendedor_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'vendedor_id');
    }

    public function agendaVisitas()
    {
        return $this->hasMany(AgendaVisita::class, 'vendedor_id');
    }

    public function oportunidades()
    {
        return $this->hasMany(Oportunidade::class, 'vendedor_id');
    }

    public function metas()
    {
        return $this->hasMany(Meta::class, 'vendedor_id');
    }

    public function comissoes()
    {
        return $this->hasMany(Comissao::class, 'vendedor_id');
    }
}
