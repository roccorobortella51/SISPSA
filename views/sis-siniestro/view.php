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
            <?php Html::a(
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
                [
                    'index', 
                    'user_id' => $model->iduser,
                    'clinica_id' => $model->idclinica
                ],
                [
                    'class' => 'btn-base btn-gray',
                    'title' => 'Volver a la lista de siniestros',
                    'data' => [
                        'pjax' => 0,
                    ],
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
                        <h5 class="text-muted">Clínica Asociada</h5>
                        <p class="h4 text-dark"><?= Html::encode($model->clinica->nombre) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Servicios de Baremo</h5>
                        <?php 
                        if (!empty($baremos) && is_array($baremos)) {
                            $nombresBaremos = [];
                            foreach ($baremos as $baremo) {
                                $nombresBaremos[] = Html::encode($baremo->nombre_servicio);
                            }
                            echo '<p class="h5 text-dark">' . implode(', ', $nombresBaremos) . '</p>';
                        } else {
                            echo '<p class="text-muted">No se han seleccionado servicios de baremo</p>';
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Fecha del Siniestro</h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDate($model->fecha) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Hora del Siniestro</h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asTime($model->hora) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Fecha de Atención</h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDate($model->fecha_atencion) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Hora de Atención</h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asTime($model->hora_atencion) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Atendido</h5>
                        <p class="h5 text-dark"><?= formatBooleanIcon($model->atendido) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Afiliado</h5>
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
            <div class="alert alert-success" align="center">
                <h2><strong>Total:</strong> <?= nl2br(Html::encode($model->costo_total)) ?> Bs</h2>
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
                        <h5 class="text-muted">Fecha de Creación</h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card-body text-center">
                        <h5 class="text-muted">Última Actualización</h5>
                        <p class="h5 text-dark"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
