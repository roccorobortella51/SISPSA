<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "sis_siniestro".
 *
 * @property int $id
 * @property int $idclinica
 * @property string $fecha
 * @property string $hora
 * @property string $idbaremo
 * @property int $atendido
 * @property string|null $fecha_atencion
 * @property string|null $hora_atencion
 * @property int $iduser
 * @property string|null $descripcion
 * @property int $es_cita (0=Siniestro, 1=Cita)
 * @property float|null $costo_total
 * @property string|null $imagen_recipe
 * @property string|null $imagen_informe
 * @property string $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 * @property RmClinica $clinica
 * @property UserDatos $afiliado
 * @property Baremo[] $baremos
 * @property SisSiniestroBaremo[] $sisSiniestroBaremos
 * @property SisConsulta[] $sisConsultas
 */
class SisSiniestro extends \yii\db\ActiveRecord
{

    const APPOINTMENT_STATUS_SCHEDULED = 'scheduled';
    const APPOINTMENT_STATUS_CONFIRMED = 'confirmed';
    const APPOINTMENT_STATUS_CANCELLED = 'cancelled';
    const APPOINTMENT_STATUS_COMPLETED = 'completed';
    const APPOINTMENT_STATUS_NO_SHOW = 'no_show';

    const CANCELLATION_WINDOW_HOURS = 48;

    public $imagenRecipeFile;
    public $imagenInformeFile;
    public $otrosDocumentosFile = []; // Array for multiple documents



    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sis_siniestro';
    }

    /**
     * {@inheritdoc}
     * This method is CRITICAL for making new database columns accessible
     */
    public function attributes()
    {
        return [
            'id',
            'idclinica',
            'fecha',
            'hora',
            'idbaremo',
            'atendido',
            'fecha_atencion',
            'hora_atencion',
            'iduser',
            'descripcion',
            'es_cita',
            'costo_total',
            'imagen_recipe',
            'imagen_informe',
            'created_at',
            'updated_at',
            'deleted_at',
            'admission_analyst',
            'otros_documentos',
            'appointment_status',
            'cancelled_at',
            'cancelled_by',
            'checked_in_at',     // ← ADD THIS
            'checked_out_at',    // ← ADD THIS (for completeness)
            'cancellation_reason',
            'no_show_processed',
            'reminder_24h_sent',
            'nombre_doctor',  // ← CRITICAL: Add this line
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields[] = 'nombre_doctor';
        return $fields;
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_atencion', 'hora_atencion', 'descripcion', 'updated_at', 'deleted_at'], 'default', 'value' => null],
            [['atendido', 'es_cita'], 'default', 'value' => 0],
            [['idclinica', 'fecha', 'hora', 'iduser', 'descripcion'], 'required'],
            [['costo_total'], 'number'],
            [['idclinica', 'atendido', 'iduser', 'es_cita'], 'default', 'value' => null],
            [['idbaremo'], 'default', 'value' => ''],
            [['idclinica', 'atendido', 'iduser', 'es_cita'], 'integer'],
            [['idbaremo'], 'safe'],
            [['fecha', 'fecha_atencion', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['descripcion'], 'string'],
            [['nombre_doctor'], 'safe'],
            [['nombre_doctor'], 'string', 'max' => 255],
            [['nombre_doctor'], 'default', 'value' => null],
            [['appointment_status'], 'string', 'max' => 50],
            [['cancelled_at', 'reminder_24h_sent'], 'safe'],
            [['cancelled_by'], 'string', 'max' => 100],
            [['cancellation_reason'], 'string'],
            [['no_show_processed'], 'boolean'],
            [['appointment_status'], 'default', 'value' => self::APPOINTMENT_STATUS_SCHEDULED],
            [['checked_in_at', 'checked_out_at'], 'safe'],
            [['no_show_processed'], 'default', 'value' => false],

            // Time validation without seconds (HH:MM format)
            [
                ['hora', 'hora_atencion'],
                'match',
                'pattern' => '/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/',
                'message' => 'Formato inválido. Use HH:MM (ejemplo: 14:30)'
            ],

            [['idclinica'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['idclinica' => 'id']],
            [['admission_analyst'], 'safe'],
            [['admission_analyst'], 'string', 'max' => 255],
            [['admission_analyst'], 'default', 'value' => null],

            [['imagen_recipe', 'imagen_informe'], 'string', 'max' => 255],
            [['imagenRecipeFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf', 'maxSize' => 1024 * 1024 * 10],
            [['imagenInformeFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf', 'maxSize' => 1024 * 1024 * 10],
            [['otrosDocumentosFile'], 'each', 'rule' => ['file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf, doc, docx', 'maxSize' => 1024 * 1024 * 10]],
        ];
    }

    /**
     * Normalize time from any format to 24-hour HH:MM
     * @param string $timeStr Time string (e.g., "10:02 AM", "14:30:45", "2:30 PM")
     * @return string Time in 24-hour format HH:MM
     */
    private function normalizeTime($timeStr)
    {
        if (empty($timeStr)) {
            return null;
        }

        // Trim whitespace
        $timeStr = trim($timeStr);

        // Check if already in 24-hour format (HH:MM)
        if (preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $timeStr)) {
            return $timeStr;
        }

        // Handle format with seconds (HH:MM:SS)
        if (preg_match('/^(\d{1,2}):(\d{2}):\d{2}$/', $timeStr, $matches)) {
            return sprintf('%02d:%02d', (int)$matches[1], (int)$matches[2]);
        }

        // Handle format without leading zeros (e.g., "9:05")
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $timeStr, $matches)) {
            $hour = (int)$matches[1];
            $minute = (int)$matches[2];
            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return sprintf('%02d:%02d', $hour, $minute);
            }
        }

        // Handle 12-hour format with AM/PM (e.g., "10:02 AM" or "02:30 PM")
        if (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $timeStr, $matches)) {
            $hour = (int)$matches[1];
            $minute = (int)$matches[2];
            $ampm = strtoupper($matches[3]);

            // Convert to 24-hour format
            if ($ampm === 'PM' && $hour !== 12) {
                $hour += 12;
            } elseif ($ampm === 'AM' && $hour === 12) {
                $hour = 0;
            }

            // Validate ranges
            if ($hour < 0) $hour = 0;
            if ($hour > 23) $hour = 23;
            if ($minute < 0) $minute = 0;
            if ($minute > 59) $minute = 59;

            return sprintf('%02d:%02d', $hour, $minute);
        }

        // Return original as fallback (will be caught by validation)
        return $timeStr;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate()
    {
        // Normalize hora to 24-hour HH:MM format
        if ($this->hora) {
            $this->hora = $this->normalizeTime($this->hora);
        }

        // Normalize hora_atencion to 24-hour HH:MM format
        if ($this->hora_atencion) {
            $this->hora_atencion = $this->normalizeTime($this->hora_atencion);
        }

        // Strip any remaining seconds (just in case)
        if ($this->hora && strpos($this->hora, ':') !== false) {
            $parts = explode(':', $this->hora);
            if (count($parts) >= 2) {
                $this->hora = $parts[0] . ':' . $parts[1];
            }
        }

        if ($this->hora_atencion && strpos($this->hora_atencion, ':') !== false) {
            $parts = explode(':', $this->hora_atencion);
            if (count($parts) >= 2) {
                $this->hora_atencion = $parts[0] . ':' . $parts[1];
            }
        }

        // If idbaremo is a string and not empty, convert it to array for validation
        if (is_string($this->idbaremo) && !empty($this->idbaremo)) {
            $this->idbaremo = explode(',', $this->idbaremo);
        }
        // If idbaremo is null or empty string, convert it to empty array
        if (empty($this->idbaremo)) {
            $this->idbaremo = [];
        }

        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        // Ensure time is in HH:MM format before adding seconds
        if ($this->hora) {
            $this->hora = $this->normalizeTime($this->hora);
        }
        if ($this->hora_atencion) {
            $this->hora_atencion = $this->normalizeTime($this->hora_atencion);
        }

        // Add :00 seconds for database compatibility
        if ($this->hora && strlen($this->hora) == 5) { // HH:MM format
            $this->hora .= ':00';
        }
        if ($this->hora_atencion && strlen($this->hora_atencion) == 5) {
            $this->hora_atencion .= ':00';
        }

        // Convert idbaremo array to string for database storage
        if (is_array($this->idbaremo)) {
            $this->idbaremo = !empty($this->idbaremo) ? implode(',', $this->idbaremo) : '';
        }

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind()
    {
        parent::afterFind();

        // Convert hora from HH:MM:SS to HH:MM for display
        if (!empty($this->hora)) {
            $parts = explode(':', $this->hora);
            if (count($parts) >= 2) {
                $this->hora = $parts[0] . ':' . $parts[1];
            }
        }

        // Convert hora_atencion from HH:MM:SS to HH:MM for display
        if (!empty($this->hora_atencion)) {
            $parts = explode(':', $this->hora_atencion);
            if (count($parts) >= 2) {
                $this->hora_atencion = $parts[0] . ':' . $parts[1];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idclinica' => 'Clínica',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
            'idbaremo' => 'Baremo(s)',
            'atendido' => 'Atendido',
            'fecha_atencion' => 'Fecha Atención',
            'hora_atencion' => 'Hora Atención',
            'iduser' => 'ID Usuario',
            'descripcion' => 'Descripción',
            'admission_analyst' => 'Analista de Admisión',
            'es_cita' => 'Es Cita',
            'costo_total' => 'Costo Total',
            'imagen_recipe' => 'URL Receta Médica',
            'imagen_informe' => 'URL Informe Médico',
            'created_at' => 'Creado El',
            'updated_at' => 'Actualizado El',
            'deleted_at' => 'Eliminado El',
            'otros_documentos' => 'Documentos Adicionales',
            'nombre_doctor' => 'Nombre del Doctor',

        ];
    }

    /**
     * Gets query for [[SisSiniestroBaremos]] (relación con la tabla intermedia)
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSisSiniestroBaremos()
    {
        return $this->hasMany(SisSiniestroBaremo::class, ['siniestro_id' => 'id']);
    }

    /**
     * Gets query for [[Baremos]] (relación con múltiples baremos)
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBaremos()
    {
        return $this->hasMany(Baremo::class, ['id' => 'baremo_id'])
            ->viaTable('sis_siniestro_baremo', ['siniestro_id' => 'id']);
    }

    /**
     * Obtiene los IDs de los baremos como array
     * @return array
     */
    public function getBaremoIds()
    {
        return !empty($this->idbaremo) ? explode(',', $this->idbaremo) : [];
    }

    /**
     * @deprecated Mantenido por compatibilidad
     */
    public function getIdbaremo0()
    {
        $id = !empty($this->idbaremo) ? explode(',', $this->idbaremo)[0] : null;
        return $this->hasOne(Baremo::class, ['id' => 'id'])->where(['id' => $id]);
    }

    /**
     * Gets query for [[Idclinica0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'idclinica']);
    }

    /**
     * Gets query for [[SisConsultas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSisConsultas()
    {
        return $this->hasMany(SisConsulta::class, ['idsiniestro' => 'id']);
    }

    /**
     * Gets query for [[Afiliado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAfiliado()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'iduser']);
    }

    /**
     * Guarda la relación con los baremos
     * @param array $baremoIds Array de IDs de baremos a guardar
     * @return bool
     */
    public function saveBaremos($baremoIds)
    {
        if (!is_array($baremoIds)) {
            $baremoIds = [];
        }

        // 1. Eliminar las relaciones existentes
        SisSiniestroBaremo::deleteAll(['siniestro_id' => $this->id]);

        // 2. Agregar las nuevas relaciones
        foreach ($baremoIds as $baremoId) {
            if (empty($baremoId)) {
                continue;
            }

            // Es más eficiente usar scalar() si solo necesitas un campo
            $baremocosto = Baremo::find()->select('precio')->where(['id' => $baremoId])->scalar();

            if ($baremocosto === null) {
                // Manejar caso donde el Baremo no existe
                continue;
            }

            $relacion = new SisSiniestroBaremo([
                'siniestro_id' => $this->id,
                'baremo_id' => $baremoId,
                'costo' => $baremocosto // El precio del baremo
            ]);

            if (!$relacion->save()) {
                // Retorna false si falla la inserción de una relación
                return false;
            }

            // ============ DECREMENT LOGIC - COMPLETELY REMOVED ============
            // The cantidad_limite is NEVER modified in the database.
            // Availability is calculated dynamically in _form.php as:
            // remaining = original_limit - veces_usado
            // ============ END REMOVED LOGIC ============
        }

        // ====================================================================
        // 3. Lógica para actualizar el costo_total en SisSiniestro
        // ====================================================================

        // a) Calcular la suma total de los costos de los baremos para este siniestro
        $totalCosto = SisSiniestroBaremo::find()
            ->where(['siniestro_id' => $this->id])
            ->sum('costo');

        // b) Asignar el total al campo costo_total del modelo actual ($this es SisSiniestro)
        // Se usa (float) para asegurar que el valor sea numérico (sum() puede devolver NULL o un string)
        $this->costo_total = (float) $totalCosto;

        // c) Guardar el modelo SisSiniestro
        if (!$this->save(false)) { // Usamos save(false) para omitir la validación de otros campos del SisSiniestro
            // Retorna false si falla la actualización del costo_total
            return false;
        }

        // ====================================================================

        return true;
    }

    /**
     * Valida los baremos seleccionados contra las restricciones del plan
     * @param array $baremoIds Array de IDs de baremos a validar
     * @param int $userId ID del usuario/afiliado
     * @param int $esCita 0=Siniestro, 1=Cita (AHORA AMBOS VALIDAN COBERTURA)
     * @param SisSiniestro|null $model El modelo actual (para updates)
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validarBaremosConPlan($baremoIds, $userId, $esCita = 0, $model = null)
    {
        $errors = [];

        if (empty($baremoIds) || !is_array($baremoIds)) {
            return ['valid' => true, 'errors' => []];
        }

        // Obtener datos del afiliado
        $afiliado = UserDatos::findOne($userId);
        if (!$afiliado || !$afiliado->plan_id) {
            $errors[] = 'No se pudo obtener la información del plan del afiliado.';
            return ['valid' => false, 'errors' => $errors];
        }

        // Obtener el contrato activo del afiliado
        $contrato = Contratos::find()
            ->where(['user_id' => $userId])
            ->andWhere(['estatus' => 'Activo'])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        if (!$contrato) {
            $errors[] = 'No se encontró un contrato activo para el afiliado.';
            return ['valid' => false, 'errors' => $errors];
        }

        $fechaInicioContrato = new \DateTime($contrato->fecha_ini);
        $fechaActual = new \DateTime();

        // ============================================
        // CALCULAR COSTO TOTAL SELECCIONADO Y COBERTURA DISPONIBLE
        // ============================================
        $costoTotalSeleccionado = 0;
        foreach ($baremoIds as $baremoId) {
            if (empty($baremoId)) continue;
            $baremo = Baremo::findOne($baremoId);
            if ($baremo) {
                $costoTotalSeleccionado += (float)$baremo->precio;
            }
        }

        // Obtener cobertura disponible (suma de TODAS las atenciones Y citas existentes)
        $sumatoriaEventos = self::find()
            ->where(['iduser' => $afiliado->id])
            ->sum('costo_total');

        // Si es una actualización, restar el costo actual del modelo
        if ($model && !$model->isNewRecord && $model->costo_total) {
            $sumatoriaEventos -= (float)$model->costo_total;
        }

        $plan = Planes::findOne($afiliado->plan_id);
        $coberturaTotal = $plan ? (float)$plan->cobertura : 0;
        $coberturaDisponible = $coberturaTotal - $sumatoriaEventos;

        // ============================================
        // VALIDAR CADA BAREMO INDIVIDUALMENTE
        // ============================================
        foreach ($baremoIds as $baremoId) {
            if (empty($baremoId)) continue;

            // Obtener configuración del baremo en el plan
            $planItemCobertura = PlanesItemsCobertura::find()
                ->where(['plan_id' => $afiliado->plan_id, 'baremo_id' => $baremoId])
                ->one();

            if (!$planItemCobertura) {
                $baremo = Baremo::findOne($baremoId);
                $nombreBaremo = $baremo ? $baremo->nombre_servicio : "ID: $baremoId";
                $errors[] = "El servicio '$nombreBaremo' no está configurado en el plan del afiliado.";
                continue;
            }

            $baremo = Baremo::findOne($baremoId);
            $nombreBaremo = $baremo ? $baremo->nombre_servicio : "ID: $baremoId";
            $precioBaremo = $baremo ? (float)$baremo->precio : 0;

            // ============================================
            // 1. VALIDACIÓN DE PLAZO DE ESPERA (APLICA PARA AMBOS)
            // ============================================
            if (!empty($planItemCobertura->plazo_espera) && $planItemCobertura->plazo_espera > 0) {
                $diff = $fechaInicioContrato->diff($fechaActual);
                $mesesTranscurridos = $diff->y * 12 + $diff->m;

                if ($mesesTranscurridos < (int)$planItemCobertura->plazo_espera) {
                    $tipoEvento = ($esCita == 1) ? 'cita' : 'atención';
                    $errors[] = "No se puede registrar la $tipoEvento para '$nombreBaremo'. Aún no ha cumplido el plazo de espera de {$planItemCobertura->plazo_espera} meses.";
                    continue;
                }
            }

            // ============================================
            // 2. VALIDACIÓN DE LÍMITE DE USO (APLICA PARA AMBOS)
            // ============================================
            if ($planItemCobertura->cantidad_limite !== null && $planItemCobertura->cantidad_limite > 0) {
                $anioActual = self::calcularAnioVigencia($fechaInicioContrato, $fechaActual);
                list($inicioAnioVigencia, $finAnioVigencia) = self::calcularPeriodoVigencia($fechaInicioContrato, $anioActual);

                // Contar usos en el período actual
                $eventosUsados = self::find()
                    ->alias('s')
                    ->innerJoin('sis_siniestro_baremo sb', 'sb.siniestro_id = s.id')
                    ->where(['s.iduser' => $afiliado->id])
                    ->andWhere(['sb.baremo_id' => $baremoId])
                    ->andWhere(['>=', 's.fecha', $inicioAnioVigencia->format('Y-m-d')])
                    ->andWhere(['<=', 's.fecha', $finAnioVigencia->format('Y-m-d')]);

                if ($model && !$model->isNewRecord) {
                    $eventosUsados->andWhere(['<>', 's.id', $model->id]);
                }

                $vecesUsado = $eventosUsados->count();

                if ($vecesUsado >= $planItemCobertura->cantidad_limite) {
                    $tipoEvento = ($esCita == 1) ? 'cita' : 'atención';
                    $errors[] = "No se puede registrar la $tipoEvento para '$nombreBaremo'. Ha alcanzado el límite de {$planItemCobertura->cantidad_limite} usos. Ya se ha utilizado $vecesUsado veces.";
                    continue;
                }
            }
        }

        // ============================================
        // 3. VALIDACIÓN DE COBERTURA TOTAL (AHORA APLICA PARA AMBOS)
        // ============================================
        $tipoEvento = ($esCita == 1) ? 'cita' : 'atención';

        if ($costoTotalSeleccionado > $coberturaDisponible) {
            $errors[] = "No se puede crear la $tipoEvento. Cobertura insuficiente.\n" .
                "Cobertura disponible: $" . number_format($coberturaDisponible, 2) . "\n" .
                "Costo total de servicios: $" . number_format($costoTotalSeleccionado, 2) . "\n" .
                "Diferencia: $" . number_format($costoTotalSeleccionado - $coberturaDisponible, 2);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Calcula en qué año de vigencia se encuentra el afiliado
     * @param \DateTime $fechaInicio Fecha de inicio del contrato
     * @param \DateTime $fechaActual Fecha actual
     * @return int Año de vigencia (0 = primer año, 1 = segundo año, etc.)
     */
    private static function calcularAnioVigencia($fechaInicio, $fechaActual)
    {
        $diferencia = $fechaInicio->diff($fechaActual);
        return $diferencia->y;
    }

    /**
     * Calcula el período de vigencia (fecha de inicio y fin) para un año específico
     * @param \DateTime $fechaInicio Fecha de inicio del contrato
     * @param int $anioVigencia Año de vigencia (0 = primer año, 1 = segundo año, etc.)
     * @return array [DateTime $inicio, DateTime $fin]
     */
    private static function calcularPeriodoVigencia($fechaInicio, $anioVigencia)
    {
        $inicio = clone $fechaInicio;
        $inicio->modify("+{$anioVigencia} years");

        $fin = clone $inicio;
        $fin->modify('+1 year -1 day');

        return [$inicio, $fin];
    }

    /**
     * Parsea el plazo de espera en formato texto a número de meses
     * @param string $plazoEspera Ej: "4 meses", "1 mes", "6 months"
     * @return int Número de meses
     */
    private static function parsePlazoEspera($plazoEspera)
    {
        if (empty($plazoEspera)) {
            return 0;
        }

        // Extraer el número del texto
        preg_match('/\d+/', $plazoEspera, $matches);

        if (!empty($matches)) {
            return (int)$matches[0];
        }

        return 0;
    }
    /**
     * Get status options for dropdown
     */
    public static function getAppointmentStatusOptions()
    {
        return [
            self::APPOINTMENT_STATUS_SCHEDULED => 'Agendada',
            self::APPOINTMENT_STATUS_CONFIRMED => 'Confirmada',
            self::APPOINTMENT_STATUS_CANCELLED => 'Cancelada',
            self::APPOINTMENT_STATUS_COMPLETED => 'Completada',
            self::APPOINTMENT_STATUS_NO_SHOW => 'No Asistió',
        ];
    }

    /**
     * Get status badge HTML
     */
    public function getAppointmentStatusBadge()
    {
        $badges = [
            self::APPOINTMENT_STATUS_SCHEDULED => '<span class="badge badge-warning"><i class="fas fa-calendar"></i> Agendada</span>',
            self::APPOINTMENT_STATUS_CONFIRMED => '<span class="badge badge-info"><i class="fas fa-check-circle"></i> Confirmada</span>',
            self::APPOINTMENT_STATUS_CANCELLED => '<span class="badge badge-secondary"><i class="fas fa-ban"></i> Cancelada</span>',
            self::APPOINTMENT_STATUS_COMPLETED => '<span class="badge badge-success"><i class="fas fa-check-double"></i> Completada</span>',
            self::APPOINTMENT_STATUS_NO_SHOW => '<span class="badge badge-danger"><i class="fas fa-user-slash"></i> No Asistió</span>',
        ];

        return $badges[$this->appointment_status] ?? $badges[self::APPOINTMENT_STATUS_SCHEDULED];
    }

    /**
     * Check if appointment can be cancelled without penalty
     */
    public function canBeCancelledWithoutPenalty()
    {
        if (!$this->fecha_atencion) {
            return false;
        }

        $appointmentDateTime = new \DateTime($this->fecha_atencion . ' ' . ($this->hora_atencion ?? '00:00:00'));
        $now = new \DateTime();
        $hoursDifference = ($now->diff($appointmentDateTime)->days * 24) + $now->diff($appointmentDateTime)->h;

        return $hoursDifference >= self::CANCELLATION_WINDOW_HOURS;
    }

    /**
     * Get hours until appointment
     */
    public function getHoursUntilAppointment()
    {
        if (!$this->fecha_atencion) {
            return null;
        }

        $appointmentDateTime = new \DateTime($this->fecha_atencion . ' ' . ($this->hora_atencion ?? '00:00:00'));
        $now = new \DateTime();
        $diff = $now->diff($appointmentDateTime);

        if ($now > $appointmentDateTime) {
            return - ($diff->h + ($diff->days * 24));
        }

        return $diff->h + ($diff->days * 24);
    }

    /**
     * Cancel appointment
     */
    public function cancelAppointment($reason, $cancelledBy = null)
    {
        if ($this->canBeCancelledWithoutPenalty()) {
            // Early cancellation - restore coverage
            $this->appointment_status = self::APPOINTMENT_STATUS_CANCELLED;
            $this->costo_total = 0; // Remove cost to restore coverage
        } else {
            // Late cancellation - treat as no-show, keep deduction
            $this->appointment_status = self::APPOINTMENT_STATUS_NO_SHOW;
            $this->no_show_processed = true;
        }

        $this->cancelled_at = date('Y-m-d H:i:s');
        $this->cancelled_by = $cancelledBy ?? Yii::$app->user->identity->username ?? 'system';
        $this->cancellation_reason = $reason;

        return $this->save(false);
    }


    /**
     * Complete appointment
     */
    public function complete()
    {
        $this->checked_out_at = date('Y-m-d H:i:s');
        $this->appointment_status = self::APPOINTMENT_STATUS_COMPLETED;
        $this->atendido = 1;
        return $this->save(false);
    }
}
