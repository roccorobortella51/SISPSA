<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var array $cuotas */

$this->title = 'Update Pagos: ' . $model->id;

// --- PREPARE DATA FOR DISPLAY ---
$nombres = $model->userDatos->nombres ?? 'N/A';
$apellidos = $model->userDatos->apellidos ?? 'N/A';
$cedula = $model->userDatos->cedula ?? 'N/A';
$tipoCedula = $model->userDatos->tipo_cedula ?? '';
$nombreCompleto = $nombres . ' ' . $apellidos;

// --- CREATE STYLIZED HTML FOR DISPLAY ---
$nombreCompletoStyled = Html::tag('span', Html::encode($nombreCompleto), [
    'style' => 'color: yellow; font-weight: bold;'
]);

$cedulaStyled = Html::tag('span', Html::encode(' (C.I.: ' . $tipoCedula . $cedula . ')'), [
    'style' => 'color: white; font-weight: bold;'
]);

$richDisplayTitle = 'Crear Pago para: ' . $nombreCompletoStyled . $cedulaStyled;

?>
<div class="pagos-update">
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-undo-alt"></i> Volver', Url::to(['contratos/index', 'user_id' => $model->user_id]), ['class' => 'btn btn-info btn-lg']) ?>
        </div>
    </div>

    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header bg-primary text-white text-center py-3">
                <h3 class="card-title mb-0 display-3" style="font-size: 1.8em; line-height: 1.2;">
                    <i class="fas fa-edit me-2"></i> <?= $richDisplayTitle ?>
                </h3>
            </div>
            <div class="ms-panel-body">

                <!-- ADD CONTRACT CONTEXT SECTION HERE -->
                <?php
                $user_id = $model->user_id ?? null;
                if ($user_id):
                ?>
                    <?php
                    // Get contract info for context
                    $contratoActivo = \app\models\Contratos::getContratoActivo($user_id);
                    $contratosValidos = \app\models\Contratos::getContratosValidos($user_id);
                    ?>

                    <?php if (!empty($contratosValidos)): ?>
                        <div class="contract-info-card mb-5">
                            <div class="card border-primary" style="border-width: 2px;">
                                <div class="card-header bg-primary text-white py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="mb-0" style="font-size: 1.5rem; font-weight: 600;">
                                            <i class="fas fa-file-contract me-3"></i>INFORMACIÓN DEL CONTRATO
                                        </h4>
                                        <span class="badge bg-light text-primary" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                            <?= count($contratosValidos) ?> CONTRATO<?= count($contratosValidos) > 1 ? 'S' : '' ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="card-body p-4">
                                    <?php if ($contratoActivo): ?>
                                        <!-- ACTIVE CONTRACT DETAILS -->
                                        <div class="active-contract-details">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="contract-summary mb-4">
                                                        <div class="d-flex align-items-center mb-3">
                                                            <span class="badge bg-success me-3" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                                <i class="fas fa-circle me-2"></i>ACTIVO
                                                            </span>
                                                            <h3 class="mb-0 text-primary" style="font-size: 1.75rem; font-weight: 700;">
                                                                CONTRATO #<?= $contratoActivo->nrocontrato ?: $contratoActivo->id ?>
                                                            </h3>
                                                        </div>

                                                        <div class="contract-period mb-4">
                                                            <div class="mb-2" style="font-size: 1.1rem; color: #6c757d; font-weight: 500;">
                                                                <i class="far fa-calendar-alt me-2"></i>PERIODO DEL CONTRATO
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge bg-light text-dark border me-3" style="font-size: 1.25rem; padding: 0.75rem 1.25rem;">
                                                                    <?= Yii::$app->formatter->asDate($contratoActivo->fecha_ini, 'php:d M Y') ?>
                                                                </span>
                                                                <i class="fas fa-arrow-right text-muted mx-3" style="font-size: 1.5rem;"></i>
                                                                <span class="badge bg-light text-dark border" style="font-size: 1.25rem; padding: 0.75rem 1.25rem;">
                                                                    <?= $contratoActivo->fecha_ven ? Yii::$app->formatter->asDate($contratoActivo->fecha_ven, 'php:d M Y') : 'PRESENTE' ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="contract-plan mb-4">
                                                        <div class="mb-2" style="font-size: 1.1rem; color: #6c757d; font-weight: 500;">
                                                            <i class="fas fa-clipboard-list me-2"></i>PLAN CONTRATADO
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <div class="plan-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                                <i class="fas fa-shield-alt" style="font-size: 1.25rem;"></i>
                                                            </div>
                                                            <span class="font-weight-bold" style="font-size: 1.5rem; color: #333;">
                                                                <?= $contratoActivo->plan ? $contratoActivo->plan->nombre : 'N/A' ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="contract-clinica mb-4">
                                                        <div class="mb-2" style="font-size: 1.1rem; color: #6c757d; font-weight: 500;">
                                                            <i class="fas fa-hospital me-2"></i>CLÍNICA ASIGNADA
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <div class="clinica-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                                <i class="fas fa-hospital" style="font-size: 1.25rem;"></i>
                                                            </div>
                                                            <span class="font-weight-bold" style="font-size: 1.5rem; color: #333;">
                                                                <?= $contratoActivo->clinica ? $contratoActivo->clinica->nombre : 'N/A' ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="contract-monto">
                                                        <div class="mb-2" style="font-size: 1.1rem; color: #6c757d; font-weight: 500;">
                                                            <i class="fas fa-dollar-sign me-2"></i>MONTO MENSUAL
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <div class="monto-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                                <i class="fas fa-money-bill-wave" style="font-size: 1.25rem;"></i>
                                                            </div>
                                                            <span class="font-weight-bold" style="font-size: 2rem; color: #28a745;">
                                                                <?= Yii::$app->formatter->asCurrency($contratoActivo->monto ?: 0, 'USD') ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if (count($contratosValidos) > 1): ?>
                                                <hr class="my-4" style="border-width: 2px;">
                                                <div class="other-contracts">
                                                    <div class="mb-3" style="font-size: 1.1rem; color: #6c757d; font-weight: 500;">
                                                        <i class="fas fa-history me-2"></i>OTROS CONTRATOS DEL AFILIADO
                                                    </div>
                                                    <div class="row">
                                                        <?php
                                                        $otrosContratos = array_filter($contratosValidos, function ($c) use ($contratoActivo) {
                                                            return $c->id !== $contratoActivo->id;
                                                        });
                                                        ?>
                                                        <?php foreach (array_slice($otrosContratos, 0, 2) as $contrato): ?>
                                                            <div class="col-md-6">
                                                                <div class="other-contract-card p-3 mb-3 border rounded" style="border-width: 2px;">
                                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                                        <span class="text-muted" style="font-size: 1.1rem; font-weight: 500;">
                                                                            #<?= $contrato->nrocontrato ?: $contrato->id ?>
                                                                        </span>
                                                                        <span class="badge bg-light text-dark border" style="font-size: 1rem; padding: 0.5rem 0.75rem;">
                                                                            <?= strtoupper($contrato->estatus) ?>
                                                                        </span>
                                                                    </div>
                                                                    <div class="d-block" style="font-size: 1.1rem; font-weight: 500;">
                                                                        <?= Yii::$app->formatter->asDate($contrato->fecha_ini, 'php:M Y') ?>
                                                                        <?= $contrato->fecha_ven ? ' - ' . Yii::$app->formatter->asDate($contrato->fecha_ven, 'php:M Y') : '' ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>

                                                        <?php if (count($otrosContratos) > 2): ?>
                                                            <div class="col-md-12 mt-2">
                                                                <div class="text-muted" style="font-size: 1.1rem;">
                                                                    <i class="fas fa-ellipsis-h me-2"></i>
                                                                    Y <?= count($otrosContratos) - 2 ?> CONTRATO(S) MÁS
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- NO ACTIVE CONTRACTS - SHOW ALL VALID CONTRACTS -->
                                        <div class="no-active-contract">
                                            <div class="alert alert-warning mb-4" style="font-size: 1.1rem; padding: 1rem 1.5rem;">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-exclamation-triangle fa-2x me-4"></i>
                                                    <div>
                                                        <strong style="font-size: 1.3rem;">NO HAY CONTRATO ACTIVO ACTUALMENTE</strong>
                                                        <p class="mb-0 mt-1" style="font-size: 1.1rem;">El afiliado tiene los siguientes contratos (no activos):</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <?php foreach ($contratosValidos as $contrato): ?>
                                                    <div class="col-md-6 mb-4">
                                                        <div class="contract-card border rounded p-4" style="border-width: 2px;">
                                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                                <div>
                                                                    <h4 class="mb-2 text-primary" style="font-size: 1.5rem; font-weight: 700;">
                                                                        CONTRATO #<?= $contrato->nrocontrato ?: $contrato->id ?>
                                                                    </h4>
                                                                    <span class="badge bg-light text-dark border" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                                        <?= strtoupper($contrato->estatus) ?>
                                                                    </span>
                                                                </div>
                                                                <?php if ($contrato->plan): ?>
                                                                    <span class="text-muted" style="font-size: 1.1rem; font-weight: 500;"><?= $contrato->plan->nombre ?></span>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="contract-period mb-3">
                                                                <div class="mb-2" style="font-size: 1.1rem; color: #6c757d; font-weight: 500;">
                                                                    <i class="far fa-calendar-alt me-2"></i>PERIODO
                                                                </div>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="text-dark" style="font-size: 1.25rem; font-weight: 600;">
                                                                        <?= Yii::$app->formatter->asDate($contrato->fecha_ini, 'php:d M Y') ?>
                                                                    </span>
                                                                    <i class="fas fa-arrow-right text-muted mx-3" style="font-size: 1.5rem;"></i>
                                                                    <span class="text-dark" style="font-size: 1.25rem; font-weight: 600;">
                                                                        <?= $contrato->fecha_ven ? Yii::$app->formatter->asDate($contrato->fecha_ven, 'php:d M Y') : 'PRESENTE' ?>
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <?php if ($contrato->monto): ?>
                                                                <div class="contract-monto">
                                                                    <div class="mb-2" style="font-size: 1.1rem; color: #6c757d; font-weight: 500;">
                                                                        <i class="fas fa-dollar-sign me-2"></i>MONTO MENSUAL
                                                                    </div>
                                                                    <span class="font-weight-bold" style="font-size: 1.75rem; color: #28a745;">
                                                                        <?= Yii::$app->formatter->asCurrency($contrato->monto, 'USD') ?>
                                                                    </span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($user_id): ?>
                        <div class="alert alert-danger mb-4" style="font-size: 1.2rem; padding: 1.5rem;">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-times-circle fa-2x me-4"></i>
                                <div>
                                    <strong style="font-size: 1.4rem;">NO SE ENCONTRARON CONTRATOS VÁLIDOS</strong>
                                    <p class="mb-0 mt-2" style="font-size: 1.1rem;">El afiliado no tiene contratos activos o registrados.</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="<?= \yii\helpers\Url::to(['/contratos/create', 'user_id' => $user_id]) ?>" class="btn btn-lg btn-outline-light" style="font-size: 1.1rem;">
                                    <i class="fas fa-plus-circle me-2"></i>CREAR NUEVO CONTRATO
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <!-- END CONTRACT CONTEXT SECTION -->

                <?= $this->render('_form', [
                    'model' => $model,
                    'cuotas' => $cuotas, // THIS IS THE KEY LINE - pass cuotas to the form
                    'user_id' => $model->user_id,
                    'isEditable' => true,
                ]) ?>
            </div>
        </div>
    </div>
</div>
<style>
    .contract-info-card .card {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-radius: 12px;
    }

    .contract-info-card .card-header {
        border-radius: 12px 12px 0 0 !important;
        font-weight: 600;
    }

    .contract-info-card .badge {
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .contract-info-card .plan-icon,
    .contract-info-card .clinica-icon,
    .contract-info-card .monto-icon {
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
    }

    .contract-card {
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .contract-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .other-contract-card {
        background: #f8f9fa;
        transition: all 0.2s ease;
        border: 2px solid #dee2e6 !important;
    }

    .other-contract-card:hover {
        background: #e9ecef;
        border-color: #adb5bd !important;
    }

    /* Microsoft-style font sizing standards */
    h4 {
        font-size: 1.5rem !important;
    }

    h3 {
        font-size: 1.75rem !important;
    }

    .badge {
        font-size: 1rem !important;
    }

    .text-primary {
        color: #0056b3 !important;
    }

    .text-success {
        color: #107c10 !important;
    }

    .bg-primary {
        background-color: #0056b3 !important;
    }

    .bg-success {
        background-color: #107c10 !important;
    }

    /* Better spacing for readability */
    .mb-4 {
        margin-bottom: 1.5rem !important;
    }

    .p-4 {
        padding: 1.5rem !important;
    }

    .py-3 {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }
</style>