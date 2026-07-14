<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;

class HaciendaAnexoController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::query()->orderBy('name')->get(['id', 'name']);
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');

        $companyId = $request->get('company_id');
        $year = $request->get('year', $currentYear);
        $month = $request->get('month', $currentMonth);

        $counts = [
            'anexo1' => 0,
            'anexo2' => 0,
            'anexo3' => 0,
        ];

        if ($companyId) {
            $counts['anexo1'] = Sale::query()
                ->join('dte', 'dte.sale_id', '=', 'sales.id')
                ->where('sales.company_id', $companyId)
                ->whereYear('sales.date', $year)
                ->whereMonth('sales.date', $month)
                ->whereIn('sales.state', [0, 1])
                ->whereNotNull('dte.codigoGeneracion')
                ->where('dte.codigoGeneracion', '!=', '')
                ->where('sales.typedocument_id', 3)
                ->count();

            $counts['anexo2'] = Sale::query()
                ->join('dte', 'dte.sale_id', '=', 'sales.id')
                ->where('sales.company_id', $companyId)
                ->whereYear('sales.date', $year)
                ->whereMonth('sales.date', $month)
                ->whereIn('sales.state', [0, 1])
                ->whereNotNull('dte.codigoGeneracion')
                ->where('dte.codigoGeneracion', '!=', '')
                ->where('sales.typedocument_id', 6)
                ->count();

            $counts['anexo3'] = Purchase::where('company_id', $companyId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->count();
        }

        return view('hacienda-anexos.index', [
            'companies' => $companies,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'companyId' => $companyId ? (int)$companyId : null,
            'year' => (int)$year,
            'month' => (int)$month,
            'counts' => $counts,
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:' . ((int) date('Y') + 1)],
            'month' => ['required', 'integer', 'between:1,12'],
            'annex_type' => ['required', 'in:anexo1,anexo2,anexo3'],
            'operation_type' => ['nullable', 'integer', 'between:0,4'],
            'income_type' => ['nullable', 'integer', 'between:0,13'],
            'classification' => ['nullable', 'integer', 'in:1,2'],
            'sector' => ['nullable', 'integer', 'between:1,4'],
            'cost_type' => ['nullable', 'integer', 'between:1,7'],
        ]);

        $company = Company::findOrFail($validated['company_id']);
        $year = (int) $validated['year'];
        $month = str_pad((string) $validated['month'], 2, '0', STR_PAD_LEFT);

        $rows = match ($validated['annex_type']) {
            'anexo1' => $this->buildAnexo1Rows($validated),
            'anexo2' => $this->buildAnexo2Rows($validated),
            default => $this->buildAnexo3Rows($validated),
        };

        $suffix = strtoupper($validated['annex_type']);
        $filename = "hacienda_{$suffix}_{$company->id}_{$year}{$month}.csv";

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildAnexo1Rows(array $validated): array
    {
        $sales = Sale::query()
            ->join('clients', 'clients.id', '=', 'sales.client_id')
            ->leftJoin('dte', 'dte.sale_id', '=', 'sales.id')
            ->join('typedocuments', 'typedocuments.id', '=', 'sales.typedocument_id')
            ->where('sales.company_id', $validated['company_id'])
            ->whereYear('sales.date', $validated['year'])
            ->whereMonth('sales.date', $validated['month'])
            ->whereIn('sales.state', [0, 1])
            ->whereNotNull('dte.codigoGeneracion')
            ->where('dte.codigoGeneracion', '!=', '')
            ->where('sales.typedocument_id', 3) // Crédito fiscal (CCF)
            ->select([
                'sales.id',
                'sales.date',
                'sales.state',
                'sales.totalamount',
                'sales.nu_doc',
                'clients.nit',
                'clients.ncr',
                'clients.firstname',
                'clients.secondname',
                'clients.firstlastname',
                'clients.secondlastname',
                'clients.comercial_name',
                'clients.name_contribuyente',
                'clients.tpersona',
                'typedocuments.codemh',
                'dte.id_doc',
                'dte.codigoGeneracion',
                'dte.selloRecibido',
            ])
            ->selectRaw('COALESCE((SELECT SUM(sd.exempt) FROM salesdetails sd WHERE sd.sale_id = sales.id),0) AS exenta')
            ->selectRaw('COALESCE((SELECT SUM(sd.nosujeta) FROM salesdetails sd WHERE sd.sale_id = sales.id),0) AS nosujeta')
            ->selectRaw('COALESCE((SELECT SUM(sd.pricesale) FROM salesdetails sd WHERE sd.sale_id = sales.id),0) AS gravada')
            ->selectRaw('COALESCE((SELECT SUM(sd.detained13) FROM salesdetails sd WHERE sd.sale_id = sales.id),0) AS iva')
            ->orderBy('sales.date')
            ->orderBy('sales.id')
            ->get();

        $operationType = (string) ($validated['operation_type'] ?? 0);
        $incomeType = (string) ($validated['income_type'] ?? 0);
        $rows = [];

        foreach ($sales as $sale) {
            $isAnnulled = ($sale->state == 0);
            $exenta = $isAnnulled ? 0.0 : (float) $sale->exenta;
            $noSujeta = $isAnnulled ? 0.0 : (float) $sale->nosujeta;
            $gravada = $isAnnulled ? 0.0 : (float) $sale->gravada;
            $iva = $isAnnulled ? 0.0 : (float) $sale->iva;
            $total = $exenta + $noSujeta + $gravada + $iva;

            $clientName = $isAnnulled ? 'ANULADO' : ($sale->tpersona === 'J'
                ? ($sale->name_contribuyente ?: $sale->comercial_name)
                : trim(implode(' ', array_filter([
                    $sale->firstname,
                    $sale->secondname,
                    $sale->firstlastname,
                    $sale->secondlastname,
                ]))));

            $isDte = !empty($sale->codigoGeneracion);
            $claseDoc = $isDte ? '4' : '1';

            $colD = '';
            $colE = '';
            $colF = '';

            if ($isDte) {
                $colD = $this->normalizeDocument((string) $sale->codigoGeneracion);
                $colE = $this->normalizeDocument((string) $sale->selloRecibido);
                $colF = $this->normalizeDocument((string) $sale->id_doc);
            } else {
                $colD = '';
                $colE = '';
                $colF = $this->normalizeDocument((string) ($sale->nu_doc ?: $sale->id));
            }

            $nit = $this->normalizeTaxId((string) $sale->nit);
            $ncr = $this->normalizeTaxId((string) $sale->ncr);

            $duiVal = '';
            $nitOrNcrVal = '';
            if (strlen($nit) === 9) {
                $duiVal = $nit;
                $nitOrNcrVal = $ncr ?: '';
            } else {
                $nitOrNcrVal = $nit ?: $ncr;
            }

            $rows[] = [
                date('d/m/Y', strtotime((string) $sale->date)), // A
                $claseDoc, // B
                $sale->codemh ?: '03', // C
                $isDte ? $colF : '', // D (Número de Resolución -> Número de Control para DTE, vacío para físico)
                $colE, // E (Serie de Documento -> Sello de Recepción para DTE, vacío para físico)
                $isDte ? $colD : $colF, // F (Número Correlativo de Documento -> Código de Generación para DTE, correlativo físico para físico)
                '', // G (Número de Control Interno -> vacío para DTE)
                $nitOrNcrVal, // H
                mb_strtoupper($clientName ?: 'CONSUMIDOR FINAL'), // I
                $this->formatAmount($exenta), // J
                $this->formatAmount($noSujeta), // K
                $this->formatAmount($gravada), // L
                $this->formatAmount($iva), // M
                $this->formatAmount(0), // N
                $this->formatAmount(0), // O
                $this->formatAmount($total), // P
                $duiVal, // Q DUI
                $operationType, // R
                $incomeType, // S
                '1', // T
            ];
        }

        return $rows;
    }

    private function buildAnexo2Rows(array $validated): array
    {
        $sales = Sale::query()
            ->leftJoin('dte', 'dte.sale_id', '=', 'sales.id')
            ->join('typedocuments', 'typedocuments.id', '=', 'sales.typedocument_id')
            ->where('sales.company_id', $validated['company_id'])
            ->whereYear('sales.date', $validated['year'])
            ->whereMonth('sales.date', $validated['month'])
            ->whereIn('sales.state', [0, 1])
            ->whereNotNull('dte.codigoGeneracion')
            ->where('dte.codigoGeneracion', '!=', '')
            ->where('sales.typedocument_id', 6) // Consumidor final
            ->select([
                'sales.id',
                'sales.date',
                'sales.nu_doc',
                'sales.state',
                'typedocuments.codemh',
                'dte.codigoGeneracion',
                'dte.id_doc',
            ])
            ->selectRaw('COALESCE((SELECT SUM(sd.exempt) FROM salesdetails sd WHERE sd.sale_id = sales.id),0) AS exenta')
            ->selectRaw('COALESCE((SELECT SUM(sd.nosujeta) FROM salesdetails sd WHERE sd.sale_id = sales.id),0) AS nosujeta')
            ->selectRaw('COALESCE((SELECT SUM(sd.pricesale) FROM salesdetails sd WHERE sd.sale_id = sales.id),0) AS gravada')
            ->orderBy('sales.date')
            ->orderBy('sales.id')
            ->get();

        $operationType = (string) ($validated['operation_type'] ?? 0);
        $incomeType = (string) ($validated['income_type'] ?? 0);

        $rows = [];
        foreach ($sales as $sale) {
            $isDte = !empty($sale->codigoGeneracion);
            $claseDoc = $isDte ? '4' : '1';
            $docType = $sale->codemh ?: '01';

            $isAnnulled = ($sale->state == 0);
            $exenta = $isAnnulled ? 0.0 : (float) $sale->exenta;
            $noSujeta = $isAnnulled ? 0.0 : (float) $sale->nosujeta;
            $gravada = $isAnnulled ? 0.0 : (float) $sale->gravada;
            $total = $exenta + $noSujeta + $gravada;

            $controlNum = $isDte ? $this->normalizeDocument((string) $sale->id_doc) : '';
            $docNum = $isDte ? $this->normalizeDocument((string) $sale->codigoGeneracion) : $this->normalizeDocument((string) ($sale->nu_doc ?: $sale->id));

            $rows[] = [
                date('d/m/Y', strtotime((string) $sale->date)), // A: Fecha
                $claseDoc, // B: Clase de Documento
                $docType, // C: Tipo de Documento Emitido
                $isDte ? 'N/A' : '', // D: Número de Resolución
                $isDte ? 'N/A' : '', // E: Serie de Documento
                $controlNum, // F: Número de Control Interno (del)
                $controlNum, // G: Número de Control Interno (al)
                $docNum, // H: Número de documento (del)
                $docNum, // I: Número de documento (al)
                '', // J: N° de Máquina registradora
                $this->formatAmount($exenta), // K: Ventas Exentas
                $this->formatAmount(0), // L: Ventas Internas Exentas No Sujetas a Proporcionalidad
                $this->formatAmount($noSujeta), // M: Ventas No Sujetas
                $this->formatAmount($gravada), // N: Ventas gravadas locales
                $this->formatAmount(0), // O: Exportaciones dentro del área Centroamericana
                $this->formatAmount(0), // P: Exportaciones fuera del área Centroamericana
                $this->formatAmount(0), // Q: Exportaciones de servicios
                $this->formatAmount(0), // R: Ventas a Zonas Francas y DPA (Tasa cero)
                $this->formatAmount(0), // S: Ventas a Cuenta de Terceros No Domiciliados
                $this->formatAmount($total), // T: Total Ventas
                $operationType, // U: Tipo de Operación (Renta)
                $incomeType, // V: Tipo de Ingreso (Renta)
                '2', // W: Número de anexo
            ];
        }

        return $rows;
    }

    private function buildAnexo3Rows(array $validated): array
    {
        $purchases = Purchase::query()
            ->join('providers', 'providers.id', '=', 'purchases.provider_id')
            ->leftJoin('typedocuments', 'typedocuments.id', '=', 'purchases.document_id')
            ->where('purchases.company_id', $validated['company_id'])
            ->whereYear('purchases.date', $validated['year'])
            ->whereMonth('purchases.date', $validated['month'])
            ->select([
                'purchases.date',
                'purchases.number',
                'purchases.exenta',
                'purchases.gravada',
                'purchases.iva',
                'purchases.contrns',
                'purchases.fovial',
                'purchases.otros',
                'purchases.codigo_generacion',
                'purchases.document_tipo_dte',
                'providers.razonsocial',
                'providers.nit',
                'providers.ncr',
                'typedocuments.codemh',
            ])
            ->orderBy('purchases.date')
            ->orderBy('purchases.id')
            ->get();

        $classification = (string) ($validated['classification'] ?? 1);
        $sector = (string) ($validated['sector'] ?? 2);
        $costType = (string) ($validated['cost_type'] ?? 5);

        $rows = [];
        foreach ($purchases as $purchase) {
            $g = (float) $purchase->exenta + (float) $purchase->contrns + (float) $purchase->fovial + (float) $purchase->otros;
            $j = (float) $purchase->gravada;
            $n = (float) $purchase->iva;
            $total = $g + $j;

            $operationType = $this->resolveOperationType($g, 0, $j);

            $claseDoc = !empty($purchase->codigo_generacion) ? '4' : '1';
            $docType = $purchase->document_tipo_dte ?: ($purchase->codemh ?: '03');
            $docNumRaw = !empty($purchase->codigo_generacion) ? $purchase->codigo_generacion : $purchase->number;

            $nit = $this->normalizeTaxId((string) $purchase->nit);
            $ncr = $this->normalizeTaxId((string) $purchase->ncr);

            $duiVal = '';
            $nitOrNcrVal = '';
            if (strlen($nit) === 9) {
                $duiVal = $nit;
                $nitOrNcrVal = $ncr ?: '';
            } else {
                $nitOrNcrVal = $nit ?: $ncr;
            }

            $rows[] = [
                date('d/m/Y', strtotime((string) $purchase->date)), // A
                $claseDoc, // B
                $docType, // C
                $this->normalizeDocument((string) $docNumRaw), // D
                $nitOrNcrVal, // E
                mb_strtoupper((string) $purchase->razonsocial), // F
                $this->formatAmount($g), // G
                $this->formatAmount(0), // H
                $this->formatAmount(0), // I
                $this->formatAmount($j), // J
                $this->formatAmount(0), // K
                $this->formatAmount(0), // L
                $this->formatAmount(0), // M
                $this->formatAmount($n), // N
                $this->formatAmount($total), // O
                $duiVal, // P DUI
                (string) $operationType, // Q
                $classification, // R
                $sector, // S
                $costType, // T
                '3', // U
            ];
        }

        return $rows;
    }

    private function formatAmount(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function normalizeDocument(string $value): string
    {
        return str_replace(['-', ' '], '', trim($value));
    }

    private function normalizeTaxId(string $value): string
    {
        return str_replace(['-', ' '], '', trim($value));
    }

    private function resolveOperationType(float $exenta, float $noSujeta, float $gravada): int
    {
        $hasExenta = $exenta > 0;
        $hasNoSujeta = $noSujeta > 0;
        $hasGravada = $gravada > 0;

        $count = ($hasExenta ? 1 : 0) + ($hasNoSujeta ? 1 : 0) + ($hasGravada ? 1 : 0);
        if ($count === 0) {
            return 0;
        }
        if ($count > 1) {
            return 4;
        }
        if ($hasGravada) {
            return 1;
        }
        if ($hasExenta) {
            return 2;
        }

        return 3;
    }

    private function mapClaseDocForExcel(string $clase): string
    {
        return match ($clase) {
            '4' => '4. DOCUMENTO TRIBUTARIO ELECTRÓNICO (DTE)',
            '1' => '1. IMPRESO POR IMPRENTA O TIQUETES',
            default => $clase,
        };
    }

    private function mapTipoDocForExcel(string $tipo): string
    {
        return match ($tipo) {
            '01' => '01. FACTURA',
            '03' => '03. COMPROBANTE DE CRÉDITO FISCAL',
            '05' => '05. NOTA DE CRÉDITO',
            '06' => '06. NOTA DE DÉBITO',
            '07' => '07. Comprobante de Retención',
            '10' => '10. TIQUETES DE MAQUINA REGISTRADORA',
            '11' => '11. FACTURA DE EXPORTACIÓN',
            '14' => '14. FACTURA DE SUJETO EXCLUIDO',
            default => $tipo,
        };
    }

    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:' . ((int) date('Y') + 1)],
            'month' => ['required', 'integer', 'between:1,12'],
            'annex_type' => ['required', 'in:anexo1,anexo2,anexo3,todos'],
            'operation_type' => ['nullable', 'integer', 'between:0,4'],
            'income_type' => ['nullable', 'integer', 'between:0,13'],
            'classification' => ['nullable', 'integer', 'in:1,2'],
            'sector' => ['nullable', 'integer', 'between:1,4'],
            'cost_type' => ['nullable', 'integer', 'between:1,7'],
        ]);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // Remove default sheet

        $company = Company::findOrFail($validated['company_id']);
        $year = (int) $validated['year'];
        $month = str_pad((string) $validated['month'], 2, '0', STR_PAD_LEFT);

        $annexTypes = ($validated['annex_type'] === 'todos') ? ['anexo1', 'anexo2', 'anexo3'] : [$validated['annex_type']];
        $hasData = false;

        foreach ($annexTypes as $type) {
            $rows = [];
            $headers = [];
            $sheetTitle = '';

            if ($type === 'anexo1') {
                $rows = $this->buildAnexo1Rows($validated);
                $sheetTitle = '1';
                $headers = [
                    'Fecha Emisión',
                    'Clase de Documento',
                    'Tipo de Documento Emitido',
                    'Número de Resolución',
                    'Serie de Documento',
                    'Número Correlativo de Documento',
                    'Número de Control Interno',
                    'NIT o NRC del Cliente',
                    'Nombre del Cliente',
                    'Ventas exentas',
                    'Ventas no sujetas',
                    'Ventas gravadas locales',
                    'Débito Fiscal por ventas gravadas locales',
                    'Ventasa cuenta de terceros no domiciliados',
                    'Débito Fiscal por ventas a cuenta de terceros no domiciliados',
                    'Total Ventas',
                    'DUI del Cliente',
                    'Tipo de Operación (Renta)',
                    'Tipo de Ingreso (Renta)',
                    'Número de anexo'
                ];
            } elseif ($type === 'anexo2') {
                $rows = $this->buildAnexo2Rows($validated);
                $sheetTitle = '2';
                $headers = [
                    'Fecha',
                    'Clase de Documento',
                    'Tipo de Documento Emitido',
                    'Número de Resolución',
                    'Serie de Documento',
                    'Número de Control Interno (del)',
                    'Número de Control Interno (al)',
                    'Número de documento (del)',
                    'Número de documento (al)',
                    'N° de Máquina registradora',
                    'Ventas Exentas',
                    'Ventas Internas Exentas No Sujetas a Proporcionalidad',
                    'Ventas No Sujetas',
                    'Ventas gravadas locales',
                    'Exportaciones dentro del área Centroamericana',
                    'Exportaciones fuera del área Centroamericana',
                    'Exportaciones de servicios',
                    'Ventas a Zonas Francas y DPA (Tasa cero)',
                    'Ventas a Cuenta de Terceros No Domiciliados',
                    'Total Ventas',
                    'Tipo de Operación (Renta)',
                    'Tipo de Ingreso (Renta)',
                    'Número de anexo'
                ];
            } elseif ($type === 'anexo3') {
                $rows = $this->buildAnexo3Rows($validated);
                $sheetTitle = '3';
                $headers = [
                    'Fecha de emisión',
                    'Clase de documento',
                    'Tipo de documento emitido',
                    'Número Correlativo de Documento',
                    'NIT o NRC del proveedor',
                    'Nombre del proveedor',
                    'Compras Internas Exentas',
                    'Internaciones exentas',
                    'Importaciones Exentas y/o no sujetas',
                    'Compras Internas Gravadas',
                    'Internaciones Gravadas de bienes',
                    'Importaciones gravadas de bienes',
                    'Importaciones de servicios gravados',
                    'Crédito Fiscal',
                    'Total Compras',
                    'DUI del Proveedor',
                    'Tipo de Operación (Renta)',
                    'Clasificación (Renta)',
                    'Sector (Renta)',
                    'Tipo de Costo / Gasto (Renta)',
                    'Número de Anexo'
                ];
            }

            if (!empty($rows)) {
                $hasData = true;
            }

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($sheetTitle);

            // 1. Escribir Cabeceras en Fila 1
            $colIndex = 1;
            foreach ($headers as $header) {
                $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . '1';
                $sheet->setCellValue($cellAddress, $header);
                $sheet->getStyle($cellAddress)->getFont()->setBold(true);
                $colIndex++;
            }

            // 2. Escribir Datos a partir de Fila 2
            $rowIndex = 2;
            foreach ($rows as $rowData) {
                $colIndex = 1;
                foreach ($rowData as $val) {
                    $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                    
                    // Map values for Excel dropdown formats
                    if ($colIndex === 2) {
                        $val = $this->mapClaseDocForExcel((string)$val);
                    } elseif ($colIndex === 3) {
                        $val = $this->mapTipoDocForExcel((string)$val);
                    }

                    if (is_numeric($val) && $val !== '' && strpos((string)$val, '/') === false) {
                        $sheet->setCellValueExplicit($cellAddress, (float)$val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->setCellValueExplicit($cellAddress, $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                    $colIndex++;
                }
                $rowIndex++;
            }

            // Auto-dimensionar columnas
            foreach (range(1, count($headers)) as $col) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }
        }

        if (!$hasData && $validated['annex_type'] !== 'todos') {
            return back()->with('error', 'No se encontraron registros para exportar en este período.');
        }

        $suffix = strtoupper($validated['annex_type']);
        $filename = "F07_{$suffix}_{$company->id}_{$year}{$month}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
