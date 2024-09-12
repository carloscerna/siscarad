<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderShipped extends Mailable
{
    use Queueable, SerializesModels;
    public $name;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    /**
     * Build the message.
     *
     * @return $this
     */
     public function __construct($name)
     {
         $this->name = $name;
     }

    public function build()
    {
        return $this->view('mails.CorreoNuevo');
    }
}
