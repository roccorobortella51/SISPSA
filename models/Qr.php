<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "qr".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $id_clinica
 * @property int|null $id_corporativo
 * @property int|null $id_asesor
 * @property string|null $codigoQR
 * @property string|null $url
 * @property string|null $fecha_inicio
 * @property string|null $fecha_final
 * @property string|null $nombre_promocion
 * @property string|null $estatus
 * @property int|null $id_asesor_campaña_vencida
 * @property string|null $descripcion_promo
 *
 * @property RmClinica $clinica
 */
class Qr extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qr';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_clinica', 'id_corporativo', 'id_asesor', 'codigoQR', 'url', 'fecha_inicio', 'fecha_final', 'nombre_promocion', 'estatus', 'id_asesor_campaña_vencida', 'descripcion_promo'], 'default', 'value' => null],
            [['created_at', 'fecha_inicio', 'fecha_final'], 'safe'],
            [['id_clinica', 'id_corporativo', 'id_asesor', 'id_asesor_campaña_vencida'], 'default', 'value' => null],
            [['id_clinica', 'id_corporativo', 'id_asesor', 'id_asesor_campaña_vencida'], 'integer'],
            [['codigoQR', 'url', 'nombre_promocion', 'estatus', 'descripcion_promo'], 'string'],
            [['id_clinica'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['id_clinica' => 'id']],
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
            'id_clinica' => 'Id Clinica',
            'id_corporativo' => 'Id Corporativo',
            'id_asesor' => 'Id Asesor',
            'codigoQR' => 'Codigo Qr',
            'url' => 'Url',
            'fecha_inicio' => 'Fecha Inicio',
            'fecha_final' => 'Fecha Final',
            'nombre_promocion' => 'Nombre Promocion',
            'estatus' => 'Estatus',
            'id_asesor_campaña_vencida' => 'Id Asesor Campaña Vencida',
            'descripcion_promo' => 'Descripcion Promo',
        ];
    }

    /**
     * Gets query for [[Clinica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'id_clinica']);
    }

}
