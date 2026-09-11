<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\EventoReportSearch;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EventoReportController extends Controller
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
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new EventoReportSearch();
        $data = [];
        $summary = ['total_rows' => 0];

        $params = Yii::$app->request->get();

        if (!empty($params) && isset($params['EventoReportSearch'])) {
            $data = $searchModel->getPsnpData($params);
            $summary = $searchModel->getSummary($params);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'data' => $data,
            'summary' => $summary,
        ]);
    }

    public function actionExportExcel()
    {
        $searchModel = new EventoReportSearch();

        $params = Yii::$app->request->post();
        if (empty($params) || empty($params['EventoReportSearch'])) {
            $params = Yii::$app->request->get();
        }

        $hasDateFilter = false;
        if (isset($params['EventoReportSearch']['date_range_type']) && !empty($params['EventoReportSearch']['date_range_type'])) {
            $hasDateFilter = true;
        }
        if (isset($params['EventoReportSearch']['date_from']) && !empty($params['EventoReportSearch']['date_from'])) {
            $hasDateFilter = true;
        }
        if (isset($params['EventoReportSearch']['date_to']) && !empty($params['EventoReportSearch']['date_to'])) {
            $hasDateFilter = true;
        }

        if (!$hasDateFilter) {
            $params['EventoReportSearch']['date_range_type'] = 'since_incidents';
        }

        $data = $searchModel->getPsnpData($params);
        $summary = $searchModel->getSummary($params);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matriz PSNP');

        $headers = [
            'A' => 'N° de inscripción en la SUDEASEG',
            'B' => 'N° de Póliza',
            'C' => 'Fecha de inicio de vigencia',
            'D' => 'Fecha de fin de vigencia',
            'E' => 'N° de Evento de salud',
            'F' => 'Nombre del Afiliado',
            'G' => 'Cédula del Afiliado',
            'H' => 'Ramo',
            'I' => 'Nombre del Tomador',
            'J' => 'Cédula del Tomador',
            'K' => 'Ubicación Geográfica',
            'L' => 'Suma Asegurada',
            'M' => 'Causa del Evento',
            'N' => 'Fecha/Hora Ocurrencia',
            'O' => 'Fecha de Notificación',
            'P' => 'Valor estimado del Evento',
            'Q' => 'Estatus del evento',
            'R' => 'Moneda original',
            'S' => 'Tipo de cambio',
            'T' => 'Código CCR',
        ];

        $headerRow = 1;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $headerRow, $header);
        }

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
        $sheet->getStyle('A' . $headerRow . ':T' . $headerRow)->applyFromArray($headerStyle);

        $dataRow = 2;
        foreach ($data as $row) {
            $sheet->setCellValue('A' . $dataRow, $row['n_inscripcion_sudeaseg'] ?? '');
            $sheet->setCellValue('B' . $dataRow, $row['n_poliza'] ?? '');
            $sheet->setCellValue('C' . $dataRow, $row['fecha_ini_vigencia'] ?? '');
            $sheet->setCellValue('D' . $dataRow, $row['fecha_fin_vigencia'] ?? '');
            $sheet->setCellValue('E' . $dataRow, $row['n_evento_salud'] ?? '');
            $sheet->setCellValue('F' . $dataRow, $row['nombre_afiliado'] ?? '');
            $sheet->setCellValue('G' . $dataRow, $row['cedula_afiliado'] ?? '');
            $sheet->setCellValue('H' . $dataRow, $row['ramo'] ?? '');
            $sheet->setCellValue('I' . $dataRow, $row['nombre_tomador'] ?? '');
            $sheet->setCellValue('J' . $dataRow, $row['cedula_tomador'] ?? '');
            $sheet->setCellValue('K' . $dataRow, $row['ubicacion_geografica'] ?? '');
            $sheet->setCellValue('L' . $dataRow, $row['suma_asegurada'] ?? '0,00');
            $sheet->setCellValue('M' . $dataRow, $row['causa_evento'] ?? '');
            $sheet->setCellValue('N' . $dataRow, $row['fecha_hora_ocurrencia'] ?? '');
            $sheet->setCellValue('O' . $dataRow, $row['fecha_notificacion'] ?? '');
            $sheet->setCellValue('P' . $dataRow, $row['valor_estimado_evento'] ?? '0,00');
            $sheet->setCellValue('Q' . $dataRow, $row['estatus_evento'] ?? '');
            $sheet->setCellValue('R' . $dataRow, $row['moneda_original'] ?? '');
            $sheet->setCellValue('S' . $dataRow, $row['tipo_cambio'] ?? '');
            $sheet->setCellValue('T' . $dataRow, $row['codigo_ccr'] ?? '');
            $dataRow++;
        }

        if ($dataRow > 2) {
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
            $sheet->getStyle('A2:T' . ($dataRow - 1))->applyFromArray($dataAlignmentStyle);
        }

        foreach (range('A', 'T') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $sheet->freezePane('A2');
        $sheet->getRowDimension($headerRow)->setRowHeight(25);

        // IMPORTANT: Fixed filename format per circular
        $registrationCode = 'MP000'; // Should come from configuration
        $filename = $registrationCode . 'PSNP' . date('Y') . '.xlsx';

        while (ob_get_level()) ob_end_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
