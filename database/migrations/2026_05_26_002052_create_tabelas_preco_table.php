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
        Schema::create('tabelas_preco', function (Blueprint $table) {
            $table->id();
            $table->string('erp_code')->nullable()->index();
            $table->string('nome');
            $table->boolean('padrao')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamp('vigencia_inicio')->nullable();
            $table->timestamp('vigencia_fim')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabelas_preco');
    }
};
