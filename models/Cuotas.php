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
 * @property int|null $monto
 * @property string|null $Estatus
 * @property string|null $fecha_pago
 * @property int|null $rate_usd_bs
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
            [['contrato_id', 'fecha_vencimiento', 'monto', 'Estatus', 'fecha_pago', 'rate_usd_bs'], 'default', 'value' => null],
            [['created_at', 'fecha_vencimiento', 'fecha_pago'], 'safe'],
            [['contrato_id', 'monto', 'rate_usd_bs'], 'default', 'value' => null],
            [['contrato_id', 'monto', 'rate_usd_bs'], 'integer'],
            [['Estatus'], 'string'],
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
            'contrato_id' => 'Contrato ID',
            'fecha_vencimiento' => 'Fecha Vencimiento',
            'monto' => 'Monto',
            'Estatus' => 'Estatus',
            'fecha_pago' => 'Fecha Pago',
            'rate_usd_bs' => 'Rate Usd Bs',
        ];
    }

}
