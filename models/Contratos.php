<?php

namespace app\models;

use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "contratos".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $plan_id
 * @property int|null $ente_id
 * @property int|null $clinica_id
 * @property string|null $fecha_ini
 * @property string|null $fecha_ven
 * @property float|null $monto
 * @property string|null $estatus
 * @property string|null $nrocontrato
 * @property string|null $frecuencia_pago
 * @property string|null $sucursal
 * @property string|null $moneda
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $anulado_por
 * @property string|null $anulado_fecha
 * @property string|null $anulado_motivo
 * @property int|null $user_id
 * @property string|null $pdf
 *
 * @property RmClinica $clinica
 * @property Planes $plan
 * @property Recibos[] $recibos
 * @property UserDatos $user
 * @property Pagos[] $pagosContrato
 */
class Contratos extends \yii\db\ActiveRecord
{
    // Status constants
    const STATUS_REGISTRADO = 'Registrado';
    const STATUS_ACTIVO = 'Activo';
    const STATUS_ANULADO = 'Anulado';
    const STATUS_VENCIDO = 'Vencido';
    const STATUS_PENDIENTE = 'Pendiente';
    const STATUS_SUSPENDIDO = 'suspendido';
    const STATUS_CREADO_MANUAL = 'Creado Manual';  // Match database exactly (lowercase 'm')

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contratos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_ini', 'fecha_ven'], 'required', 'message' => 'Este campo es obligatorio.'],
            [['plan_id', 'ente_id', 'clinica_id', 'fecha_ini', 'fecha_ven', 'monto', 'estatus', 'nrocontrato', 'frecuencia_pago', 'sucursal', 'moneda', 'updated_at', 'deleted_at', 'anulado_por', 'anulado_fecha', 'anulado_motivo', 'user_id', 'pdf'], 'default', 'value' => null],
            [['created_at', 'fecha_ini', 'fecha_ven', 'updated_at', 'deleted_at', 'anulado_fecha'], 'safe'],
            [['plan_id', 'ente_id', 'clinica_id', 'anulado_por', 'user_id'], 'default', 'value' => null],
            [['plan_id', 'ente_id', 'clinica_id', 'anulado_por', 'user_id'], 'integer'],
            [['monto'], 'number'],
            [['estatus', 'nrocontrato', 'frecuencia_pago', 'sucursal', 'moneda', 'anulado_motivo', 'pdf'], 'string'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Planes::class, 'targetAttribute' => ['plan_id' => 'id']],
            [['clinica_id'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['clinica_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'plan_id' => 'Plan ID',
            'ente_id' => 'Ente ID',
            'clinica_id' => 'Clinica ID',
            'fecha_ini' => 'Fecha Ini *',
            'fecha_ven' => 'Fecha Ven *',
            'monto' => 'Monto',
            'estatus' => 'Estatus',
            'nrocontrato' => 'Nrocontrato',
            'frecuencia_pago' => 'Frecuencia Pago',
            'sucursal' => 'Sucursal',
            'moneda' => 'Moneda',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'anulado_por' => 'Anulado Por',
            'anulado_fecha' => 'Anulado Fecha',
            'anulado_motivo' => 'Anulado Motivo',
            'user_id' => 'User ID',
            'pdf' => 'Pdf',
        ];
    }

    /**
     * Gets query for [[Clinica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'clinica_id']);
    }

    /**
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Planes::class, ['id' => 'plan_id']);
    }

    /**
     * Gets query for [[Recibos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecibos()
    {
        return $this->hasMany(Recibos::class, ['contrato_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Pagos]] - OLD RELATIONSHIP (by user_id)
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getPagos()
    {
        return $this->hasMany(Pagos::class, ['user_id' => 'user_id'])
            ->orderBy(['fecha_pago' => SORT_DESC]);
    }

    /**
     * Gets query for [[PagosContrato]] - NEW RELATIONSHIP (by contrato_id)
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getPagosContrato()
    {
        return $this->hasMany(Pagos::class, ['contrato_id' => 'id']);
    }

    /**
     * Gets payments that belong to this specific contract period
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getPagosDelContrato()
    {
        // Find all payment IDs that are linked to cuotas of this contract
        $paymentIds = Cuotas::find()
            ->select('id_pago')
            ->where(['contrato_id' => $this->id])
            ->andWhere(['IS NOT', 'id_pago', null])
            ->column();

        // If no payment IDs found, return an empty query
        if (empty($paymentIds)) {
            return Pagos::find()->where('0=1'); // Returns no results
        }

        // Get the actual payments
        $query = Pagos::find()->where(['id' => $paymentIds]);

        // For ANULADO contracts: Only show payments before annulment date
        if ($this->estatus === self::STATUS_ANULADO && $this->anulado_fecha) {
            $query->andWhere(['<', 'fecha_pago', $this->anulado_fecha]);
        }

        // Order by payment date (newest first)
        $query->orderBy(['fecha_pago' => SORT_DESC]);

        return $query;
    }

    /**
     * Get status options for dropdown
     * 
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_REGISTRADO => 'Registrado',
            self::STATUS_ACTIVO => 'Activo',
            self::STATUS_ANULADO => 'Anulado',
            self::STATUS_VENCIDO => 'Vencido',
            self::STATUS_PENDIENTE => 'Pendiente',
            self::STATUS_SUSPENDIDO => 'suspendido',
            self::STATUS_CREADO_MANUAL => 'Creado Manual',
        ];
    }

    /**
     * Get status with badge
     * 
     * @return string HTML badge
     */
    public function getStatusBadge()
    {
        $status = $this->estatus ?: self::STATUS_REGISTRADO;

        $badgeClasses = [
            self::STATUS_REGISTRADO => 'badge badge-primary',
            self::STATUS_ACTIVO => 'badge badge-success',
            self::STATUS_ANULADO => 'badge badge-danger',
            self::STATUS_VENCIDO => 'badge badge-warning',
            self::STATUS_PENDIENTE => 'badge badge-info',
            self::STATUS_SUSPENDIDO => 'badge badge-secondary',
            self::STATUS_CREADO_MANUAL => 'badge badge-dark',
        ];

        $class = $badgeClasses[$status] ?? 'badge badge-light';

        return Html::tag('span', $status, ['class' => $class]);
    }

    /**
     * Get status label with proper capitalization
     * 
     * @return string
     */
    public function getStatusLabel()
    {
        $status = $this->estatus ?: self::STATUS_REGISTRADO;

        $labels = [
            self::STATUS_REGISTRADO => 'Registrado',
            self::STATUS_ACTIVO => 'Activo',
            self::STATUS_ANULADO => 'Anulado',
            self::STATUS_VENCIDO => 'Vencido',
            self::STATUS_PENDIENTE => 'Pendiente',
            self::STATUS_SUSPENDIDO => 'Suspendido',
            self::STATUS_CREADO_MANUAL => 'Creado Manual',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Update contract status based on dates and payments
     */
    public function updateStatus()
    {
        // Add debug
        Yii::info("updateStatus() called for Contract #{$this->id}, current status: '{$this->estatus}'", 'contratos');

        // Don't update if already annulled
        if ($this->estatus === self::STATUS_ANULADO) {
            Yii::info("Contract #{$this->id} is ANULADO, skipping", 'contratos');
            return;
        }

        // SPECIAL HANDLING FOR CREADO MANUAL STATUS
        if ($this->estatus === self::STATUS_CREADO_MANUAL) {
            Yii::info("Contract #{$this->id} is CREADO MANUAL, calling specialized handler", 'contratos');
            return $this->updateCreadoManualStatus();
        }

        // SPECIAL HANDLING FOR SUSPENDIDO STATUS
        if ($this->estatus === self::STATUS_SUSPENDIDO) {
            Yii::info("Contract #{$this->id} is SUSPENDIDO, calling specialized handler", 'contratos');
            return $this->updateSuspendidoStatus();
        }

        // Regular status update logic for other statuses
        Yii::info("Contract #{$this->id} using regular status update", 'contratos');
        return $this->updateRegularStatus();
    }

    // In app/models/Contratos.php

    protected function updateCreadoManualStatus()
    {
        Yii::info("INSIDE updateCreadoManualStatus() for Contract #{$this->id}", 'contratos');

        // Check if THIS SPECIFIC contract has any pending cuotas
        $hasPendingCuotas = Cuotas::find()
            ->where(['contrato_id' => $this->id])
            ->andWhere(['estatus' => 'pendiente'])  // Only check for 'pendiente' cuotas
            ->exists();

        $pendingCount = Cuotas::find()
            ->where(['contrato_id' => $this->id])
            ->andWhere(['estatus' => 'pendiente'])
            ->count();

        Yii::info("Contract #{$this->id} has {$pendingCount} pending cuotas", 'contratos');

        // Also check for pagadas cuotas (for logging)
        $paidCount = Cuotas::find()
            ->where(['contrato_id' => $this->id])
            ->andWhere(['estatus' => 'pagada'])
            ->count();

        Yii::info("Contract #{$this->id} has {$paidCount} paid cuotas", 'contratos');

        $today = date('Y-m-d');
        $isExpired = ($this->fecha_ven && $today > $this->fecha_ven);
        $isStarted = ($this->fecha_ini && $today >= $this->fecha_ini);

        Yii::info("Today: {$today}, Start: {$this->fecha_ini}, End: {$this->fecha_ven}", 'contratos');
        Yii::info("isStarted: " . ($isStarted ? 'YES' : 'NO') . ", isExpired: " . ($isExpired ? 'YES' : 'NO'), 'contratos');

        // RULE 1: If has pending cuotas → STAY Creado Manual (NO CHANGE)
        if ($hasPendingCuotas) {
            Yii::info("RULE 1: Has pending cuotas - staying Creado Manual (waiting for reconciliation)", 'contratos');
            return false; // No status change
        }

        // RULE 2: If no pending cuotas, check dates
        if (!$hasPendingCuotas) {
            // Check if ALL cuotas are paid (pagada)
            $totalCuotas = Cuotas::find()->where(['contrato_id' => $this->id])->count();
            $paidCuotas = Cuotas::find()
                ->where(['contrato_id' => $this->id])
                ->andWhere(['estatus' => 'pagada'])
                ->count();

            Yii::info("Total cuotas: {$totalCuotas}, Paid cuotas: {$paidCuotas}", 'contratos');

            // If all cuotas are paid, then proceed with date checks
            if ($totalCuotas > 0 && $totalCuotas == $paidCuotas) {
                // 2a. If expired → VENCIDO
                if ($isExpired) {
                    Yii::info("RULE 2a: All cuotas paid but expired - changing to VENCIDO", 'contratos');
                    $this->estatus = self::STATUS_VENCIDO;
                    return $this->save(false);
                }

                // 2b. If started and not expired → ACTIVO
                if ($isStarted && !$isExpired) {
                    Yii::info("RULE 2b: All cuotas paid, started, not expired - changing to ACTIVO", 'contratos');
                    $this->estatus = self::STATUS_ACTIVO;
                    return $this->save(false);
                }

                // 2c. Future start date → REGISTRADO
                if (!$isStarted && !$isExpired) {
                    Yii::info("RULE 2c: All cuotas paid, future start date - changing to REGISTRADO", 'contratos');
                    $this->estatus = self::STATUS_REGISTRADO;
                    return $this->save(false);
                }
            } else {
                Yii::info("Not all cuotas are paid yet - staying Creado Manual", 'contratos');
            }
        }

        Yii::info("No rules matched - no status change", 'contratos');
        return false;
    }

    /**
     * Update logic specifically for Suspendido contracts
     */
    protected function updateSuspendidoStatus()
    {
        // Check if THIS SPECIFIC contract still has pending cuotas
        $hasPendingCuotas = Cuotas::find()
            ->where(['contrato_id' => $this->id])
            ->andWhere(['estatus' => 'pendiente'])
            ->exists();

        $pendingCount = Cuotas::find()
            ->where(['contrato_id' => $this->id])
            ->andWhere(['estatus' => 'pendiente'])
            ->count();

        Yii::info("Contract #{$this->id} (Suspendido) has {$pendingCount} pending cuotas", 'contratos');

        $today = date('Y-m-d');
        $isExpired = ($this->fecha_ven && $today > $this->fecha_ven);
        $isStarted = ($this->fecha_ini && $today >= $this->fecha_ini);

        // LOGIC FOR SUSPENDIDO:

        // 1. If still has pending cuotas, stay SUSPENDIDO
        if ($hasPendingCuotas) {
            Yii::info("Contract #{$this->id} still has pending cuotas - staying as Suspendido", 'contratos');
            return false;
        }

        // 2. If no pending cuotas, check dates
        if (!$hasPendingCuotas) {
            // 2a. If expired → VENCIDO
            if ($isExpired) {
                Yii::info("Contract #{$this->id} changed from Suspendido to Vencido - contract expired", 'contratos');
                $this->estatus = self::STATUS_VENCIDO;
                return $this->save(false);
            }

            // 2b. If start date has passed and not expired → ACTIVO
            if ($isStarted && !$isExpired) {
                Yii::info("Contract #{$this->id} changed from Suspendido to Activo - all cuotas now paid, dates valid", 'contratos');
                $this->estatus = self::STATUS_ACTIVO;
                return $this->save(false);
            }

            // 2c. If start date not reached yet → REGISTRADO
            if (!$isStarted && !$isExpired) {
                Yii::info("Contract #{$this->id} changed from Suspendido to Registrado - waiting for start date", 'contratos');
                $this->estatus = self::STATUS_REGISTRADO;
                return $this->save(false);
            }
        }

        return false;
    }

    /**
     * Regular status update for other statuses
     */
    protected function updateRegularStatus()
    {
        $today = date('Y-m-d');

        // Check if contract is expired
        if ($this->fecha_ven && $today > $this->fecha_ven) {
            $this->estatus = self::STATUS_VENCIDO;
        }
        // Check if contract is active (start date has passed)
        elseif ($this->fecha_ini && $today >= $this->fecha_ini) {
            // Only set to ACTIVE if currently REGISTERED
            if ($this->estatus === self::STATUS_REGISTRADO) {
                $this->estatus = self::STATUS_ACTIVO;
            }
            // If already ACTIVE, keep it active
            // If any other status (except suspended), keep it
        }
        // Check if contract is registered but not yet started
        elseif ($this->fecha_ini && $today < $this->fecha_ini) {
            $this->estatus = self::STATUS_REGISTRADO;
        }
        // Default to Registrado
        else {
            $this->estatus = self::STATUS_REGISTRADO;
        }

        return $this->save(false);
    }

    /**
     * Check if a contract can be activated (helper method)
     */
    public function canBeActivated()
    {
        $today = date('Y-m-d');

        // Check if contract has pending cuotas
        $hasPendingCuotas = Cuotas::find()
            ->where(['contrato_id' => $this->id])
            ->andWhere(['estatus' => 'pendiente'])
            ->exists();

        if ($hasPendingCuotas) {
            return false;
        }

        // Check date validity
        if (!$this->fecha_ini) {
            return false;
        }

        if ($today < $this->fecha_ini) {
            return false;
        }

        if ($this->fecha_ven && $today > $this->fecha_ven) {
            return false;
        }

        return true;
    }

    /**
     * Activate a Creado Manual contract if possible
     * 
     * @return bool
     */
    public function activateFromCreadoManual()
    {
        if (!$this->canBeActivated()) {
            return false;
        }

        $this->estatus = self::STATUS_ACTIVO;
        if ($this->save(false)) {
            Yii::info("Contract #{$this->id} activated from Creado Manual", 'contratos');
            return true;
        }

        return false;
    }

    /**
     * Get the currently active contract for a user
     * 
     * @param int $user_id
     * @return Contratos|null
     */
    public static function getContratoActivo($user_id)
    {
        if (!$user_id) {
            return null;
        }

        $today = date('Y-m-d');

        return self::find()
            ->where(['user_id' => $user_id])
            ->andWhere(['!=', 'estatus', self::STATUS_ANULADO])
            ->andWhere(['<=', 'fecha_ini', $today])
            ->andWhere([
                'or',
                ['>=', 'fecha_ven', $today],
                ['fecha_ven' => null]
            ])
            ->orderBy(['fecha_ini' => SORT_DESC])
            ->one();
    }

    /**
     * Get all valid (non-anulled) contracts for a user
     * 
     * @param int $user_id
     * @return Contratos[]
     */
    public static function getContratosValidos($user_id)
    {
        if (!$user_id) {
            return [];
        }

        return self::find()
            ->where(['user_id' => $user_id])
            ->andWhere(['!=', 'estatus', self::STATUS_ANULADO])
            ->orderBy(['fecha_ini' => SORT_DESC])
            ->all();
    }
}
