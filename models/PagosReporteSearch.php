<?php
// app/models/PagosReporteSearch.php - BULLETPROOF VERSION

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
    public $intermediario;

    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['fecha_pago', 'metodo_pago', 'estatus', 'numero_referencia_pago'], 'safe'],
            [['monto_usd'], 'number'],
            [['nombres', 'apellidos', 'cedula', 'intermediario'], 'safe'],
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

        $query->innerJoin(['ud' => 'user_datos'], 'ud.id = p.user_id');

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
        } elseif (!empty($userSelectedClinicas) && !in_array('todas', $userSelectedClinicas)) {
            $query->andWhere(['ud.clinica_id' => $userSelectedClinicas]);
            Yii::info("Applied admin clinic filter: " . json_encode($userSelectedClinicas), 'pagos-report');
            return true;
        }

        return true;
    }

    /**
     * MAIN SEARCH METHOD - BULLETPROOF VERSION
     * 
     * This version uses a simpler approach with LEFT JOINs and proper NULL handling
     */
    public function search($params, $startDate, $endDate, $status = 'Conciliado', $clinicas = [])
    {
        $query = Pagos::find()
            ->alias('p')
            ->select([
                'p.*',
                'ud.nombres',
                'ud.apellidos',
                'ud.cedula',
                'ud.clinica_id',
                'ud.asesor_id',
                // Intermediario data - using COALESCE to handle NULLs
                'intermediario_ud.nombres as intermediario_nombres',
                'intermediario_ud.apellidos as intermediario_apellidos',
                'intermediario_ud.cedula as intermediario_cedula',
                // Agency data
                'ag.nom as agencia_nombre'
            ]);

        // Apply clinic filter (adds user_datos join with correct alias)
        $this->buildClinicFilterCondition($query, $clinicas);

        // =============================================
        // INTERMEDIARIO JOINS - Using LEFT JOIN to not lose records
        // =============================================
        // Join to AgenteFuerza using ud.asesor_id
        $query->leftJoin(
            ['af' => 'agente_fuerza'],
            'af.id = ud.asesor_id'
        );

        // Join to UserDatos to get the Intermediario's name
        $query->leftJoin(
            ['intermediario_ud' => 'user_datos'],
            'intermediario_ud.id = af.idusuario AND intermediario_ud.deleted_at IS NULL'
        );

        // Join to Agente to get the Agency name (for reference)
        $query->leftJoin(
            ['ag' => 'agente'],
            'ag.id = af.agente_id'
        );

        // Date filter
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
                    'intermediario' => [
                        'asc' => ['intermediario_ud.nombres' => SORT_ASC, 'intermediario_ud.apellidos' => SORT_ASC],
                        'desc' => ['intermediario_ud.nombres' => SORT_DESC, 'intermediario_ud.apellidos' => SORT_DESC],
                        'label' => 'Intermediario',
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
            ->andFilterWhere(['ilike', 'CAST(ud.cedula AS TEXT)', $this->cedula])
            // Filter by Intermediario name
            ->andFilterWhere([
                'or',
                ['ilike', 'intermediario_ud.nombres', $this->intermediario],
                ['ilike', 'intermediario_ud.apellidos', $this->intermediario],
                ['ilike', "CONCAT(intermediario_ud.nombres, ' ', intermediario_ud.apellidos)", $this->intermediario]
            ]);

        return $dataProvider;
    }

    /**
     * SEARCH WITH CLINICAS - BULLETPROOF VERSION
     */
    public function searchConClinicas($params, $startDate, $endDate, $status = 'Conciliado', $clinicas = [])
    {
        $query = Pagos::find()
            ->alias('p')
            ->select([
                'p.*',
                'ud.nombres',
                'ud.apellidos',
                'ud.cedula',
                'ud.clinica_id',
                'ud.asesor_id',
                'intermediario_ud.nombres as intermediario_nombres',
                'intermediario_ud.apellidos as intermediario_apellidos',
                'intermediario_ud.cedula as intermediario_cedula',
                'ag.nom as agencia_nombre'
            ]);

        $this->buildClinicFilterCondition($query, $clinicas);

        // Intermediario JOINS
        $query->leftJoin(
            ['af' => 'agente_fuerza'],
            'af.id = ud.asesor_id'
        );
        $query->leftJoin(
            ['intermediario_ud' => 'user_datos'],
            'intermediario_ud.id = af.idusuario AND intermediario_ud.deleted_at IS NULL'
        );
        $query->leftJoin(
            ['ag' => 'agente'],
            'ag.id = af.agente_id'
        );

        // Date filter
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
                    'intermediario' => [
                        'asc' => ['intermediario_ud.nombres' => SORT_ASC, 'intermediario_ud.apellidos' => SORT_ASC],
                        'desc' => ['intermediario_ud.nombres' => SORT_DESC, 'intermediario_ud.apellidos' => SORT_DESC],
                        'label' => 'Intermediario',
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
            ->andFilterWhere(['ilike', 'CAST(ud.cedula AS TEXT)', $this->cedula])
            ->andFilterWhere([
                'or',
                ['ilike', 'intermediario_ud.nombres', $this->intermediario],
                ['ilike', 'intermediario_ud.apellidos', $this->intermediario],
                ['ilike', "CONCAT(intermediario_ud.nombres, ' ', intermediario_ud.apellidos)", $this->intermediario]
            ]);

        return $dataProvider;
    }

    /**
     * Get the Intermediario (Asesor) name for a model
     * This is the BULLETPROOF method that tries multiple approaches
     * 
     * @param mixed $model The payment model
     * @return string
     */
    public function getIntermediarioName($model)
    {
        // Approach 1: Check if the joined data is available
        if (isset($model->intermediario_nombres) && !empty($model->intermediario_nombres)) {
            $name = trim($model->intermediario_nombres . ' ' . ($model->intermediario_apellidos ?? ''));
            if (!empty($name)) {
                return $name;
            }
        }

        // Approach 2: Through UserDatos.asesor_id -> AgenteFuerza -> UserDatos (Intermediario)
        if ($model->userDatos && $model->userDatos->asesor_id) {
            $agenteFuerza = AgenteFuerza::find()
                ->where(['id' => $model->userDatos->asesor_id])
                ->one();
            if ($agenteFuerza && $agenteFuerza->userDatos) {
                $name = trim($agenteFuerza->userDatos->nombres . ' ' . $agenteFuerza->userDatos->apellidos);
                if (!empty($name)) {
                    return $name;
                }
            }
        }

        // Approach 3: Check if there's a direct relationship from UserDatos to an agent
        if ($model->userDatos && $model->userDatos->agencia_id) {
            // Try to find the agent through agencia_id
            $agente = Agente::findOne($model->userDatos->agencia_id);
            if ($agente) {
                return $agente->nom;
            }
        }

        // Approach 4: Check if there's an AgenteFuerza record for this user
        if ($model->userDatos) {
            $agenteFuerza = AgenteFuerza::find()
                ->where(['idusuario' => $model->userDatos->id])
                ->one();
            if ($agenteFuerza && $agenteFuerza->userDatos) {
                $name = trim($agenteFuerza->userDatos->nombres . ' ' . $agenteFuerza->userDatos->apellidos);
                if (!empty($name)) {
                    return $name;
                }
            }
        }

        return 'Sin Intermediario';
    }

    /**
     * SUMMARY BY CLINIC
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
     * GENERAL SUMMARY
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
     * COMMISSION REPORT
     */
    public function searchComisiones($params)
    {
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

        // Apply clinic access restrictions
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
                Yii::info("Commission report filtered to clinic: {$clinicaId}", 'comisiones');
            } else {
                $query->andWhere('1=0');
                Yii::warning("Clinic role user has no clinic assigned", 'comisiones');
            }
        } elseif (!empty($params['clinicas']) && is_array($params['clinicas']) && !in_array('todas', $params['clinicas'])) {
            $query->andWhere(['ud.clinica_id' => $params['clinicas']]);
            Yii::info("Admin clinic filter applied: " . json_encode($params['clinicas']), 'comisiones');
        }

        // Date filter
        if (!empty($params['range']) && $params['range'] !== 'custom') {
            $this->applyDateRange($query, $params['range']);
        } elseif (!empty($params['custom_range']) && !empty($params['date_from']) && !empty($params['date_to'])) {
            $query->andWhere(['>=', 'p.fecha_pago', $params['date_from']])
                ->andWhere(['<=', 'p.fecha_pago', $params['date_to']]);
            Yii::info("Custom date range: {$params['date_from']} to {$params['date_to']}", 'comisiones');
        }

        // Status filter
        if (!empty($params['status']) && $params['status'] !== 'todos') {
            $query->andWhere(['p.estatus' => $params['status']]);
            Yii::info("Status filter: {$params['status']}", 'comisiones');
        }

        Yii::info("Commission report SQL: " . $query->createCommand()->rawSql, 'comisiones');

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
