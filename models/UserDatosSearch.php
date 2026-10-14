<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\UserDatos;
use Yii;
use app\components\UserHelper;
use DateTime;
use yii\db\Expression;

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
    public $critical_filter;

    /**
     * Unified search field.
     *
     * The user types anything here — name, last name, or both — and we
     * build a forgiving, accent-insensitive, token-based search across
     * the name and last-name columns only.
     */
    public $nombre_completo;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // ============================================================
            // INTEGER FIELDS
            // Note: 'cedula' is intentionally NOT here — it must accept
            // strings like "V-12345678" or "1234" for partial search.
            // ============================================================
            [[
                'id',
                'clinica_id',
                'plan_id',
                'contrato_id',
                'asesor_id',
                'user_login_id',
                'user_datos_type_id',
                'afiliado_corporativo_id',
                'consecutivo_menor',
                'agencia_id'
            ], 'integer'],

            // ============================================================
            // SAFE FIELDS (no validation, only mass-assignable)
            // ============================================================
            [[
                'created_at',
                'user_id',
                'nombres',
                'fechanac',
                'sexo',
                'selfie',
                'telefono',
                'estado',
                'role',
                'estatus',
                'imagen_identificacion',
                'qr',
                'video',
                'ciudad',
                'municipio',
                'parroquia',
                'direccion',
                'codigoValidacion',
                'apellidos',
                'email',
                'deleted_at',
                'updated_at',
                'ver_cedula',
                'ver_foto',
                'session_id',
                'tipo_cedula',
                'tipo_sangre',
                'estatus_solvente',
                'clinica_nombre',
                'contrato_estatus',
                'critical_filter',
                'nombre_completo',
                'cedula',           // ← string now, allows partial + formatted search
            ], 'safe'],

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
        $labels['critical_filter']  = 'Filtro Crítico';
        $labels['nombre_completo']  = 'Buscar Afiliado';
        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    // ============================================================
    // SMART TEXT SEARCH — NAME / LAST NAME ONLY
    // ============================================================

    /**
     * Search the name columns only:
     *   - nombres
     *   - apellidos
     *   - nombres || ' ' || apellidos (full name)
     *
     * Tokens are AND-ed. Accent- and case-insensitive. Partial matches.
     *
     * @param \yii\db\ActiveQuery $query
     * @param string              $term
     */
    private function applySmartTextSearch($query, $term)
    {
        if ($term === null || trim($term) === '') {
            return;
        }

        $term   = mb_strtolower(trim((string)$term), 'UTF-8');
        $tokens = preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($tokens)) {
            return;
        }

        // Reusable SQL fragments (built once, used per token)
        $sqlNombres = new Expression(
            "lower(public.immutable_unaccent(coalesce(user_datos.nombres, '')))"
        );
        $sqlApellidos = new Expression(
            "lower(public.immutable_unaccent(coalesce(user_datos.apellidos, '')))"
        );
        $sqlFullName = new Expression(
            "lower(public.immutable_unaccent(coalesce(user_datos.nombres, '') || ' ' || coalesce(user_datos.apellidos, '')))"
        );

        foreach ($tokens as $token) {
            // Escape LIKE wildcards
            $safeToken = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $token);
            $like      = '%' . $safeToken . '%';

            $query->andWhere([
                'or',
                ['like', $sqlNombres,   $like, false],
                ['like', $sqlApellidos, $like, false],
                ['like', $sqlFullName,  $like, false],
            ]);
        }
    }

    // ============================================================
    // SMART TEXT SEARCH — CÉDULA ONLY
    // ============================================================

    /**
     * Search ONLY the cédula field — accent/case-insensitive, partial match.
     *
     * Strips formatting so "V-12345678" and "12345678" behave the same.
     *
     * @param \yii\db\ActiveQuery $query
     * @param string              $term
     */
    private function applySmartTextSearchCedula($query, $term)
    {
        $term = trim((string)$term);
        if ($term === '') {
            return;
        }

        // Strip everything that isn't alphanumeric so "V-12.345.678"
        // becomes "V12345678" and matches cleanly.
        $clean = preg_replace('/[^0-9A-Za-z]/', '', $term);
        if ($clean === '') {
            return;
        }

        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $clean) . '%';

        $sqlCedula = new Expression(
            "public.immutable_unaccent(coalesce(user_datos.cedula::text, ''))"
        );

        $query->andWhere(['like', $sqlCedula, $like, false]);
    }

    // ============================================================
    // SMART TEXT SEARCH — EMAIL ONLY
    // ============================================================

    /**
     * Search ONLY the email field — case-insensitive, partial match.
     *
     * @param \yii\db\ActiveQuery $query
     * @param string              $term
     */
    private function applySmartTextSearchEmail($query, $term)
    {
        $term = mb_strtolower(trim((string)$term), 'UTF-8');
        if ($term === '') {
            return;
        }

        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';

        $sqlEmail = new Expression(
            "lower(coalesce(user_datos.email, ''))"
        );

        $query->andWhere(['like', $sqlEmail, $like, false]);
    }

    // ============================================================
    // SMART TEXT SEARCH — TELÉFONO ONLY
    // ============================================================

    /**
     * Search ONLY the teléfono field — case-insensitive, partial match.
     *
     * Strips non-digits so "+58 414-1234567" and "04141234567" behave
     * the same, and "0414" finds any phone starting with 0414.
     *
     * @param \yii\db\ActiveQuery $query
     * @param string              $term
     */
    private function applySmartTextSearchTelefono($query, $term)
    {
        $term = trim((string)$term);
        if ($term === '') {
            return;
        }

        // Keep only digits so partial numeric search works consistently
        $digits = preg_replace('/\D+/', '', $term);
        if ($digits === '') {
            return;
        }

        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $digits) . '%';

        $sqlTelefono = new Expression(
            "coalesce(user_datos.telefono, '')"
        );

        $query->andWhere(['like', $sqlTelefono, $like, false]);
    }

    // ============================================================
    // MAIN SEARCH
    // ============================================================

    /**
     * Creates data provider instance with search query applied
     *
     * @param array       $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $rol   = UserHelper::getMyRol();
        $query = UserDatos::find();

        // ============================================================
        // CLINIC FILTERING
        // ============================================================
        if (UserHelper::hasClinicAccess()) {
            $clinicId = UserHelper::getMyClinicaId();
            if ($clinicId) {
                $query->andFilterWhere(['user_datos.clinica_id' => $clinicId]);
            }
        }

        // ============================================================
        // ASESOR FILTERING
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
        // AGENTE FILTERING
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
        // SELECT + JOINS
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
            'clinica',
        ]);

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
            'contratos.estatus',
        ]);

        // ============================================================
        // DATA PROVIDER
        // ============================================================
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $dataProvider->sort->attributes['contrato_estatus'] = [
            'asc'  => ['contratos.estatus' => SORT_ASC],
            'desc' => ['contratos.estatus' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['user_datos_type_id'] = [
            'asc'  => ['user_datos_type.nombre' => SORT_ASC],
            'desc' => ['user_datos_type.nombre' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['afiliado_corporativo_id'] = [
            'asc'  => ['user_datos.afiliado_corporativo_id' => SORT_ASC],
            'desc' => ['user_datos.afiliado_corporativo_id' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['corporativo'] = [
            'asc'  => ['corporativos.nombre' => SORT_ASC],
            'desc' => ['corporativos.nombre' => SORT_DESC],
        ];

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // ============================================================
        // FILTERING — EXACT / STRUCTURED
        // ============================================================
        $query->andFilterWhere([
            'user_datos.id'                        => $this->id,
            'user_datos.paso'                      => $this->paso,
            'user_datos.clinica_id'                => $this->clinica_id,
            'user_datos.plan_id'                   => $this->plan_id,
            'user_datos.contrato_id'               => $this->contrato_id,
            'user_datos.asesor_id'                 => $this->asesor_id,
            'user_datos.updated_at'                => $this->updated_at,
            'user_datos.user_login_id'             => $this->user_login_id,
            'user_datos.user_datos_type_id'        => $this->user_datos_type_id,
            'user_datos.afiliado_corporativo_id'   => $this->afiliado_corporativo_id,
            'user_datos.consecutivo_menor'         => $this->consecutivo_menor,
            'user_datos.agencia_id'                => $this->agencia_id,
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
            $query->andFilterWhere([
                'between',
                'user_datos.fechanac',
                $dates[0] . ' 00:00:00',
                $dates[1] . ' 23:59:59',
            ]);
        }

        // ============================================================
        // CONTRACT STATUS FILTER
        // ============================================================
        if (!empty($this->contrato_estatus)) {
            if ($this->contrato_estatus === 'sin_contrato') {
                $query->andWhere(['contratos.id' => null]);
            } elseif ($this->contrato_estatus === 'anulado') {
                $subQuery = Contratos::find()
                    ->select('estatus')
                    ->where('contratos.user_id = user_datos.id')
                    ->orderBy(['id' => SORT_DESC])
                    ->limit(1);

                $query->andWhere(['contratos.id' => $subQuery]);
                $query->andWhere(['contratos.estatus' => Contratos::STATUS_ANULADO]);
            } else {
                $query->andFilterWhere(['contratos.estatus' => $this->contrato_estatus]);
            }
        }

        // ============================================================
        // CRITICAL FILTER
        // ============================================================
        if ($this->critical_filter === 'true') {
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
                $query->andWhere('1=0');
            }
        }

        // ============================================================
        // PROFESSIONAL TEXT SEARCH
        // Each grid filter column routes to its own dedicated method.
        // No cross-contamination between fields.
        // ============================================================

        // 1. Unified "Nombre Completo" search box → names + last names only
        if (!empty($this->nombre_completo)) {
            $this->applySmartTextSearch($query, $this->nombre_completo);
        }

        // 2. Legacy per-column name filters → also names + last names only
        if (!empty($this->nombres)) {
            $this->applySmartTextSearch($query, $this->nombres);
        }
        if (!empty($this->apellidos)) {
            $this->applySmartTextSearch($query, $this->apellidos);
        }

        // 3. Cédula column filter → searches ONLY the cedula field
        if (!empty($this->cedula)) {
            $this->applySmartTextSearchCedula($query, (string)$this->cedula);
        }

        // 4. Email column filter → searches ONLY the email field
        if (!empty($this->email)) {
            $this->applySmartTextSearchEmail($query, $this->email);
        }

        // 5. Teléfono column filter → searches ONLY the telefono field
        if (!empty($this->telefono)) {
            $this->applySmartTextSearchTelefono($query, $this->telefono);
        }

        // ============================================================
        // REMAINING "LOOSE" FILTERS
        // Note: telefono is NOT in this list anymore — it's handled
        //       exclusively by applySmartTextSearchTelefono() so the
        //       smart search isn't cancelled by a conflicting ilike.
        // ============================================================
        $query
            ->andFilterWhere(['ilike', 'user_datos.sexo',             $this->sexo])
            ->andFilterWhere(['ilike', 'user_datos.estado',           $this->estado])
            ->andFilterWhere(['ilike', 'user_datos.role',             $this->role])
            ->andFilterWhere(['ilike', 'user_datos.estatus',          $this->estatus])
            ->andFilterWhere(['ilike', 'user_datos.tipo_cedula',      $this->tipo_cedula])
            ->andFilterWhere(['ilike', 'user_datos.tipo_sangre',      $this->tipo_sangre])
            ->andFilterWhere(['ilike', 'user_datos.estatus_solvente', $this->estatus_solvente])
            ->andFilterWhere(['ilike', 'rm_clinica.nombre',           $this->clinica_nombre])
            ->andFilterWhere(['ilike', 'user_datos.direccion',        $this->direccion])
            ->andWhere(['is', 'user_datos.deleted_at', null]);

        return $dataProvider;
    }

    // ============================================================
    // SUMMARY STATISTICS
    // ============================================================

    /**
     * Get summary statistics for affiliates
     */
    public function getSummaryStatistics($params = [])
    {
        $query = UserDatos::find();
        $this->applyFilters($query, $params);

        $query->andWhere(['user_datos.role' => 'afiliado']);
        $query->andWhere(['is', 'user_datos.deleted_at', null]);

        $totalAfiliados = $query->count();

        $activeContractsQuery = clone $query;
        $activeContractsQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_ACTIVO])
            ->distinct('user_datos.id');
        $totalActivos = $activeContractsQuery->count();

        $suspendedContractsQuery = clone $query;
        $suspendedContractsQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_SUSPENDIDO])
            ->distinct('user_datos.id');
        $totalSuspendidos = $suspendedContractsQuery->count();

        $registradosContractsQuery = clone $query;
        $registradosContractsQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_REGISTRADO])
            ->distinct('user_datos.id');
        $totalRegistrados = $registradosContractsQuery->count();

        $anuladosContractsQuery = clone $query;
        $anuladosContractsQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_ANULADO])
            ->distinct('user_datos.id');
        $totalAnulados = $anuladosContractsQuery->count();

        // VENCIDOS — count affiliates whose CURRENT (most recent) contract is Vencido.
        // We deliberately use the same "latest contract" pattern as the grid filter
        // so the KPI card number matches what the user sees after clicking the card.
        $vencidosContractsQuery = clone $query;
        $latestVencidoSubquery = Contratos::find()
            ->select('contratos.id')
            ->where('contratos.user_id = user_datos.id')
            ->orderBy(['contratos.id' => SORT_DESC])
            ->limit(1);

        $vencidosContractsQuery
            ->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->andWhere(['contratos.id' => $latestVencidoSubquery])
            ->andWhere(['contratos.estatus' => Contratos::STATUS_VENCIDO])
            ->distinct('user_datos.id');
        $totalVencidos = $vencidosContractsQuery->count();

        $criticalQuery = clone $query;
        $criticalQuery->innerJoin('contratos', 'contratos.user_id = user_datos.id')
            ->innerJoin('cuotas', 'cuotas.contrato_id = contratos.id')
            ->andWhere(['contratos.estatus' => Contratos::STATUS_SUSPENDIDO])
            ->andWhere(['cuotas.estatus' => 'vencida'])
            ->groupBy('user_datos.id')
            ->having(['>=', 'COUNT(cuotas.id)', 3])
            ->distinct('user_datos.id');
        $criticalCount = $criticalQuery->count();

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
            'total_afiliados'                => $totalAfiliados,
            'total_contratos_activos'        => $totalActivos,
            'total_contratos_suspendidos'    => $totalSuspendidos,
            'total_contratos_registrados'    => $totalRegistrados,
            'total_contratos_anulados'       => $totalAnulados,
            'total_contratos_vencidos'       => $totalVencidos,
            'critical_delinquency' => [
                'count'                 => $criticalCount,
                'total_vencidas_cuotas' => $totalOverdueCuotas,
                'average_vencidas'      => $averageOverdue,
            ],
        ];
    }

    // ============================================================
    // TOP CRITICAL DELINQUENTS
    // ============================================================

    /**
     * Get top critical delinquents (users with 3+ overdue payments)
     */
    public function getTopCriticalDelinquents($limit = 5, $params = [])
    {
        $query = UserDatos::find();
        $this->applyFilters($query, $params);

        $query->andWhere(['user_datos.role' => 'afiliado']);
        $query->andWhere(['is', 'user_datos.deleted_at', null]);

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

        $query->leftJoin('rm_clinica', 'rm_clinica.id = user_datos.clinica_id');

        $query->select([
            'user_datos.id',
            'user_datos.nombres',
            'user_datos.apellidos',
            'user_datos.tipo_cedula',
            'user_datos.cedula',
            'rm_clinica.nombre as clinica_nombre',
            'COUNT(cuotas.id) as cuotas_vencidas',
            'SUM(cuotas.monto_usd) as total_adeudado',
        ]);

        $results = $query->asArray()->all();

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'id'              => $row['id'],
                'nombre_completo' => $row['nombres'] . ' ' . $row['apellidos'],
                'cedula'          => ($row['tipo_cedula'] ? $row['tipo_cedula'] . '-' : '') . $row['cedula'],
                'clinica'         => $row['clinica_nombre'] ?? 'No asignada',
                'cuotas_vencidas' => (int)$row['cuotas_vencidas'],
                'total_adeudado'  => (float)$row['total_adeudado'],
            ];
        }

        return $formatted;
    }

    // ============================================================
    // SHARED FILTER APPLICATION (for stats and top-delinquents)
    // ============================================================

    /**
     * Apply filters to query (extracted for reuse)
     */
    private function applyFilters(&$query, $params = [])
    {
        $rol = UserHelper::getMyRol();

        if (UserHelper::hasClinicAccess()) {
            $clinicId = UserHelper::getMyClinicaId();
            if ($clinicId) {
                $query->andFilterWhere(['user_datos.clinica_id' => $clinicId]);
            }
        }

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
        }
    }
}
