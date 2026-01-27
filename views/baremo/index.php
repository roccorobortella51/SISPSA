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
            <div class="flex-grow-1"></div>
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

    <?php if ($permisos) : ?>
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
                            'value' => function ($model) use ($permisos) {
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
                            'template' => '<div class="d-flex justify-content-center gap-0>{view}{update}{delete}</div>',
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
                                'update' => function ($url, $model, $key) use ($permisos) {
                                    if ($permisos) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>',
                                            Url::to(['update', 'id' => $model->id]),
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn-action edit'
                                            ]
                                        );
                                    }
                                },
                                'delete' => function ($url, $model, $key) use ($permisos) {
                                    if ($permisos) {
                                        return Html::a(
                                            '<i class="far fa-trash-alt"></i>',
                                            Url::to(['delete', 'id' => $model->id]),
                                            [
                                                'title' => 'Eliminar',
                                                'data-confirm' => '¿Estás seguro de que quieres eliminar este baremo?',
                                                'data-method' => 'post',
                                                'class' => 'btn-action delete'
                                            ]
                                        );
                                    }
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
    document.addEventListener('DOMContentLoaded', function() {
        const importBtn = document.getElementById('importExcelBtn');
        const fileInput = document.getElementById('excelFile');
        const fileFeedback = document.getElementById('fileFeedback');
        const importForm = document.getElementById('importForm');

        // Create progress bar container (initially hidden)
        const progressContainer = document.createElement('div');
        progressContainer.id = 'uploadProgressContainer';
        progressContainer.className = 'upload-progress-container';
        progressContainer.style.display = 'none';
        fileFeedback.appendChild(progressContainer);

        // When import button is clicked, trigger the hidden file input
        importBtn.addEventListener('click', function() {
            fileInput.click();
        });

        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const file = this.files[0];

                // Validate file type
                const validTypes = ['.xlsx', '.xls'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                if (!validTypes.includes(fileExtension)) {
                    fileFeedback.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Formato no válido. Use archivos .xlsx o .xls</span>';
                    return;
                }

                // Validate file size (10MB max)
                if (file.size > 10 * 1024 * 1024) {
                    fileFeedback.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>El archivo es demasiado grande. Máximo 10MB</span>';
                    return;
                }

                // Show file info and confirmation
                fileFeedback.innerHTML = `
            <div class="file-info mt-2 p-2 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-file-excel text-success me-2"></i>
                        <strong>${file.name}</strong> (${(file.size / 1024 / 1024).toFixed(2)} MB)
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="cancelFile">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="confirmImport">
                            <i class="fas fa-upload me-1"></i> Importar
                        </button>
                    </div>
                </div>
                <div class="mt-1 small text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Formato esperado: Área | Nombre Servicio | Descripción | Costo | Precio
                </div>
            </div>
        `;

                // Re-append progress container
                fileFeedback.appendChild(progressContainer);

                // Add event listeners for the new buttons
                document.getElementById('cancelFile').addEventListener('click', function() {
                    fileInput.value = '';
                    fileFeedback.innerHTML = '';
                });

                document.getElementById('confirmImport').addEventListener('click', function() {
                    startUpload(file);
                });
            } else {
                fileFeedback.innerHTML = '';
            }
        });

        // Start upload function
        function startUpload(file) {
            // Show progress container
            progressContainer.style.display = 'block';
            progressContainer.innerHTML = `
            <div class="upload-progress">
                <div class="upload-status">
                    <div class="upload-text">
                        <i class="fas fa-cloud-upload-alt me-2"></i>
                        <span class="upload-message">Procesando archivo...</span>
                    </div>
                    <div class="upload-percentage">0%</div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
                <div class="upload-details">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                </div>
            </div>
        `;

            // Disable confirm button
            const confirmBtn = document.getElementById('confirmImport');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Importando...';

            // Simulate progress for demo (replace with actual upload progress if using AJAX)
            simulateProgress();

            // Submit the form
            importForm.submit();
        }

        // Simulate progress function (remove this if implementing real upload progress)
        function simulateProgress() {
            const progressFill = progressContainer.querySelector('.progress-fill');
            const uploadPercentage = progressContainer.querySelector('.upload-percentage');
            const uploadMessage = progressContainer.querySelector('.upload-message');

            let progress = 0;
            const interval = setInterval(() => {
                if (progress >= 100) {
                    clearInterval(interval);
                    uploadMessage.textContent = 'Completado! Redirigiendo...';
                    return;
                }

                progress += 5;
                progressFill.style.width = progress + '%';
                uploadPercentage.textContent = progress + '%';

                // Update messages at different stages
                if (progress < 30) {
                    uploadMessage.textContent = 'Validando archivo...';
                } else if (progress < 60) {
                    uploadMessage.textContent = 'Procesando datos...';
                } else if (progress < 90) {
                    uploadMessage.textContent = 'Guardando en base de datos...';
                } else {
                    uploadMessage.textContent = 'Finalizando importación...';
                }
            }, 150);
        }

        // Drag and drop functionality
        const fileInputWrapper = document.querySelector('.file-input-wrapper');

        if (fileInputWrapper) {
            fileInputWrapper.addEventListener('dragover', function(e) {
                e.preventDefault();
                fileInputWrapper.style.backgroundColor = '#f8f9fa';
                fileInputWrapper.style.borderColor = '#4f46e5';
            });

            fileInputWrapper.addEventListener('dragleave', function(e) {
                e.preventDefault();
                fileInputWrapper.style.backgroundColor = '';
                fileInputWrapper.style.borderColor = '';
            });

            fileInputWrapper.addEventListener('drop', function(e) {
                e.preventDefault();
                fileInputWrapper.style.backgroundColor = '';
                fileInputWrapper.style.borderColor = '';

                if (e.dataTransfer.files.length > 0) {
                    fileInput.files = e.dataTransfer.files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        }
    });
</script>

<style>
    /* Microsoft-style Progress Bar */
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

    /* Animations */
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

    /* Success/Error States (optional) */
    .upload-progress.success .progress-fill {
        background: #107c10;
    }

    .upload-progress.error .progress-fill {
        background: #d13438;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .upload-progress {
            padding: 15px;
        }

        .upload-status {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .upload-details {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }

        .file-name {
            max-width: 100%;
        }
    }

    /* Keep existing styles for buttons */
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

    .btn-gray:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .import-container {
        position: relative;
        display: inline-block;
    }

    .file-input-wrapper {
        display: inline-block;
        position: relative;
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

    .file-input-wrapper {
        border: 2px dashed transparent;
        border-radius: 6px;
        padding: 5px;
        transition: all 0.3s ease;
    }

    .file-input-wrapper.dragover {
        border-color: #4f46e5;
        background-color: #f8fafc;
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

    /* Service Counter Styles */
    .service-counter-card {
        border-left: 4px solid #4e73df !important;
        border-radius: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .service-counter-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    }

    .service-counter-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .text-xs {
        font-size: 0.8rem;
    }

    .h5 {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .text-sm {
        font-size: 0.875rem;
    }

    /* If you want to show Active/Inactive counts as well, you can add this: */
    .service-stats-container {
        margin-bottom: 20px;
    }

    .service-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e1e5e9;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 1.2rem;
    }

    .stat-icon.active {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    }

    .stat-icon.inactive {
        background: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
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

    /* Responsive Design */
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

    /* Animation for number counting (optional) */
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

    .ms-stat-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .ms-stat-card:nth-child(3) {
        animation-delay: 0.2s;
    }
</style>