<?php

namespace App\Http\Controllers;

use App\Models\Cupom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CupomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $cupons = Cupom::all();
            return response()->json($cupons);
        } catch (\Exception $e) {
            Log::error('Erro ao listar cupons: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao listar cupons'], 500);
        }
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
        try {
            $request->validate([
                'codigo' => 'required|string|unique:cupons',
                'desconto' => 'required|numeric|min:0',
                'valor_minimo' => 'required|numeric|min:0',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after:data_inicio',
                'ativo' => 'boolean'
            ]);

            $cupom = Cupom::create($request->all());
            return response()->json($cupom, 201);
        } catch (\Exception $e) {
            Log::error('Erro ao criar cupom: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao criar cupom: ' . $e->getMessage()], 400);
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
        try {
            $cupom = Cupom::findOrFail($id);
            return response()->json($cupom);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar cupom: ' . $e->getMessage());
            return response()->json(['error' => 'Cupom não encontrado'], 404);
        }
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
        try {
            $request->validate([
                'codigo' => 'required|string|unique:cupons,codigo,' . $id,
                'desconto' => 'required|numeric|min:0',
                'valor_minimo' => 'required|numeric|min:0',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after:data_inicio',
                'ativo' => 'boolean'
            ]);

            $cupom = Cupom::findOrFail($id);
            $cupom->update($request->all());
            return response()->json($cupom);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar cupom: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao atualizar cupom: ' . $e->getMessage()], 400);
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
            $cupom = Cupom::findOrFail($id);
            $cupom->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir cupom: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao excluir cupom'], 400);
        }
    }

    public function validar($codigo)
    {
        try {
            $cupom = Cupom::where('codigo', $codigo)
                ->where('ativo', true)
                ->where('data_inicio', '<=', now())
                ->where('data_fim', '>=', now())
                ->first();

            if (!$cupom) {
                return response()->json(['error' => 'Cupom inválido ou expirado'], 400);
            }

            return response()->json([
                'id' => $cupom->id,
                'codigo' => $cupom->codigo,
                'desconto' => $cupom->desconto,
                'valor_minimo' => $cupom->valor_minimo
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao validar cupom: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao validar cupom'], 500);
        }
    }
}
