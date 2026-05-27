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
        Schema::create('comissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendedor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->decimal('valor_base', 15, 2);
            $table->decimal('percentual', 5, 2);
            $table->decimal('valor_comissao', 15, 2);
            $table->enum('status', ['pendente', 'aprovada', 'paga', 'cancelada'])->default('pendente');
            $table->integer('mes');
            $table->integer('ano');
            $table->timestamp('pago_em')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comissoes');
    }
};
