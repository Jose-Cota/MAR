<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProyectoNotificacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $proyecto;
    public $mensajeBitacora;

    /**
     * Create a new message instance.
     */
    public function __construct($proyecto, $mensajeBitacora = '')
    {
        $this->proyecto = $proyecto;
        $this->mensajeBitacora = $mensajeBitacora;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notificación: Ficha de Proyecto POA 2027',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.proyecto_notificacion',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
