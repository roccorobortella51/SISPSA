<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use kartik\depdrop\DepDrop;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use app\models\RmClinica;
use app\models\User;
use app\models\UserDatos;
use app\components\UserHelper;
use app\models\RmEstado;
use app\models\RmMunicipio;
use app\models\RmParroquia;
use app\models\RmCiudad;


/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */
/** @var yii\widgets\ActiveForm $form */

// Variables de control similares a rm_clinica
$readOnly = false;
if (!$model->isNewRecord) {
    $readOnly = true;
}

?>
<div class="corporativo-form">

    <?php $form = ActiveForm::begin([]); ?>

    <h3 class="box-title">Datos Generales del Corporativo</h3>
    <hr>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'nombre')->textInput([
                'maxlength' => true,
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Nombre completo del Corporativo',
                'autofocus' => true,
                'readonly' => $readOnly
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'rif')->widget(MaskedInput::class, [
                'mask' => 'J-99999999-9',
                'options' => [
                    'placeholder' => 'J-XXXXXXXX-X',
                    'class' => 'form-control form-control-lg',
                    'maxlength' => true,
                    'readonly' => $readOnly
                ]
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'telefono')->textInput([
                'maxlength' => 11,
                'placeholder' => 'Ej: 04121234567',
                'class' => 'form-control form-control-lg',
                'type' => 'tel',
            ]) ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ingrese el correo electrónico',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'estatus')->widget(Select2::classname(), [
                'data' => [
                    'Activo' => 'Activo',
                    'Inactivo' => 'Inactivo',
                    'Pendiente' => 'Pendiente',
                ],
                'options' => [
                    'placeholder' => 'Seleccione el estatus...',
                    'class' => 'form-control form-control-lg',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]) ?>
        </div>
    </div>

    <h3 class="box-title mt-4">Ubicación Geográfica</h3>
    <hr>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'estado')->widget(Select2::classname(), [
                'data' => UserHelper::getEstadosList(),
                'options' => [
                    'placeholder' => 'Seleccione un estado...',
                    'class' => 'form-control form-control-lg',
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
                'options' => [
                    'id' => 'municipio_id',
                    'placeholder' => 'Seleccione un municipio...',
                    'class' => 'form-control form-control-lg',
                ],
                'pluginOptions' => [
                    'depends' => ['estado_id'],
                    'url' => Url::to(['/site/municipio']),
                    'initialize' => true,
                ]
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'parroquia')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'options' => [
                    'id' => 'parroquia_id',
                    'placeholder' => 'Seleccione una parroquia...',
                    'class' => 'form-control form-control-lg',
                ],
                'pluginOptions' => [
                    'depends' => ['municipio_id'],
                    'url' => Url::to(['/site/parroquia']),
                ]
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'ciudad')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'options' => [
                    'id' => 'ciudad_id',
                    'placeholder' => 'Seleccione una ciudad...',
                    'class' => 'form-control form-control-lg',
                ],
                'pluginOptions' => [
                    'depends' => ['estado_id'],
                    'url' => Url::to(['/site/ciudad']),
                    'initialize' => true,
                ]
            ]); ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-12">
            <?= $form->field($model, 'direccion')->textarea([
                'rows' => 3,
                'placeholder' => 'Ingrese la dirección física completa',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <?= $form->field($model, 'domicilio_fiscal')->textarea([
        'rows' => 3,
        'placeholder' => 'Ingrese la dirección de domicilio fiscal (si es diferente a la dirección física)',
        'class' => 'form-control form-control-lg',
    ]) ?>

    <h3 class="box-title mt-4">Datos de Registro Mercantil</h3>
    <hr>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'lugar_registro')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: Registro Mercantil Primero de Caracas',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'fecha_registro_mercantil')->input('date', [
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'tomo_registro')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: 123A',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'folio_registro')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: 456B',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <h3 class="box-title mt-4">Persona de Contacto</h3>
    <hr>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'contacto_nombre')->textInput([
                'maxlength' => true,
                'placeholder' => 'Nombre completo de la persona de contacto',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'contacto_cedula')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: V-12345678',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <?= $form->field($model, 'contacto_telefono')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: 04121234567',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'contacto_cargo')->textInput([
                'maxlength' => true,
                'placeholder' => 'Ej: Gerente de Recursos Humanos',
                'class' => 'form-control form-control-lg',
            ]) ?>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- DATOS FINANCIEROS Y DE ACTIVIDAD             -->
    <!-- ============================================ -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="section-title">
                <i class="fas fa-chart-line"></i> Datos Financieros y de Actividad
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
                        'options' => [
                            'placeholder' => 'Seleccione la actividad económica...',
                            'class' => 'form-control form-control-lg',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'productos_servicios')->widget(Select2::class, [
                        'data' => [
                            'Independiente' => 'Independiente',
                            'Dependiente' => 'Dependiente',
                            'Societaria' => 'Societaria',
                        ],
                        'options' => [
                            'placeholder' => 'Seleccione el tipo de productos y servicios...',
                            'class' => 'form-control form-control-lg',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'utilidad_ejercicio_anterior')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ingrese la utilidad del ejercicio anterior',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'patrimonio')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ingrese el patrimonio',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- DATOS DEL REPRESENTANTE LEGAL                -->
    <!-- ============================================ -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="section-title">
                <i class="fas fa-user-tie"></i> Datos del Representante Legal
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'nombre_representante')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Nombre completo del representante',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'cedula_representante')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ejemplo: 12345678'
                    ])->label('Cédula de Identidad') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'fecha_nacimiento_representante')->input('date', [
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Seleccione la fecha de nacimiento'
                    ])->label('Fecha de Nacimiento') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'sexo_representante')->widget(Select2::class, [
                        'data' => [
                            'Masculino' => 'Masculino',
                            'Femenino' => 'Femenino',
                            'Otro' => 'Otro',
                        ],
                        'options' => [
                            'placeholder' => 'Seleccione el sexo...',
                            'class' => 'form-control form-control-lg',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ])->label('Sexo') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'nacionalidad_representante')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ej: Venezolana',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'estado_civil_representante')->widget(Select2::class, [
                        'data' => [
                            'Soltero' => 'Soltero',
                            'Casado' => 'Casado',
                            'Divorciado' => 'Divorciado',
                            'Viudo' => 'Viudo',
                        ],
                        'options' => [
                            'placeholder' => 'Seleccione el estado civil...',
                            'class' => 'form-control form-control-lg',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ])->label('Estado Civil') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'lugar_nacimiento_representante')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ej: Caracas',
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'profesion_representante')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ej: Ingeniero',
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'ocupacion_representante')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ej: Gerente General',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'descripcion_actividad_representante')->widget(Select2::class, [
                        'data' => [
                            'Independiente' => 'Independiente',
                            'Dependiente' => 'Dependiente',
                            'Societaria' => 'Societaria',
                        ],
                        'options' => [
                            'placeholder' => 'Seleccione la descripción de la actividad...',
                            'class' => 'form-control form-control-lg',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ])->label('Descripción de la Actividad') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'direccion_representante')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ingrese la dirección del representante'
                    ])->label('Dirección') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'telefono_representante')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ej: 04121234567'
                    ])->label('Teléfono') ?>
                </div>
            </div>
        </div>
    </div>

    <h3 class="box-title mt-4">Asociación con Clínicas y Empleados</h3>
    <hr>

    <div class="row g-3 mb-3">
        <div class="col-md-12">
            <?php
            $clinicasList = ArrayHelper::map(RmClinica::find()->orderBy('nombre')->all(), 'id', 'nombre');
            echo $form->field($model, 'clinicas_ids')->widget(Select2::class, [
                'data' => $clinicasList,
                'options' => [
                    'placeholder' => 'Seleccione una o varias clínicas...',
                    'multiple' => true,
                    'class' => 'form-control form-control-lg',
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label('Clínicas Asociadas');
            ?>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-12">
            <?php
            $afiliadosList = UserHelper::getAfiliadosList();

            echo $form->field($model, 'users_ids')->widget(Select2::class, [
                'data' => $afiliadosList,
                'options' => [
                    'placeholder' => 'Seleccione uno o varios afiliados...',
                    'multiple' => true,
                    'class' => 'form-control form-control-lg',
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])->label('Afiliados Asociados');
            ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-start">
            <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar', ['class' => 'btn btn-success btn-lg mr-4']) ?>

            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver',
                ['index'],
                [
                    'class' => 'btn btn-secondary btn-lg mr-4',
                    'onclick' => 'window.history.back(); return false;',
                    'title' => 'Volver a la página anterior',
                ]
            ) ?>

            <?php
            if (isset($isNewRecord) && $isNewRecord) {
                echo Html::button('<i class="fas fa-sync-alt mr-2"></i> Refrescar', [
                    'class' => 'btn btn-info btn-lg',
                    'id' => 'btn-refrescar-form'
                ]);
            }
            ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>