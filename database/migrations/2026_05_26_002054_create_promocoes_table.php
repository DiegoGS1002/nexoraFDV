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
        Schema::create('promocoes', function (Blueprint $table) {
            $table->id();
            $table->string('erp_code')->nullable()->index();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->enum('tipo', ['desconto_percentual', 'desconto_valor', 'brinde', 'combo'])->default('desconto_percentual');
            $table->decimal('valor', 10, 2)->default(0);
            $table->dateTime('vigencia_inicio');
            $table->dateTime('vigencia_fim');
            $table->boolean('active')->default(true);
            $table->integer('qtd_minima')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocoes');
    }
};
