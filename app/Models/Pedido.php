<?php

namespace App\Models;

use App\Traits\CalculaFrete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory, CalculaFrete;

    protected $fillable = [
        'numero_pedido',
        'status',
        'subtotal',
        'desconto',
        'frete',
        'total',
        'cupom_id',
        'cep',
        'logradouro',
        'bairro',
        'cidade',
        'uf'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'desconto' => 'decimal:2',
        'frete' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'pedido_produto')
            ->withPivot('quantidade', 'preco')
            ->withTimestamps();
    }

    public function cupom()
    {
        return $this->belongsTo(Cupom::class);
    }

    public function calcularTotal()
    {
        $this->frete = $this->calcularFrete();
        $this->total = $this->subtotal + $this->frete - $this->desconto;
        return $this->total;
    }
}
