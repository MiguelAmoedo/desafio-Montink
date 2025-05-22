@extends('layouts.app')

@section('title', 'Cupons')

@section('content')
<div class="row">
    <div class="col-md-6">
        <h3>Cadastrar Cupom</h3>
        <form id="form-cupom">
            <div class="mb-3">
                <label for="codigo" class="form-label">Código</label>
                <input type="text" class="form-control" id="codigo" name="codigo" required>
            </div>
            <div class="mb-3">
                <label for="desconto" class="form-label">Desconto (R$)</label>
                <input type="number" step="0.01" class="form-control" id="desconto" name="desconto" required>
            </div>
            <div class="mb-3">
                <label for="valor_minimo" class="form-label">Valor Mínimo (R$)</label>
                <input type="number" step="0.01" class="form-control" id="valor_minimo" name="valor_minimo" required>
            </div>
            <div class="mb-3">
                <label for="data_inicio" class="form-label">Data de Início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
            </div>
            <div class="mb-3">
                <label for="data_fim" class="form-label">Data de Fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" required>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Cupom</button>
        </form>
    </div>
    <div class="col-md-6">
        <h3>Cupons Cadastrados</h3>
        <table class="table table-bordered" id="tabela-cupons">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Desconto</th>
                    <th>Valor Mínimo</th>
                    <th>Validade</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Cupons serão carregados via JS -->
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Carregar cupons
function carregarCupons() {
    fetch('/api/cupons', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(res => res.json())
    .then(response => {
        if (response.error) {
            alert(response.error);
            return;
        }
        
        const cupons = Array.isArray(response) ? response : [];
        let html = '';
        cupons.forEach(cupom => {
            const hoje = new Date();
            const dataInicio = new Date(cupom.data_inicio);
            const dataFim = new Date(cupom.data_fim);
            const status = hoje >= dataInicio && hoje <= dataFim && cupom.ativo ? 
                '<span class="badge bg-success">Ativo</span>' : 
                '<span class="badge bg-danger">Inativo</span>';

            html += `<tr>
                <td>${cupom.codigo}</td>
                <td>R$ ${cupom.desconto}</td>
                <td>R$ ${cupom.valor_minimo}</td>
                <td>${new Date(cupom.data_inicio).toLocaleDateString()} - ${new Date(cupom.data_fim).toLocaleDateString()}</td>
                <td>${status}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="excluirCupom(${cupom.id})">Excluir</button>
                </td>
            </tr>`;
        });
        document.querySelector('#tabela-cupons tbody').innerHTML = html;
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao carregar cupons');
    });
}

// Submeter formulário de cupom
const formCupom = document.getElementById('form-cupom');
formCupom.addEventListener('submit', function(e) {
    e.preventDefault();
    const data = {
        codigo: formCupom.codigo.value,
        desconto: formCupom.desconto.value,
        valor_minimo: formCupom.valor_minimo.value,
        data_inicio: formCupom.data_inicio.value,
        data_fim: formCupom.data_fim.value,
        ativo: true
    };

    fetch('/api/cupons', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(response => {
        if (response.error) {
            alert(response.error);
        } else {
            alert('Cupom cadastrado com sucesso!');
            formCupom.reset();
            carregarCupons();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao cadastrar cupom');
    });
});

// Excluir cupom
function excluirCupom(id) {
    if (confirm('Tem certeza que deseja excluir este cupom?')) {
        fetch(`/api/cupons/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => {
            if (res.ok) {
                alert('Cupom excluído com sucesso!');
                carregarCupons();
            } else {
                res.json().then(response => {
                    alert(response.error || 'Erro ao excluir cupom');
                });
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir cupom');
        });
    }
}

// Inicialização
carregarCupons();
</script>
@endpush 