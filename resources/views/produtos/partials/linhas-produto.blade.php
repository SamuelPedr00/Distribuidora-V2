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
            <span class="badge bg-{{ ($produto->status ?? 'ativo') === 'ativo' ? 'success' : 'danger' }}">
                {{ ucfirst($produto->status ?? 'ativo') }}
            </span>
        </td>
        <td class="text-center">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" onclick="editarProduto({{ $produto->id }})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalDetalhesProduto"
                    onclick="carregarDetalhes({{ $produto->id }})" title="Detalhes">
                    <i class="fas fa-eye"></i>
                </button>

                @if (($produto->status ?? 'ativo') === 'ativo')
                    <form action="{{ route('produtos.destroy', $produto) }}" method="POST" style="display: inline;"
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
            <h5 class="text-muted">Nenhum produto encontrado</h5>
        </td>
    </tr>
@endforelse
