<?php

namespace App\Models;

use App\Traits\GerenciadorEstoque;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variacao extends Model
{
    use HasFactory, GerenciadorEstoque;

    protected $table = 'variacoes';

    protected $fillable = [
        'produto_id',
        'nome',
        'preco_adicional',
        'estoque',
        'ativo'
    ];

    protected $casts = [
        'preco_adicional' => 'decimal:2',
        'estoque' => 'integer',
        'ativo' => 'boolean'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function pedidoItems()
    {
        return $this->hasMany(PedidoItem::class);
    }
}
