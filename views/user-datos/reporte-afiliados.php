<?php
// views/user-datos/reporte-afiliados.php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AfiliadosReportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $clinicaList array */
/* @var $tipoAfiliadoList array */

$this->title = 'Reporte de Afiliados';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-datos-index">

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-file-alt"></i> <?= Html::encode($this->title) ?></h4>
        </div>
        <div class="card-body">

            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filtros</h5>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin([
                        'action' => ['reporte-afiliados'],
                        'method' => 'get',
                        'options' => ['class' => 'form-horizontal'],
                        'fieldConfig' => [
                            'template' => "{label}\n{input}\n{error}",
                            'labelOptions' => ['class' => 'control-label'],
                        ],
                    ]); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($searchModel, 'clinica_id')->widget(Select2::class, [
                                'data' => $clinicaList,
                                'options' => ['placeholder' => 'Todas las clínicas'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]) ?>
                        </div>

                        <div class="col-md-6">
                            <?= $form->field($searchModel, 'user_datos_type_id')->widget(Select2::class, [
                                'data' => $tipoAfiliadoList,
                                'options' => ['placeholder' => 'Todos los tipos'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]) ?>
                        </div>

                        <!-- REMOVED: Estatus Solvente filter -->
                    </div>

                    <div class="form-group">
                        <?= Html::submitButton('<i class="fas fa-search"></i> Buscar', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('<i class="fas fa-redo"></i> Limpiar', ['reporte-afiliados'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="mb-4">
                <div class="btn-group" role="group">
                    <?= Html::a('<i class="fas fa-file-excel"></i> Exportar a Excel', ['exportar-excel-afiliados'] + Yii::$app->request->queryParams, [
                        'class' => 'btn btn-success',
                        'target' => '_blank',
                        'data-pjax' => '0',
                    ]) ?>

                    <?= Html::a('<i class="fas fa-file-pdf"></i> Exportar a PDF', ['exportar-pdf-afiliados'] + Yii::$app->request->queryParams, [
                        'class' => 'btn btn-danger',
                        'target' => '_blank',
                        'data-pjax' => '0',
                    ]) ?>
                </div>

                <div class="float-right">
                    <span class="badge badge-info">
                        <i class="fas fa-users"></i> Total: <?= number_format($dataProvider->getTotalCount()) ?> afiliados
                    </span>
                </div>
            </div>

            <!-- Results Grid -->
            <?php Pjax::begin(['id' => 'reporte-grid']); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'summary' => '',
                'columns' => [
                    [
                        'header' => '#',
                        'value' => function ($model, $key, $index, $column) use ($dataProvider) {
                            $page = $dataProvider->pagination->page;
                            $pageSize = $dataProvider->pagination->pageSize;
                            return ($page * $pageSize) + $index + 1;
                        },
                        'headerOptions' => ['style' => 'width: 5%'],
                        'contentOptions' => ['style' => 'text-align: center; font-weight: bold;'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Nombre Completo',
                        'value' => function ($model) {
                            return $model->nombres . ' ' . $model->apellidos;
                        },
                        'headerOptions' => ['style' => 'width: 35%'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Cédula de Identidad',
                        'value' => function ($model) {
                            return $model->tipo_cedula . '-' . $model->cedula;
                        },
                        'headerOptions' => ['style' => 'width: 20%'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Clínica',
                        'value' => function ($model) {
                            return $model->clinica ? $model->clinica->nombre : '';
                        },
                        'headerOptions' => ['style' => 'width: 40%'],
                        'enableSorting' => false,
                    ],
                ],
                'pager' => [
                    'firstPageLabel' => '«',
                    'lastPageLabel' => '»',
                    'prevPageLabel' => '<',
                    'nextPageLabel' => '>',
                    'maxButtonCount' => 5,
                    'options' => ['class' => 'pagination justify-content-center'],
                    'linkOptions' => ['class' => 'page-link'],
                    'pageCssClass' => 'page-item',
                    'activePageCssClass' => 'active',
                    'disabledPageCssClass' => 'disabled',
                ],
            ]); ?>
            <?php Pjax::end(); ?>

            <!-- Summary -->
            <div class="alert alert-info mt-3">
                <div class="row">
                    <div class="col-md-4">
                        <strong><i class="fas fa-info-circle"></i> Total de Afiliados:</strong>
                        <span class="badge badge-pill badge-primary"><?= number_format($dataProvider->getTotalCount()) ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-hospital"></i> Clínicas:</strong>
                        <?php
                        // Get unique clinic IDs from the current page
                        $clinicaIds = [];
                        foreach ($dataProvider->getModels() as $model) {
                            if ($model->clinica_id) {
                                $clinicaIds[$model->clinica_id] = true;
                            }
                        }
                        $uniqueClinicas = count($clinicaIds);
                        ?>
                        <span class="badge badge-pill badge-secondary"><?= $uniqueClinicas ?></span>
                    </div>
                    <div class="col-md-4 text-right">
                        <small><i class="fas fa-clock"></i> Generado: <?= date('d/m/Y H:i:s') ?></small>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer text-muted">
            <div class="row">
                <div class="col-md-6">
                    <small>
                        <i class="fas fa-lightbulb"></i>
                        <strong>Consejo:</strong> Use los filtros para buscar afiliados específicos.
                    </small>
                </div>
                <div class="col-md-6 text-right">
                    <small>
                        <i class="fas fa-download"></i>
                        <strong>Exportar:</strong> Use los botones de exportar para generar reportes.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Add CSS styles
$this->registerCss('
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
        border: none;
        margin-bottom: 20px;
    }
    .card-header {
        border-bottom: none;
    }
    .table th {
        background-color: #f8f9fa;
        border-top: none;
        color: #495057;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    .table td {
        color: #212529;
    }
    .btn-group .btn {
        margin-right: 5px;
    }
    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.7em;
    }
    .alert {
        border: none;
        border-radius: 8px;
    }
    .select2-container--krajee .select2-selection {
        border-radius: 4px;
        border: 1px solid #ced4da;
    }
');
?>