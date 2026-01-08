<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use app\models\SisSiniestroReporteSearch;
use app\models\RmClinica;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * ReporteAtencionesController handles medical attention reports by clinic
 */
class ReporteAtencionesController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Main report index page
     */
    public function actionIndex()
    {
        $this->layout = 'main'; // Or your preferred layout

        $searchModel = new SisSiniestroReporteSearch();
        $searchModel->load(Yii::$app->request->get());

        $clinicas = RmClinica::find()
            ->where(['estatus' => 'Activo'])
            ->orderBy('nombre')
            ->all();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'clinicas' => $clinicas,
        ]);
    }

    /**
     * AJAX endpoint to generate report
     */
    public function actionGenerateReport()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $searchModel = new SisSiniestroReporteSearch();
            $searchModel->range = Yii::$app->request->post('range', 'day');
            $searchModel->date_from = Yii::$app->request->post('date_from');
            $searchModel->date_to = Yii::$app->request->post('date_to');
            $searchModel->clinicas = Yii::$app->request->post('clinicas', []);

            $reportData = $searchModel->generateReport();

            // Use renderAjax instead of renderPartial
            $html = $this->renderAjax('_report_results', [
                'reportData' => $reportData,
                'searchModel' => $searchModel,
            ]);

            return [
                'success' => true,
                'html' => $html,
                'summary' => $reportData['summary'],
            ];
        } catch (\Exception $e) {
            Yii::error('Error generating report: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * AJAX endpoint to get clinic detail
     */
    public function actionClinicDetail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $clinicId = Yii::$app->request->get('id');
            if (!$clinicId) {
                return [
                    'success' => false,
                    'message' => 'ID de clínica no proporcionado',
                ];
            }

            $searchModel = new SisSiniestroReporteSearch();
            $searchModel->range = Yii::$app->request->get('range', 'day');
            $searchModel->date_from = Yii::$app->request->get('date_from');
            $searchModel->date_to = Yii::$app->request->get('date_to');
            $searchModel->clinicas = explode(',', Yii::$app->request->get('clinicas', 'todas'));

            $detailData = $searchModel->generateClinicDetailReport($clinicId);

            if (!$detailData) {
                return [
                    'success' => false,
                    'message' => 'Clínica no encontrada o sin datos',
                ];
            }

            $html = $this->renderAjax('_clinic_detail', [
                'detailData' => $detailData,
            ]);

            return [
                'success' => true,
                'html' => $html,
            ];
        } catch (\Exception $e) {
            Yii::error('Error getting clinic detail: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Error al obtener detalles de la clínica: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Export report to Excel
     */
    public function actionExportExcel()
    {
        $searchModel = new SisSiniestroReporteSearch();
        $searchModel->load(Yii::$app->request->get());

        $reportData = $searchModel->generateReport();

        // Generate Excel file
        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $objPHPExcel->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Reporte de Atenciones por Clínica');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        $sheet->setCellValue('A2', 'Período: ' . Yii::$app->formatter->asDate($reportData['summary']['date_range']['from']) .
            ' al ' . Yii::$app->formatter->asDate($reportData['summary']['date_range']['to']));
        $sheet->mergeCells('A2:H2');

        // Column headers
        $headers = [
            'A3' => 'Clínica',
            'B3' => 'Estado',
            'C3' => 'Total Atenciones',
            'D3' => 'Atendidas',
            'E3' => 'Pendientes',
            'F3' => 'Pacientes Únicos',
            'G3' => 'Tasa de Atención %',
            'H3' => 'Costo Total',
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Data rows
        $row = 4;
        foreach ($reportData['data'] as $item) {
            $sheet->setCellValue('A' . $row, $item['clinic_name']);
            $sheet->setCellValue('B' . $row, $item['clinic_status']);
            $sheet->setCellValue('C' . $row, $item['total_attentions']);
            $sheet->setCellValue('D' . $row, $item['attended_count']);
            $sheet->setCellValue('E' . $row, $item['pending_count']);
            $sheet->setCellValue('F' . $row, $item['unique_patients']);
            $sheet->setCellValue('G' . $row, $item['attendance_rate'] . '%');
            $sheet->setCellValue('H' . $row, $item['total_cost']);

            // Color code attendance rate
            if ($item['attendance_rate'] >= 90) {
                $sheet->getStyle('G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFC6EFCE');
            } elseif ($item['attendance_rate'] < 60) {
                $sheet->getStyle('G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFC7CE');
            }

            $row++;
        }

        // Summary row
        $row++;
        $sheet->setCellValue('A' . $row, 'TOTALES');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $row, $reportData['summary']['total_attentions']);
        $sheet->setCellValue('F' . $row, $reportData['summary']['total_patients']);
        $sheet->setCellValue('H' . $row, $reportData['summary']['total_cost']);

        // Auto size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Atenciones_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export report to PDF
     */
    public function actionExportPdf()
    {
        $searchModel = new SisSiniestroReporteSearch();
        $searchModel->load(Yii::$app->request->get());

        $reportData = $searchModel->generateReport();

        $pdf = Yii::$app->pdf;
        $pdf->content = $this->renderPartial('_pdf_report', [
            'reportData' => $reportData,
            'searchModel' => $searchModel,
        ]);

        return $pdf->render();
    }
}
