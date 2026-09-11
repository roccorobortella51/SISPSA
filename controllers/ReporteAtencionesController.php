<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use app\models\SisSiniestroReporteSearch;
use app\models\RmClinica;
use app\models\Baremo;
use app\components\UserHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\SisSiniestroUsoSearch;

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
        $this->layout = 'main';

        $searchModel = new SisSiniestroReporteSearch();
        $searchModel->load(Yii::$app->request->get());

        $clinicas = RmClinica::find()
            ->where(['estatus' => 'Activo'])
            ->andWhere(['IS', 'deleted_at', null])
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
            $searchModel->type = Yii::$app->request->post('type', 'all');

            // Apply clinic access restrictions
            $clinicasParam = Yii::$app->request->post('clinicas', []);
            if (UserHelper::hasClinicAccess()) {
                // Force to user's assigned clinics
                $accessibleClinicas = UserHelper::getAccessibleClinicas();
                $searchModel->clinicas = array_column($accessibleClinicas, 'id');
            } else {
                $searchModel->clinicas = $clinicasParam;
            }

            $reportData = $searchModel->generateReport();

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

            // Verify user has access to this clinic
            if (UserHelper::hasClinicAccess()) {
                $accessibleClinicaIds = UserHelper::getAccessibleClinicaIds();
                if ($accessibleClinicaIds !== null && !in_array($clinicId, $accessibleClinicaIds)) {
                    return [
                        'success' => false,
                        'message' => 'No tiene acceso a esta clínica',
                    ];
                }
            }

            $searchModel = new SisSiniestroReporteSearch();
            $searchModel->range = Yii::$app->request->get('range', 'day');
            $searchModel->date_from = Yii::$app->request->get('date_from');
            $searchModel->date_to = Yii::$app->request->get('date_to');
            $searchModel->type = Yii::$app->request->get('type', 'all');

            // Apply clinic access restrictions
            if (UserHelper::hasClinicAccess()) {
                $accessibleClinicas = UserHelper::getAccessibleClinicas();
                $searchModel->clinicas = array_column($accessibleClinicas, 'id');
            } else {
                $clinicasParam = explode(',', Yii::$app->request->get('clinicas', 'todas'));
                $searchModel->clinicas = $clinicasParam;
            }

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
     * Export report to Excel with detailed clinic information
     */
    public function actionExportExcel()
    {
        $searchModel = new SisSiniestroReporteSearch();
        $searchModel->load(Yii::$app->request->get());

        // Set date range from request
        $searchModel->date_from = Yii::$app->request->get('date_from');
        $searchModel->date_to = Yii::$app->request->get('date_to');
        $searchModel->range = Yii::$app->request->get('range', 'day');
        $searchModel->type = Yii::$app->request->get('type', 'all');

        // Apply clinic access restrictions for export
        $clinicasParam = Yii::$app->request->get('clinicas', 'todas');
        if (UserHelper::hasClinicAccess()) {
            // Force to user's assigned clinics
            $accessibleClinicas = UserHelper::getAccessibleClinicas();
            $searchModel->clinicas = array_column($accessibleClinicas, 'id');
        } else {
            $searchModel->clinicas = $clinicasParam === 'todas' ? [] : explode(',', $clinicasParam);
        }

        $reportData = $searchModel->generateReport();

        // Generate Excel file
        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Set document properties
        $objPHPExcel->getProperties()
            ->setCreator(Yii::$app->user->identity->username ?? 'System')
            ->setTitle('Reporte de Atenciones por Clínica')
            ->setSubject('Reporte de Atenciones Médicas')
            ->setDescription('Reporte generado desde el sistema de gestión de atenciones médicas');

        // ============================================================
        // SHEET 1: SUMMARY REPORT
        // ============================================================
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Resumen');

        // ===== TITLE SECTION =====
        $sheet->setCellValue('A1', 'Reporte de Atenciones por Clínica');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // ===== DATE RANGE =====
        $sheet->setCellValue('A2', 'Período: ' . Yii::$app->formatter->asDate($reportData['summary']['date_range']['from']) .
            ' al ' . Yii::$app->formatter->asDate($reportData['summary']['date_range']['to']));
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // ===== TYPE FILTER =====
        $typeLabels = [
            'all' => 'Todas las Atenciones',
            'citas' => 'Citas Médicas',
            'siniestros' => 'Emergencias / Siniestros'
        ];
        $currentType = $searchModel->type ?? 'all';
        $typeLabel = $typeLabels[$currentType] ?? 'Todas las Atenciones';
        $sheet->setCellValue('A3', 'Tipo de Atención: ' . $typeLabel);
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFont()->getColor()->setRGB('0078D4');

        // ===== EXPORT INFO =====
        $sheet->setCellValue('A4', 'Exportado: ' . date('Y-m-d H:i:s'));
        $sheet->mergeCells('A4:H4');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // ===== CLINIC ACCESS INDICATOR =====
        if (UserHelper::hasClinicAccess()) {
            $sheet->setCellValue('A5', '🔒 Acceso Restringido a Clínicas Asignadas');
            $sheet->mergeCells('A5:H5');
            $sheet->getStyle('A5')->getFont()->getColor()->setRGB('107C10');
            $sheet->getStyle('A5')->getFont()->setBold(true);
            $sheet->getStyle('A5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // ===== COLUMN HEADERS =====
        $headers = [
            'A7' => 'Clínica',
            'B7' => 'Estado',
            'C7' => 'Total Atenciones',
            'D7' => 'Pacientes Únicos',
            'E7' => 'Monto Total',
            'F7' => 'Citas',
            'G7' => 'Emergencias',
            'H7' => 'Tasa Atención',
        ];

        // Style for headers with Microsoft theme
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0078D4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }

        // ===== DATA ROWS =====
        $row = 8;
        $totalAttentions = 0;
        $totalCost = 0;
        $totalPatients = 0;

        foreach ($reportData['data'] as $item) {
            // Determine row color based on performance
            $rowColor = 'FFFFFF';
            if ($item['attendance_rate'] >= 90) {
                $rowColor = 'E6F3E6';
            } elseif ($item['attendance_rate'] >= 75) {
                $rowColor = 'FFF9E6';
            } elseif ($item['attendance_rate'] < 60) {
                $rowColor = 'FDE8E8';
            }

            $sheet->setCellValue('A' . $row, $item['clinic_name']);
            $sheet->setCellValue('B' . $row, $item['clinic_status']);
            $sheet->setCellValue('C' . $row, $item['total_attentions']);
            $sheet->setCellValue('D' . $row, $item['unique_patients']);
            $sheet->setCellValue('E' . $row, number_format($item['total_cost'], 2));
            $sheet->setCellValue('F' . $row, $item['appointments_count']);
            $sheet->setCellValue('G' . $row, $item['emergencies_count']);
            $sheet->setCellValue('H' . $row, $item['attendance_rate'] . '%');

            // Apply styling to row
            $rowRange = 'A' . $row . ':H' . $row;
            $sheet->getStyle($rowRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $rowColor],
                ],
            ]);

            $totalAttentions += $item['total_attentions'];
            $totalCost += $item['total_cost'];
            $totalPatients += $item['unique_patients'];
            $row++;
        }

        // ===== TOTALS ROW =====
        $row++;
        $sheet->setCellValue('A' . $row, 'TOTALES');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE6F2FF');

        $sheet->setCellValue('C' . $row, $totalAttentions);
        $sheet->setCellValue('D' . $row, $totalPatients);
        $sheet->setCellValue('E' . $row, number_format($totalCost, 2));

        // ===== SUMMARY SECTION =====
        $summaryRow = $row + 3;
        $sheet->setCellValue('A' . $summaryRow, 'RESUMEN EJECUTIVO');
        $sheet->mergeCells('A' . $summaryRow . ':H' . $summaryRow);
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $summaryRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF0078D4');
        $sheet->getStyle('A' . $summaryRow)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

        $summaryData = [
            ['Total Clínicas Activas', count($reportData['data'])],
            ['Total Atenciones', $totalAttentions],
            ['Total Pacientes Únicos', $totalPatients],
            ['Monto Total', '$' . number_format($totalCost, 2)],
            ['Monto Promedio por Atención', '$' . number_format($totalAttentions > 0 ? $totalCost / $totalAttentions : 0, 2)],
            ['Tipo de Atención', $typeLabel],
        ];

        $summaryRow++;
        foreach ($summaryData as $idx => $data) {
            $currentRow = $summaryRow + $idx;
            $sheet->setCellValue('A' . $currentRow, $data[0]);
            $sheet->setCellValue('B' . $currentRow, $data[1]);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        }

        // Auto size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // ============================================================
        // SHEET 2: DETAILED CLINIC REPORT (SAME AS PANEL DETAILS)
        // ============================================================
        $detailSheet = $objPHPExcel->createSheet();
        $detailSheet->setTitle('Detalle por Clínica');
        $detailRow = 1;

        // Process each clinic with detailed information
        foreach ($reportData['data'] as $clinicItem) {
            $clinicId = $clinicItem['id'];

            // Get detailed clinic data
            $detailData = $searchModel->generateClinicDetailReport($clinicId);
            if (!$detailData) {
                continue;
            }

            // ===== CLINIC HEADER =====
            $detailSheet->setCellValue('A' . $detailRow, 'CLÍNICA: ' . $detailData['clinic']->nombre);
            $detailSheet->mergeCells('A' . $detailRow . ':K' . $detailRow);
            $detailSheet->getStyle('A' . $detailRow)->getFont()->setBold(true)->setSize(14);
            $detailSheet->getStyle('A' . $detailRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF0078D4');
            $detailSheet->getStyle('A' . $detailRow)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
            $detailRow++;

            // ===== CLINIC INFO =====
            $detailSheet->setCellValue('A' . $detailRow, 'Estado:');
            $detailSheet->setCellValue('B' . $detailRow, $detailData['clinic']->estatus);
            $detailRow++;

            if (!empty($detailData['clinic']->direccion)) {
                $detailSheet->setCellValue('A' . $detailRow, 'Dirección:');
                $detailSheet->setCellValue('B' . $detailRow, $detailData['clinic']->direccion);
                $detailRow++;
            }

            if (!empty($detailData['clinic']->telefono)) {
                $detailSheet->setCellValue('A' . $detailRow, 'Teléfono:');
                $detailSheet->setCellValue('B' . $detailRow, $detailData['clinic']->telefono);
                $detailRow++;
            }

            $detailSheet->setCellValue('A' . $detailRow, 'Período:');
            $detailSheet->setCellValue(
                'B' . $detailRow,
                Yii::$app->formatter->asDate($detailData['date_range']['from']) .
                    ' al ' .
                    Yii::$app->formatter->asDate($detailData['date_range']['to'])
            );
            $detailRow++;

            // ===== CLINIC SUMMARY STATS =====
            $detailRow++;
            $detailSheet->setCellValue('A' . $detailRow, 'RESUMEN DE ATENCIONES');
            $detailSheet->mergeCells('A' . $detailRow . ':K' . $detailRow);
            $detailSheet->getStyle('A' . $detailRow)->getFont()->setBold(true)->setSize(12);
            $detailSheet->getStyle('A' . $detailRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE6F2FF');
            $detailRow++;

            // Calculate stats from attentions
            $totalAttentions = count($detailData['attentions']);
            $totalCost = 0;
            $uniquePatients = [];
            $appointmentsCount = 0;
            $emergenciesCount = 0;

            foreach ($detailData['attentions'] as $attention) {
                $totalCost += (float)$attention->costo_total;
                if ($attention->iduser) {
                    $uniquePatients[$attention->iduser] = true;
                }
                if ($attention->es_cita) {
                    $appointmentsCount++;
                } else {
                    $emergenciesCount++;
                }
            }

            $stats = [
                ['Total Atenciones', $totalAttentions],
                ['Pacientes Únicos', count($uniquePatients)],
                ['Monto Total', '$' . number_format($totalCost, 2)],
                ['Monto Promedio', '$' . number_format($totalAttentions > 0 ? $totalCost / $totalAttentions : 0, 2)],
                ['Citas', $appointmentsCount],
                ['Emergencias', $emergenciesCount],
            ];

            foreach ($stats as $stat) {
                $detailSheet->setCellValue('A' . $detailRow, $stat[0]);
                $detailSheet->setCellValue('B' . $detailRow, $stat[1]);
                $detailSheet->getStyle('A' . $detailRow)->getFont()->setBold(true);
                $detailRow++;
            }

            // ===== MOST COMMON SERVICES =====
            if (!empty($detailData['common_baremos'])) {
                $detailRow += 2;
                $detailSheet->setCellValue('A' . $detailRow, 'SERVICIOS MÁS UTILIZADOS');
                $detailSheet->mergeCells('A' . $detailRow . ':K' . $detailRow);
                $detailSheet->getStyle('A' . $detailRow)->getFont()->setBold(true)->setSize(12);
                $detailSheet->getStyle('A' . $detailRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF9E6');
                $detailRow++;

                // Headers - Added "Descripción" column
                $serviceHeaders = ['Servicio', 'Descripción', 'Veces Usado', 'Precio Promedio', 'Monto Total'];
                $col = 'A';
                foreach ($serviceHeaders as $header) {
                    $detailSheet->setCellValue($col . $detailRow, $header);
                    $detailSheet->getStyle($col . $detailRow)->getFont()->setBold(true);
                    $col++;
                }
                $detailRow++;

                foreach ($detailData['common_baremos'] as $service) {
                    // Get the full baremo record to get description
                    $baremo = Baremo::findOne($service['baremo_id'] ?? null);
                    $descripcion = $baremo ? $baremo->descripcion : '';

                    $detailSheet->setCellValue('A' . $detailRow, $service['service_name'] ?? 'N/A');
                    $detailSheet->setCellValue('B' . $detailRow, $descripcion);
                    $detailSheet->setCellValue('C' . $detailRow, $service['usage_count'] ?? 0);
                    $detailSheet->setCellValue('D' . $detailRow, '$' . number_format($service['avg_price'] ?? 0, 2));
                    $detailSheet->setCellValue('E' . $detailRow, '$' . number_format($service['total_cost'] ?? 0, 2));
                    $detailRow++;
                }
            }

            // ===== DAILY STATISTICS =====
            if (!empty($detailData['daily_stats'])) {
                $detailRow += 2;
                $detailSheet->setCellValue('A' . $detailRow, 'ESTADÍSTICAS DIARIAS');
                $detailSheet->mergeCells('A' . $detailRow . ':K' . $detailRow);
                $detailSheet->getStyle('A' . $detailRow)->getFont()->setBold(true)->setSize(12);
                $detailSheet->getStyle('A' . $detailRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE6F3E6');
                $detailRow++;

                // Headers
                $dailyHeaders = ['Fecha', 'Atenciones', 'Monto Diario', 'Pacientes'];
                $col = 'A';
                foreach ($dailyHeaders as $header) {
                    $detailSheet->setCellValue($col . $detailRow, $header);
                    $detailSheet->getStyle($col . $detailRow)->getFont()->setBold(true);
                    $col++;
                }
                $detailRow++;

                foreach ($detailData['daily_stats'] as $daily) {
                    $detailSheet->setCellValue('A' . $detailRow, Yii::$app->formatter->asDate($daily['date']));
                    $detailSheet->setCellValue('B' . $detailRow, $daily['attentions_count'] ?? 0);
                    $detailSheet->setCellValue('C' . $detailRow, '$' . number_format($daily['daily_cost'] ?? 0, 2));
                    $detailSheet->setCellValue('D' . $detailRow, $daily['daily_patients'] ?? 0);
                    $detailRow++;
                }
            }

            // ===== RECENT ATTENTIONS =====
            if (!empty($detailData['attentions'])) {
                $detailRow += 2;
                $detailSheet->setCellValue('A' . $detailRow, 'ATENCIONES RECIENTES');
                $detailSheet->mergeCells('A' . $detailRow . ':K' . $detailRow);
                $detailSheet->getStyle('A' . $detailRow)->getFont()->setBold(true)->setSize(12);
                $detailSheet->getStyle('A' . $detailRow)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFDE8E8');
                $detailRow++;

                // Headers
                $attentionHeaders = ['Fecha', 'Hora', 'Paciente', 'Cédula', 'Servicios', 'Descripción', 'Monto', 'Tipo'];
                $col = 'A';
                foreach ($attentionHeaders as $header) {
                    $detailSheet->setCellValue($col . $detailRow, $header);
                    $detailSheet->getStyle($col . $detailRow)->getFont()->setBold(true);
                    $col++;
                }
                $detailRow++;

                $displayedAttentions = array_slice($detailData['attentions'], 0, 50);
                foreach ($displayedAttentions as $attention) {
                    // Get patient name
                    $patientName = 'N/A';
                    $patientCedula = 'N/A';
                    if ($attention->afiliado) {
                        $patientName = trim($attention->afiliado->nombres . ' ' . $attention->afiliado->apellidos);
                        $patientCedula = $attention->afiliado->cedula ?? 'N/A';
                    }

                    // Get service names and descriptions
                    $serviceNames = [];
                    $serviceDescriptions = [];
                    foreach ($attention->baremos as $baremo) {
                        $serviceNames[] = $baremo->nombre_servicio;
                        $serviceDescriptions[] = $baremo->descripcion;
                    }
                    $serviceList = !empty($serviceNames) ? implode(', ', $serviceNames) : 'N/A';
                    $descriptionList = !empty($serviceDescriptions) ? implode(', ', $serviceDescriptions) : 'N/A';

                    $detailSheet->setCellValue('A' . $detailRow, Yii::$app->formatter->asDate($attention->fecha));
                    $detailSheet->setCellValue('B' . $detailRow, $attention->hora);
                    $detailSheet->setCellValue('C' . $detailRow, $patientName);
                    $detailSheet->setCellValue('D' . $detailRow, $patientCedula);
                    $detailSheet->setCellValue('E' . $detailRow, $serviceList);
                    $detailSheet->setCellValue('F' . $detailRow, $descriptionList);
                    $detailSheet->setCellValue('G' . $detailRow, '$' . number_format($attention->costo_total ?? 0, 2));
                    $detailSheet->setCellValue('H' . $detailRow, $attention->es_cita ? 'Cita' : 'Emergencia');
                    $detailRow++;
                }
            }

            // ===== SPACER BETWEEN CLINICS =====
            $detailRow += 3;
        }

        // Auto size columns on detail sheet
        foreach (range('A', 'K') as $column) {
            $detailSheet->getColumnDimension($column)->setAutoSize(true);
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
     * Export report to PDF with detailed clinic information
     */
    public function actionExportPdf()
    {
        $searchModel = new SisSiniestroReporteSearch();
        $searchModel->load(Yii::$app->request->get());

        // Set date range from request
        $searchModel->date_from = Yii::$app->request->get('date_from');
        $searchModel->date_to = Yii::$app->request->get('date_to');
        $searchModel->range = Yii::$app->request->get('range', 'day');
        $searchModel->type = Yii::$app->request->get('type', 'all');

        // Apply clinic access restrictions for export
        $clinicasParam = Yii::$app->request->get('clinicas', 'todas');
        if (UserHelper::hasClinicAccess()) {
            $accessibleClinicas = UserHelper::getAccessibleClinicas();
            $searchModel->clinicas = array_column($accessibleClinicas, 'id');
        } else {
            $searchModel->clinicas = $clinicasParam === 'todas' ? [] : explode(',', $clinicasParam);
        }

        $reportData = $searchModel->generateReport();

        // Get detailed data for each clinic
        $detailedClinics = [];
        foreach ($reportData['data'] as $clinicItem) {
            $detailData = $searchModel->generateClinicDetailReport($clinicItem['id']);
            if ($detailData) {
                $detailedClinics[] = $detailData;
            }
        }

        // Get the rendered HTML content with details
        $content = $this->renderPartial('_pdf_report', [
            'reportData' => $reportData,
            'searchModel' => $searchModel,
            'userRole' => UserHelper::getMyRol(),
            'hasClinicAccess' => UserHelper::hasClinicAccess(),
            'accessibleClinicas' => UserHelper::hasClinicAccess() ? UserHelper::getAccessibleClinicas() : null,
            'detailedClinics' => $detailedClinics,
        ]);

        // ============================================================
        // GENERATE PDF USING mPDF DIRECTLY
        // ============================================================
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'default_font_size' => 11,
            'default_font' => 'dejavusans',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 20,
        ]);

        $mpdf->SetTitle('Reporte de Atenciones por Clínica');
        $mpdf->SetSubject('Reporte de Atenciones Médicas');
        $mpdf->SetAuthor(Yii::$app->user->identity->username ?? 'System');

        // Write HTML content to PDF
        $mpdf->WriteHTML($content);

        // Output PDF to browser (I = inline view, D = download)
        $mpdf->Output('Reporte_Atenciones_' . date('Y-m-d') . '.pdf', 'I');
        exit;
    }

    /**
     * Service Usage Report - Shows which clients have used services
     * 
     * @return string
     */
    public function actionUsoServicios()
    {
        $this->layout = 'main';

        $searchModel = new SisSiniestroUsoSearch();
        $searchModel->load(Yii::$app->request->get());

        // Set default date range (last month)
        if (empty($searchModel->date_from)) {
            $searchModel->date_from = date('Y-m-d', strtotime('-1 month'));
        }
        if (empty($searchModel->date_to)) {
            $searchModel->date_to = date('Y-m-d');
        }

        // Get report data
        $reportData = $searchModel->generateReport();

        // Get additional data for charts
        $timelineData = $searchModel->getUsageTimeline($searchModel->date_from, $searchModel->date_to);
        $categoryData = $searchModel->getUsageByCategory($searchModel->date_from, $searchModel->date_to);
        $inactiveUsers = $searchModel->getInactiveUsers($searchModel->date_from, $searchModel->date_to);

        // Get clinics for filter
        $clinicas = RmClinica::find()
            ->where(['estatus' => 'Activo'])
            ->andWhere(['IS', 'deleted_at', null])
            ->orderBy('nombre')
            ->all();

        // Get baremos for filter
        $baremos = Baremo::find()
            ->where(['estatus' => 'Activo'])
            ->orderBy('nombre_servicio')
            ->all();

        return $this->render('uso-servicios', [
            'searchModel' => $searchModel,
            'reportData' => $reportData,
            'timelineData' => $timelineData,
            'categoryData' => $categoryData,
            'inactiveUsers' => $inactiveUsers,
            'clinicas' => $clinicas,
            'baremos' => $baremos,
        ]);
    }

    /**
     * AJAX endpoint to generate service usage report
     */
    public function actionGenerateUsoServicios()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $searchModel = new SisSiniestroUsoSearch();
            $searchModel->date_from = Yii::$app->request->post('date_from');
            $searchModel->date_to = Yii::$app->request->post('date_to');
            $searchModel->clinica_id = Yii::$app->request->post('clinica_id');
            $searchModel->tipo_atencion = Yii::$app->request->post('tipo_atencion', 'all');
            $searchModel->scope = Yii::$app->request->post('scope', 'period');
            $searchModel->baremo_id = Yii::$app->request->post('baremo_id');

            $reportData = $searchModel->generateReport();

            // Get additional data
            $dateRange = $searchModel->getDateRange();
            $isCartera = ($searchModel->scope === 'cartera');

            $timelineData = $searchModel->getUsageTimeline($dateRange, $isCartera);
            $categoryData = $searchModel->getUsageByCategory($dateRange, $isCartera);
            $inactiveUsers = $searchModel->getInactiveUsers($dateRange, $isCartera);

            $html = $this->renderAjax('_uso_servicios_results', [
                'reportData' => $reportData,
                'timelineData' => $timelineData,
                'categoryData' => $categoryData,
                'inactiveUsers' => $inactiveUsers,
                'searchModel' => $searchModel,
                'isCartera' => $isCartera,
            ]);

            return [
                'success' => true,
                'html' => $html,
                'summary' => $reportData['summary'],
            ];
        } catch (\Exception $e) {
            Yii::error('Error generating usage report: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * AJAX endpoint to get emergency details for a clinic
     */
    public function actionEmergencyDetail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            Yii::info('EmergencyDetail request: ' . print_r(Yii::$app->request->get(), true), __METHOD__);

            $clinicId = Yii::$app->request->get('clinic_id');
            $dateFrom = Yii::$app->request->get('date_from');
            $dateTo = Yii::$app->request->get('date_to');
            $scope = Yii::$app->request->get('scope', 'cartera');

            if (!$clinicId) {
                Yii::error('No clinic ID provided', __METHOD__);
                return ['success' => false, 'message' => 'ID de clínica no proporcionado'];
            }

            $searchModel = new SisSiniestroUsoSearch();
            $searchModel->date_from = $dateFrom;
            $searchModel->date_to = $dateTo;
            $searchModel->scope = $scope;

            $dateRange = $searchModel->getDateRange();
            $isCartera = ($scope === 'cartera');

            Yii::info('Calling getEmergencyDetailsByClinic with clinicId: ' . $clinicId, __METHOD__);

            $emergencyData = $searchModel->getEmergencyDetailsByClinic($clinicId, $dateRange, $isCartera);

            Yii::info('EmergencyData total: ' . ($emergencyData['totals']['total'] ?? 0), __METHOD__);

            // Get clinic name
            $clinic = RmClinica::findOne($clinicId);

            $html = $this->renderAjax('_emergency_detail_panel', [
                'emergencyData' => $emergencyData,
                'clinic' => $clinic,
                'dateRange' => $dateRange,
                'isCartera' => $isCartera,
            ]);

            return [
                'success' => true,
                'html' => $html,
                'clinic_name' => $clinic ? $clinic->nombre : 'Clínica',
            ];
        } catch (\Exception $e) {
            Yii::error('Error in actionEmergencyDetail: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage(),
            ];
        }
    }
}
