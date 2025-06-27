<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sis_sesion".
 *
 * @property int $idsesion
 * @property int $idusuario
 * @property string|null $tk
 * @property string|null $ultima
 * @property int|null $activo
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class SisSesion extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sis_sesion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tk', 'updated_at', 'deleted_at'], 'default', 'value' => null],
            [['activo'], 'default', 'value' => 1],
            [['idusuario'], 'required'],
            [['idusuario', 'activo'], 'default', 'value' => null],
            [['idusuario', 'activo'], 'integer'],
            [['ultima', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['tk'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idsesion' => 'Idsesion',
            'idusuario' => 'Idusuario',
            'tk' => 'Tk',
            'ultima' => 'Ultima',
            'activo' => 'Activo',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

}
