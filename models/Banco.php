<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bancos".
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property string $estatus
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Banco extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bancos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['updated_at'], 'default', 'value' => null],
            [['estatus'], 'default', 'value' => 'Activo'],
            [['codigo', 'nombre'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['codigo'], 'string', 'max' => 10],
            [['nombre'], 'string', 'max' => 255],
            [['estatus'], 'string', 'max' => 20],
            [['codigo'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigo' => 'Codigo',
            'nombre' => 'Nombre',
            'estatus' => 'Estatus',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
