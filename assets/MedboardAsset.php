<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle para la plantilla MedBoard.
 */
class MedboardAsset extends AssetBundle
{
    public $basePath = '@webroot/medboard'; // Ruta física a tu carpeta de assets de MedBoard
    public $baseUrl = '@web/medboard';     // URL pública a tu carpeta de assets de MedBoard

    public $css = [
        // CSS principales de MedBoard
        'assets/css/style.css', // O el nombre del archivo CSS principal de tu plantilla MedBoard
        // Agrega aquí otros archivos CSS que sean esenciales para la plantilla,
        // por ejemplo, si hay un 'assets/css/bootstrap.min.css' o 'assets/css/responsive.css'
        // Revisa el <head> del index.html de MedBoard para ver qué CSS carga
    ];

    public $js = [
        // JS principales de MedBoard
        'assets/js/jquery-3.3.1.min.js', // MedBoard usa jQuery, asegúrate de que sea la versión correcta
        'assets/js/bootstrap.min.js',    // JS de Bootstrap (si la plantilla lo incluye)
        //'assets/js/custom.js',           // JS personalizado de MedBoard
        // Agrega aquí otros archivos JS que sean esenciales,
        // Revisa el final del <body> del index.html de MedBoard para ver qué JS carga
    ];

    public $depends = [
        // Elimina las dependencias de Bootstrap de Yii2 si MedBoard ya las incluye
        // o si causan conflictos. MedBoard probablemente trae su propio Bootstrap.
        // 'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset', // Si MedBoard tiene su propio jQuery, NO incluyas este
        // 'yii\bootstrap4\BootstrapAsset',
        // 'yii\bootstrap4\BootstrapPluginAsset',
    ];

    // Posiciona los scripts al final del body para mejorar la velocidad de carga
    public $jsOptions = ['position' => \yii\web\View::POS_END];
}