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
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .total {
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Confirmação de Pedido</h1>
        </div>
        <div class="content">
            <p>Olá,</p>
            <p>Seu pedido foi recebido com sucesso!</p>
            
            <h2>Detalhes do Pedido</h2>
            <p><strong>Número do Pedido:</strong> {{ $pedido->numero_pedido }}</p>
            <p><strong>Data:</strong> {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
            
            <h3>Endereço de Entrega</h3>
            <p>
                {{ $pedido->logradouro }}<br>
                {{ $pedido->bairro }}<br>
                {{ $pedido->cidade }} - {{ $pedido->uf }}<br>
                CEP: {{ $pedido->cep }}
            </p>

            <h3>Itens do Pedido</h3>
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->produtos as $produto)
                    <tr>
                        <td>{{ $produto->nome }}</td>
                        <td>{{ $produto->pivot->quantidade }}</td>
                        <td>R$ {{ number_format($produto->pivot->preco, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($produto->pivot->preco * $produto->pivot->quantidade, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total">
                <p><strong>Subtotal:</strong> R$ {{ number_format($pedido->subtotal, 2, ',', '.') }}</p>
                @if($pedido->desconto > 0)
                <p><strong>Desconto:</strong> R$ {{ number_format($pedido->desconto, 2, ',', '.') }}</p>
                @endif
                <p><strong>Frete:</strong> R$ {{ number_format($pedido->frete, 2, ',', '.') }}</p>
                <p><strong>Total:</strong> R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
            </div>

            <p>Agradecemos sua compra!</p>
        </div>
        <div class="footer">
            <p>Este é um e-mail automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html> 