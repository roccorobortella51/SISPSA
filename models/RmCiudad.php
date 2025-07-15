<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "rm_ciudad".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $codigo_ciudad
 * @property int|null $estado_codigo
 * @property string|null $nombre
 * @property int|null $capital
 * @property string|null $estado
 */
class RmCiudad extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rm_ciudad';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo_ciudad', 'estado_codigo', 'nombre', 'capital', 'estado'], 'default', 'value' => null],
            [['created_at'], 'safe'],
            [['codigo_ciudad', 'estado_codigo', 'capital'], 'default', 'value' => null],
            [['codigo_ciudad', 'estado_codigo', 'capital'], 'integer'],
            [['nombre', 'estado'], 'string'],
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
            'codigo_ciudad' => 'Codigo Ciudad',
            'estado_codigo' => 'Estado Codigo',
            'nombre' => 'Nombre',
            'capital' => 'Capital',
            'estado' => 'Estado',
        ];
    }

}
