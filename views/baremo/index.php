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
use app\models\Baremo; // ADDED: Assuming this is the model for the form
use app\models\BaremoSearch; // CORRECTED: Assuming this is the search model
use app\models\RmClinica; // ADDED: For the $clinica variable

/**
 * @var yii\web\View $this
 * @var app\models\BaremoSearch $searchModel // Corrected type hint
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\Baremo $model // ADDED: For the form
 * @var app\models\RmClinica $clinica // ADDED: For the clinic details in the header
 */

// --- BREADCRUMBS ---
// Se asume que $clinica se pasa desde el controlador. Se añade una comprobación.
$this->params['breadcrumbs'][] = ['label' => 'CLINICAS', 'url' => ['/rm-clinica/index']];
if (isset($clinica) && $clinica) {
    $this->params['breadcrumbs'][] = ['label' => $clinica->nombre, 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = 'Gestión de Baremos';
} else {
    $this->params['breadcrumbs'][] = 'Gestión de Baremos'; // Fallback si $clinica no está disponible
}
// --- FIN BREADCRUMBS ---

// Set the main title for the page
$this->title = 'Gestión de Baremos';

?>

<div class="row" style="margin:3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
       
    </div>
    <div class="col-md-12">
        <div class="ms-panel ms-panel-fh">

            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <!-- Consolidado y corregido el título para evitar el error "Attempt to read property 'nombre' on null" -->
                <h1><?= 'Agregar de Baremos a la Clínica ' . (isset($clinica) && $clinica ? Html::encode($clinica->nombre) : 'Desconocida'); ?></h1>
                        
                <div>
                    <?= Html::a(
                        '<i class="fas fa-undo"></i> Volver', 
                        '#',
                        [
                            'class' => 'btn btn-primary btn-lg', 
                            'onclick' => 'window.history.back(); return false;', 
                            'title' => 'Volver a la página anterior', 
                        ]
                    ) ?> 
                </div>
            </div>

            <div class="ms-panel-body">
                <?php $form = ActiveForm::begin(); ?>
                <div class="row">
                    <div class="col-md-2">
                        <?= $form->field($model, 'area_id')->widget(Select2::classname(), [
                            'data' => UserHelper::getAreaList(),
                            'options' => [
                                'placeholder' => 'Seleccione un área...', // Cambiado el placeholder para mayor claridad
                                'class' => 'form-control form-control-lg',
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                        ])->label('Área') // Añadido label explícito
                        ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'nombre_servicio')->textInput([ 'class' => 'form-control form-control-lg', 'placeholder' => 'Escriba un nombre para el Baremo'])->label('Nombre del Servicio') ?>
                    </div>
                    <div class="col-md-4">
                         <?= $form->field($model, 'descripcion')->textInput([ 'class' => 'form-control form-control-lg', 'placeholder' => 'Escriba una descripción para el Baremo'])->label('Descripción') ?>
                    </div>
                    <div class="col-md-2">
                         <?= $form->field($model, 'costo')->textInput(['type' => 'number','class' => 'form-control form-control-lg', 'placeholder' => '0.00' ])->label('Costo') ?>
                    </div>
                    <div class="col-md-2">
                         <?= $form->field($model, 'precio')->textInput(['type' => 'number', 'class' => 'form-control form-control-lg', 'placeholder' => '0.00'])->label('Precio') ?>
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
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <!-- Consolidado y corregido el título para el GridView -->
                <h1><?= 'Gestión de Baremos de ' . (isset($clinica) && $clinica ? Html::encode($clinica->nombre) : 'Clínica Desconocida'); ?></h1>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?= GridView::widget([
                        'id' => 'baremo-grid', // Cambiado el ID para mayor claridad
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'layout' => "{items}{pager}",
                        'resizableColumns' => false,
                        'bordered' => false,
                        'responsiveWrap' => false,
                        'persistResize' => false,
                        'tableOptions' => [
                            'class' => 'table table-striped table-bordered table-hover table-sm'
                        ],
                        'options' => [
                            'class' => 'grid-view-container table-responsive',
                        ],
                        'columns' => [
                            [
                                'attribute' => 'area_id',
                                'value' => function ($model, $key, $index, $widget) {
                                    return $model->area ? $model->area->nombre : "";
                                },
                                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                                'filter' => UserHelper::getAreaList(),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => Yii::t('app', 'Seleccione')],
                                'format' => 'raw',
                                'headerOptions' => ['class' => 'text-center header-link'],
                                'label' => 'Area',
                            ],
                            [
                                'attribute' => 'nombre_servicio',
                                'format' => 'ntext',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'options' => ['style' => 'width: 250px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Búsqueda',
                                    'class' => 'form-control text-center',
                                ],
                            ],
                            [
                                'attribute' => 'descripcion',
                                'format' => 'ntext',
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'options' => ['style' => 'width: 250px;'],
                                'filterInputOptions' => [
                                    'placeholder' => 'Búsqueda',
                                    'class' => 'form-control text-center',
                                ],
                            ],
                            [
                                'attribute' => 'costo',
                                'format' => ['currency', ''],
                                'contentOptions' => ['style' => 'text-align: right;'],
                                'filter' => false
                            ],
                            [
                                'attribute' => 'precio',
                                'format' => ['currency', ''],
                                'contentOptions' => ['style' => 'text-align: right;'],
                                'filter' => false
                            ],
                            [
                                'label' => 'Estado',
                                'attribute' => 'estatus',
                                'format' => 'raw',
                                'headerOptions' => ['class' => 'text-left header-link'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'value' => function ($model) {
                                    $isActive = ($model->estatus === 'Activo' || $model->estatus === 1 || $model->estatus === true);
                                    
                                    return SwitchInput::widget([
                                        'name' => 'status_'.$model->id,
                                        'value' => $isActive,
                                        'pluginEvents' => [
                                            'switchChange.bootstrapSwitch' => "function(e){updatestatus('$model->id')}"
                                        ],
                                        'pluginOptions' => [
                                            'onText' => 'Activo',
                                            'offText' => 'Inactivo',
                                            'onColor' => 'success',
                                            'offColor' => 'danger',
                                            'state' => $isActive
                                        ],
                                        'options' => [
                                            'id' => 'status-switch-'.$model->id
                                        ],
                                        'labelOptions' => ['style' => 'font-size: 12px;'],
                                    ]);
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'ACCIONES',
                                'template' => '<div class="d-flex justify-content-center gap-0">{update}</div>',
                                'options' => ['style' => 'width:55px; min-width:55px;'],
                                'headerOptions' => ['style' => 'color: white!important;'],
                                'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                                'buttons' => [
                                    'update' => function ($url, $model, $key) {
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
    <div class="clearfix"></div>
</div>
