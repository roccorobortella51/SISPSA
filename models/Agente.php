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
            // Campos que pueden ser NULL por defecto si no se envían (y la DB lo permite)
            [['nom', 'por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente'], 'default', 'value' => null],
    
            // Valor por defecto para por_max
            [['por_max'], 'default', 'value' => 15],
    
            // idusuariopropietario es obligatorio y entero
            // ELIMINAMOS el 'default' => null para idusuariopropietario si es 'required'
            [['idusuariopropietario'], 'required'],
            [['idusuariopropietario'], 'integer'],
    
            // Campos numéricos (incluyendo 'por_max' si no quieres que sea solo un default)
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente', 'por_max'], 'number'],
    
            // Campos de fecha: se marcan como 'safe' para permitir asignación si no se usa Behavior,
            // PERO lo ideal es usar TimestampBehavior para created_at y updated_at.
            // deleted_at puede ser 'safe' o gestionarse manualmente si es para soft-delete.
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
    
            // Validación de string para 'nom'
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
