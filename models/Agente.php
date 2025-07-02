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
            
    
            // 2. Campos numéricos (porcentajes)
            // Ningún porcentaje puede ser mayor a 15% y no puede ser negativo.
            // Aquí también puedes personalizar los mensajes de 'min' y 'max' si quieres.
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente'], 'number', 
                'min' => 0, 
                'max' => 15,
                'tooSmall' => 'El porcentaje no puede ser negativo.', // Mensaje personalizado para min
                'tooBig' => 'El porcentaje no puede ser mayor a 15.' // Mensaje personalizado para max
            ],
            
            // 'por_max' es un número y su valor por defecto es 15 si no se especifica.
            [['por_max'], 'number'],
    
            // 3. Valores por defecto
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente'], 'default', 'value' => null],
            [['por_max'], 'default', 'value' => 15],
    
            // 4. Validación de cadena de texto
            // Aquí también podrías personalizar el mensaje si la cadena es muy larga
            [['nom'], 'string', 'max' => 255, 'tooLong' => 'El nombre es demasiado largo (máximo 255 caracteres).'],
            
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

    public function getAgenteFuerzas()
    {
        return $this->hasMany(AgenteFuerza::class, ['agente_id' => 'id']);
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
        return $this->hasOne(User::class, ['id' => 'idusuariopropietario']);
    }


}
