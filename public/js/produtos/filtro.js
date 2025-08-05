
    document.addEventListener('DOMContentLoaded', function () {
        const buscarInput = document.getElementById('buscarProduto');
        const filtroCategoria = document.getElementById('filtroCategoria');
        const filtroStatus = document.getElementById('filtroStatus');

        function buscarProdutos() {
            const termo = buscarInput.value;
            const categoria = filtroCategoria.value;
            const status = filtroStatus.value;

            fetch(rotaFiltrar + `?termo=${termo}&categoria=${categoria}&status=${status}`)


                .then(res => res.json())
                .then(data => {
                    document.querySelector('#tabelaProdutos tbody').innerHTML = data.html;
                });
        }

        buscarInput.addEventListener('input', buscarProdutos);
        filtroCategoria.addEventListener('change', buscarProdutos);
        filtroStatus.addEventListener('change', buscarProdutos);
    });

    function limparFiltros() {
        document.getElementById('buscarProduto').value = '';
        document.getElementById('filtroCategoria').value = '';
        document.getElementById('filtroStatus').value = '';
        // Recarrega todos os produtos
        document.dispatchEvent(new Event('DOMContentLoaded'));
    }

