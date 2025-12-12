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
     * @var UploadedFile El archivo CSV a subir.
     */
    public $masivoFile;

    /**
     * Define las reglas de validación para los atributos del formulario.
     */
    public function rules()
{
    return [
        // El corporativo_id es requerido y debe ser un entero
        [['corporativo_id'], 'required', 'message' => 'Debe seleccionar un corporativo.'],
        [['corporativo_id'], 'integer'],

        // REGLA CRÍTICA CORREGIDA: Hacemos la validación de archivo más permisiva
        [['masivoFile'], 'file', 
            'skipOnEmpty' => false, 
            // Acepta .csv en mayúsculas y minúsculas, e incluso .txt (ya que el CSV es un archivo de texto)
            'extensions' => ['csv', 'CSV', 'txt'], 
            
            // Acepta los MIME types comunes que usan los navegadores para los CSV
            'mimeTypes' => [
                'text/csv', 
                'text/plain', 
                'application/vnd.ms-excel', // A veces se reporta así si viene de Excel
            ],
            
            'maxSize' => 1024 * 1024 * 5, // 5MB
            'tooBig' => 'El archivo es demasiado grande. El máximo permitido es 5MB.',
            'wrongExtension' => 'Sólo se aceptan archivos con las siguientes extensiones: {extensions}',
            
            // Desactivamos la verificación estricta del MIME type si la extensión es correcta.
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
            'masivoFile' => 'Archivo CSV de Afiliados',
        ];
    }
}