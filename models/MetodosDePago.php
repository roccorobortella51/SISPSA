<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "metodos_de_pago".
 *
 * @property int $id
 * @property string $created_at
 * @property string|null $nombre_banco
 * @property string|null $rif
 * @property string|null $telefono
 * @property string|null $tipo
 * @property string|null $updated_at
 * @property string|null $correo_zelle
 * @property string|null $numero_cuenta
 * @property string|null $deleted_at
 */
class MetodosDePago extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'metodos_de_pago';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre_banco', 'rif', 'telefono', 'tipo', 'updated_at', 'correo_zelle', 'numero_cuenta', 'deleted_at'], 'default', 'value' => null],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['nombre_banco', 'rif', 'telefono', 'tipo', 'correo_zelle', 'numero_cuenta'], 'string'],
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
            'nombre_banco' => 'Nombre Banco',
            'rif' => 'Rif',
            'telefono' => 'Telefono',
            'tipo' => 'Tipo',
            'updated_at' => 'Updated At',
            'correo_zelle' => 'Correo Zelle',
            'numero_cuenta' => 'Numero Cuenta',
            'deleted_at' => 'Deleted At',
        ];
    }

}
