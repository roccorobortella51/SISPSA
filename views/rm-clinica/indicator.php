<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper; // Importar el UserHelper

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */
/** @var array $estadosList */
/** @var array $municipiosList */
/** @var array $parroquiaList */
/** @var array $ciudadesList */
/** @var array $listaEstatus */

// Asegúrate de que estas variables siempre tengan un valor para evitar errores
$estadosList = $estadosList ?? [];
$municipiosList = $municipiosList ?? [];
$parroquiaList = $parquiaList ?? [];
$ciudadesList = $ciudadesList ?? [];
$listaEstatus = $listaEstatus ?? [];

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION' || $rol == 'Administrador-clinica');

$permisos = false;

if ($rol == 'superadmin') 
{
    $permisos = true;
}

$this->title = 'INDICADORES DE LA CLÍNICA: ' . Html::encode($model->nombre);
if($permisos == true){
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['index']];
}
$this->params['breadcrumbs'][] = Html::encode($model->nombre);

\yii\web\YiiAsset::register($this); // Esto registra los assets por defecto de Yii

// Función auxiliar para formatear fechas, manejando valores nulos
function formatUpdatedAt($value) {
    if (empty($value)) {
        return 'No se ha modificado';
    }
    // Asume que updated_at es un timestamp o fecha válida que Yii puede formatear
    return Yii::$app->formatter->asDatetime($value, 'medium');
}



?>

<div class="main-container"> 
   

    <!-- Encabezado y Botones de Acción Principal -->
    <div class="header-section"> 
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="header-buttons-group"> 
            <?php
            echo Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver',
                    '#',
                    [
                        'class' => 'btn-base btn-gray', /* Usando clases de botón definidas en el fragmento CSS */
                        'onclick' => 'window.history.back(); return false;',
                        'title' => 'Volver a la página anterior',
                    ]
                );          

            ?>
           
            
        </div>
    </div>

    

    <!-- Tarjeta de Información General de la Clínica -->
    <div class="info-card info-card-border-blue"> <!-- Usando las clases 'info-card' y 'info-card-border-blue' definidas en el fragmento CSS -->
        <h3>
            <i class="fas fa-hospital-alt text-blue-600 mr-3"></i> Información General de la Clínica
        </h3>
        <div class="info-grid"> <!-- Usando la clase 'info-grid' definida en el fragmento CSS -->
            <div>
                <h5><strong>Nombre:</strong> <?= Html::encode($model->nombre) ?></h5>
                <h5><strong>RIF:</strong> <?= Html::encode($model->rif) ?></h5>
            </div>
            <div>
                <h5><strong>Teléfono:</strong> <?= Html::encode($model->telefono) ?></h5>
                <h5><strong>Correo Electrónico:</strong> <?= Html::a(Html::encode($model->correo), 'mailto:' . Html::encode($model->correo), ['class' => 'text-blue-500']) ?></h5>
            </div>
        </div>
        <div class="info-grid border-top-section"> <!-- Usando la clase 'border-top-section' definida en el fragmento CSS -->
            <div>
                <h5><strong>Código de Clínica:</strong> <?= Html::encode($model->codigo_clinica) ?></h5>
            </div>
            <div>
                <h5><strong>Estatus:</strong> <span class="status-badge <?= $model->estatus == 'Activo' ? 'active' : 'inactive' ?>"><?= Html::encode($listaEstatus[$model->estatus] ?? $model->estatus) ?></span></h5>
            </div>
        </div>
    </div>
    <!-- Tarjeta de Indicadores de la Clínica -->
    <div class="info-card info-card-border-green">
        <h3>
            <i class="fas fa-chart-line text-green-600 mr-3"></i> Indicadores de la Clínica
        </h3>
        <div class="info-grid">
            <div class="info-grid md-col-2">
                <div>
                    <h5><strong>Total de Afiliados:</strong> <?= Html::encode($totalAfiliados) ?></h5>
                </div>
            </div>
            <div class="info-grid md-col-2">
                <div>
                    <h5><strong>Total de Siniestros:</strong> <?= Html::encode($totalSiniestrosAfiliados) ?></h5>
                </div>
            </div>
            <div class="info-grid md-col-2">
                <div>
                    <h5><strong>Total de Pagos:</strong> <?= Html::encode($totalPagosAfiliados) ?></h5>
                </div>
            </div>
            <div class="info-grid md-col-2">
                <div>
                    <h5><strong>Monto Total de Pagos:</strong> <?= Html::encode($montoTotalPagosAfiliados) ?>Bs</h5>
                </div>
            </div>
            <div class="info-grid md-col-2">
                <div>
                    <h5><strong>Fondo de clínica:</strong> <?= Html::encode($montoTotalPagosAfiliados*0.7) ?>Bs</h5>
                </div>
            </div>
        </div>
    </div>     
</div>
