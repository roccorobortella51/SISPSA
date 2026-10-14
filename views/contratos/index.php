<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Cuotas;

// Register jQuery and Bootstrap 4 assets
\yii\web\JqueryAsset::register($this);
\yii\bootstrap4\BootstrapAsset::register($this);
\yii\bootstrap4\BootstrapPluginAsset::register($this);

$this->title = 'Contratos';
$dataProvider = isset($dataProvider) ? $dataProvider : (isset($contratosDataProvider) ? $contratosDataProvider : null);

?>

<?php $afiliado_datos = (isset($afiliado) && is_object($afiliado)) ? $afiliado->nombres . ' ' . $afiliado->apellidos . " " . $afiliado->tipo_cedula . ' ' . $afiliado->cedula : ''; ?>

<div class="view-main-container">
    <!-- Header with Page Title and User Info -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h2 class="mb-0 font-weight-bold">
                    <i class="fas fa-file-contract mr-2"></i><?= Html::encode($this->title) ?>
                </h2>
            </div>
            <div class="d-flex">
                <?php if (isset($searchModel) && $searchModel->estatus === 'Anulado'): ?>
                    <?= Html::a(
                        '<i class="fas fa-plus mr-2"></i> Nuevo Contrato',
                        ['create'],
                        [
                            'class' => 'btn btn-light btn-sm mr-2',
                            'data-toggle' => 'tooltip',
                            'title' => 'Crear un nuevo contrato para este afiliado'
                        ]
                    ) ?>
                <?php endif; ?>

                <?= Html::a(
                    '<i class="fas fa-undo-alt mr-2"></i> Volver',
                    Url::to(['user-datos/view', 'id' => (is_object($afiliado) ? $afiliado->id : '')]),
                    [
                        'class' => 'btn btn-light btn-sm',
                        'data-toggle' => 'tooltip',
                        'title' => 'Volver al perfil del afiliado'
                    ]
                ); ?>
            </div>
        </div>
        <?php if ($afiliado_datos && $dataProvider): ?>
            <div class="card-body bg-light py-3">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle p-3 mr-3" data-toggle="tooltip" title="Afiliado titular del contrato">
                                <i class="fas fa-user fa-lg"></i>
                            </div>
                            <div>
                                <label class="text-muted small d-block mb-1">Afiliado</label>
                                <div class="font-weight-bold text-dark h5 mb-0"
                                    data-toggle="tooltip"
                                    title="<strong>Afiliado:</strong> <?= Html::encode($afiliado_datos) ?>"
                                    style="cursor: help;">
                                    <?= $afiliado_datos ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="text-center">
                            <div class="text-muted small mb-1">Total Contratos</div>
                            <div class="font-weight-bold text-primary display-4"
                                data-toggle="tooltip"
                                title="<strong>Total de Contratos:</strong> <?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?> contrato(s) registrado(s) para este afiliado"
                                style="cursor: help;">
                                <?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="text-muted small mb-1">Contratos Activos</div>
                            <div class="font-weight-bold text-success display-4"
                                data-toggle="tooltip"
                                title="<strong>Contratos Activos:</strong> Contratos actualmente vigentes con cobertura activa"
                                style="cursor: help;">
                                <?php
                                $activeCount = 0;
                                foreach ($dataProvider->getModels() as $model) {
                                    if ($model->estatus === 'Activo') {
                                        $activeCount++;
                                    }
                                }
                                echo $activeCount;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Contracts List -->
    <?php if ($dataProvider && $dataProvider->getTotalCount() > 0): ?>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="text-dark mb-0">
                    <i class="fas fa-list mr-2 text-primary"></i>Lista de Contratos
                    <span class="badge badge-primary ml-2"><?= $dataProvider->getTotalCount() ?> registros</span>
                </h4>
                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Haga clic en cualquier contrato para ver los detalles del mismo
                </small>
            </div>
        </div>

        <?php foreach ($dataProvider->getModels() as $index => $model): ?>
            <?php
            $pagosDelContrato = $model->getPagosDelContrato()->orderBy(['fecha_pago' => SORT_ASC])->all();
            $totalPagado = 0;
            $totalPagos = count($pagosDelContrato);
            $lastPaymentDate = null;

            foreach ($pagosDelContrato as $pago) {
                $totalPagado += floatval($pago->monto_pagado);
                if (!$lastPaymentDate || strtotime($pago->fecha_pago) > strtotime($lastPaymentDate)) {
                    $lastPaymentDate = $pago->fecha_pago;
                }
            }

            $remainingDays = null;
            $remainingStatus = '';
            $remainingColor = '';
            if ($model->fecha_ven) {
                $today = new DateTime();
                $endDate = new DateTime($model->fecha_ven);
                if ($today < $endDate) {
                    $remainingDays = $today->diff($endDate)->days;
                    $remainingStatus = 'días restantes';
                    $remainingColor = '#28a745';
                } elseif ($today > $endDate) {
                    $remainingDays = $endDate->diff($today)->days;
                    $remainingStatus = 'días vencido';
                    $remainingColor = '#dc3545';
                } else {
                    $remainingDays = 0;
                    $remainingStatus = 'finaliza hoy';
                    $remainingColor = '#ffc107';
                }
            }

            $periodoInfo = Yii::$app->formatter->asDate($model->fecha_ini, 'php:d/m/Y');
            if ($model->fecha_ven) {
                $periodoInfo .= ' - ' . Yii::$app->formatter->asDate($model->fecha_ven, 'php:d/m/Y');
            } else {
                $periodoInfo .= ' - Sin fecha de vencimiento';
            }

            $status = $model->estatus ?: 'Registrado';
            $statusIcons = [
                'Registrado' => '📋',
                'Activo' => '✅',
                'Anulado' => '❌',
                'Vencido' => '⏰',
                'Pendiente' => '⏳',
                'suspendido' => '⏸️',
            ];
            $icon = $statusIcons[$status] ?? '';

            $statusTooltipMsg = '<strong>Estatus del Contrato:</strong> ' . $status;
            if ($status === 'Activo') $statusTooltipMsg .= '<br>✅ El afiliado tiene cobertura completa';
            if ($status === 'suspendido') $statusTooltipMsg .= '<br>⏸️ Contrato suspendido por cuotas vencidas';
            if ($status === 'Anulado') $statusTooltipMsg .= '<br>❌ Contrato cancelado permanentemente';
            if ($status === 'Vencido') $statusTooltipMsg .= '<br>⏰ El contrato ha expirado';
            if ($status === 'Registrado') $statusTooltipMsg .= '<br>📋 Contrato registrado pero aún no activo';
            if ($status === 'Pendiente') $statusTooltipMsg .= '<br>⏳ Contrato pendiente de aprobación';

            $pendingCuotasCount = Cuotas::find()
                ->where(['contrato_id' => $model->id, 'estatus' => 'pendiente'])
                ->count();
            ?>

            <!-- Contract Card -->
            <div class="card contract-card mb-3 shadow-sm border-0" style="border-radius: 10px; overflow: hidden; transition: all 0.3s ease; border-left: 4px solid <?= $model->estatus === 'Activo' ? '#28a745' : ($model->estatus === 'Anulado' ? '#dc3545' : '#0078d4') ?>;">
                <div class="card-header bg-gradient-primary text-white py-1 px-3"
                    style="cursor: pointer; border-bottom: 0; background: linear-gradient(135deg, <?= $model->estatus === 'Activo' ? '#28a745' : ($model->estatus === 'Anulado' ? '#dc3545' : '#0078d4') ?> 0%, <?= $model->estatus === 'Activo' ? '#1e7e34' : ($model->estatus === 'Anulado' ? '#b02a37' : '#005a9e') ?> 100%) !important;"
                    data-toggle="collapse"
                    data-target="#contractDetails<?= $model->id ?>"
                    aria-expanded="false"
                    aria-controls="contractDetails<?= $model->id ?>"
                    title="Haga clic para expandir o contraer los detalles del contrato">

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center" style="gap: 12px;">
                            <div class="mr-2">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">
                                    <i class="fas fa-chevron-down contract-toggle-icon text-primary" style="font-size: 14px;"></i>
                                </div>
                            </div>

                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="text-white-80 small font-weight-medium" style="opacity: 0.7; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Contrato</div>
                                        <h6 class="mb-0 font-weight-bold text-white" style="font-size: 20px; letter-spacing: 0.3px; line-height: 1.2;">
                                            #<?= $model->id ?>
                                        </h6>
                                    </div>
                                </div>

                                <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                                <div class="status-badge">
                                    <?php
                                    $badgeClasses = [
                                        'Registrado' => 'badge-info',
                                        'Activo' => 'badge-success',
                                        'Anulado' => 'badge-danger',
                                        'Vencido' => 'badge-warning',
                                        'Pendiente' => 'badge-primary',
                                        'suspendido' => 'badge-secondary',
                                    ];
                                    $class = $badgeClasses[$status] ?? 'badge-light';
                                    ?>
                                    <span class="badge <?= $class ?> font-weight-bold text-white px-3 py-1 status-blink"
                                        data-toggle="tooltip"
                                        title="<?= $statusTooltipMsg ?>"
                                        style="border-radius: 14px; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 12px rgba(0,0,0,0.25); animation: blinkStatus 1.5s ease-in-out infinite; cursor: help;">
                                        <?= $icon ?> <?= $status ?>
                                    </span>
                                </div>

                                <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                                <?php if ($model->nrocontrato): ?>
                                    <div class="d-flex align-items-center">
                                        <span class="badge px-3 py-1"
                                            data-toggle="tooltip"
                                            title="<strong>N° de Contrato:</strong> <?= Html::encode($model->nrocontrato) ?>"
                                            style="border-radius: 14px; font-size: 11px; color: #495057; background: rgba(255,255,255,0.9); display: inline-flex; align-items: center; gap: 4px; border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 2px 4px rgba(0,0,0,0.05); cursor: help;">
                                            <i class="fas fa-hashtag" style="font-size: 10px; color: #6c757d;"></i>
                                            <?= $model->nrocontrato ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php
                        $cuotasDelContratoForBalance = Cuotas::find()->where(['contrato_id' => $model->id])->all();
                        $montoTotalCuotas = 0;
                        $montoPagadoCuotas = 0;
                        foreach ($cuotasDelContratoForBalance as $c) {
                            $montoTotalCuotas += $c->monto ?: 0;
                            if ($c->estatus === 'pagada') {
                                $montoPagadoCuotas += $c->monto ?: 0;
                            }
                        }
                        $saldoPendienteCuotas = $montoTotalCuotas - $montoPagadoCuotas;
                        $balanceColor = $saldoPendienteCuotas > 0 ? '#ffc107' : ($saldoPendienteCuotas < 0 ? '#dc3545' : '#28a745');
                        ?>
                        <div class="d-flex align-items-center" style="gap: 20px;">
                            <div class="text-right">
                                <div class="text-white-80 small font-weight-medium" style="opacity: 0.7; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-dollar-sign mr-1" style="font-size: 8px;"></i> Monto Total
                                </div>
                                <div class="font-weight-bold text-white"
                                    data-toggle="tooltip"
                                    title="<strong>Monto Total:</strong> Valor total del contrato: <?= $model->monto ? Yii::$app->formatter->asCurrency($model->monto, 'USD') : 'N/A' ?>"
                                    style="font-size: 17px; cursor: help;">
                                    <?= $model->monto ? Yii::$app->formatter->asCurrency($model->monto, 'USD') : 'N/A' ?>
                                </div>
                            </div>

                            <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                            <div class="text-right">
                                <div class="text-white-80 small font-weight-medium" style="opacity: 0.7; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-receipt mr-1" style="font-size: 8px;"></i> Pagos
                                </div>
                                <div class="font-weight-bold text-white"
                                    data-toggle="tooltip"
                                    title="<strong>Pagos:</strong> <?= $totalPagos ?> pago(s) registrado(s) para este contrato"
                                    style="font-size: 17px; cursor: help;">
                                    <?= $totalPagos ?>
                                </div>
                            </div>

                            <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                            <div class="text-right">
                                <div class="text-white-80 small font-weight-medium" style="opacity: 0.7; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-check-circle mr-1" style="font-size: 8px;"></i> Pagado
                                </div>
                                <div class="font-weight-bold text-white"
                                    data-toggle="tooltip"
                                    title="<strong>Total Pagado:</strong> <?= Yii::$app->formatter->asCurrency($montoPagadoCuotas, 'USD') ?> pagado hasta la fecha"
                                    style="font-size: 17px; color: #28a745; cursor: help;">
                                    <?= Yii::$app->formatter->asCurrency($montoPagadoCuotas, 'USD') ?>
                                </div>
                            </div>

                            <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                            <div class="text-right">
                                <div class="text-white-80 small font-weight-medium" style="opacity: 0.7; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-balance-scale mr-1" style="font-size: 8px;"></i> Saldo
                                </div>
                                <div class="font-weight-bold"
                                    data-toggle="tooltip"
                                    title="<strong>Saldo Pendiente:</strong> <?= Yii::$app->formatter->asCurrency($saldoPendienteCuotas, 'USD') ?> <?= $saldoPendienteCuotas > 0 ? 'pendiente(s) de pago' : ($saldoPendienteCuotas == 0 ? '✓ Al día' : 'a favor') ?>"
                                    style="font-size: 17px; color: <?= $balanceColor ?>; cursor: help;">
                                    <?= Yii::$app->formatter->asCurrency($saldoPendienteCuotas, 'USD') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract Details (Collapsible) -->
                <div id="contractDetails<?= $model->id ?>" class="collapse">
                    <div class="card-body pt-3 pb-3" style="background: #fafbfc; padding: 12px 16px !important;">
                        <!-- Contract Summary Cards -->
                        <div class="row mb-3" style="margin: 0 -4px;">
                            <!-- Card 1: PLAN -->
                            <div class="col-md-3 mb-2" style="padding: 0 4px;">
                                <div class="card border-0 h-100 shadow-lg" style="border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; position: relative; overflow: hidden;">
                                    <div style="position: absolute; top: -30px; right: -30px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                                    <div style="position: absolute; bottom: -40px; left: -40px; width: 100px; height: 100px; background: rgba(255,255,255,0.03); border-radius: 50%;"></div>
                                    <div class="card-body py-3 px-3" style="padding: 12px 16px !important; position: relative; z-index: 1;">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                                <i class="fas fa-tag" style="color: #667eea; font-size: 20px;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 ml-2 font-weight-bold" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85;">Plan</h6>
                                            </div>
                                        </div>
                                        <div class="pl-2">
                                            <div class="font-weight-bold"
                                                data-toggle="tooltip"
                                                title="<strong>Plan:</strong> <?= $model->plan ? Html::encode($model->plan->nombre) : 'N/A' ?>"
                                                style="font-size: 22px; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.1); cursor: help;">
                                                <?= $model->plan ? $model->plan->nombre : 'N/A' ?>
                                            </div>
                                            <?php if ($model->clinica): ?>
                                                <div style="font-size: 13px; opacity: 0.9; color: rgba(255,255,255,0.9); margin-top: 2px;"
                                                    data-toggle="tooltip"
                                                    title="<strong>Clínica:</strong> <?= Html::encode($model->clinica->nombre) ?>">
                                                    <i class="fas fa-hospital mr-1" style="font-size: 11px;"></i> <?= $model->clinica->nombre ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($model->plan): ?>
                                                <div class="d-flex justify-content-between align-items-center mt-2" style="background: rgba(255,255,255,0.12); border-radius: 8px; padding: 6px 12px;">
                                                    <span style="font-size: 14px; color: #ffffff; opacity: 0.95; font-weight: 500;">
                                                        <i class="fas fa-dollar-sign mr-1" style="font-size: 12px;"></i> <?= Yii::$app->formatter->asCurrency($model->plan->precio, 'USD') ?>/mes
                                                    </span>
                                                    <span class="badge" style="background: rgba(255,255,255,0.2); color: white; font-size: 11px; padding: 4px 12px; border-radius: 12px;">
                                                        <i class="fas fa-shield-alt mr-1" style="font-size: 10px;"></i> <?= Yii::$app->formatter->asCurrency($model->plan->cobertura, 'USD') ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: TIEMPO -->
                            <div class="col-md-3 mb-2" style="padding: 0 4px;">
                                <div class="card border-0 h-100 shadow-lg" style="border-radius: 12px; background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%); color: white; position: relative; overflow: hidden;">
                                    <div style="position: absolute; top: -30px; right: -30px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                                    <div style="position: absolute; bottom: -40px; left: -40px; width: 100px; height: 100px; background: rgba(255,255,255,0.03); border-radius: 50%;"></div>
                                    <div class="card-body py-3 px-3" style="padding: 12px 16px !important; position: relative; z-index: 1;">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                                <i class="fas fa-calendar-alt" style="color: #36d1dc; font-size: 20px;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 ml-2 font-weight-bold" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85;">Tiempo</h6>
                                            </div>
                                        </div>
                                        <div class="pl-2">
                                            <div style="font-size: 14px; opacity: 0.9; font-weight: 500;">
                                                <i class="fas fa-play mr-1" style="font-size: 11px;"></i> <?= Yii::$app->formatter->asDate($model->fecha_ini, 'php:d/m/Y') ?>
                                                <span style="margin: 0 6px;">→</span>
                                                <i class="fas fa-flag mr-1" style="font-size: 11px;"></i> <?= $model->fecha_ven ? Yii::$app->formatter->asDate($model->fecha_ven, 'php:d/m/Y') : 'Indefinido' ?>
                                            </div>
                                            <div class="mt-2">
                                                <?php if ($remainingDays !== null): ?>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span style="font-size: 28px; font-weight: bold; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                                <?= $remainingDays ?>
                                                            </span>
                                                            <span style="font-size: 14px; opacity: 0.8; font-weight: 500; margin-left: 4px;"><?= $remainingStatus ?></span>
                                                        </div>
                                                        <div class="progress" style="width: 40%; height: 6px; background: rgba(255,255,255,0.25); border-radius: 3px;">
                                                            <?php
                                                            $totalDays = 365;
                                                            if ($model->fecha_ven && $model->fecha_ini) {
                                                                $start = new DateTime($model->fecha_ini);
                                                                $end = new DateTime($model->fecha_ven);
                                                                $totalDays = $start->diff($end)->days;
                                                            }
                                                            $daysPassed = $totalDays - $remainingDays;
                                                            $progressPercent = $totalDays > 0 ? min(100, round(($daysPassed / $totalDays) * 100)) : 0;
                                                            ?>
                                                            <div class="progress-bar" style="width: <?= $progressPercent ?>%; height: 100%; background: rgba(255,255,255,0.8); border-radius: 3px; transition: width 0.5s ease;"></div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="font-size: 16px; opacity: 0.9; color: #ffffff; font-weight: 500;">Contrato Indefinido</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3: Cuotas -->
                            <div class="col-md-3 mb-2" style="padding: 0 4px;">
                                <?php
                                $cuotasDelContrato = Cuotas::find()->where(['contrato_id' => $model->id])->all();
                                $totalCuotas = count($cuotasDelContrato);
                                $pagadas = 0;
                                $pendientes = 0;
                                $vencidas = 0;
                                $enGracias = 0;
                                $montoTotal = 0;
                                $montoPagado = 0;

                                foreach ($cuotasDelContrato as $c) {
                                    $montoTotal += $c->monto ?: 0;
                                    switch ($c->estatus) {
                                        case 'pagada':
                                            $pagadas++;
                                            $montoPagado += $c->monto ?: 0;
                                            break;
                                        case 'pendiente':
                                            $pendientes++;
                                            break;
                                        case 'vencida':
                                            $vencidas++;
                                            break;
                                        case 'en_gracias':
                                            $enGracias++;
                                            break;
                                    }
                                }
                                $saldoPendiente = $montoTotal - $montoPagado;
                                ?>
                                <div class="card border-0 h-100 shadow-lg" style="border-radius: 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; position: relative; overflow: hidden;">
                                    <div style="position: absolute; top: -30px; right: -30px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                                    <div style="position: absolute; bottom: -40px; left: -40px; width: 100px; height: 100px; background: rgba(255,255,255,0.03); border-radius: 50%;"></div>
                                    <div class="card-body py-3 px-3" style="padding: 12px 16px !important; position: relative; z-index: 1;">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                                <i class="fas fa-credit-card" style="color: #f5576c; font-size: 20px;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 ml-2 font-weight-bold" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85;">Cuotas</h6>
                                            </div>
                                        </div>
                                        <div class="pl-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span style="font-size: 13px; opacity: 0.85;">Total</span>
                                                <span class="font-weight-bold" style="font-size: 18px;"><?= $totalCuotas ?></span>
                                            </div>
                                            <div class="row" style="margin: 4px -2px 0;">
                                                <div class="col-6" style="padding: 0 2px;">
                                                    <div class="d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.12); border-radius: 4px; padding: 3px 8px;">
                                                        <span style="font-size: 11px; opacity: 0.8;">
                                                            <i class="fas fa-check-circle" style="color: #28a745; font-size: 10px;"></i> Pagadas
                                                        </span>
                                                        <span class="font-weight-bold" style="font-size: 15px;"><?= $pagadas ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-6" style="padding: 0 2px;">
                                                    <div class="d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.12); border-radius: 4px; padding: 3px 8px;">
                                                        <span style="font-size: 11px; opacity: 0.8;">
                                                            <i class="fas fa-clock" style="color: #ffc107; font-size: 10px;"></i> Pendientes
                                                        </span>
                                                        <span class="font-weight-bold" style="font-size: 15px;"><?= $pendientes ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-6" style="padding: 0 2px; margin-top: 3px;">
                                                    <div class="d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.12); border-radius: 4px; padding: 3px 8px;">
                                                        <span style="font-size: 11px; opacity: 0.8;">
                                                            <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 10px;"></i> Vencidas
                                                        </span>
                                                        <span class="font-weight-bold" style="font-size: 15px;"><?= $vencidas ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-6" style="padding: 0 2px; margin-top: 3px;">
                                                    <div class="d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.12); border-radius: 4px; padding: 3px 8px;">
                                                        <span style="font-size: 11px; opacity: 0.8;">
                                                            <i class="fas fa-hourglass-half" style="color: #17a2b8; font-size: 10px;"></i> En Gracias
                                                        </span>
                                                        <span class="font-weight-bold" style="font-size: 15px;"><?= $enGracias ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($saldoPendiente > 0): ?>
                                                <div class="mt-2" style="border-top: 1px solid rgba(255,255,255,0.15); padding-top: 3px;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span style="font-size: 12px; opacity: 0.8; font-weight: 500;">Saldo pendiente</span>
                                                        <span class="font-weight-bold" style="font-size: 16px; color: #ffc107;">$ <?= number_format($saldoPendiente, 2) ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 4: Resumen -->
                            <div class="col-md-3 mb-2" style="padding: 0 4px;">
                                <div class="card border-0 h-100 shadow-lg" style="border-radius: 12px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; position: relative; overflow: hidden;">
                                    <div style="position: absolute; top: -30px; right: -30px; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                                    <div style="position: absolute; bottom: -40px; left: -40px; width: 100px; height: 100px; background: rgba(255,255,255,0.03); border-radius: 50%;"></div>
                                    <div class="card-body py-3 px-3" style="padding: 12px 16px !important; position: relative; z-index: 1;">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                                <i class="fas fa-lightbulb" style="color: #4facfe; font-size: 20px;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 ml-2 font-weight-bold" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85;">Resumen</h6>
                                            </div>
                                        </div>
                                        <div class="pl-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span style="font-size: 12px; opacity: 0.85;">Estado</span>
                                                <?php
                                                $statusColors = [
                                                    'Activo' => '#28a745',
                                                    'Anulado' => '#dc3545',
                                                    'Vencido' => '#ffc107',
                                                    'Registrado' => '#17a2b8',
                                                    'Pendiente' => '#007bff',
                                                    'suspendido' => '#6c757d',
                                                ];
                                                $statusBgColor = $statusColors[$model->estatus] ?? '#6c757d';
                                                ?>
                                                <span style="background: <?= $statusBgColor ?>; color: white; font-size: 12px; padding: 4px 14px; border-radius: 20px; font-weight: 600; display: inline-block; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                                    <?= $model->estatus ?: 'Registrado' ?>
                                                </span>
                                            </div>

                                            <?php
                                            $lastPayment = null;
                                            if (!empty($pagosDelContrato)) {
                                                $lastPayment = end($pagosDelContrato);
                                            }
                                            ?>
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <span style="font-size: 12px; opacity: 0.85;">Último pago</span>
                                                <span style="font-size: 14px; font-weight: 600;">
                                                    <?php if ($lastPayment): ?>
                                                        <?= Yii::$app->formatter->asDate($lastPayment->fecha_pago, 'php: d/m/Y') ?>
                                                        <small style="font-size: 12px; opacity: 0.75;">$<?= number_format($lastPayment->monto_pagado, 2) ?></small>
                                                    <?php else: ?>
                                                        <span style="opacity: 0.6;">Sin pagos</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>

                                            <?php
                                            $nextCuota = Cuotas::find()
                                                ->where(['contrato_id' => $model->id])
                                                ->andWhere(['in', 'estatus', ['pendiente', 'en_gracias']])
                                                ->orderBy(['fecha_vencimiento' => SORT_ASC])
                                                ->one();
                                            ?>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <span style="font-size: 12px; opacity: 0.85;">Próximo vencimiento</span>
                                                <span style="font-size: 14px; font-weight: 600;">
                                                    <?php if ($nextCuota): ?>
                                                        <?= Yii::$app->formatter->asDate($nextCuota->fecha_vencimiento, 'php:d/m/Y') ?>
                                                        <small style="font-size: 12px; opacity: 0.75;">$<?= number_format($nextCuota->monto, 2) ?></small>
                                                    <?php else: ?>
                                                        <span style="opacity: 0.6; color: #28a745;">✓ Sin pendientes</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>

                                            <div class="mt-2" style="border-top: 1px solid rgba(255,255,255,0.15); padding-top: 3px;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span style="font-size: 12px; opacity: 0.8;">
                                                        <i class="fas fa-file-invoice-dollar mr-1"></i> Total pagado
                                                    </span>
                                                    <span class="font-weight-bold" style="font-size: 16px;">$ <?= number_format($montoPagado, 2) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-1">
                                                    <span style="font-size: 12px; opacity: 0.8;">
                                                        <i class="fas fa-percent mr-1"></i> Progreso
                                                    </span>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress" style="width: 60px; height: 6px; background: rgba(255,255,255,0.25); border-radius: 3px;">
                                                            <?php $progressPercent = $montoTotal > 0 ? min(100, round(($montoPagado / $montoTotal) * 100)) : 0; ?>
                                                            <div class="progress-bar" style="width: <?= $progressPercent ?>%; height: 100%; background: rgba(255,255,255,0.8); border-radius: 3px;"></div>
                                                        </div>
                                                        <span class="ml-1" style="font-size: 14px; font-weight: 600;"><?= $progressPercent ?>%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment History -->
                        <div class="mb-2">
                            <div style="background: linear-gradient(135deg, #90c6ed 0%, #49aaf8 100%); border-radius: 8px 8px 0 0; padding: 10px 18px; border-left: 4px solid #0078d4;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="d-flex align-items-center">
                                            <div style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px; box-shadow: 0 2px 8px rgba(0,120,212,0.3);">
                                                <i class="fas fa-credit-card" style="color: white; font-size: 18px;"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-0 font-weight-bold" style="color: #005a9e; font-size: 16px;">
                                                    Historial de Pagos
                                                    <span class="badge ml-2" style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); padding: 4px 10px; border-radius: 14px; font-size: 11px; color: white;">
                                                        <?= $totalPagos ?>
                                                    </span>
                                                </h5>
                                                <p class="mb-0 mt-1" style="font-size: 12px; color: #495057; opacity: 0.8;">
                                                    <i class="fas fa-info-circle mr-1" style="font-size: 11px;"></i>
                                                    Registro detallado de todas las transacciones realizadas para este contrato
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <?php if ($model->estatus !== 'Anulado'): ?>
                                            <?= Html::a(
                                                '<i class="fas fa-plus-circle mr-1"></i> Nuevo Pago',
                                                Url::to(['pagos/create', 'user_id' => $model->user_id, 'contrato_id' => $model->id]),
                                                [
                                                    'class' => 'btn btn-success btn-sm',
                                                    'data-toggle' => 'tooltip',
                                                    'title' => 'Registrar un nuevo pago para este contrato',
                                                    'style' => 'background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border: none; border-radius: 6px; padding: 4px 14px; font-size: 12px; font-weight: 600;'
                                                ]
                                            ) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($pagosDelContrato)): ?>
                                <div class="collapse show" id="paymentHistoryCollapse<?= $model->id ?>">
                                    <div class="card border-0 shadow-sm" style="border-radius: 0 0 8px 8px; overflow: hidden; border-top: none;">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0" id="payment-history-table" style="font-size: 14px;">
                                                <thead style="background: #0078d4 !important; border-bottom: 2px solid #005a9e;">
                                                    <tr>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 50px; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Número secuencial del pago">#</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 100px; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Fecha en que se realizó el pago">Fecha</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 110px; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Monto pagado en dólares (USD)">Monto USD</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 110px; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Monto equivalente en bolívares (Bs)">Monto Bs</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 90px; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Tasa de cambio aplicada en el pago">Tasa</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 100px; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Método utilizado para realizar el pago">Método</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 120px; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Número de referencia o comprobante del pago">Referencia</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 100px; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Comprobante digital del pago (imagen)">Comprobante</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 110px; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Estado actual del pago en el sistema">Estatus</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 130px; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Acciones disponibles para este pago">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $paymentCounter = 1; ?>
                                                    <?php foreach ($pagosDelContrato as $pago): ?>
                                                        <?php
                                                        $tasaCalculada = '';
                                                        if ($pago->monto_pagado > 0 && $pago->monto_usd > 0) {
                                                            $tasaCalculada = number_format($pago->monto_usd / $pago->monto_pagado, 2);
                                                        } elseif ($pago->tasa) {
                                                            $tasaCalculada = number_format($pago->tasa, 2);
                                                        } else {
                                                            $tasaCalculada = 'N/A';
                                                        }

                                                        $rowClass = ($paymentCounter % 2 == 0) ? 'payment-even' : 'payment-odd';
                                                        ?>
                                                        <tr class="<?= $rowClass ?>" style="border-bottom: 1px solid #e8edf2;">
                                                            <td class="text-center align-middle" style="padding: 8px 6px;">
                                                                <span class="badge" style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); color: white; padding: 5px 10px; border-radius: 6px; font-size: 13px; min-width: 35px; display: inline-block;">
                                                                    <?= str_pad($paymentCounter, 2, '0', STR_PAD_LEFT) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle" style="padding: 8px 6px;" data-toggle="tooltip" title="Fecha del pago: <?= Yii::$app->formatter->asDate($pago->fecha_pago, 'php:d/m/Y') ?>">
                                                                <span class="font-weight-medium text-dark" style="font-size: 14px;">
                                                                    <i class="fas fa-calendar-day text-muted mr-1" style="font-size: 12px;"></i>
                                                                    <?= Yii::$app->formatter->asDate($pago->fecha_pago, 'php:d/m/Y') ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle" style="padding: 8px 6px;" data-toggle="tooltip" title="Monto pagado: $<?= Yii::$app->formatter->asDecimal($pago->monto_pagado, 2) ?> USD">
                                                                <span class="font-weight-bold text-success" style="font-size: 17px;">
                                                                    <i class="fas fa-dollar-sign mr-1" style="font-size: 12px;"></i>
                                                                    <?= Yii::$app->formatter->asDecimal($pago->monto_pagado, 2) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle" style="padding: 8px 6px;" data-toggle="tooltip" title="Monto en bolívares: <?= Yii::$app->formatter->asDecimal($pago->monto_usd, 2) ?> Bs">
                                                                <span class="font-weight-bold text-primary" style="font-size: 17px;">
                                                                    <i class="fas fa-chart-line mr-1" style="font-size: 12px;"></i>
                                                                    <?= Yii::$app->formatter->asDecimal($pago->monto_usd, 2) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle" style="padding: 8px 6px;" data-toggle="tooltip" title="Tasa de cambio: <?= $tasaCalculada ?>">
                                                                <?php if ($tasaCalculada !== 'N/A'): ?>
                                                                    <span class="badge" style="background-color: #e9ecef; color: #495057; padding: 4px 8px; font-size: 12px; font-weight: 600; border-radius: 4px;">
                                                                        <i class="fas fa-exchange-alt mr-1" style="font-size: 10px;"></i> <?= $tasaCalculada ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted small" style="font-size: 12px;">N/A</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center align-middle" style="padding: 8px 6px;" data-toggle="tooltip" title="Método de pago: <?= $pago->metodo_pago ?: 'N/A' ?>">
                                                                <?php
                                                                $methods = [
                                                                    'transferencia' => '<span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-university mr-1" style="font-size: 10px;"></i>Transferencia</span>',
                                                                    'transferencia_bancaria' => '<span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-university mr-1" style="font-size: 10px;"></i>Transferencia</span>',
                                                                    'efectivo' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-money-bill-wave mr-1" style="font-size: 10px;"></i>Efectivo</span>',
                                                                    'efectivo_dolar' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-dollar-sign mr-1" style="font-size: 10px;"></i>Efectivo USD</span>',
                                                                    'efectivo - dólar ($)' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-dollar-sign mr-1" style="font-size: 10px;"></i>Efectivo USD</span>',
                                                                    'pago_movil' => '<span class="badge" style="background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-mobile-alt mr-1" style="font-size: 10px;"></i>Pago Móvil</span>',
                                                                    'pagomovil' => '<span class="badge" style="background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-mobile-alt mr-1" style="font-size: 10px;"></i>Pago Móvil</span>',
                                                                    'zelle' => '<span class="badge" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fab fa-zelle mr-1" style="font-size: 10px;"></i>Zelle</span>',
                                                                    'paypal' => '<span class="badge" style="background: linear-gradient(135deg, #003087 0%, #001f6b 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fab fa-paypal mr-1" style="font-size: 10px;"></i>PayPal</span>',
                                                                ];
                                                                $methodKey = strtolower(trim($pago->metodo_pago));
                                                                echo isset($methods[$methodKey]) ? $methods[$methodKey] : '<span class="badge badge-secondary" style="font-size: 12px; padding: 5px 12px;">' . ($pago->metodo_pago ?: 'N/A') . '</span>';
                                                                ?>
                                                            </td>
                                                            <td class="text-center align-middle" style="padding: 8px 6px;" data-toggle="tooltip" title="<?= $pago->numero_referencia_pago ? 'Número de referencia: ' . $pago->numero_referencia_pago : 'Sin número de referencia' ?>">
                                                                <?php if ($pago->numero_referencia_pago): ?>
                                                                    <code class="font-monospace small" style="background: #f5f5f5; padding: 3px 8px; border-radius: 4px; font-size: 12px;">
                                                                        <i class="fas fa-hashtag mr-1" style="color: #6c757d; font-size: 10px;"></i>
                                                                        <?= $pago->numero_referencia_pago ?>
                                                                    </code>
                                                                <?php else: ?>
                                                                    <span class="text-muted small" style="font-size: 12px;"><i class="fas fa-ban mr-1" style="font-size: 10px;"></i>Sin ref.</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center align-middle" style="padding: 8px 6px;">
                                                                <?php if ($pago->imagen_prueba): ?>
                                                                    <?= Html::a(
                                                                        '<i class="fas fa-file-invoice-dollar mr-1" style="font-size: 12px;"></i>Ver',
                                                                        $pago->imagen_prueba,
                                                                        [
                                                                            'target' => '_blank',
                                                                            'data-toggle' => 'tooltip',
                                                                            'title' => 'Ver comprobante de pago en nueva pestaña',
                                                                            'class' => 'btn btn-sm btn-outline-primary',
                                                                            'style' => 'border-radius: 4px; padding: 3px 10px; font-size: 12px;'
                                                                        ]
                                                                    ) ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted small" style="font-size: 12px;"><i class="fas fa-ban mr-1" style="font-size: 10px;"></i>Sin comp.</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center align-middle" style="padding: 8px 6px;">
                                                                <?php
                                                                $badges = [
                                                                    'Conciliado' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-check-circle mr-1" style="font-size: 10px;"></i>Conciliado</span>',
                                                                    'Por Conciliar' => '<span class="badge" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-clock mr-1" style="font-size: 10px;"></i>Por Conciliar</span>',
                                                                    'pendiente' => '<span class="badge" style="background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-hourglass-half mr-1" style="font-size: 10px;"></i>Pendiente</span>',
                                                                    'cancelado' => '<span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-times-circle mr-1" style="font-size: 10px;"></i>Cancelado</span>',
                                                                    'verificado' => '<span class="badge" style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-check-double mr-1" style="font-size: 10px;"></i>Verificado</span>',
                                                                ];
                                                                $estatus = $pago->estatus ?? 'Por Conciliar';
                                                                $badgeHtml = isset($badges[$estatus]) ? $badges[$estatus] : '<span class="badge badge-secondary" style="font-size: 12px; padding: 5px 12px;">' . $estatus . '</span>';
                                                                $estatusTooltips = [
                                                                    'Conciliado' => 'Pago conciliado y verificado',
                                                                    'Por Conciliar' => 'Pago pendiente de conciliación bancaria',
                                                                    'pendiente' => 'Pago pendiente de procesamiento',
                                                                    'cancelado' => 'Pago cancelado',
                                                                    'verificado' => 'Pago verificado y confirmado',
                                                                ];
                                                                $estatusTip = $estatusTooltips[$estatus] ?? 'Estado del pago: ' . $estatus;
                                                                ?>
                                                                <span data-toggle="tooltip" title="<?= $estatusTip ?>">
                                                                    <?= $badgeHtml ?>
                                                                </span>
                                                            </td>
                                                            <!-- ========== ACCIONES COLUMN ========== -->
                                                            <td class="text-center align-middle" style="padding: 4px 2px; width: 130px;">
                                                                <div class="d-flex align-items-center justify-content-center" style="gap: 3px;">
                                                                    <?= Html::a(
                                                                        '<i class="fas fa-eye text-info" style="font-size: 18px;"></i>',
                                                                        Url::to(['pagos/view', 'id' => $pago->id]),
                                                                        [
                                                                            'title' => 'Ver detalles del pago',
                                                                            'data-toggle' => 'tooltip',
                                                                            'class' => 'action-icon',
                                                                            'style' => 'display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 4px; text-decoration: none; transition: all 0.2s ease;'
                                                                        ]
                                                                    ) ?>

                                                                    <?= Html::a(
                                                                        '<i class="fas fa-edit text-warning" style="font-size: 18px;"></i>',
                                                                        Url::to(['pagos/update', 'id' => $pago->id]),
                                                                        [
                                                                            'title' => 'Editar pago',
                                                                            'data-toggle' => 'tooltip',
                                                                            'class' => 'action-icon',
                                                                            'style' => 'display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 4px; text-decoration: none; transition: all 0.2s ease;'
                                                                        ]
                                                                    ) ?>

                                                                    <?php
                                                                    $receipts = \app\components\ReceiptGenerator::getReceiptsForPayment($pago->id);

                                                                    if (!empty($receipts)):
                                                                    ?>
                                                                        <?php if (count($receipts) == 1): ?>
                                                                            <?= Html::a(
                                                                                '<i class="fas fa-receipt" style="font-size: 18px; color: #1e7e34;"></i>',
                                                                                Url::to(['receipts/print', 'id' => $receipts[0]->id, 'auto_print' => 1]),
                                                                                [
                                                                                    'title' => 'Imprimir Recibo ' . $receipts[0]->receipt_number,
                                                                                    'data-toggle' => 'tooltip',
                                                                                    'class' => 'action-icon',
                                                                                    'style' => 'display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 4px; text-decoration: none; transition: all 0.2s ease; background-color: rgba(30, 126, 52, 0.08);',
                                                                                    'target' => '_blank'
                                                                                ]
                                                                            ) ?>
                                                                        <?php else: ?>
                                                                            <div class="btn-group" style="display: inline-flex;">
                                                                                <?= Html::a(
                                                                                    '<i class="fas fa-receipt" style="font-size: 18px; color: #1e7e34;"></i>',
                                                                                    '#',
                                                                                    [
                                                                                        'title' => 'Ver/Imprimir Recibos (' . count($receipts) . ')',
                                                                                        'data-toggle' => 'dropdown',
                                                                                        'class' => 'action-icon dropdown-toggle',
                                                                                        'style' => 'display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 4px; text-decoration: none; transition: all 0.2s ease; background-color: rgba(30, 126, 52, 0.08);',
                                                                                        'aria-haspopup' => 'true',
                                                                                        'aria-expanded' => 'false'
                                                                                    ]
                                                                                ) ?>
                                                                                <div class="dropdown-menu dropdown-menu-right" style="min-width: 180px; padding: 4px 0;">
                                                                                    <div class="dropdown-header" style="font-weight: 600; color: #0078d4; font-size: 11px; padding: 6px 12px;">
                                                                                        <i class="fas fa-receipt mr-1"></i> Recibos (<?= count($receipts) ?>)
                                                                                    </div>
                                                                                    <div class="dropdown-divider" style="margin: 2px 0;"></div>
                                                                                    <?= Html::a(
                                                                                        '<i class="fas fa-print mr-2"></i> Imprimir todos',
                                                                                        Url::to(['receipts/print-all', 'paymentId' => $pago->id, 'auto_print' => 1]),
                                                                                        ['class' => 'dropdown-item', 'target' => '_blank', 'style' => 'font-size: 12px; padding: 4px 12px;']
                                                                                    ) ?>
                                                                                    <div class="dropdown-divider" style="margin: 2px 0;"></div>
                                                                                    <?php foreach ($receipts as $receipt): ?>
                                                                                        <?= Html::a(
                                                                                            '<i class="fas fa-file-pdf mr-2 text-danger"></i> ' . $receipt->receipt_number,
                                                                                            Url::to(['receipts/print', 'id' => $receipt->id, 'auto_print' => 1]),
                                                                                            ['class' => 'dropdown-item', 'target' => '_blank', 'style' => 'font-size: 12px; padding: 4px 12px;']
                                                                                        ) ?>
                                                                                    <?php endforeach; ?>
                                                                                    <div class="dropdown-divider" style="margin: 2px 0;"></div>
                                                                                    <?= Html::a(
                                                                                        '<i class="fas fa-list mr-2"></i> Ver todos',
                                                                                        Url::to(['pagos/view-receipts', 'payment_id' => $pago->id]),
                                                                                        ['class' => 'dropdown-item', 'target' => '_blank', 'style' => 'font-size: 12px; padding: 4px 12px;']
                                                                                    ) ?>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <div class="btn-group" style="display: inline-flex;">
                                                                            <?= Html::a(
                                                                                '<i class="fas fa-receipt" style="font-size: 18px; color: #adb5bd;"></i>',
                                                                                '#',
                                                                                [
                                                                                    'title' => 'Sin recibos generados',
                                                                                    'data-toggle' => 'tooltip',
                                                                                    'class' => 'action-icon disabled',
                                                                                    'style' => 'display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 4px; text-decoration: none; cursor: not-allowed; background-color: rgba(173, 181, 189, 0.08);'
                                                                                ]
                                                                            ) ?>
                                                                            <?= Html::a(
                                                                                '<i class="fas fa-plus-circle text-primary" style="font-size: 18px;"></i>',
                                                                                Url::to(['pagos/generate-receipts', 'id' => $pago->id]),
                                                                                [
                                                                                    'title' => 'Generar Recibos para este pago',
                                                                                    'data-toggle' => 'tooltip',
                                                                                    'class' => 'action-icon',
                                                                                    'style' => 'display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 4px; text-decoration: none; transition: all 0.2s ease;',
                                                                                    'data' => [
                                                                                        'method' => 'post',
                                                                                        'confirm' => '¿Desea generar los recibos para este pago?'
                                                                                    ]
                                                                                ]
                                                                            ) ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php $paymentCounter++; ?>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot style="background: #f8f9fa; border-top: 2px solid #0078d4;">
                                                    <tr>
                                                        <td colspan="2" class="text-right font-weight-bold" style="padding: 12px;">
                                                            <i class="fas fa-chart-line mr-1 text-primary" style="font-size: 13px;"></i> Totales:
                                                        </td>
                                                        <td class="text-center font-weight-bold text-success" style="padding: 12px; font-size: 18px;">
                                                            <i class="fas fa-dollar-sign mr-1" style="font-size: 13px;"></i> <?= number_format(array_sum(array_map(function ($p) {
                                                                                                                                    return $p->monto_pagado;
                                                                                                                                }, $pagosDelContrato)), 2) ?>
                                                        </td>
                                                        <td class="text-center font-weight-bold text-primary" style="padding: 12px; font-size: 18px;">
                                                            <i class="fas fa-chart-line mr-1" style="font-size: 13px;"></i> <?= number_format(array_sum(array_map(function ($p) {
                                                                                                                                return $p->monto_usd;
                                                                                                                            }, $pagosDelContrato)), 2) ?>
                                                        </td>
                                                        <td colspan="6"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <!-- Summary Cards Row -->
                                        <div class="row p-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-top: 1px solid #e1e1e1;">
                                            <div class="col-md-3">
                                                <div class="card border-0 shadow-sm" style="border-radius: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    <div class="card-body text-center py-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <small class="text-white-50" style="font-size: 9px;">TOTAL PAGOS</small>
                                                                <h4 class="text-white mb-0 font-weight-bold" style="font-size: 20px;"><?= $totalPagos ?></h4>
                                                            </div>
                                                            <div class="bg-white rounded-circle p-1" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-receipt" style="color: #667eea; font-size: 14px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border-0 shadow-sm" style="border-radius: 8px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                                                    <div class="card-body text-center py-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <small class="text-white-50" style="font-size: 9px;">TOTAL USD</small>
                                                                <h4 class="text-white mb-0 font-weight-bold" style="font-size: 20px;">$ <?= number_format(array_sum(array_map(function ($p) {
                                                                                                                                            return $p->monto_pagado;
                                                                                                                                        }, $pagosDelContrato)), 0) ?></h4>
                                                            </div>
                                                            <div class="bg-white rounded-circle p-1" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-dollar-sign" style="color: #28a745; font-size: 14px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border-0 shadow-sm" style="border-radius: 8px; background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%);">
                                                    <div class="card-body text-center py-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <small class="text-white-50" style="font-size: 9px;">TOTAL BS</small>
                                                                <h4 class="text-white mb-0 font-weight-bold" style="font-size: 20px;">Bs <?= number_format(array_sum(array_map(function ($p) {
                                                                                                                                                return $p->monto_usd;
                                                                                                                                            }, $pagosDelContrato)), 0) ?></h4>
                                                            </div>
                                                            <div class="bg-white rounded-circle p-1" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-chart-line" style="color: #17a2b8; font-size: 14px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border-0 shadow-sm" style="border-radius: 8px; background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
                                                    <div class="card-body text-center py-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <small class="text-dark-50" style="font-size: 9px; color: rgba(0,0,0,0.6);">PROMEDIO</small>
                                                                <h4 class="mb-0 font-weight-bold" style="font-size: 18px; color: #212529;">$<?= $totalPagos > 0 ? number_format(array_sum(array_map(function ($p) {
                                                                                                                                                return $p->monto_pagado;
                                                                                                                                            }, $pagosDelContrato)) / $totalPagos, 2) : '0.00' ?></h4>
                                                            </div>
                                                            <div class="bg-white rounded-circle p-1" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-calculator" style="color: #ffc107; font-size: 14px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-footer" style="background: #f8f9fa; border-top: 1px solid #e1e1e1; padding: 6px 12px;">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <small class="text-muted" style="font-size: 9px;">
                                                        <i class="fas fa-info-circle mr-1" style="font-size: 8px;"></i>
                                                        <?php if ($model->estatus === 'Anulado'): ?>
                                                            Se muestran solo los pagos realizados antes de la anulación (<?= $model->anulado_fecha ? Yii::$app->formatter->asDate($model->anulado_fecha, 'php:d/m/Y') : 'N/A' ?>).
                                                        <?php else: ?>
                                                            <i class="fas fa-check-circle text-success mr-1" style="font-size: 8px;"></i> Historial completo de pagos realizados durante la vigencia del contrato.
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <small class="text-muted" style="font-size: 9px;">
                                                        <i class="fas fa-sync-alt mr-1" style="font-size: 8px;"></i>
                                                        <?= date('d/m/Y H:i') ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card border-0 shadow-sm" style="border-radius: 0 0 8px 8px; background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);">
                                    <div class="card-body text-center py-3">
                                        <div class="mb-2">
                                            <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                                                <i class="fas fa-credit-card fa-2x text-white"></i>
                                            </div>
                                            <h6 class="text-dark mb-1 font-weight-bold" style="font-size: 14px;">No hay pagos registrados</h6>
                                            <p class="text-muted mb-0" style="font-size: 11px;">No se han registrado pagos para este contrato hasta el momento.</p>
                                        </div>
                                        <?php if ($model->estatus !== 'Anulado'): ?>
                                            <div class="mt-2">
                                                <?= Html::a(
                                                    '<i class="fas fa-plus-circle mr-1"></i> Registrar Primer Pago',
                                                    Url::to(['pagos/create', 'user_id' => $model->user_id, 'contrato_id' => $model->id]),
                                                    [
                                                        'class' => 'btn btn-success btn-sm',
                                                        'data-toggle' => 'tooltip',
                                                        'title' => 'Registrar el primer pago para este contrato',
                                                        'style' => 'background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border: none; border-radius: 6px; padding: 4px 14px; font-size: 11px; font-weight: 600;'
                                                    ]
                                                ) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center" style="border-top: 1px solid #e8edf2 !important;">
                            <div>
                                <small class="text-muted" style="font-size: 10px;">
                                    <i class="fas fa-calendar-alt mr-1" style="font-size: 9px;"></i>
                                    <?= $periodoInfo ?>
                                </small>
                            </div>
                            <div class="d-flex align-items-center" style="gap: 4px;">
                                <?php if ($model->estatus !== 'Anulado'): ?>
                                    <?= Html::a(
                                        '<i class="fas fa-eye mr-1" style="font-size: 10px;"></i> Ver Detalles del Contrato',
                                        ['view', 'id' => $model->id],
                                        [
                                            'class' => 'btn btn-primary btn-sm',
                                            'data-toggle' => 'tooltip',
                                            'title' => 'Ver la página completa con todos los detalles del contrato',
                                            'style' => 'padding: 2px 10px; font-size: 10px; border-radius: 4px; background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); border: none;'
                                        ]
                                    ) ?>
                                    <?php if (
                                        $model->fecha_ven &&
                                        $model->fecha_ven < date('Y-m-d') &&
                                        empty($usersWithMultipleContracts[(int)$model->user_id])
                                    ): ?>
                                        <?= Html::a(
                                            '<i class="fas fa-sync-alt mr-1" style="font-size: 10px;"></i> Generar Nuevo Contrato',
                                            ['renew', 'id' => $model->id],
                                            [
                                                'class' => 'btn btn-info btn-sm',
                                                'title' => 'Crear un nuevo período de cobertura con este plan',
                                                'data-toggle' => 'tooltip',
                                                'style' => 'padding: 2px 10px; font-size: 10px; border-radius: 4px;'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                    <?= Html::a(
                                        '<i class="fas fa-file-invoice-dollar mr-1" style="font-size: 10px;"></i> Pago',
                                        ['pagos/create', 'user_id' => $model->user_id, 'contrato_id' => $model->id],
                                        [
                                            'class' => 'btn btn-success btn-sm',
                                            'data-toggle' => 'tooltip',
                                            'title' => 'Registrar un nuevo pago para este contrato',
                                            'style' => 'padding: 2px 10px; font-size: 10px; border-radius: 4px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border: none;'
                                        ]
                                    ) ?>
                                    <?php if (
                                        Yii::$app->user->can('superadmin') ||
                                        Yii::$app->user->can('GERENTE-COMERCIALIZACION') ||
                                        Yii::$app->user->can('GERENTE-CLINICA') ||
                                        Yii::$app->user->can('GERENTE-OPERACIONES')
                                    ): ?>
                                        <?= Html::button(
                                            '<i class="fas fa-ban mr-1" style="font-size: 10px;"></i> Anular',
                                            [
                                                'type' => 'button',
                                                'class' => 'btn btn-danger btn-sm btn-anular',
                                                'data-contrato-id' => $model->id,
                                                'data-contrato-nro' => $model->nrocontrato ?: 'N/A',
                                                'data-contrato-plan' => $model->plan ? $model->plan->nombre : 'N/A',
                                                'data-contrato-clinica' => $model->clinica ? $model->clinica->nombre : 'N/A',
                                                'data-contrato-fecha-ini' => Yii::$app->formatter->asDate($model->fecha_ini, 'php:d/m/Y'),
                                                'data-contrato-fecha-ven' => $model->fecha_ven ? Yii::$app->formatter->asDate($model->fecha_ven, 'php:d/m/Y') : 'N/A',
                                                'data-contrato-monto' => number_format($model->monto, 2),
                                                'data-contrato-estatus' => $model->estatus,
                                                'data-contrato-pending-cuotas' => $pendingCuotasCount,
                                                'title' => 'Anular este contrato (acción irreversible)',
                                                'style' => 'padding: 2px 10px; font-size: 10px; border-radius: 4px; background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); border: none;'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-danger py-1 px-2" style="font-size: 10px;"
                                        data-toggle="tooltip"
                                        title="Este contrato ha sido anulado y no se pueden realizar acciones sobre él">
                                        <i class="fas fa-ban mr-1"></i> Anulado
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <?php if ($dataProvider->pagination->pageSize < $dataProvider->totalCount): ?>
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-body py-2">
                    <?= \yii\widgets\LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'options' => ['class' => 'pagination justify-content-center mb-0'],
                        'linkOptions' => ['class' => 'page-link'],
                        'pageCssClass' => 'page-item',
                        'prevPageCssClass' => 'page-item',
                        'nextPageCssClass' => 'page-item',
                        'disabledPageCssClass' => 'page-item disabled',
                        'activePageCssClass' => 'page-item active',
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- No Contracts Message -->
        <div class="card border-warning shadow-sm">
            <div class="card-header bg-warning text-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-circle mr-2"></i>Sin Contratos Registrados
                </h5>
            </div>
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-file-contract fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay contratos registrados</h4>
                    <p class="text-muted">No se han registrado contratos para este afiliado.</p>
                </div>

                <div class="mt-4">
                    <?= Html::a(
                        '<i class="fas fa-plus-circle mr-2"></i> Crear Primer Contrato',
                        ['create'],
                        [
                            'class' => 'btn btn-success btn-lg py-2 px-4',
                            'data-toggle' => 'tooltip',
                            'title' => 'Crear el primer contrato para este afiliado'
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ============================================================ -->
<!-- SHARED ANULAR MODAL — PROFESSIONAL LAYOUT -->
<!-- ============================================================ -->
<div class="modal anular-modal" id="anularModalShared" tabindex="-1" role="dialog" aria-labelledby="anularModalSharedLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header anular-modal-header">
                <div class="d-flex align-items-center">
                    <div class="anular-header-icon">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="ml-3">
                        <h5 class="modal-title mb-0" id="anularModalSharedLabel">
                            Anular Contrato <span class="anular-header-id">#<span id="anular-modal-id">-</span></span>
                        </h5>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <?= Html::beginForm(['contratos/anular'], 'post', [
                'id' => 'anular-shared-form',
                'class' => 'anular-form',
                'data-base-action' => Url::to(['contratos/anular']),
            ]) ?>

            <input type="hidden" name="id" id="anular-modal-contrato-id" value="">

            <div class="modal-body anular-modal-body">

                <!-- Unified warning banner -->
                <div id="anular-warning-banner" class="anular-warning-banner">
                    <i id="anular-warning-icon" class="fas fa-exclamation-triangle anular-warning-icon"></i>
                    <div>
                        <strong id="anular-warning-title">¡Atención!</strong>
                        <span id="anular-warning-message">
                            Está a punto de anular este contrato. Esta acción no se puede deshacer.
                        </span>
                        <span id="anular-warning-extra" style="display: none;">
                            Además, este contrato tiene <strong><span id="anular-pending-count">0</span></strong>
                            cuota(s) pendiente(s) de pago que quedarán sin efecto al confirmar la anulación.
                        </span>
                    </div>
                </div>

                <!-- Contract summary -->
                <div class="anular-section">
                    <div class="anular-section-header">
                        <i class="fas fa-file-alt"></i>
                        <span>Resumen del Contrato</span>
                    </div>
                    <div class="anular-summary-grid">
                        <div class="anular-summary-item">
                            <span class="anular-summary-label">Contrato ID</span>
                            <span class="anular-summary-value" id="anular-summary-id">-</span>
                        </div>
                        <div class="anular-summary-item">
                            <span class="anular-summary-label">N° Contrato</span>
                            <span class="anular-summary-value" id="anular-summary-nro">-</span>
                        </div>
                        <div class="anular-summary-item">
                            <span class="anular-summary-label">Plan</span>
                            <span class="anular-summary-value" id="anular-summary-plan">-</span>
                        </div>
                        <div class="anular-summary-item">
                            <span class="anular-summary-label">Clínica</span>
                            <span class="anular-summary-value" id="anular-summary-clinica">-</span>
                        </div>
                        <div class="anular-summary-item">
                            <span class="anular-summary-label">Fecha Inicio</span>
                            <span class="anular-summary-value" id="anular-summary-fecha-ini">-</span>
                        </div>
                        <div class="anular-summary-item">
                            <span class="anular-summary-label">Fecha Vencimiento</span>
                            <span class="anular-summary-value" id="anular-summary-fecha-ven">-</span>
                        </div>
                        <div class="anular-summary-item">
                            <span class="anular-summary-label">Monto</span>
                            <span class="anular-summary-value anular-summary-amount" id="anular-summary-monto">-</span>
                        </div>
                        <div class="anular-summary-item">
                            <span class="anular-summary-label">Estatus Actual</span>
                            <span class="anular-summary-value" id="anular-summary-estatus">-</span>
                        </div>
                    </div>
                </div>

                <!-- Reason -->
                <div class="anular-section">
                    <div class="anular-section-header">
                        <i class="fas fa-comment-alt"></i>
                        <span>Motivo de la Anulación <span class="text-danger">*</span></span>
                    </div>
                    <textarea name="anulado_motivo"
                        id="anulado_motivo_shared"
                        class="form-control anular-textarea"
                        rows="4"
                        required
                        maxlength="500"
                        placeholder="Describa la razón por la cual se anula este contrato. Este motivo será visible en el historial del contrato."></textarea>
                    <div class="anular-helper-row">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Mínimo 10 caracteres. Este motivo quedará registrado permanentemente.
                        </small>
                        <small class="text-muted">
                            <span id="anular-charcount">0</span>/500
                        </small>
                    </div>
                </div>

                <!-- Confirmation -->
                <div class="anular-confirm-box">
                    <label class="anular-confirm-label" for="confirm_shared">
                        <input type="checkbox" id="confirm_shared" required>
                        <span class="anular-confirm-checkbox"></span>
                        <span class="anular-confirm-text">
                            Confirmo que deseo <strong>ANULAR</strong> este contrato y entiendo que esta acción es irreversible.
                        </span>
                    </label>
                </div>

            </div>

            <div class="modal-footer anular-modal-footer">
                <button type="button" class="btn btn-light anular-btn-cancel" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>
                <button type="submit" class="btn anular-btn-confirm" id="submit-anular-shared">
                    <i class="fas fa-ban mr-1"></i> Sí, Anular Contrato
                </button>
            </div>

            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- CSS -->
<!-- ============================================================ -->
<?php
$this->registerCss(
    <<<CSS
    /* ===== Microsoft Fluent Design System tokens ===== */
    :root {
        --ms-blue: #0078d4;
        --ms-blue-dark: #005a9e;
        --ms-gray-100: #f3f2f1;
        --ms-gray-200: #e1dfdd;
        --ms-gray-300: #c8c6c4;
        --ms-red: #d13438;
        --ms-green: #107c10;
        --ms-yellow: #ffb900;
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1);
        transition: box-shadow 0.2s ease;
    }

    .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .contract-card {
        transition: all 0.3s ease !important;
    }

    .contract-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
    }

    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        padding: 4px 10px;
        border-radius: 12px;
    }

    .btn {
        border-radius: 4px;
        font-weight: 500;
        padding: 4px 12px;
        transition: all 0.2s ease;
        font-size: 11px;
    }

    .btn-sm {
        padding: 2px 8px;
        font-size: 10px;
    }

    .form-control {
        border-radius: 4px;
        border: 1px solid var(--ms-gray-200);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-control:focus {
        border-color: var(--ms-blue);
        box-shadow: 0 0 0 2px rgba(0,120,212,0.25);
    }

    .card-header.bg-primary {
        background: linear-gradient(135deg, var(--ms-blue) 0%, var(--ms-blue-dark) 100%) !important;
        border-bottom: none;
    }

    .rounded-circle {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    code {
        background: var(--ms-gray-100);
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 9px;
    }

    /* ============================================
       PAYMENT HISTORY: alternating row colors
       ============================================ */
    #payment-history-table tbody tr.payment-even td {
        background-color: #b9cbde !important;
    }
    #payment-history-table tbody tr.payment-odd td {
        background-color: #ffffff !important;
    }
    #payment-history-table tbody tr.payment-even:hover td {
        background-color: #a3b8ce !important;
    }
    #payment-history-table tbody tr.payment-odd:hover td {
        background-color: #f5f7fa !important;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .action-icon {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 4px !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
        color: #6c757d !important;
    }

    .action-icon:hover {
        background-color: #e9ecef !important;
        transform: scale(1.1) !important;
    }

    .action-icon.disabled {
        cursor: not-allowed !important;
        opacity: 1 !important;
    }

    .action-icon.disabled:hover {
        background-color: transparent !important;
        transform: none !important;
    }

    tfoot tr {
        background: linear-gradient(135deg, #e8f0fe 0%, #d4e2fa 100%) !important;
    }
    tfoot tr td {
        background: transparent !important;
    }

    @media (max-width: 768px) {
        .display-4 { font-size: 2rem; }
        .table { font-size: 10px; }
    }

    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: var(--ms-gray-100); border-radius: 4px; }
    ::-webkit-scrollbar-thumb { background: var(--ms-gray-300); border-radius: 4px; }

    @keyframes blinkStatus {
        0%   { opacity: 1; transform: scale(1); }
        25%  { opacity: 1; transform: scale(1.02); }
        50%  { opacity: 1; transform: scale(1); }
        75%  { opacity: 1; transform: scale(1.02); }
        100% { opacity: 1; transform: scale(1); }
    }
    .status-blink { animation: blinkStatus 1.5s ease-in-out infinite !important; }
    .badge-success.status-blink { animation-duration: 2s !important; }
    .badge-danger.status-blink  { animation-duration: 0.8s !important; }
    .badge-warning.status-blink { animation-duration: 1.2s !important; }
    .badge-info.status-blink    { animation-duration: 1.8s !important; }

    /* ============================================
       TOOLTIPS
       ============================================ */
    .tooltip {
        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, system-ui, sans-serif !important;
        z-index: 99999 !important;
    }
    .tooltip-inner {
        background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%) !important;
        color: #ffffff !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        padding: 8px 14px !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.25) !important;
        max-width: 320px !important;
        text-align: left !important;
        line-height: 1.5 !important;
    }
    .tooltip-inner strong { color: #ffc107 !important; font-weight: 700 !important; }
    .tooltip.bs-tooltip-top .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="top"] .arrow::before { border-top-color: #1a237e !important; }
    .tooltip.bs-tooltip-bottom .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="bottom"] .arrow::before { border-bottom-color: #1a237e !important; }
    .tooltip.bs-tooltip-left .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="left"] .arrow::before { border-left-color: #1a237e !important; }
    .tooltip.bs-tooltip-right .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="right"] .arrow::before { border-right-color: #1a237e !important; }

    /* ============================================
       ANULAR MODAL — PROFESSIONAL LAYOUT
       ============================================ */
    #anularModalShared {
        z-index: 1055 !important;
    }
    #anularModalShared.modal {
        opacity: 1 !important;
        transition: none !important;
    }
   #anularModalShared .modal-dialog {
    transition: none !important;
    transform: none !important;
    top: auto !important;
    margin: 20px auto !important;
    max-width: 720px !important;
    width: 92% !important;
    max-height: calc(100vh - 40px) !important;
}

#anularModalShared .modal-content {
    max-height: calc(100vh - 40px) !important;
    display: flex !important;
    flex-direction: column !important;
}

#anularModalShared .anular-modal-header,
#anularModalShared .anular-modal-footer {
    flex-shrink: 0 !important;
}

#anularModalShared .anular-modal-body {
    overflow-y: auto !important;
    flex: 1 1 auto !important;
    min-height: 0 !important;
}
    .modal-backdrop { z-index: 1050 !important; }

    /* Header */
    .anular-modal-header {
        background: linear-gradient(135deg, #b02a37 0%, #7a1a24 100%);
        border-bottom: none;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .anular-header-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        border: 2px solid rgba(255,255,255,0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 22px;
        flex-shrink: 0;
    }
    .anular-modal-header .modal-title {
        color: #ffffff;
        font-weight: 700;
        font-size: 19px;
        letter-spacing: 0.2px;
    }
    .anular-header-id {
        opacity: 0.85;
        font-weight: 600;
        color: white;
    }
    .anular-header-subtitle {
        color: rgba(255,255,255,0.8);
        font-size: 12px;
        display: block;
        margin-top: 2px;
    }
    .anular-modal-header .close {
        color: #ffffff;
        opacity: 0.8;
        font-size: 26px;
        text-shadow: none;
        font-weight: 300;
        padding: 0 4px;
        margin: 0;
    }
    .anular-modal-header .close:hover {
        opacity: 1;
    }

    /* Force the contract # in the header title to white */
#anularModalShared .anular-modal-header .modal-title,
#anularModalShared .anular-modal-header .modal-title span,
#anularModalShared .anular-modal-header .anular-header-id {
    color: #ffffff !important;
}

/* Force the close (×) button to white */
#anularModalShared .anular-modal-header .close {
    color: #ffffff !important;
    opacity: 0.85 !important;
    text-shadow: none !important;
}
#anularModalShared .anular-modal-header .close:hover,
#anularModalShared .anular-modal-header .close:focus {
    color: #ffffff !important;
    opacity: 1 !important;
}
#anularModalShared .anular-modal-header .close span {
    color: #ffffff !important;
}
    /* Body */
    .anular-modal-body {
        padding: 24px 28px;
        background: #ffffff;
    }

    /* Unified warning banner (base state = amber) */
.anular-warning-banner {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #fff8e1;
    border: 1px solid #ffd54f;
    border-left: 4px solid #ffa726;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 16px;
    font-size: 13px;
    color: #5d4037;
    line-height: 1.5;
    transition: background 0.25s ease, border-color 0.25s ease, color 0.25s ease;
}
.anular-warning-icon {
    color: #ef6c00;
    font-size: 18px;
    margin-top: 1px;
    flex-shrink: 0;
    transition: color 0.25s ease;
}

/* Escalated state (when contract has pending cuotas) */
.anular-warning-banner.anular-warning-banner-danger {
    background: #ffebee;
    border-color: #ef9a9a;
    border-left-color: #d32f2f;
    color: #b71c1c;
}
.anular-warning-banner.anular-warning-banner-danger .anular-warning-icon {
    color: #c62828;
}

    /* Section */
    .anular-section {
        margin-bottom: 20px;
    }
    .anular-section:last-child {
        margin-bottom: 0;
    }
    .anular-section-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        color: #1a237e;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e8eaf6;
    }
    .anular-section-header i {
        color: #0078d4;
        font-size: 14px;
    }

    /* Summary grid */
    .anular-summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1px;
        background: #e8eaf6;
        border: 1px solid #e8eaf6;
        border-radius: 6px;
        overflow: hidden;
    }
    .anular-summary-item {
        background: #fafbff;
        padding: 10px 14px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .anular-summary-label {
        font-size: 10px;
        font-weight: 600;
        color: #5c6bc0;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .anular-summary-value {
        font-size: 14px;
        font-weight: 600;
        color: #212529;
        line-height: 1.35;
        word-break: break-word;
    }
    .anular-summary-amount {
        color: #1e7e34;
        font-weight: 700;
    }

    /* Textarea */
    .anular-textarea {
        font-size: 13px;
        line-height: 1.5;
        border-radius: 6px;
        border: 1px solid #d0d7de;
        resize: vertical;
        min-height: 96px;
        padding: 10px 12px;
    }
    .anular-textarea:focus {
        border-color: #0078d4;
        box-shadow: 0 0 0 3px rgba(0,120,212,0.15);
    }
    .anular-helper-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 6px;
    }
    .anular-helper-row small {
        font-size: 11px;
    }

    /* Confirmation checkbox */
    .anular-confirm-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 14px 16px;
    }
    .anular-confirm-label {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer;
        margin: 0;
        font-weight: 500;
    }
    .anular-confirm-label input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    .anular-confirm-checkbox {
        width: 18px;
        height: 18px;
        border: 2px solid #b02a37;
        border-radius: 4px;
        flex-shrink: 0;
        margin-top: 1px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        background: #ffffff;
        position: relative;
    }
    .anular-confirm-label input[type="checkbox"]:checked + .anular-confirm-checkbox {
        background: #b02a37;
        border-color: #b02a37;
    }
    .anular-confirm-label input[type="checkbox"]:checked + .anular-confirm-checkbox::after {
        content: '';
        display: block;
        width: 4px;
        height: 9px;
        border: solid #ffffff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg) translate(-1px, -2px);
    }
    .anular-confirm-label input[type="checkbox"]:focus + .anular-confirm-checkbox {
        box-shadow: 0 0 0 3px rgba(176,42,55,0.2);
    }
    .anular-confirm-text {
        font-size: 13px;
        color: #212529;
        line-height: 1.5;
    }
    .anular-confirm-text strong {
        color: #b02a37;
        text-transform: uppercase;
        font-weight: 700;
    }

    /* Footer */
    .anular-modal-footer {
        background: #f8f9fa;
        border-top: 1px solid #e8eaf6;
        padding: 16px 24px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    .anular-btn-cancel {
        background: #ffffff;
        border: 1px solid #d0d7de;
        color: #495057;
        font-weight: 600;
        font-size: 13px;
        padding: 8px 18px;
        border-radius: 6px;
    }
    .anular-btn-cancel:hover {
        background: #f1f3f5;
        color: #212529;
    }
    .anular-btn-confirm {
        background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
        border: none;
        color: #ffffff;
        font-weight: 600;
        font-size: 13px;
        padding: 8px 18px;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(176,42,55,0.3);
        transition: all 0.2s ease;
    }
    .anular-btn-confirm:hover:not(:disabled) {
        background: linear-gradient(135deg, #c82333 0%, #941e29 100%);
        box-shadow: 0 4px 10px rgba(176,42,55,0.4);
        transform: translateY(-1px);
    }
    .anular-btn-confirm:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    @media (max-width: 576px) {
        .anular-summary-grid {
            grid-template-columns: 1fr;
        }
        .anular-modal-body {
            padding: 18px 18px;
        }
        .anular-modal-header {
            padding: 16px 18px;
        }
        .anular-header-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
        }
        .anular-modal-header .modal-title {
            font-size: 16px;
        }
    }
CSS
);

$this->registerJs(
    <<<'JS'
$(document).ready(function() {
    console.log('Document ready - Initializing Bootstrap 4 components');

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover',
        placement: 'top',
        html: true,
        container: 'body',
        delay: { show: 300, hide: 100 }
    });

    $('.collapse').on('shown.bs.collapse', function () {
        $('[data-toggle="tooltip"]').tooltip('dispose').tooltip({
            trigger: 'hover',
            placement: 'top',
            html: true,
            container: 'body',
            delay: { show: 300, hide: 100 }
        });
    });

    // Contract card chevron toggle
    $('.contract-card .card-header').on('click', function() {
        var icon = $(this).find('.contract-toggle-icon');
        if (icon.hasClass('fa-chevron-down')) {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    });

    // Character counter for the reason textarea
    $(document).on('input', '#anulado_motivo_shared', function() {
        var len = $(this).val().length;
        $('#anular-charcount').text(len);
    });

    // ============================================================
    // ANULAR: populate + show shared modal
    // ============================================================
    $(document).on('click', '.btn-anular', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $btn = $(this);
        var id       = $btn.data('contrato-id');
        var nro      = $btn.data('contrato-nro');
        var plan     = $btn.data('contrato-plan');
        var clinica  = $btn.data('contrato-clinica');
        var fechaIni = $btn.data('contrato-fecha-ini');
        var fechaVen = $btn.data('contrato-fecha-ven');
        var monto    = $btn.data('contrato-monto');
        var estatus  = $btn.data('contrato-estatus');
        var pendingCuotas = parseInt($btn.data('contrato-pending-cuotas')) || 0;

        console.log('Anular clicked, contract ID:', id);

        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');

        var $form = $('#anular-shared-form');
if ($form.length) {
    $form[0].reset();

    // Use the Yii-generated base action (works in dev AND prod)
    var baseAction = $form.data('base-action');
    if (!baseAction) {
        // Fallback: derive from current URL path
        baseAction = window.location.pathname.replace(/\/contratos\/.*$/, '/contratos/anular');
    }
    var separator = baseAction.indexOf('?') >= 0 ? '&' : '?';
    $form.attr('action', baseAction + separator + 'id=' + id);
}

        $('#anular-modal-id').text(id);
        $('#anular-modal-contrato-id').val(id);
        $('#anular-summary-id').text(id);
        $('#anular-summary-nro').text(nro);
        $('#anular-summary-plan').text(plan);
        $('#anular-summary-clinica').text(clinica);
        $('#anular-summary-fecha-ini').text(fechaIni);
        $('#anular-summary-fecha-ven').text(fechaVen);
        $('#anular-summary-monto').text('$' + monto);

        var statusClassMap = {
            'Registrado': 'badge-primary',
            'Activo': 'badge-success',
            'Anulado': 'badge-danger',
            'Vencido': 'badge-warning',
            'suspendido': 'badge-secondary',
            'Pendiente': 'badge-info'
        };
        var cls = statusClassMap[estatus] || 'badge-light';
        $('#anular-summary-estatus').html('<span class="badge ' + cls + '">' + estatus + '</span>');

        var $banner = $('#anular-warning-banner');
var $icon   = $('#anular-warning-icon');
var $extra  = $('#anular-warning-extra');

if (pendingCuotas > 0) {
    // Escalate banner to red and show extra sentence
    $('#anular-pending-count').text(pendingCuotas);
    $extra.show();
    $banner.addClass('anular-warning-banner-danger');
    $icon.removeClass('fa-exclamation-triangle fa-info-circle')
         .addClass('fa-exclamation-circle');
} else {
    // Reset to amber state
    $extra.hide();
    $banner.removeClass('anular-warning-banner-danger');
    $icon.removeClass('fa-exclamation-circle fa-info-circle')
         .addClass('fa-exclamation-triangle');
}

        $('#anular-charcount').text(0);

        $('#anularModalShared').modal({
            backdrop: 'static',
            keyboard: true,
            show: true
        });

        $('#anularModalShared').off('shown.bs.modal.cleanup').on('shown.bs.modal.cleanup', function() {
    var $dialog = $(this).find('.modal-dialog');
    $dialog.css({
        'transform': 'none',
        'top': 'auto',
        'margin': '20px auto',
        'max-height': 'calc(100vh - 40px)'
    });
    $(this).find('.modal-content').css({
        'max-height': 'calc(100vh - 40px)',
        'display': 'flex',
        'flex-direction': 'column'
    });
    $(this).find('.anular-modal-body').css({
        'overflow-y': 'auto',
        'flex': '1 1 auto',
        'min-height': '0'
    });
});
    });

    $(document).on('shown.bs.modal', '#anularModalShared', function () {
        $(this).find('[data-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            placement: 'top',
            html: true,
            container: 'body'
        });
    });

    // Anular form submission
    $(document).on('submit', '#anular-shared-form', function(e) {
        var motivo = $('#anulado_motivo_shared').val().trim();

        // Soft validation: warn if reason is too short
        if (motivo.length < 10) {
            e.preventDefault();
            alert('Por favor, indique un motivo con al menos 10 caracteres.');
            $('#anulado_motivo_shared').focus();
            return false;
        }

        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin mr-1"></i> Anulando...');
        return true;
    });

    console.log('.btn-anular buttons found:', $('.btn-anular').length);
    console.log('Shared modal found:', $('#anularModalShared').length);
    console.log('Bootstrap modal available:', typeof $.fn.modal === 'function');
});
JS
);
?>