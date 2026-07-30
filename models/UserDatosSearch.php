<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\UserDatos;
use Yii;
use app\components\UserHelper;
use DateTime;

/**
 * UserDatosSearch represents the model behind the search form of `app\models\UserDatos`.
 */
class UserDatosSearch extends UserDatos
{
    public $user_datos_type_id;
    public $afiliado_corporativo_id;
    public $clinica_nombre;
    public $consecutivo_menor;
    public $contrato_estatus;
    public $critical_filter; // Added for critical affiliates filter

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'clinica_id', 'plan_id', 'contrato_id', 'asesor_id', 'cedula', 'user_login_id', 'user_datos_type_id', 'afiliado_corporativo_id', 'consecutivo_menor', 'agencia_id'], 'integer'],
            [['created_at', 'user_id', 'nombres', 'fechanac', 'sexo', 'selfie', 'telefono', 'estado', 'role', 'estatus', 'imagen_identificacion', 'qr', 'video', 'ciudad', 'municipio', 'parroquia', 'direccion', 'codigoValidacion', 'apellidos', 'email', 'deleted_at', 'updated_at', 'ver_cedula', 'ver_foto', 'session_id', 'tipo_cedula', 'tipo_sangre', 'estatus_solvente', 'clinica_nombre', 'contrato_estatus', 'critical_filter'], 'safe'],
            [['paso'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['contrato_estatus'] = 'Estatus Contrato';
        $labels['critical_filter'] = 'Filtro Crítico';
        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $rol = UserHelper::getMyRol();
        $query = UserDatos::find();

        // ============================================================
        // CLINIC FILTERING - Apply based on user role
        // ============================================================
        if (UserHelper::hasClinicAccess()) {
            $clinicId = UserHelper::getMyClinicaId();
            if ($clinicId) {
                $query->andFilterWhere(['user_datos.clinica_id' => $clinicId]);
            }
        }

        // ============================================================
        // ASESOR FILTERING - For Asesor role
        // ============================================================
        if ($rol == "Asesor") {
            $asesorId = UserHelper::getAgenteFuerzaId();
            if ($asesorId) {
                $query->andWhere(['user_datos.asesor_id' => $asesorId]);
            } else {
                $userDatos = UserDatos::findOne(['user_login_id' => Yii::$app->user->id]);
                if ($userDatos) {
                    $query->andWhere(['user_datos.asesor_id' => $userDatos->id]);
                } else {
                    $query->andWhere('1=0');
                }
            }
        }

        // ============================================================
        // AGENTE FILTERING - For Agente role
        // ============================================================
        if ($rol == "Agente") {
            $agenteId = UserHelper::getAgenteId();

            if ($agenteId) {
                $condition = ['or'];

                $asesorPersonIds = AgenteFuerza::find()
                    ->where(['agente_id' => $agenteId])
                    ->select('idusuario')
                    ->distinct()
                    ->column();

                if (!empty($asesorPersonIds)) {
                    $allAgenteFuerzaIds = AgenteFuerza::find()
                        ->where(['idusuario' => $asesorPersonIds])
                        ->select('id')
                        ->column();

                    if (!empty($allAgenteFuerzaIds)) {
                        $condition[] = ['user_datos.asesor_id' => $allAgenteFuerzaIds];
                    }
                }

                $condition[] = ['user_datos.agencia_id' => $agenteId];
                $query->andWhere($condition);
            } else {
                $query->andWhere('1=0');
            }
        }

        // ============================================================
        // QUERY BUILDING WITH SELECTIONS AND JOINS
        // ============================================================
        $query->select([
            'user_datos.id',
            'user_datos.created_at',
            'user_datos.nombres',
            'user_datos.apellidos',
            'user_datos.cedula',
            'user_datos.tipo_cedula',
            'user_datos.telefono',
            'user_datos.email',
            'user_datos.estatus_solvente',
            'user_datos.user_datos_type_id',
            'user_datos.clinica_id',
            'user_datos.asesor_id',
            'user_datos.agencia_id',
            'user_datos.deleted_at',
            'user_datos.consecutivo_menor',
            'user_datos.afiliado_corporativo_id',
            'user_datos_type.nombre as userDatosTypeNombre',
            'rm_clinica.nombre as clinicaNombre',
            'ud_asesor.nombres as asesorNombres',
            'ud_asesor.apellidos as asesorApellidos',
            'contratos.estatus as contrato_estatus',
        ]);

        $query->joinWith(['userDatosType']);
        $query->joinWith([
            'asesor.userDatos' => function ($q) {
                $q->from(['ud_asesor' => 'user_datos']);
            },
            'clinica'
        ]);

        // ============================================================
        // FIX: LEFT JOIN CONTRATOS WITHOUT EXCLUDING ANULADO
        // ============================================================
        // Join ALL contracts (including annulled) so we can filter by them
        $query->leftJoin('contratos', 'contratos.user_id = user_datos.id');

        $query->leftJoin('corporativos', 'user_datos.afiliado_corporativo_id = corporativos.id');

        $query->groupBy([
            'user_datos.id',
            'user_datos.created_at',
            'user_datos.nombres',
            'user_datos.apellidos',
            'user_datos.cedula',
            'user_datos.tipo_cedula',
            'user_datos.telefono',
            'user_datos.email',
            'user_datos.estatus_solvente',
            'user_datos.user_datos_type_id',
            'user_datos.clinica_id',
            'user_datos.asesor_id',
            'user_datos.agencia_id',
            'user_datos.deleted_at',
            'user_datos.consecutivo_menor',
            'user_datos.afiliado_corporativo_id',
            'user_datos_type.nombre',
            'rm_clinica.nombre',
            'ud_asesor.nombres',
            'ud_asesor.apellidos',
            'contratos.estatus'
        ]);

        // ============================================================
        // DATA PROVIDER CONFIGURATION
        // ============================================================
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        // Sort attributes
        $dataProvider->sort->attributes['contrato_estatus'] = [
            'asc' => ['contratos.estatus' => SORT_ASC],
            'desc' => ['contratos.estatus' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['user_datos_type_id'] = [
            'asc' => ['user_datos_type.nombre' => SORT_ASC],
            'desc' => ['user_datos_type.nombre' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['afiliado_corporativo_id'] = [
            'asc' => ['user_datos.afiliado_corporativo_id' => SORT_ASC],
            'desc' => ['user_datos.afiliado_corporativo_id' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['corporativo'] = [
            'asc' => ['corporativos.nombre' => SORT_ASC],
            'desc' => ['corporativos.nombre' => SORT_DESC],
        ];

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // ============================================================
        // FILTERING CONDITIONS
        // ============================================================

        $query->andFilterWhere([
            'user_datos.id' => $this->id,
            'user_datos.paso' => $this->paso,
            'user_datos.clinica_id' => $this->clinica_id,
            'user_datos.plan_id' => $this->plan_id,
            'user_datos.contrato_id' => $this->contrato_id,
            'user_datos.asesor_id' => $this->asesor_id,
            'user_datos.updated_at' => $this->updated_at,
            'user_datos.user_login_id' => $this->user_login_id,
            'user_datos.user_datos_type_id' => $this->user_datos_type_id,
            'user_datos.afiliado_corporativo_id' => $this->afiliado_corporativo_id,
            'user_datos.consecutivo_menor' => $this->consecutivo_menor,
            'user_datos.agencia_id' => $this->agencia_id,
        ]);

        // Date range filter for created_at
        if (isset($this->created_at) && !empty($this->created_at)) {
            $dates = explode(" a ", $this->created_at);
            if (count($dates) == 2) {
                $d1 = DateTime::createFromFormat('d/m/Y', trim($dates[0]));
                $d2 = DateTime::createFromFormat('d/m/Y', trim($dates[1]));
                if ($d1 && $d2) {
                    $date1 = $d1->format('Y-m-d') . ' 00:00:00';
                    $date2 = $d2->format('Y-m-d') . ' 23:59:59';
                    $query->andFilterWhere(['between', 'user_datos.created_at', $date1, $date2]);
                }
            }
        }

        // Date range filter for fechanac
        if (isset($this->fechanac) && !empty($this->fechanac)) {
            $dates = explode("-", $this->fechanac);
            $query->andFilterWhere(['between', 'user_datos.fechanac', $dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
        }

        // ============================================================
        // CONTRACT STATUS FILTER - Includes "Anulado" support
        // ============================================================
        if (!empty($this->contrato_estatus)) {
            if ($this->contrato_estatus === 'sin_contrato') {
                // Show users with NO contracts at all
                $query->andWhere(['contratos.id' => null]);
            } elseif ($this->contrato_estatus === 'anulado') {
                // Show users whose MOST RECENT contract is Anulado
                // Use a subquery to find the most recent contract status
                $subQuery = Contratos::find()
                    ->select('estatus')
                    ->where('contratos.user_id = user_datos.id')
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(1);

                $query->andWhere(['contratos.id' => $subQuery]);
                $query->andWhere(['contratos.estatus' => Contratos::STATUS_ANULADO]);
            } else {
                // Show users with contracts in the specified status
                $query->andFilterWhere(['contratos.estatus' => $this->contrato_estatus]);
            }
        }

        // ============================================================
        // CRITICAL FILTER - Show only users with 3+ expired cuotas
        // ============================================================
        if ($this->critical_filter === 'true') {
            // Subquery to find users with 3+ expired cuotas
            $subQuery = Cuotas::find()
                ->select('contratos.user_id')
                ->innerJoin('contratos', 'contratos.id = cuotas.contrato_id')
                ->where(['cuotas.estatus' => 'vencida'])
                ->andWhere(['contratos.estatus' => Contratos::STATUS_SUSPENDIDO])
                ->groupBy('contratos.user_id')
                ->having(['>=', 'COUNT(cuotas.id)', 3]);

            $criticalUserIds = $subQuery->column();

            if (!empty($criticalUserIds)) {
                $query->andWhere(['user_datos.id' => $criticalUserIds]);
            } else {
                // No critical users found, return empty result
                $query->andWhere('1=0');
            }
        }

        // Text filters (ILike)
        $query->andFilterWhere(['ilike', 'user_id', $this->user_id])
            ->andFilterWhere(['ilike', 'user_datos.nombres', $this->nombres])
            ->andFilterWhere(['ilike', 'user_datos.sexo', $this->sexo])
            ->andFilterWhere(['ilike', 'user_datos.selfie', $this->selfie])
            ->andFilterWhere(['ilike', 'user_datos.telefono', $this->telefono])
            ->andFilterWhere(['ilike', 'user_datos.estado', $this->estado])
            ->andFilterWhere(['ilike', 'user_datos.role', $this->role])
            ->andFilterWhere(['ilike', 'user_datos.estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'user_datos.imagen_identificacion', $this->imagen_identificacion])
            ->andFilterWhere(['ilike', 'user_datos.qr', $this->qr])
            ->andFilterWhere(['ilike', 'user_datos.video', $this->video])
            ->andFilterWhere(['ilike', 'user_datos.ciudad', $this->ciudad])
            ->andFilterWhere(['ilike', 'user_datos.municipio', $this->municipio])
            ->andFilterWhere(['ilike', 'user_datos.parroquia', $this->parroquia])
            ->andFilterWhere(['ilike', 'user_datos.direccion', $this->direccion])
            ->andFilterWhere(['ilike', 'user_datos.codigoValidacion', $this->codigoValidacion])
            ->andFilterWhere(['ilike', 'user_datos.apellidos', $this->apellidos])
            ->andFilterWhere(['ilike', 'user_datos.email', $this->email])
            ->andFilterWhere(['ilike', 'user_datos.ver_cedula', $this->ver_cedula])
            ->andFilterWhere(['ilike', 'user_datos.ver_foto', $this->ver_foto])
            ->andFilterWhere(['ilike', 'user_datos.session_id', $this->session_id])
            ->andFilterWhere(['ilike', 'user_datos.tipo_cedula', $this->tipo_cedula])
            ->andFilterWhere(['ilike', 'user_datos.tipo_sangre', $this->tipo_sangre])
            ->andFilterWhere(['ilike', 'user_datos.estatus_solvente', $this->estatus_solvente])
            ->andFilterWhere(['ilike', 'rm_clinica.nombre', $this->clinica_nombre])
            ->andFilterWhere(['ilike', 'CAST(user_datos.cedula AS TEXT)', $this->cedula])
            ->andWhere(['is', 'user_datos.deleted_at', null]);

        return $dataProvider;
    }

    /**
     * Get summary statistics for affiliates
     * 
     * @param array $params Search parameters
     * @return array Summary data with totals and critical delinquents
     */
    public function getSummaryStatistics($params = [])
    {
        // Apply the same filtering as the main search
        $query = UserDatos::find();
        $this->applyFilters($query, $params);

        // Only include affiliates (not other roles)
        $query->andWhere(['user_datos.role' => 'afiliado']);
        $query->andWhere(['is', 'user_datos.deleted_at', null]);

        // Get total affiliates count
        $totalAfiliados = $query->count();

        // Get active contracts count (users with active contracts)
        $activeContractsQuery = clone $query;
        $activeContractsQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_ACTIVO])
            ->distinct('user_datos.id');
        $totalActivos = $activeContractsQuery->count();

        // Get suspended contracts count (users with suspended contracts)
        $suspendedContractsQuery = clone $query;
        $suspendedContractsQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_SUSPENDIDO])
            ->distinct('user_datos.id');
        $totalSuspendidos = $suspendedContractsQuery->count();

        // ============================================================
        // ADDED: Get registrados contracts count
        // ============================================================
        $registradosContractsQuery = clone $query;
        $registradosContractsQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_REGISTRADO])
            ->distinct('user_datos.id');
        $totalRegistrados = $registradosContractsQuery->count();

        // ============================================================
        // ADDED: Get anulados contracts count
        // ============================================================
        $anuladosContractsQuery = clone $query;
        $anuladosContractsQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_ANULADO])
            ->distinct('user_datos.id');
        $totalAnulados = $anuladosContractsQuery->count();

        // Get critical delinquents (users with 3+ overdue payments)
        $criticalQuery = clone $query;
        $criticalQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->innerJoin('cuotas', 'cuotas.contrato_id = contratos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_SUSPENDIDO])
            ->andWhere(['cuotas.estatus' => 'vencida'])
            ->groupBy('user_datos.id')
            ->having(['>=', 'COUNT(cuotas.id)', 3])
            ->distinct('user_datos.id');

        $criticalCount = $criticalQuery->count();

        // Get total overdue cuotas for critical users
        $criticalCuotasQuery = clone $query;
        $criticalCuotasQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->innerJoin('cuotas', 'cuotas.contrato_id = contratos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_SUSPENDIDO])
            ->andWhere(['cuotas.estatus' => 'vencida'])
            ->groupBy('user_datos.id')
            ->having(['>=', 'COUNT(cuotas.id)', 3]);

        $criticalUsers = $criticalCuotasQuery->select('user_datos.id')->column();
        $totalOverdueCuotas = 0;
        $averageOverdue = 0;

        if (!empty($criticalUsers)) {
            $overdueQuery = Cuotas::find()
                ->innerJoin('contratos', 'contratos.id = cuotas.contrato_id')
                ->where(['contratos.user_id' => $criticalUsers])
                ->andWhere(['contratos.estatus' => Contratos::STATUS_SUSPENDIDO])
                ->andWhere(['cuotas.estatus' => 'vencida']);

            $totalOverdueCuotas = $overdueQuery->count();
            $averageOverdue = $criticalCount > 0 ? round($totalOverdueCuotas / $criticalCount, 1) : 0;
        }

        return [
            'total_afiliados' => $totalAfiliados,
            'total_contratos_activos' => $totalActivos,
            'total_contratos_suspendidos' => $totalSuspendidos,
            'total_contratos_registrados' => $totalRegistrados,
            'total_contratos_anulados' => $totalAnulados,
            'critical_delinquency' => [
                'count' => $criticalCount,
                'total_vencidas_cuotas' => $totalOverdueCuotas,
                'average_vencidas' => $averageOverdue,
            ],
        ];
    }

    /**
     * Get top critical delinquents (users with 3+ overdue payments)
     * 
     * @param int $limit Maximum number of results
     * @param array $params Search parameters
     * @return array List of critical delinquents with details
     */
    public function getTopCriticalDelinquents($limit = 5, $params = [])
    {
        $query = UserDatos::find();
        $this->applyFilters($query, $params);

        // Only include affiliates
        $query->andWhere(['user_datos.role' => 'afiliado']);
        $query->andWhere(['is', 'user_datos.deleted_at', null]);

        // Join with contracts and cuotas
        $query->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->innerJoin('cuotas', 'cuotas.contrato_id = contratos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_SUSPENDIDO])
            ->andWhere(['cuotas.estatus' => 'vencida'])
            ->groupBy([
                'user_datos.id',
                'user_datos.nombres',
                'user_datos.apellidos',
                'user_datos.tipo_cedula',
                'user_datos.cedula',
                'rm_clinica.nombre',
            ])
            ->having(['>=', 'COUNT(cuotas.id)', 3])
            ->orderBy(['COUNT(cuotas.id)' => SORT_DESC])
            ->limit($limit);

        // Add joins for clinic name
        $query->leftJoin('rm_clinica', 'rm_clinica.id = user_datos.clinica_id');

        // Select the needed fields
        $query->select([
            'user_datos.id',
            'user_datos.nombres',
            'user_datos.apellidos',
            'user_datos.tipo_cedula',
            'user_datos.cedula',
            'rm_clinica.nombre as clinica_nombre',
            'COUNT(cuotas.id) as cuotas_vencidas',
            'SUM(cuotas.monto_usd) as total_adeudado'
        ]);

        $results = $query->asArray()->all();

        // Format the results
        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'id' => $row['id'],
                'nombre_completo' => $row['nombres'] . ' ' . $row['apellidos'],
                'cedula' => ($row['tipo_cedula'] ? $row['tipo_cedula'] . '-' : '') . $row['cedula'],
                'clinica' => $row['clinica_nombre'] ?? 'No asignada',
                'cuotas_vencidas' => (int)$row['cuotas_vencidas'],
                'total_adeudado' => (float)$row['total_adeudado'],
            ];
        }

        return $formatted;
    }

    /**
     * Apply filters to query (extracted for reuse)
     * 
     * @param \yii\db\ActiveQuery $query The query to apply filters to
     * @param array $params Search parameters
     */
    private function applyFilters(&$query, $params = [])
    {
        $rol = UserHelper::getMyRol();

        // Clinic access filtering
        if (UserHelper::hasClinicAccess()) {
            $clinicId = UserHelper::getMyClinicaId();
            if ($clinicId) {
                $query->andFilterWhere(['user_datos.clinica_id' => $clinicId]);
            }
        }

        // Asesor filtering
        if ($rol == "Asesor") {
            $asesorId = UserHelper::getAgenteFuerzaId();
            if ($asesorId) {
                $query->andWhere(['user_datos.asesor_id' => $asesorId]);
            } else {
                $userDatos = UserDatos::findOne(['user_login_id' => Yii::$app->user->id]);
                if ($userDatos) {
                    $query->andWhere(['user_datos.asesor_id' => $userDatos->id]);
                } else {
                    $query->andWhere('1=0');
                }
            }
        }

        // Agente filtering
        if ($rol == "Agente") {
            $agenteId = UserHelper::getAgenteId();
            if ($agenteId) {
                $condition = ['or'];
                $asesorPersonIds = AgenteFuerza::find()
                    ->where(['agente_id' => $agenteId])
                    ->select('idusuario')
                    ->distinct()
                    ->column();

                if (!empty($asesorPersonIds)) {
                    $allAgenteFuerzaIds = AgenteFuerza::find()
                        ->where(['idusuario' => $asesorPersonIds])
                        ->select('id')
                        ->column();
                    if (!empty($allAgenteFuerzaIds)) {
                        $condition[] = ['user_datos.asesor_id' => $allAgenteFuerzaIds];
                    }
                }
                $condition[] = ['user_datos.agencia_id' => $agenteId];
                $query->andWhere($condition);
            } else {
                $query->andWhere('1=0');
            }
        }

        // Apply any additional filters from params
        if (!empty($params['UserDatosSearch'])) {
            $searchParams = $params['UserDatosSearch'];

            if (!empty($searchParams['clinica_id'])) {
                $query->andFilterWhere(['user_datos.clinica_id' => $searchParams['clinica_id']]);
            }
            if (!empty($searchParams['user_datos_type_id'])) {
                $query->andFilterWhere(['user_datos.user_datos_type_id' => $searchParams['user_datos_type_id']]);
            }
            if (!empty($searchParams['estatus_solvente'])) {
                $query->andFilterWhere(['user_datos.estatus_solvente' => $searchParams['estatus_solvente']]);
            }
            if (!empty($searchParams['contrato_estatus'])) {
                // This will be handled by the main search logic
            }
            if (!empty($searchParams['critical_filter'])) {
                // This will be handled by the main search logic
            }
        }
    }
}
