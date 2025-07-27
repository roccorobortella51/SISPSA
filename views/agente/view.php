<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper; // Importar el UserHelper

/** @var yii\web\View $this */
/** @var app\models\Agente $model */

$this->title = 'DETALLES DE LA AGENCIA: ' . Html::encode($model->nom); // Changed title for better context
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->nom); // Changed breadcrumb to agent's name
\yii\web\YiiAsset::register($this); // Registra los assets por defecto de Yii (AppAsset se encargará del resto)

// Function to format percentages (assuming they are stored as integers representing percentage points)
function formatPercentage($value) {
    // Ensure value is treated as a number before division
    return Yii::$app->formatter->asPercent((float)$value / 100);
}

// Function to format dates
function formatDateTime($value) {
    return $value ? Yii::$app->formatter->asDatetime($value) : 'N/A';
}


$ownerContactInfo = UserHelper::getAgenteOwnerContactInfo($model->id);

?>

<div class="view-main-container"> 
   
    <!-- Encabezado y Botones de Acción -->
    <div class="ms-panel-header"> 
        <h4 class="mb-0"><?= Html::encode($this->title) ?></h4> 
       
        <div class="button-group-spacing">
            <?= Html::a(
                '<i class="fas fa-edit mr-2"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'btn btn-primary'] 
            ) ?>
            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver',
                ['index'],
                [
                    'class' => 'btn btn-secondary', // Clases de Bootstrap, estilizadas por sipsa.css
                    'title' => 'Volver a la lista de agencias',
                ]
            ) ?>
      
        </div>
    </div>

    <!-- Tarjeta de Información General de la Agencia -->
    <div class="ms-panel"> <!-- Usando clase global de sipsa.css -->
        <div class="ms-panel-body"> <!-- Contenedor de cuerpo de panel -->
            <h3 class="section-title">
                <i class="fas fa-building text-blue-600 mr-3"></i> Información General de la Agencia
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <p class="text-gray-700 mb-2"><strong>Nombre del Propietario:</strong> <?= Html::encode($model->propietario->userDatos->nombres ?? 'N/A') . ' ' . Html::encode($model->propietario->userDatos->apellidos ?? '') ?></p>
                    <p class="text-gray-700 mb-2"><strong>RIF:</strong> <?= Html::encode($ownerContactInfo['rif']) ?></p>
                </div>
                <div class="col-md-6">
                    <p class="text-gray-700 mb-2"><strong>Email:</strong> <?= Html::a(Html::encode($ownerContactInfo['email']), 'mailto:' . Html::encode($ownerContactInfo['email']), ['class' => 'text-primary']) ?></p>
                    <p class="text-gray-700 mb-2"><strong>Teléfono:</strong> <?= Html::encode($ownerContactInfo['telefono']) ?></p>
                </div>
            </div>
            <p class="text-gray-700 mt-4 pt-4 border-top"><strong>Dirección:</strong> <?= nl2br(Html::encode($ownerContactInfo['direccion'])) ?></p>
        </div>
    </div>

    <!-- Tarjeta de Porcentajes de Comisión -->
    <div class="ms-panel"> <!-- Usando clase global de sipsa.css -->
        <div class="ms-panel-body"> <!-- Contenedor de cuerpo de panel -->
            <h3 class="section-title">
                <i class="fas fa-percent text-purple-600 mr-3"></i> Porcentajes de Comisión
            </h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Porcentaje Venta</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_venta) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Porcentaje Asesoría</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_asesor) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Porcentaje Cobranza</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_cobranza) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Porcentaje Post Venta</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_post_venta) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Porcentaje Agente</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_agente) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Porcentaje Máximo</h6>
                        <p class="h4 text-info"><?= formatPercentage($model->por_max) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Fechas de Gestión -->
    <div class="ms-panel"> <!-- Usando clase global de sipsa.css -->
        <div class="ms-panel-body"> <!-- Contenedor de cuerpo de panel -->
            <h3 class="section-title">
                <i class="fas fa-calendar-alt text-gray-600 mr-3"></i> Fechas de Gestión
            </h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Fecha de Creación</h6>
                        <p class="h5 text-dark"><?= formatDateTime($model->created_at) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Última Actualización</h6>
                        <p class="h5 text-dark"><?= formatDateTime($model->updated_at) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
