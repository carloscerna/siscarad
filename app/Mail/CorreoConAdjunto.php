<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CorreoConAdjunto extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre; // Pasar variables al correo

    public function __construct($nombre)
    {
        $this->nombre = $nombre;
    }

    public function build()
    {
        return $this->view('emails.correo')
                    ->subject('Correo con adjunto e imagen')
                    ->attach(public_path('documentos/archivo.pdf')) // Ruta del archivo adjunto
                    ->withSwiftMessage(function ($message) {
                        $cid = $message->embed(public_path('imagenes/logo.png')); // Imagen embebida
                        $this->viewData['cid'] = $cid;
                    });
    }
}
