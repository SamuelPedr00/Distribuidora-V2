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
        Schema::table('caixa', function (Blueprint $table) {
            $table->unsignedBigInteger('movimentacao_id')->nullable()->after('id');

            // Se quiser garantir integridade referencial:
            $table->foreign('movimentacao_id')->references('id')->on('movimentacoes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('caixa', function (Blueprint $table) {
            $table->dropForeign(['movimentacao_id']);
            $table->dropColumn('movimentacao_id');
        });
    }
};
