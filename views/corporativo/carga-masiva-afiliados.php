<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model app\models\MasivoAfiliadosForm */
/* @var $corporativos array */
/* @var $planes array */
/* @var $asesores array */

if (Yii::$app->session->hasFlash('error')) {
    echo '<div class="mt-4 mb-4">' . Yii::$app->session->getFlash('error') . '</div>';
}

$corporativos = (isset($corporativos) && is_array($corporativos)) ? $corporativos : [];
$planes       = (isset($planes) && is_array($planes))             ? $planes       : [];
$asesores     = (isset($asesores) && is_array($asesores))         ? $asesores     : [];

$preselectedCorporativoId = Yii::$app->request->get('corporativo_id');
$preselectedCorporativoId = ($preselectedCorporativoId !== null && $preselectedCorporativoId !== '')
    ? (int) $preselectedCorporativoId
    : null;
$isCorporativoLocked = ($preselectedCorporativoId !== null && $preselectedCorporativoId > 0);

$this->title = 'Carga Masiva de Afiliados Corporativos';
$this->params['breadcrumbs'][] = $this->title;
$getPlanesUrl = Yii::$app->urlManager->createUrl(['corporativo/obtener-planes-por-corporativo']);

// ── Single source of truth: all 24 CSV fields (10 req + 14 opt) ──
$fields = [
    ['tipo_cedula', 'Tipo de documento de identidad del afiliado.', 'Valores válidos', ['V', 'E', 'P', 'J'], 'tipo_cedula tipo documento v e p j', true],
    ['cedula', 'Número de cédula, solo dígitos, sin prefijos ni puntos.', 'Ejemplo', ['19088456'], 'cedula documento identidad numeros', true],
    ['nombres', 'Nombres completos del afiliado. Se recomienda usar mayúsculas.', 'Ejemplo', ['JUAN PABLO'], 'nombres nombre completo juan pablo', true],
    ['apellidos', 'Apellidos completos del afiliado. Se recomienda usar mayúsculas.', 'Ejemplo', ['ROJAS PEREZ'], 'apellidos apellido completo rojas perez', true],
    ['fechanac', 'Fecha de nacimiento del afiliado.', 'Formatos aceptados', ['DD/MM/AAAA', 'YYYY-MM-DD'], 'fechanac fecha nacimiento', true],
    ['sexo', 'Sexo del afiliado.', 'Valores válidos', ['M', 'F', 'Masculino', 'Femenino'], 'sexo genero m f masculino femenino', true],
    ['telefono', 'Teléfono celular de 11 dígitos. Debe incluir el 0 inicial.', 'Ejemplo', ['04121234567'], 'telefono celular movil 11 digitos 0', true],
    ['email', 'Correo electrónico único en el sistema.', 'Ejemplo', ['juan@ejemplo.com'], 'email correo electronico unico', true],
    ['direccion', 'Dirección de residencia del afiliado.', 'Ejemplo', ['Calle Principal #123'], 'direccion residencia domicilio', true],
    ['estado', 'Estado de residencia. Consulte el catálogo de estados.', 'Ejemplo', ['MIRANDA'], 'estado provincia region miranda', true],
    ['nacionalidad', 'Nacionalidad del afiliado.', 'Ejemplo', ['VENEZOLANA'], 'nacionalidad venezolana', false],
    ['estado_civil', 'Estado civil del afiliado.', 'Valores válidos', ['Soltero', 'Casado', 'Divorciado', 'Viudo'], 'estado_civil soltero casado divorciado viudo', false],
    ['lugar_nacimiento', 'Ciudad o lugar de nacimiento.', 'Ejemplo', ['CARACAS'], 'lugar_nacimiento caracas ciudad', false],
    ['profesion', 'Profesión del afiliado.', 'Ejemplo', ['INGENIERO'], 'profesion ingeniero abogado medico', false],
    ['ocupacion', 'Ocupación actual del afiliado.', 'Ejemplo', ['EMPLEADO'], 'ocupacion empleado obrero', false],
    ['actividad_economica', 'Sector económico de la actividad.', 'Valores válidos', ['Industrial', 'Comercial', 'Profesional', 'Gubernamental'], 'actividad_economica industrial comercial', false],
    ['ramo_comercial', 'Ramo o sector comercial específico.', 'Ejemplo', ['SERVICIOS'], 'ramo_comercial servicios comercio', false],
    ['descripcion_actividad', 'Tipo de actividad económica.', 'Valores válidos', ['Independiente', 'Dependiente', 'Societaria'], 'descripcion_actividad independiente dependiente', false],
    ['ingreso_anual', 'Rango de ingreso anual en salarios mínimos.', 'Ejemplo', ['De 6 a 10 Salarios mínimos'], 'ingreso_anual salario minimo', false],
    ['direccion_cobro', 'Dirección para el cobro de la póliza.', 'Ejemplo', ['Av. Principal #456'], 'direccion_cobro cobro facturacion', false],
    ['telefono_residencia', 'Teléfono fijo de residencia.', 'Ejemplo', ['02125551234'], 'telefono_residencia fijo casa', false],
    ['direccion_oficina', 'Dirección de la oficina o lugar de trabajo.', 'Ejemplo', ['Av. Principal, Edif. Azul'], 'direccion_oficina trabajo oficina', false],
    ['telefono_oficina', 'Teléfono de la oficina.', 'Ejemplo', ['2125871425'], 'telefono_oficina trabajo', false],
    ['tipo_sangre', 'Grupo sanguíneo del afiliado.', 'Valores válidos', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], 'tipo_sangre sangre', false],
];

// ── Stepper steps (Seleccionar first) ──
$steps = [
    [1, 'Seleccionar', 'Corp. · Plan · Asesor', 'fa-building', ''],
    [2, 'Descargar', 'Plantillas CSV', 'fa-download', ''],
    [3, 'Preparar CSV', 'Datos de afiliados', 'fa-edit', 'compact-step--critical'],
    [4, 'Adjuntar CSV', 'Archivo de carga', 'fa-file-csv', ''],
    [5, 'Procesar', 'Ver resultados', 'fa-play', 'compact-step--result'],
];

// ── Slide-in panels: single spec array ──
$panels = [
    'fields' => [
        'title'    => 'Diccionario de Campos CSV',
        'subtitle' => '24 campos: 10 requeridos · 14 opcionales',
        'icon'     => 'fa-table',
        'header'   => 'fields-panel__header--fields',
        'footer'   => 'fields-panel__footer',
        'footerIc' => 'fa-lightbulb text-warning',
        'footerTx' => 'Los campos opcionales pueden estar vacíos. El sistema los validará solo si tienen contenido.',
    ],
    'errores' => [
        'title'    => 'Errores Comunes',
        'subtitle' => null,
        'icon'     => 'fa-exclamation-triangle',
        'header'   => 'fields-panel__header--errores',
        'footer'   => 'fields-panel__footer--warning',
        'footerIc' => 'fa-lightbulb text-warning',
        'footerTx' => 'Revise dos veces antes de cargar. La mayoría de los errores se evitan con una buena preparación.',
    ],
    'consejos' => [
        'title'    => 'Consejos para el Éxito',
        'subtitle' => null,
        'icon'     => 'fa-check-circle',
        'header'   => 'fields-panel__header--consejos',
        'footer'   => 'fields-panel__footer--success',
        'footerIc' => 'fa-lightbulb text-warning',
        'footerTx' => 'Siguiendo estos consejos, su carga masiva será rápida y exitosa.',
    ],
];

$checklist = [
    'check1' => 'Usé la plantilla descargada',
    'check2' => 'Campos requeridos completos',
    'check3' => 'Sin cédulas duplicadas',
    'check4' => 'Emails únicos y válidos',
    'check5' => 'Teléfonos con 0 inicial',
    'check6' => 'Fechas en formato correcto',
    'check7' => 'Cédula sin prefijos',
    'check8' => 'Archivo guardado como CSV',
];

$erroresList = [
    ['Teléfonos sin el 0 inicial', 'Ejemplo incorrecto: <kbd>4121234567</kbd> → Correcto: <kbd>04121234567</kbd>'],
    ['Fechas en formato incorrecto', 'Use <kbd>DD/MM/AAAA</kbd> o <kbd>YYYY-MM-DD</kbd>. Ej: <kbd>15/08/1990</kbd>'],
    ['Cédula con prefijos o guiones', 'Solo números. Ej: <kbd>19088456</kbd> (sin V- ni puntos)'],
    ['Emails duplicados', 'Cada afiliado debe tener un email único.'],
    ['Estado escrito incorrectamente', 'Use el catálogo de estados. Ej: <kbd>MIRANDA</kbd>, <kbd>DISTRITO CAPITAL</kbd>'],
];

$consejosList = [
    ['Use la plantilla de ejemplo', 'Descárguela y respete el orden de las columnas.'],
    ['Sin cédulas duplicadas', 'Verifique que cada cédula sea única en el archivo.'],
    ['Emails únicos y válidos', 'Use formatos correctos: <kbd>usuario@dominio.com</kbd>'],
    ['Use mayúsculas en nombres', 'Ej: <kbd>JUAN PABLO</kbd> — mejora la consistencia.'],
    ['Guarde como CSV UTF-8', 'Asegure compatibilidad de caracteres especiales.'],
];

$corporativoIdForForm = ($preselectedCorporativoId !== null && $preselectedCorporativoId > 0)
    ? $preselectedCorporativoId
    : (int) ($model->corporativo_id ?? 0);
?>

<!-- PROGRESS OVERLAY -->
<div id="progress-overlay" class="progress-overlay" aria-hidden="true">
    <div class="card shadow-lg progress-overlay__card">
        <div class="card-body text-center p-4">
            <div class="spinner-border text-primary" style="width:60px;height:60px;border-width:6px;" role="status"><span class="sr-only">Cargando...</span></div>
            <h5 class="mt-3 font-weight-bold" id="progress-title">Procesando Carga Masiva</h5>
            <div class="progress mt-3" style="height:20px;border-radius:10px;overflow:hidden;">
                <div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width:0%;border-radius:10px;"><span id="upload-progress-text" class="font-weight-bold">0%</span></div>
            </div>
            <div id="progress-status" class="mt-2 text-muted small">Preparando archivo...</div>
        </div>
    </div>
</div>

<div class="container-fluid px-4">

    <header class="page-header d-flex flex-column mb-4 pb-3 border-bottom">
        <h1 class="display-4 font-weight-bold text-primary mb-3"><i class="fas fa-users mr-3"></i><?= Html::encode($this->title) ?></h1>
        <p class="lead text-muted mb-0"><i class="fas fa-rocket mr-2"></i>Registre múltiples afiliados corporativos de forma rápida y eficiente mediante un archivo CSV.</p>
    </header>

    <!-- GUIDE -->
    <section class="card shadow-sm mb-4 border-0 overflow-hidden compact-flow-card">
        <div class="card-header bg-gradient-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h2 class="h5 mb-0 font-weight-bold compact-flow-card__title"><i class="fas fa-map-signs mr-2"></i>Guía Rápida del Flujo</h2>
                <div class="d-flex align-items-center compact-flow-card__meta">
                    <span class="badge badge-light text-primary px-3 py-1 mr-2" style="color:white !important;"><i class="fas fa-clock mr-1"></i> Aprox. 5 min</span>
                    <span class="badge badge-light text-success px-3 py-1" style="color:white !important;"><i class="fas fa-check-circle mr-1"></i> Paso 3 es crítico</span>
                </div>
            </div>
        </div>
        <div class="card-body py-3 px-3">
            <div class="d-none d-md-flex align-items-center justify-content-between compact-stepper">
                <?php foreach ($steps as $i => $s): ?>
                    <?php if ($i): ?><div class="compact-step__arrow"><i class="fas fa-chevron-right"></i></div><?php endif; ?>
                    <div class="compact-step compact-step-link <?= $s[4] ?>" data-step="<?= $s[0] ?>">
                        <div class="compact-step__icon"><i class="fas <?= $s[3] ?>"></i></div>
                        <div class="compact-step__body">
                            <div class="compact-step__label"><span class="compact-step__num"><?= $s[0] ?></span> <?= $s[1] ?><?php if ($s[0] === 3): ?> <span class="compact-step__badge">CRÍTICO</span><?php endif; ?></div>
                            <div class="compact-step__hint"><?= $s[2] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="d-block d-md-none compact-stepper-mobile">
                <?php foreach ($steps as $s): ?>
                    <div class="compact-step compact-step-link mb-2 <?= $s[4] ?>" data-step="<?= $s[0] ?>">
                        <div class="compact-step__icon"><i class="fas <?= $s[3] ?>"></i></div>
                        <div class="compact-step__body">
                            <div class="compact-step__label"><span class="compact-step__num"><?= $s[0] ?></span> <?= $s[1] ?><?php if ($s[0] === 3): ?> <span class="compact-step__badge">CRÍTICO</span><?php endif; ?></div>
                            <div class="compact-step__hint"><?= $s[2] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="compact-stats-strip">
                <span class="compact-stats-strip__item"><i class="fas fa-file-csv text-success"></i> <strong>10</strong> campos requeridos</span>
                <span class="compact-stats-strip__sep"></span>
                <span class="compact-stats-strip__item"><i class="fas fa-info-circle text-info"></i> <strong>14</strong> campos opcionales</span>
                <span class="compact-stats-strip__sep"></span>
                <span class="compact-stats-strip__item"><i class="fas fa-users text-primary"></i> Carga ilimitada</span>
                <span class="compact-stats-strip__sep"></span>
                <span class="compact-stats-strip__item"><i class="fas fa-clock text-warning"></i> Procesamiento rápido</span>
            </div>
        </div>
    </section>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'id' => 'carga-masiva-form']]); ?>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- STEP 1 — SELECCIONAR CONFIGURACIÓN (incl. Vigencia)        -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <section id="step1-select" class="card shadow-lg border-primary mb-5">
        <div class="card-header bg-primary text-white py-3">
            <h2 class="h5 mb-0 font-weight-bold">
                <span class="step-badge bg-white text-primary mr-2">1</span>
                <i class="fas fa-building mr-2"></i>Seleccionar Configuración
            </h2>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4"><i class="fas fa-info-circle mr-2"></i><strong>Importante:</strong> Seleccione el corporativo, plan y asesor que se asignará a todos los afiliados del archivo CSV. El sistema determinará automáticamente la clínica asociada al plan seleccionado.</div>

            <!-- Hidden inputs carried by the form (synced by JS from the visible selects) -->
            <?= $form->field($model, 'corporativo_id')->hiddenInput(['id' => 'masivoafiliadosform-corporativo_id', 'value' => $corporativoIdForForm])->label(false) ?>
            <?= $form->field($model, 'plan_id')->hiddenInput(['id' => 'masivoafiliadosform-plan_id'])->label(false) ?>
            <?= $form->field($model, 'asesor_id')->hiddenInput(['id' => 'masivoafiliadosform-asesor_id'])->label(false) ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group p-3 bg-light rounded corporativo-field<?= $isCorporativoLocked ? ' corporativo-field--locked' : '' ?>">
                        <label for="corporativo-selector" class="font-weight-bold text-primary mb-2 d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-building mr-2"></i>Corporativo <span class="text-danger">*</span></span>
                            <?php if ($isCorporativoLocked): ?><span class="badge badge-secondary corporativo-lock-badge" title="Bloqueado porque accedió desde la carga masiva de este corporativo"><i class="fas fa-lock mr-1"></i> Bloqueado</span><?php endif; ?>
                        </label>
                        <?= Html::dropDownList('corporativo_selector', $preselectedCorporativoId, $corporativos, [
                            'id' => 'corporativo-selector',
                            'prompt' => '--- Seleccione el Corporativo ---',
                            'class' => 'form-control form-control-lg',
                            'data-preselected' => $preselectedCorporativoId ?? '',
                            'disabled' => $isCorporativoLocked,
                        ]) ?>
                        <?php if ($isCorporativoLocked): ?>
                            <small class="text-muted d-block mt-2 corporativo-lock-hint"><i class="fas fa-info-circle mr-1"></i>Este campo está bloqueado porque accedió desde la carga masiva de este corporativo. Para cambiarlo, regrese al listado y use el botón de carga masiva del corporativo correcto.</small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group p-3 bg-light rounded">
                        <label for="plan-selector" class="font-weight-bold text-primary mb-2"><i class="fas fa-file-contract mr-2"></i>Plan de Afiliación <span class="text-danger">*</span></label>
                        <?= Html::dropDownList('plan_selector', null, [], ['id' => 'plan-selector', 'prompt' => '--- Primero seleccione un corporativo ---', 'class' => 'form-control form-control-lg', 'disabled' => true]) ?>
                        <div id="plan-info-strip" class="plan-info-strip" hidden>
                            <div class="plan-info-strip__item"><span class="plan-info-strip__icon"><i class="fas fa-hospital"></i></span><span class="plan-info-strip__label">Clínica:</span><span class="plan-info-strip__value plan-info-strip__value--clinica" id="clinica-nombre">—</span></div>
                            <div class="plan-info-strip__item plan-info-strip__item--price"><span class="plan-info-strip__icon"><i class="fas fa-dollar-sign"></i></span><span class="plan-info-strip__label">Precio:</span><span class="plan-info-strip__value plan-info-strip__value--price" id="plan-precio">—</span><span class="plan-info-strip__suffix">/ mes</span></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group p-3 bg-light rounded">
                        <label for="asesor-selector" class="font-weight-bold text-primary mb-2"><i class="fas fa-user-tie mr-2"></i>Asesor / Intermediario</label>
                        <?= Html::dropDownList('asesor_selector', null, $asesores, ['id' => 'asesor-selector', 'prompt' => '--- Sin Asesor (Opcional) ---', 'class' => 'form-control form-control-lg']) ?>
                        <small class="text-muted">Opcional: Seleccione un asesor si desea asociarlo a los afiliados.</small>
                    </div>
                </div>
            </div>

            <div id="selected-info" class="mt-3">
                <div class="alert alert-secondary mb-0 text-center py-3"><i class="fas fa-hand-pointer mr-2"></i><strong>Seleccione un corporativo y un plan arriba</strong> para confirmar la configuración antes de continuar.</div>
            </div>

            <!-- ── Vigencia del Contrato (now inside Step 1) ── -->
            <hr class="my-4">
            <div class="form-group mb-0">
                <label class="font-weight-bold text-success" for="masivo-fecha-ini"><i class="fas fa-calendar-alt mr-2"></i>Vigencia del Contrato <span class="text-danger">*</span></label>
                <div class="alert alert-info mb-2" style="border-left:4px solid #17a2b8;"><i class="fas fa-info-circle mr-2"></i>Estas fechas se aplicarán al <strong>contrato corporativo</strong> de este año y a <strong>todos los contratos individuales</strong> de los afiliados que se carguen.</div>
                <div class="row">
                    <div class="col-md-5">
                        <label for="masivo-fecha-ini" class="small text-muted mb-1"><i class="fas fa-play"></i> Desde <span class="text-danger">*</span></label>
                        <?= $form->field($model, 'fecha_ini')->textInput(['type' => 'date', 'id' => 'masivo-fecha-ini', 'class' => 'form-control form-control-lg', 'required' => true])->label(false) ?>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center"><i class="fas fa-arrow-right text-muted" style="font-size:1.2rem;"></i></div>
                    <div class="col-md-5">
                        <label for="masivo-fecha-ven" class="small text-muted mb-1"><i class="fas fa-stop"></i> Hasta <span class="badge badge-secondary ml-1" style="font-size:10px;font-weight:500;">calculada</span></label>
                        <?= $form->field($model, 'fecha_ven')->textInput(['type' => 'date', 'id' => 'masivo-fecha-ven', 'class' => 'form-control form-control-lg bg-light', 'readonly' => true])->label(false) ?>
                    </div>
                </div>
                <small class="text-muted d-block mt-2"><i class="fas fa-lightbulb text-warning"></i>La fecha de fin se calcula automáticamente como <strong>inicio + 1 año − 1 día</strong>.</small>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- STEP 2 — DESCARGAR PLANTILLAS                               -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <section id="step2-download" class="card shadow-sm mb-5 border-0">
        <div class="card-header bg-gradient-step1 text-white py-3">
            <h2 class="h5 mb-0 font-weight-bold"><span class="step-badge mr-2">2</span><i class="fas fa-download mr-2"></i>Descargar Plantillas</h2>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Comience descargando los recursos necesarios para preparar su carga:</p>
            <div class="d-flex flex-wrap">
                <?= Html::a('<i class="fas fa-file-csv mr-2"></i> Plantilla CSV de Ejemplo', ['/corporativo/descargar-plantilla'], ['class' => 'btn btn-success mr-3 mb-2 px-4 py-2 shadow-sm']) ?>
                <?= Html::a('<i class="fas fa-map-marker-alt mr-2"></i> Catálogo de Estados', ['/corporativo/descargar-catalogo-estados'], ['class' => 'btn btn-outline-primary mr-3 mb-2 px-4 py-2 shadow-sm']) ?>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- STEP 3 — PREPARAR CSV                                       -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <section id="step3-prepare" class="card shadow-sm mb-5 border-0">
        <div class="card-header bg-gradient-step2 text-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h2 class="h5 mb-0 font-weight-bold"><span class="step-badge mr-2">3</span><i class="fas fa-edit mr-2"></i>Preparar Archivo CSV</h2>
                <span class="badge badge-light text-success px-3 py-2" style="font-size:.75rem;color:white !important;"><i class="fas fa-exclamation-circle mr-1"></i> PASO CRÍTICO</span>
            </div>
        </div>
        <div class="card-body">
            <div class="step2-hero">
                <div class="step2-hero__icon"><i class="fas fa-lightbulb"></i></div>
                <div class="step2-hero__body">
                    <h3 class="step2-hero__title">Un archivo bien preparado garantiza una carga exitosa</h3>
                    <p class="step2-hero__text mb-0">Use la plantilla descargada, respete el orden de columnas y verifique los datos antes de subir.</p>
                </div>
            </div>
            <div class="step2-pills">
                <div class="step2-pill"><span class="step2-pill__icon step2-pill__icon--success"><i class="fas fa-check-double"></i></span>
                    <div><strong>Datos correctos</strong><small class="d-block text-muted">Campos requeridos completos</small></div>
                </div>
                <div class="step2-pill"><span class="step2-pill__icon step2-pill__icon--info"><i class="fas fa-file-alt"></i></span>
                    <div><strong>Formato CSV</strong><small class="d-block text-muted">Misma estructura de la plantilla</small></div>
                </div>
                <div class="step2-pill"><span class="step2-pill__icon step2-pill__icon--warning"><i class="fas fa-eye"></i></span>
                    <div><strong>Sin duplicados</strong><small class="d-block text-muted">Cédulas y emails únicos</small></div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="step2-action-card step2-action-card--checklist">
                        <div class="step2-action-card__head">
                            <span class="step2-action-card__icon"><i class="fas fa-clipboard-list"></i></span>
                            <div>
                                <h3 class="step2-action-card__title">Lista de Verificación</h3><small class="text-muted">Marque cada punto antes de subir</small>
                            </div>
                        </div>
                        <div class="step2-action-card__body">
                            <?php $last = array_key_last($checklist);
                            foreach ($checklist as $id => $label): ?>
                                <div class="custom-control custom-checkbox <?= $id === $last ? '' : 'mb-2' ?>">
                                    <input type="checkbox" class="custom-control-input" id="<?= $id ?>">
                                    <label class="custom-control-label small" for="<?= $id ?>"><?= $label ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="step2-action-card step2-action-card--diccionario">
                        <div class="step2-action-card__head">
                            <span class="step2-action-card__icon step2-action-card__icon--primary"><i class="fas fa-table"></i></span>
                            <div>
                                <h3 class="step2-action-card__title">Diccionario de Campos</h3><small class="text-muted">24 campos · buscador y filtros</small>
                            </div>
                        </div>
                        <div class="step2-action-card__body">
                            <p class="small text-muted mb-3">Consulte la especificación completa de cada campo: nombres, formatos, valores válidos y ejemplos.</p>
                            <button type="button" class="btn btn-primary btn-block mb-3" id="open-fields-panel"><i class="fas fa-book-open mr-2"></i> Abrir Diccionario de Campos</button>
                            <div class="step2-quicklinks">
                                <button type="button" class="step2-quicklink step2-quicklink--warning" id="open-errores-panel"><i class="fas fa-exclamation-triangle"></i><span><strong>Errores Comunes</strong><small>Qué evitar</small></span><i class="fas fa-chevron-right step2-quicklink__arrow"></i></button>
                                <button type="button" class="step2-quicklink step2-quicklink--success" id="open-consejos-panel"><i class="fas fa-check-circle"></i><span><strong>Consejos para el Éxito</strong><small>Mejores prácticas</small></span><i class="fas fa-chevron-right step2-quicklink__arrow"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SLIDE-IN PANELS -->
    <?php foreach ($panels as $key => $p): ?>
        <div id="<?= $key ?>-panel-overlay" class="fields-panel-overlay"></div>
        <aside id="<?= $key ?>-panel" class="fields-panel" aria-hidden="true" role="dialog" aria-label="<?= Html::encode($p['title']) ?>">
            <div class="fields-panel__header <?= $p['header'] ?>">
                <div class="d-flex align-items-center">
                    <i class="fas <?= $p['icon'] ?> text-white mr-2" style="font-size:1.5rem;"></i>
                    <div>
                        <h4 class="mb-0 text-white font-weight-bold"><?= Html::encode($p['title']) ?></h4>
                        <?php if ($p['subtitle']): ?><small class="text-white-50" style="font-size:.9rem;"><?= Html::encode($p['subtitle']) ?></small><?php endif; ?>
                    </div>
                </div>
                <button type="button" class="fields-panel__close" id="close-<?= $key ?>-panel" aria-label="Cerrar"><i class="fas fa-times"></i></button>
            </div>

            <?php if ($key === 'fields'): ?>
                <div class="fields-panel__toolbar">
                    <div class="input-group mb-2">
                        <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span></div>
                        <input type="text" id="fields-search" class="form-control border-left-0" placeholder="Buscar campo... (ej: cedula, email, teléfono)" autocomplete="off">
                        <div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="fields-search-clear" title="Limpiar"><i class="fas fa-times"></i></button></div>
                    </div>
                    <div class="btn-group btn-block" role="group" aria-label="Filtro de campos">
                        <button type="button" class="btn btn-outline-primary active" data-filter="all"><i class="fas fa-list mr-1"></i> Todos <span class="badge badge-light ml-1">24</span></button>
                        <button type="button" class="btn btn-outline-success" data-filter="required"><i class="fas fa-exclamation-circle mr-1"></i> Requeridos <span class="badge badge-success ml-1">10</span></button>
                        <button type="button" class="btn btn-outline-info" data-filter="optional"><i class="fas fa-info-circle mr-1"></i> Opcionales <span class="badge badge-info ml-1">14</span></button>
                    </div>
                </div>
                <div class="fields-panel__body" id="fields-panel-body">
                    <div id="fields-empty-state" class="fields-empty-state" hidden>
                        <i class="fas fa-search-minus"></i>
                        <h6 class="mt-3 mb-1">Sin resultados</h6>
                        <p class="text-muted small mb-0">No se encontraron campos que coincidan con "<span id="fields-empty-term"></span>".</p>
                    </div>
                    <?php foreach ([true, false] as $req): $list = array_values(array_filter($fields, fn($f) => $f[5] === $req));
                        $type = $req ? 'required' : 'optional'; ?>
                        <div class="fields-section" data-section="<?= $type ?>">
                            <button type="button" class="fields-section__header fields-section__header--<?= $type ?> <?= $req ? '' : 'collapsed' ?>" data-toggle="collapse" data-target="#section-<?= $type ?>" aria-expanded="<?= $req ? 'true' : 'false' ?>">
                                <span class="fields-section__title">
                                    <i class="fas <?= $req ? 'fa-exclamation-circle' : 'fa-info-circle' ?> mr-2"></i>
                                    Campos <?= $req ? 'Requeridos' : 'Opcionales' ?>
                                    <span class="badge badge-<?= $req ? 'success' : 'info' ?> ml-2"><?= count($list) ?></span>
                                </span>
                                <span class="fields-section__chevron"><i class="fas fa-chevron-down"></i></span>
                            </button>
                            <div class="collapse <?= $req ? 'show' : '' ?>" id="section-<?= $type ?>">
                                <div class="fields-section__intro">
                                    <i class="fas <?= $req ? 'fa-info-circle text-success' : 'fa-lightbulb text-info' ?> mr-1"></i>
                                    <?= $req
                                        ? 'Estos 10 campos son <strong>obligatorios</strong>. El sistema rechazará la fila si falta alguno.'
                                        : 'Estos 14 campos son <strong>opcionales</strong>. Si están vacíos, el sistema los ignora; si tienen contenido, los valida.' ?>
                                </div>
                                <?php foreach ($list as $idx => $f): ?>
                                    <div class="field-card" data-field-type="<?= $type ?>" data-search="<?= Html::encode($f[4]) ?>">
                                        <div class="field-card__head">
                                            <span class="field-card__num"><?= str_pad($idx + ($req ? 1 : 11), 2, '0', STR_PAD_LEFT) ?></span>
                                            <code class="field-card__name"><?= Html::encode($f[0]) ?></code>
                                            <span class="field-card__badge field-card__badge--<?= $type ?>"><?= $req ? 'Requerido' : 'Opcional' ?></span>
                                        </div>
                                        <p class="field-card__desc"><?= Html::encode($f[1]) ?></p>
                                        <div class="field-card__values">
                                            <span class="field-card__values-label"><?= Html::encode($f[2]) ?>:</span>
                                            <?php foreach ($f[3] as $val): ?><kbd><?= Html::encode($val) ?></kbd><?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: $items = $key === 'errores' ? $erroresList : $consejosList;
                $iconClass = $key === 'errores' ? 'fa-times-circle text-danger' : 'fa-check-circle text-success'; ?>
                <div class="fields-panel__body">
                    <div class="fields-panel__section">
                        <div class="fields-panel__section-title fields-panel__section-title--<?= $key === 'errores' ? 'warning' : 'success' ?>">
                            <i class="fas <?= $key === 'errores' ? 'fa-list' : 'fa-star' ?> mr-2"></i> <?= $key === 'errores' ? 'Errores Frecuentes' : 'Mejores Prácticas' ?>
                        </div>
                        <?php foreach ($items as $it): ?>
                            <div class="fields-panel__field">
                                <div class="fields-panel__field-name"><i class="fas <?= $iconClass ?> mr-2"></i> <?= $it[0] ?></div>
                                <div class="fields-panel__field-desc"><?= $it[1] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="fields-panel__footer <?= $p['footer'] ?>"><i class="fas <?= $p['footerIc'] ?> mr-1"></i><small class="text-muted"><?= $p['footerTx'] ?></small></div>
                </div>
            <?php endif; ?>
        </aside>
    <?php endforeach; ?>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- STEP 4 — ADJUNTAR ARCHIVO CSV (only)                        -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <section id="step4-form" class="card shadow-lg border-warning mb-5">
        <div class="card-header bg-gradient-step4 text-white py-3">
            <h2 class="h5 mb-0 font-weight-bold"><span class="step-badge mr-2">4</span><i class="fas fa-file-csv mr-2"></i>Adjuntar Archivo CSV</h2>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-info mb-3"><i class="fas fa-info-circle mr-2"></i>Seleccione el archivo CSV que preparó en el Paso 3. El archivo debe respetar el formato de la plantilla descargada.</div>
            <div class="form-group mb-0">
                <label class="font-weight-bold text-warning" for="csv-file-input"><i class="fas fa-file-csv mr-2"></i>Archivo CSV <span class="text-danger">*</span></label>
                <?= $form->field($model, 'masivoFile')->fileInput(['class' => 'sr-only', 'accept' => '.csv', 'required' => true, 'id' => 'csv-file-input'])->label(false) ?>
                <div class="csv-file-picker" id="csv-file-picker">
                    <label for="csv-file-input" class="csv-file-picker__btn" style="color:white !important;"><i class="fas fa-folder-open mr-2"></i> Seleccionar archivo CSV</label>
                    <span class="csv-file-picker__name" id="csv-file-picker-name">Ningún archivo seleccionado</span>
                </div>
                <small class="text-muted d-block mt-2">Formatos permitidos: CSV | Tamaño máximo: 5MB</small>
                <div id="file-info" class="mt-2" hidden>
                    <div class="alert alert-success small mb-0"><i class="fas fa-check-circle mr-1"></i>Archivo seleccionado: <strong id="file-name">-</strong><span class="badge badge-info ml-2" id="file-size">-</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- STEP 5 — PROCESAR                                           -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <section id="step5-process" class="card shadow-lg border-success mb-5">
        <div class="card-header bg-gradient-step5 text-white py-3">
            <h2 class="h5 mb-0 font-weight-bold"><span class="step-badge mr-2">5</span><i class="fas fa-play mr-2"></i>Procesar Carga Masiva</h2>
        </div>
        <div class="card-body p-4 text-center">
            <?= Html::submitButton('<i class="fas fa-paper-plane mr-2"></i> Procesar Carga Masiva', ['class' => 'btn btn-success btn-lg px-5 py-3 shadow', 'style' => 'font-size:1.2rem;', 'id' => 'submit-button']) ?>
            <div id="validation-alert" class="mt-3" hidden>
                <div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle mr-2"></i><span id="validation-message"></span></div>
            </div>
        </div>
    </section>

    <?php ActiveForm::end(); ?>
</div>

<style>
    :root {
        --fp: #0078d4;
        --fpd: #106ebe;
        --g50: #faf9f8;
        --g100: #f3f2f1;
        --g200: #edebe9;
        --g300: #e1dfdd;
        --g400: #c8c6c4;
        --g600: #8a8886;
        --g700: #605e5c;
        --g800: #3b3a39;
        --g900: #201f1e;
        --sh1: 0 1.6px 3.6px 0 rgba(0, 0, 0, .132), 0 .3px .9px 0 rgba(0, 0, 0, .108);
        --sh2: 0 6.4px 14.4px 0 rgba(0, 0, 0, .132), 0 1.2px 3.6px 0 rgba(0, 0, 0, .108);
        --r: 8px;
        --rl: 12px
    }

    body {
        background: var(--g50);
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', 'Helvetica Neue', sans-serif;
        color: var(--g900)
    }

    .bg-gradient-primary,
    .bg-gradient-step1 {
        background: linear-gradient(135deg, #4e73df, #224abe)
    }

    .bg-gradient-step2 {
        background: linear-gradient(135deg, #1cc88a, #13855c)
    }

    .bg-gradient-step4 {
        background: linear-gradient(135deg, #f6c23e, #dda20a)
    }

    .bg-gradient-step5 {
        background: linear-gradient(135deg, #e74a3b, #be2617)
    }

    .progress-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .6);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center
    }

    .progress-overlay.is-visible {
        display: flex
    }

    .progress-overlay__card {
        max-width: 500px;
        width: 90%;
        border-radius: 16px;
        background: #fff;
        animation: fadeInUp .3s ease
    }

    #upload-progress-bar {
        transition: width .3s ease
    }

    .compact-flow-card {
        border-radius: var(--rl);
        border: 1px solid var(--g200);
        box-shadow: var(--sh1);
        background: #fff
    }

    .compact-flow-card__title {
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: -.2px
    }

    .compact-flow-card__meta .badge {
        font-size: .75rem;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 20px
    }

    .compact-stepper {
        gap: 6px
    }

    .compact-step {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-radius: var(--r);
        background: var(--g50);
        border: 1px solid var(--g200);
        transition: all .2s;
        flex: 1;
        min-width: 0;
        cursor: pointer
    }

    .compact-step:hover {
        transform: translateY(-2px);
        box-shadow: var(--sh2);
        border-color: var(--fp);
        background: #fff
    }

    .compact-step--critical {
        background: linear-gradient(135deg, #e8f8f0, #c3e6cb);
        border: 2px solid #1cc88a;
        box-shadow: 0 0 12px rgba(28, 200, 138, .15)
    }

    .compact-step--result {
        background: linear-gradient(135deg, #e8f8f0, #c3e6cb);
        border: 2px solid #1cc88a
    }

    .compact-step__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #fff;
        color: var(--fp);
        font-size: 1rem;
        box-shadow: var(--sh1);
        flex-shrink: 0
    }

    .compact-step--critical .compact-step__icon {
        color: #1cc88a
    }

    .compact-step__body {
        min-width: 0;
        flex: 1
    }

    .compact-step__label {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--g900);
        line-height: 1.2;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap
    }

    .compact-step__num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--fp);
        color: #fff;
        font-size: .8rem;
        font-weight: 800;
        flex-shrink: 0
    }

    .compact-step--critical .compact-step__num,
    .compact-step--result .compact-step__num {
        background: #1cc88a
    }

    .compact-step__badge {
        font-size: .65rem;
        font-weight: 800;
        background: #e74a3b;
        color: #fff;
        padding: 2px 8px;
        border-radius: 12px;
        letter-spacing: .3px;
        animation: pulseBadge 1.5s infinite
    }

    .compact-step__hint {
        font-size: .8rem;
        color: var(--g600);
        line-height: 1.15;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis
    }

    .compact-step__arrow {
        color: var(--g400);
        font-size: .8rem;
        flex-shrink: 0;
        padding: 0 2px
    }

    .compact-stats-strip {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px dashed var(--g300);
        font-size: .85rem;
        color: var(--g700)
    }

    .compact-stats-strip__item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: var(--g50);
        border: 1px solid var(--g200);
        border-radius: 20px;
        white-space: nowrap
    }

    .compact-stats-strip__sep {
        width: 1px;
        height: 16px;
        background: var(--g300)
    }

    .step2-hero {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 20px;
        margin-bottom: 18px;
        background: linear-gradient(135deg, #e8f8f0, #d4f1e3);
        border-left: 5px solid #1cc88a;
        border-radius: var(--rl)
    }

    .step2-hero__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        background: #fff;
        border-radius: 12px;
        color: #1cc88a;
        font-size: 1.5rem;
        box-shadow: var(--sh1)
    }

    .step2-hero__title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #13855c;
        margin-bottom: 2px
    }

    .step2-hero__text {
        font-size: .88rem;
        color: var(--g700)
    }

    .step2-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 4px
    }

    .step2-pill {
        flex: 1 1 220px;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: #fff;
        border: 1px solid var(--g200);
        border-radius: var(--r);
        transition: all .15s
    }

    .step2-pill:hover {
        border-color: var(--fp);
        box-shadow: var(--sh1)
    }

    .step2-pill__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border-radius: 8px;
        font-size: .95rem
    }

    .step2-pill__icon--success {
        background: #d4edda;
        color: #155724
    }

    .step2-pill__icon--info {
        background: #d1ecf1;
        color: #0c5460
    }

    .step2-pill__icon--warning {
        background: #fff3cd;
        color: #856404
    }

    .step2-pill strong {
        font-size: .92rem;
        color: var(--g900);
        display: block;
        line-height: 1.2
    }

    .step2-pill small {
        font-size: .78rem;
        line-height: 1.2
    }

    .step2-action-card {
        background: #fff;
        border: 1px solid var(--g200);
        border-radius: var(--rl);
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: box-shadow .2s
    }

    .step2-action-card:hover {
        box-shadow: var(--sh1)
    }

    .step2-action-card__head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--g200);
        background: var(--g50);
        border-radius: var(--rl) var(--rl) 0 0
    }

    .step2-action-card__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 10px;
        font-size: 1rem;
        background: #fff3cd;
        color: #856404
    }

    .step2-action-card__icon--primary {
        background: #cfe2ff;
        color: var(--fp)
    }

    .step2-action-card__title {
        font-size: .98rem;
        font-weight: 700;
        color: var(--g900);
        margin: 0;
        line-height: 1.2
    }

    .step2-action-card__body {
        padding: 16px 18px;
        flex: 1
    }

    .step2-quicklinks {
        display: flex;
        flex-direction: column;
        gap: 8px
    }

    .step2-quicklink {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 10px 14px;
        background: #fff;
        border: 1px solid var(--g200);
        border-radius: var(--r);
        text-align: left;
        cursor: pointer;
        transition: all .15s
    }

    .step2-quicklink:hover {
        border-color: var(--fp);
        background: var(--g50);
        transform: translateX(2px)
    }

    .step2-quicklink>i:first-child {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border-radius: 8px;
        font-size: .9rem
    }

    .step2-quicklink--warning>i:first-child {
        background: #fff3cd;
        color: #856404
    }

    .step2-quicklink--success>i:first-child {
        background: #d4edda;
        color: #155724
    }

    .step2-quicklink>span {
        flex: 1;
        min-width: 0
    }

    .step2-quicklink strong {
        display: block;
        font-size: .88rem;
        color: var(--g900);
        line-height: 1.2
    }

    .step2-quicklink small {
        display: block;
        font-size: .75rem;
        color: var(--g600);
        line-height: 1.2
    }

    .step2-quicklink__arrow {
        color: var(--g400);
        font-size: .8rem;
        flex-shrink: 0;
        transition: transform .15s
    }

    .step2-quicklink:hover .step2-quicklink__arrow {
        transform: translateX(3px);
        color: var(--fp)
    }

    .plan-info-strip {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 20px;
        margin-top: 12px;
        padding: 16px 18px;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #a7f3d0;
        border-radius: var(--rl);
        font-size: .95rem;
        animation: fadeInUp .25s ease
    }

    .plan-info-strip__item {
        display: inline-flex;
        align-items: center;
        gap: 10px
    }

    .plan-info-strip__item--price {
        padding-left: 20px;
        border-left: 1px dashed #6ee7b7
    }

    .plan-info-strip__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #fff;
        color: #059669;
        font-size: .95rem;
        flex-shrink: 0
    }

    .plan-info-strip__label {
        color: var(--g700);
        font-weight: 600;
        font-size: .95rem
    }

    .plan-info-strip__value {
        color: #065f46;
        font-weight: 700
    }

    .plan-info-strip__value--clinica {
        font-size: 1.6rem;
        font-weight: 800;
        color: #065f46;
        letter-spacing: -.3px;
        line-height: 1.2
    }

    .plan-info-strip__value--price {
        font-size: 2.2rem;
        font-weight: 800;
        color: #107c10;
        letter-spacing: -.8px;
        font-variant-numeric: tabular-nums;
        line-height: 1.1
    }

    .plan-info-strip__suffix {
        font-size: .9rem;
        color: var(--g600);
        font-weight: 500;
        margin-left: 4px
    }

    .fields-panel-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .4);
        z-index: 1040;
        opacity: 0;
        visibility: hidden;
        transition: opacity .3s, visibility .3s;
        backdrop-filter: blur(2px)
    }

    .fields-panel-overlay.is-open {
        opacity: 1;
        visibility: visible
    }

    .fields-panel {
        position: fixed;
        top: 0;
        right: 0;
        width: 620px;
        max-width: 100%;
        height: 100%;
        background: #fff;
        z-index: 1050;
        transform: translateX(100%);
        transition: transform .35s cubic-bezier(.4, 0, .2, 1);
        display: flex;
        flex-direction: column;
        box-shadow: -8px 0 30px rgba(0, 0, 0, .15)
    }

    .fields-panel.is-open {
        transform: translateX(0)
    }

    .fields-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        flex-shrink: 0
    }

    .fields-panel__header--fields,
    .fields-panel__header--consejos {
        background: linear-gradient(135deg, #1cc88a, #13855c)
    }

    .fields-panel__header--errores {
        background: linear-gradient(135deg, #f6c23e, #dda20a)
    }

    .fields-panel__header h4 {
        font-size: 1.35rem
    }

    .fields-panel__close {
        background: rgba(255, 255, 255, .2);
        border: none;
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .2s, transform .2s;
        font-size: 1.2rem
    }

    .fields-panel__close:hover {
        background: rgba(255, 255, 255, .35);
        transform: rotate(90deg)
    }

    .fields-panel__toolbar {
        padding: 16px 20px;
        background: var(--g50);
        border-bottom: 1px solid var(--g200);
        flex-shrink: 0
    }

    .fields-panel__toolbar .form-control {
        font-size: 1rem
    }

    .fields-panel__toolbar .btn {
        font-size: .95rem
    }

    .fields-panel__body {
        flex: 1;
        overflow-y: auto;
        padding: 20px 24px 32px
    }

    .fields-panel__body::-webkit-scrollbar {
        width: 6px
    }

    .fields-panel__body::-webkit-scrollbar-track {
        background: #f1f1f1
    }

    .fields-panel__body::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px
    }

    .fields-section {
        margin-bottom: 20px
    }

    .fields-section__header {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        cursor: pointer;
        transition: filter .2s;
        text-align: left
    }

    .fields-section__header--required {
        background: #d4edda;
        color: #155724
    }

    .fields-section__header--optional {
        background: #d1ecf1;
        color: #0c5460
    }

    .fields-section__header:hover {
        filter: brightness(.97)
    }

    .fields-section__chevron {
        transition: transform .25s;
        font-size: .95rem
    }

    .fields-section__header.collapsed .fields-section__chevron {
        transform: rotate(-90deg)
    }

    .fields-section__intro {
        padding: 12px 16px;
        font-size: .98rem;
        color: var(--g700);
        background: var(--g50);
        border-radius: 8px;
        margin: 12px 0 10px;
        line-height: 1.55
    }

    .field-card {
        padding: 16px 18px;
        margin-bottom: 10px;
        border: 1px solid var(--g200);
        border-radius: 10px;
        background: #fff;
        transition: all .15s
    }

    .field-card:hover {
        border-color: var(--fp);
        box-shadow: var(--sh1);
        background: var(--g50)
    }

    .field-card__head {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
        flex-wrap: wrap
    }

    .field-card__num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 26px;
        padding: 0 8px;
        background: var(--g100);
        color: var(--g600);
        font-size: .85rem;
        font-weight: 800;
        border-radius: 6px;
        font-variant-numeric: tabular-nums
    }

    .field-card__name {
        font-size: 1.15rem;
        font-weight: 700;
        padding: 4px 10px;
        background: #f4f4f4;
        color: #e83e8c;
        border-radius: 4px;
        font-family: Consolas, Monaco, monospace
    }

    .field-card__badge {
        margin-left: auto;
        font-size: .78rem;
        font-weight: 800;
        padding: 4px 12px;
        border-radius: 12px;
        letter-spacing: .3px;
        text-transform: uppercase
    }

    .field-card__badge--required {
        background: #d4edda;
        color: #155724
    }

    .field-card__badge--optional {
        background: #d1ecf1;
        color: #0c5460
    }

    .field-card__desc {
        font-size: 1.02rem;
        color: var(--g700);
        line-height: 1.55;
        margin: 0 0 10px
    }

    .field-card__values {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        padding-top: 10px;
        border-top: 1px dashed var(--g200);
        font-size: .98rem
    }

    .field-card__values-label {
        color: var(--g600);
        font-weight: 600;
        margin-right: 4px;
        font-size: .95rem
    }

    .field-card__values kbd {
        font-size: .92rem;
        padding: 3px 9px;
        background: var(--g800);
        color: #fff;
        border-radius: 4px;
        font-weight: 500
    }

    .fields-panel__section {
        margin-bottom: 26px
    }

    .fields-panel__section-title {
        font-size: 1.05rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px
    }

    .fields-panel__section-title--warning {
        background: #fff3cd;
        color: #856404
    }

    .fields-panel__section-title--success {
        background: #d4edda;
        color: #155724
    }

    .fields-panel__field {
        padding: 14px 16px;
        border-bottom: 1px solid var(--g100);
        transition: background .15s
    }

    .fields-panel__field:last-child {
        border-bottom: none
    }

    .fields-panel__field:hover {
        background: var(--g50);
        border-radius: 6px
    }

    .fields-panel__field-name {
        font-size: 1.02rem;
        font-weight: 600;
        color: var(--g900);
        margin-bottom: 4px
    }

    .fields-panel__field-desc {
        font-size: .98rem;
        color: var(--g700);
        line-height: 1.55
    }

    .fields-panel__field-desc kbd {
        font-size: .85rem;
        padding: 2px 7px;
        background: var(--g800);
        color: #fff;
        border-radius: 4px
    }

    .fields-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--g600)
    }

    .fields-empty-state i {
        font-size: 3rem;
        color: var(--g300)
    }

    .fields-empty-state h6 {
        color: var(--g800);
        font-size: 1.15rem
    }

    .fields-empty-state p {
        font-size: 1rem
    }

    .fields-panel__footer {
        padding: 16px;
        background: #fff9e6;
        border-radius: 8px;
        border-left: 3px solid #ffc107;
        margin-top: 14px
    }

    .fields-panel__footer--warning {
        background: #fff3cd
    }

    .fields-panel__footer--success {
        background: #d4edda;
        border-left-color: #28a745
    }

    .fields-panel__footer small {
        font-size: .98rem;
        line-height: 1.55
    }

    .csv-file-picker {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        background: var(--g50);
        border: 1px dashed var(--g300);
        border-radius: var(--r);
        transition: all .15s
    }

    .csv-file-picker:hover {
        border-color: var(--fp);
        background: #fff
    }

    .csv-file-picker__btn {
        display: inline-flex;
        align-items: center;
        margin: 0;
        padding: 8px 16px;
        background: var(--fp);
        color: #fff;
        font-size: .95rem;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: background .15s;
        white-space: nowrap
    }

    .csv-file-picker__btn:hover {
        background: var(--fpd)
    }

    .csv-file-picker__name {
        font-size: 1.2rem;
        color: var(--g700);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0
    }

    .csv-file-picker__name.is-set {
        color: var(--g900);
        font-weight: 600
    }

    .corporativo-field--locked {
        border-left: 4px solid #6c757d;
        background: linear-gradient(135deg, #f8f9fa, #eef0f2) !important;
        position: relative
    }

    .corporativo-field--locked select:disabled {
        background-color: #e9ecef !important;
        cursor: not-allowed;
        color: #495057 !important;
        font-weight: 700;
        opacity: 1
    }

    .corporativo-lock-badge {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        padding: 5px 10px;
        border-radius: 20px
    }

    .corporativo-lock-hint {
        font-size: .78rem;
        line-height: 1.4;
        padding-left: 2px
    }

    .step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: #fff;
        color: #4e73df;
        border-radius: 50%;
        font-weight: bold;
        margin-right: 8px
    }

    code {
        background: #f4f4f4;
        padding: 2px 6px;
        border-radius: 4px;
        color: #e83e8c
    }

    @keyframes pulseBadge {

        0%,
        100% {
            transform: scale(1)
        }

        50% {
            transform: scale(1.05)
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }

    @keyframes pulseRed {
        0% {
            box-shadow: 0 0 0 0 rgba(231, 74, 59, .7);
            border-color: #e74a3b
        }

        70% {
            box-shadow: 0 0 0 15px rgba(231, 74, 59, 0);
            border-color: #e74a3b
        }

        100% {
            box-shadow: 0 0 0 0 rgba(231, 74, 59, 0);
            border-color: transparent
        }
    }

    .required-missing {
        animation: pulseRed .8s ease-out;
        border: 2px solid #e74a3b !important;
        border-radius: 8px
    }

    @media (max-width:768px) {
        .display-4 {
            font-size: 1.8rem
        }

        .compact-step {
            padding: 8px 10px
        }

        .compact-step__icon {
            width: 30px;
            height: 30px;
            font-size: .85rem
        }

        .compact-step__label {
            font-size: 1rem
        }

        .compact-step__hint {
            font-size: .75rem
        }

        .plan-info-strip {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            padding: 14px
        }

        .plan-info-strip__item--price {
            margin-left: 0;
            padding-left: 0;
            border-left: none;
            padding-top: 10px;
            border-top: 1px dashed #6ee7b7;
            width: 100%
        }

        .plan-info-strip__value--clinica {
            font-size: 1.3rem
        }

        .plan-info-strip__value--price {
            font-size: 1.8rem
        }

        .fields-panel {
            width: 100%
        }

        .step2-hero {
            flex-direction: column;
            align-items: flex-start;
            text-align: left
        }

        .step2-pill {
            flex: 1 1 100%
        }

        .field-card__name {
            font-size: 1.05rem
        }

        .field-card__desc {
            font-size: .98rem
        }

        .fields-panel__header h4 {
            font-size: 1.15rem
        }

        .csv-file-picker {
            flex-direction: column;
            align-items: stretch
        }

        .csv-file-picker__btn {
            justify-content: center
        }

        .csv-file-picker__name {
            text-align: center
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(function() {
        'use strict';

        const getPlanesUrl = "<?= $getPlanesUrl ?>";
        const PANEL_KEYS = ['fields', 'errores', 'consejos'];
        const STEP_TARGETS = {
            1: '#step1-select',
            2: '#step2-download',
            3: '#step3-prepare',
            4: '#step4-form',
            5: '#step5-process'
        };

        // ── Slide-in panels ──
        function togglePanel(key, open) {
            $('#' + key + '-panel').toggleClass('is-open', open).attr('aria-hidden', open ? 'false' : 'true');
            $('#' + key + '-panel-overlay').toggleClass('is-open', open);
            $('body').css('overflow', open ? 'hidden' : '');
        }
        PANEL_KEYS.forEach(function(key) {
            $('#open-' + key + '-panel').on('click', function() {
                togglePanel(key, true);
                if (key === 'fields') resetFieldsFilter();
            });
            $('#close-' + key + '-panel, #' + key + '-panel-overlay').on('click', function() {
                togglePanel(key, false);
            });
        });
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') PANEL_KEYS.forEach(function(k) {
                togglePanel(k, false);
            });
        });

        // ── Diccionario: search + filter ──
        let currentFilter = 'all';

        function applyFieldFilters() {
            const term = ($('#fields-search').val() || '').toLowerCase().trim();
            let visible = 0;
            $('.field-card').each(function() {
                const $c = $(this);
                const okFilter = currentFilter === 'all' || currentFilter === $c.data('field-type');
                const okSearch = !term || (($c.data('search') || '') + ' ' + $c.text().toLowerCase()).indexOf(term) !== -1;
                const show = okFilter && okSearch;
                $c.toggle(show);
                if (show) visible++;
            });
            $('#fields-empty-state').attr('hidden', visible !== 0);
            if (!visible) $('#fields-empty-term').text(term || '(filtro vacío)');
            if (currentFilter === 'required' || currentFilter === 'optional') {
                $('#section-' + currentFilter).addClass('show');
                $('[data-target="#section-' + currentFilter + '"]').removeClass('collapsed').attr('aria-expanded', 'true');
            }
        }

        function resetFieldsFilter() {
            $('#fields-search').val('');
            $('[data-filter]').removeClass('active');
            $('[data-filter="all"]').addClass('active');
            currentFilter = 'all';
            applyFieldFilters();
        }
        $('#fields-search').on('input', applyFieldFilters);
        $('#fields-search-clear').on('click', function() {
            $('#fields-search').val('').trigger('input').focus();
        });
        $('[data-filter]').on('click', function() {
            $('[data-filter]').removeClass('active');
            $(this).addClass('active');
            currentFilter = $(this).data('filter');
            applyFieldFilters();
        });

        // ── Vigencia auto-calc ──
        function calcEnd(ini) {
            if (!ini) return '';
            const p = ini.split('-');
            if (p.length !== 3) return '';
            const d = new Date(+p[0], +p[1] - 1, +p[2]);
            d.setFullYear(d.getFullYear() + 1);
            d.setDate(d.getDate() - 1);
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        function syncVigencia() {
            $('#masivo-fecha-ven').val(calcEnd($('#masivo-fecha-ini').val()));
        }
        $('#masivo-fecha-ini').on('change input', syncVigencia);
        (function() {
            const $i = $('#masivo-fecha-ini');
            if ($i.length && !$i.val()) {
                const t = new Date();
                $i.val(t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0') + '-' + String(t.getDate()).padStart(2, '0'));
            }
            syncVigencia();
        })();

        // ── CSV picker ──
        $('#csv-file-input').on('change', function() {
            const f = this.files[0];
            if (f) {
                $('#csv-file-picker-name').text(f.name).addClass('is-set');
                $('#file-name').text(f.name);
                $('#file-size').text((f.size / 1048576).toFixed(2) + ' MB');
                $('#file-info').removeAttr('hidden');
            } else {
                $('#csv-file-picker-name').text('Ningún archivo seleccionado').removeClass('is-set');
                $('#file-info').attr('hidden', true);
            }
        });

        // ── Plans loader ──
        const $plan = $('#plan-selector'),
            $info = $('#selected-info'),
            $strip = $('#plan-info-strip');

        function showInfo(html) {
            $info.html(html);
        }

        function cargarPlanes(corpId) {
            if (!corpId) {
                $plan.prop('disabled', true).html('<option value="">--- Primero seleccione un corporativo ---</option>').val('');
                $strip.attr('hidden', true);
                showInfo('<div class="alert alert-secondary mb-0 text-center py-3"><i class="fas fa-hand-pointer mr-2"></i><strong>Seleccione un corporativo arriba</strong> para ver los planes disponibles.</div>');
                return;
            }
            $plan.prop('disabled', true).html('<option value="">Cargando planes...</option>').val('');
            showInfo('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i><br><span class="text-muted">Cargando planes disponibles...</span></div>');
            $.getJSON(getPlanesUrl, {
                    id: corpId
                })
                .done(function(r) {
                    if (r.success && Array.isArray(r.data) && r.data.length) {
                        const opts = ['<option value="">--- Seleccione un Plan ---</option>'];
                        r.data.forEach(function(p) {
                            opts.push('<option value="' + p.id + '" data-clinica="' + p.clinica_nombre + '" data-clinica-id="' + p.clinica_id + '" data-precio="' + p.precio + '">' + p.nombre + '</option>');
                        });
                        $plan.html(opts.join('')).prop('disabled', false);
                        $strip.attr('hidden', true);
                        showInfo('<div class="alert alert-secondary mb-0 text-center py-3"><i class="fas fa-arrow-up mr-2"></i><strong>Seleccione un plan del menú desplegable</strong> para ver su información y precio.</div>');
                    } else if (r.success) {
                        $plan.prop('disabled', true).html('<option value="">--- No hay planes disponibles ---</option>').val('');
                        $strip.attr('hidden', true);
                        showInfo('<div class="alert alert-warning mb-0 text-center py-3"><i class="fas fa-exclamation-triangle mr-2"></i><strong>No se encontraron planes</strong><br>Este corporativo no tiene planes asociados. Contacte al administrador.</div>');
                    } else {
                        $plan.prop('disabled', true).html('<option value="">--- Error al cargar planes ---</option>').val('');
                        $strip.attr('hidden', true);
                        showInfo('<div class="alert alert-danger mb-0"><i class="fas fa-bug mr-2"></i>Error: ' + (r.error || 'No se pudieron cargar los datos') + '</div>');
                    }
                })
                .fail(function(x, s, err) {
                    $plan.prop('disabled', true).html('<option value="">--- Error de conexión ---</option>').val('');
                    $strip.attr('hidden', true);
                    showInfo('<div class="alert alert-danger mb-0"><i class="fas fa-plug mr-2"></i>Error de conexión. Verifique que el servidor esté funcionando correctamente.<br><small>Detalles: ' + err + '</small></div>');
                });
        }

        // ── Select wiring ──
        $('#corporativo-selector').on('change', function() {
            const v = $(this).val();
            $('#masivoafiliadosform-corporativo_id').val(v);
            $('#masivoafiliadosform-plan_id').val('');
            cargarPlanes(v);
        });
        $plan.on('change', function() {
            const id = $(this).val();
            const $opt = $(this).find('option:selected');
            $('#masivoafiliadosform-plan_id').val(id);
            if (!id) {
                $strip.attr('hidden', true);
                showInfo('<div class="alert alert-secondary mb-0 text-center py-3"><i class="fas fa-arrow-up mr-2"></i><strong>Seleccione un plan del menú desplegable</strong> para ver su información y precio.</div>');
                return;
            }
            const clinica = $opt.data('clinica') || '—';
            const precioRaw = $opt.data('precio');
            const precio = (precioRaw !== undefined && precioRaw !== '') ? parseFloat(precioRaw).toFixed(2) : '—';
            $('#clinica-nombre').text(clinica);
            $('#plan-precio').text('$' + precio);
            $strip.removeAttr('hidden');
            let asesor = $('#asesor-selector option:selected').text();
            if (asesor === '--- Sin Asesor (Opcional) ---') asesor = 'Ninguno';
            showInfo('<div class="alert alert-success mb-0 text-center py-3"><i class="fas fa-check-circle mr-2"></i><strong>Configuración confirmada:</strong> <span class="text-muted">Corporativo</span> <strong>' + $('#corporativo-selector option:selected').text() + '</strong> · <span class="text-muted">Plan</span> <strong>' + $opt.text() + '</strong> · <span class="text-muted">Clínica</span> <strong>' + clinica + '</strong> · <span class="text-muted">Asesor</span> <strong>' + asesor + '</strong></div>');
        });
        $('#asesor-selector').on('change', function() {
            $('#masivoafiliadosform-asesor_id').val($(this).val());
            if ($plan.val()) $plan.trigger('change');
        });

        // ── Preselection + scroll helper ──
        function smoothScrollTo(sel) {
            const $el = $(sel);
            if ($el.length) $('html,body').animate({
                scrollTop: $el.offset().top - 80
            }, 500);
        }
        (function initPreselection() {
            const preCorp = '<?= $preselectedCorporativoId ?? '' ?>';
            const $corp = $('#corporativo-selector');
            const current = $corp.val();
            if (current) {
                $('#masivoafiliadosform-corporativo_id').val(current);
                cargarPlanes(current);
                if (preCorp && String(preCorp) === String(current)) {
                    setTimeout(function() {
                        smoothScrollTo('#step1-select');
                        $corp.closest('.form-group').addClass('required-missing');
                        setTimeout(function() {
                            $corp.closest('.form-group').removeClass('required-missing');
                        }, 1600);
                    }, 250);
                }
            }
            const prePlan = '<?= $model->plan_id ?? '' ?>';
            if (prePlan) $plan.val(prePlan).trigger('change');
            const preAsesor = '<?= $model->asesor_id ?? '' ?>';
            if (preAsesor) {
                $('#asesor-selector').val(preAsesor);
                $('#masivoafiliadosform-asesor_id').val(preAsesor);
            }
        })();

        // ── Stepper navigation ──
        $('.compact-step-link').on('click', function(e) {
            e.preventDefault();
            const t = STEP_TARGETS[$(this).data('step')];
            if (t) smoothScrollTo(t);
        });

        // ── Form submit guard ──
        $('#carga-masiva-form').on('submit', function(e) {
            const checks = [{
                    ok: $('#masivoafiliadosform-corporativo_id').val(),
                    msg: 'ERROR: Debe seleccionar un corporativo en el Paso 1.',
                    target: '#step1-select'
                },
                {
                    ok: $('#masivoafiliadosform-plan_id').val(),
                    msg: 'ERROR: Debe seleccionar un plan en el Paso 1.',
                    target: '#step1-select'
                },
                {
                    ok: $('#masivo-fecha-ini').val() && $('#masivo-fecha-ven').val(),
                    msg: 'ERROR: Debe indicar la vigencia del contrato en el Paso 1.',
                    target: '#step1-select'
                },
                {
                    ok: $('#csv-file-input').val(),
                    msg: 'ERROR: Debe seleccionar un archivo CSV en el Paso 4.',
                    target: '#step4-form'
                },
            ];
            for (const c of checks) {
                if (!c.ok) {
                    e.preventDefault();
                    $('#validation-message').text(c.msg);
                    $('#validation-alert').removeAttr('hidden');
                    smoothScrollTo(c.target);
                    $(c.target).addClass('required-missing');
                    setTimeout(function() {
                        $(c.target).removeClass('required-missing');
                    }, 2000);
                    return false;
                }
            }
            $('#progress-overlay').addClass('is-visible');
            $('#upload-progress-bar').css('width', '0%').removeClass('bg-danger').addClass('bg-success');
            $('#upload-progress-text').text('0%');
            $('#progress-title').text('Procesando Carga Masiva');
            $('#progress-status').text('Preparando archivo...');
            let p = 0;
            const t = setInterval(function() {
                p += 3;
                if (p > 90) {
                    clearInterval(t);
                    $('#progress-status').text('Procesando archivo en el servidor...');
                    return;
                }
                $('#upload-progress-bar').css('width', p + '%');
                $('#upload-progress-text').text(p + '%');
                $('#progress-status').text(p < 30 ? 'Validando archivo...' : p < 60 ? 'Subiendo archivo al servidor...' : 'Procesando datos...');
            }, 300);
            return true;
        });

        // ── Flash messages ──
        <?php if (Yii::$app->session->hasFlash('success') || Yii::$app->session->hasFlash('warning') || Yii::$app->session->hasFlash('error')): ?>
            $('#progress-overlay').addClass('is-visible');
            $('.spinner-border').remove();
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                $('#progress-overlay .card-body').prepend('<i class="fas fa-check-circle text-success" style="font-size:60px;"></i>');
                $('#upload-progress-bar').css('width', '100%').addClass('bg-success');
                $('#upload-progress-text').text('100%');
                $('#progress-title').text('✅ ¡Proceso Completado!');
                $('#progress-status').text('<?= addslashes(Yii::$app->session->getFlash('success')) ?>');
            <?php else: ?>
                $('#progress-overlay .card-body').prepend('<i class="fas fa-exclamation-circle text-danger" style="font-size:60px;"></i>');
                $('#upload-progress-bar').css('width', '100%').removeClass('bg-success').addClass('bg-danger');
                $('#upload-progress-text').text('100%');
                $('#progress-title').text('⚠️ Proceso Completado con Errores');
                $('#progress-status').text('<?= addslashes(Yii::$app->session->getFlash('warning') ?: Yii::$app->session->getFlash('error')) ?>');
            <?php endif; ?>
            setTimeout(function() {
                $('#progress-overlay').fadeOut(500, function() {
                    $(this).removeClass('is-visible').show();
                });
            }, 4000);
        <?php endif; ?>
    });
</script>