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
            $table->unsignedBigInteger('venda_id')->nullable()->after('id');
            $table->foreign('venda_id')->references('id')->on('vendas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caixa', function (Blueprint $table) {
            $table->dropForeign(['venda_id']);
            $table->dropColumn('venda_id');
        });
    }
};
