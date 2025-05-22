@extends('layouts.app')

@section('title', 'Carrinho')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="row">
    <div class="col-md-8">
        <h3>Carrinho de Compras</h3>
        <div id="carrinho-items">
            <!-- Items do carrinho serão carregados via JS -->
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Resumo do Pedido</h5>
                
                <!-- Campo de CEP -->
                <div class="mb-3">
                    <label for="cep" class="form-label">CEP</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="cep" placeholder="00000-000" maxlength="9">
                        <button class="btn btn-outline-secondary" type="button" onclick="consultarCEP()">Consultar</button>
                    </div>
                    <small class="text-muted">Digite o CEP para calcular o frete</small>
                </div>

                <!-- Endereço (será preenchido após consulta do CEP) -->
                <div id="endereco-info" class="mb-3 d-none">
                    <p class="mb-1"><strong>Endereço:</strong> <span id="logradouro"></span></p>
                    <p class="mb-1"><strong>Bairro:</strong> <span id="bairro"></span></p>
                    <p class="mb-1"><strong>Cidade:</strong> <span id="cidade"></span></p>
                    <p class="mb-1"><strong>Estado:</strong> <span id="uf"></span></p>
                </div>

                <div class="mb-3">
                    <label for="cupom" class="form-label">Cupom de Desconto</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="cupom" placeholder="Digite o código">
                        <button class="btn btn-outline-secondary" type="button" onclick="aplicarCupom()">Aplicar</button>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span id="subtotal">R$ 0,00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Desconto:</span>
                    <span id="desconto">R$ 0,00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Frete:</span>
                    <span id="frete">R$ 0,00</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong id="total">R$ 0,00</strong>
                </div>
                <button class="btn btn-primary w-100" id="btnFinalizar" onclick="finalizarPedido()">
                    <span class="spinner-border spinner-border-sm d-none" id="loadingSpinner" role="status" aria-hidden="true"></span>
                    <span id="btnText">Finalizar Pedido</span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
let carrinho = [];
let endereco = null;

// Máscara para o CEP
document.getElementById('cep').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 5) {
        value = value.substring(0, 5) + '-' + value.substring(5, 8);
    }
    e.target.value = value;
});

// Consultar CEP
function consultarCEP() {
    const cep = document.getElementById('cep').value.replace(/\D/g, '');
    
    if (cep.length !== 8) {
        alert('CEP inválido. O CEP deve conter 8 dígitos.');
        return;
    }

    // Mostrar loading
    document.getElementById('endereco-info').classList.add('d-none');
    
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(res => {
            if (!res.ok) {
                throw new Error('Erro ao consultar CEP');
            }
            return res.json();
        })
        .then(data => {
            if (data.erro) {
                alert('CEP não encontrado na base dos Correios');
                return;
            }

            // Preencher endereço
            document.getElementById('logradouro').textContent = data.logradouro || 'Não informado';
            document.getElementById('bairro').textContent = data.bairro || 'Não informado';
            document.getElementById('cidade').textContent = data.localidade || 'Não informado';
            document.getElementById('uf').textContent = data.uf || 'Não informado';
            
            // Mostrar informações do endereço
            document.getElementById('endereco-info').classList.remove('d-none');
            
            // Salvar endereço para uso posterior
            endereco = {
                cep: data.cep,
                logradouro: data.logradouro,
                bairro: data.bairro,
                cidade: data.localidade,
                uf: data.uf,
                complemento: data.complemento,
                ddd: data.ddd
            };
            
            // Recalcular frete com o novo CEP
            atualizarResumo();
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao consultar CEP. Por favor, tente novamente.');
        });
}

// Carregar carrinho
function carregarCarrinho() {
    const carrinhoSalvo = localStorage.getItem('carrinho');
    if (carrinhoSalvo) {
        carrinho = JSON.parse(carrinhoSalvo);
        atualizarCarrinho();
        atualizarContadorCarrinho();
    }
}

// Atualizar contador do carrinho no navbar
function atualizarContadorCarrinho() {
    const totalItens = carrinho.reduce((total, item) => total + item.quantidade, 0);
    const contador = document.getElementById('carrinho-contador');
    if (contador) {
        contador.textContent = totalItens;
        contador.style.display = totalItens > 0 ? 'inline' : 'none';
    }
}

// Atualizar carrinho
function atualizarCarrinho() {
    let html = '';
    carrinho.forEach((item, index) => {
        html += `<div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="card-title">${item.nome}</h5>
                        <p class="card-text">R$ ${item.preco}</p>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" onclick="alterarQuantidade(${index}, -1)">-</button>
                            <input type="number" class="form-control text-center" value="${item.quantidade}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="alterarQuantidade(${index}, 1)" data-estoque="${item.estoque}">+</button>
                        </div>
                        <small class="text-muted">Estoque disponível: ${item.estoque}</small>
                        <button class="btn btn-danger btn-sm mt-2 w-100" onclick="removerItem(${index})">Remover</button>
                    </div>
                </div>
            </div>
        </div>`;
    });
    document.getElementById('carrinho-items').innerHTML = html;
    atualizarResumo();
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    atualizarContadorCarrinho();
}

// Alterar quantidade
function alterarQuantidade(index, delta) {
    const item = carrinho[index];
    const novoValor = item.quantidade + delta;
    const estoqueDisponivel = item.estoque;

    if (novoValor < 1) {
        return;
    }

    if (novoValor > estoqueDisponivel) {
        Swal.fire({
            icon: 'warning',
            title: 'Estoque insuficiente',
            text: `Só temos ${estoqueDisponivel} unidades disponíveis deste produto.`
        });
        return;
    }

    item.quantidade = novoValor;
    atualizarCarrinho();
}

// Remover item
function removerItem(index) {
    carrinho.splice(index, 1);
    atualizarCarrinho();
}

// Atualizar resumo
function atualizarResumo() {
    const subtotal = carrinho.reduce((total, item) => total + (item.preco * item.quantidade), 0);
    const desconto = parseFloat(document.getElementById('desconto').textContent.replace('R$ ', '').replace(',', '.'));
    const frete = calcularFrete(subtotal);

    document.getElementById('subtotal').textContent = `R$ ${subtotal.toFixed(2)}`;
    document.getElementById('frete').textContent = `R$ ${frete.toFixed(2)}`;
    document.getElementById('total').textContent = `R$ ${(subtotal - desconto + frete).toFixed(2)}`;
}

// Calcular frete
function calcularFrete(subtotal) {
    if (subtotal >= 200) return 0;
    if (subtotal >= 52 && subtotal <= 166.59) return 15;
    return 20;
}

// Aplicar cupom
function aplicarCupom() {
    const codigo = document.getElementById('cupom').value;
    fetch(`/api/cupons/validar/${codigo}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(res => res.json())
    .then(res => {
        if (res.error) {
            toastr.error(res.error);
        } else {
            const subtotal = carrinho.reduce((total, item) => total + (item.preco * item.quantidade), 0);
            if (subtotal >= parseFloat(res.valor_minimo)) {
                const desconto = parseFloat(res.desconto);
                document.getElementById('desconto').textContent = `R$ ${desconto.toFixed(2)}`;
                atualizarResumo();
                toastr.success('Cupom aplicado com sucesso!');
            } else {
                toastr.warning(`Valor mínimo para este cupom: R$ ${parseFloat(res.valor_minimo).toFixed(2)}`);
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        toastr.error('Erro ao validar cupom');
    });
}

// Finalizar pedido
function finalizarPedido() {
    const btnFinalizar = document.getElementById('btnFinalizar');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const btnText = document.getElementById('btnText');

    if (btnFinalizar.disabled) return;

    if (carrinho.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Carrinho vazio',
            text: 'Adicione itens ao carrinho primeiro!'
        });
        return;
    }

    if (!endereco || !endereco.cep || !endereco.logradouro || !endereco.bairro || !endereco.cidade || !endereco.uf || endereco.uf.length !== 2) {
        Swal.fire({
            icon: 'warning',
            title: 'Endereço incompleto',
            text: 'Por favor, consulte o CEP e preencha todos os campos do endereço corretamente.'
        });
        return;
    }

    // Validar produtos antes de enviar
    const produtosInvalidos = carrinho.filter(item => !item.id || isNaN(item.id));
    if (produtosInvalidos.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Erro no carrinho',
            text: 'Existem produtos inválidos no carrinho. Por favor, limpe o carrinho e adicione os produtos novamente.'
        }).then(() => {
            carrinho = [];
            localStorage.removeItem('carrinho');
            atualizarCarrinho();
        });
        return;
    }

    btnFinalizar.disabled = true;
    loadingSpinner.classList.remove('d-none');
    btnText.textContent = 'Processando...';

    const cupom = document.getElementById('cupom').value;
    const data = {
        produtos: carrinho.map(item => ({
            id: parseInt(item.id),
            quantidade: parseInt(item.quantidade),
            variacao_id: item.variacao_id ? parseInt(item.variacao_id) : null
        })),
        endereco: {
            cep: endereco.cep,
            logradouro: endereco.logradouro,
            bairro: endereco.bairro,
            cidade: endereco.cidade,
            uf: endereco.uf.toUpperCase().slice(0,2)
        },
        cupom: (cupom && cupom.trim() !== '') ? cupom.trim() : null
    };

    console.log('Enviando pedido:', data);

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000);

    fetch('/api/pedidos', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data),
        signal: controller.signal
    })
    .then(res => {
        clearTimeout(timeoutId);
        if (!res.ok) {
            return res.json().then(err => {
                let msg = err.error || err.message || 'Erro na requisição';
                if (err.errors) {
                    msg += '\n';
                    for (const campo in err.errors) {
                        msg += `- ${campo}: ${err.errors[campo].join(', ')}\n`;
                    }
                }
                throw new Error(msg);
            });
        }
        return res.json();
    })
    .then(res => {
        Swal.fire({
            icon: 'success',
            title: 'Pedido realizado com sucesso!',
            text: 'Você será redirecionado para a página de pedidos.'
        }).then(() => {
            carrinho = [];
            localStorage.removeItem('carrinho');
            window.location.href = '/pedidos';
        });
    })
    .catch(error => {
        console.error('Erro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro ao finalizar pedido',
            text: error.message || 'Ocorreu um erro ao processar seu pedido. Tente novamente.'
        });
    })
    .finally(() => {
        btnFinalizar.disabled = false;
        loadingSpinner.classList.add('d-none');
        btnText.textContent = 'Finalizar Pedido';
    });
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarCarrinho();
});

function adicionarAoCarrinho(produto) {
    const index = carrinho.findIndex(item => item.id === parseInt(produto.id));
    
    if (index === -1) {
        if (produto.estoque < 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Produto sem estoque',
                text: 'Este produto não está disponível no momento.'
            });
            return;
        }
        
        carrinho.push({
            id: parseInt(produto.id),
            nome: produto.nome,
            preco: parseFloat(produto.preco),
            quantidade: 1,
            estoque: parseInt(produto.estoque),
            variacao_id: produto.variacao_id ? parseInt(produto.variacao_id) : null
        });
    } else {
        if (carrinho[index].quantidade >= produto.estoque) {
            Swal.fire({
                icon: 'warning',
                title: 'Estoque insuficiente',
                text: `Só temos ${produto.estoque} unidades disponíveis deste produto.`
            });
            return;
        }
        carrinho[index].quantidade++;
    }
    
    atualizarCarrinho();
}
</script>
@endpush 