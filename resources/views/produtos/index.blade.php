@extends('layouts.app')

@section('title', 'Produtos - Sistema Distribuidora')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">
            <i class="fas fa-box me-2"></i>Gestão de Produtos
        </h1>
        <button class="btn btn-gradient-primary" data-bs-toggle="modal" data-bs-target="#modalCadastrarProduto">
            <i class="fas fa-plus me-2"></i>Novo Produto
        </button>
    </div>

    <!-- Filtros e Busca -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="buscarProduto" placeholder="Buscar produto...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroCategoria">
                        <option value="">Todas as categorias</option>
                        @foreach ($produtos->pluck('categoria')->unique() as $categoria)
                            <option value="{{ $categoria }}">{{ $categoria }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filtroStatus">
                        <option value="">Todos os status</option>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary w-100" onclick="limparFiltros()">
                        <i class="fas fa-times me-1"></i>Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Produtos -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Lista de Produtos
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabelaProdutos">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th class="text-end">Preço Compra</th>
                            <th class="text-end">Preço Venda</th>
                            <th class="text-end">Preço Fardo</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($produtos as $produto)
                            <tr data-categoria="{{ $produto->categoria }}" data-status="{{ $produto->status ?? 'ativo' }}">
                                <td>
                                    <span class="badge bg-secondary">{{ $produto->codigo }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $produto->nome }}</div>
                                    @if ($produto->descricao)
                                        <small class="text-muted">{{ Str::limit($produto->descricao, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $produto->categoria }}</span>
                                </td>
                                <td class="text-end">
                                    R$ {{ number_format($produto->preco_compra_atual, 2, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold">
                                    R$ {{ number_format($produto->preco_venda_atual, 2, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    @if ($produto->preco_venda_fardo)
                                        R$ {{ number_format($produto->preco_venda_fardo, 2, ',', '.') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-{{ ($produto->status ?? 'ativo') === 'ativo' ? 'success' : 'danger' }}">
                                        {{ ucfirst($produto->status ?? 'ativo') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary"
                                            onclick="editarProduto({{ $produto->id }})" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="verDetalhes({{ $produto->id }})"
                                            title="Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if (($produto->status ?? 'ativo') === 'ativo')
                                            <form action="{{ route('produtos.destroy', $produto) }}" method="POST"
                                                style="display: inline;"
                                                onsubmit="return confirmarAcao('Tem certeza que deseja desativar este produto?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Desativar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nenhum produto cadastrado</h5>
                                    <p class="text-muted">Clique em "Novo Produto" para começar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('produtos.modals.detalhes')
    @include('produtos.modals.cadastrar')
@endsection

@push('scripts')
    <script src="{{ asset('js/produtos.js') }}"></script>
    <script>
        // Dados dos produtos para JavaScript
        window.produtos = @json($produtos);
    </script>
@endpush
