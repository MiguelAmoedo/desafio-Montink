<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/produtos');
});

Route::get('/produtos', function () {
    return view('produtos');
});

Route::get('/cupons', function () {
    return view('cupons');
});

Route::get('/pedidos', function () {
    return view('pedidos');
});

Route::get('/carrinho', function () {
    return view('carrinho');
});
