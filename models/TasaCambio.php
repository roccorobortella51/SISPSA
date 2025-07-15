<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tasa_cambio".
 *
 * @property int $id
 * @property string|null $fecha
 * @property string|null $hora
 * @property float|null $tasa_cambio
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class TasaCambio extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tasa_cambio';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha', 'hora', 'tasa_cambio', 'updated_at', 'deleted_at'], 'default', 'value' => null],
            [['fecha', 'hora', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['tasa_cambio'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
            'tasa_cambio' => 'Tasa Cambio',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

}
