<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pagos".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $recibo_id
 * @property string|null $fecha_pago
 * @property float|null $monto_pagado
 * @property string|null $metodo_pago
 * @property string|null $estatus
 * @property string|null $numero_referencia_pago
 * @property string|null $updated_at
 * @property string|null $imagen_prueba
 * @property int|null $user_id
 * @property string|null $nombre_conciliador
 * @property string|null $fecha_conciliacion
 * @property string|null $fecha_registro
 * @property string|null $deleted_at
 * @property int|null $conciliador_id
 * @property int|null $conciliado
 * @property float|null $monto_usd
 *
 * @property Recibos $recibo
 */
class Pagos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pagos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['recibo_id', 'fecha_pago', 'monto_pagado', 'metodo_pago', 'estatus', 'numero_referencia_pago', 'updated_at', 'imagen_prueba', 'user_id', 'nombre_conciliador', 'fecha_conciliacion', 'fecha_registro', 'deleted_at', 'conciliador_id', 'conciliado'], 'default', 'value' => null],
            [['monto_usd'], 'default', 'value' => 0],
            [['created_at', 'fecha_pago', 'updated_at', 'fecha_conciliacion', 'fecha_registro', 'deleted_at'], 'safe'],
            [['recibo_id', 'user_id', 'conciliador_id', 'conciliado'], 'default', 'value' => null],
            [['recibo_id', 'user_id', 'conciliador_id', 'conciliado'], 'integer'],
            [['monto_pagado', 'monto_usd'], 'number'],
            [['metodo_pago', 'estatus', 'numero_referencia_pago', 'imagen_prueba', 'nombre_conciliador'], 'string'],
            [['recibo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Recibos::class, 'targetAttribute' => ['recibo_id' => 'id']],
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
            'recibo_id' => 'Recibo ID',
            'fecha_pago' => 'Fecha Pago',
            'monto_pagado' => 'Monto Pagado',
            'metodo_pago' => 'Metodo Pago',
            'estatus' => 'Estatus',
            'numero_referencia_pago' => 'Numero Referencia Pago',
            'updated_at' => 'Updated At',
            'imagen_prueba' => 'Imagen Prueba',
            'user_id' => 'User ID',
            'nombre_conciliador' => 'Nombre Conciliador',
            'fecha_conciliacion' => 'Fecha Conciliacion',
            'fecha_registro' => 'Fecha Registro',
            'deleted_at' => 'Deleted At',
            'conciliador_id' => 'Conciliador ID',
            'conciliado' => 'Conciliado',
            'monto_usd' => 'Monto Usd',
        ];
    }

    /**
     * Gets query for [[Recibo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecibo()
    {
        return $this->hasOne(Recibos::class, ['id' => 'recibo_id']);
    }

}
