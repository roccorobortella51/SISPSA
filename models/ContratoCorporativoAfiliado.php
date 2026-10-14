<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Pure ActiveRecord for `contratos_corporativos_afiliados`.
 *
 * @property int $contrato_corporativo_id
 * @property int $user_datos_id
 * @property int|null $contrato_individual_id
 * @property float $precio_individual
 * @property string $created_at
 *
 * @property ContratoCorporativo $contrato
 * @property UserDatos $afiliado
 * @property Contratos|null $contratoIndividual
 */
class ContratoCorporativoAfiliado extends ActiveRecord
{
    public static function tableName()
    {
        return 'contratos_corporativos_afiliados';
    }

    public function rules()
    {
        return [
            [['contrato_corporativo_id', 'user_datos_id', 'precio_individual'], 'required'],
            [['contrato_corporativo_id', 'user_datos_id', 'contrato_individual_id'], 'integer'],
            [['precio_individual'], 'number'],
            [['created_at'], 'safe'],
            [
                ['contrato_corporativo_id', 'user_datos_id'],
                'unique',
                'targetAttribute' => ['contrato_corporativo_id', 'user_datos_id']
            ],
        ];
    }

    public function getContrato()
    {
        return $this->hasOne(ContratoCorporativo::class, ['id' => 'contrato_corporativo_id']);
    }

    public function getAfiliado()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_datos_id']);
    }

    public function getContratoIndividual()
    {
        return $this->hasOne(Contratos::class, ['id' => 'contrato_individual_id']);
    }
}
