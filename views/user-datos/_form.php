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


$currentRoute = Yii::$app->controller->getRoute(); // 'controlador/accion'


/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var app\models\Contratos $modelContrato // Asumo que tienes un modelo de Contrato separado para los datos de contrato */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
/* Tu estilo existente para las pestañas */
.nav-tabs .nav-link.active {
    background-color: #007bff !important; /* Bootstrap primary blue */
    color: white !important;
    border-color: #007bff #007bff #fff !important;
}

/* **NUEVO ESTILO OPCIONAL para asegurar que los botones .btn.active no se vean aplastados**
   Si el estilo 'active' de btn está causando que el padding se reduzca, esto lo restaurará
*/
.btn.active {
    padding: .5rem 1rem !important; /* Ajusta según el padding estándar de btn-lg */
    /* Asegúrate de que el color de fondo y borde también sean consistentes con el diseño de botón activo */
}

.file-input {
    width: 150px !important;
}

.file-input .file-toolbar{
    width: 150px !important;
    margin: 0 auto;
    box-sizing: border-box;
}

.file-input .file-preview {
    width: 60rem !important;
    margin: 0 auto;
    box-sizing: border-box;
}

.file-input .file-caption {
    width: 150px !important;
    box-sizing: border-box;
}
</style>

<?php
$getPlanMontoUrl = Url::to(['/site/planmonto']);
$js = <<<JS
$('.nav-tabs .nav-link').click(function(e) {
    e.preventDefault();

    // Remove active class from all tab links and panes
    $('.nav-tabs .nav-link').removeClass('active');
    $('.tab-content .tab-pane').removeClass('active');

    // Add active class to clicked tab and corresponding pane
    $(this).addClass('active');
    var targetId = $(this).attr('href');
    $(targetId).addClass('active');
});

// Escucha el evento 'change' en el DepDrop del plan
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

// Opcional: Si necesitas que el monto se cargue al inicio cuando hay un plan preseleccionado
// Esto se ejecutará cuando la página cargue si ya hay un valor en #plan_id
$(document).ready(function() {
    var initialPlanId = $('#plan_id').val();
    if (initialPlanId) {
        // Dispara el evento 'change' para que se ejecute la lógica de AJAX
        // Esto simula una selección de usuario al cargar la página
        $('#plan_id').trigger('change');
    }
});

JS;
$this->registerJs($js);
?>

<div class="user-datos-form">
<?php $form = ActiveForm::begin(['id' => 'user-datos-form']); ?>

<?php
$jsValidation = <<<JS
// Hook into Yii ActiveForm afterValidate event on the specific form
$('#user-datos-form').on('afterValidate', function(event, attribute, messages) {
    // If there are validation errors
    if (messages && Object.keys(messages).length > 0) {
        // Find the first invalid input
        var firstInvalid = $(this).find('.has-error').first();
        if (firstInvalid.length) {
            // Find the closest tab-pane parent
            var tabPane = firstInvalid.closest('.tab-pane');
            if (tabPane.length) {
                var tabId = tabPane.attr('id');
                // Remove active class from all tabs and panes
                $('.nav-tabs .nav-link').removeClass('active');
                $('.tab-content .tab-pane').removeClass('active');
                // Activate the tab link and pane corresponding to the invalid field
                $('.nav-tabs .nav-link[href="#' + tabId + '"]').addClass('active');
                tabPane.addClass('active');
            }
        }
        // Prevent form submission
        event.preventDefault();
        return false;
    }
    return true;
});
JS;
$this->registerJs($jsValidation);
?>

<?php
// *** LOS ESTILOS DE LOS BOTONES APLICADOS AQUÍ ***
if (!$model->isNewRecord) { ?>
<div class="row row-cols-1 row-cols-md-4 justify-content-center g-3 mb-4">
    <div class="col"> <?= Html::a('<i class="fas fa-user"></i> Datos Personales', Url::to(['index']), [
            'class' => 'btn btn-primary btn-lg w-100 ' . ($currentRoute === 'index' ? 'active' : ''),
        ]) ?>
    </div>

    <div class="col"> <?= Html::a('<i class="fas fa-phone-alt"></i> Contactos de Emergencia', Url::to(['contactos-emergencia/index', 'user_id' => $model->id]), [
            'class' => 'btn btn-primary btn-lg w-100 ' . ($currentRoute === 'contactos-emergencia/index' ? 'active' : ''),
            'data-pjax' => '0'
        ]) ?>
    </div>

    <div class="col"> <?= Html::a('<i class="fas fa-heartbeat"></i> Declaración de Salud', Url::to(['declaracion-de-salud/index', 'user_id' => $model->id]), [
            'class' => 'btn btn-primary btn-lg w-100 ' . ($currentRoute === 'declaracion-salud/index' ? 'active' : ''),
            'data-pjax' => '0'
        ]) ?>
    </div>

    <div class="col"> <?= Html::a('<i class="fas fa-id-card"></i> Afiliación', Url::to(['contratos/index','user_id' => $model->id]), [
            'class' => 'btn btn-primary btn-lg w-100 ' . ($currentRoute === 'contratos/index' ? 'active' : ''),
            'data-pjax' => '0'
        ]) ?>
    </div>
</div>
<?php } ?>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="tab13">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'codigoAsesor')->textInput(['class' => 'form-control form-control-lg',]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'asesor_id')->widget(Select2::classname(), [
                            'data' => UserHelper::getAgenteFuerzaList(),
                            'options' => [
                                'placeholder' => 'Seleccione el asesor', // Placeholder adaptado
                                'class' => 'form-control form-control-lg',
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                    ])->label('NOMBRE DEL ASESOR') // Etiqueta adaptada
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'email')->textInput(['class' => 'form-control form-control-lg',]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'telefono')->widget(MaskedInput::class, [
                        'mask' =>  '99999999999',
                        'options' => [
                            'placeholder' => '(XXXX) XXX-XXXX',
                            'class' => 'form-control  form-control-lg',
                            'maxlength' => true,
                        ],
                        'clientOptions' => [
                        'clearIncomplete' => true, // Opcional: limpia el campo si el usuario no lo completa
                        'unmaskAsSubmit' => true,  // <-- Envía solo los dígitos al servidor
                        ]
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'nombres')->textInput(['class' => 'form-control form-control-lg',]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'apellidos')->textInput(['class' => 'form-control form-control-lg',]) ?>
                </div>
            </div>
            <div class="row ">
                 <div class="col-md-1">
                            <?= $form->field($model, 'tipo_cedula')->widget(Select2::class, [ // Changed field to tipo_cedula
                                'data' => [ // Updated data options
                                    'V' => 'V',
                                    'E' => 'E',
                                    'J' => 'J',
                                    'P' => 'P',
                                    'N' => 'N',
                                    'M' => 'M',
                                ],
                                'options' => [
                                    'placeholder' => 'Tipo', // Updated placeholder for brevity
                                    'class' => 'form-control form-control-lg', 
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true, 
                                ],
                                ])->label('Tipo') // Updated label for brevity
                            ?>
                </div>

                    <?php if ($model->isNewRecord) { ?>
                        <div class="col-md-2">
                            <?= $form->field($model, 'cedula')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ejemplo: 12345678'
                            ])->label('Cédula de Identidad') // Added label for clarity
                            ?>
                        </div>
                    <?php }else{?>
                        <div class="col-md-2">

                            <?= $form->field($model, 'cedula')->textInput([ // <-- ¡MODIFICADO!
                                'class' => 'form-control form-control-lg',
                                'readonly' => true, // La cédula no se edita directamente una vez creada.
                            ])->label('Cédula de Identidad') // <-- ¡IMPORTANTE! Añade una etiqueta explícita.
                        ?>
                         </div>
                    <?php } ?>

                <div class="col-md-3">
            
                    <?= $form->field($model, 'fechanac')->textInput([
                                    'class' => 'form-control form-control-lg',
                                    'type' => 'date',
                                    'placeholder' => 'Seleccione su fecha de nacimiento'
                                ])->label('Fecha de Nacimiento') ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'sexo')->widget(Select2::class, [ // Usado Select2::class en lugar de \kartik\select2\Select2::class
                        'data' => [
                            'Masculino' => 'Masculino', // <-- ¡SUGERENCIA! Usa mayúsculas iniciales para consistencia
                            'Femenino' => 'Femenino',   // <-- ¡SUGERENCIA!
                            'Otro' => 'Otro',           // <-- ¡SUGERENCIA! Si tu modelo permite 'Otro'
                        ],
                        'options' => ['placeholder' => 'Seleccione el sexo...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'tipo_sangre')->widget(Select2::class, [ // Usado Select2::class en lugar de \kartik\select2\Select2::class
                        'data' => [
                            'A+' => 'A+',
                            'A-' => 'A-',
                            'B+' => 'B+',
                            'B-' => 'B-',
                            'AB+' => 'AB+',
                            'AB-' => 'AB-',
                            'O+' => 'O+',
                            'O-' => 'O-',
                        ],
                        'options' => ['placeholder' => 'Seleccione el tipo de sangre...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'estado')->widget(Select2::classname(), [
                            'data' => UserHelper::getEstadosList(),
                            'options' => [
                                'placeholder' => 'Seleccione',
                                'class' => 'form-control  form-control-lg',
                                'id' => 'estado_id'
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                        ]);
                    ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'municipio')->widget(DepDrop::classname(), [
                            'type' => DepDrop::TYPE_SELECT2,
                            'options'=>[
                                'id'=>'municipio_id',
                                'placeholder' => 'Seleccione',
                                'class' => 'form-control  form-control-lg',
                            ],
                            'pluginOptions'=>[
                                'depends'=>['estado_id'],
                                'url'=>Url::to(['/site/municipio']),
                                'initialize' => true,
                            ]
                        ]);
                    ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'parroquia')->widget(DepDrop::classname(), [
                            'type' => DepDrop::TYPE_SELECT2,
                            'options'=>[
                                'id'=>'parroquia_id',
                                'placeholder' => 'Seleccione',
                                'class' => 'form-control  form-control-lg',
                            ],
                            'pluginOptions'=>[
                                'depends'=>['municipio_id'],
                                'url'=>Url::to(['/site/parroquia']),
                                // 'initValueText' => isset($parroquiaName) ? $parroquiaName : '', // Comentado si no pasas esta variable
                            ]
                        ]);
                    ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'ciudad')->widget(DepDrop::classname(), [
                            'type' => DepDrop::TYPE_SELECT2,
                            'options'=>[
                                'id'=>'ciudad_id',
                                'placeholder' => 'Seleccione',
                                'class' => 'form-control  form-control-lg',
                            ],
                            'pluginOptions'=>[
                                'depends'=>['estado_id'],
                                'url'=>Url::to(['/site/ciudad']),
                                'initialize' => true,
                            ]
                        ]);  ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'selfie')->widget(FileInput::classname(),[
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
                <div class="col-md-6">
                    <?= $form->field($model, 'imagen_identificacion')->widget(FileInput::classname(),[
                        'name' => 'attachments',
                        'pluginOptions' => [
                            'browseClass' => 'btn btn-primary',
                            'removeClass' => 'btn btn-secondary',
                            'removeIcon' => '<i class="fas fa-trash"></i> ',
                            'previewFileType' => 'image',
                            'showUpload' => false,
                            'showCancel' => false,
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
                        ])->label('Imagen de identificacion');
                    ?>    
                </div>
            <br>
            <h1>Datos del Contrato</h1>
                <br>
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


            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'direccion')->textInput(['class' => 'form-control form-control-lg',]) ?>
                </div>
                <div class="col-md-12">
                    <div class="form-group text-end mt-4"> <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg me-2']) ?> <?= Html::a('Cancelar', ['index', 'clinica_id' => $model->clinica_id], ['class' => 'btn btn-warning btn-lg']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
