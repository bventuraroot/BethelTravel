<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Información de tu Vuelo</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333333;
        }
        .wrapper {
            width: 100%;
            background-color: #f4f6f9;
            padding: 20px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #7367f0, #a8a1f7);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
            font-size: 16px;
        }
        .flight-card {
            background-color: #f8f9fa;
            border-left: 4px solid #7367f0;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .flight-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px dashed #e9ecef;
            padding-bottom: 8px;
        }
        .flight-info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .info-label {
            font-weight: bold;
            color: #555555;
        }
        .info-value {
            color: #333333;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888888;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>Bethel Travel</h1>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Control de Prechequeo de Reservas</p>
            </div>
            <div class="content">
                <div>
                    {!! nl2br($bodyContent) !!}
                </div>

                <div class="flight-card">
                    <div style="font-weight: bold; font-size: 18px; margin-bottom: 15px; color: #7367f0;">
                        Detalle del Viaje
                    </div>
                    <div class="flight-info-row">
                        <span class="info-label">Código de Reserva:</span>
                        <span class="info-value" style="font-family: monospace; font-size: 18px; font-weight: bold; color: #28c76f;">{{ $detail->reserva }}</span>
                    </div>
                    @if($detail->linea && $detail->airline)
                        <div class="flight-info-row">
                            <span class="info-label">Aerolínea:</span>
                            <span class="info-value">{{ $detail->airline->nombre }}</span>
                        </div>
                    @endif
                    @if($detail->ruta)
                        <div class="flight-info-row">
                            <span class="info-label">Ruta:</span>
                            <span class="info-value">{{ $detail->ruta }}</span>
                        </div>
                    @endif
                    @if($detail->fecha_viaje)
                        <div class="flight-info-row">
                            <span class="info-label">Fecha de Viaje:</span>
                            <span class="info-value">{{ $detail->fecha_viaje->format('d/m/Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="footer">
                <p>Este correo ha sido generado de forma automática por el sistema de Bethel Travel.</p>
                <p>&copy; {{ date('Y') }} Bethel Travel. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
