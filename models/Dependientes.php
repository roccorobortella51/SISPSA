<?php
// models/Dependientes.php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "dependientes".
 *
 * @property int $id
 * @property int $titular_id
 * @property int $dependiente_id
 * @property string $parentesco
 * @property float $porcentaje_pago
 * @property bool $activo
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property UserDatos $titular
 * @property UserDatos $dependiente
 */
class Dependientes extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dependientes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['titular_id', 'dependiente_id', 'parentesco'], 'required'],
            [['titular_id', 'dependiente_id'], 'integer'],
            [['porcentaje_pago'], 'number', 'min' => 0, 'max' => 100],
            [['parentesco'], 'string', 'max' => 50],
            [['activo'], 'boolean'],
            [['activo'], 'default', 'value' => true],
            [['porcentaje_pago'], 'default', 'value' => 100.00],

            // Unique constraint: a dependiente can only have one titular
            [['dependiente_id'], 'unique', 'message' => 'Este afiliado ya está registrado como dependiente de otra persona.'],

            // Validation: cannot be dependent on oneself
            ['dependiente_id', 'compare', 'compareAttribute' => 'titular_id', 'operator' => '!=', 'message' => 'No puede ser dependiente de sí mismo.'],

            // Foreign key validations
            [['titular_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['titular_id' => 'id']],
            [['dependiente_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['dependiente_id' => 'id']],

            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titular_id' => 'Titular',
            'dependiente_id' => 'Dependiente',
            'parentesco' => 'Parentesco',
            'porcentaje_pago' => 'Porcentaje de Pago (%)',
            'activo' => 'Activo',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTitular()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'titular_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDependiente()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'dependiente_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_at = date('Y-m-d H:i:s');
            }
            $this->updated_at = date('Y-m-d H:i:s');
            return true;
        }
        return false;
    }

    /**
     * Get available parentesco options
     */
    public static function getParentescoOptions()
    {
        return [
            'Hijo' => 'Hijo',
            'Hija' => 'Hija',
            'Cónyuge' => 'Cónyuge',
            'Madre' => 'Madre',
            'Padre' => 'Padre',
            'Hermano' => 'Hermano',
            'Hermana' => 'Hermana',
            'Otro' => 'Otro',
        ];
    }

    /**
     * Check if user is a titular (has dependents)
     */
    public static function isTitular($userId)
    {
        return self::find()->where(['titular_id' => $userId, 'activo' => true])->exists();
    }

    /**
     * Check if user is a dependent
     */
    public static function isDependiente($userId)
    {
        return self::find()->where(['dependiente_id' => $userId, 'activo' => true])->exists();
    }

    /**
     * Get titular for a dependent
     */
    public static function getTitularForDependiente($dependienteId)
    {
        $model = self::find()
            ->where(['dependiente_id' => $dependienteId, 'activo' => true])
            ->one();

        return $model ? $model->titular : null;
    }

    /**
     * Get all active dependents for a titular
     */
    public static function getDependientesForTitular($titularId)
    {
        return self::find()
            ->with('dependiente')
            ->where(['titular_id' => $titularId, 'activo' => true])
            ->all();
    }

    /**
     * Get all pending payments for a titular and their dependents
     */
    public static function getPendingPaymentsForTitular($titularId)
    {
        $titular = UserDatos::findOne($titularId);
        if (!$titular) return [];

        $pendingPayments = [];

        // Get titular's pending payments
        if ($titular->contrato_id) {
            $titularCuotas = Cuotas::find()
                ->joinWith(['contrato'])
                ->where(['contratos.user_id' => $titularId])
                ->andWhere(['cuotas.estatus' => 'pendiente'])
                ->all();

            foreach ($titularCuotas as $cuota) {
                $pendingPayments[] = [
                    'type' => 'titular',
                    'user_id' => $titularId,
                    'user_name' => $titular->nombres . ' ' . $titular->apellidos,
                    'cuota_id' => $cuota->id,
                    'monto' => $cuota->monto,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'descripcion' => 'Cuota titular - ' . $titular->nombres . ' ' . $titular->apellidos,
                ];
            }
        }

        // Get dependents' pending payments
        $dependientes = self::getDependientesForTitular($titularId);
        foreach ($dependientes as $dependienteRel) {
            $dependiente = $dependienteRel->dependiente;
            if ($dependiente && $dependiente->contrato_id) {
                $dependienteCuotas = Cuotas::find()
                    ->joinWith(['contrato'])
                    ->where(['contratos.user_id' => $dependiente->id])
                    ->andWhere(['cuotas.estatus' => 'pendiente'])
                    ->all();

                foreach ($dependienteCuotas as $cuota) {
                    $pendingPayments[] = [
                        'type' => 'dependiente',
                        'user_id' => $dependiente->id,
                        'user_name' => $dependiente->nombres . ' ' . $dependiente->apellidos,
                        'cuota_id' => $cuota->id,
                        'monto' => $cuota->monto * ($dependienteRel->porcentaje_pago / 100),
                        'fecha_vencimiento' => $cuota->fecha_vencimiento,
                        'descripcion' => 'Cuota dependiente (' . $dependienteRel->parentesco . ') - ' . $dependiente->nombres . ' ' . $dependiente->apellidos,
                        'parentesco' => $dependienteRel->parentesco,
                        'porcentaje_pago' => $dependienteRel->porcentaje_pago,
                    ];
                }
            }
        }

        // Sort by fecha_vencimiento
        usort($pendingPayments, function ($a, $b) {
            return strtotime($a['fecha_vencimiento']) - strtotime($b['fecha_vencimiento']);
        });

        return $pendingPayments;
    }
}
