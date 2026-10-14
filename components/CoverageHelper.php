<?php

namespace app\components;

use Yii;
use app\models\Contratos;

/**
 * CoverageHelper
 *
 * Encapsulates the logic for determining an affiliate's CONTINUOUS COVERAGE
 * chain across multiple individual contracts.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * BUSINESS RULES
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *   1. An affiliate may hold many contracts over time (renewals, plan changes).
 *
 *   2. Two consecutive contracts are considered CONTINUOUS when the gap
 *      between the previous contract's `fecha_ven` and the next contract's
 *      `fecha_ini` is LESS THAN OR EQUAL TO MAX_GAP_DAYS (5 days).
 *
 *   3. The WAITING PERIOD (`plazo_espera`) of a medical service is measured
 *      from the START of the OLDEST contract in the continuous chain that
 *      leads up to the contract under evaluation — NOT from the current
 *      contract's `fecha_ini`.
 *
 *   4. If a gap of MORE THAN 5 days exists between two contracts, the chain
 *      BREAKS: the waiting period resets to the start of the first contract
 *      AFTER the gap.
 *
 *   5. Annulled contracts are SKIPPED entirely. Skipping one may create a
 *      gap that breaks the chain.
 *
 *   6. Future contracts (fecha_ini > today) are NOT part of the chain when
 *      evaluating the current coverage.
 *
 *   7. The chain is built BACKWARDS from the REFERENCE contract (typically
 *      the affiliate's current active contract).
 *
 *   8. The `cantidad_limite` (usage limit) is NOT affected by this helper:
 *      it is always counted per calendar year of vigencia (current behavior).
 * ─────────────────────────────────────────────────────────────────────────────
 */
class CoverageHelper
{
    /**
     * Maximum number of days between the end of one contract and the start
     * of the next for them to be considered part of the same continuous
     * coverage chain.
     */
    public const MAX_GAP_DAYS = 5;

    // ============================================================
    // PUBLIC API
    // ============================================================

    /**
     * Returns the "effective coverage start date" for a user: the
     * `fecha_ini` of the OLDEST contract in the continuous chain that
     * leads up to (and includes) the reference contract.
     *
     * @param  int       $userId
     * @param  int|null  $referenceContractId  The contract under evaluation.
     *                                         If null, the most recent non-annulled
     *                                         contract with fecha_ini <= today is used.
     * @return string|null  Y-m-d  or null when no contract exists.
     */
    public static function getEffectiveCoverageStartDate(
        int $userId,
        ?int $referenceContractId = null
    ): ?string {
        $chain = self::getContinuousContractChain($userId, $referenceContractId);

        if (empty($chain)) {
            return null;
        }

        return $chain[0]->fecha_ini;
    }

    /**
     * Returns the ordered list of contracts (OLDEST → NEWEST) that form the
     * continuous coverage chain ending at the reference contract.
     *
     * @param  int       $userId
     * @param  int|null  $referenceContractId
     * @return Contratos[]
     */
    public static function getContinuousContractChain(
        int $userId,
        ?int $referenceContractId = null
    ): array {
        if (!$userId) {
            return [];
        }

        $reference = self::resolveReferenceContract($userId, $referenceContractId);

        if (!$reference) {
            return [];
        }

        // Load all non-annulled contracts for this user, oldest first.
        $allContracts = Contratos::find()
            ->where(['user_id' => $userId])
            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
            ->andWhere(['IS NOT', 'fecha_ini', null])
            ->andWhere(['IS NOT', 'fecha_ven', null])
            ->orderBy(['fecha_ini' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if (empty($allContracts)) {
            return [];
        }

        // Exclude future contracts (fecha_ini > today) — rule 6.
        $today = date('Y-m-d');
        $past = array_values(array_filter($allContracts, function ($c) use ($today) {
            return $c->fecha_ini <= $today;
        }));

        // Locate the reference in the past-filtered list.
        $refIndex = null;
        foreach ($past as $i => $c) {
            if ((int)$c->id === (int)$reference->id) {
                $refIndex = $i;
                break;
            }
        }

        // If the reference is future-dated or not in the list, fall back to
        // the last contract in the list.
        if ($refIndex === null) {
            if (empty($past)) {
                return [];
            }
            $refIndex = count($past) - 1;
        }

        // Walk backwards, building the chain.
        $chain = [$past[$refIndex]];
        $cursor = $refIndex;

        while ($cursor > 0) {
            $previous = $past[$cursor - 1];
            $current  = $past[$cursor];

            if (self::isContinuous($previous, $current)) {
                array_unshift($chain, $previous);
                $cursor--;
                continue;
            }

            // Gap too big → stop.
            break;
        }

        return $chain;
    }

    /**
     * Returns the number of full months elapsed since the effective coverage
     * start date, up to today.
     *
     * @return int  -1 when no coverage exists.
     */
    public static function getMonthsOfContinuousCoverage(
        int $userId,
        ?int $referenceContractId = null
    ): int {
        $start = self::getEffectiveCoverageStartDate($userId, $referenceContractId);

        if (!$start) {
            return -1;
        }

        try {
            $startDate = new \DateTime($start);
            $today     = new \DateTime();
            $diff      = $startDate->diff($today);

            return ($diff->y * 12) + $diff->m;
        } catch (\Exception $e) {
            Yii::error(
                "CoverageHelper::getMonthsOfContinuousCoverage failed: " . $e->getMessage(),
                __METHOD__
            );
            return -1;
        }
    }

    /**
     * Returns true when the affiliate has satisfied a waiting period of
     * $plazoEsperaMonths, measured from the effective coverage start date.
     *
     * @param  int  $plazoEsperaMonths  The service's required waiting period.
     */
    public static function isWaitingPeriodSatisfied(
        int $userId,
        int $plazoEsperaMonths,
        ?int $referenceContractId = null
    ): bool {
        if ($plazoEsperaMonths <= 0) {
            return true;
        }

        $months = self::getMonthsOfContinuousCoverage($userId, $referenceContractId);

        if ($months < 0) {
            return false;
        }

        return $months >= $plazoEsperaMonths;
    }

    /**
     * Returns the date on which the waiting period WILL be satisfied,
     * assuming the contract remains active and continuous.
     *
     * @return string|null  Y-m-d  or null when it is already satisfied
     *                       or when there is no coverage.
     */
    public static function getWaitingPeriodTargetDate(
        int $userId,
        int $plazoEsperaMonths,
        ?int $referenceContractId = null
    ): ?string {
        if ($plazoEsperaMonths <= 0) {
            return null;
        }

        $start = self::getEffectiveCoverageStartDate($userId, $referenceContractId);

        if (!$start) {
            return null;
        }

        try {
            $target = new \DateTime($start);
            $target->modify("+{$plazoEsperaMonths} months");

            return $target->format('Y-m-d');
        } catch (\Exception $e) {
            Yii::error(
                "CoverageHelper::getWaitingPeriodTargetDate failed: " . $e->getMessage(),
                __METHOD__
            );
            return null;
        }
    }

    /**
     * Returns an informative array describing the coverage continuity for
     * a user, suitable for rendering in the UI.
     *
     * @return array{
     *     has_coverage: bool,
     *     effective_start: ?string,
     *     months_covered: int,
     *     contracts_in_chain: int,
     *     chain_contract_ids: int[],
     *     broken_before: ?array
     * }
     */
    public static function getCoverageSummary(
        int $userId,
        ?int $referenceContractId = null
    ): array {
        $chain = self::getContinuousContractChain($userId, $referenceContractId);

        if (empty($chain)) {
            return [
                'has_coverage'        => false,
                'effective_start'     => null,
                'months_covered'      => -1,
                'contracts_in_chain'  => 0,
                'chain_contract_ids'  => [],
                'broken_before'       => null,
            ];
        }

        $start = $chain[0]->fecha_ini;
        $startDate = new \DateTime($start);
        $today = new \DateTime();
        $diff = $startDate->diff($today);
        $months = ($diff->y * 12) + $diff->m;

        // Was there a contract BEFORE the chain that got excluded due to a gap?
        $broken = null;
        $oldestInChain = $chain[0];

        $candidatesBefore = Contratos::find()
            ->where(['user_id' => $userId])
            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
            ->andWhere(['IS NOT', 'fecha_ini', null])
            ->andWhere(['IS NOT', 'fecha_ven', null])
            ->andWhere(['<', 'fecha_ven', $oldestInChain->fecha_ini])
            ->orderBy(['fecha_ven' => SORT_DESC])
            ->limit(1)
            ->one();

        if ($candidatesBefore && !self::isContinuous($candidatesBefore, $oldestInChain)) {
            $gapDays = self::daysBetween($candidatesBefore->fecha_ven, $oldestInChain->fecha_ini);
            $broken = [
                'previous_contract_id'  => (int)$candidatesBefore->id,
                'previous_fecha_ven'    => $candidatesBefore->fecha_ven,
                'current_fecha_ini'     => $oldestInChain->fecha_ini,
                'gap_days'              => $gapDays,
                'reason'                => "Gap of {$gapDays} days exceeds the "
                    . self::MAX_GAP_DAYS . "-day continuity threshold.",
            ];
        }

        return [
            'has_coverage'       => true,
            'effective_start'    => $start,
            'months_covered'     => $months,
            'contracts_in_chain' => count($chain),
            'chain_contract_ids' => array_map(fn($c) => (int)$c->id, $chain),
            'broken_before'      => $broken,
        ];
    }

    // ============================================================
    // INTERNAL HELPERS
    // ============================================================

    /**
     * Determine which contract should be used as the reference for the chain.
     *
     * Priority:
     *   1. The explicitly provided contract (if it exists and belongs to the user).
     *   2. The user's current ACTIVE contract (fecha_ini <= today <= fecha_ven,
     *      estatus not Anulado, preferably Activo/Registrado).
     *   3. The most recent non-annulled contract with fecha_ini <= today.
     *   4. The most recent non-annulled contract overall.
     */
    protected static function resolveReferenceContract(
        int $userId,
        ?int $referenceContractId
    ): ?Contratos {
        if ($referenceContractId) {
            $explicit = Contratos::findOne([
                'id'      => $referenceContractId,
                'user_id' => $userId,
            ]);
            if ($explicit && $explicit->estatus !== Contratos::STATUS_ANULADO) {
                return $explicit;
            }
        }

        $today = date('Y-m-d');

        // Current active contract
        $active = Contratos::find()
            ->where(['user_id' => $userId])
            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
            ->andWhere(['<=', 'fecha_ini', $today])
            ->andWhere(['>=', 'fecha_ven', $today])
            ->orderBy(['fecha_ini' => SORT_DESC, 'id' => SORT_DESC])
            ->one();

        if ($active) {
            return $active;
        }

        // Most recent non-annulled contract with fecha_ini <= today
        $recentPast = Contratos::find()
            ->where(['user_id' => $userId])
            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
            ->andWhere(['<=', 'fecha_ini', $today])
            ->orderBy(['fecha_ini' => SORT_DESC, 'id' => SORT_DESC])
            ->one();

        if ($recentPast) {
            return $recentPast;
        }

        // Fall back to the most recent non-annulled contract (even if future)
        return Contratos::find()
            ->where(['user_id' => $userId])
            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
            ->orderBy(['fecha_ini' => SORT_DESC, 'id' => SORT_DESC])
            ->one();
    }

    /**
     * Returns true when two contracts are continuous:
     *   gap between previous.fecha_ven and next.fecha_ini <= MAX_GAP_DAYS.
     *
     * Overlapping contracts (next.fecha_ini <= previous.fecha_ven) are
     * considered continuous (gap <= 0).
     */
    protected static function isContinuous(Contratos $previous, Contratos $next): bool
    {
        if (empty($previous->fecha_ven) || empty($next->fecha_ini)) {
            return false;
        }

        $gap = self::daysBetween($previous->fecha_ven, $next->fecha_ini);

        return $gap >= 0 ? $gap <= self::MAX_GAP_DAYS : true;
    }

    /**
     * Signed day count between two Y-m-d strings.
     * Positive when $to > $from.
     */
    protected static function daysBetween(string $from, string $to): int
    {
        try {
            $fromDate = new \DateTime($from);
            $toDate   = new \DateTime($to);
            $interval = $fromDate->diff($toDate);

            // diff() always returns positive; use %r for sign.
            $days = (int)$interval->days;

            return $interval->invert ? -$days : $days;
        } catch (\Exception $e) {
            return PHP_INT_MAX;
        }
    }
    /**
     * Returns the display state of a single service's waiting period,
     * given the affiliate's continuous coverage history.
     *
     * Returns:
     *   [
     *     'status'          => 'sin_espera' | 'pendiente' | 'no_aplica' | 'sin_plazo',
     *     'label'           => 'Sin espera' | '12' | 'NO APLICA' | '—',
     *     'months_required' => int,
     *     'months_covered'  => int,
     *     'is_renewal'      => bool,
     *   ]
     *
     * Evaluation order (first match wins):
     *   0. Service name is MATERNIDAD              → 'no_aplica'  (label: 'NO APLICA')
     *   1. Raw value is "NO APLICA"                → 'no_aplica'  (label: 'NO APLICA')
     *   2. Raw value is "SIN P/E" / "SIN PE"       → 'sin_espera' (label: 'Sin espera')
     *   3. Numeric N, chain = 1                    → 'pendiente'  (label: 'N')
     *   4. Numeric N, chain ≥ 2, covered ≥ N       → 'sin_espera' (label: 'Sin espera')
     *   5. Numeric N, chain ≥ 2, covered < N       → 'pendiente'  (label: 'N')
     *   6. Empty / null / 0 / other non-numeric    → 'sin_plazo'  (label: '—')
     *
     * @param  int    $userId
     * @param  int    $referenceContractId  The NEW (current) contract ID
     * @param  mixed  $plazoEspera          Raw plazo_espera value from PlanServicios
     * @param  string $serviceName          Service name (used for the MATERNIDAD override)
     * @return array
     */
    public static function getPlazoEsperaDisplay(
        int $userId,
        int $referenceContractId,
        $plazoEspera,
        string $serviceName = ''
    ): array {

        // ════════════════════════════════════════════════════════════
        // RULE 0 — MATERNIDAD hard override
        // ------------------------------------------------------------
        // The MATERNIDAD service is optional in every plan and its
        // P/E must always read "NO APLICA", regardless of the raw
        // value, the continuity chain, or anything else.
        //
        // Match: case-insensitive substring "maternidad". This covers
        // variants like "MATERNIDAD", "Maternidad", "MATERNIDAD
        // (opcional si lo desea)", "Maternidad - opcional", etc.
        // ════════════════════════════════════════════════════════════
        $serviceNameUpper = mb_strtoupper(trim((string)$serviceName), 'UTF-8');
        if ($serviceNameUpper !== '' && mb_strpos($serviceNameUpper, 'MATERNIDAD', 0, 'UTF-8') !== false) {
            return [
                'status'          => 'no_aplica',
                'label'           => 'NO APLICA',
                'months_required' => 0,
                'months_covered'  => 0,
                'is_renewal'      => false,
            ];
        }

        // ── Null / empty ──
        if ($plazoEspera === null || $plazoEspera === '') {
            return [
                'status'          => 'sin_plazo',
                'label'           => '—',
                'months_required' => 0,
                'months_covered'  => 0,
                'is_renewal'      => false,
            ];
        }

        $raw      = trim((string)$plazoEspera);
        $rawUpper = mb_strtoupper($raw, 'UTF-8');

        // ════════════════════════════════════════════════════════════
        // RULE 1 — "NO APLICA" → unchanged
        // ════════════════════════════════════════════════════════════
        if ($rawUpper === 'NO APLICA') {
            return [
                'status'          => 'no_aplica',
                'label'           => 'NO APLICA',
                'months_required' => 0,
                'months_covered'  => 0,
                'is_renewal'      => false,
            ];
        }

        // ════════════════════════════════════════════════════════════
        // RULE 2 — "SIN P/E" / "SIN PE" → "Sin espera"
        // ════════════════════════════════════════════════════════════
        $compact = preg_replace('/\s+/', '', $rawUpper);
        if ($compact === 'SINP/E' || $compact === 'SINPE') {
            $chain = self::getContinuousContractChain($userId, $referenceContractId);
            return [
                'status'          => 'sin_espera',
                'label'           => 'Sin espera',
                'months_required' => 0,
                'months_covered'  => self::getMonthsOfContinuousCoverage($userId, $referenceContractId),
                'is_renewal'      => count($chain) >= 2,
            ];
        }

        // ── Extract numeric value ──
        $monthsRequired = 0;
        if (is_numeric($raw)) {
            $monthsRequired = (int)$raw;
        } elseif (preg_match('/\d+/', $raw, $m)) {
            $monthsRequired = (int)$m[0];
        }

        // ════════════════════════════════════════════════════════════
        // RULE 6 — no meaningful numeric value
        // ════════════════════════════════════════════════════════════
        if ($monthsRequired <= 0) {
            return [
                'status'          => 'sin_plazo',
                'label'           => '—',
                'months_required' => 0,
                'months_covered'  => 0,
                'is_renewal'      => false,
            ];
        }

        // ════════════════════════════════════════════════════════════
        // RULES 3 / 4 / 5 — numeric value with continuity logic
        // ════════════════════════════════════════════════════════════
        $monthsCovered = self::getMonthsOfContinuousCoverage($userId, $referenceContractId);
        $chain         = self::getContinuousContractChain($userId, $referenceContractId);
        $isRenewal     = count($chain) >= 2;

        if ($isRenewal && $monthsCovered >= 0 && $monthsCovered >= $monthsRequired) {
            return [
                'status'          => 'sin_espera',
                'label'           => 'Sin espera',
                'months_required' => $monthsRequired,
                'months_covered'  => $monthsCovered,
                'is_renewal'      => true,
            ];
        }

        return [
            'status'          => 'pendiente',
            'label' => $monthsRequired . ' mes' . ($monthsRequired === 1 ? '' : 'es'),
            'months_required' => $monthsRequired,
            'months_covered'  => $monthsCovered,
            'is_renewal'      => $isRenewal,
        ];
    }
}
