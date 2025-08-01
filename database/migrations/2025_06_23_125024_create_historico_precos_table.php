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
        Schema::create('historico_precos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->decimal('preco_compra', 10, 2);
            $table->decimal('preco_venda', 10, 2);
            $table->decimal('margem_lucro', 7, 2); // Percentual de lucro
            $table->timestamp('data_vigencia');
            $table->timestamp('data_fim')->nullable(); // Quando o preço deixou de valer
            $table->boolean('ativo')->default(true);
            $table->string('motivo_alteracao')->nullable(); // Motivo da mudança de preço
            $table->timestamps();

            // Índices para performance
            $table->index(['produto_id', 'ativo']);
            $table->index(['data_vigencia', 'data_fim']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico_precos');
    }
};
