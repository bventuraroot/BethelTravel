<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnviarDteInvalidado extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $asunto = 'Comprobante de Invalidación DTE';
        if (!empty($this->data['numero_original'])) {
            $asunto = "Comprobante de Invalidación DTE No. {$this->data['numero_original']}";
        }

        return new Envelope(
            subject: $asunto,
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.dte_invalidado',
            with: [
                'data' => $this->data,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }

    /**
     * Build the message (método alternativo para compatibilidad)
     *
     * @return $this
     */
    public function build()
    {
        $asunto = 'Comprobante de Invalidación DTE';
        if (!empty($this->data['numero_original'])) {
            $asunto = "Comprobante de Invalidación DTE No. {$this->data['numero_original']}";
        }

        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($asunto)
                    ->view('emails.dte_invalidado')
                    ->with('data', $this->data);
    }
}
