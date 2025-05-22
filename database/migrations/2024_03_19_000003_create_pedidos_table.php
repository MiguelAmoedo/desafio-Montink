<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido')->unique();
            $table->string('status')->default('pendente');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('frete', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->foreignId('cupom_id')->nullable()->constrained('cupons')->nullOnDelete();
            
            // Campos de endereço
            $table->string('cep', 9);
            $table->string('logradouro');
            $table->string('bairro');
            $table->string('cidade');
            $table->string('uf', 2);
            
            $table->timestamps();
        });

        Schema::create('pedido_produto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained()->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained()->cascadeOnDelete();
            $table->integer('quantidade');
            $table->decimal('preco', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedido_produto');
        Schema::dropIfExists('pedidos');
    }
}; 