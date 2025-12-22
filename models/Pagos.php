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
 * @property string|null $observacion
 * @property float|null $tasa
 *
 * @property Recibos $recibo
 * @property UserDatos $userDatos
 * @property Cuotas[] $cuotas
 * @property Contratos[] $contratos
 */
class Pagos extends \yii\db\ActiveRecord
{
    public $imagen_prueba_file; // atributo para el archivo subido
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
    // En Pagos.php, actualiza la regla para el archivo:
    public function rules()
    {
        return [
            // --- MANDATORY FIELDS ---
            [['metodo_pago', 'fecha_pago', 'monto_pagado', 'tasa', 'monto_usd', 'numero_referencia_pago'], 'required'],
            // ---------------------------------

            // Para nuevos registros, requerir O imagen_prueba_file O imagen_prueba (ya subida)
            [['imagen_prueba'], 'required', 'on' => 'create', 'message' => 'Comprobante de Pago no puede estar vacío'],

            [['corporativo_id', 'pago_corporativo_id'], 'integer'],
            [['tipo_pago'], 'string', 'max' => 50],
            [['tipo_pago'], 'default', 'value' => 'individual'],

            [['recibo_id', 'fecha_pago', 'monto_pagado', 'metodo_pago', 'estatus', 'numero_referencia_pago', 'updated_at', 'imagen_prueba', 'user_id', 'nombre_conciliador', 'fecha_conciliacion', 'fecha_registro', 'deleted_at', 'conciliador_id', 'conciliado'], 'default', 'value' => null],
            [['monto_usd', 'tasa'], 'default', 'value' => 0],
            [['created_at', 'fecha_pago', 'updated_at', 'fecha_conciliacion', 'fecha_registro', 'deleted_at'], 'safe'],
            [['recibo_id', 'user_id', 'conciliador_id', 'conciliado'], 'default', 'value' => null],
            [['recibo_id', 'user_id', 'conciliador_id', 'conciliado'], 'integer'],
            [['monto_pagado', 'monto_usd', 'tasa'], 'number'],
            [['metodo_pago', 'estatus', 'numero_referencia_pago', 'imagen_prueba', 'nombre_conciliador', 'observacion'], 'string'],
            [['imagen_prueba_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 1024 * 1024 * 5, 'tooBig' => 'El archivo no debe exceder los 5MB.'],
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
            'observacion' => 'Observación',
            'tasa' => 'Tasa de Cambio',
            'imagen_prueba_file' => 'Comprobante de Pago',
            'corporativo_id' => 'Corporativo ID',
            'pago_corporativo_id' => 'Pago Corporativo ID',
            'tipo_pago' => 'Tipo de Pago',
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
            return $this->monto_pagado / $this->tasa;
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
            return $this->monto_pagado * $this->tasa;
        }
        return $this->monto_pagado;
    }

    /**
     * Before save event
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Calcular monto_usd automáticamente si no está establecido
            if (empty($this->monto_usd) && $this->monto_pagado && $this->tasa) {
                $this->monto_usd = $this->calcularMontoUsd();
            }

            // Establecer fecha de registro si es nuevo
            if ($insert) {
                $this->fecha_registro = date('Y-m-d H:i:s');
            }

            return true;
        }
        return false;
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

        // Ensure we always return an array with both keys
        return [
            'total_monto' => $result['total_monto'] ?? 0,
            'total_count' => $result['total_count'] ?? 0
        ];
    }
    // Add relations
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
    // In Pagos.php model:

    /**
     * Get the payer name (corporation or individual)
     * @return string
     */
    public function getNombrePagador()
    {
        // Check if this is a corporate payment
        if ($this->tipo_pago === 'corporativo' || $this->corporativo_id) {
            // Corporate payment
            if ($this->corporativo && $this->corporativo->nombre) {
                return $this->corporativo->nombre . ' (Corporativo)';
            } else {
                return 'Corporativo (ID: ' . ($this->corporativo_id ?? 'N/A') . ')';
            }
        } else {
            // Individual payment
            return $this->userDatos ? $this->userDatos->nombres . ' ' . $this->userDatos->apellidos : 'N/A';
        }
    }

    /**
     * Get the payer identification (RIF for corporations, Cedula for individuals)
     * @return string
     */
    public function getIdentificacionPagador()
    {
        // Check if this is a corporate payment
        if ($this->tipo_pago === 'corporativo' || $this->corporativo_id) {
            // Corporate payment - show RIF
            if ($this->corporativo && $this->corporativo->rif) {
                return $this->corporativo->rif;
            } else {
                return 'N/A';
            }
        } else {
            // Individual payment - show cedula
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
