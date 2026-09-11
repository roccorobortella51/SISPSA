<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * MasivoAfiliadosForm es el modelo para el formulario de carga de afiliados masivos.
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
     * @var int ID del asesor seleccionado en el formulario.
     */
    public $asesor_id;

    /**
     * @var UploadedFile El archivo CSV a subir.
     */
    public $masivoFile;

    public $fecha_ini;
    public $fecha_ven;

    /**
     * Define las reglas de validación para los atributos del formulario.
     */
    public function rules()
    {
        return [
            // El corporativo_id es requerido y debe ser un entero
            [['corporativo_id'], 'required', 'message' => 'Debe seleccionar un corporativo.'],
            [['corporativo_id'], 'integer'],

            // El plan_id es requerido y debe ser un entero
            [['plan_id'], 'required', 'message' => 'Debe seleccionar un plan.'],
            [['plan_id'], 'integer'],

            // El asesor_id es opcional
            [['asesor_id'], 'integer'],

            // REGLA CRÍTICA CORREGIDA: Hacemos la validación de archivo más permisiva
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

            // Nuevas reglas para fechas
            [['fecha_ini', 'fecha_ven'], 'required', 'message' => 'Este campo es obligatorio.'],
            [['fecha_ini', 'fecha_ven'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * Define las etiquetas de los atributos para la vista.
     */
    public function attributeLabels()
    {
        return [
            'corporativo_id' => 'Corporativo Destino',
            'plan_id' => 'Plan de Afiliación',
            'asesor_id' => 'Asesor / Intermediario',
            'masivoFile' => 'Archivo CSV de Afiliados',
        ];
    }
}
