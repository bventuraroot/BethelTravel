@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
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

        var form = $('<form>', { 'method': 'POST', 'action': '{{ route("report.fse.excel") }}' });
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

@section('title', 'Reporte de Sujeto Excluido (FSE)')

@section('content')
<h4 class="py-3 mb-4 fw-bold">
    <span class="text-muted fw-light">Reportes /</span> Sujeto Excluido (FSE - DTE 14)
</h4>

<div class="card mb-4">
    <form id="sendfilters" action="{{ route('report.fsesearch') }}" method="post">
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
                    <button type="submit" class="btn btn-warning text-dark w-100 fw-bold"><i class="fa-solid fa-magnifying-glass me-1"></i> Buscar</button>
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
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-user-shield me-2 text-warning"></i>Facturas de Sujeto Excluido (FSE) - {{ $heading->name ?? '' }} ({{ $period }}/{{ $yearB }})</h5>
        <span class="badge bg-warning text-dark fs-6">{{ count($sales) }} registros</span>
    </div>
    <div class="table-responsive text-nowrap p-3" id="areaImprimir">
        <table class="table table-bordered table-striped table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>N° Control DTE</th>
                    <th>Código Generación DTE</th>
                    <th>Sujeto Excluido / Nombre</th>
                    <th>DUI / NIT</th>
                    <th>Monto Bruto ($)</th>
                    <th>Retención Renta (10%) ($)</th>
                    <th>Total Líquido ($)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totBruto = 0;
                    $totRetencion = 0;
                    $totLiquido = 0;
                @endphp
                @forelse($sales as $index => $sale)
                    @php
                        $bruto = (float)($sale->gravada ?? $sale->totalamount ?? 0);
                        $retencion = (float)($sale->retencion_renta ?? ($bruto * 0.10));
                        $liquido = $bruto - $retencion;
                        $totBruto += $bruto;
                        $totRetencion += $retencion;
                        $totLiquido += $liquido;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $sale->dateF ?? $sale->date }}</td>
                        <td class="text-center fw-bold">{{ $sale->numeroControl ?? $sale->nu_doc ?? $sale->correlativo }}</td>
                        <td class="text-center"><small class="text-muted">{{ $sale->codigoGeneracion ?? 'N/A' }}</small></td>
                        <td>{{ $sale->nombre_completo ?? $sale->firstname }}</td>
                        <td class="text-center">{{ $sale->nit ?? $sale->dui ?? 'N/A' }}</td>
                        <td class="text-end">$ {{ number_format($bruto, 2) }}</td>
                        <td class="text-end text-danger">$ {{ number_format($retencion, 2) }}</td>
                        <td class="text-end fw-bold text-success">$ {{ number_format($liquido, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No se encontraron facturas de sujeto excluido (FSE) para el período seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($sales) > 0)
            <tfoot class="table-secondary fw-bold text-end">
                <tr>
                    <td colspan="6" class="text-center">TOTALES GENERALES FSE:</td>
                    <td>$ {{ number_format($totBruto, 2) }}</td>
                    <td class="text-danger">$ {{ number_format($totRetencion, 2) }}</td>
                    <td class="text-success">$ {{ number_format($totLiquido, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif
@endsection
