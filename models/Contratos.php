<?php

namespace app\models;

use Yii;
use yii\helpers\Html; // ADD THIS IMPORT

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
 * @property string|null $PDF
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
    const STATUS_SUSPENDIDO = 'suspendido'; // lowercase

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
            [['plan_id', 'ente_id', 'clinica_id', 'fecha_ini', 'fecha_ven', 'monto', 'estatus', 'nrocontrato', 'frecuencia_pago', 'sucursal', 'moneda', 'updated_at', 'deleted_at', 'anulado_por', 'anulado_fecha', 'anulado_motivo', 'user_id', 'PDF'], 'default', 'value' => null],
            [['created_at', 'fecha_ini', 'fecha_ven', 'updated_at', 'deleted_at', 'anulado_fecha'], 'safe'],
            [['plan_id', 'ente_id', 'clinica_id', 'anulado_por', 'user_id'], 'default', 'value' => null],
            [['plan_id', 'ente_id', 'clinica_id', 'anulado_por', 'user_id'], 'integer'],
            [['monto'], 'number'],
            [['estatus', 'nrocontrato', 'frecuencia_pago', 'sucursal', 'moneda', 'anulado_motivo', 'PDF'], 'string'],
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
            'PDF' => 'Pdf',
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
     * This is the correct relationship if your Pagos table has a contrato_id column
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
        // Base query: get all payments for this user
        $query = Pagos::find()->where(['user_id' => $this->user_id]);

        // For ALL contracts (including Anulado): Only show payments made on or after start date
        if ($this->fecha_ini) {
            $query->andWhere(['>=', 'fecha_pago', $this->fecha_ini]);
        }

        // For ANULADO contracts: Use anulado_fecha as the cutoff
        if ($this->estatus === self::STATUS_ANULADO) {
            if ($this->anulado_fecha) {
                // Show payments made BEFORE the annulment date
                $query->andWhere(['<', 'fecha_pago', $this->anulado_fecha]);
            }
            // If no annulment date, don't show any payments (contract was never properly annulled)
            else {
                $query->andWhere('1=0'); // Force no results
            }
        }
        // For NON-Anulado contracts: Use fecha_ven if it exists
        else if ($this->fecha_ven) {
            $query->andWhere(['<=', 'fecha_pago', $this->fecha_ven]);
        }
        // For NON-Anulado contracts without fecha_ven: Show all payments from start date onward
        // (no upper date limit for ongoing contracts)

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
            self::STATUS_SUSPENDIDO => 'Suspendido', // Capitalized for display
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Update contract status based on dates and payments
     */
    public function updateStatus()
    {
        if ($this->estatus === self::STATUS_ANULADO) {
            return; // Don't update if already annulled
        }

        $today = date('Y-m-d');

        // Check if contract is expired
        if ($this->fecha_ven && $today > $this->fecha_ven) {
            $this->estatus = self::STATUS_VENCIDO;
        }
        // Check if contract is active (start date has passed)
        elseif ($this->fecha_ini && $today >= $this->fecha_ini) {
            $this->estatus = self::STATUS_ACTIVO;
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
