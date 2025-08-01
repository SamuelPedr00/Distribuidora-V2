<?php

namespace App\Http\Controllers;

use App\Models\{Cliente, Venda, Produto};
use App\Http\Controllers\VendaController;
use Illuminate\Http\Request;

class CreditoController extends Controller
{
    protected $vendaController;

    public function __construct(VendaController $vendaController)
    {
        $this->vendaController = $vendaController;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produtos = Produto::where('status', 'ativo')
            ->orderBy('nome')
            ->get();

        $clientes = Cliente::orderBy('nome')->get();

        // Clientes com crédito pendente
        $clientesComCredito = $clientes->map(function ($cliente) {
            $totalCredito = $cliente->vendas()
                ->where('status', 'pendente')
                ->sum('total_venda');

            return [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
                'credito' => $totalCredito,
            ];
        })->filter(function ($c) {
            return $c['credito'] > 0;
        })->values();

        return view('credito.index', compact('produtos', 'clientes', 'clientesComCredito'));
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
        // Adiciona o tipo de venda como crédito
        $request->merge(['tipo_venda' => 'credito']);

        // Redireciona para o controller de vendas
        return app(VendaController::class)->store($request);
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

    public function vendasPendentes(Cliente $cliente)
    {
        $vendas = $cliente->vendas()
            ->with(['itens.produto'])
            ->where('status', 'pendente')
            ->orderByDesc('data_venda')
            ->get();

        return view('credito.vendas-pendentes', compact('vendas', 'cliente'));
    }

    public function vendasPendentesApi(Cliente $cliente)
    {
        $vendas = $cliente->vendas()
            ->with(['itens.produto'])
            ->where('status', 'pendente')
            ->orderByDesc('data_venda')
            ->get();

        return response()->json($vendas);
    }

    /**
     * Relatório de créditos por cliente
     */
    public function relatorio()
    {
        $clientes = Cliente::with(['vendas' => function ($query) {
            $query->where('status', 'pendente');
        }])->get();

        $relatorio = $clientes->map(function ($cliente) {
            $vendasPendentes = $cliente->vendas;
            $totalCredito = $vendasPendentes->sum('total_venda');

            return [
                'cliente' => $cliente,
                'total_credito' => $totalCredito,
                'quantidade_vendas' => $vendasPendentes->count(),
                'vendas' => $vendasPendentes
            ];
        })->filter(function ($item) {
            return $item['total_credito'] > 0;
        });

        return view('credito.relatorio', compact('relatorio'));
    }

    /**
     * Histórico de créditos de um cliente específico
     */
    public function historico(Cliente $cliente)
    {
        $vendas = $cliente->vendas()
            ->with(['itens.produto'])
            ->where('tipo_pagamento', 'credito')
            ->orderByDesc('data_venda')
            ->paginate(10);

        $totalPendente = $cliente->vendas()
            ->where('status', 'pendente')
            ->sum('total_venda');

        $totalRecebido = $cliente->vendas()
            ->where('status', 'concluida')
            ->where('tipo_pagamento', 'credito')
            ->sum('total_venda');

        return view('credito.historico', compact('cliente', 'vendas', 'totalPendente', 'totalRecebido'));
    }
}
