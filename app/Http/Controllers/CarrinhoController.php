<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Variacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CarrinhoController extends Controller
{
    public function index()
    {
        $carrinho = Session::get('carrinho', []);
        $items = [];
        $subtotal = 0;

        foreach ($carrinho as $item) {
            $produto = Produto::find($item['produto_id']);
            $variacao = isset($item['variacao_id']) ? Variacao::find($item['variacao_id']) : null;

            if ($produto) {
                $preco = $produto->preco;
                if ($variacao) {
                    $preco += $variacao->preco_adicional;
                }

                $items[] = [
                    'produto' => $produto,
                    'variacao' => $variacao,
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $preco,
                    'subtotal' => $preco * $item['quantidade']
                ];

                $subtotal += $preco * $item['quantidade'];
            }
        }

        $frete = $this->calcularFrete($subtotal);
        $total = $subtotal + $frete;

        return response()->json([
            'items' => $items,
            'subtotal' => $subtotal,
            'frete' => $frete,
            'total' => $total
        ]);
    }

    public function adicionar(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'variacao_id' => 'nullable|exists:variacoes,id',
            'quantidade' => 'required|integer|min:1'
        ]);

        $produto = Produto::findOrFail($request->produto_id);
        
        if ($request->has('variacao_id')) {
            $variacao = $produto->variacoes()->findOrFail($request->variacao_id);
            if ($variacao->estoque < $request->quantidade) {
                return response()->json([
                    'error' => 'Quantidade indisponível em estoque'
                ], 400);
            }
        }

        $carrinho = Session::get('carrinho', []);
        $key = $request->produto_id . '_' . ($request->variacao_id ?? 'sem_variacao');

        if (isset($carrinho[$key])) {
            $carrinho[$key]['quantidade'] += $request->quantidade;
        } else {
            $carrinho[$key] = [
                'produto_id' => $request->produto_id,
                'variacao_id' => $request->variacao_id,
                'quantidade' => $request->quantidade
            ];
        }

        Session::put('carrinho', $carrinho);
        return response()->json(['message' => 'Item adicionado ao carrinho']);
    }

    public function atualizar(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'variacao_id' => 'nullable|exists:variacoes,id',
            'quantidade' => 'required|integer|min:1'
        ]);

        $carrinho = Session::get('carrinho', []);
        $key = $request->produto_id . '_' . ($request->variacao_id ?? 'sem_variacao');

        if (!isset($carrinho[$key])) {
            return response()->json(['error' => 'Item não encontrado no carrinho'], 404);
        }

        if ($request->has('variacao_id')) {
            $variacao = Variacao::findOrFail($request->variacao_id);
            if ($variacao->estoque < $request->quantidade) {
                return response()->json([
                    'error' => 'Quantidade indisponível em estoque'
                ], 400);
            }
        }

        $carrinho[$key]['quantidade'] = $request->quantidade;
        Session::put('carrinho', $carrinho);

        return response()->json(['message' => 'Quantidade atualizada']);
    }

    public function remover(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'variacao_id' => 'nullable|exists:variacoes,id'
        ]);

        $carrinho = Session::get('carrinho', []);
        $key = $request->produto_id . '_' . ($request->variacao_id ?? 'sem_variacao');

        if (isset($carrinho[$key])) {
            unset($carrinho[$key]);
            Session::put('carrinho', $carrinho);
        }

        return response()->json(['message' => 'Item removido do carrinho']);
    }

    public function limpar()
    {
        Session::forget('carrinho');
        return response()->json(['message' => 'Carrinho limpo']);
    }

    private function calcularFrete($subtotal)
    {
        if ($subtotal >= 200) {
            return 0;
        } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15;
        }
        return 20;
    }
}
