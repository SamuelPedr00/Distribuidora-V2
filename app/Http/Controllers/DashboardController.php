<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Produto, Caixa};

class DashboardController extends Controller
{
    public function index()
    {
        // Produtos ativos com estoque
        $produtos = Produto::with('estoque')
            ->where('status', 'ativo')
            ->get();

        $totalProdutos = $produtos->count();

        // Produtos com estoque baixo (≤ 10) ou sem estoque
        $produtosBaixoEstoque = Produto::with('estoque')
            ->where('status', 'ativo')
            ->where(function ($query) {
                $query->whereHas('estoque', function ($q) {
                    $q->where('quantidade', '<=', 10);
                })->orWhereDoesntHave('estoque');
            })
            ->get();

        $quantidadeProdutosBaixo = $produtosBaixoEstoque->count();

        // Produtos com estoque cadastrado
        $produtosComEstoque = Produto::where('status', 'ativo')
            ->whereHas('estoque')
            ->count();

        // Saldo atual do caixa
        $valorCaixa = Caixa::selectRaw("
            SUM(
                CASE 
                    WHEN tipo = 'entrada' THEN valor
                    WHEN tipo = 'saida' THEN -valor
                    ELSE 0
                END
            ) as total
        ")->value('total') ?? 0;

        return view('dashboard.index', compact(
            'totalProdutos',
            'produtosBaixoEstoque',
            'quantidadeProdutosBaixo',
            'produtosComEstoque',
            'valorCaixa'
        ));
    }
}
