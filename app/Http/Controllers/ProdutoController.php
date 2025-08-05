<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Produto::whereNotNull('categoria')
            ->pluck('categoria')
            ->unique()
            ->sort()
            ->values();

        $produtos = Produto::where('status', 'ativo')
            ->orderBy('nome')
            ->get();

        return view('produtos.index', compact('produtos', 'categorias'));
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
     * Retorna os detalhes de um produto específico
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            // Buscar o produto (sem relacionamento, categoria é string)
            $produto = Produto::findOrFail($id);

            // Preparar os dados para retorno
            $dados = [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'codigo' => $produto->codigo,
                'descricao' => $produto->descricao,
                'preco' => $produto->preco_venda_atual,
                'categoria' => $produto->categoria, // campo direto da tabela
                'status' => $produto->status, // 'ativo' ou 'inativo'
                'ativo' => $produto->status === 'ativo', // conversão para boolean
                'created_at' => $produto->created_at,
                'updated_at' => $produto->updated_at,
            ];

            return response()->json($dados);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Produto não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Buscar produtos com paginação
     */
    public function filtrar(Request $request)
    {
        $query = Produto::query();

        if ($request->filled('termo')) {
            $query->where('nome', 'like', '%' . $request->termo . '%');
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $produtos = $query->get();

        // Renderiza o HTML das linhas da tabela e retorna como resposta
        $html = view('produtos.partials.linhas-produto', compact('produtos'))->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Reativar produto
     */
    public function reativar($id): JsonResponse
    {
        try {
            $produto = Produto::findOrFail($id);
            $produto->update(['status' => 'ativo']);

            return response()->json([
                'success' => true,
                'message' => 'Produto reativado com sucesso'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao reativar produto'
            ], 500);
        }
    }
}
