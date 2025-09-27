<?php

namespace app\models;

use Yii;
use app\models\Corporativo;

/**
 * This is the model class for table "corporativo_clinica".
 *
 * @property int $corporativo_id
 * @property int $clinica_id
 * @property string|null $created_at
 *
 * @property RmClinica $clinica
 * @property Corporativos $corporativo
 */
class CorporativoClinica extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'corporativo_clinica';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporativo_id', 'clinica_id'], 'required'],
            [['corporativo_id', 'clinica_id'], 'default', 'value' => null],
            [['corporativo_id', 'clinica_id'], 'integer'],
            [['created_at'], 'safe'],
            [['corporativo_id', 'clinica_id'], 'unique', 'targetAttribute' => ['corporativo_id', 'clinica_id']],
            [['corporativo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporativos::class, 'targetAttribute' => ['corporativo_id' => 'id']],
            [['clinica_id'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['clinica_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'corporativo_id' => 'Corporativo ID',
            'clinica_id' => 'Clinica ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Clinica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'clinica_id']);
    }

    /**
     * Gets query for [[Corporativo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCorporativo()
    {
        return $this->hasOne(Corporativo::class, ['id' => 'corporativo_id']);
    }

}
