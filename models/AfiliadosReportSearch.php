<?php
// app/models/AfiliadosReportSearch.php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use Yii;
use app\components\UserHelper;

/**
 * AfiliadosReportSearch represents the model behind the report form.
 */
class AfiliadosReportSearch extends Model
{
    public $clinica_id;
    public $clinica_ids;
    public $user_datos_type_id;
    public $plan_id;
    public $date_from;
    public $date_to;
    public $estatus;
    public $meta_status;
    public $meta_min;
    public $meta_max;

    // Track whether user has restricted clinic access
    private $_hasClinicRestriction = false;
    private $_accessibleClinicaIds = null;

    /**
     * Constructor - Apply clinic restrictions based on user role
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        // Apply clinic restrictions for non-admin users
        $user = Yii::$app->user;

        // Check if user has clinic-level access (Gerente Clinica, etc.)
        // but is NOT superadmin or admin
        $isSuperAdmin = $user->can('superadmin') || $user->can('admin');

        if (!$isSuperAdmin && UserHelper::hasClinicAccess()) {
            $this->_hasClinicRestriction = true;
            $accessibleClinicas = UserHelper::getAccessibleClinicas();
            $this->_accessibleClinicaIds = \yii\helpers\ArrayHelper::getColumn($accessibleClinicas, 'id');
        } else {
            $this->_hasClinicRestriction = false;
            $this->_accessibleClinicaIds = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['clinica_id', 'user_datos_type_id', 'plan_id', 'meta_min', 'meta_max'], 'integer'],
            [['clinica_ids'], 'each', 'rule' => ['integer']],
            [['date_from', 'date_to', 'estatus', 'meta_status'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'clinica_id' => 'Clínica',
            'clinica_ids' => 'Clínicas',
            'user_datos_type_id' => 'Tipo de Afiliado',
            'plan_id' => 'Plan',
            'date_from' => 'Fecha Desde',
            'date_to' => 'Fecha Hasta',
            'estatus' => 'Estado',
            'meta_status' => 'Estado de Meta',
            'meta_min' => 'Meta Mínima',
            'meta_max' => 'Meta Máxima',
        ];
    }

    /**
     * Apply clinic restrictions to a query
     */
    private function applyClinicRestrictions(&$query, $alias = 'ud')
    {
        if ($this->_hasClinicRestriction && !empty($this->_accessibleClinicaIds)) {
            $query->andWhere(["{$alias}.clinica_id" => $this->_accessibleClinicaIds]);
        }
    }

    /**
     * Get filtered clinic IDs for queries (respects user access)
     * @return array|null Returns array of clinic IDs or null if no filter (all clinics)
     */
    private function getFilteredClinicaIds()
    {
        // If user has clinic restriction, always limit to their clinics
        if ($this->_hasClinicRestriction) {
            return !empty($this->_accessibleClinicaIds) ? $this->_accessibleClinicaIds : null;
        }

        // For admin/superadmin, use the selected filters if any
        if (!empty($this->clinica_ids) && is_array($this->clinica_ids)) {
            // Filter out empty values
            $filtered = array_filter($this->clinica_ids, function ($id) {
                return !empty($id) && is_numeric($id);
            });
            return !empty($filtered) ? $filtered : null;
        }

        if (!empty($this->clinica_id) && is_numeric($this->clinica_id)) {
            return [(int)$this->clinica_id];
        }

        // No filter, return null (meaning all clinics for admins)
        return null;
    }

    /**
     * Helper method to build clinic ID placeholder for SQL
     * @param array $clinicaIds
     * @return string
     */
    private function buildClinicaPlaceholders($clinicaIds, &$sqlParams, $prefix = 'clinica_id')
    {
        if (empty($clinicaIds)) {
            return '';
        }

        $placeholders = [];
        foreach ($clinicaIds as $index => $clinicaId) {
            $paramName = ":{$prefix}_{$index}";
            $placeholders[] = $paramName;
            $sqlParams[$paramName] = (int)$clinicaId;
        }
        return implode(',', $placeholders);
    }

    /**
     * Helper method to group plan names by category
     */
    public function groupPlanName($planName)
    {
        $planName = strtolower(trim($planName));

        if (strpos($planName, 'bronce') !== false) {
            return 'Bronce';
        } elseif (strpos($planName, 'plata') !== false) {
            return 'Plata';
        } elseif (strpos($planName, 'oro') !== false) {
            return 'Oro';
        } elseif (strpos($planName, 'esmeralda') !== false) {
            return 'Esmeralda';
        } elseif (strpos($planName, 'diamante') !== false) {
            return 'Diamante';
        } elseif (strpos($planName, 'basico') !== false || strpos($planName, 'básico') !== false) {
            return 'Básico';
        } elseif (strpos($planName, 'premium') !== false) {
            return 'Premium';
        } else {
            return ucfirst($planName);
        }
    }

    /**
     * Creates data provider instance with search query applied
     */
    public function search($params)
    {
        $query = UserDatos::find()
            ->alias('ud')
            ->innerJoinWith(['clinica'])
            ->leftJoin('user_datos_type udt', 'ud.user_datos_type_id = udt.id')
            ->leftJoin('planes', 'ud.plan_id = planes.id')
            ->where(['ud.role' => 'afiliado'])
            ->andWhere(['IS', 'ud.deleted_at', null]);

        // Apply clinic restrictions based on user role
        $this->applyClinicRestrictions($query);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => ['pageSize' => 50],
        ]);

        $query->orderBy(['rm_clinica.nombre' => SORT_ASC, 'ud.nombres' => SORT_ASC, 'ud.apellidos' => SORT_ASC]);
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Handle multiple clinics - but respect user restrictions
        $filteredClinicaIds = $this->getFilteredClinicaIds();
        if ($filteredClinicaIds !== null && !empty($filteredClinicaIds)) {
            $query->andWhere(['ud.clinica_id' => $filteredClinicaIds]);
        }

        if (!empty($this->user_datos_type_id)) {
            $query->andWhere(['ud.user_datos_type_id' => $this->user_datos_type_id]);
        }

        if (!empty($this->plan_id)) {
            $query->andWhere(['ud.plan_id' => $this->plan_id]);
        }

        if (!empty($this->estatus)) {
            $query->andWhere(['ud.estatus' => $this->estatus]);
        }

        if (!empty($this->date_from)) {
            $query->andWhere(['>=', 'ud.created_at', $this->date_from . ' 00:00:00']);
        }

        if (!empty($this->date_to)) {
            $query->andWhere(['<=', 'ud.created_at', $this->date_to . ' 23:59:59']);
        }

        // Meta status filtering
        if ($this->meta_status == 'con_meta') {
            $query->andWhere(['>', 'rm_clinica.meta', 0]);
        } elseif ($this->meta_status == 'sin_meta') {
            $query->andWhere(['or', ['rm_clinica.meta' => null], ['rm_clinica.meta' => 0]]);
        }

        // Meta range filtering
        if ($this->meta_min !== null && $this->meta_min !== '') {
            $query->andWhere(['>=', 'rm_clinica.meta', $this->meta_min]);
        }

        if ($this->meta_max !== null && $this->meta_max !== '') {
            $query->andWhere(['<=', 'rm_clinica.meta', $this->meta_max]);
        }

        return $dataProvider;
    }

    /**
     * Get all affiliates for export
     */
    public function getAllAffiliates($params)
    {
        $this->load($params);

        $query = UserDatos::find()
            ->alias('ud')
            ->innerJoinWith(['clinica'])
            ->leftJoin('user_datos_type udt', 'ud.user_datos_type_id = udt.id')
            ->leftJoin('planes', 'ud.plan_id = planes.id')
            ->where(['ud.role' => 'afiliado'])
            ->andWhere(['IS', 'ud.deleted_at', null])
            ->orderBy(['rm_clinica.nombre' => SORT_ASC, 'ud.nombres' => SORT_ASC, 'ud.apellidos' => SORT_ASC]);

        // Apply clinic restrictions based on user role
        $this->applyClinicRestrictions($query);

        // Handle multiple clinics - but respect user restrictions
        $filteredClinicaIds = $this->getFilteredClinicaIds();
        if ($filteredClinicaIds !== null && !empty($filteredClinicaIds)) {
            $query->andWhere(['ud.clinica_id' => $filteredClinicaIds]);
        }

        if (!empty($this->user_datos_type_id)) {
            $query->andWhere(['ud.user_datos_type_id' => $this->user_datos_type_id]);
        }

        if (!empty($this->plan_id)) {
            $query->andWhere(['ud.plan_id' => $this->plan_id]);
        }

        if (!empty($this->estatus)) {
            $query->andWhere(['ud.estatus' => $this->estatus]);
        }

        if (!empty($this->date_from)) {
            $query->andWhere(['>=', 'ud.created_at', $this->date_from . ' 00:00:00']);
        }

        if (!empty($this->date_to)) {
            $query->andWhere(['<=', 'ud.created_at', $this->date_to . ' 23:59:59']);
        }

        // Meta status filtering
        if ($this->meta_status == 'con_meta') {
            $query->andWhere(['>', 'rm_clinica.meta', 0]);
        } elseif ($this->meta_status == 'sin_meta') {
            $query->andWhere(['or', ['rm_clinica.meta' => null], ['rm_clinica.meta' => 0]]);
        }

        if ($this->meta_min !== null && $this->meta_min !== '') {
            $query->andWhere(['>=', 'rm_clinica.meta', $this->meta_min]);
        }

        if ($this->meta_max !== null && $this->meta_max !== '') {
            $query->andWhere(['<=', 'rm_clinica.meta', $this->meta_max]);
        }

        return $query->all();
    }

    /**
     * Get summary by clinic with counts (including meta information)
     * NOW INCLUDES CLINICS WITH ZERO AFFILIATES
     * FIXED: Changed 'suspendido' to 'Suspendido' for contract status
     */
    public function getSummaryByClinic($params = [])
    {
        $this->load($params);

        // First, get all clinics that match the filters
        $clinicasQuery = RmClinica::find()
            ->select([
                'c.id as clinica_id',
                'c.nombre as clinica_nombre',
                'c.rif as clinica_rif',
                'c.meta as clinica_meta'
            ])
            ->from('rm_clinica c')
            ->where(['c.deleted_at' => null]);

        // Apply clinic restrictions based on user role
        if ($this->_hasClinicRestriction && !empty($this->_accessibleClinicaIds)) {
            $clinicasQuery->andWhere(['c.id' => $this->_accessibleClinicaIds]);
        }
        // Apply clinic filters (only for users without restrictions)
        else {
            $filteredClinicaIds = $this->getFilteredClinicaIds();
            if ($filteredClinicaIds !== null && !empty($filteredClinicaIds)) {
                $clinicasQuery->andWhere(['c.id' => $filteredClinicaIds]);
            }
        }

        // Apply meta status filtering to clinics
        if ($this->meta_status == 'con_meta') {
            $clinicasQuery->andWhere(['>', 'c.meta', 0]);
        } elseif ($this->meta_status == 'sin_meta') {
            $clinicasQuery->andWhere(['or', ['c.meta' => null], ['c.meta' => 0]]);
        }

        // Apply meta range filtering
        if ($this->meta_min !== null && $this->meta_min !== '') {
            $clinicasQuery->andWhere(['>=', 'c.meta', (int)$this->meta_min]);
        }
        if ($this->meta_max !== null && $this->meta_max !== '') {
            $clinicasQuery->andWhere(['<=', 'c.meta', (int)$this->meta_max]);
        }

        $allClinicas = $clinicasQuery->asArray()->all();

        if (empty($allClinicas)) {
            return [];
        }

        // Now get the affiliate counts for these clinics
        $clinicaIds = array_column($allClinicas, 'clinica_id');

        // Build placeholders for clinic IDs
        $placeholders = [];
        $sqlParams = [];
        foreach ($clinicaIds as $index => $clinicaId) {
            $placeholder = ":clinica_id_{$index}";
            $placeholders[] = $placeholder;
            $sqlParams[$placeholder] = $clinicaId;
        }
        $clinicaInClause = implode(',', $placeholders);

        $sql = "
        SELECT 
            c.id as clinica_id,
            COUNT(DISTINCT ud.id) as total_afiliados,
            COUNT(DISTINCT CASE WHEN ud.user_datos_type_id = 1 THEN ud.id END) as tipo_individual,
            COUNT(DISTINCT CASE WHEN ud.user_datos_type_id = 2 THEN ud.id END) as tipo_corporativo,
            SUM(CASE WHEN ud.estatus = 'Registrado' THEN 1 ELSE 0 END) as activos,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Registrado' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_registrados,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Activo' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_activos,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Anulado' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_anulados,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Vencido' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_vencidos,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Pendiente' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_pendientes,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Suspendido' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_suspendidos,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Creado Manual' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_creado_manual,
            COUNT(DISTINCT contr.id) as total_contratos,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Registrado' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_registrados,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Activo' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_activos,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Anulado' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_anulados,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Vencido' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_vencidos,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Pendiente' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_pendientes,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Suspendido' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_suspendidos,
            COUNT(DISTINCT CASE WHEN contr.estatus = 'Creado Manual' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_creado_manual
        FROM rm_clinica c
        LEFT JOIN user_datos ud ON ud.clinica_id = c.id 
            AND ud.role = 'afiliado' 
            AND ud.deleted_at IS NULL
        LEFT JOIN contratos contr ON ud.id = contr.user_id AND contr.deleted_at IS NULL
        WHERE c.id IN ({$clinicaInClause})
    ";

        // Add additional filters as AND conditions in the WHERE clause (not in JOIN)
        $whereConditions = [];

        if (!empty($this->user_datos_type_id)) {
            $whereConditions[] = "ud.user_datos_type_id = :user_datos_type_id";
            $sqlParams[':user_datos_type_id'] = $this->user_datos_type_id;
        }

        if (!empty($this->plan_id)) {
            $whereConditions[] = "ud.plan_id = :plan_id";
            $sqlParams[':plan_id'] = $this->plan_id;
        }

        if (!empty($this->estatus)) {
            $whereConditions[] = "ud.estatus = :estatus";
            $sqlParams[':estatus'] = $this->estatus;
        }

        if (!empty($this->date_from)) {
            $whereConditions[] = "ud.created_at >= :date_from";
            $sqlParams[':date_from'] = $this->date_from . ' 00:00:00';
        }

        if (!empty($this->date_to)) {
            $whereConditions[] = "ud.created_at <= :date_to";
            $sqlParams[':date_to'] = $this->date_to . ' 23:59:59';
        }

        // Add WHERE conditions if any
        if (!empty($whereConditions)) {
            $sql .= " AND (" . implode(' AND ', $whereConditions) . ")";
        }

        $sql .= " GROUP BY c.id ORDER BY total_afiliados DESC, c.nombre ASC";

        $connection = Yii::$app->db;
        $command = $connection->createCommand($sql, $sqlParams);
        $countData = $command->queryAll();

        // Create a map of clinic_id to count data
        $countMap = [];
        foreach ($countData as $data) {
            $countMap[$data['clinica_id']] = $data;
        }

        // Merge all clinics with their counts (including zeros)
        $result = [];
        foreach ($allClinicas as $clinica) {
            $clinicaId = $clinica['clinica_id'];
            $counts = $countMap[$clinicaId] ?? [
                'total_afiliados' => 0,
                'tipo_individual' => 0,
                'tipo_corporativo' => 0,
                'activos' => 0,
                'contratos_registrados' => 0,
                'contratos_activos' => 0,
                'contratos_anulados' => 0,
                'contratos_vencidos' => 0,
                'contratos_pendientes' => 0,
                'contratos_suspendidos' => 0,
                'contratos_creado_manual' => 0,
                'total_contratos' => 0,
                'total_contratos_registrados' => 0,
                'total_contratos_activos' => 0,
                'total_contratos_anulados' => 0,
                'total_contratos_vencidos' => 0,
                'total_contratos_pendientes' => 0,
                'total_contratos_suspendidos' => 0,
                'total_contratos_creado_manual' => 0,
            ];

            $result[] = [
                'clinica_id' => $clinicaId,
                'clinica_nombre' => $clinica['clinica_nombre'],
                'clinica_rif' => $clinica['clinica_rif'],
                'clinica_meta' => $clinica['clinica_meta'],
                'total_afiliados' => (int)$counts['total_afiliados'],
                'tipo_individual' => (int)$counts['tipo_individual'],
                'tipo_corporativo' => (int)$counts['tipo_corporativo'],
                'activos' => (int)$counts['activos'],
                'contratos_registrados' => (int)$counts['contratos_registrados'],
                'contratos_activos' => (int)$counts['contratos_activos'],
                'contratos_anulados' => (int)$counts['contratos_anulados'],
                'contratos_vencidos' => (int)$counts['contratos_vencidos'],
                'contratos_pendientes' => (int)$counts['contratos_pendientes'],
                'contratos_suspendidos' => (int)$counts['contratos_suspendidos'],
                'contratos_creado_manual' => (int)$counts['contratos_creado_manual'],
                'total_contratos' => (int)$counts['total_contratos'],
                'total_contratos_registrados' => (int)$counts['total_contratos_registrados'],
                'total_contratos_activos' => (int)$counts['total_contratos_activos'],
                'total_contratos_anulados' => (int)$counts['total_contratos_anulados'],
                'total_contratos_vencidos' => (int)$counts['total_contratos_vencidos'],
                'total_contratos_pendientes' => (int)$counts['total_contratos_pendientes'],
                'total_contratos_suspendidos' => (int)$counts['total_contratos_suspendidos'],
                'total_contratos_creado_manual' => (int)$counts['total_contratos_creado_manual'],
            ];
        }

        // Return WITHOUT sorting here - sorting will be done in the view as needed
        return $result;
    }

    /**
     * Get clinics with their meta goals and actual affiliate counts
     * @param array $params
     * @return array
     */
    public function getClinicasConMeta($params = [])
    {
        $this->load($params);

        $query = RmClinica::find()
            ->select([
                'rm_clinica.id',
                'rm_clinica.nombre',
                'rm_clinica.meta',
                'COUNT(DISTINCT user_datos.id) as total_afiliados'
            ])
            ->leftJoin('user_datos', 'user_datos.clinica_id = rm_clinica.id AND user_datos.role = :role AND user_datos.deleted_at IS NULL', [':role' => 'afiliado'])
            ->where(['rm_clinica.deleted_at' => null])
            ->groupBy('rm_clinica.id, rm_clinica.nombre, rm_clinica.meta')
            ->orderBy(['rm_clinica.nombre' => SORT_ASC]);

        // Apply clinic restriction based on user role
        if ($this->_hasClinicRestriction && !empty($this->_accessibleClinicaIds)) {
            $query->andWhere(['rm_clinica.id' => $this->_accessibleClinicaIds]);
        }
        // Apply clinic filter if needed (only for users without restrictions)
        else {
            $filteredClinicaIds = $this->getFilteredClinicaIds();
            if ($filteredClinicaIds !== null && !empty($filteredClinicaIds)) {
                $query->andWhere(['rm_clinica.id' => $filteredClinicaIds]);
            }
        }

        // Apply meta status filter
        if ($this->meta_status == 'con_meta') {
            $query->andWhere(['>', 'rm_clinica.meta', 0]);
        } elseif ($this->meta_status == 'sin_meta') {
            $query->andWhere(['or', ['rm_clinica.meta' => null], ['rm_clinica.meta' => 0]]);
        }

        // Apply meta range filter
        if ($this->meta_min !== null && $this->meta_min !== '') {
            $query->andWhere(['>=', 'rm_clinica.meta', $this->meta_min]);
        }

        if ($this->meta_max !== null && $this->meta_max !== '') {
            $query->andWhere(['<=', 'rm_clinica.meta', $this->meta_max]);
        }

        return $query->asArray()->all();
    }

    /**
     * Get meta goal achievement summary
     * @param array $params
     * @return array
     */
    public function getMetaAchievementSummary($params = [])
    {
        $clinicasConMeta = $this->getClinicasConMeta($params);

        $summary = [
            'clinicas_con_meta' => 0,
            'clinicas_sin_meta' => 0,
            'clinicas_que_alcanzaron_meta' => 0,
            'clinicas_cerca_meta' => 0,
            'clinicas_lejos_meta' => 0,
            'total_meta_objetivo' => 0,
            'total_afiliados_actual' => 0,
            'porcentaje_global_cumplimiento' => 0,
            'detalle_por_clinica' => []
        ];

        foreach ($clinicasConMeta as $clinica) {
            $meta = (int)($clinica['meta'] ?? 0);
            $totalAfiliados = (int)$clinica['total_afiliados'];

            $summary['total_meta_objetivo'] += $meta;
            $summary['total_afiliados_actual'] += $totalAfiliados;

            if ($meta > 0) {
                $summary['clinicas_con_meta']++;

                $porcentaje = $totalAfiliados > 0 ? ($totalAfiliados / $meta) * 100 : 0;

                $clinicaData = [
                    'id' => $clinica['id'],
                    'nombre' => $clinica['nombre'],
                    'meta' => $meta,
                    'total_afiliados' => $totalAfiliados,
                    'porcentaje' => round($porcentaje, 1),
                    'faltante' => max(0, $meta - $totalAfiliados),
                    'excedente' => max(0, $totalAfiliados - $meta),
                    'estado' => $porcentaje >= 100 ? 'alcanzada' : ($porcentaje >= 75 ? 'cerca' : 'pendiente')
                ];

                if ($porcentaje >= 100) {
                    $summary['clinicas_que_alcanzaron_meta']++;
                } elseif ($porcentaje >= 75) {
                    $summary['clinicas_cerca_meta']++;
                } else {
                    $summary['clinicas_lejos_meta']++;
                }

                $summary['detalle_por_clinica'][] = $clinicaData;
            } else {
                $summary['clinicas_sin_meta']++;
            }
        }

        // Calculate global percentage
        if ($summary['total_meta_objetivo'] > 0) {
            $summary['porcentaje_global_cumplimiento'] = round(
                ($summary['total_afiliados_actual'] / $summary['total_meta_objetivo']) * 100,
                1
            );
        }

        return $summary;
    }

    /**
     * Get summary by plan with grouping by category
     */
    public function getSummaryByPlan($params = [])
    {
        $this->load($params);

        $sql = "
            SELECT 
                p.id as plan_id,
                p.nombre as plan_nombre,
                p.precio as plan_precio,
                COUNT(ud.id) as total_afiliados,
                SUM(CASE WHEN ud.user_datos_type_id = 1 THEN 1 ELSE 0 END) as tipo_individual,
                SUM(CASE WHEN ud.user_datos_type_id = 2 THEN 1 ELSE 0 END) as tipo_corporativo
            FROM user_datos ud
            INNER JOIN planes p ON ud.plan_id = p.id
            INNER JOIN rm_clinica c ON ud.clinica_id = c.id
            WHERE ud.role = 'afiliado'
                AND ud.deleted_at IS NULL
                AND p.id IS NOT NULL
        ";

        $sqlParams = [];

        // Apply clinic restrictions based on user role
        if ($this->_hasClinicRestriction && !empty($this->_accessibleClinicaIds)) {
            $placeholders = $this->buildClinicaPlaceholders($this->_accessibleClinicaIds, $sqlParams, 'restricted_clinica_id');
            if ($placeholders) {
                $sql .= " AND ud.clinica_id IN (" . $placeholders . ")";
            }
        }
        // Handle user-selected clinic filters (only for users without restrictions)
        elseif (!$this->_hasClinicRestriction) {
            $filteredClinicaIds = $this->getFilteredClinicaIds();
            if ($filteredClinicaIds !== null && !empty($filteredClinicaIds)) {
                $placeholders = $this->buildClinicaPlaceholders($filteredClinicaIds, $sqlParams, 'clinica_id');
                if ($placeholders) {
                    $sql .= " AND ud.clinica_id IN (" . $placeholders . ")";
                }
            }
        }

        if (!empty($this->user_datos_type_id)) {
            $sql .= " AND ud.user_datos_type_id = :user_datos_type_id";
            $sqlParams[':user_datos_type_id'] = $this->user_datos_type_id;
        }

        if (!empty($this->plan_id)) {
            $sql .= " AND ud.plan_id = :plan_id";
            $sqlParams[':plan_id'] = $this->plan_id;
        }

        if (!empty($this->estatus)) {
            $sql .= " AND ud.estatus = :estatus";
            $sqlParams[':estatus'] = $this->estatus;
        }

        if (!empty($this->date_from)) {
            $sql .= " AND ud.created_at >= :date_from";
            $sqlParams[':date_from'] = $this->date_from . ' 00:00:00';
        }

        if (!empty($this->date_to)) {
            $sql .= " AND ud.created_at <= :date_to";
            $sqlParams[':date_to'] = $this->date_to . ' 23:59:59';
        }

        // Meta status filtering
        if ($this->meta_status == 'con_meta') {
            $sql .= " AND c.meta > 0";
        } elseif ($this->meta_status == 'sin_meta') {
            $sql .= " AND (c.meta IS NULL OR c.meta = 0)";
        }

        $sql .= " GROUP BY p.id, p.nombre, p.precio ORDER BY total_afiliados DESC";

        $connection = Yii::$app->db;
        $command = $connection->createCommand($sql, $sqlParams);
        $results = $command->queryAll();

        // Group results by plan category
        $groupedPlans = [];
        foreach ($results as $plan) {
            $category = $this->groupPlanName($plan['plan_nombre']);

            if (!isset($groupedPlans[$category])) {
                $groupedPlans[$category] = [
                    'plan_category' => $category,
                    'total_afiliados' => 0,
                    'tipo_individual' => 0,
                    'tipo_corporativo' => 0,
                    'plans' => []
                ];
            }

            $groupedPlans[$category]['total_afiliados'] += $plan['total_afiliados'];
            $groupedPlans[$category]['tipo_individual'] += $plan['tipo_individual'];
            $groupedPlans[$category]['tipo_corporativo'] += $plan['tipo_corporativo'];
            $groupedPlans[$category]['plans'][] = $plan;
        }

        // Convert to array and sort by total_afiliados descending
        $groupedPlans = array_values($groupedPlans);
        usort($groupedPlans, function ($a, $b) {
            return $b['total_afiliados'] - $a['total_afiliados'];
        });

        return $groupedPlans;
    }

    /**
     * Get timeline data for chart (monthly registrations)
     */
    public function getTimelineData($params = [])
    {
        $this->load($params);

        $sql = "
            SELECT 
                DATE_TRUNC('month', ud.created_at) as month,
                COUNT(*) as total,
                SUM(CASE WHEN ud.user_datos_type_id = 1 THEN 1 ELSE 0 END) as individual,
                SUM(CASE WHEN ud.user_datos_type_id = 2 THEN 1 ELSE 0 END) as corporativo
            FROM user_datos ud
            INNER JOIN rm_clinica c ON ud.clinica_id = c.id
            WHERE ud.role = 'afiliado'
                AND ud.deleted_at IS NULL
                AND ud.created_at >= :date_limit
        ";

        $sqlParams = [':date_limit' => date('Y-m-d', strtotime('-12 months'))];

        // Apply clinic restrictions based on user role
        if ($this->_hasClinicRestriction && !empty($this->_accessibleClinicaIds)) {
            $placeholders = $this->buildClinicaPlaceholders($this->_accessibleClinicaIds, $sqlParams, 'restricted_clinica_id');
            if ($placeholders) {
                $sql .= " AND ud.clinica_id IN (" . $placeholders . ")";
            }
        }
        // Handle user-selected clinic filters (only for users without restrictions)
        elseif (!$this->_hasClinicRestriction) {
            $filteredClinicaIds = $this->getFilteredClinicaIds();
            if ($filteredClinicaIds !== null && !empty($filteredClinicaIds)) {
                $placeholders = $this->buildClinicaPlaceholders($filteredClinicaIds, $sqlParams, 'clinica_id');
                if ($placeholders) {
                    $sql .= " AND ud.clinica_id IN (" . $placeholders . ")";
                }
            }
        }

        // Meta status filtering
        if ($this->meta_status == 'con_meta') {
            $sql .= " AND c.meta > 0";
        } elseif ($this->meta_status == 'sin_meta') {
            $sql .= " AND (c.meta IS NULL OR c.meta = 0)";
        }

        $sql .= " GROUP BY DATE_TRUNC('month', ud.created_at) ORDER BY month ASC";

        $connection = Yii::$app->db;
        $command = $connection->createCommand($sql, $sqlParams);
        $results = $command->queryAll();

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'month' => date('M Y', strtotime($row['month'])),
                'total' => (int)$row['total'],
                'individual' => (int)$row['individual'],
                'corporativo' => (int)$row['corporativo'],
            ];
        }

        return $data;
    }

    /**
     * Get top clinics by active affiliates count (contratos_activos)
     * MODIFIED: Now sorts by active affiliates instead of total affiliates
     */
    public function getTopClinics($limit = 10, $params = [])
    {
        $summary = $this->getSummaryByClinic($params);

        // Filter out clinics with zero active affiliates for top clinics
        $nonZeroClinics = array_filter($summary, function ($clinic) {
            return ($clinic['contratos_activos'] ?? 0) > 0;
        });

        // Sort by contratos_activos descending
        usort($nonZeroClinics, function ($a, $b) {
            return ($b['contratos_activos'] ?? 0) - ($a['contratos_activos'] ?? 0);
        });

        return array_slice(array_values($nonZeroClinics), 0, $limit);
    }

    /**
     * Get totals for KPI cards (including meta totals)
     */
    public function getTotals($params = [])
    {
        $summary = $this->getSummaryByClinic($params);
        $metaSummary = $this->getMetaAchievementSummary($params);

        return [
            // User counts
            'total_afiliados' => array_sum(array_column($summary, 'total_afiliados')),
            'total_individual' => array_sum(array_column($summary, 'tipo_individual')),
            'total_corporativo' => array_sum(array_column($summary, 'tipo_corporativo')),
            'total_activos' => array_sum(array_column($summary, 'activos')),

            // USERS with contracts by status
            'total_contratos_registrados' => array_sum(array_column($summary, 'contratos_registrados')),
            'total_contratos_activos' => array_sum(array_column($summary, 'contratos_activos')),
            'total_contratos_anulados' => array_sum(array_column($summary, 'contratos_anulados')),
            'total_contratos_vencidos' => array_sum(array_column($summary, 'contratos_vencidos')),
            'total_contratos_pendientes' => array_sum(array_column($summary, 'contratos_pendientes')),
            'total_contratos_suspendidos' => array_sum(array_column($summary, 'contratos_suspendidos')),
            'total_contratos_creado_manual' => array_sum(array_column($summary, 'contratos_creado_manual')),

            // CONTRACT counts (for reference)
            'total_contratos' => array_sum(array_column($summary, 'total_contratos')),
            'total_contratos_registrados_count' => array_sum(array_column($summary, 'total_contratos_registrados')),
            'total_contratos_activos_count' => array_sum(array_column($summary, 'total_contratos_activos')),
            'total_contratos_anulados_count' => array_sum(array_column($summary, 'total_contratos_anulados')),
            'total_contratos_vencidos_count' => array_sum(array_column($summary, 'total_contratos_vencidos')),
            'total_contratos_pendientes_count' => array_sum(array_column($summary, 'total_contratos_pendientes')),
            'total_contratos_suspendidos_count' => array_sum(array_column($summary, 'total_contratos_suspendidos')),
            'total_contratos_creado_manual_count' => array_sum(array_column($summary, 'total_contratos_creado_manual')),

            'total_clinicas' => count($summary),

            // Meta totals
            'meta' => [
                'clinicas_con_meta' => $metaSummary['clinicas_con_meta'],
                'clinicas_sin_meta' => $metaSummary['clinicas_sin_meta'],
                'clinicas_que_alcanzaron_meta' => $metaSummary['clinicas_que_alcanzaron_meta'],
                'clinicas_cerca_meta' => $metaSummary['clinicas_cerca_meta'],
                'clinicas_lejos_meta' => $metaSummary['clinicas_lejos_meta'],
                'total_meta_objetivo' => $metaSummary['total_meta_objetivo'],
                'total_afiliados_actual' => $metaSummary['total_afiliados_actual'],
                'porcentaje_global_cumplimiento' => $metaSummary['porcentaje_global_cumplimiento'],
                'detalle_por_clinica' => $metaSummary['detalle_por_clinica'],
            ],
        ];
    }
}
