@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('title', 'Control de Reservas y Prechequeo')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Servicios /</span> Control de Reservas y Prechequeo
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="nav-align-top mb-4">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-control" aria-controls="tab-control" aria-selected="true">
                            <i class="tf-icons ti ti-plane-departure ti-xs me-1"></i> Control de Reservas
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-config" aria-controls="tab-config" aria-selected="false">
                            <i class="tf-icons ti ti-mail ti-xs me-1"></i> Parametrización de Correo
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <!-- Pestaña 1: Control de Prechequeos -->
                    <div class="tab-pane fade show active" id="tab-control" role="tabpanel">
                        <!-- Filtros -->
                        <div class="card mb-4 shadow-none border-bottom rounded-0">
                            <div class="card-body p-0 pb-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label" for="filter-company">Empresa</label>
                                        <select id="filter-company" class="form-select">
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ $company->id == $selectedCompanyId ? 'selected' : '' }}>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="filter-status">Estado de Prechequeo</label>
                                        <select id="filter-status" class="form-select">
                                            <option value="todos">Todos</option>
                                            <option value="pendiente" selected>Pendiente</option>
                                            <option value="realizado">Realizado</option>
                                            <option value="no_requerido">No requerido</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="filter-date-from">Viaje Desde</label>
                                        <input type="date" id="filter-date-from" class="form-control" />
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="filter-date-to">Viaje Hasta</label>
                                        <input type="date" id="filter-date-to" class="form-control" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover border-top" id="table-reservas">
                                <thead>
                                    <tr>
                                        <th>Alerta</th>
                                        <th>Pasajero</th>
                                        <th>Aerolínea / Ruta</th>
                                        <th>Reserva #</th>
                                        <th>Fecha Viaje</th>
                                        <th>Estado Prechequeo</th>
                                        <th>Alerta Correo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Cargado vía AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pestaña 2: Parametrización de Correo -->
                    <div class="tab-pane fade" id="tab-config" role="tabpanel">
                        <form action="{{ route('precheckin.config') }}" method="POST" id="form-config">
                            @csrf
                            <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">
                            
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <div class="card shadow-none border p-3">
                                        <h5 class="card-header p-0 pb-3">Plantilla del Correo de Alerta</h5>
                                        
                                        <div class="mb-3">
                                            <label class="form-label" for="config-asunto">Asunto del Correo</label>
                                            <input type="text" id="config-asunto" name="asunto" class="form-control" value="{{ $config->asunto }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="config-cuerpo">Cuerpo del Mensaje (Soporta saltos de línea y HTML básico)</label>
                                            <textarea id="config-cuerpo" name="cuerpo" rows="12" class="form-control" required>{{ $config->cuerpo }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card shadow-none border p-3 mb-4">
                                        <h5 class="card-header p-0 pb-3">Configuración de Envío</h5>

                                        <div class="mb-3">
                                            <label class="form-label" for="config-dias">Días de Anticipación para Alerta</label>
                                            <input type="number" id="config-dias" name="dias_antes" class="form-control" value="{{ $config->dias_antes }}" min="1" max="30" required>
                                            <small class="text-muted">Enviar correos y mostrar alertas visuales N días antes del vuelo.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="config-email-agencia">Correos de la Agencia (Copias)</label>
                                            <input type="text" id="config-email-agencia" name="email_agencia" class="form-control" value="{{ $config->email_agencia }}" placeholder="agencia@correo.com, copias@correo.com">
                                            <small class="text-muted">Separar por comas si son varios correos.</small>
                                        </div>

                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="config-enviar-cliente" name="enviar_cliente" value="1" {{ $config->enviar_cliente ? 'checked' : '' }}>
                                            <label class="form-check-label" for="config-enviar-cliente">Enviar correo al Pasajero</label>
                                        </div>

                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="config-active" name="active" value="1" {{ $config->active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="config-active">Alertas de Prechequeo Activas</label>
                                        </div>
                                    </div>

                                    <div class="card shadow-none border p-3">
                                        <h5 class="card-header p-0 pb-3">Comodines Dinámicos</h5>
                                        <p class="small text-muted">Use estos textos dentro del Asunto o Cuerpo para que se reemplacen dinámicamente:</p>
                                        <ul class="list-group list-group-flush small">
                                            <li class="list-group-item px-0 py-2"><code>{cliente}</code> : Nombre completo del cliente o contribuyente.</li>
                                            <li class="list-group-item px-0 py-2"><code>{reserva}</code> : Número/código de reserva del boleto.</li>
                                            <li class="list-group-item px-0 py-2"><code>{aerolinea}</code> : Nombre de la aerolínea encargada.</li>
                                            <li class="list-group-item px-0 py-2"><code>{ruta}</code> : Ruta del viaje ingresada (ej. SAL JFK SAL).</li>
                                            <li class="list-group-item px-0 py-2"><code>{fecha_viaje}</code> : Fecha programada de salida del vuelo (dd/mm/aaaa).</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i> Guardar Parametrización
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Cambiar Estado y Editar Reserva -->
    <div class="modal fade" id="modalUpdateReserva" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3 p-md-5">
                <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="mb-4 text-center">
                        <h3 class="mb-2">Gestionar Prechequeo</h3>
                        <p class="text-muted">Reserva: <span id="modal-title-reserva" class="fw-bold"></span></p>
                    </div>
                    <form id="formUpdateReserva" class="row">
                        @csrf
                        <input type="hidden" id="modal-detail-id">
                        
                        <div class="col-12 mb-3">
                            <label class="form-label" for="modal-status">Estado del Prechequeo</label>
                            <select id="modal-status" class="form-select" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="realizado">Realizado</option>
                                <option value="no_requerido">No requerido</option>
                            </select>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label" for="modal-fecha-viaje">Ajustar Fecha de Viaje</label>
                            <input type="date" id="modal-fecha-viaje" class="form-control">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label" for="modal-notes">Observaciones / Notas del Prechequeo</label>
                            <textarea id="modal-notes" class="form-control" rows="3" placeholder="Añada información sobre la maleta de mano, pase de abordar, etc."></textarea>
                        </div>

                        <div class="text-center col-12 mt-4">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Actualizar</button>
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript interactivos -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar filtros y datatables
            const filterCompany = document.getElementById('filter-company');
            const filterStatus = document.getElementById('filter-status');
            const filterDateFrom = document.getElementById('filter-date-from');
            const filterDateTo = document.getElementById('filter-date-to');
            const formConfig = document.getElementById('form-config');

            // Cambiar empresa recarga la vista con la empresa seleccionada
            filterCompany.addEventListener('change', function () {
                window.location.href = "{{ route('precheckin.index') }}?company_id=" + this.value;
            });

            // Datatable
            const table = $('#table-reservas').DataTable({
                processing: true,
                serverSide: false, // Usaremos Ajax local sobre los datos devueltos
                ajax: {
                    url: "{{ route('precheckin.data') }}",
                    data: function (d) {
                        d.company_id = filterCompany.value;
                        d.status = filterStatus.value;
                        d.date_from = filterDateFrom.value;
                        d.date_to = filterDateTo.value;
                    }
                },
                columns: [
                    { 
                        data: 'is_alert',
                        orderable: false,
                        render: function (data, type, row) {
                            if (data) {
                                let days = row.days_remaining;
                                let msg = days < 0 ? `Vuelo vencido hace ${Math.abs(days)} días` : `¡Viaja en ${days} días!`;
                                return `<span class="badge bg-danger" title="${msg}"><i class="ti ti-alert-triangle"></i> Urgente</span>`;
                            }
                            return `<span class="badge bg-light text-muted"><i class="ti ti-circle-check"></i> Normal</span>`;
                        }
                    },
                    { data: 'client_name' },
                    { 
                        data: 'airline_name',
                        render: function(data, type, row) {
                            return `<strong>${data}</strong><br><small class="text-muted">${row.ruta} &rarr; ${row.destination}</small>`;
                        }
                    },
                    { 
                        data: 'reserva',
                        render: function (data) {
                            return `<span class="badge bg-label-primary font-monospace">${data}</span>`;
                        }
                    },
                    { data: 'fecha_viaje' },
                    { 
                        data: 'precheckin_status',
                        render: function (data, type, row) {
                            let badge = 'bg-label-warning';
                            let text = 'Pendiente';
                            if (data === 'realizado') {
                                badge = 'bg-label-success';
                                text = 'Realizado';
                                if (row.precheckin_completed_at) {
                                    text += ` (${row.precheckin_completed_at.split(' ')[0]})`;
                                }
                            } else if (data === 'no_requerido') {
                                badge = 'bg-label-secondary';
                                text = 'No requerido';
                            }
                            return `<span class="badge ${badge}">${text}</span>`;
                        }
                    },
                    { 
                        data: 'precheckin_email_sent',
                        render: function (data, type, row) {
                            if (data) {
                                return `<span class="text-success"><i class="ti ti-mail-check"></i> Enviado</span><br><small class="text-muted">${row.precheckin_email_sent_at}</small>`;
                            }
                            return `<span class="text-warning"><i class="ti ti-mail-x"></i> No enviado</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function (data, type, row) {
                            let actionBtns = `
                                <div class="d-flex align-items-center gap-1">
                                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill btn-edit" title="Gestionar Prechequeo">
                                        <i class="ti ti-edit ti-sm text-primary"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill btn-email" title="Enviar correo manual">
                                        <i class="ti ti-mail ti-sm text-success"></i>
                                    </button>
                                </div>
                            `;
                            return actionBtns;
                        }
                    }
                ],
                order: [[4, 'asc']], // ordenar por fecha de viaje
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });

            // Recargar tabla al cambiar filtros locales
            $('#filter-status, #filter-date-from, #filter-date-to').on('change', function () {
                table.ajax.reload();
            });

            // Evento: Abrir Modal de Edición
            $('#table-reservas tbody').on('click', '.btn-edit', function () {
                const data = table.row($(this).parents('tr')).data();
                
                document.getElementById('modal-detail-id').value = data.id;
                document.getElementById('modal-title-reserva').innerText = data.reserva;
                document.getElementById('modal-status').value = data.precheckin_status;
                document.getElementById('modal-fecha-viaje').value = data.fecha_viaje_raw;
                document.getElementById('modal-notes').value = data.precheckin_notes;

                $('#modalUpdateReserva').modal('show');
            });

            // Guardar Cambios del Modal
            document.getElementById('formUpdateReserva').addEventListener('submit', function (e) {
                e.preventDefault();
                const id = document.getElementById('modal-detail-id').value;
                const status = document.getElementById('modal-status').value;
                const fecha_viaje = document.getElementById('modal-fecha-viaje').value;
                const notes = document.getElementById('modal-notes').value;

                // Ocultar modal primero para evitar problemas de z-index y bloqueo con el backdrop de Bootstrap
                $('#modalUpdateReserva').modal('hide');

                Swal.fire({
                    title: '¿Guardar cambios?',
                    text: "Se actualizará la información de la reserva.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        $.ajax({
                            url: `/precheckin/update-status/${id}`,
                            method: 'POST',
                            data: {
                                _token: $('input[name="_token"]').val(),
                                precheckin_status: status,
                                fecha_viaje: fecha_viaje,
                                precheckin_notes: notes
                            },
                            success: function (res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Actualizado!',
                                    text: res.message,
                                    customClass: { confirmButton: 'btn btn-success' }
                                });
                                table.ajax.reload();
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'No se pudo guardar la información.',
                                    customClass: { confirmButton: 'btn btn-danger' }
                                });
                                // Reabrir el modal si ocurre un error
                                $('#modalUpdateReserva').modal('show');
                            }
                        });
                    } else {
                        // Reabrir el modal si el usuario canceló
                        $('#modalUpdateReserva').modal('show');
                    }
                });
            });

            // Evento: Enviar Correo Manual
            $('#table-reservas tbody').on('click', '.btn-email', function () {
                const data = table.row($(this).parents('tr')).data();

                Swal.fire({
                    title: '¿Enviar correo de alerta?',
                    text: `Se enviará la alerta a: ${data.client_email || 'correo no configurado'}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-success me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Enviando correo...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: `/precheckin/send-mail/${data.id}`,
                            method: 'POST',
                            data: {
                                _token: $('input[name="_token"]').val()
                            },
                            success: function (res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Correo Enviado!',
                                    text: res.message,
                                    customClass: { confirmButton: 'btn btn-success' }
                                });
                                table.ajax.reload();
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error al enviar',
                                    text: xhr.responseJSON?.message || 'No se pudo enviar el correo.',
                                    customClass: { confirmButton: 'btn btn-danger' }
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
