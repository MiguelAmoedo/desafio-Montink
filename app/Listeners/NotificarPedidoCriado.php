<?php

namespace App\Listeners;

use App\Events\PedidoCriado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotificarPedidoCriado implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PedidoCriado  $event
     * @return void
     */
    public function handle(PedidoCriado $event)
    {
        $pedido = $event->pedido;
        
        // Aqui você pode implementar a lógica de notificação
        // Por exemplo, enviar e-mail, SMS, etc.
        Log::info('Novo pedido criado', [
            'numero_pedido' => $pedido->numero_pedido,
            'total' => $pedido->total
        ]);
    }
}
