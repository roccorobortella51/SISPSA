<?php

use yii\helpers\Html;
use yii\helpers\Url;

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
                        ['class' => 'btn btn-light btn-sm mr-2']
                    ) ?>
                <?php endif; ?>

                <?= Html::a(
                    '<i class="fas fa-undo-alt mr-2"></i> Volver',
                    Url::to(['user-datos/update', 'id' => (is_object($afiliado) ? $afiliado->id : '')]),
                    ['class' => 'btn btn-light btn-sm']
                ); ?>
            </div>
        </div>
        <?php if ($afiliado_datos && $dataProvider): ?>
            <div class="card-body bg-light py-3">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle p-3 mr-3">
                                <i class="fas fa-user fa-lg"></i>
                            </div>
                            <div>
                                <label class="text-muted small d-block mb-1">Afiliado</label>
                                <div class="font-weight-bold text-dark h5 mb-0"><?= $afiliado_datos ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="text-center">
                            <div class="text-muted small mb-1">Total Contratos</div>
                            <div class="font-weight-bold text-primary display-4">
                                <?= $dataProvider ? $dataProvider->getTotalCount() : 0 ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="text-muted small mb-1">Contratos Activos</div>
                            <div class="font-weight-bold text-success display-4">
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
            ?>

            <!-- Contract Card -->
            <div class="card contract-card mb-4 border-left-<?= $model->estatus === 'Anulado' ? 'danger' : ($model->estatus === 'Activo' ? 'success' : 'secondary') ?>">
                <div class="card-header bg-gradient-primary text-white py-1"
                    style="cursor: pointer; border-bottom: 0;"
                    data-toggle="collapse"
                    data-target="#contractDetails<?= $model->id ?>"
                    aria-expanded="false"
                    aria-controls="contractDetails<?= $model->id ?>">

                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Left side: Contract info -->
                        <div class="d-flex align-items-center">
                            <!-- Expand/Collapse Icon -->
                            <div class="mr-3">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fas fa-chevron-down contract-toggle-icon text-primary"></i>
                                </div>
                            </div>

                            <!-- Contract Details -->
                            <div>
                                <!-- First row: Contract number and status -->
                                <div class="d-flex align-items-center mb-1">
                                    <h5 class="mb-0 mr-3 font-weight-bold text-white">
                                        <i class="fas fa-file-contract mr-2"></i>Contrato #<?= $model->id ?>
                                    </h5>
                                    <div class="status-badge">
                                        <?php
                                        // Status badges with appropriate colors for dark background
                                        $status = $model->estatus ?: 'Registrado';
                                        $badgeClasses = [
                                            'Registrado' => 'badge-info',
                                            'Activo' => 'badge-success',
                                            'Anulado' => 'badge-danger',
                                            'Vencido' => 'badge-warning',
                                            'Pendiente' => 'badge-primary',
                                            'suspendido' => 'badge-secondary',
                                        ];
                                        $class = $badgeClasses[$status] ?? 'badge-light';
                                        echo '<span class="badge ' . $class . ' font-weight-bold text-white">' . $status . '</span>';
                                        ?>
                                    </div>
                                </div>

                                <!-- Second row: Additional info -->
                                <div class="d-flex flex-wrap">
                                    <!-- Period -->
                                    <div class="mr-3 mb-1">
                                        <small class="text-white-80">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            <?= $periodoInfo ?>
                                        </small>
                                    </div>

                                    <!-- Plan -->
                                    <?php if ($model->plan): ?>
                                        <div class="mr-3 mb-1">
                                            <small class="text-white-80">
                                                <i class="fas fa-tag mr-1"></i>
                                                <?= $model->plan->nombre ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Clinic -->
                                    <?php if ($model->clinica): ?>
                                        <div class="mb-1">
                                            <small class="text-white-80">
                                                <i class="fas fa-hospital mr-1"></i>
                                                <?= $model->clinica->nombre ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Right side: Quick stats -->
                        <div class="d-flex align-items-center">
                            <!-- Total Amount -->
                            <div class="text-right mr-4">
                                <div class="text-white-80 small font-weight-medium">Monto Total</div>
                                <div class="font-weight-bold text-white h5 mb-0">
                                    <?= $model->monto ? Yii::$app->formatter->asCurrency($model->monto, 'USD') : 'N/A' ?>
                                </div>
                            </div>

                            <!-- Payment Count -->
                            <div class="text-right mr-3">
                                <div class="text-white-80 small font-weight-medium">Pagos</div>
                                <div class="font-weight-bold text-white h5 mb-0">
                                    <?= $totalPagos ?> registr<?= $totalPagos === 1 ? 'o' : 'os' ?>
                                </div>
                            </div>

                            <!-- Action Indicator -->
                            <div class="ml-2">
                                <i class="fas fa-ellipsis-v text-white-60"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract Details (Collapsible) -->
                <div id="contractDetails<?= $model->id ?>" class="collapse">
                    <div class="card-body pt-4">
                        <!-- Contract Summary Cards - Removed Duración card, added Tiempo Restante -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="card border-0 bg-primary text-white h-100 shadow-sm">
                                    <div class="card-body text-center py-4">
                                        <div class="mb-3">
                                            <i class="fas fa-tag fa-2x"></i>
                                        </div>
                                        <h6 class="card-title font-weight-bold mb-1">Plan</h6>
                                        <h5 class="mb-2"><?= $model->plan ? $model->plan->nombre : 'N/A' ?></h5>
                                        <?php if ($model->plan): ?>
                                            <small class="opacity-8">
                                                <?= Yii::$app->formatter->asCurrency($model->plan->precio, 'USD') ?> / mes
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border-0 bg-success text-white h-100 shadow-sm">
                                    <div class="card-body text-center py-4">
                                        <div class="mb-3">
                                            <i class="fas fa-shield-alt fa-2x"></i>
                                        </div>
                                        <h6 class="card-title font-weight-bold mb-1">Cobertura</h6>
                                        <h4 class="mb-0">
                                            <?= $model->plan ? Yii::$app->formatter->asCurrency($model->plan->cobertura, 'USD') : 'N/A' ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border-0 h-100 shadow-sm" style="background: linear-gradient(135deg, <?= $remainingColor ?> 0%, <?= $remainingColor ?>cc 100%); color: white;">
                                    <div class="card-body text-center py-4">
                                        <div class="mb-3">
                                            <i class="fas fa-hourglass-half fa-2x"></i>
                                        </div>
                                        <h6 class="card-title font-weight-bold mb-1">Tiempo Restante</h6>
                                        <?php if ($remainingDays !== null): ?>
                                            <h2 class="mb-0 font-weight-bold"><?= $remainingDays ?></h2>
                                            <h5 class="mb-2"><?= $remainingStatus ?></h5>
                                        <?php else: ?>
                                            <h5 class="mb-0">Contrato Indefinido</h5>
                                            <small class="opacity-8">Sin fecha de vencimiento</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card border-0 bg-warning text-white h-100 shadow-sm">
                                    <div class="card-body text-center py-4">
                                        <div class="mb-3">
                                            <i class="fas fa-dollar-sign fa-2x"></i>
                                        </div>
                                        <h6 class="card-title font-weight-bold mb-1">Monto Total</h6>
                                        <h4 class="mb-0">
                                            <?= $model->monto ? Yii::$app->formatter->asCurrency($model->monto, 'USD') : 'N/A' ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment History -->
                        <div class="mb-4">
                            <!-- Eye-catching Header Section with Collapsible Arrow -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <div style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px; box-shadow: 0 4px 10px rgba(40,167,69,0.2);">
                                            <i class="fas fa-credit-card" style="color: white; font-size: 22px;"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 font-weight-bold" style="color: #2c3e50; font-size: 18px;">
                                                Historial de Pagos
                                                <span class="badge ml-2" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); padding: 6px 12px; border-radius: 20px; font-size: 12px;">
                                                    <i class="fas fa-chart-line mr-1"></i> <?= $totalPagos ?> registro<?= $totalPagos !== 1 ? 's' : '' ?>
                                                </span>
                                            </h5>
                                            <p class="mb-0 mt-1" style="font-size: 12px; color: #6c757d;">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Registro detallado de todas las transacciones realizadas para este contrato
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <?php if ($model->estatus !== 'Anulado'): ?>
                                        <?= Html::a(
                                            '<i class="fas fa-plus-circle mr-2"></i> Registrar Nuevo Pago',
                                            Url::to(['pagos/create', 'user_id' => $model->user_id, 'contrato_id' => $model->id]),
                                            [
                                                'class' => 'btn btn-success mr-3',
                                                'style' => 'background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border: none; border-radius: 8px; padding: 8px 20px; font-weight: 600; box-shadow: 0 2px 6px rgba(40,167,69,0.3);',
                                                'title' => 'Registrar nuevo pago'
                                            ]
                                        ) ?>
                                    <?php endif; ?>

                                    <?php if ($totalPagos > 1): ?>
                                        <!-- Collapsible Arrow Button -->
                                        <div class="collapse-arrow" style="cursor: pointer;" data-toggle="collapse" data-target="#paymentHistoryCollapse<?= $model->id ?>" aria-expanded="true">
                                            <div style="background: #f8f9fa; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                                <i class="fas fa-chevron-up" style="color: #0078d4; font-size: 18px; transition: transform 0.3s ease;"></i>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($pagosDelContrato)): ?>
                                <!-- Collapsible Content - Expanded by default -->
                                <div class="collapse show" id="paymentHistoryCollapse<?= $model->id ?>">
                                    <!-- Payment Table -->
                                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0" style="font-size: 13px;">
                                                <thead style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%);">
                                                    <tr>
                                                        <th class="text-center align-middle text-white font-weight-bold" style="width: 50px; padding: 15px 8px; color: #ffffff !important;">#</th>
                                                        <th class="text-center align-middle text-white font-weight-bold" style="width: 100px; padding: 15px 8px; color: #ffffff !important;">Fecha</th>
                                                        <th class="text-center align-middle text-white font-weight-bold" style="width: 110px; padding: 15px 8px; color: #ffffff !important;">Monto USD</th>
                                                        <th class="text-center align-middle text-white font-weight-bold" style="width: 110px; padding: 15px 8px; color: #ffffff !important;">Monto Bs</th>
                                                        <th class="text-center align-middle text-white font-weight-bold" style="width: 90px; padding: 15px 8px; color: #ffffff !important;">Tasa</th>
                                                        <th class="text-center align-middle text-white font-weight-bold" style="width: 100px; padding: 15px 8px; color: #ffffff !important;">Método</th>
                                                        <th class="text-center align-middle text-white font-weight-bold" style="width: 120px; padding: 15px 8px; color: #ffffff !important;">Referencia</th>
                                                        <th class="text-center align-middle text-white font-weight-bold" style="width: 100px; padding: 15px 8px; color: #ffffff !important;">Comprobante</th>
                                                        <th class="text-center align-middle text-white font-weight-bold" style="width: 110px; padding: 15px 8px; color: #ffffff !important;">Estatus</th>
                                                        <th class="text-center align-middle text-white font-weight-bold" style="width: 100px; padding: 15px 8px; color: #ffffff !important;">Acciones</th>
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
                                                        ?>
                                                        <tr style="border-bottom: 1px solid #f0f0f0; transition: all 0.2s ease;">
                                                            <td class="text-center align-middle" style="background-color: #f8f9fa;">
                                                                <span class="badge" style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); color: white; padding: 6px 10px; border-radius: 8px; font-size: 12px; min-width: 40px; display: inline-block;">
                                                                    <?= str_pad($paymentCounter, 2, '0', STR_PAD_LEFT) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <span class="font-weight-medium text-dark">
                                                                    <i class="fas fa-calendar-day text-muted mr-1" style="font-size: 11px;"></i>
                                                                    <?= Yii::$app->formatter->asDate($pago->fecha_pago, 'php:d/m/Y') ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <span class="font-weight-bold text-success" style="font-size: 15px;">
                                                                    <i class="fas fa-dollar-sign mr-1" style="font-size: 11px;"></i>
                                                                    <?= Yii::$app->formatter->asDecimal($pago->monto_pagado, 2) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <span class="font-weight-bold text-primary">
                                                                    <i class="fas fa-chart-line mr-1" style="font-size: 11px;"></i>
                                                                    <?= Yii::$app->formatter->asDecimal($pago->monto_usd, 2) ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <?php if ($tasaCalculada !== 'N/A'): ?>
                                                                    <span class="badge" style="background-color: #e9ecef; color: #495057; padding: 5px 8px; font-size: 11px; font-weight: 600; border-radius: 6px;">
                                                                        <i class="fas fa-exchange-alt mr-1"></i> <?= $tasaCalculada ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted small">N/A</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <?php
                                                                $methods = [
                                                                    'transferencia' => '<span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-university mr-1"></i>Transferencia</span>',
                                                                    'transferencia_bancaria' => '<span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-university mr-1"></i>Transferencia</span>',
                                                                    'efectivo' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-money-bill-wave mr-1"></i>Efectivo</span>',
                                                                    'efectivo_dolar' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-dollar-sign mr-1"></i>Efectivo USD</span>',
                                                                    'Efectivo - Dólar ($)' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-dollar-sign mr-1"></i>Efectivo USD</span>',
                                                                    'pago_movil' => '<span class="badge" style="background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-mobile-alt mr-1"></i>Pago Móvil</span>',
                                                                    'pagomovil' => '<span class="badge" style="background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-mobile-alt mr-1"></i>Pago Móvil</span>',
                                                                    'zelle' => '<span class="badge" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fab fa-zelle mr-1"></i>Zelle</span>',
                                                                    'paypal' => '<span class="badge" style="background: linear-gradient(135deg, #003087 0%, #001f6b 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fab fa-paypal mr-1"></i>PayPal</span>',
                                                                ];
                                                                $methodKey = strtolower(trim($pago->metodo_pago));
                                                                echo isset($methods[$methodKey]) ? $methods[$methodKey] : '<span class="badge badge-secondary">' . ($pago->metodo_pago ?: 'N/A') . '</span>';
                                                                ?>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <?php if ($pago->numero_referencia_pago): ?>
                                                                    <code class="font-monospace small" style="background: #f5f5f5; padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                                                                        <i class="fas fa-hashtag mr-1" style="color: #6c757d;"></i>
                                                                        <?= $pago->numero_referencia_pago ?>
                                                                    </code>
                                                                <?php else: ?>
                                                                    <span class="text-muted small"><i class="fas fa-ban mr-1"></i>Sin ref.</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <?php if ($pago->imagen_prueba): ?>
                                                                    <?= Html::a(
                                                                        '<i class="fas fa-file-invoice-dollar mr-1"></i>Ver',
                                                                        $pago->imagen_prueba,
                                                                        [
                                                                            'target' => '_blank',
                                                                            'title' => 'Ver comprobante de pago',
                                                                            'class' => 'btn btn-sm btn-outline-primary',
                                                                            'style' => 'border-radius: 6px; padding: 4px 10px; font-size: 11px;'
                                                                        ]
                                                                    ) ?>
                                                                <?php else: ?>
                                                                    <span class="text-muted small"><i class="fas fa-ban mr-1"></i>Sin comp.</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <?php
                                                                $badges = [
                                                                    'Conciliado' => '<span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-check-circle mr-1"></i>Conciliado</span>',
                                                                    'Por Conciliar' => '<span class="badge" style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #212529; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-clock mr-1"></i>Por Conciliar</span>',
                                                                    'pendiente' => '<span class="badge" style="background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-hourglass-half mr-1"></i>Pendiente</span>',
                                                                    'cancelado' => '<span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-times-circle mr-1"></i>Cancelado</span>',
                                                                    'verificado' => '<span class="badge" style="background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%); color: white; padding: 6px 12px; border-radius: 20px; font-size: 11px;"><i class="fas fa-check-double mr-1"></i>Verificado</span>',
                                                                ];
                                                                $estatus = $pago->estatus ?? 'Por Conciliar';
                                                                echo isset($badges[$estatus]) ? $badges[$estatus] : '<span class="badge badge-secondary">' . $estatus . '</span>';
                                                                ?>
                                                            </td>
                                                            <!-- ========== ACCIONES COLUMN WITH RECEIPTS ========== -->
                                                            <td class="text-center align-middle">
                                                                <div class="btn-group btn-group-sm" role="group">
                                                                    <!-- View button -->
                                                                    <?= Html::a(
                                                                        '<i class="fas fa-eye"></i>',
                                                                        Url::to(['pagos/view', 'id' => $pago->id]),
                                                                        [
                                                                            'title' => 'Ver detalles del pago',
                                                                            'class' => 'btn btn-outline-info btn-sm',
                                                                            'style' => 'border-radius: 6px; margin: 0 2px; padding: 5px 8px;'
                                                                        ]
                                                                    ) ?>

                                                                    <!-- Edit button -->
                                                                    <?= Html::a(
                                                                        '<i class="fas fa-edit"></i>',
                                                                        Url::to(['pagos/update', 'id' => $pago->id]),
                                                                        [
                                                                            'title' => 'Editar pago',
                                                                            'class' => 'btn btn-outline-warning btn-sm',
                                                                            'style' => 'border-radius: 6px; margin: 0 2px; padding: 5px 8px;'
                                                                        ]
                                                                    ) ?>

                                                                    <!-- ========== RECEIPT BUTTONS ========== -->
                                                                    <?php
                                                                    // Get receipts for this payment
                                                                    $receipts = \app\components\ReceiptGenerator::getReceiptsForPayment($pago->id);

                                                                    if (!empty($receipts)):
                                                                    ?>
                                                                        <?php if (count($receipts) == 1): ?>
                                                                            <!-- Single receipt - direct print with auto_print -->
                                                                            <?= Html::a(
                                                                                '<i class="fas fa-receipt"></i>',
                                                                                Url::to(['receipts/print', 'id' => $receipts[0]->id, 'auto_print' => 1]),
                                                                                [
                                                                                    'title' => 'Imprimir Recibo ' . $receipts[0]->receipt_number,
                                                                                    'class' => 'btn btn-outline-success btn-sm',
                                                                                    'style' => 'border-radius: 6px; margin: 0 2px; padding: 5px 8px;',
                                                                                    'target' => '_blank'
                                                                                ]
                                                                            ) ?>
                                                                        <?php else: ?>
                                                                            <!-- Multiple receipts - dropdown -->
                                                                            <div class="btn-group">
                                                                                <?= Html::a(
                                                                                    '<i class="fas fa-receipt"></i> <span class="caret"></span>',
                                                                                    '#',
                                                                                    [
                                                                                        'title' => 'Ver/Imprimir Recibos (' . count($receipts) . ')',
                                                                                        'class' => 'btn btn-outline-success btn-sm dropdown-toggle',
                                                                                        'data-toggle' => 'dropdown',
                                                                                        'style' => 'border-radius: 6px; margin: 0 2px; padding: 5px 8px;',
                                                                                        'aria-haspopup' => 'true',
                                                                                        'aria-expanded' => 'false'
                                                                                    ]
                                                                                ) ?>
                                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                                    <!-- Print all option -->
                                                                                    <?= Html::a(
                                                                                        '<i class="fas fa-print mr-2"></i> Imprimir todos (' . count($receipts) . ')',
                                                                                        Url::to(['receipts/print-all', 'paymentId' => $pago->id, 'auto_print' => 1]),
                                                                                        ['class' => 'dropdown-item', 'target' => '_blank']
                                                                                    ) ?>
                                                                                    <div class="dropdown-divider"></div>
                                                                                    <!-- Individual receipts -->
                                                                                    <?php foreach ($receipts as $receipt): ?>
                                                                                        <?= Html::a(
                                                                                            '<i class="fas fa-file-pdf mr-2"></i> ' . $receipt->receipt_number,
                                                                                            Url::to(['receipts/print', 'id' => $receipt->id, 'auto_print' => 1]),
                                                                                            ['class' => 'dropdown-item', 'target' => '_blank']
                                                                                        ) ?>
                                                                                    <?php endforeach; ?>
                                                                                    <div class="dropdown-divider"></div>
                                                                                    <!-- View all receipts page -->
                                                                                    <?= Html::a(
                                                                                        '<i class="fas fa-list mr-2"></i> Ver todos los recibos',
                                                                                        Url::to(['pagos/view-receipts', 'payment_id' => $pago->id]),
                                                                                        ['class' => 'dropdown-item', 'target' => '_blank']
                                                                                    ) ?>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <!-- No receipts - show disabled icon and generate button -->
                                                                        <div class="btn-group">
                                                                            <?= Html::a(
                                                                                '<i class="fas fa-receipt"></i>',
                                                                                '#',
                                                                                [
                                                                                    'title' => 'Sin recibos generados',
                                                                                    'class' => 'btn btn-outline-secondary btn-sm disabled',
                                                                                    'style' => 'opacity: 0.5; cursor: not-allowed; border-radius: 6px; margin: 0 2px; padding: 5px 8px;'
                                                                                ]
                                                                            ) ?>
                                                                            <?= Html::a(
                                                                                '<i class="fas fa-plus-circle"></i>',
                                                                                Url::to(['pagos/generate-receipts', 'id' => $pago->id]),
                                                                                [
                                                                                    'title' => 'Generar Recibos para este pago',
                                                                                    'class' => 'btn btn-outline-primary btn-sm',
                                                                                    'style' => 'border-radius: 6px; margin: 0 2px; padding: 5px 8px;',
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
                                                <tfoot style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-top: 2px solid #dee2e6;">
                                                    <tr>
                                                        <td colspan="2" class="text-right font-weight-bold" style="padding: 12px;">
                                                            <i class="fas fa-chart-line mr-1 text-primary"></i> Totales:
                                                        </td>
                                                        <td class="text-center font-weight-bold text-success" style="padding: 12px; font-size: 16px;">
                                                            <i class="fas fa-dollar-sign mr-1"></i> <?= number_format(array_sum(array_map(function ($p) {
                                                                                                        return $p->monto_pagado;
                                                                                                    }, $pagosDelContrato)), 2) ?>
                                                        </td>
                                                        <td class="text-center font-weight-bold text-primary" style="padding: 12px; font-size: 16px;">
                                                            <i class="fas fa-chart-line mr-1"></i> <?= number_format(array_sum(array_map(function ($p) {
                                                                                                        return $p->monto_usd;
                                                                                                    }, $pagosDelContrato)), 2) ?>
                                                        </td>
                                                        <td colspan="6"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <!-- Summary Cards Row - MOVED TO THE END AFTER THE TABLE -->
                                        <div class="row p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-top: 1px solid #e1e1e1;">
                                            <div class="col-md-3">
                                                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    <div class="card-body text-center py-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <small class="text-white-50" style="font-size: 11px;">TOTAL PAGOS</small>
                                                                <h3 class="text-white mb-0 font-weight-bold" style="font-size: 28px;"><?= $totalPagos ?></h3>
                                                            </div>
                                                            <div class="bg-white rounded-circle p-2" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-receipt" style="color: #667eea; font-size: 20px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                                                    <div class="card-body text-center py-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <small class="text-white-50" style="font-size: 11px;">TOTAL USD</small>
                                                                <h3 class="text-white mb-0 font-weight-bold" style="font-size: 28px;">$ <?= number_format(array_sum(array_map(function ($p) {
                                                                                                                                            return $p->monto_pagado;
                                                                                                                                        }, $pagosDelContrato)), 0) ?></h3>
                                                            </div>
                                                            <div class="bg-white rounded-circle p-2" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-dollar-sign" style="color: #28a745; font-size: 20px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #17a2b8 0%, #0f6c7a 100%);">
                                                    <div class="card-body text-center py-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <small class="text-white-50" style="font-size: 11px;">TOTAL BS</small>
                                                                <h3 class="text-white mb-0 font-weight-bold" style="font-size: 28px;">Bs <?= number_format(array_sum(array_map(function ($p) {
                                                                                                                                                return $p->monto_usd;
                                                                                                                                            }, $pagosDelContrato)), 0) ?></h3>
                                                            </div>
                                                            <div class="bg-white rounded-circle p-2" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-chart-line" style="color: #17a2b8; font-size: 20px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);">
                                                    <div class="card-body text-center py-3">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <small class="text-dark-50" style="font-size: 11px; color: rgba(0,0,0,0.6);">PROMEDIO PAGO</small>
                                                                <h3 class="mb-0 font-weight-bold" style="font-size: 28px; color: #212529;">$ <?= number_format(array_sum(array_map(function ($p) {
                                                                                                                                                    return $p->monto_pagado;
                                                                                                                                                }, $pagosDelContrato)) / $totalPagos, 2) ?></h3>
                                                            </div>
                                                            <div class="bg-white rounded-circle p-2" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="fas fa-calculator" style="color: #ffc107; font-size: 20px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-footer" style="background: #f8f9fa; border-top: 1px solid #e1e1e1; padding: 12px 20px;">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        <?php if ($model->estatus === 'Anulado'): ?>
                                                            Se muestran solo los pagos realizados antes de la anulación (<?= $model->anulado_fecha ? Yii::$app->formatter->asDate($model->anulado_fecha, 'php:d/m/Y') : 'N/A' ?>).
                                                        <?php else: ?>
                                                            <i class="fas fa-check-circle text-success mr-1"></i> Historial completo de pagos realizados durante la vigencia del contrato.
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <small class="text-muted">
                                                        <i class="fas fa-sync-alt mr-1"></i>
                                                        Actualizado: <?= date('d/m/Y H:i') ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Empty State -->
                                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);">
                                    <div class="card-body text-center py-5">
                                        <div class="mb-4">
                                            <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                                <i class="fas fa-credit-card fa-3x text-white"></i>
                                            </div>
                                            <h5 class="text-dark mb-2 font-weight-bold" style="font-size: 18px;">No hay pagos registrados</h5>
                                            <p class="text-muted mb-0" style="font-size: 13px;">No se han registrado pagos para este contrato hasta el momento.</p>
                                        </div>
                                        <?php if ($model->estatus !== 'Anulado'): ?>
                                            <div class="mt-4">
                                                <?= Html::a(
                                                    '<i class="fas fa-plus-circle mr-2"></i> Registrar Primer Pago',
                                                    Url::to(['pagos/create', 'user_id' => $model->user_id, 'contrato_id' => $model->id]),
                                                    [
                                                        'class' => 'btn btn-success btn-lg py-2 px-4',
                                                        'style' => 'background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border: none; border-radius: 10px; font-weight: 600; box-shadow: 0 4px 10px rgba(40,167,69,0.3);',
                                                        'title' => 'Registrar el primer pago para este contrato'
                                                    ]
                                                ) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Periodo: <?= $periodoInfo ?>
                                </small>
                            </div>
                            <div>
                                <?php if ($model->estatus !== 'Anulado'): ?>
                                    <?= Html::a(
                                        '<i class="fas fa-eye mr-1"></i> Ver Detalles del Contrato',
                                        ['view', 'id' => $model->id],
                                        ['class' => 'btn btn-outline-info btn-sm mr-2']
                                    ) ?>
                                    <?= Html::a(
                                        '<i class="fas fa-file-invoice-dollar mr-1"></i> Registrar Pago',
                                        ['pagos/create', 'user_id' => $model->user_id, 'contrato_id' => $model->id],
                                        ['class' => 'btn btn-success btn-sm mr-2']
                                    ) ?>

                                    <!-- ANULAR BUTTON - Available for Superadmin, GERENTE-COMERCIALIZACION, and GERENTE-CLINICA -->
                                    <?php if (
                                        Yii::$app->user->can('superadmin') ||
                                        Yii::$app->user->can('GERENTE-COMERCIALIZACION') ||
                                        Yii::$app->user->can('GERENTE-CLINICA') ||
                                        Yii::$app->user->can('GERENTE-OPERACIONES')

                                    ): ?>
                                        <?= Html::a(
                                            '<i class="fas fa-ban mr-1"></i> Anular Contrato',
                                            ['contratos/anular-form', 'id' => $model->id],
                                            [
                                                'class' => 'btn btn-danger btn-sm',
                                                'style' => 'transition: all 0.2s ease;',
                                                'data-toggle' => 'tooltip',
                                                'title' => 'Anular este contrato (acción irreversible)'
                                            ]
                                        ) ?>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <span class="badge badge-danger py-2 px-3">
                                        <i class="fas fa-ban mr-1"></i> Contrato Anulado
                                        <?php if ($model->anulado_fecha): ?>
                                            el <?= Yii::$app->formatter->asDate($model->anulado_fecha, 'php:d/m/Y') ?>
                                        <?php endif; ?>
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
                        ['class' => 'btn btn-success btn-lg py-2 px-4']
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
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.1);
        transition: box-shadow 0.2s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    /* Microsoft-inspired badge styling */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        padding: 6px 12px;
        border-radius: 16px;
    }
    
    /* Button styling with Fluent Design */
    .btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 6px 16px;
        transition: all 0.2s ease;
    }
    
    .btn-sm {
        padding: 4px 12px;
        font-size: 12px;
    }
    
    /* Table header styling */
    .table thead th {
        border-bottom: 2px solid var(--ms-gray-200);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
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
        border-radius: 6px;
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
        padding: 16px 24px;
    }
    
    .modal-footer {
        border-top: 1px solid var(--ms-gray-200);
        padding: 16px 24px;
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
    
    /* Hover effects for interactive elements */
    .contract-card .card-header {
        transition: background 0.2s ease;
    }
    
    .contract-card .card-header:hover {
        filter: brightness(1.05);
    }
    
    /* Code styling for reference numbers */
    code {
        background: var(--ms-gray-100);
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 12px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .display-4 {
            font-size: 2rem;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 11px;
        }
        
        .table {
            font-size: 11px;
        }
    }
    
    /* Microsoft-themed scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
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
CSS
);

$this->registerJs(
    <<<JS
$(document).ready(function() {
    console.log('Document ready - Initializing Bootstrap 4 components');
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize all modals
    $('.modal').modal({
        show: false
    });

    // Handle contract card toggle animation
    $('.contract-card .card-header').on('click', function() {
        const icon = $(this).find('.contract-toggle-icon');
        if (icon.hasClass('fa-chevron-down')) {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    });

    // Toggle arrow icon when collapsing/expanding payment history
    $('[id^="paymentHistoryCollapse"]').on('show.bs.collapse', function () {
        $(this).closest('.mb-4').find('.collapse-arrow i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        $(this).closest('.mb-4').find('.collapse-arrow div').css('transform', 'rotate(0deg)');
    });
    
    $('[id^="paymentHistoryCollapse"]').on('hide.bs.collapse', function () {
        $(this).closest('.mb-4').find('.collapse-arrow i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        $(this).closest('.mb-4').find('.collapse-arrow div').css('transform', 'rotate(180deg)');
    });
    
    // Hover effect for collapse arrow
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
    
    // Add smooth hover effects to buttons (Microsoft Fluent Design)
    $('.btn').hover(
        function() {
            $(this).css({
                'transform': 'translateY(-2px)',
                'transition': 'transform 0.2s ease, box-shadow 0.2s ease',
                'box-shadow': '0 4px 12px rgba(0,0,0,0.15)'
            });
        },
        function() {
            $(this).css({
                'transform': 'translateY(0)',
                'box-shadow': 'none'
            });
        }
    );

    // Specific styling for danger buttons
    $('.btn-danger').hover(
        function() {
            $(this).css({
                'background': '#dc3545',
                'box-shadow': '0 4px 12px rgba(220,53,69,0.3)'
            });
        },
        function() {
            $(this).css({
                'background': '',
                'box-shadow': 'none'
            });
        }
    );
    
    // Handle anular button click to show modal
    $('[data-toggle="modal"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        console.log('Opening modal: ' + target);
        $(target).modal('show');
    });
    
    // Handle anular form submission to prevent double submission
    $('.anular-form').on('submit', function() {
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin mr-1"></i> Anulando...');
        
        console.log('Anular form submitted');
        return true;
    });

    // Debug: Check if Bootstrap is loaded
    if (typeof $.fn.modal === 'function') {
        console.log('Bootstrap modal function is available');
    } else {
        console.error('Bootstrap modal function is NOT available');
    }
    
    // Add Microsoft Fluent Design card hover effects
    $('.contract-card').hover(
        function() {
            $(this).find('.card').css({
                'box-shadow': '0 8px 20px rgba(0,0,0,0.12)',
                'transition': 'box-shadow 0.3s ease'
            });
        },
        function() {
            $(this).find('.card').css({
                'box-shadow': ''
            });
        }
    );
});
JS
);
?>