<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestro $model
 * @var app\models\UserDatos $afiliado
 * @var array $baremos
 * @var float $total
 */

$this->title = ($model->es_cita == 1 ? 'Cita Médica' : 'Atención Médica') . ' #' . $model->id;
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>

    <!-- Font Awesome 5 (Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <?php $this->head() ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background: #f0f2f5;
            padding: 40px 20px;
            margin: 0;
        }

        .print-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        /* Header - Clean with red accent */
        .print-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            color: #2c3e50;
            padding: 35px 35px;
            text-align: center;
            position: relative;
            border-bottom: 4px solid #e74c3c;
        }

        .heartbeat-icon-header {
            position: absolute;
            top: 50%;
            left: 35px;
            transform: translateY(-50%);
            font-size: 55px;
            color: #e74c3c;
            opacity: 0.8;
        }

        .heartbeat-icon-header-right {
            position: absolute;
            top: 50%;
            right: 35px;
            transform: translateY(-50%);
            font-size: 55px;
            color: #e74c3c;
            opacity: 0.8;
        }

        @keyframes heartbeat {

            0%,
            100% {
                transform: translateY(-50%) scale(1);
            }

            25% {
                transform: translateY(-50%) scale(1.2);
            }

            35% {
                transform: translateY(-50%) scale(1.1);
            }

            45% {
                transform: translateY(-50%) scale(1.2);
            }

            55% {
                transform: translateY(-50%) scale(1);
            }
        }

        .heartbeat-icon-header,
        .heartbeat-icon-header-right {
            animation: heartbeat 1.5s ease-in-out infinite;
        }

        .print-header h1 {
            font-size: 32px;
            margin: 0 0 10px 0;
            font-weight: 700;
            letter-spacing: -0.3px;
            color: #2c3e50;
        }

        .print-header .record-id {
            background: #e74c3c;
            display: inline-block;
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 500;
            margin-top: 10px;
            color: white;
        }

        .print-header .record-id i {
            margin-right: 5px;
        }

        /* Content Sections */
        .print-content {
            padding: 35px;
        }

        .section {
            margin-bottom: 35px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            border-left: 4px solid #e74c3c;
            padding-left: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #e74c3c;
            font-size: 18px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-row {
            display: flex;
            border-bottom: 1px solid #e9ecef;
            padding: 12px 0;
        }

        .info-label {
            font-weight: 600;
            width: 160px;
            color: #6c757d;
            font-size: 13px;
            letter-spacing: 0.3px;
        }

        .info-value {
            flex: 1;
            color: #2c3e50;
            font-size: 14px;
            font-weight: 500;
        }

        .info-value i {
            margin-right: 6px;
            color: #e74c3c;
            width: 16px;
        }

        /* Services Table */
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .services-table th {
            background: #f8f9fa;
            padding: 12px 12px;
            text-align: left;
            font-weight: 700;
            color: #2c3e50;
            font-size: 13px;
            border-bottom: 2px solid #e74c3c;
        }

        .services-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 13px;
            color: #495057;
            vertical-align: top;
        }

        .services-table tr:hover td {
            background-color: #fef9f9;
        }

        .services-table tr:last-child td {
            border-bottom: none;
        }

        .total-row {
            background: #fef9f9;
            font-weight: 700;
        }

        .total-row td {
            border-top: 2px solid #e74c3c;
            font-size: 15px;
            font-weight: 800;
            color: #e74c3c;
            background: #fef9f9;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.cita {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-badge.atencion {
            background: #fef9f9;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        .status-badge.atendido {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-badge.no-atendido {
            background: #f5f5f5;
            color: #757575;
        }

        .status-badge i {
            font-size: 11px;
        }

        /* Footer */
        .print-footer {
            background: #f8f9fa;
            padding: 20px 35px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .footer-left {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #6c757d;
        }

        .footer-left i {
            color: #e74c3c;
            font-size: 16px;
        }

        .footer-right {
            font-size: 11px;
            color: #adb5bd;
        }

        /* Print Button */
        .print-button-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print-page {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 12px 35px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-print-page:hover {
            background: #c0392b;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }

        .btn-print-page i {
            font-size: 16px;
        }

        .text-muted {
            color: #adb5bd;
            font-style: italic;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .print-container {
                box-shadow: none;
                border-radius: 0;
            }

            .print-button-container {
                display: none;
            }

            .section {
                page-break-inside: avoid;
            }

            .print-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .services-table th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="print-button-container">
        <button onclick="window.print();" class="btn-print-page">
            <i class="fas fa-print"></i> Imprimir / Guardar como PDF
        </button>
    </div>

    <div class="print-container">
        <div class="print-header">
            <i class="fas fa-heartbeat heartbeat-icon-header"></i>
            <i class="fas fa-heartbeat heartbeat-icon-header-right"></i>
            <h1>
                <?= Html::encode($model->es_cita == 1 ? 'CITA MÉDICA' : 'ATENCIÓN MÉDICA') ?>
            </h1>
            <div class="record-id">
                <i class="fas fa-hashtag"></i> Registro #<?= $model->id ?>
            </div>
        </div>

        <div class="print-content">
            <!-- Patient Information Section -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-user-circle"></i>
                    <span>INFORMACIÓN DEL PACIENTE</span>
                </div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Nombre Completo:</div>
                        <div class="info-value"><i class="fas fa-user"></i> <?= Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Documento de Identidad:</div>
                        <div class="info-value"><i class="fas fa-id-card"></i> <?= Html::encode($afiliado->tipo_cedula . '-' . $afiliado->cedula) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Plan Médico:</div>
                        <div class="info-value"><i class="fas fa-file-invoice"></i> <?= $afiliado->plan ? Html::encode($afiliado->plan->nombre) : '<span class="text-muted">No especificado</span>' ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Centro Médico:</div>
                        <div class="info-value"><i class="fas fa-hospital"></i> <?= $model->clinica ? Html::encode($model->clinica->nombre) : '<span class="text-muted">No especificada</span>' ?></div>
                    </div>
                </div>
            </div>

            <!-- Medical Attention Details -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-clinic-medical"></i>
                    <span>DETALLES DE LA <?= $model->es_cita == 1 ? 'CITA' : 'ATENCIÓN' ?></span>
                </div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Tipo de Atención:</div>
                        <div class="info-value">
                            <span class="status-badge <?= $model->es_cita == 1 ? 'cita' : 'atencion' ?>">
                                <i class="fas <?= $model->es_cita == 1 ? 'fa-calendar-alt' : 'fa-heartbeat' ?>"></i>
                                <?= $model->es_cita == 1 ? 'Cita Programada' : 'Atención Médica' ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha de Registro:</div>
                        <div class="info-value"><i class="far fa-calendar-alt"></i> <?= Yii::$app->formatter->asDate($model->fecha) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Hora de Registro:</div>
                        <div class="info-value"><i class="far fa-clock"></i> <?= Yii::$app->formatter->asTime($model->hora) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Estado de Atención:</div>
                        <div class="info-value">
                            <span class="status-badge <?= $model->atendido == 1 ? 'atendido' : 'no-atendido' ?>">
                                <i class="fas <?= $model->atendido == 1 ? 'fa-check-circle' : 'fa-clock' ?>"></i>
                                <?= $model->atendido == 1 ? 'Atendido' : 'Pendiente' ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($model->fecha_atencion): ?>
                        <div class="info-row">
                            <div class="info-label">Fecha de Atención:</div>
                            <div class="info-value"><i class="fas fa-calendar-check"></i> <?= Yii::$app->formatter->asDate($model->fecha_atencion) ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($model->hora_atencion): ?>
                        <div class="info-row">
                            <div class="info-label">Hora de Atención:</div>
                            <div class="info-value"><i class="fas fa-hourglass-half"></i> <?= Yii::$app->formatter->asTime($model->hora_atencion) ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-label">Analista de Admisión:</div>
                        <div class="info-value">
                            <?= !empty($model->admission_analyst) ? '<i class="fas fa-user-check"></i> ' . Html::encode($model->admission_analyst) : '<span class="text-muted"><i class="fas fa-user-slash"></i> No asignado</span>' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Section -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-notes-medical"></i>
                    <span>SERVICIOS MÉDICOS</span>
                </div>
                <?php if (empty($baremos)): ?>
                    <p class="text-muted"><i class="fas fa-info-circle"></i> No hay servicios registrados para esta atención.</p>
                <?php else: ?>
                    <table class="services-table">
                        <thead>
                            <tr>
                                <th style="width: 45px;">#</th>
                                <th style="width: 22%;">Área</th>
                                <th style="width: 25%;">Servicio</th>
                                <th style="width: 33%;">Descripción</th>
                                <th style="width: 15%; text-align: right;">Costo (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 1; ?>
                            <?php foreach ($baremos as $baremo): ?>
                                <?php
                                $serviceArea = is_array($baremo) ? ($baremo['area_nombre'] ?? '') : ($baremo->area->nombre ?? '');
                                $serviceName = is_array($baremo) ? $baremo['nombre_servicio'] : $baremo->nombre_servicio;
                                $serviceDescription = is_array($baremo) ? ($baremo['descripcion'] ?? '') : ($baremo->descripcion ?? '');
                                $servicePrice = is_array($baremo) ? ($baremo['precio'] ?? 0) : ($baremo->precio ?? 0);
                                ?>
                                <tr>
                                    <td style="text-align: center; font-weight: 600; color: #e74c3c;"><?= $counter++ ?></td>
                                    <td>
                                        <i class="fas fa-tag" style="margin-right: 6px; color: #e74c3c;"></i>
                                        <?= Html::encode($serviceArea ?: '<span class="text-muted">No especificada</span>') ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-stethoscope" style="margin-right: 6px; color: #e74c3c;"></i>
                                        <?= Html::encode($serviceName) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($serviceDescription)): ?>
                                            <i class="fas fa-align-left" style="margin-right: 6px; color: #e74c3c;"></i>
                                            <?= Html::encode($serviceDescription) ?>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-minus-circle"></i> Sin descripción</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 600;">$ <?= number_format($servicePrice, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="4" style="text-align: right; font-weight: 700;">TOTAL GENERAL:</td>
                                <td style="text-align: right; font-weight: 800; font-size: 16px;">$ <?= number_format($total, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Description Section -->
            <?php if (!empty($model->descripcion)): ?>
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-file-alt"></i>
                        <span>DESCRIPCIÓN DETALLADA</span>
                    </div>
                    <div style="background: #fef9f9; border-radius: 10px; padding: 20px; margin-top: 5px; border-left: 3px solid #e74c3c;">
                        <div style="line-height: 1.8; color: #495057;">
                            <i class="fas fa-quote-left" style="color: #e74c3c; opacity: 0.6; margin-right: 10px;"></i>
                            <?= nl2br(Html::encode($model->descripcion)) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="print-footer">
            <div class="footer-content">
                <div class="footer-left">
                    <i class="fas fa-heartbeat"></i>
                    <span>Documento de Atención Médica</span>
                </div>
                <div class="footer-right">
                    <i class="far fa-calendar-alt"></i> <?= Yii::$app->formatter->asDate(date('Y-m-d')) ?> &nbsp;|&nbsp;
                    <i class="far fa-clock"></i> <?= Yii::$app->formatter->asTime(date('H:i:s')) ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>