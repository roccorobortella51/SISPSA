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
JS;
$this->registerJs($js);
?>

<div class="user-datos-form">
    <?php $form = ActiveForm::begin(); ?>
    <ul class="nav nav-tabs d-flex nav-justified mb-4" role="tablist">
        <li role="presentation"><a href="#tab13" aria-controls="tab13" class="nav-link active" role="tab" data-toggle="tab">Datos Personales</a></li>
        <li role="presentation"><a href="#tab14" aria-controls="tab14" class="nav-link" role="tab" data-toggle="tab">Fotos</a></li>
        <li role="presentation"><a href="#tab15" aria-controls="tab15" class="nav-link" role="tab" data-toggle="tab">Contactos</a></li>
        <li role="presentation"><a href="#tab16" aria-controls="tab16" class="nav-link" role="tab" data-toggle="tab">Declaracion de Salud</a></li>
        <li role="presentation"><a href="#tab17" aria-controls="tab17" class="nav-link" role="tab" data-toggle="tab">Activa tu Afiliacion</a></li>
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
                            'class' => 'form-control',
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
                            'class' => 'form-control',
                            'maxlength' => true,
                        ],
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'fechanac')->widget(\kartik\date\DatePicker::class, [
                        'options' => ['placeholder' => 'Seleccione la fecha de nacimiento...'],
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
                                'class' => 'form-control',
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
                                'class' => 'form-control',
                            ],
                            'pluginOptions'=>[
                                'depends'=>['estado_id'],
                                'url'=>Url::to(['/site/municipio'])
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
                                'class' => 'form-control',
                            ],
                            'pluginOptions'=>[
                                'depends'=>['municipio_id'],
                                'url'=>Url::to(['/site/parroquia'])
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
                                'class' => 'form-control',
                            ],
                            'pluginOptions'=>[
                                'depends'=>['estado_id'],
                                'url'=>Url::to(['/site/ciudad'])
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
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
