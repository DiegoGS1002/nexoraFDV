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
        Schema::create('roteiros_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roteiro_id')->constrained('roteiros')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->integer('ordem')->default(0);
            $table->text('observacoes')->nullable();
            $table->unique(['roteiro_id', 'cliente_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roteiros_clientes');
    }
};
