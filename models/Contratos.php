<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contratos".
 *
 * @property int $id
 * @property string $created_at
 * @property int|null $plan_id
 * @property int|null $ente_id
 * @property int|null $clinica_id
 * @property string|null $fecha_ini
 * @property string|null $fecha_ven
 * @property float|null $monto
 * @property string|null $estatus
 * @property string|null $nrocontrato
 * @property string|null $frecuencia_pago
 * @property string|null $sucursal
 * @property string|null $moneda
 * @property string|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $anulado_por
 * @property string|null $anulado_fecha
 * @property string|null $anulado_motivo
 * @property int|null $user_id
 * @property string|null $PDF
 *
 * @property RmClinica $clinica
 * @property Planes $plan
 * @property Recibos[] $recibos
 * @property UserDatos $user
 */
class Contratos extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contratos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_ini', 'fecha_ven'], 'required', 'message' => 'Este campo es obligatorio.'],
            [['plan_id', 'ente_id', 'clinica_id', 'fecha_ini', 'fecha_ven', 'monto', 'estatus', 'nrocontrato', 'frecuencia_pago', 'sucursal', 'moneda', 'updated_at', 'deleted_at', 'anulado_por', 'anulado_fecha', 'anulado_motivo', 'user_id', 'PDF'], 'default', 'value' => null],
            [['created_at', 'fecha_ini', 'fecha_ven', 'updated_at', 'deleted_at', 'anulado_fecha'], 'safe'],
            [['plan_id', 'ente_id', 'clinica_id', 'anulado_por', 'user_id'], 'default', 'value' => null],
            [['plan_id', 'ente_id', 'clinica_id', 'anulado_por', 'user_id'], 'integer'],
            [['monto'], 'number'],
            [['estatus', 'nrocontrato', 'frecuencia_pago', 'sucursal', 'moneda', 'anulado_motivo', 'PDF'], 'string'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Planes::class, 'targetAttribute' => ['plan_id' => 'id']],
            [['clinica_id'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['clinica_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'plan_id' => 'Plan ID',
            'ente_id' => 'Ente ID',
            'clinica_id' => 'Clinica ID',
            'fecha_ini' => 'Fecha Ini *',
            'fecha_ven' => 'Fecha Ven *',
            'monto' => 'Monto',
            'estatus' => 'Estatus',
            'nrocontrato' => 'Nrocontrato',
            'frecuencia_pago' => 'Frecuencia Pago',
            'sucursal' => 'Sucursal',
            'moneda' => 'Moneda',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'anulado_por' => 'Anulado Por',
            'anulado_fecha' => 'Anulado Fecha',
            'anulado_motivo' => 'Anulado Motivo',
            'user_id' => 'User ID',
            'PDF' => 'Pdf',
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
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Planes::class, ['id' => 'plan_id']);
    }

    /**
     * Gets query for [[Recibos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecibos()
    {
        return $this->hasMany(Recibos::class, ['contrato_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_id']);
    }

    public function getPagos()
    {
        return $this->hasMany(Pagos::class, ['user_id' => 'user_id']);
    }

}
