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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripcion',
            'precio' => 'Precio',
            'estatus' => 'Estatus',
            'nota' => 'Nota',
            'tipo' => 'Tipo',
            'clinica_id' => 'Clinica ID',
            'cobertura' => 'Cobertura',
            'PDF' => 'Pdf',
            'comision' => 'Comision',
            'edad_minima' => 'Edad Minima',
            'edad_limite' => 'Edad Limite',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
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
