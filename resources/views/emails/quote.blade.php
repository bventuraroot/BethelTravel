<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Propuesta de Viaje - {{ $quote->title }}</title>
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
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            padding: 35px 30px;
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
        .quote-card {
            background-color: #f8f9fa;
            border-left: 4px solid #1e3a8a;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .quote-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px dashed #e9ecef;
            padding-bottom: 8px;
        }
        .quote-row:last-child {
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
                <h1>{{ $quote->company->name ?? 'Bethel Travel' }}</h1>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Tu propuesta de viaje ideal</p>
            </div>
            <div class="content">
                <div>
                    {!! nl2br($bodyContent) !!}
                </div>

                <div class="quote-card">
                    <div style="font-weight: bold; font-size: 18px; margin-bottom: 15px; color: #1e3a8a;">
                        Resumen de la Propuesta
                    </div>
                    <div class="quote-row">
                        <span class="info-label">Destino:</span>
                        <span class="info-value" style="font-weight: bold; color: #3b82f6;">{{ $quote->title }}</span>
                    </div>
                    @if($quote->subtitle)
                        <div class="quote-row">
                            <span class="info-label">Fecha / Vigencia:</span>
                            <span class="info-value">{{ $quote->subtitle }}</span>
                        </div>
                    @endif
                    <div class="quote-row">
                        <span class="info-label">Agente:</span>
                        <span class="info-value">{{ $quote->user->name ?? 'Asesor de Viajes' }}</span>
                    </div>
                </div>
                
                <p style="font-size: 14px; color: #666;">
                    * Hemos adjuntado a este correo un documento PDF detallado con la cotización formal, las opciones de hoteles, tarifas por persona e itinerario de vuelos.
                </p>
            </div>
            <div class="footer">
                <p>Este correo ha sido enviado por Bethel Travel.</p>
                <p>&copy; {{ date('Y') }} {{ $quote->company->name ?? 'Bethel Travel' }}. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
