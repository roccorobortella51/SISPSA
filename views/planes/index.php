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
use yii\bootstrap4\Modal;

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
$permisos = ($rol == 'superadmin'); 

if($permisos == true){
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
                        'data-toggle' => 'modal',
                        'data-target' => '#importModal'
                    ] 
                ) ?>
            <?php endif; ?>
            <div class="flex-grow-1"></div>
            <?php if ($clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver', 
                    ['/rm-clinica/view', 'id' => $clinica->id], 
                    [
                        'class' => 'btn btn-secondary btn-sm ms-5', // ms-5 adds noticeable left margin
                        'title' => 'Volver a los detalles de la clínica',
                        'style' => 'margin-left:40px;'
                    ]
                ) ?>
                
            <?php endif; ?>
        </div>
    </div>

    <?php Modal::begin([
        'id' => 'importModal',
        'title' => '<h4 class="modal-title">Importar Planes desde Excel</h4>',
        'options' => ['tabindex' => false],
        'size' => Modal::SIZE_LARGE,
    ]); ?>
    
    <?php $form = ActiveForm::begin([
        'id' => 'import-form',
        'action' => ['planes/import'], // CORRECTED: Using 'planes' instead of 'plan'
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>
    
    <div class="modal-body">
        <div class="alert alert-info">
            <strong><i class="fas fa-info-circle"></i> Instrucciones:</strong><br>
            - El archivo Excel debe tener una hoja principal llamada <strong>"Plans"</strong>.<br>
            - Para cada plan listado en la hoja "Plans" (ej. "Bronce"), debe existir una hoja con el <strong>mismo nombre exacto</strong> ("Bronce") que contenga sus servicios.<br>
            - Asegúrese de que el formato de datos sea correcto.<br>
            - El archivo debe estar en formato .xlsx o .xls
        </div>
        
        <div class="form-group">
            <label for="excel-file" class="font-weight-bold">Seleccionar archivo Excel</label>
            <?= Html::fileInput('excelFile', null, [
                'class' => 'form-control-file',
                'accept' => '.xlsx,.xls',
                'required' => true,
                'id' => 'excel-file'
            ]) ?>
            <small class="form-text text-muted">Formatos soportados: .xlsx, .xls (Tamaño máximo: 10MB)</small>
        </div>
        
        <div class="form-group">
            <?= Html::hiddenInput('clinica_id', $clinica->id) ?>
        </div>
        
        <!-- Enhanced Progress Section -->
        <div class="import-progress" style="display: none;">
            <div class="progress mb-3">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
            </div>
            <div class="progress-info">
                <div class="progress-text text-center font-weight-bold mb-2">Procesando archivo...</div>
                <div class="progress-details small text-muted text-center">
                    <div id="progress-details-text"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <?= Html::submitButton('<i class="fas fa-upload mr-2"></i> Importar Planes', [
            'class' => 'btn btn-success',
            'id' => 'submit-import'
        ]) ?>
    </div>
    <?php ActiveForm::end(); ?>
    
    <?php Modal::end(); ?>

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
                            'format' => ['percent',2],
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
                            'options' => ['style' => 'width:90px; min-width:90px;'], // Adjusted width for smaller buttons
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 8px !important;'], // Reduced padding
                            'buttons' => [
                                'view' => function ($url, $model, $key) use ($clinica) {
                                    return Html::a(
                                        '<i class="fa fa-eye"></i>', // Removed "Detalles" text
                                        Url::to(['view', 'id' => $model->id, 'clinica_id' => $clinica->id]),
                                        [
                                            'title' => 'Detalles del Plan',
                                            'class' => 'btn btn-xs btn-info px-2 py-0 me-2', // Changed to btn-xs, reduced padding
                                            'style' => 'font-weight:500; font-size:10px; margin-right: 8px !important; min-height: 24px; line-height: 1;' // Smaller font and dimensions
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) use ($permisos, $clinica) {
                                    if($permisos == true){
                                        return Html::a(
                                            '<i class="fas fa-pencil-alt"></i>', // Removed "Editar" text
                                            Url::to(['update', 'id' => $model->id, 'clinica_id' => $clinica->id]),
                                            [
                                                'title' => 'Editar',
                                                'class' => 'btn btn-xs btn-warning px-2 py-0 ms-2', // Changed to btn-xs, reduced padding
                                                'style' => 'font-weight:500; font-size:10px; margin-left: 8px !important; min-height: 24px; line-height: 1;' // Smaller font and dimensions
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
// NEW JAVASCRIPT BLOCK WITH REAL-TIME PROGRESS TRACKING
$js = <<<JS

let progressPoller = null; // Variable to hold the setInterval timer
let currentTaskId = null;

// Function to poll for import progress
function pollProgress(taskId) {
    $.ajax({
        url: IMPORT_STATUS_URL,
        type: 'GET',
        data: { taskId: taskId },
        success: function(response) {
            if (!response) return;

            // Update progress bar and text
            const progressBar = $('.progress-bar');
            const progressText = $('.progress-text');
            const progressDetails = $('#progress-details-text');
            
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
                    if (response.details.current_sheet && response.details.current_sheet !== response.details.current_plan) {
                        detailsHtml += ` (\${response.details.current_sheet})`;
                    }
                }
                progressDetails.html(detailsHtml);
            }
            
            // Change progress bar color based on progress
            if (response.progress < 30) {
                progressBar.removeClass('bg-warning bg-success').addClass('bg-danger');
            } else if (response.progress < 70) {
                progressBar.removeClass('bg-danger bg-success').addClass('bg-warning');
            } else {
                progressBar.removeClass('bg-danger bg-warning').addClass('bg-success');
            }

            // Check if the process is finished
            if (response.finished) {
                clearInterval(progressPoller); // Stop polling
                handleImportResult(response.result);
            }
        },
        error: function() {
            // If polling fails, stop and show an error
            clearInterval(progressPoller);
            showImportError('Connection Error', 'Could not get import status from the server.');
            resetImportForm();
        }
    });
}

// Function to handle the final result of the import
function handleImportResult(result) {
    const submitBtn = $('#submit-import');
    
    if (result.success) {
        // Show 100% progress before success message
        $('.progress-bar').css('width', '100%').text('100%').removeClass('bg-warning bg-danger').addClass('bg-success');
        $('.progress-text').text('Importación completada!');
        
        setTimeout(function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Importación Exitosa!',
                    html: result.message,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    $('#importModal').modal('hide');
                    location.reload();
                });
            } else {
                alert('¡Importación exitosa! ' + result.message);
                $('#importModal').modal('hide');
                location.reload();
            }
        }, 1000);
    } else {
        // Handle failure
        showImportError(result.message, result.detailed_error);
        resetImportForm();
    }
}

// Function to reset the form and progress bar
function resetImportForm() {
    $('#submit-import').prop('disabled', false).html('<i class="fas fa-upload mr-2"></i> Importar Planes');
    $('.import-progress').hide();
    $('.progress-bar').css('width', '0%').text('');
    $('#progress-details-text').empty();
}

// Main import form submission handler
$('#import-form').on('beforeSubmit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    var submitBtn = $('#submit-import');
    
    // Hide previous errors and show loading state IMMEDIATELY
    $('#import-error-alert').hide();
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Iniciando...');
    $('.import-progress').show();
    
    // Show immediate progress feedback
    $('.progress-bar').css('width', '5%').text('5%').removeClass('bg-warning bg-success').addClass('bg-danger');
    $('.progress-text').text('Iniciando importación...');
    $('#progress-details-text').html('Preparando archivo...');
    
    // This initial AJAX call starts the process and gets a task ID
    $.ajax({
        url: IMPORT_URL,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
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
            if (response.success && response.taskId) {
                // Successfully started, now begin polling for status
                currentTaskId = response.taskId;
                $('.progress-text').text('Archivo subido. Procesando...');
                $('.progress-bar').css('width', '10%').text('10%');
                
                // Start polling for progress IMMEDIATELY
                progressPoller = setInterval(function() {
                    pollProgress(currentTaskId);
                }, 800); // Poll every 800ms for more responsive updates
            } else {
                // Failed to start the import process
                showImportError(response.message || 'Failed to start import.', 'The server did not provide a task ID.');
                resetImportForm();
            }
        },
        error: function(xhr) {
            var errorMessage = 'Error iniciando la importación.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showImportError(errorMessage, xhr.responseText);
            resetImportForm();
        }
    });
    
    return false;
});

// Enhanced error display functions
function showImportError(mainMessage, detailedMessage) {
    $('#error-main-message').text(mainMessage);
    $('#error-detailed-message').text(detailedMessage || 'No hay detalles adicionales disponibles.');
    $('#error-stack-trace').text(detailedMessage || 'No hay detalles técnicos disponibles.');
    $('#import-error-alert').show();
    
    // Scroll to error inside modal
    var modalBody = $('#importModal .modal-body');
    modalBody.animate({
        scrollTop: modalBody.scrollTop() + $('#import-error-alert').position().top - 20
    }, 500);
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

// Reset form when modal is closed (and stop polling just in case)
$('#importModal').on('hidden.bs.modal', function () {
    if (progressPoller) {
        clearInterval(progressPoller);
    }
    $('#import-form')[0].reset();
    resetImportForm();
    $('#import-error-alert').hide();
    currentTaskId = null;
});

// File input change event to validate file type
$('#excel-file').on('change', function() {
    var file = this.files[0];
    if (file) {
        var fileName = file.name;
        var fileExtension = fileName.split('.').pop().toLowerCase();
        var validExtensions = ['xlsx', 'xls'];
        
        if (!validExtensions.includes(fileExtension)) {
            alert('Error: Por favor seleccione un archivo Excel válido (.xlsx o .xls)');
            $(this).val('');
            return false;
        }
        
        if (file.size > 10 * 1024 * 1024) {
            alert('Error: El archivo es demasiado grande. El tamaño máximo permitido es 10MB.');
            $(this).val('');
            return false;
        }
    }
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
JS;

$this->registerJs($js);
?>

<?php
// Add CSS to ensure buttons stay small and for enhanced progress display
$css = <<<CSS
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
}

/* Enhanced Error Display Styles */
#import-error-alert {
    border-left: 4px solid #dc3545;
    margin-bottom: 20px;
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
}

/* Modal enhancements */
#importModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.import-progress {
    margin-top: 15px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
    border: 1px solid #e9ecef;
}

/* Progress bar color transitions */
.progress-bar {
    transition: width 0.5s ease-in-out, background-color 0.5s ease;
}
CSS;

$this->registerCss($css);
?>