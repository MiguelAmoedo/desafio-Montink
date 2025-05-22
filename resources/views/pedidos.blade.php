@extends('layouts.app')

@section('title', 'Pedidos')

@section('content')
<div class="row">
    <div class="col-12">
        <h3>Pedidos</h3>
        <table class="table table-bordered" id="tabela-pedidos">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Data</th>
                    <th>Subtotal</th>
                    <th>Desconto</th>
                    <th>Frete</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Pedidos serão carregados via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="modal-detalhes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informações do Pedido</h6>
                        <p><strong>Número:</strong> <span id="detalhe-numero"></span></p>
                        <p><strong>Data:</strong> <span id="detalhe-data"></span></p>
                        <p><strong>Status:</strong> <span id="detalhe-status"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Valores</h6>
                        <p><strong>Subtotal:</strong> R$ <span id="detalhe-subtotal"></span></p>
                        <p><strong>Desconto:</strong> R$ <span id="detalhe-desconto"></span></p>
                        <p><strong>Frete:</strong> R$ <span id="detalhe-frete"></span></p>
                        <p><strong>Total:</strong> R$ <span id="detalhe-total"></span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Produtos</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Preço</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detalhe-produtos">
                                <!-- Produtos serão carregados via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Carregar pedidos
function carregarPedidos() {
    fetch('/api/pedidos')
        .then(res => res.json())
        .then(pedidos => {
            let html = '';
            pedidos.forEach(pedido => {
                html += `<tr>
                    <td>${pedido.numero_pedido}</td>
                    <td>${new Date(pedido.created_at).toLocaleDateString()}</td>
                    <td>R$ ${pedido.subtotal}</td>
                    <td>R$ ${pedido.desconto}</td>
                    <td>R$ ${pedido.frete}</td>
                    <td>R$ ${pedido.total}</td>
                    <td>${pedido.status}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="verDetalhes(${pedido.id})">Detalhes</button>
                        <button class="btn btn-danger btn-sm" onclick="excluirPedido(${pedido.id})">Excluir</button>
                    </td>
                </tr>`;
            });
            document.querySelector('#tabela-pedidos tbody').innerHTML = html;
        });
}

// Ver detalhes do pedido
function verDetalhes(id) {
    fetch(`/api/pedidos/${id}`)
        .then(res => res.json())
        .then(pedido => {
            document.getElementById('detalhe-numero').textContent = pedido.numero_pedido;
            document.getElementById('detalhe-data').textContent = new Date(pedido.created_at).toLocaleDateString();
            document.getElementById('detalhe-status').textContent = pedido.status;
            document.getElementById('detalhe-subtotal').textContent = pedido.subtotal;
            document.getElementById('detalhe-desconto').textContent = pedido.desconto;
            document.getElementById('detalhe-frete').textContent = pedido.frete;
            document.getElementById('detalhe-total').textContent = pedido.total;

            let html = '';
            pedido.produtos.forEach(item => {
                html += `<tr>
                    <td>${item.nome}</td>
                    <td>${item.pivot.quantidade}</td>
                    <td>R$ ${item.preco}</td>
                    <td>R$ ${(item.preco * item.pivot.quantidade).toFixed(2)}</td>
                </tr>`;
            });
            document.getElementById('detalhe-produtos').innerHTML = html;

            new bootstrap.Modal(document.getElementById('modal-detalhes')).show();
        });
}

// Excluir pedido
function excluirPedido(id) {
    if (confirm('Tem certeza que deseja excluir este pedido?')) {
        fetch(`/api/pedidos/${id}`, {
            method: 'DELETE'
        })
        .then(res => {
            if (res.ok) {
                alert('Pedido excluído com sucesso!');
                carregarPedidos();
            } else {
                alert('Erro ao excluir pedido');
            }
        });
    }
}

// Inicialização
carregarPedidos();
</script>
@endpush 