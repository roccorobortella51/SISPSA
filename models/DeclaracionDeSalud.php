<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "declaracion_de_salud".
 *
 * @property int $id
 * @property string $created_at
 * @property string $p1_sino
 * @property string $p1_especifica
 * @property string $p2_sino
 * @property string $p2_especifica
 * @property string $p3_sino
 * @property string $p3_especifica
 * @property string $p4_sino
 * @property string $p4_especifica
 * @property string $p5_sino
 * @property string $p5_especifica
 * @property string $p6_sino
 * @property string $p6_especifica
 * @property string $p7_sino
 * @property string $p7_especifica
 * @property string $p8_sino
 * @property string $p8_especifica
 * @property string $p9_sino
 * @property string $p9_especifica
 * @property string $p10_sino
 * @property string $p10_especifica
 * @property string $p11_sino
 * @property string $p11_especifica
 * @property string $p12_sino
 * @property string $p12_especifica
 * @property string $p13_sino
 * @property string $p13_especifica
 * @property string $p14_sino
 * @property string $p14_especifica
 * @property string $p15_sino
 * @property string $p15_especifica
 * @property string $p16_sino
 * @property string $p16_especifica
 * @property string|null $deleted_at
 * @property string|null $updated_at
 * @property int|null $ver_usuario_id
 * @property string|null $ver_observacion
 * @property string|null $ver_si_no
 * @property string|null $ver_fecha
 * @property string|null $url_video_declaracion
 * @property string|null $estatus
 * @property int|null $user_id
 * @property string|null $estatura
 * @property string|null $peso
 *
 * @property UserDatos $user
 */
class DeclaracionDeSalud extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'declaracion_de_salud';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deleted_at', 'updated_at', 'ver_usuario_id', 'ver_observacion', 'ver_si_no', 'ver_fecha', 'url_video_declaracion', 'estatus', 'user_id', 'estatura', 'peso'], 'default', 'value' => null],
            [['p16_especifica'], 'default', 'value' => ''],
            [['created_at', 'deleted_at', 'updated_at', 'ver_fecha'], 'safe'],
            [['p1_sino', 'p1_especifica', 'p2_sino', 'p2_especifica', 'p3_sino', 'p3_especifica', 'p4_sino', 'p4_especifica', 'p5_sino', 'p5_especifica', 'p6_sino', 'p6_especifica', 'p7_sino', 'p7_especifica', 'p8_sino', 'p8_especifica', 'p9_sino', 'p9_especifica', 'p10_sino', 'p10_especifica', 'p11_sino', 'p11_especifica', 'p12_sino', 'p12_especifica', 'p13_sino', 'p13_especifica', 'p14_sino', 'p14_especifica', 'p15_sino', 'p15_especifica', 'p16_sino', 'p16_especifica', 'ver_observacion', 'ver_si_no', 'url_video_declaracion', 'estatus', 'estatura', 'peso'], 'string'],
            [['ver_usuario_id', 'user_id'], 'default', 'value' => null],
            [['ver_usuario_id', 'user_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'p1_sino' => 'P1 Sino',
            'p1_especifica' => 'P1 Especifica',
            'p2_sino' => 'P2 Sino',
            'p2_especifica' => 'P2 Especifica',
            'p3_sino' => 'P3 Sino',
            'p3_especifica' => 'P3 Especifica',
            'p4_sino' => 'P4 Sino',
            'p4_especifica' => 'P4 Especifica',
            'p5_sino' => 'P5 Sino',
            'p5_especifica' => 'P5 Especifica',
            'p6_sino' => 'P6 Sino',
            'p6_especifica' => 'P6 Especifica',
            'p7_sino' => 'P7 Sino',
            'p7_especifica' => 'P7 Especifica',
            'p8_sino' => 'P8 Sino',
            'p8_especifica' => 'P8 Especifica',
            'p9_sino' => 'P9 Sino',
            'p9_especifica' => 'P9 Especifica',
            'p10_sino' => 'P10 Sino',
            'p10_especifica' => 'P10 Especifica',
            'p11_sino' => 'P11 Sino',
            'p11_especifica' => 'P11 Especifica',
            'p12_sino' => 'P12 Sino',
            'p12_especifica' => 'P12 Especifica',
            'p13_sino' => 'P13 Sino',
            'p13_especifica' => 'P13 Especifica',
            'p14_sino' => 'P14 Sino',
            'p14_especifica' => 'P14 Especifica',
            'p15_sino' => 'P15 Sino',
            'p15_especifica' => 'P15 Especifica',
            'p16_sino' => 'P16 Sino',
            'p16_especifica' => 'P16 Especifica',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'ver_usuario_id' => 'Ver Usuario ID',
            'ver_observacion' => 'Ver Observacion',
            'ver_si_no' => 'Ver Si No',
            'ver_fecha' => 'Ver Fecha',
            'url_video_declaracion' => 'Url Video Declaracion',
            'estatus' => 'Estatus',
            'user_id' => 'User ID',
            'estatura' => 'Estatura',
            'peso' => 'Peso',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_id']);
    }

}
