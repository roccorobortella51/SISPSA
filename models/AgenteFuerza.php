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
            // 1. Campos obligatorios: Asegura que estos campos estén presentes.
            [['idusuario', 'agente_id'], 'required'],
    
            // 2. Tipo de dato entero para IDs y booleanos:
            // Asegura que estos campos sean números enteros. Los campos booleanos
            // (puede_vender, etc.) en PHP suelen manejarse como 0 o 1, que son enteros.
            [['id', 'idusuario', 'agente_id', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'integer'],
    
            // 3. Tipo de dato numérico para porcentajes:
            // 'number' permite enteros y flotantes (decimales).
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number'],
    
            // 4. Reglas de valor por defecto:
            // Asigna 'null' a los campos de fecha si no se envían.
            // Asigna '1' por defecto a 'puede_registrar' si no se especifica.
            // Es importante que los 'default' vayan DESPUÉS de los 'required' si un campo puede ser null.
            // Y los 'default' para booleanos/enteros deben ir después de su regla 'integer'.
            [['updated_at', 'deleted_at'], 'default', 'value' => null],
            [['puede_registrar'], 'default', 'value' => 1], // Si el valor por defecto para nuevo registro es 1 (true)
    
            // 5. Campos de fecha y hora:
            // 'safe' es suficiente para created_at y updated_at si son manejados por TimestampBehavior
            // o si no son validados con reglas específicas y pueden ser enviados directamente.
            // Si usas TimestampBehavior, no necesitas validación 'safe' aquí.
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],

            [['agente_nombre'], 'string'],
    
            // 6. Validación de unicidad para 'id':
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
