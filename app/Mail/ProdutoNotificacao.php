<?php

namespace App\Mail;

use App\Models\Produto;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProdutoNotificacao extends Mailable
{
    use Queueable, SerializesModels;

    public $produto;
    public $tipo;

    public function __construct(Produto $produto, string $tipo = 'criado')
    {
        $this->produto = $produto;
        $this->tipo = $tipo;
    }

    public function build()
    {
        $assunto = match($this->tipo) {
            'criado' => 'Novo Produto Cadastrado',
            'atualizado' => 'Produto Atualizado',
            'excluido' => 'Produto Excluído',
            default => 'Notificação de Produto'
        };

        return $this->subject($assunto . ' - ' . $this->produto->nome)
                    ->view('emails.produto');
    }
} 