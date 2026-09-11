<?php
// views/user-datos/reporte-afiliados.php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\data\ArrayDataProvider;
use kartik\select2\Select2;
use app\components\UserHelper;
use app\models\RmClinica;

/* @var $this yii\web\View */
/* @var $searchModel app\models\AfiliadosReportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $clinicaList array */
/* @var $tipoAfiliadoList array */

// Ensure $searchModel is defined to prevent undefined variable notices in views
if (!isset($searchModel) || !$searchModel) {
    $searchModel = new \app\models\AfiliadosReportSearch();
}

$this->title = 'Reporte de Afiliados';
$this->params['breadcrumbs'][] = $this->title;

// Check user role for clinic access
$hasClinicAccess = UserHelper::hasClinicAccess();
$isSuperAdmin = Yii::$app->user->can('superadmin') || Yii::$app->user->can('admin');

// Get user's accessible clinics for display
if ($hasClinicAccess) {
    $accessibleClinicas = UserHelper::getAccessibleClinicas();
    $accessibleClinicaIds = \yii\helpers\ArrayHelper::getColumn($accessibleClinicas, 'id');
    $hasMultipleClinicas = count($accessibleClinicas) > 1;
} else {
    $accessibleClinicas = [];
    $accessibleClinicaIds = [];
    $hasMultipleClinicas = true;
}

// Ensure $clinicaList is defined to prevent undefined variable notices
if (!isset($clinicaList) || !is_array($clinicaList)) {
    if ($hasClinicAccess && !empty($accessibleClinicas)) {
        $clinicaList = \yii\helpers\ArrayHelper::map($accessibleClinicas, 'id', 'nombre');
    } else {
        // Fallback: try to load all clinics for admins or when no access restriction
        $clinicaList = [];
        try {
            $clinicas = RmClinica::find()->select(['id', 'nombre'])->orderBy('nombre')->all();
            $clinicaList = \yii\helpers\ArrayHelper::map($clinicas, 'id', 'nombre');
        } catch (\Throwable $e) {
            // keep empty list on failure
            $clinicaList = [];
        }
    }
}

// Ensure $dataProvider is defined to prevent undefined variable notices
if (!isset($dataProvider) || !$dataProvider) {
    $dataProvider = new ArrayDataProvider([
        'allModels' => [],
        'pagination' => ['pageSize' => 10],
    ]);
}

// Ensure $tipoAfiliadoList is defined to prevent undefined variable notices
if (!isset($tipoAfiliadoList) || !is_array($tipoAfiliadoList)) {
    $tipoAfiliadoList = [];
}
?>
<div class="user-datos-index">

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-file-alt"></i> <?= Html::encode($this->title) ?>
                <?php if ($hasClinicAccess): ?>
                    <span class="badge bg-info ms-2" style="font-size: 0.8rem;">
                        <i class="fas fa-lock me-1"></i> Acceso Restringido
                    </span>
                <?php endif; ?>
            </h4>
        </div>
        <div class="card-body">

            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-filter"></i> Filtros
                        <?php if ($hasClinicAccess): ?>
                            <small class="text-white ms-2">
                                <i class="fas fa-info-circle"></i> Datos limitados a sus clínicas asignadas
                            </small>
                        <?php endif; ?>
                    </h5>
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
                            <?php if ($hasClinicAccess && count($accessibleClinicas) === 1): ?>
                                <!-- Single clinic - display as read-only with hidden input -->
                                <div class="form-group">
                                    <label class="control-label">Clínica</label>
                                    <div class="form-control-static border rounded p-2" style="background-color: #f8f9fa;">
                                        <i class="fas fa-hospital me-2 text-success"></i>
                                        <strong><?= Html::encode($accessibleClinicas[0]->nombre) ?></strong>
                                        <input type="hidden" name="AfiliadosReportSearch[clinica_id]" value="<?= $accessibleClinicas[0]->id ?>">
                                        <input type="hidden" name="AfiliadosReportSearch[clinica_ids][]" value="<?= $accessibleClinicas[0]->id ?>">
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-lock me-1"></i>Acceso restringido a su clínica asignada
                                    </small>
                                </div>
                            <?php elseif ($hasClinicAccess && count($accessibleClinicas) > 1): ?>
                                <!-- Multiple clinics - show as disabled multi-select -->
                                <div class="form-group">
                                    <label class="control-label">Clínicas (Acceso Restringido)</label>
                                    <div class="form-control-static border rounded p-2" style="background-color: #f8f9fa;">
                                        <?php foreach ($accessibleClinicas as $clinica): ?>
                                            <span class="badge bg-success me-1 mb-1 p-2">
                                                <i class="fas fa-hospital me-1"></i><?= Html::encode($clinica->nombre) ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <input type="hidden" name="AfiliadosReportSearch[clinica_ids][]" value="<?= implode(',', $accessibleClinicaIds) ?>">
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-lock me-1"></i>Datos limitados a sus <?= count($accessibleClinicas) ?> clínicas asignadas
                                    </small>
                                </div>
                            <?php else: ?>
                                <!-- Normal clinic selector for admin/superadmin -->
                                <?= $form->field($searchModel, 'clinica_id')->widget(Select2::class, [
                                    'data' => $clinicaList,
                                    'options' => ['placeholder' => 'Todas las clínicas'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]) ?>
                            <?php endif; ?>
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
                    <?php if ($hasClinicAccess): ?>
                        <span class="badge badge-warning ms-2">
                            <i class="fas fa-lock"></i> Datos Restringidos
                        </span>
                    <?php endif; ?>
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
                        'value' => function ($model, $key, $index, $column) {
                            $dataProvider = isset($column->grid->dataProvider) ? $column->grid->dataProvider : null;
                            if ($dataProvider && $dataProvider->pagination) {
                                $page = $dataProvider->pagination->page;
                                $pageSize = $dataProvider->pagination->pageSize;
                                return ($page * $pageSize) + $index + 1;
                            }
                            return $index + 1;
                        },
                        'headerOptions' => ['style' => 'width: 4%'],
                        'contentOptions' => ['style' => 'text-align: center; font-weight: bold;'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Nombre Completo',
                        'value' => function ($model) {
                            return $model->nombres . ' ' . $model->apellidos;
                        },
                        'headerOptions' => ['style' => 'width: 16%'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Cédula de Identidad',
                        'value' => function ($model) {
                            return $model->tipo_cedula . '-' . $model->cedula;
                        },
                        'headerOptions' => ['style' => 'width: 10%'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'enableSorting' => false,
                    ],
                    // ============================================
                    // NEW COLUMN: Fecha de Nacimiento
                    // ============================================
                    [
                        'label' => 'Fecha de Nacimiento',
                        'value' => function ($model) {
                            if (!empty($model->fechanac)) {
                                return Yii::$app->formatter->asDate($model->fechanac, 'dd/MM/yyyy');
                            }
                            return 'No definida';
                        },
                        'headerOptions' => ['style' => 'width: 10%'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Fecha de Afiliación',
                        'value' => function ($model) {
                            $contrato = $model->getContratos()
                                ->where(['!=', 'estatus', 'Anulado'])
                                ->orderBy(['fecha_ini' => SORT_DESC])
                                ->one();

                            if ($contrato && !empty($contrato->fecha_ini)) {
                                return Yii::$app->formatter->asDate($contrato->fecha_ini, 'dd/MM/yyyy');
                            } elseif ($contrato && !empty($contrato->created_at)) {
                                return Yii::$app->formatter->asDate($contrato->created_at, 'dd/MM/yyyy');
                            }
                            return 'No definida';
                        },
                        'headerOptions' => ['style' => 'width: 10%'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Plan',
                        'value' => function ($model) {
                            return $model->plan ? $model->plan->nombre : '';
                        },
                        'headerOptions' => ['style' => 'width: 10%'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Cuotas Pagadas',
                        'value' => function ($model) {
                            $totalPaid = \app\models\Cuotas::find()
                                ->innerJoin('contratos', 'contratos.id = cuotas.contrato_id')
                                ->where(['contratos.user_id' => $model->id])
                                ->andWhere(['!=', 'contratos.estatus', 'Anulado'])
                                ->andWhere(['cuotas.estatus' => 'pagada'])
                                ->count();
                            return $totalPaid > 0 ? $totalPaid : '0';
                        },
                        'headerOptions' => ['style' => 'width: 8%'],
                        'contentOptions' => ['style' => 'text-align: center;'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Clínica',
                        'value' => function ($model) {
                            return $model->clinica ? $model->clinica->nombre : '';
                        },
                        'headerOptions' => ['style' => 'width: 32%'],
                        'contentOptions' => ['style' => 'text-align: center;'],
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
                        <span class="badge badge-pill badge-primary"><?=
                                                                        (isset($dataProvider) && is_object($dataProvider) && method_exists($dataProvider, 'getTotalCount'))
                                                                            ? number_format($dataProvider->getTotalCount()) : '0'
                                                                        ?>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-hospital"></i> Clínicas:</strong>
                        <?php
                        $clinicaIds = [];
                        if (isset($dataProvider) && is_object($dataProvider) && method_exists($dataProvider, 'getModels')) {
                            foreach ($dataProvider->getModels() as $model) {
                                if (isset($model->clinica_id) && $model->clinica_id) {
                                    $clinicaIds[$model->clinica_id] = true;
                                }
                            }
                        }
                        $uniqueClinicas = count($clinicaIds);
                        ?>
                        <span class="badge badge-pill badge-secondary"><?= $uniqueClinicas ?></span>
                        <?php if ($hasClinicAccess): ?>
                            <small class="text-muted ms-2">
                                <i class="fas fa-lock"></i> Limitado a sus clínicas
                            </small>
                        <?php endif; ?>
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
                        <?php if ($hasClinicAccess): ?>
                            <span class="text-primary"><i class="fas fa-lock ms-2 me-1"></i>Sus datos están limitados a sus clínicas asignadas.</span>
                        <?php endif; ?>
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
    .form-control-static {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0.375rem 0.75rem;
    }
    .badge.bg-info {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
        vertical-align: middle;
    }
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #212529;
    }
');
?>