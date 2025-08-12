<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Agente; // Asegúrate de que Agente esté importado
use app\models\User; // Asegúrate de que User esté importado
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */

$this->title = 'DETALLES DE ASESOR VENDEDOR: ';
$this->params['breadcrumbs'][] = ['label' => 'AGENTES DE FUERZA', 'url' => ['index-by-agente']]; // Añadido URL para breadcrumb
$this->params['breadcrumbs'][] = 'DETALLES';

\yii\web\YiiAsset::register($this);

// Preparar el nombre de la agencia
$agenciaNombre = 'N/A';
if ($model->agente) {
    $agenciaNombre = $model->agente->nom;
}

// Preparar el nombre de usuario (asumiendo que userDatos tiene nombres y apellidos)
$nombreCompletoUsuario = 'N/A';
$telefonoUsuario = 'N/A';
$emailUsuario = 'N/A';

if ($model->user && $model->user->userDatos) { // Acceder a userDatos a través de la relación user
    $nombreCompletoUsuario = $model->user->userDatos->nombres . ' ' . $model->user->userDatos->apellidos;
    $telefonoUsuario = $model->user->userDatos->telefono ?? 'N/A';
    $emailUsuario = $model->user->userDatos->email ?? 'N/A';
} elseif ($model->user) {
    $nombreCompletoUsuario = $model->user->username; // Fallback al username si no hay userDatos
}

// Función auxiliar para mostrar Sí/No con íconos y clases de CSS
function formatBooleanIcon($value) {
    if ($value) {
        return '<span class="text-green-600 mr-1"><i class="fas fa-check-circle"></i></span> Sí';
    } else {
        return '<span class="text-red-600 mr-1"><i class="fas fa-times-circle"></i></span> No';
    }
}


$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION'); 

?>

<div class="view-main-container">
   

    <!-- Encabezado y Botones de Acción -->
    <div class="ms-panel-header">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="button-group-spacing">
            <?php if($permisos){ ?>
                <?= Html::a(
                    '<i class="fas fa-edit"></i> Actualizar',
                    ['update', 'id' => $model->id],
                    ['class' => 'btn btn-primary']
                ) ?>
            <?php } ?>
            <?= Html::a(
                '<i class="fas fa-arrow-left"></i> Volver',
                ['agente-fuerza/index-by-agente', 'agente_id' => $model->agente_id],
                [
                    'class' => 'btn btn-secondary',
                    'title' => 'Volver a la lista de agentes de fuerza',
                ]
            ) ?>
           
        </div>
    </div>

    <!-- Tarjeta de Información General del Asesor -->
    <div class="ms-panel border-blue">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-user-tie text-blue-600"></i> Información General del Asesor
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Usuario:</strong> <?= Html::encode($nombreCompletoUsuario) ?></p>
                    <p><strong>Teléfono:</strong> <?= Html::encode($telefonoUsuario) ?></p>
                </div>
                <div>
                    <p><strong>Agencia Asociada:</strong> <?= Html::encode($agenciaNombre) ?></p>
                    <p><strong>Correo Electrónico:</strong> <?= Html::a(Html::encode($emailUsuario), 'mailto:' . Html::encode($emailUsuario), ['class' => 'text-primary']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Porcentajes de Comisión -->
    <div class="ms-panel border-purple">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-percent text-purple-600"></i> Porcentajes de Comisión
            </h3>
            <div class="info-grid-percentages">
                <div class="info-card-body col-md-2" style="margin-right:10px; margin-bottom:10px;">
                    <h6>Venta</h6>
                    <p class="h4"><?= Yii::$app->formatter->asPercent($model->por_venta / 100) ?></p>
                </div>
                <div class="info-card-body col-md-2" style="margin-right:10px; margin-bottom:10px;">
                    <h6>Asesoría</h6>
                    <p class="h4"><?= Yii::$app->formatter->asPercent($model->por_asesor / 100) ?></p>
                </div>
                <div class="info-card-body col-md-2" style="margin-right:10px; margin-bottom:10px;">
                    <h6>Cobranza</h6>
                    <p class="h4"><?= Yii::$app->formatter->asPercent($model->por_cobranza / 100) ?></p>
                </div>
                <div class="info-card-body col-md-2" style="margin-right:10px; margin-bottom:10px;">
                    <h6>Post Venta</h6>
                    <p class="h4"><?= Yii::$app->formatter->asPercent($model->por_post_venta / 100) ?></p>
                </div>
                <div class="info-card-body col-md-2" style="margin-right:10px; margin-bottom:10px;">
                    <h6>Registro</h6>
                    <p class="h4"><?= Yii::$app->formatter->asPercent($model->por_registrar / 100) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Permisos de Acceso -->
    <div class="ms-panel border-green">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-lock text-green-600"></i> Permisos de Acceso
            </h3>
            <div class="info-grid">
                <div class="info-card-body">
                    <h6 class="section-subtitle">Venta y Asesoría</h6>
                    <ul class="divide-y">
                        <li class="py-3 flex justify-between items-center text-gray-700">
                            Puede Vender
                            <span><?= formatBooleanIcon($model->puede_vender) ?></span>
                        </li>
                        <li class="py-3 flex justify-between items-center text-gray-700">
                            Puede Asesorar
                            <span><?= formatBooleanIcon($model->puede_asesorar) ?></span>
                        </li>
                    </ul>
                </div>
                <div class="info-card-body">
                    <h6 class="section-subtitle">Gestión y Cobranza</h6>
                    <ul class="divide-y">
                        <li class="py-3 flex justify-between items-center text-gray-700">
                            Puede Cobrar
                            <span><?= formatBooleanIcon($model->puede_cobrar) ?></span>
                        </li>
                        <li class="py-3 flex justify-between items-center text-gray-700">
                            Puede Post Venta
                            <span><?= formatBooleanIcon($model->puede_post_venta) ?></span>
                        </li>
                        <li class="py-3 flex justify-between items-center text-gray-700">
                            Puede Registrar
                            <span><?= formatBooleanIcon($model->puede_registrar) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Fechas de Gestión -->
    <div class="ms-panel border-gray">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-calendar-alt text-gray-600"></i> Fechas de Gestión
            </h3>
            <div class="info-grid">
                <div class="info-card-body">
                    <h6>Fecha de Creación</h6>
                    <p class="h5"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></p>
                </div>
                <div class="info-card-body">
                    <h6>Última Actualización</h6>
                    <p class="h5"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></p>
                </div>
                <?php /*
                <div class="info-card-body">
                    <h6>Fecha de Eliminación</h6>
                    <p class="h5"><?= $model->deleted_at ? Yii::$app->formatter->asDatetime($model->deleted_at) : 'N/A' ?></p>
                </div>
                */ ?>
            </div>
        </div>
    </div>

</div>
