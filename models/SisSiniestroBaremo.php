<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Esta es la clase modelo para la tabla "sis_siniestro_baremo".
 *
 * @property int $siniestro_id
 * @property int $baremo_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Baremo $baremo
 * @property SisSiniestro $siniestro
 */
class SisSiniestroBaremo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sis_siniestro_baremo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['siniestro_id', 'baremo_id'], 'required'],
            [['siniestro_id', 'baremo_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['siniestro_id', 'baremo_id'], 'unique', 'targetAttribute' => ['siniestro_id', 'baremo_id']],
            [['baremo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Baremo::class, 'targetAttribute' => ['baremo_id' => 'id']],
            [['siniestro_id'], 'exist', 'skipOnError' => true, 'targetClass' => SisSiniestro::class, 'targetAttribute' => ['siniestro_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'siniestro_id' => 'Siniestro ID',
            'baremo_id' => 'Baremo ID',
            'created_at' => 'Creado el',
            'updated_at' => 'Actualizado el',
        ];
    }

    /**
     * Gets query for [[Baremo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBaremo()
    {
        return $this->hasOne(Baremo::class, ['id' => 'baremo_id']);
    }

    /**
     * Gets query for [[Siniestro]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiniestro()
    {
        return $this->hasOne(SisSiniestro::class, ['id' => 'siniestro_id']);
    }
}