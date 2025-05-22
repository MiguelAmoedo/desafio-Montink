<?php

namespace App\Traits;

use App\Models\Variacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait GerenciadorEstoque
{
    public function temEstoque(int $quantidade, ?int $variacaoId = null): bool
    {
        if ($variacaoId) {
            $variacao = $this->variacoes()->find($variacaoId);
            return $variacao && $variacao->estoque >= $quantidade;
        }
        
        return $this->estoque >= $quantidade;
    }

    public function reservarEstoque(int $quantidade, ?int $variacaoId = null): bool
    {
        try {
            DB::beginTransaction();

            if ($variacaoId) {
                $variacao = $this->variacoes()->lockForUpdate()->find($variacaoId);
                if (!$variacao || $variacao->estoque < $quantidade) {
                    DB::rollBack();
                    return false;
                }
                $variacao->estoque -= $quantidade;
                $variacao->save();
            } else {
                if ($this->estoque < $quantidade) {
                    DB::rollBack();
                    return false;
                }
                $this->estoque -= $quantidade;
                $this->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao reservar estoque: ' . $e->getMessage());
            return false;
        }
    }

    public function liberarEstoque(int $quantidade, ?int $variacaoId = null): bool
    {
        try {
            DB::beginTransaction();

            if ($variacaoId) {
                $variacao = $this->variacoes()->lockForUpdate()->find($variacaoId);
                if (!$variacao) {
                    DB::rollBack();
                    return false;
                }
                $variacao->estoque += $quantidade;
                $variacao->save();
            } else {
                $this->estoque += $quantidade;
                $this->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao liberar estoque: ' . $e->getMessage());
            return false;
        }
    }

    public function ajustarEstoque(int $quantidade, ?int $variacaoId = null): bool
    {
        try {
            DB::beginTransaction();

            if ($variacaoId) {
                $variacao = $this->variacoes()->lockForUpdate()->find($variacaoId);
                if (!$variacao) {
                    DB::rollBack();
                    return false;
                }
                $variacao->estoque = $quantidade;
                $variacao->save();
            } else {
                $this->estoque = $quantidade;
                $this->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao ajustar estoque: ' . $e->getMessage());
            return false;
        }
    }
} 