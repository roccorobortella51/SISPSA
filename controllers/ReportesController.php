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
                        'actions' => ['index', 'get-pagos-detail', 'generate-pdf', 'export-excel'],
                        // Acceso para 'superadmin' y 'finanzas'
                        'roles' => ['superadmin', 'FINANZAS'],
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
        $range = $request->post('range', 'day'); // 'day', 'week', 'month', 'last-month'
        $specificDate = $request->post('specific_date');
        $status = $request->post('status', 'Por Conciliar');
        $clinicas = $request->post('clinicas', []); // Nuevo: array de IDs de clínicas

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $title = "Detalle de Pagos de Hoy";

        // 1. Lógica de rango de fechas (CON NUEVO CASO PARA MES ANTERIOR)
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
        }

        if ($specificDate && $specificDate !== 'Invalid date') {
            $startDate = $specificDate;
            $endDate = $specificDate;
            $title = "Detalle de Pagos para el día: " . Yii::$app->formatter->asDate($specificDate, 'long');
        }

        // Modificar título para reflejar el estado seleccionado
        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $title .= " ({$statusLabel})";

        // 2. Crear y configurar el modelo de búsqueda
        $searchModel = new PagosReporteSearch();

        // 3. Obtener el resumen general
        $summary = $searchModel->obtenerResumenGeneral($startDate, $endDate, $status, $clinicas);

        // 4. Obtener el resumen por clínica (si hay filtro de clínicas)
        $summaryPorClinica = [];
        if (!empty($clinicas)) {
            if (in_array('todas', $clinicas)) {
                // Cuando se selecciona "todas", obtener todas las clínicas con pagos
                $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, []);
            } else {
                // Cuando se seleccionan clínicas específicas
                $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, $clinicas);
            }
        } else {
            // Por defecto, mostrar todas las clínicas
            $summaryPorClinica = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, []);
        }

        // 5. Obtener el dataProvider para el GridView
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
            // Configuración básica
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            'destination' => Pdf::DEST_DOWNLOAD,
            'content' => $content,

            // Opciones
            'options' => [
                'title' => $title,
            ],

            // Márgenes
            'marginLeft' => 10,
            'marginRight' => 10,
            'marginTop' => 20,
            'marginBottom' => 20,
            'marginHeader' => 5,
            'marginFooter' => 10,

            // CSS SIMPLIFICADO que SÍ funciona
            'cssInline' => $simpleCss,

            // Encabezado y pie de página
            'methods' => [
                'SetHeader' => ['Sistema SISPSA - Reporte de Pagos||Página {PAGENO} de {nb}'],
                'SetFooter' => ['Generado el ' . date('d/m/Y H:i:s') . '||'],
            ],

            // Fuente por defecto
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
        // HOJA 1: DETALLE DE PAGOS
        // =============================================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detalle de Pagos');

        // Encabezado principal
        $sheet->setCellValue('A1', 'REPORTE DE PAGOS DE AFILIADOS');
        $sheet->mergeCells('A1:I1');
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

        // Encabezados de columnas
        $headerRow = 7;
        $headers = [
            'ID Pago',
            'Nombres',
            'Apellidos',
            'Cédula',
            'Monto (Bs.)',
            'Fecha Pago',
            'Método de Pago',
            'Estado',
            'Clínica'
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

        // Datos de pagos
        $dataRow = $headerRow + 1;
        $totalMonto = 0;

        if (empty($models)) {
            $sheet->setCellValue('A' . $dataRow, 'No hay datos para el período seleccionado');
            $sheet->mergeCells('A' . $dataRow . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . $dataRow);
            $sheet->getStyle('A' . $dataRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $dataRow)->getFont()->setItalic(true);
        } else {
            foreach ($models as $model) {
                $col = 1; // Empezar en columna A (índice 1)
                $colLetter = 'A'; // Columna inicial como letra

                // ID Pago
                $sheet->setCellValue($colLetter . $dataRow, $model->id);
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);

                // Nombres
                $nombres = $model->userDatos ? $model->userDatos->nombres : 'N/A';
                $sheet->setCellValue($colLetter . $dataRow, $nombres);
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);

                // Apellidos
                $apellidos = $model->userDatos ? $model->userDatos->apellidos : 'N/A';
                $sheet->setCellValue($colLetter . $dataRow, $apellidos);
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);

                // Cédula
                $cedula = $model->userDatos ? $model->userDatos->cedula : 'N/A';
                $sheet->setCellValue($colLetter . $dataRow, $cedula);
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);

                // Monto
                $sheet->setCellValue($colLetter . $dataRow, $model->monto_usd);
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);

                // Fecha Pago
                $fechaPago = $model->fecha_pago ? Yii::$app->formatter->asDate($model->fecha_pago, 'php:d/m/Y') : 'N/A';
                $sheet->setCellValue($colLetter . $dataRow, $fechaPago);
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);

                // Método de Pago
                $sheet->setCellValue($colLetter . $dataRow, $model->metodo_pago ?: 'N/A');
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);

                // Estatus
                $sheet->setCellValue($colLetter . $dataRow, $model->estatus ?: 'N/A');
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);

                // Clínica
                $clinicaNombre = 'Sin Clínica';
                if ($model->contratos && count($model->contratos) > 0) {
                    foreach ($model->contratos as $contrato) {
                        if ($contrato->clinica) {
                            $clinicaNombre = $contrato->clinica->nombre;
                            break;
                        }
                    }
                }
                $sheet->setCellValue($colLetter . $dataRow, $clinicaNombre);

                $dataRow++;
            }
        }

        // Autoajustar ancho de columnas
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Formato de moneda para columna de monto
        $lastDataRow = empty($models) ? $dataRow : $dataRow - 1;
        $sheet->getStyle('E' . ($headerRow + 1) . ':E' . $lastDataRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // Bordes para todos los datos
        if (!empty($models)) {
            $dataStyle = $sheet->getStyle('A' . $headerRow . ':I' . $lastDataRow);
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
}
