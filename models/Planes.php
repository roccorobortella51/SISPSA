<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "planes".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $nombre
 * @property string|null $descripcion
 * @property float|null $precio
 * @property string|null $estatus
 * @property string|null $nota
 * @property string|null $tipo
 * @property int|null $clinica_id
 * @property int|null $cobertura
 * @property string|null $PDF
 * @property float|null $comision
 * @property float|null $edad_minima
 * @property int|null $edad_limite
 * @property string|null $deleted_at
 * @property string|null $updated_at
 *
 * @property RmClinica $clinica
 * @property Contratos[] $contratos
 * @property PlanesItemsCobertura[] $planesItemsCoberturas
 */
class Planes extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'planes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'descripcion', 'precio', 'estatus', 'nota', 'tipo', 'clinica_id', 'cobertura', 'PDF', 'comision', 'edad_minima', 'edad_limite', 'deleted_at', 'updated_at'], 'default', 'value' => null],
            [['created_at', 'deleted_at', 'updated_at'], 'safe'],
            [['nombre', 'descripcion', 'estatus', 'nota', 'tipo', 'PDF'], 'string'],
            [['precio', 'comision', 'edad_minima'], 'number'],
            [['clinica_id', 'cobertura', 'edad_limite'], 'default', 'value' => null],
            [['clinica_id', 'cobertura', 'edad_limite'], 'integer'],
            [['clinica_id'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['clinica_id' => 'id']],

            [['nombre', 'descripcion', 'precio', 'estatus', 'cobertura',  'comision', 'edad_minima', 'edad_limite'], 'required',],

             // Reglas para campos obligatorios y tipos de datos
            [['nombre', 'precio', 'edad_minima'], 'required', 'message' => 'El campo {attribute} es obligatorio.'],

            // Reglas para tipo de datos
            [['precio', 'comision'], 'number', 'message' => 'El campo {attribute} debe ser un número.'],
            [['edad_minima', 'edad_limite'], 'integer', 'message' => 'El campo {attribute} debe ser un número entero.'],

            // Reglas para rangos de valores numéricos
            // edad_minima
            ['cobertura', 'compare', 'compareValue' => 0, 'operator' => '>=', 'type' => 'number', 'message' => 'La cobertura no puede ser menor a 0.'],

            ['edad_minima', 'compare', 'compareValue' => 0, 'operator' => '>=', 'type' => 'number', 'message' => 'La edad mínima no puede ser menor a 0.'],
            ['edad_minima', 'compare', 'compareAttribute' => 'edad_limite', 'operator' => '<', 'message' => 'La edad mínima debe ser menor que la edad límite.', 'when' => function($model) {
                return !empty($model->edad_limite); // Aplica esta regla solo si edad_limite tiene un valor
            }, 'whenClient' => "function (attribute, value) {
                return $('#plan-edad_limite').val() != '';
            }"],

            // edad_limite
            ['edad_limite', 'compare', 'compareAttribute' => 'edad_minima', 'operator' => '>', 'message' => 'La edad límite debe ser mayor que la edad mínima.', 'when' => function($model) {
                return !empty($model->edad_minima); // Aplica esta regla solo si edad_minima tiene un valor
            }, 'whenClient' => "function (attribute, value) {
                return $('#plan-edad_minima').val() != '';
            }"],
            // Puedes agregar un límite superior si es necesario, ej:
            ['edad_limite', 'compare', 'compareValue' => 120, 'operator' => '<=', 'type' => 'number', 'message' => 'La edad límite no puede exceder los 120 años.'],

            // precio
            ['precio', 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number', 'message' => 'El precio debe ser mayor a 0.'],

            // comision (opcional, si tiene un rango específico)
            ['comision', 'compare', 'compareValue' => 0, 'operator' => '>=', 'type' => 'number', 'message' => 'La comisión no puede ser negativa.'],
            ['comision', 'compare', 'compareValue' => 100, 'operator' => '<=', 'type' => 'number', 'message' => 'La comisión no puede ser mayor a 100%.'],


            // Reglas para longitud de cadenas
            [['nombre', 'descripcion', 'cobertura'], 'string', 'max' => 255], // O la longitud máxima de tus columnas
            // ... otras reglas ...
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Creado en',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripción',
            'precio' => 'Precio',
            'estatus' => 'Estatus',
            'nota' => 'Nota',
            'tipo' => 'Tipo',
            'clinica_id' => 'Clínica ID',
            'cobertura' => 'Cobertura',
            'PDF' => 'PDF',
            'comision' => 'Comisión',
            'edad_minima' => 'Edad mínima',
            'edad_limite' => 'Edad límite',
            'deleted_at' => 'Eliminado en',
            'updated_at' => 'Actualizado en',
        ];
    }

    /**
     * Gets query for [[Clinica]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'clinica_id']);
    }

    /**
     * Gets query for [[Contratos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContratos()
    {
        return $this->hasMany(Contratos::class, ['plan_id' => 'id']);
    }

    /**
     * Gets query for [[PlanesItemsCoberturas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlanesItemsCoberturas()
    {
        return $this->hasMany(PlanesItemsCobertura::class, ['plan_id' => 'id']);
    }

}
