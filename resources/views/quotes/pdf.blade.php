<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cotización - {{ $quote->title }}</title>
    <style type="text/css">
        @page {
            margin: 30px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 12.5px;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .logo-td {
            width: 50%;
            vertical-align: middle;
        }
        .logo-img {
            max-height: 50px;
            max-width: 230px;
        }
        .agency-info-td {
            width: 50%;
            text-align: right;
            font-size: 10.5px;
            color: #555555;
            vertical-align: middle;
        }
        .agency-name {
            font-weight: bold;
            font-size: 12.5px;
            color: #1e3a8a;
            margin-bottom: 2px;
        }
        /* Banner Images Table */
        .banner-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .banner-td {
            width: 33.33%;
            padding: 0 4px;
        }
        .banner-img {
            width: 100%;
            height: 95px;
            border-radius: 6px;
            object-fit: cover;
        }
        /* Title Block */
        .title-block {
            text-align: center;
            margin-bottom: 12px;
        }
        .dest-title {
            font-size: 28px;
            font-weight: 900;
            color: #f59e0b; /* Orange/Gold accent */
            letter-spacing: 4px;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            font-family: 'Arial Black', Gadget, sans-serif;
        }
        .dates-subtitle {
            font-size: 13px;
            font-weight: bold;
            color: #e11d48; /* Red/Crimson accent */
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        /* Package Includes */
        .section-title {
            font-size: 13.5px;
            font-weight: bold;
            color: #333333;
            margin: 12px 0 6px 0;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
            page-break-after: avoid;
        }
        .includes-list {
            margin: 0;
            padding: 0;
            list-style: none;
            page-break-inside: avoid;
        }
        .includes-item {
            position: relative;
            padding-left: 20px;
            margin-bottom: 5px;
            font-size: 12px;
        }
        .includes-icon {
            position: absolute;
            left: 0;
            top: 2px;
            color: #10b981; /* Green color for airplane/checkmark */
            font-size: 10px;
        }
        /* Pricing Table */
        .price-section-title {
            text-align: center;
            font-weight: bold;
            color: #e11d48; /* Red */
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 15px 0 6px 0;
            page-break-after: avoid;
        }
        .price-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            border-radius: 6px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .price-table th {
            background-color: #1e3a8a; /* Deep Navy Blue */
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10.5px;
            padding: 7px 9px;
            border: 1px solid #1e3a8a;
            text-align: center;
        }
        .price-table th.hotel-header {
            text-align: left;
        }
        .price-table td {
            padding: 7px 9px;
            border: 1px solid #e5e7eb;
            font-size: 11.5px;
        }
        .price-table td.hotel-name {
            font-weight: bold;
            color: #4b5563;
        }
        .price-table td.price-cell {
            text-align: center;
            font-weight: bold;
            color: #1f2937;
        }
        .price-table tr {
            page-break-inside: avoid;
        }
        .price-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .price-footer-note {
            font-size: 9.5px;
            font-weight: bold;
            color: #1d4ed8; /* Blue accent */
            text-align: center;
            margin-top: 4px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        /* Flight Itinerary */
        .flight-container {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }
        .flight-card {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px;
            background-color: #fafafa;
            page-break-inside: avoid;
        }
        .flight-card-table {
            width: 100%;
            border-collapse: collapse;
        }
        .airline-badge {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 3px 6px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px;
            display: inline-block;
            margin-right: 5px;
        }
        .flight-header {
            font-size: 11px;
            font-weight: bold;
            color: #4b5563;
            margin-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 4px;
        }
        .airport-code {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
        }
        .airport-name {
            font-size: 11px;
            color: #6b7280;
        }
        .flight-time {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
        }
        .flight-date {
            font-size: 11px;
            color: #6b7280;
        }
        .flight-separator {
            text-align: center;
            color: #9ca3af;
            font-size: 16px;
            vertical-align: middle;
            width: 40px;
        }
        .flight-details-td {
            padding: 5px 0;
        }
        /* Important Notes */
        .notes-list {
            margin: 0;
            padding: 0;
            list-style: none;
            page-break-inside: avoid;
        }
        .notes-item {
            font-size: 11px;
            color: #4b5563;
            margin-bottom: 4px;
            padding-left: 10px;
            position: relative;
        }
        .notes-item::before {
            content: "-";
            position: absolute;
            left: 0;
            color: #9ca3af;
        }
        /* Client Information Info */
        .client-info-table {
            width: 100%;
            margin-bottom: 15px;
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 6px;
            padding: 8px 12px;
        }
        .client-info-table td {
            font-size: 11.5px;
            color: #475569;
        }
        .client-label {
            font-weight: bold;
            color: #334155;
        }
    </style>
</head>
<body>

    <!-- Header Table with Logo and Agency details -->
    <table class="header-table">
        <tr>
            <td class="logo-td">
                @if(!empty($logoPath) && file_exists($logoPath))
                    <img src="{{ $logoPath }}" class="logo-img" alt="Logo">
                @else
                    <span style="font-size: 22px; font-weight: bold; color: #1e3a8a;">{{ $quote->company->name ?? 'BETHEL TRAVEL' }}</span>
                @endif
            </td>
            <td class="agency-info-td">
                <div class="agency-name">{{ $quote->company->name ?? 'Bethel Travel' }}</div>
                <div>NIT: {{ $quote->company->nit ?? 'N/A' }} | NCR: {{ $quote->company->ncr ?? 'N/A' }}</div>
                <div>Email: {{ $quote->company->email ?? 'N/A' }}</div>
                <div>Agente: {{ $quote->user->name ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <!-- Banner Images (3 Column layout) -->
    @if(!empty($bannerImagesPaths))
        <table class="banner-table">
            <tr>
                @foreach($bannerImagesPaths as $path)
                    <td class="banner-td">
                        @if(!empty($path) && file_exists($path))
                            <img src="{{ $path }}" class="banner-img" alt="Landscape">
                        @else
                            <div style="width: 100%; height: 120px; background-color: #e5e7eb; border-radius: 6px; text-align: center; line-height: 120px; color: #9ca3af; font-size: 11px;">Imagen no disponible</div>
                        @endif
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <!-- Destination & Dates -->
    <div class="title-block">
        <h1 class="dest-title">{{ $quote->title }}</h1>
        @if($quote->subtitle)
            <h2 class="dates-subtitle">{{ $quote->subtitle }}</h2>
        @endif
    </div>

    <!-- Client Info -->
    <table class="client-info-table">
        <tr>
            <td style="width: 50%;">
                <span class="client-label">Preparado para:</span> {{ $quote->customer_name }}
            </td>
            <td style="width: 50%; text-align: right;">
                <span class="client-label">Fecha de Cotización:</span> {{ $quote->created_at ? $quote->created_at->format('d/m/Y') : date('d/m/Y') }}
            </td>
        </tr>
        @if($quote->client_email || $quote->client_phone)
            <tr>
                <td colspan="2" style="padding-top: 4px;">
                    @if($quote->client_email) <span class="client-label">Email:</span> {{ $quote->client_email }} @endif
                    @if($quote->client_phone) &nbsp;|&nbsp; <span class="client-label">Teléfono:</span> {{ $quote->client_phone }} @endif
                </td>
            </tr>
        @endif
    </table>

    <!-- Inclusions -->
    @if(!empty($quote->includes))
        <div class="section-title">Paquete Incluye:</div>
        <ul class="includes-list">
            @foreach($quote->includes as $item)
                <li class="includes-item">
                    <span class="includes-icon">✈</span>
                    {{ $item }}
                </li>
            @endforeach
        </ul>
    @endif

    <!-- Prices Grid -->
    @if(!empty($quote->hotels_grid) && !empty($quote->hotels_grid['rows']))
        <div class="price-section-title">PRECIOS POR PERSONA EN DOLARES</div>
        
        <table class="price-table">
            <thead>
                <tr>
                    <th class="hotel-header">{{ $quote->hotels_grid['title'] ?? 'HOTELES Y TARIFAS' }}</th>
                    @foreach($quote->hotels_grid['columns'] as $col)
                        <th>{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($quote->hotels_grid['rows'] as $row)
                    <tr>
                        <td class="hotel-name">{{ $row['hotel'] ?? 'N/A' }}</td>
                        @foreach($quote->hotels_grid['columns'] as $col)
                            @php
                                $priceVal = $row['prices'][$col] ?? '';
                                $formattedPrice = is_numeric($priceVal) ? '$' . number_format($priceVal, 0) : $priceVal;
                            @endphp
                            <td class="price-cell">{{ $formattedPrice }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        @if(!empty($quote->hotels_grid['footer']))
            <div class="price-footer-note">{{ $quote->hotels_grid['footer'] }}</div>
        @endif
    @endif

    <!-- Flight Itinerary -->
    @if(!empty($quote->flights))
        <div class="section-title">Itinerario de vuelo:</div>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 5px; page-break-inside: avoid;">
            <tbody>
                @foreach($quote->flights as $flight)
                    <tr style="page-break-inside: avoid; border-bottom: 1px solid #e5e7eb;">
                        <!-- Logo & Flight Number -->
                        <td style="width: 20%; vertical-align: middle; padding: 6px 0;">
                            <div style="font-weight: bold; color: #1e3a8a; font-size: 11px;">{{ $flight['airline_name'] ?? ($flight['airline_code'] ?? 'AÉREO') }}</div>
                            <div style="font-size: 10px; color: #4b5563; font-weight: bold; margin-top: 2px;">{{ $flight['airline_code'] ?? '' }} {{ $flight['flight_number'] ?? '' }}</div>
                        </td>
                        
                        <!-- Departure Info -->
                        <td style="width: 35%; vertical-align: middle; padding: 6px 10px;">
                            <div style="font-size: 11.5px; font-weight: bold; color: #333333;">
                                {{ $flight['origin_name'] ?? '' }} ({{ $flight['origin_code'] ?? '' }})
                            </div>
                            <div style="font-size: 10.5px; color: #6b7280; margin-top: 1px;">
                                {{ !empty($flight['departure_date']) ? \Carbon\Carbon::parse($flight['departure_date'])->locale('es')->isoFormat('ddd, D MMM') : '' }} | <strong style="color: #111827;">{{ $flight['departure_time'] ?? '' }}</strong>
                            </div>
                        </td>
                        
                        <!-- Arrow -->
                        <td style="width: 10%; text-align: center; vertical-align: middle; color: #9ca3af; font-size: 16px; font-weight: bold;">
                            &gt;
                        </td>
                        
                        <!-- Arrival Info -->
                        <td style="width: 35%; vertical-align: middle; padding: 6px 10px; text-align: right;">
                            <div style="font-size: 11.5px; font-weight: bold; color: #333333;">
                                {{ $flight['destination_name'] ?? '' }} ({{ $flight['destination_code'] ?? '' }})
                            </div>
                            <div style="font-size: 10.5px; color: #6b7280; margin-top: 1px;">
                                {{ !empty($flight['arrival_date']) ? \Carbon\Carbon::parse($flight['arrival_date'])->locale('es')->isoFormat('ddd, D MMM') : '' }} | <strong style="color: #111827;">{{ $flight['arrival_time'] ?? '' }}</strong>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Important / Notes -->
    @if(!empty($quote->notes))
        <div class="section-title" style="margin-top: 15px;">Importante:</div>
        <ul class="notes-list">
            @foreach($quote->notes as $note)
                <li class="notes-item">{{ $note }}</li>
            @endforeach
        </ul>
    @endif

</body>
</html>
