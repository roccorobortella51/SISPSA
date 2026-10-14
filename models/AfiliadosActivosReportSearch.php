<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\components\UserHelper;
use yii\helpers\ArrayHelper;

/**
 * AfiliadosActivosReportSearch
 *
 * Calculates the number of "ACTIVE" affiliates at the end of each month.
 *
 * DEFINITION OF ACTIVE:
 *   An affiliate is ACTIVE at end of month M if:
 *   1. Had a contract existing during month M (not Anulado, not deleted)
 *   2. No cuota had estatus = 'vencida' as of end of month M
 *
 * Counting is by UNIQUE USER (one person = one count).
 */
class AfiliadosActivosReportSearch extends Model
{
    public $clinica_ids = [];
    public $user_datos_type_id;
    public $plan_id;

    /** @var bool True if current user has clinic-level restriction */
    private $_hasClinicRestriction = false;

    /** @var array|null IDs of clinics the user can access (null = all) */
    private $_accessibleClinicaIds = null;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $user = Yii::$app->user;
        $isSuperAdmin = $user->can('superadmin') || $user->can('admin');

        if (!$isSuperAdmin && UserHelper::hasClinicAccess()) {
            $this->_hasClinicRestriction = true;
            $accessibleClinicas = UserHelper::getAccessibleClinicas();
            $this->_accessibleClinicaIds = ArrayHelper::getColumn($accessibleClinicas, 'id');
        } else {
            $this->_hasClinicRestriction = false;
            $this->_accessibleClinicaIds = null;
        }
    }

    public function rules()
    {
        return [
            [['clinica_ids'], 'each', 'rule' => ['integer']],
            [['user_datos_type_id', 'plan_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'clinica_ids' => 'Clínicas',
            'user_datos_type_id' => 'Tipo de Afiliado',
            'plan_id' => 'Plan',
        ];
    }

    /**
     * True if current user has clinic-level restriction (only sees their own clinic).
     * @return bool
     */
    public function hasClinicRestriction()
    {
        return $this->_hasClinicRestriction;
    }

    /**
     * Get the clinics available to display in the filter UI.
     * - Restricted users: only their clinic(s)
     * - Everyone else: all clinics
     *
     * @return \app\models\RmClinica[]
     */
    public function getAvailableClinicas()
    {
        if ($this->_hasClinicRestriction) {
            return UserHelper::getAccessibleClinicas();
        }

        return RmClinica::find()
            ->where(['IS', 'deleted_at', null])
            ->orderBy(['nombre' => SORT_ASC])
            ->all();
    }

    /**
     * Resolve the effective clinic filter:
     *   - If restricted user → force their clinic IDs, ignore selection
     *   - Else if user selected clinics → use those
     *   - Else → null (all clinics)
     *
     * @return array|null
     */
    public function getFilteredClinicaIds()
    {
        // Restricted users are ALWAYS limited to their clinics, no matter what they submit
        if ($this->_hasClinicRestriction) {
            return !empty($this->_accessibleClinicaIds) ? $this->_accessibleClinicaIds : null;
        }

        // Free users: respect the submitted selection
        if (!empty($this->clinica_ids) && is_array($this->clinica_ids)) {
            $filtered = array_filter($this->clinica_ids, function ($id) {
                return !empty($id) && is_numeric($id);
            });
            return !empty($filtered) ? array_map('intval', $filtered) : null;
        }

        return null;
    }

    /**
     * Build the list of the last 12 months.
     * @return array ['keys' => [...], 'labels' => [...], 'starts' => [...], 'ends' => [...]]
     */
    public function getLast12Months()
    {
        $keys = [];
        $labels = [];
        $starts = [];
        $ends = [];

        $monthNames = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ];

        for ($i = 11; $i >= 0; $i--) {
            $ts = strtotime("-{$i} months", strtotime(date('Y-m-01')));
            $keys[] = date('Y-m', $ts);
            $labels[] = $monthNames[(int)date('n', $ts)] . ' ' . substr(date('Y', $ts), -2);
            $starts[] = date('Y-m-01', $ts);
            $ends[] = date('Y-m-t', $ts);
        }

        return [
            'keys' => $keys,
            'labels' => $labels,
            'starts' => $starts,
            'ends' => $ends,
        ];
    }

    /**
     * Get total active affiliates per month (overall).
     */
    public function getActiveAffiliatesByMonth()
    {
        $months = $this->getLast12Months();
        $filteredClinicaIds = $this->getFilteredClinicaIds();

        $totals = [];
        foreach ($months['keys'] as $index => $key) {
            $totals[$key] = $this->countActiveAffiliatesForMonth(
                $months['starts'][$index],
                $months['ends'][$index],
                $filteredClinicaIds
            );
        }

        return [
            'keys' => $months['keys'],
            'labels' => $months['labels'],
            'totals' => array_values($totals),
        ];
    }

    /**
     * Get active affiliates per clinic per month (matrix).
     */
    public function getActiveAffiliatesByClinic()
    {
        $months = $this->getLast12Months();
        $filteredClinicaIds = $this->getFilteredClinicaIds();

        // Load clinics to display
        $clinicsQuery = RmClinica::find()
            ->select(['id', 'nombre'])
            ->where(['IS', 'deleted_at', null]);

        if ($filteredClinicaIds !== null && !empty($filteredClinicaIds)) {
            $clinicsQuery->andWhere(['id' => $filteredClinicaIds]);
        }

        $clinics = $clinicsQuery->orderBy(['nombre' => SORT_ASC])->asArray()->all();

        $clinicMatrix = [];
        $monthlyTotals = array_fill_keys($months['keys'], 0);
        $grandTotal = 0;

        foreach ($clinics as $clinic) {
            $clinicId = $clinic['id'];
            $row = [
                'clinica_id'      => $clinicId,
                'clinica_nombre'  => $clinic['nombre'],
                'months'          => array_fill_keys($months['keys'], 0),
                'total'           => 0,
                'growth_rate'     => null,
                'growth_status'   => 'na',
                'growth_months'   => 0,
                'begin_value'     => 0,
                'end_value'       => 0,
            ];

            foreach ($months['keys'] as $index => $key) {
                $count = $this->countActiveAffiliatesForMonth(
                    $months['starts'][$index],
                    $months['ends'][$index],
                    [$clinicId]
                );
                $row['months'][$key] = $count;
                $row['total'] += $count;
                $monthlyTotals[$key] += $count;
                $grandTotal += $count;
            }

            // ============================================================
            // Calculate monthly trend (linear regression slope, normalized)
            //   Uses ALL active months (from the first non-zero month).
            // ============================================================
            $trend = $this->calculateTrend($row['months'], $months['keys']);

            $row['growth_rate']   = $trend['rate'];
            $row['growth_status'] = $trend['status'];
            $row['growth_months'] = $trend['months'];
            $row['begin_value']   = $trend['begin_value'];
            $row['end_value']     = $trend['end_value'];

            // ⬇️ THIS IS THE MISSING LINE THAT FIXES THE BUG
            if ($row['total'] > 0) {
                $clinicMatrix[] = $row;
            }
        }

        // Sort by growth_rate DESC (best performers first)
        // Clinics without a comparable growth_rate go to the bottom,
        // and within them, the ones with more total activity come first.
        usort($clinicMatrix, function ($a, $b) {
            $aRate = $a['growth_rate'] ?? null;
            $bRate = $b['growth_rate'] ?? null;

            // 1. Both have a numeric rate → higher wins
            if ($aRate !== null && $bRate !== null) {
                if ($aRate === $bRate) {
                    return ($b['total'] ?? 0) <=> ($a['total'] ?? 0);
                }
                return $bRate <=> $aRate; // descending
            }

            // 2. Only $a has a numeric rate → $a comes first
            if ($aRate !== null && $bRate === null) {
                return -1;
            }

            // 3. Only $b has a numeric rate → $b comes first
            if ($aRate === null && $bRate !== null) {
                return 1;
            }

            // 4. Neither has a numeric rate → sort by total activity
            return ($b['total'] ?? 0) <=> ($a['total'] ?? 0);
        });

        return [
            'month_keys'     => $months['keys'],
            'month_labels'   => $months['labels'],
            'clinics'        => $clinicMatrix,
            'monthly_totals' => $monthlyTotals,
            'grand_total'    => $grandTotal,
        ];
    }

    /**
     * Calculate the monthly trend (linear regression slope, normalized by mean).
     *
     * Uses ALL active months (from the first month with activity).
     *
     *   slope = (n·Σxy − Σx·Σy) / (n·Σx² − (Σx)²)
     *   rate  = (slope / mean_y) × 100   →  %/mes
     *
     * Status:
     *   - 'up'           : rate ≥ +1.0 %/mes
     *   - 'down'         : rate ≤ -1.0 %/mes
     *   - 'flat'         : |rate| < 1.0 %/mes
     *   - 'insufficient' : fewer than 3 active months
     *   - 'zero'         : no activity at all
     *
     * @param array $monthValues  Map of month_key => count (ordered oldest → newest)
     * @param array $monthKeys    Ordered list of month keys (oldest → newest)
     * @return array{rate: float|null, status: string, months: int, begin_value: int, end_value: int}
     */
    private function calculateTrend(array $monthValues, array $monthKeys): array
    {
        $result = [
            'rate'        => null,
            'status'      => 'zero',
            'months'      => 0,
            'begin_value' => 0,
            'end_value'   => 0,
        ];

        $count = count($monthKeys);
        if ($count < 2) {
            return $result;
        }

        // ------------------------------------------------------------
        // Slice off leading zeros — start where the clinic first had activity
        // ------------------------------------------------------------
        $firstActiveIdx = null;
        foreach ($monthKeys as $i => $key) {
            if ((int)($monthValues[$key] ?? 0) > 0) {
                $firstActiveIdx = $i;
                break;
            }
        }

        if ($firstActiveIdx === null) {
            $result['status'] = 'zero';
            return $result;
        }

        $sliceKeys = array_slice($monthKeys, $firstActiveIdx);
        $y = [];
        foreach ($sliceKeys as $key) {
            $y[] = (int)($monthValues[$key] ?? 0);
        }

        $n = count($y);
        $result['months']      = $n;
        $result['begin_value'] = $y[0];
        $result['end_value']   = $y[$n - 1];

        // Need at least 3 active months to trust a regression
        if ($n < 3) {
            $result['status'] = 'insufficient';
            return $result;
        }

        // ------------------------------------------------------------
        // Linear regression slope (closed-form least squares)
        // x = 0, 1, ..., n-1
        // ------------------------------------------------------------
        $sumX  = 0;
        $sumY  = 0;
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumX  += $i;
            $sumY  += $y[$i];
            $sumXY += $i * $y[$i];
            $sumX2 += $i * $i;
        }

        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        if ($denominator === 0) {
            $result['status'] = 'flat';
            return $result;
        }

        $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;

        $meanY = $sumY / $n;
        if ($meanY == 0) {
            $result['status'] = 'flat';
            return $result;
        }

        $rate = ($slope / $meanY) * 100;   // %/mes relative to average size

        $result['rate'] = round($rate, 1);

        // ------------------------------------------------------------
        // Absolute movement check — guards against tiny clinics
        // where a single ±1 wobble produces a misleading % rate.
        //
        //   fitted_movement = |slope| × (n − 1)
        //
        // This is how many affiliates the fitted line travels from the
        // first month to the last. If it's less than 1 affiliate, the
        // clinic is effectively flat — no matter what the % says.
        // ------------------------------------------------------------
        $fittedMovement = abs($slope) * ($n - 1);

        // Also require the endpoints to actually differ
        $endpointDelta = abs($y[$n - 1] - $y[0]);

        if ($fittedMovement < 1.0 && $endpointDelta < 2) {
            // Too little real movement — treat as flat
            $result['status'] = 'flat';
            $result['rate']   = 0.0;
            return $result;
        }

        // ------------------------------------------------------------
        // Percentage thresholds
        // ------------------------------------------------------------
        if ($rate >= 1.0) {
            $result['status'] = 'up';
        } elseif ($rate <= -1.0) {
            $result['status'] = 'down';
        } else {
            $result['status'] = 'flat';
        }

        return $result;
    }

    /**
     * Core method: count active affiliates for a single month.
     */
    public function countActiveAffiliatesForMonth($monthStart, $monthEnd, $clinicaIds = null)
    {
        $db = Yii::$app->db;

        $where = [];
        $params = [
            ':monthStart' => $monthStart,
            ':monthEnd' => $monthEnd,
        ];

        if (!empty($clinicaIds)) {
            $placeholders = [];
            foreach ($clinicaIds as $i => $id) {
                $ph = ":clinic_{$i}";
                $placeholders[] = $ph;
                $params[$ph] = (int)$id;
            }
            // Filter at the CONTRACT level (a user might have contracts in multiple clinics)
            $where[] = "EXISTS (
                SELECT 1 FROM contratos cc
                WHERE cc.user_id = ud.id
                  AND cc.clinica_id IN (" . implode(',', $placeholders) . ")
                  AND cc.deleted_at IS NULL
                  AND cc.estatus != 'Anulado'
            )";
        }

        if (!empty($this->user_datos_type_id)) {
            $where[] = "ud.user_datos_type_id = :user_type";
            $params[':user_type'] = (int)$this->user_datos_type_id;
        }

        if (!empty($this->plan_id)) {
            $where[] = "ud.plan_id = :plan_id";
            $params[':plan_id'] = (int)$this->plan_id;
        }

        $whereSql = !empty($where) ? 'AND ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT COUNT(DISTINCT ud.id)
            FROM user_datos ud
            WHERE ud.role = 'afiliado'
              AND ud.deleted_at IS NULL
              {$whereSql}

              AND EXISTS (
                  SELECT 1
                  FROM contratos c
                  WHERE c.user_id = ud.id
                    AND c.deleted_at IS NULL
                    AND c.estatus != 'Anulado'
                    AND c.fecha_ini <= :monthEnd
                    AND (c.fecha_ven IS NULL OR c.fecha_ven >= :monthStart)
              )

              AND NOT EXISTS (
                  SELECT 1
                  FROM contratos c2
                  INNER JOIN cuotas cu ON cu.contrato_id = c2.id
                  WHERE c2.user_id = ud.id
                    AND c2.deleted_at IS NULL
                    AND c2.estatus != 'Anulado'
                    AND cu.estatus = 'vencida'
                    AND cu.fecha_vencimiento <= :monthEnd
              )
        ";

        try {
            return (int)$db->createCommand($sql, $params)->queryScalar();
        } catch (\Exception $e) {
            Yii::error("Error counting active affiliates for {$monthStart} to {$monthEnd}: " . $e->getMessage(), 'afiliados-activos');
            return 0;
        }
    }

    /**
     * Summary stats for KPI cards.
     */
    public function getSummaryStats()
    {
        $monthly = $this->getActiveAffiliatesByMonth();
        $totals = $monthly['totals'];
        $labels = $monthly['labels'];

        if (empty($totals)) {
            return [
                'current' => 0,
                'previous' => 0,
                'growth_rate' => 0,
                'best_month_label' => '-',
                'best_month_count' => 0,
                'worst_month_label' => '-',
                'worst_month_count' => 0,
                'peak_label' => '-',
                'peak_count' => 0,
            ];
        }

        $current = end($totals);
        $previous = count($totals) > 1 ? $totals[count($totals) - 2] : $current;

        // Overall trend — uses the same 12-month window as the matrix
        $overallTrend = $this->calculateTrend(
            array_combine($monthly['keys'], $totals),
            $monthly['keys']
        );
        $growthRate = $overallTrend['rate'] ?? 0;

        $maxCount = max($totals);
        $minCount = min($totals);
        $maxIndex = array_search($maxCount, $totals);
        $minIndex = array_search($minCount, $totals);

        return [
            'current' => $current,
            'previous' => $previous,
            'growth_rate' => $growthRate,
            'growth_status' => $overallTrend['status'] ?? 'flat',
            'best_month_label' => $labels[$maxIndex] ?? '-',
            'best_month_count' => $maxCount,
            'worst_month_label' => $labels[$minIndex] ?? '-',
            'worst_month_count' => $minCount,
            'peak_label' => $labels[$maxIndex] ?? '-',
            'peak_count' => $maxCount,
        ];
    }
}
