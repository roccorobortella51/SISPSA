<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "rm_municipio".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $codigo_muni
 * @property int|null $estado_codigo
 * @property string|null $nombre
 * @property string|null $estado
 */
class RmMunicipio extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rm_municipio';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo_muni', 'estado_codigo', 'nombre', 'estado'], 'default', 'value' => null],
            [['created_at'], 'safe'],
            [['codigo_muni', 'estado_codigo'], 'default', 'value' => null],
            [['codigo_muni', 'estado_codigo'], 'integer'],
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
            'codigo_muni' => 'Codigo Muni',
            'estado_codigo' => 'Estado Codigo',
            'nombre' => 'Nombre',
            'estado' => 'Estado',
        ];
    }

}
