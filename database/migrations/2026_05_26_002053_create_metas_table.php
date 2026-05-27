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
        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendedor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipo', ['valor', 'quantidade', 'clientes_novos', 'visitas'])->default('valor');
            $table->string('descricao')->nullable();
            $table->integer('mes'); // 1-12
            $table->integer('ano');
            $table->decimal('valor_meta', 15, 2);
            $table->decimal('valor_realizado', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['vendedor_id', 'tipo', 'mes', 'ano']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
