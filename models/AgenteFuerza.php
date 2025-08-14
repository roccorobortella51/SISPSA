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
 *
 * @property User $user
 * @property UserDatos $userDatos // Asumiendo que esta es una relación que tienes o planeas tener.
 * @property Agente $agente
 */
class AgenteFuerza extends ActiveRecord
{
    public $nombre_agente;
    public $asesor_id;
    public $registro_corredor_actividad_aseguradora;
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

            // Reglas para unicidad
            ['idusuario', 'unique', 'message' => 'Este usuario ya tiene una asignación.'],

            // Reglas para enteros
            [['idusuario', 'agente_id', 'puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar', 'asesor_id'], 'integer'],

            // Reglas para números flotantes (porcentajes)
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'number'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar'], 'default', 'value' => 0.00], // Valor por defecto si no se especifica

            // Reglas para fechas (created_at, updated_at, deleted_at)
            [['created_at', 'updated_at', 'deleted_at', 'nombre_agente'], 'safe'], // 'safe' porque se suelen manejar automáticamente por comportamientos o triggers

            // Reglas de rangos para porcentajes (opcional, pero recomendado)
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_registrar', 'registro_corredor_actividad_aseguradora'], 'number', 'min' => 0, 'max' => 100],

            // Reglas para asegurar que 'puede_...' sean 0 o 1 (booleano)
            [['puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'in', 'range' => [0, 1]],
            [['puede_vender', 'puede_asesorar', 'puede_cobrar', 'puede_post_venta', 'puede_registrar'], 'default', 'value' => 0],

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

    /**
     * Gets query for [[UserDatos]].
     *
     * Asumo que tienes una tabla UserDatos relacionada con User o directamente con AgenteFuerza.
     * Si no es el caso, puedes eliminar o ajustar esta relación.
     * Si UserDatos es una extensión de User, la relación podría ser diferente.
     *
     * @return \yii\db\ActiveQuery
     */

    public function getcodigoAgente()
    {
        return $this->hasOne(Agente::class, ['id' => 'agente_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'idusuario']);
    }

}