@extends('layouts.app')

@section('title', 'Vendas - Sistema Distribuidora')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <i class="fas fa-shopping-cart me-2"></i>Sistema de Vendas
        </h1>
        <button class="btn btn-gradient-success" data-bs-toggle="modal" data-bs-target="#modalNovaVenda">
            <i class="fas fa-plus me-2"></i>Nova Venda
        </button>
    </div>

    <!-- Resumo de Vendas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card success h-100">
                <div class="card-body text-center">
                    <div class="stats-value">{{ $vendas->where('status', 'concluida')->count() }}</div>
                    <div class="text-muted fw-semibold">
                        <i class="fas fa-check-circle me-1"></i>Vendas Concluídas
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card warning h-100">
                <div class="card-body text-center">
                    <div class="stats-value">{{ $vendas->where('status', 'pendente')->count() }}</div>
                    <div class="text-muted fw-semibold">
                        <i class="fas fa-clock me-1"></i>Vendas Pendentes
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card success h-100">
                <div class="card-body text-center">
                    <div class="stats-value">
                        R$ {{ number_format($vendas->where('status', 'concluida')->sum('total_venda'), 2, ',', '.') }}
                    </div>
                    <div class="text-muted fw-semibold">
                        <i class="fas fa-money-bill-wave me-1"></i>Total Recebido
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <div class="stats-value">
                        R$
                        {{ number_format($vendas->where('status', 'concluida')->sum('total_venda') - $vendas->where('status', 'concluida')->sum('total_custo'), 2, ',', '.') }}
                    </div>
                    <div class="text-muted fw-semibold">
                        <i class="fas fa-chart-line me-1"></i>Lucro Total
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Data Início</label>
                    <input type="date" class="form-control" id="dataInicio">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Fim</label>
                    <input type="date" class="form-control" id="dataFim">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="filtroStatus">
                        <option value="">Todos</option>
                        <option value="concluida">Concluída</option>
                        <option value="pendente">Pendente</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cliente</label>
                    <select class="form-select" id="filtroCliente">
                        <option value="">Todos</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-primary w-100" onclick="filtrarVendas()">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Vendas -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Histórico de Vendas
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabelaVendas">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Total Custo</th>
                            <th class="text-end">Total Venda</th>
                            <th class="text-end">Lucro</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vendas as $venda)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $venda->data_venda->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $venda->data_venda->format('H:i') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">#{{ $venda->numero_venda }}</span>
                                </td>
                                <td>
                                    @if ($venda->cliente)
                                        <div class="fw-semibold">{{ $venda->cliente->nome }}</div>
                                        <small class="text-muted">
                                            <i class="fas fa-credit-card me-1"></i>Crédito
                                        </small>
                                    @else
                                        <div class="text-muted">
                                            <i class="fas fa-money-bill me-1"></i>Venda Direta
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($venda->status === 'concluida')
                                        <span class="status-badge status-concluida">
                                            <i class="fas fa-check-circle me-1"></i>Concluída
                                        </span>
                                    @elseif($venda->status === 'pendente')
                                        <span class="status-badge status-pendente">
                                            <i class="fas fa-clock me-1"></i>Pendente
                                        </span>
                                    @else
                                        <span class="status-badge status-cancelada">
                                            <i class="fas fa-times-circle me-1"></i>Cancelada
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    R$ {{ number_format($venda->total_custo, 2, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold">
                                    R$ {{ number_format($venda->total_venda, 2, ',', '.') }}
                                </td>
                                <td
                                    class="text-end {{ $venda->status === 'concluida' ? 'text-success' : 'text-muted' }} fw-semibold">
                                    R$ {{ number_format($venda->total_venda - $venda->total_custo, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick="verDetalhes({{ $venda->id }})"
                                            title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if ($venda->status === 'pendente')
                                            <button class="btn btn-outline-success"
                                                onclick="receberVenda({{ $venda->id }})" title="Receber Pagamento">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                        @endif
                                        @if ($venda->status !== 'cancelada')
                                            <button class="btn btn-outline-danger"
                                                onclick="reverterVenda({{ $venda->id }})" title="Reverter Venda">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nenhuma venda registrada</h5>
                                    <p class="text-muted">Clique em "Nova Venda" para começar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($vendas->hasPages())
            <div class="card-footer">
                {{ $vendas->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Nova Venda -->
    <div class="modal fade" id="modalNovaVenda" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Nova Venda
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formVenda" action="{{ route('vendas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <!-- Tipo de Venda -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Venda *</label>
                                <select class="form-select" name="tipo_venda" id="tipoVenda" required>
                                    <option value="vista">💵 À Vista</option>
                                    <option value="credito">💳 Crédito</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="clienteGroup" style="display: none;">
                                <label class="form-label">Cliente *</label>
                                <select class="form-select" name="cliente_id" id="clienteSelect">
                                    <option value="">Selecione um cliente</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Itens da Venda -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Itens da Venda</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addItem">
                                    <i class="fas fa-plus me-1"></i>Adicionar Item
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="itensVenda">
                                    <!-- Primeiro item -->
                                    <div class="item-venda border rounded p-3 mb-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">Produto *</label>
                                                <select name="itens[0][produto_id]" class="form-select produto-select"
                                                    required>
                                                    <option value="">Selecione um produto</option>
                                                    @foreach ($produtos as $produto)
                                                        <option value="{{ $produto->id }}">{{ $produto->nome }}
                                                            ({{ $produto->codigo }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Quantidade *</label>
                                                <input type="number" name="itens[0][quantidade]" class="form-control"
                                                    min="1" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Preço *</label>
                                                <select name="itens[0][preco]" class="form-select select-preco" required>
                                                    <option value="">Selecione um produto primeiro</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Subtotal</label>
                                                <input type="text" class="form-control subtotal" readonly>
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger w-100 remove-item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total -->
                                <div class="row">
                                    <div class="col-md-8"></div>
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <strong>Total da Venda:</strong>
                                                    <strong id="totalVenda">R$ 0,00</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observações -->
                        <div class="mb-3">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacoes" rows="2" placeholder="Observações sobre a venda"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="button" class="btn btn-gradient-success" id="confirmarVenda">
                            <i class="fas fa-check me-1"></i>Confirmar Venda
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Confirmação -->
    <div class="modal fade" id="modalConfirmacao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Confirmar Venda
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="resumoVenda"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Verifique os dados antes de confirmar a venda.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-gradient-success" id="finalizarVenda">
                        <i class="fas fa-check me-1"></i>Finalizar Venda
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes da Venda -->
    <div class="modal fade" id="modalDetalhesVenda" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Detalhes da Venda
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="conteudoDetalhesVenda">
                    <!-- Conteúdo será carregado via JavaScript -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/vendas.js') }}"></script>
    <script>
        // Dados para JavaScript
        window.produtos = @json($produtos);
        window.clientes = @json($clientes);
        window.vendas = @json($vendas);
    </script>
@endpush
