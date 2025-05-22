<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('cep', 9)->after('cupom_id');
            $table->string('logradouro')->after('cep');
            $table->string('bairro')->after('logradouro');
            $table->string('cidade')->after('bairro');
            $table->string('uf', 2)->after('cidade');
        });
    }

    public function down()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['cep', 'logradouro', 'bairro', 'cidade', 'uf']);
        });
    }
}; 