<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoletaEstudianteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alumno;
    protected $pdfContenido;
    protected $nombreArchivo;

    /**
     * El constructor recibe el objeto del alumno, el string binario del PDF y el nombre asignado.
     */
    public function __construct($alumno, $pdfContenido, $nombreArchivo)
    {
        $this->alumno = $alumno;
        $this->pdfContenido = $pdfContenido;
        $this->nombreArchivo = $nombreArchivo;
    }

    /**
     * Define el asunto del correo electrónico.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Boleta Oficial de Calificaciones - NIE: ' . $this->alumno->codigo_nie,
        );
    }

    /**
     * Asigna la vista que servirá como cuerpo del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.boleta_digital',
        );
    }

    /**
     * Adjunta el documento PDF utilizando los datos almacenados en memoria.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContenido, $this->nombreArchivo)
                ->withMime('application/pdf'),
        ];
    }
}