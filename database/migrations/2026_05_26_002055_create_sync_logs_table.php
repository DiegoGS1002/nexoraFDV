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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('entity'); // clientes, produtos, pedidos, etc.
            $table->enum('direction', ['download', 'upload'])->default('download');
            $table->enum('status', ['iniciado', 'sucesso', 'erro'])->default('iniciado');
            $table->integer('registros_processados')->default(0);
            $table->integer('registros_erro')->default(0);
            $table->text('mensagem')->nullable();
            $table->json('detalhes')->nullable();
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('finalizado_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
