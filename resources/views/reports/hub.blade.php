@extends('layouts/layoutMaster')

@section('title', 'Centro de Reportes y Módulo Contable')

@section('page-style')
<style>
  .report-card {
    transition: all 0.25s ease-in-out;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 12px;
    height: 100%;
  }
  .report-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border-color: #7367f0;
  }
  .icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
  }
  .category-title {
    font-weight: 700;
    font-size: 1.15rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #7367f0;
    display: inline-block;
  }
  .badge-dte {
    font-size: 0.75rem;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
  }
  .search-hero {
    background: linear-gradient(135deg, #7367f0 0%, #4834d4 100%);
    color: #fff;
    border-radius: 16px;
    padding: 2.5rem 2rem;
  }
  .search-hero input {
    border-radius: 30px;
    padding-left: 1.5rem;
    height: 50px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  
  <!-- Hero / Header section with Live Search -->
  <div class="search-hero mb-4 text-center">
    <h2 class="text-white fw-bold mb-2"><i class="fa-solid fa-chart-pie me-2"></i>Centro de Reportes Contables y Fiscales</h2>
    <p class="text-white-50 mb-4 fs-6">Accede rápidamente a todos los reportes de ventas, compras, liquidaciones, anexos de hacienda y DTEs.</p>
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="input-group input-group-merge">
          <span class="input-group-text border-0 ps-3 bg-white text-muted"><i class="fa-solid fa-search"></i></span>
          <input type="text" id="reportSearch" class="form-control border-0" placeholder="Buscar reporte por nombre (ej. FEX, Sujeto Excluido, IVA, Consumidor)..." autocomplete="off">
        </div>
      </div>
    </div>
  </div>

  <!-- Contadores Rápidos -->
  <div class="row mb-4">
    <div class="col-md-3 col-6 mb-3 mb-md-0">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted d-block mb-1">Total Reportes</span>
            <h4 class="mb-0 fw-bold">18</h4>
          </div>
          <div class="icon-wrapper bg-label-primary">
            <i class="fa-solid fa-folder-open"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-3 mb-md-0">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted d-block mb-1">DTEs Soportados</span>
            <h4 class="mb-0 fw-bold">7 Tipos</h4>
          </div>
          <div class="icon-wrapper bg-label-success">
            <i class="fa-solid fa-file-invoice-dollar"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-3 mb-md-0">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted d-block mb-1">Reportes Anexos</span>
            <h4 class="mb-0 fw-bold">F-07 MH</h4>
          </div>
          <div class="icon-wrapper bg-label-warning">
            <i class="fa-solid fa-building-columns"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-3 mb-md-0">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted d-block mb-1">Formatos Export.</span>
            <h4 class="mb-0 fw-bold">Excel / PDF</h4>
          </div>
          <div class="icon-wrapper bg-label-info">
            <i class="fa-solid fa-file-export"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CATEGORÍA 1: VENTAS E IVA DÉBITO -->
  <div class="mb-5 report-category">
    <div class="d-flex align-items-center mb-3">
      <div class="icon-wrapper bg-label-primary me-2" style="width: 36px; height: 36px; font-size: 1.1rem;">
        <i class="fa-solid fa-arrow-trend-up"></i>
      </div>
      <h4 class="mb-0 fw-bold">Ventas y Documentos Emitidos (IVA Débito)</h4>
    </div>
    
    <div class="row g-3">
      <!-- Ventas Generales -->
      <div class="col-md-6 col-lg-4 report-item" data-title="ventas generales reporte">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-wrapper bg-label-primary me-3"><i class="fa-solid fa-chart-line"></i></div>
              <div>
                <h5 class="card-title mb-0 fw-semibold">Ventas Generales</h5>
                <small class="text-muted">Consulta general de ventas</small>
              </div>
            </div>
            <p class="card-text text-muted small">Listado global de transacciones de ventas registradas en el sistema con detalle por sucursal y fecha.</p>
            <a href="{{ route('report.sales') }}" class="btn btn-sm btn-outline-primary w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Ver Reporte</a>
          </div>
        </div>
      </div>

      <!-- Ventas Consumidor Final (FAC - DTE 01) -->
      <div class="col-md-6 col-lg-4 report-item" data-title="ventas consumidor final fac dte 01 factura">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-label-info me-3"><i class="fa-solid fa-receipt"></i></div>
                <div>
                  <h5 class="card-title mb-0 fw-semibold">Consumidor Final</h5>
                  <small class="text-muted">Facturas de Venta</small>
                </div>
              </div>
              <span class="badge bg-label-info badge-dte">DTE-01 (FAC)</span>
            </div>
            <p class="card-text text-muted small">Libro de ventas a consumidor final con detalle de correlativos DTE, exento, gravado e IVA.</p>
            <a href="{{ route('report.consumidor') }}" class="btn btn-sm btn-outline-info w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Ver Reporte</a>
          </div>
        </div>
      </div>

      <!-- Ventas Crédito Fiscal (CCF - DTE 03) -->
      <div class="col-md-6 col-lg-4 report-item" data-title="ventas contribuyentes ccf dte 03 credito fiscal">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-label-success me-3"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div>
                  <h5 class="card-title mb-0 fw-semibold">Contribuyentes (CCF)</h5>
                  <small class="text-muted">Comprobantes de Crédito Fiscal</small>
                </div>
              </div>
              <span class="badge bg-label-success badge-dte">DTE-03 (CCF)</span>
            </div>
            <p class="card-text text-muted small">Libro de ventas a contribuyentes con registro de NRC, NIT, Débito Fiscal e IVA retenido.</p>
            <a href="{{ route('report.contribuyentes') }}" class="btn btn-sm btn-outline-success w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Ver Reporte</a>
          </div>
        </div>
      </div>

      <!-- Ventas Exportación (FEX - DTE 11) - NUEVO -->
      <div class="col-md-6 col-lg-4 report-item" data-title="ventas exportacion fex dte 11 factura exportacion internacional">
        <div class="card report-card border-primary">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-label-primary me-3"><i class="fa-solid fa-globe"></i></div>
                <div>
                  <h5 class="card-title mb-0 fw-semibold">Facturas Exportación</h5>
                  <small class="text-primary fw-bold">Reporte FEX</small>
                </div>
              </div>
              <span class="badge bg-primary badge-dte">DTE-11 (FEX)</span>
            </div>
            <p class="card-text text-muted small">Reporte especializado de ventas de exportación a clientes extranjeros con montos exentos y DTE.</p>
            <a href="{{ route('report.fex') }}" class="btn btn-sm btn-primary w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Abrir Reporte FEX</a>
          </div>
        </div>
      </div>

      <!-- Facturas Sujeto Excluido (FSE - DTE 14) - NUEVO -->
      <div class="col-md-6 col-lg-4 report-item" data-title="sujeto excluido fse dte 14 retencion renta 10%">
        <div class="card report-card border-warning">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-label-warning me-3"><i class="fa-solid fa-user-shield"></i></div>
                <div>
                  <h5 class="card-title mb-0 fw-semibold">Sujeto Excluido</h5>
                  <small class="text-warning fw-bold">Reporte FSE</small>
                </div>
              </div>
              <span class="badge bg-warning badge-dte text-dark">DTE-14 (FSE)</span>
            </div>
            <p class="card-text text-muted small">Reporte de compras/servicios con sujetos excluidos, retención de Renta (10%) y DTE emitido.</p>
            <a href="{{ route('report.fse') }}" class="btn btn-sm btn-warning w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Abrir Reporte FSE</a>
          </div>
        </div>
      </div>

      <!-- Notas de Crédito (NCR - DTE 05) - NUEVO -->
      <div class="col-md-6 col-lg-4 report-item" data-title="notas de credito ncr dte 05 devoluciones ajustes">
        <div class="card report-card border-danger">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-label-danger me-3"><i class="fa-solid fa-file-circle-minus"></i></div>
                <div>
                  <h5 class="card-title mb-0 fw-semibold">Notas de Crédito</h5>
                  <small class="text-danger fw-bold">Reporte NCR</small>
                </div>
              </div>
              <span class="badge bg-danger badge-dte">DTE-05 (NCR)</span>
            </div>
            <p class="card-text text-muted small">Registro de notas de crédito emitidas, ajuste de IVA devuelto y referencia al documento origen.</p>
            <a href="{{ route('report.ncr') }}" class="btn btn-sm btn-danger w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Abrir Reporte NCR</a>
          </div>
        </div>
      </div>

      <!-- Recibos de Ingreso (REC - DTE 15 / REC) - NUEVO -->
      <div class="col-md-6 col-lg-4 report-item" data-title="recibos de ingreso rec dte 15 donacion cobros">
        <div class="card report-card border-dark">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper bg-label-dark me-3"><i class="fa-solid fa-money-bill-transfer"></i></div>
                <div>
                  <h5 class="card-title mb-0 fw-semibold">Recibos de Ingreso</h5>
                  <small class="text-dark fw-bold">Reporte REC</small>
                </div>
              </div>
              <span class="badge bg-dark badge-dte">DTE-15 / REC</span>
            </div>
            <p class="card-text text-muted small">Reporte de recibos de pago e ingresos emitidos a clientes con detalle de concepto y montos.</p>
            <a href="{{ route('report.rec') }}" class="btn btn-sm btn-dark w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Abrir Reporte REC</a>
          </div>
        </div>
      </div>

      <!-- Ventas por Clientes -->
      <div class="col-md-6 col-lg-4 report-item" data-title="ventas por cliente agro report acumulado">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-wrapper bg-label-secondary me-3"><i class="fa-solid fa-users"></i></div>
              <div>
                <h5 class="card-title mb-0 fw-semibold">Ventas por Clientes</h5>
                <small class="text-muted">Resumen acumulado</small>
              </div>
            </div>
            <p class="card-text text-muted small">Consolidado de ventas agrupadas por cliente con detalle de facturación y saldos.</p>
            <a href="{{ route('agro-report.sales-by-client') }}" class="btn btn-sm btn-outline-secondary w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Ver Reporte</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CATEGORÍA 2: COMPRAS E IVA CRÉDITO -->
  <div class="mb-5 report-category">
    <div class="d-flex align-items-center mb-3">
      <div class="icon-wrapper bg-label-success me-2" style="width: 36px; height: 36px; font-size: 1.1rem;">
        <i class="fa-solid fa-cart-shopping"></i>
      </div>
      <h4 class="mb-0 fw-bold">Compras y Gastos (IVA Crédito)</h4>
    </div>
    
    <div class="row g-3">
      <!-- Compras Generales -->
      <div class="col-md-6 col-lg-6 report-item" data-title="compras generales proveedores costo">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-wrapper bg-label-success me-3"><i class="fa-solid fa-truck-ramp-box"></i></div>
              <div>
                <h5 class="card-title mb-0 fw-semibold">Compras Generales</h5>
                <small class="text-muted">Registro de adquisiciones</small>
              </div>
            </div>
            <p class="card-text text-muted small">Listado de compras registradas a proveedores con número de documento, tipo e importe.</p>
            <a href="{{ route('report.purchases') }}" class="btn btn-sm btn-outline-success w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Ver Reporte</a>
          </div>
        </div>
      </div>

      <!-- Libro de Compras -->
      <div class="col-md-6 col-lg-6 report-item" data-title="libro de compras ivacredito fiscal proveedores">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-wrapper bg-label-success me-3"><i class="fa-solid fa-book-open"></i></div>
              <div>
                <h5 class="card-title mb-0 fw-semibold">Libro de Compras Fiscal</h5>
                <small class="text-muted">Reporte de Crédito Fiscal</small>
              </div>
            </div>
            <p class="card-text text-muted small">Libro IVA compras oficial con desglose de exento, gravado, crédito fiscal 13% y proveedores.</p>
            <a href="{{ route('report.bookpurchases') }}" class="btn btn-sm btn-outline-success w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Ver Libro de Compras</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CATEGORÍA 3: LIQUIDACIONES Y TERCEROS -->
  <div class="mb-5 report-category">
    <div class="d-flex align-items-center mb-3">
      <div class="icon-wrapper bg-label-warning me-2" style="width: 36px; height: 36px; font-size: 1.1rem;">
        <i class="fa-solid fa-handshake"></i>
      </div>
      <h4 class="mb-0 fw-bold">Liquidaciones y Operaciones con Terceros</h4>
    </div>
    
    <div class="row g-3">
      <!-- Comprobantes de Liquidación (CLQ) -->
      <div class="col-md-6 col-lg-3 report-item" data-title="comprobantes de liquidacion clq dte 07">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="icon-wrapper bg-label-warning"><i class="fa-solid fa-file-contract"></i></div>
              <span class="badge bg-label-warning badge-dte">DTE-07 (CLQ)</span>
            </div>
            <h5 class="card-title mb-1 fw-semibold">Liquidaciones (CLQ)</h5>
            <p class="card-text text-muted small">Comprobantes de liquidación emitidos por operaciones en nombre de terceros.</p>
            <a href="{{ route('report.liquidacion') }}" class="btn btn-sm btn-outline-warning w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Abrir CLQ</a>
          </div>
        </div>
      </div>

      <!-- Ventas a Terceros -->
      <div class="col-md-6 col-lg-3 report-item" data-title="ventas a terceros mandatario intermediacion">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="icon-wrapper bg-label-warning"><i class="fa-solid fa-people-arrows"></i></div>
            </div>
            <h5 class="card-title mb-1 fw-semibold">Ventas a Terceros</h5>
            <p class="card-text text-muted small">Reporte de ingresos intermediados y comisiones/fees generados.</p>
            <a href="{{ route('report.ventasTerceros') }}" class="btn btn-sm btn-outline-warning w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Ver Reporte</a>
          </div>
        </div>
      </div>

      <!-- Detalle Facturas por CLQ -->
      <div class="col-md-6 col-lg-3 report-item" data-title="detalle facturas por clq comprobante liquidacion">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="icon-wrapper bg-label-warning"><i class="fa-solid fa-list-check"></i></div>
            </div>
            <h5 class="card-title mb-1 fw-semibold">Detalle Facturas CLQ</h5>
            <p class="card-text text-muted small">Desglose de boletos/servicios agrupados por comprobante de liquidación.</p>
            <a href="{{ route('report.clqDetalle') }}" class="btn btn-sm btn-outline-warning w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Ver Detalle</a>
          </div>
        </div>
      </div>

      <!-- Facturas Terceros Mandante -->
      <div class="col-md-6 col-lg-3 report-item" data-title="facturas terceros mandante proveedores">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-2">
              <div class="icon-wrapper bg-label-warning"><i class="fa-solid fa-user-tag"></i></div>
            </div>
            <h5 class="card-title mb-1 fw-semibold">Facturas Mandante</h5>
            <p class="card-text text-muted small">Reporte de facturas emitidas por cuenta de terceros (Mandantes).</p>
            <a href="{{ route('report.facturasTerceros') }}" class="btn btn-sm btn-outline-warning w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Ver Mandantes</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CATEGORÍA 4: FISCALES, ANEXOS Y RESÚMENES -->
  <div class="mb-4 report-category">
    <div class="d-flex align-items-center mb-3">
      <div class="icon-wrapper bg-label-info me-2" style="width: 36px; height: 36px; font-size: 1.1rem;">
        <i class="fa-solid fa-building-columns"></i>
      </div>
      <h4 class="mb-0 fw-bold">Declaraciones, Anexos MH y Resúmenes</h4>
    </div>
    
    <div class="row g-3">
      <!-- Control de IVA y Pago a Cuenta -->
      <div class="col-md-6 col-lg-4 report-item" data-title="control de iva pago a cuenta debito credito impuesto hacienda">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-wrapper bg-label-info me-3"><i class="fa-solid fa-calculator"></i></div>
              <div>
                <h5 class="card-title mb-0 fw-semibold">Control IVA y Pago a Cuenta</h5>
                <small class="text-muted">Cálculo de impuestos mensual</small>
              </div>
            </div>
            <p class="card-text text-muted small">Resumen de IVA Débito vs Crédito Fiscal, impuesto a pagar y retención de pago a cuenta (1.75%).</p>
            <a href="{{ route('report.ivacontrol') }}" class="btn btn-sm btn-outline-info w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Abrir Control IVA</a>
          </div>
        </div>
      </div>

      <!-- Anexos de Hacienda F-07 -->
      <div class="col-md-6 col-lg-4 report-item" data-title="anexos de hacienda f-07 f07 exportacion txt excel mh csv">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-wrapper bg-label-danger me-3"><i class="fa-solid fa-file-export"></i></div>
              <div>
                <h5 class="card-title mb-0 fw-semibold">Anexos Hacienda (F-07)</h5>
                <small class="text-muted">Archivos para sistema MH</small>
              </div>
            </div>
            <p class="card-text text-muted small">Generación de archivos CSV/Excel formateados para la declaración F-07 del Ministerio de Hacienda.</p>
            <a href="{{ route('report.hacienda-anexos') }}" class="btn btn-sm btn-outline-danger w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Exportar Anexos</a>
          </div>
        </div>
      </div>

      <!-- Resumen Anual -->
      <div class="col-md-6 col-lg-4 report-item" data-title="resumen anual ventas compras por año acumulado">
        <div class="card report-card">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="icon-wrapper bg-label-secondary me-3"><i class="fa-solid fa-calendar-days"></i></div>
              <div>
                <h5 class="card-title mb-0 fw-semibold">Resumen Anual</h5>
                <small class="text-muted">Ventas y Compras por Año</small>
              </div>
            </div>
            <p class="card-text text-muted small">Comparativo mensualizado de ventas y compras del ejercicio fiscal completo.</p>
            <a href="{{ route('report.reportyear') }}" class="btn btn-sm btn-outline-secondary w-100 mt-2"><i class="fa-solid fa-arrow-right me-1"></i> Ver Resumen Anual</a>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('reportSearch');
  const reportItems = document.querySelectorAll('.report-item');
  const categories = document.querySelectorAll('.report-category');

  searchInput.addEventListener('keyup', function () {
    const term = this.value.toLowerCase().trim();

    reportItems.forEach(item => {
      const keywords = item.getAttribute('data-title').toLowerCase();
      if (keywords.includes(term)) {
        item.style.display = 'block';
      } else {
        item.style.display = 'none';
      }
    });

    // Ocultar categorías vacías
    categories.forEach(category => {
      const visibleItems = category.querySelectorAll('.report-item[style*="display: block"], .report-item:not([style*="display: none"])');
      if (visibleItems.length === 0 && term !== '') {
        category.style.display = 'none';
      } else {
        category.style.display = 'block';
      }
    });
  });
});
</script>
@endsection
