<?php
// app/controllers/ReportesController.php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Pagos;
use app\models\PagosReporteSearch;
use kartik\mpdf\Pdf; // IMPORTANTE: Asegúrate de incluir esta dependencia

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
                        // Todas las acciones del controlador
                        'actions' => ['index', 'get-pagos-detail', 'generate-pdf', 'export-excel', 'comisiones', 'get-comisiones-detail', 'generate-comisiones-pdf-tcpdf', 'generate-comisiones-pdf', 'export-comisiones-excel', 'test-data', 'test-pdf'],
                        // Acceso para 'superadmin' y 'finanzas'
                        'roles' => ['superadmin', 'FINANZAS', 'COORDINADOR-CLINICA'],
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
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['get-pagos-detail'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function actionTestAccess()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'isGuest' => Yii::$app->user->isGuest,
            'userId' => Yii::$app->user->id,
            'identity' => Yii::$app->user->identity ? Yii::$app->user->identity->username : null,
            'roles' => array_keys(Yii::$app->authManager->getRolesByUser(Yii::$app->user->id)),
            'canAccess' => Yii::$app->user->can('superadmin') || Yii::$app->user->can('FINANZAS'),
            'sessionId' => session_id(),
            'csrfToken' => Yii::$app->request->csrfToken,
        ];
    }


    /**
     * Muestra la vista principal del reporte (Grid y filtros).
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

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
        $clinicas = $request->post('clinicas', []);

        // NUEVO: Parámetros para rango personalizado
        $customRange = $request->post('custom_range', false);
        $dateFrom = $request->post('date_from');
        $dateTo = $request->post('date_to');
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $title = "Detalle de Pagos de Hoy";

        // =============================================
        // LÓGICA ACTUALIZADA PARA RANGO DE FECHAS
        // =============================================

        if ($customRange && $dateFrom && $dateTo) {
            // 1. PRIMERO: Verificar si es rango personalizado
            $startDate = $dateFrom;
            $endDate = $dateTo;
            $title = "Detalle de Pagos para el período personalizado";
        } else if ($specificDate && $specificDate !== 'Invalid date') {
            // 2. Fecha específica (existente)
            $startDate = $specificDate;
            $endDate = $specificDate;
            $title = "Detalle de Pagos para el día: " . Yii::$app->formatter->asDate($specificDate, 'long');
        } else {
            // 3. Rangos predefinidos
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
                    // Ya tiene valores por defecto
                    break;
            }
        }

        // Modificar título para reflejar el estado seleccionado
        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $title .= " ({$statusLabel})";

        // =============================================
        // LOGGING PARA DEBUG (OPCIONAL)
        // =============================================
        Yii::debug("Report parameters:", 'application');
        Yii::debug("Range: {$range}", 'application');
        Yii::debug("Custom Range: " . ($customRange ? 'true' : 'false'), 'application');
        Yii::debug("Date From: {$dateFrom}", 'application');
        Yii::debug("Date To: {$dateTo}", 'application');
        Yii::debug("Start Date: {$startDate}", 'application');
        Yii::debug("End Date: {$endDate}", 'application');

        // 2. Crear y configurar el modelo de búsqueda
        $searchModel = new PagosReporteSearch();

        // 3. Obtener el resumen general
        $summary = $searchModel->obtenerResumenGeneral($startDate, $endDate, $status, $clinicas);

        // 4. Obtener el resumen por clínica
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

        // 5. Obtener el dataProvider
        $params = $request->post();

        // Usar searchConClinicas si hay filtro de clínicas, de lo contrario usar search normal
        if (!empty($clinicas) && !in_array('todas', $clinicas)) {
            $dataProvider = $searchModel->searchConClinicas($params, $startDate, $endDate, $status, $clinicas);
        } else {
            $dataProvider = $searchModel->search($params, $startDate, $endDate, $status, $clinicas);
        }

        // Devolver el HTML de la vista parcial
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
            // Agregar debug info si es necesario
            'debug' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'range' => $range,
                'customRange' => $customRange,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ]
        ];
    }


    /**
     * Genera el reporte en PDF (basado en los mismos parámetros GET que la AJAX call).
     * @param string $range
     * @param string|null $specific_date
     * @param string $status NUEVO: Estado del pago a filtrar.
     * @return Response
     */
    public function actionGeneratePdf($range = 'day', $specific_date = null, $status = 'Por Conciliar')
    {
        $request = Yii::$app->request;

        // Obtener todos los parámetros
        $status = $request->get('status', 'Por Conciliar');
        $customRange = $request->get('custom_range', false);
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $clinicasParam = $request->get('clinicas', '');

        // Procesar clínicas
        $clinicasArray = [];
        if (!empty($clinicasParam)) {
            if (is_array($clinicasParam)) {
                $clinicasArray = $clinicasParam;
            } else if (strpos($clinicasParam, ',') !== false) {
                $clinicasArray = explode(',', $clinicasParam);
            } else {
                $clinicasArray = [$clinicasParam];
            }
        }

        // Inicializar fechas
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');

        // Crear título profesional
        $title = "REPORTE DE PAGOS - SISTEMA SISPSA";
        $subtitle = "";

        // Lógica de rango de fechas
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

        // Agregar estado
        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $subtitle .= " - " . $statusLabel;

        // Agregar información de clínicas
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

        // Crear y configurar el modelo de búsqueda
        $searchModel = new PagosReporteSearch();

        // Obtener el resumen general
        $summary = $searchModel->obtenerResumenGeneral($startDate, $endDate, $status, $clinicasArray);

        // Obtener el resumen por clínica
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

        // Crear y configurar el dataProvider
        $params = $request->get();
        if (!empty($clinicasArray) && !in_array('todas', $clinicasArray)) {
            $dataProvider = $searchModel->searchConClinicas($params, $startDate, $endDate, $status, $clinicasArray);
        } else {
            $dataProvider = $searchModel->search($params, $startDate, $endDate, $status, $clinicasArray);
        }

        // Desactivar paginación para obtener todos los datos
        $dataProvider->pagination = false;

        // Generar contenido HTML para el PDF
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

        // CSS SIMPLIFICADO que SÍ funciona con MPDF
        $simpleCss = '
        /* Estilos básicos que funcionan con MPDF */
        body { 
            font-family: DejaVu Sans, Arial, sans-serif; 
            font-size: 10pt; 
            line-height: 1.3; 
            color: #000000; 
            margin: 0; 
            padding: 0; 
        }
        
        /* Título principal */
        .main-title { 
            font-size: 20pt; 
            font-weight: bold; 
            color: #2c3e50; 
            text-align: center; 
            margin: 0 0 5px 0; 
            padding: 0; 
        }
        
        /* Subtítulo */
        .subtitle { 
            font-size: 12pt; 
            color: #0078d4; 
            text-align: center; 
            margin: 0 0 20px 0; 
            padding: 0; 
            font-weight: bold; 
        }
        
        /* Información del reporte */
        .report-info { 
            background-color: #f8f9fa; 
            border: 1px solid #dee2e6; 
            padding: 10px; 
            margin: 0 0 20px 0; 
            border-radius: 5px; 
        }
        
        .info-grid { 
            display: table; 
            width: 100%; 
        }
        
        .info-row { 
            display: table-row; 
        }
        
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
        
        /* Tablas */
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
        
        .pdf-table td.text-left { 
            text-align: left; 
        }
        
        .pdf-table td.text-right { 
            text-align: right; 
        }
        
        .pdf-table tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        
        /* Badges */
        .badge { 
            display: inline-block; 
            padding: 3px 8px; 
            border-radius: 3px; 
            font-size: 8pt; 
            font-weight: bold; 
        }
        
        .badge-success { 
            background-color: #dff6dd; 
            color: #107c10; 
        }
        
        .badge-warning { 
            background-color: #fff4ce; 
            color: #7a5c00; 
        }
        
        /* Total General DESTACADO */
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
        
        /* Footer */
        .report-footer { 
            margin-top: 30px; 
            padding-top: 15px; 
            border-top: 1px solid #dddddd; 
            text-align: center; 
            font-size: 8pt; 
            color: #666666; 
        }
        
        /* Clases de utilidad */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-20 { margin-bottom: 20px; }
        .mt-20 { margin-top: 20px; }
        .mt-30 { margin-top: 30px; }
    ';

        // Configuración del PDF
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
            // REMOVE THIS LINE: 'cssInline' => $comisionesCss,
            'methods' => [
                'SetHeader' => ['Sistema SISPSA - Reporte de Comisiones||Página {PAGENO} de {nb}'],
                'SetFooter' => ['Generado el ' . date('d/m/Y H:i:s') . '||'],
            ],
            'defaultFont' => 'dejavusans',
        ]);

        // Nombre del archivo
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
    public function actionExportExcel($range = 'day', $specific_date = null, $status = 'Por Conciliar')
    {
        $request = Yii::$app->request;

        // Obtener parámetros
        $status = $request->get('status', 'Por Conciliar');
        $clinicasParam = $request->get('clinicas', '');
        $clinicasArray = [];

        // Procesar clínicas
        if (!empty($clinicasParam)) {
            if (is_array($clinicasParam)) {
                $clinicasArray = $clinicasParam;
            } else if (strpos($clinicasParam, ',') !== false) {
                $clinicasArray = explode(',', $clinicasParam);
            } else {
                $clinicasArray = [$clinicasParam];
            }
        }

        // Determinar fechas
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');

        switch ($range) {
            case 'week':
                $startDate = date('Y-m-d', strtotime('last Monday'));
                break;
            case 'month':
                $startDate = date('Y-m-01');
                break;
            case 'last-month':
                $startDate = date('Y-m-01', strtotime('first day of last month'));
                $endDate = date('Y-m-t', strtotime('last month'));
                break;
        }

        if ($specific_date && $specific_date !== 'Invalid date') {
            $startDate = $specific_date;
            $endDate = $specific_date;
        }

        // Obtener datos
        $searchModel = new PagosReporteSearch();
        $params = $request->get();

        // =============================================
        // NEW: Get summary data (MISSING PART)
        // =============================================

        // 1. Get general summary
        $summary = $searchModel->obtenerResumenGeneral($startDate, $endDate, $status, $clinicasArray);

        // 2. Get summary by clinic
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

        // 3. Get detailed data for the main sheet
        if (!empty($clinicasArray) && !in_array('todas', $clinicasArray)) {
            $dataProvider = $searchModel->searchConClinicas($params, $startDate, $endDate, $status, $clinicasArray);
        } else {
            $dataProvider = $searchModel->search($params, $startDate, $endDate, $status, $clinicasArray);
        }

        // Desactivar paginación para obtener todos los datos
        $dataProvider->pagination = false;
        $models = $dataProvider->getModels();

        // Create filename
        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');

        $fileName = 'Reporte_Pagos_' . $startDate . '_al_' . $endDate . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $statusLabel) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Clean output buffer
        ob_clean();
        ob_start();

        // Create new Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Propiedades del documento
        $spreadsheet->getProperties()
            ->setCreator("Sistema Sipsa")
            ->setLastModifiedBy("Sistema Sipsa")
            ->setTitle("Reporte de Pagos")
            ->setSubject("Reporte de Pagos de Afiliados")
            ->setDescription("Reporte generado automáticamente por el sistema Sipsa")
            ->setKeywords("pagos afiliados reporte excel")
            ->setCategory("Reporte");

        // =============================================
        // HOJA 1: DETALLE DE PAGOS - UPDATED WITH NEW COLUMN ORDER AND CENTERED VALUES
        // =============================================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detalle de Pagos');

        // Encabezado principal
        $sheet->setCellValue('A1', 'Reporte de Operaciones en Efectivo y Transacciones con Activos Virtuales (SUDEASEG-002)');
        $sheet->mergeCells('A1:H1'); // 8 columns total
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Información del reporte
        $sheet->setCellValue('A2', 'Periodo:');
        $sheet->setCellValue('B2', $startDate . ' al ' . $endDate);

        $sheet->setCellValue('A3', 'Estado:');
        $sheet->setCellValue('B3', $statusLabel);

        // Información de clínicas si aplica
        if (!empty($clinicasArray) && !in_array('todas', $clinicasArray)) {
            $clinicasNombres = [];
            foreach ($clinicasArray as $clinicaId) {
                $clinica = \app\models\RmClinica::findOne($clinicaId);
                if ($clinica) {
                    $clinicasNombres[] = $clinica->nombre;
                }
            }

            if (!empty($clinicasNombres)) {
                $clinicasStr = count($clinicasNombres) > 3 ?
                    count($clinicasNombres) . ' clínicas seleccionadas' :
                    implode(', ', $clinicasNombres);

                $sheet->setCellValue('A4', 'Clínicas:');
                $sheet->setCellValue('B4', $clinicasStr);
            }
        }

        $sheet->setCellValue('A5', 'Generado:');
        $sheet->setCellValue('B5', date('d/m/Y H:i:s'));

        // Encabezados de columnas - NEW ORDER AS SPECIFIED
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

        $col = 1; // Columna A = 1
        foreach ($headers as $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $headerRow;
            $sheet->setCellValue($cell, $header);
            $col++;
        }

        // Estilo para encabezados
        $headerStyle = $sheet->getStyle('A' . $headerRow . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . $headerRow);
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $headerStyle->getFill()->getStartColor()->setARGB('FFCCCCCC');
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Datos de pagos - CORRECTED VERSION WITH ALL ADJUSTMENTS
        $dataRow = $headerRow + 1;

        if (empty($models)) {
            $sheet->setCellValue('A' . $dataRow, 'No hay datos para el período seleccionado');
            $sheet->mergeCells('A' . $dataRow . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . $dataRow);
            $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        } else {
            // Pre-load all state data for better performance
            $states = \app\models\RmEstado::find()->indexBy('id')->all();

            foreach ($models as $model) {
                // 1. Fecha de Operación (Column A) - DD-MM-YYYY format
                $fechaPago = 'N/A';
                if ($model->fecha_pago) {
                    try {
                        $date = new \DateTime($model->fecha_pago);
                        $fechaPago = $date->format('d-m-Y'); // DD-MM-YYYY format
                    } catch (\Exception $e) {
                        $fechaPago = 'N/A';
                    }
                }
                $sheet->setCellValue('A' . $dataRow, $fechaPago);

                // 2. Nombre o Razón Social (Column B) - Nombres + Apellidos in UPPERCASE
                $nombreCompleto = 'N/A';
                if ($model->userDatos) {
                    $nombres = $model->userDatos->nombres ?: '';
                    $apellidos = $model->userDatos->apellidos ?: '';
                    $nombreCompleto = trim($nombres . ' ' . $apellidos);
                    if (!empty($nombreCompleto)) {
                        $nombreCompleto = strtoupper($nombreCompleto);
                    }
                }
                $sheet->setCellValue('B' . $dataRow, $nombreCompleto);

                // 3. Nº de Identificación (Column C) - cedula
                $cedula = $model->userDatos ? $model->userDatos->cedula : 'N/A';
                $sheet->setCellValue('C' . $dataRow, $cedula);

                // 4. Tipo de Cliente (Column D) - tipo_cedula
                $tipoCedula = $model->userDatos ? $model->userDatos->tipo_cedula : 'N/A';
                $sheet->setCellValue('D' . $dataRow, $tipoCedula);

                // 5. Ubicación Geográfica (Column E) - estado name in UPPERCASE
                $estadoNombre = 'N/A';
                if ($model->userDatos && $model->userDatos->estado) {
                    // Check if estado is numeric (ID) or already a string
                    if (is_numeric($model->userDatos->estado) && isset($states[$model->userDatos->estado])) {
                        $estadoNombre = strtoupper($states[$model->userDatos->estado]->nombre);
                    } else {
                        // If it's already a string, use it as is
                        $estadoNombre = strtoupper($model->userDatos->estado);
                    }
                }
                $sheet->setCellValue('E' . $dataRow, $estadoNombre);

                // 6. Método de Pago (Column F) - UPPERCASE
                $metodoPago = $model->metodo_pago ? strtoupper($model->metodo_pago) : 'N/A';
                $sheet->setCellValue('F' . $dataRow, $metodoPago);

                // 7. Tipo de Moneda (Column G)
                // Determine currency based on metodo_pago (use original for detection)
                $tipoMoneda = 'USD';
                if (strpos($model->metodo_pago, 'Bolívar') !== false || strpos($model->metodo_pago, 'Bs') !== false) {
                    $tipoMoneda = 'Bs.';
                }
                $sheet->setCellValue('G' . $dataRow, $tipoMoneda);

                // 8. Monto (Column H)
                // Display amount based on currency type
                $monto = $tipoMoneda === 'Bs.' ? $model->monto_usd : $model->monto_pagado;
                $sheet->setCellValue('H' . $dataRow, $monto);

                $dataRow++;
            }
        }

        // Autoajustar ancho de columnas
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Formato de moneda para columna de Monto (Column H) y centrado
        $lastDataRow = empty($models) ? $dataRow : $dataRow - 1;

        // Apply centered alignment to ALL data columns
        $dataRange = 'A' . ($headerRow + 1) . ':H' . $lastDataRow;
        $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Apply vertical centering for better appearance
        $sheet->getStyle($dataRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Apply number format to Monto column (H) after centering
        $sheet->getStyle('H' . ($headerRow + 1) . ':H' . $lastDataRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // Optional: Wrap text for columns that might have longer content (like Nombre o Razón Social)
        $sheet->getStyle('B' . ($headerRow + 1) . ':B' . $lastDataRow)
            ->getAlignment()->setWrapText(true);

        // Also debug the data count
        Yii::debug("Total models found: " . count($models), 'application');

        // Bordes para todos los datos
        if (!empty($models)) {
            $sample = $models[0];
            Yii::debug("Sample model - monto_usd: {$sample->monto_usd}, monto_pagado: {$sample->monto_pagado}", 'application');
            $dataStyle = $sheet->getStyle('A' . $headerRow . ':H' . $lastDataRow);
            $dataStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        // =============================================
        // HOJA 2: RESUMEN POR CLÍNICA
        // =============================================
        // Crear segunda hoja para resumen
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Resumen por Clínica');

        // Título del resumen
        $sheet2->setCellValue('A1', 'RESUMEN DE PAGOS POR CLÍNICA');
        $sheet2->mergeCells('A1:F1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Encabezados de resumen
        $resumenHeaders = ['Clínica', 'RIF', 'Total Pagos', 'Conciliados', 'Pendientes', 'Total (Bs.)'];
        $col = 1;
        foreach ($resumenHeaders as $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet2->setCellValue($colLetter . '3', $header);
            $col++;
        }

        // Estilo para encabezados del resumen
        $headerRangeResumen = 'A3:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($resumenHeaders)) . '3';
        $sheet2->getStyle($headerRangeResumen)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '107C10']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Escribir datos del resumen
        $resumenRow = 4;
        if (!empty($summaryPorClinica)) {
            foreach ($summaryPorClinica as $resumen) {
                $col = 1;

                // Clínica
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['clinica_nombre'] ?? 'N/A');
                $col++;

                // RIF
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['clinica_rif'] ?? 'N/A');
                $col++;

                // Total Pagos
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['total_pagos'] ?? 0);
                $col++;

                // Conciliados
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['conciliados'] ?? 0);
                $col++;

                // Pendientes
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['pendientes'] ?? 0);
                $col++;

                // Total (Bs.)
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['total_monto'] ?? 0);

                $resumenRow++;
            }

            // Autoajustar columnas
            foreach (range('A', \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($resumenHeaders))) as $column) {
                $sheet2->getColumnDimension($column)->setAutoSize(true);
            }

            // Agregar total general
            $totalRow = $resumenRow + 1;
            $sheet2->setCellValue('A' . $totalRow, 'TOTAL GENERAL');
            $sheet2->getStyle('A' . $totalRow)->getFont()->setBold(true);

            // Calcular totales
            $granTotalPagos = array_sum(array_column($summaryPorClinica, 'total_pagos'));
            $granTotalConciliados = array_sum(array_column($summaryPorClinica, 'conciliados'));
            $granTotalPendientes = array_sum(array_column($summaryPorClinica, 'pendientes'));
            $granTotalMonto = array_sum(array_column($summaryPorClinica, 'total_monto'));

            $sheet2->setCellValue('C' . $totalRow, $granTotalPagos);
            $sheet2->setCellValue('D' . $totalRow, $granTotalConciliados);
            $sheet2->setCellValue('E' . $totalRow, $granTotalPendientes);
            $sheet2->setCellValue('F' . $totalRow, $granTotalMonto);

            // Formato de moneda para columna de total
            $sheet2->getStyle('F4:F' . $totalRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        } else {
            $sheet2->setCellValue('A4', 'No hay datos de resumen disponibles');
            $sheet2->mergeCells('A4:F4');
            $sheet2->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('A4')->getFont()->setItalic(true);
        }

        // Regresar a la primera hoja
        $spreadsheet->setActiveSheetIndex(0);

        // =============================================
        // GENERAR Y DESCARGAR ARCHIVO
        // =============================================

        // Configurar headers para descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        // Crear writer y enviar al navegador
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

        exit;
    }
    // app/controllers/ReportesController.php - Add this action
    public function actionComisiones()
    {
        $model = new PagosReporteSearch();
        $title = 'Reporte de Comisiones';

        return $this->render('comisiones', [
            'model' => $model,
            'title' => $title,
        ]);
    }

    public function actionGetComisionesDetail()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        Yii::debug('Starting get-comisiones-detail action', 'application');

        try {
            // Log incoming parameters
            $request = Yii::$app->request;
            Yii::debug('POST data: ' . print_r($request->post(), true), 'application');

            $searchModel = new PagosReporteSearch();

            // Get filter parameters
            $range = $request->post('range', 'day');
            $status = $request->post('status', 'todos');
            $clinicas = $request->post('clinicas', []);
            $dateFrom = $request->post('date_from');
            $dateTo = $request->post('date_to');
            $customRange = $request->post('custom_range', false);

            Yii::debug("Params - range: $range, status: $status", 'application');

            // Apply filters to the search model
            $dataProvider = $searchModel->searchComisiones(Yii::$app->request->post());

            Yii::debug('DataProvider total count: ' . $dataProvider->getTotalCount(), 'application');

            // Calculate summary by clinic
            $summaryPorClinica = $this->calculateComisionesSummaryByClinica($dataProvider->query);
            $summary = $this->calculateComisionesSummary($dataProvider->query);

            // Get actual models for calculations
            $models = $dataProvider->getModels();

            // Calculate date range for display
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
            ]);

            return [
                'success' => true,
                'html' => $html,
                'debug' => [
                    'totalCount' => $dataProvider->getTotalCount(),
                    'modelCount' => count($models),
                    'summaryCount' => count($summaryPorClinica),
                ]
            ];
        } catch (\Exception $e) {
            Yii::error('Error generating commission report: ' . $e->getMessage(), __METHOD__);
            Yii::error($e->getTraceAsString(), __METHOD__);

            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
                'trace' => YII_DEBUG ? $e->getTraceAsString() : null,
            ];
        }
    }

    // In ReportesController.php - Simplified version
    private function calculateComisionesSummaryByClinica($query)
    {
        $summary = [];

        // Get all models first
        $models = $query->all();

        // Group by clinic manually
        $clinicaData = [];

        foreach ($models as $model) {
            $clinicaId = null;
            $clinicaNombre = 'Sin Clínica';
            $clinicaRif = 'N/A';

            // Get clinic from model contracts (same as in the grid view)
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

            // Calculate commissions for this payment
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

        // Convert to array
        foreach ($clinicaData as $data) {
            $summary[] = $data;
        }

        return $summary;
    }

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
    // Add this helper method if it doesn't exist
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
    public function actionTestAjax()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'success' => true,
            'message' => 'AJAX test successful',
            'time' => date('Y-m-d H:i:s'),
            'postData' => Yii::$app->request->post(),
        ];
    }
    /**
     * Genera el reporte de COMISIONES en PDF
     */
    public function actionGenerateComisionesPdf($range = 'day', $specific_date = null, $status = 'todos')
    {
        Yii::debug("=== PDF GENERATION DEBUG ===", 'application');
        Yii::debug("GET parameters: " . print_r(Yii::$app->request->get(), true), 'application');
        Yii::debug("range param: " . $range, 'application');
        Yii::debug("status param: " . $status, 'application');
        $request = Yii::$app->request;

        // Obtener todos los parámetros
        $status = $request->get('status', 'todos');
        $clinicasParam = $request->get('clinicas', '');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $customRange = $request->get('custom_range', false);

        // Debug log
        Yii::debug("PDF Request - range: {$range}, status: {$status}, clinicasParam: {$clinicasParam}", 'application');

        // Procesar clínicas - FIXED VERSION
        $clinicasArray = [];
        if (!empty($clinicasParam)) {
            if (is_array($clinicasParam)) {
                $clinicasArray = $clinicasParam;
            } else if (strpos($clinicasParam, ',') !== false) {
                $clinicasArray = explode(',', $clinicasParam);
            } else {
                $clinicasArray = [$clinicasParam];
            }
        }

        // Ensure it's always an array
        $clinicasArray = (array)$clinicasArray;

        // Log the processed clinics
        Yii::debug("Processed clinics array: " . print_r($clinicasArray, true), 'application');

        // Determinar fechas
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');

        // Lógica de rango de fechas - FIX DEFAULT VALUES
        if ($customRange && $dateFrom && $dateTo) {
            $startDate = $dateFrom;
            $endDate = $dateTo;
            $subtitle = "Período Personalizado: " . Yii::$app->formatter->asDate($dateFrom, 'long') . " al " . Yii::$app->formatter->asDate($dateTo, 'long');
        } else if ($specific_date && $specific_date !== 'Invalid date') {
            $startDate = $specific_date;
            $endDate = $specific_date;
            $subtitle = "Fecha Específica: " . Yii::$app->formatter->asDate($specific_date, 'long');
        } else {
            // Handle 'undefined' or empty range
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

        // Handle 'undefined' status
        $status = ($status === 'undefined' || empty($status)) ? 'todos' : $status;

        // Crear títulos
        $title = "REPORTE DE COMISIONES - SISTEMA SISPSA";
        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $subtitle .= " - " . $statusLabel;

        // Obtener datos para el reporte de comisiones
        $searchModel = new PagosReporteSearch();
        $params = $request->get();

        // Ensure params are arrays
        $params['range'] = $range;
        $params['status'] = $status;
        $params['clinicas'] = $clinicasArray;
        if ($customRange) {
            $params['custom_range'] = true;
            $params['date_from'] = $dateFrom;
            $params['date_to'] = $dateTo;
        }

        // Obtener dataProvider para comisiones
        $dataProvider = $searchModel->searchComisiones($params);
        $dataProvider->pagination = false; // Obtener todos los datos

        // Log data count
        Yii::debug("DataProvider total count: " . $dataProvider->getTotalCount(), 'application');

        // Obtener summary por clínica
        $summaryPorClinica = $this->calculateComisionesSummaryByClinica($dataProvider->query);

        // Obtener modelos para cálculos
        $models = $dataProvider->getModels();

        // Log model count
        Yii::debug("Models count: " . count($models), 'application');

        // Calcular totales generales
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
                // EXACT SAME LOGIC AS IN _comisiones-grid.php
                $montoBs = $model->monto_usd;        // Actually Bs.
                $montoUsd = $model->monto_pagado;    // Actually USD

                $tasaDia = 0;
                if ($montoUsd > 0 && $montoBs > 0) {
                    $tasaDia = $montoBs / $montoUsd;  // Bs. per USD
                }

                $totalMontoBs += $montoBs;
                $totalMontoUsd += $montoUsd;
                $totalComisionAsesorBs += $montoBs * 0.10;
                $totalComisionAsesorUsd += $tasaDia > 0 ? ($montoBs * 0.10) / $tasaDia : 0;
                $totalComisionAgenciaBs += $montoBs * 0.04;
                $totalComisionAgenciaUsd += $tasaDia > 0 ? ($montoBs * 0.04) / $tasaDia : 0;
                $totalComisionClinicaBs += $montoBs * 0.70;      // 70% of Bs.
                $totalComisionClinicaUsd += $montoUsd * 0.70;    // 70% of USD
            }
        }

        // Log totals
        Yii::debug("Calculated totals - Bs: {$totalMontoBs}, USD: {$totalMontoUsd}", 'application');

        // Generar contenido HTML para el PDF
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

        // Configuración del PDF - NO CSS IN CONTROLLER
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
            // REMOVED: 'cssInline' => $comisionesCss,
            'methods' => [
                'SetHeader' => ['Sistema SISPSA - Reporte de Comisiones||Página {PAGENO} de {nb}'],
                'SetFooter' => ['Generado el ' . date('d/m/Y H:i:s') . '||'],
            ],
            'defaultFont' => 'dejavusans',
        ]);

        // Nombre del archivo
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
     * Exporta el reporte de COMISIONES a Excel - Matching PDF format
     */
    public function actionExportComisionesExcel($range = 'day', $specific_date = null, $status = 'todos')
    {
        $request = Yii::$app->request;

        // Obtener parámetros
        $status = $request->get('status', 'todos');
        $clinicasParam = $request->get('clinicas', '');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $customRange = $request->get('custom_range', false);

        // Procesar clínicas
        $clinicasArray = [];
        if (!empty($clinicasParam)) {
            if (is_array($clinicasParam)) {
                $clinicasArray = $clinicasParam;
            } else if (strpos($clinicasParam, ',') !== false) {
                $clinicasArray = explode(',', $clinicasParam);
            } else {
                $clinicasArray = [$clinicasParam];
            }
        }

        $clinicasArray = (array)$clinicasArray;

        // Handle 'undefined' values
        $range = ($range === 'undefined' || empty($range)) ? 'day' : $range;
        $status = ($status === 'undefined' || empty($status)) ? 'todos' : $status;

        // Determinar fechas
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');

        if ($customRange && $dateFrom && $dateTo) {
            $startDate = $dateFrom;
            $endDate = $dateTo;
        } else if ($specific_date && $specific_date !== 'Invalid date') {
            $startDate = $specific_date;
            $endDate = $specific_date;
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
                    // 'day' is default
            }
        }

        // Obtener datos
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

        // Obtener summary por clínica
        $summaryPorClinica = $this->calculateComisionesSummaryByClinica($dataProvider->query);

        // Calcular totales generales
        $totalMontoBs = 0;
        $totalMontoUsd = 0;
        $totalComisionAsesorBs = 0;
        $totalComisionAsesorUsd = 0;
        $totalComisionAgenciaBs = 0;
        $totalComisionAgenciaUsd = 0;
        $totalComisionClinicaBs = 0;
        $totalComisionClinicaUsd = 0;

        foreach ($models as $model) {
            $montoBs = $model->monto_usd;        // Bs.
            $montoUsd = $model->monto_pagado;    // USD

            $tasaDia = 0;
            if ($montoUsd > 0 && $montoBs > 0) {
                $tasaDia = $montoBs / $montoUsd;  // Bs. per USD
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

        // Crear Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // =============================================
        // HOJA 1: DETALLE DE COMISIONES (PDF Format)
        // =============================================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detalle Comisiones');

        // Título principal
        $sheet->setCellValue('A1', 'REPORTE DE COMISIONES - SISTEMA SISPSA');
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Subtítulo con período
        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $periodoText = "Período: " . date('d/m/Y', strtotime($startDate)) . " al " . date('d/m/Y', strtotime($endDate)) . " - " . $statusLabel;
        $sheet->setCellValue('A2', $periodoText);
        $sheet->mergeCells('A2:O2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getFont()->getColor()->setARGB('FF0078d4');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Información del reporte
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

        // =============================================
        // ENCABEZADOS DE TABLA - EXACTAMENTE COMO EL PDF
        // =============================================
        $headerRow = 7;

        // Primera fila de encabezados (títulos principales)
        $sheet->mergeCells('A' . $headerRow . ':A' . ($headerRow + 1)); // #
        $sheet->setCellValue('A' . $headerRow, '#');

        $sheet->mergeCells('B' . $headerRow . ':B' . ($headerRow + 1)); // Afiliado
        $sheet->setCellValue('B' . $headerRow, 'Afiliado');

        $sheet->mergeCells('C' . $headerRow . ':C' . ($headerRow + 1)); // Cédula
        $sheet->setCellValue('C' . $headerRow, 'Cédula');

        // Sección MONTOS (colspan 3)
        $sheet->mergeCells('D' . $headerRow . ':F' . $headerRow);
        $sheet->setCellValue('D' . $headerRow, 'MONTOS');
        $sheet->getStyle('D' . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Subencabezados de MONTOS
        $sheet->setCellValue('D' . ($headerRow + 1), 'USD');
        $sheet->setCellValue('E' . ($headerRow + 1), 'TASA');
        $sheet->setCellValue('F' . ($headerRow + 1), 'Bs.');

        // Sección COMISIONES (colspan 4)
        $sheet->mergeCells('G' . $headerRow . ':J' . $headerRow);
        $sheet->setCellValue('G' . $headerRow, 'COMISIONES');
        $sheet->getStyle('G' . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Subencabezados de COMISIONES
        $sheet->setCellValue('G' . ($headerRow + 1), 'ASESOR (10%) Bs.');
        $sheet->setCellValue('H' . ($headerRow + 1), 'ASESOR (10%) USD');
        $sheet->setCellValue('I' . ($headerRow + 1), 'AGENCIA (4%) Bs.');
        $sheet->setCellValue('J' . ($headerRow + 1), 'AGENCIA (4%) USD');

        // Sección PAGOS CLÍNICA (colspan 2)
        $sheet->mergeCells('K' . $headerRow . ':L' . $headerRow);
        $sheet->setCellValue('K' . $headerRow, 'PAGOS CLÍNICA (70%)');
        $sheet->getStyle('K' . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Subencabezados de PAGOS CLÍNICA
        $sheet->setCellValue('K' . ($headerRow + 1), 'Bs.');
        $sheet->setCellValue('L' . ($headerRow + 1), 'USD');

        // Últimas columnas
        $sheet->mergeCells('M' . $headerRow . ':M' . ($headerRow + 1)); // Fecha
        $sheet->setCellValue('M' . $headerRow, 'Fecha');

        $sheet->mergeCells('N' . $headerRow . ':N' . ($headerRow + 1)); // Método
        $sheet->setCellValue('N' . $headerRow, 'Método');

        $sheet->mergeCells('O' . $headerRow . ':O' . ($headerRow + 1)); // Clínica
        $sheet->setCellValue('O' . $headerRow, 'Clínica');

        // Aplicar estilos a los encabezados
        $headerStyle = $sheet->getStyle('A' . $headerRow . ':O' . ($headerRow + 1));
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $headerStyle->getFill()->getStartColor()->setARGB('FF2c3e50');
        $headerStyle->getFont()->getColor()->setARGB('FFFFFFFF');
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Colores específicos para las secciones
        $sheet->getStyle('D' . $headerRow . ':F' . $headerRow)->getFill()->getStartColor()->setARGB('FF0078d4'); // Azul para MONTOS
        $sheet->getStyle('G' . $headerRow . ':J' . $headerRow)->getFill()->getStartColor()->setARGB('FF8b0000'); // Rojo para COMISIONES
        $sheet->getStyle('K' . $headerRow . ':L' . $headerRow)->getFill()->getStartColor()->setARGB('FF006400'); // Verde para PAGOS CLÍNICA

        // =============================================
        // DATOS DE LA TABLA
        // =============================================
        $dataRow = $headerRow + 2;
        $consecutivo = 1;

        if (empty($models)) {
            $sheet->setCellValue('A' . $dataRow, 'No hay datos para el período seleccionado');
            $sheet->mergeCells('A' . $dataRow . ':O' . $dataRow);
            $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        } else {
            foreach ($models as $model) {
                // Obtener datos del modelo
                $montoBs = $model->monto_usd;        // Bs.
                $montoUsd = $model->monto_pagado;    // USD

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

                // Obtener nombres
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

                // Fecha
                $fecha = $model->fecha_pago ? date('d/m/Y', strtotime($model->fecha_pago)) : 'N/A';

                // Escribir datos
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

                // Aplicar colores de fondo como en el PDF
                $sheet->getStyle('D' . $dataRow . ':F' . $dataRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF0F8FF'); // Azul claro para montos

                $sheet->getStyle('G' . $dataRow . ':H' . $dataRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFF9E6'); // Amarillo claro para comisión asesor

                $sheet->getStyle('I' . $dataRow . ':J' . $dataRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFE6E6'); // Rojo claro para comisión agencia

                $sheet->getStyle('K' . $dataRow . ':L' . $dataRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE6FFE6'); // Verde claro para pagos clínica

                $dataRow++;
            }

            // =============================================
            // FILA DE TOTALES
            // =============================================
            $totalRow = $dataRow;

            // Combinar celdas para el texto "TOTAL DETALLE"
            $sheet->mergeCells('A' . $totalRow . ':C' . $totalRow);
            $sheet->setCellValue('A' . $totalRow, 'TOTAL DETALLE');
            $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);

            // Totales de montos
            $sheet->setCellValue('D' . $totalRow, $totalMontoUsd);
            $sheet->setCellValue('E' . $totalRow, '');
            $sheet->setCellValue('F' . $totalRow, $totalMontoBs);

            // Totales de comisiones
            $sheet->setCellValue('G' . $totalRow, $totalComisionAsesorBs);
            $sheet->setCellValue('H' . $totalRow, $totalComisionAsesorUsd);
            $sheet->setCellValue('I' . $totalRow, $totalComisionAgenciaBs);
            $sheet->setCellValue('J' . $totalRow, $totalComisionAgenciaUsd);

            // Totales de pagos clínica
            $sheet->setCellValue('K' . $totalRow, $totalComisionClinicaBs);
            $sheet->setCellValue('L' . $totalRow, $totalComisionClinicaUsd);

            // Celdas vacías para las últimas columnas
            $sheet->setCellValue('M' . $totalRow, '');
            $sheet->setCellValue('N' . $totalRow, '');
            $sheet->setCellValue('O' . $totalRow, '');

            // Estilo para la fila de totales
            $sheet->getStyle('A' . $totalRow . ':O' . $totalRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF2c3e50');
            $sheet->getStyle('A' . $totalRow . ':O' . $totalRow)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A' . $totalRow . ':O' . $totalRow)->getFont()->setBold(true);
        }

        // Autoajustar columnas
        foreach (range('A', 'O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Formato de números para columnas de montos
        $currencyColumns = ['D', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        $lastDataRow = $dataRow - 1;

        foreach ($currencyColumns as $col) {
            $sheet->getStyle($col . ($headerRow + 2) . ':' . $col . $lastDataRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
            $sheet->getStyle($col . $totalRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Columnas especiales
        $sheet->getStyle('E' . ($headerRow + 2) . ':E' . $lastDataRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // =============================================
        // HOJA 2: RESUMEN POR CLÍNICA
        // =============================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Resumen por Clínica');

        // Título
        $sheet2->setCellValue('A1', 'RESUMEN DE COMISIONES POR CLÍNICA');
        $sheet2->mergeCells('A1:H1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Encabezados
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

        // Estilo encabezados
        $headerRangeResumen = 'A3:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($resumenHeaders)) . '3';
        $sheet2->getStyle($headerRangeResumen)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2c3e50']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Datos
        $resumenRow = 4;
        $totalGeneralComisiones = 0;

        if (!empty($summaryPorClinica)) {
            foreach ($summaryPorClinica as $resumen) {
                $col = 1;

                $totalComisiones = ($resumen['total_comision_asesor_bs'] ?? 0) +
                    ($resumen['total_comision_agencia_bs'] ?? 0);
                $totalGeneralComisiones += $totalComisiones;

                // Clínica
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['clinica_nombre'] ?? 'N/A');
                $col++;

                // RIF
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['clinica_rif'] ?? 'N/A');
                $col++;

                // Total Pagos
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['total_pagos'] ?? 0);
                $col++;

                // Conciliados
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['conciliados'] ?? 0);
                $col++;

                // Pendientes
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['pendientes'] ?? 0);
                $col++;

                // Comisión Asesor Bs.
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['total_comision_asesor_bs'] ?? 0);
                $col++;

                // Comisión Agencia Bs.
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $resumen['total_comision_agencia_bs'] ?? 0);
                $col++;

                // Total Comisiones Bs.
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet2->setCellValue($colLetter . $resumenRow, $totalComisiones);

                $resumenRow++;
            }

            // Fila de totales
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

            // Autoajustar columnas
            foreach (range('A', 'H') as $column) {
                $sheet2->getColumnDimension($column)->setAutoSize(true);
            }

            // Formato numérico
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

        // =============================================
        // HOJA 3: TARJETAS DE RESUMEN
        // =============================================
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Resumen General');

        // Título
        $sheet3->setCellValue('A1', 'RESUMEN GENERAL DE COMISIONES');
        $sheet3->mergeCells('A1:C1');
        $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet3->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Tarjeta 1: Comisión Asesor
        $sheet3->setCellValue('A3', 'COMISIÓN ASESOR');
        $sheet3->getStyle('A3')->getFont()->setBold(true)->setSize(12);
        $sheet3->getStyle('A3')->getFont()->getColor()->setARGB('FFFF8C00');

        $sheet3->setCellValue('A4', 'Bs. ' . number_format($totalComisionAsesorBs, 2, ',', '.'));
        $sheet3->getStyle('A4')->getFont()->setBold(true)->setSize(16);

        $sheet3->setCellValue('A5', 'USD ' . number_format($totalComisionAsesorUsd, 2, ',', '.'));
        $sheet3->getStyle('A5')->getFont()->setSize(12);

        $sheet3->setCellValue('A6', '10%');
        $sheet3->getStyle('A6')->getFont()->setBold(true);

        // Tarjeta 2: Comisión Agencia
        $sheet3->setCellValue('C3', 'COMISIÓN AGENCIA');
        $sheet3->getStyle('C3')->getFont()->setBold(true)->setSize(12);
        $sheet3->getStyle('C3')->getFont()->getColor()->setARGB('FFDC3545');

        $sheet3->setCellValue('C4', 'Bs. ' . number_format($totalComisionAgenciaBs, 2, ',', '.'));
        $sheet3->getStyle('C4')->getFont()->setBold(true)->setSize(16);

        $sheet3->setCellValue('C5', 'USD ' . number_format($totalComisionAgenciaUsd, 2, ',', '.'));
        $sheet3->getStyle('C5')->getFont()->setSize(12);

        $sheet3->setCellValue('C6', '4%');
        $sheet3->getStyle('C6')->getFont()->setBold(true);

        // Tarjeta 3: Pagos Clínica
        $sheet3->setCellValue('E3', 'PAGOS CLÍNICA');
        $sheet3->getStyle('E3')->getFont()->setBold(true)->setSize(12);
        $sheet3->getStyle('E3')->getFont()->getColor()->setARGB('FF107C10');

        $sheet3->setCellValue('E4', 'Bs. ' . number_format($totalComisionClinicaBs, 2, ',', '.'));
        $sheet3->getStyle('E4')->getFont()->setBold(true)->setSize(16);

        $sheet3->setCellValue('E5', 'USD ' . number_format($totalComisionClinicaUsd, 2, ',', '.'));
        $sheet3->getStyle('E5')->getFont()->setSize(12);

        $sheet3->setCellValue('E6', '70%');
        $sheet3->getStyle('E6')->getFont()->setBold(true);

        // Total General
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

        // Autoajustar columnas en hoja 3
        $sheet3->getColumnDimension('A')->setWidth(30);
        $sheet3->getColumnDimension('C')->setWidth(30);
        $sheet3->getColumnDimension('E')->setWidth(30);

        // Regresar a la primera hoja
        $spreadsheet->setActiveSheetIndex(0);

        // Generar archivo
        $filename = 'Reporte_Comisiones_' . date('Ymd_His') . '.xlsx';

        // Limpiar buffers
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

    public function actionTestData()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Test 1: Get all payments
        $allPayments = Pagos::find()->count();

        // Test 2: Get payments from today
        $today = date('Y-m-d');
        $todayPayments = Pagos::find()
            ->where(['DATE(fecha_pago)' => $today])
            ->count();

        // Test 3: Get payments with Conciliado/Por Conciliar status
        $statusPayments = Pagos::find()
            ->where(['estatus' => ['Conciliado', 'Por Conciliar']])
            ->count();

        // Test 4: Get payments with contracts
        $paymentsWithContracts = Pagos::find()
            ->joinWith('contratos')
            ->where(['IS NOT', 'contratos.id', null])
            ->count();

        return [
            'all_payments' => $allPayments,
            'today_payments' => $todayPayments,
            'status_payments' => $statusPayments,
            'payments_with_contracts' => $paymentsWithContracts,
            'today_date' => $today,
        ];
    }
    public function actionTestPdf()
    {
        // Simple test - get ALL data without filters
        $models = Pagos::find()
            ->joinWith(['userDatos', 'contratos'])
            ->where(['pagos.estatus' => ['Conciliado', 'Por Conciliar']])
            ->limit(10)
            ->all();

        Yii::debug("Test found " . count($models) . " models", 'application');

        if (empty($models)) {
            return "No data found at all! Check your database.";
        }

        // Calculate totals
        $totalMontoBs = 0;
        $totalMontoUsd = 0;

        foreach ($models as $model) {
            $totalMontoBs += $model->monto_usd;
            $totalMontoUsd += $model->monto_pagado;
        }

        $content = "
    <h1>Test PDF</h1>
    <p>Found " . count($models) . " records</p>
    <p>Total Bs: " . number_format($totalMontoBs, 2) . "</p>
    <p>Total USD: " . number_format($totalMontoUsd, 2) . "</p>
    <p>First record:</p>
    <ul>
        <li>ID: " . $models[0]->id . "</li>
        <li>monto_usd: " . $models[0]->monto_usd . "</li>
        <li>monto_pagado: " . $models[0]->monto_pagado . "</li>
        <li>fecha_pago: " . $models[0]->fecha_pago . "</li>
        <li>estatus: " . $models[0]->estatus . "</li>
    </ul>
    ";

        $pdf = new \kartik\mpdf\Pdf([
            'mode' => \kartik\mpdf\Pdf::MODE_UTF8,
            'format' => \kartik\mpdf\Pdf::FORMAT_A4,
            'orientation' => \kartik\mpdf\Pdf::ORIENT_PORTRAIT,
            'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
            'content' => $content,
            'cssInline' => 'body { font-family: DejaVu Sans; }',
        ]);

        return $pdf->render();
    }

    /**
     * Genera el reporte de COMISIONES en PDF usando TCPDF (Professional version)
     */
    public function actionGenerateComisionesPdfTcpdf($range = 'day', $specific_date = null, $status = 'todos')
    {
        // Clear all output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        ob_start();

        try {
            $request = Yii::$app->request;

            // Obtener todos los parámetros
            $status = $request->get('status', 'todos');
            $clinicasParam = $request->get('clinicas', '');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $customRange = $request->get('custom_range', false);

            // Procesar clínicas
            $clinicasArray = [];
            if (!empty($clinicasParam)) {
                if (is_array($clinicasParam)) {
                    $clinicasArray = $clinicasParam;
                } else if (strpos($clinicasParam, ',') !== false) {
                    $clinicasArray = explode(',', $clinicasParam);
                } else {
                    $clinicasArray = [$clinicasParam];
                }
            }

            $clinicasArray = (array)$clinicasArray;

            // Determinar fechas
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

            // Crear títulos
            $title = "REPORTE DE COMISIONES - SISTEMA SISPSA";
            $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
            $subtitle .= " - " . $statusLabel;

            // Obtener datos
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

            // Obtener summary por clínica
            $summaryPorClinica = $this->calculateComisionesSummaryByClinica($dataProvider->query);

            // Calcular totales
            $totalMontoBs = 0;
            $totalMontoUsd = 0;
            $totalComisionAsesorBs = 0;
            $totalComisionAsesorUsd = 0;
            $totalComisionAgenciaBs = 0;
            $totalComisionAgenciaUsd = 0;
            $totalComisionClinicaBs = 0;
            $totalComisionClinicaUsd = 0;

            foreach ($models as $model) {
                $montoBs = $model->monto_usd;        // Actually Bs.
                $montoUsd = $model->monto_pagado;    // Actually USD

                $tasaDia = 0;
                if ($montoUsd > 0 && $montoBs > 0) {
                    $tasaDia = $montoBs / $montoUsd;  // Bs. per USD
                }

                $totalMontoBs += $montoBs;
                $totalMontoUsd += $montoUsd;
                $totalComisionAsesorBs += $montoBs * 0.10;
                $totalComisionAsesorUsd += $tasaDia > 0 ? ($montoBs * 0.10) / $tasaDia : 0;
                $totalComisionAgenciaBs += $montoBs * 0.04;
                $totalComisionAgenciaUsd += $tasaDia > 0 ? ($montoBs * 0.04) / $tasaDia : 0;
                $totalComisionClinicaBs += $montoBs * 0.70;      // 70% of Bs.
                $totalComisionClinicaUsd += $montoUsd * 0.70;    // 70% of USD
            }

            // Create TCPDF instance
            $pdf = new \app\components\TcpdfHelper('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Set report titles for header
            $pdf->setReportTitle($title);
            $pdf->setReportSubtitle($subtitle);

            // Set document information
            $pdf->SetCreator('Sistema SISPSA');
            $pdf->SetAuthor('Sistema SISPSA');
            $pdf->SetTitle($title);
            $pdf->SetSubject('Reporte de Comisiones');

            // Set default header data (we'll override in Header method)
            $pdf->SetHeaderData('', 0, '', '');

            // Set header and footer fonts
            $pdf->setHeaderFont(array('helvetica', '', 10));
            $pdf->setFooterFont(array('helvetica', '', 8));

            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // Set margins
            $pdf->SetMargins(10, 40, 10); // Left, Top, Right
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);

            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 15);

            // Set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // Add a page - LANDSCAPE
            $pdf->AddPage('L');

            // Set font for content
            $pdf->SetFont('helvetica', '', 9);

            // =============================================
            // BUILD PDF CONTENT MANUALLY FOR PRECISE CONTROL
            // =============================================

            // Report information section
            $pdf->SetFillColor(248, 249, 250); // #f8f9fa
            $pdf->SetDrawColor(222, 226, 230); // #dee2e6
            $pdf->RoundedRect(10, $pdf->GetY(), 277, 25, 3, '1111', 'DF');

            $pdf->SetY($pdf->GetY() + 5);
            $pdf->SetX(15);

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetTextColor(44, 62, 80); // #2c3e50
            $pdf->Cell(25, 5, 'Período:', 0, 0, 'L');

            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(51, 51, 51); // #333333
            $pdf->Cell(80, 5, date('d/m/Y', strtotime($startDate)) . ' al ' . date('d/m/Y', strtotime($endDate)), 0, 0, 'L');

            $pdf->SetX(120);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(25, 5, 'Generado:', 0, 0, 'L');

            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(51, 51, 51);
            $pdf->Cell(60, 5, date('d/m/Y H:i:s'), 0, 1, 'L');

            // Summary Cards Section
            $pdf->SetY($pdf->GetY() + 10);

            // Card 1: Comisión Asesor
            $pdf->SetFillColor(255, 249, 230); // #fff9e6
            $pdf->SetDrawColor(255, 193, 7); // #ffc107
            $pdf->RoundedRect(10, $pdf->GetY(), 85, 35, 5, '1111', 'DF');

            $pdf->SetY($pdf->GetY() + 3);
            $pdf->SetX(15);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetTextColor(44, 62, 80); // #2c3e50
            $pdf->Cell(75, 5, 'Comisión Asesor', 0, 1, 'C');

            $pdf->SetX(15);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(75, 8, 'Bs. ' . number_format($totalComisionAsesorBs, 2, ',', '.'), 0, 1, 'C');

            $pdf->SetX(15);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(102, 102, 102); // #666666
            $pdf->Cell(75, 5, 'USD ' . number_format($totalComisionAsesorUsd, 2, ',', '.'), 0, 1, 'C');

            // Card 2: Comisión Agencia
            $pdf->SetY($pdf->GetY() - 21);
            $pdf->SetX(105);
            $pdf->SetFillColor(255, 230, 230); // #ffe6e6
            $pdf->SetDrawColor(220, 53, 69); // #dc3545
            $pdf->RoundedRect(100, $pdf->GetY(), 85, 35, 5, '1111', 'DF');

            $pdf->SetY($pdf->GetY() + 3);
            $pdf->SetX(105);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(75, 5, 'Comisión Agencia', 0, 1, 'C');

            $pdf->SetX(105);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(75, 8, 'Bs. ' . number_format($totalComisionAgenciaBs, 2, ',', '.'), 0, 1, 'C');

            $pdf->SetX(105);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(102, 102, 102);
            $pdf->Cell(75, 5, 'USD ' . number_format($totalComisionAgenciaUsd, 2, ',', '.'), 0, 1, 'C');

            // Card 3: Pago a Clínicas
            $pdf->SetY($pdf->GetY() - 21);
            $pdf->SetX(195);
            $pdf->SetFillColor(230, 255, 230); // #e6ffe6
            $pdf->SetDrawColor(40, 167, 69); // #28a745
            $pdf->RoundedRect(190, $pdf->GetY(), 85, 35, 5, '1111', 'DF');

            $pdf->SetY($pdf->GetY() + 3);
            $pdf->SetX(195);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(75, 5, 'Pago a Clínicas', 0, 1, 'C');

            $pdf->SetX(195);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(75, 8, 'Bs. ' . number_format($totalComisionClinicaBs, 2, ',', '.'), 0, 1, 'C');

            $pdf->SetX(195);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(102, 102, 102);
            $pdf->Cell(75, 5, 'USD ' . number_format($totalComisionClinicaUsd, 2, ',', '.'), 0, 1, 'C');

            // Detailed Table Header
            $pdf->SetY($pdf->GetY() + 15);
            $pdf->SetFillColor(44, 62, 80); // #2c3e50
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(277, 8, 'DETALLE DE COMISIONES', 1, 1, 'C', true);

            // Table Headers
            $pdf->SetY($pdf->GetY());
            $pdf->SetFont('helvetica', 'B', 8);

            // Define column widths (total 277mm)
            $colWidths = [
                11,  // # (4%)
                33,  // Afiliado (12%)
                22,  // Cédula (8%)
                19,  // USD (7%)
                19,  // Tasa (7%)
                19,  // Bs. (7%)
                25,  // Com. Asesor Bs. (9%)
                25,  // Com. Asesor USD (9%)
                25,  // Com. Agencia Bs. (9%)
                25,  // Com. Agencia USD (9%)
                25,  // Pago Clínica Bs. (9%)
                25,  // Pago Clínica USD (9%)
                17   // Clínica (6%)
            ];

            $headers = ['#', 'Afiliado', 'Cédula', 'USD', 'Tasa', 'Bs.', 'Com. Asesor Bs.', 'Com. Asesor USD', 'Com. Agencia Bs.', 'Com. Agencia USD', 'Pago Clínica Bs.', 'Pago Clínica USD', 'Clínica'];

            // Print headers
            $pdf->SetFillColor(52, 73, 94); // #34495e slightly darker
            foreach ($headers as $index => $header) {
                $pdf->Cell($colWidths[$index], 7, $header, 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Table Data
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(0, 0, 0);  // Set text to BLACK for all data
            $row = 0;

            // Reset totals for calculation
            $totalMontoBs = 0;
            $totalMontoUsd = 0;
            $totalComisionAsesorBs = 0;
            $totalComisionAsesorUsd = 0;
            $totalComisionAgenciaBs = 0;
            $totalComisionAgenciaUsd = 0;
            $totalPagoClinicaBs = 0;
            $totalPagoClinicaUsd = 0;

            if (empty($models)) {
                $pdf->SetFillColor(255, 255, 255);
                $pdf->Cell(277, 20, 'No hay datos para el período seleccionado', 1, 1, 'C', true);
            } else {
                foreach ($models as $index => $model) {
                    // Alternate row color
                    if ($row % 2 == 0) {
                        $pdf->SetFillColor(248, 249, 250); // #f8f9fa
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                    }

                    // Get data from model
                    $montoBs = $model->monto_usd;
                    $montoUsd = $model->monto_pagado;

                    $tasaDia = 0;
                    if ($montoUsd > 0 && $montoBs > 0) {
                        $tasaDia = $montoBs / $montoUsd;
                    }

                    // Calculate commissions
                    $comisionAsesorBs = $montoBs * 0.10;
                    $comisionAsesorUsd = $tasaDia > 0 ? $comisionAsesorBs / $tasaDia : 0;
                    $comisionAgenciaBs = $montoBs * 0.04;
                    $comisionAgenciaUsd = $tasaDia > 0 ? $comisionAgenciaBs / $tasaDia : 0;
                    $pagoClinicaBs = $montoBs * 0.70;
                    $pagoClinicaUsd = $montoUsd * 0.70;

                    // Accumulate totals
                    $totalMontoBs += $montoBs;
                    $totalMontoUsd += $montoUsd;
                    $totalComisionAsesorBs += $comisionAsesorBs;
                    $totalComisionAsesorUsd += $comisionAsesorUsd;
                    $totalComisionAgenciaBs += $comisionAgenciaBs;
                    $totalComisionAgenciaUsd += $comisionAgenciaUsd;
                    $totalPagoClinicaBs += $pagoClinicaBs;
                    $totalPagoClinicaUsd += $pagoClinicaUsd;

                    // Get clinic name
                    $clinicaNombre = 'Sin Clínica';
                    if ($model->contratos && count($model->contratos) > 0) {
                        foreach ($model->contratos as $contrato) {
                            if ($contrato->clinica) {
                                $clinicaNombre = $contrato->clinica->nombre;
                                break;
                            }
                        }
                    }

                    // Get affiliate name
                    $afiliado = 'N/A';
                    if ($model->userDatos) {
                        $afiliado = $model->userDatos->nombres . ' ' . $model->userDatos->apellidos;
                    }

                    // Get cedula
                    $cedula = $model->userDatos ? $model->userDatos->cedula : 'N/A';

                    // Print row - ALL TEXT IS BLACK
                    $pdf->Cell($colWidths[0], 6, $index + 1, 1, 0, 'C', true);
                    $pdf->Cell($colWidths[1], 6, substr($afiliado, 0, 20), 1, 0, 'L', true);
                    $pdf->Cell($colWidths[2], 6, $cedula, 1, 0, 'C', true);

                    // USD column with blue background
                    $pdf->SetFillColor(232, 244, 253); // #e8f4fd
                    $pdf->Cell($colWidths[3], 6, number_format($montoUsd, 2, ',', '.'), 1, 0, 'R', true);

                    // Tasa column
                    if ($row % 2 == 0) {
                        $pdf->SetFillColor(248, 249, 250);
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                    }
                    $pdf->Cell($colWidths[4], 6, $tasaDia > 0 ? number_format($tasaDia, 2, ',', '.') : 'N/A', 1, 0, 'C', true);

                    // Bs. column with blue background
                    $pdf->SetFillColor(232, 244, 253);
                    $pdf->Cell($colWidths[5], 6, number_format($montoBs, 2, ',', '.'), 1, 0, 'R', true);

                    // Com. Asesor Bs. with yellow background
                    $pdf->SetFillColor(255, 249, 230); // #fff9e6
                    $pdf->Cell($colWidths[6], 6, number_format($comisionAsesorBs, 2, ',', '.'), 1, 0, 'R', true);

                    // Com. Asesor USD with yellow background
                    $pdf->Cell($colWidths[7], 6, number_format($comisionAsesorUsd, 2, ',', '.'), 1, 0, 'R', true);

                    // Com. Agencia Bs. with red background
                    $pdf->SetFillColor(255, 230, 230); // #ffe6e6
                    $pdf->Cell($colWidths[8], 6, number_format($comisionAgenciaBs, 2, ',', '.'), 1, 0, 'R', true);

                    // Com. Agencia USD with red background
                    $pdf->Cell($colWidths[9], 6, number_format($comisionAgenciaUsd, 2, ',', '.'), 1, 0, 'R', true);

                    // Pago Clínica Bs. with green background
                    $pdf->SetFillColor(232, 247, 232); // #e8f7e8
                    $pdf->Cell($colWidths[10], 6, number_format($pagoClinicaBs, 2, ',', '.'), 1, 0, 'R', true);

                    // Pago Clínica USD with green background
                    $pdf->Cell($colWidths[11], 6, number_format($pagoClinicaUsd, 2, ',', '.'), 1, 0, 'R', true);

                    // Clínica column
                    if ($row % 2 == 0) {
                        $pdf->SetFillColor(248, 249, 250);
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                    }
                    $pdf->Cell($colWidths[12], 6, substr($clinicaNombre, 0, 12), 1, 1, 'C', true);

                    $row++;

                    // Check if we need a new page
                    if ($pdf->GetY() > 180 && $index < count($models) - 1) {
                        $pdf->AddPage('L');

                        // Reprint headers on new page
                        $pdf->SetFillColor(52, 73, 94);
                        $pdf->SetTextColor(255, 255, 255);  // White for headers
                        $pdf->SetFont('helvetica', 'B', 8);
                        $pdf->SetY(40);

                        foreach ($headers as $i => $header) {
                            $pdf->Cell($colWidths[$i], 7, $header, 1, 0, 'C', true);
                        }
                        $pdf->Ln();
                        $pdf->SetFont('helvetica', '', 7);
                        // RESET TEXT COLOR TO BLACK FOR DATA
                        $pdf->SetTextColor(0, 0, 0);
                    }
                }

                // Totals Row
                $pdf->SetFillColor(44, 62, 80); // #2c3e50
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('helvetica', 'B', 8);

                $pdf->Cell($colWidths[0] + $colWidths[1] + $colWidths[2], 7, 'TOTALES:', 1, 0, 'R', true);

                // USD total
                $pdf->SetFillColor(44, 62, 80);
                $pdf->Cell($colWidths[3], 7, number_format($totalMontoUsd, 2, ',', '.'), 1, 0, 'R', true);

                // Empty cell for Tasa
                $pdf->Cell($colWidths[4], 7, '', 1, 0, 'C', true);

                // Bs. total
                $pdf->Cell($colWidths[5], 7, number_format($totalMontoBs, 2, ',', '.'), 1, 0, 'R', true);

                // Com. Asesor Bs. total
                $pdf->Cell($colWidths[6], 7, number_format($totalComisionAsesorBs, 2, ',', '.'), 1, 0, 'R', true);

                // Com. Asesor USD total
                $pdf->Cell($colWidths[7], 7, number_format($totalComisionAsesorUsd, 2, ',', '.'), 1, 0, 'R', true);

                // Com. Agencia Bs. total
                $pdf->Cell($colWidths[8], 7, number_format($totalComisionAgenciaBs, 2, ',', '.'), 1, 0, 'R', true);

                // Com. Agencia USD total
                $pdf->Cell($colWidths[9], 7, number_format($totalComisionAgenciaUsd, 2, ',', '.'), 1, 0, 'R', true);

                // Pago Clínica Bs. total
                $pdf->Cell($colWidths[10], 7, number_format($totalPagoClinicaBs, 2, ',', '.'), 1, 0, 'R', true);

                // Pago Clínica USD total
                $pdf->Cell($colWidths[11], 7, number_format($totalPagoClinicaUsd, 2, ',', '.'), 1, 0, 'R', true);

                // Empty cell for Clínica
                $pdf->Cell($colWidths[12], 7, '', 1, 1, 'C', true);
            }

            // =============================================
            // RESUMEN POR CLÍNICA SECTION
            // =============================================
            if (!empty($summaryPorClinica)) {
                // Check if we need a new page for clinic summary
                if ($pdf->GetY() > 150) {
                    $pdf->AddPage('L');
                } else {
                    $pdf->SetY($pdf->GetY() + 10);
                }

                // Clinic Summary Header
                $pdf->SetFillColor(44, 62, 80); // #2c3e50
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(277, 8, 'RESUMEN POR CLÍNICA', 1, 1, 'C', true);

                // Clinic Summary Headers
                $pdf->SetY($pdf->GetY());
                $pdf->SetFont('helvetica', 'B', 8);

                // Define column widths for clinic summary
                $clinicaColWidths = [
                    70,   // Clínica (25%)
                    42,   // RIF (15%)
                    28,   // Total Pagos (10%)
                    28,   // Conciliados (10%)
                    28,   // Pendientes (10%)
                    42,   // Com. Asesor Bs. (15%)
                    42    // Com. Agencia Bs. (15%)
                ];

                $clinicaHeaders = ['Clínica', 'RIF', 'Total Pagos', 'Conciliados', 'Pendientes', 'Com. Asesor Bs.', 'Com. Agencia Bs.'];

                // Print clinic headers
                $pdf->SetFillColor(52, 73, 94);
                foreach ($clinicaHeaders as $index => $header) {
                    $pdf->Cell($clinicaColWidths[$index], 7, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();

                // Clinic Summary Data
                $pdf->SetFont('helvetica', '', 7);
                $pdf->SetTextColor(0, 0, 0);  // Black text

                $clinicaTotalPagos = 0;
                $clinicaTotalConciliados = 0;
                $clinicaTotalPendientes = 0;
                $clinicaTotalComisionAsesor = 0;
                $clinicaTotalComisionAgencia = 0;
                $clinicaRow = 0;

                foreach ($summaryPorClinica as $index => $resumen) {
                    // Alternate row color
                    if ($clinicaRow % 2 == 0) {
                        $pdf->SetFillColor(248, 249, 250); // #f8f9fa
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                    }

                    // Accumulate totals
                    $clinicaTotalPagos += $resumen['total_pagos'] ?? 0;
                    $clinicaTotalConciliados += $resumen['conciliados'] ?? 0;
                    $clinicaTotalPendientes += $resumen['pendientes'] ?? 0;
                    $clinicaTotalComisionAsesor += $resumen['total_comision_asesor_bs'] ?? 0;
                    $clinicaTotalComisionAgencia += $resumen['total_comision_agencia_bs'] ?? 0;

                    // Print clinic row
                    $pdf->Cell($clinicaColWidths[0], 6, substr($resumen['clinica_nombre'] ?? 'N/A', 0, 25), 1, 0, 'L', true);
                    $pdf->Cell($clinicaColWidths[1], 6, substr($resumen['clinica_rif'] ?? 'N/A', 0, 12), 1, 0, 'C', true);
                    $pdf->Cell($clinicaColWidths[2], 6, $resumen['total_pagos'] ?? 0, 1, 0, 'C', true);
                    $pdf->Cell($clinicaColWidths[3], 6, $resumen['conciliados'] ?? 0, 1, 0, 'C', true);
                    $pdf->Cell($clinicaColWidths[4], 6, $resumen['pendientes'] ?? 0, 1, 0, 'C', true);

                    // Com. Asesor Bs. with yellow background
                    $pdf->SetFillColor(255, 249, 230); // #fff9e6
                    $pdf->Cell($clinicaColWidths[5], 6, number_format($resumen['total_comision_asesor_bs'] ?? 0, 2, ',', '.'), 1, 0, 'R', true);

                    // Com. Agencia Bs. with red background
                    $pdf->SetFillColor(255, 230, 230); // #ffe6e6
                    $pdf->Cell($clinicaColWidths[6], 6, number_format($resumen['total_comision_agencia_bs'] ?? 0, 2, ',', '.'), 1, 1, 'R', true);

                    $clinicaRow++;
                }

                // Clinic Totals Row
                $pdf->SetFillColor(44, 62, 80); // #2c3e50
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('helvetica', 'B', 8);

                $pdf->Cell($clinicaColWidths[0] + $clinicaColWidths[1], 7, 'TOTALES CLÍNICAS:', 1, 0, 'R', true);
                $pdf->Cell($clinicaColWidths[2], 7, $clinicaTotalPagos, 1, 0, 'C', true);
                $pdf->Cell($clinicaColWidths[3], 7, $clinicaTotalConciliados, 1, 0, 'C', true);
                $pdf->Cell($clinicaColWidths[4], 7, $clinicaTotalPendientes, 1, 0, 'C', true);
                $pdf->Cell($clinicaColWidths[5], 7, number_format($clinicaTotalComisionAsesor, 2, ',', '.'), 1, 0, 'R', true);
                $pdf->Cell($clinicaColWidths[6], 7, number_format($clinicaTotalComisionAgencia, 2, ',', '.'), 1, 1, 'R', true);
            }

            // Grand Total Section
            $pdf->SetY($pdf->GetY() + 10);
            $pdf->SetFillColor(44, 62, 80); // #2c3e50
            $pdf->SetDrawColor(0, 120, 212); // #0078d4
            $pdf->SetLineWidth(2);
            $pdf->RoundedRect(50, $pdf->GetY(), 197, 30, 5, '1111', 'DF');

            $pdf->SetY($pdf->GetY() + 5);
            $pdf->SetX(55);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(187, 8, 'TOTAL GENERAL DE COMISIONES', 0, 1, 'C');

            $pdf->SetX(55);
            $pdf->SetFont('helvetica', 'B', 20);
            $pdf->SetTextColor(76, 217, 100); // #4cd964
            $pdf->Cell(187, 12, 'Bs. ' . number_format($totalComisionAsesorBs + $totalComisionAgenciaBs, 2, ',', '.'), 0, 1, 'C');

            $pdf->SetX(55);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(204, 204, 204); // #cccccc
            $pdf->Cell(187, 5, 'USD ' . number_format($totalComisionAsesorUsd + $totalComisionAgenciaUsd, 2, ',', '.'), 0, 1, 'C');

            // Footer information
            $pdf->SetY($pdf->GetY() + 15);
            $pdf->SetDrawColor(221, 221, 221); // #dddddd
            $pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());

            $pdf->SetY($pdf->GetY() + 5);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(102, 102, 102); // #666666
            $pdf->Cell(277, 5, 'Reporte generado automáticamente por el Sistema SISPSA', 0, 1, 'C');
            $pdf->Cell(277, 5, 'Total de registros procesados: ' . count($models), 0, 1, 'C');
            $pdf->Cell(277, 5, 'Documento confidencial - Uso interno', 0, 1, 'C');

            // Clean output and send PDF
            ob_clean();

            $filename = 'Reporte_Comisiones_' . date('Ymd_His') . '.pdf';
            $pdf->Output($filename, 'D');

            exit;
        } catch (\Exception $e) {
            Yii::error('Error generando PDF con TCPDF: ' . $e->getMessage());
            Yii::error($e->getTraceAsString());
            Yii::$app->session->setFlash('error', 'Error al generar el PDF: ' . $e->getMessage());
            return $this->redirect(['comisiones']);
        }
    }
}
