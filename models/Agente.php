<?php

namespace app\models;

use Yii;
use app\models\User;

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
 * @property string|null $sudeaseg 
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * 
 * @property AgenteFuerza[] $agenteFuerzas
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
            // 1. Campos obligatorios
            [['nom'], 'required', 'message' => 'El nombre no puede estar vacío.'],
            [['idusuariopropietario'], 'required', 'message' => 'El nombre del propetario no puede estar vacío.'],
            
            // 'idusuariopropietario' debe ser un número entero.
            [['idusuariopropietario'], 'integer'],

            // codigo de unicidad.
            ['idusuariopropietario', 'unique', 'message' => 'Este usuario ya es propietario de otra agencia.'],
                
            // 2. Campos numéricos (porcentajes)
            // Ningún porcentaje puede ser mayor a 15% y no puede ser negativo.
            // Aquí también puedes personalizar los mensajes de 'min' y 'max' si quieres.
        
            // --- INICIO: REGLAS MODIFICADAS PARA LOS PORCENTAJES ---
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente'], 'required', 'message' => 'El porcentaje no puede estar vacío.'], // Hacemos estos campos obligatorios
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente'], 'number', 
                'min' => 0, 
                'max' => 100, // CAMBIADO: Ahora el máximo es 100
                'tooSmall' => 'El porcentaje no puede ser negativo.',
                'tooBig' => 'El porcentaje no puede ser mayor a 100.' // Mensaje actualizado
            ],
            
            // 'por_max' es también un número, con su valor por defecto y validación de rango 0-100
            [['por_max'], 'required', 'message' => 'El porcentaje máximo no puede estar vacío.'], // Si también debe ser obligatorio
            [['por_max'], 'number',
                'min' => 0,
                'max' => 100, // CAMBIADO: Ahora el máximo es 100
                'tooSmall' => 'El porcentaje máximo no puede ser negativo.',
                'tooBig' => 'El porcentaje máximo no puede ser mayor a 100.' // Mensaje actualizado
            ],

            // --- FIN: REGLAS MODIFICADAS ---

            // 3. Valores por defecto (se mantienen, pero la validación 'required' tiene precedencia)
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente'], 'default', 'value' => null],
            [['por_max'], 'default', 'value' => 100], // CAMBIADO: Valor por defecto a 100 si es el nuevo máximo
    
            // 4. Validación de cadena de texto
            // Aquí también podrías personalizar el mensaje si la cadena es muy larga
            [['nom'], 'string', 'max' => 255, 'tooLong' => 'El nombre es demasiado largo (máximo 255 caracteres).'],

            [['sudeaseg'], 'string', 'max' => 50, 'message' => 'El Código SUDEASEG es demasiado largo.'], // Ajusta el 'max' al tamaño de tu VARCHAR
            [['sudeaseg'], 'safe'], // O 'required' si debe ser obligatorio
            
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
            'id' => 'ID',
            'idusuariopropietario' => 'Idusuariopropietario', // O "Propietario" para ser más amigable
            'nom' => 'Nombre', // Cambiado a 'Nombre' para ser más amigable
            'por_venta' => 'Porcentaje Venta', // Cambiado a 'Porcentaje Venta'
            'por_asesor' => 'Porcentaje Asesoría',
            'por_cobranza' => 'Porcentaje Cobranza',
            'por_post_venta' => 'Porcentaje Post Venta',
            'por_agente' => 'Porcentaje Agente',
            'por_max' => 'Porcentaje Máximo',
            'sudeaseg' => 'Código SUDEASEG', // <--- ¡Añade esta línea!
            'created_at' => 'Fecha Creación', // Cambiado para ser más amigable
            'updated_at' => 'Última Actualización',
            'deleted_at' => 'Fecha Eliminación',
        ];
    }

    
    public function getAgenteFuerzas()
    {
        return $this->hasMany(User::class, ['id' => 'idusuario']) // <-- ¡CAMBIADO AQUÍ! 'idusuario' es la columna en agente_fuerza que apunta al ID del usuario
                    ->viaTable('agente_fuerza', ['agente_id' => 'id']) // 'agente_fuerza' es la tabla intermedia, 'agente_id' apunta al ID del agente
                    ->onCondition(['user.status' => User::STATUS_ACTIVE]); // Filtra para que solo se cuenten usuarios con status = 10 (ACTIVO)
    }

    /**
     * Método para obtener el conteo de AgenteFuerza relacionados.
     * Esto es útil para mostrar directamente en el GridView.
     *
     * @return int
     */
    public function getAgenteFuerzaCount()
    {
        return $this->getAgenteFuerzas()->count();
    }

    public function getPropietario()
    {
        // Relaciona idusuariopropietario de Agente con id de User
        return $this->hasOne(UserDatos::class, ['id' => 'idusuariopropietario']);
    }

    public function getUserDatos()
    {
        return $this->hasOne(UserDatos::class, ['user_login_id' => 'id']); // Asegúrate que 'user_login_id' es la FK en UserDatos
    }


}
