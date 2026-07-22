@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/FormValidation.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/cleavejs/cleave.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/cleavejs/cleave-phone.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/pickr/pickr.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
<script>
$(function () {
    var iduser = $("#iduser").val();
    $.ajax({
        url: "/company/getCompanybyuser/" + iduser,
        method: "GET",
        success: function (response) {
            $.each(response, function (index, value) {
                var selected = (value.id == "{{ @$heading['id'] }}") ? 'selected' : '';
                $("#company").append('<option value="' + value.id + '" ' + selected + '>' + value.name.toUpperCase() + "</option>");
            });
        }
    });

    $("#first-filter").click(function(){
        $('#sendfilters').submit();
    });

    $('#btn-export-excel').on('click', function() {
        var company = $('#company').val();
        var year = $('#year').val();
        var period = $('#period').val();

        if (!company) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Por favor, primero realiza una búsqueda para generar el reporte.' });
            return;
        }

        var form = $('<form>', { 'method': 'POST', 'action': '{{ route("report.rec.excel") }}' });
        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
        form.append($('<input>', { 'type': 'hidden', 'name': 'company', 'value': company }));
        form.append($('<input>', { 'type': 'hidden', 'name': 'year', 'value': year }));
        form.append($('<input>', { 'type': 'hidden', 'name': 'period', 'value': period }));
        $('body').append(form);
        form.submit();
        form.remove();
    });

    $('#btn-export-pdf').on('click', function() {
        var company = $('#company').val();
        var year = $('#year').val();
        var period = $('#period').val();

        if (!company) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Por favor, primero realiza una búsqueda para generar el reporte.' });
            return;
        }

        var form = $('<form>', { 'method': 'POST', 'action': '{{ route("report.rec.pdf") }}' });
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
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Por favor, primero realiza una búsqueda para generar el reporte.' });
            return;
        }

        var form = $('<form>', { 'method': 'POST', 'action': '{{ route("report.rec.merge-pdf") }}' });
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

@section('title', 'Reporte de Recibos de Ingreso (REC)')

@section('content')
<h4 class="py-3 mb-4 fw-bold">
    <span class="text-muted fw-light">Reportes /</span> Recibos de Ingreso (REC / DTE 15)
</h4>

<!-- Advanced Search -->
<div class="card">
    <form id="sendfilters" action="{{ route('report.recsearch') }}" method="post">
        @csrf @method('POST')
        <input type="hidden" name="iduser" id="iduser" value="{{ Auth::user()->id }}">
        <div class="card-header">
            <div class="row">
                <div class="col-4">
                    <div class="row g-3">
                        <select class="form-control" name="company" id="company">
                        </select>
                    </div>
                </div>
                <div class="col-1">
                    <div class="row g-3">
                        <select class="form-control" name="year" id="year">
                            <?php
                            $year = date("Y");
                            for ($i=0; $i < 5 ; $i++) {
                                $yearnew = $year-$i;
                                $selected = (isset($yearB) && $yearnew == $yearB) ? "selected" : "";
                                echo "<option value ='".$yearnew."' ".$selected.">".$yearnew."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-4">
                    <div class="row g-3">
                        <select class="form-control" name="period" id="period">
                            <?php if(empty($period)){ $period = (date('n') == 1) ? '12' : sprintf('%02d', date('n') - 1); } ?>
                            <option value="01" <?php echo (@$period == '01') ? "selected" : "" ?>>Enero</option>
                            <option value="02" <?php echo (@$period == '02') ? "selected" : "" ?>>Febrero</option>
                            <option value="03" <?php echo (@$period == '03') ? "selected" : "" ?>>Marzo</option>
                            <option value="04" <?php echo (@$period == '04') ? "selected" : "" ?>>Abril</option>
                            <option value="05" <?php echo (@$period == '05') ? "selected" : "" ?>>Mayo</option>
                            <option value="06" <?php echo (@$period == '06') ? "selected" : "" ?>>Junio</option>
                            <option value="07" <?php echo (@$period == '07') ? "selected" : "" ?>>Julio</option>
                            <option value="08" <?php echo (@$period == '08') ? "selected" : "" ?>>Agosto</option>
                            <option value="09" <?php echo (@$period == '09') ? "selected" : "" ?>>Septiembre</option>
                            <option value="10" <?php echo (@$period == '10') ? "selected" : "" ?>>Octubre</option>
                            <option value="11" <?php echo (@$period == '11') ? "selected" : "" ?>>Noviembre</option>
                            <option value="12" <?php echo (@$period == '12') ? "selected" : "" ?>>Diciembre</option>
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <button type="button" id="first-filter" class="btn rounded-pill btn-primary waves-effect waves-light">Buscar</button>
                </div>
            </div>
        </div>
    </form>

    @isset($heading)
    <?php
    $mesesDelAno = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $mesesDelAnoMayuscula = array_map('strtoupper', $mesesDelAno);
    ?>
    <div class="row">
        <div class="col-12">
            <div class="box-header" style="text-align: right; margin-right: 6%;">
                <button type="button" class='btn btn-primary' title='Exportar a Excel' id="btn-export-excel">
                    <i class="fa-solid fa-file-excel"></i> &nbsp;&nbsp;Exportar a Excel
                </button>
                &nbsp;
                <button type="button" class='btn btn-danger' title='Exportar a PDF' id="btn-export-pdf">
                    <i class="fa-solid fa-file-pdf"></i> &nbsp;&nbsp;Exportar a PDF
                </button>
                &nbsp;
                <button type="button" class='btn btn-warning' title='Concatenar PDFs de documentos' id="btn-merge-pdf">
                    <i class="fa-solid fa-file-pdf"></i> &nbsp;&nbsp;Unir PDFs de documentos
                </button>
                &nbsp;
                <a href="#!" class='btn btn-success' title='Imprimir' onclick="impFAC('areaImprimir');">
                    <i class="fa-solid fa-print"> </i> &nbsp;&nbsp;Imprimir
                </a>
            </div>
        </div>
    </div>

    <style>
        .report-container {
            max-height: 70vh;
            overflow: auto;
        }
        .report-container table {
            font-size: 11px;
        }
        .report-container thead td,
        .report-container thead th {
            font-size: 11px;
        }
        .report-container tbody td {
            font-size: 10px;
        }
    </style>

    <div id="areaImprimir" class="report-container">
        <table class="table table-sm" style="min-width: 1500px;">
            <thead>
                <tr>
                    <th class="text-center" colspan="14">
                        <b>REGISTRO DE RECIBOS DE INGRESO (REC / DTE 15)</b>
                    </th>
                </tr>
                <tr>
                    <td class="text-center" colspan="14">
                        <b>Nombre del Contribuyente: </b> <?php echo $heading['name']; ?>
                        <b>N.R.C.: </b> <?php echo $heading['ncr']; ?> <b>MES: </b><?php echo $mesesDelAnoMayuscula[(int)$period-1] ?>
                        <b>Año: </b> <?php echo $yearB; ?><p>(Valores expresados en Dolares Estadounidenses)</p>
                    </td>
                </tr>
            </thead>

            <tbody>
                <tr class="text-center">
                    <td style="width: 40px;">Corr.</td>
                    <td style="width: 80px;">FECHA</td>
                    <td style="width: 60px;">No. Recibo</td>
                    <td style="text-align: left; width: 180px;">CLIENTE / PAGADOR</td>
                    <td style="text-align: left; width: 220px;">CONCEPTO / DETALLE</td>
                    <td style="text-align: right; width: 110px;">MONTO RECIBIDO TOTAL</td>
                    <td style="text-align: center; min-width: 200px;">NÚMERO CONTROL DTE</td>
                    <td style="text-align: center; min-width: 200px;">CÓDIGO GENERACIÓN</td>
                    <td style="text-align: center; min-width: 200px;">SELLO RECEPCIÓN</td>
                    <td style="text-align: center; min-width: 200px;">Nº CONTROL ANULACIÓN</td>
                    <td style="text-align: center; min-width: 200px;">CÓD. GEN. ANULACIÓN</td>
                    <td style="text-align: center; min-width: 200px;">SELLO ANULACIÓN</td>
                </tr>
                <?php
                $i = 1;
                $tot_monto = 0.00;
                ?>
                @foreach ($sales as $sale)
                <?php
                $monto = (float)($sale['totalamount'] ?? $sale['gravada'] ?? 0);
                ?>
                <tr>
                    <td style="text-align: center; padding: 2px 4px;"><?php echo $i; ?></td>
                    <td style="text-align: center; padding: 2px 4px;"><?php echo $sale['dateF']; ?></td>
                    <td style="text-align: center; padding: 2px 4px; font-size: 9px;"><?php echo $sale['correlativo'] ?? '-'; ?></td>
                    <td class="text-uppercase" style="text-align: left; padding: 2px 4px;">
                        @if($sale['typesale']=='0')
                            <span style="color: #c00; font-weight: bold;">ANULADO</span>
                        @else
                            {{ $sale['nombre_completo'] ?? '' }}
                        @endif
                    </td>
                    <td style="text-align: left; padding: 2px 4px;"><?php echo $sale['concept'] ?? $sale['description'] ?? 'Pago recibido / Recibo de Ingreso'; ?></td>
                    <td style="text-align: right; padding: 2px 4px; font-weight: bold; color: green;">
                        @if($sale['typesale']=='0') 0.00 @else {{ number_format($monto, 2) }} <?php $tot_monto += $monto; ?> @endif
                    </td>
                    <td style="text-align: center; font-size: 9px; padding: 2px 4px;">{{ $sale['numeroControl'] ?? '' }}</td>
                    <td style="text-align: center; font-size: 9px; padding: 2px 4px;">{{ $sale['codigoGeneracion'] ?? '' }}</td>
                    <td style="text-align: center; font-size: 9px; padding: 2px 4px;">{{ $sale['selloRecibido'] ?? '' }}</td>
                    <td style="text-align: center; font-size: 9px; padding: 2px 4px;">{{ $sale['numeroControl_anulacion'] ?? '' }}</td>
                    <td style="text-align: center; font-size: 9px; padding: 2px 4px;">{{ $sale['codigoGeneracion_anulacion'] ?? '' }}</td>
                    <td style="text-align: center; font-size: 9px; padding: 2px 4px;">{{ $sale['selloRecibido_anulacion'] ?? '' }}</td>
                </tr>
                <?php $i++; ?>
                @endforeach
                <tr style="font-weight: bold; background-color: rgba(0,0,0,0.05);">
                    <td colspan="5" class="text-center">TOTAL GENERAL RECIBIDOS</td>
                    <td style="text-align: right; color: green;">{{ number_format($tot_monto, 2) }}</td>
                    <td colspan="6"></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endisset
</div>
@endsection
