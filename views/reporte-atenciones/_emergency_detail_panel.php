<?php
// app/views/reporte-atenciones/_emergency_detail_panel.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $emergencyData */
/** @var \app\models\RmClinica $clinic */
/** @var array|null $dateRange */
/** @var bool $isCartera */

$colors = [
    'Consulta' => ['bg' => '#e8f4fd', 'border' => '#0078d4', 'text' => '#0078d4', 'icon' => 'fa-user-md', 'hover' => '#d0e8f7'],
    'Cirugía' => ['bg' => '#fdf0f0', 'border' => '#d13438', 'text' => '#d13438', 'icon' => 'fa-scalpel', 'hover' => '#fce0e0'],
    'Examen' => ['bg' => '#fff8e6', 'border' => '#ff8c00', 'text' => '#ff8c00', 'icon' => 'fa-microscope', 'hover' => '#ffe0b2'],
    'Emergencia' => ['bg' => '#fde8e8', 'border' => '#d13438', 'text' => '#d13438', 'icon' => 'fa-ambulance', 'hover' => '#f5d0d0'],
    'Imagen' => ['bg' => '#f3e8ff', 'border' => '#6f42c1', 'text' => '#6f42c1', 'icon' => 'fa-x-ray', 'hover' => '#e0ccff'],
    'Laboratorio' => ['bg' => '#e6f7f0', 'border' => '#17a2b8', 'text' => '#17a2b8', 'icon' => 'fa-flask', 'hover' => '#ccefe6'],
    'Tratamiento' => ['bg' => '#fef3e8', 'border' => '#fd7e14', 'text' => '#fd7e14', 'icon' => 'fa-pills', 'hover' => '#fde0c0'],
    'Hospitalización' => ['bg' => '#f0e6ff', 'border' => '#6610f2', 'text' => '#6610f2', 'icon' => 'fa-hospital', 'hover' => '#dccfff'],
    'Maternidad' => ['bg' => '#fce4ec', 'border' => '#e83e8c', 'text' => '#e83e8c', 'icon' => 'fa-baby', 'hover' => '#f8c8d8'],
    'Odontología' => ['bg' => '#e3f2fd', 'border' => '#0d6efd', 'text' => '#0d6efd', 'icon' => 'fa-tooth', 'hover' => '#cce0ff'],
    'Sin Categoría' => ['bg' => '#f8f9fa', 'border' => '#6c757d', 'text' => '#6c757d', 'icon' => 'fa-question-circle', 'hover' => '#e9ecef'],
];
?>

<div class="emergency-detail-panel" style="font-family: 'Segoe UI', system-ui, sans-serif;">

    <!-- ============================================================ -->
    <!-- HEADER -->
    <!-- ============================================================ -->
    <div style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%) !important; padding: 2rem 2.5rem; border-radius: 0 0 20px 20px; color: white; margin-bottom: 2rem;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 style="font-weight: 600; margin: 0; font-size: 2rem; color: #ffffff !important;">
                    <i class="fas fa-ambulance me-2" style="color: #ffffff !important;"></i>
                    Detalle de Emergencias
                </h3>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 1.4rem; color: #ffffff !important;">
                    <?= Html::encode($clinic->nombre ?? 'Clínica') ?>
                </p>
                <small style="opacity: 0.8; color: #ffffff !important; font-size: 1.1rem;">
                    <?php if ($isCartera): ?>
                        <i class="fas fa-database me-1" style="color: #ffffff !important;"></i> Cartera completa
                    <?php else: ?>
                        <i class="fas fa-calendar-alt me-1" style="color: #ffffff !important;"></i>
                        <?= Yii::$app->formatter->asDate($dateRange['from'] ?? null) ?>
                        al <?= Yii::$app->formatter->asDate($dateRange['to'] ?? null) ?>
                    <?php endif; ?>
                </small>
            </div>
            <!-- CLOSE BUTTON WITH ICON -->
            <button type="button" class="emergency-panel-close" aria-label="Cerrar" style="
                background: rgba(255,255,255,0.15);
                border: 1px solid rgba(255,255,255,0.3);
                color: #ffffff;
                width: 48px;
                height: 48px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 1.4rem;
                padding: 0;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            "
                onmouseover="this.style.background='rgba(255,255,255,0.25)'; this.style.transform='scale(1.05)';"
                onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='scale(1)';"
                onclick="window.closeEmergencyPanel()">
                <i class="fas fa-times" style="color: #ffffff; font-size: 1.4rem;"></i>
            </button>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- SUMMARY CARDS -->
    <!-- ============================================================ -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.2rem; padding: 0 2rem; margin-bottom: 2rem;">
        <div style="background: #f0f7ff !important; border-left: 4px solid #0078d4; padding: 1rem 1.5rem; border-radius: 8px;">
            <div style="font-size: 0.85rem; text-transform: uppercase; color: #6c757d; font-weight: 600;">Total Emergencias</div>
            <div style="font-size: 2.4rem; font-weight: 700; color: #0078d4;"><?= number_format($emergencyData['totals']['total'] ?? 0) ?></div>
        </div>
        <div style="background: #f0faf0 !important; border-left: 4px solid #28a745; padding: 1rem 1.5rem; border-radius: 8px;">
            <div style="font-size: 0.85rem; text-transform: uppercase; color: #6c757d; font-weight: 600;">Precio Total</div>
            <div style="font-size: 2.4rem; font-weight: 700; color: #28a745;">$<?= number_format($emergencyData['totals']['total_precio'] ?? 0, 2) ?></div>
        </div>
        <div style="background: #fdf0f0 !important; border-left: 4px solid #d13438; padding: 1rem 1.5rem; border-radius: 8px;">
            <div style="font-size: 0.85rem; text-transform: uppercase; color: #6c757d; font-weight: 600;">Costo Real</div>
            <div style="font-size: 2.4rem; font-weight: 700; color: #d13438;">$<?= number_format($emergencyData['totals']['total_costo_real'] ?? 0, 2) ?></div>
        </div>
        <div style="background: #f5f0ff !important; border-left: 4px solid #6f42c1; padding: 1rem 1.5rem; border-radius: 8px;">
            <div style="font-size: 0.85rem; text-transform: uppercase; color: #6c757d; font-weight: 600;">Margen</div>
            <?php
            $margin = $emergencyData['totals']['total_margen'] ?? 0;
            $marginPercent = ($emergencyData['totals']['total_precio'] ?? 0) > 0
                ? round(($margin / ($emergencyData['totals']['total_precio'] ?? 1)) * 100, 1)
                : 0;
            ?>
            <div style="font-size: 2.4rem; font-weight: 700; color: <?= $marginPercent >= 50 ? '#28a745' : ($marginPercent >= 30 ? '#ff8c00' : '#d13438') ?>;">
                $<?= number_format($margin, 2) ?>
                <small style="font-size: 1.1rem; font-weight: 400;">(<?= $marginPercent ?>%)</small>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- CATEGORY BREAKDOWN - COLOR CODED ROWS -->
    <!-- ============================================================ -->
    <?php if (!empty($emergencyData['grouped'])): ?>
        <div style="padding: 0 2rem;">
            <h5 style="font-weight: 600; color: #2c3e50; margin-bottom: 1.2rem; font-size: 1.4rem;">
                <i class="fas fa-tags me-2" style="color: #0078d4;"></i>
                Categorías de Servicios
                <span style="font-size: 1rem; font-weight: 400; color: #6c757d; margin-left: 0.5rem;">
                    (Haz clic en cada categoría para ver los detalles)
                </span>
            </h5>

            <?php foreach ($emergencyData['grouped'] as $categoria => $group): ?>
                <?php
                $colorInfo = $colors[$categoria] ?? $colors['Sin Categoría'];
                $percentage = ($emergencyData['totals']['total'] ?? 0) > 0
                    ? round(($group['count'] / ($emergencyData['totals']['total'] ?? 1)) * 100, 1)
                    : 0;
                ?>
                <!-- CATEGORY ROW WITH COLORED BACKGROUND -->
                <div style="background: <?= $colorInfo['bg'] ?> !important; border-left: 5px solid <?= $colorInfo['border'] ?>; border-radius: 8px; margin-bottom: 1rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); transition: all 0.2s ease;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; cursor: pointer; background: <?= $colorInfo['bg'] ?> !important;" class="category-toggle" data-category="<?= Html::encode($categoria) ?>">
                        <div>
                            <i class="fas <?= $colorInfo['icon'] ?>" style="color: <?= $colorInfo['border'] ?>; font-size: 1.3rem; margin-right: 12px; width: 24px; text-align: center;"></i>
                            <strong style="font-size: 1.2rem; color: <?= $colorInfo['text'] ?> !important;"><?= Html::encode($categoria) ?></strong>
                            <span class="badge ms-2" style="font-size: 0.85rem; padding: 0.35rem 0.9rem; background: <?= $colorInfo['border'] ?>; color: #ffffff;"><?= $group['count'] ?> servicios</span>
                        </div>
                        <div>
                            <span style="font-weight: 600; color: #28a745; margin-right: 1.2rem; font-size: 1.1rem;">
                                $<?= number_format($group['total_precio'] ?? 0, 2) ?>
                            </span>
                            <span style="font-size: 1rem; color: #6c757d;"><?= $percentage ?>%</span>
                            <i class="fas fa-chevron-down ms-2 category-arrow" style="font-size: 0.9rem; color: <?= $colorInfo['border'] ?>; transition: transform 0.3s ease;"></i>
                        </div>
                    </div>
                    <!-- DETAILS PANEL -->
                    <div class="category-details" data-category="<?= Html::encode($categoria) ?>" style="display: none; padding: 0 1.5rem 1.2rem 1.5rem; border-top: 1px solid rgba(0,0,0,0.05); background: <?= $colorInfo['bg'] ?> !important;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-top: 1rem;">
                            <div style="background: rgba(255,255,255,0.7) !important; padding: 0.7rem 1rem; border-radius: 6px;">
                                <small style="color: #6c757d; font-size: 0.85rem;">Costo Real</small>
                                <div style="font-weight: 600; color: #d13438; font-size: 1.2rem;">$<?= number_format($group['total_costo_real'] ?? 0, 2) ?></div>
                            </div>
                            <div style="background: rgba(255,255,255,0.7) !important; padding: 0.7rem 1rem; border-radius: 6px;">
                                <small style="color: #6c757d; font-size: 0.85rem;">Margen</small>
                                <?php
                                $catMargin = $group['total_margen'] ?? 0;
                                $catMarginPercent = ($group['total_precio'] ?? 0) > 0
                                    ? round(($catMargin / ($group['total_precio'] ?? 1)) * 100, 1)
                                    : 0;
                                ?>
                                <div style="font-weight: 600; color: <?= $catMarginPercent >= 50 ? '#28a745' : ($catMarginPercent >= 30 ? '#ff8c00' : '#d13438') ?>; font-size: 1.2rem;">
                                    $<?= number_format($catMargin, 2) ?> (<?= $catMarginPercent ?>%)
                                </div>
                            </div>
                        </div>
                        <div style="max-height: 300px; overflow-y: auto; margin-top: 1rem;">
                            <table style="width: 100%; font-size: 1rem; border-collapse: collapse;">
                                <thead style="background: rgba(255,255,255,0.6) !important;">
                                    <tr>
                                        <th style="text-align: left; padding: 0.6rem 0.8rem; font-weight: 600; color: #2c3e50 !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Servicio</th>
                                        <th style="text-align: left; padding: 0.6rem 0.8rem; font-weight: 600; color: #2c3e50 !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Paciente</th>
                                        <th style="text-align: center; padding: 0.6rem 0.8rem; font-weight: 600; color: #2c3e50 !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Fecha</th>
                                        <th style="text-align: right; padding: 0.6rem 0.8rem; font-weight: 600; color: #2c3e50 !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Precio</th>
                                        <th style="text-align: right; padding: 0.6rem 0.8rem; font-weight: 600; color: #2c3e50 !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Costo Real</th>
                                        <th style="text-align: right; padding: 0.6rem 0.8rem; font-weight: 600; color: #2c3e50 !important; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">% Margen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($group['items'] as $item): ?>
                                        <?php
                                        $itemPrecio = $item['total_precio'] ?? 0;
                                        $itemCostoReal = $item['total_costo_real'] ?? 0;
                                        $itemMargin = $itemPrecio - $itemCostoReal;
                                        $itemMarginPercent = $itemPrecio > 0 ? round(($itemMargin / $itemPrecio) * 100, 1) : 0;
                                        ?>
                                        <tr style="border-bottom: 1px solid rgba(0,0,0,0.05); background: rgba(255,255,255,0.4) !important;">
                                            <td style="padding: 0.6rem 0.8rem;">
                                                <strong style="color: <?= $colorInfo['text'] ?> !important; font-size: 1.05rem;"><?= Html::encode($item['nombre_servicio'] ?? 'N/A') ?></strong>
                                                <?php if (!empty($item['nombre_doctor'])): ?>
                                                    <br><small style="color: #6c757d; font-size: 0.85rem;">Dr. <?= Html::encode($item['nombre_doctor']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding: 0.6rem 0.8rem;">
                                                <span style="color: #2c3e50 !important; font-size: 1.05rem;"><?= Html::encode($item['nombres'] ?? '') ?> <?= Html::encode($item['apellidos'] ?? '') ?></span>
                                                <br><small style="color: #6c757d; font-size: 0.85rem;">
                                                    <?= Html::encode(($item['tipo_cedula'] ? $item['tipo_cedula'] . '-' : '') . ($item['cedula'] ?? '')) ?>
                                                </small>
                                            </td>
                                            <td style="text-align: center; padding: 0.6rem 0.8rem; color: #2c3e50 !important; font-size: 1rem;">
                                                <?= Yii::$app->formatter->asDate($item['fecha'] ?? null) ?>
                                                <br><small style="color: #6c757d; font-size: 0.85rem;"><?= $item['hora'] ?? '' ?></small>
                                            </td>
                                            <td style="text-align: right; padding: 0.6rem 0.8rem; font-weight: 600; color: #0078d4; font-size: 1.05rem;">
                                                $<?= number_format($itemPrecio, 2) ?>
                                            </td>
                                            <td style="text-align: right; padding: 0.6rem 0.8rem; font-weight: 600; color: #d13438; font-size: 1.05rem;">
                                                $<?= number_format($itemCostoReal, 2) ?>
                                            </td>
                                            <td style="text-align: right; padding: 0.6rem 0.8rem; font-weight: 600; color: <?= $itemMarginPercent >= 50 ? '#28a745' : ($itemMarginPercent >= 30 ? '#ff8c00' : '#d13438') ?>; font-size: 1.05rem;">
                                                <?= $itemMarginPercent ?>%
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 3rem 1.5rem; color: #6c757d;">
            <i class="fas fa-inbox fa-3x d-block mb-3 opacity-25"></i>
            <p style="font-size: 1.2rem;">No hay emergencias registradas para esta clínica</p>
        </div>
    <?php endif; ?>

    <!-- ============================================================ -->
    <!-- FOOTER -->
    <!-- ============================================================ -->
    <div style="padding: 1.2rem 2rem; border-top: 1px solid #e9ecef; margin-top: 2rem; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; border-radius: 0 0 20px 20px;">
        <div style="font-size: 1rem; color: #6c757d;">
            <i class="fas fa-info-circle me-1" style="color: #0078d4;"></i>
            <strong style="color: #2c3e50 !important; font-size: 1.05rem;">Nota:</strong> <span style="color: #2c3e50 !important; font-size: 1.05rem;">Se muestran únicamente los servicios clasificados como</span> <span class="badge bg-warning" style="font-size: 0.9rem; padding: 0.35rem 0.9rem; color: #2c3e50 !important;">Emergencias</span>
        </div>
        <div style="font-size: 1rem; color: #6c757d;">
            <i class="fas fa-file-alt me-1" style="color: #0078d4;"></i>
            <span style="color: #2c3e50 !important; font-size: 1.05rem;"><?= number_format($emergencyData['totals']['total'] ?? 0) ?> registros encontrados</span>
        </div>
    </div>
</div>