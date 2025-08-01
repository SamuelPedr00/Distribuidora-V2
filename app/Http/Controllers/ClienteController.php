<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::withCount(['vendas as total_vendas'])
            ->withSum(['vendas as total_compras' => function ($query) {
                $query->where('status', 'concluida');
            }], 'total_venda')
            ->withSum(['vendas as credito_pendente' => function ($query) {
                $query->where('status', 'pendente');
            }], 'total_venda')
            ->orderBy('nome')
            ->get();

        return view('clientes.index', compact('clientes'));
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
            'nome' => 'required|string|max:255|unique:clientes,nome',
            'email' => 'nullable|email|unique:clientes,email',
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:500',
            'cpf_cnpj' => 'nullable|string|max:18|unique:clientes,cpf_cnpj'
        ]);

        Cliente::create($request->all());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        $cliente->load(['vendas' => function ($query) {
            $query->orderByDesc('data_venda');
        }]);

        $estatisticas = [
            'total_vendas' => $cliente->vendas->count(),
            'total_comprado' => $cliente->vendas->where('status', 'concluida')->sum('total_venda'),
            'credito_pendente' => $cliente->vendas->where('status', 'pendente')->sum('total_venda'),
            'ticket_medio' => $cliente->vendas->count() > 0
                ? $cliente->vendas->avg('total_venda')
                : 0
        ];

        return view('clientes.show', compact('cliente', 'estatisticas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:clientes,nome,' . $cliente->id,
            'email' => 'nullable|email|unique:clientes,email,' . $cliente->id,
            'telefone' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:500',
            'cpf_cnpj' => 'nullable|string|max:18|unique:clientes,cpf_cnpj,' . $cliente->id
        ]);

        $cliente->update($request->all());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        // Verificar se o cliente tem vendas pendentes
        $vendasPendentes = $cliente->vendas()->where('status', 'pendente')->count();

        if ($vendasPendentes > 0) {
            return redirect()->route('clientes.index')
                ->with('error', 'Não é possível excluir cliente com vendas pendentes.');
        }

        // Soft delete - marcar como inativo ao invés de deletar
        $cliente->update(['status' => 'inativo']);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente desativado com sucesso!');
    }


    /**
     * Lista clientes ativos para uso em selects
     */
    public function clientesAtivos()
    {
        return Cliente::where('status', 'ativo')
            ->orderBy('nome')
            ->get();
    }

    /**
     * Busca clientes por nome (para autocomplete)
     */
    public function buscar(Request $request)
    {
        $termo = $request->get('q');

        $clientes = Cliente::where('status', 'ativo')
            ->where('nome', 'LIKE', "%{$termo}%")
            ->limit(10)
            ->get(['id', 'nome']);

        return response()->json($clientes);
    }

    /**
     * Relatório de clientes
     */
    public function relatorio()
    {
        $clientes = Cliente::withCount(['vendas as total_vendas'])
            ->withSum(['vendas as total_compras' => function ($query) {
                $query->where('status', 'concluida');
            }], 'total_venda')
            ->withSum(['vendas as credito_pendente' => function ($query) {
                $query->where('status', 'pendente');
            }], 'total_venda')
            ->where('status', 'ativo')
            ->orderByDesc('total_compras')
            ->get();

        return view('clientes.relatorio', compact('clientes'));
    }
}
