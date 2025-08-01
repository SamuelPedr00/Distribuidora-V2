@extends('layouts.app')

@section('title', 'Dashboard - Sistema Distribuidora')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <i class="fas fa-chart-pie me-2"></i>Dashboard
        </h1>
        <div class="text-muted">
            <i class="fas fa-calendar-alt me-1"></i>
            {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <div class="stats-value">{{ $totalProdutos }}</div>
                    <div class="text-muted fw-semibold">
                        <i class="fas fa-box me-1"></i>Total de Produtos
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card success h-100">
                <div class="card-body text-center">
                    <div class="stats-value">{{ $produtosComEstoque }}</div>
                    <div class="text-muted fw-semibold">
                        <i class="fas fa-warehouse me-1"></i>Itens em Estoque
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card {{ $valorCaixa >= 0 ? 'success' : 'danger' }} h-100">
                <div class="card-body text-center">
                    <div class="stats-value">
                        R$ {{ number_format($valorCaixa, 2, ',', '.') }}
                    </div>
                    <div class="text-muted fw-semibold">
                        <i class="fas fa-cash-register me-1"></i>Saldo em Caixa
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card warning h-100">
                <div class="card-body text-center">
                    <div class="stats-value">{{ $quantidadeProdutosBaixo }}</div>
                    <div class="text-muted fw-semibold">
                        <i class="fas fa-exclamation-triangle me-1"></i>Produtos em Baixa
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-gradient">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-rocket me-2"></i>Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('vendas.index') }}" class="btn btn-gradient-success w-100 py-3">
                                <i class="fas fa-shopping-cart me-2"></i>
                                <div class="fw-bold">Nova Venda</div>
                                <small>Registrar venda</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('produtos.index') }}" class="btn btn-gradient-primary w-100 py-3">
                                <i class="fas fa-plus me-2"></i>
                                <div class="fw-bold">Cadastrar Produto</div>
                                <small>Novo produto</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('movimentacoes.index') }}" class="btn btn-gradient-warning w-100 py-3">
                                <i class="fas fa-exchange-alt me-2"></i>
                                <div class="fw-bold">Movimentar Estoque</div>
                                <small>Entrada/Saída</small>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('caixa.index') }}" class="btn btn-gradient-primary w-100 py-3">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                <div class="fw-bold">Lançar no Caixa</div>
                                <small>Entrada/Saída</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Produtos com Estoque Baixo -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            Produtos com Estoque Baixo
                        </h5>
                        <small class="text-muted">≤ 10 unidades</small>
                    </div>
                    <a href="{{ route('estoque.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-warehouse me-1"></i>Gerenciar Estoque
                    </a>
                </div>
                <div class="card-body p-0">
                    @if ($produtosBaixoEstoque->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Código</th>
                                        <th class="text-center">Estoque Atual</th>
                                        <th class="text-end">Preço</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($produtosBaixoEstoque as $produto)
                                        <tr
                                            class="{{ optional($produto->estoque)->quantidade <= 5 ? 'table-danger' : 'table-warning' }}">
                                            <td>
                                                <div class="fw-semibold">{{ $produto->nome }}</div>
                                                <small class="text-muted">{{ $produto->categoria }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $produto->codigo }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ optional($produto->estoque)->quantidade <= 5 ? 'bg-danger' : 'bg-warning' }}">
                                                    {{ optional($produto->estoque)->quantidade ?? 0 }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-semibold">
                                                R$ {{ number_format($produto->preco_venda_atual, 2, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('movimentacoes.index') }}"
                                                        class="btn btn-outline-primary" title="Movimentar Estoque">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                    <a href="{{ route('produtos.edit', $produto) }}"
                                                        class="btn btn-outline-secondary" title="Editar Produto">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                            <h5 class="text-success">Todos os produtos estão com estoque adequado!</h5>
                            <p class="text-muted">Nenhum produto com estoque baixo no momento.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
