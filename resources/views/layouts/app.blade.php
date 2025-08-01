<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema Distribuidora')</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/icons.css') }}">

</head>

<body>

    <div class="main-container">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light rounded-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('dashboard') }}">
                    <i class="fas fa-store me-2"></i>Sistema Distribuidora
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}">
                                <i class="fas fa-chart-pie me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('produtos.*') ? 'active' : '' }}"
                                href="{{ route('produtos.index') }}">
                                <i class="fas fa-box me-1"></i>Produtos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('estoque.*') ? 'active' : '' }}"
                                href="{{ route('estoque.index') }}">
                                <i class="fas fa-warehouse me-1"></i>Estoque
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('movimentacoes.*') ? 'active' : '' }}"
                                href="{{ route('movimentacoes.index') }}">
                                <i class="fas fa-exchange-alt me-1"></i>Movimentação
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('vendas.*') ? 'active' : '' }}"
                                href="{{ route('vendas.index') }}">
                                <i class="fas fa-shopping-cart me-1"></i>Vendas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('credito.*') ? 'active' : '' }}"
                                href="{{ route('credito.index') }}">
                                <i class="fas fa-credit-card me-1"></i>Crédito
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}"
                                href="{{ route('clientes.index') }}">
                                <i class="fas fa-users me-1"></i>Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('caixa.*') ? 'active' : '' }}"
                                href="{{ route('caixa.index') }}">
                                <i class="fas fa-cash-register me-1"></i>Caixa
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Content Area -->
        <div class="content-area">
            @yield('content')
        </div>
    </div>

    <!-- Modal de Mensagens -->
    @include('components.message-modal')

    <!-- jQuery -->
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>

    <!-- Scripts globais -->
    <script>
        // Configuração global do CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Sistema de mensagens
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('msgModal');
            const texto = document.getElementById('msgModalText');
            const btnFechar = document.getElementById('msgModalClose');
            const modalContent = document.getElementById('msgModalContent');

            @if (session('success'))
                texto.textContent = "{{ session('success') }}";
                modalContent.className = 'modal-content border-success';
                modal.style.display = 'flex';
            @elseif ($errors->any())
                let erros = "";
                @foreach ($errors->all() as $error)
                    erros += "• {{ $error }}\n";
                @endforeach
                texto.textContent = erros.trim();
                modalContent.className = 'modal-content border-danger';
                modal.style.display = 'flex';
            @elseif (session('error'))
                texto.textContent = "{{ session('error') }}";
                modalContent.className = 'modal-content border-danger';
                modal.style.display = 'flex';
            @endif

            if (btnFechar) {
                btnFechar.onclick = () => {
                    modal.style.display = 'none';
                };
            }

            // Fechar modal clicando fora
            window.onclick = (event) => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            };
        });

        // Função para formatação de valores
        function formatarMoeda(valor) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor);
        }

        // Função para confirmação de ações
        function confirmarAcao(mensagem) {
            return confirm(mensagem);
        }
    </script>

    @stack('scripts')

</body>
