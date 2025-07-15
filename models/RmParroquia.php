<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "rm_parroquia".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $codigo_parro
 * @property int|null $muni_codigo
 * @property string|null $nombre
 * @property string|null $municipio
 * @property string|null $estado
 */
class RmParroquia extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rm_parroquia';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo_parro', 'muni_codigo', 'nombre', 'municipio', 'estado'], 'default', 'value' => null],
            [['created_at'], 'safe'],
            [['codigo_parro', 'muni_codigo'], 'default', 'value' => null],
            [['codigo_parro', 'muni_codigo'], 'integer'],
            [['nombre', 'municipio', 'estado'], 'string'],
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
            'codigo_parro' => 'Codigo Parro',
            'muni_codigo' => 'Muni Codigo',
            'nombre' => 'Nombre',
            'municipio' => 'Municipio',
            'estado' => 'Estado',
        ];
    }

}
