<?php
// app/models/Preexistencias.php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "preexistencias".
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $sis_siniestro_id
 * @property string $nombre
 * @property string|null $descripcion
 * @property string|null $fecha_diagnostico
 * @property string|null $medico_tratante
 * @property string|null $medicamentos
 * @property string|null $observaciones
 * @property string $estatus
 * @property string $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 * @property UserDatos $afiliado
 * @property SisSiniestro $siniestro
 */
class Preexistencias extends ActiveRecord
{
    const ESTATUS_ACTIVO = 'Activo';
    const ESTATUS_INACTIVO = 'Inactivo';
    const ESTATUS_EN_REMISION = 'En Remisión';
    const ESTATUS_TRATAMIENTO = 'En Tratamiento';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'preexistencias';
    }

    /**
     * Sobrescribir el método find() para evitar ambigüedad en deleted_at
     */
    public static function find()
    {
        return parent::find()
            ->alias('preexistencias')
            ->andWhere(['IS', 'preexistencias.deleted_at', null]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'nombre'], 'required'],
            [['user_id', 'sis_siniestro_id'], 'integer'],
            [['descripcion', 'medicamentos', 'observaciones'], 'string'],
            [['fecha_diagnostico', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['nombre', 'medico_tratante'], 'string', 'max' => 255],
            [['estatus'], 'string', 'max' => 50],
            [['estatus'], 'default', 'value' => self::ESTATUS_ACTIVO],
            [['estatus'], 'in', 'range' => [
                self::ESTATUS_ACTIVO,
                self::ESTATUS_INACTIVO,
                self::ESTATUS_EN_REMISION,
                self::ESTATUS_TRATAMIENTO
            ]],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserDatos::class, 'targetAttribute' => ['user_id' => 'id']],
            [['sis_siniestro_id'], 'exist', 'skipOnError' => true, 'targetClass' => SisSiniestro::class, 'targetAttribute' => ['sis_siniestro_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Afiliado',
            'sis_siniestro_id' => 'Atención/Cita',
            'nombre' => 'Nombre de la Pre-existencia',
            'descripcion' => 'Descripción',
            'fecha_diagnostico' => 'Fecha de Diagnóstico',
            'medico_tratante' => 'Médico Tratante',
            'medicamentos' => 'Medicamentos',
            'observaciones' => 'Observaciones',
            'estatus' => 'Estatus',
            'created_at' => 'Fecha de Registro',
            'updated_at' => 'Actualizado El',
            'deleted_at' => 'Eliminado El',
        ];
    }

    /**
     * Gets query for [[Afiliado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAfiliado()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Siniestro]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiniestro()
    {
        return $this->hasOne(SisSiniestro::class, ['id' => 'sis_siniestro_id']);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge()
    {
        $badges = [
            self::ESTATUS_ACTIVO => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Activo</span>',
            self::ESTATUS_INACTIVO => '<span class="badge badge-secondary"><i class="fas fa-ban"></i> Inactivo</span>',
            self::ESTATUS_EN_REMISION => '<span class="badge badge-warning"><i class="fas fa-clock"></i> En Remisión</span>',
            self::ESTATUS_TRATAMIENTO => '<span class="badge badge-info"><i class="fas fa-heartbeat"></i> En Tratamiento</span>',
        ];

        return $badges[$this->estatus] ?? $badges[self::ESTATUS_ACTIVO];
    }

    /**
     * Get status options for dropdown
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            self::ESTATUS_ACTIVO => 'Activo',
            self::ESTATUS_INACTIVO => 'Inactivo',
            self::ESTATUS_EN_REMISION => 'En Remisión',
            self::ESTATUS_TRATAMIENTO => 'En Tratamiento',
        ];
    }

    /**
     * Get the health declaration that created this pre-existence
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getHealthDeclaration()
    {
        return $this->hasOne(DeclaracionDeSalud::class, ['user_id' => 'user_id']);
    }

    /**
     * Check if this pre-existence was auto-generated from a health declaration
     * 
     * @return bool
     */
    public function isFromHealthDeclaration()
    {
        foreach (DeclaracionDeSalud::QUESTIONS as $key => $label) {
            if (strpos($this->nombre, $label) === 0 || $this->nombre === $label) {
                return true;
            }
        }
        return false;
    }
}
