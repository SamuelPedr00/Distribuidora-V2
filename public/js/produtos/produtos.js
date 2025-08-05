// Função para carregar os detalhes do produto (modal abre automaticamente)
function carregarDetalhes(produtoId) {
    // O modal já abre automaticamente pelo data-bs-toggle
    // Só precisamos carregar o conteúdo
    
    const conteudoDetalhes = document.getElementById('conteudoDetalhes');
    conteudoDetalhes.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-2">Carregando detalhes do produto...</p>
        </div>
    `;
    
    // Fazer requisição AJAX para buscar os detalhes
    fetch(`/produtos/${produtoId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Renderizar os detalhes do produto
        renderizarDetalhes(data);
    })
    .catch(error => {
        console.error('Erro completo:', error);
        console.error('URL chamada:', `/produtos/${produtoId}/detalhes`);
        conteudoDetalhes.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Erro ao carregar detalhes:</strong><br>
                <small>${error.message}</small><br>
                <small>URL: /produtos/${produtoId}/detalhes</small><br>
                <small>Verifique o console para mais detalhes</small>
            </div>
        `;
    });
}

// Função para renderizar os detalhes no modal
function renderizarDetalhes(produto) {
    const conteudoDetalhes = document.getElementById('conteudoDetalhes');
    
    conteudoDetalhes.innerHTML = `
        <div class="row">
            <div class="col-md-12">
                <h4 class="text-primary mb-3">${produto.nome}</h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Código:</strong> 
                        <span class="badge bg-secondary">${produto.codigo}</span>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>Preço:</strong> 
                        <span class="text-success fw-bold fs-5">
                            R$ ${parseFloat(produto.preco).toLocaleString('pt-BR', {minimumFractionDigits: 2})}
                        </span>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Categoria:</strong> 
                        <span class="badge bg-info">${produto.categoria || 'Sem categoria'}</span>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong> 
                        <span class="badge ${produto.status === 'ativo' ? 'bg-success' : 'bg-danger'}">
                            ${produto.status === 'ativo' ? 'Ativo' : 'Inativo'}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        ${produto.descricao ? `
            <hr>
            <div class="mt-3">
                <h6><i class="fas fa-align-left me-2"></i>Descrição</h6>
                <p class="text-muted">${produto.descricao}</p>
            </div>
        ` : ''}
        
        <hr>
        <div class="row mt-3">
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="fas fa-calendar-plus me-1"></i>
                    Criado em: ${new Date(produto.created_at).toLocaleDateString('pt-BR')}
                </small>
            </div>
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="fas fa-calendar-edit me-1"></i>
                    Atualizado em: ${new Date(produto.updated_at).toLocaleDateString('pt-BR')}
                </small>
            </div>
        </div>
    `;
}

// Alternativa: Função usando jQuery (caso prefira)
function verDetalhesJQuery(produtoId) {
    const $modal = $('#modalDetalhesProduto');
    const $conteudo = $('#conteudoDetalhes');
    
    // Mostrar loading
    $conteudo.html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-2">Carregando detalhes do produto...</p>
        </div>
    `);
    
    // Abrir modal
    $modal.modal('show');
    
    // Requisição AJAX
    $.ajax({
        url: `/produtos/${produtoId}/detalhes`,
        type: 'GET',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            renderizarDetalhesJQuery(data);
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar detalhes:', error);
            $conteudo.html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erro ao carregar os detalhes do produto. Tente novamente.
                </div>
            `);
        }
    });
}

function renderizarDetalhesJQuery(produto) {
    // Mesma lógica de renderização da função anterior
    $('#conteudoDetalhes').html(/* mesmo HTML da função renderizarDetalhes */);
}