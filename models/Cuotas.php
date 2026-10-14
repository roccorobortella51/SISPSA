<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cuotas".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $contrato_id
 * @property string|null $fecha_vencimiento
 * @property float|null $monto
 * @property string|null $estatus
 * @property string|null $fecha_pago
 * @property float|null $rate_usd_bs
 * @property float|null $monto_usd
 * @property int|null $id_pago
 * @property int|null $numero_cuota
 * @property string|null $coverage_start
 * @property string|null $coverage_end
 */
class Cuotas extends \yii\db\ActiveRecord
{
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_PAGADA = 'pagada';
    const ESTADO_VENCIDA = 'vencida';
    const ESTADO_ANULADA = 'anulada';
    const ESTADO_GRACE_PERIOD = 'en_gracias';

    // Grace period configuration (7 days as per CuotaController)
    const GRACE_PERIOD_DAYS = 7;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cuotas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contrato_id', 'fecha_vencimiento', 'monto', 'numero_cuota'], 'required'],
            [['contrato_id', 'id_pago', 'numero_cuota'], 'default', 'value' => null],
            [['contrato_id', 'id_pago', 'numero_cuota'], 'integer'],
            [['created_at', 'fecha_vencimiento', 'fecha_pago', 'coverage_start', 'coverage_end'], 'safe'],
            [['monto', 'rate_usd_bs', 'monto_usd'], 'number'],
            [['monto', 'monto_usd'], 'number', 'numberPattern' => '/^\d+(\.\d{1,2})?$/'], // 2 decimal validation
            [['estatus'], 'string', 'max' => 20],
            [['numero_cuota'], 'integer', 'min' => 1, 'max' => 12],
            ['numero_cuota', 'validateUniqueCuota'],
        ];
    }

    /**
     * Ensure each cuota number is unique per contract
     */
    public function validateUniqueCuota($attribute, $params)
    {
        if ($this->isNewRecord) {
            $exists = self::find()
                ->where([
                    'contrato_id' => $this->contrato_id,
                    'numero_cuota' => $this->numero_cuota
                ])
                ->exists();

            if ($exists) {
                $this->addError($attribute, "La cuota #{$this->numero_cuota} ya existe para este contrato.");
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Ensure monto and monto_usd always have 2 decimal places
            if ($this->monto !== null) {
                $this->monto = round($this->monto, 2);
            }
            if ($this->monto_usd !== null) {
                $this->monto_usd = round($this->monto_usd, 2);
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'contrato_id' => 'Contrato ID',
            'fecha_vencimiento' => 'Fecha Vencimiento',
            'monto' => 'Monto',
            'estatus' => 'Estatus',
            'fecha_pago' => 'Fecha Pago',
            'rate_usd_bs' => 'Rate Usd Bs',
            'monto_usd' => 'Monto USD',
            'id_pago' => 'ID Pago',
            'numero_cuota' => 'Número de Cuota',
            'coverage_start' => 'Inicio de Cobertura',
            'coverage_end' => 'Fin de Cobertura',
        ];
    }

    /**
     * Get the contract associated with this cuota
     */
    public function getContrato()
    {
        return $this->hasOne(Contratos::class, ['id' => 'contrato_id']);
    }

    /**
     * Get payment associated with this cuota
     */
    public function getPago()
    {
        return $this->hasOne(Pagos::class, ['id' => 'id_pago']);
    }

    // ============================================================
    // PRORATION HELPERS
    // ============================================================

    /**
     * Returns true when this cuota is a PRORATED (partial-month) cuota,
     * i.e., its coverage window is shorter than a full calendar month.
     *
     * The determination is based purely on the coverage dates, so it
     * works for cuotas created by any generator (prorated or aligned).
     *
     * @return bool
     */
    public function getIsProrated()
    {
        if (empty($this->coverage_start) || empty($this->coverage_end)) {
            return false;
        }

        try {
            $start = new \DateTime($this->coverage_start);
            $end   = new \DateTime($this->coverage_end);

            $daysCovered = (int) $start->diff($end)->days + 1;

            // A full month is (days in start's calendar month).
            // Anything shorter than that is a prorated/partial cuota.
            $daysInStartMonth = (int) $start->format('t');

            return $daysCovered < $daysInStartMonth;
        } catch (\Exception $e) {
            Yii::warning(
                "getIsProrated failed for cuota #{$this->id}: " . $e->getMessage(),
                'cuotas'
            );
            return false;
        }
    }

    /**
     * Returns a small HTML badge marking this cuota as PRORRATED.
     * Returns an empty string when the cuota is not prorated.
     *
     * @return string
     */
    public function getProratedBadge()
    {
        if (!$this->getIsProrated()) {
            return '';
        }

        $tooltip = 'Cuota prorrateada: cubre solo una fracción del mes '
            . 'por incorporación a mitad de ciclo. El afiliado queda cubierto '
            . 'desde el primer día sin dejar un período sin cobertura médica.';

        return '<span class="badge badge-prorated" '
            . 'data-toggle="tooltip" data-placement="top" '
            . 'title="' . htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') . '">'
            . '<i class="fas fa-calculator mr-1"></i>PRORRATEADA'
            . '</span>';
    }

    // ============================================================
    // GENERATORS — Anniversary / Individual
    // ============================================================

    /**
     * Generate 12 cuotas based on monthly anniversary dates
     *
     * @param int $contrato_id
     * @param string $fecha_inicio Contract start date (YYYY-MM-DD)
     * @param float $monto Monthly amount
     * @return array ['success' => bool, 'cuotas' => array, 'error' => string]
     */
    public static function generateCuotasAnniversaryBased($contrato_id, $fecha_inicio, $monto)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $cuotas = [];
            $startDate = new \DateTime($fecha_inicio);
            $startDay = (int)$startDate->format('d');

            // Calculate contract end date: 1 year from start minus 1 day
            $contractEndDate = clone $startDate;
            $contractEndDate->modify('+1 year');
            $contractEndDate->modify('-1 day');

            // Update the contract with the correct end date
            $contrato = Contratos::findOne($contrato_id);
            if ($contrato) {
                $contrato->fecha_ven = $contractEndDate->format('Y-m-d');
                $contrato->save(false);
                Yii::info("Contrato #{$contrato_id} actualizado con fecha fin: {$contrato->fecha_ven}", 'cuotas');
            }

            // Log the calculation
            Yii::info("Contract dates - Start: {$fecha_inicio}, End: {$contractEndDate->format('Y-m-d')}, Total days: " . $startDate->diff($contractEndDate)->days + 1, 'cuotas');

            // Generate 12 cuotas
            for ($i = 1; $i <= 12; $i++) {
                $cuota = new self();
                $cuota->contrato_id = $contrato_id;
                $cuota->numero_cuota = $i;
                $cuota->monto = $monto;
                $cuota->monto_usd = $monto;
                $cuota->estatus = self::ESTADO_PENDIENTE;
                $cuota->rate_usd_bs = 1; // Default rate

                // Calculate due date: monthly anniversary
                $dueDate = clone $startDate;
                $dueDate->modify('+' . $i . ' months');

                // Handle month-end dates (e.g., Jan 31 -> Feb 28/29)
                if ($dueDate->format('d') != $startDay) {
                    // Day overflow occurred, use last day of the month
                    $dueDate->modify('last day of this month');
                }

                $cuota->fecha_vencimiento = $dueDate->format('Y-m-d');

                // Calculate coverage period
                $coverageStart = clone $startDate;
                if ($i > 1) {
                    $coverageStart->modify('+' . ($i - 1) . ' months');
                    // Handle month-end for coverage start as well
                    if ($coverageStart->format('d') != $startDay) {
                        $coverageStart->modify('last day of this month');
                    }
                }

                $coverageEnd = clone $dueDate;
                $coverageEnd->modify('-1 day');

                $cuota->coverage_start = $coverageStart->format('Y-m-d');
                $cuota->coverage_end = $coverageEnd->format('Y-m-d');

                if ($cuota->save()) {
                    $cuotas[] = $cuota;
                    Yii::info("Cuota #{$i} generada: Vence {$cuota->fecha_vencimiento}, Cubre {$cuota->coverage_start} al {$cuota->coverage_end}", 'cuotas');
                } else {
                    throw new \Exception('Error guardando cuota #' . $i . ': ' . json_encode($cuota->getErrors()));
                }
            }

            $transaction->commit();

            Yii::info("✅ Generadas 12 cuotas con vencimiento en aniversario para contrato #{$contrato_id}", 'cuotas');

            return [
                'success' => true,
                'cuotas' => $cuotas,
                'count' => count($cuotas),
                'message' => '12 cuotas generadas exitosamente con vencimiento en fecha aniversario'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("❌ Error generando cuotas: " . $e->getMessage(), 'cuotas');
            return [
                'success' => false,
                'cuotas' => [],
                'count' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate 12 cuotas with due dates on the same day as contract start
     * FIRST PAYMENT IS DUE ON THE START DATE ITSELF
     *
     * @param int $contrato_id
     * @param string $fecha_inicio Contract start date (YYYY-MM-DD)
     * @param float $monto Monthly amount
     * @return array ['success' => bool, 'cuotas' => array, 'error' => string]
     */
    public static function generateCuotasSimple($contrato_id, $fecha_inicio, $monto)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $cuotas = [];
            $startDate = new \DateTime($fecha_inicio);
            $startDay = (int)$startDate->format('d');

            // Calculate contract end date: 1 year from start minus 1 day
            $contractEndDate = clone $startDate;
            $contractEndDate->modify('+1 year');
            $contractEndDate->modify('-1 day');

            // Update the contract with the correct end date
            $contrato = Contratos::findOne($contrato_id);
            if ($contrato) {
                $contrato->fecha_ven = $contractEndDate->format('Y-m-d');
                $contrato->save(false);
                Yii::info("Contrato #{$contrato_id} actualizado con fecha fin: {$contrato->fecha_ven}", 'cuotas');
            }

            // Generate 12 cuotas
            for ($i = 1; $i <= 12; $i++) {
                $cuota = new self();
                $cuota->contrato_id = $contrato_id;
                $cuota->numero_cuota = $i;
                $cuota->monto = $monto;
                $cuota->monto_usd = $monto;
                $cuota->estatus = self::ESTADO_PENDIENTE;
                $cuota->rate_usd_bs = 1; // Default rate

                // Calculate due date: start date + (i-1) months
                $dueDate = clone $startDate;
                $dueDate->modify('+' . ($i - 1) . ' months');

                // Handle month-end dates (e.g., Jan 31 -> Feb 28)
                if ($dueDate->format('d') != $startDay) {
                    // Day overflow occurred, use last day of the month
                    $dueDate->modify('last day of this month');
                }

                $cuota->fecha_vencimiento = $dueDate->format('Y-m-d');

                // Calculate coverage period
                $coverageStart = clone $startDate;
                if ($i > 1) {
                    $coverageStart->modify('+' . ($i - 1) . ' months');
                    // Handle month-end for coverage start as well
                    if ($coverageStart->format('d') != $startDay) {
                        $coverageStart->modify('last day of this month');
                    }
                }

                $coverageEnd = clone $dueDate;
                $coverageEnd->modify('+1 month');
                $coverageEnd->modify('-1 day');

                $cuota->coverage_start = $coverageStart->format('Y-m-d');
                $cuota->coverage_end = $coverageEnd->format('Y-m-d');

                if ($cuota->save()) {
                    $cuotas[] = $cuota;
                    Yii::info("Cuota #{$i} generada: Vence {$cuota->fecha_vencimiento}, Cubre {$cuota->coverage_start} al {$cuota->coverage_end}", 'cuotas');
                } else {
                    throw new \Exception('Error guardando cuota #' . $i . ': ' . json_encode($cuota->getErrors()));
                }
            }

            $transaction->commit();

            Yii::info("✅ Generadas 12 cuotas simples para contrato #{$contrato_id}", 'cuotas');

            return [
                'success' => true,
                'cuotas' => $cuotas,
                'count' => count($cuotas),
                'message' => '12 cuotas generadas exitosamente con vencimiento en la misma fecha de inicio'
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("❌ Error generando cuotas: " . $e->getMessage(), 'cuotas');
            return [
                'success' => false,
                'cuotas' => [],
                'count' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    // ============================================================
    // GENERATORS — Corporate Aligned
    // ============================================================

    /**
     * Generates cuotas for a CORPORATE affiliate, aligned to the corporate's
     * monthly billing cycle.
     *
     * Rules:
     *   - The billing day comes from the corporate's fecha_ini (e.g., day 1, day 15).
     *   - The first cuota is the first monthly billing date on/after the
     *     affiliate's fecha_ini (Rule A).
     *   - Subsequent cuotas are one month apart, on the same billing day.
     *   - Generation stops at (and includes) the corporate's fecha_ven.
     *   - Number of cuotas is not fixed — it depends on how many months fit.
     *   - Month-end handling: if the billing day doesn't exist in a given month,
     *     the last day of that month is used (e.g., day 31 → Feb 28/29).
     *
     * NOTE: This is the LEGACY generator. It does NOT emit a prorated cuota
     *       for the gap between the affiliate's join date and the first
     *       aligned billing date. Use generateCorporateAlignedCuotasWithProration()
     *       when you want that gap to be billed.
     *
     * @param int    $contrato_id         The new affiliate's contract ID.
     * @param string $affiliate_fecha_ini User-picked start date (Y-m-d).
     * @param string $corporate_fecha_ini Corporate contract start date (Y-m-d).
     * @param string $corporate_fecha_ven Corporate contract end date (Y-m-d).
     * @param float  $monto               Monthly amount per cuota.
     * @return array ['success' => bool, 'cuotas' => Cuotas[], 'error' => string|null, 'count' => int,
     *                'has_proration' => bool, 'proration_info' => array|null]
     */
    public static function generateCorporateAlignedCuotas(
        $contrato_id,
        $affiliate_fecha_ini,
        $corporate_fecha_ini,
        $corporate_fecha_ven,
        $monto
    ) {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // ------------------------------------------------------------
            // 1. Validate inputs
            // ------------------------------------------------------------
            if (empty($affiliate_fecha_ini) || empty($corporate_fecha_ini) || empty($corporate_fecha_ven)) {
                throw new \Exception('Las fechas del afiliado y del corporativo son obligatorias para generar cuotas.');
            }

            $affStart   = new \DateTime($affiliate_fecha_ini);
            $corpStart  = new \DateTime($corporate_fecha_ini);
            $corpEnd    = new \DateTime($corporate_fecha_ven);

            if ($corpEnd < $corpStart) {
                throw new \Exception('La fecha de vencimiento del corporativo es anterior a su fecha de inicio.');
            }

            if ($affStart > $corpEnd) {
                throw new \Exception('La fecha de inicio del afiliado es posterior al vencimiento del contrato corporativo.');
            }

            // ------------------------------------------------------------
            // 2. Determine the anchor billing day from the corporate's start
            // ------------------------------------------------------------
            $anchorDay = (int)$corpStart->format('d');

            Yii::info(
                "Corporate-aligned cuota generation: contrato #{$contrato_id}, "
                    . "affiliate start={$affiliate_fecha_ini}, corporate={$corporate_fecha_ini} → {$corporate_fecha_ven}, "
                    . "anchor day={$anchorDay}",
                'cuotas'
            );

            // ------------------------------------------------------------
            // 3. Find the FIRST billing date on/after the affiliate's start
            //    on the anchor day. Rule A.
            // ------------------------------------------------------------
            $firstDue = self::nextBillingDate($affStart, $anchorDay);

            // ------------------------------------------------------------
            // 4. Generate cuotas month by month, stopping at corporate's fecha_ven
            // ------------------------------------------------------------
            $cuotas      = [];
            $currentDue  = clone $firstDue;
            $cuotaNumber = 1;

            // Safety guard: never generate more than 36 cuotas in case of bad input
            $maxCuotas = 36;

            while ($currentDue <= $corpEnd && $cuotaNumber <= $maxCuotas) {

                $cuota                 = new self();
                $cuota->contrato_id    = $contrato_id;
                $cuota->numero_cuota   = $cuotaNumber;
                $cuota->monto          = round((float)$monto, 2);
                $cuota->monto_usd      = round((float)$monto, 2);
                $cuota->estatus        = self::ESTADO_PENDIENTE;
                $cuota->rate_usd_bs    = 1;
                $cuota->fecha_vencimiento = $currentDue->format('Y-m-d');

                // Coverage period: from currentDue to (nextDue - 1 day)
                $coverageStart = clone $currentDue;
                $coverageEnd   = clone $currentDue;

                // Advance one month for coverage end
                $coverageEnd = self::nextBillingDateAfter($currentDue, $anchorDay);
                $coverageEnd->modify('-1 day');

                $cuota->coverage_start = $coverageStart->format('Y-m-d');
                $cuota->coverage_end   = $coverageEnd->format('Y-m-d');

                if (!$cuota->save()) {
                    throw new \Exception(
                        'Error guardando cuota #' . $cuotaNumber . ': '
                            . json_encode($cuota->getErrors())
                    );
                }

                $cuotas[] = $cuota;

                Yii::info(
                    "Cuota #{$cuotaNumber} generada: vence {$cuota->fecha_vencimiento}, "
                        . "cubre {$cuota->coverage_start} al {$cuota->coverage_end}",
                    'cuotas'
                );

                // Advance to next month's billing date
                $currentDue  = self::nextBillingDateAfter($currentDue, $anchorDay);
                $cuotaNumber++;
            }

            if (empty($cuotas)) {
                throw new \Exception('No se generó ninguna cuota. Verifique las fechas ingresadas.');
            }

            // ------------------------------------------------------------
            // 5. Sync the affiliate's contract dates (fecha_ven = corporate's)
            // ------------------------------------------------------------
            $contrato = Contratos::findOne($contrato_id);
            if ($contrato) {
                $contrato->fecha_ven = $corporate_fecha_ven;
                if (!$contrato->save(false)) {
                    Yii::warning(
                        "Could not update contract #{$contrato_id} fecha_ven. Errors: "
                            . json_encode($contrato->getErrors()),
                        'cuotas'
                    );
                }
            }

            $transaction->commit();

            $count = count($cuotas);
            Yii::info(
                "✅ Generated {$count} corporate-aligned cuotas for contract #{$contrato_id}",
                'cuotas'
            );

            // This generator does NOT emit a prorated cuota — always false.
            return [
                'success'        => true,
                'cuotas'         => $cuotas,
                'count'          => $count,
                'error'          => null,
                'message'        => "Se generaron {$count} cuotas alineadas al ciclo del corporativo.",
                'has_proration'  => false,
                'proration_info' => null,
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("❌ Error generating corporate-aligned cuotas: " . $e->getMessage(), 'cuotas');
            return [
                'success'        => false,
                'cuotas'         => [],
                'count'          => 0,
                'error'          => $e->getMessage(),
                'has_proration'  => false,
                'proration_info' => null,
            ];
        }
    }

    /**
     * Generates a PRORATED first cuota (for the gap between the affiliate's
     * join date and the corporate's next billing anchor date) plus the
     * regular aligned monthly cuotas through the corporate's end date.
     *
     * ────────────────────────────────────────────────────────────────────
     * EXAMPLE
     * ────────────────────────────────────────────────────────────────────
     * Corporate contract: 2026-01-01 → 2026-12-31  (anchor day = 1)
     * Affiliate joins:    2026-09-25
     * Monthly amount:     $50.00
     *
     * Generated cuotas:
     *   #1  vence 2026-09-25  cubre 2026-09-25 → 2026-09-30   $10.00  (prorated, 6/30 days)
     *   #2  vence 2026-10-01  cubre 2026-10-01 → 2026-10-31   $50.00
     *   #3  vence 2026-11-01  cubre 2026-11-01 → 2026-11-30   $50.00
     *   #4  vence 2026-12-01  cubre 2026-12-01 → 2026-12-31   $50.00
     *
     * Total billed: $160.00  — exactly 3 months + 6 days of coverage.
     * ────────────────────────────────────────────────────────────────────
     *
     * KEY DECISIONS:
     *
     *   1. The prorated cuota is due ON THE AFFILIATE'S JOIN DATE. This
     *      ensures the affiliate is billed immediately for the partial
     *      month they are being granted coverage for.
     *
     *   2. The prorated amount is proportional to the number of days
     *      actually covered in that first calendar month:
     *          prorated = monto × (daysCovered / daysInCalendarMonth)
     *
     *   3. If the affiliate joins EXACTLY on the corporate anchor day,
     *      there is NO gap → no prorated cuota is generated, and the
     *      regular aligned loop starts on the join date itself.
     *
     *   4. If a prorated cuota is generated, the regular aligned loop
     *      starts on the NEXT aligned billing date (the anchor day of
     *      the following month), not on the join date.
     *
     *   5. Cuota numbering is sequential across both branches: the
     *      prorated cuota is always #1, regular aligned cuotas follow
     *      as #2, #3, ...
     *
     * @param int    $contrato_id
     * @param string $affiliate_fecha_ini   Y-m-d  (the affiliate's join date)
     * @param string $corporate_fecha_ini   Y-m-d  (used to derive the anchor day)
     * @param string $corporate_fecha_ven   Y-m-d  (hard stop for generation)
     * @param float  $monto                 Monthly amount per cuota
     *
     * @return array {
     *     success: bool,
     *     cuotas:  Cuotas[],
     *     count:   int,
     *     error:   string|null,
     *     message: string,
     *     has_proration: bool,
     *     proration_info: array|null
     * }
     */
    public static function generateCorporateAlignedCuotasWithProration(
        $contrato_id,
        $affiliate_fecha_ini,
        $corporate_fecha_ini,
        $corporate_fecha_ven,
        $monto
    ) {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // ────────────────────────────────────────────────────────────
            // 1. Validate inputs
            // ────────────────────────────────────────────────────────────
            if (empty($affiliate_fecha_ini) || empty($corporate_fecha_ini) || empty($corporate_fecha_ven)) {
                throw new \Exception('Las fechas del afiliado y del corporativo son obligatorias.');
            }

            $affStart  = new \DateTime($affiliate_fecha_ini);
            $corpStart = new \DateTime($corporate_fecha_ini);
            $corpEnd   = new \DateTime($corporate_fecha_ven);

            if ($corpEnd < $corpStart) {
                throw new \Exception('La fecha de vencimiento del corporativo es anterior a su inicio.');
            }

            if ($affStart > $corpEnd) {
                throw new \Exception('La fecha de inicio del afiliado es posterior al vencimiento del contrato corporativo.');
            }

            $anchorDay = (int) $corpStart->format('d');

            Yii::info(
                "Prorated corporate-aligned generation: contrato #{$contrato_id}, "
                    . "affiliate={$affiliate_fecha_ini}, corporate={$corporate_fecha_ini} → {$corporate_fecha_ven}, "
                    . "anchor day={$anchorDay}, monto={$monto}",
                'cuotas'
            );

            $cuotas      = [];
            $cuotaNumber = 1;
            $maxCuotas   = 36;

            // ────────────────────────────────────────────────────────────
            // 2. Find the first ALIGNED billing date on/after affiliate's join
            // ────────────────────────────────────────────────────────────
            $firstAlignedDue = self::nextBillingDate($affStart, $anchorDay);

            // ────────────────────────────────────────────────────────────
            // 3. Emit the PRORATED cuota if there's a gap
            // ────────────────────────────────────────────────────────────
            if ($affStart < $firstAlignedDue) {

                $gapEnd = (clone $firstAlignedDue)->modify('-1 day');

                // Days in the calendar month containing the affiliate's join date
                $daysInMonthOfStart = (int) $affStart->format('t');
                $daysCovered        = (int) $affStart->diff($gapEnd)->days + 1;

                // Guard against zero/negative (shouldn't happen, but be safe)
                if ($daysCovered <= 0 || $daysInMonthOfStart <= 0) {
                    throw new \Exception('No se pudo calcular el prorrateo (días inválidos).');
                }

                $proratedAmount = round((float) $monto * ($daysCovered / $daysInMonthOfStart), 2);

                $prorated                     = new self();
                $prorated->contrato_id        = $contrato_id;
                $prorated->numero_cuota       = $cuotaNumber;
                $prorated->monto              = $proratedAmount;
                $prorated->monto_usd          = $proratedAmount;
                $prorated->estatus            = self::ESTADO_PENDIENTE;
                $prorated->rate_usd_bs        = 1;

                // Due on the affiliate's join date (immediately)
                $prorated->fecha_vencimiento  = $affStart->format('Y-m-d');

                // Coverage window: join date → day before the first aligned due
                $prorated->coverage_start     = $affStart->format('Y-m-d');
                $prorated->coverage_end       = $gapEnd->format('Y-m-d');

                if (!$prorated->save()) {
                    throw new \Exception(
                        'Error guardando la cuota prorrateada: '
                            . json_encode($prorated->getErrors())
                    );
                }

                $cuotas[] = $prorated;
                $cuotaNumber++;

                Yii::info(
                    "  → Cuota PRORRATEADA #{$prorated->numero_cuota}: "
                        . "vence {$prorated->fecha_vencimiento}, "
                        . "cubre {$prorated->coverage_start} → {$prorated->coverage_end}, "
                        . "monto {$prorated->monto_usd} "
                        . "(base {$monto} × {$daysCovered}/{$daysInMonthOfStart} días)",
                    'cuotas'
                );
            } else {
                Yii::info(
                    "  → No se requiere prorrateo: el afiliado se unió exactamente en el día ancla ({$anchorDay}).",
                    'cuotas'
                );
            }

            // ────────────────────────────────────────────────────────────
            // 4. Emit the REGULAR aligned cuotas from firstAlignedDue → corpEnd
            // ────────────────────────────────────────────────────────────
            $currentDue = clone $firstAlignedDue;

            while ($currentDue <= $corpEnd && $cuotaNumber <= $maxCuotas) {

                $coverageStart = clone $currentDue;
                $coverageEnd   = self::nextBillingDateAfter($currentDue, $anchorDay);
                $coverageEnd->modify('-1 day');

                // If the corporate contract ends before the next anchor day,
                // trim the coverage window so we never claim coverage past the end.
                if ($coverageEnd > $corpEnd) {
                    $coverageEnd = clone $corpEnd;
                }

                $cuota                     = new self();
                $cuota->contrato_id        = $contrato_id;
                $cuota->numero_cuota       = $cuotaNumber;
                $cuota->monto              = round((float) $monto, 2);
                $cuota->monto_usd          = round((float) $monto, 2);
                $cuota->estatus            = self::ESTADO_PENDIENTE;
                $cuota->rate_usd_bs        = 1;
                $cuota->fecha_vencimiento  = $currentDue->format('Y-m-d');
                $cuota->coverage_start     = $coverageStart->format('Y-m-d');
                $cuota->coverage_end       = $coverageEnd->format('Y-m-d');

                if (!$cuota->save()) {
                    throw new \Exception(
                        "Error guardando la cuota #{$cuotaNumber}: "
                            . json_encode($cuota->getErrors())
                    );
                }

                $cuotas[] = $cuota;
                $cuotaNumber++;

                Yii::info(
                    "  → Cuota alineada #{$cuota->numero_cuota}: "
                        . "vence {$cuota->fecha_vencimiento}, "
                        . "cubre {$cuota->coverage_start} → {$cuota->coverage_end}, "
                        . "monto {$cuota->monto_usd}",
                    'cuotas'
                );

                $currentDue = self::nextBillingDateAfter($currentDue, $anchorDay);
            }

            if (empty($cuotas)) {
                throw new \Exception('No se generó ninguna cuota. Verifique las fechas ingresadas.');
            }

            // ────────────────────────────────────────────────────────────
            // 5. Sync the affiliate's contract end date to the corporate's
            // ────────────────────────────────────────────────────────────
            $contrato = Contratos::findOne($contrato_id);
            if ($contrato) {
                $contrato->fecha_ven = $corporate_fecha_ven;
                if (!$contrato->save(false)) {
                    Yii::warning(
                        "Could not update contract #{$contrato_id} fecha_ven. Errors: "
                            . json_encode($contrato->getErrors()),
                        'cuotas'
                    );
                }
            }

            $transaction->commit();

            $count = count($cuotas);
            Yii::info(
                "✅ Generated {$count} prorated-corporate-aligned cuotas for contract #{$contrato_id}",
                'cuotas'
            );

            // ────────────────────────────────────────────────────────────
            // 6. Detect whether a prorated cuota was generated
            // ────────────────────────────────────────────────────────────
            $proratedCuota = null;
            foreach ($cuotas as $c) {
                // The prorated cuota is the only one whose coverage_start
                // matches the affiliate's join date AND whose amount differs
                // from the regular monthly amount.
                if (
                    $c->coverage_start === $affStart->format('Y-m-d')
                    && abs((float)$c->monto - (float)$monto) > 0.001
                ) {
                    $proratedCuota = $c;
                    break;
                }
            }

            $prorationInfo = null;
            if ($proratedCuota) {
                $coveredDays      = (int) (new \DateTime($proratedCuota->coverage_start))
                    ->diff(new \DateTime($proratedCuota->coverage_end))->days + 1;
                $daysInMonthStart = (int) (new \DateTime($proratedCuota->coverage_start))->format('t');

                $prorationInfo = [
                    'cuota_id'           => (int)$proratedCuota->id,
                    'numero_cuota'       => (int)$proratedCuota->numero_cuota,
                    'fecha_vencimiento'  => $proratedCuota->fecha_vencimiento,
                    'coverage_start'     => $proratedCuota->coverage_start,
                    'coverage_end'       => $proratedCuota->coverage_end,
                    'monto'              => (float)$proratedCuota->monto,
                    'monto_base'         => (float)$monto,
                    'dias_cubiertos'     => $coveredDays,
                    'dias_del_mes'       => $daysInMonthStart,
                    'porcentaje_del_mes' => round(($coveredDays / $daysInMonthStart) * 100, 2),
                ];
            }

            return [
                'success'        => true,
                'cuotas'         => $cuotas,
                'count'          => $count,
                'error'          => null,
                'message'        => "Se generaron {$count} cuotas (prorrateada + alineadas).",
                'has_proration'  => $prorationInfo !== null,
                'proration_info' => $prorationInfo,
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error(
                "❌ Error generating prorated corporate-aligned cuotas: " . $e->getMessage(),
                'cuotas'
            );

            return [
                'success'        => false,
                'cuotas'         => [],
                'count'          => 0,
                'error'          => $e->getMessage(),
                'message'        => 'Error generando cuotas prorrateadas.',
                'has_proration'  => false,
                'proration_info' => null,
            ];
        }
    }

    /**
     * Returns the first monthly billing date on/after $referenceDate,
     * using $anchorDay as the billing day (1-31).
     *
     * Handles month-end: if $anchorDay doesn't exist in a given month
     * (e.g., 31 in February), the last day of the month is used.
     *
     * Rule A: if $referenceDate falls exactly on the anchor day, it is returned.
     *
     * @param \DateTime $referenceDate
     * @param int       $anchorDay
     * @return \DateTime
     */
    protected static function nextBillingDate(\DateTime $referenceDate, int $anchorDay): \DateTime
    {
        $candidate = self::buildDateWithAnchorDay(
            (int)$referenceDate->format('Y'),
            (int)$referenceDate->format('m'),
            $anchorDay
        );

        if ($candidate >= $referenceDate) {
            return $candidate;
        }

        // Move one month forward
        return self::buildDateWithAnchorDay(
            (int)$referenceDate->format('Y'),
            (int)$referenceDate->format('m') + 1,
            $anchorDay
        );
    }

    /**
     * Returns the next monthly billing date strictly AFTER $currentDue
     * on the same anchor day.
     *
     * @param \DateTime $currentDue
     * @param int       $anchorDay
     * @return \DateTime
     */
    protected static function nextBillingDateAfter(\DateTime $currentDue, int $anchorDay): \DateTime
    {
        $year  = (int)$currentDue->format('Y');
        $month = (int)$currentDue->format('m') + 1;

        // Normalize month overflow (13 → next year's January)
        if ($month > 12) {
            $month = 1;
            $year++;
        }

        return self::buildDateWithAnchorDay($year, $month, $anchorDay);
    }

    /**
     * Builds a DateTime for the given year/month at the anchor day,
     * falling back to the last day of the month if the anchor day doesn't exist.
     *
     * @param int $year
     * @param int $month
     * @param int $anchorDay
     * @return \DateTime
     */
    protected static function buildDateWithAnchorDay(int $year, int $month, int $anchorDay): \DateTime
    {
        // Normalize month overflow
        while ($month > 12) {
            $month -= 12;
            $year++;
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day         = min($anchorDay, $daysInMonth);

        return new \DateTime(sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    // ============================================================
    // BACKWARD COMPATIBILITY
    // ============================================================

    /**
     * For backward compatibility, keep the old method name but point to the new one
     */
    public static function generate12Cuotas($contrato_id, $fecha_inicio, $monto)
    {
        return self::generateCuotasAnniversaryBased($contrato_id, $fecha_inicio, $monto);
    }

    // ============================================================
    // STATUS / GRACE PERIOD
    // ============================================================

    /**
     * Check cuota statuses and apply grace period logic
     * This should be called by a daily cron job
     *
     * @return array Statistics of updated cuotas
     */
    public static function checkCuotasStatus()
    {
        $today = date('Y-m-d');
        $gracePeriodEnd = date('Y-m-d', strtotime('-' . self::GRACE_PERIOD_DAYS . ' days'));

        Yii::info("🔍 Verificando estado de cuotas: Hoy={$today}, Fin gracias={$gracePeriodEnd}", 'cuotas');

        // 1. Move from PENDIENTE to GRACE_PERIOD when past due date (within grace period)
        $toGrace = self::updateAll(
            ['estatus' => self::ESTADO_GRACE_PERIOD],
            [
                'and',
                ['estatus' => self::ESTADO_PENDIENTE],
                ['<', 'fecha_vencimiento', $today],
                ['>=', 'fecha_vencimiento', $gracePeriodEnd]
            ]
        );

        // 2. Move from GRACE_PERIOD to VENCIDA after grace period ends
        $toVencidaFromGrace = self::updateAll(
            ['estatus' => self::ESTADO_VENCIDA],
            [
                'and',
                ['estatus' => self::ESTADO_GRACE_PERIOD],
                ['<', 'fecha_vencimiento', $gracePeriodEnd]
            ]
        );

        // 3. Directly from PENDIENTE to VENCIDA (if we missed grace period check)
        $toVencidaDirect = self::updateAll(
            ['estatus' => self::ESTADO_VENCIDA],
            [
                'and',
                ['estatus' => self::ESTADO_PENDIENTE],
                ['<', 'fecha_vencimiento', $gracePeriodEnd]
            ]
        );

        return [
            'to_grace' => $toGrace,
            'to_vencida_from_grace' => $toVencidaFromGrace,
            'to_vencida_direct' => $toVencidaDirect,
            'total' => $toGrace + $toVencidaFromGrace + $toVencidaDirect
        ];
    }

    /**
     * Check if cuota is in grace period
     */
    public function isInGracePeriod()
    {
        if ($this->estatus != self::ESTADO_PENDIENTE) {
            return false;
        }

        $today = date('Y-m-d');
        $graceEnd = date('Y-m-d', strtotime($this->fecha_vencimiento . ' + ' . self::GRACE_PERIOD_DAYS . ' days'));

        return ($today > $this->fecha_vencimiento && $today <= $graceEnd);
    }

    /**
     * Get days remaining in grace period
     */
    public function getGraceDaysRemaining()
    {
        if (!$this->isInGracePeriod()) {
            return 0;
        }

        $today = new \DateTime();
        $graceEnd = new \DateTime($this->fecha_vencimiento);
        $graceEnd->modify('+' . self::GRACE_PERIOD_DAYS . ' days');

        $interval = $today->diff($graceEnd);
        return $interval->days;
    }

    // ============================================================
    // QUERIES
    // ============================================================

    /**
     * Get pending cuotas for a user
     */
    public static function getPendingCuotasForUser($user_id)
    {
        if (empty($user_id)) {
            return [];
        }

        // Find only NON-ANULLED contracts for this user
        $contratos = Contratos::find()
            ->where(['user_id' => $user_id])
            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO]) // Exclude anulled contracts
            ->all();

        if (empty($contratos)) {
            Yii::info("No active contracts found for user_id: " . $user_id);
            return [];
        }

        $contratoIds = [];
        foreach ($contratos as $contrato) {
            $contratoIds[] = $contrato->id;
            Yii::info("Found active contract ID: " . $contrato->id . " with status: " . $contrato->estatus . " for user_id: " . $user_id);
        }

        // Find pending cuotas only from these active contracts
        $cuotas = self::find()
            ->where(['IN', 'contrato_id', $contratoIds])
            ->andWhere(['in', 'estatus', [
                'pendiente',     // Future payments
                'en_gracias',    // In grace period
                'vencida'        // OVERDUE - MUST SHOW!
            ]])
            ->orderBy(['fecha_vencimiento' => SORT_ASC])
            ->all();

        Yii::info("Found " . count($cuotas) . " pending cuotas from active contracts for user_id: " . $user_id . " with contract IDs: " . implode(', ', $contratoIds));

        foreach ($cuotas as $cuota) {
            // Use rounded values for logging
            $monto = $cuota->monto ? round($cuota->monto, 2) : 0;
            $monto_usd = $cuota->monto_usd ? round($cuota->monto_usd, 2) : 0;
            Yii::info("Cuota ID: " . $cuota->id . ", Contrato ID: " . $cuota->contrato_id . ", Monto USD: " . $monto_usd . ", Monto: " . $monto);
        }

        return $cuotas;
    }

    /**
     * Get the last cuota (paid or pending) for a contract
     */
    public static function getLastCuotaForContract($contrato_id)
    {
        return self::find()
            ->where(['contrato_id' => $contrato_id])
            ->orderBy(['fecha_vencimiento' => SORT_DESC])
            ->one();
    }

    /**
     * Get next due date based on last cuota
     */
    public static function calculateNextDueDate($lastCuotaDate = null)
    {
        if ($lastCuotaDate) {
            $date = new \DateTime($lastCuotaDate);
        } else {
            $date = new \DateTime();
        }

        // Add one month and set day to 7th (typical due day)
        $date->modify('+1 month');
        $date->setDate($date->format('Y'), $date->format('m'), 7);

        return $date->format('Y-m-d');
    }

    // ============================================================
    // ADVANCE CUOTAS
    // ============================================================

    /**
     * Preview advance cuotas without saving
     */
    public static function previewCuotasAdelantadas($contrato_id, $num_cuotas, $fecha_inicio = null, $meses = '', $modo = 'cantidad', $fecha_limite = null)
    {
        try {
            // Get contract details
            $contrato = Contratos::findOne($contrato_id);
            if (!$contrato) {
                throw new \Exception("Contrato no encontrado");
            }

            // Get last cuota
            $lastCuota = self::getLastCuotaForContract($contrato_id);
            $startDate = $fecha_inicio;

            if (!$startDate && $lastCuota) {
                $startDate = $lastCuota->fecha_vencimiento;
            } elseif (!$startDate) {
                $startDate = $contrato->fecha_ini ?: date('Y-m-d');
            }

            // Determine amount
            $montoCuota = $contrato->monto;
            if (!$montoCuota && $lastCuota) {
                $montoCuota = $lastCuota->monto_usd ?: $lastCuota->monto;
            }

            $previewCuotas = [];
            $currentDate = new \DateTime($startDate);

            for ($i = 0; $i < $num_cuotas; $i++) {
                $currentDate->modify('+1 month');
                $fechaVencimiento = $currentDate->format('Y-m-07');

                // Check if exists
                $exists = self::find()
                    ->where([
                        'contrato_id' => $contrato_id,
                        'fecha_vencimiento' => $fechaVencimiento
                    ])
                    ->exists();

                $previewCuotas[] = [
                    'numero' => $i + 1,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'monto' => round($montoCuota, 2),
                    'existe' => $exists,
                    'mes' => $currentDate->format('F Y')
                ];
            }

            return [
                'success' => true,
                'contrato' => [
                    'id' => $contrato->id,
                    'nrocontrato' => $contrato->nrocontrato,
                    'user_id' => $contrato->user_id,
                    'fecha_ini' => $contrato->fecha_ini
                ],
                'last_cuota' => $lastCuota ? [
                    'fecha_vencimiento' => $lastCuota->fecha_vencimiento,
                    'estatus' => $lastCuota->estatus,
                    'monto' => $lastCuota->monto_usd ?: $lastCuota->monto
                ] : null,
                'preview' => $previewCuotas,
                'total' => round($montoCuota * $num_cuotas, 2)
            ];
        } catch (\Exception $e) {
            \Yii::error("Error in previewCuotasAdelantadas: " . $e->getMessage(), 'cuotas');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate advance cuotas for a contract with 1-year limit
     */
    public static function generarCuotasAdelantadas($contrato_id, $num_cuotas, $fecha_inicio = null, $meses = '', $fecha_limite = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Get contract details
            $contrato = Contratos::findOne($contrato_id);
            if (!$contrato) {
                throw new \Exception("Contrato no encontrado");
            }

            // Get last cuota to determine next date
            $lastCuota = self::getLastCuotaForContract($contrato_id);
            $startDate = $fecha_inicio;

            if (!$startDate && $lastCuota) {
                // Start from last cuota's due date
                $startDate = $lastCuota->fecha_vencimiento;
            } elseif (!$startDate) {
                // Start from contract start date or today
                $startDate = $contrato->fecha_ini ?: date('Y-m-d');
            }

            // Determine amount to use
            $montoCuota = $contrato->monto;
            if (!$montoCuota && $lastCuota) {
                $montoCuota = $lastCuota->monto_usd ?: $lastCuota->monto;
            }

            if (!$montoCuota) {
                throw new \Exception("No se puede determinar el monto de la cuota");
            }

            $generatedCuotas = [];
            $currentDate = new \DateTime($startDate);

            // Preparar array de cuotas a generar
            $cuotas_a_generar = [];

            // Si hay meses específicos
            if (!empty($meses)) {
                $mesesArray = explode(',', $meses);
                $contador = 1;
                foreach ($mesesArray as $mes) {
                    // Crear fecha del primer día del mes
                    $fechaVencimiento = new \DateTime($mes . '-01');
                    // Establecer como día 7 del mes
                    $fechaVencimiento->setDate(
                        $fechaVencimiento->format('Y'),
                        $fechaVencimiento->format('m'),
                        7
                    );

                    $cuotas_a_generar[] = [
                        'numero' => $contador++,
                        'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                        'mes' => $fechaVencimiento->format('F Y')
                    ];
                }
            } else {
                // Modo: por cantidad de cuotas
                for ($i = 0; $i < $num_cuotas; $i++) {
                    $fechaVencimiento = clone $currentDate;
                    $fechaVencimiento->modify('+' . ($i + 1) . ' month');
                    $fechaVencimiento->setDate(
                        $fechaVencimiento->format('Y'),
                        $fechaVencimiento->format('m'),
                        7
                    );

                    $cuotas_a_generar[] = [
                        'numero' => $i + 1,
                        'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                        'mes' => $fechaVencimiento->format('F Y')
                    ];
                }
            }

            // VALIDACIÓN DE LÍMITE DE 1 AÑO
            if ($fecha_limite) {
                $fechaLimiteObj = new \DateTime($fecha_limite);
                foreach ($cuotas_a_generar as $cuota) {
                    $fechaCuota = new \DateTime($cuota['fecha_vencimiento']);
                    if ($fechaCuota > $fechaLimiteObj) {
                        return [
                            'success' => false,
                            'error' => "No se pueden generar cuotas después de " . $fechaLimiteObj->format('Y-m-d') .
                                " (límite de 1 año desde inicio del contrato)",
                            'generated' => 0
                        ];
                    }
                }
            }

            // Generar las cuotas válidas
            foreach ($cuotas_a_generar as $cuotaData) {
                $fechaVencimiento = $cuotaData['fecha_vencimiento'];

                // Check if cuota already exists for this date
                $existingCuota = self::find()
                    ->where([
                        'contrato_id' => $contrato_id,
                        'fecha_vencimiento' => $fechaVencimiento
                    ])
                    ->one();

                if ($existingCuota) {
                    Yii::info("Cuota ya existe para {$fechaVencimiento} en contrato #{$contrato_id}", 'cuotas');
                    continue;
                }

                // Create new cuota
                $cuota = new self([
                    'contrato_id' => $contrato_id,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'monto' => round($montoCuota, 2),
                    'monto_usd' => round($montoCuota, 2),
                    'estatus' => 'pendiente',
                    'rate_usd_bs' => 1.0, // Default rate
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                if ($cuota->save()) {
                    $generatedCuotas[] = $cuota;
                    Yii::info("Cuota adelantada generada: ID {$cuota->id} para {$fechaVencimiento}", 'cuotas');
                } else {
                    Yii::error("Error generando cuota: " . print_r($cuota->errors, true), 'cuotas');
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'generated' => count($generatedCuotas),
                'cuotas' => $generatedCuotas
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Error generando cuotas adelantadas: " . $e->getMessage(), 'cuotas');
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // ============================================================
    // DISPLAY HELPERS
    // ============================================================

    /**
     * Get status badge HTML
     */
    public function getStatusBadge()
    {
        $badges = [
            self::ESTADO_PENDIENTE => '<span class="badge badge-warning">Pendiente</span>',
            self::ESTADO_PAGADA => '<span class="badge badge-success">Pagada</span>',
            self::ESTADO_VENCIDA => '<span class="badge badge-danger">Vencida</span>',
            self::ESTADO_ANULADA => '<span class="badge badge-secondary">Anulada</span>',
            self::ESTADO_GRACE_PERIOD => '<span class="badge badge-info">En Gracias (' . self::GRACE_PERIOD_DAYS . ' días)</span>',
        ];

        return $badges[$this->estatus] ?? '<span class="badge badge-light">' . $this->estatus . '</span>';
    }

    /**
     * Get formatted coverage period for display
     */
    public function getCoveragePeriodText()
    {
        if ($this->coverage_start && $this->coverage_end) {
            $start = Yii::$app->formatter->asDate($this->coverage_start, 'dd/MM/yyyy');
            $end = Yii::$app->formatter->asDate($this->coverage_end, 'dd/MM/yyyy');
            return "Cubre: {$start} al {$end}";
        }
        return '';
    }

    // ============================================================
    // NEXT PENDING / PAYMENT SUMMARY / MARK AS PAID
    // ============================================================

    /**
     * Get next pending cuota for a contract
     *
     * @param int $contrato_id
     * @return Cuotas|null
     */
    public static function getNextPending($contrato_id)
    {
        return self::find()
            ->where(['contrato_id' => $contrato_id])
            ->andWhere(['in', 'estatus', [self::ESTADO_PENDIENTE, self::ESTADO_GRACE_PERIOD]])
            ->orderBy(['numero_cuota' => SORT_ASC])
            ->one();
    }

    /**
     * Get payment summary for a contract
     *
     * @param int $contrato_id
     * @return array
     */
    public static function getPaymentSummary($contrato_id)
    {
        $cuotas = self::find()
            ->where(['contrato_id' => $contrato_id])
            ->orderBy(['numero_cuota' => SORT_ASC])
            ->all();

        $total = count($cuotas);
        $pagadas = 0;
        $pendientes = 0;
        $vencidas = 0;
        $enGracias = 0;
        $montoTotal = 0;
        $montoPagado = 0;

        foreach ($cuotas as $cuota) {
            $montoTotal += $cuota->monto;

            switch ($cuota->estatus) {
                case self::ESTADO_PAGADA:
                    $pagadas++;
                    $montoPagado += $cuota->monto;
                    break;
                case self::ESTADO_PENDIENTE:
                    $pendientes++;
                    break;
                case self::ESTADO_GRACE_PERIOD:
                    $enGracias++;
                    break;
                case self::ESTADO_VENCIDA:
                    $vencidas++;
                    break;
            }
        }

        return [
            'total_cuotas' => $total,
            'pagadas' => $pagadas,
            'pendientes' => $pendientes,
            'en_gracias' => $enGracias,
            'vencidas' => $vencidas,
            'monto_total' => $montoTotal,
            'monto_pagado' => $montoPagado,
            'saldo_pendiente' => $montoTotal - $montoPagado,
            'porcentaje_pagado' => $montoTotal > 0 ? round(($montoPagado / $montoTotal) * 100, 1) : 0
        ];
    }

    /**
     * Mark cuota as paid when payment is recorded
     *
     * @param int $pago_id
     * @return bool
     */
    public function markAsPaid($pago_id)
    {
        $this->id_pago = $pago_id;
        $this->fecha_pago = date('Y-m-d H:i:s');
        $this->estatus = self::ESTADO_PAGADA;

        if ($this->save()) {
            Yii::info("✅ Cuota #{$this->numero_cuota} (ID: {$this->id}) marcada como pagada", 'cuotas');

            // Check if this was the last pending cuota to update contract status
            $this->checkAndUpdateContractStatus();

            return true;
        }

        return false;
    }

    /**
     * Check if this was the last pending cuota and update contract status if needed
     */
    private function checkAndUpdateContractStatus()
    {
        $pendingCuotas = self::find()
            ->where(['contrato_id' => $this->contrato_id])
            ->andWhere(['in', 'estatus', [self::ESTADO_PENDIENTE, self::ESTADO_GRACE_PERIOD, self::ESTADO_VENCIDA]])
            ->count();

        if (
            $pendingCuotas == 0 &&
            $this->contrato &&
            $this->contrato->estatus !== Contratos::STATUS_ANULADO
        ) {
            // All cuotas are paid - contract is fully paid
            Yii::info("💰 Todas las cuotas pagadas para contrato #{$this->contrato_id}", 'cuotas');

            $this->contrato->updateStatus();

            if ($this->contrato->user) {
                $this->contrato->user->estatus_solvente = 'Si';
                $this->contrato->user->save(false);
            }
        }
    }
}
