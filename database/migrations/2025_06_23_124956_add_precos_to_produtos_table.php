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
        Schema::table('produtos', function (Blueprint $table) {
            // Renomear 'preco' para 'preco_venda_atual'
            $table->renameColumn('preco', 'preco_venda_atual');
            // Adicionar preço de compra atual
            $table->decimal('preco_compra_atual', 10, 2)->after('preco_venda_atual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('preco_compra_atual');
            $table->renameColumn('preco_venda_atual', 'preco');
        });
    }
};
