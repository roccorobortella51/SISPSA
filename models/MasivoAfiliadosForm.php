<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * MasivoAfiliadosForm es el modelo para el formulario de carga de afiliados masivos.
 *
 * The contract vigencia (fecha_ini / fecha_ven) is captured on this form.
 * Because a corporation can hold one contract per year, the user picks the
 * window for the batch they're about to upload. These dates flow into
 * `persistCorporateContract()`, which stores them on the corporate contract
 * rows (one per plan) and mirrors them onto each affiliate's individual
 * contract.
 */
class MasivoAfiliadosForm extends Model
{
    /**
     * @var int ID del corporativo seleccionado en el formulario.
     */
    public $corporativo_id;

    /**
     * @var int ID del plan seleccionado en el formulario.
     */
    public $plan_id;

    /**
     * @var int|null ID del asesor seleccionado en el formulario.
     */
    public $asesor_id;

    /**
     * @var UploadedFile El archivo CSV a subir.
     */
    public $masivoFile;

    /**
     * @var string Vigencia del contrato: fecha de inicio (YYYY-MM-DD).
     */
    public $fecha_ini;

    /**
     * @var string Vigencia del contrato: fecha de vencimiento (YYYY-MM-DD).
     *             Auto-computed as fecha_ini + 1 year − 1 day.
     */
    public $fecha_ven;

    /**
     * Define las reglas de validación para los atributos del formulario.
     */
    public function rules()
    {
        return [
            // The corporate is required.
            [['corporativo_id'], 'required', 'message' => 'Debe seleccionar un corporativo.'],
            [['corporativo_id'], 'integer'],

            // The plan is required.
            [['plan_id'], 'required', 'message' => 'Debe seleccionar un plan.'],
            [['plan_id'], 'integer'],

            // The advisor is optional.
            [['asesor_id'], 'integer'],

            // Vigencia: both dates are required, must be valid, and
            // fecha_ven must be strictly after fecha_ini.
            [['fecha_ini', 'fecha_ven'], 'required', 'message' => 'La vigencia del contrato es obligatoria.'],
            [['fecha_ini', 'fecha_ven'], 'date', 'format' => 'php:Y-m-d'],
            [
                'fecha_ven',
                'compare',
                'compareAttribute' => 'fecha_ini',
                'operator'         => '>',
                'message'          => 'La fecha de vencimiento debe ser posterior a la fecha de inicio.',
            ],

            // CSV upload.
            [
                ['masivoFile'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => ['csv', 'CSV', 'txt'],
                'mimeTypes' => [
                    'text/csv',
                    'text/plain',
                    'application/vnd.ms-excel',
                ],
                'maxSize' => 1024 * 1024 * 5, // 5MB
                'tooBig' => 'El archivo es demasiado grande. El máximo permitido es 5MB.',
                'wrongExtension' => 'Sólo se aceptan archivos con las siguientes extensiones: {extensions}',
                'checkExtensionByMimeType' => false,
            ],
        ];
    }

    /**
     * Define las etiquetas de los atributos para la vista.
     */
    public function attributeLabels()
    {
        return [
            'corporativo_id' => 'Corporativo Destino',
            'plan_id'        => 'Plan de Afiliación',
            'asesor_id'      => 'Asesor / Intermediario',
            'masivoFile'     => 'Archivo CSV de Afiliados',
            'fecha_ini'      => 'Vigencia Desde',
            'fecha_ven'      => 'Vigencia Hasta',
        ];
    }
}
