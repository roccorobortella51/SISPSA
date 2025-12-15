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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
        // TEMPORARY DEBUG - Check if user is authenticated
        if (Yii::$app->user->isGuest) {
            return [
                'success' => false,
                'message' => 'User is guest. Not authenticated.',
                'identity' => null
            ];
        }

        // TEMPORARY DEBUG - Check user roles
        $user = Yii::$app->user->identity;
        $roles = Yii::$app->authManager->getRolesByUser($user->id);

        Yii::error('User roles: ' . json_encode(array_keys($roles)));
        Yii::error('User ID: ' . $user->id);

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

            // Lógica de rango de fechas
            if ($customRange && $dateFrom && $dateTo) {
                // Rango personalizado
                $startDate = $dateFrom;
                $endDate = $dateTo;
                $title = "Detalle de Pagos del " . Yii::$app->formatter->asDate($dateFrom, 'long') .
                    " al " . Yii::$app->formatter->asDate($dateTo, 'long');
            } else {
                // Rangos predefinidos
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

        // Obtener el estado del pago del parámetro GET
        $status = $request->get('status', 'Por Conciliar');

        // Obtener clínicas del parámetro GET (puede ser string separada por comas)
        $clinicasParam = $request->get('clinicas', '');
        $clinicasArray = [];

        if (!empty($clinicasParam)) {
            if (is_array($clinicasParam)) {
                $clinicasArray = $clinicasParam;
            }
            // Si es un string separado por comas
            else if (strpos($clinicasParam, ',') !== false) {
                $clinicasArray = explode(',', $clinicasParam);
            } else {
                $clinicasArray = [$clinicasParam];
            }
        }

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $title = "Detalle de Pagos de Hoy";

        // 1. Lógica de rango de fechas
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

        // Check for custom date range
        if ($customRange && $dateFrom && $dateTo) {
            $startDate = $dateFrom;
            $endDate = $dateTo;
            $title = "Detalle de Pagos del " . Yii::$app->formatter->asDate($dateFrom, 'long') .
                " al " . Yii::$app->formatter->asDate($dateTo, 'long');
        } else if ($specific_date && $specific_date !== 'Invalid date') {
            $startDate = $specific_date;
            $endDate = $specific_date;
            $title = "Detalle de Pagos para el día: " . Yii::$app->formatter->asDate($specific_date, 'long');
        }

        // Modificar título para reflejar el estado seleccionado
        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');
        $title .= " ({$statusLabel})";

        // Agregar información de clínicas filtradas al título si aplica
        if (!empty($clinicasArray) && !in_array('todas', $clinicasArray)) {
            // Obtener nombres de las clínicas seleccionadas
            $clinicasNombres = [];
            foreach ($clinicasArray as $clinicaId) {
                $clinica = \app\models\RmClinica::findOne($clinicaId);
                if ($clinica) {
                    $clinicasNombres[] = $clinica->nombre;
                }
            }

            if (!empty($clinicasNombres)) {
                $clinicasStr = implode(', ', $clinicasNombres);
                $title .= " - Clínicas: " . (count($clinicasNombres) > 3 ?
                    count($clinicasNombres) . ' clínicas seleccionadas' : $clinicasStr);
            }
        }

        // 2. Crear y configurar el modelo de búsqueda
        $searchModel = new PagosReporteSearch();

        // 3. Obtener el resumen general
        $summary = $searchModel->obtenerResumenGeneral($startDate, $endDate, $status, $clinicasArray);

        // 4. Obtener el resumen por clínica
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

        // 5. Crear y configurar el dataProvider para el GridView
        $params = $request->get();

        // Usar searchConClinicas si hay filtro de clínicas específicas, de lo contrario usar search normal
        if (!empty($clinicasArray) && !in_array('todas', $clinicasArray)) {
            $dataProvider = $searchModel->searchConClinicas($params, $startDate, $endDate, $status, $clinicasArray);
        } else {
            $dataProvider = $searchModel->search($params, $startDate, $endDate, $status, $clinicasArray);
        }

        // In actionGeneratePdf(), replace the content rendering part:
        $content = $this->renderPartial('_pagos-pdf', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title' => $title,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $summary,
            'summaryPorClinica' => $summaryPorClinica,
            'clinicasSeleccionadas' => $clinicasArray,
        ]);

        // Simplify the PDF configuration
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            'destination' => Pdf::DEST_DOWNLOAD,
            'content' => $content,
            'options' => [
                'title' => $title,
            ],
            'methods' => [
                'SetHeader' => [$title . '||Generado: ' . date('d/m/Y H:i:s')],
                'SetFooter' => ['|Página {PAGENO}|'],
            ]
        ]);

        return $pdf->render();
    }
    /**
     * Exporta el reporte a Excel usando PHPSpreadsheet
     */
    public function actionExportExcel($range = 'day', $specific_date = null, $status = 'Por Conciliar')
    {
        $request = Yii::$app->request;

        // Obtener parámetros
        $status = $request->get('status', 'Por Conciliar');
        $customRange = $request->get('custom_range', false);
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Procesar clínicas
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

        if (!empty($clinicasArray) && !in_array('todas', $clinicasArray)) {
            $dataProvider = $searchModel->searchConClinicas($params, $startDate, $endDate, $status, $clinicasArray);
        } else {
            $dataProvider = $searchModel->search($params, $startDate, $endDate, $status, $clinicasArray);
        }

        // Desactivar paginación para obtener todos los datos
        $dataProvider->pagination = false;
        $models = $dataProvider->getModels();

        // Crear título del archivo
        $statusLabel = $status === 'todos' ? 'Todos los Estados' : ($status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar');

        $fileName = 'Reporte_Pagos_' . $startDate . '_al_' . $endDate . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $statusLabel) . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Limpiar buffer de salida
        ob_clean();
        ob_start();

        // Crear nuevo Spreadsheet
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

                // ID Pago
                $sheet->setCellValueByColumnAndRow($col++, $dataRow, $model->id);

                // Nombres
                $nombres = $model->userDatos ? $model->userDatos->nombres : 'N/A';
                $sheet->setCellValueByColumnAndRow($col++, $dataRow, $nombres);

                // Apellidos
                $apellidos = $model->userDatos ? $model->userDatos->apellidos : 'N/A';
                $sheet->setCellValueByColumnAndRow($col++, $dataRow, $apellidos);

                // Cédula
                $cedula = $model->userDatos ? $model->userDatos->cedula : 'N/A';
                $sheet->setCellValueByColumnAndRow($col++, $dataRow, $cedula);

                // Monto
                $monto = $model->monto_usd ?: 0;
                $sheet->setCellValueByColumnAndRow($col++, $dataRow, $monto);
                $totalMonto += $monto;

                // Fecha Pago
                $fecha = $model->fecha_pago ? date('d/m/Y', strtotime($model->fecha_pago)) : 'N/A';
                $sheet->setCellValueByColumnAndRow($col++, $dataRow, $fecha);

                // Método de Pago
                $sheet->setCellValueByColumnAndRow($col++, $dataRow, $model->metodo_pago ?: 'N/A');

                // Estado
                $estado = $model->estatus === 'Conciliado' ? 'Conciliado' : 'Por Conciliar';
                $sheet->setCellValueByColumnAndRow($col++, $dataRow, $estado);

                // Clínica
                $clinica = 'Sin Clínica';
                if ($model->contratos && count($model->contratos) > 0) {
                    foreach ($model->contratos as $contrato) {
                        if ($contrato->clinica) {
                            $clinica = $contrato->clinica->nombre;
                            break;
                        }
                    }
                }
                $sheet->setCellValueByColumnAndRow($col++, $dataRow, $clinica);

                $dataRow++;
            }

            // Totales
            $totalRow = $dataRow + 1;

            $sheet->setCellValue('A' . $totalRow, 'TOTAL GENERAL:');
            $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
            $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue('E' . $totalRow, $totalMonto);
            $sheet->getStyle('E' . $totalRow)->getFont()->setBold(true);
            $sheet->getStyle('E' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->setCellValue('F' . $totalRow, count($models) . ' pagos');
            $sheet->mergeCells('F' . $totalRow . ':I' . $totalRow);
            $sheet->getStyle('F' . $totalRow)->getFont()->setBold(true);
            $sheet->getStyle('F' . $totalRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Estilo para totales
            $totalStyle = $sheet->getStyle('A' . $totalRow . ':I' . $totalRow);
            $totalStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $totalStyle->getFill()->getStartColor()->setARGB('FFE6F3FF');
            $totalStyle->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);
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
        if (!empty($models)) {
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('Resumen por Clínica');

            // Obtener resumen por clínica
            $searchModel = new PagosReporteSearch();
            $clinicasParaResumen = in_array('todas', $clinicasArray) ? [] : $clinicasArray;
            $resumenClinicas = $searchModel->obtenerResumenPorClinica($startDate, $endDate, $status, $clinicasParaResumen);

            // Encabezado
            $sheet2->setCellValue('A1', 'RESUMEN DE PAGOS POR CLÍNICA');
            $sheet2->mergeCells('A1:F1');
            $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet2->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Encabezados de resumen
            $resumenHeaders = ['Clínica', 'RIF', 'Total Pagos', 'Conciliados', 'Pendientes', 'Total (Bs.)'];
            $col = 1;
            foreach ($resumenHeaders as $header) {
                $sheet2->setCellValueByColumnAndRow($col++, 3, $header);
            }

            // Estilo encabezados resumen
            $sheet2->getStyle('A3:F3')->getFont()->setBold(true);
            $sheet2->getStyle('A3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet2->getStyle('A3:F3')->getFill()->getStartColor()->setARGB('FFCCCCCC');
            $sheet2->getStyle('A3:F3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Datos de resumen
            $resumenRow = 4;
            $granTotal = 0;
            $granTotalPagos = 0;

            foreach ($resumenClinicas as $resumen) {
                $col = 1;
                $sheet2->setCellValueByColumnAndRow($col++, $resumenRow, $resumen['clinica_nombre'] ?? 'N/A');
                $sheet2->setCellValueByColumnAndRow($col++, $resumenRow, $resumen['clinica_rif'] ?? 'N/A');
                $sheet2->setCellValueByColumnAndRow($col++, $resumenRow, $resumen['total_pagos'] ?? 0);
                $sheet2->setCellValueByColumnAndRow($col++, $resumenRow, $resumen['conciliados'] ?? 0);
                $sheet2->setCellValueByColumnAndRow($col++, $resumenRow, $resumen['pendientes'] ?? 0);

                $totalClinica = $resumen['total_monto'] ?? 0;
                $sheet2->setCellValueByColumnAndRow($col++, $resumenRow, $totalClinica);

                $granTotal += $totalClinica;
                $granTotalPagos += ($resumen['total_pagos'] ?? 0);
                $resumenRow++;
            }

            // Totales del resumen
            $sheet2->setCellValue('A' . ($resumenRow + 1), 'TOTAL GENERAL');
            $sheet2->mergeCells('A' . ($resumenRow + 1) . ':B' . ($resumenRow + 1));
            $sheet2->getStyle('A' . ($resumenRow + 1))->getFont()->setBold(true);
            $sheet2->getStyle('A' . ($resumenRow + 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $sheet2->setCellValue('C' . ($resumenRow + 1), $granTotalPagos);
            $sheet2->setCellValue('F' . ($resumenRow + 1), $granTotal);

            // Estilo para totales del resumen
            $totalResumenStyle = $sheet2->getStyle('A' . ($resumenRow + 1) . ':F' . ($resumenRow + 1));
            $totalResumenStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $totalResumenStyle->getFill()->getStartColor()->setARGB('FFE6F3FF');
            $totalResumenStyle->getFont()->setBold(true);

            // Formato de moneda para totales
            $sheet2->getStyle('F4:F' . ($resumenRow + 1))
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');

            // Autoajustar columnas
            for ($i = 1; $i <= 6; $i++) {
                $sheet2->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            }

            // Bordes para datos del resumen
            if (!empty($resumenClinicas)) {
                $resumenDataStyle = $sheet2->getStyle('A3:F' . ($resumenRow - 1));
                $resumenDataStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
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
