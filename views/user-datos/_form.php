<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // Para campos con máscaras como RIF y teléfono
use app\components\UserHelper;
use kartik\widgets\SwitchInput;
use kartik\widgets\DatePicker;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;


/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
.nav-tabs .nav-link.active {
    background-color: #007bff !important; /* Bootstrap primary blue */
    color: white !important;
    border-color: #007bff #007bff #fff !important;
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
    <ul class="nav nav-tabs d-flex nav-justified mb-4" role="tablist">
        <li role="presentation"><a href="#tab13" aria-controls="tab13" class="nav-link active" role="tab" data-toggle="tab">Datos Personales</a></li>
        <li role="presentation"><a href="#tab14" aria-controls="tab14" class="nav-link" role="tab" data-toggle="tab">Fotos</a></li>
        <li role="presentation"><a href="#tab15" aria-controls="tab15" class="nav-link" role="tab" data-toggle="tab">Contactos</a></li>
        <li role="presentation"><a href="#tab16" aria-controls="tab16" class="nav-link" role="tab" data-toggle="tab">Declaracion de Salud</a></li>
        <li role="presentation"><a href="#tab17" aria-controls="tab17" class="nav-link" role="tab" data-toggle="tab">Tu Afiliacion</a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="tab13">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'codigoAsesor')->textInput() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'asesor_id')->textInput() ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'email')->textInput() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'telefono')->widget(MaskedInput::class, [
                        'mask' => '(9999) 999-9999',
                        'options' => [
                            'placeholder' => '(XXXX) XXX-XXXX',
                            'class' => 'form-control  form-control-lg',
                            'maxlength' => true,
                        ]
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'nombres')->textInput() ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'apellidos')->textInput() ?>
                </div>
            </div>
            <div class="row ">
                <div class="col-md-3">
                    <?= $form->field($model, 'cedula')->widget(\yii\widgets\MaskedInput::class, [
                        'mask' => 'a-99999999',
                        'clientOptions' => [
                            'definitions' => [
                                'a' => [
                                    'validator' => '[VE]',
                                    'cardinality' => 1,
                                ],
                            ],
                        ],
                        'options' => [
                            'placeholder' => 'V-99999999 o E-99999999',
                            'class' => 'form-control  form-control-lg',
                            'maxlength' => true,
                        ],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'fechanac')->widget(\kartik\date\DatePicker::class, [
                        'options' => [
                            'placeholder' => 'Seleccione la fecha de nacimiento',
                            'class' => 'form-control  form-control-lg',
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'sexo')->widget(\kartik\select2\Select2::class, [
                        'data' => [
                            'masculino' => 'Masculino',
                            'femenino' => 'Femenino',
                        ],
                        'options' => ['placeholder' => 'Seleccione el sexo...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'tipo_sangre')->widget(\kartik\select2\Select2::class, [
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
                                'initValueText' => isset($parroquiaName) ? $parroquiaName : '',
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
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'direccion')->textarea() ?>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="tab14">
            <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam urna nunc, congue nec laoreet sed, maximus non massa. Fusce vestibulum vel risus vitae tincidunt. </p>
            <p> Cras egestas nisi vel tempor dignissim. Ut condimentum iaculis ex nec ornare. Vivamus sit amet elementum ante. Fusce eget erat volutpat </p>
            <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam urna nunc, congue nec laoreet sed, maximus non massa. Fusce vestibulum vel risus vitae tincidunt. </p>
        </div>
        <div role="tabpanel" class="tab-pane" id="tab15">
            <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam urna nunc, congue nec laoreet sed, maximus non massa. Fusce vestibulum vel risus vitae tincidunt. </p>
            <p> Cras egestas nisi vel tempor dignissim. Ut condimentum iaculis ex nec ornare. Vivamus sit amet elementum ante. Fusce eget erat volutpat </p>
            <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam urna nunc, congue nec laoreet sed, maximus non massa. Fusce vestibulum vel risus vitae tincidunt. </p>
        </div>
        <div role="tabpanel" class="tab-pane" id="tab16">
            <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam urna nunc, congue nec laoreet sed, maximus non massa. Fusce vestibulum vel risus vitae tincidunt. </p>
            <p> Cras egestas nisi vel tempor dignissim. Ut condimentum iaculis ex nec ornare. Vivamus sit amet elementum ante. Fusce eget erat volutpat </p>
            <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam urna nunc, congue nec laoreet sed, maximus non massa. Fusce vestibulum vel risus vitae tincidunt. </p>
        </div>
        <div role="tabpanel" class="tab-pane" id="tab17">
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
                        ]); ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($modelContrato, 'plan_id')->widget(DepDrop::classname(), [
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
                        ]);  
                        ?>
                </div>
                <div class="col-md-4"><?= $form->field($modelContrato, 'fecha_ini')->widget(\kartik\date\DatePicker::class, [
                    'options' => [
                        'placeholder' => 'Seleccione la fecha de nacimiento',
                        'class' => 'form-control  form-control-lg',
                    ],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true,
                    ],
                    ]) ?></div>
                <div class="col-md-4">
                    <?= $form->field($modelContrato, 'fecha_ven')->widget(\kartik\date\DatePicker::class, [
                        'options' => [
                            'placeholder' => 'Seleccione la fecha de nacimiento',
                            'class' => 'form-control  form-control-lg',
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                        ],
                        ]) ?>
                </div>
                <div class="col-md-4"> <?= $form->field($modelContrato, 'monto')->textInput() ?></div>
                
                

            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
