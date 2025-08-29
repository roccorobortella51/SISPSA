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

/* En este formulario, evitar flex en botones para compatibilidad con FileInput */
.user-datos-form .btn {
    display: inline-block !important;
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
    /* quitar restricción que podía causar solapamientos */
    max-width: none;
    margin: 0 auto;
    box-sizing: border-box;
    display: block;
    float: none;
}

.file-input .file-caption {
    width: 100% !important;
    box-sizing: border-box;
    border-radius: 8px;
}

/* Hacer que el contenedor de input-group del FileInput envuelva correctamente */
.file-input .input-group {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
}

/* EXCEPCIÓN: No usar flex en los botones dentro del widget FileInput */
.file-input .btn,
.file-input .btn-file {
    display: inline-block !important;
    align-items: initial !important;
    justify-content: initial !important;
}

/* Asegurar que el input file posicionado encima sea clickeable */
.file-input .btn-file {
    position: relative;
    overflow: hidden;
    z-index: 10; /* por encima de previews o captions */
}
.file-input .btn-file input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    pointer-events: auto;
}

/* Evitar distorsión en previsualización de imágenes (kartik FileInput) */
.file-input .kv-file-content img,
.file-input .file-preview-image {
    width: auto !important;
    height: auto !important;
    max-width: 100%;
    max-height: 180px;
    object-fit: contain;
}

.file-input .file-preview-frame {
    max-width: 220px;
}

/* Thumbnails en varias filas sin desbordar */
.file-input .file-preview-thumbnails {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

/* Evitar que el preview se superponga a los controles */
.file-input .file-preview,
.file-input .file-preview-thumbnails,
.file-input .file-preview-frame {
    z-index: 1;
}

/* Evitar que las acciones se salgan de la tarjeta */
.file-input .file-actions,
.file-input .file-footer-buttons,
.file-input .file-preview-status,
.file-input .fileinput-remove,
.file-input .fileinput-upload {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

/* Alinear correctamente la barra de botones */
.file-input .btn-file,
.file-input .fileinput-remove-button,
.file-input .fileinput-upload-button {
    margin-top: 8px;
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
                        <div class="col-md-6">
                            <?= $form->field($model, 'tiene_contratante_diferente')->checkbox(['class' => 'form-control-lg']) ?>
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
                        <i class="fas fa-user-plus"></i> Datos Adicionales del Afiliado
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-globe"></i>
                            <?= $form->field($model, 'nacionalidad')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'estado_civil')->widget(Select2::class, [
                                'data' => [
                                    'Soltero' => 'Soltero',
                                    'Casado' => 'Casado',
                                    'Divorciado' => 'Divorciado',
                                    'Viudo' => 'Viudo',
                                ],
                                'options' => ['placeholder' => 'Seleccione el estado civil...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Estado Civil') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= $form->field($model, 'lugar_nacimiento')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-briefcase"></i>
                            <?= $form->field($model, 'profesion')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-user-md"></i>
                            <?= $form->field($model, 'ocupacion')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'actividad_economica')->widget(Select2::class, [
                                'data' => [
                                    'Industrial' => 'Industrial',
                                    'Comercial' => 'Comercial',
                                    'Profesional' => 'Profesional',
                                    'Gubernamental' => 'Gubernamental',
                                ],
                                'options' => ['placeholder' => 'Seleccione la actividad económica...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Actividad Económica') ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-store"></i>
                            <?= $form->field($model, 'ramo_comercial')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'descripcion_actividad')->widget(Select2::class, [
                                'data' => [
                                    'Independiente' => 'Independiente',
                                    'Dependiente' => 'Dependiente',
                                    'Societaria' => 'Societaria',
                                ],
                                'options' => ['placeholder' => 'Seleccione la descripción de la actividad...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Descripción de la Actividad') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'ingreso_anual')->widget(Select2::class, [
                                'data' => [
                                    'De 1 a 5 Salarios mínimos' => 'De 1 a 5 Salarios mínimos',
                                    'De 6 a 10 Salarios mínimos' => 'De 6 a 10 Salarios mínimos',
                                    'De 11 a 20 Salarios mínimos' => 'De 11 a 20 Salarios mínimos',
                                    'De 20 Salarios mínimos en adelante' => 'De 20 Salarios mínimos en adelante',
                                ],
                                'options' => ['placeholder' => 'Seleccione el ingreso anual...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Ingreso Anual Bs') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-home"></i>
                            <?= $form->field($model, 'direccion_residencia')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección de residencia'
                            ])->label('Dirección de Residencia') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-building"></i>
                            <?= $form->field($model, 'direccion_oficina')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección de oficina'
                            ])->label('Dirección de Oficina') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-phone"></i>
                            <?= $form->field($model, 'telefono_residencia')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono residencia'
                            ])->label('Teléfono Residencia') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-phone-office"></i>
                            <?= $form->field($model, 'telefono_oficina')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono oficina'
                            ])->label('Teléfono Oficina') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-mobile-alt"></i>
                            <?= $form->field($model, 'telefono_celular')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono celular'
                            ])->label('Teléfono Celular') ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-user-tie"></i> Datos del Representante Legal
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-signature"></i>
                            <?= $form->field($model, 'nombre_representante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-signature"></i>
                            <?= $form->field($model, 'apellido_representante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-2">
                            <?= $form->field($model, 'tipo_cedula_representante')->widget(Select2::class, [
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
                                ])->label('Tipo Cédula')
                            ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-id-card"></i>
                            <?= $form->field($model, 'cedula_representante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ejemplo: 12345678'
                            ])->label('Cédula de Identidad') ?>
                        </div>
                        <div class="col-md-3 field-with-icon">
                            <i class="fas fa-birthday-cake"></i>
                            <?= $form->field($model, 'fecha_nacimiento_representante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'type' => 'date',
                                'placeholder' => 'Seleccione la fecha de nacimiento'
                            ])->label('Fecha de Nacimiento') ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'sexo_representante')->widget(Select2::class, [
                                'data' => [
                                    'Masculino' => 'Masculino',
                                    'Femenino' => 'Femenino',
                                    'Otro' => 'Otro',
                                ],
                                'options' => ['placeholder' => 'Seleccione el sexo...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Sexo') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-globe"></i>
                            <?= $form->field($model, 'nacionalidad_representante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'estado_civil_representante')->widget(Select2::class, [
                                'data' => [
                                    'Soltero' => 'Soltero',
                                    'Casado' => 'Casado',
                                    'Divorciado' => 'Divorciado',
                                    'Viudo' => 'Viudo',
                                ],
                                'options' => ['placeholder' => 'Seleccione el estado civil...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Estado Civil') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= $form->field($model, 'lugar_nacimiento_representante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-briefcase"></i>
                            <?= $form->field($model, 'profesion_representante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-user-md"></i>
                            <?= $form->field($model, 'ocupacion_representante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'descripcion_actividad_representante')->widget(Select2::class, [
                                'data' => [
                                    'Independiente' => 'Independiente',
                                    'Dependiente' => 'Dependiente',
                                    'Societaria' => 'Societaria',
                                ],
                                'options' => ['placeholder' => 'Seleccione la descripción de la actividad...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Descripción de la Actividad') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-home"></i>
                            <?= $form->field($model, 'direccion_representante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección'
                            ])->label('Dirección') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-phone"></i>
                            <?= $form->field($model, 'telefono_representante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono'
                            ])->label('Teléfono') ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-file-contract"></i> Datos del Plan Solicitado
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-file-contract"></i>
                            <?= $form->field($model, 'plan_seleccionado')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-coins"></i>
                            <?= $form->field($model, 'moneda')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-percentage"></i>
                            <?= $form->field($model, 'deducible')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-chart-line"></i>
                            <?= $form->field($model, 'limite_cobertura')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'cobertura_maternidad')->checkbox(['class' => 'form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-percentage"></i>
                            <?= $form->field($model, 'deducible_maternidad')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-chart-line"></i>
                            <?= $form->field($model, 'limite_cobertura_maternidad')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-users"></i> Grupo Familiar
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div id="grupo-familiar-container">
                                <!-- Aquí se agregarán dinámicamente los campos para el grupo familiar -->
                            </div>
                            <button type="button" id="agregar-miembro" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Agregar Miembro
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-user-tag"></i> Datos del Beneficiario
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-signature"></i>
                            <?= $form->field($model, 'nombre_beneficiario')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-id-card"></i>
                            <?= $form->field($model, 'cedula_beneficiario')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'parentesco_beneficiario')->widget(Select2::class, [
                                'data' => [
                                    'Padre' => 'Padre',
                                    'Madre' => 'Madre',
                                    'Hijo' => 'Hijo',
                                    'Hija' => 'Hija',
                                    'Hermano' => 'Hermano',
                                    'Hermana' => 'Hermana',
                                    'Cónyuge' => 'Cónyuge',
                                    'Otro' => 'Otro',
                                ],
                                'options' => ['placeholder' => 'Seleccione el parentesco...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Parentesco') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'sexo_beneficiario')->widget(Select2::class, [
                                'data' => [
                                    'Masculino' => 'Masculino',
                                    'Femenino' => 'Femenino',
                                    'Otro' => 'Otro',
                                ],
                                'options' => ['placeholder' => 'Seleccione el sexo...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Sexo') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-birthday-cake"></i>
                            <?= $form->field($model, 'fecha_nacimiento_beneficiario')->textInput([
                                'class' => 'form-control form-control-lg',
                                'type' => 'date',
                                'placeholder' => 'Seleccione la fecha de nacimiento'
                            ])->label('Fecha de Nacimiento') ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-money-check-alt"></i> Datos Bancarios
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-user"></i>
                            <?= $form->field($model, 'nombre_titular')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-id-card"></i>
                            <?= $form->field($model, 'cedula_titular')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-credit-card"></i>
                            <?= $form->field($model, 'numero_cuenta')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-university"></i>
                            <?= $form->field($model, 'banco')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'tipo_cuenta')->widget(Select2::class, [
                                'data' => [
                                    'Cuenta Corriente' => 'Cuenta Corriente',
                                    'Cuenta Ahorro' => 'Cuenta Ahorro',
                                    'Tarjeta Crédito Visa' => 'Tarjeta Crédito Visa',
                                    'Tarjeta Crédito MasterCard' => 'Tarjeta Crédito MasterCard',
                                ],
                                'options' => ['placeholder' => 'Seleccione el tipo de cuenta...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Tipo de Cuenta') ?>
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
                                // No sobreescribir el name; ActiveForm lo asigna correctamente como UserDatos[selfieFile]
                                'options' => [
                                    'accept' => 'image/*',
                                ],
                                'pluginOptions' => [
                                    'theme' => 'fa5',
                                    'browseClass' => 'btn btn-primary',
                                    'removeClass' => 'btn btn-secondary',
                                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                                    'showUpload' => false,
                                    'showCancel' => false,
                                    'showCaption' => false,
                                    'previewFileType' => 'image',
                                    'allowedFileExtensions' => ['jpg','jpeg','png'],
                                    'maxFileSize' => 2048,
                                    'dropZoneEnabled' => false,
                                    'showClose' => false,
                                    'browseLabel' => 'Seleccionar',
                                    'removeLabel' => 'Quitar',
                                    'fileActionSettings' => [
                                        'showZoom' => false,
                                        'showDrag' => false,
                                    ],
                                    'previewSettings' => [
                                        'image' => ['width' => '150px', 'height' => 'auto'],
                                    ],
                                    'layoutTemplates' => [
                                        'main1' => "{preview}{browse}{remove}",
                                        'main2' => "{preview}{browse}{remove}",
                                    ],
                                ],
                                ])->label('Foto del usuario');
                            ?>    
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'imagenIdentificacionFile')->widget(FileInput::classname(),[
                                // No sobreescribir el name; ActiveForm lo asigna correctamente como UserDatos[imagenIdentificacionFile]
                                'options' => [
                                    'accept' => 'image/*',
                                ],
                                'pluginOptions' => [
                                    'theme' => 'fa5',
                                    'browseClass' => 'btn btn-primary',
                                    'removeClass' => 'btn btn-secondary',
                                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                                    'previewFileType' => 'image',
                                    'showUpload' => false,
                                    'showCancel' => false,
                                    'showCaption' => false,
                                    'allowedFileExtensions' => ['jpg','jpeg','png'],
                                    'maxFileSize' => 5120,
                                    'dropZoneEnabled' => false,
                                    'showClose' => false,
                                    'browseLabel' => 'Seleccionar',
                                    'removeLabel' => 'Quitar',
                                    'fileActionSettings' => [
                                        'showZoom' => false,
                                        'showDrag' => false,
                                    ],
                                    'previewSettings' => [
                                        'image' => ['width' => '150px', 'height' => 'auto'],
                                    ],
                                    'layoutTemplates' => [
                                        'main1' => "{preview}{browse}{remove}",
                                        'main2' => "{preview}{browse}{remove}",
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

            <div class="card mb-4" id="contratante-section" style="display: none;">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-user-tie"></i> Datos del Contratante
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-signature"></i>
                            <?= $form->field($model, 'nombre_contratante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-signature"></i>
                            <?= $form->field($model, 'apellido_contratante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-2">
                            <?= $form->field($model, 'tipo_cedula_contratante')->widget(Select2::class, [
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
                                ])->label('Tipo Cédula')
                            ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-id-card"></i>
                            <?= $form->field($model, 'cedula_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ejemplo: 12345678'
                            ])->label('Cédula de Identidad') ?>
                        </div>
                        <div class="col-md-3 field-with-icon">
                            <i class="fas fa-birthday-cake"></i>
                            <?= $form->field($model, 'fecha_nacimiento_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'type' => 'date',
                                'placeholder' => 'Seleccione la fecha de nacimiento'
                            ])->label('Fecha de Nacimiento') ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'sexo_contratante')->widget(Select2::class, [
                                'data' => [
                                    'Masculino' => 'Masculino',
                                    'Femenino' => 'Femenino',
                                    'Otro' => 'Otro',
                                ],
                                'options' => ['placeholder' => 'Seleccione el sexo...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Sexo') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-globe"></i>
                            <?= $form->field($model, 'nacionalidad_contratante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'estado_civil_contratante')->widget(Select2::class, [
                                'data' => [
                                    'Soltero' => 'Soltero',
                                    'Casado' => 'Casado',
                                    'Divorciado' => 'Divorciado',
                                    'Viudo' => 'Viudo',
                                ],
                                'options' => ['placeholder' => 'Seleccione el estado civil...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Estado Civil') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= $form->field($model, 'lugar_nacimiento_contratante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-briefcase"></i>
                            <?= $form->field($model, 'profesion_contratante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-user-md"></i>
                            <?= $form->field($model, 'ocupacion_contratante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'actividad_economica_contratante')->widget(Select2::class, [
                                'data' => [
                                    'Industrial' => 'Industrial',
                                    'Comercial' => 'Comercial',
                                    'Profesional' => 'Profesional',
                                    'Gubernamental' => 'Gubernamental',
                                ],
                                'options' => ['placeholder' => 'Seleccione la actividad económica...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Actividad Económica') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'descripcion_actividad_contratante')->widget(Select2::class, [
                                'data' => [
                                    'Independiente' => 'Independiente',
                                    'Dependiente' => 'Dependiente',
                                    'Societaria' => 'Societaria',
                                ],
                                'options' => ['placeholder' => 'Seleccione la descripción de la actividad...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Descripción de la Actividad') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'ingreso_anual_contratante')->widget(Select2::class, [
                                'data' => [
                                    'De 1 a 5 Salarios mínimos' => 'De 1 a 5 Salarios mínimos',
                                    'De 6 a 10 Salarios mínimos' => 'De 6 a 10 Salarios mínimos',
                                    'De 11 a 20 Salarios mínimos' => 'De 11 a 20 Salarios mínimos',
                                    'De 20 Salarios mínimos en adelante' => 'De 20 Salarios mínimos en adelante',
                                ],
                                'options' => ['placeholder' => 'Seleccione el ingreso anual...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Ingreso Anual Bs') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-home"></i>
                            <?= $form->field($model, 'direccion_residencia_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección de residencia'
                            ])->label('Dirección de Residencia') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-building"></i>
                            <?= $form->field($model, 'direccion_oficina_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección de oficina'
                            ])->label('Dirección de Oficina') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-map-pin"></i>
                            <?= $form->field($model, 'direccion_cobro_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección de cobro'
                            ])->label('Dirección de Cobro') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-phone"></i>
                            <?= $form->field($model, 'telefono_residencia_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono residencia'
                            ])->label('Teléfono Residencia') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-phone-office"></i>
                            <?= $form->field($model, 'telefono_oficina_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono oficina'
                            ])->label('Teléfono Oficina') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <i class="fas fa-mobile-alt"></i>
                            <?= $form->field($model, 'telefono_celular_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono celular'
                            ])->label('Teléfono Celular') ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-envelope"></i>
                            <?= $form->field($model, 'email_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Correo electrónico',
                                'type' => 'email'
                            ])->label('Correo Electrónico') ?>
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

<?php
$this->registerJs(<<<'JS'
// Refuerzo de clic y diagnóstico para FileInput en este formulario
$(function(){
  var root = $('#user-datos-form');
  // Asegurar click habilitado
  root.find('.file-input .btn-file').css({'pointer-events':'auto'});
  root.find('.file-input .btn-file input[type="file"]').css({'pointer-events':'auto'});

  // Log de eventos para diagnóstico
  root.on('click', '.file-input .btn-file', function(e){
    console.log('[FileInput] btn-file click', e.target);
  });
  root.on('change', '.file-input input[type="file"]', function(e){
    console.log('[FileInput] input change -> files:', this.files);
  });

  // Fallback: si por alguna razón el input no recibe el click, forzarlo
  root.on('click', '.file-input .fileinput-browse', function(){
    var $grp = $(this).closest('.file-input');
    var $inp = $grp.find('.btn-file input[type="file"]').first();
    if ($inp.length) { $inp.trigger('click'); }
  });
  
  // Mostrar/ocultar sección de contratante
  function toggleContratanteSection() {
    if ($('#userdatos-tiene_contratante_diferente').is(':checked')) {
      $('#contratante-section').show();
    } else {
      $('#contratante-section').hide();
    }
  }
  
  // Inicializar estado al cargar la página
  toggleContratanteSection();
  
  // Cambiar estado cuando se marca/desmarca el checkbox
  $('#userdatos-tiene_contratante_diferente').on('change', function() {
    toggleContratanteSection();
 });
 
 // Funcionalidad para el grupo familiar
 var miembroIndex = 0;
 
 // Función para agregar un nuevo miembro al grupo familiar
 function agregarMiembro() {
   miembroIndex++;
   var miembroHtml = `
     <div class="card mb-3 miembro-familiar" data-index="${miembroIndex}">
       <div class="card-body">
         <div class="row">
           <div class="col-md-12">
             <h5>Miembro #${miembroIndex} <button type="button" class="btn btn-danger btn-sm float-right eliminar-miembro" data-index="${miembroIndex}"><i class="fas fa-trash"></i></button></h5>
           </div>
         </div>
         <div class="row">
           <div class="col-md-6 field-with-icon">
             <i class="fas fa-signature"></i>
             <input type="text" class="form-control form-control-lg" name="UserDatos[grupo_familiar][${miembroIndex}][nombre]" placeholder="Nombre completo">
           </div>
           <div class="col-md-6 field-with-icon">
             <i class="fas fa-id-card"></i>
             <input type="text" class="form-control form-control-lg" name="UserDatos[grupo_familiar][${miembroIndex}][cedula]" placeholder="Cédula de identidad">
           </div>
         </div>
         <div class="row mt-2">
           <div class="col-md-6">
             <select class="form-control form-control-lg" name="UserDatos[grupo_familiar][${miembroIndex}][parentesco]">
               <option value="">Seleccione el parentesco</option>
               <option value="Padre">Padre</option>
               <option value="Madre">Madre</option>
               <option value="Hijo">Hijo</option>
               <option value="Hija">Hija</option>
               <option value="Hermano">Hermano</option>
               <option value="Hermana">Hermana</option>
               <option value="Cónyuge">Cónyuge</option>
               <option value="Otro">Otro</option>
             </select>
           </div>
           <div class="col-md-6">
             <select class="form-control form-control-lg" name="UserDatos[grupo_familiar][${miembroIndex}][sexo]">
               <option value="">Seleccione el sexo</option>
               <option value="Masculino">Masculino</option>
               <option value="Femenino">Femenino</option>
               <option value="Otro">Otro</option>
             </select>
           </div>
         </div>
         <div class="row mt-2">
           <div class="col-md-12 field-with-icon">
             <i class="fas fa-birthday-cake"></i>
             <input type="date" class="form-control form-control-lg" name="UserDatos[grupo_familiar][${miembroIndex}][fecha_nacimiento]" placeholder="Fecha de nacimiento">
           </div>
         </div>
       </div>
     </div>
   `;
   $('#grupo-familiar-container').append(miembroHtml);
 }
 
 // Función para eliminar un miembro del grupo familiar
 function eliminarMiembro(index) {
   $('.miembro-familiar[data-index="' + index + '"]').remove();
 }
 
 // Evento para agregar miembro
 $('#agregar-miembro').on('click', function() {
   agregarMiembro();
 });
 
 // Evento para eliminar miembro (usando delegación de eventos)
 $('#grupo-familiar-container').on('click', '.eliminar-miembro', function() {
   var index = $(this).data('index');
   eliminarMiembro(index);
 });
 
 // Mostrar/ocultar sección de afiliado corporativo
 function toggleAfiliadoCorporativo() {
   if ($('#userdatos-user_datos_type_id').val() == '2') { // Asumiendo que 2 es el ID para afiliado corporativo
     $('#afiliado_corporativo_container').show();
   } else {
     $('#afiliado_corporativo_container').hide();
   }
 }
 
 // Inicializar estado al cargar la página
 toggleAfiliadoCorporativo();
 
 // Cambiar estado cuando se selecciona un tipo de afiliado
 $('#userdatos-user_datos_type_id').on('change', function() {
   toggleAfiliadoCorporativo();
 });
});
JS);
?>