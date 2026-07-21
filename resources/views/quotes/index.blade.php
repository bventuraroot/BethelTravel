@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('.datatables-quotes').DataTable({
                order: [[1, 'desc']], // Sort by date/ID desc
                responsive: true,
                dom: '<"row me-2"<"col-md-2"<"me-3"l>><"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                buttons: [
                    {
                        text: '<i class="ti ti-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Nueva Cotización</span>',
                        className: 'btn btn-primary mx-3',
                        action: function (e, dt, node, config) {
                            window.location.href = "{{ route('quote.create') }}";
                        }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        });

        // Delete confirmation
        function deleteQuote(quoteId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer y eliminará los archivos de imagen asociados.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ea5455',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then(function (result) {
                if (result.value) {
                    window.location.href = "{{ url('quote/destroy') }}/" + quoteId;
                }
            });
        }

        // Open Send Email Modal
        function openSendEmailModal(id, email, title, customerName) {
            $('#sendEmailQuoteId').val(id);
            $('#sendEmailAddress').val(email || '');
            $('#sendEmailSubject').val('Propuesta de Viaje: ' + title);
            
            let defaultBody = 'Hola ' + (customerName || 'Cliente') + ',\n\n';
            defaultBody += 'Es un placer saludarte. Adjuntamos a este correo la propuesta detallada para tu próximo viaje a ' + title + '.\n\n';
            defaultBody += 'Por favor, revisa el documento adjunto con los hoteles cotizados, tarifas e itinerario de vuelos. Quedamos a tu total disposición para realizar cualquier ajuste.\n\n';
            defaultBody += 'Atentamente,\n' + "{{ auth()->user()->name }}";
            
            $('#sendEmailBody').val(defaultBody);
            
            $('#sendEmailModal').modal('show');
        }

        // Submit Send Email AJAX
        function submitSendEmail() {
            const form = $('#sendEmailForm');
            const quoteId = $('#sendEmailQuoteId').val();
            const submitBtn = $('#sendEmailSubmitBtn');
            const spinner = $('#sendEmailSpinner');

            // Validate
            if (!document.getElementById('sendEmailForm').checkValidity()) {
                form.addClass('was-validated');
                return;
            }

            // Show Loading
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');

            $.ajax({
                url: "{{ url('quote/send-email') }}/" + quoteId,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    email: $('#sendEmailAddress').val(),
                    subject: $('#sendEmailSubject').val(),
                    body: $('#sendEmailBody').val()
                },
                success: function(response) {
                    $('#sendEmailModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: '¡Correo Enviado!',
                        text: response.message,
                        customClass: { confirmButton: 'btn btn-success' }
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al enviar el correo.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMsg,
                        customClass: { confirmButton: 'btn btn-danger' }
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        }
    </script>
@endsection

@section('title', 'Cotizaciones')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Producción /</span> Cotizaciones
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Listado de Cotizaciones</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table datatables-quotes border-top">
                <thead>
                    <tr>
                        <th style="width: 100px;">Acciones</th>
                        <th>Nº</th>
                        <th>Destino</th>
                        <th>Fechas</th>
                        <th>Cliente</th>
                        <th>Agente</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotes as $quote)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- Print/View PDF -->
                                    <a href="{{ route('quote.pdf', $quote->id) }}" target="_blank" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light" title="Ver / Descargar PDF">
                                        <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
                                    </a>
                                    
                                    <!-- Send Email -->
                                    <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light" 
                                            onclick="openSendEmailModal({{ $quote->id }}, '{{ $quote->client_email ?: ($quote->client->email ?? '') }}', '{{ addslashes($quote->title) }}', '{{ addslashes($quote->customer_name) }}')"
                                            title="Enviar por Correo">
                                        <i class="fa-solid fa-envelope text-primary fs-5"></i>
                                    </button>

                                    <!-- Convert to Sale -->
                                    @if($quote->status !== 'approved')
                                        <a href="{{ route('quote.convert-to-sale', $quote->id) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light" title="Convertir a Venta">
                                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                                        </a>
                                    @else
                                        <button disabled class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light" title="Venta Realizada">
                                            <i class="fa-solid fa-circle-check text-muted fs-5"></i>
                                        </button>
                                    @endif

                                    <!-- Dots Dropdown Actions -->
                                    <div class="d-inline-block">
                                        <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical fs-5"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end m-0">
                                            <a href="{{ route('quote.edit', $quote->id) }}" class="dropdown-item">
                                                <i class="ti ti-edit me-2"></i>Editar
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a href="javascript:deleteQuote({{ $quote->id }});" class="dropdown-item text-danger">
                                                <i class="ti ti-trash me-2"></i>Eliminar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold">COT-{{ str_pad($quote->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <span class="fw-semibold text-heading">{{ $quote->title }}</span>
                            </td>
                            <td>{{ $quote->subtitle ?: 'N/A' }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $quote->customer_name }}</span>
                                    @if($quote->client_email)
                                        <small class="text-muted">{{ $quote->client_email }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $quote->user->name ?? 'N/A' }}</td>
                            <td>
                                @if($quote->status === 'draft')
                                    <span class="badge bg-label-secondary">Borrador</span>
                                @elseif($quote->status === 'sent')
                                    <span class="badge bg-label-warning">Enviada</span>
                                @elseif($quote->status === 'approved')
                                    <span class="badge bg-label-success">Aprobada</span>
                                @else
                                    <span class="badge bg-label-danger">{{ $quote->status }}</span>
                                @endif
                            </td>
                            <td>{{ $quote->created_at ? $quote->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Enviar Correo -->
    <div class="modal fade" id="sendEmailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enviar Cotización por Correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="sendEmailForm" novalidate>
                        <input type="hidden" id="sendEmailQuoteId">
                        
                        <div class="row">
                            <div class="col mb-3">
                                <label for="sendEmailAddress" class="form-label">Correo del Destinatario</label>
                                <input type="email" id="sendEmailAddress" class="form-label form-control" placeholder="cliente@example.com" required>
                                <div class="invalid-feedback">Por favor ingrese un correo válido.</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="sendEmailSubject" class="form-label">Asunto</label>
                                <input type="text" id="sendEmailSubject" class="form-label form-control" required>
                                <div class="invalid-feedback">El asunto es requerido.</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-0">
                                <label for="sendEmailBody" class="form-label">Mensaje</label>
                                <textarea id="sendEmailBody" class="form-label form-control" rows="8" required></textarea>
                                <div class="invalid-feedback">El mensaje es requerido.</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary d-flex align-items-center" id="sendEmailSubmitBtn" onclick="submitSendEmail()">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="sendEmailSpinner" role="status" aria-hidden="true"></span>
                        Enviar Correo
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
