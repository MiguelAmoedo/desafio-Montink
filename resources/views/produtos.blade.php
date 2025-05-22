@extends('layouts.app')

@section('title', 'Produtos')

@section('content')
<div class="row">
    <div class="col-md-6">
        <h3>Cadastrar Produto</h3>
        <form id="form-produto">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="preco" class="form-label">Preço (R$)</label>
                <input type="number" step="0.01" class="form-control" id="preco" name="preco" required>
            </div>
            <div class="mb-3">
                <label for="estoque" class="form-label">Estoque</label>
                <input type="number" class="form-control" id="estoque" name="estoque" required min="0">
            </div>
            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" checked>
                    <label class="form-check-label" for="ativo">
                        Ativo
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Produto</button>
        </form>
    </div>
    <div class="col-md-6">
        <h3>Produtos Cadastrados</h3>
        <table class="table table-bordered" id="tabela-produtos">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Produtos serão carregados via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="modal-edicao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-edicao">
                    <input type="hidden" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="edit-nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-preco" class="form-label">Preço (R$)</label>
                        <input type="number" step="0.01" class="form-control" id="edit-preco" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="edit-descricao" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit-ativo">
                            <label class="form-check-label" for="edit-ativo">
                                Ativo
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="salvarEdicao()">Salvar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Carregar produtos
function carregarProdutos() {
    fetch('/api/produtos', {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(produtos => {
            let html = '';
            produtos.forEach(produto => {
                html += `<tr>
                    <td>${produto.nome}</td>
                    <td>R$ ${produto.preco}</td>
                    <td>${produto.estoque}</td>
                    <td>${produto.ativo ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>'}</td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="adicionarAoCarrinho(${produto.id}, '${produto.nome}', ${produto.preco})">
                            <i class="bi bi-cart-plus"></i>
                        </button>
                        <button class="btn btn-info btn-sm" onclick="editarProduto(${produto.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="excluirProduto(${produto.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            document.querySelector('#tabela-produtos tbody').innerHTML = html;
        });
}

// Adicionar ao carrinho
function adicionarAoCarrinho(id, nome, preco) {
    let carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    const item = carrinho.find(i => i.id === id);
    
    if (item) {
        item.quantidade++;
    } else {
        carrinho.push({
            id,
            nome,
            preco,
            quantidade: 1
        });
    }
    
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    atualizarContadorCarrinho();
    toastr.success('Produto adicionado ao carrinho!');
}

// Atualizar contador do carrinho
function atualizarContadorCarrinho() {
    const carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    const totalItens = carrinho.reduce((total, item) => total + item.quantidade, 0);
    const contador = document.getElementById('carrinho-contador');
    if (contador) {
        contador.textContent = totalItens;
        contador.style.display = totalItens > 0 ? 'inline' : 'none';
    }
}

// Submeter formulário de produto
const formProduto = document.getElementById('form-produto');
formProduto.addEventListener('submit', function(e) {
    e.preventDefault();
    const data = {
        nome: formProduto.nome.value,
        preco: formProduto.preco.value,
        estoque: formProduto.estoque.value,
        descricao: formProduto.descricao.value,
        ativo: formProduto.ativo.checked
    };

    fetch('/api/produtos', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if (res.error) {
            toastr.error(res.error);
        } else {
            toastr.success('Produto cadastrado com sucesso!');
            formProduto.reset();
            carregarProdutos();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        toastr.error('Erro ao cadastrar produto');
    });
});

// Editar produto
function editarProduto(id) {
    fetch(`/api/produtos/${id}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(res => res.json())
        .then(produto => {
            document.getElementById('edit-id').value = produto.id;
            document.getElementById('edit-nome').value = produto.nome;
            document.getElementById('edit-preco').value = produto.preco;
            document.getElementById('edit-descricao').value = produto.descricao;
            document.getElementById('edit-ativo').checked = produto.ativo;
            new bootstrap.Modal(document.getElementById('modal-edicao')).show();
        });
}

// Salvar edição
function salvarEdicao() {
    const id = document.getElementById('edit-id').value;
    const data = {
        nome: document.getElementById('edit-nome').value,
        preco: document.getElementById('edit-preco').value,
        descricao: document.getElementById('edit-descricao').value,
        ativo: document.getElementById('edit-ativo').checked
    };

    fetch(`/api/produtos/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if (res.error) {
            toastr.error(res.error);
        } else {
            toastr.success('Produto atualizado com sucesso!');
            bootstrap.Modal.getInstance(document.getElementById('modal-edicao')).hide();
            carregarProdutos();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        toastr.error('Erro ao atualizar produto');
    });
}

// Excluir produto
function excluirProduto(id) {
    if (confirm('Tem certeza que deseja excluir este produto?')) {
        fetch(`/api/produtos/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => {
            if (res.ok) {
                toastr.success('Produto excluído com sucesso!');
                carregarProdutos();
            } else {
                toastr.error('Erro ao excluir produto');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            toastr.error('Erro ao excluir produto');
        });
    }
}

// Inicialização
carregarProdutos();
atualizarContadorCarrinho();
</script>
@endpush 