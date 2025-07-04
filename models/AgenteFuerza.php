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
            // 1. Campos obligatorios
            
            [['nom'], 'required', 'message' => 'El nombre no puede estar vacío.'],
            [['idusuariopropietario'], 'required', 'message' => 'El propietario no puede estar vacío.'],
            [['idusuariopropietario'], 'integer'],
            
            // --- REGLAS DE PORCENTAJES: AJUSTE DE MENSAJE PARA FORMATO INVÁLIDO ---
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente'], 'required', 'message' => 'El porcentaje no puede estar vacío.'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente'], 'number', 
                'min' => 0, 
                'max' => 100, 
                'tooSmall' => 'El porcentaje no puede ser negativo.',
                'tooBig' => 'El porcentaje no puede ser mayor a 100.',
                'message' => 'El porcentaje debe ser un número válido.' // ¡Añadida esta línea!
            ],
            
            [['por_max'], 'required', 'message' => 'El porcentaje máximo no puede estar vacío.'],
            [['por_max'], 'number',
                'min' => 0,
                'max' => 100,
                'tooSmall' => 'El porcentaje máximo no puede ser negativo.',
                'tooBig' => 'El porcentaje máximo no puede ser mayor a 100.',
                'message' => 'El porcentaje máximo debe ser un número válido.' // ¡Añadida esta línea!
            ],
    
            // 3. Valores por defecto
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente'], 'default', 'value' => null],
            [['por_max'], 'default', 'value' => 100],
    
            // 4. Validación de cadena de texto
            [['nom'], 'string', 'max' => 255, 'tooLong' => 'El nombre es demasiado largo (máximo 255 caracteres).'],
            [['sudeaseg'], 'string', 'max' => 50, 'message' => 'El Código SUDEASEG es demasiado largo.'],
            [['sudeaseg'], 'safe'],
            
            // 5. Campos de fecha
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
