<?php

use yii\helpers\Html;

$this->title = 'Detalles de la Atención: ' . Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula);
$this->params['breadcrumbs'][] = ['label' => 'Siniestros', 'url' => ['index', 'user_id' => $model->iduser]];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);

function formatBooleanIcon($value) {
    $isTrue = (bool)$value;
    return $isTrue ? '<span class="status-badge active">Sí</span>' : '<span class="status-badge inactive">No</span>';
}

?>

<div class="main-container">
   
    <div class="header-section">
        <h1><?= Html::encode($this->title) ?></h1>
       
        <div class="header-buttons-group">
            <?= Html::a(
                '<i class="fas fa-edit mr-2"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'btn-base btn-blue']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-trash-alt mr-2"></i> Eliminar',
                ['delete', 'id' => $model->id],
                [
                    'class' => 'btn-base btn-red',
                    'data' => [
                        'confirm' => '¿Está seguro de que desea eliminar esta atención? Esta acción no se puede deshacer.',
                        'method' => 'post',
                    ],
                ]
            ) ?>
            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver',
                '#',
                [
                    'class' => 'btn-base btn-gray',
                    'onclick' => 'window.history.back(); return false;',
                    'title' => 'Volver a la página anterior',
                ]
            ) ?>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-info-circle text-blue-600 mr-3"></i> Información General de la Atención
            </h3>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Clínica Asociada</h6>
                        <p class="h4 text-dark"><?= Html::encode($model->clinica->nombre) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Servicio de Baremo</h6>
                        <p class="h4 text-dark"><?= Html::encode($model->baremo->nombre_servicio) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Fecha del Siniestro</h6>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDate($model->fecha) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Hora del Siniestro</h6>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asTime($model->hora) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Fecha de Atención</h6>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDate($model->fecha_atencion) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Hora de Atención</h6>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asTime($model->hora_atencion) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Atendido</h6>
                        <p class="h5 text-dark"><?= formatBooleanIcon($model->atendido) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Afiliado</h6>
                        <p class="h5 text-dark"><?= Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " (" . $afiliado->tipo_cedula . "-" . $afiliado->cedula . ")") ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-file-alt text-blue-600 mr-3"></i> Descripción del Siniestro
            </h3>
            <div class="info-card-body">
                <h5><strong>Descripción:</strong> <?= nl2br(Html::encode($model->descripcion)) ?></h5>
            </div>
        </div>
    </div>

    <div class="ms-panel">
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-clock text-blue-600 mr-3"></i> Fechas de Registro
            </h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Fecha de Creación</h6>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h6 class="text-muted">Última Actualización</h6>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
