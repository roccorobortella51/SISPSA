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

    <!-- Hidden fields for maternity data -->
    <?= $form->field($model, 'deducible_maternidad')->hiddenInput(['id' => 'userdatos-deducible_maternidad'])->label(false) ?>
    <?= $form->field($model, 'limite_cobertura_maternidad')->hiddenInput(['id' => 'userdatos-limite_cobertura_maternidad'])->label(false) ?>

    <!-- ============================================================ -->
    <!-- SECTION 1: INFORMACIÓN BÁSICA Y DATOS DEL CONTRATO            -->
    <!-- ============================================================ -->
    <div class="ms-panel ms-panel-fh border-indigo mb-4">
        <div class="ms-panel-header bg-indigo border-bottom">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle mr-3" style="font-size: 1.5rem;"></i>
                <h5 class="mb-0 font-weight-semibold">Información Básica y Datos del Contrato</h5>
                <span class="badge badge-pill ml-3" style="
                    background: rgba(255, 255, 255, 0.25);
                    color: #ffffff;
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    font-weight: 500;
                    padding: 4px 14px;
                    font-size: 0.8rem;
                    letter-spacing: 0.3px;
                ">Paso 1</span>
            </div>
        </div>
        <div class="ms-panel-body">

            <!-- Row 1: Tipo, Corporativo, Clínica, Intermediario -->
            <div class="row">
                <div class="col-md-3">
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
                <div class="col-md-3" id="afiliado_corporativo_container" style="display: none;">
                    <?= $form->field($model, 'afiliado_corporativo_id')->widget(Select2::class, [
                        'data' => UserHelper::getCorporativoList(),
                        'options' => [
                            'id' => 'afiliado_corporativo_id',
                            'placeholder' => 'Seleccione...',
                            'class' => 'form-control form-control-lg corporativo-required',
                            'id' => 'corporativo_id'
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ])->label('Afiliado Corporativo <span class="text-danger conditional-asterisk">*</span>') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'clinica_id')->widget(DepDrop::classname(), [
                        'type' => DepDrop::TYPE_SELECT2,
                        'options' => [
                            'id' => 'clinica_id',
                            'placeholder' => 'Seleccione la Clínica',
                            'class' => 'form-control form-control-lg conditional-required',
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
                <div class="col-md-3">
                    <?= $form->field($model, 'asesor_id')->widget(Select2::classname(), [
                        'data' => UserHelper::getAgenteFuerzaList(),
                        'options' => [
                            'placeholder' => 'Seleccione el Intermediario...',
                            'class' => 'form-control form-control-lg',
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                        ],
                    ])->label('Intermediario (Vendedor)');
                    ?>
                </div>
            </div>

            <!-- Row 2: Fecha Inicio, Fecha Vencimiento, Duración -->
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($modelContrato, 'fecha_ini')->textInput([
                        'class' => 'form-control form-control-lg fecha-ini-field',
                        'type' => 'date',
                        'required' => true,
                        'placeholder' => 'Seleccione la fecha de inicio',
                        'id' => 'contrato-fecha-ini'
                    ])->label('Fecha de Inicio del Contrato <span class="text-danger"></span>') ?>
                </div>

                <div class="col-md-3 fecha-ven-container" style="display: none;">
                    <?= $form->field($modelContrato, 'fecha_ven')->textInput([
                        'class' => 'form-control form-control-lg fecha-ven-field bg-light',
                        'type' => 'date',
                        'readonly' => true,
                        'disabled' => true,
                        'placeholder' => 'Fecha calculada automáticamente'
                    ])->label('Fecha de Vencimiento <span class="text-muted">(calculada)</span>') ?>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Duración del Contrato</label>
                        <div class="form-control form-control-lg bg-light" style="
                        height: auto;
                        padding: 12px 15px;
                        position: relative;
                        overflow: hidden;
                        border: 2px solid #ff6b00;
                        transition: all 0.3s ease;
                    ">
                            <!-- LIGHTING EFFECTS -->
                            <div style="
                            position: absolute;
                            top: -50%;
                            left: -50%;
                            width: 200%;
                            height: 200%;
                            background: radial-gradient(
                                circle at var(--mouse-x, 50%) var(--mouse-y, 50%),
                                rgba(255, 107, 0, 0.15) 0%,
                                rgba(255, 107, 0, 0.05) 30%,
                                transparent 60%
                            );
                            pointer-events: none;
                            animation: lighting-sweep 4s ease-in-out infinite;
                            z-index: 0;
                        "></div>
                            <div style="
                            position: absolute;
                            top: -100%;
                            left: -100%;
                            width: 300%;
                            height: 300%;
                            background: radial-gradient(
                                ellipse at 30% 40%,
                                rgba(255, 200, 100, 0.08) 0%,
                                rgba(255, 107, 0, 0.04) 20%,
                                transparent 50%
                            );
                            pointer-events: none;
                            animation: spotlight-move 6s ease-in-out infinite alternate;
                            z-index: 0;
                        "></div>
                            <div style="
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background: radial-gradient(
                                ellipse at 70% 80%,
                                rgba(255, 107, 0, 0.06) 0%,
                                transparent 40%
                            );
                            pointer-events: none;
                            animation: flicker-glow 3s ease-in-out infinite alternate;
                            z-index: 0;
                        "></div>

                            <!-- Content -->
                            <div style="position: relative; z-index: 1;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="font-weight-bold text-primary">12 meses</span>
                                        <small class="text-muted d-block">Vigencia anual</small>
                                    </div>
                                    <div style="flex-shrink: 0;">
                                        <span class="badge px-2 py-1" style="
                                        font-size: 1.65rem;
                                        background: linear-gradient(135deg, #ff6b00, #e65c00);
                                        color: #fff;
                                        animation: pulse-badge 2s infinite;
                                        box-shadow: 0 2px 12px rgba(255, 107, 0, 0.4);
                                        position: relative;
                                        z-index: 2;
                                    ">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Importante
                                        </span>
                                    </div>
                                </div>

                                <!-- Warning Message -->
                                <div class="mt-2 pt-2" style="
                                border-top: 2px solid rgba(255, 107, 0, 0.3);
                                animation: slideInWarning 0.6s ease-out;
                                position: relative;
                                z-index: 1;
                            ">
                                    <div class="d-flex align-items-center" style="gap: 8px; flex-wrap: nowrap;">
                                        <div style="flex-shrink: 0; animation: bounce-icon 2s infinite; position: relative;">
                                            <div style="
                                            position: absolute;
                                            top: 50%;
                                            left: 50%;
                                            transform: translate(-50%, -50%);
                                            width: 35px;
                                            height: 35px;
                                            background: radial-gradient(circle, rgba(255, 107, 0, 0.2) 0%, transparent 70%);
                                            border-radius: 50%;
                                            animation: glow-ring 2s ease-in-out infinite;
                                        "></div>
                                            <div style="
                                            background: linear-gradient(135deg, #ff6b00, #e65c00);
                                            border-radius: 50%;
                                            width: 24px;
                                            height: 24px;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            box-shadow: 0 2px 12px rgba(255, 107, 0, 0.5);
                                            position: relative;
                                            z-index: 1;
                                        ">
                                                <i class="fas fa-exclamation" style="color: #fff; font-size: 0.9rem; font-weight: 900;"></i>
                                            </div>
                                        </div>

                                        <div style="flex: 1; min-width: 0; font-size: 1.3rem; color: #7a4a00; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <strong style="color: #cc5500; text-decoration: underline; text-decoration-color: #ff6b00; text-underline-offset: 2px; animation: text-glow 2s ease-in-out infinite;">Fecha de inicio</strong>
                                            debe coincidir con la
                                            <strong style="color: #cc5500; text-decoration: underline; text-decoration-color: #ff6b00; text-underline-offset: 2px; animation: text-glow 2s ease-in-out infinite 0.5s;">fecha del primer pago</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- SECTION 2: DATOS DEL PLAN SOLICITADO (4 IN ONE ROW)           -->
    <!-- ============================================================ -->
    <div class="ms-panel ms-panel-fh border-success mb-4">
        <div class="ms-panel-header bg-success border-bottom">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="fas fa-file-contract mr-3" style="font-size: 1.5rem;"></i>
                    <h5 class="mb-0 font-weight-semibold">Datos del Plan Solicitado</h5>
                    <span class="badge badge-pill ml-3 mr-4" style="
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.2);
    font-weight: 500;
    padding: 4px 14px;
    font-size: 0.8rem;
    letter-spacing: 0.3px;
">Paso 2</span>
                </div>
                <div id="plan-status-badge" class="d-none">
                    <span class="badge px-3 py-2" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-check-circle mr-1"></i> Plan Seleccionado
                    </span>
                </div>
            </div>
        </div>
        <div class="ms-panel-body">

            <!-- ============================================================ -->
            <!-- ALL ELEMENTS IN ONE ROW                                       -->
            <!-- ============================================================ -->
            <div class="row g-3 align-items-stretch">

                <!-- 1. Plan Selection -->
                <div class="col-md-3">
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

                <!-- 2. Empty state (shown when NO plan is selected) -->
                <div class="col-md-9" id="plan-empty-state">
                    <label class="control-label">&nbsp;</label>
                    <div style="
                    background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
                    border-radius: 10px;
                    border: 2px dashed #dee2e6;
                    padding: 10px 20px;
                    height: 48px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.3s ease;
                ">
                        <i class="fas fa-arrow-up text-success mr-2" style="font-size: 0.9rem;"></i>
                        <span class="text-muted" style="font-size: 0.9rem;">
                            Seleccione un plan para ver los detalles de cobertura
                        </span>
                    </div>
                </div>

                <!-- 3. Coverage Limit (shown when plan IS selected) -->
                <div class="col-md-3 plan-detail-col" id="plan-details-container" style="display: none;">
                    <label class="control-label">&nbsp;</label>
                    <div class="plan-detail-card" style="
                    background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
                    border-radius: 10px;
                    border: 1px solid #e1eaf5;
                    border-left: 4px solid #0078d4;
                    padding: 10px 14px;
                    height: 48px;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                ">
                        <div class="d-flex align-items-center" style="gap: 10px; width: 100%;">
                            <div style="
                            background: rgba(0, 120, 212, 0.1);
                            border-radius: 6px;
                            width: 32px;
                            height: 32px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        ">
                                <i class="fas fa-shield-alt text-primary" style="font-size: 1rem;"></i>
                            </div>
                            <div style="min-width: 0; flex: 1;">
                                <div class="text-muted text-uppercase" style="font-size: 1.4rem; letter-spacing: 0.4px; font-weight: 600; line-height: 1;">
                                    Límite Cobertura
                                </div>
                                <div class="font-weight-bold text-primary" id="userdatos-limite_cobertura_display" style="font-size: 1.2rem; line-height: 1.3;">
                                    $0.00
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="userdatos-limite_cobertura" value="">
                    </div>
                </div>

                <!-- 4. Maternity Coverage Toggle (shown when plan IS selected) -->
                <div class="col-md-3 plan-detail-col" id="maternidad_toggle_container" style="display: none;">
                    <label class="control-label">&nbsp;</label>
                    <div class="plan-detail-card" style="
                    background: linear-gradient(135deg, #fff5f7 0%, #ffffff 100%);
                    border-radius: 10px;
                    border: 1px solid #f5e1e8;
                    border-left: 4px solid #e83e8c;
                    padding: 10px 14px;
                    height: 48px;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                ">
                        <div class="d-flex align-items-center justify-content-between" style="gap: 8px; width: 100%;">
                            <div class="d-flex align-items-center" style="gap: 10px; min-width: 0; flex: 1;">
                                <div style="
                                background: rgba(232, 62, 140, 0.1);
                                border-radius: 6px;
                                width: 32px;
                                height: 32px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                flex-shrink: 0;
                            ">
                                    <i class="fas fa-baby text-pink" style="font-size: 1.4rem;"></i>
                                </div>
                                <div style="min-width: 0;">
                                    <div class="text-muted text-uppercase" style="font-size: 1.4rem; letter-spacing: 0.4px; font-weight: 600; line-height: 1;">
                                        Maternidad
                                    </div>
                                    <div class="font-weight-bold text-pink" style="font-size: 1.1rem; line-height: 1.3;">
                                        Cubre
                                    </div>
                                </div>
                            </div>
                            <div class="custom-control custom-switch" style="padding-left: 2.8rem; min-height: 1.5rem; margin: 0; flex-shrink: 0;">
                                <input type="checkbox" id="cobertura_maternidad_checkbox" class="custom-control-input"
                                    name="UserDatos[cobertura_maternidad]" value="1"
                                    <?= $model->cobertura_maternidad ? 'checked' : '' ?>
                                    style="position: absolute; z-index: -1; opacity: 0;">
                                <label class="custom-control-label" for="cobertura_maternidad_checkbox" style="
                                padding-left: 0.4rem;
                                cursor: pointer;
                                font-weight: 600;
                                font-size: 0.75rem;
                                color: #323130;
                                margin: 0;
                                line-height: 1.5rem;
                            " id="maternidad_label">No</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Maternity Details (shown when plan IS selected AND maternity is ON) -->
                <div class="col-md-3 plan-detail-col" id="maternidad_fields" style="display: none;">
                    <label class="control-label">&nbsp;</label>
                    <div class="plan-detail-card" style="
                    background: linear-gradient(135deg, #fef3f2 0%, #ffffff 100%);
                    border-radius: 10px;
                    border: 1px solid #f5e1e1;
                    border-left: 4px solid #dc3545;
                    padding: 10px 14px;
                    height: 48px;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                ">
                        <div class="d-flex align-items-center" style="gap: 10px; width: 100%;">
                            <div style="
                            background: rgba(220, 53, 69, 0.1);
                            border-radius: 6px;
                            width: 32px;
                            height: 32px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        ">
                                <i class="fas fa-heart text-danger" style="font-size: 1rem;"></i>
                            </div>
                            <div style="min-width: 0; flex: 1;">
                                <div class="text-muted text-uppercase" style="font-size: 0.6rem; letter-spacing: 0.4px; font-weight: 600; line-height: 1;">
                                    Deducible Mat.
                                </div>
                                <div class="font-weight-bold text-danger" id="userdatos-deducible_maternidad_display" style="font-size: 1rem; line-height: 1.3;">
                                    $0.00
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="userdatos-deducible_maternidad" value="">
                        <input type="hidden" id="userdatos-limite_cobertura_maternidad" value="">
                        <input type="hidden" id="userdatos-limite_cobertura_maternidad_display" value="">
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- ============================================================ -->
    <!-- SECTION 3: DATOS DEL AFILIADO                                 -->
    <!-- ============================================================ -->
    <div class="ms-panel ms-panel-fh border-info mb-4">
        <div class="ms-panel-header bg-info border-bottom">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-plus text-info mr-3" style="font-size: 1.5rem;"></i>
                <h5 class="mb-0 font-weight-semibold">Datos del Afiliado</h5>
                <span class="badge badge-pill ml-3" style="
                    background: rgba(255, 255, 255, 0.25);
                    color: #ffffff;
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    font-weight: 500;
                    padding: 4px 14px;
                    font-size: 0.8rem;
                    letter-spacing: 0.3px;
                ">Paso 3</span>
            </div>
        </div>
        <div class="ms-panel-body">

            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'nombres')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Ingrese los nombres']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'apellidos')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Ingrese los apellidos']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'email')->textInput(['class' => 'form-control form-control-lg']) ?>
                </div>

                <div class="col-md-2">
                    <?= $form->field($model, 'telefono')->widget(MaskedInput::class, [
                        'mask' => '99999999999',
                        'options' => [
                            'placeholder' => '0412-1234567',
                            'class' => 'form-control form-control-lg',
                            'maxlength' => true,
                        ],
                        'clientOptions' => [
                            'clearIncomplete' => true,
                            'unmaskAsSubmit' => true,
                        ]
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'tipo_cedula')->widget(Select2::class, [
                        'data' => [
                            '' => 'Seleccione Tipo',
                            'V' => 'V - Venezolano',
                            'E' => 'E - Extranjero',
                            'J' => 'J - Jurídico',
                            'P' => 'P - Pasaporte',
                            'Menor Sin Cédula' => 'Menor Sin Cédula',
                            'Afiliado Otras Clínicas' => 'Afiliado Otras Clínicas'
                        ],
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

                <div class="col-md-4">
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
                        <small class="form-text text-muted">Número consecutivo (1-20)</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'fechanac')->textInput([
                        'class' => 'form-control form-control-lg',
                        'type' => 'date',
                        'placeholder' => 'Seleccione su fecha de nacimiento',
                        'id' => 'userdatos-fechanac'
                    ])->label('Fecha de Nacimiento') ?>
                    <div id="fechanac-error" class="text-danger mt-1" style="display:none;"></div>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'lugar_nacimiento')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Ciudad de nacimiento']) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'sexo')->widget(Select2::class, [
                        'data' => ['Masculino' => 'Masculino', 'Femenino' => 'Femenino', 'Otro' => 'Otro'],
                        'options' => ['placeholder' => 'Seleccione el sexo...'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'nacionalidad')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Venezolana']) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'estado_civil')->widget(Select2::class, [
                        'data' => ['Soltero' => 'Soltero', 'Casado' => 'Casado', 'Divorciado' => 'Divorciado', 'Viudo' => 'Viudo'],
                        'options' => ['placeholder' => 'Seleccione...'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('Estado Civil') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'profesion')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Profesión']) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'ocupacion')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Ocupación']) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'actividad_economica')->widget(Select2::class, [
                        'data' => ['Industrial' => 'Industrial', 'Comercial' => 'Comercial', 'Profesional' => 'Profesional', 'Gubernamental' => 'Gubernamental'],
                        'options' => ['placeholder' => 'Seleccione...'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('Actividad Económica') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'ramo_comercial')->textInput(['class' => 'form-control form-control-lg', 'placeholder' => 'Ramo comercial']) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'descripcion_actividad')->widget(Select2::class, [
                        'data' => ['Independiente' => 'Independiente', 'Dependiente' => 'Dependiente', 'Societaria' => 'Societaria'],
                        'options' => ['placeholder' => 'Seleccione...'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('Desc. de la Actividad') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'ingreso_anual')->widget(Select2::class, [
                        'data' => [
                            'De 1 a 5 Salarios mínimos' => 'De 1 a 5 Salarios mínimos',
                            'De 6 a 10 Salarios mínimos' => 'De 6 a 10 Salarios mínimos',
                            'De 11 a 20 Salarios mínimos' => 'De 11 a 20 Salarios mínimos',
                            'De 20 Salarios mínimos en adelante' => 'De 20 Salarios mínimos en adelante'
                        ],
                        'options' => ['placeholder' => 'Seleccione...'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('Ingreso Anual') ?>
                </div>
            </div>

            <!-- Location Fields -->
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

            <!-- Address Fields -->
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'direccion')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ingrese la dirección completa de residencia'
                    ])->label('Dirección de Residencia') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'direccion_oficina')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ingrese la dirección de oficina (opcional)'
                    ])->label('Dirección de Oficina') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'direccion_cobro')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Ingrese la dirección de cobro'
                    ])->label('Dirección de Cobro') ?>
                </div>
            </div>

            <!-- Phone Fields -->
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'telefono_residencia')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '0212-1234567'
                    ])->label('Teléfono Residencia') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'telefono_oficina')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '0212-7654321'
                    ])->label('Teléfono Oficina') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'telefono_celular')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => '0412-1234567'
                    ])->label('Teléfono Celular') ?>
                </div>
            </div>

        </div>
    </div>

    <!-- ============================================================ -->
    <!-- SECTION 4: CONTRATANTE DIFERENTE                              -->
    <!-- ============================================================ -->
    <div class="ms-panel ms-panel-fh border-warning mb-4">
        <div class="ms-panel-header bg-warning border-bottom">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-tie mr-3" style="font-size: 1.5rem;"></i>
                <h5 class="mb-0 font-weight-semibold">Datos del Contratante</h5>
                <span class="badge badge-pill ml-3" style="
                    background: rgba(0, 0, 0, 0.12);
                    color: #212529;
                    border: 1px solid rgba(0, 0, 0, 0.08);
                    font-weight: 500;
                    padding: 4px 14px;
                    font-size: 0.8rem;
                    letter-spacing: 0.3px;
                ">Opcional</span>
            </div>
        </div>
        <div class="ms-panel-body">

            <!-- ============================================================ -->
            <!-- CONTRATANTE DIFERENTE - Toggle Card                          -->
            <!-- ============================================================ -->
            <div class="checkbox-section mb-3">
                <div class="row">
                    <div class="col-md-12">
                        <div class="contratante-card-selector">
                            <div style="
                                background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
                                border-radius: 12px;
                                padding: 14px 18px;
                                border: 2px solid #e9ecef;
                                transition: all 0.3s ease;
                                position: relative;
                                overflow: hidden;
                                cursor: pointer;
                                height: 100%;
                                min-height: 80px;
                            " id="contratante-card">
                                <!-- Decorative background pattern -->
                                <div style="
                                    position: absolute;
                                    top: -50%;
                                    right: -20%;
                                    width: 150px;
                                    height: 150px;
                                    background: radial-gradient(circle, rgba(0, 120, 212, 0.03) 0%, transparent 70%);
                                    border-radius: 50%;
                                    pointer-events: none;
                                "></div>

                                <!-- Status indicator bar -->
                                <div style="
                                    position: absolute;
                                    bottom: 0;
                                    left: 0;
                                    right: 0;
                                    height: 3px;
                                    background: #d2d0ce;
                                    transition: all 0.4s ease;
                                    border-radius: 0 0 12px 12px;
                                " id="status-bar"></div>

                                <div class="d-flex align-items-center justify-content-between" style="height: 100%;">
                                    <div class="d-flex align-items-center" style="gap: 14px;">
                                        <div style="
                                            background: linear-gradient(135deg, #0078d4, #00bcf2);
                                            border-radius: 10px;
                                            width: 44px;
                                            height: 44px;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            flex-shrink: 0;
                                            box-shadow: 0 4px 12px rgba(0, 120, 212, 0.25);
                                        ">
                                            <i class="fas fa-file-invoice-dollar text-white" style="font-size: 1.2rem;"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; font-size: 1.5rem; color: #1a1a1a;">
                                                Contratante Diferente
                                            </div>
                                            <div style="font-size: 1.2rem; color: #6c757d;" id="contratante-status-message">
                                                Active esta opción solo si el afiliado <strong>no</strong> es quien paga el plan médico
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center" style="gap: 12px;">
                                        <!-- Status Badge -->
                                        <span style="
                                            font-size: 1.1rem;
                                            font-weight: 500;
                                            color: #6c757d;
                                            padding: 4px 14px;
                                            border-radius: 20px;
                                            background: #f1f3f5;
                                            transition: all 0.3s ease;
                                            white-space: nowrap;
                                        " id="status-text">Inactivo</span>

                                        <!-- Toggle Switch -->
                                        <label class="switch" style="
                                            position: relative;
                                            display: inline-block;
                                            width: 44px;
                                            height: 24px;
                                            margin: 0;
                                            flex-shrink: 0;
                                        ">
                                            <input type="hidden" name="UserDatos[tiene_contratante_diferente]" value="0">
                                            <input type="checkbox" id="userdatos-tiene_contratante_diferente"
                                                name="UserDatos[tiene_contratante_diferente]"
                                                value="1"
                                                <?= (bool)$model->tiene_contratante_diferente ? 'checked' : '' ?>
                                                style="
                                                    opacity: 0;
                                                    width: 0;
                                                    height: 0;
                                                    position: absolute;
                                                ">
                                            <span class="slider" style="
                                                position: absolute;
                                                cursor: pointer;
                                                top: 0;
                                                left: 0;
                                                right: 0;
                                                bottom: 0;
                                                background: #d2d0ce;
                                                transition: 0.3s;
                                                border-radius: 24px;
                                                box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
                                            ">
                                                <span class="slider-thumb" style="
                                                    position: absolute;
                                                    content: '';
                                                    height: 18px;
                                                    width: 18px;
                                                    left: 3px;
                                                    bottom: 3px;
                                                    background: white;
                                                    transition: 0.3s;
                                                    border-radius: 50%;
                                                    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
                                                "></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- CONTRATANTE FIELDS                                            -->
            <!-- ============================================================ -->
            <div id="contratante-fields-container" style="display: none;">

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'nombre_contratante', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text required-star">*</span></div></div>{error}',
                        ])->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Ingrese el nombre del contratante',
                            'class' => 'form-control form-control-lg contratante-field',
                            'id' => 'userdatos-nombre_contratante'
                        ]) ?>
                    </div>

                    <div class="col-md-6">
                        <?= $form->field($model, 'apellido_contratante', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text required-star">*</span></div></div>{error}',
                        ])->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Ingrese el apellido del contratante',
                            'class' => 'form-control form-control-lg contratante-field',
                            'id' => 'userdatos-apellido_contratante'
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'tipo_cedula_contratante', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text required-star">*</span></div></div>{error}',
                        ])->dropDownList([
                            '' => 'Seleccione',
                            'V' => 'V - Venezolano',
                            'E' => 'E - Extranjero',
                            'P' => 'P - Pasaporte',
                            'J' => 'J - Jurídico',
                        ], [
                            'class' => 'form-control form-control-lg contratante-field',
                            'id' => 'userdatos-tipo_cedula_contratante',
                        ]) ?>
                    </div>

                    <div class="col-md-3">
                        <?= $form->field($model, 'cedula_contratante', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text required-star">*</span></div></div>{error}',
                        ])->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Cédula o pasaporte',
                            'class' => 'form-control form-control-lg contratante-field',
                            'id' => 'userdatos-cedula_contratante',
                        ]) ?>
                    </div>

                    <div class="col-md-3">
                        <?= $form->field($model, 'fecha_nacimiento_contratante', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text required-star">*</span></div></div>{error}',
                        ])->textInput([
                            'class' => 'form-control form-control-lg contratante-field',
                            'type' => 'date',
                            'placeholder' => 'Seleccione fecha de nacimiento',
                            'id' => 'userdatos-fecha_nacimiento_contratante'
                        ]) ?>
                    </div>

                    <div class="col-md-3">
                        <?= $form->field($model, 'sexo_contratante', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text required-star">*</span></div></div>{error}',
                        ])->dropDownList([
                            '' => 'Seleccione',
                            'Masculino' => 'Masculino',
                            'Femenino' => 'Femenino',
                        ], [
                            'class' => 'form-control form-control-lg contratante-field',
                            'id' => 'userdatos-sexo_contratante'
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'email_contratante', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text required-star">*</span></div></div>{error}',
                        ])->textInput([
                            'maxlength' => true,
                            'placeholder' => 'correo@contratante.com',
                            'class' => 'form-control form-control-lg contratante-field',
                            'type' => 'email',
                            'id' => 'userdatos-email_contratante'
                        ]) ?>
                    </div>

                    <div class="col-md-4">
                        <?= $form->field($model, 'telefono_celular_contratante', [
                            'options' => ['class' => 'form-group'],
                            'template' => '{label}<div class="input-group">{input}<div class="input-group-append"><span class="input-group-text required-star">*</span></div></div>{error}',
                        ])->textInput([
                            'maxlength' => 15,
                            'placeholder' => '0412-1234567',
                            'class' => 'form-control form-control-lg contratante-field',
                            'id' => 'userdatos-telefono_celular_contratante'
                        ]) ?>
                    </div>

                    <div class="col-md-4">
                        <?= $form->field($model, 'nacionalidad_contratante')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Nacionalidad (opcional)',
                            'class' => 'form-control form-control-lg contratante-field',
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'direccion_residencia_contratante')->textarea([
                            'rows' => 2,
                            'placeholder' => 'Dirección de residencia (opcional)',
                            'class' => 'form-control contratante-field',
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'direccion_oficina_contratante')->textarea([
                            'rows' => 2,
                            'placeholder' => 'Dirección de oficina (opcional)',
                            'class' => 'form-control contratante-field',
                        ]) ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ============================================================ -->
    <!-- SECTION 5: DATOS BANCARIOS                                    -->
    <!-- ============================================================ -->
    <div class="ms-panel ms-panel-fh border-secondary mb-4">
        <div class="ms-panel-header bg-secondary border-bottom">
            <div class="d-flex align-items-center">
                <i class="fas fa-money-check-alt mr-3" style="font-size: 1.5rem;"></i>
                <h5 class="mb-0 font-weight-semibold">Datos Bancarios</h5>
                <span class="badge badge-pill ml-3" style="
                    background: rgba(255, 255, 255, 0.25);
                    color: #ffffff;
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    font-weight: 500;
                    padding: 4px 14px;
                    font-size: 0.8rem;
                    letter-spacing: 0.3px;
                ">Opcional</span>
            </div>
        </div>
        <div class="ms-panel-body">

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'nombre_titular')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Nombre del titular de la cuenta'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'cedula_titular')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Cédula del titular'
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'numero_cuenta')->textInput([
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Número de cuenta'
                    ]) ?>
                </div>
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <?= $form->field($model, 'tipo_cuenta')->widget(Select2::class, [
                        'data' => [
                            'Cuenta Corriente' => 'Cuenta Corriente',
                            'Cuenta Ahorro' => 'Cuenta Ahorro',
                            'Tarjeta Crédito Visa' => 'Tarjeta Crédito Visa',
                            'Tarjeta Crédito MasterCard' => 'Tarjeta Crédito MasterCard',
                        ],
                        'options' => ['placeholder' => 'Seleccione...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ])->label('Tipo de Cuenta') ?>
                </div>
            </div>

        </div>
    </div>

    <!-- ============================================================ -->
    <!-- SECTION 6: IMÁGENES                                           -->
    <!-- ============================================================ -->
    <div class="ms-panel ms-panel-fh border-purple mb-4">
        <div class="ms-panel-header bg-purple border-bottom">
            <div class="d-flex align-items-center">
                <i class="fas fa-camera mr-3" style="font-size: 1.5rem;"></i>
                <h5 class="mb-0 font-weight-semibold">Documentos e Imágenes</h5>
                <span class="badge badge-pill ml-3" style="
                    background: rgba(255, 255, 255, 0.25);
                    color: #ffffff;
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    font-weight: 500;
                    padding: 4px 14px;
                    font-size: 0.8rem;
                    letter-spacing: 0.3px;
                ">Opcional</span>
            </div>
        </div>
        <div class="ms-panel-body">

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'selfieFile')->widget(FileInput::classname(), [
                        'options' => [
                            'accept' => 'image/*',
                        ],
                        'pluginOptions' => [
                            'theme' => 'fa5',
                            'browseClass' => 'btn btn-primary btn-white-text',
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
                    ])->label('Foto del usuario <span class="text-muted">(selfie)</span>');
                    ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'imagenIdentificacionFile')->widget(FileInput::classname(), [
                        'options' => [
                            'accept' => 'image/*',
                        ],
                        'pluginOptions' => [
                            'theme' => 'fa5',
                            'browseClass' => 'btn btn-primary btn-white-text',
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
                    ])->label('Imagen de identificación <span class="text-muted">(cédula/pasaporte)</span>');
                    ?>
                </div>
            </div>

        </div>
    </div>

    <!-- ============================================================ -->
    <!-- SECTION 7: FORM ACTIONS                                       -->
    <!-- ============================================================ -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar', [
                    'class' => 'btn btn-success btn-lg px-5',
                    'style' => 'min-width: 150px;'
                ]) ?>

                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver',
                    '#',
                    [
                        'class' => 'btn btn-secondary btn-lg px-4',
                        'onclick' => 'window.history.back(); return false;',
                        'title' => 'Volver a la página anterior',
                    ]
                ) ?>

                <?php if ($model->isNewRecord) { ?>
                    <?= Html::button('<i class="fas fa-sync-alt mr-2"></i> Refrescar', [
                        'class' => 'btn btn-info btn-lg px-4',
                        'id' => 'btn-refrescar-form'
                    ]) ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<!-- ============================================================ -->
<!-- MODAL: DEPENDIENTES                                           -->
<!-- ============================================================ -->
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
    // ============================================================
    // TOGGLE AFILIADO CORPORATIVO - Show/Hide corporativo field
    // ============================================================
    function toggleAfiliadoCorporativo() {
        var tipoAfiliado = $('#user_datos_type_id_field').val();
        var $container = $('#afiliado_corporativo_container');
        var $corporativoField = $('#corporativo_id');
        var $asterisk = $('.conditional-asterisk');
        
        console.log('toggleAfiliadoCorporativo called, tipoAfiliado:', tipoAfiliado);
        
        if (tipoAfiliado == '2') {
            console.log('Showing corporativo container');
            $container.show();
            $corporativoField.attr('required', true);
            $('.field-userdatos-afiliado_corporativo_id').addClass('required-field');
            $asterisk.show();
        } else {
            console.log('Hiding corporativo container');
            $container.hide();
            $corporativoField.removeAttr('required');
            $('.field-userdatos-afiliado_corporativo_id').removeClass('required-field');
            $asterisk.hide();
        }
    }

    // ============================================================
    // TOGGLE CONDITIONAL REQUIRED - For Individual/Corporativo
    // ============================================================
    function toggleConditionalRequired() {
        var tipoAfiliado = $('#user_datos_type_id_field').val();
        var clinicaField = $('#clinica_id');
        var planField = $('#plan_id');
        var corporativoField = $('#corporativo_id');
        
        console.log('toggleConditionalRequired called, tipoAfiliado:', tipoAfiliado);
        
        if (tipoAfiliado == '1') {
            // Individual - make clinica and plan required
            clinicaField.attr('required', true);
            planField.attr('required', true);
            $('.conditional-asterisk').show();
            $('.field-userdatos-clinica_id, .field-contratos-plan_id').addClass('required-field');
            
            // Remove required from corporativo
            corporativoField.removeAttr('required');
            $('.field-userdatos-afiliado_corporativo_id').removeClass('required-field');
            
            // Hide corporativo container
            $('#afiliado_corporativo_container').hide();
            
        } else if (tipoAfiliado == '2') {
            // Corporativo - remove required from clinica and plan
            clinicaField.removeAttr('required');
            planField.removeAttr('required');
            $('.conditional-asterisk').hide();
            $('.field-userdatos-clinica_id, .field-contratos-plan_id').removeClass('required-field');
            
            // Make corporativo required and show container
            corporativoField.attr('required', true);
            $('.field-userdatos-afiliado_corporativo_id').addClass('required-field');
            $('#afiliado_corporativo_container').show();
            
        } else {
            // No type selected - remove all conditional requirements
            clinicaField.removeAttr('required');
            planField.removeAttr('required');
            corporativoField.removeAttr('required');
            $('.conditional-asterisk').hide();
            $('.field-userdatos-clinica_id, .field-contratos-plan_id, .field-userdatos-afiliado_corporativo_id').removeClass('required-field');
            $('#afiliado_corporativo_container').hide();
        }
    }

    // ============================================================
    // TOGGLE CEDULA FIELDS - Show/hide consecutivo for minors
    // ============================================================
    function toggleCedulaFields() {
        var tipoCedula = $('#userdatos-tipo_cedula').val();
        var $consecutivoContainer = $('#consecutivo-container');
        var $cedulaField = $('#userdatos-cedula');
        var $cedulaLabel = $('label[for="userdatos-cedula"]');
        var $consecutivoField = $('#userdatos-consecutivo_menor');
        
        if (tipoCedula === 'Menor Sin Cédula') {
            $consecutivoContainer.show();
            $cedulaField.attr('placeholder', 'Cédula del padre/madre/tutor');
            $cedulaLabel.html('Cédula del Tutor <span class="text-danger"></span>');
            $consecutivoField.attr('required', true);
            $('.field-userdatos-consecutivo_menor').addClass('required');
            $('#user-datos-form').yiiActiveForm('updateAttribute', 'userdatos-consecutivo_menor', {
                required: true
            });
            $('#user-datos-form').yiiActiveForm('updateAttribute', 'userdatos-cedula', {
                required: false
            });
        } else {
            $consecutivoContainer.hide();
            $cedulaField.attr('placeholder', 'Ejemplo: 12345678');
            $cedulaLabel.html('Cédula de Identidad <span class="text-danger conditional-asterisk"></span>');
            $consecutivoField.removeAttr('required');
            $('.field-userdatos-consecutivo_menor').removeClass('required');
            $('#user-datos-form').yiiActiveForm('updateAttribute', 'userdatos-consecutivo_menor', {
                required: false
            });
            $('#user-datos-form').yiiActiveForm('updateAttribute', 'userdatos-cedula', {
                required: true
            });
        }
    }

    // ============================================================
    // INITIALIZATION - Initialize all form elements
    // ============================================================
    function initializePage() {
        console.log('Initializing page...');
        
        // Get current user ID
        var currentUserId = $('#userdatos-id').val();
        
        // Initialize variables
        dependientesList = [];
        $dependientesContainer = $('#dependientes-list');
        $modal = $('#editDependienteModal');
        
        // Check if tiene_dependientes is checked
        if ($('#tiene_dependientes').is(':checked')) {
            $('#dependientes-section').show();
            loadDependientes();
        }
        
        // Initialize other form elements
        toggleAfiliadoCorporativo();
        toggleConditionalRequired();
        toggleCedulaFields();
        togglePlanDetails();
        toggleContratanteFields();
        updateContratanteUI();
        toggleFechaVen();
        
        console.log('Page initialized');
    }

    // ============================================================
    // EVENT LISTENERS
    // ============================================================
    
    // Listen for tipo_afiliado changes
    $('#user_datos_type_id_field').on('change', function() {
        var selectedValue = $(this).val();
        console.log('Tipo de Afiliado changed to:', selectedValue);
        toggleAfiliadoCorporativo();
        toggleConditionalRequired();
    });
    
    // Listen for tipo_cedula changes
    $('#userdatos-tipo_cedula').on('change', toggleCedulaFields);
    

    // Call when maternity checkbox changes
    $('#cobertura_maternidad_checkbox').on('change', function() {
        if ($(this).is(':checked')) {
            $('#maternidad_fields').slideDown(300, function() {
                // Reset columns and centering after animation
                var $row = $('#plan-details-container .row');
                $row.removeClass('justify-content-center justify-content-start');
                $row.find('.col-md-4, .col-md-5').removeClass('col-md-4 col-md-5').addClass('col-md-4');
                $row.addClass('justify-content-start');
                $('#userdatos-cobertura_maternidad').val(1);
            });
        } else {
            $('#maternidad_fields').slideUp(300, function() {
                // Center the remaining two cards after animation
                var $row = $('#plan-details-container .row');
                $row.removeClass('justify-content-center justify-content-start');
                $row.find('.col-md-4, .col-md-5').removeClass('col-md-4 col-md-5').addClass('col-md-5');
                $row.addClass('justify-content-center');
                $('#userdatos-cobertura_maternidad').val(0);
            });
        }
    });

    // ============================================================
// PLAN DETAILS - Show/Hide plan detail elements
// ============================================================
function togglePlanDetails() {
    var planId = $('#plan_id').val();
    var $coverage = $('#plan-details-container');
    var $maternityToggle = $('#maternidad_toggle_container');
    var $maternityDetails = $('#maternidad_fields');
    var $emptyState = $('#plan-empty-state');
    var $statusBadge = $('#plan-status-badge');
    
    if (planId && planId !== '') {
        // Hide the empty state message
        $emptyState.hide();
        
        // Show the plan detail cards
        $coverage.show();
        $maternityToggle.show();
        $statusBadge.removeClass('d-none');
        
        // Show maternity details only if the toggle is checked
        if ($('#cobertura_maternidad_checkbox').is(':checked')) {
            $maternityDetails.show();
        } else {
            $maternityDetails.hide();
        }
    } else {
        // Show the empty state message
        $emptyState.show();
        
        // Hide the plan detail cards
        $coverage.hide();
        $maternityToggle.hide();
        $maternityDetails.hide();
        $statusBadge.addClass('d-none');
    }
}
    
    // ============================================================
    // CONTRATANTE DIFERENTE - Toggle Logic
    // ============================================================
    function toggleContratanteFields() {
        var $toggle = $('#userdatos-tiene_contratante_diferente');
        var $container = $('#contratante-fields-container');
        var $requiredStars = $('.required-star');
        
        // Define which fields are mandatory (8 required fields)
        var requiredFieldIds = [
            'userdatos-nombre_contratante',
            'userdatos-apellido_contratante',
            'userdatos-tipo_cedula_contratante',
            'userdatos-cedula_contratante',
            'userdatos-fecha_nacimiento_contratante',
            'userdatos-sexo_contratante',
            'userdatos-email_contratante',
            'userdatos-telefono_celular_contratante'
        ];
        
        if ($toggle.is(':checked')) {
            $container.slideDown(300);
            requiredFieldIds.forEach(function(id) {
                var $field = $('#' + id);
                if ($field.length) {
                    $field.closest('.form-group').find('label').addClass('required-field');
                    $field.attr('required', true);
                }
            });
            $requiredStars.show();
        } else {
            $container.slideUp(300);
            requiredFieldIds.forEach(function(id) {
                var $field = $('#' + id);
                if ($field.length) {
                    $field.closest('.form-group').find('label').removeClass('required-field');
                    $field.removeAttr('required');
                }
            });
            $requiredStars.hide();
        }
    }

    // ============================================================
    // CONTRATANTE CARD SELECTOR - Enhanced visual feedback
    // ============================================================
    function updateContratanteUI() {
        var $checkbox = $('#userdatos-tiene_contratante_diferente');
        var $card = $('#contratante-card');
        var $statusText = $('#status-text');
        var $statusBar = $('#status-bar');
        var $statusMessage = $('#contratante-status-message');
        
        if ($checkbox.length && $checkbox.is(':checked')) {
            // Active state
            $card.css({
                'border-color': '#0078d4',
                'background': 'linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%)',
                'box-shadow': '0 4px 20px rgba(0, 120, 212, 0.15)'
            });
            $statusText.text('Activo').css({
                'background': '#dff6dd',
                'color': '#107c10'
            });
            $statusBar.css({
                'background': 'linear-gradient(90deg, #0078d4, #00bcf2)',
                'box-shadow': '0 0 20px rgba(0, 120, 212, 0.3)'
            });
$statusMessage.html('<i class="fas fa-check-circle text-success mr-1" style="font-size: 1.2rem;"></i> El pago del plan médico lo realiza una persona distinta al afiliado');            
            // Toggle the slider
            $card.find('.slider').css('background', '#0078d4');
            $card.find('.slider-thumb').css('transform', 'translateX(20px)');
        } else {
            // Inactive state
            $card.css({
                'border-color': '#e9ecef',
                'background': 'linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%)',
                'box-shadow': 'none'
            });
            $statusText.text('Inactivo').css({
                'background': '#f1f3f5',
                'color': '#6c757d'
            });
            $statusBar.css({
                'background': '#d2d0ce',
                'box-shadow': 'none'
            });
$statusMessage.html('Active esta opción solo si el afiliado <strong>no</strong> es quien paga el plan médico');
            
            $card.find('.slider').css('background', '#d2d0ce');
            $card.find('.slider-thumb').css('transform', 'translateX(0px)');
        }
    }

    // Click on card toggles the checkbox
    $(document).on('click', '#contratante-card', function(e) {
        // Prevent toggling if click is on the switch itself
        if ($(e.target).closest('.switch').length) return;
        
        var $checkbox = $('#userdatos-tiene_contratante_diferente');
        $checkbox.prop('checked', !$checkbox.is(':checked')).trigger('change');
    });
    
    // ============================================================
    // CALL INITIALIZE ON PAGE LOAD
    // ============================================================
    initializePage();
    
    // Also reinitialize when the page is shown again (for browser back/forward)
    $(window).on('pageshow', function() {
        setTimeout(initializePage, 100);
    });
    
    // Listen for plan selection changes
    $('#plan_id').on('change', function() {
        togglePlanDetails();
    });
    
    // Listen for contratante toggle changes
    $('#userdatos-tiene_contratante_diferente').on('change', function() {
        updateContratanteUI();
        toggleContratanteFields();
        // Reset validation when toggle changes
        var $form = $(this).closest('form');
        $form.find('.required-star').each(function() {
            var $field = $(this).closest('.input-group').find('input, select, textarea');
            if ($field.length) {
                var $error = $field.closest('.form-group').find('.help-block');
                if ($error.length) {
                    $error.html('');
                    $field.closest('.form-group').removeClass('has-error');
                }
            }
        });
    });
    
    // ============================================================
// Maternity Coverage Toggle
// ============================================================
$('#cobertura_maternidad_checkbox').on('change', function() {
    if ($(this).is(':checked')) {
        $('#maternidad_label').text('Sí');
        $('#maternidad_fields').fadeIn(200);
        $('#userdatos-cobertura_maternidad').val(1);
    } else {
        $('#maternidad_label').text('No');
        $('#maternidad_fields').fadeOut(200);
        $('#userdatos-cobertura_maternidad').val(0);
    }
});
    
    // ============================================================
    // FECHA INICIO - Auto-calculate expiration date
    // ============================================================
    function calcularFechaVencimiento(fechaIni) {
        if (fechaIni) {
            var parts = fechaIni.split('-');
            var year = parseInt(parts[0]);
            var month = parseInt(parts[1]) - 1;
            var day = parseInt(parts[2]);
            
            var fecha = new Date(year, month, day);
            fecha.setFullYear(fecha.getFullYear() + 1);
            fecha.setDate(fecha.getDate() - 1);
            
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
    
    $('.fecha-ini-field').on('change', toggleFechaVen);
    toggleFechaVen();
    
    // ============================================================
    // DATOS PLAN - Update coverage display
    // ============================================================
    window.datosplan = function(plan_id) {
        if (!plan_id || plan_id === '') {
            $('#userdatos-limite_cobertura').val('');
            $('#userdatos-limite_cobertura_display').text('$0.00');
            togglePlanDetails();
            return;
        }
        
        var csrfToken = $('meta[name="csrf-token"]').attr("content");
        $.ajax({
            url: "datosdelplan",
            type: "post",
            dataType: "json",
            data: {
                id: plan_id,
                "_csrf": csrfToken,
            },
            success: function(data) {
                if (data && data.data) {
                    const limite_cobertura = data.data.limite_cobertura || 0;
                    const moneda = data.data.moneda || 'USD';
                    const formatter = new Intl.NumberFormat('es-VE', {
                        style: 'currency',
                        currency: moneda
                    });
                    
                    $('#userdatos-limite_cobertura_display').text(formatter.format(limite_cobertura));
                    $('#userdatos-limite_cobertura').val(limite_cobertura);
                    $('#userdatos-moneda').val(moneda);
                    
                    togglePlanDetails();
                }
            },
            error: function() {
                $('#userdatos-limite_cobertura_display').text('$0.00');
                $('#userdatos-limite_cobertura').val('');
            }
        });
    };
    
    // Initialize on page load
    var initialPlanId = $('#plan_id').val();
    if (initialPlanId) {
        datosplan(initialPlanId);
        togglePlanDetails();
    }
});
JS
);
?>

<!-- ============================================================ -->
<!-- CUSTOM STYLES                                                 -->
<!-- ============================================================ -->
<style>
    /* ============================================================
       MICROSOFT-STYLE PANEL HEADERS WITH BACKGROUND COLORS
       ============================================================ */
    .ms-panel {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }

    .ms-panel:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.10);
    }

    .ms-panel-header {
        padding: 16px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        transition: all 0.3s ease;
    }

    .ms-panel-body {
        padding: 24px;
        background: #ffffff;
    }

    /* ============================================================
       SECTION HEADER BACKGROUND COLORS
       ============================================================ */
    .ms-panel-header.bg-indigo {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        color: #ffffff;
    }

    .ms-panel-header.bg-indigo .text-primary,
    .ms-panel-header.bg-indigo i,
    .ms-panel-header.bg-indigo h5,
    .ms-panel-header.bg-indigo .badge {
        color: #ffffff !important;
    }

    .ms-panel-header.bg-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        color: #ffffff;
    }

    .ms-panel-header.bg-success .text-success,
    .ms-panel-header.bg-success i,
    .ms-panel-header.bg-success h5,
    .ms-panel-header.bg-success .badge {
        color: #ffffff !important;
    }

    .ms-panel-header.bg-info {
        background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
        color: #ffffff;
    }

    .ms-panel-header.bg-info .text-info,
    .ms-panel-header.bg-info i,
    .ms-panel-header.bg-info h5,
    .ms-panel-header.bg-info .badge {
        color: #ffffff !important;
    }

    .ms-panel-header.bg-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #212529;
    }

    .ms-panel-header.bg-warning .text-warning,
    .ms-panel-header.bg-warning i,
    .ms-panel-header.bg-warning h5,
    .ms-panel-header.bg-warning .badge {
        color: #212529 !important;
    }

    .ms-panel-header.bg-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        color: #ffffff;
    }

    .ms-panel-header.bg-secondary .text-secondary,
    .ms-panel-header.bg-secondary i,
    .ms-panel-header.bg-secondary h5,
    .ms-panel-header.bg-secondary .badge {
        color: #ffffff !important;
    }

    .ms-panel-header.bg-purple {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: #ffffff;
    }

    .ms-panel-header.bg-purple .text-purple,
    .ms-panel-header.bg-purple i,
    .ms-panel-header.bg-purple h5,
    .ms-panel-header.bg-purple .badge {
        color: #ffffff !important;
    }

    /* Override badge colors inside colored headers */
    .ms-panel-header .badge {
        background: rgba(255, 255, 255, 0.2) !important;
        color: inherit !important;
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .ms-panel-header.bg-warning .badge {
        background: rgba(0, 0, 0, 0.15) !important;
        color: #212529 !important;
    }

    /* Header text and icon colors */
    .ms-panel-header h5 {
        color: inherit !important;
        font-weight: 600;
    }

    .ms-panel-header i {
        color: inherit !important;
    }

    /* ============================================================
       BORDER COLORS
       ============================================================ */
    .border-indigo {
        border-top: 4px solid #4f46e5 !important;
    }

    .border-success {
        border-top: 4px solid #28a745 !important;
    }

    .border-info {
        border-top: 4px solid #17a2b8 !important;
    }

    .border-warning {
        border-top: 4px solid #ffc107 !important;
    }

    .border-secondary {
        border-top: 4px solid #6c757d !important;
    }

    .border-purple {
        border-top: 4px solid #7c3aed !important;
    }

    /* ============================================================
       BADGE STYLES
       ============================================================ */
    .badge-purple {
        background-color: #7c3aed;
        color: #fff;
    }

    .text-pink {
        color: #e83e8c;
    }

    /* ============================================================
       REQUIRED FIELD STYLES
       ============================================================ */
    .required-field::after {
        content: ' *';
        color: #d13438;
        font-weight: 600;
    }

    .required-star {
        color: #d13438;
        font-weight: 700;
        font-size: 1.2rem;
        display: none;
    }

    .required-star.visible {
        display: inline-block;
    }

    /* ============================================================
       CONTRATANTE FIELDS CONTAINER
       ============================================================ */
    #contratante-fields-container {
        transition: all 0.3s ease;
    }

    /* ============================================================
       PLAN DETAILS CARDS
       ============================================================ */
    #plan-details-container .card {
        border-radius: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    #plan-details-container .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.10);
    }

    /* ============================================================
       FORM CONTROLS
       ============================================================ */
    .form-control-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        border: 1px solid #d2d0ce;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-control-lg:focus {
        border-color: #0078d4;
        box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.15);
    }

    .form-control-lg.bg-light {
        background-color: #f3f2f1 !important;
    }

    /* ============================================================
       SELECT2 OVERRIDES
       ============================================================ */
    .select2-container--default .select2-selection--single {
        height: 48px !important;
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid #d2d0ce;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 34px !important;
        font-size: 1rem;
        color: #323130;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
    }

    /* ============================================================
       CUSTOM SWITCH - MICROSOFT STYLE
       ============================================================ */
    .custom-switch .custom-control-label::before {
        border-radius: 20px;
        background-color: #d2d0ce;
        border-color: #8a8886;
        width: 2.5rem;
        height: 1.4rem;
    }

    .custom-switch .custom-control-label::after {
        border-radius: 50%;
        width: 1.2rem;
        height: 1.2rem;
        background-color: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .custom-switch .custom-control-input:checked~.custom-control-label::before {
        background-color: #0078d4;
        border-color: #0078d4;
    }

    .custom-switch .custom-control-input:checked~.custom-control-label::after {
        transform: translateX(1.1rem);
    }

    .custom-switch .custom-control-label {
        font-weight: 500;
        font-size: 1rem;
        color: #323130;
        padding-top: 2px;
    }

    /* ============================================================
       GAP UTILITY
       ============================================================ */
    .gap-3 {
        gap: 1rem;
    }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 768px) {
        .ms-panel-body {
            padding: 16px;
        }

        .ms-panel-header {
            padding: 12px 16px;
        }

        .gap-3 {
            gap: 0.75rem;
        }
    }

    /* ============================================================
       LIGHTING EFFECT ANIMATIONS
       ============================================================ */
    @keyframes lighting-sweep {
        0% {
            transform: translate(-30%, -30%) scale(1);
            opacity: 0.6;
        }

        25% {
            transform: translate(30%, -10%) scale(1.2);
            opacity: 1;
        }

        50% {
            transform: translate(-10%, 30%) scale(0.9);
            opacity: 0.7;
        }

        75% {
            transform: translate(20%, -20%) scale(1.1);
            opacity: 0.9;
        }

        100% {
            transform: translate(-30%, -30%) scale(1);
            opacity: 0.6;
        }
    }

    @keyframes spotlight-move {
        0% {
            transform: translate(-30%, -20%) scale(1);
            opacity: 0.5;
        }

        33% {
            transform: translate(20%, 10%) scale(1.3);
            opacity: 0.8;
        }

        66% {
            transform: translate(-10%, 30%) scale(0.9);
            opacity: 0.4;
        }

        100% {
            transform: translate(10%, -10%) scale(1.2);
            opacity: 0.7;
        }
    }

    @keyframes flicker-glow {
        0% {
            opacity: 0.3;
            transform: scale(1);
        }

        20% {
            opacity: 0.8;
            transform: scale(1.05);
        }

        40% {
            opacity: 0.4;
            transform: scale(0.95);
        }

        60% {
            opacity: 0.9;
            transform: scale(1.1);
        }

        80% {
            opacity: 0.5;
            transform: scale(0.98);
        }

        100% {
            opacity: 0.3;
            transform: scale(1);
        }
    }

    @keyframes glow-ring {

        0%,
        100% {
            transform: translate(-50%, -50%) scale(1);
            opacity: 0.5;
        }

        50% {
            transform: translate(-50%, -50%) scale(1.5);
            opacity: 0;
        }
    }

    @keyframes text-glow {

        0%,
        100% {
            text-shadow: 0 0 5px rgba(255, 107, 0, 0.1);
        }

        50% {
            text-shadow: 0 0 15px rgba(255, 107, 0, 0.3), 0 0 30px rgba(255, 107, 0, 0.1);
        }
    }

    @keyframes lightning-flash {

        0%,
        90%,
        100% {
            opacity: 0.6;
            transform: scale(0.9);
        }

        95% {
            opacity: 1;
            transform: scale(1.2);
            color: #ffaa00;
            text-shadow: 0 0 20px rgba(255, 107, 0, 0.8);
        }
    }

    @keyframes pulse-badge {

        0%,
        100% {
            transform: scale(1);
            box-shadow: 0 2px 12px rgba(255, 107, 0, 0.4);
        }

        50% {
            transform: scale(1.08);
            box-shadow: 0 4px 24px rgba(255, 107, 0, 0.6);
        }
    }

    @keyframes bounce-icon {

        0%,
        100% {
            transform: scale(1) rotate(0deg);
        }

        25% {
            transform: scale(1.1) rotate(-5deg);
        }

        75% {
            transform: scale(1.1) rotate(5deg);
        }
    }

    @keyframes slideInWarning {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes shimmer-progress {
        0% {
            background-position: -200% 0;
            box-shadow: 0 0 20px rgba(255, 107, 0, 0.3);
        }

        50% {
            box-shadow: 0 0 40px rgba(255, 107, 0, 0.6);
        }

        100% {
            background-position: 200% 0;
            box-shadow: 0 0 20px rgba(255, 107, 0, 0.3);
        }
    }

    .form-control-lg.bg-light:hover {
        border-color: #ff6b00 !important;
        box-shadow: 0 0 30px rgba(255, 107, 0, 0.15), inset 0 0 30px rgba(255, 107, 0, 0.05);
        transform: scale(1.01);
        transition: all 0.3s ease;
    }

    @keyframes border-pulse {

        0%,
        100% {
            border-color: #ff6b00;
            box-shadow: 0 0 0 0 rgba(255, 107, 0, 0.2);
        }

        50% {
            border-color: #ffaa00;
            box-shadow: 0 0 30px rgba(255, 107, 0, 0.15);
        }
    }

    .form-control-lg.bg-light::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border-radius: 8px;
        background: linear-gradient(45deg, #ff6b00, transparent, #ff6b00);
        background-size: 200% 200%;
        animation: ambient-light 3s ease-in-out infinite;
        z-index: -1;
        opacity: 0.3;
        pointer-events: none;
    }

    @keyframes ambient-light {

        0%,
        100% {
            background-position: 0% 50%;
            opacity: 0.2;
        }

        50% {
            background-position: 100% 50%;
            opacity: 0.5;
        }
    }

    /* ============================================================
       MICROSOFT-STYLE CUSTOM CHECKBOX
       ============================================================ */
    .custom-control.custom-checkbox {
        padding-left: 2.5rem;
        min-height: 1.8rem;
    }

    .custom-control-input {
        position: absolute;
        z-index: -1;
        opacity: 0;
    }

    .custom-control-label {
        position: relative;
        margin-bottom: 0;
        vertical-align: top;
        cursor: pointer;
        font-weight: 500;
        font-size: 1rem;
        color: #323130;
        padding-top: 2px;
        user-select: none;
    }

    .custom-control-label::before {
        position: absolute;
        top: 0.15rem;
        left: -2.5rem;
        display: block;
        width: 1.8rem;
        height: 1.8rem;
        pointer-events: none;
        content: "";
        background-color: #ffffff;
        border: 2px solid #8a8886;
        border-radius: 4px;
        transition: all 0.15s ease;
    }

    .custom-control-label::after {
        position: absolute;
        top: 0.15rem;
        left: -2.5rem;
        display: block;
        width: 1.8rem;
        height: 1.8rem;
        pointer-events: none;
        content: "";
        background: no-repeat 50% / 50% 50%;
        transition: all 0.15s ease;
    }

    .custom-control-input:checked~.custom-control-label::before {
        color: #fff;
        border-color: #0078d4;
        background-color: #0078d4;
    }

    .custom-control-input:checked~.custom-control-label::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26l2.974 2.99L8 2.193z'/%3e%3c/svg%3e");
    }

    .custom-control-input:hover~.custom-control-label::before {
        border-color: #0078d4;
        box-shadow: 0 0 0 2px rgba(0, 120, 212, 0.1);
    }

    .custom-control-input:focus~.custom-control-label::before {
        border-color: #0078d4;
        box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.25);
    }

    .custom-control-input:active~.custom-control-label::before {
        border-color: #106ebe;
        background-color: rgba(0, 120, 212, 0.1);
    }

    .custom-control-input:disabled~.custom-control-label::before {
        background-color: #f3f2f1;
        border-color: #c8c6c4;
    }

    .custom-control-input:disabled~.custom-control-label {
        color: #8a8886;
        cursor: not-allowed;
    }

    /* ============================================================
       CHECKBOX SECTION BAR ANIMATIONS
       ============================================================ */
    @keyframes bar-pulse {

        0%,
        100% {
            background-position: 0% 0%;
        }

        50% {
            background-position: 0% 100%;
        }
    }

    @keyframes dot-pulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 0.3;
        }

        50% {
            transform: scale(1.3);
            opacity: 1;
        }
    }

    .checkbox-section:hover {
        border-color: #0078d4;
        box-shadow: 0 4px 16px rgba(0, 120, 212, 0.08);
        transition: all 0.3s ease;
    }

    .checkbox-section:hover .checkbox-bar {
        width: 6px;
        transition: width 0.3s ease;
    }

    .font-weight-semibold {
        font-weight: 600 !important;
    }

    /* Custom class for white text on browse button */
    .btn-white-text,
    .btn-white-text span,
    .btn-white-text i,
    .btn-white-text .file-input-button-text {
        color: #ffffff !important;
    }

    /* ============================================================
       CONTRATANTE CARD SELECTOR - Custom Styles
       ============================================================ */
    #contratante-card:hover {
        border-color: #0078d4 !important;
        box-shadow: 0 4px 16px rgba(0, 120, 212, 0.08) !important;
        transition: all 0.3s ease;
    }

    .switch:hover .slider {
        box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.15);
    }

    .switch input:checked:hover .slider {
        box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.25);
    }

    /* ============================================================
   PLAN DETAIL CARDS - Compact Horizontal Style
   ============================================================ */
    .plan-detail-card {
        transition: all 0.3s ease;
    }

    .plan-detail-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    /* Remove the extra margin-top that Yii/Select2 adds to the field */
    #plan-details-container .form-group,
    #maternidad_toggle_container .form-group,
    #maternidad_fields .form-group {
        margin-bottom: 0;
    }

    /* Align labels on the same baseline */
    #plan-details-container>label,
    #maternidad_toggle_container>label,
    #maternidad_fields>label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #323130;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .plan-detail-card {
            height: auto !important;
            min-height: 48px;
            padding: 10px !important;
        }

        .plan-detail-card .d-flex>div:first-child {
            width: 28px !important;
            height: 28px !important;
        }

        .plan-detail-card .d-flex>div:first-child i {
            font-size: 0.85rem !important;
        }
    }
</style>