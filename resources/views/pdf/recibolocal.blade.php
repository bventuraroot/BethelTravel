<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recibo de Ingreso No. {{ $documento[0]['nu_doc'] ?? $documento[0]['id_doc'] }}</title>

    <style type="text/css">
        * {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
        }

        body {
            margin: 0;
            padding: 10px;
        }

        table {
            font-size: 11px;
            border-collapse: collapse;
        }

        .header-table {
            margin-bottom: 25px;
        }

        .cuadro-recibo {
            border: 2px solid #2A3F54;
            border-radius: 8px;
            padding: 12px;
            background-color: #f8f9fa;
        }

        .titulo-recibo {
            font-size: 16px;
            font-weight: bold;
            color: #2A3F54;
            text-align: center;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .numero-recibo {
            font-size: 18px;
            font-weight: bold;
            color: #d9534f;
            text-align: center;
        }

        .seccion-titulo {
            background-color: #2A3F54;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
            padding: 5px 8px;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-radius: 3px;
        }

        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .detalle-table {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .detalle-table th {
            background-color: #2A3F54;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            font-size: 10px;
            border: 1px solid #2A3F54;
        }

        .detalle-table td {
            padding: 8px 10px;
            border: 1px solid #dddddd;
        }

        .detalle-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .total-box {
            background-color: #2A3F54;
            color: #ffffff;
            padding: 10px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            border-radius: 5px;
            min-width: 150px;
        }

        .total-letras {
            font-style: italic;
            font-weight: bold;
            color: #555555;
            padding: 10px;
            border: 1px dashed #cccccc;
            border-radius: 5px;
            background-color: #fdfdfd;
        }

        .firma-container {
            margin-top: 60px;
            text-align: center;
        }

        .linea-firma {
            border-top: 1px solid #888888;
            width: 200px;
            margin: 0 auto 5px auto;
        }

        .firma-label {
            font-size: 10px;
            color: #666666;
            text-transform: uppercase;
        }

        .watermark-anulado {
            position: fixed;
            bottom: 30%;
            left: 20%;
            width: 60%;
            font-size: 80px;
            color: rgba(217, 83, 79, 0.2);
            border: 10px solid rgba(217, 83, 79, 0.2);
            padding: 10px;
            text-align: center;
            font-weight: bold;
            transform: rotate(-30deg);
            z-index: 1000;
            pointer-events: none;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    @if ($codTransaccion == "02")
        <div class="watermark-anulado">
            ANULADO
        </div>
    @endif

    <!-- ENCABEZADO Y NUMERO DE RECIBO -->
    <table width="100%" class="header-table">
        <tr valign="top">
            <td width="55%">
                <table width="100%">
                    <tr>
                        <td>
                            @if(!empty($emisor[0]['ncr']))
                                <img src="{{ logo_pdf($emisor[0]['ncr']) }}" alt="logo" width="140px" style="object-fit: contain; margin-bottom: 5px;">
                            @else
                                <h3 style="color: #2A3F54; margin: 0 0 5px 0;">BETHEL TRAVEL</h3>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; font-weight: bold; color: #2A3F54;">
                            {{ $emisor[0]["nombreComercial"] ?? 'BETHEL TRAVEL SV' }}
                        </td>
                    </tr>
                    <tr style="font-size: 10px; color: #555555;">
                        <td>
                            <strong>NIT:</strong> {{ $emisor[0]["nit"] }} &nbsp;|&nbsp; 
                            <strong>NRC:</strong> {{ $emisor[0]["ncr"] ?? 'N/A' }}<br>
                            <strong>Giro:</strong> {{ $emisor[0]["descActividad"] }}<br>
                            <strong>Dirección:</strong> {{ $emisor[0]["direccion"] }}, 
                            {{ get_name_municipio($emisor[0]['municipio']) }}, {{ get_name_departamento($emisor[0]['departamento']) }}<br>
                            <strong>Teléfono:</strong> {{ $emisor[0]["telefono"] }} &nbsp;|&nbsp; 
                            <strong>Email:</strong> {{ $emisor[0]["correo"] }}
                        </td>
                    </tr>
                </table>
            </td>
            <td width="45%" align="right">
                <div style="width: 280px; text-align: left;" class="cuadro-recibo">
                    <div class="titulo-recibo">Recibo de Ingreso</div>
                    <div class="numero-recibo">No. {{ str_pad($documento[0]['nu_doc'] ?? $documento[0]['id_doc'], 6, '0', STR_PAD_LEFT) }}</div>
                    <hr style="border: 0; border-top: 1px solid #dddddd; margin: 8px 0;">
                    <table width="100%" style="font-size: 10px;">
                        <tr>
                            <td width="40%"><strong>Fecha Emisión:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($documento[0]['fecha_venta'])->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Hora Emisión:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($documento[0]['fechacreacion'])->format('H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Elaborado por:</strong></td>
                            <td>{{ $documento[0]['NombreUsuario'] }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- DATOS DEL CLIENTE / RECEPTOR -->
    <div class="seccion-titulo">Recibimos de (Cliente)</div>
    <table width="100%" style="margin-bottom: 20px;" class="info-table">
        <tr>
            <td width="15%"><strong>Nombre/Razón:</strong></td>
            <td width="45%">{{ $cliente[0]["nombre"] }}</td>
            <td width="15%"><strong>DUI/NIT/Pasaporte:</strong></td>
            <td width="25%">{{ $cliente[0]["numDocumento"] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Dirección:</strong></td>
            <td>
                {{ $cliente[0]["direccion"] ?? 'N/A' }}
                @if(!empty($cliente[0]["municipio"]))
                    , {{ get_name_municipio($cliente[0]['municipio']) }}
                @endif
                @if(!empty($cliente[0]["departamento"]))
                    , {{ get_name_departamento($cliente[0]['departamento']) }}
                @endif
            </td>
            <td><strong>Teléfono:</strong></td>
            <td>{{ $cliente[0]["telefono"] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $cliente[0]["correo"] ?? 'N/A' }}</td>
            <td><strong>Forma de Pago:</strong></td>
            <td>
                @switch($totales['condicionOperacion'])
                    @case(1)
                        CONTADO
                        @break
                    @case(2)
                        CRÉDITO
                        @break
                    @case(3)
                        OTRO
                        @break
                    @default
                        EFECTIVO
                @endswitch
            </td>
        </tr>
    </table>

    <!-- DETALLE DE CONCEPTOS -->
    <table class="detalle-table">
        <thead>
            <tr>
                <th width="8%" align="center">Cant.</th>
                <th width="62%" align="left">Descripción / Concepto</th>
                <th width="15%" align="right">Precio Unit.</th>
                <th width="15%" align="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalle as $prod)
                @php
                    $cant = (float)($prod['cantidad'] ?? 1);
                    $cant = $cant > 0 ? $cant : 1;
                    $ivaUnitario = ((float)($prod['iva'] ?? 0)) / $cant;
                    $precioUnitarioConIva = (float)($prod['precio_unitario'] ?? 0) + $ivaUnitario;
                    $subtotalConIva = (float)($prod['subtotal'] ?? 0) + (float)($prod['iva'] ?? 0);
                @endphp
                <tr>
                    <td align="center">{{ number_format($cant, 0) }}</td>
                    <td align="left">
                        {{ $prod['descripcion'] ?? '' }}
                    </td>
                    <td align="right">$ {{ number_format($precioUnitarioConIva, 2) }}</td>
                    <td align="right">$ {{ number_format($subtotalConIva, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- SECCIÓN DE SUMATORIA Y LETRAS -->
    <table width="100%" style="margin-top: 10px;">
        <tr valign="top">
            <td width="65%">
                <table width="100%">
                    <tr>
                        <td><strong>Cantidad en letras:</strong></td>
                    </tr>
                    <tr>
                        <td class="total-letras">
                            Son: {{ $totales['totalLetras'] }}
                        </td>
                    </tr>
                </table>
            </td>
            <td width="35%" align="right">
                <table style="width: 220px; font-size: 11px;">
                    <tr>
                        <td align="right" style="padding: 5px;"><strong>Subtotal Ventas:</strong></td>
                        <td align="right" style="padding: 5px; width: 90px;">$ {{ number_format($totales['subTotal'] + ($totales['totalIva'] ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr style="border: 0; border-top: 1px solid #cccccc; margin: 5px 0;"></td>
                    </tr>
                    <tr>
                        <td align="right" style="padding: 5px;"><strong>TOTAL RECIBIDO:</strong></td>
                        <td align="right" style="padding: 5px;" class="total-box">
                            $ {{ number_format($totales['totalPagar'], 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- SECCIÓN DE FIRMAS -->
    <table width="100%" style="margin-top: 80px;">
        <tr>
            <td width="50%" align="center">
                <div class="firma-container">
                    <div class="linea-firma"></div>
                    <div class="firma-label">Entregado por (Cliente)</div>
                </div>
            </td>
            <td width="50%" align="center">
                <div class="firma-container">
                    <div class="linea-firma"></div>
                    <div class="firma-label">Recibido por (Caja)</div>
                </div>
            </td>
        </tr>
    </table>

</body>

</html>
