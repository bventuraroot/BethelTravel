@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // Select2 initialization
            $('.select2').select2();

            // Select2 tags for title/destination selection
            $('.select2-tags').select2({
                tags: true,
                placeholder: 'Seleccione o escriba destino...'
            });

            // Listen to destination title changes to filter hotel lists
            $('#title').on('change', function() {
                const selectedDest = ($(this).val() || '').toUpperCase().trim();
                
                $('.select2-in-hotel').each(function() {
                    const selectEl = $(this);
                    const currentVal = selectEl.val();
                    
                    // Clear and rebuild options
                    selectEl.empty();
                    selectEl.append(new Option('Seleccione o escriba hotel...', ''));
                    
                    allHotels.forEach(hotel => {
                        if (!selectedDest || hotel.destino === selectedDest) {
                            selectEl.append(new Option(hotel.nombre, hotel.nombre));
                        }
                    });
                    
                    // Restore previous value
                    if (currentVal) {
                        if (selectEl.find(`option[value="${currentVal}"]`).length === 0) {
                            selectEl.append(new Option(currentVal, currentVal, true, true));
                        } else {
                            selectEl.val(currentVal);
                        }
                    }
                    selectEl.trigger('change.select2');
                });
            });

            // Flatpickr initialization
            $('.datepicker').flatpickr({
                dateFormat: 'Y-m-d',
                locale: 'es'
            });

            // Toggle prospect fields
            $('#client_id').on('change', function() {
                if ($(this).val() === 'new') {
                    $('#prospect_fields').slideDown();
                    $('#client_name').prop('required', true);
                } else {
                    $('#prospect_fields').slideUp();
                    $('#client_name').prop('required', false);
                }
            });

            // Toggle cards based on quote type
            $('#quote_type').on('change', function() {
                const type = $(this).val();
                if (type === 'package') {
                    $('#hotels-card-container').slideDown();
                    $('#flights-card-container').slideDown();
                    $('#inclusions-card-container').slideDown();
                } else if (type === 'flight') {
                    $('#hotels-card-container').slideUp();
                    $('#flights-card-container').slideDown();
                    $('#inclusions-card-container').slideUp();
                } else if (type === 'hotel') {
                    $('#hotels-card-container').slideDown();
                    $('#flights-card-container').slideUp();
                    $('#inclusions-card-container').slideUp();
                } else if (type === 'service') {
                    $('#hotels-card-container').slideUp();
                    $('#flights-card-container').slideUp();
                    $('#inclusions-card-container').slideDown();
                }
            });

            // Default Medellin values loader
            $('#predefined_destination').on('change', function() {
                if ($(this).val() === 'medellin') {
                    $('#title').val('MEDELLÍN');
                    $('#subtitle').val('DEL 14-19 DE AGOSTO 2026');
                    
                    // Clear and load Medellin inclusions
                    $('#includes-container').html('');
                    const defaultsIncludes = [
                        'Boleto aéreo vía Avianca, con maleta de mano',
                        'Traslados Aeropuerto / Hotel / Aeropuerto en Medellín',
                        '05 noches de alojamiento en Medellín',
                        'Desayunos',
                        'Excursión a Guatapé y el Alto del Chocho + Paseo en barco (Punto de salida: PARQUE DEL POBLADO)'
                    ];
                    defaultsIncludes.forEach(inc => addInclusion(inc));

                    // Load Medellin Hotels Grid
                    loadMedellinHotelsGrid();

                    // Load Medellin flight segments
                    loadMedellinFlights();

                    // Load default Medellin notes
                    $('#notes-container').html('');
                    const defaultsNotes = [
                        'Precios sujetos a cambio sin previo aviso',
                        'Reservaciones sujetas a disponibilidad al momento de reservar en firme'
                    ];
                    defaultsNotes.forEach(note => addNote(note));
                }
            });

            // Add first elements
            addInclusion();
            addNote();
            addFlight();
            
            // Setup default hotel columns
            addHotelColumn('SENCILLA');
            addHotelColumn('DOBLE');
            addHotelColumn('TRIPLE');
            addHotelColumn('NIÑO');
            
            // Add first hotel row
            addHotelRow();
        });

        // 1. Inclusions Logic
        let inclusionIndex = 0;
        function addInclusion(value = '') {
            inclusionIndex++;
            const html = `
                <div class="input-group mb-2 inclusion-item" id="inclusion-group-${inclusionIndex}">
                    <span class="input-group-text"><i class="fa-solid fa-plane-departure text-success"></i></span>
                    <input type="text" name="includes[]" class="form-control" placeholder="Ej. Boleto aéreo vía Avianca, con maleta de mano" value="${value}">
                    <button class="btn btn-outline-danger" type="button" onclick="removeElement('inclusion-group-${inclusionIndex}')"><i class="ti ti-trash"></i></button>
                </div>
            `;
            $('#includes-container').append(html);
        }

        // 2. Notes Logic
        let noteIndex = 0;
        function addNote(value = '') {
            noteIndex++;
            const html = `
                <div class="input-group mb-2 note-item" id="note-group-${noteIndex}">
                    <span class="input-group-text"><i class="fa-solid fa-circle-info text-info"></i></span>
                    <input type="text" name="notes[]" class="form-control" placeholder="Ej. Precios sujetos a cambio sin previo aviso" value="${value}">
                    <button class="btn btn-outline-danger" type="button" onclick="removeElement('note-group-${noteIndex}')"><i class="ti ti-trash"></i></button>
                </div>
            `;
            $('#notes-container').append(html);
        }

        // Generic Element remover
        function removeElement(id) {
            $(`#${id}`).remove();
        }

        // 3. Dynamic Hotel Grid Logic
        let hotelColumns = [];
        let hotelRowIndex = 0;

        function addHotelColumnPrompt() {
            Swal.fire({
                title: 'Nombre de la Columna de Tarifa',
                input: 'text',
                inputPlaceholder: 'Ej. SENCILLA, DOBLE, NIÑO, etc.',
                showCancelButton: true,
                confirmButtonText: 'Añadir',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return '¡Necesitas escribir un nombre!';
                    }
                    if (hotelColumns.includes(value.toUpperCase())) {
                        return 'Esa columna ya existe.';
                    }
                }
            }).then((result) => {
                if (result.value) {
                    addHotelColumn(result.value.toUpperCase());
                }
            });
        }

        function addHotelColumn(colName) {
            if (hotelColumns.includes(colName)) return;
            
            hotelColumns.push(colName);
            
            // Add th header before the action header
            const thHtml = `<th id="th-col-${colName}" class="text-center">${colName}</th>`;
            $('#hotel-grid-headers th:last-child').before(thHtml);
            
            // Add inputs to all existing rows
            $('.hotel-row').each(function() {
                const rowIndex = $(this).data('index');
                const tdHtml = `
                    <td id="td-price-${rowIndex}-${colName}">
                        <input type="number" step="any" name="hotels_grid_prices[${rowIndex}][${colName}]" class="form-control form-control-sm text-center" placeholder="0.00" value="0">
                    </td>
                `;
                $(this).find('td:last-child').before(tdHtml);
            });

            // Update column inputs in main form
            const colInputHtml = `<input type="hidden" name="hotels_grid_cols[]" id="hidden-col-${colName}" value="${colName}">`;
            $('#hidden-columns-container').append(colInputHtml);
        }

        function removeHotelColumn(colName) {
            hotelColumns = hotelColumns.filter(c => c !== colName);
            $(`#th-col-${colName}`).remove();
            $(`[id^="td-price-"][id$="-${colName}"]`).remove();
            $(`#hidden-col-${colName}`).remove();
        }

        // Master list of hotels and their destinations from the database
        const allHotels = [
            @foreach($allHotels as $h)
                { nombre: "{{ addslashes($h->nombre) }}", destino: "{{ strtoupper(addslashes($h->destino)) }}" },
            @endforeach
        ];

        function addHotelRow(hotelName = '', prices = {}) {
            hotelRowIndex++;
            
            // Get currently selected destination
            const selectedDest = ($('#title').val() || '').toUpperCase().trim();
            
            // Generate hotels option html
            let hotelOptions = '<option value="">Seleccione o escriba hotel...</option>';
            allHotels.forEach(hotel => {
                if (!selectedDest || hotel.destino === selectedDest) {
                    hotelOptions += `<option value="${hotel.nombre}">${hotel.nombre}</option>`;
                }
            });
            
            let rowHtml = `
                <tr class="hotel-row" id="hotel-row-${hotelRowIndex}" data-index="${hotelRowIndex}">
                    <td style="min-width: 320px;">
                        <select name="hotels_grid_rows[${hotelRowIndex}]" class="form-select select2-in-hotel" required>
                            ${hotelOptions}
                        </select>
                    </td>
            `;
            
            // Render columns
            hotelColumns.forEach(col => {
                const priceVal = prices[col] !== undefined ? prices[col] : '0';
                rowHtml += `
                    <td id="td-price-${hotelRowIndex}-${col}">
                        <input type="number" step="any" name="hotels_grid_prices[${hotelRowIndex}][${col}]" class="form-control form-control-sm text-center" placeholder="0.00" value="${priceVal}">
                    </td>
                `;
            });
            
            rowHtml += `
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="removeElement('hotel-row-${hotelRowIndex}')">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            $('#hotel-grid-body').append(rowHtml);

            // Initialize select2 with tags on the new input
            const selectEl = $(`#hotel-row-${hotelRowIndex} select[name="hotels_grid_rows[${hotelRowIndex}]"]`);
            selectEl.select2({
                tags: true,
                placeholder: 'Seleccione o escriba hotel...'
            });

            // Set value if passed
            if (hotelName) {
                if (selectEl.find(`option[value="${hotelName}"]`).length === 0) {
                    const newOption = new Option(hotelName, hotelName, true, true);
                    selectEl.append(newOption).trigger('change');
                } else {
                    selectEl.val(hotelName).trigger('change');
                }
            }
        }

        function loadMedellinHotelsGrid() {
            // Reset grid
            $('#hotel-grid-body').html('');
            $('#hotel-grid-headers').html('<th>Hotel / Opción</th><th>Acciones</th>');
            $('#hidden-columns-container').html('');
            hotelColumns = [];
            
            // Set columns
            addHotelColumn('SENCILLA');
            addHotelColumn('DOBLE');
            addHotelColumn('TRIPLE');
            addHotelColumn('NIÑO');
            
            // Set grid title and footer
            $('#hotels_grid_title').val('HOTELES EL POBLADO');
            $('#hotels_grid_footer').val('PRECIO COTIZADO EN BASE A MINIMO 2 PERSONAS, SI VIAJA 2 PASAJERO SOLO AGREGAR $50.00 DE TRASLADOS');

            // Add rows
            addHotelRow('Hotel Sociatel Medellin***', {'SENCILLA': 858, 'DOBLE': 708, 'TRIPLE': 693, 'NIÑO': 573});
            addHotelRow('Loyds Hotel***', {'SENCILLA': 893, 'DOBLE': 733, 'TRIPLE': 728, 'NIÑO': 708});
            addHotelRow('Hotel The Morgana Poblado Suites****', {'SENCILLA': 908, 'DOBLE': 773, 'TRIPLE': 733, 'NIÑO': 643});
            addHotelRow('V Grand Hotel, A Member Of Radisson Individuals****', {'SENCILLA': 1098, 'DOBLE': 878, 'TRIPLE': 799, 'NIÑO': 738});
        }

        // 4. Flight Itinerary Logic
        let flightIndex = 0;
        function addFlight(data = null) {
            flightIndex++;
            
            const airlineCode = data ? data.airline_code : '';
            const flightNumber = data ? data.flight_number : '';
            const originCode = data ? data.origin_code : '';
            const originName = data ? data.origin_name : '';
            const depDate = data ? data.departure_date : '';
            const depTime = data ? data.departure_time : '';
            const destCode = data ? data.destination_code : '';
            const destName = data ? data.destination_name : '';
            const arrDate = data ? data.arrival_date : '';
            const arrTime = data ? data.arrival_time : '';

            // Generate airlines option html
            let airlineOptions = '<option value="">Seleccione aerolínea...</option>';
            @foreach($airlines as $airline)
                airlineOptions += `<option value="{{ $airline->iata }}" \${airlineCode == '{{ $airline->iata }}' ? 'selected' : ''}>{{ $airline->nombre }} ({{ $airline->iata }})</option>`;
            @endforeach

            // Generate airports option html
            let airportOptions = '<option value="">Seleccione aeropuerto...</option>';
            @foreach($airports as $ap)
                airportOptions += `<option value="{{ $ap->iata }}">{{ $ap->ciudad }} - {{ $ap->pais }} ({{ $ap->iata }})</option>`;
            @endforeach

            const html = `
                <div class="card bg-lighter mb-3 flight-item" id="flight-group-${flightIndex}">
                    <div class="card-header pb-2 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-primary"><i class="fa-solid fa-plane me-2"></i>Segmento de Vuelo #${flightIndex}</h6>
                        <button type="button" class="btn btn-sm btn-label-danger" onclick="removeElement('flight-group-${flightIndex}')">
                            <i class="ti ti-trash me-1"></i>Eliminar Tramo
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Aerolínea</label>
                                <select name="flights[${flightIndex}][airline_code]" class="form-select select2-in-flight">
                                    ${airlineOptions}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nº Vuelo</label>
                                <input type="text" name="flights[${flightIndex}][flight_number]" class="form-control" placeholder="Ej. AV 369" value="${flightNumber}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Origen (Aeropuerto / Ciudad)</label>
                                <select name="flights[${flightIndex}][origin_code]" class="form-select select2-in-flight select2-airport-origin">
                                    ${airportOptions}
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Fecha Salida</label>
                                <input type="text" name="flights[${flightIndex}][departure_date]" class="form-control datepicker" placeholder="YYYY-MM-DD" value="${depDate}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Hora Salida</label>
                                <input type="text" name="flights[${flightIndex}][departure_time]" class="form-control" placeholder="Ej. 09:20" value="${depTime}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Destino (Aeropuerto / Ciudad)</label>
                                <select name="flights[${flightIndex}][destination_code]" class="form-select select2-in-flight select2-airport-destination">
                                    ${airportOptions}
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Fecha Llegada</label>
                                <input type="text" name="flights[${flightIndex}][arrival_date]" class="form-control datepicker" placeholder="YYYY-MM-DD" value="${arrDate}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Hora Llegada</label>
                                <input type="text" name="flights[${flightIndex}][arrival_time]" class="form-control" placeholder="Ej. 13:10" value="${arrTime}">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('#flights-container').append(html);
            
            // Set values if passed
            if (originCode) {
                $(`#flight-group-${flightIndex} select[name="flights[${flightIndex}][origin_code]"]`).val(originCode).trigger('change');
            }
            if (destCode) {
                $(`#flight-group-${flightIndex} select[name="flights[${flightIndex}][destination_code]"]`).val(destCode).trigger('change');
            }

            // Re-init flatpickr and select2 on the new inputs
            $(`#flight-group-${flightIndex} .datepicker`).flatpickr({
                dateFormat: 'Y-m-d',
                locale: 'es'
            });
            $(`#flight-group-${flightIndex} .select2-in-flight`).select2({
                placeholder: 'Seleccione opción...'
            });
        }

        function loadMedellinFlights() {
            $('#flights-container').html('');
            
            // Outbound segment
            addFlight({
                airline_code: 'AV',
                flight_number: 'AV 369',
                origin_code: 'SAL',
                origin_name: 'San Salvador (SAL)',
                departure_date: '2026-08-14',
                departure_time: '09:20',
                destination_code: 'MDE',
                destination_name: 'Medellín (MDE)',
                arrival_date: '2026-08-14',
                arrival_time: '13:10'
            });

            // Return segment
            addFlight({
                airline_code: 'AV',
                flight_number: 'AV 370',
                origin_code: 'MDE',
                origin_name: 'Medellín (MDE)',
                departure_date: '2026-08-19',
                departure_time: '14:10',
                destination_code: 'SAL',
                destination_name: 'San Salvador (SAL)',
                arrival_date: '2026-08-19',
                arrival_time: '16:00'
            });
        }
    </script>
@endsection

@section('title', 'Nueva Cotización')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Cotizaciones /</span> Nueva Cotización
    </h4>

    <form action="{{ route('quote.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="company_selected" value="{{ $company_selected }}">

        <!-- Hidden columns container for hotel grid -->
        <div id="hidden-columns-container"></div>

        <div class="row">
            <!-- Left Side: Basic Details -->
            <div class="col-xl-8 col-lg-7">
                
                <!-- Card 1: Main Info -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Información de la Propuesta</h5>
                        <div class="col-md-4">
                            <select id="predefined_destination" class="form-select form-select-sm text-primary border-primary">
                                <option value="">-- Cargar Plantilla Destino --</option>
                                <option value="medellin">Medellín, Colombia</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="quote_type" class="form-label text-warning fw-bold">Tipo de Cotización</label>
                                <select id="quote_type" name="quote_type" class="form-select border-warning">
                                    <option value="package" selected>Paquete Completo</option>
                                    <option value="flight">Solo Vuelo / Segmento Aéreo</option>
                                    <option value="hotel">Solo Hotel / Alojamiento</option>
                                    <option value="service">Solo Traslados / Servicios de Asistencia</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="title" class="form-label text-primary fw-bold">Destino / Título Principal</label>
                                <select id="title" name="title" class="select2-tags form-select" required>
                                    <option value="">-- Seleccione o escriba destino --</option>
                                    @foreach($airports as $ap)
                                        <option value="{{ strtoupper($ap->ciudad) }}">{{ $ap->ciudad }} ({{ $ap->pais }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label for="subtitle" class="form-label">Fechas / Rango de Vigencia</label>
                                <input type="text" id="subtitle" name="subtitle" class="form-control" placeholder="Ej. DEL 14-19 DE AGOSTO 2026">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Imágenes de Banner (Máximo 3)</label>
                                <input type="file" name="banner_images[]" class="form-control" multiple accept="image/*">
                                <small class="text-muted">Si seleccionas la plantilla Medellín, el sistema cargará automáticamente las 3 imágenes premium por defecto.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Hotels Pricing Grid -->
                <div class="card mb-4" id="hotels-card-container">
                    <div class="card-header d-flex justify-content-between align-items-center pb-2">
                        <h5 class="mb-0">Tabla de Tarifas de Hotel</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="addHotelColumnPrompt()">
                                <i class="ti ti-plus me-1"></i>Añadir Columna
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addHotelRow()">
                                <i class="ti ti-plus me-1"></i>Añadir Fila de Hotel
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="hotels_grid_title" class="form-label">Categoría o Ubicación de Hoteles</label>
                                <input type="text" id="hotels_grid_title" name="hotels_grid_title" class="form-control form-control-sm" placeholder="Ej. HOTELES EL POBLADO" value="HOTELES Y TARIFAS">
                            </div>
                            <div class="col-md-6">
                                <label for="hotels_grid_footer" class="form-label">Nota de pie de tabla</label>
                                <input type="text" id="hotels_grid_footer" name="hotels_grid_footer" class="form-control form-control-sm" placeholder="Ej. Precios cotizados en base a mínimo 2 personas..." value="">
                            </div>
                        </div>

                        <div class="table-responsive text-nowrap border rounded">
                            <table class="table table-sm">
                                <thead>
                                    <tr id="hotel-grid-headers">
                                        <th style="min-width: 320px;">Hotel / Opción</th>
                                        <!-- Columns injected here -->
                                        <th class="text-center" style="width: 80px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="hotel-grid-body">
                                    <!-- Rows injected here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Flights Itinerary -->
                <div class="card mb-4" id="flights-card-container">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Itinerario de Vuelos</h5>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addFlight()">
                            <i class="ti ti-plus me-1"></i>Añadir Tramo Aéreo
                        </button>
                    </div>
                    <div class="card-body" id="flights-container">
                        <!-- Flight segments injected here -->
                    </div>
                </div>

            </div>

            <!-- Right Side: Client selection, includes & notes -->
            <div class="col-xl-4 col-lg-5">
                
                <!-- Card 4: Client Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="client_id" class="form-label">Seleccionar Cliente</label>
                                <select id="client_id" name="client_id" class="select2 form-select" data-allow-clear="true">
                                    <option value="">Seleccione cliente registrado...</option>
                                    <option value="new" class="text-primary fw-bold">+ Registrar Prospecto / Nuevo Cliente</option>
                                    @foreach($clients as $c)
                                        @php
                                            $name = trim(($c->firstname ?? '') . ' ' . ($c->firstlastname ?? ''));
                                            if (empty($name)) $name = $c->name_contribuyente;
                                        @endphp
                                        <option value="{{ $c->id }}">{{ $name }} ({{ $c->nit }})</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Prospect manual inputs (hidden by default) -->
                            <div id="prospect_fields" style="display: none;" class="col-12 border-top pt-3 mt-3">
                                <div class="mb-3">
                                    <label for="client_name" class="form-label">Nombre del Cliente Potencial</label>
                                    <input type="text" id="client_name" name="client_name" class="form-control" placeholder="Ej. Juan Pérez">
                                </div>
                                <div class="mb-3">
                                    <label for="client_email" class="form-label">Correo Electrónico</label>
                                    <input type="email" id="client_email" name="client_email" class="form-control" placeholder="juan.perez@example.com">
                                </div>
                                <div class="mb-3">
                                    <label for="client_phone" class="form-label">Teléfono</label>
                                    <input type="text" id="client_phone" name="client_phone" class="form-control" placeholder="7777-7777">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 5: Inclusions -->
                <div class="card mb-4" id="inclusions-card-container">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">El Paquete Incluye</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-primary" onclick="addInclusion()" title="Añadir viñeta">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>
                    <div class="card-body" id="includes-container">
                        <!-- Inclusions items -->
                    </div>
                </div>

                <!-- Card 6: Notes & Conditions -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Importante / Condiciones</h5>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-primary" onclick="addNote()" title="Añadir condición">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>
                    <div class="card-body" id="notes-container">
                        <!-- Conditions items -->
                    </div>
                </div>

                <!-- Submit Button Area -->
                <div class="card mb-4 bg-transparent border-0 shadow-none">
                    <div class="card-body p-0">
                        <button type="submit" class="btn btn-success w-100 py-3 mb-2">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Guardar Cotización
                        </button>
                        <a href="{{ route('quote.index') }}" class="btn btn-label-secondary w-100">Cancelar</a>
                    </div>
                </div>

            </div>
        </div>
    </form>
@endsection
