<?php
// app/models/SisSiniestroUsoSearch.php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\web\Response;
use app\components\UserHelper;

/**
 * SisSiniestroUsoSearch handles service usage statistics for affiliates
 * This shows which clients have used medical services and tracks usage patterns
 */
class SisSiniestroUsoSearch extends Model
{
    public $date_from;
    public $date_to;
    public $clinica_id = [];
    public $tipo_atencion; // 'all', 'citas', 'siniestros'
    public $baremo_id;
    public $scope; // 'period' or 'cartera'

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to'], 'safe'],
            [['clinica_id', 'baremo_id'], 'integer'],
            [['tipo_atencion', 'scope'], 'string'],
            ['tipo_atencion', 'in', 'range' => ['all', 'citas', 'siniestros']],
            ['scope', 'in', 'range' => ['period', 'cartera']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'date_from' => 'Fecha Inicial',
            'date_to' => 'Fecha Final',
            'clinica_id' => 'Clínica',
            'tipo_atencion' => 'Tipo de Atención',
            'baremo_id' => 'Servicio',
            'scope' => 'Alcance del Reporte',
        ];
    }

    /**
     * Get date range based on scope
     * 
     * @return array|null Returns date range or null if scope is 'cartera'
     */
    public function getDateRange()
    {
        if ($this->scope === 'cartera') {
            return null;
        }

        $dateFrom = $this->date_from ?? date('Y-m-d', strtotime('-1 month'));
        $dateTo = $this->date_to ?? date('Y-m-d');

        return [
            'from' => $dateFrom,
            'to' => $dateTo,
        ];
    }

    /**
     * Generate the usage report
     * 
     * @return array Report data with usage statistics
     */
    public function generateReport()
    {
        $dateRange = $this->getDateRange();
        $isCartera = ($this->scope === 'cartera');

        // Get all active affiliates (with active contracts)
        $activeAffiliates = $this->getActiveAffiliates($isCartera);

        // Get usage data
        $usageData = $this->getUsageData($dateRange, $isCartera);

        // Calculate statistics
        $totalAffiliates = count($activeAffiliates);
        $totalUsers = count($usageData['users']);
        $totalAttentions = $usageData['total_attentions'];
        $totalServices = $usageData['total_services'];
        $totalCostReal = $usageData['total_cost_real'];
        $totalPrecio = $usageData['total_precio'];
        $totalMargen = $usageData['total_margen'];

        // Calculate usage percentage
        $usagePercentage = $totalAffiliates > 0 ? round(($totalUsers / $totalAffiliates) * 100, 2) : 0;

        // Get usage by service type
        $usageByService = $this->getUsageByService($dateRange, $isCartera);

        // Get usage by clinic with cost details
        $usageByClinic = $this->getUsageByClinicWithCost($dateRange, $isCartera);

        // Get top users (most frequent)
        $topUsers = $this->getTopUsers($dateRange, $isCartera, 10);

        return [
            'summary' => [
                'total_affiliates' => $totalAffiliates,
                'total_users_with_attentions' => $totalUsers,
                'total_attentions' => $totalAttentions,
                'total_services_used' => $totalServices,
                'usage_percentage' => $usagePercentage,
                'total_cost_real' => $totalCostReal,
                'total_precio' => $totalPrecio,
                'total_margen' => $totalMargen,
                'scope' => $this->scope,
                'date_range' => $dateRange ? [
                    'from' => $dateRange['from'],
                    'to' => $dateRange['to'],
                ] : null,
            ],
            'usage_by_service' => $usageByService,
            'usage_by_clinic' => $usageByClinic,
            'top_users' => $topUsers,
            'detail' => $usageData['detail'],
            'cost_summary' => $this->getCostSummary($dateRange, $isCartera),
        ];
    }

    /**
     * Get all active affiliates (with active contracts in the period)
     * 
     * @param bool $isCartera If true, get all affiliates with "Activo" contracts regardless of date
     * @return array List of active affiliates
     */
    private function getActiveAffiliates($isCartera = false)
    {
        $query = UserDatos::find()
            ->alias('u')
            ->select(['u.id', 'u.nombres', 'u.apellidos', 'u.cedula', 'u.tipo_cedula', 'u.clinica_id'])
            ->innerJoin('contratos c', 'c.user_id = u.id')
            ->where(['u.role' => 'afiliado'])
            ->andWhere(['IS', 'u.deleted_at', null]);

        if (!$isCartera) {
            $query->andWhere(['c.estatus' => 'Activo'])
                ->andWhere(['<=', 'c.fecha_ini', date('Y-m-d')])
                ->andWhere(['>=', 'c.fecha_ven', date('Y-m-d')]);
        } else {
            $query->andWhere(['c.estatus' => 'Activo']);
        }

        if (!empty($this->clinica_id)) {
            $query->andWhere(['u.clinica_id' => $this->clinica_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $userClinicaIds = UserHelper::getAccessibleClinicaIds();
            if (!empty($userClinicaIds)) {
                $query->andWhere(['u.clinica_id' => $userClinicaIds]);
            }
        }

        return $query->asArray()->all();
    }

    /**
     * Get usage data for the specified period
     * 
     * @param array|null $dateRange Date range or null for all data
     * @param bool $isCartera If true, get all data regardless of date
     * @return array Usage data
     */
    private function getUsageData($dateRange, $isCartera = false)
    {
        $query = SisSiniestro::find()
            ->alias('s')
            ->select([
                's.id',
                's.iduser',
                's.fecha',
                's.hora',
                's.idclinica',
                's.es_cita',
                's.atendido',
                's.costo_total',
                's.descripcion',
                'u.nombres',
                'u.apellidos',
                'u.cedula',
                'u.tipo_cedula',
                'c.nombre as clinica_nombre',
            ])
            ->innerJoin('user_datos u', 'u.id = s.iduser')
            ->leftJoin('rm_clinica c', 'c.id = s.idclinica')
            ->andWhere(['IS', 's.deleted_at', null]);

        if (!$isCartera && $dateRange) {
            $query->andWhere(['>=', 's.fecha', $dateRange['from']])
                ->andWhere(['<=', 's.fecha', $dateRange['to']]);
        }

        if ($this->tipo_atencion === 'citas') {
            $query->andWhere(['s.es_cita' => true]);
        } elseif ($this->tipo_atencion === 'siniestros') {
            $query->andWhere(['s.es_cita' => false]);
        }

        if (!empty($this->clinica_id)) {
            $query->andWhere(['s.idclinica' => $this->clinica_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $userClinicaIds = UserHelper::getAccessibleClinicaIds();
            if (!empty($userClinicaIds)) {
                $query->andWhere(['s.idclinica' => $userClinicaIds]);
            }
        }

        $attentions = $query->asArray()->all();

        // Get unique users
        $userIds = array_unique(array_column($attentions, 'iduser'));
        $users = [];
        $detail = [];
        $totalCostReal = 0;
        $totalPrecio = 0;
        $totalMargen = 0;

        foreach ($attentions as $attention) {
            $userId = $attention['iduser'];
            if (!isset($users[$userId])) {
                $users[$userId] = [
                    'id' => $userId,
                    'nombre' => $attention['nombres'] . ' ' . $attention['apellidos'],
                    'cedula' => ($attention['tipo_cedula'] ? $attention['tipo_cedula'] . '-' : '') . $attention['cedula'],
                    'clinica' => $attention['clinica_nombre'] ?? 'No asignada',
                    'total_attentions' => 0,
                    'total_cost' => 0,
                    'attentions' => [],
                ];
            }

            $users[$userId]['total_attentions']++;
            $users[$userId]['total_cost'] += (float)$attention['costo_total'];

            // Get baremos for this attention with cost details
            $baremos = $this->getBaremosForAttention($attention['id']);

            // Calculate totals - FIXED: Use costo_real from baremo.costo
            $attentionCostReal = 0;
            $attentionPrecio = 0;
            foreach ($baremos as $baremo) {
                $attentionCostReal += (float)($baremo['costo_real'] ?? 0);  // from baremo.costo (what SISPSA pays)
                $attentionPrecio += (float)($baremo['precio'] ?? 0);        // from baremo.precio (what SISPSA charges)
            }
            $attentionMargen = $attentionPrecio - $attentionCostReal;

            $totalCostReal += $attentionCostReal;
            $totalPrecio += $attentionPrecio;
            $totalMargen += $attentionMargen;

            $users[$userId]['attentions'][] = [
                'id' => $attention['id'],
                'fecha' => $attention['fecha'],
                'hora' => $attention['hora'],
                'tipo' => $attention['es_cita'] ? 'Cita' : 'Emergencia',
                'atendido' => $attention['atendido'],
                'costo' => $attention['costo_total'],
                'descripcion' => $attention['descripcion'],
                'baremos' => $baremos,
                'cost_real' => $attentionCostReal,
                'precio' => $attentionPrecio,
                'margen' => $attentionMargen,
            ];
        }

        return [
            'users' => array_values($users),
            'total_attentions' => count($attentions),
            'total_services' => $this->countTotalServices($attentions),
            'total_cost_real' => $totalCostReal,
            'total_precio' => $totalPrecio,
            'total_margen' => $totalMargen,
            'detail' => $attentions,
        ];
    }

    /**
     * Get baremos for a specific attention with cost details
     * FIXED: Added costo_real from baremo.costo
     * 
     * @param int $attentionId
     * @return array
     */
    private function getBaremosForAttention($attentionId)
    {
        return SisSiniestroBaremo::find()
            ->alias('sb')
            ->select([
                'b.id',
                'b.nombre_servicio',
                'b.precio',                      // What SISPSA charges the affiliate
                'b.costo as costo_real',         // What SISPSA pays to the clinic (REAL COST)
                'sb.costo as costo_sb',          // Stored price (keep for reference)
                'a.nombre as area_nombre',
                'a.id as area_id'
            ])
            ->innerJoin('baremo b', 'b.id = sb.baremo_id')
            ->leftJoin('area a', 'a.id = b.area_id')
            ->where(['sb.siniestro_id' => $attentionId])
            ->asArray()
            ->all();
    }

    /**
     * Count total services used across all attentions
     * 
     * @param array $attentions
     * @return int
     */
    private function countTotalServices($attentions)
    {
        $total = 0;
        foreach ($attentions as $attention) {
            $baremos = $this->getBaremosForAttention($attention['id']);
            $total += count($baremos);
        }
        return $total;
    }

    /**
     * Get usage by service type with cost details
     * 
     * @param array|null $dateRange
     * @param bool $isCartera
     * @return array
     */
    private function getUsageByService($dateRange, $isCartera = false)
    {
        $query = (new Query())
            ->select([
                'b.id',
                'b.nombre_servicio',
                'a.nombre as area_nombre',
                'COUNT(DISTINCT s.iduser) as unique_users',
                'COUNT(sb.siniestro_id) as total_uses',
                'SUM(b.costo) as total_costo_real',    // FIXED: Use baremo.costo
                'SUM(b.precio) as total_precio',        // Use baremo.precio
                '(SUM(b.precio) - SUM(b.costo)) as total_margen',
                'ROUND(((SUM(b.precio) - SUM(b.costo)) / NULLIF(SUM(b.precio), 0)) * 100, 2) as margen_porcentaje',
            ])
            ->from(['sb' => 'sis_siniestro_baremo'])
            ->innerJoin('sis_siniestro s', 's.id = sb.siniestro_id')
            ->innerJoin('baremo b', 'b.id = sb.baremo_id')
            ->leftJoin('area a', 'a.id = b.area_id')
            ->where(['IS', 's.deleted_at', null])
            ->groupBy(['b.id', 'b.nombre_servicio', 'a.nombre'])
            ->orderBy(['total_uses' => SORT_DESC]);

        if (!$isCartera && $dateRange) {
            $query->andWhere(['>=', 's.fecha', $dateRange['from']])
                ->andWhere(['<=', 's.fecha', $dateRange['to']]);
        }

        if ($this->tipo_atencion === 'citas') {
            $query->andWhere(['s.es_cita' => true]);
        } elseif ($this->tipo_atencion === 'siniestros') {
            $query->andWhere(['s.es_cita' => false]);
        }

        if (!empty($this->clinica_id)) {
            $query->andWhere(['s.idclinica' => $this->clinica_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $userClinicaIds = UserHelper::getAccessibleClinicaIds();
            if (!empty($userClinicaIds)) {
                $query->andWhere(['s.idclinica' => $userClinicaIds]);
            }
        }

        if (!empty($this->baremo_id)) {
            $query->andWhere(['b.id' => $this->baremo_id]);
        }

        return $query->all();
    }

    /**
     * Get usage by clinic with cost details (COMPLETELY REWRITTEN)
     * 
     * @param array|null $dateRange
     * @param bool $isCartera
     * @return array
     */
    private function getUsageByClinicWithCost($dateRange, $isCartera = false)
    {
        // ============================================================
        // QUERY 1: Get ALL clinics with their basic counts
        // This includes citas_count and emergencias_count from sis_siniestro
        // ============================================================
        $query = (new Query())
            ->select([
                'c.id',
                'c.nombre as clinica_nombre',
                'COALESCE(COUNT(DISTINCT s.iduser), 0) as unique_users',
                'COALESCE(COUNT(s.id), 0) as total_attentions',
                'COALESCE(SUM(s.costo_total), 0) as total_costo_venta',
                'COALESCE(COUNT(CASE WHEN s.es_cita = TRUE THEN 1 END), 0) as citas_count',
                'COALESCE(COUNT(CASE WHEN s.es_cita = FALSE THEN 1 END), 0) as emergencias_count',
            ])
            ->from(['c' => 'rm_clinica'])
            ->leftJoin('sis_siniestro s', 's.idclinica = c.id AND s.deleted_at IS NULL')
            ->where(['c.estatus' => 'Activo'])
            ->andWhere(['IS', 'c.deleted_at', null])
            ->groupBy(['c.id', 'c.nombre'])
            ->orderBy(['unique_users' => SORT_DESC]);

        // Apply date filters
        if (!$isCartera && $dateRange) {
            $query->andWhere(['>=', 's.fecha', $dateRange['from']])
                ->andWhere(['<=', 's.fecha', $dateRange['to']]);
        }

        // Apply tipo_atencion filter
        if ($this->tipo_atencion === 'citas') {
            $query->andWhere(['s.es_cita' => true]);
        } elseif ($this->tipo_atencion === 'siniestros') {
            $query->andWhere(['s.es_cita' => false]);
        }

        // Apply clinic filter
        if (!empty($this->clinica_id)) {
            $query->andWhere(['c.id' => $this->clinica_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $userClinicaIds = UserHelper::getAccessibleClinicaIds();
            if (!empty($userClinicaIds)) {
                $query->andWhere(['c.id' => $userClinicaIds]);
            }
        }

        $clinicData = $query->all();

        // ============================================================
        // QUERY 2: Get cost data from baremos for each clinic
        // Use LEFT JOIN to include clinics with no baremo data
        // ============================================================
        $costQuery = (new Query())
            ->select([
                's.idclinica',
                'COALESCE(SUM(b.costo), 0) as total_costo_real',
                'COALESCE(SUM(b.precio), 0) as total_precio',
                'COALESCE(SUM(b.precio) - SUM(b.costo), 0) as total_margen',
                'ROUND(COALESCE(((SUM(b.precio) - SUM(b.costo)) / NULLIF(SUM(b.precio), 0)) * 100, 0), 2) as margen_porcentaje',
            ])
            ->from(['s' => 'sis_siniestro'])
            ->leftJoin('sis_siniestro_baremo sb', 'sb.siniestro_id = s.id')
            ->leftJoin('baremo b', 'b.id = sb.baremo_id')
            ->where(['IS', 's.deleted_at', null]);

        // Apply date filters
        if (!$isCartera && $dateRange) {
            $costQuery->andWhere(['>=', 's.fecha', $dateRange['from']])
                ->andWhere(['<=', 's.fecha', $dateRange['to']]);
        }

        // Apply tipo_atencion filter
        if ($this->tipo_atencion === 'citas') {
            $costQuery->andWhere(['s.es_cita' => true]);
        } elseif ($this->tipo_atencion === 'siniestros') {
            $costQuery->andWhere(['s.es_cita' => false]);
        }

        // Apply clinic filter
        if (!empty($this->clinica_id)) {
            $costQuery->andWhere(['s.idclinica' => $this->clinica_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $userClinicaIds = UserHelper::getAccessibleClinicaIds();
            if (!empty($userClinicaIds)) {
                $costQuery->andWhere(['s.idclinica' => $userClinicaIds]);
            }
        }

        $costQuery->groupBy(['s.idclinica']);
        $costData = $costQuery->all();

        // ============================================================
        // MERGE: Combine clinic data with cost data
        // ============================================================
        $costMap = [];
        foreach ($costData as $cost) {
            $costMap[$cost['idclinica']] = $cost;
        }

        $result = [];
        foreach ($clinicData as $clinic) {
            $clinicId = $clinic['id'];
            $costInfo = $costMap[$clinicId] ?? [
                'total_costo_real' => 0,
                'total_precio' => 0,
                'total_margen' => 0,
                'margen_porcentaje' => 0,
            ];

            $result[] = array_merge($clinic, $costInfo);
        }

        return $result;
    }

    /**
     * Get cost summary by category (FIXED)
     * 
     * @param array|null $dateRange
     * @param bool $isCartera
     * @return array
     */
    public function getCostSummary($dateRange, $isCartera = false)
    {
        $query = (new Query())
            ->select([
                'a.nombre as categoria',
                'COUNT(DISTINCT s.iduser) as unique_users',
                'COUNT(sb.siniestro_id) as total_uses',
                'SUM(b.costo) as total_costo_real',      // FIXED: Use baremo.costo
                'SUM(b.precio) as total_precio',          // Use baremo.precio
                '(SUM(b.precio) - SUM(b.costo)) as total_margen',
                'ROUND(((SUM(b.precio) - SUM(b.costo)) / NULLIF(SUM(b.precio), 0)) * 100, 2) as margen_porcentaje',
            ])
            ->from(['sb' => 'sis_siniestro_baremo'])
            ->innerJoin('sis_siniestro s', 's.id = sb.siniestro_id')
            ->innerJoin('baremo b', 'b.id = sb.baremo_id')
            ->leftJoin('area a', 'a.id = b.area_id')
            ->where(['IS', 's.deleted_at', null])
            ->groupBy(['a.nombre'])
            ->orderBy(['total_uses' => SORT_DESC]);

        if (!$isCartera && $dateRange) {
            $query->andWhere(['>=', 's.fecha', $dateRange['from']])
                ->andWhere(['<=', 's.fecha', $dateRange['to']]);
        }

        if ($this->tipo_atencion === 'citas') {
            $query->andWhere(['s.es_cita' => true]);
        } elseif ($this->tipo_atencion === 'siniestros') {
            $query->andWhere(['s.es_cita' => false]);
        }

        if (!empty($this->clinica_id)) {
            $query->andWhere(['s.idclinica' => $this->clinica_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $userClinicaIds = UserHelper::getAccessibleClinicaIds();
            if (!empty($userClinicaIds)) {
                $query->andWhere(['s.idclinica' => $userClinicaIds]);
            }
        }

        return $query->all();
    }

    /**
     * Get top users with most attentions
     * 
     * @param array|null $dateRange
     * @param bool $isCartera
     * @param int $limit
     * @return array
     */
    private function getTopUsers($dateRange, $isCartera = false, $limit = 10)
    {
        $query = (new Query())
            ->select([
                'u.id',
                'u.nombres',
                'u.apellidos',
                'u.cedula',
                'u.tipo_cedula',
                'COUNT(s.id) as total_attentions',
                'SUM(s.costo_total) as total_cost',
                'COUNT(CASE WHEN s.es_cita = TRUE THEN 1 END) as citas_count',
                'COUNT(CASE WHEN s.es_cita = FALSE THEN 1 END) as emergencias_count',
            ])
            ->from(['u' => 'user_datos'])
            ->innerJoin('sis_siniestro s', 's.iduser = u.id')
            ->where(['IS', 's.deleted_at', null])
            ->andWhere(['u.role' => 'afiliado'])
            ->groupBy(['u.id', 'u.nombres', 'u.apellidos', 'u.cedula', 'u.tipo_cedula'])
            ->orderBy(['total_attentions' => SORT_DESC])
            ->limit($limit);

        if (!$isCartera && $dateRange) {
            $query->andWhere(['>=', 's.fecha', $dateRange['from']])
                ->andWhere(['<=', 's.fecha', $dateRange['to']]);
        }

        if ($this->tipo_atencion === 'citas') {
            $query->andWhere(['s.es_cita' => true]);
        } elseif ($this->tipo_atencion === 'siniestros') {
            $query->andWhere(['s.es_cita' => false]);
        }

        if (!empty($this->clinica_id)) {
            $query->andWhere(['s.idclinica' => $this->clinica_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $userClinicaIds = UserHelper::getAccessibleClinicaIds();
            if (!empty($userClinicaIds)) {
                $query->andWhere(['s.idclinica' => $userClinicaIds]);
            }
        }

        return $query->all();
    }

    /**
     * Get service usage by category (FIXED)
     * 
     * @param array|null $dateRange
     * @param bool $isCartera
     * @return array
     */
    public function getUsageByCategory($dateRange, $isCartera = false)
    {
        $query = (new Query())
            ->select([
                'a.nombre as area_nombre',
                'COUNT(DISTINCT s.iduser) as unique_users',
                'COUNT(sb.siniestro_id) as total_uses',
                'SUM(b.costo) as total_costo_real',      // FIXED: Use baremo.costo
                'SUM(b.precio) as total_precio',          // Use baremo.precio
                '(SUM(b.precio) - SUM(b.costo)) as total_margen',
            ])
            ->from(['sb' => 'sis_siniestro_baremo'])
            ->innerJoin('sis_siniestro s', 's.id = sb.siniestro_id')
            ->innerJoin('baremo b', 'b.id = sb.baremo_id')
            ->leftJoin('area a', 'a.id = b.area_id')
            ->where(['IS', 's.deleted_at', null])
            ->groupBy(['a.nombre'])
            ->orderBy(['total_uses' => SORT_DESC]);

        if (!$isCartera && $dateRange) {
            $query->andWhere(['>=', 's.fecha', $dateRange['from']])
                ->andWhere(['<=', 's.fecha', $dateRange['to']]);
        }

        if ($this->tipo_atencion === 'citas') {
            $query->andWhere(['s.es_cita' => true]);
        } elseif ($this->tipo_atencion === 'siniestros') {
            $query->andWhere(['s.es_cita' => false]);
        }

        if (!empty($this->clinica_id)) {
            $query->andWhere(['s.idclinica' => $this->clinica_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $userClinicaIds = UserHelper::getAccessibleClinicaIds();
            if (!empty($userClinicaIds)) {
                $query->andWhere(['s.idclinica' => $userClinicaIds]);
            }
        }

        return $query->all();
    }

    /**
     * Get user with no usage (inactive users)
     * 
     * @param array|null $dateRange
     * @param bool $isCartera
     * @return array
     */
    public function getInactiveUsers($dateRange, $isCartera = false)
    {
        $query = UserDatos::find()
            ->alias('u')
            ->select([
                'u.id',
                'u.nombres',
                'u.apellidos',
                'u.cedula',
                'u.tipo_cedula',
                'c.nombre as clinica_nombre',
            ])
            ->innerJoin('contratos ct', 'ct.user_id = u.id')
            ->leftJoin('rm_clinica c', 'c.id = u.clinica_id')
            ->where(['u.role' => 'afiliado'])
            ->andWhere(['IS', 'u.deleted_at', null]);

        if (!$isCartera) {
            $query->andWhere(['ct.estatus' => 'Activo'])
                ->andWhere(['<=', 'ct.fecha_ini', date('Y-m-d')])
                ->andWhere(['>=', 'ct.fecha_ven', date('Y-m-d')]);
        } else {
            $query->andWhere(['ct.estatus' => 'Activo']);
        }

        $subQuery = (new Query())
            ->select(new \yii\db\Expression('1'))
            ->from('sis_siniestro s')
            ->where('s.iduser = u.id')
            ->andWhere(['IS', 's.deleted_at', null]);

        if (!$isCartera && $dateRange) {
            $subQuery->andWhere(['>=', 's.fecha', $dateRange['from']])
                ->andWhere(['<=', 's.fecha', $dateRange['to']]);
        }

        $query->andWhere(['NOT EXISTS', $subQuery]);

        if (!empty($this->clinica_id)) {
            $query->andWhere(['u.clinica_id' => $this->clinica_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $userClinicaIds = UserHelper::getAccessibleClinicaIds();
            if (!empty($userClinicaIds)) {
                $query->andWhere(['u.clinica_id' => $userClinicaIds]);
            }
        }

        return $query->asArray()->all();
    }

    /**
     * Get service usage timeline (monthly)
     * 
     * @param array|null $dateRange
     * @param bool $isCartera
     * @return array
     */
    public function getUsageTimeline($dateRange, $isCartera = false)
    {
        $query = (new Query())
            ->select([
                "DATE_TRUNC('month', s.fecha) as month_date",
                'COUNT(DISTINCT s.iduser) as unique_users',
                'COUNT(s.id) as total_attentions',
                'SUM(s.costo_total) as total_cost',
            ])
            ->from('sis_siniestro s')
            ->where(['IS', 's.deleted_at', null])
            ->groupBy(["DATE_TRUNC('month', s.fecha)"])
            ->orderBy(['month_date' => SORT_ASC]);

        if (!$isCartera && $dateRange) {
            $query->andWhere(['>=', 's.fecha', $dateRange['from']])
                ->andWhere(['<=', 's.fecha', $dateRange['to']]);
        }

        if ($this->tipo_atencion === 'citas') {
            $query->andWhere(['s.es_cita' => true]);
        } elseif ($this->tipo_atencion === 'siniestros') {
            $query->andWhere(['s.es_cita' => false]);
        }

        if (!empty($this->clinica_id)) {
            $query->andWhere(['s.idclinica' => $this->clinica_id]);
        }

        if (UserHelper::hasClinicAccess()) {
            $userClinicaIds = UserHelper::getAccessibleClinicaIds();
            if (!empty($userClinicaIds)) {
                $query->andWhere(['s.idclinica' => $userClinicaIds]);
            }
        }

        $results = $query->all();

        $formattedResults = [];
        foreach ($results as $row) {
            $formattedResults[] = [
                'month' => isset($row['month_date']) ? date('Y-m', strtotime($row['month_date'])) : '',
                'unique_users' => $row['unique_users'],
                'total_attentions' => $row['total_attentions'],
                'total_cost' => $row['total_cost'],
            ];
        }

        return $formattedResults;
    }
    /**
     * AJAX endpoint to get emergency details for a clinic
     */
    public function actionEmergencyDetail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $clinicId = Yii::$app->request->get('clinic_id');
            $dateFrom = Yii::$app->request->get('date_from');
            $dateTo = Yii::$app->request->get('date_to');
            $scope = Yii::$app->request->get('scope', 'period');

            if (!$clinicId) {
                return ['success' => false, 'message' => 'ID de clínica no proporcionado'];
            }

            $searchModel = new SisSiniestroUsoSearch();
            $searchModel->date_from = $dateFrom;
            $searchModel->date_to = $dateTo;
            $searchModel->scope = $scope;

            $dateRange = $searchModel->getDateRange();
            $isCartera = ($scope === 'cartera');

            $emergencyData = $searchModel->getEmergencyDetailsByClinic($clinicId, $dateRange, $isCartera);

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
            Yii::error('Error getting emergency details: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage(),
            ];
        }
    }
    /**
     * Get emergency details for a specific clinic with category breakdown
     * 
     * @param int $clinicId
     * @param array|null $dateRange
     * @param bool $isCartera
     * @return array
     */
    public function getEmergencyDetailsByClinic($clinicId, $dateRange, $isCartera = false)
    {
        try {
            // ============================================================
            // Build the base query for emergencies
            // ============================================================
            $query = (new Query())
                ->select([
                    's.id',
                    's.fecha',
                    's.hora',
                    's.descripcion',
                    's.costo_total',
                    's.es_cita',
                    's.nombre_doctor',
                    'u.nombres',
                    'u.apellidos',
                    'u.cedula',
                    'u.tipo_cedula',
                    'b.id as baremo_id',
                    'b.nombre_servicio',
                    'b.precio',
                    'b.costo as costo_real',
                    'a.nombre as categoria',
                    'a.id as categoria_id',
                ])
                ->from(['s' => 'sis_siniestro'])
                ->innerJoin('user_datos u', 'u.id = s.iduser')
                ->leftJoin('sis_siniestro_baremo sb', 'sb.siniestro_id = s.id')
                ->leftJoin('baremo b', 'b.id = sb.baremo_id')
                ->leftJoin('area a', 'a.id = b.area_id')
                ->where(['s.idclinica' => $clinicId])
                ->andWhere(['s.es_cita' => false])
                ->andWhere(['IS', 's.deleted_at', null]);

            if (!$isCartera && $dateRange) {
                $query->andWhere(['>=', 's.fecha', $dateRange['from']])
                    ->andWhere(['<=', 's.fecha', $dateRange['to']]);
            }

            // Get all results
            $results = $query->all();

            // If no results, return empty
            if (empty($results)) {
                return [
                    'grouped' => [],
                    'totals' => [
                        'total' => 0,
                        'total_precio' => 0,
                        'total_costo_real' => 0,
                        'total_margen' => 0,
                    ],
                    'all_items' => [],
                ];
            }

            // ============================================================
            // Process results: Group by siniestro_id first
            // ============================================================
            $emergenciesMap = [];
            foreach ($results as $row) {
                $siniestroId = $row['id'];

                if (!isset($emergenciesMap[$siniestroId])) {
                    $emergenciesMap[$siniestroId] = [
                        'id' => $row['id'],
                        'fecha' => $row['fecha'],
                        'hora' => $row['hora'],
                        'descripcion' => $row['descripcion'],
                        'costo_total' => $row['costo_total'],
                        'nombre_doctor' => $row['nombre_doctor'],
                        'nombres' => $row['nombres'],
                        'apellidos' => $row['apellidos'],
                        'cedula' => $row['cedula'],
                        'tipo_cedula' => $row['tipo_cedula'],
                        'baremos' => [],
                        'total_precio' => 0,
                        'total_costo_real' => 0,
                    ];
                }

                // Add baremo if it exists (not null)
                if ($row['baremo_id'] !== null) {
                    $emergenciesMap[$siniestroId]['baremos'][] = [
                        'baremo_id' => $row['baremo_id'],
                        'nombre_servicio' => $row['nombre_servicio'],
                        'precio' => (float)$row['precio'],
                        'costo_real' => (float)$row['costo_real'],
                        'categoria' => $row['categoria'] ?? 'Sin Categoría',
                        'categoria_id' => $row['categoria_id'],
                    ];
                }
            }

            // ============================================================
            // Calculate totals per emergency and determine category
            // ============================================================
            $processedEmergencies = [];
            $totals = [
                'total' => 0,
                'total_precio' => 0,
                'total_costo_real' => 0,
                'total_margen' => 0,
            ];

            foreach ($emergenciesMap as $emergency) {
                $totalPrecio = 0;
                $totalCostoReal = 0;
                $categories = [];

                foreach ($emergency['baremos'] as $baremo) {
                    $totalPrecio += $baremo['precio'];
                    $totalCostoReal += $baremo['costo_real'];
                    if (!empty($baremo['categoria'])) {
                        $categories[] = $baremo['categoria'];
                    }
                }

                // If no baremos, use costo_total as fallback
                if (empty($emergency['baremos'])) {
                    $totalPrecio = (float)($emergency['costo_total'] ?? 0);
                    $totalCostoReal = (float)($emergency['costo_total'] ?? 0) * 0.5;
                    $categories[] = 'Sin Categoría';
                }

                // Determine primary category (most common or first)
                $primaryCategory = !empty($categories) ? $categories[0] : 'Sin Categoría';

                $processedEmergencies[] = [
                    'id' => $emergency['id'],
                    'fecha' => $emergency['fecha'],
                    'hora' => $emergency['hora'],
                    'descripcion' => $emergency['descripcion'],
                    'costo_total' => $emergency['costo_total'],
                    'nombre_doctor' => $emergency['nombre_doctor'],
                    'nombres' => $emergency['nombres'],
                    'apellidos' => $emergency['apellidos'],
                    'cedula' => $emergency['cedula'],
                    'tipo_cedula' => $emergency['tipo_cedula'],
                    'baremos' => $emergency['baremos'],
                    'total_precio' => $totalPrecio,
                    'total_costo_real' => $totalCostoReal,
                    'total_margen' => $totalPrecio - $totalCostoReal,
                    'categoria' => $primaryCategory,
                    'nombre_servicio' => !empty($emergency['baremos']) ? $emergency['baremos'][0]['nombre_servicio'] : 'Sin servicio',
                ];

                $totals['total']++;
                $totals['total_precio'] += $totalPrecio;
                $totals['total_costo_real'] += $totalCostoReal;
                $totals['total_margen'] += ($totalPrecio - $totalCostoReal);
            }

            // ============================================================
            // Group by category
            // ============================================================
            $grouped = [];
            foreach ($processedEmergencies as $item) {
                $categoria = $item['categoria'] ?? 'Sin Categoría';
                if (!isset($grouped[$categoria])) {
                    $grouped[$categoria] = [
                        'categoria' => $categoria,
                        'items' => [],
                        'count' => 0,
                        'total_precio' => 0,
                        'total_costo_real' => 0,
                        'total_margen' => 0,
                    ];
                }
                $grouped[$categoria]['items'][] = $item;
                $grouped[$categoria]['count']++;
                $grouped[$categoria]['total_precio'] += $item['total_precio'];
                $grouped[$categoria]['total_costo_real'] += $item['total_costo_real'];
                $grouped[$categoria]['total_margen'] += $item['total_margen'];
            }

            return [
                'grouped' => $grouped,
                'totals' => $totals,
                'all_items' => $processedEmergencies,
            ];
        } catch (\Exception $e) {
            Yii::error('Error in getEmergencyDetailsByClinic: ' . $e->getMessage(), __METHOD__);
            return [
                'grouped' => [],
                'totals' => [
                    'total' => 0,
                    'total_precio' => 0,
                    'total_costo_real' => 0,
                    'total_margen' => 0,
                ],
                'all_items' => [],
            ];
        }
    }
}
