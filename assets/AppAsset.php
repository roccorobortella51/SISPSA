<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/custom-gridview.css',
        'css/site.css',
        'css/sispsa.css',
        'css/main-login.css',
        'css/custom-sidebar.css',
        'cssMedboard/css/style.css',
        'cssMedboard/css/animate.min.css',
        // ¡IMPORTANTE! Comenta o elimina esta línea para evitar la doble carga de Bootstrap CSS
        // 'cssMedboard/css/bootstrap.min.css', 
        'cssMedboard/css/cropper.min.css',
        'cssMedboard/css/datatables.min.css',
        'cssMedboard/css/driver.min.css',
        'cssMedboard/css/ion.rangeSlider.min.css',
        'cssMedboard/css/jquery-ui.min.css',
        'cssMedboard/css/morris.css',
        'cssMedboard/css/slick.css',
        'cssMedboard/css/sweetalert2.min.css',
        'cssMedboard/css/toastr.min.css',
        'vendors/iconic-fonts/font-awesome/css/all.min.css',
        'css/iconos.css.map',
        //'css/affiliation-pdf.css'
    ];
   public $js = [
        // ¡IMPORTANTE! Comenta o elimina estas líneas para evitar la doble carga de Bootstrap JS y Popper.js
        // 'jsMedboard/js/popper.min.js',
        // 'jsMedboard/js/bootstrap.min.js',
        'jsMedboard/js/moment.js',
        'jsMedboard/js/perfect-scrollbar.js',
        'jsMedboard/js/sweetalert2.min.js',
        //'jsMedboard/js/sweet-alerts.js',
        'jsMedboard/js/toastr.min.js',
        'jsMedboard/js/toast.js',
        'jsMedboard/js/slick.min.js',
        'jsMedboard/js/ion.rangeSlider.min.js',
        'jsMedboard/js/range-slider.js',
        'jsMedboard/js/promise.min.js',
        'jsMedboard/js/raphael.min.js',
        'jsMedboard/js/d3.v3.min.js',
        'jsMedboard/js/topojson.v1.min.js',
        'jsMedboard/js/animate.js',
        'jsMedboard/js/settings.js',
        'sispsa.js'
        // Asegúrate de que esta línea esté COMENTADA si no la estás usando activamente para evitar conflictos con el jQuery de YiiAsset
        // 'jsMedboard/js/jquery-3.3.1.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset', // Esto ya carga jQuery de Yii
        // ¡IMPORTANTE! Descomenta estas dos líneas para que AppAsset dependa de los Asset Bundles de Bootstrap 4 de Yii
        'yii\bootstrap4\BootstrapAsset',        // Para el CSS de Bootstrap 4
        'yii\bootstrap4\BootstrapPluginAsset',  // Para el JavaScript de Bootstrap 4 (incluye Popper.js)
    ];
}