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
        Schema::create('tabelas_preco_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tabela_preco_id')->constrained('tabelas_preco')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->decimal('preco', 15, 2);
            $table->decimal('desconto_maximo', 5, 2)->default(0); // % max discount
            $table->timestamps();
            $table->unique(['tabela_preco_id', 'produto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabelas_preco_itens');
    }
};
