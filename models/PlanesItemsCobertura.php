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
    // En PlanesItemsCobertura.php
        public function rules()
        {
            return [
                [['baremo_id', 'plan_id'], 'required'],
                [['porcentaje_cobertura'], 'integer', 'min' => 0, 'max' => 100],
                [['cantidad_limite'], 'integer', 'min' => 0],
                [['plazo_espera'], 'string', 'max' => 50],
                [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Planes::class, 'targetAttribute' => ['plan_id' => 'id']],
                [['baremo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Baremo::class, 'targetAttribute' => ['baremo_id' => 'id']],
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

    public function getBaremo()
    {
        return $this->hasOne(Baremo::class, ['id' => 'baremo_id']);
    }

}
