@extends('layouts.app')

@section('title', 'Pedidos')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="container">
    <h2>Pedidos</h2>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Data</th>
                    <th>Subtotal</th>
                    <th>Desconto</th>
                    <th>Frete</th>
                    <th>Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="pedidos-lista">
                <!-- Pedidos serão carregados via JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Detalhes do Pedido -->
<div class="modal fade" id="detalhesPedidoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalhes-pedido">
                    <!-- Detalhes serão carregados via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
let pedidos = [];

function carregarPedidos() {
    fetch('/api/pedidos')
        .then(res => res.json())
        .then(data => {
            pedidos = data;
            atualizarTabela();
        })
        .catch(error => {
            console.error('Erro:', error);
            Swal.fire('Erro', 'Erro ao carregar pedidos', 'error');
        });
}

function atualizarTabela() {
    const tbody = document.getElementById('pedidos-lista');
    tbody.innerHTML = '';

    pedidos.forEach(pedido => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${pedido.numero_pedido}</td>
            <td>${new Date(pedido.created_at).toLocaleDateString()}</td>
            <td>R$ ${parseFloat(pedido.subtotal).toFixed(2)}</td>
            <td>R$ ${parseFloat(pedido.desconto).toFixed(2)}</td>
            <td>R$ ${parseFloat(pedido.frete).toFixed(2)}</td>
            <td>R$ ${parseFloat(pedido.total).toFixed(2)}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="verDetalhes(${pedido.id})">
                    <i class="fas fa-eye"></i> Ver Detalhes
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function verDetalhes(pedidoId) {
    const pedido = pedidos.find(p => p.id === pedidoId);
    if (!pedido) return;

    const modal = document.getElementById('detalhesPedidoModal');
    const detalhes = document.getElementById('detalhes-pedido');
    
    let html = `
        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Informações do Pedido</h6>
                <p><strong>Número:</strong> ${pedido.numero_pedido}</p>
                <p><strong>Data:</strong> ${new Date(pedido.created_at).toLocaleString()}</p>
            </div>
            <div class="col-md-6">
                <h6>Endereço de Entrega</h6>
                <p>${pedido.logradouro}</p>
                <p>${pedido.bairro}</p>
                <p>${pedido.cidade} - ${pedido.uf}</p>
                <p>CEP: ${pedido.cep}</p>
            </div>
        </div>
        <h6>Produtos</h6>
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
    `;

    pedido.produtos.forEach(produto => {
        html += `
            <tr>
                <td>${produto.nome}</td>
                <td>${produto.pivot.quantidade}</td>
                <td>R$ ${parseFloat(produto.pivot.preco).toFixed(2)}</td>
                <td>R$ ${(parseFloat(produto.pivot.preco) * produto.pivot.quantidade).toFixed(2)}</td>
            </tr>
        `;
    });

    html += `
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                    <td>R$ ${parseFloat(pedido.subtotal).toFixed(2)}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>Desconto:</strong></td>
                    <td>R$ ${parseFloat(pedido.desconto).toFixed(2)}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>Frete:</strong></td>
                    <td>R$ ${parseFloat(pedido.frete).toFixed(2)}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td>R$ ${parseFloat(pedido.total).toFixed(2)}</td>
                </tr>
            </tfoot>
        </table>
    `;

    detalhes.innerHTML = html;
    new bootstrap.Modal(modal).show();
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarPedidos();
});
</script>
@endpush 