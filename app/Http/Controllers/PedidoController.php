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
            'cupom' => 'nullable|string|exists:cupons,codigo',
            'endereco' => 'required|array',
            'endereco.cep' => 'required|string|size:9',
            'endereco.logradouro' => 'required|string',
            'endereco.bairro' => 'required|string',
            'endereco.cidade' => 'required|string',
            'endereco.uf' => 'required|string|size:2'
        ]);

        try {
            DB::beginTransaction();

            // Calcular subtotal
            $subtotal = 0;
            foreach ($request->produtos as $item) {
                $produto = Produto::findOrFail($item['id']);
                $subtotal += $produto->preco * $item['quantidade'];
            }

            // Validar e aplicar cupom
            $desconto = 0;
            $cupom = null;
            if ($request->cupom) {
                $cupom = Cupom::where('codigo', $request->cupom)
                    ->where('ativo', true)
                    ->where('data_inicio', '<=', now())
                    ->where('data_fim', '>=', now())
                    ->first();

                if (!$cupom) {
                    throw new \Exception('Cupom inválido ou expirado');
                }

                if ($subtotal < $cupom->valor_minimo) {
                    throw new \Exception('Valor mínimo para este cupom: R$ ' . $cupom->valor_minimo);
                }

                $desconto = $cupom->desconto;
            }

            // Calcular frete
            $frete = 0;
            if ($subtotal < 200) {
                if ($subtotal >= 52 && $subtotal <= 166.59) {
                    $frete = 15;
                } else {
                    $frete = 20;
                }
            }

            // Criar pedido
            $pedido = Pedido::create([
                'numero_pedido' => 'PED' . time(),
                'status' => 'confirmado',
                'subtotal' => $subtotal,
                'desconto' => $desconto,
                'frete' => $frete,
                'total' => $subtotal - $desconto + $frete,
                'cupom_id' => $cupom ? $cupom->id : null,
                'cep' => $request->endereco['cep'],
                'logradouro' => $request->endereco['logradouro'],
                'bairro' => $request->endereco['bairro'],
                'cidade' => $request->endereco['cidade'],
                'uf' => $request->endereco['uf']
            ]);

            // Adicionar produtos ao pedido
            foreach ($request->produtos as $item) {
                $pedido->produtos()->attach($item['id'], [
                    'quantidade' => $item['quantidade'],
                    'preco' => Produto::find($item['id'])->preco
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
                \Log::info('E-mail de confirmação enviado para o pedido: ' . $pedido->numero_pedido);
            } catch (\Exception $e) {
                \Log::error('Erro ao enviar e-mail: ' . $e->getMessage());
            }

            return response()->json($pedido->load('produtos'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
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
} 