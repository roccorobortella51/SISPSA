// File: app/assets/AgenteFormAsset.php

namespace app\assets;

use yii\web\AssetBundle;

class AgenteFormAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    // You can add your custom JS files here if you move the script out of the view
    public $js = [
        // Example: 'js/agente-search.js', 
    ];

    // CRITICAL: Declare a dependency on the core jQuery Asset
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset', // Forces jQuery to load first
        'yii\bootstrap4\BootstrapPluginAsset', // Required for Bootstrap JavaScript (like tooltips and buttons)
    ];
}