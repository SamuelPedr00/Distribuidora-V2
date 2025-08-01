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
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_venda')->unique(); // Número sequencial da venda
            $table->decimal('total_venda', 10, 2);
            $table->decimal('total_custo', 10, 2); // Custo total dos produtos vendidos
            $table->decimal('lucro_total', 10, 2); // Lucro da venda
            $table->enum('status', ['pendente', 'concluida', 'cancelada'])->default('pendente');
            $table->string('cliente_nome')->nullable();
            $table->string('observacoes')->nullable();
            $table->timestamp('data_venda');
            $table->timestamps();

            $table->index('data_venda');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};
