<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "clinica_fondos".
 *
 * @property int $id
 * @property int|null $clinica_id
 * @property int|null $pagos_id
 * @property float|null $monto
 * @property string|null $fecha_pago
 * @property int|null $enterado
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class ClinicaFondos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'clinica_fondos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['clinica_id', 'pagos_id', 'monto', 'fecha_pago', 'updated_at', 'deleted_at'], 'default', 'value' => null],
            [['enterado'], 'default', 'value' => 0],
            [['clinica_id', 'pagos_id', 'enterado'], 'default', 'value' => null],
            [['clinica_id', 'pagos_id', 'enterado'], 'integer'],
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
            'clinica_id' => 'Clinica ID',
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
