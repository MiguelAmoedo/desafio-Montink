<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CupomController;
use App\Http\Controllers\CarrinhoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rotas de Produtos
Route::get('/produtos', [ProdutoController::class, 'index']);
Route::post('/produtos', [ProdutoController::class, 'store']);
Route::get('/produtos/{id}', [ProdutoController::class, 'show']);
Route::put('/produtos/{id}', [ProdutoController::class, 'update']);
Route::delete('/produtos/{id}', [ProdutoController::class, 'destroy']);

// Rotas de Pedidos
Route::get('/pedidos', [PedidoController::class, 'index']);
Route::post('/pedidos', [PedidoController::class, 'store']);
Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy']);

// Rotas de Cupons
Route::get('/cupons', [CupomController::class, 'index']);
Route::post('/cupons', [CupomController::class, 'store']);
Route::get('/cupons/{id}', [CupomController::class, 'show']);
Route::put('/cupons/{id}', [CupomController::class, 'update']);
Route::delete('/cupons/{id}', [CupomController::class, 'destroy']);
Route::get('/cupons/validar/{codigo}', [CupomController::class, 'validar']);

// Rotas do Carrinho
Route::get('carrinho', [CarrinhoController::class, 'index']);
Route::post('carrinho/adicionar', [CarrinhoController::class, 'adicionar']);
Route::put('carrinho/atualizar', [CarrinhoController::class, 'atualizar']);
Route::delete('carrinho/remover', [CarrinhoController::class, 'remover']);
Route::delete('carrinho/limpar', [CarrinhoController::class, 'limpar']);
