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
        'css/site.css',
        'css/sispsa.css',
        'css/main-login.css',
        'css/custom-sidebar.css', // Archivo CSS personalizado para el menú lateral
        'cssMedboard/css/style.css', // Este ya estaba, asegúrate de que la ruta sea correcta
        'cssMedboard/css/animate.min.css',
        'cssMedboard/css/bootstrap.min.css',
        'cssMedboard/css/cropper.min.css',
        'cssMedboard/css/datatables.min.css',
        'cssMedboard/css/driver.min.css',
        'cssMedboard/css/ion.rangeSlider.min.css',
        'cssMedboard/css/jquery-ui.min.css', // ¡OJO! Comprueba si realmente lo necesitas
        'cssMedboard/css/morris.css',
        'cssMedboard/css/slick.css',
        'cssMedboard/css/sweetalert2.min.css',
        'cssMedboard/css/toastr.min.css',
        'vendors/iconic-fonts/font-awesome/css/all.min.css', // Ruta corregida
        //'css/iconos.css.map',
    ];
   public $js = [
        // Core / Librerías base
        'jsMedboard/js/popper.min.js',
        'jsMedboard/js/bootstrap.min.js',
        'jsMedboard/js/moment.js', // Librería de manejo de fechas
        // Plugins y Utilidades
        'jsMedboard/js/perfect-scrollbar.js',
        'jsMedboard/js/sweetalert2.min.js',
        'jsMedboard/js/sweet-alerts.js', // Puede ser un script personalizado basado en sweetalert2
        'jsMedboard/js/toastr.min.js',
        'jsMedboard/js/toast.js', // Parece ser un script personalizado basado en toastr
        'jsMedboard/js/slick.min.js',
        'jsMedboard/js/ion.rangeSlider.min.js',
        'jsMedboard/js/range-slider.js', // Puede ser un script personalizado basado en ion.rangeSlider
        'jsMedboard/js/promise.min.js', // A veces necesario para algunas libs asíncronas
        // Librerías de gráficos (asegúrate del orden si hay dependencias como Raphael/Morris)
        'jsMedboard/js/raphael.min.js', // A menudo requerido por Morris Charts
        // Mapas y Geodatos
        'jsMedboard/js/d3.v3.min.js',
        'jsMedboard/js/topojson.v1.min.js',
        // Tablas de datos
        //'jsMedboard/js/datatables.min.js',
        //'jsMedboard/js/data-tables.js', // Puede depender de datatables.min.js
        // Scripts específicos de la plantilla/módulos
        'jsMedboard/js/animate.js',
        'jsMedboard/js/settings.js',
        //'jsMedboard/js/framework.js', // Puede ser un script global de la plantilla
        //'jsMedboard/js/calendar.js',
        //'jsMedboard/js/client-managemenet.js',
        //'jsMedboard/js/department.js',
        //'jsMedboard/js/doctor-report.js',
        //'jsMedboard/js/index-chart.js', // Puede ser el script principal del dashboard
        //'jsMedboard/js/project-management.js',
        //'jsMedboard/js/schedule.js',
        //'jsMedboard/js/social-media.js',
        //'jsMedboard/js/web-analytics.js',
        //'jsMedboard/js/widgets.js',
        //'jsMedboard/js/datamaps.all.min.js',
        //'jsMedboard/js/vector-maps.js', // Puede depender de datamaps.all.min.js
        //'jsMedboard/js/google-maps.js', // Si usas Google Maps API
        //'jsMedboard/js/morris.min.js',
        //'jsMedboard/js/morris-charts.js', // Puede depender de morris.min.js
        //'jsMedboard/js/Chart.bundle.min.js',
        //'jsMedboard/js/Chart.Financial.js',
        //'jsMedboard/js/charts.js', // Puede ser un script de inicialización para ChajsMedboard
        //'jsMedboard/js/cropper.min.js',
        //'jsMedboard/js/crop.js', // Puede depender de cropper.min.js
        //'jsMedboard/js/driver.min.js',
        //'jsMedboard/js/tour.js', // Puede depender de driver.min.js
        //'jsMedboard/js/coming-soon.js',
        //'jsMedboard/js/jquery.webticker.min.js',
        //'jsMedboard/js/jquery.countdown.min.js',
        //'jsMedboard/js/jquery.steps.min.js',
        //'jsMedboard/js/form-wizard.js', // Puede depender de jquery.steps.min.js
        //'jsMedboard/js/jquery-ui.min.js', // ¡OJO! Asegúrate de necesitarlo, es una librería grande
        //'jsMedboard/js/jquery-3.3.1.min.js', // ¡IMPORTANTE: Si MedBoard usa su propio jQuery, NO DEPENDAS DE Yii\web\JqueryAsset!
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',        
    ];
}
