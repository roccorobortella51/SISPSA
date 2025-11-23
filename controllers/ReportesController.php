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
                        // Permitir a ambas acciones (index y ajax/pdf)
                        'actions' => ['index', 'get-pagos-detail', 'generate-pdf'], 
                        // Acceso solo para 'superadmin'
                        'roles' => ['superadmin'], 
                    ],
                ],
            ],
            // === VERBFILTER: Se añade 'generate-pdf' con método GET ===
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    // La acción de AJAX DEBE aceptar POST
                    'get-pagos-detail' => ['POST'],
                    // El index solo necesita GET
                    'index' => ['GET'],
                    // La acción de PDF DEBE aceptar GET para que funcione el target='_blank'
                    'generate-pdf' => ['GET'], 
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
        $range = $request->post('range', 'day'); // 'day', 'week', 'month'
        $specificDate = $request->post('specific_date');
        // NUEVO: Obtener el estado del pago, por defecto 'Por Conciliar'
        $status = $request->post('status', 'Por Conciliar'); 
        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
        $title = "Detalle de Pagos de Hoy";

        // 1. Lógica de rango de fechas (sin cambios)
        switch ($range) {
            case 'week':
                $startDate = date('Y-m-d', strtotime('last Monday'));
                $title = "Detalle de Pagos Semanales";
                break;
            case 'month':
                $startDate = date('Y-m-01');
                $title = "Detalle de Pagos Mensuales";
                break;
        }

        if ($specificDate && $specificDate !== 'Invalid date') {
            $startDate = $specificDate;
            $endDate = $specificDate;
            $title = "Detalle de Pagos para el día: " . Yii::$app->formatter->asDate($specificDate, 'long');
        }
        
        // NUEVO: Modificar título para reflejar el estado seleccionado
        $statusLabel = $status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar';
        $title .= " ({$statusLabel})";


        // 2. Obtener el resumen (para el panel superior de totales)
        // CAMBIO: Pasar el parámetro $status
        $summary = Pagos::getPaymentsSummaryForDateRange($startDate, $endDate, $status);
        
        // 3. Crear y configurar el modelo de búsqueda para el GridView
        $searchModel = new PagosReporteSearch();
        $params = $request->post();
        // CAMBIO: Pasar el parámetro $status
        $dataProvider = $searchModel->search($params, $startDate, $endDate, $status);

        // Devolver el HTML de la vista parcial (GridView)
        return [
            'success' => true,
            // Pasar el resumen al renderPartial para mostrar los totales
            'html' => $this->renderPartial('_pagos-grid', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'title' => $title,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'summary' => $summary, // Se pasa el resumen
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
    // MODIFICADO: Incluir $status en la firma de la función (aunque se sobrescribe con GET)
    public function actionGeneratePdf($range = 'day', $specific_date = null, $status = 'Por Conciliar') 
        {
            $request = Yii::$app->request;
            
            // NUEVO: Obtener el estado del pago del parámetro GET
            $status = $request->get('status', 'Por Conciliar');

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
            }

            if ($specificDate && $specificDate !== 'Invalid date') {
                $startDate = $specificDate;
                $endDate = $specificDate;
                $title = "Detalle de Pagos para el día: " . Yii::$app->formatter->asDate($specificDate, 'long');
            }
            
            // NUEVO: Modificar título para reflejar el estado seleccionado
            $statusLabel = $status === 'Conciliado' ? 'Conciliados' : 'Por Conciliar';
            $title .= " ({$statusLabel})";

            // 2. Obtener el resumen (para el panel superior de totales)
            // CAMBIO: Pasar el parámetro $status
            $summary = Pagos::getPaymentsSummaryForDateRange($startDate, $endDate, $status);
            
            // 3. Crear y configurar el modelo de búsqueda para el GridView
            $searchModel = new PagosReporteSearch();
            $params = $request->get();
            // CAMBIO: Pasar el parámetro $status
            $dataProvider = $searchModel->search($params, $startDate, $endDate, $status);

            // 4. Renderizar la vista parcial (_pagos-grid.php) como contenido HTML
            $content = $this->renderPartial('_pagos-grid', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'title' => $title,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'summary' => $summary,
            ]);

            // 5. Instanciar el componente PDF de Kartik
            $pdf = new Pdf([
                'mode' => Pdf::MODE_CORE, 
                'format' => Pdf::FORMAT_A4, 
                'orientation' => Pdf::ORIENT_LANDSCAPE, 
                'destination' => Pdf::DEST_DOWNLOAD, 
                'content' => $content,  
                // ... (rest of PDF configuration)
                'options' => ['title' => $title],
                'cssInline' => '
                    body { 
                        font-size: 16px !important; 
                        font-family: Arial, sans-serif !important;
                    }
                    .grid-view table { 
                        width: 100% !important;
                        font-size: 18px !important;
                    }
                    .grid-view table th { 
                        font-size: 20px !important; 
                        font-weight: bold !important;
                        background-color: #f8f9fa !important;
                        padding: 12px 8px !important;
                    }
                    .grid-view table td { 
                        font-size: 18px !important; 
                        padding: 10px 8px !important;
                        line-height: 1.4 !important;
                    }
                    /* Make summary cards larger */
                    .display-4 {
                        font-size: 2.5rem !important;
                    }
                    .card-body h5 {
                        font-size: 1.2rem !important;
                    }
                ',
                'methods' => [ 
                    'SetHeader'=>[$title . '||Generado el: ' . Yii::$app->formatter->asDate(time(), 'long')], 
                    'SetFooter'=>['|Página {PAGENO}|'],
                ]
            ]);
            
            return $pdf->render();
        }
    }