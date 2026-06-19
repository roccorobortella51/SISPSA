<?php
// File: C:\xampp\htdocs\sipsa\controllers\RTONReportController.php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\RTONReportSearch;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use app\components\UserHelper;

class RTONReportController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'export-excel' => ['POST', 'GET'],
                    'export-csv' => ['POST', 'GET'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new RTONReportSearch();
        $dataProvider = null;
        $summary = ['total_payments' => 0, 'total_amount' => 0, 'total_usd_amount' => 0, 'total_coverage' => 0, 'unique_clinics' => 0];

        $params = Yii::$app->request->get();

        if (!empty($params) && isset($params['RTONReportSearch'])) {
            $groupedData = $searchModel->getRTONData($params);

            if (!empty($groupedData)) {
                $dataProvider = new \yii\data\ArrayDataProvider([
                    'allModels' => $groupedData,
                    'pagination' => ['pageSize' => 20],
                    'sort' => [
                        'attributes' => [
                            'clinica_nombre',
                        ],
                    ],
                ]);
            }

            $summary = $searchModel->getSummary($params);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'clinicas' => [],
            'summary' => $summary,
        ]);
    }

    public function actionExportExcel()
    {
        $searchModel = new RTONReportSearch();

        // Get params from POST first, then GET as fallback
        $params = Yii::$app->request->post();

        // If POST is empty, try GET
        if (empty($params) || empty($params['RTONReportSearch'])) {
            $params = Yii::$app->request->get();
        }

        // Remove clinica_ids if present (not used anymore)
        if (isset($params['RTONReportSearch']['clinica_ids'])) {
            unset($params['RTONReportSearch']['clinica_ids']);
        }

        // Make sure we have the date range parameters
        $hasDateFilter = false;
        if (isset($params['RTONReportSearch']['date_range_type']) && !empty($params['RTONReportSearch']['date_range_type'])) {
            $hasDateFilter = true;
        }
        if (isset($params['RTONReportSearch']['date_from']) && !empty($params['RTONReportSearch']['date_from'])) {
            $hasDateFilter = true;
        }
        if (isset($params['RTONReportSearch']['date_to']) && !empty($params['RTONReportSearch']['date_to'])) {
            $hasDateFilter = true;
        }

        // If no date filter is set, default to this month
        if (!$hasDateFilter) {
            $params['RTONReportSearch']['date_range_type'] = 'this_month';
        }

        // Get the data with the params
        $data = $searchModel->getRTONDataFlat($params);
        $summary = $searchModel->getSummary($params);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('RTON Reporte');

        // ============================================
        // HEADERS START AT ROW 1
        // ============================================
        $headers = [
            'A' => 'CÓD. SUCURSAL',
            'B' => 'FECHA DE EMISIÓN',
            'C' => 'NÚMERO DE PÓLIZA',
            'D' => 'RAMO',
            'E' => 'FECHA DE INICIO DE VIGENCIA',
            'F' => 'FECHA DE FIN DE VIGENCIA',
            'G' => 'MONTO DE LA COBERTURA',
            'H' => 'MONTO DE LA PRIMA (Bs.)',
            'I' => 'MONTO PAGADO (USD)',
            'J' => 'MONEDA',
            'K' => 'FORMA DE PAGO',
            'L' => 'NOMBRE O RAZÓN SOCIAL DEL PRIMER INTERMEDIARIO',
            'M' => 'CÓD. DE AUTORIZACIÓN',
            'N' => 'NOMBRE O RAZÓN SOCIAL DEL SEGUNDO INTERMEDIARIO',
            'O' => 'CÓD. DE AUTORIZACIÓN',
            'P' => 'NÚMERO DE IDENTIFICACIÓN DEL CONTRATANTE C.I.O RIF',
            'Q' => 'NOMBRE O RAZÓN SOCIAL DEL CONTRATANTE',
            'R' => 'TELÉFONO CELULAR',
            'S' => 'TELÉFONO HAB.',
            'T' => 'TELÉFONO DE EMERGENCIA',
            'U' => 'TIPO DE CLIENTE',
            'V' => 'PROFESION DEL CONTRATANTE',
            'W' => 'OCUPACIÓN DEL CONTRATANTE',
            'X' => 'ESTADO',
            'Y' => 'DIRECCIÓN DEL CONTRATANTE',
            'Z' => 'BENEFICIARIO',
        ];

        // Set headers at row 1
        $headerRow = 1;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $headerRow, $header);
        }

        // Apply header styling - CENTERED
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];
        $sheet->getStyle('A' . $headerRow . ':Z' . $headerRow)->applyFromArray($headerStyle);

        // Data starts at row 2
        $dataRow = 2;
        foreach ($data as $row) {
            $sheet->setCellValue('A' . $dataRow, $row['cod_sucursal'] ?? '');
            $sheet->setCellValue('B' . $dataRow, $row['fecha_emision'] ?? '');
            $sheet->setCellValue('C' . $dataRow, $row['numero_poliza'] ?? '');
            $sheet->setCellValue('D' . $dataRow, $row['ramo'] ?? '');
            $sheet->setCellValue('E' . $dataRow, $row['fecha_ini_vigencia'] ?? '');
            $sheet->setCellValue('F' . $dataRow, $row['fecha_fin_vigencia'] ?? '');
            $sheet->setCellValue('G' . $dataRow, $row['monto_cobertura'] ?? '0.00');
            $sheet->setCellValue('H' . $dataRow, $row['monto_prima'] ?? '0.00');
            $sheet->setCellValue('I' . $dataRow, $row['monto_pagado'] ?? '0.00');
            $sheet->setCellValue('J' . $dataRow, $row['moneda'] ?? '');
            $sheet->setCellValue('K' . $dataRow, $row['forma_pago'] ?? '');
            $sheet->setCellValue('L' . $dataRow, $row['primer_intermediario'] ?? '');
            $sheet->setCellValue('M' . $dataRow, $row['cod_autorizacion_1'] ?? '');
            $sheet->setCellValue('N' . $dataRow, $row['segundo_intermediario'] ?? '');
            $sheet->setCellValue('O' . $dataRow, $row['cod_autorizacion_2'] ?? '');
            $sheet->setCellValue('P' . $dataRow, $row['identificacion_contratante'] ?? '');
            $sheet->setCellValue('Q' . $dataRow, $row['nombre_contratante'] ?? '');
            $sheet->setCellValue('R' . $dataRow, $row['telefono_celular'] ?? '');
            $sheet->setCellValue('S' . $dataRow, $row['telefono_hab'] ?? '');
            $sheet->setCellValue('T' . $dataRow, $row['telefono_emergencia'] ?? '');
            $sheet->setCellValue('U' . $dataRow, $row['tipo_cliente'] ?? '');
            $sheet->setCellValue('V' . $dataRow, $row['profesion_contratante'] ?? '');
            $sheet->setCellValue('W' . $dataRow, $row['ocupacion_contratante'] ?? '');
            $sheet->setCellValue('X' . $dataRow, $row['estado'] ?? '');
            $sheet->setCellValue('Y' . $dataRow, $row['direccion_contratante'] ?? '');
            $sheet->setCellValue('Z' . $dataRow, $row['beneficiario'] ?? '0');
            $dataRow++;
        }

        // Apply number format to currency columns
        $sheet->getStyle('G2:I' . ($dataRow - 1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        // ============================================
        // APPLY CENTER ALIGNMENT TO ALL DATA CELLS
        // ============================================
        if ($dataRow > 2) {
            // Center all data cells horizontally and vertically
            $dataAlignmentStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
            ];
            $sheet->getStyle('A2:Z' . ($dataRow - 1))->applyFromArray($dataAlignmentStyle);

            // For currency columns (G, H, I), keep the number format but center the alignment
            $sheet->getStyle('G2:I' . ($dataRow - 1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }

        // Auto-size columns
        foreach (range('A', 'Z') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Freeze the header row
        $sheet->freezePane('A2');
        $sheet->getRowDimension($headerRow)->setRowHeight(25);

        // Generate filename
        $filename = 'RTON_Reporte_' . date('Y-m-d_His') . '.xlsx';

        while (ob_get_level()) ob_end_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function actionExportCsv()
    {
        $searchModel = new RTONReportSearch();

        // Get params from POST first, then GET as fallback
        $params = Yii::$app->request->post();

        // If POST is empty, try GET
        if (empty($params) || empty($params['RTONReportSearch'])) {
            $params = Yii::$app->request->get();
        }

        // Remove clinica_ids if present (not used anymore)
        if (isset($params['RTONReportSearch']['clinica_ids'])) {
            unset($params['RTONReportSearch']['clinica_ids']);
        }

        // Make sure we have the date range parameters
        $hasDateFilter = false;
        if (isset($params['RTONReportSearch']['date_range_type']) && !empty($params['RTONReportSearch']['date_range_type'])) {
            $hasDateFilter = true;
        }
        if (isset($params['RTONReportSearch']['date_from']) && !empty($params['RTONReportSearch']['date_from'])) {
            $hasDateFilter = true;
        }
        if (isset($params['RTONReportSearch']['date_to']) && !empty($params['RTONReportSearch']['date_to'])) {
            $hasDateFilter = true;
        }

        // If no date filter is set, default to this month
        if (!$hasDateFilter) {
            $params['RTONReportSearch']['date_range_type'] = 'this_month';
        }

        $data = $searchModel->getRTONDataFlat($params);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="RTON_Reporte_' . date('Y-m-d_His') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $headers = [
            'CÓD. SUCURSAL',
            'FECHA DE EMISIÓN',
            'NÚMERO DE PÓLIZA',
            'RAMO',
            'FECHA DE INICIO DE VIGENCIA',
            'FECHA DE FIN DE VIGENCIA',
            'MONTO DE LA COBERTURA',
            'MONTO DE LA PRIMA (Bs.)',
            'MONTO PAGADO (USD)',
            'MONEDA',
            'FORMA DE PAGO',
            'PRIMER INTERMEDIARIO',
            'CÓD. AUTORIZACIÓN',
            'SEGUNDO INTERMEDIARIO',
            'CÓD. AUTORIZACIÓN',
            'IDENTIFICACIÓN CONTRATANTE',
            'NOMBRE CONTRATANTE',
            'TELÉFONO CELULAR',
            'TELÉFONO HAB.',
            'TELÉFONO EMERGENCIA',
            'TIPO CLIENTE',
            'PROFESIÓN',
            'OCUPACIÓN',
            'ESTADO',
            'DIRECCIÓN',
            'BENEFICIARIO'
        ];

        fputcsv($output, $headers);

        foreach ($data as $row) {
            $csvRow = [
                $row['cod_sucursal'] ?? '',
                $row['fecha_emision'] ?? '',
                $row['numero_poliza'] ?? '',
                $row['ramo'] ?? '',
                $row['fecha_ini_vigencia'] ?? '',
                $row['fecha_fin_vigencia'] ?? '',
                $row['monto_cobertura'] ?? '0.00',
                $row['monto_prima'] ?? '0.00',
                $row['monto_pagado'] ?? '0.00',
                $row['moneda'] ?? '',
                $row['forma_pago'] ?? '',
                $row['primer_intermediario'] ?? '',
                $row['cod_autorizacion_1'] ?? '',
                $row['segundo_intermediario'] ?? '',
                $row['cod_autorizacion_2'] ?? '',
                $row['identificacion_contratante'] ?? '',
                $row['nombre_contratante'] ?? '',
                $row['telefono_celular'] ?? '',
                $row['telefono_hab'] ?? '',
                $row['telefono_emergencia'] ?? '',
                $row['tipo_cliente'] ?? '',
                $row['profesion_contratante'] ?? '',
                $row['ocupacion_contratante'] ?? '',
                $row['estado'] ?? '',
                $row['direccion_contratante'] ?? '',
                $row['beneficiario'] ?? '0',
            ];
            fputcsv($output, $csvRow);
        }

        fclose($output);
        exit;
    }
}
