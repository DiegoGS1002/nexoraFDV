<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@nexora.com',
            'password' => Hash::make('password'),
            'perfil' => 'admin',
            'ativo' => true,
        ]);

        // Gerente
        $gerente = User::create([
            'name' => 'Gerente Comercial',
            'email' => 'gerente@nexora.com',
            'password' => Hash::make('password'),
            'perfil' => 'gerente',
            'ativo' => true,
        ]);

        // Supervisor
        $supervisor = User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@nexora.com',
            'password' => Hash::make('password'),
            'perfil' => 'supervisor',
            'supervisor_id' => $gerente->id,
            'ativo' => true,
        ]);

        // Vendedores
        User::create([
            'name' => 'Vendedor 1',
            'email' => 'vendedor1@nexora.com',
            'password' => Hash::make('password'),
            'perfil' => 'vendedor',
            'supervisor_id' => $supervisor->id,
            'ativo' => true,
        ]);

        User::create([
            'name' => 'Vendedor 2',
            'email' => 'vendedor2@nexora.com',
            'password' => Hash::make('password'),
            'perfil' => 'vendedor',
            'supervisor_id' => $supervisor->id,
            'ativo' => true,
        ]);

        $this->command->info('✅ Usuários criados com sucesso!');
        $this->command->info('   Admin: admin@nexora.com / password');
        $this->command->info('   Gerente: gerente@nexora.com / password');
        $this->command->info('   Supervisor: supervisor@nexora.com / password');
        $this->command->info('   Vendedor1: vendedor1@nexora.com / password');
        $this->command->info('   Vendedor2: vendedor2@nexora.com / password');
    }
}
