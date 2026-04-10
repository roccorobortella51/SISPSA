<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\widgets\ActiveForm;
use app\components\UserHelper;
use kartik\widgets\SwitchInput;
use kartik\select2\Select2;
use app\models\Baremo;

// Register necessary assets for delete confirmation and AJAX
\yii\web\YiiAsset::register($this);
\yii\bootstrap\BootstrapPluginAsset::register($this);

/**
 * @var yii\web\View $this
 * @var app\models\BaremoSearch $searchModel
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\RmClinica $clinica 
 * @var app\models\Baremo 
 */

if (!isset($clinica)) {
    $clinica = (object)['id' => null, 'nombre' => 'Clínica Desconocida'];
}
$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'COORDINADOR-CLINICA');
$isReadOnly = ($rol == 'COORDINADOR-CLINICA' || $rol == 'Asesor');

// --- BREADCRUMBS ---
if ($permisos == true) {
    $this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];
}
$this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];

$this->params['breadcrumbs'][] = 'BAREMOS';

$this->title = 'Gestión de Baremos de ' . Html::encode($clinica->nombre);


?>

<div class="main-container">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />

    <div class="header-section d-flex align-items-center justify-content-between">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="header-buttons-group d-flex align-items-center flex-grow-1">
            <?php if (!$isReadOnly): ?>
                <?= Html::a(
                    '<i class="fas fa-download mr-2"></i> Descargar Plantilla',
                    ['download-template', 'clinica_id' => $clinica->id],
                    [
                        'class' => 'btn-base btn-info btn-fixed-width me-2',
                        'title' => 'Descargar plantilla Excel para Baremo',
                    ]
                ) ?>

                <div class="import-container ms-2 d-inline-block">
                    <?php $form = ActiveForm::begin([
                        'action' => ['import-excel', 'clinica_id' => $clinica->id],
                        'options' => ['enctype' => 'multipart/form-data', 'class' => 'import-form'],
                        'id' => 'importForm'
                    ]); ?>
                    <div class="file-input-wrapper">
                        <?= Html::fileInput('excelFile', null, [
                            'accept' => '.xlsx,.xls',
                            'class' => 'form-control',
                            'id' => 'excelFile',
                            'required' => true,
                            'style' => 'display: none;'
                        ]) ?>
                        <button type="button" class="btn-base btn-success btn-fixed-width" id="importExcelBtn">
                            <i class="fas fa-file-excel mr-2"></i> Importar desde Excel
                        </button>
                        <div id="fileFeedback" class="file-feedback"></div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            <?php endif; ?>

            <div class="flex-grow-1"></div>
            <!-- NEW BUTTON: Download Baremos Excel -->
            <?= Html::a(
                '<i class="fas fa-file-excel mr-2"></i> Exportar Baremos',
                ['export-baremos-excel', 'clinica_id' => $clinica->id],
                [
                    'class' => 'btn-base btn-success btn-fixed-width me-2',
                    'title' => 'Descargar lista de baremos en Excel',
                    'style' => 'background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%) !important;'

                ]
            ) ?>
            <?= Html::a(
                '<span class="text-white"><i class="fas fa-undo mr-2"></i>Volver a Clínica</span>',
                ['/rm-clinica/view', 'id' => $clinica->id],
                [
                    'class' => 'btn-base btn-gray btn-fixed-width ms-5',
                    'title' => 'Volver a los detalles de la clínica',
                    'style' => 'margin-left:40px;'
                ]
            ) ?>
        </div>
    </div>

    <?php if ($permisos && !$isReadOnly) : ?>
        <div class="ms-panel ms-panel-fh border-blue">
            <div class="ms-panel-header">
                <h3 class="section-title">
                    <i class="fas fa-plus-circle mr-3 text-blue-600"></i> Agregar Nuevo Baremo a la Clínica
                </h3>
            </div>
            <div class="ms-panel-body">
                <?php $form = ActiveForm::begin([
                    'action' => ['index', 'clinica_id' => $clinica->id],
                ]); ?>
                <div class="row g-3">
                    <div class="col-md-2">
                        <?= $form->field($model, 'area_id')->widget(Select2::classname(), [
                            'data' => UserHelper::getAreaList(),
                            'options' => [
                                'placeholder' => 'Seleccione un área...',
                                'class' => 'form-control form-control-lg',
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                        ])->label('Área') ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'nombre_servicio')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Nombre del Baremo'
                        ])->label('Nombre del Servicio') ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'descripcion')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => 'Descripción del Baremo'
                        ])->label('Descripción') ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'costo')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => '0.00'
                        ])->label('Costo') ?>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($model, 'precio')->textInput([
                            'class' => 'form-control form-control-lg',
                            'placeholder' => '0.00'
                        ])->label('Precio') ?>
                    </div>
                    <div class="col-md-12 text-end mt-4">
                        <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar Baremo', ['class' => 'btn-base btn-blue']) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="ms-panel ms-panel-fh border-indigo">
        <div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-list-alt mr-3 text-indigo-600"></i> Listado de Baremos de <?= Html::encode($clinica->nombre) ?>
            </h3>
        </div>
        <div class="ms-panel-body">
            <!-- Service Statistics Section - Microsoft Style -->
            <div class="service-stats-container mb-5">
                <div class="row">
                    <?php
                    // Calculate active/inactive counts
                    $activeCount = Baremo::find()
                        ->where(['clinica_id' => $clinica->id, 'estatus' => 'Activo'])
                        ->count();
                    $inactiveCount = Baremo::find()
                        ->where(['clinica_id' => $clinica->id, 'estatus' => 'Inactivo'])
                        ->count();
                    $totalCount = $activeCount + $inactiveCount;
                    ?>

                    <!-- Active Services Card -->
                    <div class="col-md-4 mb-4">
                        <div class="ms-stat-card ms-stat-active">
                            <div class="ms-stat-header">
                                <div class="ms-stat-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="ms-stat-title">Servicios Activos</div>
                            </div>
                            <div class="ms-stat-body">
                                <div class="ms-stat-number"><?= number_format($activeCount, 0) ?></div>
                                <div class="ms-stat-percentage">
                                    <?php if ($totalCount > 0): ?>
                                        <span class="ms-stat-badge ms-stat-badge-success">
                                            <?= number_format(($activeCount / $totalCount) * 100, 1) ?>%
                                        </span>
                                        <span class="ms-stat-label">del total</span>
                                    <?php else: ?>
                                        <span class="ms-stat-badge ms-stat-badge-secondary">0%</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="ms-stat-footer">
                                <div class="ms-stat-trend">
                                    <i class="fas fa-arrow-up text-success mr-1"></i>
                                    <span class="ms-stat-trend-text">Operacional</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inactive Services Card -->
                    <div class="col-md-4 mb-4">
                        <div class="ms-stat-card ms-stat-inactive">
                            <div class="ms-stat-header">
                                <div class="ms-stat-icon">
                                    <i class="fas fa-pause-circle"></i>
                                </div>
                                <div class="ms-stat-title">Servicios Inactivos</div>
                            </div>
                            <div class="ms-stat-body">
                                <div class="ms-stat-number"><?= number_format($inactiveCount, 0) ?></div>
                                <div class="ms-stat-percentage">
                                    <?php if ($totalCount > 0): ?>
                                        <span class="ms-stat-badge ms-stat-badge-secondary">
                                            <?= number_format(($inactiveCount / $totalCount) * 100, 1) ?>%
                                        </span>
                                        <span class="ms-stat-label">del total</span>
                                    <?php else: ?>
                                        <span class="ms-stat-badge ms-stat-badge-secondary">0%</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="ms-stat-footer">
                                <div class="ms-stat-trend">
                                    <i class="fas fa-minus text-secondary mr-1"></i>
                                    <span class="ms-stat-trend-text">No disponibles</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Services Card -->
                    <div class="col-md-4 mb-4">
                        <div class="ms-stat-card ms-stat-total">
                            <div class="ms-stat-header">
                                <div class="ms-stat-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="ms-stat-title">Total de Servicios</div>
                            </div>
                            <div class="ms-stat-body">
                                <div class="ms-stat-number"><?= number_format($totalCount, 0) ?></div>
                                <div class="ms-stat-meta">
                                    <div class="ms-stat-meta-item">
                                        <i class="fas fa-layer-group mr-1 text-primary"></i>
                                        <span class="ms-stat-meta-label">
                                            <?= $dataProvider->pagination ? number_format(ceil($totalCount / $dataProvider->pagination->pageSize)) : '1' ?> páginas
                                        </span>
                                    </div>
                                    <div class="ms-stat-meta-item">
                                        <i class="fas fa-database mr-1 text-primary"></i>
                                        <span class="ms-stat-meta-label">Registros en BD</span>
                                    </div>
                                </div>
                            </div>
                            <div class="ms-stat-footer">
                                <div class="ms-stat-trend">
                                    <i class="fas fa-chart-line text-primary mr-1"></i>
                                    <span class="ms-stat-trend-text">Carga total del sistema</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Service Statistics Section -->
            <div class="table-responsive">
                <?= GridView::widget([
                    'id' => 'baremo-grid',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'layout' => "{items}{pager}",
                    'tableOptions' => [
                        'class' => 'table table-striped table-bordered table-hover'
                    ],
                    'columns' => [
                        [
                            'header' => '#',
                            'headerOptions' => [
                                'style' => 'color: white!important; width: 50px; text-align: center;',
                                'class' => 'text-center'
                            ],
                            'contentOptions' => [
                                'style' => 'text-align: center; font-weight: bold;'
                            ],
                            'value' => function ($model, $key, $index, $column) use ($dataProvider) {
                                // Calculate consecutive number based on pagination
                                $page = Yii::$app->request->get('page', 1) - 1; // Current page (0-indexed)
                                $pageSize = $dataProvider->pagination->pageSize;
                                return ($page * $pageSize) + $index + 1;
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'area_id',
                            'value' => function ($model) {
                                return $model->area ? $model->area->nombre : "";
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => UserHelper::getAreaList(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => Yii::t('app', 'Seleccione')],
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'text-center header-link'],
                            'label' => 'Área',
                        ],
                        [
                            'attribute' => 'nombre_servicio',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Búsqueda',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        [
                            'attribute' => 'descripcion',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Búsqueda',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        [
                            'attribute' => 'costo',
                            'format' => ['currency', 'USD'],
                            'contentOptions' => ['style' => 'text-align: right;'],
                            'filter' => false
                        ],
                        [
                            'attribute' => 'precio',
                            'format' => ['currency', 'USD'],
                            'contentOptions' => ['style' => 'text-align: right;'],
                            'filter' => false
                        ],
                        [
                            'label' => 'Estatus',
                            'attribute' => 'estatus',
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'text-left header-link'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 10px !important;'],
                            'value' => function ($model) use ($permisos, $isReadOnly) {
                                // If user is read-only (COORDINADOR-CLINICA or Asesor), don't show switch
                                if ($isReadOnly) {
                                    return '<span class="status-badge ' . ($model->estatus == 'Activo' ? 'active' : 'inactive') . '">' .
                                        ($model->estatus == 'Activo' ? 'Activo' : 'Inactivo') . '</span>';
                                }

                                if ($permisos) {
                                    $isActive = ($model->estatus === 'Activo' || $model->estatus === 1 || $model->estatus === true);

                                    return SwitchInput::widget([
                                        'name' => 'status_' . $model->id,
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
                                            'id' => 'status-switch-' . $model->id
                                        ],
                                        'labelOptions' => ['style' => 'font-size: 12px;'],
                                    ]);
                                } else {
                                    return '<span class="status-badge ' . ($model->estatus == 'Activo' ? 'active' : 'inactive') . '">' .
                                        ($model->estatus == 'Activo' ? 'Activo' : 'Inactivo') . '</span>';
                                }
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'],
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => 'Estatus'],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACCIONES',
                            'template' => $isReadOnly ? '<div class="d-flex justify-content-center gap-0">{view}</div>' : '<div class="d-flex justify-content-center gap-0">{view}{update}{delete}</div>',
                            'options' => ['style' => 'width:55px; min-width:55px;'],
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                            'buttons' => [
                                'view' => function ($url, $model, $key) use ($clinica) {
                                    return Html::a(
                                        '<i class="fa fa-eye"></i>',
                                        Url::to(['view', 'id' => $model->id, 'clinica_id' => $clinica->id]),
                                        [
                                            'title' => 'Detalle del baremo',
                                            'class' => 'btn-action view'
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) use ($permisos, $isReadOnly) {
                                    if ($permisos && !$isReadOnly) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>',
                                            Url::to(['update', 'id' => $model->id]),
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn-action edit'
                                            ]
                                        );
                                    }
                                    return '';
                                },
                                'delete' => function ($url, $model, $key) use ($permisos, $isReadOnly) {
                                    if ($permisos && !$isReadOnly) {
                                        return Html::a(
                                            '<i class="far fa-trash-alt"></i>',
                                            Url::to(['delete', 'id' => $model->id]),
                                            [
                                                'title' => 'Eliminar',
                                                'data-confirm' => '¿Estás seguro de que quieres eliminar este baremo?',
                                                'data-method' => 'post',
                                                'class' => 'btn-action delete',
                                                'data-id' => $model->id, // Add data-id for manual handling
                                            ]
                                        );
                                    }
                                    return '';
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Manual delete handler
    document.addEventListener('DOMContentLoaded', function() {
        // Function to handle delete
        function handleDelete(event) {
            var link = event.currentTarget;
            var url = link.getAttribute('href');
            var confirmMessage = link.getAttribute('data-confirm');

            if (confirm(confirmMessage || '¿Estás seguro de que quieres eliminar este baremo?')) {
                var csrfToken = document.getElementById('csrf-token').value;

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-Token': csrfToken,
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        credentials: 'same-origin'
                    })
                    .then(function(response) {
                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert('Error al eliminar el baremo. Por favor, intente de nuevo.');
                        }
                    })
                    .catch(function(error) {
                        console.error('Error during deletion:', error);
                        alert('Error al eliminar el baremo. Por favor, intente de nuevo.');
                    });
            }

            event.preventDefault();
            return false;
        }

        // Initialize delete handlers
        var deleteButtons = document.querySelectorAll('.delete');
        for (var i = 0; i < deleteButtons.length; i++) {
            if (!deleteButtons[i].hasAttribute('data-handler-attached')) {
                deleteButtons[i].addEventListener('click', handleDelete);
                deleteButtons[i].setAttribute('data-handler-attached', 'true');
            }
        }

        // Observer for dynamic content
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    var newDeleteButtons = document.querySelectorAll('.delete');
                    for (var i = 0; i < newDeleteButtons.length; i++) {
                        if (!newDeleteButtons[i].hasAttribute('data-handler-attached')) {
                            newDeleteButtons[i].addEventListener('click', handleDelete);
                            newDeleteButtons[i].setAttribute('data-handler-attached', 'true');
                        }
                    }
                }
            });
        });

        var tableContainer = document.querySelector('.table-responsive');
        if (tableContainer) {
            observer.observe(tableContainer, {
                childList: true,
                subtree: true
            });
        }

        if (typeof $ !== 'undefined') {
            $(document).on('pjax:end', function() {
                var newDeleteButtons = document.querySelectorAll('.delete');
                for (var i = 0; i < newDeleteButtons.length; i++) {
                    if (!newDeleteButtons[i].hasAttribute('data-handler-attached')) {
                        newDeleteButtons[i].addEventListener('click', handleDelete);
                        newDeleteButtons[i].setAttribute('data-handler-attached', 'true');
                    }
                }
            });
        }

        // ========== IMPORT FUNCTIONALITY ==========
        const importBtn = document.getElementById('importExcelBtn');
        const fileInput = document.getElementById('excelFile');
        const fileFeedback = document.getElementById('fileFeedback');
        const importForm = document.getElementById('importForm');
        const csrfToken = document.getElementById('csrf-token').value;

        if (importBtn && fileInput && fileFeedback) {
            // Create progress container
            const progressContainer = document.createElement('div');
            progressContainer.id = 'uploadProgressContainer';
            progressContainer.className = 'upload-progress-container';
            progressContainer.style.display = 'none';
            fileFeedback.appendChild(progressContainer);

            // Trigger file input when button clicked
            importBtn.addEventListener('click', function(e) {
                e.preventDefault();
                fileInput.click();
            });

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                if (this.files.length === 0) {
                    fileFeedback.innerHTML = '';
                    fileFeedback.appendChild(progressContainer);
                    return;
                }

                const file = this.files[0];
                const validTypes = ['.xlsx', '.xls'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                // Validate file type
                if (!validTypes.includes(fileExtension)) {
                    fileFeedback.innerHTML = '<div class="alert alert-danger alert-dismissible fade show mt-2"><i class="fas fa-exclamation-triangle me-2"></i>Formato no válido. Use archivos .xlsx o .xls<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                    fileFeedback.appendChild(progressContainer);
                    fileInput.value = '';
                    return;
                }

                // Validate file size (max 10MB)
                if (file.size > 10 * 1024 * 1024) {
                    fileFeedback.innerHTML = '<div class="alert alert-danger alert-dismissible fade show mt-2"><i class="fas fa-exclamation-triangle me-2"></i>El archivo es demasiado grande. Máximo 10MB<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
                    fileFeedback.appendChild(progressContainer);
                    fileInput.value = '';
                    return;
                }

                // Escape HTML helper
                function escapeHtml(str) {
                    const div = document.createElement('div');
                    div.textContent = str;
                    return div.innerHTML;
                }

                // Show confirmation UI
                fileFeedback.innerHTML = `
                <div class="file-info mt-2 p-3 border rounded bg-white shadow-sm">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <i class="fas fa-file-excel text-success fa-2x me-3"></i>
                            <strong>${escapeHtml(file.name)}</strong> 
                            <span class="text-muted">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="cancelFile">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </button>
                            <button type="button" class="btn btn-sm btn-success" id="confirmImport">
                                <i class="fas fa-upload me-1"></i>Importar
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Formato esperado: Área | Nombre Servicio | Descripción | Costo | Precio
                    </div>
                </div>
            `;
                fileFeedback.appendChild(progressContainer);
                progressContainer.style.display = 'none';

                // Cancel button handler
                const cancelBtn = document.getElementById('cancelFile');
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function() {
                        fileInput.value = '';
                        fileFeedback.innerHTML = '';
                        fileFeedback.appendChild(progressContainer);
                        progressContainer.style.display = 'none';
                    });
                }

                // Confirm import button handler
                const confirmBtn = document.getElementById('confirmImport');
                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function() {
                        performUpload(file);
                    });
                }
            });

            // Perform actual AJAX upload
            function performUpload(file) {
                progressContainer.style.display = 'block';
                progressContainer.innerHTML = `
                <div class="upload-progress">
                    <div class="upload-status">
                        <div class="upload-text">
                            <i class="fas fa-cloud-upload-alt me-2"></i>
                            <span class="upload-message">Subiendo archivo...</span>
                        </div>
                        <div class="upload-percentage">0%</div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <div class="upload-details">
                        <div class="file-name">${escapeHtml(file.name)}</div>
                        <div class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                    </div>
                </div>
            `;

                // Disable confirm button
                const confirmBtn = document.getElementById('confirmImport');
                if (confirmBtn) {
                    confirmBtn.disabled = true;
                    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Importando...';
                }

                const formData = new FormData();
                formData.append('excelFile', file);
                formData.append('_csrf', csrfToken);

                const xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        const progressFill = progressContainer.querySelector('.progress-fill');
                        const percentageEl = progressContainer.querySelector('.upload-percentage');
                        const messageEl = progressContainer.querySelector('.upload-message');

                        if (progressFill) progressFill.style.width = percent + '%';
                        if (percentageEl) percentageEl.textContent = percent + '%';

                        if (messageEl) {
                            if (percent < 30) messageEl.textContent = 'Subiendo archivo...';
                            else if (percent < 60) messageEl.textContent = 'Procesando datos...';
                            else if (percent < 90) messageEl.textContent = 'Guardando en base de datos...';
                            else messageEl.textContent = 'Finalizando importación...';
                        }
                    }
                });

                xhr.addEventListener('load', function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                const progressFill = progressContainer.querySelector('.progress-fill');
                                const percentageEl = progressContainer.querySelector('.upload-percentage');
                                const messageEl = progressContainer.querySelector('.upload-message');

                                if (progressFill) progressFill.style.width = '100%';
                                if (percentageEl) percentageEl.textContent = '100%';
                                if (messageEl) messageEl.textContent = '¡Importación completada!';

                                showNotification('success', response.message || 'Importación completada exitosamente');

                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                showNotification('error', response.message || 'Error durante la importación');
                                resetUpload();
                            }
                        } catch (e) {
                            showNotification('error', 'Error al procesar la respuesta del servidor');
                            resetUpload();
                        }
                    } else {
                        showNotification('error', 'Error en el servidor. Código: ' + xhr.status);
                        resetUpload();
                    }
                });

                xhr.addEventListener('error', function() {
                    showNotification('error', 'Error de red. Por favor, verifique su conexión e intente nuevamente.');
                    resetUpload();
                });

                xhr.timeout = 60000;
                xhr.open('POST', importForm.action);
                xhr.send(formData);
            }

            function showNotification(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

                const notification = document.createElement('div');
                notification.className = `alert ${alertClass} alert-dismissible fade show mt-3`;
                notification.role = 'alert';
                notification.innerHTML = `
                <i class="fas ${icon} me-2"></i>
                ${escapeHtml(message)}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;

                fileFeedback.appendChild(notification);

                setTimeout(function() {
                    if (notification.parentNode) {
                        $(notification).alert('close');
                    }
                }, 5000);
            }

            function resetUpload() {
                fileInput.value = '';
                setTimeout(function() {
                    progressContainer.style.display = 'none';
                    progressContainer.innerHTML = '';
                }, 2000);

                const confirmBtn = document.getElementById('confirmImport');
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-upload me-1"></i>Importar';
                }

                setTimeout(function() {
                    if (fileFeedback.children.length <= 1 || !fileFeedback.querySelector('.alert-danger, .alert-success')) {
                        fileFeedback.innerHTML = '';
                        fileFeedback.appendChild(progressContainer);
                    }
                }, 3000);
            }

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            // Drag and drop functionality
            const fileInputWrapper = document.querySelector('.file-input-wrapper');
            if (fileInputWrapper) {
                fileInputWrapper.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.borderColor = '#4f46e5';
                    this.style.borderStyle = 'dashed';
                });

                fileInputWrapper.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.style.backgroundColor = '';
                    this.style.borderColor = 'transparent';
                    this.style.borderStyle = 'dashed';
                });

                fileInputWrapper.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.style.backgroundColor = '';
                    this.style.borderColor = 'transparent';

                    if (e.dataTransfer.files.length > 0) {
                        fileInput.files = e.dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change'));
                    }
                });
            }
        }
    });

    // Status update function
    function updatestatus(id) {
        <?php if (!$isReadOnly): ?>
            $.ajax({
                url: '<?= Url::to(['updatestatus']) ?>',
                type: 'POST',
                data: {
                    id: id,
                    '_csrf': $('#csrf-token').val()
                }
            });
        <?php endif; ?>
    }
</script>

<style>
    /* Keep all existing styles - they remain unchanged */
    .upload-progress-container {
        margin-top: 20px;
        animation: fadeIn 0.3s ease-in-out;
    }

    .upload-progress {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #e1e5e9;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .upload-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .upload-text {
        display: flex;
        align-items: center;
        font-weight: 600;
        color: #323130;
        font-size: 14px;
    }

    .upload-text i {
        color: #0078d4;
        font-size: 16px;
    }

    .upload-percentage {
        font-weight: 600;
        color: #0078d4;
        font-size: 14px;
    }

    .progress-bar {
        height: 4px;
        background: #edebe9;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 12px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #0078d4 0%, #50e6ff 100%);
        border-radius: 2px;
        transition: width 0.3s ease-in-out;
        position: relative;
        overflow: hidden;
    }

    .progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background-image: linear-gradient(90deg,
                rgba(255, 255, 255, 0.1) 25%,
                transparent 25%,
                transparent 50%,
                rgba(255, 255, 255, 0.1) 50%,
                rgba(255, 255, 255, 0.1) 75%,
                transparent 75%,
                transparent);
        background-size: 20px 100%;
        animation: shimmer 1s infinite linear;
    }

    .upload-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #605e5c;
    }

    .file-name {
        font-weight: 500;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 70%;
    }

    .file-size {
        font-weight: 600;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -20px 0;
        }

        100% {
            background-position: 20px 0;
        }
    }

    .btn-fixed-width {
        min-width: 220px;
        width: 220px;
        text-align: center;
        padding: 10px 15px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        transition: all 0.3s ease;
        cursor: pointer;
        color: white;
    }

    .btn-gray {
        background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%) !important;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%) !important;
    }

    .btn-gray:hover,
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .import-container {
        position: relative;
        display: inline-block;
    }

    .file-input-wrapper {
        display: inline-block;
        position: relative;
        border: 2px dashed transparent;
        border-radius: 6px;
        padding: 5px;
        transition: all 0.3s ease;
    }

    .file-feedback {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1000;
        margin-top: 5px;
    }

    .file-info {
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: 1px solid #e1e5e9;
        border-radius: 4px;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .status-badge.active {
        background-color: #d1fae5;
        color: #065f46;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
    }

    .status-badge.inactive {
        background-color: #fee2e2;
        color: #991b1b;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.875rem;
    }

    .header-buttons-group {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .header-buttons-group {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-fixed-width {
            width: 100%;
            min-width: auto;
        }

        .import-container {
            display: block;
            margin-left: 0 !important;
            margin-top: 10px;
        }
    }

    /* Microsoft Style Statistics Cards */
    .ms-stat-card {
        background: white;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid #e1e5e9;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        font-family: 'Segoe UI', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .ms-stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
        border-color: #c7e0f4;
    }

    .ms-stat-active {
        border-top: 4px solid #107c10;
    }

    .ms-stat-inactive {
        border-top: 4px solid #605e5c;
    }

    .ms-stat-total {
        border-top: 4px solid #0078d4;
    }

    .ms-stat-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .ms-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        font-size: 20px;
    }

    .ms-stat-active .ms-stat-icon {
        background-color: #dff6dd;
        color: #107c10;
    }

    .ms-stat-inactive .ms-stat-icon {
        background-color: #f3f2f1;
        color: #605e5c;
    }

    .ms-stat-total .ms-stat-icon {
        background-color: #deecf9;
        color: #0078d4;
    }

    .ms-stat-title {
        font-size: 16px;
        font-weight: 600;
        color: #323130;
        letter-spacing: 0.3px;
    }

    .ms-stat-body {
        flex: 1;
        margin-bottom: 20px;
    }

    .ms-stat-number {
        font-size: 42px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 12px;
        color: #323130;
        font-family: 'Segoe UI Semibold', 'Segoe UI', sans-serif;
    }

    .ms-stat-active .ms-stat-number {
        color: #107c10;
    }

    .ms-stat-inactive .ms-stat-number {
        color: #605e5c;
    }

    .ms-stat-total .ms-stat-number {
        color: #0078d4;
    }

    .ms-stat-percentage {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ms-stat-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .ms-stat-badge-success {
        background-color: #dff6dd;
        color: #107c10;
    }

    .ms-stat-badge-secondary {
        background-color: #f3f2f1;
        color: #605e5c;
    }

    .ms-stat-label {
        font-size: 13px;
        color: #605e5c;
        font-weight: 500;
    }

    .ms-stat-meta {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 12px;
    }

    .ms-stat-meta-item {
        display: flex;
        align-items: center;
        font-size: 13px;
        color: #605e5c;
    }

    .ms-stat-meta-label {
        margin-left: 6px;
        font-weight: 500;
    }

    .ms-stat-footer {
        border-top: 1px solid #edebe9;
        padding-top: 16px;
    }

    .ms-stat-trend {
        display: flex;
        align-items: center;
        font-size: 13px;
        font-weight: 500;
    }

    .ms-stat-trend-text {
        color: #605e5c;
        letter-spacing: 0.2px;
    }

    @media (max-width: 992px) {
        .ms-stat-card {
            padding: 20px;
        }

        .ms-stat-number {
            font-size: 36px;
        }

        .ms-stat-icon {
            width: 44px;
            height: 44px;
            font-size: 18px;
        }
    }

    @media (max-width: 768px) {
        .ms-stat-card {
            margin-bottom: 16px;
        }

        .ms-stat-number {
            font-size: 32px;
        }

        .ms-stat-header {
            margin-bottom: 16px;
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ms-stat-card {
        animation: fadeInUp 0.5s ease-out;
    }
</style>