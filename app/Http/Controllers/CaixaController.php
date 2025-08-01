<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CaixaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Dados para exibição inicial (últimos 30 dias)
        $dataInicio = Carbon::now()->subDays(30)->startOfDay();
        $dataFim = Carbon::now()->endOfDay();

        $dados = $this->buscarDados($dataInicio, $dataFim);

        return view('caixa.index', $dados);
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
            'tipo' => 'required|in:entrada,saida',
            'categoria' => 'required|string|max:100',
            'valor' => 'required|numeric|min:0.01',
            'observacao' => 'required|string|max:500'
        ]);

        Caixa::create([
            'tipo' => $request->tipo,
            'categoria' => $request->categoria,
            'valor' => $request->valor,
            'data' => now(),
            'observacao' => $request->observacao
        ]);

        return redirect()->route('caixa.index')
            ->with('success', 'Lançamento registrado com sucesso!');
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

    public function destroy(Caixa $caixa)
    {
        $caixa->delete();

        return redirect()->route('caixa.index')
            ->with('success', 'Lançamento excluído com sucesso!');
    }

    public function filtrar(Request $request)
    {
        $dataInicio = $request->dataInicio
            ? Carbon::parse($request->dataInicio)->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $dataFim = $request->dataFim
            ? Carbon::parse($request->dataFim)->endOfDay()
            : Carbon::now()->endOfDay();

        $categoria = $request->categoria;

        $dados = $this->buscarDados($dataInicio, $dataFim, $categoria);

        return response()->json($dados);
    }


    /**
     * Busca dados do caixa com filtros
     */
    private function buscarDados($dataInicio, $dataFim, $categoria = null)
    {
        $query = Caixa::whereBetween('data', [$dataInicio, $dataFim]);

        if ($categoria) {
            $query->where('categoria', $categoria);
        }

        $movimentacoes = $query->orderByDesc('data')->get();

        // Cálculos
        $entradas = $movimentacoes->where('tipo', 'entrada')->sum('valor');
        $saidas = $movimentacoes->where('tipo', 'saida')->sum('valor');
        $saldo = $entradas - $saidas;

        // Resumo por categoria
        $resumo = $movimentacoes->groupBy('categoria')->map(function ($items, $categoria) {
            $entradas = $items->where('tipo', 'entrada')->sum('valor');
            $saidas = $items->where('tipo', 'saida')->sum('valor');
            return $entradas - $saidas;
        });

        // Formatar dados para exibição
        $dados = $movimentacoes->map(function ($item) {
            return [
                'id' => $item->id,
                'data' => $item->data->format('d/m/Y H:i'),
                'tipo' => ucfirst($item->tipo),
                'categoria' => $this->formatarCategoria($item->categoria),
                'descricao' => $item->observacao,
                'valor' => $item->valor
            ];
        });

        // Saldo total atual (todos os lançamentos até hoje)
        $saldoTotal = Caixa::selectRaw("
            SUM(
                CASE 
                    WHEN tipo = 'entrada' THEN valor
                    WHEN tipo = 'saida' THEN -valor
                    ELSE 0
                END
            ) as total
        ")->value('total') ?? 0;

        return [
            'dados' => $dados,
            'entradas' => $entradas,
            'saidas' => $saidas,
            'saldo' => $saldo,
            'saldoTotal' => $saldoTotal,
            'resumo' => $resumo,
            'movimentacoes' => $movimentacoes
        ];
    }

    /**
     * Formatar nome das categorias para exibição
     */
    private function formatarCategoria($categoria)
    {
        $categorias = [
            'venda' => '💰 Venda de Produtos',
            'compra' => '📦 Compra de Produtos',
            'despesa_operacional' => '🏢 Despesa Operacional',
            'despesa_administrativa' => '📋 Despesa Administrativa',
            'prolabore' => '👤 Pró-labore',
            'investimento' => '📈 Investimento',
            'emprestimo' => '🏦 Empréstimo',
            'impostos' => '📋 Impostos',
            'outros' => '📝 Outros'
        ];

        return $categorias[$categoria] ?? ucfirst($categoria);
    }

    /**
     * Relatório mensal do caixa
     */
    public function relatorioMensal(Request $request)
    {
        $ano = $request->get('ano', date('Y'));
        $mes = $request->get('mes', date('m'));

        $dataInicio = Carbon::createFromDate($ano, $mes, 1)->startOfMonth();
        $dataFim = Carbon::createFromDate($ano, $mes, 1)->endOfMonth();

        $dados = $this->buscarDados($dataInicio, $dataFim);

        // Dados por dia
        $dadosDiarios = Caixa::whereBetween('data', [$dataInicio, $dataFim])
            ->selectRaw('DATE(data) as dia, tipo, SUM(valor) as total')
            ->groupBy('dia', 'tipo')
            ->get()
            ->groupBy('dia')
            ->map(function ($items) {
                $entradas = $items->where('tipo', 'entrada')->sum('total');
                $saidas = $items->where('tipo', 'saida')->sum('total');
                return [
                    'entradas' => $entradas,
                    'saidas' => $saidas,
                    'saldo' => $entradas - $saidas
                ];
            });

        return view('caixa.relatorio-mensal', compact('dados', 'dadosDiarios', 'ano', 'mes'));
    }

    /**
     * Dashboard financeiro
     */
    public function dashboard()
    {
        $hoje = Carbon::today();
        $mesAtual = Carbon::now()->startOfMonth();
        $anoAtual = Carbon::now()->startOfYear();

        // Dados do dia
        $dadosHoje = $this->buscarDados($hoje, $hoje->copy()->endOfDay());

        // Dados do mês
        $dadosMes = $this->buscarDados($mesAtual, Carbon::now()->endOfDay());

        // Dados do ano
        $dadosAno = $this->buscarDados($anoAtual, Carbon::now()->endOfDay());

        return view('caixa.dashboard', compact('dadosHoje', 'dadosMes', 'dadosAno'));
    }
}
