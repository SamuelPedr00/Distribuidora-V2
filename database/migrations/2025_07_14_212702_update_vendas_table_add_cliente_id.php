<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            // Remove a coluna antiga
            $table->dropColumn('cliente_nome');

            // Adiciona a nova coluna com foreign key
            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->nullOnDelete(); // Define o cliente_id como NULL se o cliente for deletado
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            // Reverte as mudanças
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');

            $table->string('cliente_nome')->nullable();
        });
    }
};
