<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\components\ContractHelper;
use app\models\ContratoCorporativo;

/** @var app\models\ContratoCorporativo $contract */
/** @var \app\models\ContratoCorporativo[] $siblings */

$this->title = 'Anular Contrato ' . $contract->nrocontrato;
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = [
    'label' => $contract->corporativo ? $contract->corporativo->nombre : 'N/A',
    'url'   => ['view', 'id' => $contract->corporativo_id],
];
$this->params['breadcrumbs'][] = 'Anular Contrato';

// ── Aggregate data across the entire contract group ──────────────────
$siblings = $siblings ?? [$contract];

$totalAffiliates = 0;
$totalAmount     = 0.0;
$planNames       = [];
$clinicaNames    = [];

foreach ($siblings as $row) {
    $totalAffiliates += (int)$row->cantidad_afiliados;
    $totalAmount     += (float)$row->monto_total;
    if ($row->plan) {
        $planNames[] = $row->plan->nombre;
    }
    if ($row->clinica && !in_array($row->clinica->nombre, $clinicaNames, true)) {
        $clinicaNames[] = $row->clinica->nombre;
    }
}

$vigenciaIni = $contract->fecha_ini;
$vigenciaVen = $contract->fecha_ven;
?>

<style>
    /* ══════════════════════════════════════════════════════════════ */
    /* ANULAR CONTRATO — mirrors the style of view.php                */
    /* ══════════════════════════════════════════════════════════════ */
    .corp-page {
        padding: 24px;
        max-width: 1100px;
        margin: 0 auto;
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
        color: #323130;
        font-size: 17px;
        line-height: 1.6;
    }

    /* ─── Page header ────────────────────────────────────────── */
    .corp-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        padding: 28px 30px;
        background: #fff;
        border: 1px solid #e8e6e3;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .corp-header__left {
        display: flex;
        align-items: center;
        gap: 22px;
        min-width: 0;
    }

    .corp-header__icon {
        width: 84px;
        height: 84px;
        border-radius: 18px;
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 38px;
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.3);
        flex-shrink: 0;
    }

    .corp-header__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1.05rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.6px;
        color: #8a8886;
        margin-bottom: 8px;
    }

    .corp-header__title {
        font-size: 2.4rem;
        font-weight: 800;
        color: #991b1b;
        margin: 0 0 12px 0;
        line-height: 1.05;
        letter-spacing: -0.5px;
        word-break: break-word;
    }

    .corp-header__subtitle {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: 1.2rem;
        color: #605e5c;
        font-weight: 500;
        flex-wrap: wrap;
    }

    .corp-header__badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f3f2f1;
        color: #605e5c;
        font-size: 1.1rem;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 12px;
    }

    .corp-header__badge i {
        color: #8a8886;
        font-size: 1.05rem;
    }

    .corp-header__actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
        flex-wrap: wrap;
    }

    /* ─── Buttons ─────────────────────────────────────────────── */
    .btn-corp {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 24px;
        border-radius: 10px;
        font-size: 1.15rem;
        font-weight: 700;
        text-decoration: none;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
        line-height: 1.2;
    }

    .btn-corp i {
        font-size: 1.15rem;
    }

    .btn-corp--danger {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        color: #fff;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
    }

    .btn-corp--danger:hover {
        background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 38, 38, 0.5);
        text-decoration: none;
    }

    .btn-corp--ghost {
        background: #fff;
        color: #4338ca;
        border-color: #c7d2fe;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.10);
    }

    .btn-corp--ghost:hover {
        background: #eef2ff;
        color: #312e81;
        border-color: #818cf8;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.25);
        text-decoration: none;
    }

    /* ─── Panels ──────────────────────────────────────────────── */
    .corp-panel {
        background: #fff;
        border: 1px solid #e8e6e3;
        border-radius: 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
        overflow: hidden;
        transition: box-shadow 0.15s ease;
    }

    .corp-panel__title {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: 1.4rem;
        font-weight: 700;
        color: #fff;
        margin: 0;
        padding: 18px 28px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
    }

    .corp-panel__title i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        font-size: 1.15rem;
        color: #fff;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        flex-shrink: 0;
    }

    /* Red — used for the danger/summary panels */
    .corp-panel--red .corp-panel__title {
        background: linear-gradient(135deg, #991b1b 0%, #dc2626 50%, #ef4444 100%);
    }

    /* Amber — used for the warning alert panel */
    .corp-panel--amber .corp-panel__title {
        background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #f59e0b 100%);
    }

    /* Blue — used for the "reason" form panel */
    .corp-panel--blue .corp-panel__title {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
    }

    .corp-panel__body {
        padding: 28px 30px;
    }

    /* Tinted panel bodies matching the header color family */
    .corp-panel--red .corp-panel__body {
        background: linear-gradient(180deg, #fff5f5 0%, #fef2f2 100%);
    }

    .corp-panel--amber .corp-panel__body {
        background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 100%);
    }

    /* ─── Info grid ──────────────────────────────────────────── */
    .corp-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px 44px;
        align-items: start;
    }

    @media (max-width: 768px) {
        .corp-info-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
    }

    .corp-info-row {
        margin: 0;
        font-size: 1.15rem;
        color: #323130;
        line-height: 1.55;
    }

    .corp-info-row strong {
        display: inline-block;
        min-width: 200px;
        color: #605e5c;
        font-weight: 600;
        margin-right: 10px;
    }

    .corp-info-row--full {
        grid-column: 1 / -1;
    }

    /* ─── Plan chips (matching the table chips) ──────────────── */
    .plan-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 4px;
    }

    .plan-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        color: #4338ca;
        font-size: 1rem;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 10px;
        border: 1px solid #c7d2fe;
        white-space: nowrap;
    }

    /* ─── Warning bullets ────────────────────────────────────── */
    .warn-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .warn-list__item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        font-size: 1.15rem;
        color: #323130;
        border-bottom: 1px solid rgba(180, 83, 9, 0.15);
    }

    .warn-list__item:last-child {
        border-bottom: none;
    }

    .warn-list__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #fef3c7;
        color: #b45309;
        font-size: 0.95rem;
        flex-shrink: 0;
        margin-top: 2px;
    }

    /* ─── Form field ─────────────────────────────────────────── */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 1.15rem;
        font-weight: 700;
        color: #1a237e;
        margin-bottom: 8px;
    }

    .form-label .required {
        color: #dc2626;
        margin-left: 4px;
    }

    .form-control {
        display: block;
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e8e6e3;
        border-radius: 10px;
        font-size: 1.1rem;
        font-family: inherit;
        color: #323130;
        background: #fff;
        transition: all 0.15s ease;
        resize: vertical;
        min-height: 120px;
        line-height: 1.55;
    }

    .form-control:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12);
    }

    .form-control::placeholder {
        color: #a19f9d;
        font-style: italic;
    }

    .form-hint {
        margin-top: 8px;
        font-size: 1rem;
        color: #8a8886;
    }

    /* ─── Action bar at the bottom of the form ───────────────── */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 8px;
        flex-wrap: wrap;
    }

    /* ─── Responsive ─────────────────────────────────────────── */
    @media (max-width: 992px) {
        .corp-header__title {
            font-size: 2rem;
        }
    }

    @media (max-width: 576px) {
        .corp-page {
            padding: 14px;
        }

        .corp-header {
            padding: 20px;
        }

        .corp-header__icon {
            width: 64px;
            height: 64px;
            font-size: 28px;
        }

        .corp-header__title {
            font-size: 1.6rem;
        }

        .corp-header__subtitle {
            font-size: 1rem;
        }

        .corp-panel__title {
            font-size: 1.15rem;
            padding: 14px 20px;
            gap: 10px;
        }

        .corp-panel__title i {
            width: 32px;
            height: 32px;
            font-size: 1rem;
        }

        .corp-panel__body {
            padding: 20px 22px;
        }

        .corp-info-row strong {
            min-width: 140px;
        }

        .corp-info-row {
            font-size: 1.05rem;
        }

        .btn-corp {
            padding: 12px 20px;
            font-size: 1rem;
        }

        .warn-list__item {
            font-size: 1.05rem;
        }

        .form-label {
            font-size: 1.05rem;
        }

        .form-control {
            font-size: 1rem;
            padding: 12px 14px;
        }
    }
</style>

<div class="corp-page">

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- PAGE HEADER                                                  -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="corp-header">
        <div class="corp-header__left">
            <div class="corp-header__icon">
                <i class="fas fa-ban" aria-hidden="true"></i>
            </div>
            <div>
                <div class="corp-header__eyebrow">Anulación de Contrato</div>
                <h1 class="corp-header__title">Anular Contrato Colectivo</h1>
                <div class="corp-header__subtitle">
                    <?php if ($contract->corporativo): ?>
                        <span class="corp-header__badge">
                            <i class="fas fa-building" aria-hidden="true"></i>
                            <?= Html::encode($contract->corporativo->nombre) ?>
                        </span>
                    <?php endif; ?>
                    <span class="corp-header__badge">
                        <i class="fas fa-file-contract" aria-hidden="true"></i>
                        <?= Html::encode($contract->nrocontrato) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="corp-header__actions">
            <?= Html::a(
                '<i class="fas fa-arrow-left" aria-hidden="true"></i> Volver al Corporativo',
                ['view', 'id' => $contract->corporativo_id],
                ['class' => 'btn-corp btn-corp--ghost']
            ) ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- WARNING — consequences of the action                        -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="corp-panel corp-panel--amber">
        <h3 class="corp-panel__title">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            Atención — Esta acción es irreversible
        </h3>
        <div class="corp-panel__body">
            <ul class="warn-list">
                <li class="warn-list__item">
                    <span class="warn-list__icon"><i class="fas fa-file-contract" aria-hidden="true"></i></span>
                    <div>
                        Marcará el contrato colectivo
                        <strong><?= Html::encode($contract->nrocontrato) ?></strong>
                        como <strong>Anulado</strong> en todos sus planes.
                    </div>
                </li>
                <li class="warn-list__item">
                    <span class="warn-list__icon"><i class="fas fa-user-slash" aria-hidden="true"></i></span>
                    <div>
                        Anulará <strong>los contratos individuales</strong> de los
                        <strong><?= (int)$totalAffiliates ?></strong> afiliados cubiertos.
                    </div>
                </li>
                <li class="warn-list__item">
                    <span class="warn-list__icon"><i class="fas fa-money-bill-wave" aria-hidden="true"></i></span>
                    <div>
                        Cancelará las <strong>cuotas pendientes y vencidas</strong>
                        de esos afiliados.
                    </div>
                </li>
                <li class="warn-list__item">
                    <span class="warn-list__icon"><i class="fas fa-lock" aria-hidden="true"></i></span>
                    <div>
                        Esta operación <strong>no se puede deshacer</strong>. Los registros
                        permanecerán en el sistema marcados como anulados.
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- CONTRACT SUMMARY                                             -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="corp-panel corp-panel--red">
        <h3 class="corp-panel__title">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            Resumen del Contrato a Anular
        </h3>
        <div class="corp-panel__body">
            <div class="corp-info-grid">
                <p class="corp-info-row">
                    <strong>N° Contrato:</strong>
                    <span style="font-family: 'Consolas', monospace; font-weight: 700; color: #1a237e;">
                        <?= Html::encode($contract->nrocontrato) ?>
                    </span>
                </p>
                <p class="corp-info-row">
                    <strong>Corporativo:</strong>
                    <?= Html::encode($contract->corporativo ? $contract->corporativo->nombre : 'N/A') ?>
                </p>
                <p class="corp-info-row">
                    <strong>Vigencia:</strong>
                    <?= $vigenciaIni ? date('d/m/Y', strtotime($vigenciaIni)) : 'N/A' ?>
                    <span style="color: #c8c6c4; padding: 0 6px;">→</span>
                    <?= $vigenciaVen ? date('d/m/Y', strtotime($vigenciaVen)) : 'N/A' ?>
                </p>
                <p class="corp-info-row">
                    <strong>Estatus actual:</strong>
                    <?= ContractHelper::getCorporateContractStatusBadge($contract) ?>
                </p>
                <p class="corp-info-row">
                    <strong>Afiliados cubiertos:</strong>
                    <span style="font-weight: 700; color: #107c10;">
                        <?= (int)$totalAffiliates ?>
                    </span>
                </p>
                <p class="corp-info-row">
                    <strong>Monto total mensual:</strong>
                    <span style="font-weight: 700; color: #107c10;">
                        $<?= number_format($totalAmount, 2, ',', '.') ?>
                    </span>
                </p>

                <?php if (!empty($planNames)): ?>
                    <p class="corp-info-row corp-info-row--full">
                        <strong>Planes afectados (<?= count($planNames) ?>):</strong>
                        <span class="plan-chips">
                            <?php foreach ($planNames as $pName): ?>
                                <span class="plan-chip"><?= Html::encode($pName) ?></span>
                            <?php endforeach; ?>
                        </span>
                    </p>
                <?php endif; ?>

                <?php if (!empty($clinicaNames)): ?>
                    <p class="corp-info-row corp-info-row--full">
                        <strong>Clínicas asociadas:</strong>
                        <?= Html::encode(implode(', ', $clinicaNames)) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- REASON FORM                                                  -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="corp-panel corp-panel--blue">
        <h3 class="corp-panel__title">
            <i class="fas fa-pen" aria-hidden="true"></i>
            Motivo de la Anulación
        </h3>
        <div class="corp-panel__body">
            <?php $form = ActiveForm::begin([
                'action'  => ['anular-contrato-corporativo', 'id' => $contract->id],
                'method'  => 'post',
                'options' => ['id' => 'annul-contract-form'],
            ]); ?>

            <div class="form-group">
                <label class="form-label" for="motivo">
                    <i class="fas fa-quote-left" aria-hidden="true" style="color: #8a8886; margin-right: 6px;"></i>
                    Motivo de la anulación
                    <span class="required">*</span>
                </label>
                <textarea
                    name="motivo"
                    id="motivo"
                    class="form-control"
                    rows="4"
                    required
                    maxlength="1000"
                    placeholder="Ej.: El corporativo solicitó la cancelación del servicio a partir del 15/03/2026 debido a reestructuración interna."></textarea>
                <p class="form-hint">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    Este motivo quedará registrado en la auditoría del contrato y en los contratos individuales anulados.
                </p>
            </div>

            <div class="form-actions">
                <?= Html::a(
                    '<i class="fas fa-arrow-left" aria-hidden="true"></i> Cancelar',
                    ['view', 'id' => $contract->corporativo_id],
                    ['class' => 'btn-corp btn-corp--ghost']
                ) ?>
                <button type="submit" class="btn-corp btn-corp--danger">
                    <i class="fas fa-ban" aria-hidden="true"></i>
                    Confirmar Anulación
                </button>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>

<?php
// Small UX nicety: prevent double-submit
$this->registerJs(<<<JS
(function() {
    var form = document.getElementById('annul-contract-form');
    if (!form) return;
    var submitted = false;
    form.addEventListener('submit', function(e) {
        if (submitted) {
            e.preventDefault();
            return;
        }
        var motivo = document.getElementById('motivo');
        if (motivo && motivo.value.trim() === '') {
            e.preventDefault();
            motivo.focus();
            return;
        }
        submitted = true;
        var btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando…';
        }
    });
})();
JS);
?>