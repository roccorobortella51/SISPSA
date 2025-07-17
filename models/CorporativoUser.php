<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "corporativo_user".
 *
 * @property int $corporativo_id
 * @property int $user_id
 * @property string|null $fecha_vinculacion
 * @property string|null $rol_en_corporativo
 *
 * @property Corporativos $corporativo
 * @property User $user
 */
class CorporativoUser extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'corporativo_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rol_en_corporativo'], 'default', 'value' => null],
            [['corporativo_id', 'user_id'], 'required'],
            [['corporativo_id', 'user_id'], 'default', 'value' => null],
            [['corporativo_id', 'user_id'], 'integer'],
            [['fecha_vinculacion'], 'safe'],
            [['rol_en_corporativo'], 'string', 'max' => 50],
            [['corporativo_id', 'user_id'], 'unique', 'targetAttribute' => ['corporativo_id', 'user_id']],
            [['corporativo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporativos::class, 'targetAttribute' => ['corporativo_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'corporativo_id' => 'Corporativo ID',
            'user_id' => 'User ID',
            'fecha_vinculacion' => 'Fecha Vinculacion',
            'rol_en_corporativo' => 'Rol En Corporativo',
        ];
    }

    /**
     * Gets query for [[Corporativo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCorporativo()
    {
        return $this->hasOne(Corporativos::class, ['id' => 'corporativo_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
