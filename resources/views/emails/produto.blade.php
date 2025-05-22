<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
        .produto-info {
            margin: 20px 0;
            padding: 15px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .produto-info p {
            margin: 10px 0;
        }
        .label {
            font-weight: bold;
            color: #2c3e50;
        }
        .valor {
            color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $tipo === 'criado' ? 'Novo Produto Cadastrado' : ($tipo === 'atualizado' ? 'Produto Atualizado' : 'Produto Excluído') }}</h1>
        </div>
        <div class="content">
            <p>Olá,</p>
            <p>Um produto foi {{ $tipo === 'criado' ? 'cadastrado' : ($tipo === 'atualizado' ? 'atualizado' : 'excluído') }} no sistema.</p>
            
            <div class="produto-info">
                <p><span class="label">Nome:</span> {{ $produto->nome }}</p>
                <p><span class="label">Descrição:</span> {{ $produto->descricao }}</p>
                <p><span class="label">Preço:</span> <span class="valor">R$ {{ number_format($produto->preco, 2, ',', '.') }}</span></p>
                <p><span class="label">Estoque:</span> {{ $produto->estoque }} unidades</p>
                @if($produto->variacoes->count() > 0)
                <p><span class="label">Variações:</span></p>
                <ul>
                    @foreach($produto->variacoes as $variacao)
                    <li>{{ $variacao->nome }} - R$ {{ number_format($variacao->preco, 2, ',', '.') }}</li>
                    @endforeach
                </ul>
                @endif
            </div>

            <p>Data da operação: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <div class="footer">
            <p>Este é um e-mail automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html> 