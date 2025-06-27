<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "balances_int".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $fecha
 * @property string|null $fuente
 * @property int|null $fuente_id
 * @property float|null $monto
 * @property float|null $monto_usd
 * @property float|null $balance
 * @property float|null $balance_usd
 * @property string|null $descripcion
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $responsablePago_id
 */
class BalancesInt extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'balances_int';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'fecha', 'fuente', 'fuente_id', 'monto', 'monto_usd', 'balance', 'balance_usd', 'descripcion', 'updated_at', 'deleted_at', 'responsablePago_id'], 'default', 'value' => null],
            [['user_id', 'fuente_id', 'responsablePago_id'], 'default', 'value' => null],
            [['user_id', 'fuente_id', 'responsablePago_id'], 'integer'],
            [['fecha', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['monto', 'monto_usd', 'balance', 'balance_usd'], 'number'],
            [['fuente'], 'string', 'max' => 45],
            [['descripcion'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'fecha' => 'Fecha',
            'fuente' => 'Fuente',
            'fuente_id' => 'Fuente ID',
            'monto' => 'Monto',
            'monto_usd' => 'Monto Usd',
            'balance' => 'Balance',
            'balance_usd' => 'Balance Usd',
            'descripcion' => 'Descripcion',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'responsablePago_id' => 'Responsable Pago ID',
        ];
    }

}
