<?php

use yii\helpers\Html;
use yii\helpers\Url;

// Register jQuery and Bootstrap 4 assets
\yii\web\JqueryAsset::register($this);
\yii\bootstrap4\BootstrapAsset::register($this);

$this->title = 'Contratos';

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
                <?php if ($searchModel->estatus == 'Anulado'): ?>
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
        <?php if ($afiliado_datos): ?>
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
                                <?= $dataProvider->getTotalCount() ?>
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
    <?php if ($dataProvider->getTotalCount() > 0): ?>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="text-dark mb-0">
                    <i class="fas fa-list mr-2 text-primary"></i>Lista de Contratos
                    <span class="badge badge-primary ml-2"><?= $dataProvider->getTotalCount() ?> registros</span>
                </h4>
                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1"></i>
                    Haga clic en cualquier contrato para ver los detalles
                </small>
            </div>
        </div>

        <?php foreach ($dataProvider->getModels() as $index => $model): ?>
            <?php
            // Calculate payment statistics for this contract
            $pagosDelContrato = $model->getPagosDelContrato()->all();
            $totalPagado = 0;
            $totalPagos = count($pagosDelContrato);
            $lastPaymentDate = null;

            foreach ($pagosDelContrato as $pago) {
                $totalPagado += floatval($pago->monto_pagado);
                if (!$lastPaymentDate || strtotime($pago->fecha_pago) > strtotime($lastPaymentDate)) {
                    $lastPaymentDate = $pago->fecha_pago;
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
                    aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>"
                    aria-controls="contractDetails<?= $model->id ?>">

                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Left side: Contract info -->
                        <div class="d-flex align-items-center">
                            <!-- Expand/Collapse Icon -->
                            <div class="mr-3">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fas fa-chevron-<?= $index === 0 ? 'up' : 'down' ?> contract-toggle-icon text-primary"></i>
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
                                            'Registrado' => 'badge-info',        // Blue for registered
                                            'Activo' => 'badge-success',         // Green for active
                                            'Anulado' => 'badge-danger',         // Red for cancelled
                                            'Vencido' => 'badge-warning',        // Yellow for expired
                                            'Pendiente' => 'badge-primary',      // Primary blue for pending
                                            'suspendido' => 'badge-secondary',   // Gray for suspended
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
                <div id="contractDetails<?= $model->id ?>" class="collapse <?= $index === 0 ? 'show' : '' ?>">
                    <div class="card-body pt-4">
                        <!-- Contract Summary Cards -->
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
                                <div class="card border-0 bg-info text-white h-100 shadow-sm">
                                    <div class="card-body text-center py-4">
                                        <div class="mb-3">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                        <h6 class="card-title font-weight-bold mb-1">Duración</h6>
                                        <h5 class="mb-0">
                                            <?php
                                            if ($model->fecha_ini && $model->fecha_ven) {
                                                $start = new DateTime($model->fecha_ini);
                                                $end = new DateTime($model->fecha_ven);
                                                $interval = $start->diff($end);

                                                $years = $interval->y;
                                                $months = $interval->m;

                                                if ($years > 0) {
                                                    echo $years . ' año' . ($years > 1 ? 's' : '');
                                                    if ($months > 0) {
                                                        echo ', ' . $months . ' mes' . ($months > 1 ? 'es' : '');
                                                    }
                                                } elseif ($months > 0) {
                                                    echo $months . ' mes' . ($months > 1 ? 'es' : '');
                                                } else {
                                                    echo $interval->days . ' días';
                                                }
                                            } else {
                                                echo 'Indefinido';
                                            }
                                            ?>
                                        </h5>
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

                        <!-- Payment Statistics -->
                        <?php if ($totalPagos > 0): ?>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h5 class="text-dark mb-3">
                                        <i class="fas fa-chart-bar mr-2 text-info"></i>Estadísticas de Pagos
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light h-100 border-0 shadow-sm">
                                                <div class="card-body text-center py-4">
                                                    <h6 class="card-title text-muted mb-1">Total Pagos</h6>
                                                    <h3 class="text-primary"><?= $totalPagos ?></h3>
                                                    <small class="text-muted">registros</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light h-100 border-0 shadow-sm">
                                                <div class="card-body text-center py-4">
                                                    <h6 class="card-title text-muted mb-1">Monto Total</h6>
                                                    <h3 class="text-success"><?= Yii::$app->formatter->asCurrency($totalPagado, 'USD') ?></h3>
                                                    <small class="text-muted">acumulado</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light h-100 border-0 shadow-sm">
                                                <div class="card-body text-center py-4">
                                                    <h6 class="card-title text-muted mb-1">Último Pago</h6>
                                                    <h5 class="text-info">
                                                        <?= $lastPaymentDate ? Yii::$app->formatter->asDate($lastPaymentDate, 'php:d/m/Y') : 'N/A' ?>
                                                    </h5>
                                                    <small class="text-muted">fecha</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="card bg-light h-100 border-0 shadow-sm">
                                                <div class="card-body text-center py-4">
                                                    <h6 class="card-title text-muted mb-1">Promedio</h6>
                                                    <h5 class="text-warning">
                                                        <?= $totalPagos > 0 ? Yii::$app->formatter->asCurrency($totalPagado / $totalPagos, 'USD') : 'N/A' ?>
                                                    </h5>
                                                    <small class="text-muted">por pago</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Payment History -->
                        <!-- Payment History -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-dark mb-0">
                                    <i class="fas fa-credit-card mr-2 text-success"></i>Historial de Pagos
                                    <span class="badge badge-success ml-2"><?= $totalPagos ?> registro<?= $totalPagos !== 1 ? 's' : '' ?></span>
                                </h5>
                                <?php if ($model->estatus !== 'Anulado'): ?>
                                    <?= Html::a(
                                        '<i class="fas fa-plus-circle mr-1"></i> Registrar Pago',
                                        Url::to(['pagos/create', 'user_id' => $model->user_id]),
                                        [
                                            'class' => 'btn btn-success btn-sm',
                                            'title' => 'Registrar nuevo pago'
                                        ]
                                    ) ?>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($pagosDelContrato)): ?>
                                <div class="card border-0 shadow-sm">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead style="background-color: #007bff !important;">
                                                <tr>
                                                    <th style="width: 100px;" class="text-center align-middle text-white font-weight-bold">Fecha</th>
                                                    <th style="width: 120px;" class="text-center align-middle text-white font-weight-bold">Monto USD</th>
                                                    <th style="width: 120px;" class="text-center align-middle text-white font-weight-bold">Monto Bs</th>
                                                    <th style="width: 100px;" class="text-center align-middle text-white font-weight-bold">Método</th>
                                                    <th style="width: 120px;" class="text-center align-middle text-white font-weight-bold">Referencia</th>
                                                    <th style="width: 80px;" class="text-center align-middle text-white font-weight-bold">Tasa</th>
                                                    <th style="width: 100px;" class="text-center align-middle text-white font-weight-bold">Comprobante</th>
                                                    <th style="width: 120px;" class="text-center align-middle text-white font-weight-bold">Estatus</th>
                                                    <th style="width: 90px;" class="text-center align-middle text-white font-weight-bold">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pagosDelContrato as $pago): ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <span class="font-weight-medium text-dark">
                                                                <?= Yii::$app->formatter->asDate($pago->fecha_pago, 'php:d/m/Y') ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="font-weight-bold text-success">
                                                                <?= Yii::$app->formatter->asDecimal($pago->monto_pagado, 2) ?> USD
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="text-primary">
                                                                <?= Yii::$app->formatter->asDecimal($pago->monto_usd, 2) ?> Bs
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <?php
                                                            $methods = [
                                                                'transferencia' => '<i class="fas fa-university mr-1 text-primary"></i>Transferencia',
                                                                'efectivo' => '<i class="fas fa-money-bill-wave mr-1 text-success"></i>Efectivo',
                                                                'pago_movil' => '<i class="fas fa-mobile-alt mr-1 text-info"></i>Pago Móvil',
                                                                'zelle' => '<i class="fas fa-exchange-alt mr-1 text-warning"></i>Zelle',
                                                                'paypal' => '<i class="fab fa-paypal mr-1 text-danger"></i>PayPal',
                                                            ];
                                                            echo isset($methods[$pago->metodo_pago]) ?
                                                                $methods[$pago->metodo_pago] :
                                                                '<span class="text-muted">' . ($pago->metodo_pago ?: 'N/A') . '</span>';
                                                            ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <?php if ($pago->numero_referencia_pago): ?>
                                                                <span class="font-monospace small bg-light py-1 px-2 rounded text-dark">
                                                                    <?= $pago->numero_referencia_pago ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted small">Sin ref.</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <?php if ($pago->tasa): ?>
                                                                <span class="badge badge-light text-dark">
                                                                    <?= Yii::$app->formatter->asDecimal($pago->tasa, 2) ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted small">N/A</span>
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
                                                                        'class' => 'btn btn-sm btn-outline-primary'
                                                                    ]
                                                                ) ?>
                                                            <?php else: ?>
                                                                <span class="text-muted small"><i class="fas fa-ban mr-1"></i>Sin comp.</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <?php
                                                            $badges = [
                                                                'Conciliado' => 'badge-success',
                                                                'Por Conciliar' => 'badge-warning',
                                                                'pendiente' => 'badge-info',
                                                                'cancelado' => 'badge-danger',
                                                                'verificado' => 'badge-primary',
                                                            ];
                                                            $estatus = $pago->estatus ?? 'Por Conciliar';
                                                            $class = $badges[$estatus] ?? 'badge-secondary';
                                                            $icons = [
                                                                'Conciliado' => 'fas fa-check-circle',
                                                                'Por Conciliar' => 'fas fa-clock',
                                                                'pendiente' => 'fas fa-hourglass-half',
                                                                'cancelado' => 'fas fa-times-circle',
                                                                'verificado' => 'fas fa-check-double',
                                                            ];
                                                            $icon = isset($icons[$estatus]) ? '<i class="' . $icons[$estatus] . ' mr-1"></i>' : '';
                                                            echo Html::tag('span', $icon . $estatus, [
                                                                'class' => "badge $class py-1 px-2"
                                                            ]);
                                                            ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <div class="btn-group btn-group-sm">
                                                                <?= Html::a(
                                                                    '<i class="fas fa-eye"></i>',
                                                                    Url::to(['pagos/view', 'id' => $pago->id]),
                                                                    [
                                                                        'title' => 'Ver detalles del pago',
                                                                        'class' => 'btn btn-outline-info'
                                                                    ]
                                                                ) ?>
                                                                <?= Html::a(
                                                                    '<i class="fas fa-edit"></i>',
                                                                    Url::to(['pagos/update', 'id' => $pago->id]),
                                                                    [
                                                                        'title' => 'Editar pago',
                                                                        'class' => 'btn btn-outline-warning'
                                                                    ]
                                                                ) ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer bg-light border-top py-2">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    <?php if ($model->estatus === 'Anulado'): ?>
                                                        Se muestran solo los pagos realizados antes de la anulación (<?= $model->anulado_fecha ? Yii::$app->formatter->asDate($model->anulado_fecha, 'php:d/m/Y') : 'N/A' ?>).
                                                    <?php else: ?>
                                                        Se muestran los pagos realizados durante la vigencia del contrato.
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
                            <?php else: ?>
                                <div class="card border-warning">
                                    <div class="card-body text-center py-5">
                                        <div class="mb-4">
                                            <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
                                            <h5 class="text-muted">No hay pagos registrados para este contrato</h5>
                                            <p class="text-muted mb-0">No se han registrado pagos durante el periodo activo del contrato.</p>
                                        </div>
                                        <?php if ($model->estatus !== 'Anulado'): ?>
                                            <div class="mt-4">
                                                <?= Html::a(
                                                    '<i class="fas fa-plus-circle mr-2"></i> Registrar Primer Pago',
                                                    Url::to(['pagos/create', 'user_id' => $model->user_id]),
                                                    [
                                                        'class' => 'btn btn-success',
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
                                        ['pagos/create', 'user_id' => $model->user_id],
                                        ['class' => 'btn btn-success btn-sm']
                                    ) ?>
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

<!-- Custom CSS for Bootstrap 4 -->
<style>
    .view-main-container {
        padding: 20px;
        background-color: #f8f9fa;
        min-height: 100vh;
    }

    /* Card styling */
    .card {
        border-radius: 0.5rem;
        border: 1px solid #e9ecef;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
        border-bottom: 1px solid #e9ecef;
    }

    /* Left border for contract cards */
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }

    .border-left-secondary {
        border-left: 4px solid #6c757d !important;
    }

    /* Contract card specific styling */
    .contract-card {
        transition: all 0.3s ease;
    }

    .contract-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .contract-toggle-icon {
        transition: transform 0.3s ease;
    }

    .contract-card .card-header:hover {
        background-color: #f8f9fa;
    }

    /* Table styling */
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
        background-color: #343a40;
        color: white;
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    /* Badge styling */
    .badge {
        font-size: 0.85em;
        font-weight: 500;
        padding: 0.35em 0.65em;
        border-radius: 0.25rem;
    }

    /* Button styling */
    .btn-group-sm>.btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* Font monospace for references */
    .font-monospace {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.85em;
    }

    /* Display classes */
    .display-4 {
        font-size: 2.5rem;
        font-weight: 300;
        line-height: 1.2;
    }

    /* Background colors for summary cards */
    .bg-primary {
        background-color: #007bff !important;
    }

    .bg-success {
        background-color: #28a745 !important;
    }

    .bg-info {
        background-color: #17a2b8 !important;
    }

    .bg-warning {
        background-color: #ffc107 !important;
        color: #212529;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }

    /* Text colors */
    .text-white {
        color: #fff !important;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .text-dark {
        color: #343a40 !important;
    }

    .text-primary {
        color: #007bff !important;
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-info {
        color: #17a2b8 !important;
    }

    .text-warning {
        color: #ffffff !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .view-main-container {
            padding: 10px;
        }

        .d-flex.justify-content-between.align-items-start {
            flex-direction: column;
        }

        .d-flex.align-items-start.w-75 {
            width: 100% !important;
            margin-bottom: 15px;
        }

        .d-flex.flex-column.align-items-end {
            width: 100%;
            align-items: flex-start !important;
        }

        .d-flex.flex-column.align-items-end>div {
            text-align: left !important;
        }

        .display-4 {
            font-size: 2rem;
        }

        .d-flex.flex-wrap>div {
            margin-bottom: 5px;
        }

        .table-responsive {
            font-size: 0.85em;
        }

        .table th,
        .table td {
            padding: 0.5rem;
        }

        .card-body .row>div[class^="col-"] {
            margin-bottom: 15px;
        }

        .card-header .d-flex {
            flex-direction: column;
        }

        .card-header .d-flex>div {
            width: 100%;
        }
    }

    /* Print styles */
    @media print {
        .contract-card .card-header {
            background-color: #fff !important;
            color: #000 !important;
        }

        .btn {
            display: none !important;
        }

        .contract-card {
            break-inside: avoid;
        }
    }

    /* Contract Header Styles */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #0069d9 0%, #0056b3 100%) !important;
    }

    .text-white-80 {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    .text-white-60 {
        color: rgba(255, 255, 255, 0.6) !important;
    }

    .contract-card .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
        transition: all 0.3s ease;
    }

    .contract-card .card-header:hover {
        background: linear-gradient(135deg, #0056b3 0%, #004085 100%) !important;
    }

    .status-badge .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        font-weight: 600;
        border-radius: 4px;
    }

    /* Contract toggle icon animation */
    .contract-toggle-icon {
        transition: transform 0.3s ease;
    }

    .contract-card .card-header:hover .contract-toggle-icon {
        transform: scale(1.1);
    }

    /* Left border colors */
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }

    .border-left-secondary {
        border-left: 4px solid #6c757d !important;
    }

    /* Responsive adjustments for contract header */
    @media (max-width: 992px) {
        .contract-card .card-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .contract-card .card-header .d-flex>div:last-child {
            margin-top: 15px;
            width: 100%;
            justify-content: space-between !important;
        }

        .d-flex.flex-wrap>div {
            margin-bottom: 5px;
        }
    }

    @media (max-width: 768px) {
        .contract-card .card-header .d-flex.justify-content-between {
            flex-direction: column;
        }

        .contract-card .card-header .d-flex.align-items-center {
            flex-direction: column;
            align-items: flex-start !important;
            width: 100%;
        }

        .contract-card .card-header .d-flex.align-items-center>div:last-child {
            margin-top: 15px;
            width: 100%;
            flex-direction: column;
            align-items: flex-start !important;
        }

        .contract-card .card-header .d-flex.align-items-center>div:last-child>div {
            margin-bottom: 10px;
            margin-right: 0 !important;
            text-align: left !important;
        }
    }

    /* Status badge styling for dark background */
    .status-badge .badge-info {
        background-color: #17a2b8 !important;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .status-badge .badge-success {
        background-color: #28a745 !important;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .status-badge .badge-danger {
        background-color: #dc3545 !important;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .status-badge .badge-warning {
        background-color: #ffc107 !important;
        color: #212529 !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .status-badge .badge-primary {
        background-color: #007bff !important;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .status-badge .badge-secondary {
        background-color: #6c757d !important;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .status-badge .badge-light {
        background-color: #f8f9fa !important;
        color: #212529 !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .status-badge .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        font-weight: 600;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Payment history table styling */
    .table-dark {
        background-color: #343a40;
        color: white;
    }

    .table-dark thead th {
        background-color: #212529;
        border-color: #454d55;
        color: white !important;
    }

    .table-dark tbody tr {
        background-color: #343a40;
        border-color: #454d55;
    }

    .table-dark tbody tr:hover {
        background-color: #3e444a;
    }

    /* Ensure all text in table is white */
    .table-dark td,
    .table-dark th,
    .table-dark span,
    .table-dark small {
        color: white !important;
    }

    /* Override specific text colors to ensure visibility */
    .table-dark .text-success {
        color: #75b798 !important;
        /* Lighter green for dark background */
        font-weight: bold;
    }

    .table-dark .text-primary {
        color: #8bb9fe !important;
        /* Lighter blue for dark background */
    }

    .table-dark .text-warning {
        color: #ffd351 !important;
        /* Lighter yellow for dark background */
    }

    /* Button styling for dark table */
    .btn-outline-light {
        color: white;
        border-color: #6c757d;
    }

    .btn-outline-light:hover {
        color: #212529;
        background-color: white;
        border-color: white;
    }

    /* Badge styling for dark background */
    .table-dark .badge-light {
        background-color: #e9ecef;
        color: #212529 !important;
    }

    /* Text opacity classes */
    .text-white-80 {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    /* Card footer for payment history */
    .card-footer.bg-dark {
        background-color: #212529 !important;
        border-top: 1px solid #495057;
    }

    /* Force white text in table header */
    .table thead th.text-white {
        color: white !important;
        background-color: #007bff !important;
    }

    /* Specific styling for payment history table header */
    .card .table thead {
        background-color: #007bff !important;
    }

    .card .table thead th {
        color: white !important;
        border-bottom: 2px solid #0056b3;
    }

    /* Ensure the header background stays primary blue */
    .table thead.bg-primary {
        background-color: #007bff !important;
    }

    /* Remove any conflicting Bootstrap styles */
    .table thead th {
        color: white !important;
    }
</style>

<!-- JavaScript for Bootstrap 4 interactions -->
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Handle contract card toggle animation
        $('.contract-card .card-header').on('click', function() {
            const icon = $(this).find('.contract-toggle-icon');
            if (icon.hasClass('fa-chevron-down')) {
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            } else {
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }
        });

        // Auto-expand the first contract
        if ($('.contract-card').length > 0 && $('.collapse.show').length === 0) {
            const firstContract = $('.contract-card:first');
            const firstHeader = firstContract.find('.card-header');
            const firstCollapse = firstContract.find('.collapse');

            firstCollapse.addClass('show');
            firstHeader.attr('aria-expanded', 'true');
            firstHeader.find('.contract-toggle-icon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }

        // Add smooth hover effects to buttons
        $('.btn').hover(
            function() {
                $(this).css({
                    'transform': 'translateY(-2px)',
                    'transition': 'transform 0.2s ease',
                    'box-shadow': '0 4px 8px rgba(0,0,0,0.1)'
                });
            },
            function() {
                $(this).css({
                    'transform': 'translateY(0)',
                    'box-shadow': 'none'
                });
            }
        );
    });
</script>