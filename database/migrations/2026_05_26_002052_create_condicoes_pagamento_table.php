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
        Schema::create('condicoes_pagamento', function (Blueprint $table) {
            $table->id();
            $table->string('erp_code')->nullable()->index();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->integer('prazo_medio')->default(0); // dias
            $table->decimal('acrescimo', 5, 2)->default(0); // %
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condicoes_pagamento');
    }
};
