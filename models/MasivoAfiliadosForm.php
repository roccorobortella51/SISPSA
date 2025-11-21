<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Modelo para el formulario de carga masiva de afiliados corporativos.
 * Contiene el ID del corporativo seleccionado y el archivo CSV subido.
 */
class MasivoAfiliadosForm extends Model
{
    /**
     * @var int ID del corporativo seleccionado en el formulario.
     */
    public $corporativo_id;

    /**
     * @var UploadedFile Atributo para el archivo subido.
     */
    public $masivoFile;

    /**
     * Define las reglas de validación para el formulario.
     */
    public function rules()
    {
        return [
            [['corporativo_id'], 'required', 'message' => 'Debe seleccionar un corporativo destino.'],
            [['corporativo_id'], 'integer'],
            // Regla para el archivo subido
            [['masivoFile'], 'file', 
                'skipOnEmpty' => false, 
                'extensions' => 'csv', 
                'maxSize' => 1024 * 1024 * 5, // 5MB limit
                'tooBig' => 'El archivo no debe exceder los 5MB.',
                'uploadRequired' => 'Debe seleccionar un archivo CSV para cargar.'
            ],
        ];
    }

    /**
     * Define las etiquetas de atributos (labels) para la vista.
     */
    public function attributeLabels()
    {
        return [
            'corporativo_id' => 'Corporativo Destino',
            'masivoFile' => 'Archivo de Afiliados (CSV)',
        ];
    }
}