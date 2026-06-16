<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "AgenteFuerza".
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
 * @property string|null $registro_corredor_actividad_aseguradora
 *
 * @property User $user
 * @property UserDatos $userDatos
 * @property Agente $agente
 */
class AgenteFuerza extends ActiveRecord
{
    public $nombre_agente;
    public $asesor_id;

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
            // Reglas para campos obligatorios
            [['idusuario', 'agente_id'], 'required'],

            // Reglas para unicidad se comento debido a que el cliente lo solicito asi
            // ['idusuario', 'unique', 'message' => 'Este usuario ya tiene una asignación.'],

            // Reglas para enteros
            [['idusuario', 'agente_id', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar', 'asesor_id'], 'integer'],

            // Reglas para números flotantes (porcentajes)
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'default', 'value' => 0.00],

            // Reglas para fechas
            [['created_at', 'updated_at', 'deleted_at', 'nombre_agente'], 'safe'],

            // Reglas de rangos para porcentajes
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number', 'min' => 0, 'max' => 100],

            // Reglas para asegurar que 'puede_...' sean 0 o 1 (booleano)
            [['puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'in', 'range' => [0, 1]],
            [['puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'default', 'value' => 0],

            // Regla para registro_corredor_actividad_aseguradora - AHORA ES STRING
            [['registro_corredor_actividad_aseguradora'], 'string', 'max' => 50],

            // Regla opcional: permite valores vacíos o nulos
            [['registro_corredor_actividad_aseguradora'], 'default', 'value' => null],

            // Reglas para relaciones (foreign keys)
            [['idusuario'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['idusuario' => 'id']],
            [['agente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Agente::class, 'targetAttribute' => ['agente_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idusuario' => 'Usuario',
            'agente_id' => 'Agente Asociado',
            'por_venta' => 'Porcentaje de Venta',
            'por_asesor' => 'Porcentaje de Asesoramiento',
            'por_cobranza' => 'Porcentaje de Cobranza',
            'por_post_venta' => 'Porcentaje de Post Venta',
            'puede_vender' => 'Puede Vender',
            'puede_asesorar' => 'Puede Asesorar',
            'puede_cobrar' => 'Puede Cobrar',
            'puede_post_venta' => 'Puede Realizar Post Venta',
            'created_at' => 'Fecha de Creación',
            'updated_at' => 'Fecha de Actualización',
            'deleted_at' => 'Fecha de Eliminación',
            'puede_registrar' => 'Puede Registrar',
            'por_registrar' => 'Porcentaje de Registro',
            'registro_corredor_actividad_aseguradora' => 'Código SUDEASEG',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserDatos()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'idusuario']);
    }

    /**
     * Gets query for [[Agente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAgente()
    {
        return $this->hasOne(Agente::class, ['id' => 'agente_id']);
    }

    public function getcodigoAgente()
    {
        return $this->hasOne(Agente::class, ['id' => 'agente_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'idusuario']);
    }
}
