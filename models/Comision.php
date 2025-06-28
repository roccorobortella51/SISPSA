<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comision".
 *
 * @property int $id
 * @property int|null $idusuario
 * @property int|null $pagos_id
 * @property float|null $monto
 * @property string|null $fecha_pago
 * @property int|null $enterado
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class Comision extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comision';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idusuario', 'pagos_id', 'monto', 'fecha_pago', 'updated_at', 'deleted_at'], 'default', 'value' => null],
            [['enterado'], 'default', 'value' => 0],
            [['idusuario', 'pagos_id', 'enterado'], 'default', 'value' => null],
            [['idusuario', 'pagos_id', 'enterado'], 'integer'],
            [['monto'], 'number'],
            [['fecha_pago', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idusuario' => 'Idusuario',
            'pagos_id' => 'Pagos ID',
            'monto' => 'Monto',
            'fecha_pago' => 'Fecha Pago',
            'enterado' => 'Enterado',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

}
