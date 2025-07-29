<?php

use yii\helpers\Html;
use kartik\form\ActiveForm; // Asegúrate de que esto es 'kartik\form\ActiveForm'
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // <--- ¡IMPORTANTE! Sigue siendo 'yii\widgets\MaskedInput' para el campo de cédula
use app\components\UserHelper;
use kartik\widgets\SwitchInput; // No usado en este fragmento, pero puede mantenerse.
use kartik\widgets\DatePicker;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;
use kartik\widgets\FileInput;

use app\models\UserDatosType;

$currentRoute = Yii::$app->controller->getRoute(); // 'controlador/accion'


/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var app\models\Contratos $modelContrato // Asumo que tienes un modelo de Contrato separado para los datos de contrato */
/** @var yii\widgets\ActiveForm $form */


// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
// --- FIN  --- 


$this->title = 'Gestión Masiva de Afiliados'; 

$getPlanMontoUrl = Url::to(['/site/planmonto']);
$js = <<<JS
$('#plan_id').on('change', function() {
    var selectedPlanId = $(this).val(); // Obtiene el ID del plan seleccionado
    console.log("Plan ID seleccionado:", selectedPlanId);

    if (selectedPlanId) {
        $.ajax({
            url: '{$getPlanMontoUrl}', // Usa la URL generada por Yii
            type: 'GET',
            data: { id: selectedPlanId }, // Envía el ID del plan
            dataType: 'json', // Espera una respuesta JSON
            success: function(response) {
                if (response && typeof response.monto !== 'undefined') {
                    console.log("Monto del plan recibido:", response.monto);
                    // CAMBIO AQUÍ: Asegúrate que el ID del campo de monto en el formulario sea correcto
                    $('#contratos-monto').val(response.monto);
                } else {
                    console.log("Respuesta AJAX no válida o monto no encontrado.");
                    $('#contratos-monto').val(0); // O un valor por defecto
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener el monto del plan:", error);
                $('#contratos-monto').val(0); // En caso de error, limpia el campo
            }
        });
    } else {
        // Si no hay plan seleccionado (ej. se limpia el campo), el monto es 0
        $('#contratos-monto').val(0);
    }
});
JS;
$this->registerJs($js);

// JavaScript para mejorar la experiencia del usuario con los reportes de validación
$validationJs = <<<JS
$(document).ready(function() {
    // Agregar botón de cerrar a los reportes de validación
    $('.validation-report').each(function() {
        var closeBtn = $('<button type="button" class="btn-close" aria-label="Cerrar" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; color: #dc3545; cursor: pointer;">&times;</button>');
        $(this).css('position', 'relative').prepend(closeBtn);
        
        closeBtn.on('click', function() {
            $(this).parent().fadeOut();
        });
    });
    
    // Auto-scroll a los errores si existen
    if ($('.validation-report').length > 0) {
        $('html, body').animate({
            scrollTop: $('.validation-report').offset().top - 100
        }, 500);
    }
    
    // Resaltar filas con errores en la tabla
    $('.validation-report table tbody tr').hover(
        function() {
            $(this).addClass('table-warning');
        },
        function() {
            $(this).removeClass('table-warning');
        }
    );
});
JS;
$this->registerJs($validationJs);
?>
<style>
.file-input .file-toolbar{
    width: 150px !important;
    margin: 0 auto;
    box-sizing: border-box;
}

.file-input .file-preview {
    margin: 0 auto;
    box-sizing: border-box;
}

.file-input .file-caption {
    width: 150px !important;
    box-sizing: border-box;
}

/* Estilos para el reporte de validación */
.validation-report {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.validation-report h3 {
    color: #dc3545;
    margin-bottom: 15px;
    border-bottom: 2px solid #dc3545;
    padding-bottom: 10px;
}

.validation-report .summary {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.validation-report .summary ul {
    margin-bottom: 0;
    padding-left: 20px;
}

.validation-report .summary li {
    margin-bottom: 5px;
}

.validation-report .errors h4 {
    color: #dc3545;
    margin-bottom: 15px;
}

.validation-report table {
    font-size: 14px;
}

.validation-report table th {
    background: #dc3545;
    color: white;
    font-weight: bold;
}

.validation-report table td {
    vertical-align: top;
}

.validation-report table td ul {
    margin-bottom: 0;
    padding-left: 15px;
}

.validation-report table td small {
    font-size: 12px;
    color: #666;
    line-height: 1.4;
}

/* Botón para descargar plantilla */
.download-template-btn {
    margin-bottom: 20px;
}

/* Responsive para tablas */
@media (max-width: 768px) {
    .validation-report table {
        font-size: 12px;
    }
    
    .validation-report table td small {
        font-size: 10px;
    }
}

/* Estilos para el botón de cerrar */
.validation-report .btn-close {
    transition: all 0.3s ease;
}

.validation-report .btn-close:hover {
    transform: scale(1.2);
    color: #000 !important;
}

/* Animación para el reporte */
.validation-report {
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
<div class=row style="margin:3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
        
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1><?= $this->title ;?></h1>
                <div>
                    <?= Html::a('<i class="fas fa-download"></i> Descargar Plantilla', ['download-template'], [
                        'class' => 'btn btn-info btn-lg download-template-btn',
                        'title' => 'Descargar plantilla Excel de ejemplo'
                    ]) ?>
                </div>
            </div>
            <div class="ms-panel-body">
                <?php
                // Mostrar reporte de validación si existe
                if (Yii::$app->session->hasFlash('error')) {
                    $flashMessage = Yii::$app->session->getFlash('error');
                    // Si el mensaje contiene HTML (es un reporte de validación)
                    if (strpos($flashMessage, '<div class="validation-report">') !== false) {
                        echo '<div class="alert alert-danger">';
                        echo $flashMessage; // Mostrar el HTML del reporte
                        echo '</div>';
                    } else {
                        // Mensaje de error normal
                        echo '<div class="alert alert-danger">' . Html::encode($flashMessage) . '</div>';
                    }
                }
                
                // Mostrar mensajes de éxito
                if (Yii::$app->session->hasFlash('success')) {
                    echo '<div class="alert alert-success">' . Html::encode(Yii::$app->session->getFlash('success')) . '</div>';
                }
                
                // Mostrar mensajes de advertencia
                if (Yii::$app->session->hasFlash('warning')) {
                    echo '<div class="alert alert-warning">' . Html::encode(Yii::$app->session->getFlash('warning')) . '</div>';
                }
                ?>
                
                <?php $form = ActiveForm::begin([
                    'id' => 'user-datos-form',
                    'options' => ['enctype' => 'multipart/form-data']
                ]); 
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Información sobre el archivo Excel</h5>
                            <p><strong>Formato requerido:</strong> El archivo debe tener las siguientes columnas:</p>
                            <ul>
                                <li><strong>A:</strong> Email (obligatorio, formato válido)</li>
                                <li><strong>B:</strong> Teléfono (obligatorio, formato venezolano)</li>
                                <li><strong>C:</strong> Nombres (obligatorio, mínimo 2 caracteres)</li>
                                <li><strong>D:</strong> Apellidos (obligatorio, mínimo 2 caracteres)</li>
                                <li><strong>E:</strong> Tipo Cédula (obligatorio: V, E, P, J)</li>
                                <li><strong>F:</strong> Cédula (obligatorio, 6-10 dígitos numéricos)</li>
                                <li><strong>G:</strong> Fecha Nacimiento (obligatorio, formato DD/MM/YYYY)</li>
                                <li><strong>H:</strong> Sexo (obligatorio: M, F, Masculino, Femenino)</li>
                                <li><strong>I:</strong> Tipo Sangre (opcional: A+, A-, B+, B-, AB+, AB-, O+, O-)</li>
                                <li><strong>J:</strong> Estado ID (opcional, ID numérico)</li>
                                <li><strong>K:</strong> Municipio ID (opcional, ID numérico)</li>
                                <li><strong>L:</strong> Parroquia ID (opcional, ID numérico)</li>
                                <li><strong>M:</strong> Ciudad ID (opcional, ID numérico)</li>
                                <li><strong>N:</strong> Dirección (obligatorio, mínimo 10 caracteres)</li>
                            </ul>
                            <p><strong>Nota:</strong> La primera fila debe contener los encabezados. Descarga la plantilla de ejemplo para ver el formato correcto.</p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($model, 'masivoFile')->widget(FileInput::classname(),[
                                'name' => 'attachments',
                                'pluginOptions' => [
                                    'browseClass' => 'btn btn-primary',
                                        'removeClass' => 'btn btn-secondary',
                                        'removeIcon' => '<i class="fas fa-trash"></i> ',
                                        'showUpload' => false,
                                        'showCancel' => false,
                                        'previewFileType' => 'image',
                                        'maxFileSize' => 2800,
                                        'previewSettings' => [
                                            'image' => ['width' => '150px', 'height' => 'auto'],
                                        ],
                                        //'initialPreview' => $initialPreview,
                                        //'initialPreviewAsData' => true,
                                        //'initialPreviewConfig' => $initialPreviewConfig,
                                        //'overwriteInitial' => true,
                                        //'layoutTemplates' => [
                                        //    'preview' => '<div class="file-preview {class}" style="width: 200px;"></div>',
                                        //],
                                    ],
                                    'options' => [
                                        //'disabled' => $disabled,
                                    ],
                                    ])->label('Foto del usuario');
                            ?>    
                    </div>
                </div>
                <h1>Datos del Contrato</h1>
                <div class = 'row'>
                    <div class="col-md-6">
                        <?= $form->field($model, 'clinica_id')->widget(Select2::classname(), [
                                'data' => UserHelper::getClinicasList(),
                                'options' => [
                                    'placeholder' => 'Seleccione',
                                    'class' => 'form-control  form-control-lg',
                                    'id' => 'clinica_id'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => false,
                                ],
                            ])->label('Clinica'); ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($modelContrato, 'plan_id')->widget(DepDrop::classname(), [ // <-- ¡VERIFICA EL MODELO!
                            'type' => DepDrop::TYPE_SELECT2,
                            'options'=>[
                                'id'=>'plan_id',
                                'placeholder' => 'Seleccione',
                                'class' => 'form-control  form-control-lg',
                            ],
                            'pluginOptions'=>[
                                'depends'=>['clinica_id'],
                                'url'=>Url::to(['/site/planes']),
                                'initialize' => true,
                                ]
                            ])->label('Plan');
                            ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($modelContrato, 'fecha_ini')->textInput([
                                        'class' => 'form-control form-control-lg',
                                        'type' => 'date',
                                        'placeholder' => 'Seleccione su fecha de nacimiento'
                                    ])->label('Fecha de Inicio') ?>
                    </div>
                    <div class="col-md-4">
                            <?= $form->field($modelContrato, 'fecha_ven')->textInput([
                                        'class' => 'form-control form-control-lg',
                                        'type' => 'date',
                                        'placeholder' => 'Seleccione su fecha de nacimiento'
                                    ])->label('Fecha de Vencimiento') ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($modelContrato, 'monto')->textInput(['class' => 'form-control  form-control-lg', 'type' => 'number']) ?>
                    </div>
                </div>
                <div class="form-group text-end mt-4"> <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg me-2']) ?> <?= Html::a('Cancelar', ['index', 'clinica_id' => $model->clinica_id], ['class' => 'btn btn-warning btn-lg']); ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>

