<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "balance".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $recibo_id
 * @property int|null $pago_id
 * @property string|null $descripcion
 * @property string|null $fecha
 * @property float|null $monto_ref
 * @property float|null $tasa_divisa
 * @property float|null $monto_pagado
 * @property float|null $saldo_pendiente
 * @property int|null $dependiente_id
 * @property int|null $user_id_titular
 */
class Balance extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'balance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['recibo_id', 'pago_id', 'descripcion', 'fecha', 'monto_ref', 'tasa_divisa', 'monto_pagado', 'saldo_pendiente', 'dependiente_id', 'user_id_titular'], 'default', 'value' => null],
            [['created_at', 'fecha'], 'safe'],
            [['recibo_id', 'pago_id', 'dependiente_id', 'user_id_titular'], 'default', 'value' => null],
            [['recibo_id', 'pago_id', 'dependiente_id', 'user_id_titular'], 'integer'],
            [['descripcion'], 'string'],
            [['monto_ref', 'tasa_divisa', 'monto_pagado', 'saldo_pendiente'], 'number'],
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
            'recibo_id' => 'Recibo ID',
            'pago_id' => 'Pago ID',
            'descripcion' => 'Descripcion',
            'fecha' => 'Fecha',
            'monto_ref' => 'Monto Ref',
            'tasa_divisa' => 'Tasa Divisa',
            'monto_pagado' => 'Monto Pagado',
            'saldo_pendiente' => 'Saldo Pendiente',
            'dependiente_id' => 'Dependiente ID',
            'user_id_titular' => 'User Id Titular',
        ];
    }

}
