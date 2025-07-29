<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var string $estado Nombre del estado resuelto para mostrar */
/** @var string $municipio Nombre del municipio resuelto para mostrar */
/** @var string $parroquia Nombre de la parroquia resuelta para mostrar */
/** @var string $ciudad Nombre de la ciudad resuelta para mostrar */

$this->title = 'PERFIL DEL AFILIADO: ' . Html::encode($model->nombres . ' ' . $model->apellidos);
$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->nombres . ' ' . $model->apellidos);
\yii\web\YiiAsset::register($this); // Registra los assets por defecto de Yii (AppAsset se encargará del resto)

// Función para formatear fechas y horas (si no está disponible globalmente)
function formatDateTime($value) {
    return $value ? Yii::$app->formatter->asDatetime($value) : 'N/A';
}

$rol = UserHelper::getMyRol();

$permisos = false;

if ($rol == 'superadmin' || $rol == 'Agente' || $rol == 'Asesor') 
{
    $permisos = true;
}

?>

<!-- Breadcrumbs -->
<nav class="mb-6" aria-label="breadcrumb">
    <ol class="breadcrumb">
        <?php foreach ($this->params['breadcrumbs'] as $i => $breadcrumb): ?>
            <li class="breadcrumb-item <?= $i === count($this->params['breadcrumbs']) - 1 ? 'active' : '' ?>">
                <?php if (is_array($breadcrumb)): ?>
                    <?= Html::a(Html::encode($breadcrumb['label']), $breadcrumb['url']) ?>
                <?php else: ?>
                    <?= Html::encode($breadcrumb) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>

<!-- Encabezado y Botones de Acción -->
<div class="ms-panel-header"> 
    <h1 class="mb-0"><?= Html::encode($this->title) ?></h1> 
    <div class="button-group-spacing">
        <?= Html::a(
            '<i class="fas fa-file-pdf mr-2"></i> Contrato',
            ['user-datos/generar-contratov', 'id' => $model->id],
            [
                'class' => 'btn btn-danger', // Usando btn-danger para el contrato PDF
                'target' => '_blank',
                'data-pjax' => '0'
            ]
        ) ?>

        
        <?php if ($permisos): ?>
            <?= Html::a(
                '<i class="fas fa-edit mr-2"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'btn btn-primary']
            ) ?>
        <?php endif; ?>
        
        <?= Html::a(
            '<i class="fas fa-undo mr-2"></i> Volver',
            '#',
            [
                'class' => 'btn btn-secondary',
                'onclick' => 'window.history.back(); return false;',
                'title' => 'Volver a la página anterior',
            ]
        ) ?>
    </div>
</div>

<!-- Sección de Foto de Perfil y Datos Personales -->
<div class="ms-panel border-blue text-center">
    <div class="ms-panel-body">
        <!-- Contenedor de imágenes de perfil e identificación -->
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-4 mb-4">
            <div class="profile-img-container text-center">
                <h6>Foto de Perfil</h6>
                <?php if ($model->selfie): ?>
                    <?= Html::img(Yii::$app->request->baseUrl . $model->selfie, [
                        'alt' => 'Foto de Perfil',
                        'class' => 'profile-img'
                    ]) ?>
                <?php else: ?>
                    <i class="fas fa-user-circle text-gray-400" style="font-size: 80px;"></i>
                    <p class="text-muted mt-2">No hay selfie</p>
                <?php endif; ?>
            </div>
            <div class="profile-img-container text-center">
                <h6>Imagen de Identificación</h6>
                <?php if ($model->imagen_identificacion): ?>
                    <?= Html::img(Yii::$app->request->baseUrl . $model->imagen_identificacion, [
                        'alt' => 'Imagen de Identificación',
                        'class' => 'profile-img'
                    ]) ?>
                <?php else: ?>
                    <i class="fas fa-id-card text-gray-400" style="font-size: 80px;"></i>
                    <p class="text-muted mt-2">No hay imagen de identificación</p>
                <?php endif; ?>
            </div>
        </div>
        

        <h3 class="section-title justify-content-center">
            <i class="fas fa-address-card text-blue-600 mr-3"></i> Datos Personales
        </h3>
        <div class="info-grid text-left">
            <div>
                <p class="text-gray-700 mb-2"><strong>Nombres:</strong> <?= Html::encode($model->nombres ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>Cédula de Identidad:</strong> <?= Html::encode($model->tipo_cedula . '-' . $model->cedula ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>Sexo:</strong> <?= Html::encode($model->sexo ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>Correo Electrónico:</strong> <?= !empty($model->email) ? Html::a(Html::encode($model->email), 'mailto:' . Html::encode($model->email), ['class' => 'text-primary']) : 'N/A' ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Apellidos:</strong> <?= Html::encode($model->apellidos ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>Fecha de Nacimiento:</strong> <span class="font-medium"><?= Html::encode(Yii::$app->formatter->asDate($model->fechanac, 'd-m-Y') ?? 'N/A') ?></span></p>
                <p class="text-gray-700 mb-2"><strong>Teléfono:</strong> <?= Html::encode($model->telefono ?? 'N/A') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Tarjeta de Ubicación -->
<div class="ms-panel border-indigo">
    <div class="ms-panel-body">
        <h3 class="section-title">
            <i class="fas fa-map-marker-alt text-indigo-600 mr-3"></i> Ubicación
        </h3>
        <div class="info-grid">
            <div>
                <p class="text-gray-700 mb-2"><strong>Estado:</strong> <?= Html::encode($estado ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>Ciudad:</strong> <?= Html::encode($ciudad ?? 'N/A') ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Municipio:</strong> <?= Html::encode($municipio ?? 'N/A') ?></p>
                <p class="text-gray-700 mb-2"><strong>Parroquia:</strong> <?= Html::encode($parroquia ?? 'N/A') ?></p>
            </div>
        </div>
        <p class="text-gray-700 mt-4 pt-4 border-top"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion ?? 'N/A')) ?></p>
    </div>
</div>

<!-- Tarjeta de Información Adicional -->
<div class="ms-panel border-gray">
    <div class="ms-panel-body">
        <h3 class="section-title">
            <i class="fas fa-info-circle text-gray-600 mr-3"></i> Información Adicional
        </h3>
        <div class="info-grid">
            <div>
                <p class="text-gray-700 mb-2"><strong>Clínica:</strong> <?= Html::encode($model->clinica ? $model->clinica->nombre : 'No asignada') ?></p>
                <p class="text-gray-700 mb-2"><strong>Asesor:</strong> <?= Html::encode($model->asesor ? $model->asesor->nom : 'Sin asignar') ?></p>
                <p class="text-gray-700 mb-2"><strong>Tipo de Sangre:</strong> <?= Html::encode($model->tipo_sangre ?? 'N/A') ?></p>
            </div>
            <div>
                <p class="text-gray-700 mb-2"><strong>Plan:</strong> <?= Html::encode($model->plan ? $model->plan->nombre : 'No asignado') ?></p>
                <?php
                    $estatusText = $model->estatus ?? 'N/A';
                    $estatusClass = 'inactive';
                    if ($estatusText === 'Activo' || $estatusText === 'Registrado') { // Asume 'Registrado' también es un estado "activo" para el badge
                        $estatusClass = 'active';
                    }
                ?>
                <p class="text-gray-700 mb-2"><strong>Estatus:</strong> <span class="status-badge <?= $estatusClass ?>"><?= Html::encode($estatusText) ?></span></p>
            </div>
        </div>
    </div>
</div>
