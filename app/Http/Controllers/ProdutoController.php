<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produtos = Produto::where('status', 'ativo')
            ->orderBy('nome')
            ->get();

        return view('produtos.index', compact('produtos'));
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
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|unique:produtos,codigo',
            'compra' => 'required|numeric|min:0',
            'venda' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:100',
            'venda_fardo' => 'nullable|numeric|min:0',
            'descricao' => 'nullable|string|max:1000'
        ]);

        Produto::create([
            'nome' => $request->nome,
            'codigo' => $request->codigo,
            'preco_compra_atual' => $request->compra,
            'preco_venda_atual' => $request->venda,
            'preco_venda_fardo' => $request->venda_fardo,
            'categoria' => $request->categoria,
            'descricao' => $request->descricao,
            'status' => 'ativo'
        ]);

        return redirect()->route('produtos.index')
            ->with('success', 'Produto cadastrado com sucesso!');
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
    public function update(Request $request, Produto $produto)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'required|string|unique:produtos,codigo,' . $produto->id,
            'compra' => 'required|numeric|min:0',
            'venda' => 'required|numeric|min:0',
            'categoria' => 'required|string|max:100',
            'venda_fardo' => 'nullable|numeric|min:0',
            'descricao' => 'nullable|string|max:1000'
        ]);

        $produto->update([
            'nome' => $request->nome,
            'codigo' => $request->codigo,
            'preco_compra_atual' => $request->compra,
            'preco_venda_atual' => $request->venda,
            'preco_venda_fardo' => $request->venda_fardo,
            'categoria' => $request->categoria,
            'descricao' => $request->descricao
        ]);

        return redirect()->route('produtos.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        // Soft delete - apenas muda status
        $produto->update(['status' => 'inativo']);

        return redirect()->route('produtos.index')
            ->with('success', 'Produto desativado com sucesso!');
    }

    /**
     * Retorna os preços de um produto específico
     */
    public function getPrecos(Produto $produto)
    {
        return response()->json([
            'preco_venda_atual' => $produto->preco_venda_atual,
            'preco_venda_fardo' => $produto->preco_venda_fardo
        ]);
    }
}
