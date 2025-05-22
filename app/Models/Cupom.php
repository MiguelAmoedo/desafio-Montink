<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupom extends Model
{
    use HasFactory;

    protected $table = 'cupons';

    protected $fillable = [
        'codigo',
        'desconto',
        'valor_minimo',
        'data_inicio',
        'data_fim',
        'ativo'
    ];

    protected $casts = [
        'desconto' => 'decimal:2',
        'valor_minimo' => 'decimal:2',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'ativo' => 'boolean'
    ];

    protected $dates = [
        'data_inicio',
        'data_fim',
        'created_at',
        'updated_at'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function isValid()
    {
        return $this->ativo && 
               now()->between($this->data_inicio, $this->data_fim);
    }
}
