<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "asociacion".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $origen
 * @property int|null $clinica_id
 * @property int|null $plan_id
 * @property string|null $nota
 * @property string|null $estatus
 * @property string|null $name
 */
class Asociacion extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'asociacion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['origen', 'clinica_id', 'plan_id', 'nota', 'estatus', 'name'], 'default', 'value' => null],
            [['created_at'], 'safe'],
            [['origen', 'nota', 'estatus', 'name'], 'string'],
            [['clinica_id', 'plan_id'], 'default', 'value' => null],
            [['clinica_id', 'plan_id'], 'integer'],
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
            'origen' => 'Origen',
            'clinica_id' => 'Clinica ID',
            'plan_id' => 'Plan ID',
            'nota' => 'Nota',
            'estatus' => 'Estatus',
            'name' => 'Name',
        ];
    }

}
