<?php

namespace App\Mail;

use App\Models\PrecheckinConfig;
use App\Models\Salesdetail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PrecheckinNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $detail;
    public $config;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Salesdetail $detail, PrecheckinConfig $config)
    {
        $this->detail = $detail;
        $this->config = $config;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $this->replacePlaceholders($this->config->asunto);
        $body = $this->replacePlaceholders($this->config->cuerpo);

        return $this->view('emails.precheckin_notification')
            ->subject($subject)
            ->with([
                'bodyContent' => $body,
                'detail' => $this->detail
            ]);
    }

    /**
     * Reemplaza los comodines dinámicos por los valores reales de la reserva.
     */
    protected function replacePlaceholders($text)
    {
        $client = $this->detail->sale?->client;
        $clientName = $client ? trim(($client->firstname ?? '') . ' ' . ($client->firstlastname ?? '')) : '';
        if (empty($clientName) && $client) {
            $clientName = $client->name_contribuyente ?: 'Pasajero';
        }
        if (empty($clientName)) {
            $clientName = 'Pasajero';
        }

        $replacements = [
            '{cliente}' => $clientName,
            '{reserva}' => $this->detail->reserva ?? 'N/A',
            '{aerolinea}' => $this->detail->airline?->nombre ?? 'N/A',
            '{ruta}' => $this->detail->ruta ?? 'N/A',
            '{fecha_viaje}' => $this->detail->fecha_viaje ? $this->detail->fecha_viaje->format('d/m/Y') : 'N/A',
        ];

        return str_ireplace(array_keys($replacements), array_values($replacements), $text);
    }
}
