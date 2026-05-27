<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('perfil', ['vendedor', 'supervisor', 'gerente', 'backoffice', 'financeiro', 'admin'])
                ->default('vendedor')->after('email');
            $table->boolean('ativo')->default(true)->after('perfil');
            $table->string('phone')->nullable()->after('ativo');
            $table->string('cpf', 14)->nullable()->after('phone');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete()->after('cpf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['perfil', 'ativo', 'phone', 'cpf', 'supervisor_id']);
        });
    }
};
