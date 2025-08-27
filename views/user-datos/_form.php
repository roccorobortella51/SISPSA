<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\components\UserHelper;
use kartik\widgets\DatePicker;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;
use kartik\widgets\FileInput;
use app\models\UserDatosType;

$currentRoute = Yii::$app->controller->getRoute();

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var app\models\Contratos $modelContrato */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
/* Estilos generales mejorados */
.user-datos-form {
    background-color: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.form-control-lg {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 12px 15px;
    font-size: 16px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

/* Mejoras para las pestañas */
.nav-tabs {
    border-bottom: 2px solid #dee2e6;
    margin-bottom: 25px;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 600;
    padding: 12px 20px;
    transition: all 0.3s;
}

.nav-tabs .nav-link:hover {
    border-color: #adb5bd;
    color: #495057;
    background-color: transparent;
}

.nav-tabs .nav-link.active {
    background-color: transparent !important;
    color: #007bff !important;
    border-color: #007bff #007bff #fff !important;
    border-bottom: 3px solid #007bff !important;
}

/* Estilo para campos con iconos */
.field-with-icon {
    position: relative;
}

.field-with-icon .form-control {
    padding-left: 40px;
}

.field-with-icon i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 5;
}

/* Botones mejorados */
.btn {
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn i {
    margin-right: 8px;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-info {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.btn-info:hover {
    background-color: #138496;
    border-color: #117a8b;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Mejoras para los select2 */
.select2-container--krajee .select2-selection--single {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 8px 15px;
    height: auto;
}

/* Mejoras para file inputs */
.file-input {
    width: 100% !important;
}

.file-input .file-preview {
    width: 100% !important;
    margin: 0 auto;
    box-sizing: border-box;
}

.file-input .file-caption {
    width: 100% !important;
    box-sizing: border-box;
    border-radius: 8px;
}

/* Títulos de sección */
h1 {
    color: #2c3e50;
    font-weight: 700;
    margin: 30px 0 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eaecef;
}

/* Tarjetas para agrupar campos */
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-body {
    padding: 20px;
}

.section-title {
    font-size: 18px;
    color: #2c3e50;
    margin-bottom: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.section-title i {
    margin-right: 10px;
    color: #007bff;
}

/* Mejoras responsivas */
@media (max-width: 768px) {
    .user-datos-form {
        padding: 15px;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .d-flex.justify-content-start.gap-3 {
        flex-direction: column;
    }
}

/* Animaciones suaves */
.form-control, .btn, .nav-link {
    transition: all 0.3s ease;
}

/* Focus states mejorados */
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

/* Estilo para campos requeridos */
.required label:after {
    content: " *";
    color: #dc3545;
}
</style>

<?php
$getPlanMontoUrl = Url::to(['/site/planmonto']);
$js = <<<JS
// [El código JavaScript permanece igual]
JS;
$this->registerJs($js);
?>

<div class="user-datos-form">
<?php $form = ActiveForm::begin([
    'id' => 'user-datos-form',
    'options' => ['enctype' => 'multipart/form-data', 'class' => 'enhanced-form']
    ]); ?>

<?php
$jsValidation = <<<JS
// [El código de validación permanece igual]
JS;
$this->registerJs($jsValidation);
?>

<?php if (!$model->isNewRecord) { ?>
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
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i> Información Básica
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'user_datos_type_id')->widget(Select2::class, [
                                'data' => UserDatosType::getList(),
                                'options' => [
                                    'placeholder' => 'Seleccionar tipo de afiliado...',
                                    'class' => 'form-control form-control-lg',
                                    'id' => 'user_datos_type_id_field',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Tipo de Afiliado') ?>
                        </div>

                        <div class="col-md-6" id="afiliado_corporativo_container" style="display: none;">
                            <?= $form->field($model, 'afiliado_corporativo_id')->widget(Select2::class, [
                                'data' => UserHelper::getCorporativoList(),
                                'options' => [
                                    'placeholder' => 'Seleccione.',
                                    'class' => 'form-control form-control-lg',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Afiliado Corporativo') ?>
                        </div>

                        <div class="col-md-6">
                            <?= $form->field($model, 'asesor_id')->widget(Select2::classname(), [
                                    'data' => UserHelper::getAgenteFuerzaList(),
                                    'options' => [
                                        'placeholder' => 'Seleccione el asesor',
                                        'class' => 'form-control form-control-lg',
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => false,
                                    ],
                            ])->label('NOMBRE DEL ASESOR')
                            ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-envelope"></i>
                            <?= $form->field($model, 'email')->textInput(['class' => 'form-control form-control-lg',]) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-phone"></i>
                            <?= $form->field($model, 'telefono')->widget(MaskedInput::class, [
                                'mask' =>  '99999999999',
                                'options' => [
                                    'placeholder' => '(XXXX) XXX-XXXX',
                                    'class' => 'form-control  form-control-lg',
                                    'maxlength' => true,
                                ],
                                'clientOptions' => [
                                'clearIncomplete' => true,
                                'unmaskAsSubmit' => true,
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-user"></i> Datos Personales
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-signature"></i>
                            <?= $form->field($model, 'nombres')->textInput(['class' => 'form-control form-control-lg',]) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-signature"></i>
                            <?= $form->field($model, 'apellidos')->textInput(['class' => 'form-control form-control-lg',]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-1">
                            <?= $form->field($model, 'tipo_cedula')->widget(Select2::class, [
                                'data' => [
                                    'V' => 'V',
                                    'E' => 'E',
                                    'J' => 'J',
                                    'P' => 'P',
                                    'N' => 'N',
                                    'M' => 'M',
                                ],
                                'options' => [
                                    'placeholder' => 'Tipo',
                                    'class' => 'form-control form-control-lg', 
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true, 
                                ],
                                ])->label('Tipo')
                            ?>
                        </div>

                        <?php if ($model->isNewRecord) { ?>
                            <div class="col-md-2 field-with-icon">
                                <i class="fas fa-id-card"></i>
                                <?= $form->field($model, 'cedula')->textInput([
                                    'class' => 'form-control form-control-lg',
                                    'placeholder' => 'Ejemplo: 12345678'
                                ])->label('Cédula de Identidad')
                                ?>
                            </div>
                        <?php } else { ?>
                            <div class="col-md-2 field-with-icon">
                                <i class="fas fa-id-card"></i>
                                <?= $form->field($model, 'cedula')->textInput([
                                    'class' => 'form-control form-control-lg',
                                    'readonly' => true,
                                ])->label('Cédula de Identidad')
                                ?>
                            </div>
                        <?php } ?>

                        <div class="col-md-3 field-with-icon">
                            <i class="fas fa-birthday-cake"></i>
                            <?= $form->field($model, 'fechanac')->textInput([
                                'class' => 'form-control form-control-lg',
                                'type' => 'date',
                                'placeholder' => 'Seleccione su fecha de nacimiento'
                            ])->label('Fecha de Nacimiento') ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($model, 'sexo')->widget(Select2::class, [
                                'data' => [
                                    'Masculino' => 'Masculino',
                                    'Femenino' => 'Femenino',
                                    'Otro' => 'Otro',
                                ],
                                'options' => ['placeholder' => 'Seleccione el sexo...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'tipo_sangre')->widget(Select2::class, [
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
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-map-marker-alt"></i> Ubicación
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
                            ]); ?>
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
                            ]); ?>
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
                                ]
                            ]); ?>
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
                    
                    <div class='row'>
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-home"></i>
                            <?= $form->field($model, 'direccion')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección'
                            ])->label('Dirección') ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-camera"></i> Imágenes
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'selfieFile')->widget(FileInput::classname(),[
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
                                ],
                                ])->label('Foto del usuario');
                            ?>    
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'imagenIdentificacionFile')->widget(FileInput::classname(),[
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
                                ],
                                ])->label('Imagen de identificación');
                            ?>    
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-file-contract"></i> Datos del Contrato
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'clinica_id')->widget(Select2::classname(), [
                                'data' => UserHelper::getClinicasList(),
                                'options' => [
                                    'placeholder' => 'Seleccione',
                                    'class' => 'form-control  form-control-lg',
                                    'id' => 'clinica_id'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Clínica'); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($modelContrato, 'plan_id')->widget(DepDrop::classname(), [
                                'type' => DepDrop::TYPE_SELECT2,
                                'options'=>[
                                    'id'=>'plan_id',
                                    'placeholder' => 'Seleccione',
                                    'class' => 'form-control  form-control-lg',
                                    'allowClear' => true,
                                ],
                                'pluginOptions'=>[
                                    'depends'=>['clinica_id'],
                                    'url'=>Url::to(['/site/planes']),
                                    'initialize' => true,
                                    ]
                                ])->label('Plan');
                            ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-calendar-alt"></i>
                            <?= $form->field($modelContrato, 'fecha_ini')->textInput([
                                'class' => 'form-control form-control-lg',
                                'type' => 'date',
                                'placeholder' => 'Seleccione la fecha de inicio'
                            ])->label('Fecha de Inicio') ?>
                        </div>

                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-calendar-times"></i>
                            <?= $form->field($modelContrato, 'fecha_ven')->textInput([
                                'class' => 'form-control form-control-lg',
                                'type' => 'date',
                                'placeholder' => 'Seleccione la fecha de vencimiento'
                            ])->label('Fecha de Vencimiento') ?>
                        </div>
                        <div class="col-md-4 field-with-icon" style="display:none;">
                            <i class="fas fa-dollar-sign"></i>
                            <?= $form->field($modelContrato, 'monto')->textInput([
                                'class' => 'form-control  form-control-lg', 
                                'type' => 'number',
                                'placeholder' => '0.00'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 d-flex justify-content-start gap-3">
                    <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
                    
                    <?= Html::a(
                        '<i class="fas fa-undo mr-2"></i> Volver', 
                        '#',
                        [
                            'class' => 'btn btn-secondary btn-lg', 
                            'onclick' => 'window.history.back(); return false;', 
                            'title' => 'Volver a la página anterior', 
                        ]
                    ) ?>

                    <?php if ($model->isNewRecord) { ?>
                        <?= Html::button('<i class="fas fa-sync-alt mr-2"></i> Refrescar', [
                            'class' => 'btn btn-info btn-lg',
                            'id' => 'btn-refrescar-form'
                        ]) ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>