<?php
// app/models/PagosReporteSearch.php - COMPLETE FIXED VERSION

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Pagos;
use yii\db\Expression;
use Yii;
use app\components\UserHelper;

class PagosReporteSearch extends Pagos
{
    public $nombres;
    public $apellidos;
    public $cedula;

    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'], // REMOVED recibo_id
            [['fecha_pago', 'metodo_pago', 'estatus', 'numero_referencia_pago'], 'safe'],
            [['monto_usd'], 'number'],
            [['nombres', 'apellidos', 'cedula'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Get restricted clinic ID for clinic role users
     * @return int|null
     */
    private function getRestrictedClinicaId()
    {
        $userRole = UserHelper::getMyRol();
        $clinicRoles = [
            "Administrador-clinica",
            "CONTROL DE CITAS",
            "ADMISIÓN",
            "ATENCIÓN",
            "COORDINADOR-CLINICA",
            "GERENTE-CLINICA"
        ];

        if (in_array($userRole, $clinicRoles)) {
            return UserHelper::getMyClinicaId();
        }
        return null;
    }

    /**
     * Build clinic filter with correct alias usage
     */
    private function buildClinicFilterCondition($query, $userSelectedClinicas = [])
    {
        $userRole = UserHelper::getMyRol();
        $clinicRoles = [
            "Administrador-clinica",
            "CONTROL DE CITAS",
            "ADMISIÓN",
            "ATENCIÓN",
            "COORDINADOR-CLINICA",
            "GERENTE-CLINICA"
        ];

        // IMPORTANT: Use the table alias 'p' for pagos
        $query->innerJoin(['ud' => 'user_datos'], 'ud.id = p.user_id');

        // Case 1: Clinic role users
        if (in_array($userRole, $clinicRoles)) {
            $clinicaId = $this->getRestrictedClinicaId();
            if ($clinicaId) {
                $query->andWhere(['ud.clinica_id' => $clinicaId]);
                Yii::info("Applied clinic restriction: clinica_id = {$clinicaId}", 'pagos-report');
                return true;
            } else {
                $query->andWhere('1=0');
                return false;
            }
        }
        // Case 2: Admin with selected clinics
        elseif (!empty($userSelectedClinicas) && !in_array('todas', $userSelectedClinicas)) {
            $query->andWhere(['ud.clinica_id' => $userSelectedClinicas]);
            Yii::info("Applied admin clinic filter: " . json_encode($userSelectedClinicas), 'pagos-report');
            return true;
        }

        return true;
    }

    /**
     * MAIN SEARCH METHOD - FIXED date handling (no +1 day)
     */
    public function search($params, $startDate, $endDate, $status = 'Conciliado', $clinicas = [])
    {
        // Use alias 'p' for pagos table
        $query = Pagos::find()
            ->alias('p')
            ->select(['p.*', 'ud.nombres', 'ud.apellidos', 'ud.cedula', 'ud.clinica_id'])
            ->groupBy('p.id, ud.id');

        // Apply clinic filter (adds user_datos join with correct alias)
        $this->buildClinicFilterCondition($query, $clinicas);

        // Date filter - FIXED: Use >= and <= instead of between with +1 day
        if ($startDate && $endDate) {
            $query->andWhere(['>=', 'p.fecha_pago', $startDate])
                ->andWhere(['<=', 'p.fecha_pago', $endDate]);
            Yii::info("Date filter: {$startDate} to {$endDate} (inclusive)", 'pagos-report');
        }

        // Status filter
        if ($status !== 'todos') {
            $query->andWhere(['p.estatus' => $status]);
            Yii::info("Status filter: {$status}", 'pagos-report');
        }

        // Log the final SQL for debugging
        Yii::info("FINAL SEARCH SQL: " . $query->createCommand()->rawSql, 'pagos-report');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['fecha_pago' => SORT_DESC],
                'attributes' => [
                    'id',
                    'fecha_pago',
                    'monto_usd',
                    'metodo_pago',
                    'estatus',
                    'nombres' => [
                        'asc' => ['ud.nombres' => SORT_ASC],
                        'desc' => ['ud.nombres' => SORT_DESC],
                    ],
                    'apellidos' => [
                        'asc' => ['ud.apellidos' => SORT_ASC],
                        'desc' => ['ud.apellidos' => SORT_DESC],
                    ],
                    'cedula' => [
                        'asc' => ['ud.cedula' => SORT_ASC],
                        'desc' => ['ud.cedula' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Additional filters
        $query->andFilterWhere(['p.id' => $this->id])
            ->andFilterWhere(['p.user_id' => $this->user_id])
            ->andFilterWhere(['ilike', 'p.metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 'p.numero_referencia_pago', $this->numero_referencia_pago])
            ->andFilterWhere(['ilike', 'p.estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'ud.nombres', $this->nombres])
            ->andFilterWhere(['ilike', 'ud.apellidos', $this->apellidos])
            ->andFilterWhere(['ilike', 'CAST(ud.cedula AS TEXT)', $this->cedula]);

        return $dataProvider;
    }

    /**
     * SEARCH WITH CLINICAS - FIXED date handling (no +1 day)
     */
    public function searchConClinicas($params, $startDate, $endDate, $status = 'Conciliado', $clinicas = [])
    {
        $query = Pagos::find()
            ->alias('p')
            ->select(['p.*', 'ud.nombres', 'ud.apellidos', 'ud.cedula', 'ud.clinica_id'])
            ->groupBy('p.id, ud.id');

        $this->buildClinicFilterCondition($query, $clinicas);

        // Date filter - FIXED: Use >= and <= instead of between with +1 day
        if ($startDate && $endDate) {
            $query->andWhere(['>=', 'p.fecha_pago', $startDate])
                ->andWhere(['<=', 'p.fecha_pago', $endDate]);
        }

        if ($status !== 'todos') {
            $query->andWhere(['p.estatus' => $status]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['fecha_pago' => SORT_DESC],
                'attributes' => [
                    'id',
                    'fecha_pago',
                    'monto_usd',
                    'metodo_pago',
                    'estatus',
                    'nombres' => [
                        'asc' => ['ud.nombres' => SORT_ASC],
                        'desc' => ['ud.nombres' => SORT_DESC],
                    ],
                    'apellidos' => [
                        'asc' => ['ud.apellidos' => SORT_ASC],
                        'desc' => ['ud.apellidos' => SORT_DESC],
                    ],
                    'cedula' => [
                        'asc' => ['ud.cedula' => SORT_ASC],
                        'desc' => ['ud.cedula' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['p.id' => $this->id])
            ->andFilterWhere(['p.user_id' => $this->user_id])
            ->andFilterWhere(['ilike', 'p.metodo_pago', $this->metodo_pago])
            ->andFilterWhere(['ilike', 'p.numero_referencia_pago', $this->numero_referencia_pago])
            ->andFilterWhere(['ilike', 'p.estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'ud.nombres', $this->nombres])
            ->andFilterWhere(['ilike', 'ud.apellidos', $this->apellidos])
            ->andFilterWhere(['ilike', 'CAST(ud.cedula AS TEXT)', $this->cedula]);

        return $dataProvider;
    }

    /**
     * SUMMARY BY CLINIC - FIXED date handling (no +1 day)
     */
    public function obtenerResumenPorClinica($startDate, $endDate, $status = 'todos', $clinicas = [])
    {
        $query = Pagos::find()
            ->alias('p')
            ->select([
                'clinica_id' => 'ud.clinica_id',
                'clinica_nombre' => 'rc.nombre',
                'clinica_rif' => 'rc.rif',
                'total_monto' => 'COALESCE(SUM(p.monto_usd), 0)',
                'total_pagos' => 'COUNT(DISTINCT p.id)',
                'conciliados' => new Expression("SUM(CASE WHEN p.estatus = 'Conciliado' THEN 1 ELSE 0 END)"),
                'pendientes' => new Expression("SUM(CASE WHEN p.estatus = 'Por Conciliar' THEN 1 ELSE 0 END)")
            ])
            ->innerJoin(['ud' => 'user_datos'], 'ud.id = p.user_id')
            ->innerJoin(['rc' => 'rm_clinica'], 'rc.id = ud.clinica_id')
            ->where(['>=', 'p.fecha_pago', $startDate])
            ->andWhere(['<=', 'p.fecha_pago', $endDate])
            ->groupBy(['ud.clinica_id', 'rc.nombre', 'rc.rif']);

        // Apply clinic filter
        $userRole = UserHelper::getMyRol();
        $clinicRoles = [
            "Administrador-clinica",
            "CONTROL DE CITAS",
            "ADMISIÓN",
            "ATENCIÓN",
            "COORDINADOR-CLINICA",
            "GERENTE-CLINICA"
        ];

        if (in_array($userRole, $clinicRoles)) {
            $clinicaId = $this->getRestrictedClinicaId();
            if ($clinicaId) {
                $query->andWhere(['ud.clinica_id' => $clinicaId]);
            } else {
                return [];
            }
        } elseif (!empty($clinicas) && !in_array('todas', $clinicas)) {
            $query->andWhere(['ud.clinica_id' => $clinicas]);
        }

        if ($status !== 'todos') {
            $query->andWhere(['p.estatus' => $status]);
        }

        $result = $query->orderBy(['total_monto' => SORT_DESC])->asArray()->all();

        Yii::info("Summary found " . count($result) . " clinics", 'pagos-report');

        return $result;
    }

    /**
     * GENERAL SUMMARY - FIXED date handling (no +1 day)
     */
    public function obtenerResumenGeneral($startDate, $endDate, $status = 'todos', $clinicas = [])
    {
        $query = Pagos::find()
            ->alias('p')
            ->innerJoin(['ud' => 'user_datos'], 'ud.id = p.user_id')
            ->where(['>=', 'p.fecha_pago', $startDate])
            ->andWhere(['<=', 'p.fecha_pago', $endDate]);

        // Apply clinic filter
        $userRole = UserHelper::getMyRol();
        $clinicRoles = [
            "Administrador-clinica",
            "CONTROL DE CITAS",
            "ADMISIÓN",
            "ATENCIÓN",
            "COORDINADOR-CLINICA",
            "GERENTE-CLINICA"
        ];

        if (in_array($userRole, $clinicRoles)) {
            $clinicaId = $this->getRestrictedClinicaId();
            if ($clinicaId) {
                $query->andWhere(['ud.clinica_id' => $clinicaId]);
            } else {
                return ['total_monto' => 0, 'total_count' => 0, 'conciliados' => 0, 'pendientes' => 0];
            }
        } elseif (!empty($clinicas) && !in_array('todas', $clinicas)) {
            $query->andWhere(['ud.clinica_id' => $clinicas]);
        }

        if ($status !== 'todos') {
            $query->andWhere(['p.estatus' => $status]);
        }

        $totalMonto = $query->sum('p.monto_usd');
        $totalCount = $query->count();

        $conciliadosCount = 0;
        $pendientesCount = 0;

        if ($status === 'todos') {
            $conciliadosCount = (clone $query)->andWhere(['p.estatus' => 'Conciliado'])->count();
            $pendientesCount = (clone $query)->andWhere(['p.estatus' => 'Por Conciliar'])->count();
        } elseif ($status === 'Conciliado') {
            $conciliadosCount = $totalCount;
        } elseif ($status === 'Por Conciliar') {
            $pendientesCount = $totalCount;
        }

        return [
            'total_monto' => $totalMonto ? (float)$totalMonto : 0,
            'total_count' => $totalCount ? (int)$totalCount : 0,
            'conciliados' => $conciliadosCount,
            'pendientes' => $pendientesCount
        ];
    }

    /**
     * COMMISSION REPORT - FIXED with clinic access restrictions
     */
    public function searchComisiones($params)
    {
        // Use alias 'p' for pagos table
        $query = Pagos::find()
            ->alias('p')
            ->select([
                'p.*',
                'ud.nombres',
                'ud.apellidos',
                'ud.cedula',
                'ud.clinica_id',
                'ud.id as user_datos_id'
            ])
            ->innerJoin(['ud' => 'user_datos'], 'ud.id = p.user_id')
            ->where(['p.estatus' => ['Conciliado', 'Por Conciliar']]);

        // =============================================
        // APPLY CLINIC ACCESS RESTRICTIONS
        // =============================================
        $userRole = UserHelper::getMyRol();
        $clinicRoles = [
            "Administrador-clinica",
            "CONTROL DE CITAS",
            "ADMISIÓN",
            "ATENCIÓN",
            "COORDINADOR-CLINICA",
            "GERENTE-CLINICA"
        ];

        // Case 1: Clinic role users - restrict to their clinic
        if (in_array($userRole, $clinicRoles)) {
            $clinicaId = $this->getRestrictedClinicaId();
            if ($clinicaId) {
                $query->andWhere(['ud.clinica_id' => $clinicaId]);
                Yii::info("Commission report filtered to clinic: {$clinicaId}", 'comisiones');
            } else {
                $query->andWhere('1=0'); // No results if no clinic assigned
                Yii::warning("Clinic role user has no clinic assigned", 'comisiones');
            }
        }
        // Case 2: Admin with selected clinics
        elseif (!empty($params['clinicas']) && is_array($params['clinicas']) && !in_array('todas', $params['clinicas'])) {
            $query->andWhere(['ud.clinica_id' => $params['clinicas']]);
            Yii::info("Admin clinic filter applied: " . json_encode($params['clinicas']), 'comisiones');
        }

        // =============================================
        // DATE FILTER
        // =============================================
        if (!empty($params['range']) && $params['range'] !== 'custom') {
            $this->applyDateRange($query, $params['range']);
        } elseif (!empty($params['custom_range']) && !empty($params['date_from']) && !empty($params['date_to'])) {
            $query->andWhere(['>=', 'p.fecha_pago', $params['date_from']])
                ->andWhere(['<=', 'p.fecha_pago', $params['date_to']]);
            Yii::info("Custom date range: {$params['date_from']} to {$params['date_to']}", 'comisiones');
        }

        // =============================================
        // STATUS FILTER
        // =============================================
        if (!empty($params['status']) && $params['status'] !== 'todos') {
            $query->andWhere(['p.estatus' => $params['status']]);
            Yii::info("Status filter: {$params['status']}", 'comisiones');
        }

        // Log the final SQL for debugging
        Yii::info("Commission report SQL: " . $query->createCommand()->rawSql, 'comisiones');

        // =============================================
        // DATA PROVIDER CONFIGURATION
        // =============================================
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => ['fecha_pago' => SORT_DESC],
                'attributes' => [
                    'id',
                    'fecha_pago',
                    'monto_usd',
                    'monto_pagado',
                    'estatus',
                    'metodo_pago',
                    'nombres' => [
                        'asc' => ['ud.nombres' => SORT_ASC],
                        'desc' => ['ud.nombres' => SORT_DESC],
                    ],
                    'apellidos' => [
                        'asc' => ['ud.apellidos' => SORT_ASC],
                        'desc' => ['ud.apellidos' => SORT_DESC],
                    ],
                    'cedula' => [
                        'asc' => ['ud.cedula' => SORT_ASC],
                        'desc' => ['ud.cedula' => SORT_DESC],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Apply date range filter
     */
    private function applyDateRange($query, $range)
    {
        $today = date('Y-m-d');

        switch ($range) {
            case 'day':
                $query->andWhere(['DATE(p.fecha_pago)' => $today]);
                break;
            case 'week':
                $weekAgo = date('Y-m-d', strtotime('-7 days'));
                $query->andWhere(['>=', 'p.fecha_pago', $weekAgo])
                    ->andWhere(['<=', 'p.fecha_pago', $today]);
                break;
            case 'month':
                $monthStart = date('Y-m-01');
                $query->andWhere(['>=', 'p.fecha_pago', $monthStart])
                    ->andWhere(['<=', 'p.fecha_pago', $today]);
                break;
            case 'last-month':
                $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
                $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));
                $query->andWhere(['>=', 'p.fecha_pago', $lastMonthStart])
                    ->andWhere(['<=', 'p.fecha_pago', $lastMonthEnd]);
                break;
        }
    }
}
