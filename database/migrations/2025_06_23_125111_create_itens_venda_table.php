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
        Schema::create('itens_venda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('restrict');
            $table->integer('quantidade');
            $table->decimal('preco_compra_unitario', 10, 2); // Preço que foi comprado na época
            $table->decimal('preco_venda_unitario', 10, 2);  // Preço que foi vendido
            $table->decimal('custo_total', 10, 2); // quantidade * preco_compra_unitario
            $table->decimal('valor_total', 10, 2); // quantidade * preco_venda_unitario
            $table->decimal('lucro_item', 10, 2);  // valor_total - custo_total
            $table->timestamps();

            $table->index(['venda_id', 'produto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens_venda');
    }
};
