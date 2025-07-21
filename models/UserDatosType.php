<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_datos_type".
 *
 * @property int $id
 * @property string $nombre
 */
class UserDatosType extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_datos_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre'], 'required'],
            [['nombre'], 'string', 'max' => 100],
            [['nombre'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
        ];
    }

    /**
     * Obtiene una lista clave-valor de todos los tipos de UserDatos para usar en Select2.
     * @return array
     */
    public static function getList()
    {
        // Esto consulta la base de datos para obtener todos los registros
        // de user_datos_type y los mapea en un array donde la clave es 'id'
        // y el valor es 'nombre'.
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'id', 'nombre');
    }
}