<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "planes_Items_Cobertura".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $porcentaje_cobertura
 * @property int|null $cantidad_limite
 * @property string|null $plazo_espera
 * @property int|null $plan_id
 * @property string|null $nombre_servicio
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $baremo_id
 *
 * @property Planes $plan
 */
class PlanesItemsCobertura extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'planes_Items_Cobertura';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['porcentaje_cobertura', 'cantidad_limite', 'plazo_espera', 'plan_id', 'nombre_servicio', 'updated_at', 'deleted_at', 'baremo_id'], 'default', 'value' => null],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['porcentaje_cobertura', 'cantidad_limite', 'plan_id', 'baremo_id'], 'default', 'value' => null],
            [['porcentaje_cobertura', 'cantidad_limite', 'plan_id', 'baremo_id'], 'integer'],
            [['plazo_espera', 'nombre_servicio'], 'string'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Planes::class, 'targetAttribute' => ['plan_id' => 'id']],
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
            'porcentaje_cobertura' => 'Porcentaje Cobertura',
            'cantidad_limite' => 'Cantidad Limite',
            'plazo_espera' => 'Plazo Espera',
            'plan_id' => 'Plan ID',
            'nombre_servicio' => 'Nombre Servicio',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'baremo_id' => 'Baremo ID',
        ];
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

}
