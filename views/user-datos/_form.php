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

    <!-- Hidden fields for plan data -->
    <?= $form->field($model, 'moneda')->hiddenInput(['id' => 'userdatos-moneda'])->label(false) ?>
    <?= $form->field($model, 'deducible')->hiddenInput(['id' => 'userdatos-deducible'])->label(false) ?>
    <?= $form->field($model, 'limite_cobertura')->hiddenInput(['id' => 'userdatos-limite_cobertura_raw'])->label(false) ?>
    <?= $form->field($model, 'cobertura_maternidad')->hiddenInput(['id' => 'userdatos-cobertura_maternidad'])->label(false) ?>

    <!-- Hidden fields for maternity data (will be filled when plan provides or user enters) -->
    <?= $form->field($model, 'deducible_maternidad')->hiddenInput(['id' => 'userdatos-deducible_maternidad'])->label(false) ?>
    <?= $form->field($model, 'limite_cobertura_maternidad')->hiddenInput(['id' => 'userdatos-limite_cobertura_maternidad'])->label(false) ?>

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
                        <!-- Plan Name - Takes 1/3 of the row -->
                        <div class="col-md-4 field-with-icon">
                            <div class="form-group field-plan-name-display">
                                <label class="control-label">Nombre del Plan</label>
                                <input type="text" class="form-control form-control-lg bg-light"
                                    id="plan_name_display"
                                    value="<?=
                                            // For existing records, show the plan name from the contract
                                            (!$model->isNewRecord && $modelContrato && $modelContrato->plan) ?
                                                htmlspecialchars($modelContrato->plan->nombre) :
                                                'Seleccione un plan en la sección anterior'
                                            ?>"
                                    readonly>
                                <small class="form-text text-muted">Este es el plan que se asignará al afiliado</small>
                            </div>
                        </div>

                        <!-- Coverage Limit - Takes 1/3 of the row -->
                        <div class="col-md-4 field-with-icon">
                            <div class="form-group field-userdatos-limite_cobertura">
                                <label class="control-label" for="userdatos-limite_cobertura">Límite de Cobertura</label>
                                <input type="text" id="userdatos-limite_cobertura" class="form-control form-control-lg"
                                    value="<?= is_numeric($model->limite_cobertura) ? Yii::$app->formatter->asCurrency($model->limite_cobertura, 'USD') : '$0.00' ?>"
                                    readonly>
                                <div class="help-block"></div>
                            </div>
                        </div>

                        <!-- Maternity Coverage Checkbox - Takes 1/3 of the row -->
                        <div class="col-md-4">
                            <div class="form-group" style="padding-top: 32px;"> <!-- Adjust padding to align with other fields -->
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="cobertura_maternidad_checkbox" class="custom-control-input"
                                        name="UserDatos[cobertura_maternidad]"
                                        value="1"
                                        <?= $model->cobertura_maternidad ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="cobertura_maternidad_checkbox">¿Cubre Maternidad?</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Maternity fields - initially hidden unless checkbox is checked -->
                    <div id="maternidad_fields" style="display: <?= $model->cobertura_maternidad ? 'block' : 'none' ?>;">
                        <div class="row mt-3">
                            <div class="col-md-4 offset-md-4 field-with-icon">
                                <div class="form-group field-userdatos-deducible_maternidad">
                                    <label class="control-label" for="userdatos-deducible_maternidad_display">Deducible Maternidad</label>
                                    <input type="text" id="userdatos-deducible_maternidad_display" class="form-control form-control-lg"
                                        value="<?= is_numeric($model->deducible_maternidad) ? Yii::$app->formatter->asCurrency($model->deducible_maternidad, 'USD') : '$0.00' ?>"
                                        readonly>
                                    <div class="help-block"></div>
                                </div>
                            </div>

                            <div class="col-md-4 field-with-icon">
                                <div class="form-group field-userdatos-limite_cobertura_maternidad">
                                    <label class="control-label" for="userdatos-limite_cobertura_maternidad_display">Límite Cobertura Maternidad</label>
                                    <input type="text" id="userdatos-limite_cobertura_maternidad_display" class="form-control form-control-lg"
                                        value="<?= is_numeric($model->limite_cobertura_maternidad) ? Yii::$app->formatter->asCurrency($model->limite_cobertura_maternidad, 'USD') : '$0.00' ?>"
                                        readonly>
                                    <div class="help-block"></div>
                                </div>
                            </div>
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
    console.log('=== PAGE LOAD DEBUG ===');
    console.log('CSRF meta on load:', $('meta[name="csrf-token"]').attr('content'));
    console.log('Yii global on load:', typeof yii);
    // Add this function at the beginning of your $(document).ready()


function initializePage() {
    console.log('Initializing page...');
    
    // Get current user ID
    currentUserId = $('#userdatos-id').val();
    
    // Initialize variables
    dependientesList = [];
    $dependientesContainer = $('#dependientes-list');
    $modal = $('#editDependienteModal');
    
    // Check if tiene_dependientes is checked
    if($('#tiene_dependientes').is(':checked')) {
        $('#dependientes-section').show();
        loadDependientes();
    }
    
    // Initialize other form elements
    toggleContratanteSectionAndRequired();
    toggleConditionalRequired();
    toggleAfiliadoCorporativo();
    toggleFechaVen();
    toggleCedulaFields();
    
    console.log('Page initialized for user ID:', currentUserId);
}

// Call it on document ready
$(document).ready(function() {
    initializePage();
    
    // Also reinitialize when the page is shown again (for browser back/forward)
    $(window).on('pageshow', function() {
        setTimeout(initializePage, 100);
    });
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
            url: '/sipsa/web/user-datos/get-dependientes',
            type: 'GET',
            data: { id: currentUserId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    dependientesList = response.data;
                    renderDependientes();
                } else {
                    console.error('Error in response:', response);
                }
            },
            error: function(err) { 
                console.error('Error loading dependientes', err); 
                // Show a user-friendly message
                alert('Error al cargar dependientes. Por favor, recargue la página.');
            }
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
            cedulaLabel.html('Cédula del Tutor <span class="text-danger"></span>');
            
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
            cedulaLabel.html('Cédula de Identidad <span class="text-danger conditional-asterisk"></span>');
            
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
    
    // Toggle maternity fields visibility
    $('#cobertura_maternidad_checkbox').on('change', function() {
        if ($(this).is(':checked')) {
            $('#maternidad_fields').slideDown();
            $('#userdatos-cobertura_maternidad').val(1);
            
            // If maternity fields are empty, set default values
            if (!$('#userdatos-deducible_maternidad').val()) {
                $('#userdatos-deducible_maternidad').val('0');
                $('#userdatos-deducible_maternidad_display').val('$0.00');
            }
            if (!$('#userdatos-limite_cobertura_maternidad').val()) {
                $('#userdatos-limite_cobertura_maternidad').val('0');
                $('#userdatos-limite_cobertura_maternidad_display').val('$0.00');
            }
        } else {
            $('#maternidad_fields').slideUp();
            $('#userdatos-cobertura_maternidad').val(0);
        }
    });
    
    // Update the datosplan function to handle maternity coverage
    // Function to calculate plan details and update plan name
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
                    
                    // Format as currency for display
                    const formatter = new Intl.NumberFormat('es-VE', {
                        style: 'currency',
                        currency: moneda
                    });
                    
                    // Display formatted values (readonly)
                    $('#userdatos-limite_cobertura').val(formatter.format(limite_cobertura));
                    
                    // Store raw values in hidden fields for the database
                    $('#userdatos-moneda').val(moneda);
                    $('#userdatos-deducible').val(0);
                    $('#userdatos-limite_cobertura_raw').val(limite_cobertura);
                    
                    // Update plan name display with the selected option text
                    var selectedOption = $('#plan_id option:selected');
                    var planName = selectedOption.text().replace('Seleccione el Plan...', '').trim();
                    if (planName) {
                        $('#plan_name_display').val(planName);
                    }
                    
                    // Check if plan includes maternity coverage
                    // For now, we'll keep it unchecked by default
                    $('#cobertura_maternidad_checkbox').prop('checked', false);
                    $('#userdatos-cobertura_maternidad').val(0);
                    
                    // Clear maternity fields
                    $('#userdatos-deducible_maternidad').val('');
                    $('#userdatos-limite_cobertura_maternidad').val('');
                    $('#userdatos-deducible_maternidad_display').val('');
                    $('#userdatos-limite_cobertura_maternidad_display').val('');
                    
                    // Hide maternity fields
                    $('#maternidad_fields').hide();
                    
                    // Show/hide based on checkbox state
                    if ($('#cobertura_maternidad_checkbox').is(':checked')) {
                        $('#maternidad_fields').show();
                    }
                }
            },
            error: function() {
                // Clear all fields on error
                $('#userdatos-limite_cobertura').val('$0.00');
                $('#userdatos-moneda').val('');
                $('#userdatos-deducible').val('');
                $('#userdatos-limite_cobertura_raw').val('');
                
                // Clear plan name
                $('#plan_name_display').val('Seleccione un plan en la sección anterior');
                
                // Clear maternity fields
                $('#userdatos-deducible_maternidad').val('');
                $('#userdatos-limite_cobertura_maternidad').val('');
                $('#userdatos-deducible_maternidad_display').val('$0.00');
                $('#userdatos-limite_cobertura_maternidad_display').val('$0.00');
                
                // Uncheck maternity checkbox
                $('#cobertura_maternidad_checkbox').prop('checked', false);
                $('#userdatos-cobertura_maternidad').val(0);
                $('#maternidad_fields').hide();
            }
        });
    } else {
        // Clear all fields if no plan selected
        $('#userdatos-limite_cobertura').val('$0.00');
        $('#userdatos-moneda').val('');
        $('#userdatos-deducible').val('');
        $('#userdatos-limite_cobertura_raw').val('');
        
        // Clear plan name
        $('#plan_name_display').val('Seleccione un plan en la sección anterior');
        
        // Clear maternity fields
        $('#userdatos-deducible_maternidad').val('');
        $('#userdatos-limite_cobertura_maternidad').val('');
        $('#userdatos-deducible_maternidad_display').val('$0.00');
        $('#userdatos-limite_cobertura_maternidad_display').val('$0.00');
        
        // Uncheck maternity checkbox
        $('#cobertura_maternidad_checkbox').prop('checked', false);
        $('#userdatos-cobertura_maternidad').val(0);
        $('#maternidad_fields').hide();
    }
};

// Also update plan name when user manually selects a different option
$('#plan_id').on('change', function() {
    var selectedOption = $(this).find('option:selected');
    var planName = selectedOption.text().replace('Seleccione el Plan...', '').trim();
    
    if (planName) {
        $('#plan_name_display').val(planName);
    } else {
        $('#plan_name_display').val('Seleccione un plan en la sección anterior');
    }
    
    // Also trigger the datosplan function if a valid plan is selected
    if ($(this).val()) {
        datosplan($(this).val());
    }
});

// Initialize on page load if there's already a selected plan
$(document).ready(function() {
    var initialPlanId = $('#plan_id').val();
    if (initialPlanId) {
        // Update plan name display
        var selectedOption = $('#plan_id option:selected');
        var planName = selectedOption.text().replace('Seleccione el Plan...', '').trim();
        if (planName) {
            $('#plan_name_display').val(planName);
        }
        
        // Also trigger datosplan to update coverage limit
        datosplan(initialPlanId);
    }
});
    // Add ability to manually enter maternity values if needed
    $(document).on('dblclick', '#userdatos-deducible_maternidad_display, #userdatos-limite_cobertura_maternidad_display', function() {
        var currentId = $(this).attr('id');
        var hiddenFieldId = currentId.replace('_display', '');
        var currentValue = $('#' + hiddenFieldId).val() || '0';
        
        var newValue = prompt('Ingrese el nuevo valor (solo números):', currentValue);
        if (newValue !== null && !isNaN(newValue) && newValue.trim() !== '') {
            var numericValue = parseFloat(newValue);
            $('#' + hiddenFieldId).val(numericValue);
            
            // Format for display
            const formatter = new Intl.NumberFormat('es-VE', {
                style: 'currency',
                currency: 'USD'
            });
            $(this).val(formatter.format(numericValue));
        }
    });

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

    // ============================================
    // FIXED EVENT HANDLERS WITH DELEGATION
    // ============================================

    // 1. Open Modal - Use delegation for dynamic buttons
    $(document).on('click', '#btn-add-dependiente', function(e) {
        e.preventDefault();
        
        var data = $('#search-dependiente').select2('data')[0];
        
        if (!data || !data.id) {
            alert('Por favor, busque y seleccione un afiliado primero.');
            return;
        }

        // Check if already dependent
        var alreadyDependent = dependientesList.find(function(dep) {
            return dep.dependiente_id == data.id;
        });
        
        if (alreadyDependent) {
            alert('Este afiliado ya está registrado como dependiente.');
            return;
        }

        // Close Select2
        $('.select2-container--open').select2('close');
        
        // Reset form
        $('#edit-dependiente-id').val('');
        $('#edit-dependiente-user-id').val(data.id);
        $('#display-nombre-dependiente').val(data.text);
        $('#edit-parentesco').val('').trigger('change');
        $('#edit-porcentaje').val('100');
        $('#edit-activo').prop('checked', true);
        
        // Show modal using Bootstrap
        $('#editDependienteModal').modal({
            backdrop: 'static',
            keyboard: false
        });
    });

    $(document).on('click', '#btn-save-dependiente', function() {
    var id = $('#edit-dependiente-id').val();
    var action = id ? 'update' : 'create';
    var url = '/sipsa/web/dependientes/' + action;
    
    if (action === 'update' && id) {
        url += '?id=' + id;
    }
    
    // ============================================
    // DEBUG & GET CSRF TOKEN
    // ============================================
    console.log('=== DEBUG CSRF TOKEN ===');
    console.log('1. Meta tag exists:', $('meta[name="csrf-token"]').length);
    console.log('2. Meta tag content:', $('meta[name="csrf-token"]').attr('content'));
    console.log('3. Yii global exists:', typeof yii !== 'undefined');
    console.log('4. Yii CSRF function:', typeof yii !== 'undefined' ? typeof yii.getCsrfToken : 'n/a');
    console.log('5. CSRF input exists:', $('input[name="_csrf"]').length);
    console.log('6. CSRF input value:', $('input[name="_csrf"]').val());
    
    // Get CSRF token with multiple fallbacks
    var csrfToken = '';
    
    // Try meta tag first
    if ($('meta[name="csrf-token"]').length) {
        csrfToken = $('meta[name="csrf-token"]').attr('content');
        console.log('Using CSRF from meta tag:', csrfToken);
    }
    // Try Yii global
    else if (typeof yii !== 'undefined' && yii.getCsrfToken) {
        csrfToken = yii.getCsrfToken();
        console.log('Using CSRF from Yii global:', csrfToken);
    }
    // Try hidden input
    else if ($('input[name="_csrf"]').length) {
        csrfToken = $('input[name="_csrf"]').val();
        console.log('Using CSRF from hidden input:', csrfToken);
    }
    // Last resort: check all meta tags
    else {
        console.log('Checking all meta tags:');
        $('meta').each(function() {
            var name = $(this).attr('name');
            var content = $(this).attr('content');
            if (name && name.includes('csrf')) {
                console.log('Found CSRF meta:', name, content);
                csrfToken = content;
            }
        });
    }
    
    if (!csrfToken) {
        console.error('NO CSRF TOKEN FOUND!');
        alert('Error de seguridad: No se encontró token CSRF.\nPor favor, recargue la página.');
        return;
    }
    
    console.log('Final CSRF token:', csrfToken);
    // ============================================
    // END DEBUG
    // ============================================
    
    var payload = {
        dependiente_id: $('#edit-dependiente-user-id').val(),
        titular_id: currentUserId,
        parentesco: $('#edit-parentesco').val(),
        porcentaje_pago: $('#edit-porcentaje').val(),
        activo: $('#edit-activo').is(':checked') ? 1 : 0,
        _csrf: csrfToken
    };

    if(!payload.parentesco) {
        alert('Debe seleccionar el parentesco');
        return;
    }

    console.log('=== AJAX REQUEST ===');
    console.log('URL:', url);
    console.log('Payload:', payload);
    console.log('Current user ID:', currentUserId);

    $.ajax({
        url: url,
        type: 'POST',
        data: payload,
        dataType: 'json',
        success: function(response) {
            console.log('=== AJAX SUCCESS ===');
            console.log('Response:', response);
            
            if (response.success) {
                $('#editDependienteModal').modal('hide');
                $('#search-dependiente').val(null).trigger('change');
                loadDependientes();
                alert(response.message || 'Dependiente guardado exitosamente');
            } else {
                var errorMsg = response.message || 'Error al guardar';
                if (response.errors) {
                    var errorDetails = [];
                    $.each(response.errors, function(field, errors) {
                        errorDetails.push(field + ': ' + errors.join(', '));
                    });
                    errorMsg += '\n\n' + errorDetails.join('\n');
                }
                alert(errorMsg);
            }
        },
        error: function(xhr, status, error) { 
            console.error('=== AJAX ERROR ===');
            console.error('Status:', xhr.status);
            console.error('Status text:', xhr.statusText);
            console.error('Response:', xhr.responseText);
            console.error('Ready state:', xhr.readyState);
            
            if (xhr.status === 403) {
                alert('ERROR 403 - ACCESO DENEGADO\n\n' +
                      'Posibles causas:\n' +
                      '1. Token CSRF inválido o faltante\n' +
                      '2. Sesión de usuario expirada\n' +
                      '3. No tiene permisos para esta acción\n\n' +
                      'Token CSRF usado: ' + (csrfToken ? csrfToken.substring(0, 20) + '...' : 'Ninguno') + '\n' +
                      'Por favor, recargue la página e intente nuevamente.');
            } else {
                alert('Error del servidor: ' + error + '\nStatus: ' + xhr.status);
            }
        }
    });
});

    // 3. Edit Button - Already delegated, keep as is
    $(document).on('click', '.edit-dependiente', function() {
        var id = $(this).closest('.dependiente-card').data('id');
        var dep = dependientesList.find(function(d) { return d.id == id; });
        
        if(dep) {
            $('.select2-container--open').select2('close');
            
            $('#edit-dependiente-id').val(dep.id);
            $('#edit-dependiente-user-id').val(dep.dependiente_id);
            $('#display-nombre-dependiente').val(dep.nombre_completo);
            $('#edit-parentesco').val(dep.parentesco).trigger('change');
            $('#edit-porcentaje').val(dep.porcentaje_pago);
            $('#edit-activo').prop('checked', dep.activo);
            
            $('#editDependienteModal').modal({
                backdrop: 'static',
                keyboard: false
            });
        }
    });

    // 4. Remove Button - Make sure it's delegated
    $(document).on('click', '.remove-dependiente', function() {
        if(!confirm('¿Seguro que desea desvincular este dependiente?')) return;
        
        var id = $(this).closest('.dependiente-card').data('id');
        $.ajax({
            url: '/sipsa/web/dependientes/delete?id=' + id,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    loadDependientes();
                    alert('Dependiente eliminado exitosamente');
                } else {
                    alert('No se pudo eliminar: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function() { 
                alert('Error del servidor al eliminar'); 
            }
        });
    });

    // ============================================
    // STANDARD FORM LISTENERS (KEEP THESE)
    // ============================================
    
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

    /* ============================================
       FIXED MODAL CSS - SIMPLIFIED
       ============================================ */

    /* 1. Ensure modal is visible when shown */
    #editDependienteModal.modal.show {
        display: block !important;
        opacity: 1 !important;
        z-index: 1050 !important;
    }

    /* 2. Modal backdrop - ensure it's below modal */
    .modal-backdrop.show {
        z-index: 1040 !important;
    }

    /* 3. Prevent Bootstrap from shifting your page layout */
    body.modal-open {
        padding-right: 0 !important;
        overflow: hidden !important;
    }

    /* 4. SELECT2 FIX - Hide dropdowns when modal is open */
    body.modal-open .select2-container--open .select2-dropdown {
        display: none !important;
        z-index: 1039 !important;
    }

    /* 5. Ensure modal dialog is centered */
    #editDependienteModal .modal-dialog {
        z-index: 1051 !important;
        position: relative;
        margin: 1.75rem auto;
    }

    /* 6. Ensure modal content is above backdrop */
    #editDependienteModal .modal-content {
        z-index: 1052 !important;
        position: relative;
    }

    /* 7. Emergency override - if modal still not visible */
    .modal.in {
        display: block !important;
    }

    /* 8. Fix for any parent elements that might hide modal */
    #editDependienteModal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
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