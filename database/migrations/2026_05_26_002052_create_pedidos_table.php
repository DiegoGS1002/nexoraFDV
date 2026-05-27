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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->nullable()->unique(); // numero gerado pelo FDV
            $table->string('erp_code')->nullable()->index(); // numero no ERP após upload
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('vendedor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('condicao_pagamento_id')->nullable()->constrained('condicoes_pagamento')->nullOnDelete();
            $table->foreignId('tabela_preco_id')->nullable()->constrained('tabelas_preco')->nullOnDelete();
            $table->date('data_pedido');
            $table->date('data_entrega_prevista')->nullable();
            $table->enum('status', [
                'rascunho', 'enviado', 'aguardando_analise', 'aprovado', 'faturado', 'cancelado'
            ])->default('rascunho');
            $table->string('status_erp')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('desconto_total', 15, 2)->default(0);
            $table->decimal('acrescimo_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->text('obs_interna')->nullable();
            $table->timestamp('enviado_erp_at')->nullable();
            $table->timestamp('sincronizado_at')->nullable();
            $table->json('erp_response')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
