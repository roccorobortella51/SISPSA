<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="text-primary"><?= Html::encode("Perfil del Afiliado #{$model->id}") ?></h4>
                <div class="float-right" style="margin-bottom:10px;">
                    <?= Html::a('<i class="fas fa-file-pdf"></i> Contrato', ['index'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                    <?= Html::a(
                    '<i class="fas fa-undo"></i> Volver', 
                    '#', 
                    [
                        'class' => 'btn btn-primary btn-sm', 
                        'onclick' => 'window.history.back(); return false;', 
                        'title' => 'Volver a la página anterior', 
                    ]
                ) ?> 
                </div>
            </div>
        </div>
        <div class="ms-panel-body">
             <!-- Foto de perfil -->
            <div class="profile-header text-center">
                <?php if ($model->selfie): ?>
                    <?= Html::img(Yii::$app->request->baseUrl . '/' . $model->selfie, [
                        'alt' => 'Foto de Perfil',
                        'class' => 'profile-img'
                    ]) ?>
                    <p><strong>Foto de Perfil</strong></p>
                <?php else: ?>
                    <p><em>No hay foto de perfil</em></p>
                <?php endif; ?>
            </div>
            <!-- Datos personales -->
            <hr>
            <h5>Datos Personales</h5>
            <div class="row">
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-borderless w-100'],
                        'template' => '
                            <tr><td colspan="2"><strong>{label}</strong></td></tr>
                            <tr><td colspan="2">{value}</td></tr>
                        ',
                        'attributes' => [
                            [
                                'attribute' => 'nombres',
                                'label' => 'Nombres',
                            ],
                            [
                                'attribute' => 'cedulaFormatted',
                                'label' => 'Cédula de Identidad',
                            ],

                            [
                                'attribute' => 'sexo',
                                'label' => 'Sexo',
                            ],

                            [
                                'attribute' => 'email',
                                'format' => 'email',
                                'label' => 'Correo Electrónico',
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-borderless w-100'],
                        'template' => '
                            <tr><td colspan="2"><strong>{label}</strong></td></tr>
                            <tr><td colspan="2">{value}</td></tr>
                        ',
                        'attributes' => [

                            [
                                'attribute' => 'apellidos',
                                'label' => 'Apellidos',
                            ],
                            [
                                'attribute' => 'fechanac',
                                'label' => 'Fecha de Nacimiento',
                                'format' => ['date', 'php:d-m-Y'],
                            ],
                            [
                                'attribute' => 'telefono',
                                'label' => 'Teléfono',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
            <!-- Dirección y ubicación -->
            <hr>
            <h5>Ubicación</h5>
            <div class="row">
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-borderless w-100'],
                        'template' => '
                            <tr><td colspan="2"><strong>{label}</strong></td></tr>
                            <tr><td colspan="2">{value}</td></tr>
                        ',
                        'attributes' => [
                            [
                                'attribute' => 'estado',
                                'label' => 'Estado',
                                'value' => function($model) use($estado) {
                                        return $estado;
                                }
                            ],
                            [
                                'attribute' => 'ciudad',
                                'label' => 'Ciudad',
                                'value' => function($model) use($ciudad) {
                                        return $ciudad;
                                }

                            ],
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-borderless w-100'],
                        'template' => '
                            <tr><td colspan="2"><strong>{label}</strong></td></tr>
                            <tr><td colspan="2">{value}</td></tr>
                        ',
                        'attributes' => [
                            [
                                'attribute' => 'municipio',
                                'label' => 'Municipio',
                                'value' => function($model) use($municipio) {
                                        return $municipio;
                                }
                            ],
                            [
                                'attribute' => 'parroquia',
                                'label' => 'Parroquia',
                                'value' => function($model) use($parroquia) {
                                        return $parroquia;
                                }
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-borderless w-100'],
                        'template' => '
                            <tr><td colspan="2"><strong>{label}</strong></td></tr>
                            <tr><td colspan="2">{value}</td></tr>
                        ',
                        'attributes' => [
                            'direccion',
                        ],
                    ]) ?>
                </div>
            </div>
            <!-- Información adicional -->
            <hr>
            <h5>Información Adicional</h5>
            <div class="row">
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-borderless'],
                        'template' => '
                                    <tr><td colspan="2"><strong>{label}</strong></td></tr>
                                    <tr><td colspan="2">{value}</td></tr>
                                ',
                        'attributes' => [
                            [
                                'attribute' => 'clinica.nombre',
                                'label' => 'Clínica',
                                'value' => $model->clinica ? $model->clinica->nombre : 'No asignada',
                            ],
                            [
                                'attribute' => 'asesor.nombre',
                                'label' => 'Asesor',
                                'value' => $model->asesor ? $model->asesor->nom : 'Sin asignar',
                            ],
                            'tipo_sangre',
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-borderless'],
                        'template' => '
                                    <tr><td colspan="2"><strong>{label}</strong></td></tr>
                                    <tr><td colspan="2">{value}</td></tr>
                                ',
                        'attributes' => [
                            [
                                'attribute' => 'plan.nombre',
                                'label' => 'Plan',
                                'value' => $model->plan ? $model->plan->nombre : 'No asignado',
                            ],

                            'estatus:ntext',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>