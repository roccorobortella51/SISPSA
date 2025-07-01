<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "agente".
 *
 * @property int $id
 * @property int $idusuariopropietario
 * @property string|null $nom
 * @property float|null $por_venta
 * @property float|null $por_asesor
 * @property float|null $por_cobranza
 * @property float|null $por_post_venta
 * @property float|null $por_agente
 * @property float|null $por_max
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class Agente extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agente';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nom', 'por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente', 'updated_at', 'deleted_at'], 'default', 'value' => null],
            [['por_max'], 'default', 'value' => 15],
            [['idusuariopropietario'], 'required'],
            [['idusuariopropietario'], 'default', 'value' => null],
            [['idusuariopropietario'], 'integer'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente', 'por_max'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['nom'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idusuariopropietario' => 'Idusuariopropietario',
            'nom' => 'Nom',
            'por_venta' => 'Por Venta',
            'por_asesor' => 'Por Asesor',
            'por_cobranza' => 'Por Cobranza',
            'por_post_venta' => 'Por Post Venta',
            'por_agente' => 'Por Agente',
            'por_max' => 'Por Max',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }


}
