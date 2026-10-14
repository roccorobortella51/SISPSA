<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var app\models\Corporativo $corporativo */
/** @var array $afiliados */
/** @var array $allCuotas */
/** @var float $grandTotal */

$this->title = 'Pago Corporativo: ' . Html::encode($corporativo->nombre);
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($corporativo->nombre), 'url' => ['view', 'id' => $corporativo->id]];
$this->params['breadcrumbs'][] = 'Pago Corporativo';

$this->registerCss(
    <<<CSS
/* ===== MICROSOFT FLUENT DESIGN SYSTEM ===== */
body {
    font-family: "Segoe UI", SegoeUI, "Helvetica Neue", Helvetica, Arial, sans-serif !important;
    font-size: 14px !important;
    color: #323130 !important;
    background-color: #faf9f8 !important;
}

/* ===== TYPOGRAPHY ===== */
h1 { font-size: 28px !important; font-weight: 600 !important; }
h2 { font-size: 24px !important; font-weight: 600 !important; }
h3 { font-size: 20px !important; font-weight: 600 !important; }
h4 { font-size: 18px !important; font-weight: 600 !important; }
h5 { font-size: 16px !important; font-weight: 600 !important; }

/* ===== BUTTONS ===== */
.btn {
    border-radius: 4px !important;
    padding: 8px 20px !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    min-height: 36px !important;
}

.btn-lg {
    padding: 10px 24px !important;
    font-size: 14px !important;
    min-height: 42px !important;
}

.btn-success {
    background-color: #107c10 !important;
    border-color: #107c10 !important;
    color: #ffffff !important;
}

.btn-success:hover {
    background-color: #0e700e !important;
    border-color: #0e700e !important;
}

.btn-secondary {
    background-color: #f3f2f1 !important;
    border-color: #8a8886 !important;
    color: #323130 !important;
}

.btn-secondary:hover {
    background-color: #edebe9 !important;
    border-color: #8a8886 !important;
    color: #201f1e !important;
}

/* ===== DANGER BUTTON (Registrar Pago) ===== */
.btn-danger {
    background-color: #a4262c !important;
    border-color: #a4262c !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(164, 38, 44, 0.25) !important;
}

.btn-danger:hover {
    background-color: #8e1f24 !important;
    border-color: #8e1f24 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 10px rgba(164, 38, 44, 0.35) !important;
    transform: translateY(-1px);
}

/* ============================================================
   COMPACT PAGE HEADER — replaces the oversized panel
   ------------------------------------------------------------
   Merges title + back action into a single, low-height bar
   that sits directly under the breadcrumb. Visually similar
   to a Fluent "Command Bar".
   ============================================================ */
.fl-page-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 16px !important;
    padding: 12px 0 !important;
    margin-bottom: 16px !important;
    border-bottom: 1px solid #edebe9 !important;
    flex-wrap: wrap !important;
}

.fl-page-header-left {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    min-width: 0 !important;
}

.fl-page-header-icon {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 40px !important;
    height: 40px !important;
    border-radius: 8px !important;
    background: linear-gradient(135deg, #0078d4 0%, #005a9e 100%) !important;
    color: #ffffff !important;
    font-size: 18px !important;
    flex-shrink: 0 !important;
}

.fl-page-header-text {
    display: flex !important;
    flex-direction: column !important;
    line-height: 1.2 !important;
    min-width: 0 !important;
}

.fl-page-header-title {
    font-size: 18px !important;
    font-weight: 700 !important;
    color: #201f1e !important;
    margin: 0 !important;
    letter-spacing: -0.01em !important;
}

.fl-page-header-subtitle {
    font-size: 13px !important;
    color: #605e5c !important;
    margin: 0 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

/* ===== BREADCRUMB ===== */
.breadcrumb {
    background-color: transparent !important;
    padding: 8px 0 !important;
    margin-bottom: 4px !important;
    font-size: 13px !important;
}

.breadcrumb-item a {
    color: #0078d4 !important;
    text-decoration: none !important;
}

.breadcrumb-item a:hover {
    text-decoration: underline !important;
}

.breadcrumb-item.active {
    color: #605e5c !important;
}

/* ===== MAIN CONTAINER ===== */
.main-container {
    padding: 20px !important;
    max-width: 1400px !important;
    margin: 0 auto !important;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .main-container {
        padding: 12px !important;
    }

    .fl-page-header {
        align-items: flex-start !important;
    }

    .fl-page-header-title {
        font-size: 16px !important;
    }
}
CSS
);

$this->registerJs(
    <<<JS
// ============================================================
// "Volver" — go back to the previous screen (browser history)
// Falls back to the corporate view if there's no history.
// ============================================================
$(document).on('click', '#btn-volver', function(e) {
    e.preventDefault();
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = $(this).data('fallback-url');
    }
});
JS
);
?>

<div class="main-container">
    <!-- Breadcrumbs -->
    <?= \yii\widgets\Breadcrumbs::widget([
        'links' => $this->params['breadcrumbs'] ?? [],
        'options' => ['class' => 'breadcrumb'],
    ]) ?>

    <!-- ============================================================
         COMPACT PAGE HEADER — Command-bar style
         ============================================================ -->
    <div class="fl-page-header">
        <div class="fl-page-header-left">
            <div class="fl-page-header-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="fl-page-header-text">
                <h1 class="fl-page-header-title">Resumen de Pago Corporativo</h1>
                <span class="fl-page-header-subtitle">
                    <?= Html::encode($corporativo->nombre) ?>
                    <?php if (!empty($corporativo->rif)): ?>
                        · RIF <?= Html::encode($corporativo->rif) ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <div>
            <a href="#"
                id="btn-volver"
                class="btn btn-secondary"
                data-fallback-url="<?= \yii\helpers\Url::to(['view', 'id' => $corporativo->id]) ?>">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>
    </div>

    <!-- Payment Form (Summary + Slide-in Panel) -->
    <?= $this->render('_pagos_form', [
        'model' => $model,
        'corporativo' => $corporativo,
        'allCuotas' => $allCuotas,
        'grandTotal' => $grandTotal,
    ]) ?>
</div>