@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    var iduser = $("#iduser").val();
    var selectedCompany = "{{ isset($company_id) ? $company_id : '' }}";
    
    $.ajax({
        url: "/company/getCompanybyuser/" + iduser,
        method: "GET",
        success: function (response) {
            $("#company").empty();
            $.each(response, function (index, value) {
                var isSelected = (selectedCompany && selectedCompany == value.id) ? 'selected' : '';
                $("#company").append('<option value="' + value.id + '" ' + isSelected + '>' + value.name.toUpperCase() + '</option>');
            });
        }
    });

    $('#btn-export-excel').on('click', function() {
        var company = $('#company').val();
        var year = $('#year').val();
        var period = $('#period').val();

        if (!company) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Por favor, selecciona una empresa.' });
            return;
        }

        var form = $('<form>', { 'method': 'POST', 'action': '{{ route("report.fex.excel") }}' });
        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
        form.append($('<input>', { 'type': 'hidden', 'name': 'company', 'value': company }));
        form.append($('<input>', { 'type': 'hidden', 'name': 'year', 'value': year }));
        form.append($('<input>', { 'type': 'hidden', 'name': 'period', 'value': period }));
        $('body').append(form);
        form.submit();
        form.remove();
    });

    $('#btn-merge-pdf').on('click', function() {
        var company = $('#company').val();
        var year = $('#year').val();
        var period = $('#period').val();

        if (!company) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Por favor, selecciona una empresa.' });
            return;
        }

        var form = $('<form>', { 'method': 'POST', 'action': '{{ route("report.fex.merge-pdf") }}' });
        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
        form.append($('<input>', { 'type': 'hidden', 'name': 'company', 'value': company }));
        form.append($('<input>', { 'type': 'hidden', 'name': 'year', 'value': year }));
        form.append($('<input>', { 'type': 'hidden', 'name': 'period', 'value': period }));
        $('body').append(form);
        form.submit();
        form.remove();
    });
});

function impFAC(nombreDiv) {
    var contenido = document.getElementById(nombreDiv).innerHTML;
    var contenidoOriginal = document.body.innerHTML;
    document.body.innerHTML = contenido;
    window.print();
    location.reload(true);
}
</script>
@endsection

@section('title', 'Reporte de Facturas de Exportación (FEX)')

@section('content')
<h4 class="py-3 mb-4 fw-bold">
    <span class="text-muted fw-light">Reportes /</span> Facturas de Exportación (FEX - DTE 11)
</h4>

<div class="card mb-4">
    <form id="sendfilters" action="{{ route('report.fexsearch') }}" method="post">
        @csrf
        <input type="hidden" name="iduser" id="iduser" value="{{ Auth::user()->id }}">
        <div class="card-header">
            <div class="row align-items-end g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Empresa:</label>
                    <select class="form-select" name="company" id="company">
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Año:</label>
                    <select class="form-select" name="year" id="year">
                        @for ($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ (isset($yearB) && $yearB == $y) ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Período (Mes):</label>
                    <select class="form-select" name="period" id="period">
                        @php
                            $meses = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
                        @endphp
                        @foreach($meses as $key => $mes)
                            <option value="{{ $key }}" {{ (isset($period) && $period == $key) ? 'selected' : '' }}>{{ $mes }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass me-1"></i> Buscar</button>
                    @if(isset($sales))
                        <button type="button" id="btn-export-excel" class="btn btn-success" title="Exportar a Excel"><i class="fa-solid fa-file-excel"></i></button>
                        <button type="button" class="btn btn-danger" title="Imprimir" onclick="impFAC('areaImprimir');"><i class="fa-solid fa-print"></i></button>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>

@if(isset($sales))
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-globe me-2"></i>Facturas de Exportación (FEX) - {{ $heading->name ?? '' }} ({{ $period }}/{{ $yearB }})</h5>
        <span class="badge bg-primary fs-6">{{ count($sales) }} registros</span>
    </div>
    <div class="table-responsive text-nowrap p-3" id="areaImprimir">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>N° Control / Correlativo</th>
                    <th>Código Generación DTE</th>
                    <th>Cliente / Razón Social</th>
                    <th>NIT / Pasaporte</th>
                    <th>Exportación Gravada ($)</th>
                    <th>Exportación Exenta ($)</th>
                    <th>Monto Total ($)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totGravada = 0;
                    $totExenta = 0;
                    $totTotal = 0;
                @endphp
                @forelse($sales as $index => $sale)
                    @php
                        $gravada = (float)($sale->gravada ?? 0);
                        $exenta = (float)($sale->exenta ?? 0);
                        $total = (float)($sale->totalamount ?? ($gravada + $exenta));
                        $totGravada += $gravada;
                        $totExenta += $exenta;
                        $totTotal += $total;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $sale->dateF ?? $sale->date }}</td>
                        <td class="text-center fw-bold">{{ $sale->numeroControl ?? $sale->nu_doc ?? $sale->correlativo }}</td>
                        <td class="text-center"><small class="text-muted">{{ $sale->codigoGeneracion ?? 'N/A' }}</small></td>
                        <td>{{ $sale->nombre_completo ?? $sale->firstname }}</td>
                        <td class="text-center">{{ $sale->nit ?? $sale->ncrC ?? 'N/A' }}</td>
                        <td class="text-end">$ {{ number_format($gravada, 2) }}</td>
                        <td class="text-end">$ {{ number_format($exenta, 2) }}</td>
                        <td class="text-end fw-bold">$ {{ number_format($total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No se encontraron facturas de exportación (FEX) para el período seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($sales) > 0)
            <tfoot class="table-secondary fw-bold text-end">
                <tr>
                    <td colspan="6" class="text-center">TOTALES GENERALES FEX:</td>
                    <td>$ {{ number_format($totGravada, 2) }}</td>
                    <td>$ {{ number_format($totExenta, 2) }}</td>
                    <td>$ {{ number_format($totTotal, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif
@endsection
