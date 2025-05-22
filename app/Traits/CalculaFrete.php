<?php

namespace App\Traits;

trait CalculaFrete
{
    public function calcularFrete()
    {
        if ($this->subtotal >= 200) {
            return 0;
        } elseif ($this->subtotal >= 52 && $this->subtotal <= 166.59) {
            return 15;
        }
        return 20;
    }
} 