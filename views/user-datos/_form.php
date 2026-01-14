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

<div class="user-datos-form">
    <?php $form = ActiveForm::begin([
        'id' => 'user-datos-form',
        'options' => ['enctype' => 'multipart/form-data', 'class' => 'enhanced-form']
    ]); ?>

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

            <div class="col"> <?= Html::a('<i class="fas fa-id-card"></i> Afiliación', Url::to(['contratos/index', 'user_id' => $model->id]), [
                                    'class' => 'btn btn-primary btn-lg w-100 ' . ($currentRoute === 'contratos/index' ? 'active' : ''),
                                    'data-pjax' => '0'
                                ]) ?>
            </div>
        </div>
    <?php } ?>

    <?php if (!$model->isNewRecord) : ?>
        <?= $form->field($model, 'id')->hiddenInput(['id' => 'userdatos-id'])->label(false) ?>
    <?php endif; ?>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="tab13">

            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i> Información Básica y Datos del Contrato
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'user_datos_type_id')->widget(Select2::class, [
                                'data' => UserDatosType::getList(),
                                'options' => [
                                    'placeholder' => 'Seleccionar tipo de afiliado...',
                                    'class' => 'form-control form-control-lg conditional-required',
                                    'id' => 'user_datos_type_id_field',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Tipo de Afiliado <span class="text-danger">*</span>') ?>
                        </div>

                        <div class="col-md-6" id="afiliado_corporativo_container" style="display: none;">
                            <?= $form->field($model, 'afiliado_corporativo_id')->widget(Select2::class, [
                                'data' => UserHelper::getCorporativoList(),
                                'options' => [
                                    'id' => 'afiliado_corporativo_id',
                                    'placeholder' => 'Seleccione.',
                                    'class' => 'form-control form-control-lg corporativo-required',
                                    'id' => 'corporativo_id'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Afiliado Corporativo <span class="text-danger conditional-asterisk">*</span>') ?>
                        </div>

                        <div class="col-md-6">
                            <?= $form->field($model, 'clinica_id')->widget(DepDrop::classname(), [
                                'type' => DepDrop::TYPE_SELECT2,
                                'options' => [
                                    'id' => 'clinica_id',
                                    'placeholder' => 'Seleccione la Clínica',
                                    'class' => 'form-control  form-control-lg conditional-required',
                                    'allowClear' => true,
                                ],
                                'pluginOptions' => [
                                    'depends' => ['user_datos_type_id_field', 'corporativo_id',],
                                    'url' => Url::to(['clinicas']),
                                    'initialize' => true,
                                ]
                            ])->label('Clínica <span class="text-danger conditional-asterisk">*</span>');
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($modelContrato, 'plan_id')->widget(DepDrop::classname(), [
                                'type' => DepDrop::TYPE_SELECT2,
                                'options' => [
                                    'id' => 'plan_id',
                                    'placeholder' => 'Seleccione el Plan',
                                    'class' => 'form-control form-control-lg conditional-required',
                                    'allowClear' => true,
                                ],
                                'pluginOptions' => [
                                    'depends' => ['clinica_id'],
                                    'url' => Url::to(['/site/planes']),
                                    'initialize' => true,
                                ],
                                'pluginEvents' => [
                                    "change" => "function(e) {
                                        datosplan($(this).val());
                                    }",
                                ]
                            ])->label('Plan <span class="text-danger conditional-asterisk">*</span>') ?>
                        </div>

                        <div class="col-md-6">
                            <?= $form->field($model, 'asesor_id')->widget(Select2::classname(), [
                                'data' => UserHelper::getAgenteFuerzaList(),
                                'options' => [
                                    'placeholder' => 'Seleccione el Agente ...',
                                    'class' => 'form-control form-control-lg',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => false,
                                ],
                            ])->label('NOMBRE DEL AGENTE (Vendedor)');
                            ?>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 field-with-icon">

                            <?= $form->field($modelContrato, 'fecha_ini')->textInput([
                                'class' => 'form-control form-control-lg fecha-ini-field',
                                'type' => 'date',
                                'required' => true,
                                'placeholder' => 'Seleccione la fecha de inicio'
                            ])->label('Fecha de Inicio') ?>
                        </div>

                        <div class="col-md-6 field-with-icon fecha-ven-container" style="display: none;">

                            <?= $form->field($modelContrato, 'fecha_ven')->textInput([
                                'class' => 'form-control form-control-lg fecha-ven-field',
                                'type' => 'date',
                                'required' => true,
                                'placeholder' => 'Seleccione la fecha de vencimiento'
                            ])->label('Fecha de Vencimiento') ?>
                        </div>
                        <div class="col-md-4 field-with-icon" style="display:none;">

                            <?= $form->field($modelContrato, 'monto')->textInput([
                                'class' => 'form-control  form-control-lg',
                                'type' => 'number',
                                'placeholder' => '0.00'
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-6 field-with-icon">

                            <?= $form->field($model, 'email')->textInput(['class' => 'form-control form-control-lg',]) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">

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

                        <div class="col-md-6">
                            <?= $form->field($model, 'tiene_contratante_diferente')->checkbox([
                                'class' => 'form-control-lg',
                                'uncheck' => 0,
                                'value' => 1,
                                'checked' => (bool)$model->tiene_contratante_diferente,
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group field-userdatos-tiene_dependientes">
                                <div class="custom-control custom-checkbox">
                                    <input type="hidden" name="UserDatos[tiene_dependientes]" value="0">
                                    <input type="checkbox" id="tiene_dependientes" name="UserDatos[tiene_dependientes]"
                                        value="1" class="custom-control-input"
                                        <?= (!$model->isNewRecord && \app\models\Dependientes::isTitular($model->id)) ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="tiene_dependientes">¿Tiene Dependientes?</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4" id="dependientes-section" style="display: none;">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-family"></i> Gestión de Dependientes
                        <span class="badge badge-info badge-pill ml-2" id="dependientes-count">0</span>
                    </div>

                    <?php if ($model->isNewRecord): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Para agregar dependientes, primero debe <strong>Guardar</strong> el registro de este titular.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Busque un afiliado existente para agregarlo como dependiente.
                        </div>

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-9">
                                <?= Select2::widget([
                                    'name' => 'searchDependiente',
                                    'id' => 'search-dependiente',
                                    'options' => [
                                        'placeholder' => 'Buscar afiliado por nombre, cédula o email...',
                                        'class' => 'form-control',
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 3,
                                        'ajax' => [
                                            'url' => Url::to(['/user-datos/search-afiliados']),
                                            'dataType' => 'json',
                                            'delay' => 250,
                                            'data' => new \yii\web\JsExpression('function(params) { 
                                                return {
                                                    q: params.term,
                                                    current_user: ' . ($model->id ? $model->id : 0) . ',
                                                    _csrf: yii.getCsrfToken()
                                                }; 
                                            }'),
                                            'processResults' => new \yii\web\JsExpression('function(data) { 
                                                return { results: data.results };
                                            }'),
                                            'cache' => true
                                        ],
                                        'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
                                        'templateResult' => new \yii\web\JsExpression('function(repo) { 
                                            if (repo.loading) return repo.text;
                                            return "<div class=\'select2-result-repository clearfix\'>" +
                                                   "<div class=\'select2-result-repository__meta\'>" +
                                                   "<div class=\'select2-result-repository__title\'><b>" + repo.nombre_completo + "</b></div>" +
                                                   "<div class=\'select2-result-repository__description\'><i class=\'fas fa-id-card\'></i> " + repo.cedula + "</div>" +
                                                   "</div></div>";
                                        }'),
                                        'templateSelection' => new \yii\web\JsExpression('function (repo) { 
                                            return repo.nombre_completo || repo.text; 
                                        }'),
                                    ],
                                ]) ?>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="btn-add-dependiente" class="btn btn-primary btn-block">
                                    <i class="fas fa-plus"></i> Agregar
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div id="dependientes-list" class="mt-4">
                        <div class="text-center py-4" id="no-dependientes">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay dependientes asociados</p>
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
                        <div class="col-md-4 field-with-icon">

                            <?= $form->field($model, 'moneda')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-4 field-with-icon">

                            <?= $form->field($model, 'deducible')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-4 field-with-icon">

                            <?= $form->field($model, 'limite_cobertura')->textInput(['class' => 'form-control form-control-lg', 'readonly' => true]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'cobertura_maternidad')->checkbox(['class' => 'form-control-lg']) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 field-with-icon">

                            <?= $form->field($model, 'deducible_maternidad')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">

                            <?= $form->field($model, 'limite_cobertura_maternidad')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">
                        <i class="fas fa-user-plus"></i> Datos del Afiliado
                    </div>

                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'nombres')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'apellidos')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-1">
                            <?= $form->field($model, 'tipo_cedula')->widget(Select2::class, [
                                'data' => ['V' => 'V', 'E' => 'E', 'J' => 'J', 'P' => 'P', 'N' => 'N', 'M' => 'M', 'Menor Sin Cédula' => 'Menor Sin Cédula'],
                                'options' => [
                                    'placeholder' => 'Tipo',
                                    'class' => 'form-control form-control-lg',
                                    'id' => 'userdatos-tipo_cedula'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Tipo') ?>
                        </div>

                        <div class="col-md-2 field-with-icon">
                            <?= $form->field($model, 'cedula')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ejemplo: 12345678',
                                'id' => 'userdatos-cedula'
                            ])->label('Cédula de Identidad <span class="text-danger conditional-asterisk">*</span>') ?>
                        </div>

                        <div class="col-md-3" id="consecutivo-container" style="display: none;">
                            <div class="form-group field-userdatos-consecutivo_menor">
                                <label class="control-label" for="userdatos-consecutivo_menor">Consecutivo del Menor</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">-</span>
                                    </div>
                                    <?= $form->field($model, 'consecutivo_menor')->textInput([
                                        'class' => 'form-control form-control-lg',
                                        'type' => 'number',
                                        'min' => 1,
                                        'max' => 20,
                                        'placeholder' => '1-20',
                                        'id' => 'userdatos-consecutivo_menor'
                                    ])->label(false) ?>
                                </div>
                                <small class="form-text text-muted">Número consecutivo para identificar al menor</small>
                                <div class="help-block"></div>
                            </div>
                        </div>

                        <div class="col-md-3 field-with-icon">
                            <?= $form->field($model, 'fechanac')->textInput([
                                'class' => 'form-control form-control-lg',
                                'type' => 'date',
                                'placeholder' => 'Seleccione su fecha de nacimiento'
                            ])->label('Fecha de Nacimiento') ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($model, 'sexo')->widget(Select2::class, [
                                'data' => ['Masculino' => 'Masculino', 'Femenino' => 'Femenino', 'Otro' => 'Otro'],
                                'options' => ['placeholder' => 'Seleccione el sexo...'],
                                'pluginOptions' => ['allowClear' => true],
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 field-with-icon">
                            <?= $form->field($model, 'nacionalidad')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'estado_civil')->widget(Select2::class, [
                                'data' => ['Soltero' => 'Soltero', 'Casado' => 'Casado', 'Divorciado' => 'Divorciado', 'Viudo' => 'Viudo'],
                                'options' => ['placeholder' => 'Seleccione el estado civil...'],
                                'pluginOptions' => ['allowClear' => true],
                            ])->label('Estado Civil') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <?= $form->field($model, 'lugar_nacimiento')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'profesion')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'ocupacion')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'actividad_economica')->widget(Select2::class, [
                                'data' => ['Industrial' => 'Industrial', 'Comercial' => 'Comercial', 'Profesional' => 'Profesional', 'Gubernamental' => 'Gubernamental'],
                                'options' => ['placeholder' => 'Seleccione la actividad económica...'],
                                'pluginOptions' => ['allowClear' => true],
                            ])->label('Actividad Económica') ?>
                        </div>
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'ramo_comercial')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'descripcion_actividad')->widget(Select2::class, [
                                'data' => ['Independiente' => 'Independiente', 'Dependiente' => 'Dependiente', 'Societaria' => 'Societaria'],
                                'options' => ['placeholder' => 'Seleccione la descripción de la actividad...'],
                                'pluginOptions' => ['allowClear' => true],
                            ])->label('Descripción de la Actividad') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'ingreso_anual')->widget(Select2::class, [
                                'data' => ['De 1 a 5 Salarios mínimos' => 'De 1 a 5 Salarios mínimos', 'De 6 a 10 Salarios mínimos' => 'De 6 a 10 Salarios mínimos', 'De 11 a 20 Salarios mínimos' => 'De 11 a 20 Salarios mínimos', 'De 20 Salarios mínimos en adelante' => 'De 20 Salarios mínimos en adelante'],
                                'options' => ['placeholder' => 'Seleccione el ingreso anual...'],
                                'pluginOptions' => ['allowClear' => true],
                            ])->label('Ingreso Anual Bs') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($model, 'estado')->widget(Select2::classname(), [
                                'data' => UserHelper::getEstadosList(),
                                'options' => [
                                    'placeholder' => 'Seleccione',
                                    'class' => 'form-control form-control-lg',
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
                                'options' => [
                                    'id' => 'municipio_id',
                                    'placeholder' => 'Seleccione',
                                    'class' => 'form-control form-control-lg',
                                ],
                                'pluginOptions' => [
                                    'depends' => ['estado_id'],
                                    'url' => Url::to(['/site/municipio']),
                                    'initialize' => true,
                                ]
                            ]); ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'parroquia')->widget(DepDrop::classname(), [
                                'type' => DepDrop::TYPE_SELECT2,
                                'options' => [
                                    'id' => 'parroquia_id',
                                    'placeholder' => 'Seleccione',
                                    'class' => 'form-control form-control-lg',
                                ],
                                'pluginOptions' => [
                                    'depends' => ['municipio_id'],
                                    'url' => Url::to(['/site/parroquia']),
                                    'initialize' => true,
                                ]
                            ]); ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'ciudad')->widget(DepDrop::classname(), [
                                'type' => DepDrop::TYPE_SELECT2,
                                'options' => [
                                    'id' => 'ciudad_id',
                                    'placeholder' => 'Seleccione',
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

                    <div class='row'>
                        <div class="col-md-12 field-with-icon">
                            <?= $form->field($model, 'direccion')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección'
                            ])->label('Dirección de Residencia') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <?= $form->field($model, 'direccion_oficina')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección de oficina'
                            ])->label('Dirección de Oficina') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 field-with-icon">
                            <?= $form->field($model, 'direccion_cobro')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección de cobro'
                            ])->label('Dirección de Cobro') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 field-with-icon">
                            <?= $form->field($model, 'telefono_residencia')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono residencia'
                            ])->label('Teléfono Residencia') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <?= $form->field($model, 'telefono_oficina')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono oficina'
                            ])->label('Teléfono Oficina') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">
                            <?= $form->field($model, 'telefono_celular')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono celular'
                            ])->label('Teléfono Celular') ?>
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

                            <?= $form->field($model, 'nombre_contratante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">

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

                            <?= $form->field($model, 'cedula_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ejemplo: 12345678'
                            ])->label('Cédula de Identidad') ?>
                        </div>
                        <div class="col-md-3 field-with-icon">

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

                            <?= $form->field($model, 'lugar_nacimiento_contratante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 field-with-icon">

                            <?= $form->field($model, 'profesion_contratante')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">

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

                            <?= $form->field($model, 'direccion_residencia_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección de residencia'
                            ])->label('Dirección de Residencia') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 field-with-icon">

                            <?= $form->field($model, 'direccion_oficina_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección de oficina'
                            ])->label('Dirección de Oficina') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 field-with-icon">

                            <?= $form->field($model, 'direccion_cobro_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese la dirección de cobro'
                            ])->label('Dirección de Cobro') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 field-with-icon">

                            <?= $form->field($model, 'telefono_residencia_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono residencia'
                            ])->label('Teléfono Residencia') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">

                            <?= $form->field($model, 'telefono_oficina_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono oficina'
                            ])->label('Teléfono Oficina') ?>
                        </div>
                        <div class="col-md-4 field-with-icon">

                            <?= $form->field($model, 'telefono_celular_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Teléfono celular'
                            ])->label('Teléfono Celular') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 field-with-icon">

                            <?= $form->field($model, 'email_contratante')->textInput([
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Correo electrónico',
                                'type' => 'email'
                            ])->label('Correo Electrónico') ?>
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
                                <?php if (!$model->isNewRecord && $model->grupo_familiar): ?>
                                    <?php
                                    $grupoFamiliar = json_decode($model->grupo_familiar, true) ?: [];
                                    $miembroIndex = 0;
                                    foreach ($grupoFamiliar as $member):
                                        $miembroIndex++;
                                    ?>
                                        <div class="card mb-3 miembro-familiar" data-index="<?= $miembroIndex ?>">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <h5>Miembro #<?= $miembroIndex ?> <button type="button" class="btn btn-danger btn-sm float-right eliminar-miembro" data-index="<?= $miembroIndex ?>"><i class="fas fa-trash"></i></button></h5>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 field-with-icon">
                                                        <i class="fas fa-signature"></i>
                                                        <input type="text" class="form-control form-control-lg" name="UserDatos[grupo_familiar][<?= $miembroIndex ?>][nombre]" value="<?= htmlspecialchars($member['nombre'] ?? '') ?>" placeholder="Nombre completo">
                                                    </div>
                                                    <div class="col-md-6 field-with-icon">
                                                        <i class="fas fa-id-card"></i>
                                                        <input type="text" class="form-control form-control-lg" name="UserDatos[grupo_familiar][<?= $miembroIndex ?>][cedula]" value="<?= htmlspecialchars($member['cedula'] ?? '') ?>" placeholder="Cédula de identidad">
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-6">
                                                        <select class="form-control form-control-lg" name="UserDatos[grupo_familiar][<?= $miembroIndex ?>][parentesco]">
                                                            <option value="">Seleccione el parentesco</option>
                                                            <option value="Padre" <?= ($member['parentesco'] ?? '') == 'Padre' ? 'selected' : '' ?>>Padre</option>
                                                            <option value="Madre" <?= ($member['parentesco'] ?? '') == 'Madre' ? 'selected' : '' ?>>Madre</option>
                                                            <option value="Hijo" <?= ($member['parentesco'] ?? '') == 'Hijo' ? 'selected' : '' ?>>Hijo</option>
                                                            <option value="Hija" <?= ($member['parentesco'] ?? '') == 'Hija' ? 'selected' : '' ?>>Hija</option>
                                                            <option value="Hermano" <?= ($member['parentesco'] ?? '') == 'Hermano' ? 'selected' : '' ?>>Hermano</option>
                                                            <option value="Hermana" <?= ($member['parentesco'] ?? '') == 'Hermana' ? 'selected' : '' ?>>Hermana</option>
                                                            <option value="Cónyuge" <?= ($member['parentesco'] ?? '') == 'Cónyuge' ? 'selected' : '' ?>>Cónyuge</option>
                                                            <option value="Otro" <?= ($member['parentesco'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <select class="form-control form-control-lg" name="UserDatos[grupo_familiar][<?= $miembroIndex ?>][sexo]">
                                                            <option value="">Seleccione el sexo</option>
                                                            <option value="Masculino" <?= ($member['sexo'] ?? '') == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                                            <option value="Femenino" <?= ($member['sexo'] ?? '') == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                                                            <option value="Otro" <?= ($member['sexo'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-12 field-with-icon">
                                                        <i class="fas fa-birthday-cake"></i>
                                                        <input type="date" class="form-control form-control-lg" name="UserDatos[grupo_familiar][<?= $miembroIndex ?>][fecha_nacimiento]" value="<?= htmlspecialchars($member['fecha_nacimiento'] ?? '') ?>" placeholder="Fecha de nacimiento">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                        <i class="fas fa-money-check-alt"></i> Datos Bancarios
                    </div>

                    <div class="row">
                        <div class="col-md-6 field-with-icon">

                            <?= $form->field($model, 'nombre_titular')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">

                            <?= $form->field($model, 'cedula_titular')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 field-with-icon">

                            <?= $form->field($model, 'numero_cuenta')->textInput(['class' => 'form-control form-control-lg']) ?>
                        </div>
                        <div class="col-md-6 field-with-icon">

                            <?= $form->field($model, 'banco_id')->widget(Select2::class, [
                                'data' => \yii\helpers\ArrayHelper::map(\app\models\Banco::find()->where(['estatus' => 'Activo'])->all(), 'id', 'nombre'),
                                'options' => [
                                    'placeholder' => 'Seleccione un banco...',
                                    'class' => 'form-control form-control-lg',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Banco') ?>
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
                        <i class="fas fa-camera"></i> Imágenes
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'selfieFile')->widget(FileInput::classname(), [
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
                                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png'],
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
                            <?= $form->field($model, 'imagenIdentificacionFile')->widget(FileInput::classname(), [
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
                                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png'],
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

<div class="modal fade" id="editDependienteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-edit"></i> Datos del Dependiente</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-dependiente-form">
                    <input type="hidden" id="edit-dependiente-id" name="id">
                    <input type="hidden" id="edit-dependiente-user-id" name="user_id">

                    <div class="form-group">
                        <label>Afiliado</label>
                        <input type="text" id="display-nombre-dependiente" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="edit-parentesco">Parentesco <span class="text-danger">*</span></label>
                        <select id="edit-parentesco" class="form-control">
                            <option value="">Seleccione...</option>
                            <option value="Hijo">Hijo</option>
                            <option value="Hija">Hija</option>
                            <option value="Cónyuge">Cónyuge</option>
                            <option value="Madre">Madre</option>
                            <option value="Padre">Padre</option>
                            <option value="Hermano">Hermano</option>
                            <option value="Hermana">Hermana</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit-porcentaje">Porcentaje de Pago (%)</label>
                        <input type="number" id="edit-porcentaje" class="form-control" value="100" min="0" max="100">
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit-activo" checked>
                            <label class="custom-control-label" for="edit-activo">Activo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-save-dependiente">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="dependiente-template">
    <div class="card mb-2 dependiente-card border-left-primary shadow-sm" data-id="{id}">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="m-0 font-weight-bold text-primary">{nombre_completo}</h6>
                    <small class="text-muted">
                        <i class="fas fa-id-card"></i> {cedula} &nbsp;|&nbsp; 
                        <span class="badge badge-info">{parentesco}</span>
                        {porcentaje_badge}
                    </small>
                </div>
                <div class="col-md-4 text-right">
                    <button type="button" class="btn btn-sm btn-info edit-dependiente" title="Editar"><i class="fas fa-pencil-alt"></i></button>
                    <button type="button" class="btn btn-sm btn-danger remove-dependiente" title="Desvincular"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
</script>

<?php
$this->registerJs(
    <<<'JS'
$(document).ready(function() {
    
    $(document).on('click', '#btn-add-dependiente', function(e) {
    e.preventDefault();
    console.log('=== DEBUG MODAL SHOW ===');
    
    // Get modal element
    var modal = $('#editDependienteModal')[0];
    console.log('1. Modal element:', modal);
    console.log('2. Modal class list:', modal.className);
    console.log('3. Modal display style:', $(modal).css('display'));
    console.log('4. Modal visibility:', $(modal).css('visibility'));
    console.log('5. Modal opacity:', $(modal).css('opacity'));
    console.log('6. Modal z-index:', $(modal).css('z-index'));
    
    // Check if modal-backdrop exists
    console.log('7. Modal backdrop exists:', $('.modal-backdrop').length);
    
    // Force show with explicit options
    $('#editDependienteModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: true
    });
    
    // Check again after show
    setTimeout(function() {
        console.log('=== AFTER MODAL SHOW ===');
        console.log('8. Modal class list after:', modal.className);
        console.log('9. Modal display after:', $(modal).css('display'));
        console.log('10. Body classes:', document.body.className);
        console.log('11. Modal backdrop after:', $('.modal-backdrop').length);
        
        // Manually inspect DOM
        console.log('12. Modal HTML:', modal.outerHTML);
    }, 500);
});
    
    // 1. VARIABLE DEFINITIONS & SCOPE
    var dependientesList = [];
    var $dependientesContainer = $('#dependientes-list');
    var $modal = $('#editDependienteModal');
    var currentUserId = $('#userdatos-id').val();
    
    // Form fields vars
    var $checkboxContratante = $('#userdatos-tiene_contratante_diferente');
    var $tipoCedulaField = $('.field-userdatos-tipo_cedula');
    var $cedulaField = $('.field-userdatos-cedula');
    var $contratanteSection = $('#contratante-section');

    // 2. CORE FUNCTIONS
    window.loadDependientes = function() {
    if (!currentUserId) return;
    
        $.ajax({
            url: '/sipsa/web/user-datos/get-dependientes',   // <-- This might be the issue
            type: 'GET',
            data: { id: currentUserId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    dependientesList = response.data;
                    renderDependientes();
                }
            },
            error: function(err) { console.error('Error loading dependientes', err); }
        });
    };

    function renderDependientes() {
        var template = $('#dependiente-template').html();
        var html = '';
        
        if (dependientesList.length === 0) {
            $dependientesContainer.html('<div class="text-center text-muted p-3">No hay dependientes registrados.</div>');
            $('#dependientes-count').text('0');
            return;
        }

        dependientesList.forEach(function(dep) {
            var pctBadge = dep.porcentaje_pago < 100 ? 
                '<span class="badge badge-warning">' + dep.porcentaje_pago + '% Pago</span>' : '';
            
            var itemHtml = template
                .replace(/{id}/g, dep.id)
                .replace('{nombre_completo}', dep.nombre_completo)
                .replace('{cedula}', dep.cedula)
                .replace('{parentesco}', dep.parentesco)
                .replace('{porcentaje_badge}', pctBadge);
                
            html += itemHtml;
        });

        $dependientesContainer.html(html);
        $('#dependientes-count').text(dependientesList.length);
        
        // Re-initialize tooltips if bootstrap is available
        if(typeof $.fn.tooltip !== 'undefined') {
            $('[data-toggle="tooltip"]').tooltip();
        }
    }

    function toggleContratanteSectionAndRequired() {
        if ($checkboxContratante.is(':checked')) {
            $tipoCedulaField.removeClass('required');
            $cedulaField.removeClass('required');
            $contratanteSection.show();
        } else {
            $tipoCedulaField.addClass('required');
            $cedulaField.addClass('required');
            $contratanteSection.hide();
        }
    }

    function toggleConditionalRequired() {
        var tipoAfiliado = $('#user_datos_type_id_field').val();
        var clinicaField = $('#clinica_id');
        var planField = $('#plan_id');
        var corporativoField = $('#corporativo_id');
        
        if (tipoAfiliado == '1') {
            // Individual - make clinica and plan required
            clinicaField.attr('required', true);
            planField.attr('required', true);
            $('.conditional-asterisk').show();
            $('.field-userdatos-clinica_id, .field-contratos-plan_id').addClass('required-field');
            
            // Remove required from corporativo
            corporativoField.removeAttr('required');
            $('.field-userdatos-afiliado_corporativo_id').removeClass('required-field');
        } else if (tipoAfiliado == '2') {
            // Corporativo - remove required from clinica and plan
            clinicaField.removeAttr('required');
            planField.removeAttr('required');
            $('.conditional-asterisk').hide();
            $('.field-userdatos-clinica_id, .field-contratos-plan_id').removeClass('required-field');
            
            // Make corporativo required
            corporativoField.attr('required', true);
            $('.field-userdatos-afiliado_corporativo_id').addClass('required-field');
        } else {
            // No type selected - remove all conditional requirements
            clinicaField.removeAttr('required');
            planField.removeAttr('required');
            corporativoField.removeAttr('required');
            $('.conditional-asterisk').hide();
            $('.field-userdatos-clinica_id, .field-contratos-plan_id, .field-userdatos-afiliado_corporativo_id').removeClass('required-field');
        }
    }

    function toggleAfiliadoCorporativo() {
        if ($('#user_datos_type_id_field').val() == '2') {
            $('#afiliado_corporativo_container').show();
        } else {
            $('#afiliado_corporativo_container').hide();
        }
    }

    // Function to toggle cédula/consecutivo fields
function toggleCedulaFields() {
    var tipoCedula = $('#userdatos-tipo_cedula').val();
    var consecutivoContainer = $('#consecutivo-container');
    var cedulaField = $('#userdatos-cedula');
    var cedulaLabel = $('label[for="userdatos-cedula"]');
    var consecutivoField = $('#userdatos-consecutivo_menor');
    
    if (tipoCedula === 'Menor Sin Cédula') {
        // Show consecutivo field
        consecutivoContainer.show();
        
        // Update cedula field placeholder and label
        cedulaField.attr('placeholder', 'Cédula del padre/madre/tutor');
        cedulaLabel.html('Cédula del Padre/Madre/Tutor <span class="text-danger">*</span>');
        
        // Make consecutivo required
        consecutivoField.attr('required', true);
        
        // Update form validation attributes
        $('.field-userdatos-consecutivo_menor').addClass('required');
        
        // Update validation messages dynamically
        $('#user-datos-form').yiiActiveForm('updateAttribute', 'userdatos-consecutivo_menor', {
            required: true
        });
    } else {
        // Hide consecutivo field
        consecutivoContainer.hide();
        
        // Restore original cedula field placeholder and label
        cedulaField.attr('placeholder', 'Ejemplo: 12345678');
        cedulaLabel.html('Cédula de Identidad <span class="text-danger conditional-asterisk">*</span>');
        
        // Clear and disable consecutivo field validation
        consecutivoField.val('').removeAttr('required');
        $('.field-userdatos-consecutivo_menor').removeClass('required');
        
        // Update validation
        $('#user-datos-form').yiiActiveForm('updateAttribute', 'userdatos-consecutivo_menor', {
            required: false
        });
    }
}

// Initial call on page load
toggleCedulaFields();

// Listen for changes on tipo_cedula dropdown
$('#userdatos-tipo_cedula').on('change', toggleCedulaFields);

// Handle form validation dynamically
$('#user-datos-form').on('beforeValidate', function() {
    toggleCedulaFields();
    return true;
});

    // Handle form validation dynamically
    $('#user-datos-form').on('beforeValidate', function() {
        var tipoCedula = $('#userdatos-tipo_cedula').val();
        var cedulaField = $('#userdatos-cedula');
        var consecutivoField = $('#userdatos-consecutivo_menor');
        
        if (tipoCedula === 'Menor Sin Cédula') {
            // For "Menor Sin Cédula", cedula is not required
            cedulaField.removeAttr('required');
            consecutivoField.attr('required', true);
            
            // Also update the Yii2 validation dynamically
            $('#user-datos-form').yiiActiveForm('updateAttribute', 'userdatos-cedula', {
                required: false
            });
            $('#user-datos-form').yiiActiveForm('updateAttribute', 'userdatos-consecutivo_menor', {
                required: true
            });
        } else {
            // For regular ID types, restore normal validation
            var tieneContratante = $('#userdatos-tiene_contratante_diferente').is(':checked');
            if (!tieneContratante) {
                cedulaField.attr('required', true);
                $('#user-datos-form').yiiActiveForm('updateAttribute', 'userdatos-cedula', {
                    required: true
                });
            }
            consecutivoField.removeAttr('required');
            $('#user-datos-form').yiiActiveForm('updateAttribute', 'userdatos-consecutivo_menor', {
                required: false
            });
        }
        
        return true;
    });

    function calcularFechaVencimiento(fechaIni) {
        if (fechaIni) {
            var parts = fechaIni.split('-');
            var year = parseInt(parts[0]);
            var month = parseInt(parts[1]) - 1;
            var day = parseInt(parts[2]);
            
            var fecha = new Date(year, month, day);
            fecha.setFullYear(fecha.getFullYear() + 1);
            
            var newYear = fecha.getFullYear();
            var newMonth = String(fecha.getMonth() + 1).padStart(2, '0');
            var newDay = String(fecha.getDate()).padStart(2, '0');
            
            return newYear + '-' + newMonth + '-' + newDay;
        }
        return '';
    }

    function toggleFechaVen() {
        var fechaIni = $('.fecha-ini-field').val();
        var fechaVenContainer = $('.fecha-ven-container');
        
        if (fechaIni) {
            fechaVenContainer.show();
            var fechaVen = calcularFechaVencimiento(fechaIni);
            $('.fecha-ven-field').val(fechaVen);
        } else {
            fechaVenContainer.hide();
            $('.fecha-ven-field').val('');
        }
    }
    
    // Function to calculate plan details
    window.datosplan = function(plan_id) {
        const planIdElement = document.getElementById('plan_id');

        if (planIdElement.value !== '' && planIdElement.value !== null) {
            let csrfToken = $('meta[name="csrf-token"]').attr("content");
            var parametros = {
                id: plan_id,
                "_csrf": csrfToken,
            };

            $.ajax({
                url: "datosdelplan",
                type: "post",
                dataType: "json",
                data: parametros,
                success: function(data) {
                    if(data && data.data) {
                        const limite_cobertura = data.data.limite_cobertura;
                        const moneda = data.data.moneda;
                        $('#userdatos-moneda').val(moneda);
                        $('#userdatos-deducible').val(0);
                        $('#userdatos-limite_cobertura').val(limite_cobertura);
                    }
                },
            });
        }
    };

    // 3. EVENT LISTENERS INITIALIZATION
    
    // Initial calls
    toggleContratanteSectionAndRequired();
    toggleConditionalRequired();
    toggleAfiliadoCorporativo();
    toggleFechaVen();
    
    // Dependents Section Logic
    $('#tiene_dependientes').on('change', function() {
        if($(this).is(':checked')) {
            $('#dependientes-section').slideDown();
            loadDependientes();
        } else {
            $('#dependientes-section').slideUp();
        }
    });

    // Check on load
    if($('#tiene_dependientes').is(':checked')) {
        $('#dependientes-section').show();
        loadDependientes();
    }

    // Modal: Open to Add
    $('#btn-add-dependiente').on('click', function(e) {
        e.preventDefault();
        
        // Get data from Select2
        var data = $('#search-dependiente').select2('data')[0];
        
        if (!data || !data.id) {
            alert('Por favor, busque y seleccione un afiliado primero.');
            return;
        }

        // Reset form
        $('#edit-dependiente-id').val(''); // Empty for new
        $('#edit-dependiente-user-id').val(data.id);
        $('#display-nombre-dependiente').val(data.text);
        $('#edit-parentesco').val('').trigger('change');
        $('#edit-porcentaje').val('100');
        
        $modal.modal('show');
    });

    // Modal: Save Button
    $('#btn-save-dependiente').on('click', function() {
        var id = $('#edit-dependiente-id').val();
        var action = id ? 'update?id=' + id : 'create';
        
        var payload = {
            dependiente_id: $('#edit-dependiente-user-id').val(),
            titular_id: currentUserId,
            parentesco: $('#edit-parentesco').val(),
            porcentaje_pago: $('#edit-porcentaje').val(),
            activo: $('#edit-activo').is(':checked') ? 1 : 0
        };

        if(!payload.parentesco) {
            alert('Debe seleccionar el parentesco');
            return;
        }

        $.ajax({
            url: '/dependientes/' + action,
            type: 'POST',
            data: payload,
            success: function(response) {
                if (response.success) {
                    $modal.modal('hide');
                    $('#search-dependiente').val(null).trigger('change'); // Clear search
                    loadDependientes(); // Reload list
                } else {
                    alert('Error: ' + JSON.stringify(response.errors || response.message));
                }
            },
            error: function() { alert('Error del servidor'); }
        });
    });

    // Edit Button Click (Delegated)
    $(document).on('click', '.edit-dependiente', function() {
        var id = $(this).closest('.dependiente-card').data('id');
        var dep = dependientesList.find(function(d) { return d.id == id; });
        
        if(dep) {
            $('#edit-dependiente-id').val(dep.id);
            $('#edit-dependiente-user-id').val(dep.dependiente_id);
            $('#display-nombre-dependiente').val(dep.nombre_completo);
            $('#edit-parentesco').val(dep.parentesco).trigger('change');
            $('#edit-porcentaje').val(dep.porcentaje_pago);
            $('#edit-activo').prop('checked', dep.activo);
            $modal.modal('show');
        }
    });

    // Remove Button Click (Delegated)
    $(document).on('click', '.remove-dependiente', function() {
        if(!confirm('¿Seguro que desea desvincular este dependiente?')) return;
        
        var id = $(this).closest('.dependiente-card').data('id');
        $.post('/dependientes/delete?id=' + id, function(response) {
            if(response.success) loadDependientes();
            else alert('No se pudo eliminar');
        });
    });

    // Standard Form Listeners
    $checkboxContratante.on('change', toggleContratanteSectionAndRequired);
    
    $('#user_datos_type_id_field').on('change', function() {
        toggleAfiliadoCorporativo();
        toggleConditionalRequired();
    });
    
    $('.fecha-ini-field').on('change', toggleFechaVen);
    
    // Enhanced form validation
    $('#user-datos-form').on('beforeValidate', function() {
        toggleConditionalRequired();
        return true;
    });
});
JS
);
?>

<style>
    #consecutivo-container .input-group-prepend .input-group-text {
        background-color: #f8f9fa;
        border-right: 0;
    }

    #consecutivo-container .form-control {
        border-left: 0;
    }

    /* Fix modal z-index to be above Select2 */
    .modal {
        z-index: 99999 !important;
    }

    .modal-backdrop {
        z-index: 99998 !important;
    }

    /* Fix Select2 inside modal */
    .select2-container {
        z-index: 100000 !important;
    }

    /* Ensure modal is fully opaque */
    .modal.show {
        opacity: 1 !important;
    }

    .file-input .btn.btn-primary,
    .file-input .btn.btn-primary:not(:disabled):not(.disabled):active,
    .file-input .btn.btn-primary:not(:disabled):not(.disabled):hover,
    .file-input .btn.btn-primary span,
    .file-input .btn.btn-primary i,
    .file-input .btn.btn-primary svg {
        color: #fff !important;
        fill: #fff !important;
    }

    .file-caption-name {
        color: #fff !important;
    }

    .file-input .btn.btn-primary {
        text-shadow: none !important;
    }

    .required-field label:after {
        content: " *";
        color: #dc3545;
    }

    .text-danger {
        color: #dc3545;
    }

    .conditional-asterisk {
        display: inline;
    }
</style>