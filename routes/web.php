<?php

use Illuminate\Support\Facades\Route;
// routes/web.php

use App\Http\Controllers\{
    DashboardController,
    ProdutoController,
    EstoqueController,
    MovimentacaoController,
    VendaController,
    CreditoController,
    ClienteController,
    CaixaController
};

// Dashboard Principal
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Produtos
Route::prefix('produtos')->name('produtos.')->group(function () {
    Route::get('/', [ProdutoController::class, 'index'])->name('index');
    Route::post('/', [ProdutoController::class, 'store'])->name('store');
    Route::get('/{produto}/edit', [ProdutoController::class, 'edit'])->name('edit');
    Route::put('/{produto}', [ProdutoController::class, 'update'])->name('update');
    Route::delete('/{produto}', [ProdutoController::class, 'destroy'])->name('destroy');
    Route::get('/{produto}/precos', [ProdutoController::class, 'getPrecos'])->name('precos');
    Route::get('/filtrar', [ProdutoController::class, 'filtrar'])->name('filtrar');

    Route::get('/{id}', [ProdutoController::class, 'show'])->name('produtos.show');
});

// Estoque
Route::prefix('estoque')->name('estoque.')->group(function () {
    Route::get('/', [EstoqueController::class, 'index'])->name('index');
    Route::post('/', [EstoqueController::class, 'store'])->name('store');
    Route::put('/{estoque}', [EstoqueController::class, 'update'])->name('update');
});

// Movimentações
Route::prefix('movimentacoes')->name('movimentacoes.')->group(function () {
    Route::get('/', [MovimentacaoController::class, 'index'])->name('index');
    Route::post('/', [MovimentacaoController::class, 'store'])->name('store');
    Route::post('/{movimentacao}/reverter', [MovimentacaoController::class, 'reverter'])->name('reverter');
});

// Vendas
Route::prefix('vendas')->name('vendas.')->group(function () {
    Route::get('/', [VendaController::class, 'index'])->name('index');
    Route::post('/', [VendaController::class, 'store'])->name('store');
    Route::post('/{venda}/reverter', [VendaController::class, 'reverter'])->name('reverter');
    Route::post('/{venda}/receber', [VendaController::class, 'receberVenda'])->name('receber');
});

// Crédito
Route::prefix('credito')->name('credito.')->group(function () {
    Route::get('/', [CreditoController::class, 'index'])->name('index');
    Route::post('/', [CreditoController::class, 'store'])->name('store');
    Route::get('/cliente/{cliente}/vendas-pendentes', [CreditoController::class, 'vendasPendentes'])->name('vendas_pendentes');
});

// Clientes
Route::prefix('clientes')->name('clientes.')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index');
    Route::post('/', [ClienteController::class, 'store'])->name('store');
    Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update');
    Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->name('destroy');
});

// Caixa
Route::prefix('caixa')->name('caixa.')->group(function () {
    Route::get('/', [CaixaController::class, 'index'])->name('index');
    Route::post('/', [CaixaController::class, 'store'])->name('store');
    Route::get('/filtro', [CaixaController::class, 'filtrar'])->name('filtrar');
    Route::delete('/{caixa}', [CaixaController::class, 'destroy'])->name('destroy');
});

// APIs
Route::prefix('api')->name('api.')->group(function () {
    Route::get('cliente/{cliente}/vendas-pendentes', [CreditoController::class, 'vendasPendentesApi']);
    Route::get('produto/{produto}/precos', [ProdutoController::class, 'getPrecos']);
});
