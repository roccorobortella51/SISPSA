<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper; // Importar el UserHelper
use app\models\RmClinica; // Importar el modelo de la clínica
use app\models\Baremo; // Importar el modelo de Baremo para los servicios

/** @var yii\web\View $this */
/** @var app\models\Planes $model */
/** @var app\models\PlanesItemsCobertura[] $itemsCobertura */
/** @var app\models\Baremo[] $baremosFaltantes */
/** @var int|null $clinica_id // Se asume que el ID de la clínica se pasa a esta vista, puede ser nulo */


if (!isset($clinica_id)) {
    $clinica_id = Yii::$app->request->get('clinica_id');
}


if (empty($clinica_id) && !empty($model->clinica_id)) {
    $clinica_id = $model->clinica_id;
}

$clinica = null;
if (!empty($clinica_id)) {
    $clinica = RmClinica::findOne((int)$clinica_id);
    if (!$clinica) {
        $clinica = (object)['id' => (int)$clinica_id, 'nombre' => 'Clínica Desconocida'];
    }
} else {
    $clinica = (object)['id' => null, 'nombre' => 'Clínica Desconocida'];
}

$this->title = 'DETALLES DEL PLÁN: ' . Html::encode($model->nombre);

// Lógica de permisos
$rol = UserHelper::getMyRol();
$canManage = ($rol == 'superadmin');

if($canManage == true){
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];
}

if ($clinica->id !== null) {
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = ['label' => 'PLANES', 'url' => ['index', 'clinica_id' => $clinica->id]];
} else {
    $this->params['breadcrumbs'][] = ['label' => 'PLANES', 'url' => ['index']];
}
$this->params['breadcrumbs'][] = Html::encode($model->nombre);

\yii\web\YiiAsset::register($this);

if (!function_exists('formatDateTime')) { 
    function formatDateTime($value) {
        return $value ? Yii::$app->formatter->asDatetime($value, 'medium') : 'N/A';
    }
}


?>

<div class="main-container"> <!-- Contenedor principal de la vista -->
   
    <!-- Encabezado y Botones de Acción Principal -->
    <div class="header-section"> 
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="header-buttons-group">
            <?php if ($canManage) : ?>
                <?= Html::a(
                    '<i class="fas fa-edit mr-2"></i> Actualizar',
                    ['update', 'id' => $model->id, 'clinica_id' => $clinica->id], 
                    ['class' => 'btn-base btn-blue'] 
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-trash-alt mr-2"></i> Eliminar',
                    ['delete', 'id' => $model->id, 'clinica_id' => $clinica->id], 
                    [
                        'class' => 'btn-base btn-red', 
                        'data' => [
                            'confirm' => '¿Estás seguro de que quieres eliminar este plan?',
                            'method' => 'post',
                        ],
                    ]
                ) ?>
            <?php endif; ?>
            <?php if ($clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver', 
                    ['index', 'clinica_id' => $clinica->id], 
                    [
                        'class' => 'btn-base btn-gray', 
                        'title' => 'Volver a la lista de planes de esta clínica',
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3"> 
        <div class="col-lg-6">
            <div class="ms-panel border-blue"> 
                <div class="ms-panel-body"> 
                    <h3 class="section-title">
                        <i class="fas fa-info-circle text-blue-600 mr-3"></i> Información General del Plan
                    </h3>
                    <div class="info-grid"> 
                        <div>
                            <h5><strong>Nombre del Plan:</strong> <?= Html::encode($model->nombre) ?></h5>
                            <h5><strong>Precio:</strong> <?= Yii::$app->formatter->asCurrency($model->precio, 'USD') ?></h5>
                            <h5><strong>Comisión:</strong> <?= $model->comision ? Html::encode(Yii::$app->formatter->asPercent((float)$model->comision / 100)) : 'N/A' ?></h5>
                        </div>
                        <div>
                            <h5><strong>Estatus:</strong> 
                                <span class="status-badge <?= $model->estatus == 'Activo' || $model->estatus == "Activo" ? 'active' : 'inactive' ?>">
                                    <?= strtoupper($model->estatus == "Activo" ? 'Activo' : 'Inactivo') ?>
                                </span>
                            </h5>
                            <h5><strong>Rango de Edad:</strong> 
                                <?= Html::encode($model->edad_minima) ?> - 
                                <?= Html::encode($model->edad_limite ? $model->edad_limite . ' años' : 'Sin límite') ?>
                            </h5>
                            <h5><strong>Clínica:</strong> <?= Html::encode($model->clinica ? $model->clinica->nombre : 'N/A') ?></h5>
                        </div>
                    </div>
                    
                    <div class="border-top-section mt-4 pt-4">
                        <h3 class="section-title">
                            <i class="fas fa-align-left text-gray-600 mr-3"></i> Descripción del Plan
                        </h3>
                        <h5><?= $model->descripcion ? nl2br(Html::encode($model->descripcion)) : 'Sin descripción' ?></h5>
                    </div>
                </div>
                <div class="ms-panel-body border-top-section text-muted small"> <!-- Pie de panel para fechas -->
                    <div class="row">
                        <div class="col-md-6">
                            <i class="far fa-calendar-plus mr-2"></i> Creado: <?= formatDateTime($model->created_at) ?>
                        </div>
                        <div class="col-md-6 text-end">
                            <i class="far fa-calendar-check mr-2"></i> Actualizado: <?= formatDateTime($model->updated_at) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha - Coberturas -->
        <div class="col-lg-6">
            <!-- Tarjeta de coberturas incluidas -->
            <div class="ms-panel border-indigo mb-4"> 
                <div class="ms-panel-body">
                    <h3 class="section-title">
                        <i class="fas fa-shield-alt text-indigo-600 mr-3"></i> Coberturas Incluidas
                        <span class="status-badge active float-end"> 
                            <?= count($itemsCobertura) ?> servicios
                        </span>
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Servicio (Baremos)</th>
                                    <th class="text-center">Límite</th>
                                    <th class="text-center">Espera</th>
                                    <?php if ($canManage) : ?>
                                        <th class="text-center">Acciones</th> 
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($itemsCobertura)): ?>
                                    <tr>
                                        <td colspan="<?= $canManage ? '4' : '3' ?>" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                            No hay coberturas registradas
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($itemsCobertura as $item): ?>
                                        <tr>
                                            <td>
                                                <strong><?= Html::encode($item->baremo->nombre_servicio) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                   <?= $item->cantidad_limite ?: 'N/A' ?>
                                            </td>
                                            <td class="text-center">
                                                   <?= $item->plazo_espera ?: 'N/A' ?>
                                            </td>
                                            <?php if ($canManage) : ?>
                                                <td class="text-center">
                                                    <?php Html::a(
                                                        '<i class="far fa-trash-alt"></i>', 
                                                        ['remove-cobertura', 'id' => $item->id, 'plan_id' => $model->id, 'clinica_id' => $clinica->id], // Pasa plan_id y clinica_id
                                                        [
                                                            'title' => 'Eliminar cobertura',
                                                            'class' => 'btn-action delete', // Clase de sipsa.css
                                                            'data' => [
                                                                'confirm' => '¿Estás seguro de que quieres eliminar esta cobertura del plan?',
                                                                'method' => 'post',
                                                            ],
                                                        ]
                                                    ) ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de servicios disponibles para agregar -->
            <?php if (!empty($baremosFaltantes)): ?>
                <div class="ms-panel border-yellow"> 
                    <div class="ms-panel-body">
                        <h3 class="section-title">
                            <i class="fas fa-plus-circle text-yellow-600 mr-3"></i> Servicios Disponibles
                            <span class="status-badge active float-end"> 
                                <?= count($baremosFaltantes) ?> disponibles
                            </span>
                        </h3>
                        <div class="divide-y"> 
                            <?php foreach ($baremosFaltantes as $baremo): ?>
                                <div class="py-3 flex justify-between items-center"> 
                                    <div>
                                        <h5 class="font-medium mb-0"><?= Html::encode($baremo->nombre_servicio) ?></h5>
                                        <small class="text-muted block sm:inline"><?= Html::encode($baremo->descripcion) ?></small>
                                    </div>
                                    <?= Html::a(
                                        '<i class="fas fa-plus mr-2"></i> Agregar', 
                                        ['add-cobertura', 'plan_id' => $model->id, 'baremo_id' => $baremo->id, 'clinica_id' => $clinica->id], 
                                        ['class' => 'btn-base btn-blue btn-sm'] 
                                    ) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
