<?php
// app/models/DeclaracionDeSalud.php

namespace app\models;

use Yii;

/**
 * This is the model class for table "declaracion_de_salud".
 *
 * @property int $id
 * @property string $created_at
 * @property string $p1_sino
 * @property string $p1_especifica
 * @property string $p2_sino
 * @property string $p2_especifica
 * @property string $p3_sino
 * @property string $p3_especifica
 * @property string $p4_sino
 * @property string $p4_especifica
 * @property string $p5_sino
 * @property string $p5_especifica
 * @property string $p6_sino
 * @property string $p6_especifica
 * @property string $p7_sino
 * @property string $p7_especifica
 * @property string $p8_sino
 * @property string $p8_especifica
 * @property string $p9_sino
 * @property string $p9_especifica
 * @property string $p10_sino
 * @property string $p10_especifica
 * @property string $p11_sino
 * @property string $p11_especifica
 * @property string $p12_sino
 * @property string $p12_especifica
 * @property string $p13_sino
 * @property string $p13_especifica
 * @property string $p14_sino
 * @property string $p14_especifica
 * @property string $p15_sino
 * @property string $p15_especifica
 * @property string $p16_sino
 * @property string $p16_especifica
 * @property string|null $deleted_at
 * @property string|null $updated_at
 * @property int|null $ver_usuario_id
 * @property string|null $ver_observacion
 * @property string|null $ver_si_no
 * @property string|null $ver_fecha
 * @property string|null $url_video_declaracion
 * @property string|null $estatus
 * @property int|null $user_id
 * @property string|null $estatura
 * @property string|null $peso
 *
 * @property UserDatos $user
 */
class DeclaracionDeSalud extends \yii\db\ActiveRecord
{
    // Question labels for mapping
    const QUESTIONS = [
        'p1' => 'Enfermedades Cardiovasculares',
        'p2' => 'Enfermedades Vasculares',
        'p3' => 'Enfermedades de la Sangre',
        'p4' => 'Enfermedades de las Vías Respiratorias',
        'p5' => 'Enfermedades de las Vías Digestivas',
        'p6' => 'Enfermedades del Sistema Endocrino',
        'p7' => 'Enfermedades Osteomusculares',
        'p8' => 'Enfermedades Genito-Urinarias',
        'p9' => 'Enfermedades de Piel, Ojos, Oídos, Nariz, Garganta',
        'p10' => 'Enfermedades o Desorden Mental',
        'p11' => 'Enfermedades Transitorias Crónicas',
        'p12' => 'Cáncer, Tumores, Quistes',
        'p13' => 'Enfermedades Propias de la Mujer',
        'p14' => 'Transfusiones, Quimioterapia, Radioterapia',
        'p15' => 'Intervenciones Quirúrgicas',
        'p16' => 'Otras Enfermedades o Patologías',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'declaracion_de_salud';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deleted_at', 'updated_at', 'ver_usuario_id', 'ver_observacion', 'ver_si_no', 'ver_fecha', 'url_video_declaracion', 'estatus', 'user_id', 'estatura', 'peso'], 'default', 'value' => null],
            [['p16_especifica'], 'default', 'value' => ''],
            [['created_at', 'deleted_at', 'updated_at', 'ver_fecha'], 'safe'],
            [['p1_sino', 'p1_especifica', 'p2_sino', 'p2_especifica', 'p3_sino', 'p3_especifica', 'p4_sino', 'p4_especifica', 'p5_sino', 'p5_especifica', 'p6_sino', 'p6_especifica', 'p7_sino', 'p7_especifica', 'p8_sino', 'p8_especifica', 'p9_sino', 'p9_especifica', 'p10_sino', 'p10_especifica', 'p11_sino', 'p11_especifica', 'p12_sino', 'p12_especifica', 'p13_sino', 'p13_especifica', 'p14_sino', 'p14_especifica', 'p15_sino', 'p15_especifica', 'p16_sino', 'p16_especifica', 'ver_observacion', 'ver_si_no', 'url_video_declaracion', 'estatus', 'estatura', 'peso'], 'string'],
            [['ver_usuario_id', 'user_id'], 'default', 'value' => null],
            [['ver_usuario_id', 'user_id'], 'integer'],
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
            'created_at' => 'Fecha de Creación',
            'p1_sino' => 'Enfermedades Cardiovasculares',
            'p1_especifica' => 'Especificación',
            'p2_sino' => 'Enfermedades Vasculares',
            'p2_especifica' => 'Especificación',
            'p3_sino' => 'Enfermedades de la Sangre',
            'p3_especifica' => 'Especificación',
            'p4_sino' => 'Enfermedades de las Vías Respiratorias',
            'p4_especifica' => 'Especificación',
            'p5_sino' => 'Enfermedades de las Vías Digestivas',
            'p5_especifica' => 'Especificación',
            'p6_sino' => 'Enfermedades del Sistema Endocrino',
            'p6_especifica' => 'Especificación',
            'p7_sino' => 'Enfermedades Osteomusculares',
            'p7_especifica' => 'Especificación',
            'p8_sino' => 'Enfermedades Genito-Urinarias',
            'p8_especifica' => 'Especificación',
            'p9_sino' => 'Enfermedades de Piel, Ojos, Oídos, Nariz, Garganta',
            'p9_especifica' => 'Especificación',
            'p10_sino' => 'Enfermedades o Desorden Mental',
            'p10_especifica' => 'Especificación',
            'p11_sino' => 'Enfermedades Transitorias Crónicas',
            'p11_especifica' => 'Especificación',
            'p12_sino' => 'Cáncer, Tumores, Quistes',
            'p12_especifica' => 'Especificación',
            'p13_sino' => 'Enfermedades Propias de la Mujer',
            'p13_especifica' => 'Especificación',
            'p14_sino' => 'Transfusiones, Quimioterapia, Radioterapia',
            'p14_especifica' => 'Especificación',
            'p15_sino' => 'Intervenciones Quirúrgicas',
            'p15_especifica' => 'Especificación',
            'p16_sino' => 'Otras Enfermedades o Patologías',
            'p16_especifica' => 'Especificación',
            'deleted_at' => 'Eliminado El',
            'updated_at' => 'Actualizado El',
            'ver_usuario_id' => 'Ver Usuario ID',
            'ver_observacion' => 'Ver Observacion',
            'ver_si_no' => 'Ver Si No',
            'ver_fecha' => 'Ver Fecha',
            'url_video_declaracion' => 'Url Video Declaracion',
            'estatus' => 'Estatus',
            'user_id' => 'Afiliado',
            'estatura' => 'Estatura',
            'peso' => 'Peso',
        ];
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

    /**
     * Gets query for [[Preexistencias]] related to this declaration.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreexistencias()
    {
        return $this->hasMany(Preexistencias::class, ['user_id' => 'user_id'])
            ->where(['IS', 'preexistencias.deleted_at', null])
            ->orderBy(['preexistencias.created_at' => SORT_DESC]);
    }

    /**
     * Get all questions with their answers
     *
     * @return array
     */
    public function getQuestionsWithAnswers()
    {
        $questions = [];
        foreach (self::QUESTIONS as $key => $label) {
            $sinoField = $key . '_sino';
            $especificaField = $key . '_especifica';
            $questions[$key] = [
                'label' => $label,
                'sino' => $this->$sinoField,
                'especifica' => $this->$especificaField,
            ];
        }
        return $questions;
    }

    /**
     * Get only the questions answered with "Yes"
     *
     * @return array
     */
    public function getYesAnswers()
    {
        $yesAnswers = [];
        foreach (self::QUESTIONS as $key => $label) {
            $sinoField = $key . '_sino';
            $especificaField = $key . '_especifica';
            // Check for 'Si' or 'Yes' case-insensitively
            $value = strtolower(trim($this->$sinoField));
            if ($value === 'si' || $value === 'yes' || $value === 'sí') {
                $yesAnswers[$key] = [
                    'label' => $label,
                    'especifica' => $this->$especificaField,
                ];
            }
        }
        return $yesAnswers;
    }

    /**
     * Check if any question has a "Yes" answer
     *
     * @return bool
     */
    public function hasYesAnswers()
    {
        return !empty($this->getYesAnswers());
    }

    /**
     * Synchronize pre-existences based on health declaration answers.
     * 
     * IMPORTANT MAPPING:
     * - Preexistencias.nombre = The user's specific description (p1_especifica, etc.)
     * - Preexistencias.descripcion = The condition label + the user's specific description (concatenated)
     * - Preexistencias.fecha_diagnostico = The created_at date from the health declaration
     *
     * Example:
     * - Health Declaration: p1_sino = "Si", p1_especifica = "Hipertensión arterial en tratamiento", created_at = "2024-01-15 10:30:00"
     * - Result: 
     *   - Preexistencias.nombre = "Hipertensión arterial en tratamiento"
     *   - Preexistencias.descripcion = "Enfermedades Cardiovasculares: Hipertensión arterial en tratamiento"
     *   - Preexistencias.fecha_diagnostico = "2024-01-15"
     *
     * @return array Result with 'created', 'deleted', 'errors'
     */
    public function syncPreexistencias()
    {
        $result = [
            'created' => [],
            'deleted' => [],
            'errors' => [],
        ];

        if (!$this->user_id) {
            $result['errors'][] = 'No user_id associated with this health declaration.';
            return $result;
        }

        // Get current "Yes" answers
        $yesAnswers = $this->getYesAnswers();

        // Get existing pre-existencias for this user
        $existingPreexistencias = Preexistencias::find()
            ->where(['user_id' => $this->user_id])
            ->andWhere(['IS', 'preexistencias.deleted_at', null])
            ->all();

        // Map existing pre-existencias by their source question
        $existingMap = [];
        foreach ($existingPreexistencias as $pre) {
            $sourceKey = null;
            foreach (self::QUESTIONS as $key => $label) {
                // Check if the description starts with the label
                if (strpos($pre->descripcion, $label . ':') === 0) {
                    $sourceKey = $key;
                    break;
                }
                // Also check if the nombre matches the label (for backward compatibility)
                if ($pre->nombre === $label) {
                    $sourceKey = $key;
                    break;
                }
            }
            if ($sourceKey) {
                $existingMap[$sourceKey] = $pre;
            }
        }

        // Track which keys we've processed
        $processedKeys = [];

        // Create or update pre-existencias for "Yes" answers
        foreach ($yesAnswers as $key => $answer) {
            $processedKeys[] = $key;
            $label = $answer['label'];
            $especifica = trim($answer['especifica']);

            // Build the values
            if (!empty($especifica)) {
                $nombre = $especifica;
                $descripcion = $label . ': ' . $especifica;
            } else {
                $nombre = 'Diagnosticado con ' . $label;
                $descripcion = $label . ': ' . 'Diagnosticado con ' . $label;
            }

            // Use the created_at date as fecha_diagnostico
            $fechaDiagnostico = $this->created_at ? date('Y-m-d', strtotime($this->created_at)) : null;

            if (isset($existingMap[$key])) {
                // Update existing pre-existence
                $pre = $existingMap[$key];
                $pre->nombre = $nombre;
                $pre->descripcion = $descripcion;
                $pre->fecha_diagnostico = $fechaDiagnostico;
                $pre->estatus = Preexistencias::ESTATUS_ACTIVO;
                $pre->updated_at = date('Y-m-d H:i:s');
                if ($pre->save()) {
                    $result['created'][] = "Actualizado: {$pre->nombre} → {$pre->descripcion} (Fecha: {$fechaDiagnostico})";
                } else {
                    $result['errors'][] = "Error updating pre-existence for {$key}: " . implode(', ', $pre->getErrorSummary(true));
                }
            } else {
                // Create new pre-existence
                $pre = new Preexistencias();
                $pre->user_id = $this->user_id;
                $pre->nombre = $nombre;
                $pre->descripcion = $descripcion;
                $pre->fecha_diagnostico = $fechaDiagnostico;
                $pre->estatus = Preexistencias::ESTATUS_ACTIVO;
                $pre->created_at = date('Y-m-d H:i:s');
                if ($pre->save()) {
                    $result['created'][] = "Creado: {$pre->nombre} → {$pre->descripcion} (Fecha: {$fechaDiagnostico})";
                } else {
                    $result['errors'][] = "Error creating pre-existence for {$key}: " . implode(', ', $pre->getErrorSummary(true));
                }
            }
        }

        // Soft delete pre-existencias that are no longer "Yes"
        foreach ($existingMap as $key => $pre) {
            if (!in_array($key, $processedKeys)) {
                $pre->deleted_at = date('Y-m-d H:i:s');
                if ($pre->save()) {
                    $result['deleted'][] = "Eliminado: {$pre->nombre}";
                } else {
                    $result['errors'][] = "Error deleting pre-existence for {$key}: " . implode(', ', $pre->getErrorSummary(true));
                }
            }
        }

        return $result;
    }

    /**
     * After save, synchronize pre-existences
     *
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Synchronize pre-existences
        $result = $this->syncPreexistencias();

        // Log the synchronization results
        if (!empty($result['created']) || !empty($result['deleted']) || !empty($result['errors'])) {
            $logMessage = "Health Declaration #{$this->id} sync results:\n";
            $logMessage .= "Created: " . implode(', ', $result['created']) . "\n";
            $logMessage .= "Deleted: " . implode(', ', $result['deleted']) . "\n";
            if (!empty($result['errors'])) {
                $logMessage .= "Errors: " . implode(', ', $result['errors']);
            }
            Yii::info($logMessage, 'health-declaration-sync');
        }

        // Store sync result in session for user feedback
        if (!empty($result['created']) || !empty($result['deleted']) || !empty($result['errors'])) {
            $flashMessages = [];
            if (!empty($result['created'])) {
                $flashMessages[] = "✅ Pre-existencias creadas/actualizadas:\n" . implode("\n", $result['created']);
            }
            if (!empty($result['deleted'])) {
                $flashMessages[] = "❌ Pre-existencias eliminadas:\n" . implode("\n", $result['deleted']);
            }
            if (!empty($result['errors'])) {
                $flashMessages[] = "⚠️ Errores:\n" . implode("\n", $result['errors']);
            }

            $flashType = empty($result['errors']) ? 'success' : 'warning';
            Yii::$app->session->setFlash($flashType, nl2br(implode("\n\n", $flashMessages)));
        }
    }

    /**
     * Get the pre-existences created from this health declaration
     *
     * @return array
     */
    public function getGeneratedPreexistencias()
    {
        $yesAnswers = $this->getYesAnswers();
        $preexistencias = [];

        foreach ($yesAnswers as $key => $answer) {
            $especifica = trim($answer['especifica']);
            $label = $answer['label'];
            $fechaDiagnostico = $this->created_at ? date('Y-m-d', strtotime($this->created_at)) : null;

            $nombre = !empty($especifica) ? $especifica : 'Diagnosticado con ' . $label;
            $descripcion = $label . ': ' . $nombre;

            // Check if it exists using direct query to avoid ambiguity
            $exists = (bool) Yii::$app->db->createCommand(
                'SELECT EXISTS(
                    SELECT 1 FROM "preexistencias" 
                    WHERE user_id = :user_id 
                    AND nombre = :nombre 
                    AND deleted_at IS NULL
                )',
                [
                    ':user_id' => $this->user_id,
                    ':nombre' => $nombre
                ]
            )->queryScalar();

            $preexistencias[$key] = [
                'label' => $label,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'fecha_diagnostico' => $fechaDiagnostico,
                'exists' => $exists,
            ];
        }

        return $preexistencias;
    }

    /**
     * Get the status of pre-existence sync
     *
     * @return array
     */
    public function getSyncStatus()
    {
        $yesAnswers = $this->getYesAnswers();

        // Use direct query to avoid column ambiguity
        $existingCount = (int) Yii::$app->db->createCommand(
            'SELECT COUNT(*) FROM "preexistencias" 
             WHERE user_id = :user_id 
             AND deleted_at IS NULL',
            [':user_id' => $this->user_id]
        )->queryScalar();

        return [
            'yes_answers' => count($yesAnswers),
            'existing_pre_existencias' => $existingCount,
            'is_synced' => count($yesAnswers) == $existingCount,
        ];
    }

    /**
     * Override delete to handle cascading soft delete
     *
     * @return bool
     */
    public function delete()
    {
        $this->deleted_at = date('Y-m-d H:i:s');
        return $this->save(false);
    }
}
