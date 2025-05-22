<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class ProcessarFilaPedidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pedidos:processar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa a fila de pedidos pendentes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando processamento da fila de pedidos...');

        while (true) {
            $this->info('Aguardando novos pedidos...');
            $this->call('queue:work', [
                '--queue' => 'default',
                '--tries' => 3,
                '--timeout' => 60
            ]);

            sleep(5);
        }
    }
}
