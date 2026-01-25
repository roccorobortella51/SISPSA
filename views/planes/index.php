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
 * @var app\models\PlanSearch $searchModel // Modelo de búsqueda para planes
 * @var yii\data\ActiveDataProvider $dataProvider // Proveedor de datos para planes
 * @var app\models\RmClinica $clinica // Se asume que el modelo de clínica se pasa a la vista
 * @var app\models\Plan $model // Modelo para el formulario de agregar plan
 */

if (!isset($clinica)) {
    $clinica_id = Yii::$app->request->get('clinica_id');
    if (!empty($clinica_id)) {
        $clinica = \app\models\RmClinica::findOne((int)$clinica_id);
        if (!$clinica) {
            $clinica = (object)['id' => (int)$clinica_id, 'nombre' => 'Clínica Desconocida'];
        }
    } else {
        $clinica = (object)['id' => null, 'nombre' => 'Clínica Desconocida'];
    }
}

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'COORDINADOR-CLINICA');

if ($permisos == true) {
    $this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];
}
if ($clinica->id !== null) {
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
}
$this->params['breadcrumbs'][] = 'PLANES';

$this->title = 'Gestión de Planes de ' . Html::encode($clinica->nombre);

// DEFINE URLS FOR JAVASCRIPT
$importUrl = Url::to(['planes/import']);
$importStatusUrl = Url::to(['planes/import-status']);

// Register URLs as JS variables
$this->registerJs("const IMPORT_URL = '{$importUrl}';", \yii\web\View::POS_HEAD);
$this->registerJs("const IMPORT_STATUS_URL = '{$importStatusUrl}';", \yii\web\View::POS_HEAD);

?>

<div class="main-container">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />

    <div class="alert alert-danger alert-dismissible fade show" id="import-error-alert" style="display: none;">
        <h5><i class="fas fa-exclamation-triangle mr-2"></i> Error en Importación</h5>
        <div id="error-main-message" class="font-weight-bold mb-2"></div>
        <div id="error-detailed-message" class="small text-muted"></div>
        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="copyErrorToClipboard()">
                <i class="fas fa-copy mr-1"></i> Copiar Error
            </button>
            <button type="button" class="btn btn-sm btn-outline-info ml-2" onclick="showTechnicalDetails()">
                <i class="fas fa-info-circle mr-1"></i> Detalles Técnicos
            </button>
        </div>
        <div id="technical-details" class="mt-2 small" style="display: none;">
            <pre id="error-stack-trace" class="bg-light p-2 rounded"></pre>
        </div>
    </div>

    <div class="header-section d-flex align-items-center justify-content-between">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="header-buttons-group d-flex align-items-center flex-grow-1">
            <?php if ($permisos) : ?>
                <?= Html::a(
                    '<i class="fas fa-plus mr-2"></i> AGREGAR PLAN',
                    ['create', 'clinica_id' => $clinica->id],
                    ['class' => 'btn btn-primary btn-sm me-2']
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-download mr-2"></i> Descargar Plantilla',
                    ['download-template', 'clinica_id' => $clinica->id],
                    [
                        'class' => 'btn btn-info btn-sm me-2',
                        'title' => 'Descargar plantilla Excel para Carga Masiva de Planes y Coberturas',
                    ]
                ) ?>

                <?= Html::button(
                    '<i class="fas fa-upload mr-2"></i> IMPORTAR PLANES',
                    [
                        'class' => 'btn btn-success me-2',
                        'id' => 'import-plans-btn',
                    ]
                ) ?>
            <?php endif; ?>
            <div class="flex-grow-1"></div>
            <?php if ($clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver',
                    ['/rm-clinica/view', 'id' => $clinica->id],
                    [
                        'class' => 'btn btn-secondary btn-sm ms-5',
                        'title' => 'Volver a los detalles de la clínica',
                        'style' => 'margin-left:40px;'
                    ]
                ) ?>

            <?php endif; ?>
        </div>
    </div>

    <!-- SIMPLE, BULLETPROOF MODAL -->
    <div id="importModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999999;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 25px; border-radius: 8px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #e0e0e0; padding-bottom: 15px;">
                <h3 style="margin: 0; color: #333; font-size: 22px;">
                    <i class="fas fa-file-excel" style="color: #28a745; margin-right: 10px;"></i>
                    Importar Planes desde Excel
                </h3>
                <button type="button" onclick="hideImportModal()" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>

            <?php $form = ActiveForm::begin([
                'id' => 'import-form',
                'action' => ['planes/import'],
                'options' => ['enctype' => 'multipart/form-data'],
            ]); ?>

            <div style="margin-bottom: 25px;">
                <!-- Instructions Box -->
                <div style="background: #e8f4fd; border-left: 4px solid #2196F3; padding: 18px; border-radius: 4px; margin-bottom: 25px; font-size: 14px; line-height: 1.6;">
                    <div style="display: flex; align-items: flex-start; margin-bottom: 10px;">
                        <i class="fas fa-info-circle" style="color: #2196F3; font-size: 18px; margin-right: 10px; margin-top: 2px;"></i>
                        <div>
                            <strong style="color: #0c5460; font-size: 15px;">Instrucciones importantes:</strong>
                        </div>
                    </div>
                    <div style="margin-left: 28px;">
                        <div style="margin-bottom: 6px;">✓ El archivo Excel debe tener una hoja principal llamada <strong>"Plans"</strong>.</div>
                        <div style="margin-bottom: 6px;">✓ Para cada plan listado en la hoja "Plans" (ej. "Bronce"), debe existir una hoja con el <strong>mismo nombre exacto</strong> ("Bronce") que contenga sus servicios.</div>
                        <div style="margin-bottom: 6px;">✓ Asegúrese de que el formato de datos sea correcto.</div>
                        <div>✓ El archivo debe estar en formato .xlsx o .xls</div>
                    </div>
                </div>

                <!-- File Selection Section -->
                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; color: #495057; margin-bottom: 12px; font-size: 16px;">
                        <i class="fas fa-file-upload" style="color: #6c757d; margin-right: 8px;"></i>
                        Seleccionar archivo Excel
                    </label>

                    <!-- File Input with Custom Styling -->
                    <div style="border: 2px dashed #ced4da; border-radius: 6px; padding: 25px; text-align: center; background: #f8f9fa; margin-bottom: 12px; transition: all 0.3s;">
                        <div style="margin-bottom: 15px;">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #6c757d; margin-bottom: 10px;"></i>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <div style="display: inline-block; position: relative; overflow: hidden;">
                                <button type="button" style="background: #28a745; color: white; border: none; padding: 12px 24px; border-radius: 4px; font-weight: 500; font-size: 15px; cursor: pointer; transition: background 0.3s;"
                                    onmouseover="this.style.background='#218838'"
                                    onmouseout="this.style.background='#28a745'">
                                    <i class="fas fa-folder-open mr-2"></i> Buscar archivo
                                </button>
                                <?= Html::fileInput('excelFile', null, [
                                    'class' => 'form-control',
                                    'accept' => '.xlsx,.xls',
                                    'required' => true,
                                    'id' => 'excel-file',
                                    'style' => 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;'
                                ]) ?>
                            </div>
                        </div>

                        <div style="color: #6c757d; font-size: 14px; margin-bottom: 5px;">
                            Arrastra y suelta tu archivo aquí o haz clic para seleccionarlo
                        </div>

                        <div id="selected-file-name" style="display: none; margin-top: 15px; padding: 10px; background: #e9ecef; border-radius: 4px; font-size: 14px;">
                            <i class="fas fa-file-excel text-success mr-2"></i>
                            <span id="file-name-text"></span>
                            <button type="button" onclick="clearFileSelection()" style="background: none; border: none; color: #dc3545; margin-left: 10px; cursor: pointer;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- File Info -->
                    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; padding: 12px 15px; font-size: 13px; color: #6c757d;">
                        <div style="display: flex; align-items: center; margin-bottom: 5px;">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <span>Formatos soportados: <strong>.xlsx, .xls</strong></span>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <i class="fas fa-database text-info mr-2"></i>
                            <span>Tamaño máximo permitido: <strong>10MB</strong></span>
                        </div>
                    </div>
                </div>

                <div>
                    <?= Html::hiddenInput('clinica_id', $clinica->id) ?>
                </div>

                <!-- Enhanced Progress Section -->
                <div class="import-progress" style="display: none; margin-top: 25px; padding: 20px; background: #f8f9fa; border-radius: 6px; border: 1px solid #e9ecef;">
                    <div style="margin-bottom: 20px;">
                        <div style="height: 25px; background: #e9ecef; border-radius: 12px; overflow: hidden; position: relative;">
                            <div class="progress-bar" style="height: 100%; background: linear-gradient(90deg, #dc3545, #ffc107, #28a745); width: 0%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; transition: width 0.5s ease-in-out; font-size: 12px;">0%</div>
                        </div>
                    </div>
                    <div>
                        <div class="progress-text" style="text-align: center; font-weight: 600; color: #495057; margin-bottom: 10px; font-size: 16px;">Preparando importación...</div>
                        <div style="font-size: 13px; color: #6c757d; text-align: center; line-height: 1.5;">
                            <div id="progress-details-text">Esperando archivo...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e0e0e0; padding-top: 20px;">
                <button type="button" onclick="hideImportModal()"
                    style="padding: 10px 25px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; font-size: 15px; transition: background 0.3s;"
                    onmouseover="this.style.background='#5a6268'"
                    onmouseout="this.style.background='#6c757d'">
                    <i class="fas fa-times mr-2"></i> Cancelar
                </button>
                <?= Html::submitButton('<i class="fas fa-upload mr-2"></i> Iniciar Importación', [
                    'class' => 'btn btn-success',
                    'id' => 'submit-import',
                    'style' => 'padding: 10px 25px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; font-size: 15px; transition: background 0.3s;'
                ]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="ms-panel ms-panel-fh border-indigo">
        <div class="ms-panel-header">
            <h3 class="section-title text-start" style="text-align:left;">
                <i class="fas fa-list-alt mr-3 text-indigo-600"></i> Listado de Planes de <?= Html::encode($clinica->nombre) ?>
            </h3>
        </div>
        <div class="ms-panel-body">
            <div class="table-responsive">
                <?= GridView::widget([
                    'id' => 'planes-grid',
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'layout' => "{items}\n{pager}",
                    'tableOptions' => [
                        'class' => 'table table-striped table-bordered table-hover'
                    ],
                    'options' => [
                        'class' => 'grid-view-container table-responsive',
                    ],
                    'columns' => [
                        // Columna para el nombre del plan
                        [
                            'attribute' => 'nombre',
                            'label' => 'Nombre del Plan',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Búsqueda',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        // Columna para la descripción
                        [
                            'attribute' => 'descripcion',
                            'format' => 'ntext',
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'filterInputOptions' => [
                                'placeholder' => 'Búsqueda',
                                'class' => 'form-control text-center',
                            ],
                        ],
                        // Cobertura
                        [
                            'attribute' => 'cobertura',
                            'format' => ['currency', 'USD'],
                            'contentOptions' => ['style' => 'text-align: right;'],
                            'filter' => false
                        ],
                        // Precio
                        [
                            'attribute' => 'precio',
                            'format' => ['currency', 'USD'],
                            'contentOptions' => ['style' => 'text-align: right;'],
                            'filter' => false
                        ],
                        // Comisión
                        [
                            'attribute' => 'comision',
                            // Divide the attribute value by 100 before applying the format
                            'value' => function ($model) {
                                return $model->comision / 100;
                            },
                            'format' => ['percent', 2],
                            'contentOptions' => ['style' => 'text-align: center;'],
                            'filter' => false
                        ],
                        // Edades
                        [
                            'attribute' => 'edad_minima',
                            'contentOptions' => ['class' => 'text-center'],
                            'label' => Yii::t('app', 'Edades'),
                            'value' => function ($model) {
                                return $model->edad_minima . "-" . $model->edad_limite . " años";
                            },
                            'headerOptions' => ['class' => 'text-left header-link'],
                        ],
                        // NEW COLUMN: Services Included
                        [
                            'label' => 'Servicios Incluidos',
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'text-center', 'style' => 'color: white!important;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'value' => function ($model) use ($clinica) {
                                // Count services included in this plan
                                $includedCount = \app\models\PlanesItemsCobertura::find()
                                    ->where(['plan_id' => $model->id])
                                    ->count();

                                // Create a badge with the count
                                $badgeClass = $includedCount > 0 ? 'badge-success' : 'badge-warning';
                                $badge = '<span class="badge ' . $badgeClass . ' badge-pill" style="font-size: 12px; padding: 5px 10px;">' . $includedCount . '</span>';

                                // Always link to view page to see/manage services
                                return Html::a($badge, ['view', 'id' => $model->id, 'clinica_id' => $clinica->id], [
                                    'title' => 'Ver y gestionar servicios del plan',
                                    'style' => 'text-decoration: none;',
                                    'data-toggle' => 'tooltip',
                                    'data-placement' => 'top'
                                ]);
                            },
                            'filter' => false,
                            'enableSorting' => false,
                        ],
                        // NEW COLUMN: Available Services
                        [
                            'label' => 'Servicios Disponibles',
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'text-center', 'style' => 'color: white!important;'],
                            'contentOptions' => ['class' => 'text-center'],
                            'value' => function ($model) use ($clinica) {
                                // Count total active baremos for this clinic
                                $totalBaremos = \app\models\Baremo::find()
                                    ->where(['clinica_id' => $model->clinica_id, 'estatus' => 'Activo'])
                                    ->count();

                                // Count services already included
                                $includedCount = \app\models\PlanesItemsCobertura::find()
                                    ->where(['plan_id' => $model->id])
                                    ->count();

                                // Calculate available services (total - included)
                                $availableCount = $totalBaremos - $includedCount;

                                // Create badge - always positive or zero
                                $availableCount = max(0, $availableCount);
                                $badgeClass = $availableCount > 0 ? 'badge-info' : 'badge-secondary';
                                $badge = '<span class="badge ' . $badgeClass . ' badge-pill" style="font-size: 12px; padding: 5px 10px;">' . $availableCount . '</span>';

                                // Link to view page where user can manually add services
                                return Html::a($badge, ['view', 'id' => $model->id, 'clinica_id' => $clinica->id], [
                                    'title' => 'Ver servicios disponibles y agregar nuevos',
                                    'style' => 'text-decoration: none;',
                                    'data-toggle' => 'tooltip',
                                    'data-placement' => 'top'
                                ]);
                            },
                            'filter' => false,
                            'enableSorting' => false,
                        ],
                        // Estatus
                        [
                            'label' => 'Estatus',
                            'attribute' => 'estatus',
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'text-center header-link'],
                            'contentOptions' => ['class' => 'text-center'],
                            'value' => function ($model) {
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
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'],
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => 'Estatus'],
                        ],
                        // Columna de Acciones
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACCIONES',
                            'template' => '<div class="d-flex justify-content-center gap-3">{view}{update}</div>',
                            'options' => ['style' => 'width:90px; min-width:90px;'],
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 8px !important;'],
                            'buttons' => [
                                'view' => function ($url, $model, $key) use ($clinica) {
                                    return Html::a(
                                        '<i class="fa fa-eye"></i>',
                                        Url::to(['view', 'id' => $model->id, 'clinica_id' => $clinica->id]),
                                        [
                                            'title' => 'Detalles del Plan',
                                            'class' => 'btn btn-xs btn-info px-2 py-0 me-2',
                                            'style' => 'font-weight:500; font-size:10px; margin-right: 8px !important; min-height: 24px; line-height: 1;'
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) use ($permisos, $clinica) {
                                    if ($permisos == true) {
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>',
                                            Url::to(['update', 'id' => $model->id, 'clinica_id' => $clinica->id]),
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn btn-xs btn-warning px-2 py-0 ms-2',
                                                'style' => 'font-weight:500; font-size:10px; margin-left: 8px !important; min-height: 24px; line-height: 1;'
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

<?php
// SIMPLE, BULLETPROOF JAVASCRIPT
$bulletproofJs = <<<JS
// ============================================
// BULLETPROOF MODAL FUNCTIONS
// ============================================

// Global functions accessible from anywhere
window.showImportModal = function() {
    console.log('BULLETPROOF: Showing modal');
    
    // Get modal element
    var modal = document.getElementById('importModal');
    
    if (!modal) {
        console.error('BULLETPROOF: Modal element not found!');
        alert('Error: Modal not found. Please refresh the page.');
        return;
    }
    
    // Show modal with maximum z-index and important styles
    modal.style.display = 'block';
    modal.style.zIndex = '999999';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.backgroundColor = 'rgba(0,0,0,0.7)';
    
    // Also add a class for additional styling
    modal.classList.add('active');
    
    // Prevent body scrolling
    document.body.style.overflow = 'hidden';
    
    console.log('BULLETPROOF: Modal should now be visible');
    
    // Debug: Check if modal is in viewport
    var rect = modal.getBoundingClientRect();
    console.log('Modal position:', rect.top, rect.left, rect.width, rect.height);
}

window.hideImportModal = function() {
    console.log('BULLETPROOF: Hiding modal');
    
    var modal = document.getElementById('importModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
    
    // Restore body scrolling
    document.body.style.overflow = '';
}

// File selection display handling
function updateFileDisplay() {
    var fileInput = document.getElementById('excel-file');
    var fileNameDisplay = document.getElementById('selected-file-name');
    var fileNameText = document.getElementById('file-name-text');
    
    if (fileInput.files.length > 0) {
        var file = fileInput.files[0];
        var fileSize = (file.size / (1024 * 1024)).toFixed(2); // Convert to MB
        
        fileNameText.textContent = file.name + ' (' + fileSize + ' MB)';
        fileNameDisplay.style.display = 'block';
        
        // Style based on file size
        if (file.size > 10 * 1024 * 1024) {
            fileNameDisplay.style.background = '#f8d7da';
            fileNameDisplay.style.border = '1px solid #f5c6cb';
            fileNameText.innerHTML = '<span style="color: #721c24;">' + file.name + ' (' + fileSize + ' MB) - <strong>Archivo demasiado grande!</strong></span>';
        } else {
            fileNameDisplay.style.background = '#d4edda';
            fileNameDisplay.style.border = '1px solid #c3e6cb';
            fileNameText.innerHTML = '<span style="color: #155724;">' + file.name + ' (' + fileSize + ' MB) - <strong>Archivo válido</strong></span>';
        }
    } else {
        fileNameDisplay.style.display = 'none';
    }
}

function clearFileSelection() {
    var fileInput = document.getElementById('excel-file');
    fileInput.value = '';
    updateFileDisplay();
}

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('BULLETPROOF: DOM loaded, initializing...');
    
    // Get button and modal for debugging
    var importBtn = document.getElementById('import-plans-btn');
    var modal = document.getElementById('importModal');
    
    console.log('Import button found:', !!importBtn);
    console.log('Modal found:', !!modal);
    
    // Add click event to import button
    if (importBtn) {
        importBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('BULLETPROOF: Button clicked via event listener');
            showImportModal();
        });
    }
    
    // Also add event listener to close button
    var closeBtn = modal ? modal.querySelector('.close') || modal.querySelector('button[onclick*="hideImportModal"]') : null;
    if (closeBtn) {
        closeBtn.addEventListener('click', hideImportModal);
    }
    
    // Close modal when clicking on backdrop (outside modal content)
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideImportModal();
            }
        });
    }
    
    // Initialize file input event listeners
    var fileInput = document.getElementById('excel-file');
    if (fileInput) {
        fileInput.addEventListener('change', updateFileDisplay);
        
        // Also handle drag and drop
        var fileDropZone = fileInput.parentElement.parentElement.parentElement;
        
        fileDropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.borderColor = '#28a745';
            this.style.background = '#e9f7ef';
        });
        
        fileDropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.borderColor = '#ced4da';
            this.style.background = '#f8f9fa';
        });
        
        fileDropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.borderColor = '#ced4da';
            this.style.background = '#f8f9fa';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFileDisplay();
            }
        });
    }
    
    // Update submit button text based on file selection
    var submitBtn = document.getElementById('submit-import');
    if (submitBtn) {
        var originalText = submitBtn.innerHTML;
        
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    submitBtn.innerHTML = '<i class="fas fa-upload mr-2"></i> Importar "' + this.files[0].name + '"';
                } else {
                    submitBtn.innerHTML = originalText;
                }
            });
        }
    }
    
    // Initialize tooltips for service badges
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    console.log('BULLETPROOF: Initialization complete');
});

// ============================================
// DEBUG FUNCTION - TEST MODAL VISIBILITY
// ============================================

window.debugModal = function() {
    var modal = document.getElementById('importModal');
    if (!modal) {
        console.error('DEBUG: Modal not found in DOM');
        return;
    }
    
    console.log('=== MODAL DEBUG INFO ===');
    console.log('Modal exists:', true);
    console.log('Modal display:', modal.style.display);
    console.log('Modal computed display:', window.getComputedStyle(modal).display);
    
    var rect = modal.getBoundingClientRect();
    console.log('Modal bounds:', rect.top, rect.left, rect.width, rect.height);
    
    // Try to force show it
    console.log('DEBUG: Forcing modal to show...');
    modal.style.display = 'block';
    modal.style.zIndex = '999999';
    modal.style.position = 'fixed';
    modal.style.top = '50px';
    modal.style.left = '50px';
    modal.style.width = '500px';
    modal.style.height = '300px';
    modal.style.backgroundColor = 'red';
    modal.style.color = 'white';
    modal.style.padding = '20px';
    modal.innerHTML = '<h2>DEBUG MODAL - CAN YOU SEE THIS?</h2><p>If you can see this red box, then the modal element works.</p><button onclick="this.parentElement.style.display=\'none\'">Close</button>';
    
    alert('Debug modal activated. Check console and look for a red box on screen.');
};

// Make debug function available immediately
console.log('BULLETPROOF: Modal functions loaded');
console.log('showImportModal available:', typeof showImportModal === 'function');
console.log('hideImportModal available:', typeof hideImportModal === 'function');
console.log('debugModal available:', typeof debugModal === 'function');

// ============================================
// QUICK TEST - Add debug button
// ============================================

/ Create debug button dynamically
/*setTimeout(function() {
    var debugBtn = document.createElement('button');
   // debugBtn.innerHTML = 'DEBUG MODAL';
    debugBtn.style.position = 'fixed';
    debugBtn.style.bottom = '10px';
    debugBtn.style.right = '10px';
    debugBtn.style.zIndex = '1000000';
    debugBtn.style.backgroundColor = 'red';
    debugBtn.style.color = 'white';
    debugBtn.style.padding = '10px';
    debugBtn.style.border = 'none';
    debugBtn.style.borderRadius = '5px';
    debugBtn.style.cursor = 'pointer';
    debugBtn.onclick = debugModal;
    document.body.appendChild(debugBtn);
    console.log('BULLETPROOF: Debug button added to page');
}, 1000);*/
JS;

$this->registerJs($bulletproofJs, \yii\web\View::POS_END);

$importJs = <<<JS

let progressPoller = null;
let currentTaskId = null;
let importCompleted = false;

// Function to poll for import progress
function pollProgress(taskId) {
    if (importCompleted) {
        console.log('Import already completed, stopping polling');
        return;
    }
    
    $.ajax({
        url: IMPORT_STATUS_URL,
        type: 'GET',
        data: { taskId: taskId },
        success: function(response) {
            if (!response) {
                console.warn('No response received from progress polling');
                return;
            }

            console.log('Progress update:', response.progress + '%', response.message);
            
            const progressBar = $('.progress-bar');
            const progressText = $('.progress-text');
            const progressDetails = $('#progress-details-text');
            
            // Update progress bar and text
            progressBar.css('width', response.progress + '%').text(response.progress + '%');
            progressText.text(response.message);
            
            // Update progress details if available
            if (response.details) {
                let detailsHtml = '';
                if (response.details.plans_total > 0) {
                    detailsHtml += `Planes: \${response.details.plans_processed}/\${response.details.plans_total}`;
                }
                if (response.details.services_total > 0) {
                    if (detailsHtml) detailsHtml += ' | ';
                    detailsHtml += `Servicios: \${response.details.services_processed}/\${response.details.services_total}`;
                }
                if (response.details.current_plan) {
                    detailsHtml += `<br>Procesando: \${response.details.current_plan}`;
                }
                progressDetails.html(detailsHtml);
            }
            
            // Change progress bar color based on progress
            if (response.progress < 30) {
                progressBar.css('background', '#dc3545');
            } else if (response.progress < 70) {
                progressBar.css('background', '#ffc107');
            } else {
                progressBar.css('background', '#28a745');
            }

            // Check if the process is finished
            if (response.finished) {
                console.log('Import finished, stopping polling. Result:', response.result);
                clearProgressPolling();
                handleImportResult(response.result);
            }
        },
        error: function(xhr, status, error) {
            console.error('Progress polling error:', status, error);
            clearProgressPolling();
            showImportError('Error de conexión', 'No se pudo obtener el estado de la importación del servidor.');
            resetImportForm();
        }
    });
}

function clearProgressPolling() {
    if (progressPoller) {
        clearInterval(progressPoller);
        progressPoller = null;
    }
    importCompleted = true;
}

function handleImportResult(result) {
    console.log('Handling import result:', result);
    const submitBtn = $('#submit-import');
    
    if (result && result.success) {
        // Show 100% progress
        $('.progress-bar').css('width', '100%').text('100%').css('background', '#28a745');
        $('.progress-text').text('¡Importación completada exitosamente!');
        
        // Stop any animation and show final state
        $('.progress-bar').css('transition', 'none');
        
        // Small delay before showing success message
        setTimeout(function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Importación Exitosa!',
                    html: result.message || 'La importación se completó correctamente.',
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    hideImportModal();
                    location.reload();
                });
            } else {
                alert('¡Importación exitosa! ' + (result.message || 'La importación se completó correctamente.'));
                hideImportModal();
                location.reload();
            }
        }, 500);
    } else {
        // Handle failure
        const errorMessage = result ? result.message : 'Error desconocido durante la importación.';
        const detailedError = result ? result.detailed_error : 'No hay detalles disponibles.';
        showImportError(errorMessage, detailedError);
        resetImportForm();
    }
}

function resetImportForm() {
    console.log('Resetting import form');
    $('#submit-import').prop('disabled', false).html('<i class="fas fa-upload mr-2"></i> Iniciar Importación');
    $('.import-progress').hide();
    $('.progress-bar').css('width', '0%').text('').css('background', '#dc3545').css('transition', 'width 0.5s ease-in-out');
    $('#progress-details-text').empty();
    clearProgressPolling();
    importCompleted = false;
}

// Main import form submission handler
$('#import-form').on('beforeSubmit', function(e) {
    e.preventDefault();
    
    console.log('Starting import process...');
    
    var formData = new FormData(this);
    var submitBtn = $('#submit-import');
    
    // Reset states
    importCompleted = false;
    currentTaskId = null;
    
    // Hide previous errors and show loading state
    $('#import-error-alert').hide();
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...');
    $('.import-progress').show();
    
    // Show immediate progress feedback
    $('.progress-bar').css('width', '5%').text('5%').css('background', '#dc3545');
    $('.progress-text').text('Iniciando importación...');
    $('#progress-details-text').html('Preparando archivo...');
    
    // This initial AJAX call starts the process and gets a task ID
    $.ajax({
        url: IMPORT_URL,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        timeout: 30000, // 30 second timeout
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            
            // Track upload progress
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = (evt.loaded / evt.total) * 100;
                    // Update progress during upload (0-10%)
                    var uploadProgress = Math.min(10, percentComplete * 0.1);
                    $('.progress-bar').css('width', uploadProgress + '%').text(Math.round(uploadProgress) + '%');
                    $('.progress-text').text('Subiendo archivo... ' + Math.round(percentComplete) + '%');
                }
            }, false);
            
            return xhr;
        },
        success: function(response) {
            console.log('Import initiated successfully:', response);
            
            if (response.success && response.taskId) {
                // Successfully started, now begin polling for status
                currentTaskId = response.taskId;
                $('.progress-text').text('Archivo subido. Procesando...');
                $('.progress-bar').css('width', '10%').text('10%');
                
                // Start polling for progress
                progressPoller = setInterval(function() {
                    if (currentTaskId && !importCompleted) {
                        pollProgress(currentTaskId);
                    }
                }, 1500); // Poll every 1.5 seconds
            } else {
                // Failed to start the import process
                showImportError(response.message || 'No se pudo iniciar la importación.', 'El servidor no proporcionó un ID de tarea.');
                resetImportForm();
            }
        },
        error: function(xhr, status, error) {
            console.error('Import initiation error:', status, error);
            var errorMessage = 'Error al iniciar la importación.';
            if (xhr.status === 0) {
                errorMessage = 'Error de conexión. Verifique su conexión a internet.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showImportError(errorMessage, xhr.responseText || error);
            resetImportForm();
        }
    });
    
    return false;
});

function showImportError(mainMessage, detailedMessage) {
    console.error('Import error:', mainMessage, detailedMessage);
    
    $('#error-main-message').text(mainMessage);
    $('#error-detailed-message').text(detailedMessage || 'No hay detalles adicionales disponibles.');
    $('#error-stack-trace').text(detailedMessage || 'No hay detalles técnicos disponibles.');
    $('#import-error-alert').show();
    
    // Scroll to error inside modal
    var modalBody = $('#importModal .modal-body');
    if (modalBody.length) {
        modalBody.animate({
            scrollTop: modalBody.scrollTop() + $('#import-error-alert').position().top - 20
        }, 500);
    }
    
    // Reset form
    resetImportForm();
}

function copyErrorToClipboard() {
    var errorText = 'Error: ' + $('#error-main-message').text() + '\\n' +
                   'Detalles: ' + $('#error-detailed-message').text() + '\\n' +
                   'Stack Trace: ' + $('#error-stack-trace').text();
    
    navigator.clipboard.writeText(errorText).then(function() {
        // Show copied feedback
        var btn = event.target;
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Copiado';
        setTimeout(function() {
            btn.innerHTML = originalText;
        }, 2000);
    });
}

function showTechnicalDetails() {
    $('#technical-details').toggle();
}

// File input change event to validate file type
$('#excel-file').on('change', function() {
    var file = this.files[0];
    if (file) {
        var fileName = file.name;
        var fileExtension = fileName.split('.').pop().toLowerCase();
        var validExtensions = ['xlsx', 'xls'];
        
        if (!validExtensions.includes(fileExtension)) {
            alert('❌ Error: Por favor seleccione un archivo Excel válido (.xlsx o .xls)\\n\\nArchivos aceptados:\\n• .xlsx (Excel 2007 y superior)\\n• .xls (Excel 97-2003)');
            $(this).val('');
            updateFileDisplay();
            return false;
        }
        
        if (file.size > 10 * 1024 * 1024) {
            alert('❌ Error: El archivo es demasiado grande.\\n\\nTamaño máximo permitido: 10MB\\nTamaño del archivo: ' + (file.size / (1024 * 1024)).toFixed(2) + 'MB');
            $(this).val('');
            updateFileDisplay();
            return false;
        }
        
        // Show success message
        var fileNameDisplay = document.getElementById('selected-file-name');
        if (fileNameDisplay) {
            fileNameDisplay.style.background = '#d4edda';
            fileNameDisplay.style.border = '1px solid #c3e6cb';
        }
    }
    updateFileDisplay();
});

// Function to update plan status
function updatestatus(planId) {
    var isActive = $('#status-switch-' + planId).is(':checked');
    var status = isActive ? 'Activo' : 'Inactivo';
    
    $.post('/planes/update-status', {
        id: planId,
        status: status,
        _csrf: $('#csrf-token').val()
    }, function(response) {
        // No need for extensive handling unless you want to revert on failure
    });
}

// Reset form when modal is closed
$('#importModal').on('hide.bs.modal', function() {
    resetImportForm();
});

// Also reset when our custom hide function is called
window.hideImportModal = function() {
    console.log('Hiding modal and resetting form');
    resetImportForm();
    
    var modal = document.getElementById('importModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
    }
    
    // Restore body scrolling
    document.body.style.overflow = '';
}

// Initialize
$(document).ready(function() {
    console.log('Import functionality loaded');
    
    // Initialize Bootstrap tooltips if available
    if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Add safety check to stop polling if page is left
    $(window).on('beforeunload', function() {
        clearProgressPolling();
    });
});
JS;

$this->registerJs($importJs, \yii\web\View::POS_END);
?>

<?php
// BULLETPROOF CSS
$bulletproofCss = <<<CSS
/* BULLETPROOT MODAL STYLES - Override everything */
#importModal {
    display: none !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: rgba(0, 0, 0, 0.7) !important;
    z-index: 999999 !important;
}

#importModal.active {
    display: block !important;
}

/* Modal content */
#importModal > div {
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    background: white !important;
    padding: 25px !important;
    border-radius: 8px !important;
    width: 90% !important;
    max-width: 700px !important;
    max-height: 90vh !important;
    overflow-y: auto !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
}

/* Make sure nothing hides our modal */
body.modal-open {
    overflow: hidden !important;
}

/* Override any other modal styles */
.modal.fade {
    display: none !important;
}

/* Style for the new service badges */
.badge-pill {
    border-radius: 10rem;
    min-width: 50px;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.badge-pill:hover {
    transform: scale(1.05);
    opacity: 0.9;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Badge colors for services */
.badge-success {
    background-color: #28a745 !important;
}

.badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.badge-info {
    background-color: #17a2b8 !important;
}

.badge-secondary {
    background-color: #6c757d !important;
}

/* Tooltip styles */
.tooltip-inner {
    max-width: 300px;
    padding: 8px 12px;
    text-align: center;
    border-radius: 4px;
    font-size: 13px;
}

/* File upload area styles */
.file-upload-area {
    transition: all 0.3s ease;
}

.file-upload-area:hover {
    border-color: #28a745 !important;
    background: #f1f8ff !important;
}

/* Make buttons twice smaller and ensure they stay small */
.btn-xs {
    padding: 0.15rem 0.5rem !important;
    font-size: 0.7rem !important;
    line-height: 1.2 !important;
    border-radius: 0.2rem !important;
    min-width: 30px !important;
    min-height: 24px !important;
    height: 24px !important;
}

/* Ensure consistent spacing between action buttons */
.d-flex.justify-content-center.gap-3 {
    gap: 1rem !important;
}

/* Force margin between buttons */
.btn-info.me-2,
.btn-info[style*="margin-right"] {
    margin-right: 12px !important;
}

.btn-warning.ms-2,
.btn-warning[style*="margin-left"] {
    margin-left: 12px !important;
}

/* Specific targeting for action column buttons */
.table .btn-xs {
    margin: 0 2px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* Ensure icons are properly sized in small buttons */
.btn-xs i {
    font-size: 10px !important;
    margin-right: 0 !important;
    line-height: 1 !important;
}

/* Override any conflicting Bootstrap styles */
.ms-panel .btn-xs {
    margin: 1px 2px !important;
}

/* Prevent button text from affecting size */
.btn-xs span {
    line-height: 1 !important;
    font-size: 0.7rem !important;
}

/* Ensure the action column container doesn't compress buttons */
.d-flex.justify-content-center {
    min-width: 80px !important;
}

/* Force small button dimensions */
#planes-grid .btn-xs {
    width: 30px !important;
    height: 24px !important;
    padding: 0.1rem 0.3rem !important;
}

/* Additional protection against responsive resizing */
@media (max-width: 768px) {
    .btn-xs {
        min-width: 28px !important;
        min-height: 22px !important;
        padding: 0.1rem 0.2rem !important;
    }
    
    .btn-xs i {
        font-size: 9px !important;
    }
    
    /* Modal responsive adjustments */
    #importModal > div {
        width: 95% !important;
        padding: 15px !important;
    }
}

/* Enhanced Error Display Styles */
#import-error-alert {
    border-left: 4px solid #dc3545;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.import-progress .progress-bar {
    transition: width 0.3s ease;
    font-weight: bold;
}

.import-progress .progress {
    height: 30px;
}

.progress-info {
    margin-top: 10px;
}

.progress-details {
    font-size: 12px;
    line-height: 1.4;
}

#technical-details pre {
    max-height: 200px;
    overflow-y: auto;
    font-size: 12px;
    background-color: #f8f9fa !important;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
}

/* Progress bar color transitions */
.progress-bar {
    transition: width 0.5s ease-in-out, background-color 0.5s ease;
}
CSS;

$this->registerCss($bulletproofCss);
?>