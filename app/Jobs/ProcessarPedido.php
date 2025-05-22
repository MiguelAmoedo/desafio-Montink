<?php

namespace App\Jobs;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessarPedido implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pedido;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Aqui você pode implementar a lógica de processamento do pedido
            // Por exemplo, enviar e-mail de confirmação, atualizar estoque, etc.
            Log::info('Processando pedido', [
                'numero_pedido' => $this->pedido->numero_pedido,
                'total' => $this->pedido->total
            ]);

            // Simula um processamento demorado
            sleep(2);

            $this->pedido->update(['status' => 'aprovado']);
        } catch (\Exception $e) {
            Log::error('Erro ao processar pedido: ' . $e->getMessage());
            throw $e;
        }
    }
}
