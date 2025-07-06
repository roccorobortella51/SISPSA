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
 */

// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['/user-datos/index']];
// --- FIN  --- 


$this->title = 'Gestión de Contactos del Afiliado'; // Este sigue siendo el título para la página y breadcrumbs

?>

<div class=row style="margin:3px !important;">
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-undo"></i> Volver', ['/user-datos/update', 'id' => $afiliado->id], ['class' => 'btn btn-warning btn-lg']) ?> 
        </div>
    </div>

    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= $this->title = 'Afiliado: Nombre completo: '.$afiliado->nombres." ".$afiliado->apellidos.', Cédula: ' . $afiliado->cedula; ?> 



            </h1>
            </div>
            <div class="ms-panel-body">
                <?php $form = ActiveForm::begin(); ?>
                <h1>Agregue un nuevo Contacto</h1>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'nombre')->textInput([ 'class' => 'form-control', 'placeholder' => 'Escriba un nombre del familiar','class' => 'form-control form-control-lg',]) ?>
                    </div>
                    <div class="col-md-3">
                         <?= $form->field($model, 'telefono')->textInput([ 'class' => 'form-control', 'placeholder' => 'Escriba un telefono del contacto','class' => 'form-control form-control-lg',]) ?>
                    </div>
                    <div class="col-md-3">
                         <?= $form->field($model, 'relacion')->textInput([ 'class' => 'form-control', 'placeholder' => 'Escriba la relación del contacto','class' => 'form-control form-control-lg',]) ?>
                    </div>
                    <div class="col-md-3">
                         <?= $form->field($model, 'correo')->textInput([ 'class' => 'form-control', 'placeholder' => 'Escriba un correo electronico del contacto','class' => 'form-control form-control-lg',]) ?>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group text-rigth mt-4" style="margin-right:10px;">
                            <div class="form-group text-rigth mt-4" style="margin-right:10px;">
                                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
                            </div>
                        </div>
                    </div>
                     <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
     </div>
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= $this->title = 'Gestión de los Contacos del Afiliado '?></h1>
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

                                    //'id',
                                    //'created_at',
                                    'correo',
                                    'nombre:ntext',
                                    'telefono:ntext',
                                    //'correo:ntext',
                                    'relacion',
                                    //'user_id',
                                    //'relacion:ntext',
                                    //'deleted_at',
                                    //'updated_at',
                                   [
                                    'class' => 'yii\grid\ActionColumn',
                                    'header' => 'ACCIONES',
                                    'template' => '<div class="d-flex justify-content-center gap-0">{update}</div>',
                                    'options' => ['style' => 'width:55px; min-width:55px;'],
                                    'headerOptions' => ['style' => 'color: white!important;'],
                                    'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                    'buttons' => [
                                        /*'view' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="fa fa-eye"></i>',
                                                Url::to(['view', 'id' => $model->id]),
                                                [
                                                    'title' => 'Detalle de la Clínica',
                                                    'class' => 'btn btn-link btn-sm text-success',
                                                    'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                ]
                                            );
                                        },*/
                                        'update' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="fas fa-pencil-alt ms-text-primary"></i>',
                                                Url::to(['update', 'id' => $model->id]),
                                                [
                                                    'title' => 'Editar',
                                                    'class' => 'btn btn-link btn-sm text-success',
                                                    'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                ]
                                            );
                                        },
                                        /*'delete' => function ($url, $model, $key) {
                                            return Html::a(
                                                '<i class="far fa-trash-alt ms-text-danger"></i>',
                                                Url::to(['delete', 'id' => $model->id]),
                                                [
                                                    'title' => 'Eliminar',
                                                    'data-confirm' => '¿Estás seguro de que quieres eliminar esta clínica?',
                                                    'data-method' => 'post',
                                                    'class' => 'btn btn-link btn-sm text-danger',
                                                    'style' => 'display: contents; width: 20px; height: 20px; padding: 0 !important; margin: 0 !important; line-height: 1 !important; font-size: 0.85rem;'
                                                ]
                                            );
                                        },*/
                                        
                                    ],
                                ],
                                ],
                            ]); ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="clearfix"></div>
        </div>

 





















