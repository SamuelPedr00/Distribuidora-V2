<?php

namespace App\Http\Controllers;

use App\Models\{Produto, Venda, ItemVenda, Caixa, Cliente};
use App\Http\Controllers\EstoqueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendaController extends Controller
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
        $vendas = Venda::with(['cliente', 'itens.produto'])
            ->orderByDesc('data_venda')
            ->paginate(20);

        $produtos = Produto::where('status', 'ativo')
            ->orderBy('nome')
            ->get();

        $clientes = Cliente::orderBy('nome')->get();

        return view('vendas.index', compact('vendas', 'produtos', 'clientes'));
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
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.preco' => 'required|numeric|min:0',
            'tipo_venda' => 'in:vista,credito',
            'cliente_id' => 'required_if:tipo_venda,credito|exists:clientes,id',
            'observacoes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Verificar estoque disponível
            foreach ($request->itens as $item) {
                if (!$this->estoqueController->verificarEstoque($item['produto_id'], $item['quantidade'])) {
                    $produto = Produto::find($item['produto_id']);
                    throw new \Exception("Estoque insuficiente para o produto: {$produto->nome}");
                }
            }

            // Calcular totais
            $totalCusto = 0;
            $totalVenda = 0;

            foreach ($request->itens as $item) {
                $produto = Produto::find($item['produto_id']);
                $totalCusto += $produto->preco_compra_atual * $item['quantidade'];
                $totalVenda += $item['preco'] * $item['quantidade'];
            }

            // Criar venda
            $venda = Venda::create([
                'numero_venda' => $this->gerarNumeroVenda(),
                'cliente_id' => $request->cliente_id,
                'data_venda' => now(),
                'total_custo' => $totalCusto,
                'total_venda' => $totalVenda,
                'status' => $request->tipo_venda === 'credito' ? 'pendente' : 'concluida',
                'tipo_pagamento' => $request->tipo_venda === 'credito' ? 'credito' : 'vista',
                'observacoes' => $request->observacoes
            ]);

            // Criar itens da venda
            foreach ($request->itens as $item) {
                $produto = Produto::find($item['produto_id']);

                ItemVenda::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'preco_custo_unitario' => $produto->preco_compra_atual,
                    'preco_venda_unitario' => $item['preco']
                ]);

                // Reduzir estoque
                $this->estoqueController->reduzirEstoque($item['produto_id'], $item['quantidade']);
            }

            // Se for venda à vista, lançar no caixa
            if ($request->tipo_venda !== 'credito') {
                Caixa::create([
                    'tipo' => 'entrada',
                    'categoria' => 'venda',
                    'valor' => $totalVenda,
                    'data' => now(),
                    'observacao' => "Venda #{$venda->numero_venda}" . ($request->observacoes ? " - {$request->observacoes}" : '')
                ]);
            }

            DB::commit();

            $mensagem = $request->tipo_venda === 'credito'
                ? 'Crédito registrado com sucesso!'
                : 'Venda registrada com sucesso!';

            return redirect()->route('vendas.index')->with('success', $mensagem);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
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

    public function reverter(Request $request, Venda $venda)
    {
        try {
            DB::beginTransaction();

            // Verificar se a venda pode ser revertida
            if ($venda->status === 'cancelada') {
                throw new \Exception('Esta venda já foi cancelada.');
            }

            // Reverter estoque
            foreach ($venda->itens as $item) {
                $this->estoqueController->aumentarEstoque($item->produto_id, $item->quantidade);
            }

            // Se foi paga, reverter lançamento no caixa
            if ($venda->status === 'concluida') {
                Caixa::create([
                    'tipo' => 'saida',
                    'categoria' => 'venda',
                    'valor' => $venda->total_venda,
                    'data' => now(),
                    'observacao' => "Reversão da venda #{$venda->numero_venda}"
                ]);
            }

            // Atualizar status da venda
            $venda->update(['status' => 'cancelada']);

            DB::commit();

            return response()->json(['mensagem' => 'Venda revertida com sucesso!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['erro' => $e->getMessage()], 400);
        }
    }

    public function receberVenda(Request $request)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id'
        ]);

        try {
            $venda = Venda::findOrFail($request->venda_id);

            if ($venda->status !== 'pendente') {
                throw new \Exception('Esta venda não está pendente.');
            }

            DB::beginTransaction();

            // Atualizar status da venda
            $venda->update(['status' => 'concluida']);

            // Lançar no caixa
            Caixa::create([
                'tipo' => 'entrada',
                'categoria' => 'venda',
                'valor' => $venda->total_venda,
                'data' => now(),
                'observacao' => "Recebimento da venda #{$venda->numero_venda}"
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Pagamento recebido com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function gerarNumeroVenda()
    {
        $ultimaVenda = Venda::orderByDesc('id')->first();
        $proximoNumero = $ultimaVenda ? $ultimaVenda->id + 1 : 1;

        return str_pad($proximoNumero, 6, '0', STR_PAD_LEFT);
    }
}
