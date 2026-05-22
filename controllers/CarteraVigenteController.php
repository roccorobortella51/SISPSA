<?php
// app/controllers/CarteraVigenteController.php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\UserDatos;
use app\models\Contratos;
use app\models\Cuotas;
use app\components\UserHelper;

class CarteraVigenteController extends Controller
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
                        'actions' => ['index', 'export-excel'],
                        'roles' => ['superadmin', 'FINANZAS', 'COORDINADOR-CLINICA'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'export-excel' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * Cartera Vigente Report - SUDEASEG Circular SAA-09-0135-2026
     * 
     * @return string
     */
    public function actionIndex()
    {
        $months = $this->getMonthOptions();
        $years = $this->getYearOptions();
        $selectedMonth = date('m');
        $selectedYear = date('Y');

        return $this->render('index', [
            'months' => $months,
            'years' => $years,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
        ]);
    }

    /**
     * Export Cartera Vigente to Excel
     * 
     * @param string $month
     * @param string $year
     * @return \yii\web\Response
     */
    public function actionExportExcel($month = null, $year = null)
    {
        // Clear output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Use current month/year if not provided
        if (!$month) {
            $month = date('m');
        }
        if (!$year) {
            $year = date('Y');
        }

        // Get the cut-off date (last day of selected month)
        $cutoffDate = date('Y-m-t', strtotime("{$year}-{$month}-01"));

        // Fixed RIF as per requirement
        $rif = 'J-506549220';

        // Calculate data for NATURAL (Individual) affiliates
        $naturalData = $this->calculateCarteraData($cutoffDate, 'NATURAL');

        // Calculate data for JURÍDICO (Corporate) affiliates
        $juridicoData = $this->calculateCarteraData($cutoffDate, 'JURIDICO');

        // Create Excel spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cartera Vigente');

        // =============================================
        // HEADER ROW (Row 1) - Column Titles
        // =============================================
        $headerRow = 1;

        $headers = [
            'A' => 'ID_EMPRESA',
            'B' => 'TIPO_TOMADOR',
            'C' => 'TIPO_SEGURO',
            'D' => 'TITULARES_VIGENTES',
            'E' => 'DEPENDIENTES_VIGENTES'
        ];

        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . $headerRow, $header);
        }

        // Style the header row
        $headerStyle = $sheet->getStyle('A1:E1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFont()->setSize(11);
        $headerStyle->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2c3e50');
        $headerStyle->getFont()->getColor()->setARGB('FFFFFFFF');
        $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $headerStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // =============================================
        // FIRST DATA LINE (Row 2): NATURAL (Individual)
        // =============================================
        $dataRow = 2;

        $sheet->setCellValue('A' . $dataRow, $rif);
        $sheet->setCellValue('B' . $dataRow, 'NATURAL');
        $sheet->setCellValue('C' . $dataRow, 'PERSONAS');
        $sheet->setCellValue('D' . $dataRow, $naturalData['titulares_vigentes']);
        $sheet->setCellValue('E' . $dataRow, $naturalData['dependientes_vigentes']);

        // =============================================
        // SECOND DATA LINE (Row 3): JURÍDICO (Corporate)
        // =============================================
        $dataRow = 3;

        $sheet->setCellValue('A' . $dataRow, $rif);
        $sheet->setCellValue('B' . $dataRow, 'JURÍDICO');
        $sheet->setCellValue('C' . $dataRow, 'PERSONAS');
        $sheet->setCellValue('D' . $dataRow, $juridicoData['titulares_vigentes']);
        $sheet->setCellValue('E' . $dataRow, $juridicoData['dependientes_vigentes']);

        // =============================================
        // FORMATTING
        // =============================================

        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Center align all data
        $sheet->getStyle('A2:E3')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:E3')->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Add borders to all cells
        $sheet->getStyle('A1:E3')->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Make first data row bold
        $sheet->getStyle('A2:E2')->getFont()->setBold(true);

        // Alternating row colors
        $sheet->getStyle('A2:E2')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF8F9FA');

        $sheet->getStyle('A3:E3')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE9ECEF');

        // Format numeric columns
        $sheet->getStyle('D2:E3')
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        // Create filename
        $monthNames = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        ];
        $monthName = $monthNames[$month] ?? $month;
        $filename = "Cartera_Vigente_{$monthName}_{$year}.xlsx";

        // Output the file
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

    /**
     * Calculate Cartera Data for a specific type of affiliate
     * 
     * @param string $cutoffDate Cut-off date (last day of the month)
     * @param string $tipo 'NATURAL' or 'JURIDICO'
     * @return array ['titulares_vigentes' => int, 'dependientes_vigentes' => int]
     */
    private function calculateCarteraData($cutoffDate, $tipo)
    {
        // =============================================
        // TITULARES_VIGENTES (Active Affiliates)
        // =============================================

        if ($tipo === 'NATURAL') {
            // NATURAL: Individual affiliates (user_datos_type_id = 1) with active contracts
            $query = Contratos::find()
                ->alias('c')
                ->select(['c.user_id'])
                ->distinct()
                ->innerJoin(['ud' => 'user_datos'], 'ud.id = c.user_id')
                ->where(['c.estatus' => Contratos::STATUS_ACTIVO])
                ->andWhere(['ud.user_datos_type_id' => 1])
                ->andWhere(['ud.role' => 'afiliado'])
                ->andWhere(['IS', 'ud.deleted_at', null])
                ->andWhere([
                    'or',
                    ['c.fecha_ini' => null],
                    ['<=', 'c.fecha_ini', $cutoffDate]
                ])
                ->andWhere([
                    'or',
                    ['c.fecha_ven' => null],
                    ['>=', 'c.fecha_ven', $cutoffDate]
                ]);

            $titularesVigentes = $query->count();
        } else {
            // JURIDICO: Count DISTINCT corporations (corporativos) that have at least ONE affiliate with active contract
            $titularesVigentes = UserDatos::find()
                ->alias('ud')
                ->select(['ud.afiliado_corporativo_id'])
                ->distinct()
                ->innerJoin(['c' => 'contratos'], 'c.user_id = ud.id')
                ->where(['c.estatus' => Contratos::STATUS_ACTIVO])
                ->andWhere(['ud.user_datos_type_id' => 2]) // Corporate type affiliates
                ->andWhere(['ud.role' => 'afiliado'])
                ->andWhere(['IS', 'ud.deleted_at', null])
                ->andWhere(['IS NOT', 'ud.afiliado_corporativo_id', null])
                ->andWhere([
                    'or',
                    ['c.fecha_ini' => null],
                    ['<=', 'c.fecha_ini', $cutoffDate]
                ])
                ->andWhere([
                    'or',
                    ['c.fecha_ven' => null],
                    ['>=', 'c.fecha_ven', $cutoffDate]
                ])
                ->count();
        }

        // =============================================
        // DEPENDIENTES_VIGENTES (Active Dependents)
        // =============================================

        if ($tipo === 'NATURAL') {
            // NATURAL: Count individual dependents with tiene_contratante_diferente = true
            $dependientesVigentes = UserDatos::find()
                ->alias('u')
                ->innerJoin(['c' => 'contratos'], 'c.user_id = u.id')
                ->where(['c.estatus' => Contratos::STATUS_ACTIVO])
                ->andWhere([
                    'or',
                    ['c.fecha_ini' => null],
                    ['<=', 'c.fecha_ini', $cutoffDate]
                ])
                ->andWhere([
                    'or',
                    ['c.fecha_ven' => null],
                    ['>=', 'c.fecha_ven', $cutoffDate]
                ])
                ->andWhere(['u.tiene_contratante_diferente' => true])
                ->andWhere(['u.role' => 'afiliado'])
                ->andWhere(['IS', 'u.deleted_at', null])
                ->andWhere(['u.user_datos_type_id' => 1]) // Only individual dependents
                ->count();
        } else {
            // JURIDICO: Count ALL affiliates (individuals) who belong to ANY corporate AND have active contracts
            $dependientesVigentes = UserDatos::find()
                ->alias('ud')
                ->innerJoin(['c' => 'contratos'], 'c.user_id = ud.id')
                ->innerJoin(['corporativos' => 'corporativos'], 'corporativos.id = ud.afiliado_corporativo_id')
                ->where(['c.estatus' => Contratos::STATUS_ACTIVO])
                ->andWhere(['ud.role' => 'afiliado'])
                ->andWhere(['IS', 'ud.deleted_at', null])
                ->andWhere(['IS NOT', 'ud.afiliado_corporativo_id', null])
                ->andWhere([
                    'or',
                    ['c.fecha_ini' => null],
                    ['<=', 'c.fecha_ini', $cutoffDate]
                ])
                ->andWhere([
                    'or',
                    ['c.fecha_ven' => null],
                    ['>=', 'c.fecha_ven', $cutoffDate]
                ])
                ->count();
        }

        // Log for debugging
        Yii::info("Cartera Vigente - {$tipo}: Titulares={$titularesVigentes}, Dependientes={$dependientesVigentes}, Cutoff={$cutoffDate}", 'cartera-debug');

        return [
            'titulares_vigentes' => $titularesVigentes,
            'dependientes_vigentes' => $dependientesVigentes,
        ];
    }

    /**
     * Get month options for dropdown
     * 
     * @return array
     */
    private function getMonthOptions()
    {
        return [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];
    }

    /**
     * Get year options for dropdown
     * 
     * @return array
     */
    private function getYearOptions()
    {
        $currentYear = date('Y');
        $years = [];
        for ($year = $currentYear - 2; $year <= $currentYear + 2; $year++) {
            $years[$year] = $year;
        }
        return $years;
    }
}
