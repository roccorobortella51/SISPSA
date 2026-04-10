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

    // Track whether user has restricted clinic access
    private $_hasClinicRestriction = false;
    private $_accessibleClinicaIds = null;

    /**
     * Constructor - determine clinic access restrictions
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        // Check if user has clinic-level access
        if (UserHelper::hasClinicAccess()) {
            $this->_hasClinicRestriction = true;
            $this->_accessibleClinicaIds = [UserHelper::getMyClinicaId()];
        } elseif (!Yii::$app->user->can('superadmin') && !Yii::$app->user->can('admin')) {
            // For other roles, also restrict to their assigned clinics
            $this->_hasClinicRestriction = true;
            $this->_accessibleClinicaIds = UserHelper::getMyClinicaId() ? [UserHelper::getMyClinicaId()] : [];
        } else {
            // Superadmin and admin have no restrictions
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
            [['clinica_id', 'user_datos_type_id', 'plan_id'], 'integer'],
            [['clinica_ids'], 'each', 'rule' => ['integer']],
            [['date_from', 'date_to', 'estatus'], 'safe'],
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
     */
    private function getFilteredClinicaIds()
    {
        // If user has clinic restriction, always limit to their clinics
        if ($this->_hasClinicRestriction) {
            return $this->_accessibleClinicaIds;
        }

        // For admin/superadmin, use the selected filters if any
        if (!empty($this->clinica_ids) && is_array($this->clinica_ids)) {
            return $this->clinica_ids;
        }

        if (!empty($this->clinica_id)) {
            return [$this->clinica_id];
        }

        // No filter, return null (meaning all clinics)
        return null;
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
        if ($filteredClinicaIds !== null) {
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
        if ($filteredClinicaIds !== null) {
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

        return $query->all();
    }

    /**
     * Get summary by clinic with counts
     */
    public function getSummaryByClinic($params = [])
    {
        $this->load($params);

        $sql = "
            SELECT 
                c.id as clinica_id,
                c.nombre as clinica_nombre,
                c.rif as clinica_rif,
                COUNT(DISTINCT ud.id) as total_afiliados,
                COUNT(DISTINCT CASE WHEN ud.user_datos_type_id = 1 THEN ud.id END) as tipo_individual,
                COUNT(DISTINCT CASE WHEN ud.user_datos_type_id = 2 THEN ud.id END) as tipo_corporativo,
                SUM(CASE WHEN ud.estatus = 'Registrado' THEN 1 ELSE 0 END) as activos,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Registrado' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_registrados,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Activo' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_activos,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Anulado' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_anulados,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Vencido' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_vencidos,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Pendiente' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_pendientes,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'suspendido' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_suspendidos,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Creado Manual' AND contr.clinica_id = ud.clinica_id THEN ud.id END) as contratos_creado_manual,
                COUNT(DISTINCT contr.id) as total_contratos,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Registrado' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_registrados,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Activo' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_activos,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Anulado' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_anulados,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Vencido' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_vencidos,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Pendiente' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_pendientes,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'suspendido' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_suspendidos,
                COUNT(DISTINCT CASE WHEN contr.estatus = 'Creado Manual' AND contr.clinica_id = ud.clinica_id THEN contr.id END) as total_contratos_creado_manual
            FROM user_datos ud
            INNER JOIN rm_clinica c ON ud.clinica_id = c.id
            LEFT JOIN contratos contr ON ud.id = contr.user_id AND contr.deleted_at IS NULL
            WHERE ud.role = 'afiliado'
                AND ud.deleted_at IS NULL
        ";

        $sqlParams = [];

        // Apply clinic restrictions based on user role
        if ($this->_hasClinicRestriction && !empty($this->_accessibleClinicaIds)) {
            $placeholders = [];
            foreach ($this->_accessibleClinicaIds as $index => $clinicaId) {
                $paramName = ":restricted_clinica_id_{$index}";
                $placeholders[] = $paramName;
                $sqlParams[$paramName] = $clinicaId;
            }
            $sql .= " AND ud.clinica_id IN (" . implode(',', $placeholders) . ")";
        }
        // Handle user-selected clinic filters (only for users without restrictions)
        elseif (!$this->_hasClinicRestriction) {
            $filteredClinicaIds = $this->getFilteredClinicaIds();
            if ($filteredClinicaIds !== null) {
                $placeholders = [];
                foreach ($filteredClinicaIds as $index => $clinicaId) {
                    $paramName = ":clinica_id_{$index}";
                    $placeholders[] = $paramName;
                    $sqlParams[$paramName] = $clinicaId;
                }
                $sql .= " AND ud.clinica_id IN (" . implode(',', $placeholders) . ")";
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

        $sql .= " GROUP BY c.id, c.nombre, c.rif ORDER BY total_afiliados DESC";

        $connection = Yii::$app->db;
        $command = $connection->createCommand($sql, $sqlParams);

        return $command->queryAll();
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
            WHERE ud.role = 'afiliado'
                AND ud.deleted_at IS NULL
                AND p.id IS NOT NULL
        ";

        $sqlParams = [];

        // Apply clinic restrictions based on user role
        if ($this->_hasClinicRestriction && !empty($this->_accessibleClinicaIds)) {
            $placeholders = [];
            foreach ($this->_accessibleClinicaIds as $index => $clinicaId) {
                $paramName = ":restricted_clinica_id_{$index}";
                $placeholders[] = $paramName;
                $sqlParams[$paramName] = $clinicaId;
            }
            $sql .= " AND ud.clinica_id IN (" . implode(',', $placeholders) . ")";
        }
        // Handle user-selected clinic filters (only for users without restrictions)
        elseif (!$this->_hasClinicRestriction) {
            $filteredClinicaIds = $this->getFilteredClinicaIds();
            if ($filteredClinicaIds !== null) {
                $placeholders = [];
                foreach ($filteredClinicaIds as $index => $clinicaId) {
                    $paramName = ":clinica_id_{$index}";
                    $placeholders[] = $paramName;
                    $sqlParams[$paramName] = $clinicaId;
                }
                $sql .= " AND ud.clinica_id IN (" . implode(',', $placeholders) . ")";
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
                DATE_TRUNC('month', created_at) as month,
                COUNT(*) as total,
                SUM(CASE WHEN user_datos_type_id = 1 THEN 1 ELSE 0 END) as individual,
                SUM(CASE WHEN user_datos_type_id = 2 THEN 1 ELSE 0 END) as corporativo
            FROM user_datos
            WHERE role = 'afiliado'
                AND deleted_at IS NULL
                AND created_at >= :date_limit
        ";

        $sqlParams = [':date_limit' => date('Y-m-d', strtotime('-12 months'))];

        // Apply clinic restrictions based on user role
        if ($this->_hasClinicRestriction && !empty($this->_accessibleClinicaIds)) {
            $placeholders = [];
            foreach ($this->_accessibleClinicaIds as $index => $clinicaId) {
                $paramName = ":restricted_clinica_id_{$index}";
                $placeholders[] = $paramName;
                $sqlParams[$paramName] = $clinicaId;
            }
            $sql .= " AND clinica_id IN (" . implode(',', $placeholders) . ")";
        }
        // Handle user-selected clinic filters (only for users without restrictions)
        elseif (!$this->_hasClinicRestriction) {
            $filteredClinicaIds = $this->getFilteredClinicaIds();
            if ($filteredClinicaIds !== null) {
                $placeholders = [];
                foreach ($filteredClinicaIds as $index => $clinicaId) {
                    $paramName = ":clinica_id_{$index}";
                    $placeholders[] = $paramName;
                    $sqlParams[$paramName] = $clinicaId;
                }
                $sql .= " AND clinica_id IN (" . implode(',', $placeholders) . ")";
            }
        }

        $sql .= " GROUP BY DATE_TRUNC('month', created_at) ORDER BY month ASC";

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
     * Get top clinics by affiliates count
     */
    public function getTopClinics($limit = 10, $params = [])
    {
        $summary = $this->getSummaryByClinic($params);
        return array_slice($summary, 0, $limit);
    }

    /**
     * Get totals for KPI cards
     */
    public function getTotals($params = [])
    {
        $summary = $this->getSummaryByClinic($params);

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
        ];
    }
}
