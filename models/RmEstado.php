<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "rm_estado".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $codigo
 * @property string|null $nombre
 */
class RmEstado extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rm_estado';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo', 'nombre'], 'default', 'value' => null],
            [['created_at'], 'safe'],
            [['codigo'], 'default', 'value' => null],
            [['codigo'], 'integer'],
            [['nombre'], 'string'],
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
            'codigo' => 'Codigo',
            'nombre' => 'Nombre',
        ];
    }

}
