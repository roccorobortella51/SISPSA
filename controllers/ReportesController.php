<?php
// app/controllers/ReportesController.php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\components\UserHelper;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Pagos;
use app\models\UserDatos;
use app\models\Contratos;
use app\models\Cuotas;
use app\models\PagosReporteSearch;
use kartik\mpdf\Pdf;

class ReportesController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'get-pagos-detail', 'generate-pdf', 'export-excel', 'comisiones', 'get-comisiones-detail', 'generate-comisiones-pdf-tcpdf', 'generate-comisiones-pdf', 'export-comisiones-excel'],
                        'roles' => ['superadmin', 'FINANZAS', 'COORDINADOR-CLINICA', 'Agente'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'get-pagos-detail' => ['POST'],
                    'index' => ['GET'],
                    'generate-pdf' => ['GET'],
                    'export-excel' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * Deshabilita la validación CSRF solo para la acción AJAX de reporte.
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['get-pagos-detail'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Normaliza el filtro de clinicas y aplica restriccion por rol.
     */
    private function resolveClinicaFilter($rawClinicas): array
    {
        if (UserHelper::hasClinicAccess()) {
            $myClinicaId = UserHelper::getMyClinicaId();
            return $myClinicaId ? [(string)$myClinicaId] : [];
        }

        $clinicas = [];
        if (is_array($rawClinicas)) {
            $clinicas = $rawClinicas;
        } elseif ($rawClinicas !== null && $rawClinicas !== '') {
            $rawClinicas = (string)$rawClinicas;
            $clinicas = strpos($rawClinicas, ',') !== false
                ? explode(',', $rawClinicas)
                : [$rawClinicas];
        }

        $clinicas = array_values(array_filter(array_map(static function ($value) {
            $value = trim((string)$value);
            return $value;
        }, $clinicas), static function ($value) {
            return $value !== '';
        }));

        if (in_array('todas', $clinicas, true)) {
            return ['todas'];
        }

        return array_values(array_filter($clinicas, static function ($value) {
            return ctype_digit((string)$value);
        }));
    }

    /**
     * Muestra la vista principal del reporte (Grid y filtros).
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Obtiene el detalle de pagos para el reporte (vía AJAX)
     */
    public function actionGetPagosDetail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;

        // Parámetros de la vista
        $range = $request->post('range', 'day');
        $specificDate = $request->post('specific_date');
        $customRange = $request->post('custom_range', false);
        $dateFrom = $request->post('date_from');
        $dateTo = $request->post('date_to');
        $status = $request->post('status', 'Por Conciliar');
        $clinicas = $this->resolveClinicaFilter($request->post('clinicas', []));

        // Inicializar fechas
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $title = "Detalle de Pagos de Hoy";

        // Lógica de rango de fechas
        if ($customRange && $dateFrom && $dateTo) {
            $startDate = $dateFrom;
            $endDate = $dateTo;
            $title = "Detalle de Pagos para el período personalizado";
        } else if ($specificDate && $specificDate !== 'Invalid date') {
            $startDate = $specificDate;
            $endDate = $specificDate;
            $title = "Detalle de Pagos para el día: " . Yii::$app->formatter->asDate($specificDate, 'long');
        } else {
            switch ($range) {
                case 'week':
                    $startDate = date('Y-m-d', strtotime('last Monday'));
                    $title = "Detalle de Pagos Semanales";
                    break;
                case 'month':
                    $startDate = date('Y-m-01');
                    $title = "Detalle de Pagos Mensuales";
                    break;
                case 'last-month':
                    $startDate = date('Y-m-01', strtotime('first day of last month'));
                    $endDate = date('Y-m-t', strtotime('last month'));
                    $title = "Detalle de Pagos del Mes Anterior";
                    break;
                case 'day':
                default:
                    break;
            }
        }

        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $title .= " ({$statusLabel})";

        // Crear y configurar el modelo de búsqueda
        $searchModel = new PagosReporteSearch();

        // Obtener el resumen general
        $summary = $searchModel->obtenerResumenGeneral($startDate, $endDate, $status, $clinicas);

        // Obtener el resumen por clínica
        $summaryPorClinica = [];
        if (!empty($clinicas)) {
            if (in_array('todas', $clinicas)) {
                $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, []);
            } else {
                $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, $clinicas);
            }
        } else {
            $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, []);
        }

        // Obtener el dataProvider
        $params = $request->post();

        if (!empty($clinicas) && !in_array('todas', $clinicas)) {
            $dataProvider = $searchModel->searchConClinicas($params, $startDate, $endDate, $status, $clinicas);
        } else {
            $dataProvider = $searchModel->search($params, $startDate, $endDate, $status, $clinicas);
        }

        // Configurar ordenamiento
        $existingSort = $dataProvider->sort;
        $dataProvider->sort = [
            'defaultOrder' => [
                'fecha_pago' => SORT_ASC,
            ],
            'attributes' => array_merge(
                $existingSort ? $existingSort->attributes : [],
                [
                    'fecha_pago' => [
                        'asc' => ['fecha_pago' => SORT_ASC],
                        'desc' => ['fecha_pago' => SORT_DESC],
                        'default' => SORT_ASC,
                        'label' => 'Fecha de Pago',
                    ],
                ]
            ),
        ];

        return [
            'success' => true,
            'html' => $this->renderPartial('_pagos-grid', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'title' => $title,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'summary' => $summary,
                'summaryPorClinica' => $summaryPorClinica,
                'clinicasSeleccionadas' => $clinicas,
            ]),
        ];
    }

    /**
     * Genera el reporte en PDF
     */
    public function actionGeneratePdf($range = 'day', $specific_date = null, $status = 'Por Conciliar')
    {
        $request = Yii::$app->request;

        $status = $request->get('status', 'Por Conciliar');
        $customRange = $request->get('custom_range', false);
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $clinicasArray = $this->resolveClinicaFilter($request->get('clinicas', ''));

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');

        $title = "REPORTE DE PAGOS - SISTEMA SISPSA";
        $subtitle = "";

        if ($customRange && $dateFrom && $dateTo) {
            $startDate = $dateFrom;
            $endDate = $dateTo;
            $subtitle = "Período Personalizado: " . Yii::$app->formatter->asDate($dateFrom, 'long') . " al " . Yii::$app->formatter->asDate($dateTo, 'long');
        } else if ($specific_date && $specific_date !== 'Invalid date') {
            $startDate = $specific_date;
            $endDate = $specific_date;
            $subtitle = "Fecha Específica: " . Yii::$app->formatter->asDate($specific_date, 'long');
        } else {
            switch ($range) {
                case 'week':
                    $startDate = date('Y-m-d', strtotime('last Monday'));
                    $endDate = date('Y-m-d');
                    $subtitle = "Reporte Semanal";
                    break;
                case 'month':
                    $startDate = date('Y-m-01');
                    $endDate = date('Y-m-d');
                    $subtitle = "Reporte Mensual";
                    break;
                case 'last-month':
                    $startDate = date('Y-m-01', strtotime('first day of last month'));
                    $endDate = date('Y-m-t', strtotime('last month'));
                    $subtitle = "Reporte del Mes Anterior";
                    break;
                case 'day':
                default:
                    $subtitle = "Reporte del Día";
                    break;
            }
        }

        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $subtitle .= " - " . $statusLabel;

        if (!empty($clinicasArray) && !in_array('todas', $clinicasArray)) {
            $clinicasNombres = [];
            foreach ($clinicasArray as $clinicaId) {
                $clinica = \app\models\RmClinica::findOne($clinicaId);
                if ($clinica) {
                    $clinicasNombres[] = $clinica->nombre;
                }
            }
            if (!empty($clinicasNombres)) {
                $clinicasCount = count($clinicasNombres);
                $subtitle .= $clinicasCount > 3 ?
                    " - {$clinicasCount} clínicas" :
                    " - " . implode(', ', array_slice($clinicasNombres, 0, 3));
            }
        }

        $searchModel = new PagosReporteSearch();
        $summary = $searchModel->obtenerResumenGeneral($startDate, $endDate, $status, $clinicasArray);

        $summaryPorClinica = [];
        if (!empty($clinicasArray)) {
            if (in_array('todas', $clinicasArray)) {
                $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, []);
            } else {
                $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, $clinicasArray);
            }
        } else {
            $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, []);
        }

        $params = $request->get();
        if (!empty($clinicasArray) && !in_array('todas', $clinicasArray)) {
            $dataProvider = $searchModel->searchConClinicas($params, $startDate, $endDate, $status, $clinicasArray);
        } else {
            $dataProvider = $searchModel->search($params, $startDate, $endDate, $status, $clinicasArray);
        }

        $dataProvider->pagination = false;

        $content = $this->renderPartial('_pagos-pdf-simple', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title' => $title,
            'subtitle' => $subtitle,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'summaryPorClinica' => $summaryPorClinica,
            'clinicasSeleccionadas' => $clinicasArray,
            'generatedAt' => date('d/m/Y H:i:s'),
            'statusLabel' => $statusLabel,
        ]);

        $simpleCss = '
        body { 
            font-family: DejaVu Sans, Arial, sans-serif; 
            font-size: 10pt; 
            line-height: 1.3; 
            color: #000000; 
            margin: 0; 
            padding: 0; 
        }
        .main-title { 
            font-size: 20pt; 
            font-weight: bold; 
            color: #2c3e50; 
            text-align: center; 
            margin: 0 0 5px 0; 
            padding: 0; 
        }
        .subtitle { 
            font-size: 12pt; 
            color: #0078d4; 
            text-align: center; 
            margin: 0 0 20px 0; 
            padding: 0; 
            font-weight: bold; 
        }
        .report-info { 
            background-color: #f8f9fa; 
            border: 1px solid #dee2e6; 
            padding: 10px; 
            margin: 0 0 20px 0; 
            border-radius: 5px; 
        }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { 
            display: table-cell; 
            font-weight: bold; 
            color: #2c3e50; 
            padding: 5px 10px 5px 0; 
            width: 150px; 
        }
        .info-value { 
            display: table-cell; 
            color: #333333; 
            padding: 5px 0; 
        }
        .pdf-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
            font-size: 9pt; 
        }
        .pdf-table th { 
            background-color: #2c3e50; 
            color: white; 
            font-weight: bold; 
            padding: 8px; 
            text-align: center; 
            border: 1px solid #1a2530; 
        }
        .pdf-table td { 
            padding: 6px; 
            border: 1px solid #dddddd; 
            text-align: center; 
        }
        .pdf-table td.text-left { text-align: left; }
        .pdf-table td.text-right { text-align: right; }
        .pdf-table tr:nth-child(even) { background-color: #f8f9fa; }
        .badge { 
            display: inline-block; 
            padding: 3px 8px; 
            border-radius: 3px; 
            font-size: 8pt; 
            font-weight: bold; 
        }
        .badge-success { background-color: #dff6dd; color: #107c10; }
        .badge-warning { background-color: #fff4ce; color: #7a5c00; }
        .final-total-container { 
            text-align: center; 
            margin: 40px 0; 
            page-break-inside: avoid; 
        }
        .final-total-box { 
            display: inline-block; 
            background-color: #2c3e50; 
            border: 3px solid #0078d4; 
            border-radius: 10px; 
            padding: 25px 40px; 
            min-width: 400px; 
        }
        .final-total-label { 
            font-size: 16pt; 
            font-weight: bold; 
            color: #ffffff; 
            margin: 0 0 10px 0; 
            text-transform: uppercase; 
        }
        .final-total-value { 
            font-size: 24pt; 
            font-weight: bold; 
            color: #4cd964; 
            margin: 0; 
            font-family: "Courier New", monospace; 
        }
        .final-total-info { 
            font-size: 10pt; 
            color: #cccccc; 
            margin: 10px 0 0 0; 
            font-weight: bold; 
        }
        .report-footer { 
            margin-top: 30px; 
            padding-top: 15px; 
            border-top: 1px solid #dddddd; 
            text-align: center; 
            font-size: 8pt; 
            color: #666666; 
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-20 { margin-bottom: 20px; }
        .mt-20 { margin-top: 20px; }
        .mt-30 { margin-top: 30px; }
        ';

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            'destination' => Pdf::DEST_DOWNLOAD,
            'content' => $content,
            'options' => ['title' => $title],
            'marginLeft' => 10,
            'marginRight' => 10,
            'marginTop' => 15,
            'marginBottom' => 15,
            'marginHeader' => 5,
            'marginFooter' => 8,
            'methods' => [
                'SetHeader' => ['Sistema SISPSA - Reporte de Comisiones||Página {PAGENO} de {nb}'],
                'SetFooter' => ['Generado el ' . date('d/m/Y H:i:s') . '||'],
            ],
            'defaultFont' => 'dejavusans',
        ]);

        $filename = 'Reporte_Pagos_' . date('Ymd_His') . '.pdf';
        $pdf->filename = $filename;

        try {
            return $pdf->render();
        } catch (\Exception $e) {
            Yii::error('Error generando PDF: ' . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error al generar el PDF: ' . $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    /**
     * Exporta el reporte a Excel usando PHPSpreadsheet
     */
    public function actionExportExcel($range = null, $specific_date = null, $status = 'Por Conciliar')
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $request = Yii::$app->request;

        $status = $request->get('status', $status);
        $clinicasRaw = $request->get('clinicas', '');
        $clinicasArray = $this->resolveClinicaFilter($clinicasRaw);
        $customRange = $request->get('custom_range', false);
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $rangeFromGet = $request->get('range');
        if ($rangeFromGet && $rangeFromGet !== 'undefined') {
            $range = $rangeFromGet;
        }

        $startDate = null;
        $endDate = null;

        if (($customRange === true || $customRange === 'true' || $customRange === 1 || $customRange === '1') && $dateFrom && $dateTo) {
            $startDate = date('Y-m-d', strtotime($dateFrom));
            $endDate = date('Y-m-d', strtotime($dateTo));
        } elseif ($specific_date && $specific_date !== 'Invalid date' && $specific_date !== 'undefined') {
            $startDate = date('Y-m-d', strtotime($specific_date));
            $endDate = $startDate;
        } elseif ($range && $range !== 'undefined' && $range !== 'custom') {
            $today = date('Y-m-d');
            switch ($range) {
                case 'week':
                    $startDate = date('Y-m-d', strtotime('last Monday'));
                    $endDate = $today;
                    break;
                case 'month':
                    $startDate = date('Y-m-01');
                    $endDate = $today;
                    break;
                case 'last-month':
                    $startDate = date('Y-m-01', strtotime('first day of last month'));
                    $endDate = date('Y-m-t', strtotime('last month'));
                    break;
                case 'day':
                default:
                    $startDate = $today;
                    $endDate = $today;
                    break;
            }
        } else {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        }

        if (!$startDate || !$endDate) {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        }

        $searchModel = new PagosReporteSearch();
        $searchParams = $request->get();
        $searchParams['date_from'] = $startDate;
        $searchParams['date_to'] = $endDate;

        if (!empty($clinicasArray) && !in_array('todas', $clinicasArray)) {
            $dataProvider = $searchModel->searchConClinicas($searchParams, $startDate, $endDate, $status, $clinicasArray);
        } else {
            $dataProvider = $searchModel->search($searchParams, $startDate, $endDate, $status, $clinicasArray);
        }

        $dataProvider->pagination = false;
        $models = $dataProvider->getModels();

        $summary = $searchModel->obtenerResumenGeneral($startDate, $endDate, $status, $clinicasArray);

        $summaryPorClinica = [];
        if (!empty($clinicasArray)) {
            if (in_array('todas', $clinicasArray)) {
                $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, []);
            } else {
                $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, $clinicasArray);
            }
        } else {
            $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, []);
        }

        $statusLabel = $status === 'todos' ? 'Todos_los_Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por_Conciliar');
        $fileName = 'Reporte_Pagos_' . $startDate . '_al_' . $endDate . '_' . $statusLabel . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detalle de Pagos');

        $sheet->setCellValue('A1', 'Reporte de Operaciones en Efectivo y Transacciones con Activos Virtuales (SUDEASEG-002)');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Periodo:');
        $sheet->setCellValue('B2', date('d/m/Y', strtotime($startDate)) . ' al ' . date('d/m/Y', strtotime($endDate)));

        $sheet->setCellValue('A3', 'Estado:');
        $statusLabelDisplay = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $sheet->setCellValue('B3', $statusLabelDisplay);

        $sheet->setCellValue('A5', 'Generado:');
        $sheet->setCellValue('B5', date('d/m/Y H:i:s'));

        $headerRow = 7;
        $headers = [
            'Fecha de Operación',
            'Nombre o Razón Social',
            'Nº de Identificación',
            'Tipo de Cliente',
            'Ubicación Geográfica',
            'Método de Pago',
            'Tipo de Moneda',
            'Monto'
        ];

        $col = 1;
        foreach ($headers as $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $headerRow;
            $sheet->setCellValue($cell, $header);
            $col++;
        }

        $headerStyle = $sheet->getStyle('A' . $headerRow . ':H' . $headerRow);
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $headerStyle->getFill()->getStartColor()->setARGB('FFCCCCCC');
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $dataRow = $headerRow + 1;

        if (empty($models)) {
            $sheet->setCellValue('A' . $dataRow, 'No hay datos para el período seleccionado');
            $sheet->mergeCells('A' . $dataRow . ':H' . $dataRow);
            $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        } else {
            $states = \app\models\RmEstado::find()->indexBy('id')->all();

            foreach ($models as $model) {
                $fechaPago = 'N/A';
                if ($model->fecha_pago) {
                    try {
                        $date = new \DateTime($model->fecha_pago);
                        $fechaPago = $date->format('d-m-Y');
                    } catch (\Exception $e) {
                        $fechaPago = 'N/A';
                    }
                }
                $sheet->setCellValue('A' . $dataRow, $fechaPago);

                $nombreCompleto = 'N/A';
                if ($model->userDatos) {
                    $nombreCompleto = strtoupper(trim($model->userDatos->nombres . ' ' . $model->userDatos->apellidos));
                }
                $sheet->setCellValue('B' . $dataRow, $nombreCompleto);

                $cedula = $model->userDatos ? $model->userDatos->cedula : 'N/A';
                $sheet->setCellValue('C' . $dataRow, $cedula);

                $tipoCedula = $model->userDatos ? $model->userDatos->tipo_cedula : 'N/A';
                $sheet->setCellValue('D' . $dataRow, $tipoCedula);

                $estadoNombre = 'N/A';
                if ($model->userDatos && $model->userDatos->estado) {
                    if (is_numeric($model->userDatos->estado) && isset($states[$model->userDatos->estado])) {
                        $estadoNombre = strtoupper($states[$model->userDatos->estado]->nombre);
                    } else {
                        $estadoNombre = strtoupper($model->userDatos->estado);
                    }
                }
                $sheet->setCellValue('E' . $dataRow, $estadoNombre);

                $metodoPago = $model->metodo_pago ? strtoupper($model->metodo_pago) : 'N/A';
                $sheet->setCellValue('F' . $dataRow, $metodoPago);

                if (strpos($model->metodo_pago, 'Bolívar') !== false || strpos($model->metodo_pago, 'Bs') !== false) {
                    $sheet->setCellValue('G' . $dataRow, 'Bs.');
                    $sheet->setCellValue('H' . $dataRow, $model->monto_usd);
                } else {
                    $sheet->setCellValue('G' . $dataRow, 'USD');
                    $sheet->setCellValue('H' . $dataRow, $model->monto_pagado);
                }

                $dataRow++;
            }
        }

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $lastDataRow = empty($models) ? $dataRow : $dataRow - 1;
        $sheet->getStyle('H' . ($headerRow + 1) . ':H' . $lastDataRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // Hoja 2: Resumen por Clínica
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Resumen por Clínica');

        $sheet2->setCellValue('A1', 'RESUMEN DE PAGOS POR CLÍNICA');
        $sheet2->mergeCells('A1:F1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $resumenHeaders = ['Clínica', 'RIF', 'Total Pagos', 'Conciliados', 'Pendientes', 'Total (Bs.)'];
        $col = 1;
        foreach ($resumenHeaders as $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet2->setCellValue($colLetter . '3', $header);
            $col++;
        }

        $resumenRow = 4;
        if (!empty($summaryPorClinica)) {
            foreach ($summaryPorClinica as $resumen) {
                $col = 1;
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['clinica_nombre'] ?? 'N/A');
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['clinica_rif'] ?? 'N/A');
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['total_pagos'] ?? 0);
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['conciliados'] ?? 0);
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['pendientes'] ?? 0);
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['total_monto'] ?? 0);

                $resumenRow++;
            }

            foreach (range('A', 'F') as $column) {
                $sheet2->getColumnDimension($column)->setAutoSize(true);
            }

            $totalRow = $resumenRow + 1;
            $sheet2->setCellValue('A' . $totalRow, 'TOTAL GENERAL');
            $sheet2->getStyle('A' . $totalRow)->getFont()->setBold(true);

            $sheet2->setCellValue('C' . $totalRow, array_sum(array_column($summaryPorClinica, 'total_pagos')));
            $sheet2->setCellValue('D' . $totalRow, array_sum(array_column($summaryPorClinica, 'conciliados')));
            $sheet2->setCellValue('E' . $totalRow, array_sum(array_column($summaryPorClinica, 'pendientes')));
            $sheet2->setCellValue('F' . $totalRow, array_sum(array_column($summaryPorClinica, 'total_monto')));

            $sheet2->getStyle('F4:F' . $totalRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        } else {
            $sheet2->setCellValue('A4', 'No hay datos de resumen disponibles');
            $sheet2->mergeCells('A4:F4');
        }

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Muestra la vista del reporte de comisiones
     */
    public function actionComisiones()
    {
        $model = new PagosReporteSearch();
        $title = 'Reporte de Comisiones';

        return $this->render('comisiones', [
            'model' => $model,
            'title' => $title,
        ]);
    }

    /**
     * Obtiene el detalle de comisiones (vía AJAX)
     */
    public function actionGetComisionesDetail()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $request = Yii::$app->request;
            $searchModel = new PagosReporteSearch();

            $range = $request->post('range', 'day');
            $status = $request->post('status', 'todos');
            $clinicas = $this->resolveClinicaFilter($request->post('clinicas', []));
            $dateFrom = $request->post('date_from');
            $dateTo = $request->post('date_to');
            $customRange = $request->post('custom_range', false);

            $dataProvider = $searchModel->searchComisiones(Yii::$app->request->post());
            $summaryPorClinica = $this->calculateComisionesSummaryByClinica($dataProvider->query);
            $summary = $this->calculateComisionesSummary($dataProvider->query);
            $models = $dataProvider->getModels();

            list($startDate, $endDate) = $this->getDateRangeForDisplay($range, $dateFrom, $dateTo);

            $html = $this->renderPartial('_comisiones-grid', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'title' => 'Reporte de Comisiones',
                'summary' => $summary,
                'summaryPorClinica' => $summaryPorClinica,
                'clinicasSeleccionadas' => $clinicas ?: [],
                'startDate' => $startDate,
                'endDate' => $endDate,
                'models' => $models,
                'hasClinicAccess' => UserHelper::hasClinicAccess(),
                'userClinicaIds' => UserHelper::hasClinicAccess() ? UserHelper::getMyClinicaId() : [],
            ]);

            return [
                'success' => true,
                'html' => $html,
            ];
        } catch (\Exception $e) {
            Yii::error('Error generating commission report: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calcula el resumen de comisiones por clínica
     */
    private function calculateComisionesSummaryByClinica($query)
    {
        $summary = [];
        $models = $query->all();
        $clinicaData = [];

        foreach ($models as $model) {
            $clinicaId = null;
            $clinicaNombre = 'Sin Clínica';
            $clinicaRif = 'N/A';

            if ($model->contratos && count($model->contratos) > 0) {
                foreach ($model->contratos as $contrato) {
                    if ($contrato->clinica) {
                        $clinicaId = $contrato->clinica->id;
                        $clinicaNombre = $contrato->clinica->nombre;
                        $clinicaRif = $contrato->clinica->rif;
                        break;
                    }
                }
            }

            $key = $clinicaId ?: 'sin-clinica';

            if (!isset($clinicaData[$key])) {
                $clinicaData[$key] = [
                    'clinica_id' => $clinicaId,
                    'clinica_nombre' => $clinicaNombre,
                    'clinica_rif' => $clinicaRif,
                    'total_comision_asesor_bs' => 0,
                    'total_comision_asesor_usd' => 0,
                    'total_comision_agencia_bs' => 0,
                    'total_comision_agencia_usd' => 0,
                    'total_pagos' => 0,
                    'conciliados' => 0,
                    'pendientes' => 0,
                ];
            }

            $montoUsd = $model->monto_usd;
            $montoPagado = $model->monto_pagado;

            $clinicaData[$key]['total_comision_asesor_bs'] += $montoUsd * 0.10;
            $clinicaData[$key]['total_comision_asesor_usd'] += $montoPagado * 0.10;
            $clinicaData[$key]['total_comision_agencia_bs'] += $montoUsd * 0.04;
            $clinicaData[$key]['total_comision_agencia_usd'] += $montoPagado * 0.04;
            $clinicaData[$key]['total_pagos']++;

            if ($model->estatus === 'Conciliado') {
                $clinicaData[$key]['conciliados']++;
            } else {
                $clinicaData[$key]['pendientes']++;
            }
        }

        foreach ($clinicaData as $data) {
            $summary[] = $data;
        }

        return $summary;
    }

    /**
     * Calcula el resumen general de comisiones
     */
    private function calculateComisionesSummary($query)
    {
        $models = $query->all();

        $summary = [
            'total_comision_asesor_bs' => 0,
            'total_comision_asesor_usd' => 0,
            'total_comision_agencia_bs' => 0,
            'total_comision_agencia_usd' => 0,
            'total_count' => 0,
            'conciliados' => 0,
            'pendientes' => 0,
        ];

        foreach ($models as $model) {
            $montoUsd = $model->monto_usd;
            $montoPagado = $model->monto_pagado;

            $summary['total_comision_asesor_bs'] += $montoUsd * 0.10;
            $summary['total_comision_asesor_usd'] += $montoPagado * 0.10;
            $summary['total_comision_agencia_bs'] += $montoUsd * 0.04;
            $summary['total_comision_agencia_usd'] += $montoPagado * 0.04;
            $summary['total_count']++;

            if ($model->estatus === 'Conciliado') {
                $summary['conciliados']++;
            } else {
                $summary['pendientes']++;
            }
        }

        return $summary;
    }

    /**
     * Obtiene el rango de fechas para mostrar
     */
    private function getDateRangeForDisplay($range, $dateFrom, $dateTo)
    {
        $today = date('d/m/Y');

        switch ($range) {
            case 'day':
                return [$today, $today];
            case 'week':
                $weekAgo = date('d/m/Y', strtotime('-7 days'));
                return [$weekAgo, $today];
            case 'month':
                $monthStart = date('01/m/Y');
                return [$monthStart, $today];
            case 'last-month':
                $lastMonthStart = date('01/m/Y', strtotime('-1 month'));
                $lastMonthEnd = date('t/m/Y', strtotime('-1 month'));
                return [$lastMonthStart, $lastMonthEnd];
            case 'custom':
                return [
                    date('d/m/Y', strtotime($dateFrom)),
                    date('d/m/Y', strtotime($dateTo))
                ];
            default:
                return [$today, $today];
        }
    }

    /**
     * Genera el reporte de COMISIONES en PDF
     */
    public function actionGenerateComisionesPdf($range = 'day', $specific_date = null, $status = 'todos')
    {
        $request = Yii::$app->request;

        $status = $request->get('status', 'todos');
        $clinicasArray = $this->resolveClinicaFilter($request->get('clinicas', ''));
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $customRange = $request->get('custom_range', false);

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $subtitle = "";

        if ($customRange && $dateFrom && $dateTo) {
            $startDate = $dateFrom;
            $endDate = $dateTo;
            $subtitle = "Período Personalizado: " . Yii::$app->formatter->asDate($dateFrom, 'long') . " al " . Yii::$app->formatter->asDate($dateTo, 'long');
        } else if ($specific_date && $specific_date !== 'Invalid date') {
            $startDate = $specific_date;
            $endDate = $specific_date;
            $subtitle = "Fecha Específica: " . Yii::$app->formatter->asDate($specific_date, 'long');
        } else {
            $range = ($range === 'undefined' || empty($range)) ? 'day' : $range;

            switch ($range) {
                case 'week':
                    $startDate = date('Y-m-d', strtotime('last Monday'));
                    $endDate = date('Y-m-d');
                    $subtitle = "Reporte Semanal";
                    break;
                case 'month':
                    $startDate = date('Y-m-01');
                    $endDate = date('Y-m-d');
                    $subtitle = "Reporte Mensual";
                    break;
                case 'last-month':
                    $startDate = date('Y-m-01', strtotime('first day of last month'));
                    $endDate = date('Y-m-t', strtotime('last month'));
                    $subtitle = "Reporte del Mes Anterior";
                    break;
                case 'day':
                default:
                    $subtitle = "Reporte del Día";
                    break;
            }
        }

        $status = ($status === 'undefined' || empty($status)) ? 'todos' : $status;

        $title = "REPORTE DE COMISIONES - SISTEMA SISPSA";
        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $subtitle .= " - " . $statusLabel;

        $searchModel = new PagosReporteSearch();
        $params = $request->get();

        $params['range'] = $range;
        $params['status'] = $status;
        $params['clinicas'] = $clinicasArray;
        if ($customRange) {
            $params['custom_range'] = true;
            $params['date_from'] = $dateFrom;
            $params['date_to'] = $dateTo;
        }

        $dataProvider = $searchModel->searchComisiones($params);
        $dataProvider->pagination = false;
        $models = $dataProvider->getModels();

        $summaryPorClinica = $this->calculateComisionesSummaryByClinica($dataProvider->query);

        $totalMontoBs = 0;
        $totalMontoUsd = 0;
        $totalComisionAsesorBs = 0;
        $totalComisionAsesorUsd = 0;
        $totalComisionAgenciaBs = 0;
        $totalComisionAgenciaUsd = 0;
        $totalComisionClinicaBs = 0;
        $totalComisionClinicaUsd = 0;

        if (!empty($models)) {
            foreach ($models as $model) {
                $montoBs = $model->monto_usd;
                $montoUsd = $model->monto_pagado;

                $tasaDia = 0;
                if ($montoUsd > 0 && $montoBs > 0) {
                    $tasaDia = $montoBs / $montoUsd;
                }

                $totalMontoBs += $montoBs;
                $totalMontoUsd += $montoUsd;
                $totalComisionAsesorBs += $montoBs * 0.10;
                $totalComisionAsesorUsd += $tasaDia > 0 ? ($montoBs * 0.10) / $tasaDia : 0;
                $totalComisionAgenciaBs += $montoBs * 0.04;
                $totalComisionAgenciaUsd += $tasaDia > 0 ? ($montoBs * 0.04) / $tasaDia : 0;
                $totalComisionClinicaBs += $montoBs * 0.70;
                $totalComisionClinicaUsd += $montoUsd * 0.70;
            }
        }

        $content = $this->renderPartial('_comisiones-pdf', [
            'dataProvider' => $dataProvider,
            'title' => $title,
            'subtitle' => $subtitle,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summaryPorClinica' => $summaryPorClinica,
            'clinicasSeleccionadas' => $clinicasArray,
            'generatedAt' => date('d/m/Y H:i:s'),
            'totalMontoBs' => $totalMontoBs,
            'totalMontoUsd' => $totalMontoUsd,
            'totalComisionAsesorBs' => $totalComisionAsesorBs,
            'totalComisionAsesorUsd' => $totalComisionAsesorUsd,
            'totalComisionAgenciaBs' => $totalComisionAgenciaBs,
            'totalComisionAgenciaUsd' => $totalComisionAgenciaUsd,
            'totalComisionClinicaBs' => $totalComisionClinicaBs,
            'totalComisionClinicaUsd' => $totalComisionClinicaUsd,
            'models' => $models,
        ]);

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            'destination' => Pdf::DEST_DOWNLOAD,
            'content' => $content,
            'options' => ['title' => $title],
            'marginLeft' => 10,
            'marginRight' => 10,
            'marginTop' => 15,
            'marginBottom' => 15,
            'marginHeader' => 5,
            'marginFooter' => 8,
            'methods' => [
                'SetHeader' => ['Sistema SISPSA - Reporte de Comisiones||Página {PAGENO} de {nb}'],
                'SetFooter' => ['Generado el ' . date('d/m/Y H:i:s') . '||'],
            ],
            'defaultFont' => 'dejavusans',
        ]);

        $filename = 'Reporte_Comisiones_' . date('Ymd_His') . '.pdf';
        $pdf->filename = $filename;

        try {
            return $pdf->render();
        } catch (\Exception $e) {
            Yii::error('Error generando PDF de comisiones: ' . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error al generar el PDF: ' . $e->getMessage());
            return $this->redirect(['comisiones']);
        }
    }

    /**
     * Exporta el reporte de COMISIONES a Excel
     */
    public function actionExportComisionesExcel($range = 'day', $specific_date = null, $status = 'todos')
    {
        $request = Yii::$app->request;

        $status = $request->get('status', 'todos');
        $clinicasArray = $this->resolveClinicaFilter($request->get('clinicas', ''));
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $customRange = $request->get('custom_range', false);

        $range = ($range === 'undefined' || empty($range)) ? 'day' : $range;
        $status = ($status === 'undefined' || empty($status)) ? 'todos' : $status;

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');

        if ($customRange && $dateFrom && $dateTo) {
            $startDate = date('Y-m-d', strtotime($dateFrom));
            $endDate = date('Y-m-d', strtotime($dateTo));
        } else if ($specific_date && $specific_date !== 'Invalid date' && $specific_date !== 'undefined') {
            $startDate = date('Y-m-d', strtotime($specific_date));
            $endDate = $startDate;
        } else {
            switch ($range) {
                case 'week':
                    $startDate = date('Y-m-d', strtotime('last Monday'));
                    $endDate = date('Y-m-d');
                    break;
                case 'month':
                    $startDate = date('Y-m-01');
                    $endDate = date('Y-m-d');
                    break;
                case 'last-month':
                    $startDate = date('Y-m-01', strtotime('first day of last month'));
                    $endDate = date('Y-m-t', strtotime('last month'));
                    break;
                case 'day':
                default:
                    $startDate = date('Y-m-d');
                    $endDate = date('Y-m-d');
                    break;
            }
        }

        $searchModel = new PagosReporteSearch();
        $params = $request->get();

        $params['range'] = $range;
        $params['status'] = $status;
        $params['clinicas'] = $clinicasArray;
        if ($customRange) {
            $params['custom_range'] = true;
            $params['date_from'] = $startDate;
            $params['date_to'] = $endDate;
        }

        $dataProvider = $searchModel->searchComisiones($params);
        $dataProvider->pagination = false;
        $models = $dataProvider->getModels();

        $summaryPorClinica = $this->calculateComisionesSummaryByClinica($dataProvider->query);

        $totalMontoBs = 0;
        $totalMontoUsd = 0;
        $totalComisionAsesorBs = 0;
        $totalComisionAsesorUsd = 0;
        $totalComisionAgenciaBs = 0;
        $totalComisionAgenciaUsd = 0;
        $totalComisionClinicaBs = 0;
        $totalComisionClinicaUsd = 0;

        foreach ($models as $model) {
            $montoBs = $model->monto_usd;
            $montoUsd = $model->monto_pagado;

            $tasaDia = 0;
            if ($montoUsd > 0 && $montoBs > 0) {
                $tasaDia = $montoBs / $montoUsd;
            }

            $totalMontoBs += $montoBs;
            $totalMontoUsd += $montoUsd;
            $totalComisionAsesorBs += $montoBs * 0.10;
            $totalComisionAsesorUsd += $tasaDia > 0 ? ($montoBs * 0.10) / $tasaDia : 0;
            $totalComisionAgenciaBs += $montoBs * 0.04;
            $totalComisionAgenciaUsd += $tasaDia > 0 ? ($montoBs * 0.04) / $tasaDia : 0;
            $totalComisionClinicaBs += $montoBs * 0.70;
            $totalComisionClinicaUsd += $montoUsd * 0.70;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Hoja 1: Detalle de Comisiones
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detalle Comisiones');

        $sheet->setCellValue('A1', 'REPORTE DE COMISIONES - SISTEMA SISPSA');
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $periodoText = "Período: " . date('d/m/Y', strtotime($startDate)) . " al " . date('d/m/Y', strtotime($endDate)) . " - " . $statusLabel;
        $sheet->setCellValue('A2', $periodoText);
        $sheet->mergeCells('A2:O2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getFont()->getColor()->setARGB('FF0078d4');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A4', 'Fecha de Generación:');
        $sheet->setCellValue('B4', date('d/m/Y H:i:s'));
        $sheet->getStyle('A4')->getFont()->setBold(true);

        if (!empty($clinicasArray) && !in_array('todas', $clinicasArray)) {
            $clinicasNombres = [];
            foreach ($clinicasArray as $clinicaId) {
                $clinica = \app\models\RmClinica::findOne($clinicaId);
                if ($clinica) {
                    $clinicasNombres[] = $clinica->nombre;
                }
            }
            if (!empty($clinicasNombres)) {
                $sheet->setCellValue('A5', 'Clínicas:');
                $sheet->setCellValue('B5', implode(', ', $clinicasNombres));
                $sheet->getStyle('A5')->getFont()->setBold(true);
            }
        }

        $headerRow = 7;
        $sheet->mergeCells('A' . $headerRow . ':A' . ($headerRow + 1));
        $sheet->setCellValue('A' . $headerRow, '#');

        $sheet->mergeCells('B' . $headerRow . ':B' . ($headerRow + 1));
        $sheet->setCellValue('B' . $headerRow, 'Afiliado');

        $sheet->mergeCells('C' . $headerRow . ':C' . ($headerRow + 1));
        $sheet->setCellValue('C' . $headerRow, 'Cédula');

        $sheet->mergeCells('D' . $headerRow . ':F' . $headerRow);
        $sheet->setCellValue('D' . $headerRow, 'MONTOS');
        $sheet->getStyle('D' . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('D' . ($headerRow + 1), 'USD');
        $sheet->setCellValue('E' . ($headerRow + 1), 'TASA');
        $sheet->setCellValue('F' . ($headerRow + 1), 'Bs.');

        $sheet->mergeCells('G' . $headerRow . ':J' . $headerRow);
        $sheet->setCellValue('G' . $headerRow, 'COMISIONES');
        $sheet->getStyle('G' . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('G' . ($headerRow + 1), 'ASESOR (10%) Bs.');
        $sheet->setCellValue('H' . ($headerRow + 1), 'ASESOR (10%) USD');
        $sheet->setCellValue('I' . ($headerRow + 1), 'AGENCIA (4%) Bs.');
        $sheet->setCellValue('J' . ($headerRow + 1), 'AGENCIA (4%) USD');

        $sheet->mergeCells('K' . $headerRow . ':L' . $headerRow);
        $sheet->setCellValue('K' . $headerRow, 'PAGOS CLÍNICA (70%)');
        $sheet->getStyle('K' . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('K' . ($headerRow + 1), 'Bs.');
        $sheet->setCellValue('L' . ($headerRow + 1), 'USD');

        $sheet->mergeCells('M' . $headerRow . ':M' . ($headerRow + 1));
        $sheet->setCellValue('M' . $headerRow, 'Fecha');

        $sheet->mergeCells('N' . $headerRow . ':N' . ($headerRow + 1));
        $sheet->setCellValue('N' . $headerRow, 'Método');

        $sheet->mergeCells('O' . $headerRow . ':O' . ($headerRow + 1));
        $sheet->setCellValue('O' . $headerRow, 'Clínica');

        $headerStyle = $sheet->getStyle('A' . $headerRow . ':O' . ($headerRow + 1));
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $headerStyle->getFill()->getStartColor()->setARGB('FF2c3e50');
        $headerStyle->getFont()->getColor()->setARGB('FFFFFFFF');
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->getStyle('D' . $headerRow . ':F' . $headerRow)->getFill()->getStartColor()->setARGB('FF0078d4');
        $sheet->getStyle('G' . $headerRow . ':J' . $headerRow)->getFill()->getStartColor()->setARGB('FF8b0000');
        $sheet->getStyle('K' . $headerRow . ':L' . $headerRow)->getFill()->getStartColor()->setARGB('FF006400');

        $dataRow = $headerRow + 2;
        $consecutivo = 1;
        $totalRow = null;

        if (empty($models)) {
            $sheet->setCellValue('A' . $dataRow, 'No hay datos para el período seleccionado');
            $sheet->mergeCells('A' . $dataRow . ':O' . $dataRow);
            $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        } else {
            foreach ($models as $model) {
                $montoBs = $model->monto_usd;
                $montoUsd = $model->monto_pagado;

                $tasaDia = 0;
                if ($montoUsd > 0 && $montoBs > 0) {
                    $tasaDia = $montoBs / $montoUsd;
                }

                $comisionAsesorBs = $montoBs * 0.10;
                $comisionAsesorUsd = $tasaDia > 0 ? $comisionAsesorBs / $tasaDia : 0;
                $comisionAgenciaBs = $montoBs * 0.04;
                $comisionAgenciaUsd = $tasaDia > 0 ? $comisionAgenciaBs / $tasaDia : 0;
                $pagoClinicaBs = $montoBs * 0.70;
                $pagoClinicaUsd = $montoUsd * 0.70;

                $afiliado = $model->userDatos ?
                    trim($model->userDatos->nombres . ' ' . $model->userDatos->apellidos) : 'N/A';
                $cedula = $model->userDatos ? $model->userDatos->cedula : 'N/A';

                $clinicaNombre = 'Sin Clínica';
                if ($model->contratos && count($model->contratos) > 0) {
                    foreach ($model->contratos as $contrato) {
                        if ($contrato->clinica) {
                            $clinicaNombre = $contrato->clinica->nombre;
                            break;
                        }
                    }
                }

                $fecha = $model->fecha_pago ? date('d/m/Y', strtotime($model->fecha_pago)) : 'N/A';

                $sheet->setCellValue('A' . $dataRow, $consecutivo++);
                $sheet->setCellValue('B' . $dataRow, $afiliado);
                $sheet->setCellValue('C' . $dataRow, $cedula);
                $sheet->setCellValue('D' . $dataRow, $montoUsd);
                $sheet->setCellValue('E' . $dataRow, $tasaDia > 0 ? $tasaDia : 'N/A');
                $sheet->setCellValue('F' . $dataRow, $montoBs);
                $sheet->setCellValue('G' . $dataRow, $comisionAsesorBs);
                $sheet->setCellValue('H' . $dataRow, $comisionAsesorUsd);
                $sheet->setCellValue('I' . $dataRow, $comisionAgenciaBs);
                $sheet->setCellValue('J' . $dataRow, $comisionAgenciaUsd);
                $sheet->setCellValue('K' . $dataRow, $pagoClinicaBs);
                $sheet->setCellValue('L' . $dataRow, $pagoClinicaUsd);
                $sheet->setCellValue('M' . $dataRow, $fecha);
                $sheet->setCellValue('N' . $dataRow, $model->metodo_pago ?: 'N/A');
                $sheet->setCellValue('O' . $dataRow, $clinicaNombre);

                $dataRow++;
            }

            $totalRow = $dataRow;

            $sheet->mergeCells('A' . $totalRow . ':C' . $totalRow);
            $sheet->setCellValue('A' . $totalRow, 'TOTAL DETALLE');
            $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);

            $sheet->setCellValue('D' . $totalRow, $totalMontoUsd);
            $sheet->setCellValue('E' . $totalRow, '');
            $sheet->setCellValue('F' . $totalRow, $totalMontoBs);
            $sheet->setCellValue('G' . $totalRow, $totalComisionAsesorBs);
            $sheet->setCellValue('H' . $totalRow, $totalComisionAsesorUsd);
            $sheet->setCellValue('I' . $totalRow, $totalComisionAgenciaBs);
            $sheet->setCellValue('J' . $totalRow, $totalComisionAgenciaUsd);
            $sheet->setCellValue('K' . $totalRow, $totalComisionClinicaBs);
            $sheet->setCellValue('L' . $totalRow, $totalComisionClinicaUsd);
            $sheet->setCellValue('M' . $totalRow, '');
            $sheet->setCellValue('N' . $totalRow, '');
            $sheet->setCellValue('O' . $totalRow, '');

            $sheet->getStyle('A' . $totalRow . ':O' . $totalRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2c3e50');
            $sheet->getStyle('A' . $totalRow . ':O' . $totalRow)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A' . $totalRow . ':O' . $totalRow)->getFont()->setBold(true);
        }

        foreach (range('A', 'O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $currencyColumns = ['D', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        $lastDataRow = $dataRow - 1;

        foreach ($currencyColumns as $col) {
            $sheet->getStyle($col . ($headerRow + 2) . ':' . $col . $lastDataRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');

            if ($totalRow !== null) {
                $sheet->getStyle($col . $totalRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }
        }

        if ($totalRow !== null && $lastDataRow >= ($headerRow + 2)) {
            $sheet->getStyle('E' . ($headerRow + 2) . ':E' . $lastDataRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Hoja 2: Resumen por Clínica
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Resumen por Clínica');

        $sheet2->setCellValue('A1', 'RESUMEN DE COMISIONES POR CLÍNICA');
        $sheet2->mergeCells('A1:H1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $resumenHeaders = [
            'Clínica',
            'RIF',
            'Total Pagos',
            'Conciliados',
            'Pendientes',
            'Comisión Asesor Bs.',
            'Comisión Agencia Bs.',
            'Total Comisiones Bs.'
        ];

        $headerRowResumen = 3;
        $col = 1;
        foreach ($resumenHeaders as $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet2->setCellValue($colLetter . $headerRowResumen, $header);
            $col++;
        }

        $headerRangeResumen = 'A3:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($resumenHeaders)) . '3';
        $sheet2->getStyle($headerRangeResumen)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2c3e50']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        $resumenRow = 4;
        $totalGeneralComisiones = 0;

        if (!empty($summaryPorClinica)) {
            foreach ($summaryPorClinica as $resumen) {
                $col = 1;

                $totalComisiones = ($resumen['total_comision_asesor_bs'] ?? 0) +
                    ($resumen['total_comision_agencia_bs'] ?? 0);
                $totalGeneralComisiones += $totalComisiones;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['clinica_nombre'] ?? 'N/A');
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['clinica_rif'] ?? 'N/A');
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['total_pagos'] ?? 0);
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['conciliados'] ?? 0);
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['pendientes'] ?? 0);
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['total_comision_asesor_bs'] ?? 0);
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['total_comision_agencia_bs'] ?? 0);
                $col++;

                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $totalComisiones);

                $resumenRow++;
            }

            $totalRowResumen = $resumenRow;
            $sheet2->mergeCells('A' . $totalRowResumen . ':B' . $totalRowResumen);
            $sheet2->setCellValue('A' . $totalRowResumen, 'TOTALES GENERALES');
            $sheet2->getStyle('A' . $totalRowResumen)->getFont()->setBold(true);

            $sheet2->setCellValue('C' . $totalRowResumen, array_sum(array_column($summaryPorClinica, 'total_pagos')));
            $sheet2->setCellValue('D' . $totalRowResumen, array_sum(array_column($summaryPorClinica, 'conciliados')));
            $sheet2->setCellValue('E' . $totalRowResumen, array_sum(array_column($summaryPorClinica, 'pendientes')));
            $sheet2->setCellValue('F' . $totalRowResumen, array_sum(array_column($summaryPorClinica, 'total_comision_asesor_bs')));
            $sheet2->setCellValue('G' . $totalRowResumen, array_sum(array_column($summaryPorClinica, 'total_comision_agencia_bs')));
            $sheet2->setCellValue('H' . $totalRowResumen, $totalGeneralComisiones);

            $sheet2->getStyle('A' . $totalRowResumen . ':H' . $totalRowResumen)->getFont()->setBold(true);
            $sheet2->getStyle('A' . $totalRowResumen . ':H' . $totalRowResumen)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2c3e50');
            $sheet2->getStyle('A' . $totalRowResumen . ':H' . $totalRowResumen)->getFont()->getColor()->setARGB('FFFFFFFF');

            foreach (range('A', 'H') as $column) {
                $sheet2->getColumnDimension($column)->setAutoSize(true);
            }

            $currencyCols = ['F', 'G', 'H'];
            foreach ($currencyCols as $col) {
                $sheet2->getStyle($col . '4:' . $col . ($resumenRow - 1))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
                $sheet2->getStyle($col . $totalRowResumen)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }
        } else {
            $sheet2->setCellValue('A4', 'No hay datos de resumen disponibles');
            $sheet2->mergeCells('A4:H4');
            $sheet2->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('A4')->getFont()->setItalic(true);
        }

        // Hoja 3: Tarjetas de Resumen
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Resumen General');

        $sheet3->setCellValue('A1', 'RESUMEN GENERAL DE COMISIONES');
        $sheet3->mergeCells('A1:C1');
        $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet3->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet3->setCellValue('A3', 'COMISIÓN ASESOR');
        $sheet3->getStyle('A3')->getFont()->setBold(true)->setSize(12);
        $sheet3->getStyle('A3')->getFont()->getColor()->setARGB('FFFF8C00');

        $sheet3->setCellValue('A4', 'Bs. ' . number_format($totalComisionAsesorBs, 2, ',', '.'));
        $sheet3->getStyle('A4')->getFont()->setBold(true)->setSize(16);

        $sheet3->setCellValue('A5', 'USD ' . number_format($totalComisionAsesorUsd, 2, ',', '.'));
        $sheet3->getStyle('A5')->getFont()->setSize(12);

        $sheet3->setCellValue('A6', '10%');
        $sheet3->getStyle('A6')->getFont()->setBold(true);

        $sheet3->setCellValue('C3', 'COMISIÓN AGENCIA');
        $sheet3->getStyle('C3')->getFont()->setBold(true)->setSize(12);
        $sheet3->getStyle('C3')->getFont()->getColor()->setARGB('FFDC3545');

        $sheet3->setCellValue('C4', 'Bs. ' . number_format($totalComisionAgenciaBs, 2, ',', '.'));
        $sheet3->getStyle('C4')->getFont()->setBold(true)->setSize(16);

        $sheet3->setCellValue('C5', 'USD ' . number_format($totalComisionAgenciaUsd, 2, ',', '.'));
        $sheet3->getStyle('C5')->getFont()->setSize(12);

        $sheet3->setCellValue('C6', '4%');
        $sheet3->getStyle('C6')->getFont()->setBold(true);

        $sheet3->setCellValue('E3', 'PAGOS CLÍNICA');
        $sheet3->getStyle('E3')->getFont()->setBold(true)->setSize(12);
        $sheet3->getStyle('E3')->getFont()->getColor()->setARGB('FF107C10');

        $sheet3->setCellValue('E4', 'Bs. ' . number_format($totalComisionClinicaBs, 2, ',', '.'));
        $sheet3->getStyle('E4')->getFont()->setBold(true)->setSize(16);

        $sheet3->setCellValue('E5', 'USD ' . number_format($totalComisionClinicaUsd, 2, ',', '.'));
        $sheet3->getStyle('E5')->getFont()->setSize(12);

        $sheet3->setCellValue('E6', '70%');
        $sheet3->getStyle('E6')->getFont()->setBold(true);

        $sheet3->setCellValue('A8', 'TOTAL GENERAL DE COMISIONES');
        $sheet3->mergeCells('A8:C8');
        $sheet3->getStyle('A8')->getFont()->setBold(true)->setSize(14);

        $totalGeneralBs = $totalComisionAsesorBs + $totalComisionAgenciaBs;
        $totalGeneralUsd = $totalComisionAsesorUsd + $totalComisionAgenciaUsd;

        $sheet3->setCellValue('A9', 'Bs. ' . number_format($totalGeneralBs, 2, ',', '.'));
        $sheet3->mergeCells('A9:C9');
        $sheet3->getStyle('A9')->getFont()->setBold(true)->setSize(20);
        $sheet3->getStyle('A9')->getFont()->getColor()->setARGB('FF4CD964');

        $sheet3->setCellValue('A10', 'USD ' . number_format($totalGeneralUsd, 2, ',', '.'));
        $sheet3->mergeCells('A10:C10');
        $sheet3->getStyle('A10')->getFont()->setSize(12);

        $sheet3->setCellValue('A11', 'Total de registros: ' . count($models));
        $sheet3->mergeCells('A11:C11');
        $sheet3->getStyle('A11')->getFont()->setItalic(true);

        $sheet3->getColumnDimension('A')->setWidth(30);
        $sheet3->getColumnDimension('C')->setWidth(30);
        $sheet3->getColumnDimension('E')->setWidth(30);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Reporte_Comisiones_' . date('Ymd_His') . '.xlsx';

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
