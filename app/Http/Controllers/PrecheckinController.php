<?php

namespace App\Http\Controllers;

use App\Mail\PrecheckinNotificationMail;
use App\Models\Company;
use App\Models\PrecheckinConfig;
use App\Models\Salesdetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PrecheckinController extends Controller
{
    /**
     * Mostrar la vista del módulo.
     */
    public function index(Request $request)
    {
        $companies = Company::all();
        $selectedCompanyId = $request->get('company_id', $companies->first()?->id ?? 1);

        // Obtener o crear configuración por defecto para la empresa seleccionada
        $config = PrecheckinConfig::firstOrCreate(
            ['company_id' => $selectedCompanyId],
            [
                'dias_antes' => 2,
                'enviar_cliente' => true,
                'email_agencia' => $companies->where('id', $selectedCompanyId)->first()?->email ?? '',
                'asunto' => 'Prechequeo disponible para tu vuelo - Reserva {reserva}',
                'cuerpo' => "Hola {cliente},\n\nTe informamos que ya puedes realizar el prechequeo para tu próximo vuelo con la reserva {reserva}.\n\nDetalles del vuelo:\nAerolínea: {aerolinea}\nRuta: {ruta}\nFecha de viaje: {fecha_viaje}\n\nBuen viaje,\nBethel Travel",
                'active' => true
            ]
        );

        return view('precheckin.index', [
            'companies' => $companies,
            'selectedCompanyId' => $selectedCompanyId,
            'config' => $config
        ]);
    }

    /**
     * Retorna los datos de reservas en JSON para DataTables.
     */
    public function getData(Request $request)
    {
        $companyId = $request->get('company_id', 1);
        $status = $request->get('status', 'todos');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = DB::table('salesdetails as sd')
            ->join('sales as s', 's.id', '=', 'sd.sale_id')
            ->join('clients as c', 'c.id', '=', 's.client_id')
            ->leftJoin('aerolineas as al', 'al.id_aerolinea', '=', 'sd.linea')
            ->leftJoin('aeropuertos as ap', 'ap.id_aeropuerto', '=', 'sd.destino')
            ->where('s.company_id', $companyId)
            ->where('s.state', '<>', 0) // No anuladas
            ->whereNotNull('sd.reserva')
            ->where('sd.reserva', '!=', '')
            ->select(
                'sd.id',
                'sd.reserva',
                'sd.ruta',
                'sd.fecha_viaje',
                'sd.precheckin_status',
                'sd.precheckin_notes',
                'sd.precheckin_email_sent',
                'sd.precheckin_email_sent_at',
                'sd.precheckin_completed_at',
                's.id as sale_id',
                'c.firstname',
                'c.firstlastname',
                'c.name_contribuyente',
                'c.email as client_email',
                'al.nombre as airline_name',
                'ap.ciudad as destination_city',
                'ap.iata as destination_iata'
            );

        // Filtrar por estado
        if ($status !== 'todos') {
            $query->where('sd.precheckin_status', $status);
        }

        // Filtrar por rango de fechas de viaje
        if ($dateFrom) {
            $query->where('sd.fecha_viaje', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('sd.fecha_viaje', '<=', $dateTo);
        }

        // Ordenar por fecha de viaje (más cercana primero)
        $query->orderBy('sd.fecha_viaje', 'asc');

        $data = $query->get()->map(function ($row) {
            // Nombre formateado del cliente
            $fullname = trim(($row->firstname ?? '') . ' ' . ($row->firstlastname ?? ''));
            if (empty($fullname)) {
                $fullname = $row->name_contribuyente ?: 'Pasajero';
            }

            // Calcular si la alerta debe mostrarse (si está pendiente y la fecha de viaje está cerca)
            $isAlert = false;
            $daysRemaining = null;
            if ($row->precheckin_status === 'pendiente' && $row->fecha_viaje) {
                $travelDate = \Carbon\Carbon::parse($row->fecha_viaje)->startOfDay();
                $today = \Carbon\Carbon::now()->startOfDay();
                $daysRemaining = $today->diffInDays($travelDate, false);
                $isAlert = $daysRemaining <= 2; // Alerta si faltan 2 días o menos
            }

            return [
                'id' => $row->id,
                'reserva' => $row->reserva,
                'ruta' => $row->ruta,
                'fecha_viaje' => $row->fecha_viaje ? date('d/m/Y', strtotime($row->fecha_viaje)) : 'No definida',
                'fecha_viaje_raw' => $row->fecha_viaje,
                'precheckin_status' => $row->precheckin_status,
                'precheckin_notes' => $row->precheckin_notes ?? '',
                'precheckin_email_sent' => (bool)$row->precheckin_email_sent,
                'precheckin_email_sent_at' => $row->precheckin_email_sent_at ? date('d/m/Y H:i', strtotime($row->precheckin_email_sent_at)) : 'No enviado',
                'precheckin_completed_at' => $row->precheckin_completed_at ? date('d/m/Y H:i', strtotime($row->precheckin_completed_at)) : null,
                'sale_id' => $row->sale_id,
                'client_name' => $fullname,
                'client_email' => $row->client_email,
                'airline_name' => $row->airline_name ?? 'N/A',
                'destination' => $row->destination_city ? "{$row->destination_city} ({$row->destination_iata})" : 'N/A',
                'is_alert' => $isAlert,
                'days_remaining' => $daysRemaining
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Actualiza el estado y detalles de una reserva.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'precheckin_status' => 'required|in:pendiente,realizado,no_requerido',
            'precheckin_notes' => 'nullable|string',
            'fecha_viaje' => 'nullable|date'
        ]);

        $detail = Salesdetail::findOrFail($id);
        
        $oldStatus = $detail->precheckin_status;
        $newStatus = $request->precheckin_status;

        $detail->precheckin_status = $newStatus;
        $detail->precheckin_notes = $request->precheckin_notes;
        
        if ($request->filled('fecha_viaje')) {
            $detail->fecha_viaje = $request->fecha_viaje;
        }

        // Si cambia a realizado y antes no lo estaba
        if ($newStatus === 'realizado' && $oldStatus !== 'realizado') {
            $detail->precheckin_completed_at = now();
        } elseif ($newStatus !== 'realizado') {
            $detail->precheckin_completed_at = null;
        }

        $detail->save();

        return response()->json([
            'success' => true,
            'message' => 'Reserva actualizada correctamente.'
        ]);
    }

    /**
     * Guarda la configuración de correo de prechequeo.
     */
    public function saveConfig(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'dias_antes' => 'required|integer|min:1|max:30',
            'enviar_cliente' => 'boolean',
            'email_agencia' => 'nullable|string',
            'asunto' => 'required|string|max:255',
            'cuerpo' => 'required|string',
            'active' => 'boolean'
        ]);

        $config = PrecheckinConfig::updateOrCreate(
            ['company_id' => $request->company_id],
            [
                'dias_antes' => $request->dias_antes,
                'enviar_cliente' => $request->has('enviar_cliente') ? $request->enviar_cliente : false,
                'email_agencia' => $request->email_agencia,
                'asunto' => $request->asunto,
                'cuerpo' => $request->cuerpo,
                'active' => $request->has('active') ? $request->active : false
            ]
        );

        return redirect()->route('precheckin.index', ['company_id' => $request->company_id])
            ->with('success', 'Configuración guardada correctamente.');
    }

    /**
     * Envía manualmente el correo de prechequeo.
     */
    public function sendMailManual(Request $request, $id)
    {
        $detail = Salesdetail::with(['sale.client', 'airline'])->findOrFail($id);
        
        if (!$detail->reserva) {
            return response()->json([
                'success' => false,
                'message' => 'Esta línea de venta no tiene un número de reserva asociado.'
            ], 400);
        }

        $companyId = $detail->sale?->company_id ?? 1;

        $config = PrecheckinConfig::where('company_id', $companyId)->first();
        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'No se ha configurado la plantilla de correos para esta empresa.'
            ], 400);
        }

        $client = $detail->sale?->client;
        $recipientEmails = [];

        if ($config->enviar_cliente && $client && $client->email) {
            $recipientEmails[] = $client->email;
        }

        if ($config->email_agencia) {
            $agenciaEmails = array_filter(array_map('trim', explode(',', $config->email_agencia)));
            $recipientEmails = array_merge($recipientEmails, $agenciaEmails);
        }

        $recipientEmails = array_unique(array_filter($recipientEmails));

        if (empty($recipientEmails)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron destinatarios de correo para enviar (el cliente no tiene correo registrado y no hay correo de agencia configurado).'
            ], 400);
        }

        try {
            $mailable = new PrecheckinNotificationMail($detail, $config);
            
            Mail::to($recipientEmails)->send($mailable);

            // Registrar envío
            $detail->precheckin_email_sent = 1;
            $detail->precheckin_email_sent_at = now();
            $detail->save();

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado correctamente a: ' . implode(', ', $recipientEmails)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }
}
