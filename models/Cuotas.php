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
 */
class Cuotas extends \yii\db\ActiveRecord
{
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
            [['contrato_id', 'fecha_vencimiento', 'monto', 'estatus', 'fecha_pago', 'rate_usd_bs', 'id_pago'], 'default', 'value' => null],
            [['created_at', 'fecha_vencimiento', 'fecha_pago'], 'safe'],
            [['contrato_id', 'id_pago'], 'integer'],
            [['monto', 'rate_usd_bs', 'monto_usd'], 'number'],
            [['monto', 'monto_usd'], 'number', 'numberPattern' => '/^\d+(\.\d{1,2})?$/'], // 2 decimal validation
            [['estatus'], 'string', 'max' => 20],
        ];
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
     * Get pending cuotas for a user
     */
    public static function getPendingCuotasForUser($user_id)
    {
        if (empty($user_id)) {
            return [];
        }

        // Find all contracts for this user (including suspended ones as they might have pending cuotas)
        $contratos = Contratos::find()
            ->where(['user_id' => $user_id])
            ->all();

        if (empty($contratos)) {
            Yii::info("No contracts found for user_id: " . $user_id);
            return [];
        }

        $contratoIds = [];
        foreach ($contratos as $contrato) {
            $contratoIds[] = $contrato->id;
            Yii::info("Found contract ID: " . $contrato->id . " for user_id: " . $user_id);
        }

        // Find pending cuotas for these contracts
        $cuotas = self::find()
            ->where(['IN', 'contrato_id', $contratoIds])
            ->andWhere(['estatus' => 'pendiente'])
            ->orderBy(['fecha_vencimiento' => SORT_ASC])
            ->all();

        Yii::info("Found " . count($cuotas) . " pending cuotas for user_id: " . $user_id . " with contract IDs: " . implode(', ', $contratoIds));
        
        foreach ($cuotas as $cuota) {
            // Use rounded values for logging
            $monto = $cuota->monto ? round($cuota->monto, 2) : 0;
            $monto_usd = $cuota->monto_usd ? round($cuota->monto_usd, 2) : 0;
            Yii::info("Cuota ID: " . $cuota->id . ", Contrato ID: " . $cuota->contrato_id . ", Monto USD: " . $monto_usd . ", Monto: " . $monto);
        }

        return $cuotas;
    }
}