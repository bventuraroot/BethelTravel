@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // Select2 initialization
            $('.select2').select2();

            // Toggle prospect fields
            $('#client_selection').on('change', function() {
                if ($(this).val() === 'new') {
                    $('#new_client_fields').slideDown();
                    $('#new_firstname').prop('required', true);
                    $('#new_firstlastname').prop('required', true);
                    $('#new_nit').prop('required', true);
                } else {
                    $('#new_client_fields').slideUp();
                    $('#new_firstname').prop('required', false);
                    $('#new_firstlastname').prop('required', false);
                    $('#new_nit').prop('required', false);
                }
            });

            // Handle hotel price selection click
            $('.price-select-cell').on('click', function() {
                // Remove active class from all cells
                $('.price-select-cell').removeClass('table-primary border-primary bg-primary text-white');
                $('.price-select-cell').addClass('bg-lightCursor');
                
                // Add active class to clicked cell
                $(this).addClass('table-primary border-primary bg-primary text-white');
                $(this).removeClass('bg-lightCursor');
                
                // Set hidden inputs
                const hotel = $(this).data('hotel');
                const occupancy = $(this).data('occupancy');
                const price = $(this).data('price');
                
                $('#selected_hotel').val(hotel);
                $('#selected_occupancy').val(occupancy);
                $('#selected_price').val(price);
                
                // Update summary text
                $('#selection-summary').html(`
                    <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                        <span class="alert-icon text-primary me-2">
                            <i class="ti ti-info-circle ti-xs"></i>
                        </span>
                        <div>
                            <strong>Tarifa Seleccionada:</strong> ${hotel} &mdash; Habitación <strong>${occupancy}</strong> por un valor de <strong>$${parseFloat(price).toFixed(2)}</strong>.
                        </div>
                    </div>
                `);
                
                $('#submit-btn').prop('disabled', false);
            });
        });
    </script>
    <style>
        .price-select-cell {
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .price-select-cell:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .bg-lightCursor {
            background-color: #fafbfc;
        }
    </style>
@endsection

@section('title', 'Convertir Cotización a Venta')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Cotizaciones /</span> Convertir a Venta
    </h4>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('quote.store-convert-to-sale', $quote->id) }}" method="POST" id="convertForm">
        @csrf
        
        <input type="hidden" name="selected_hotel" id="selected_hotel">
        <input type="hidden" name="selected_occupancy" id="selected_occupancy">
        <input type="hidden" name="selected_price" id="selected_price">

        <div class="row">
            <!-- Left Side: Pricing Grid Selection & Product mapping -->
            <div class="col-xl-8 col-lg-7">
                
                <!-- Card 1: Select pricing option -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">1. Seleccione la Tarifa Comprada por el Cliente</h5>
                        <small class="text-muted">Haga clic en la casilla de la tarifa correspondiente para seleccionarla.</small>
                    </div>
                    <div class="card-body">
                        @if(!empty($quote->hotels_grid) && !empty($quote->hotels_grid['rows']))
                            <div class="table-responsive text-nowrap border rounded mb-3">
                                <table class="table table-bordered table-sm text-center align-middle">
                                    <thead>
                                        <tr class="table-dark">
                                            <th class="text-start">{{ $quote->hotels_grid['title'] ?? 'Hotel / Opción' }}</th>
                                            @foreach($quote->hotels_grid['columns'] as $col)
                                                <th>{{ $col }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($quote->hotels_grid['rows'] as $row)
                                            <tr>
                                                <td class="text-start fw-bold text-heading">{{ $row['hotel'] }}</td>
                                                @foreach($quote->hotels_grid['columns'] as $col)
                                                    @php
                                                        $priceVal = $row['prices'][$col] ?? 0;
                                                    @endphp
                                                    <td class="price-select-cell bg-lightCursor fw-bold" 
                                                        data-hotel="{{ $row['hotel'] }}" 
                                                        data-occupancy="{{ $col }}" 
                                                        data-price="{{ $priceVal }}">
                                                        ${{ number_format($priceVal, 2) }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">No hay tarifas definidas en esta cotización.</div>
                        @endif

                        <div id="selection-summary" class="mt-3">
                            <div class="alert alert-warning mb-0">Por favor, seleccione una tarifa en la tabla superior para poder continuar.</div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Product Catalog Mapping -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">2. Mapeo de Catálogo de Productos</h5>
                        <small class="text-muted">Seleccione qué producto del sistema representará este detalle de venta.</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <label for="product_id" class="form-label">Producto en Catálogo</label>
                                <select id="product_id" name="product_id" class="select2 form-select" required>
                                    <option value="">Seleccione producto...</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" {{ $p->code == 'PAQ' || str_contains(strtolower($p->name), 'paquete') ? 'selected' : '' }}>
                                            {{ $p->name }} ({{ $p->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Side: Client selection / Registration & Submit -->
            <div class="col-xl-4 col-lg-5">
                
                <!-- Card 3: Client Mapping -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">3. Cliente de la Venta</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="client_selection" class="form-label">Asociar Cliente</label>
                            <select id="client_selection" name="client_selection" class="select2 form-select" required>
                                <option value="">Seleccione cliente...</option>
                                @if(is_null($quote->client_id) && !empty($quote->client_name))
                                    <option value="new" selected class="text-primary fw-bold">+ Registrar "{{ $quote->client_name }}" como nuevo cliente</option>
                                @else
                                    <option value="new" class="text-primary fw-bold">+ Registrar como nuevo cliente...</option>
                                @endif
                                
                                @foreach($clients as $c)
                                    @php
                                        $name = trim(($c->firstname ?? '') . ' ' . ($c->firstlastname ?? ''));
                                        if (empty($name)) $name = $c->name_contribuyente;
                                    @endphp
                                    <option value="{{ $c->id }}" {{ $quote->client_id == $c->id ? 'selected' : '' }}>
                                        {{ $name }} ({{ $c->nit }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Add new client fields (visible if "new" is selected) -->
                        <div id="new_client_fields" style="display: {{ is_null($quote->client_id) && !empty($quote->client_name) ? 'block' : 'none' }};" class="border-top pt-3 mt-3">
                            <h6 class="mb-3 text-warning">Registrar Nuevo Cliente</h6>
                            
                            @php
                                // Split name if possible
                                $nameParts = explode(' ', trim($quote->client_name ?? ''));
                                $firstName = $nameParts[0] ?? '';
                                $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
                            @endphp

                            <div class="mb-3">
                                <label for="new_firstname" class="form-label">Nombres</label>
                                <input type="text" id="new_firstname" name="new_firstname" class="form-control" placeholder="Ej. Juan" value="{{ $firstName }}">
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_firstlastname" class="form-label">Primer Apellido</label>
                                <input type="text" id="new_firstlastname" name="new_firstlastname" class="form-control" placeholder="Ej. Pérez" value="{{ $lastName }}">
                            </div>

                            <div class="mb-3">
                                <label for="new_tpersona" class="form-label">Tipo Persona</label>
                                <select id="new_tpersona" name="new_tpersona" class="form-select">
                                    <option value="N" selected>Natural</option>
                                    <option value="J">Jurídica</option>
                                    <option value="E">Extranjero</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="new_nit" class="form-label">Documento (NIT/DUI/DTE ID)</label>
                                <input type="text" id="new_nit" name="new_nit" class="form-control" placeholder="000000000" value="000000000">
                            </div>

                            <div class="mb-3">
                                <label for="new_email" class="form-label">Correo Electrónico</label>
                                <input type="email" id="new_email" name="new_email" class="form-control" placeholder="juan@example.com" value="{{ $quote->client_email }}">
                            </div>

                            <div class="mb-3">
                                <label for="new_phone" class="form-label">Teléfono</label>
                                <input type="text" id="new_phone" name="new_phone" class="form-control" placeholder="7777-7777" value="{{ $quote->client_phone }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Area -->
                <div class="card bg-transparent border-0 shadow-none">
                    <div class="card-body p-0">
                        <button type="submit" class="btn btn-success w-100 py-3 mb-2" id="submit-btn" disabled>
                            <i class="fa-solid fa-circle-check me-2"></i>Confirmar y Crear Venta
                        </button>
                        <a href="{{ route('quote.index') }}" class="btn btn-label-secondary w-100">Cancelar</a>
                    </div>
                </div>

            </div>
        </div>
    </form>
@endsection
