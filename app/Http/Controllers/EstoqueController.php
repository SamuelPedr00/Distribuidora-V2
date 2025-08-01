<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Produto, Estoque};

class EstoqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produtos = Produto::with('estoque')
            ->where('status', 'ativo')
            ->orderBy('nome')
            ->get();

        // Para o formulário de cadastro
        $produtosSemEstoque = Produto::where('status', 'ativo')
            ->whereDoesntHave('estoque')
            ->orderBy('nome')
            ->get();

        return view('estoque.index', compact('produtos', 'produtosSemEstoque'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer|min:0'
        ]);

        // Verifica se já existe estoque para este produto
        $estoqueExistente = Estoque::where('produto_id', $request->produto_id)->first();

        if ($estoqueExistente) {
            return redirect()->route('estoque.index')
                ->with('error', 'Este produto já possui estoque cadastrado. Use a movimentação para alterar a quantidade.');
        }

        Estoque::create([
            'produto_id' => $request->produto_id,
            'quantidade' => $request->quantidade
        ]);

        return redirect()->route('estoque.index')
            ->with('success', 'Estoque cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Estoque $estoque)
    {
        $request->validate([
            'quantidade' => 'required|integer|min:0'
        ]);

        $estoque->update([
            'quantidade' => $request->quantidade
        ]);

        return redirect()->route('estoque.index')
            ->with('success', 'Estoque atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Retorna produtos com estoque baixo para o dashboard
     */
    public function produtosBaixoEstoque()
    {
        return Produto::with('estoque')
            ->where('status', 'ativo')
            ->where(function ($query) {
                $query->whereHas('estoque', function ($q) {
                    $q->where('quantidade', '<=', 10);
                })->orWhereDoesntHave('estoque');
            })
            ->get();
    }

    /**
     * Verifica se um produto tem estoque suficiente
     */
    public function verificarEstoque($produtoId, $quantidade)
    {
        $estoque = Estoque::where('produto_id', $produtoId)->first();

        if (!$estoque) {
            return false;
        }

        return $estoque->quantidade >= $quantidade;
    }

    /**
     * Reduz estoque após venda
     */
    public function reduzirEstoque($produtoId, $quantidade)
    {
        $estoque = Estoque::where('produto_id', $produtoId)->first();

        if ($estoque && $estoque->quantidade >= $quantidade) {
            $estoque->decrement('quantidade', $quantidade);
            return true;
        }

        return false;
    }

    /**
     * Aumenta estoque após entrada ou reversão
     */
    public function aumentarEstoque($produtoId, $quantidade)
    {
        $estoque = Estoque::where('produto_id', $produtoId)->first();

        if ($estoque) {
            $estoque->increment('quantidade', $quantidade);
            return true;
        }

        return false;
    }
}
