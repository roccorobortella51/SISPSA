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
 * @property User $user 
 * @property UserDatos $userDatos
 * @property Agente $agente
 */
class AgenteFuerza extends \yii\db\ActiveRecord
{

public $agente_nombre;
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
        // ... otras reglas ...

        // 1. Campos obligatorios:
        // Personalizamos el mensaje para 'idusuario'
        [['idusuario'], 'required', 'message' => 'Este campo del asesor de ventas  no puede estar vacío.'],
        

        // ... el resto de tus reglas ...

        [['id', 'idusuario', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'integer'],
    
        [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number', 
            'min' => 0, 
            'max' => 15,
            'tooSmall' => 'El porcentaje no puede ser negativo.',
            'tooBig' => 'El porcentaje no puede ser mayor a 15.'
        ],
    
        [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'default', 'value' => null],
        [['updated_at', 'deleted_at'], 'default', 'value' => null],
        [['puede_registrar'], 'default', 'value' => 1], 
    
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

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'idusuario']);
    }

   
    public function getAgente()
    {
        // Esto define la relación desde AgenteFuerza hacia Agente
        // donde el 'id' de la tabla 'agente' coincide con 'agente_id' en 'agente_fuerza'.
        return $this->hasOne(Agente::class, ['id' => 'agente_id']);
    }


}
