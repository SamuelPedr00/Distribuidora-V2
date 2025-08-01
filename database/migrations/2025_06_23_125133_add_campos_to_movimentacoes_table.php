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
        Schema::table('movimentacoes', function (Blueprint $table) {
            // Renomear 'valor' para 'preco_unitario'
            $table->renameColumn('valor', 'preco_unitario');
            // Adicionar campo para identificar se é preço de compra ou venda
            $table->enum('tipo_preco', ['compra', 'venda'])->after('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropColumn('tipo_preco');
            $table->renameColumn('preco_unitario', 'valor');
        });
    }
};
