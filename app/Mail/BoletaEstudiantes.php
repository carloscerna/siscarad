<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BoletaEstudiantes extends Mailable
{
    use Queueable, SerializesModels;
 
    public $nombre;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nombre)
    {
        //
        $this->nombre = $nombre;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $nombre = "Prueba de correo Mail...";
        return $this->view('mails.BoletaEstudiantes',['nombre'=>$nombre]);
    }
}
