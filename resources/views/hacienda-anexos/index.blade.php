@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Anexos de Hacienda')

@section('content')
<h4 class="py-3 mb-4 fw-bold">
    <span class="text-muted fw-light">Reportes /</span> Anexos de Hacienda (F-07)
</h4>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Generador Interactivo de Anexos</h5>
            <small class="text-muted">
                Consulte registros y exporte los reportes del formulario F-07 v14 del Ministerio de Hacienda de El Salvador.
            </small>
        </div>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('report.hacienda-anexos') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="company_id" class="form-label">Empresa</label>
                <select id="company_id" name="company_id" class="form-select" required>
                    <option value="">Seleccione empresa</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(isset($companyId) && $companyId == $company->id)>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="year" class="form-label">Año</label>
                <input type="number" min="2020" max="{{ $currentYear + 1 }}" class="form-control" id="year" name="year"
                    value="{{ old('year', $year ?? $currentYear) }}" required>
            </div>

            <div class="col-md-2">
                <label for="month" class="form-label">Mes</label>
                <select id="month" name="month" class="form-select" required>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected((int) old('month', $month ?? (int)$currentMonth) === $m)>
                            {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Consultar
                </button>
            </div>

            <!-- Acordeón Configuración Avanzada -->
            <div class="col-12 mt-3">
                <div class="accordion" id="accordionAdvanced">
                    <div class="accordion-item border-0 shadow-none">
                        <h2 class="accordion-header" id="headingAdvanced">
                            <button class="accordion-button collapsed py-2 px-0 bg-transparent text-primary fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvanced" aria-expanded="false" aria-controls="collapseAdvanced">
                                <i class="fa-solid fa-sliders me-1"></i> Configuración Contable Avanzada (Renta / Compras)
                            </button>
                        </h2>
                        <div id="collapseAdvanced" class="accordion-collapse collapse" aria-labelledby="headingAdvanced" data-bs-parent="#accordionAdvanced">
                            <div class="accordion-body px-0 py-3 row g-3">
                                <div class="col-md-3">
                                    <label for="operation_type" class="form-label">Tipo de operación</label>
                                    <select id="operation_type" name="operation_type" class="form-select">
                                        <option value="0" @selected(old('operation_type') == '0')>0 - No aplica</option>
                                        <option value="1" @selected(old('operation_type') == '1')>1 - Gravada</option>
                                        <option value="2" @selected(old('operation_type') == '2')>2 - Exenta</option>
                                        <option value="3" @selected(old('operation_type') == '3')>3 - No sujeta</option>
                                        <option value="4" @selected(old('operation_type') == '4')>4 - Mixta</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="income_type" class="form-label">Tipo de ingreso (renta)</label>
                                    <input type="number" min="0" max="13" class="form-control" id="income_type" name="income_type"
                                        value="{{ old('income_type', 0) }}">
                                </div>

                                <div class="col-md-2">
                                    <label for="classification" class="form-label">Clasificación</label>
                                    <select id="classification" name="classification" class="form-select">
                                        <option value="1" @selected(old('classification', '1') == '1')>1 - Costo</option>
                                        <option value="2" @selected(old('classification') == '2')>2 - Gasto</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="sector" class="form-label">Sector</label>
                                    <select id="sector" name="sector" class="form-select">
                                        <option value="1" @selected(old('sector') == '1')>1 - Industrial</option>
                                        <option value="2" @selected(old('sector', '2') == '2')>2 - Comercial</option>
                                        <option value="3" @selected(old('sector') == '3')>3 - Agropecuario</option>
                                        <option value="4" @selected(old('sector') == '4')>4 - Servicios</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="cost_type" class="form-label">Tipo costo/gasto</label>
                                    <input type="number" min="1" max="7" class="form-control" id="cost_type" name="cost_type"
                                        value="{{ old('cost_type', 5) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if(isset($companyId) && $companyId)
    <div class="row g-4 mb-4">
        <!-- Anexo 1 -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md me-3 bg-label-primary p-2 rounded">
                                <i class="fa-solid fa-file-invoice text-primary fs-3"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold text-wrap" style="max-width: 80%;">Anexo 1 - Ventas a Contribuyentes (CCF)</h6>
                        </div>
                        <p class="text-muted small">Reporta las ventas realizadas a clientes registrados como contribuyentes (Comprobantes de Crédito Fiscal).</p>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary small">Registros encontrados:</span>
                            @if($counts['anexo1'] > 0)
                                <span class="badge bg-label-success fw-bold fs-7">{{ $counts['anexo1'] }}</span>
                            @else
                                <span class="badge bg-label-secondary fw-bold fs-7">0</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="downloadAnnex('anexo1', 'csv')" class="btn btn-outline-primary w-100 btn-sm" @disabled($counts['anexo1'] == 0)>
                                <i class="fa-solid fa-file-csv me-1"></i> CSV
                            </button>
                            <button onclick="downloadAnnex('anexo1', 'excel')" class="btn btn-outline-success w-100 btn-sm" @disabled($counts['anexo1'] == 0)>
                                <i class="fa-solid fa-file-excel me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Anexo 2 -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md me-3 bg-label-success p-2 rounded">
                                <i class="fa-solid fa-receipt text-success fs-3"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold text-wrap" style="max-width: 80%;">Anexo 2 - Ventas a Consumidor Final (Facturas)</h6>
                        </div>
                        <p class="text-muted small">Agrupa y reporta los totales de ventas del día a consumidores finales (Facturas de Consumidor Final).</p>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary small">Registros encontrados:</span>
                            @if($counts['anexo2'] > 0)
                                <span class="badge bg-label-success fw-bold fs-7">{{ $counts['anexo2'] }}</span>
                            @else
                                <span class="badge bg-label-secondary fw-bold fs-7">0</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="downloadAnnex('anexo2', 'csv')" class="btn btn-outline-primary w-100 btn-sm" @disabled($counts['anexo2'] == 0)>
                                <i class="fa-solid fa-file-csv me-1"></i> CSV
                            </button>
                            <button onclick="downloadAnnex('anexo2', 'excel')" class="btn btn-outline-success w-100 btn-sm" @disabled($counts['anexo2'] == 0)>
                                <i class="fa-solid fa-file-excel me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Anexo 3 -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md me-3 bg-label-warning p-2 rounded">
                                <i class="fa-solid fa-cart-shopping text-warning fs-3"></i>
                            </div>
                            <h6 class="mb-0 fw-semibold text-wrap" style="max-width: 80%;">Anexo 3 - Compras a Contribuyentes (Proveedores)</h6>
                        </div>
                        <p class="text-muted small">Reporta el detalle de compras de bienes y servicios efectuados a sus proveedores contribuyentes.</p>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-secondary small">Registros encontrados:</span>
                            @if($counts['anexo3'] > 0)
                                <span class="badge bg-label-success fw-bold fs-7">{{ $counts['anexo3'] }}</span>
                            @else
                                <span class="badge bg-label-secondary fw-bold fs-7">0</span>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="downloadAnnex('anexo3', 'csv')" class="btn btn-outline-primary w-100 btn-sm" @disabled($counts['anexo3'] == 0)>
                                <i class="fa-solid fa-file-csv me-1"></i> CSV
                            </button>
                            <button onclick="downloadAnnex('anexo3', 'excel')" class="btn btn-outline-success w-100 btn-sm" @disabled($counts['anexo3'] == 0)>
                                <i class="fa-solid fa-file-excel me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Globales -->
    @if($counts['anexo1'] > 0 || $counts['anexo2'] > 0 || $counts['anexo3'] > 0)
        <div class="card border-0 bg-label-primary shadow-none mb-4">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-info text-primary fs-4 me-2"></i>
                    <span class="text-primary fw-medium small">
                        Tiene datos disponibles en este período. Puede descargar la plantilla Excel completa con todas las hojas pre-llenadas.
                    </span>
                </div>
                <button onclick="downloadAnnex('todos', 'excel')" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-file-excel me-1"></i> Descargar Plantilla Completa F-07 (.XLSM)
                </button>
            </div>
        </div>
    @endif
@else
    <div class="card border-0 bg-light py-5 text-center mb-4">
        <div class="card-body">
            <i class="fa-solid fa-circle-question text-muted fs-1 mb-3"></i>
            <h5>Sin consulta activa</h5>
            <p class="text-muted small">Seleccione la empresa, el año y el mes arriba, y presione "Consultar" para revisar la información disponible.</p>
        </div>
    </div>
@endif

<div class="alert alert-warning mt-3 mb-0">
    <strong>Importante:</strong> este generador automatiza la estructura del archivo CSV/Excel y montos base, pero la revisión
    contable final de columnas de renta (operación, ingreso, clasificación y sector) sigue siendo responsabilidad del
    contador antes de cargar en el portal de Hacienda.
</div>

<!-- Formulario oculto para descargas seguras -->
<form id="downloadForm" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="company_id" id="hidden_company_id">
    <input type="hidden" name="year" id="hidden_year">
    <input type="hidden" name="month" id="hidden_month">
    <input type="hidden" name="annex_type" id="hidden_annex_type">
    <input type="hidden" name="operation_type" id="hidden_operation_type">
    <input type="hidden" name="income_type" id="hidden_income_type">
    <input type="hidden" name="classification" id="hidden_classification">
    <input type="hidden" name="sector" id="hidden_sector">
    <input type="hidden" name="cost_type" id="hidden_cost_type">
</form>

<script>
function downloadAnnex(annexType, format) {
    document.getElementById('hidden_company_id').value = document.getElementById('company_id').value;
    document.getElementById('hidden_year').value = document.getElementById('year').value;
    document.getElementById('hidden_month').value = document.getElementById('month').value;
    document.getElementById('hidden_annex_type').value = annexType;
    document.getElementById('hidden_operation_type').value = document.getElementById('operation_type').value;
    document.getElementById('hidden_income_type').value = document.getElementById('income_type').value;
    document.getElementById('hidden_classification').value = document.getElementById('classification').value;
    document.getElementById('hidden_sector').value = document.getElementById('sector').value;
    document.getElementById('hidden_cost_type').value = document.getElementById('cost_type').value;

    const form = document.getElementById('downloadForm');
    if (format === 'excel') {
        form.action = "{{ route('report.hacienda-anexos.export-excel') }}";
    } else {
        form.action = "{{ route('report.hacienda-anexos.export') }}";
    }
    form.submit();
}
</script>
@endsection
