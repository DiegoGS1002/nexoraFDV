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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('erp_code')->nullable()->index();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj_cpf')->nullable()->index();
            $table->string('ie')->nullable(); // inscrição estadual
            $table->enum('tipo', ['juridica', 'fisica'])->default('juridica');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('cep')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('pais')->default('Brasil');
            $table->decimal('limite_credito', 15, 2)->default(0);
            $table->decimal('saldo_disponivel', 15, 2)->default(0);
            $table->enum('status', ['ativo', 'bloqueado', 'inativo'])->default('ativo');
            $table->string('motivo_bloqueio')->nullable();
            $table->foreignId('tabela_preco_id')->nullable()->constrained('tabelas_preco')->nullOnDelete();
            $table->foreignId('vendedor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('dados_extras')->nullable(); // campos adicionais ERP
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
