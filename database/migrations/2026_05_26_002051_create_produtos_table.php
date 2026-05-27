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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('erp_code')->nullable()->index();
            $table->string('sku')->nullable()->index();
            $table->string('nome');
            $table->string('descricao_curta')->nullable();
            $table->text('descricao')->nullable();
            $table->string('unidade')->default('UN');
            $table->string('categoria')->nullable();
            $table->string('marca')->nullable();
            $table->decimal('preco_base', 15, 2)->default(0);
            $table->decimal('preco_minimo', 15, 2)->default(0);
            $table->decimal('estoque_atual', 15, 3)->default(0);
            $table->decimal('estoque_reservado', 15, 3)->default(0);
            $table->decimal('peso', 10, 3)->nullable();
            $table->string('imagem_url')->nullable();
            $table->boolean('active')->default(true);
            $table->json('atributos')->nullable(); // dimensões, cor, etc.
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
        Schema::dropIfExists('produtos');
    }
};
