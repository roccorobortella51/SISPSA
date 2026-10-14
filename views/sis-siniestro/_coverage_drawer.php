<?php

use yii\helpers\Html;

/**
 * @var int    $effectiveStart
 * @var int    $monthsCovered
 * @var int    $chainCount
 * @var array  $chainContractIds
 * @var array|null $brokenBefore
 * @var \app\models\Contratos $contrato
 * @var \app\models\UserDatos $afiliado
 */

$formattedStart = Yii::$app->formatter->asDate($effectiveStart, 'd/M/Y');

// Human-friendly duration
if ($monthsCovered >= 12) {
    $years  = (int)floor($monthsCovered / 12);
    $months = $monthsCovered % 12;
    $durationText = $years . ' año' . ($years !== 1 ? 's' : '')
        . ($months > 0 ? ' y ' . $months . ' mes' . ($months !== 1 ? 'es' : '') : '');
} elseif ($monthsCovered > 0) {
    $durationText = $monthsCovered . ' mes' . ($monthsCovered !== 1 ? 'es' : '');
} else {
    $durationText = 'menos de un mes';
}

// Resolve the chain contracts for display
$chainContracts = [];
if (!empty($chainContractIds)) {
    $chainContracts = \app\models\Contratos::find()
        ->where(['id' => $chainContractIds])
        ->orderBy(['fecha_ini' => SORT_ASC])
        ->all();
}
?>

<div class="coverage-drawer-body">

    <!-- ===== STATUS CARD ===== -->
    <div class="coverage-drawer-status">
        <div class="coverage-drawer-status-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="coverage-drawer-status-text">
            <div class="coverage-drawer-status-title">Cobertura Continua Verificada</div>
            <div class="coverage-drawer-status-subtitle">
                El afiliado mantiene cobertura médica sin interrupciones desde el inicio de su historial de contratos.
            </div>
        </div>
        <span class="coverage-drawer-status-pill">
            <i class="fas fa-check-circle"></i>
            VIGENTE
        </span>
    </div>

    <!-- ===== METRICS GRID ===== -->
    <div class="coverage-drawer-section-title">
        <i class="fas fa-chart-simple"></i>
        Resumen de Cobertura
    </div>

    <div class="coverage-drawer-metrics">
        <div class="coverage-metric">
            <div class="coverage-metric-label">Cubierto desde</div>
            <div class="coverage-metric-value"><?= $formattedStart ?></div>
            <div class="coverage-metric-hint">Fecha de inicio de la cadena</div>
        </div>

        <div class="coverage-metric">
            <div class="coverage-metric-label">Antigüedad</div>
            <div class="coverage-metric-value"><?= ucfirst($durationText) ?></div>
            <div class="coverage-metric-hint">Cobertura ininterrumpida</div>
        </div>

        <div class="coverage-metric">
            <div class="coverage-metric-label">Contratos</div>
            <div class="coverage-metric-value"><?= $chainCount ?></div>
            <div class="coverage-metric-hint">En la cadena continua</div>
        </div>
    </div>

    <!-- ===== CONTRACT CHAIN TIMELINE ===== -->
    <?php if (!empty($chainContracts)): ?>
        <div class="coverage-drawer-section-title">
            <i class="fas fa-timeline"></i>
            Cadena de Contratos
        </div>

        <div class="coverage-drawer-timeline">
            <?php foreach ($chainContracts as $index => $c): ?>
                <?php
                $isCurrent = ((int)$c->id === (int)$contrato->id);
                $statusClass = strtolower($c->estatus);
                $statusLabel = ucfirst($c->estatus);
                ?>
                <div class="coverage-timeline-item <?= $isCurrent ? 'is-current' : '' ?>">
                    <div class="coverage-timeline-marker">
                        <i class="fas <?= $isCurrent ? 'fa-circle-dot' : 'fa-circle-check' ?>"></i>
                    </div>
                    <div class="coverage-timeline-content">
                        <div class="coverage-timeline-header">
                            <span class="coverage-timeline-title">
                                Contrato #<?= (int)$c->id ?>
                                <?php if ($isCurrent): ?>
                                    <span class="coverage-timeline-badge-current">Actual</span>
                                <?php endif; ?>
                            </span>
                            <span class="coverage-timeline-status <?= Html::encode($statusClass) ?>">
                                <?= Html::encode($statusLabel) ?>
                            </span>
                        </div>
                        <div class="coverage-timeline-dates">
                            <i class="far fa-calendar"></i>
                            <?= Yii::$app->formatter->asDate($c->fecha_ini, 'd/M/Y') ?>
                            &nbsp;→&nbsp;
                            <?= Yii::$app->formatter->asDate($c->fecha_ven, 'd/M/Y') ?>
                        </div>
                        <?php if ($c->plan): ?>
                            <div class="coverage-timeline-plan">
                                <i class="fas fa-file-contract"></i>
                                <?= Html::encode($c->plan->nombre) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($index < count($chainContracts) - 1): ?>
                    <?php
                    $next = $chainContracts[$index + 1];
                    $gapDays = null;
                    try {
                        $currVen = new \DateTime($c->fecha_ven);
                        $nextIni = new \DateTime($next->fecha_ini);
                        $diff = $currVen->diff($nextIni);
                        $gapDays = $diff->invert ? -$diff->days : $diff->days;
                    } catch (\Exception $e) {
                    }
                    ?>
                    <div class="coverage-timeline-gap">
                        <i class="fas fa-arrow-down"></i>
                        <span>
                            <?php if ($gapDays === 0): ?>
                                Renovación mismo día
                            <?php elseif ($gapDays > 0): ?>
                                <?= $gapDays ?> día<?= $gapDays !== 1 ? 's' : '' ?> de separación
                            <?php else: ?>
                                Contratos superpuestos
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- ===== INTERRUPTION CALLOUT ===== -->
    <?php if (!empty($brokenBefore)): ?>
        <div class="coverage-drawer-section-title">
            <i class="fas fa-triangle-exclamation"></i>
            Interrupción de Cobertura
        </div>

        <div class="coverage-drawer-callout">
            <div class="coverage-drawer-callout-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="coverage-drawer-callout-content">
                <div class="coverage-drawer-callout-title">
                    Interrupción de cobertura detectada
                </div>
                <div class="coverage-drawer-callout-body">
                    El afiliado tuvo una interrupción de cobertura de
                    <strong><?= (int)$brokenBefore['gap_days'] ?> días</strong>
                    entre el
                    <strong><?= Yii::$app->formatter->asDate($brokenBefore['previous_fecha_ven'], 'd/M/Y') ?></strong>
                    y el inicio del contrato actual.
                </div>
                <div class="coverage-drawer-callout-body">
                    Como la interrupción supera el máximo permitido
                    (<strong><?= \app\components\CoverageHelper::MAX_GAP_DAYS ?> días</strong>),
                    los períodos de espera vuelven a contar desde cero.
                </div>
                <span class="coverage-drawer-callout-pill">
                    <i class="fas fa-file-contract"></i>
                    Contrato previo #<?= (int)$brokenBefore['previous_contract_id'] ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- ===== FOOTER INFO ===== -->
    <div class="coverage-drawer-footer">
        <i class="fas fa-info-circle"></i>
        <span>
            La antigüedad de la cobertura se cuenta desde la fecha indicada.
            Los períodos de espera de los servicios médicos se evalúan en función de esta antigüedad.
        </span>
    </div>

</div>