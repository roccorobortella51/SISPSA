<?php

use yii\helpers\Html;
use yii\widgets\DetailView; // Mantendremos DetailView si lo prefieres para algunos campos, pero la mayoría será manual
use app\models\Agente;
use app\models\User;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */

$this->title = 'DETALLES DE ASESOR VENDEDOR: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'AGENTES DE FUERZA'];
$this->params['breadcrumbs'][] = $model->id;

\yii\web\YiiAsset::register($this);

// Preparar el nombre de la agencia
$agenciaNombre = 'N/A';
if ($model->agente) {
    $agenciaNombre = $model->agente->nom;
}

// Preparar el nombre de usuario
$nombreUsuario = 'N/A';
if ($model->idusuario && $model->getRelation('usuario', false) !== null && $model->usuario) {
    $nombreUsuario = $model->usuario->username;
}

// Función auxiliar para mostrar Sí/No con íconos
function formatBooleanIcon($value) {
    return $value ? '<span class="text-success me-1"><i class="fas fa-check-circle"></i></span> Sí' : '<span class="text-danger me-1"><i class="fas fa-times-circle"></i></span> No';
}

?>



<div class="agente-fuerza-view">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                        <div class="ms-panel-header header-mini">
                            <h6 style="margin: 0;">
                                <?= Html::a(
                                    'ACTUALIZAR AGENTE',
                                    ['update', 'id' => $model->id],
                                    ['class' => 'text-white']
                                ) ?>
                            </h6>
                        </div>
                        <div class="ms-panel-body">
                            <div class="text-center">
                                <i class="flaticon-information"></i>
                                <p class="mb-0">Modifica los datos de este agente de fuerza.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                        <div class="ms-panel-header header-mini">
                            <h6 style="margin: 0;">
                            <?= Html::a(
    'VOLVER A LA LISTA',
    ['agente-fuerza/index-by-agente', 'agente_id' => $model->agente_id], // ¡AQUÍ ESTÁ EL CAMBIO!
    ['class' => 'text-white']
) ?>
                            </h6>
                        </div>
                        <div class="ms-panel-body">
                            <div class="text-center">
                                <i class="flaticon-information"></i>
                                <p class="mb-0">Regresa a la lista completa de agentes.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ms-panel ms-widget ms-identifier-widget bg-info">
                        <div class="ms-panel-header header-mini">
                            <h6 style="margin: 0;">
                                <?= Html::a(
                                    'ELIMINAR AGENTE',
                                    ['delete', 'id' => $model->id],
                                    [
                                        'class' => 'text-white',
                                        'data' => [
                                            'confirm' => '¿Estás seguro de que quieres eliminar este agente de fuerza? Esta acción no se puede deshacer.',
                                            'method' => 'post',
                                        ],
                                    ]
                                ) ?>
                            </h6>
                        </div>
                        <div class="ms-panel-body">
                            <div class="text-center">
                                <i class="flaticon-information"></i>
                                <p class="mb-0">Elimina este agente de forma permanente.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <h5 class="mt-4 mb-3">Porcentajes de Comisión</h5>
            <div class="row mb-4 g-3">
                <div class="col-md-3">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Venta</h6>
                        <p class="h4 text-info"><?= Yii::$app->formatter->asPercent($model->por_venta / 100) ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Asesoría</h6>
                        <p class="h4 text-info"><?= Yii::$app->formatter->asPercent($model->por_asesor / 100) ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Cobranza</h6>
                        <p class="h4 text-info"><?= Yii::$app->formatter->asPercent($model->por_cobranza / 100) ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Post Venta</h6>
                        <p class="h4 text-info"><?= Yii::$app->formatter->asPercent($model->por_post_venta / 100) ?></p>
                    </div>
                </div>
                 <div class="col-md-3">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Registro</h6>
                        <p class="h4 text-info"><?= Yii::$app->formatter->asPercent($model->por_registrar / 100) ?></p>
                    </div>
                </div>
            </div>

            ---

            <h5 class="mt-4 mb-3">Permisos de Acceso</h5>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Venta y Asesoría</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Puede Vender
                                    <span class="badge bg-<?= $model->puede_vender ? 'success' : 'danger' ?> fs-6">
                                        <?= formatBooleanIcon($model->puede_vender) ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Puede Asesorar
                                    <span class="badge bg-<?= $model->puede_asesorar ? 'success' : 'danger' ?> fs-6">
                                        <?= formatBooleanIcon($model->puede_asesorar) ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Gestión y Cobranza</h6>
                        </div>
                        <div class="card-body">
                             <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Puede Cobrar
                                    <span class="badge bg-<?= $model->puede_cobrar ? 'success' : 'danger' ?> fs-6">
                                        <?= formatBooleanIcon($model->puede_cobrar) ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Puede Post Venta
                                    <span class="badge bg-<?= $model->puede_post_venta ? 'success' : 'danger' ?> fs-6">
                                        <?= formatBooleanIcon($model->puede_post_venta) ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Puede Registrar
                                    <span class="badge bg-<?= $model->puede_registrar ? 'success' : 'danger' ?> fs-6">
                                        <?= formatBooleanIcon($model->puede_registrar) ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            ---

            <h5 class="mt-4 mb-3">Fechas de Gestión</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Fecha de Creación</h6>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Última Actualización</h6>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-body bg-light text-center">
                        <h6 class="text-muted">Fecha de Eliminación</h6>
                        <p class="h5 text-dark"><?= $model->deleted_at ? Yii::$app->formatter->asDatetime($model->deleted_at) : 'N/A' ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>