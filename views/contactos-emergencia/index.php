<?php

// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\widgets\ActiveForm;
use app\components\UserHelper;
use kartik\widgets\SwitchInput;
use kartik\select2\Select2;

/**
 * @var yii\web\View $this
 * @var app\models\RmClinicaSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\UserDatos $afiliado
 * @var app\models\ContactosEmergencia $model
 */

// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['/user-datos/index']];
// --- FIN  --- 

// Set the title first
$this->title = 'Gestión de Contactos del Afiliado';

$rol = UserHelper::getMyRol();
// Allow ALL roles to create and manage contacts
$permisos = true; // Everyone has permissions now

// Check if $afiliado exists to prevent errors
if (!isset($afiliado) || $afiliado === null) {
    echo '<div class="alert alert-danger">Error: No se encontró el afiliado.</div>';
    return;
}

?>

<!-- The form to create new contacts is now visible to ALL roles -->
<div class="row" style="margin:3px !important;">
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-undo"></i> Volver', ['/user-datos/update', 'id' => $afiliado->id], ['class' => 'btn btn-warning btn-lg']) ?>
        </div>
    </div>

    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1>Afiliado: Nombre completo: <?= Html::encode($afiliado->nombres . " " . $afiliado->apellidos . ', Cédula: ' . $afiliado->cedula) ?></h1>
            </div>
            <div class="ms-panel-body">
                <?php $form = ActiveForm::begin(); ?>
                <h2>Agregue un nuevo Contacto</h2>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'nombre')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Escriba un nombre del familiar']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'telefono')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Escriba un telefono del contacto']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'relacion')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Escriba la relación del contacto']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'correo')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Escriba un correo electronico del contacto']) ?>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group text-right mt-4" style="margin-right:10px;">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1>Gestión de los Contactos del Afiliado</h1>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}{pager}",
                        'resizableColumns' => false,
                        'bordered' => false,
                        'responsiveWrap' => false,
                        'persistResize' => false,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            'correo',
                            'nombre:ntext',
                            'telefono:ntext',
                            'relacion',
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{update}</div>',
                                'options' => ['style' => 'width:55px; min-width:55px;'],
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10px !important;'],
                                'buttons' => [
                                    'update' => function ($url, $model, $key) use ($permisos) {
                                        // Now ALL roles can edit (since $permisos = true)
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                            Url::to(['update', 'id' => $model->id]),
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn-action view'
                                            ]
                                        );
                                    },
                                ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>