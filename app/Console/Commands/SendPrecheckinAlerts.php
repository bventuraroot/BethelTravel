<?php

namespace App\Console\Commands;

use App\Mail\PrecheckinNotificationMail;
use App\Models\Company;
use App\Models\PrecheckinConfig;
use App\Models\Salesdetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPrecheckinAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:send-precheckin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca reservas aéreas próximas al viaje y envía correos de alerta de prechequeo según la configuración';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Iniciando envío de alertas de prechequeo...");

        // Obtener todas las empresas
        $companies = Company::all();

        foreach ($companies as $company) {
            $this->info("Procesando empresa: {$company->name}");

            // Obtener configuración para esta empresa, o crear una por defecto si no existe
            $config = PrecheckinConfig::firstOrCreate(
                ['company_id' => $company->id],
                [
                    'dias_antes' => 1,
                    'enviar_cliente' => true,
                    'email_agencia' => $company->email,
                    'asunto' => 'Prechequeo disponible para tu vuelo - Reserva {reserva}',
                    'cuerpo' => "Hola {cliente},\n\nTe informamos que ya puedes realizar el prechequeo para tu próximo vuelo con la reserva {reserva}.\n\nDetalles del vuelo:\nAerolínea: {aerolinea}\nRuta: {ruta}\nFecha de viaje: {fecha_viaje}\n\nBuen viaje,\nBethel Travel",
                    'active' => true
                ]
            );

            if (!$config->active) {
                $this->warn("Las alertas de prechequeo están inactivas para esta empresa.");
                continue;
            }

            $diasAntes = $config->dias_antes;
            $hoy = now()->toDateString();
            $fechaLimite = now()->addDays($diasAntes)->toDateString();

            // Buscar boletos aéreos pendientes de prechequeo con fecha de viaje en el rango de alerta
            $details = Salesdetail::whereNotNull('reserva')
                ->where('reserva', '!=', '')
                ->where('precheckin_status', 'pendiente')
                ->where('precheckin_email_sent', 0)
                ->whereNotNull('fecha_viaje')
                ->where('fecha_viaje', '<=', $fechaLimite)
                ->where('fecha_viaje', '>=', $hoy)
                ->whereHas('sale', function ($query) use ($company) {
                    $query->where('company_id', $company->id)
                          ->where('state', '<>', 0); // No anulada
                })
                ->with(['sale.client', 'airline'])
                ->get();

            $this->info("Encontrados " . $details->count() . " boletos para alertar.");

            foreach ($details as $detail) {
                $client = $detail->sale?->client;
                $recipientEmails = [];

                if ($config->enviar_cliente && $client && $client->email) {
                    $recipientEmails[] = $client->email;
                }

                if ($config->email_agencia) {
                    // Soporta múltiples correos de agencia separados por coma
                    $agenciaEmails = array_filter(array_map('trim', explode(',', $config->email_agencia)));
                    $recipientEmails = array_merge($recipientEmails, $agenciaEmails);
                }

                $recipientEmails = array_unique(array_filter($recipientEmails));

                if (empty($recipientEmails)) {
                    $this->error("Boleto ID {$detail->id} (Reserva: {$detail->reserva}): No se encontraron destinatarios de correo.");
                    continue;
                }

                try {
                    $mailable = new PrecheckinNotificationMail($detail, $config);
                    
                    // Enviar correo
                    Mail::to($recipientEmails)->send($mailable);

                    // Marcar como enviado
                    $detail->precheckin_email_sent = 1;
                    $detail->precheckin_email_sent_at = now();
                    $detail->save();

                    $this->info("✓ Alerta enviada con éxito para la reserva: {$detail->reserva} a: " . implode(', ', $recipientEmails));
                } catch (\Exception $e) {
                    $this->error("Error enviando alerta para la reserva {$detail->reserva}: " . $e->getMessage());
                }
            }
        }

        $this->info("Proceso de alertas de prechequeo completado.");
        return 0;
    }
}
