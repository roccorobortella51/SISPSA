<?php
// app/models/Pagos.php - Updated with PHPDoc annotations

namespace app\models;

use Yii;
use app\models\Recibos;

/**
 * This is the model class for table "pagos".
 * 
 * @property int $id
 * @property string $created_at
 * @property string $fecha_pago
 * @property float $monto_pagado
 * @property string $metodo_pago
 * @property string $estatus
 * @property string $numero_referencia_pago
 * @property string $updated_at
 * @property string $imagen_prueba
 * @property int $user_id
 * @property string $nombre_conciliador
 * @property string $fecha_conciliacion
 * @property string $fecha_registro
 * @property string $deleted_at
 * @property int $conciliador_id
 * @property int $conciliado
 * @property float $monto_usd
 * @property string $observacion
 * @property int $corporativo_id
 * @property int $pago_corporativo_id
 * @property string $tipo_pago
 * 
 * @property UserDatos $userDatos
 * @property Cuotas[] $cuotas
 * @property Contratos[] $contratos
 * @property Corporativo $corporativo
 * @property Pagos $pagoCorporativo
 * @property Pagos[] $pagosAfiliados
 */
class Pagos extends \yii\db\ActiveRecord
{
    public $imagen_prueba_file;
    public $tasa;

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
            [['metodo_pago', 'fecha_pago', 'monto_pagado', 'tasa'], 'required'],
            ['monto_usd', 'required', 'message' => 'El monto en USD es requerido'],
            ['tasa', 'required', 'when' => function ($model) {
                return !in_array($model->metodo_pago, ['Zelle', 'Efectivo - Dólar ($)']);
            }, 'message' => 'La tasa de cambio es requerida para este método de pago'],
            ['numero_referencia_pago', 'required', 'when' => function ($model) {
                return $model->metodo_pago !== 'Efectivo - Dólar ($)';
            }, 'message' => 'El número de referencia es requerido para este método de pago'],
            [
                ['imagen_prueba'],
                'required',
                'when' => function ($model) {
                    return $model->metodo_pago !== 'Efectivo - Dólar ($)';
                },
                'on' => 'create',
                'message' => 'Comprobante de Pago no puede estar vacío'
            ],
            [['corporativo_id', 'pago_corporativo_id'], 'integer'],
            [['tipo_pago'], 'string', 'max' => 50],
            [['tipo_pago'], 'default', 'value' => 'individual'],
            [['fecha_pago', 'monto_pagado', 'metodo_pago', 'estatus', 'numero_referencia_pago', 'updated_at', 'imagen_prueba', 'user_id', 'nombre_conciliador', 'fecha_conciliacion', 'fecha_registro', 'deleted_at', 'conciliador_id', 'conciliado'], 'default', 'value' => null],
            [['monto_usd', 'tasa'], 'default', 'value' => 0],
            [['created_at', 'fecha_pago', 'updated_at', 'fecha_conciliacion', 'fecha_registro', 'deleted_at'], 'safe'],
            [['user_id', 'conciliador_id', 'conciliado', 'corporativo_id', 'pago_corporativo_id'], 'integer'],
            [['monto_pagado', 'monto_usd', 'tasa'], 'number'],
            [['metodo_pago', 'estatus', 'numero_referencia_pago', 'imagen_prueba', 'nombre_conciliador', 'observacion'], 'string'],
            [['imagen_prueba_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 1024 * 1024 * 5, 'tooBig' => 'El archivo no debe exceder los 5MB.'],
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
            'observacion' => 'Observación',
            'tasa' => 'Tasa de Cambio',
            'imagen_prueba_file' => 'Comprobante de Pago',
            'corporativo_id' => 'Corporativo ID',
            'pago_corporativo_id' => 'Pago Corporativo ID',
            'tipo_pago' => 'Tipo de Pago',
        ];
    }

    /**
     * Gets query for [[UserDatos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserDatos()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Cuotas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCuotas()
    {
        return $this->hasMany(Cuotas::class, ['id_pago' => 'id']);
    }

    /**
     * Gets query for [[Contratos]] a través de UserDatos.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContratos()
    {
        return $this->hasMany(Contratos::class, ['user_id' => 'user_id']);
    }

    /**
     * Obtiene la URL de la imagen de prueba
     *
     * @return string|null
     */
    public function getImagenPruebaUrl()
    {
        if ($this->imagen_prueba) {
            return \Yii::getAlias('@web') . '/' . $this->imagen_prueba;
        }
        return null;
    }

    /**
     * Calcula el monto en USD basado en la tasa
     *
     * @return float
     */
    public function calcularMontoUsd()
    {
        if ($this->monto_pagado && $this->tasa && $this->tasa > 0) {
            return $this->monto_pagado * $this->tasa;
        }
        return $this->monto_pagado;
    }

    /**
     * Calcula el monto en Bs basado en la tasa
     *
     * @return float
     */
    public function calcularMontoBs()
    {
        if ($this->monto_pagado && $this->tasa) {
            return $this->monto_pagado / $this->tasa;
        }
        return $this->monto_pagado;
    }

    /**
     * Before validate event
     */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if (empty($this->monto_usd) && !empty($this->monto_pagado) && !empty($this->tasa) && $this->tasa > 0) {
                $this->monto_usd = round($this->monto_pagado * $this->tasa, 4);
                Yii::info("beforeValidate: Calculated monto_usd={$this->monto_usd} for {$this->metodo_pago}");
            }

            if ($this->metodo_pago === 'Efectivo - Dólar ($)') {
                $this->numero_referencia_pago = null;
                $this->imagen_prueba = null;
                Yii::info("beforeValidate: Cleared numero_referencia_pago and imagen_prueba for Efectivo - Dólar ($)");
            }

            return true;
        }
        return false;
    }

    /**
     * Before save event
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (empty($this->monto_usd) && $this->monto_pagado && !empty($this->tasa) && $this->tasa > 0) {
                $this->monto_usd = round($this->monto_pagado * $this->tasa, 4);
                Yii::info("beforeSave: Calculated monto_usd={$this->monto_usd} for {$this->metodo_pago} with tasa={$this->tasa}");
            }

            if ($insert) {
                $this->fecha_registro = date('Y-m-d H:i:s');
            }

            return true;
        }
        return false;
    }

    /**
     * After save event - Generates receipts automatically when a new payment is created
     * 
     * @param bool $insert Whether this is a new record
     * @param array $changedAttributes The changed attributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            Yii::info("afterSave: New payment #{$this->id} created. Generating receipts...", 'pagos');

            // Use the component - generates ONE RECEIPT PER INSTALLMENT
            $receipts = \app\components\ReceiptGenerator::generateForPayment($this);

            if (!empty($receipts)) {
                Yii::info("afterSave: Generated " . count($receipts) . " receipts for payment #{$this->id}", 'pagos');
            } else {
                Yii::warning("afterSave: No receipts generated for payment #{$this->id}", 'pagos');
            }
        }
    }

    /**
     * Obtiene el nombre completo del usuario
     *
     * @return string
     */
    public function getNombreUsuario()
    {
        return $this->userDatos ? $this->userDatos->nombres . ' ' . $this->userDatos->apellidos : 'N/A';
    }

    /**
     * Obtiene la cédula del usuario
     *
     * @return string
     */
    public function getCedulaUsuario()
    {
        return $this->userDatos ? $this->userDatos->cedula : 'N/A';
    }

    /**
     * Verifica si el pago está conciliado
     *
     * @return bool
     */
    public function getEstaConciliado()
    {
        return $this->estatus === 'Conciliado';
    }

    /**
     * Obtiene el estado del pago en formato legible
     *
     * @return string
     */
    public function getEstadoLegible()
    {
        $estados = [
            'Conciliado' => 'Conciliado',
            'Por Conciliar' => 'Por Conciliar',
        ];

        return $estados[$this->estatus] ?? $this->estatus;
    }

    /**
     * Obtiene el resumen de pagos para un rango de fechas y estado específico
     * 
     * @param string $startDate Fecha de inicio (Y-m-d)
     * @param string $endDate Fecha de fin (Y-m-d)
     * @param string $status Estado del pago ('Por Conciliar', 'Conciliado')
     * @return array|null
     */
    public static function getPaymentsSummaryForDateRange($startDate, $endDate, $status = 'Por Conciliar')
    {
        $adjustedEndDate = (new \DateTime($endDate))->modify('+1 day')->format('Y-m-d');

        $query = self::find()
            ->where(['pagos.estatus' => $status])
            ->andWhere([
                'between',
                new \yii\db\Expression('COALESCE(pagos.fecha_pago, pagos.fecha_conciliacion)'),
                $startDate,
                $adjustedEndDate
            ]);

        $result = $query->select(
            [
                'total_monto' => 'COALESCE(SUM(pagos.monto_usd), 0)',
                'total_count' => 'COUNT(*)'
            ]
        )
            ->asArray()
            ->one();

        return [
            'total_monto' => $result['total_monto'] ?? 0,
            'total_count' => $result['total_count'] ?? 0
        ];
    }

    public function getCorporativo()
    {
        return $this->hasOne(Corporativo::class, ['id' => 'corporativo_id']);
    }

    public function getPagoCorporativo()
    {
        return $this->hasOne(Pagos::class, ['id' => 'pago_corporativo_id']);
    }

    public function getPagosAfiliados()
    {
        return $this->hasMany(Pagos::class, ['pago_corporativo_id' => 'id']);
    }

    /**
     * Returns the list of scenarios and their corresponding active attributes.
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = ['metodo_pago', 'fecha_pago', 'monto_pagado', 'tasa', 'monto_usd', 'numero_referencia_pago', 'imagen_prueba_file', 'user_id', 'estatus', 'observacion'];
        $scenarios['update'] = ['metodo_pago', 'fecha_pago', 'monto_pagado', 'tasa', 'monto_usd', 'numero_referencia_pago', 'imagen_prueba_file', 'estatus', 'observacion'];
        return $scenarios;
    }

    /**
     * Get the payer name (corporation or individual)
     * @return string
     */
    public function getNombrePagador()
    {
        if ($this->tipo_pago === 'corporativo' || $this->corporativo_id) {
            if ($this->corporativo && $this->corporativo->nombre) {
                return $this->corporativo->nombre . ' (Corporativo)';
            } else {
                return 'Corporativo (ID: ' . ($this->corporativo_id ?? 'N/A') . ')';
            }
        } else {
            return $this->userDatos ? $this->userDatos->nombres . ' ' . $this->userDatos->apellidos : 'N/A';
        }
    }

    /**
     * Get the payer identification (RIF for corporations, Cedula for individuals)
     * @return string
     */
    public function getIdentificacionPagador()
    {
        if ($this->tipo_pago === 'corporativo' || $this->corporativo_id) {
            if ($this->corporativo && $this->corporativo->rif) {
                return $this->corporativo->rif;
            } else {
                return 'N/A';
            }
        } else {
            if ($this->userDatos) {
                $cedula = $this->userDatos->cedula;
                $tipoCedula = $this->userDatos->tipo_cedula;
                return $tipoCedula && $cedula ? $tipoCedula . '-' . $cedula : ($cedula ?? 'N/A');
            }
            return 'N/A';
        }
    }

    /**
     * Get the payment type badge
     * @return string
     */
    public function getTipoPagoBadge()
    {
        if ($this->tipo_pago === 'corporativo' || $this->corporativo_id) {
            return '<span class="badge badge-info">Corporativo</span>';
        } else {
            return '<span class="badge badge-primary">Individual</span>';
        }
    }
}
