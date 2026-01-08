<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;

/**
 * SisSiniestroReporteSearch represents the model for generating clinic attention reports
 */
class SisSiniestroReporteSearch extends Model
{
    public $range;
    public $date_from;
    public $date_to;
    public $clinicas = [];
    public $status; // For possible future expansion

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['range'], 'string'],
            [['date_from', 'date_to'], 'safe'],
            [['clinicas', 'status'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'range' => 'Período',
            'date_from' => 'Fecha Inicial',
            'date_to' => 'Fecha Final',
            'clinicas' => 'Clínicas',
            'status' => 'Estado',
        ];
    }


    /**
     * Get date range based on selected range
     */
    public function getDateRange()
    {
        // If direct dates are provided, use them
        if (!empty($this->date_from) && !empty($this->date_to)) {
            Yii::info("Using direct dates: {$this->date_from} to {$this->date_to}", __METHOD__);
            return [
                'from' => $this->date_from,
                'to' => $this->date_to
            ];
        }

        $today = new \DateTime();
        $dateFrom = clone $today;
        $dateTo = clone $today;

        switch ($this->range) {
            case 'day':
                // Already set to today
                break;

            case 'week':
                // Last 7 days (including today)
                $dateFrom->modify('-6 days');
                break;

            case 'month':
                // Current month
                $dateFrom = new \DateTime('first day of this month');
                $dateTo = new \DateTime('last day of this month');
                break;

            case 'last-month':
                // Previous month
                $dateFrom = new \DateTime('first day of last month');
                $dateTo = new \DateTime('last day of last month');
                break;

            case 'custom':
                // Should have been handled by direct dates above
                break;
        }

        Yii::info("Calculated dates for range {$this->range}: {$dateFrom->format('Y-m-d')} to {$dateTo->format('Y-m-d')}", __METHOD__);

        return [
            'from' => $dateFrom->format('Y-m-d'),
            'to' => $dateTo->format('Y-m-d')
        ];
    }

    /**
     * Generate clinic attention report
     */
    public function generateReport()
    {
        $dateRange = $this->getDateRange();

        // Debug: Log the date range being used
        Yii::info("Generating report with date range: {$dateRange['from']} to {$dateRange['to']}", __METHOD__);

        $query = (new Query())
            ->select([
                'c.id',
                'c.nombre as clinic_name',
                'c.estatus as clinic_status',
                'COUNT(s.id) as total_attentions',
                // PostgreSQL-compatible using explicit boolean to integer casting
                'SUM(CASE WHEN s.atendido::integer = 1 THEN 1 ELSE 0 END) as attended_count',
                'SUM(CASE WHEN s.atendido::integer = 0 OR s.atendido IS NULL THEN 1 ELSE 0 END) as pending_count',
                'COUNT(DISTINCT s.iduser) as unique_patients',
                'AVG(s.costo_total) as avg_cost',
                'SUM(s.costo_total) as total_cost',
                // PostgreSQL-compatible using explicit boolean to integer casting
                'COUNT(CASE WHEN s.es_cita::integer = 1 THEN 1 END) as appointments_count',
                'COUNT(CASE WHEN s.es_cita::integer = 0 THEN 1 END) as emergencies_count',
            ])
            ->from(['c' => RmClinica::tableName()])
            ->leftJoin(['s' => SisSiniestro::tableName()], 'c.id = s.idclinica')
            ->where(['c.estatus' => 'Activo'])
            ->andWhere(['>=', 's.fecha', $dateRange['from']])
            ->andWhere(['<=', 's.fecha', $dateRange['to']]);

        // Filter by selected clinics
        if (!empty($this->clinicas) && !in_array('todas', $this->clinicas)) {
            $query->andWhere(['c.id' => $this->clinicas]);
        }

        $query->groupBy(['c.id', 'c.nombre', 'c.estatus'])
            ->orderBy(['total_attentions' => SORT_DESC]);

        $results = $query->all();

        // Format and enhance data
        $formattedResults = [];
        $totalAttentions = 0;
        $totalPatients = 0;
        $totalCost = 0;

        foreach ($results as $index => $row) {
            $attendanceRate = $row['total_attentions'] > 0
                ? round(($row['attended_count'] / $row['total_attentions']) * 100, 1)
                : 0;

            $avgPatientAttentions = $row['unique_patients'] > 0
                ? round($row['total_attentions'] / $row['unique_patients'], 2)
                : 0;

            $formattedResults[] = [
                'id' => $row['id'],
                'clinic_name' => $row['clinic_name'],
                'clinic_status' => $row['clinic_status'],
                'total_attentions' => (int)$row['total_attentions'],
                'attended_count' => (int)$row['attended_count'],
                'pending_count' => (int)$row['pending_count'],
                'unique_patients' => (int)$row['unique_patients'],
                'avg_cost' => (float)$row['avg_cost'],
                'total_cost' => (float)$row['total_cost'],
                'appointments_count' => (int)$row['appointments_count'],
                'emergencies_count' => (int)$row['emergencies_count'],
                'attendance_rate' => $attendanceRate,
                'avg_patient_attentions' => $avgPatientAttentions,
                'performance_level' => $this->getPerformanceLevel($attendanceRate),
            ];

            $totalAttentions += $row['total_attentions'];
            $totalPatients += $row['unique_patients'];
            $totalCost += $row['total_cost'];
        }

        return [
            'data' => $formattedResults,
            'summary' => [
                'total_clinics' => count($formattedResults),
                'total_attentions' => $totalAttentions,
                'total_patients' => $totalPatients,
                'total_cost' => $totalCost,
                'avg_cost_per_attention' => $totalAttentions > 0 ? round($totalCost / $totalAttentions, 2) : 0,
                'date_range' => $dateRange,
            ]
        ];
    }

    /**
     * Generate detailed report for a specific clinic
     */
    public function generateClinicDetailReport($clinicId)
    {
        $dateRange = $this->getDateRange();

        // Get clinic info
        $clinic = RmClinica::findOne($clinicId);
        if (!$clinic) {
            return null;
        }

        // Get attentions for this clinic
        $attentions = SisSiniestro::find()
            ->alias('s')
            ->joinWith(['afiliado a', 'baremos b'])
            ->where(['s.idclinica' => $clinicId])
            ->andWhere(['>=', 's.fecha', $dateRange['from']])
            ->andWhere(['<=', 's.fecha', $dateRange['to']])
            ->orderBy(['s.fecha' => SORT_DESC, 's.hora' => SORT_DESC])
            ->all();

        // Get daily statistics
        $dailyStats = (new Query())
            ->select([
                's.fecha as date',
                'COUNT(s.id) as attentions_count',
                // PostgreSQL-compatible using explicit boolean to integer casting
                'SUM(CASE WHEN s.atendido::integer = 1 THEN 1 ELSE 0 END) as attended_count',
                'SUM(s.costo_total) as daily_cost',
                'COUNT(DISTINCT s.iduser) as daily_patients',
            ])
            ->from(['s' => SisSiniestro::tableName()])
            ->where(['s.idclinica' => $clinicId])
            ->andWhere(['>=', 's.fecha', $dateRange['from']])
            ->andWhere(['<=', 's.fecha', $dateRange['to']])
            ->groupBy('s.fecha')
            ->orderBy('s.fecha')
            ->all();

        // Get most common baremos - FIXED for composite primary key
        $commonBaremos = (new Query())
            ->select([
                'b.nombre_servicio as service_name',
                'COUNT(*) as usage_count',  // Use COUNT(*) since primary key is composite
                'AVG(b.precio) as avg_price',
                'SUM(b.precio) as total_cost',
            ])
            ->from(['sb' => 'sis_siniestro_baremo'])
            ->leftJoin(['s' => SisSiniestro::tableName()], 'sb.siniestro_id = s.id')
            ->leftJoin(['b' => 'baremo'], 'sb.baremo_id = b.id')
            ->where(['s.idclinica' => $clinicId])
            ->andWhere(['>=', 's.fecha', $dateRange['from']])
            ->andWhere(['<=', 's.fecha', $dateRange['to']])
            ->groupBy('b.id', 'b.nombre_servicio')
            ->orderBy(['usage_count' => SORT_DESC])
            ->limit(10)
            ->all();

        return [
            'clinic' => $clinic,
            'attentions' => $attentions,
            'daily_stats' => $dailyStats,
            'common_baremos' => $commonBaremos,
            'date_range' => $dateRange,
        ];
    }

    /**
     * Get performance level based on attendance rate
     */
    private function getPerformanceLevel($attendanceRate)
    {
        if ($attendanceRate >= 90) {
            return 'excelente';
        } elseif ($attendanceRate >= 75) {
            return 'bueno';
        } elseif ($attendanceRate >= 60) {
            return 'regular';
        } else {
            return 'bajo';
        }
    }

    /**
     * Search for data provider
     */
    public function search($params)
    {
        $this->load($params);

        $reportData = $this->generateReport();

        return new ArrayDataProvider([
            'allModels' => $reportData['data'],
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'clinic_name',
                    'total_attentions',
                    'attendance_rate',
                    'total_cost',
                ],
            ],
        ]);
    }
}
