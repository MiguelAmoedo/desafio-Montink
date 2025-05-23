<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Variacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\ProdutoNotificacao;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Produto::select('id', 'nome', 'preco', 'estoque', 'ativo', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string',
            'preco' => 'required|numeric|min:0',
            'estoque' => 'required|integer|min:0',
            'variacoes' => 'nullable|array',
            'variacoes.*.nome' => 'required|string|max:255',
            'variacoes.*.preco' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $produto = Produto::create($request->except('variacoes'));

            if ($request->has('variacoes')) {
                $variacoes = collect($request->variacoes)->map(function ($variacao) {
                    return new Variacao($variacao);
                });
                $produto->variacoes()->saveMany($variacoes);
            }

            DB::commit();

            // Enviar e-mail de notificação em background
            dispatch(function() use ($produto) {
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

                    Mail::to('miguelbombs@gmail.com')->send(new ProdutoNotificacao($produto, 'criado'));
                    Log::info('E-mail de notificação enviado para o produto: ' . $produto->nome);
                } catch (\Exception $e) {
                    Log::error('Erro ao enviar e-mail: ' . $e->getMessage());
                }
            })->afterResponse();

            return response()->json($produto->load('variacoes'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar produto: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao criar produto'], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Produto::with('variacoes')->findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string',
            'preco' => 'required|numeric|min:0',
            'estoque' => 'required|integer|min:0',
            'variacoes' => 'nullable|array',
            'variacoes.*.nome' => 'required|string|max:255',
            'variacoes.*.preco' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $produto = Produto::findOrFail($id);
            $produto->update($request->except('variacoes'));

            if ($request->has('variacoes')) {
                $produto->variacoes()->delete();
                $variacoes = collect($request->variacoes)->map(function ($variacao) {
                    return new Variacao($variacao);
                });
                $produto->variacoes()->saveMany($variacoes);
            }

            DB::commit();

            // Enviar e-mail de notificação em background
            dispatch(function() use ($produto) {
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

                    Mail::to('miguelbombs@gmail.com')->send(new ProdutoNotificacao($produto, 'atualizado'));
                    Log::info('E-mail de notificação enviado para o produto: ' . $produto->nome);
                } catch (\Exception $e) {
                    Log::error('Erro ao enviar e-mail: ' . $e->getMessage());
                }
            })->afterResponse();

            return response()->json($produto->load('variacoes'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar produto: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao atualizar produto'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $produto = Produto::findOrFail($id);
            
            // Enviar e-mail de notificação em background
            dispatch(function() use ($produto) {
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

                    Mail::to('miguelbombs@gmail.com')->send(new ProdutoNotificacao($produto, 'excluido'));
                    Log::info('E-mail de notificação enviado para o produto: ' . $produto->nome);
                } catch (\Exception $e) {
                    Log::error('Erro ao enviar e-mail: ' . $e->getMessage());
                }
            })->afterResponse();

            $produto->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir produto: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao excluir produto'], 400);
        }
    }

    public function verificarEstoque(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'variacao_id' => 'nullable|exists:variacoes,id',
            'quantidade' => 'required|integer|min:1'
        ]);

        $produto = Produto::findOrFail($request->produto_id);
        
        if ($request->has('variacao_id')) {
            $variacao = $produto->variacoes()->findOrFail($request->variacao_id);
            $temEstoque = $variacao->estoque >= $request->quantidade;
            $estoqueDisponivel = $variacao->estoque;
        } else {
            $temEstoque = true; // Produto sem variação sempre tem estoque
            $estoqueDisponivel = null;
        }

        return response()->json([
            'tem_estoque' => $temEstoque,
            'estoque_disponivel' => $estoqueDisponivel
        ]);
    }
}
