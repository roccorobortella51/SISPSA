<?php
// app/views/reportes/_pagos-pdf-simple.php
use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\PagosReporteSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $title */
/** @var string $subtitle */
/** @var array $summary */
/** @var array $summaryPorClinica */
/** @var string $startDate */
/** @var string $endDate */
/** @var array $clinicasSeleccionadas */
/** @var string $generatedAt */
/** @var string $statusLabel */
?>
<div class="pdf-container">

    <!-- ENCABEZADO PRINCIPAL CON LOGO - CENTERED -->
    <div style="margin-bottom: 25px; text-align: center;">
        <!-- Logo SISPSA -->
        <div style="margin-bottom: 10px;">
            <img src="<?= Yii::getAlias('@web') ?>/img/sispsa.png"
                alt="SISPSA Logo"
                style="max-width: 150px; max-height: 80px; margin: 0 auto; display: block;"
                onerror="this.style.display='none'">
        </div>

        <!-- Título y Subtítulo -->
        <div>
            <h1 class="main-title" style="font-size: 22pt; font-weight: bold; color: #2c3e50; margin: 0 0 8px 0; text-align: center;">
                <?= Html::encode($title) ?>
            </h1>
            <div class="subtitle" style="font-size: 14pt; color: #0078d4; font-weight: bold; margin: 0 0 15px 0; text-align: center;">
                <?= Html::encode($subtitle) ?>
            </div>
        </div>

        <!-- Línea decorativa -->
        <div style="height: 2px; background: linear-gradient(90deg, transparent, #0078d4, transparent); width: 80%; margin: 0 auto;"></div>
    </div>

    <!-- INFORMACIÓN DEL REPORTE -->
    <div style="background-color: #f8f9fa; border: 2px solid #0078d4; border-radius: 8px; padding: 15px; margin-bottom: 25px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 25%; padding: 8px 0;">
                    <div style="font-weight: bold; color: #2c3e50; margin-bottom: 3px;">Período:</div>
                    <div style="color: #333;"><?= $startDate ?> al <?= $endDate ?></div>
                </td>
                <td style="width: 25%; padding: 8px 0;">
                    <div style="font-weight: bold; color: #2c3e50; margin-bottom: 3px;">Generado:</div>
                    <div style="color: #333;"><?= $generatedAt ?></div>
                </td>
                <td style="width: 25%; padding: 8px 0;">
                    <div style="font-weight: bold; color: #2c3e50; margin-bottom: 3px;">Total Registros:</div>
                    <div style="color: #333; font-weight: bold;"><?= number_format($dataProvider->getTotalCount()) ?></div>
                </td>
                <td style="width: 25%; padding: 8px 0;">
                    <div style="font-weight: bold; color: #2c3e50; margin-bottom: 3px;">Estado:</div>
                    <div style="color: #333;"><?= $statusLabel ?></div>
                </td>
            </tr>
            <?php if (!empty($clinicasSeleccionadas) && !in_array('todas', $clinicasSeleccionadas)): ?>
                <tr>
                    <td colspan="4" style="padding: 8px 0; border-top: 1px dashed #ddd;">
                        <div style="font-weight: bold; color: #2c3e50; margin-bottom: 3px;">Clínicas Filtradas:</div>
                        <div style="color: #333;"><?= count($clinicasSeleccionadas) ?> clínica(s) seleccionada(s)</div>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- RESUMEN ESTADÍSTICO -->
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: #2c3e50; border-bottom: 2px solid #0078d4; padding-bottom: 5px; margin-bottom: 15px;">
            RESUMEN ESTADÍSTICO
        </h2>

        <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
            <tr>
                <td style="width: 20%; padding: 15px; border: 2px solid #107c10; border-radius: 5px; text-align: center; background: #f8fff8;">
                    <div style="font-size: 10pt; color: #666; margin-bottom: 5px; font-weight: bold;">TOTAL RECAUDADO</div>
                    <div style="font-size: 16pt; font-weight: bold; color: #107c10;">
                        <?= Yii::$app->formatter->asCurrency($summary['total_monto'], 'VES') ?>
                    </div>
                </td>
                <td style="width: 20%; padding: 15px; border: 2px solid #0078d4; border-radius: 5px; text-align: center; background: #f8faff;">
                    <div style="font-size: 10pt; color: #666; margin-bottom: 5px; font-weight: bold;">TOTAL PAGOS</div>
                    <div style="font-size: 16pt; font-weight: bold; color: #0078d4;">
                        <?= number_format($summary['total_count']) ?>
                    </div>
                </td>
                <td style="width: 20%; padding: 15px; border: 2px solid #107c10; border-radius: 5px; text-align: center; background: #f8fff8;">
                    <div style="font-size: 10pt; color: #666; margin-bottom: 5px; font-weight: bold;">CONCILIADOS</div>
                    <div style="font-size: 16pt; font-weight: bold; color: #107c10;">
                        <?= number_format($summary['conciliados'] ?? 0) ?>
                    </div>
                </td>
                <td style="width: 20%; padding: 15px; border: 2px solid #ff8c00; border-radius: 5px; text-align: center; background: #fffcf5;">
                    <div style="font-size: 10pt; color: #666; margin-bottom: 5px; font-weight: bold;">PENDIENTES</div>
                    <div style="font-size: 16pt; font-weight: bold; color: #ff8c00;">
                        <?= number_format($summary['pendientes'] ?? 0) ?>
                    </div>
                </td>
                <td style="width: 20%; padding: 15px; border: 2px solid #6f42c1; border-radius: 5px; text-align: center; background: #f9f8ff;">
                    <div style="font-size: 10pt; color: #666; margin-bottom: 5px; font-weight: bold;">PROMEDIO</div>
                    <div style="font-size: 14pt; font-weight: bold; color: #6f42c1;">
                        <?php
                        $avg = $summary['total_count'] > 0 ? $summary['total_monto'] / $summary['total_count'] : 0;
                        echo Yii::$app->formatter->asCurrency($avg, 'VES');
                        ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- RESUMEN POR CLÍNICA -->
    <?php if (!empty($summaryPorClinica)): ?>
        <div style="margin-bottom: 30px;">
            <h2 style="color: #2c3e50; border-bottom: 2px solid #107c10; padding-bottom: 5px; margin-bottom: 15px; text-align: center;">
                RESUMEN POR CLÍNICA
            </h2>

            <table style="width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 20px;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #107c10 0%, #0e6a0e 100%); color: white;">
                        <th style="width: 5%; padding: 10px; text-align: center; border: 1px solid #0e6a0e;">#</th>
                        <th style="width: 30%; padding: 10px; text-align: left; border: 1px solid #0e6a0e;">CLÍNICA</th>
                        <th style="width: 15%; padding: 10px; text-align: center; border: 1px solid #0e6a0e;">RIF</th>
                        <th style="width: 10%; padding: 10px; text-align: center; border: 1px solid #0e6a0e;">TOTAL PAGOS</th>
                        <th style="width: 15%; padding: 10px; text-align: center; border: 1px solid #0e6a0e;">ESTADO</th>
                        <th style="width: 15%; padding: 10px; text-align: right; border: 1px solid #0e6a0e;">TOTAL (Bs.)</th>
                        <th style="width: 10%; padding: 10px; text-align: center; border: 1px solid #0e6a0e;">% PART.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $granTotal = 0;
                    $granTotalPagos = 0;
                    $counter = 1;
                    foreach ($summaryPorClinica as $resumen):
                        $granTotal += (float)($resumen['total_monto'] ?? 0);
                        $granTotalPagos += (int)($resumen['total_pagos'] ?? 0);
                    ?>
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td style="padding: 8px; text-align: center; border: 1px solid #e0e0e0;"><?= $counter++ ?></td>
                            <td style="padding: 8px; text-align: left; border: 1px solid #e0e0e0; font-weight: bold;">
                                <?= Html::encode($resumen['clinica_nombre'] ?? 'N/A') ?>
                            </td>
                            <td style="padding: 8px; text-align: center; border: 1px solid #e0e0e0;">
                                <span style="background: #e9ecef; padding: 3px 8px; border-radius: 3px; font-size: 8pt;">
                                    <?= Html::encode($resumen['clinica_rif'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td style="padding: 8px; text-align: center; border: 1px solid #e0e0e0; font-weight: bold;">
                                <span style="background: #c7e0f4; padding: 5px 10px; border-radius: 3px; color: #005a9e;">
                                    <?= number_format($resumen['total_pagos'] ?? 0) ?>
                                </span>
                            </td>
                            <td style="padding: 8px; text-align: center; border: 1px solid #e0e0e0;">
                                <div style="display: inline-block;">
                                    <span style="background: #dff6dd; color: #107c10; padding: 4px 8px; border-radius: 3px; margin-right: 3px; font-size: 8pt;">
                                        <?= $resumen['conciliados'] ?? 0 ?> ✓
                                    </span>
                                    <span style="background: #fff4ce; color: #7a5c00; padding: 4px 8px; border-radius: 3px; font-size: 8pt;">
                                        <?= $resumen['pendientes'] ?? 0 ?> ⌛
                                    </span>
                                </div>
                            </td>
                            <td style="padding: 8px; text-align: right; border: 1px solid #e0e0e0; font-weight: bold; color: #107c10;">
                                <?= Yii::$app->formatter->asCurrency($resumen['total_monto'] ?? 0, 'VES') ?>
                            </td>
                            <td style="padding: 8px; text-align: center; border: 1px solid #e0e0e0;">
                                <?php
                                $percentage = $granTotal > 0 ? (($resumen['total_monto'] ?? 0) / $granTotal) * 100 : 0;
                                echo '<span style="background: #f3f2f1; padding: 3px 6px; border-radius: 3px;">' .
                                    number_format($percentage, 1) . '%</span>';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <!-- Resumen por Clínica table footer - FIXED -->
                <tfoot>
                    <tr style="background: linear-gradient(135deg, #2c3e50 0%, #1a2530 100%); color: white; font-weight: bold;">
                        <td colspan="3" style="padding: 12px; text-align: right; border: 2px solid #1a2530; font-size: 10pt; color: #ffffff !important;">
                            <span style="color: #ffffff !important;">TOTAL GENERAL:</span>
                        </td>
                        <td style="padding: 12px; text-align: center; border: 2px solid #1a2530; font-size: 10pt; color: #ffffff !important;">
                            <?= number_format($granTotalPagos) ?>
                        </td>
                        <td style="padding: 12px; text-align: center; border: 2px solid #1a2530; font-size: 10pt; color: #ffffff !important;">
                            <span style="background: #107c10; padding: 4px 8px; border-radius: 3px; margin-right: 5px; color: white;">
                                <?= array_sum(array_column($summaryPorClinica, 'conciliados')) ?> ✓
                            </span>
                            <span style="background: #ff8c00; padding: 4px 8px; border-radius: 3px; color: white;">
                                <?= array_sum(array_column($summaryPorClinica, 'pendientes')) ?> ⌛
                            </span>
                        </td>
                        <td style="padding: 12px; text-align: right; border: 2px solid #1a2530; font-size: 11pt; color: #ffffff !important;">
                            <?= Yii::$app->formatter->asCurrency($granTotal, 'VES') ?>
                        </td>
                        <td style="padding: 12px; text-align: center; border: 2px solid #1a2530; font-size: 10pt; color: #ffffff !important;">
                            100%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>

    <!-- DETALLE DE PAGOS -->
    <div style="margin-bottom: 40px;">
        <h2 style="color: #2c3e50; border-bottom: 2px solid #d13438; padding-bottom: 5px; margin-bottom: 15px; text-align: center;">
            DETALLE DE PAGOS
        </h2>

        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}',
            'tableOptions' => [
                'style' => 'width: 100%; border-collapse: collapse; font-size: 8.5pt;',
                'class' => 'pdf-table'
            ],
            'columns' => [
                [
                    'attribute' => 'id',
                    'label' => 'ID',
                    'headerOptions' => ['style' => 'background: #2c3e50; color: white; padding: 10px; text-align: center; border: 1px solid #1a2530;'],
                    'contentOptions' => ['style' => 'padding: 8px; text-align: center; border: 1px solid #e0e0e0;'],
                    'format' => 'raw',
                    'value' => function ($model) {
                        return '<span style="background: #c7e0f4; color: #005a9e; padding: 4px 8px; border-radius: 3px; font-weight: bold;">#' . $model->id . '</span>';
                    }
                ],
                [
                    'attribute' => 'numero_referencia_pago',
                    'label' => 'REFERENCIA BANCARIA',
                    'headerOptions' => ['style' => 'background: #2c3e50; color: white; padding: 10px; text-align: center; border: 1px solid #1a2530;'],
                    'contentOptions' => ['style' => 'padding: 8px; text-align: center; border: 1px solid #e0e0e0; font-family: monospace;'],
                    'value' => function ($model) {
                        $ref = $model->numero_referencia_pago;
                        if (empty($ref)) {
                            return '<span style="color: #999; font-style: italic;">N/A</span>';
                        }

                        // Format reference number based on payment method
                        $formattedRef = strtoupper($ref);

                        // For cash payments, show "EFECTIVO"
                        if (stripos($model->metodo_pago, 'efectivo') !== false) {
                            return '<span style="background: #e9ecef; padding: 4px 8px; border-radius: 3px; color: #6c757d; font-weight: bold;">EFECTIVO</span>';
                        }

                        // For other payments, show the reference
                        return '<span style="background: #e8f4fd; padding: 4px 8px; border-radius: 3px; color: #005a9e; font-weight: bold; font-family: monospace;">' .
                            Html::encode($formattedRef) . '</span>';
                    },
                    'format' => 'raw',
                ],
                [
                    'label' => 'AFILIADO',
                    'headerOptions' => ['style' => 'background: #2c3e50; color: white; padding: 10px; text-align: left; border: 1px solid #1a2530;'],
                    'contentOptions' => ['style' => 'padding: 8px; text-align: left; border: 1px solid #e0e0e0;'],
                    'value' => function ($model) {
                        $nombre = $model->userDatos ?
                            Html::encode($model->userDatos->nombres . ' ' . $model->userDatos->apellidos) :
                            'N/A';
                        return '<div style="font-weight: bold;">' . $nombre . '</div>';
                    },
                    'format' => 'raw',
                ],
                [
                    'label' => 'CÉDULA',
                    'headerOptions' => ['style' => 'background: #2c3e50; color: white; padding: 10px; text-align: center; border: 1px solid #1a2530;'],
                    'contentOptions' => ['style' => 'padding: 8px; text-align: center; border: 1px solid #e0e0e0; font-weight: bold;'],
                    'value' => function ($model) {
                        return $model->userDatos ? Html::encode($model->userDatos->cedula) : 'N/A';
                    }
                ],
                [
                    'attribute' => 'monto_usd',
                    'label' => 'MONTO (Bs.)',
                    'headerOptions' => ['style' => 'background: #2c3e50; color: white; padding: 10px; text-align: right; border: 1px solid #1a2530;'],
                    'contentOptions' => ['style' => 'padding: 8px; text-align: right; border: 1px solid #e0e0e0; font-weight: bold; color: #107c10;'],
                    'format' => ['currency', 'VES'],
                ],
                [
                    'attribute' => 'fecha_pago',
                    'label' => 'FECHA',
                    'headerOptions' => ['style' => 'background: #2c3e50; color: white; padding: 10px; text-align: center; border: 1px solid #1a2530;'],
                    'contentOptions' => ['style' => 'padding: 8px; text-align: center; border: 1px solid #e0e0e0;'],
                    'format' => ['date', 'php:d/m/Y'],
                ],
                [
                    'attribute' => 'metodo_pago',
                    'label' => 'MÉTODO',
                    'headerOptions' => ['style' => 'background: #2c3e50; color: white; padding: 10px; text-align: center; border: 1px solid #1a2530;'],
                    'contentOptions' => ['style' => 'padding: 8px; text-align: center; border: 1px solid #e0e0e0;'],
                    'value' => function ($model) {
                        $method = Html::encode($model->metodo_pago ?: 'N/A');
                        $color = '#0078d4';
                        if (stripos($method, 'transferencia') !== false) $color = '#0078d4';
                        elseif (stripos($method, 'efectivo') !== false) $color = '#107c10';
                        elseif (stripos($method, 'tarjeta') !== false) $color = '#d13438';

                        return '<span style="color: ' . $color . '; font-weight: bold;">' . $method . '</span>';
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'estatus',
                    'label' => 'ESTADO',
                    'headerOptions' => ['style' => 'background: #2c3e50; color: white; padding: 10px; text-align: center; border: 1px solid #1a2530;'],
                    'contentOptions' => ['style' => 'padding: 8px; text-align: center; border: 1px solid #e0e0e0;'],
                    'format' => 'raw',
                    'value' => function ($model) {
                        if ($model->estatus === 'Conciliado') {
                            return '<span style="background: #dff6dd; color: #107c10; padding: 5px 10px; border-radius: 3px; font-weight: bold; display: inline-block; min-width: 100px;">CONCILIADO</span>';
                        } else {
                            return '<span style="background: #fff4ce; color: #7a5c00; padding: 5px 10px; border-radius: 3px; font-weight: bold; display: inline-block; min-width: 100px;">PENDIENTE</span>';
                        }
                    }
                ],
                [
                    'label' => 'CLÍNICA',
                    'headerOptions' => ['style' => 'background: #2c3e50; color: white; padding: 10px; text-align: left; border: 1px solid #1a2530;'],
                    'contentOptions' => ['style' => 'padding: 8px; text-align: left; border: 1px solid #e0e0e0;'],
                    'value' => function ($model) {
                        $clinicaNombre = 'Sin Clínica';
                        if ($model->contratos && count($model->contratos) > 0) {
                            foreach ($model->contratos as $contrato) {
                                if ($contrato->clinica) {
                                    $clinicaNombre = Html::encode($contrato->clinica->nombre);
                                    break;
                                }
                            }
                        }
                        return '<div style="font-weight: bold;">' . $clinicaNombre . '</div>';
                    },
                    'format' => 'raw',
                ],
            ],
        ]);
        ?>
    </div>

    <!-- TOTAL GENERAL - MINIMAL PROFESSIONAL VERSION -->
    <div style="text-align: center; margin: 25px auto 35px auto; page-break-inside: avoid;">
        <div style="
        display: inline-block;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 15px 30px;
        min-width: 350px;
    ">
            <!-- Title -->
            <div style="
            font-size: 12pt;
            font-weight: 600;
            color: #495057;
            margin: 0 0 5px 0;
            letter-spacing: 0.5px;
        ">
                Resumen Financiero
            </div>

            <!-- Amount -->
            <div style="
            font-size: 18pt;
            font-weight: 700;
            color: #28a745;
            margin: 5px 0;
            line-height: 1.1;
        ">
                <?= Yii::$app->formatter->asCurrency($summary['total_monto'], 'VES') ?>
            </div>

            <!-- Summary -->
            <div style="
            font-size: 9pt;
            color: #6c757d;
            margin-top: 8px;
        ">
                <span style="margin-right: 10px;">
                    <i class="fas fa-file-invoice-dollar" style="margin-right: 3px;"></i>
                    <?= number_format($summary['total_count']) ?> transacciones
                </span>
                <span style="margin-right: 10px;">
                    <i class="fas fa-check-circle" style="color: #28a745; margin-right: 3px;"></i>
                    <?= number_format($summary['conciliados'] ?? 0) ?>
                </span>
                <span>
                    <i class="fas fa-clock" style="color: #ffc107; margin-right: 3px;"></i>
                    <?= number_format($summary['pendientes'] ?? 0) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- FOOTER DEL REPORTE - CENTERED -->
    <div style="
    margin-top: 40px;
    padding-top: 20px;
    border-top: 2px solid #0078d4;
    text-align: center;
    font-size: 9pt;
    color: #666;
    page-break-inside: avoid;
">
        <!-- Logo SISPSA Centrado -->
        <div style="margin-bottom: 15px;">
            <img src="<?= Yii::getAlias('@web') ?>/img/sispsa.png"
                alt="SISPSA"
                style="max-width: 100px; max-height: 40px; margin: 0 auto; display: block;"
                onerror="this.style.display='none'">
        </div>

        <!-- Título Sistema -->
        <div style="font-size: 10pt; font-weight: bold; color: #0078d4; margin-bottom: 10px;">
            Sistema Integrado de Salud SISPSA
        </div>

        <!-- Información del Reporte - Centered -->
        <div style="margin-bottom: 15px; line-height: 1.5;">
            <div style="display: inline-block; text-align: left; margin: 0 20px; vertical-align: top;">
                <div style="font-weight: bold; color: #2c3e50; margin-bottom: 3px;">RESUMEN GENERAL</div>
                <div style="font-size: 8.5pt;">
                    Total Pagos: <?= number_format($summary['total_count']) ?><br>
                    Total Recaudado: <?= Yii::$app->formatter->asCurrency($summary['total_monto'], 'VES') ?>
                </div>
            </div>

            <div style="display: inline-block; text-align: left; margin: 0 20px; vertical-align: top;">
                <div style="font-weight: bold; color: #2c3e50; margin-bottom: 3px;">INFORMACIÓN</div>
                <div style="font-size: 8.5pt;">
                    Generado: <?= $generatedAt ?><br>
                    Período: <?= $startDate ?> al <?= $endDate ?>
                </div>
            </div>
        </div>

        <!-- Línea divisoria -->
        <div style="height: 1px; background: linear-gradient(90deg, transparent, #0078d4, transparent); width: 70%; margin: 15px auto;"></div>

        <!-- Aviso confidencial -->
        <div style="
        font-size: 8pt;
        color: #999;
        font-style: italic;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #ddd;
        text-align: center;
    ">
            📄 Documento generado automáticamente por el Sistema SISPSA<br>
            🔒 Confidencial - Uso interno exclusivo
        </div>
    </div>