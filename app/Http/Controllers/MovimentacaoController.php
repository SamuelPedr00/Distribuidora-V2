<?php

namespace App\Http\Controllers;

use App\Models\{Produto, Movimentacao, Caixa};
use App\Http\Controllers\EstoqueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimentacaoController extends Controller
{

    protected $estoqueController;

    public function __construct(EstoqueController $estoqueController)
    {
        $this->estoqueController = $estoqueController;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movimentacoes = Movimentacao::with('produto')
            ->orderByDesc('data')
            ->paginate(20);

        $produtos = Produto::where('status', 'ativo')
            ->orderBy('nome')
            ->get();

        return view('movimentacoes.index', compact('movimentacoes', 'produtos'));
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
            'tipo' => 'required|in:entrada,saida',
            'quantidade' => 'required|integer|min:1',
            'preco_unitario' => 'required|numeric|min:0',
            'observacao' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $produto = Produto::findOrFail($request->produto_id);

            // Verificar estoque para saídas
            if ($request->tipo === 'saida') {
                if (!$this->estoqueController->verificarEstoque($request->produto_id, $request->quantidade)) {
                    throw new \Exception('Estoque insuficiente para realizar esta saída.');
                }
            }

            // Criar movimentação
            $movimentacao = Movimentacao::create([
                'produto_id' => $request->produto_id,
                'tipo' => $request->tipo,
                'quantidade' => $request->quantidade,
                'preco_unitario' => $request->preco_unitario,
                'total' => $request->quantidade * $request->preco_unitario,
                'data' => now(),
                'observacao' => $request->observacao
            ]);

            // Atualizar estoque
            if ($request->tipo === 'entrada') {
                $this->estoqueController->aumentarEstoque($request->produto_id, $request->quantidade);

                // Lançar no caixa como saída (compra)
                Caixa::create([
                    'tipo' => 'saida',
                    'categoria' => 'compra',
                    'valor' => $movimentacao->total,
                    'data' => now(),
                    'observacao' => "Entrada de estoque - {$produto->nome} ({$request->quantidade} unidades)"
                ]);
            } else {
                $this->estoqueController->reduzirEstoque($request->produto_id, $request->quantidade);

                // Para saídas que não são vendas, pode ser ajuste, perda, etc.
                if ($request->observacao && !str_contains(strtolower($request->observacao), 'venda')) {
                    Caixa::create([
                        'tipo' => 'saida',
                        'categoria' => 'outros',
                        'valor' => $movimentacao->total,
                        'data' => now(),
                        'observacao' => "Saída de estoque - {$produto->nome}: {$request->observacao}"
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('movimentacoes.index')
                ->with('success', 'Movimentação registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function reverter(Movimentacao $movimentacao)
    {
        try {
            DB::beginTransaction();

            // Verificar se pode reverter
            if ($movimentacao->revertida) {
                throw new \Exception('Esta movimentação já foi revertida.');
            }

            // Reverter no estoque
            if ($movimentacao->tipo === 'entrada') {
                // Era entrada, agora vai ser saída
                if (!$this->estoqueController->verificarEstoque($movimentacao->produto_id, $movimentacao->quantidade)) {
                    throw new \Exception('Não há estoque suficiente para reverter esta entrada.');
                }
                $this->estoqueController->reduzirEstoque($movimentacao->produto_id, $movimentacao->quantidade);
            } else {
                // Era saída, agora vai ser entrada
                $this->estoqueController->aumentarEstoque($movimentacao->produto_id, $movimentacao->quantidade);
            }

            // Criar movimentação reversa
            Movimentacao::create([
                'produto_id' => $movimentacao->produto_id,
                'tipo' => $movimentacao->tipo === 'entrada' ? 'saida' : 'entrada',
                'quantidade' => $movimentacao->quantidade,
                'preco_unitario' => $movimentacao->preco_unitario,
                'total' => $movimentacao->total,
                'data' => now(),
                'observacao' => "Reversão da movimentação #{$movimentacao->id} - {$movimentacao->observacao}",
                'movimentacao_origem_id' => $movimentacao->id
            ]);

            // Marcar como revertida
            $movimentacao->update(['revertida' => true]);

            // Reverter no caixa se necessário
            $produto = $movimentacao->produto;
            if ($movimentacao->tipo === 'entrada') {
                // Reverter saída do caixa (devolver dinheiro)
                Caixa::create([
                    'tipo' => 'entrada',
                    'categoria' => 'outros',
                    'valor' => $movimentacao->total,
                    'data' => now(),
                    'observacao' => "Reversão de entrada de estoque - {$produto->nome}"
                ]);
            }

            DB::commit();

            return redirect()->route('movimentacoes.index')
                ->with('success', 'Movimentação revertida com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Relatório de movimentações por período
     */
    public function relatorio(Request $request)
    {
        $dataInicio = $request->get('data_inicio', now()->startOfMonth());
        $dataFim = $request->get('data_fim', now()->endOfMonth());
        $tipo = $request->get('tipo');
        $produtoId = $request->get('produto_id');

        $query = Movimentacao::with('produto')
            ->whereBetween('data', [$dataInicio, $dataFim]);

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        if ($produtoId) {
            $query->where('produto_id', $produtoId);
        }

        $movimentacoes = $query->orderByDesc('data')->get();

        // Estatísticas
        $estatisticas = [
            'total_entradas' => $movimentacoes->where('tipo', 'entrada')->sum('total'),
            'total_saidas' => $movimentacoes->where('tipo', 'saida')->sum('total'),
            'quantidade_entradas' => $movimentacoes->where('tipo', 'entrada')->sum('quantidade'),
            'quantidade_saidas' => $movimentacoes->where('tipo', 'saida')->sum('quantidade'),
        ];

        $produtos = Produto::where('status', 'ativo')->orderBy('nome')->get();

        return view('movimentacoes.relatorio', compact(
            'movimentacoes',
            'estatisticas',
            'produtos',
            'dataInicio',
            'dataFim',
            'tipo',
            'produtoId'
        ));
    }

    /**
     * Análise de rotatividade de estoque
     */
    public function analiseRotatividade()
    {
        $produtos = Produto::with(['movimentacoes' => function ($query) {
            $query->where('data', '>=', now()->subDays(30));
        }])->where('status', 'ativo')->get();

        $analise = $produtos->map(function ($produto) {
            $entradas = $produto->movimentacoes->where('tipo', 'entrada')->sum('quantidade');
            $saidas = $produto->movimentacoes->where('tipo', 'saida')->sum('quantidade');
            $estoqueAtual = optional($produto->estoque)->quantidade ?? 0;

            return [
                'produto' => $produto,
                'entradas' => $entradas,
                'saidas' => $saidas,
                'estoque_atual' => $estoqueAtual,
                'rotatividade' => $estoqueAtual > 0 ? $saidas / $estoqueAtual : 0,
                'dias_estoque' => $saidas > 0 ? ($estoqueAtual / $saidas) * 30 : 0
            ];
        })->sortByDesc('rotatividade');

        return view('movimentacoes.analise-rotatividade', compact('analise'));
    }
}
