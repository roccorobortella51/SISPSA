<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "agente_fuerza".
 *
 * @property int $id
 * @property int $idusuario
 * @property int $agente_id
 * @property float|null $por_venta
 * @property float|null $por_asesor
 * @property float|null $por_cobranza
 * @property float|null $por_post_venta
 * @property int|null $puede_vender
 * @property int|null $puede_asesorar
 * @property int|null $puede_cobrar
 * @property int|null $puede_post_venta
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $puede_registrar
 * @property float|null $por_registrar
 */
class AgenteFuerza extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agente_fuerza';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'updated_at', 'deleted_at', 'por_registrar'], 'default', 'value' => null],
            [['puede_registrar'], 'default', 'value' => 1],
            [['id', 'idusuario', 'agente_id'], 'required'],
            [['id', 'idusuario', 'agente_id', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'default', 'value' => null],
            [['id', 'idusuario', 'agente_id', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'integer'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idusuario' => 'Idusuario',
            'agente_id' => 'Agente ID',
            'por_venta' => 'Por Venta',
            'por_asesor' => 'Por Asesor',
            'por_cobranza' => 'Por Cobranza',
            'por_post_venta' => 'Por Post Venta',
            'puede_vender' => 'Puede Vender',
            'puede_asesorar' => 'Puede Asesorar',
            'puede_cobrar' => 'Puede Cobrar',
            'puede_post_venta' => 'Puede Post Venta',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'puede_registrar' => 'Puede Registrar',
            'por_registrar' => 'Por Registrar',
        ];
    }

}
