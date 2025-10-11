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

// Get the correct URL for the import action - USING PlanesController (with "es")
$importUrl = Url::to(['planes/import']);

?>

<div class="main-container"> 
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    
    <!-- Encabezado y Botones de Acción Principal -->
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
                <!-- Add Import Button -->
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

    <!-- Modal for Import -->
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
    
    <!-- In the modal-body section, update the instructions: -->
<div class="modal-body">
    <div class="alert alert-info">
        <strong><i class="fas fa-info-circle"></i> Instrucciones:</strong><br>
        - El archivo Excel debe tener dos hojas: <strong>"Plans"</strong> y <strong>"Services"</strong><br>
        - Hoja <strong>"Plans"</strong>: columnas requeridas: <strong>Nombre Plan, Descripción, Precio, Estatus, Edad Límite, Edad Mínima, Comisión, Cobertura</strong><br>
        - Hoja <strong>"Services"</strong>: nuevo formato con áreas, servicios y coberturas por plan (Bronce, Plata, Oro, Esmeralda)<br>
        - Asegúrese de que el formato de datos sea correcto<br>
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
    
    <div class="import-progress" style="display: none;">
        <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
        <div class="text-center mt-2">
            <span class="progress-text">Procesando archivo...</span>
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
$js = <<<JS
// Handle import form submission
$('#import-form').on('beforeSubmit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    var submitBtn = $('#submit-import');
    var progressContainer = $('.import-progress');
    
    // Show loading state and progress
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importando...');
    progressContainer.show();
    
    // Update progress bar
    var progressBar = $('.progress-bar');
    progressBar.css('width', '30%').text('30%');
    $('.progress-text').text('Leyendo archivo Excel...');
    
    // Use the form's action URL directly
    var importUrl = $('#import-form').attr('action');
    
    $.ajax({
        url: importUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total * 100;
                    progressBar.css('width', percentComplete + '%').text(Math.round(percentComplete) + '%');
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            progressBar.css('width', '100%').text('100%');
            $('.progress-text').text('Procesamiento completado');
            
            setTimeout(function() {
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Importación Exitosa!',
                            text: 'Se importaron ' + response.imported + ' planes correctamente.',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            $('#importModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        alert('¡Importación exitosa! Se importaron ' + response.imported + ' planes.');
                        $('#importModal').modal('hide');
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en Importación',
                            text: response.message,
                            confirmButtonText: 'Aceptar'
                        });
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            }, 500);
        },
        error: function(xhr, status, error) {
            var errorMessage = 'Error en la importación: ' + error;
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonText: 'Aceptar'
                });
            } else {
                alert(errorMessage);
            }
        },
        complete: function() {
            setTimeout(function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-upload mr-2"></i> Importar Planes');
                progressContainer.hide();
                progressBar.css('width', '0%').text('');
            }, 1000);
        }
    });
    
    return false;
});

// Reset form when modal is closed
$('#importModal').on('hidden.bs.modal', function () {
    $('#import-form')[0].reset();
    $('.import-progress').hide();
    $('.progress-bar').css('width', '0%').text('');
    $('#submit-import').prop('disabled', false).html('<i class="fas fa-upload mr-2"></i> Importar Planes');
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
        if (!response.success) {
            $('#status-switch-' + planId).bootstrapSwitch('toggleState');
            alert('Error al actualizar el estado: ' + response.message);
        }
    });
}
JS;

$this->registerJs($js);
?>

<?php
// Add CSS to ensure buttons stay small
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
CSS;

$this->registerCss($css);
?>