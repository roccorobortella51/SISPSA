<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Cuotas; // Added for Cuota calculations

// Register jQuery and Bootstrap 4 assets
\yii\web\JqueryAsset::register($this);
\yii\bootstrap4\BootstrapAsset::register($this);

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
                    Url::to(['user-datos/update', 'id' => (is_object($afiliado) ? $afiliado->id : '')]),
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
            // Calculate payment statistics for this contract
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

            // Calculate remaining time for contract
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

            // Contract information
            $periodoInfo = Yii::$app->formatter->asDate($model->fecha_ini, 'php:d/m/Y');
            if ($model->fecha_ven) {
                $periodoInfo .= ' - ' . Yii::$app->formatter->asDate($model->fecha_ven, 'php:d/m/Y');
            } else {
                $periodoInfo .= ' - Sin fecha de vencimiento';
            }

            // Determine status color for card header
            $statusColors = [
                'Activo' => 'success',
                'Anulado' => 'danger',
                'Vencido' => 'warning',
                'Registrado' => 'info',
                'Pendiente' => 'primary',
                'suspendido' => 'secondary',
            ];
            $statusColor = $statusColors[$model->estatus] ?? 'secondary';

            // Get status for blinking effect
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

            // Build status tooltip message
            $statusTooltipMsg = '<strong>Estatus del Contrato:</strong> ' . $status;
            if ($status === 'Activo') $statusTooltipMsg .= '<br>✅ El afiliado tiene cobertura completa';
            if ($status === 'suspendido') $statusTooltipMsg .= '<br>⏸️ Contrato suspendido por cuotas vencidas';
            if ($status === 'Anulado') $statusTooltipMsg .= '<br>❌ Contrato cancelado permanentemente';
            if ($status === 'Vencido') $statusTooltipMsg .= '<br>⏰ El contrato ha expirado';
            if ($status === 'Registrado') $statusTooltipMsg .= '<br>📋 Contrato registrado pero aún no activo';
            if ($status === 'Pendiente') $statusTooltipMsg .= '<br>⏳ Contrato pendiente de aprobación';
            ?>

            <!-- Contract Card - Enhanced Design (Compact) -->
            <div class="card contract-card mb-3 shadow-sm border-0" style="border-radius: 10px; overflow: hidden; transition: all 0.3s ease; border-left: 4px solid <?= $model->estatus === 'Activo' ? '#28a745' : ($model->estatus === 'Anulado' ? '#dc3545' : '#0078d4') ?>;">
                <div class="card-header bg-gradient-primary text-white py-1 px-3"
                    style="cursor: pointer; border-bottom: 0; background: linear-gradient(135deg, <?= $model->estatus === 'Activo' ? '#28a745' : ($model->estatus === 'Anulado' ? '#dc3545' : '#0078d4') ?> 0%, <?= $model->estatus === 'Activo' ? '#1e7e34' : ($model->estatus === 'Anulado' ? '#b02a37' : '#005a9e') ?> 100%) !important;"
                    data-toggle="collapse"
                    data-target="#contractDetails<?= $model->id ?>"
                    aria-expanded="false"
                    aria-controls="contractDetails<?= $model->id ?>"
                    title="Haga clic para expandir o contraer los detalles del contrato">

                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Left side: Contract info -->
                        <div class="d-flex align-items-center" style="gap: 12px;">
                            <!-- Expand/Collapse Icon -->
                            <div class="mr-2">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">
                                    <i class="fas fa-chevron-down contract-toggle-icon text-primary" style="font-size: 14px;"></i>
                                </div>
                            </div>

                            <!-- Contract Details - Improved Design -->
                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                <!-- Contract ID with Icon -->
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="text-white-80 small font-weight-medium" style="opacity: 0.7; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Contrato</div>
                                        <h6 class="mb-0 font-weight-bold text-white" style="font-size: 20px; letter-spacing: 0.3px; line-height: 1.2;">
                                            #<?= $model->id ?>
                                        </h6>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                                <!-- Status Badge - Blinking Effect & Larger Font -->
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

                                <!-- Divider -->
                                <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                                <!-- Contract Number -->
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

                        <!-- Right side: Quick stats - Better Spacing -->
                        <?php
                        // Calculate SALDO using the SAME logic as the Cuotas card
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
                            <!-- Contract Amount -->
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

                            <!-- Divider -->
                            <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                            <!-- Payment Count -->
                            <div class="text-right">
                                <div class="text-white-80 small font-weight-medium" style="opacity: 0.7; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-receipt mr-1" style="font-size: 8px;"></i> Pagos
                                </div>
                                <div class="font-weight-bold text-white"
                                    data-toggle="tooltip"
                                    title="<strong>Pagos:</strong> <?= $totalPagos ?> pago(s) registrado(s) para este contrato"
                                    style="font-size: 17px; cursor: help;">
                                    <?= $totalPagos ?>
                                    <small style="font-size: 11px; opacity: 0.6; font-weight: 400;">registr<?= $totalPagos === 1 ? 'o' : 'os' ?></small>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                            <!-- Total Paid -->
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

                            <!-- Divider -->
                            <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>

                            <!-- Balance (SALDO) - Now matches Cuotas card -->
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

                            <!-- Action Indicator -->
                            <div class="ml-1">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; opacity: 0.9; background: rgba(255,255,255,0.15) !important; border: 1px solid rgba(245, 9, 9, 0.2);">
                                    <i class="fas fa-ellipsis-v" style="font-size: 12px; color: #ffffff !important;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract Details (Collapsible) -->
                <div id="contractDetails<?= $model->id ?>" class="collapse">
                    <div class="card-body pt-3 pb-3" style="background: #fafbfc; padding: 12px 16px !important;">
                        <!-- Contract Summary Cards - Enhanced Intelligent Design with Bigger Fonts -->
                        <div class="row mb-3" style="margin: 0 -4px;">
                            <!-- Card 1: PLAN + Cobertura + Clinica - BIGGER & EYE-CATCHING -->
                            <div class="col-md-3 mb-2" style="padding: 0 4px;">
                                <div class="card border-0 h-100 shadow-lg" style="border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden;">
                                    <!-- Animated decorative element -->
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
                                                    title="<strong>Clínica:</strong> <?= Html::encode($model->clinica->nombre) ?>"
                                                    style="cursor: help;">
                                                    <i class="fas fa-hospital mr-1" style="font-size: 11px;"></i> <?= $model->clinica->nombre ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($model->plan): ?>
                                                <div class="d-flex justify-content-between align-items-center mt-2" style="background: rgba(255,255,255,0.12); border-radius: 8px; padding: 6px 12px;">
                                                    <span style="font-size: 14px; color: #ffffff; opacity: 0.95; font-weight: 500;"
                                                        data-toggle="tooltip"
                                                        title="<strong>Precio mensual:</strong> <?= Yii::$app->formatter->asCurrency($model->plan->precio, 'USD') ?> por mes"
                                                        style="cursor: help;">
                                                        <i class="fas fa-dollar-sign mr-1" style="font-size: 12px;"></i> <?= Yii::$app->formatter->asCurrency($model->plan->precio, 'USD') ?>/mes
                                                    </span>
                                                    <span class="badge"
                                                        data-toggle="tooltip"
                                                        title="<strong>Cobertura:</strong> Monto máximo cubierto por el plan: <?= Yii::$app->formatter->asCurrency($model->plan->cobertura, 'USD') ?>"
                                                        style="background: rgba(255,255,255,0.2); color: white; font-size: 11px; padding: 4px 12px; border-radius: 12px; cursor: help;">
                                                        <i class="fas fa-shield-alt mr-1" style="font-size: 10px;"></i> <?= Yii::$app->formatter->asCurrency($model->plan->cobertura, 'USD') ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: TIEMPO (Contract Period) - BIGGER & EYE-CATCHING -->
                            <div class="col-md-3 mb-2" style="padding: 0 4px;">
                                <div class="card border-0 h-100 shadow-lg" style="border-radius: 12px; background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%); color: white; transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden;">
                                    <!-- Animated decorative element -->
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
                                            <div style="font-size: 14px; opacity: 0.9; font-weight: 500;"
                                                data-toggle="tooltip"
                                                title="<strong>Período del Contrato:</strong> Del <?= Yii::$app->formatter->asDate($model->fecha_ini, 'php:d/m/Y') ?> al <?= $model->fecha_ven ? Yii::$app->formatter->asDate($model->fecha_ven, 'php:d/m/Y') : 'Indefinido' ?>"
                                                style="cursor: help;">
                                                <i class="fas fa-play mr-1" style="font-size: 11px;"></i> <?= Yii::$app->formatter->asDate($model->fecha_ini, 'php:d/m/Y') ?>
                                                <span style="margin: 0 6px;">→</span>
                                                <i class="fas fa-flag mr-1" style="font-size: 11px;"></i> <?= $model->fecha_ven ? Yii::$app->formatter->asDate($model->fecha_ven, 'php:d/m/Y') : 'Indefinido' ?>
                                            </div>
                                            <div class="mt-2">
                                                <?php if ($remainingDays !== null): ?>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span style="font-size: 28px; font-weight: bold; color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.1);"
                                                                data-toggle="tooltip"
                                                                title="<strong>Tiempo Restante:</strong> <?= $remainingDays ?> <?= $remainingStatus ?>"
                                                                style="cursor: help;">
                                                                <?= $remainingDays ?>
                                                            </span>
                                                            <span style="font-size: 14px; opacity: 0.8; font-weight: 500; margin-left: 4px;"><?= $remainingStatus ?></span>
                                                        </div>
                                                        <div class="progress" style="width: 40%; height: 6px; background: rgba(255,255,255,0.25); border-radius: 3px;"
                                                            data-toggle="tooltip"
                                                            title="<strong>Progreso del Período:</strong> <?= min(100, round((($totalDays ?? 365) - $remainingDays) / ($totalDays ?? 365) * 100)) ?>% del período completado">
                                                            <?php
                                                            // Calculate progress: days passed / total days
                                                            $totalDays = 365; // Default to 1 year
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

                            <!-- Card 3: Cuotas (Payment Status) - BIGGER FONTS -->
                            <div class="col-md-3 mb-2" style="padding: 0 4px;">
                                <?php
                                // Calculate cuota statistics
                                $cuotasDelContrato = Cuotas::find()->where(['contrato_id' => $model->id])->all();
                                $totalCuotas = count($cuotasDelContrato);
                                $pagadas = 0;
                                $pendientes = 0;
                                $vencidas = 0;
                                $enGracias = 0;
                                $anuladas = 0;
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
                                        case 'anulada':
                                            $anuladas++;
                                            break;
                                    }
                                }
                                $saldoPendiente = $montoTotal - $montoPagado;
                                ?>
                                <div class="card border-0 h-100 shadow-lg" style="border-radius: 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden;">
                                    <!-- Animated decorative element -->
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
                                            <div class="d-flex justify-content-between align-items-center"
                                                data-toggle="tooltip"
                                                title="<strong>Total de Cuotas:</strong> <?= $totalCuotas ?> cuota(s) en este contrato">
                                                <span style="font-size: 13px; opacity: 0.85;">Total</span>
                                                <span class="font-weight-bold" style="font-size: 18px;"><?= $totalCuotas ?></span>
                                            </div>
                                            <div class="row" style="margin: 4px -2px 0;">
                                                <div class="col-6" style="padding: 0 2px;">
                                                    <div class="d-flex justify-content-between align-items-center"
                                                        data-toggle="tooltip"
                                                        title="<strong>Cuotas Pagadas:</strong> <?= $pagadas ?> cuota(s) pagada(s)"
                                                        style="background: rgba(255,255,255,0.12); border-radius: 4px; padding: 3px 8px; cursor: help;">
                                                        <span style="font-size: 11px; opacity: 0.8;">
                                                            <i class="fas fa-check-circle" style="color: #28a745; font-size: 10px;"></i> Pagadas
                                                        </span>
                                                        <span class="font-weight-bold" style="font-size: 15px;"><?= $pagadas ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-6" style="padding: 0 2px;">
                                                    <div class="d-flex justify-content-between align-items-center"
                                                        data-toggle="tooltip"
                                                        title="<strong>Cuotas Pendientes:</strong> <?= $pendientes ?> cuota(s) pendiente(s) de pago"
                                                        style="background: rgba(255,255,255,0.12); border-radius: 4px; padding: 3px 8px; cursor: help;">
                                                        <span style="font-size: 11px; opacity: 0.8;">
                                                            <i class="fas fa-clock" style="color: #ffc107; font-size: 10px;"></i> Pendientes
                                                        </span>
                                                        <span class="font-weight-bold" style="font-size: 15px;"><?= $pendientes ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-6" style="padding: 0 2px; margin-top: 3px;">
                                                    <div class="d-flex justify-content-between align-items-center"
                                                        data-toggle="tooltip"
                                                        title="<strong>Cuotas Vencidas:</strong> <?= $vencidas ?> cuota(s) vencida(s) - Requieren pago inmediato"
                                                        style="background: rgba(255,255,255,0.12); border-radius: 4px; padding: 3px 8px; cursor: help;">
                                                        <span style="font-size: 11px; opacity: 0.8;">
                                                            <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 10px;"></i> Vencidas
                                                        </span>
                                                        <span class="font-weight-bold" style="font-size: 15px;"><?= $vencidas ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-6" style="padding: 0 2px; margin-top: 3px;">
                                                    <div class="d-flex justify-content-between align-items-center"
                                                        data-toggle="tooltip"
                                                        title="<strong>Cuotas en Período de Gracia:</strong> <?= $enGracias ?> cuota(s) con período de gracia de 7 días"
                                                        style="background: rgba(255,255,255,0.12); border-radius: 4px; padding: 3px 8px; cursor: help;">
                                                        <span style="font-size: 11px; opacity: 0.8;">
                                                            <i class="fas fa-hourglass-half" style="color: #17a2b8; font-size: 10px;"></i> En Gracias
                                                        </span>
                                                        <span class="font-weight-bold" style="font-size: 15px;"><?= $enGracias ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($saldoPendiente > 0): ?>
                                                <div class="mt-2" style="border-top: 1px solid rgba(255,255,255,0.15); padding-top: 3px;">
                                                    <div class="d-flex justify-content-between align-items-center"
                                                        data-toggle="tooltip"
                                                        title="<strong>Saldo Pendiente:</strong> <?= number_format($saldoPendiente, 2) ?> USD pendiente de pago">
                                                        <span style="font-size: 12px; opacity: 0.8; font-weight: 500;">Saldo pendiente</span>
                                                        <span class="font-weight-bold" style="font-size: 16px; color: #ffc107;">$ <?= number_format($saldoPendiente, 2) ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 4: Resumen Inteligente (Intelligent Summary) - BIGGER FONTS -->
                            <div class="col-md-3 mb-2" style="padding: 0 4px;">
                                <div class="card border-0 h-100 shadow-lg" style="border-radius: 12px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; transition: transform 0.3s ease, box-shadow 0.3s ease; position: relative; overflow: hidden;">
                                    <!-- Animated decorative element -->
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
                                            <!-- Contract Status -->
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span style="font-size: 12px; opacity: 0.85;">Estado</span>
                                                <?php
                                                // Status colors for the badge
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
                                                <span style="background: <?= $statusBgColor ?>; color: white; font-size: 12px; padding: 4px 14px; border-radius: 20px; font-weight: 600; display: inline-block; box-shadow: 0 2px 8px rgba(0,0,0,0.15);"
                                                    data-toggle="tooltip"
                                                    title="<strong>Estado Actual:</strong> <?= $model->estatus ?: 'Registrado' ?>"
                                                    style="cursor: help;">
                                                    <?= $model->estatus ?: 'Registrado' ?>
                                                </span>
                                            </div>

                                            <!-- Last Payment -->
                                            <?php
                                            $lastPayment = null;
                                            if (!empty($pagosDelContrato)) {
                                                $lastPayment = $pagosDelContrato[0];
                                            }
                                            ?>
                                            <div class="d-flex justify-content-between align-items-center mt-2"
                                                data-toggle="tooltip"
                                                title="<?php if ($lastPayment): ?><strong>Último Pago:</strong> <?= Yii::$app->formatter->asDate($lastPayment->fecha_pago, 'php:d/m/Y') ?> por $<?= number_format($lastPayment->monto_pagado, 2) ?><?php else: ?>No hay pagos registrados para este contrato<?php endif; ?>">
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

                                            <!-- Next Payment Due -->
                                            <?php
                                            $nextCuota = Cuotas::find()
                                                ->where(['contrato_id' => $model->id])
                                                ->andWhere(['in', 'estatus', ['pendiente', 'en_gracias']])
                                                ->orderBy(['fecha_vencimiento' => SORT_ASC])
                                                ->one();
                                            ?>
                                            <div class="d-flex justify-content-between align-items-center mt-1"
                                                data-toggle="tooltip"
                                                title="<?php if ($nextCuota): ?><strong>Próximo Vencimiento:</strong> <?= Yii::$app->formatter->asDate($nextCuota->fecha_vencimiento, 'php:d/m/Y') ?> por $<?= number_format($nextCuota->monto, 2) ?><?php else: ?>✓ No hay cuotas pendientes - El contrato está al día<?php endif; ?>">
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

                                            <!-- Quick Stats -->
                                            <div class="mt-2" style="border-top: 1px solid rgba(255,255,255,0.15); padding-top: 3px;">
                                                <div class="d-flex justify-content-between align-items-center"
                                                    data-toggle="tooltip"
                                                    title="<strong>Total Pagado:</strong> $<?= number_format($montoPagado, 2) ?> de $<?= number_format($montoTotal, 2) ?>">
                                                    <span style="font-size: 12px; opacity: 0.8;">
                                                        <i class="fas fa-file-invoice-dollar mr-1"></i> Total pagado
                                                    </span>
                                                    <span class="font-weight-bold" style="font-size: 16px;">$ <?= number_format($montoPagado, 2) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-1"
                                                    data-toggle="tooltip"
                                                    title="<strong>Progreso del Pago:</strong> <?= $montoTotal > 0 ? min(100, round(($montoPagado / $montoTotal) * 100)) : 0 ?>% del contrato ha sido pagado">
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
                            <!-- Eye-catching Header Section with Accordion Style - LARGER FONTS -->
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
                                        <?php if ($totalPagos > 1): ?>
                                            <div class="collapse-arrow ml-2" style="cursor: pointer;" data-toggle="collapse" data-target="#paymentHistoryCollapse<?= $model->id ?>" aria-expanded="true" title="Expandir/Contraer historial de pagos">
                                                <div style="background: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                                    <i class="fas fa-chevron-up" style="color: #0078d4; font-size: 14px;"></i>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($pagosDelContrato)): ?>
                                <div class="collapse show" id="paymentHistoryCollapse<?= $model->id ?>">
                                    <div class="card border-0 shadow-sm" style="border-radius: 0 0 8px 8px; overflow: hidden; border-top: none;">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0" style="font-size: 14px;">
                                                <thead style="background: #0078d4 !important; border-bottom: 2px solid #005a9e;">
                                                    <tr>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 50px; border-bottom: 2px solid #005a9e; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Número secuencial del pago">#</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 100px; border-bottom: 2px solid #005a9e; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Fecha en que se realizó el pago">Fecha</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 110px; border-bottom: 2px solid #005a9e; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Monto pagado en dólares (USD)">Monto USD</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 110px; border-bottom: 2px solid #005a9e; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Monto equivalente en bolívares (Bs)">Monto Bs</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 90px; border-bottom: 2px solid #005a9e; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Tasa de cambio aplicada en el pago">Tasa</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 100px; border-bottom: 2px solid #005a9e; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Método utilizado para realizar el pago">Método</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 120px; border-bottom: 2px solid #005a9e; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Número de referencia o comprobante del pago">Referencia</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 100px; border-bottom: 2px solid #005a9e; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Comprobante digital del pago (imagen)">Comprobante</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 110px; border-bottom: 2px solid #005a9e; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Estado actual del pago en el sistema">Estatus</th>
                                                        <th class="text-center align-middle font-weight-bold" style="color: #ffffff !important; padding: 12px 8px; width: 100px; border-bottom: 2px solid #005a9e; background: #0078d4 !important; font-size: 13px;" data-toggle="tooltip" title="Acciones disponibles para este pago">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $paymentCounter = 1; ?>
                                                    <?php foreach ($pagosDelContrato as $pago): ?>
                                                        <?php
                                                        // Calculate Tasa = Monto Bs / Monto USD
                                                        $tasaCalculada = '';
                                                        if ($pago->monto_pagado > 0 && $pago->monto_usd > 0) {
                                                            $tasaCalculada = number_format($pago->monto_usd / $pago->monto_pagado, 2);
                                                        } elseif ($pago->tasa) {
                                                            $tasaCalculada = number_format($pago->tasa, 2);
                                                        } else {
                                                            $tasaCalculada = 'N/A';
                                                        }

                                                        // Alternating row colors - Even vs Odd
                                                        $rowClass = ($paymentCounter % 2 == 0) ? 'payment-even' : 'payment-odd';
                                                        $rowBgColor = ($paymentCounter % 2 == 0) ? '#b9cbde' : '#ffffff';
                                                        ?>
                                                        <tr class="<?= $rowClass ?>" style="border-bottom: 1px solid #e8edf2; transition: all 0.2s ease;">
                                                            <td class="text-center align-middle" style="background-color: <?= $rowBgColor ?>; padding: 8px 6px;">
                                                                <span class="badge" style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); color: white; padding: 5px 10px; border-radius: 6px; font-size: 13px; min-width: 35px; display: inline-block;">
                                                                    <?= str_pad($paymentCounter, 2, '0', STR_PAD_LEFT) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle" style="background-color: <?= $rowBgColor ?>; padding: 8px 6px;" data-toggle="tooltip" title="Fecha del pago: <?= Yii::$app->formatter->asDate($pago->fecha_pago, 'php:d/m/Y') ?>">
                                                                <span class="font-weight-medium text-dark" style="font-size: 14px;">
                                                                    <i class="fas fa-calendar-day text-muted mr-1" style="font-size: 12px;"></i>
                                                                    <?= Yii::$app->formatter->asDate($pago->fecha_pago, 'php:d/m/Y') ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle" style="background-color: <?= $rowBgColor ?>; padding: 8px 6px;" data-toggle="tooltip" title="Monto pagado: $<?= Yii::$app->formatter->asDecimal($pago->monto_pagado, 2) ?> USD">
                                                                <span class="font-weight-bold text-success" style="font-size: 17px;">
                                                                    <i class="fas fa-dollar-sign mr-1" style="font-size: 12px;"></i>
                                                                    <?= Yii::$app->formatter->asDecimal($pago->monto_pagado, 2) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle" style="background-color: <?= $rowBgColor ?>; padding: 8px 6px;" data-toggle="tooltip" title="Monto en bolívares: <?= Yii::$app->formatter->asDecimal($pago->monto_usd, 2) ?> Bs">
                                                                <span class="font-weight-bold text-primary" style="font-size: 17px;">
                                                                    <i class="fas fa-chart-line mr-1" style="font-size: 12px;"></i>
                                                                    <?= Yii::$app->formatter->asDecimal($pago->monto_usd, 2) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle" style="background-color: <?= $rowBgColor ?>; padding: 8px 6px;" data-toggle="tooltip" title="Tasa de cambio: <?= $tasaCalculada ?>">
                                                                <?php if ($tasaCalculada !== 'N/A'): ?>
                                                                    <span class="badge" style="background-color: #e9ecef; color: #495057; padding: 4px 8px; font-size: 12px; font-weight: 600; border-radius: 4px;">
                                                                        <i class="fas fa-exchange-alt mr-1" style="font-size: 10px;"></i> <?= $tasaCalculada ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted small" style="font-size: 12px;">N/A</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center align-middle" style="background-color: <?= $rowBgColor ?>; padding: 8px 6px;" data-toggle="tooltip" title="Método de pago: <?= $pago->metodo_pago ?: 'N/A' ?>">
                                                                <?php
                                                                $methods = [
                                                                    'transferencia' => '<span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-university mr-1" style="font-size: 10px;"></i>Transferencia</span>',
                                                                    'transferencia_bancaria' => '<span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-university mr-1" style="font-size: 10px;"></i>Transferencia</span>',
                                                                    'efectivo' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-money-bill-wave mr-1" style="font-size: 10px;"></i>Efectivo</span>',
                                                                    'efectivo_dolar' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-dollar-sign mr-1" style="font-size: 10px;"></i>Efectivo USD</span>',
                                                                    'Efectivo - Dólar ($)' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-dollar-sign mr-1" style="font-size: 10px;"></i>Efectivo USD</span>',
                                                                    'pago_movil' => '<span class="badge" style="background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-mobile-alt mr-1" style="font-size: 10px;"></i>Pago Móvil</span>',
                                                                    'pagomovil' => '<span class="badge" style="background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fas fa-mobile-alt mr-1" style="font-size: 10px;"></i>Pago Móvil</span>',
                                                                    'zelle' => '<span class="badge" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fab fa-zelle mr-1" style="font-size: 10px;"></i>Zelle</span>',
                                                                    'paypal' => '<span class="badge" style="background: linear-gradient(135deg, #003087 0%, #001f6b 100%); color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px;"><i class="fab fa-paypal mr-1" style="font-size: 10px;"></i>PayPal</span>',
                                                                ];
                                                                $methodKey = strtolower(trim($pago->metodo_pago));
                                                                echo isset($methods[$methodKey]) ? $methods[$methodKey] : '<span class="badge badge-secondary" style="font-size: 12px; padding: 5px 12px;">' . ($pago->metodo_pago ?: 'N/A') . '</span>';
                                                                ?>
                                                            </td>
                                                            <td class="text-center align-middle" style="background-color: <?= $rowBgColor ?>; padding: 8px 6px;" data-toggle="tooltip" title="<?= $pago->numero_referencia_pago ? 'Número de referencia: ' . $pago->numero_referencia_pago : 'Sin número de referencia' ?>">
                                                                <?php if ($pago->numero_referencia_pago): ?>
                                                                    <code class="font-monospace small" style="background: #f5f5f5; padding: 3px 8px; border-radius: 4px; font-size: 12px;">
                                                                        <i class="fas fa-hashtag mr-1" style="color: #6c757d; font-size: 10px;"></i>
                                                                        <?= $pago->numero_referencia_pago ?>
                                                                    </code>
                                                                <?php else: ?>
                                                                    <span class="text-muted small" style="font-size: 12px;"><i class="fas fa-ban mr-1" style="font-size: 10px;"></i>Sin ref.</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center align-middle" style="background-color: <?= $rowBgColor ?>; padding: 8px 6px;">
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
                                                            <td class="text-center align-middle" style="background-color: <?= $rowBgColor ?>; padding: 8px 6px;">
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
                                                            <!-- ========== ACCIONES COLUMN - LARGER ICONS ========== -->
                                                            <td class="text-center align-middle" style="background-color: <?= $rowBgColor ?>; padding: 4px 2px; width: 100px;">
                                                                <div class="d-flex align-items-center justify-content-center gap-1 flex-nowrap" style="gap: 3px;">
                                                                    <!-- View button -->
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

                                                                    <!-- Edit button -->
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

                                                                    <!-- ========== RECEIPT BUTTONS ========== -->
                                                                    <?php
                                                                    // Get receipts for this payment
                                                                    $receipts = \app\components\ReceiptGenerator::getReceiptsForPayment($pago->id);

                                                                    if (!empty($receipts)):
                                                                    ?>
                                                                        <?php if (count($receipts) == 1): ?>
                                                                            <!-- Single receipt - direct print -->
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
                                                                            <!-- Multiple receipts - dropdown -->
                                                                            <div class="btn-group" style="display: inline-flex;">
                                                                                <?= Html::a(
                                                                                    '<i class="fas fa-receipt" style="font-size: 18px; color: #1e7e34;"></i>',
                                                                                    '#',
                                                                                    [
                                                                                        'title' => 'Ver/Imprimir Recibos (' . count($receipts) . ')',
                                                                                        'data-toggle' => 'tooltip',
                                                                                        'class' => 'action-icon dropdown-toggle',
                                                                                        'data-toggle' => 'dropdown',
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
                                                                        <!-- No receipts - show visible icon with generate button -->
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
                                                        <td class="text-center font-weight-bold text-success" style="padding: 12px; font-size: 18px;" data-toggle="tooltip" title="Total pagado en USD: $<?= number_format(array_sum(array_map(function ($p) {
                                                                                                                                                    return $p->monto_pagado;
                                                                                                                                                }, $pagosDelContrato)), 2) ?>">
                                                            <i class="fas fa-dollar-sign mr-1" style="font-size: 13px;"></i> <?= number_format(array_sum(array_map(function ($p) {
                                                                                                                                    return $p->monto_pagado;
                                                                                                                                }, $pagosDelContrato)), 2) ?>
                                                        </td>
                                                        <td class="text-center font-weight-bold text-primary" style="padding: 12px; font-size: 18px;" data-toggle="tooltip" title="Total pagado en Bs: <?= number_format(array_sum(array_map(function ($p) {
                                                                                                                                return $p->monto_usd;
                                                                                                                            }, $pagosDelContrato)), 2) ?> Bs">
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
                                                <div class="card border-0 shadow-sm"
                                                    data-toggle="tooltip"
                                                    title="<strong>Total de Pagos:</strong> <?= $totalPagos ?> pago(s) registrado(s)"
                                                    style="border-radius: 8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); cursor: help;">
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
                                                <div class="card border-0 shadow-sm"
                                                    data-toggle="tooltip"
                                                    title="<strong>Total en USD:</strong> $<?= number_format(array_sum(array_map(function ($p) {
                                                                                                                            return $p->monto_pagado;
                                                                                                                        }, $pagosDelContrato)), 2) ?>"
                                                    style="border-radius: 8px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); cursor: help;">
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
                                                <div class="card border-0 shadow-sm"
                                                    data-toggle="tooltip"
                                                    title="<strong>Total en Bs:</strong> <?= number_format(array_sum(array_map(function ($p) {
                                                                                                                            return $p->monto_usd;
                                                                                                                        }, $pagosDelContrato)), 2) ?> Bs"
                                                    style="border-radius: 8px; background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%); cursor: help;">
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
                                                <div class="card border-0 shadow-sm"
                                                    data-toggle="tooltip"
                                                    title="<strong>Promedio por Pago:</strong> $<?= $totalPagos > 0 ? number_format(array_sum(array_map(function ($p) {
                                                                                                                                return $p->monto_pagado;
                                                                                                                            }, $pagosDelContrato)) / $totalPagos, 2) : '0.00' ?>"
                                                    style="border-radius: 8px; background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); cursor: help;">
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
                                <!-- Empty State -->
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
                                        <?= Html::a(
                                            '<i class="fas fa-ban mr-1" style="font-size: 10px;"></i> Anular',
                                            ['contratos/anular-form', 'id' => $model->id],
                                            [
                                                'class' => 'btn btn-danger btn-sm',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#anularModal' . $model->id,
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

            <!-- ANULAR MODAL for each contract -->
            <div class="modal fade" id="anularModal<?= $model->id ?>" tabindex="-1" role="dialog" aria-labelledby="anularModalLabel<?= $model->id ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="anularModalLabel<?= $model->id ?>">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Anular Contrato #<?= $model->id ?>
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <?= Html::beginForm(['contratos/anular', 'id' => $model->id], 'post', [
                            'id' => 'anular-form-' . $model->id,
                            'class' => 'anular-form'
                        ]) ?>

                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>¡Atención!</strong> Está a punto de anular este contrato. Esta acción no se puede deshacer.
                            </div>

                            <!-- Contract Summary -->
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">Resumen del Contrato</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th>Contrato #:</th>
                                                    <td><?= $model->id ?></td>
                                                </tr>
                                                <tr>
                                                    <th>N° Contrato:</th>
                                                    <td><?= $model->nrocontrato ?: 'N/A' ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Plan:</th>
                                                    <td><?= $model->plan ? $model->plan->nombre : 'N/A' ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Clínica:</th>
                                                    <td><?= $model->clinica ? $model->clinica->nombre : 'N/A' ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th>Fecha Inicio:</th>
                                                    <td><?= Yii::$app->formatter->asDate($model->fecha_ini, 'php:d/m/Y') ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Fecha Vencimiento:</th>
                                                    <td><?= $model->fecha_ven ? Yii::$app->formatter->asDate($model->fecha_ven, 'php:d/m/Y') : 'N/A' ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Monto:</th>
                                                    <td>$<?= number_format($model->monto, 2) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Estatus Actual:</th>
                                                    <td>
                                                        <?php
                                                        $statusClass = [
                                                            'Registrado' => 'badge-primary',
                                                            'Activo' => 'badge-success',
                                                            'Vencido' => 'badge-warning',
                                                            'suspendido' => 'badge-secondary',
                                                            'Pendiente' => 'badge-info',
                                                        ];
                                                        $class = $statusClass[$model->estatus] ?? 'badge-light';
                                                        echo '<span class="badge ' . $class . '">' . $model->estatus . '</span>';
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pending Cuotas Warning -->
                            <?php
                            $pendingCuotas = \app\models\Cuotas::find()
                                ->where(['contrato_id' => $model->id])
                                ->andWhere(['estatus' => 'pendiente'])
                                ->count();

                            if ($pendingCuotas > 0):
                            ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <strong>¡Importante!</strong> Este contrato tiene <strong><?= $pendingCuotas ?></strong> cuota(s) pendiente(s) de pago.
                                    Al anular el contrato, estas cuotas quedarán sin efecto.
                                </div>
                            <?php endif; ?>

                            <!-- Reason for annulment -->
                            <div class="form-group">
                                <label for="anulado_motivo_<?= $model->id ?>" class="font-weight-bold required-field">
                                    Motivo de la Anulación <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    name="anulado_motivo"
                                    id="anulado_motivo_<?= $model->id ?>"
                                    class="form-control"
                                    rows="4"
                                    required
                                    placeholder="Indique la razón por la cual se anula este contrato..."></textarea>
                                <small class="form-text text-muted">
                                    Este motivo quedará registrado permanentemente en el sistema.
                                </small>
                            </div>

                            <!-- Confirmation checkbox -->
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="confirm_<?= $model->id ?>" required>
                                <label class="form-check-label font-weight-bold text-danger" for="confirm_<?= $model->id ?>">
                                    Confirmo que deseo ANULAR este contrato y entiendo que esta acción es irreversible
                                </label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-danger" id="submit-anular-<?= $model->id ?>">
                                <i class="fas fa-ban mr-1"></i> Sí, Anular Contrato
                            </button>
                        </div>

                        <?= Html::endForm() ?>
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

<!-- JavaScript for Bootstrap 4 interactions and Collapsible functionality -->
<?php
// Register Bootstrap 4 JavaScript properly
\yii\bootstrap4\BootstrapPluginAsset::register($this);

$this->registerCss(
    <<<CSS
    /* Microsoft Fluent Design System */
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
    
    /* Card styling with subtle borders and shadows */
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1);
        transition: box-shadow 0.2s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    /* Contract Card Hover Effect */
    .contract-card {
        transition: all 0.3s ease !important;
    }
    
    .contract-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
    }
    
    .contract-card .card-header {
        transition: all 0.3s ease !important;
    }
    
    .contract-card .card-header:hover {
        filter: brightness(1.08) !important;
    }
    
    /* Microsoft-inspired badge styling */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        padding: 4px 10px;
        border-radius: 12px;
    }
    
    /* Button styling with Fluent Design */
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
    
    /* Status badges specific colors */
    .badge-success {
        background: linear-gradient(135deg, var(--ms-green) 0%, #0b5e0b 100%);
    }
    
    .badge-danger {
        background: linear-gradient(135deg, var(--ms-red) 0%, #a80000 100%);
    }
    
    .badge-warning {
        background: linear-gradient(135deg, var(--ms-yellow) 0%, #e6a700 100%);
        color: #212529;
    }
    
    .badge-info {
        background: linear-gradient(135deg, var(--ms-blue) 0%, var(--ms-blue-dark) 100%);
    }
    
    /* Form control styling */
    .form-control {
        border-radius: 4px;
        border: 1px solid var(--ms-gray-200);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    
    .form-control:focus {
        border-color: var(--ms-blue);
        box-shadow: 0 0 0 2px rgba(0,120,212,0.25);
    }
    
    /* Modal styling */
    .modal-content {
        border: none;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }
    
    .modal-header {
        border-bottom: 1px solid var(--ms-gray-200);
        padding: 14px 20px;
    }
    
    .modal-footer {
        border-top: 1px solid var(--ms-gray-200);
        padding: 14px 20px;
    }
    
    /* Page header styling */
    .card-header.bg-primary {
        background: linear-gradient(135deg, var(--ms-blue) 0%, var(--ms-blue-dark) 100%) !important;
        border-bottom: none;
    }
    
    /* Icon circles */
    .rounded-circle {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Code styling for reference numbers */
    code {
        background: var(--ms-gray-100);
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 9px;
    }
    
    /* Payment Table Alternating Row Colors - Enhanced */
    .payment-even {
        background-color: #e8f0fe !important;
    }
    
    .payment-odd {
        background-color: #ffffff !important;
    }
    
    .payment-even:hover td {
        background-color: #d4e2fa !important;
    }
    
    .payment-odd:hover td {
        background-color: #f5f7fa !important;
    }
    
    /* Subtle row transition */
    .table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #e8edf2 !important;
    }
    
    .table tbody tr:hover {
        transform: scale(1.001);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        position: relative;
        z-index: 1;
    }
    
    /* Professional Action Icons - Hover Effects */
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
        text-decoration: none !important;
    }
    
    .action-icon .text-info:hover {
        color: #0b5e7a !important;
    }
    
    .action-icon .text-warning:hover {
        color: #b8860b !important;
    }
    
    .action-icon .text-success:hover {
        color: #0d5e0d !important;
    }
    
    .action-icon .text-primary:hover {
        color: #003d7a !important;
    }
    
    .action-icon.disabled {
        cursor: not-allowed !important;
        opacity: 1 !important;
    }
    
    .action-icon.disabled:hover {
        background-color: transparent !important;
        transform: none !important;
    }
    
    /* Force tfoot background */
    tfoot tr {
        background: linear-gradient(135deg, #e8f0fe 0%, #d4e2fa 100%) !important;
    }
    
    tfoot tr td {
        background: transparent !important;
    }
    
    tfoot tr:last-child {
        background: linear-gradient(135deg, #f8fbff 0%, #eef4fa 100%) !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .display-4 {
            font-size: 2rem;
        }
        
        .btn-sm {
            padding: 2px 6px;
            font-size: 9px;
        }
        
        .table {
            font-size: 10px;
        }
    }
    
    /* Microsoft-themed scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    
    ::-webkit-scrollbar-track {
        background: var(--ms-gray-100);
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: var(--ms-gray-300);
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: var(--ms-gray-200);
    }

    /* ===== BLINKING STATUS ANIMATION ===== */
    @keyframes blinkStatus {
        0% {
            opacity: 1;
            transform: scale(1);
            box-shadow: 0 0 10px rgba(255,255,255,0.1);
        }
        25% {
            opacity: 1;
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(255,255,255,0.3);
        }
        50% {
            opacity: 1;
            transform: scale(1);
            box-shadow: 0 0 10px rgba(255,255,255,0.1);
        }
        75% {
            opacity: 1;
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(255,255,255,0.3);
        }
        100% {
            opacity: 1;
            transform: scale(1);
            box-shadow: 0 0 10px rgba(255,255,255,0.1);
        }
    }

    .status-blink {
        animation: blinkStatus 1.5s ease-in-out infinite !important;
        position: relative;
        z-index: 2;
    }

    /* Optional: Different blink speeds for different statuses */
    .badge-success.status-blink {
        animation-duration: 2s !important;
    }
    
    .badge-danger.status-blink {
        animation-duration: 0.8s !important;
        animation-timing-function: ease-in-out !important;
    }
    
    .badge-warning.status-blink {
        animation-duration: 1.2s !important;
    }
    
    .badge-info.status-blink {
        animation-duration: 1.8s !important;
    }
    
    /* ===== CUSTOM TOOLTIP STYLING ===== */
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
    
    .tooltip-inner strong {
        color: #ffc107 !important;
        font-weight: 700 !important;
    }
    
    .tooltip-inner i {
        margin-right: 4px !important;
    }
    
    .tooltip.bs-tooltip-top .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="top"] .arrow::before {
        border-top-color: #1a237e !important;
    }
    
    .tooltip.bs-tooltip-bottom .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="bottom"] .arrow::before {
        border-bottom-color: #1a237e !important;
    }
    
    .tooltip.bs-tooltip-left .arrow::before,
    .tooltip.bs-tooltip-auto[x-placement^="left"] .arrow::before {
        border-left-color: #1a237e !important;
    }
    
    .tooltip.bs-tooltip-auto[x-placement^="right"] .arrow::before,
    .tooltip.bs-tooltip-right .arrow::before {
        border-right-color: #1a237e !important;
    }
CSS
);

$this->registerJs(
    <<<JS
$(document).ready(function() {
    console.log('Document ready - Initializing Bootstrap 4 components');
    
    // ============================================================
    // Initialize Bootstrap tooltips PROPERLY
    // ============================================================
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover',
        placement: 'top',
        html: true,
        container: 'body',
        delay: { show: 300, hide: 100 }
    });
    
    // Re-initialize tooltips after collapse events (since elements may be hidden)
    $('.collapse').on('shown.bs.collapse', function () {
        $('[data-toggle="tooltip"]').tooltip('dispose').tooltip({
            trigger: 'hover',
            placement: 'top',
            html: true,
            container: 'body',
            delay: { show: 300, hide: 100 }
        });
    });
    
    // ============================================================
    // Handle Anular button click properly
    // ============================================================
    $(document).on('click', '[data-toggle="modal"][data-target^="#anularModal"]', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        console.log('Opening modal: ' + target);
        
        if ($(target).length > 0) {
            if (!$(target).data('bs.modal')) {
                $(target).modal({
                    backdrop: 'static',
                    keyboard: true
                });
            }
            $(target).modal('show');
        } else {
            console.error('Modal not found: ' + target);
            var url = $(this).attr('href');
            if (url && url !== '#') {
                window.location.href = url;
            }
        }
    });
    
    // ============================================================
    // Ensure modals are properly initialized
    // ============================================================
    $('.modal').each(function() {
        var modalId = '#' + $(this).attr('id');
        if ($(modalId).data('bs.modal')) {
            $(modalId).modal('dispose');
        }
        $(modalId).modal({
            show: false,
            backdrop: 'static',
            keyboard: true
        });
    });
    
    // ============================================================
    // Handle contract card toggle animation
    // ============================================================
    $('.contract-card .card-header').on('click', function() {
        const icon = $(this).find('.contract-toggle-icon');
        if (icon.hasClass('fa-chevron-down')) {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    });

    // ============================================================
    // Toggle arrow icon when collapsing/expanding payment history
    // ============================================================
    $('[id^="paymentHistoryCollapse"]').on('show.bs.collapse', function () {
        $(this).closest('.mb-4').find('.collapse-arrow i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        $(this).closest('.mb-4').find('.collapse-arrow div').css('transform', 'rotate(0deg)');
    });
    
    $('[id^="paymentHistoryCollapse"]').on('hide.bs.collapse', function () {
        $(this).closest('.mb-4').find('.collapse-arrow i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        $(this).closest('.mb-4').find('.collapse-arrow div').css('transform', 'rotate(180deg)');
    });
    
    // ============================================================
    // Hover effect for collapse arrow
    // ============================================================
    $('.collapse-arrow').hover(
        function() {
            $(this).find('div').css({
                'background': '#e9ecef',
                'transform': 'scale(1.1)',
                'box-shadow': '0 4px 10px rgba(0,0,0,0.15)'
            });
        },
        function() {
            $(this).find('div').css({
                'background': '#f8f9fa',
                'transform': 'scale(1)',
                'box-shadow': '0 2px 5px rgba(0,0,0,0.1)'
            });
        }
    );
    
    // ============================================================
    // Handle anular form submission
    // ============================================================
    $(document).on('submit', '.anular-form', function() {
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin mr-1"></i> Anulando...');
        
        return true;
    });

    // Debug
    if (typeof $.fn.modal === 'function') {
        console.log('Bootstrap modal function is available');
    } else {
        console.error('Bootstrap modal function is NOT available');
    }
});
JS
);
?>
