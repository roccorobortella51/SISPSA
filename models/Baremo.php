<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "baremo".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $nombre_servicio
 * @property string|null $descripcion
 * @property string|null $estatus
 * @property string|null $deleted_at
 * @property string|null $updated_at
 * @property float|null $precio
 * @property int|null $clinica_id
 * @property float|null $costo
 * @property int|null $area_id
 *
 * @property SisSiniestro[] $sisSiniestros
 */
class Baremo extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'baremo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre_servicio', 'descripcion', 'estatus', 'deleted_at', 'updated_at', 'clinica_id'], 'default', 'value' => null],
            [['costo'], 'default', 'value' => 0],
            [['area_id'], 'default', 'value' => 1],
            [['created_at', 'deleted_at', 'updated_at'], 'safe'],
            [['nombre_servicio', 'descripcion', 'estatus'], 'string'],
            [['precio', 'costo'], 'number'],
            [['clinica_id', 'area_id'], 'default', 'value' => null],
            [['clinica_id', 'area_id'], 'integer'],
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
            'nombre_servicio' => 'Nombre Servicio',
            'descripcion' => 'Descripcion',
            'estatus' => 'Estatus',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'precio' => 'Precio',
            'clinica_id' => 'Clinica ID',
            'costo' => 'Costo',
            'area_id' => 'Area ID',
        ];
    }

    /**
     * Gets query for [[SisSiniestros]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSisSiniestros()
    {
        return $this->hasMany(SisSiniestro::class, ['idbaremo' => 'id']);
    }

}
