<?php

namespace App\Models;

use App\Traits\GerenciadorEstoque;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory, GerenciadorEstoque;

    protected $fillable = [
        'nome',
        'preco',
        'descricao',
        'estoque',
        'ativo'
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'estoque' => 'integer',
        'ativo' => 'boolean'
    ];

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class)
            ->withPivot('quantidade', 'preco')
            ->withTimestamps();
    }

    public function variacoes()
    {
        return $this->hasMany(Variacao::class);
    }

    public function pedidoItems()
    {
        return $this->hasMany(PedidoItem::class);
    }
}
