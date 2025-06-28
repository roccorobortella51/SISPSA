<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "recibos".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $numero_de_recibo
 * @property string|null $fecha_recibo
 * @property string|null $fecha_vencimiento
 * @property float|null $monto_total
 * @property float|null $monto_pagado
 * @property string|null $estatus
 * @property int|null $contrato_id
 * @property string|null $updated_at
 * @property float|null $saldo
 * @property float|null $tasa_divisa
 * @property int|null $dependiente_id
 * @property string|null $deleted_at
 * @property int|null $mes
 * @property int|null $id_titular
 *
 * @property Contratos $contrato
 * @property Beneficiarios $dependiente
 * @property Pagos[] $pagos
 * @property UserDatos $titular
 */
class Recibos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recibos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['numero_de_recibo', 'fecha_recibo', 'fecha_vencimiento', 'monto_total', 'monto_pagado', 'estatus', 'contrato_id', 'updated_at', 'saldo', 'tasa_divisa', 'dependiente_id', 'deleted_at', 'id_titular'], 'default', 'value' => null],
            [['mes'], 'default', 'value' => 0],
            [['created_at', 'fecha_recibo', 'fecha_vencimiento', 'updated_at', 'deleted_at'], 'safe'],
            [['numero_de_recibo', 'contrato_id', 'dependiente_id', 'mes', 'id_titular'], 'default', 'value' => null],
            [['numero_de_recibo', 'contrato_id', 'dependiente_id', 'mes', 'id_titular'], 'integer'],
            [['monto_total', 'monto_pagado', 'saldo', 'tasa_divisa'], 'number'],
            [['estatus'], 'string'],
            [['dependiente_id'], 'exist', 'skipOnError' => true, 'targetClass' => Beneficiarios::class, 'targetAttribute' => ['dependiente_id' => 'id']],
            [['contrato_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contratos::class, 'targetAttribute' => ['contrato_id' => 'id']],
            [['id_titular'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['id_titular' => 'id']],
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
            'numero_de_recibo' => 'Numero De Recibo',
            'fecha_recibo' => 'Fecha Recibo',
            'fecha_vencimiento' => 'Fecha Vencimiento',
            'monto_total' => 'Monto Total',
            'monto_pagado' => 'Monto Pagado',
            'estatus' => 'Estatus',
            'contrato_id' => 'Contrato ID',
            'updated_at' => 'Updated At',
            'saldo' => 'Saldo',
            'tasa_divisa' => 'Tasa Divisa',
            'dependiente_id' => 'Dependiente ID',
            'deleted_at' => 'Deleted At',
            'mes' => 'Mes',
            'id_titular' => 'Id Titular',
        ];
    }

    /**
     * Gets query for [[Contrato]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContrato()
    {
        return $this->hasOne(Contratos::class, ['id' => 'contrato_id']);
    }

    /**
     * Gets query for [[Dependiente]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDependiente()
    {
        return $this->hasOne(Beneficiarios::class, ['id' => 'dependiente_id']);
    }

    /**
     * Gets query for [[Pagos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPagos()
    {
        return $this->hasMany(Pagos::class, ['recibo_id' => 'id']);
    }

    /**
     * Gets query for [[Titular]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTitular()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'id_titular']);
    }

}
