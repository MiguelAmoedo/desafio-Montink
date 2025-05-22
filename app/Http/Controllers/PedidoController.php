<?php

namespace App\Http\Controllers;

use App\Events\PedidoCriado;
use App\Jobs\ProcessarPedido;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
use App\Models\Variacao;
use App\Models\Cupom;
use App\Mail\PedidoConfirmacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    public function index()
    {
        return Pedido::with('produtos')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'produtos' => 'required|array',
            'produtos.*.id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|integer|min:1',
            'produtos.*.variacao_id' => 'nullable|exists:variacoes,id',
            'endereco' => 'required|array',
            'endereco.cep' => 'required|string|size:9',
            'endereco.logradouro' => 'required|string',
            'endereco.bairro' => 'required|string',
            'endereco.cidade' => 'required|string',
            'endereco.uf' => 'required|string|size:2',
            'cupom' => 'nullable|string|exists:cupons,codigo'
        ]);

        try {
            DB::beginTransaction();

            // Verificar estoque de todos os produtos
            foreach ($request->produtos as $item) {
                $produto = Produto::findOrFail($item['id']);
                if (!$produto->temEstoque($item['quantidade'], $item['variacao_id'] ?? null)) {
                    throw new \Exception("Produto {$produto->nome} não possui estoque suficiente");
                }
            }

            // Gerar número do pedido
            $numeroPedido = 'PED' . date('Ymd') . str_pad(Pedido::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Calcular subtotal
            $subtotal = 0;
            foreach ($request->produtos as $item) {
                $produto = Produto::findOrFail($item['id']);
                $preco = $produto->preco;
                if (isset($item['variacao_id'])) {
                    $variacao = Variacao::find($item['variacao_id']);
                    $preco += $variacao->preco_adicional;
                }
                $subtotal += $preco * $item['quantidade'];
            }

            // Calcular desconto do cupom
            $desconto = 0;
            $cupomId = null;
            if ($request->cupom) {
                $cupom = Cupom::where('codigo', $request->cupom)->first();
                if ($cupom && $cupom->isValid() && $subtotal >= $cupom->valor_minimo) {
                    $desconto = $cupom->desconto;
                    $cupomId = $cupom->id;
                }
            }

            // Calcular frete
            $frete = 0;
            if ($subtotal < 200) {
                $frete = $subtotal >= 52 && $subtotal <= 166.59 ? 15 : 20;
            }

            // Criar pedido
            $pedido = Pedido::create([
                'numero_pedido' => $numeroPedido,
                'status' => 'finalizado',
                'subtotal' => $subtotal,
                'desconto' => $desconto,
                'frete' => $frete,
                'total' => $subtotal + $frete - $desconto,
                'cupom_id' => $cupomId,
                'cep' => $request->endereco['cep'],
                'logradouro' => $request->endereco['logradouro'],
                'bairro' => $request->endereco['bairro'],
                'cidade' => $request->endereco['cidade'],
                'uf' => $request->endereco['uf']
            ]);

            // Adicionar produtos ao pedido
            foreach ($request->produtos as $item) {
                $produto = Produto::findOrFail($item['id']);
                
                // Reservar estoque
                if (!$produto->reservarEstoque($item['quantidade'], $item['variacao_id'] ?? null)) {
                    throw new \Exception("Erro ao reservar estoque do produto {$produto->nome}");
                }

                // Calcular preço
                $preco = $produto->preco;
                if (isset($item['variacao_id'])) {
                    $variacao = Variacao::find($item['variacao_id']);
                    $preco += $variacao->preco_adicional;
                }

                // Adicionar ao pedido
                $pedido->produtos()->attach($produto->id, [
                    'quantidade' => $item['quantidade'],
                    'preco' => $preco,
                    'variacao_id' => $item['variacao_id'] ?? null
                ]);
            }

            DB::commit();

            // Disparar evento
            event(new PedidoCriado($pedido));

            // Enviar e-mail de confirmação
            try {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp' => [
                        'transport' => 'smtp',
                        'host' => config('mailtrap.host'),
                        'port' => config('mailtrap.port'),
                        'encryption' => config('mailtrap.encryption'),
                        'username' => config('mailtrap.username'),
                        'password' => config('mailtrap.password'),
                        'timeout' => null,
                        'local_domain' => env('MAIL_EHLO_DOMAIN'),
                    ],
                    'mail.from' => [
                        'address' => config('mailtrap.from.address'),
                        'name' => config('mailtrap.from.name'),
                    ],
                ]);

                Mail::to('miguelbombs@gmail.com')->send(new PedidoConfirmacao($pedido));
                Log::info('E-mail de confirmação enviado para o pedido: ' . $pedido->numero_pedido);
            } catch (\Exception $e) {
                Log::error('Erro ao enviar e-mail: ' . $e->getMessage());
            }

            return response()->json($pedido->load('produtos'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar pedido: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $pedido = Pedido::with('produtos')->findOrFail($id);
        return $pedido;
    }

    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->delete();
        return response()->json(null, 204);
    }

    public function cancelar($id)
    {
        try {
            DB::beginTransaction();

            $pedido = Pedido::findOrFail($id);
            
            if ($pedido->status === 'cancelado') {
                throw new \Exception('Pedido já está cancelado');
            }

            // Liberar estoque
            foreach ($pedido->produtos as $produto) {
                $quantidade = $pedido->produtos->where('id', $produto->id)->first()->pivot->quantidade;
                $variacaoId = $pedido->produtos->where('id', $produto->id)->first()->pivot->variacao_id;
                
                if (!$produto->liberarEstoque($quantidade, $variacaoId)) {
                    throw new \Exception("Erro ao liberar estoque do produto {$produto->nome}");
                }
            }

            $pedido->update(['status' => 'cancelado']);

            DB::commit();

            return response()->json(['message' => 'Pedido cancelado com sucesso']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cancelar pedido: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function atualizarStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:pendente,aprovado,em_preparacao,enviado,entregue,cancelado'
            ]);

            $pedido = Pedido::findOrFail($id);
            
            // Validações específicas para cada status
            switch ($request->status) {
                case 'cancelado':
                    // Liberar estoque ao cancelar
                    foreach ($pedido->produtos as $produto) {
                        $quantidade = $pedido->produtos->where('id', $produto->id)->first()->pivot->quantidade;
                        $variacaoId = $pedido->produtos->where('id', $produto->id)->first()->pivot->variacao_id;
                        
                        if (!$produto->liberarEstoque($quantidade, $variacaoId)) {
                            throw new \Exception("Erro ao liberar estoque do produto {$produto->nome}");
                        }
                    }
                    break;
                
                case 'aprovado':
                    // Verificar se o pedido está pendente
                    if ($pedido->status !== 'pendente') {
                        throw new \Exception('Apenas pedidos pendentes podem ser aprovados');
                    }
                    break;
                
                case 'enviado':
                    // Verificar se o pedido está em preparação
                    if ($pedido->status !== 'em_preparacao') {
                        throw new \Exception('O pedido precisa estar em preparação para ser enviado');
                    }
                    break;
                
                case 'entregue':
                    // Verificar se o pedido foi enviado
                    if ($pedido->status !== 'enviado') {
                        throw new \Exception('O pedido precisa ter sido enviado para ser marcado como entregue');
                    }
                    break;
            }

            $pedido->update(['status' => $request->status]);

            return response()->json([
                'message' => 'Status do pedido atualizado com sucesso',
                'pedido' => $pedido->load('produtos')
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status do pedido: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
} 