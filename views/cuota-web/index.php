<?php
// views/cuota-web/index.php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
$this->title = 'Gestión de Cuotas - Panel de Control';
$this->params['breadcrumbs'][] = $this->title;

// Register Bootstrap 4 off-canvas + custom styles
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css', [
    'depends' => [\yii\bootstrap4\BootstrapAsset::class],
]);
$this->registerCss(<<<CSS
/* =========================================================
   Microsoft-style dashboard — comfortable readable type
   ========================================================= */
.cw-page { background:#f3f4f6; min-height:100vh; padding-bottom:2rem; font-size:20px; }
.cw-page .cw-header { background:#ffffff; border-bottom:1px solid #e5e7eb; padding:1.5rem 1.75rem; }
.cw-page .cw-header h1 { font-size:2rem; font-weight:600; color:#1f2937; margin:0; line-height:1.2; }
.cw-page .cw-header .subtitle { color:#4b5563; font-size:1.2rem; margin-top:.4rem; line-height:1.4; }
.cw-page .cw-section { margin-top:2rem; }
.cw-page .cw-section-title {
  font-size:1.05rem; text-transform:uppercase; letter-spacing:.08em;
  color:#4b5563; font-weight:700; margin:0 0 1.1rem 0; display:flex; align-items:center; gap:.7rem;
}
.cw-page .cw-section-title .badge-count {
  background:#eef2ff; color:#3730a3; font-size:.95rem; padding:.15rem .65rem; border-radius:999px;
}

/* Tiles */
.cw-tile {
  background:#fff; border:1px solid #e5e7eb; border-radius:10px;
  padding:1.5rem; height:100%; display:flex; flex-direction:column;
  transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease;
  position:relative; overflow:hidden;
}
.cw-tile:hover { box-shadow:0 6px 18px rgba(15,23,42,.09); border-color:#d1d5db; transform:translateY(-1px); }
.cw-tile .cw-tile-head { display:flex; align-items:center; gap:.9rem; margin-bottom:1.1rem; }
.cw-tile .cw-tile-icon {
  width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center;
  color:#fff; font-size:1.35rem; flex:0 0 52px;
}
.cw-tile .cw-tile-icon.bg-primary  { background:#2563eb; }
.cw-tile .cw-tile-icon.bg-success  { background:#059669; }
.cw-tile .cw-tile-icon.bg-warning  { background:#d97706; }
.cw-tile .cw-tile-icon.bg-danger   { background:#dc2626; }
.cw-tile .cw-tile-icon.bg-info     { background:#0891b2; }
.cw-tile .cw-tile-icon.bg-secondary{ background:#6b7280; }
.cw-tile .cw-tile-icon.bg-dark     { background:#374151; }
.cw-tile .cw-tile-title { font-size:1.3rem; font-weight:600; color:#111827; margin:0; line-height:1.25; }
.cw-tile .cw-tile-sub   { font-size:1.05rem; color:#4b5563; margin:.25rem 0 0; line-height:1.4; }

/* Action buttons inside tiles */
.cw-action {
  display:flex; align-items:center; gap:.85rem; width:100%;
  text-align:left; padding:.85rem 1.1rem; margin-bottom:.55rem;
  border:1px solid #e5e7eb; border-radius:8px; background:#fff;
  color:#1f2937; font-size:1.15rem; font-weight:500; line-height:1.35;
  transition: background .12s ease, border-color .12s ease, color .12s ease;
}
.cw-action i { width:24px; text-align:center; font-size:1.15rem; color:#2563eb; flex:0 0 24px; }
.cw-action:hover { background:#f9fafb; border-color:#cbd5e1; color:#111827; text-decoration:none; }
.cw-action.is-danger i   { color:#dc2626; }
.cw-action.is-warning i  { color:#d97706; }
.cw-action.is-success i  { color:#059669; }
.cw-action.is-info i     { color:#0891b2; }
.cw-action.is-secondary i{ color:#6b7280; }
.cw-action:disabled, .cw-action.disabled { opacity:.6; cursor:not-allowed; }

/* KPI strip */
.cw-kpi {
  background:#fff; border:1px solid #e5e7eb; border-radius:10px;
  padding:1.4rem; display:flex; align-items:center; gap:1.15rem; height:100%;
}
.cw-kpi .cw-kpi-icon {
  width:64px; height:64px; border-radius:14px; display:flex; align-items:center; justify-content:center;
  color:#fff; font-size:1.6rem; flex:0 0 64px;
}
.cw-kpi .cw-kpi-value { font-size:2.5rem; font-weight:700; color:#111827; line-height:1; }
.cw-kpi .cw-kpi-label { font-size:1.1rem; color:#4b5563; margin-top:.35rem; font-weight:500; line-height:1.3; }

/* Result panel */
.cw-result { background:#0f172a; color:#e2e8f0; border-radius:10px; padding:1.25rem 1.4rem;
  font-family: 'Cascadia Code', Consolas, 'Courier New', monospace; font-size:1.05rem;
  line-height:1.55; white-space:pre-wrap; max-height:520px; overflow-y:auto; }
.cw-result .ok  { color:#4ade80; font-weight:600; }
.cw-result .err { color:#f87171; font-weight:600; }

/* Off-canvas drawer (Microsoft-style) */
.cw-drawer {
  position:fixed; top:0; right:0; height:100vh; width:640px; max-width:94vw;
  background:#fff; z-index:1080; box-shadow:-10px 0 28px rgba(15,23,42,.2);
  transform: translateX(105%); transition: transform .28s ease-in-out;
  display:flex; flex-direction:column;
}
.cw-drawer.is-open { transform: translateX(0); }
.cw-drawer-head { padding:1.25rem 1.5rem; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
.cw-drawer-head h5 { margin:0; font-size:1.4rem; font-weight:600; color:#111827; }
.cw-drawer-body { padding:1.5rem; overflow-y:auto; flex:1; font-size:1.15rem; line-height:1.45; }
.cw-drawer-backdrop {
  position:fixed; inset:0; background:rgba(15,23,42,.45); z-index:1079;
  opacity:0; pointer-events:none; transition: opacity .25s ease;
}
.cw-drawer-backdrop.is-open { opacity:1; pointer-events:auto; }

/* Migration card */
.cw-migration select { font-size:1.05rem; height:auto; padding:.5rem .75rem; }
.cw-migration .cw-stat { font-size:1rem; color:#4b5563; }

/* Global loading overlay */
#global-loading {
  position:fixed; inset:0; background:rgba(15,23,42,.45);
  z-index:1090; display:none; align-items:center; justify-content:center;
}
#global-loading.is-on { display:flex; }

/* Modal tweaks (Bootstrap 4 modal) */
#adelantadasModal .modal-dialog { max-width:1080px; }
#adelantadasModal .modal-title { font-size:1.45rem; }
#adelantadasModal .modal-body { font-size:1.15rem; }
#adelantadasModal .modal-body label { font-size:1.15rem; font-weight:500; color:#374151; }
#adelantadasModal .modal-body .form-control { font-size:1.15rem; height:auto; padding:.6rem .85rem; }
#adelantadasModal .modal-body .form-text,
#adelantadasModal .modal-body small { font-size:1rem !important; }
#adelantadasModal .modal-body .custom-control-label { font-size:1.15rem; }
#adelantadasModal .modal-body h6 { font-size:1.25rem; font-weight:600; }
#adelantadasModal .modal-body .table { font-size:1.15rem; }
#adelantadasModal .modal-body .alert { font-size:1.15rem; }
#adelantadasModal .modal-footer { gap:.6rem; padding:1.1rem 1.4rem; }
#adelantadasModal .modal-footer .btn { font-size:1.15rem; padding:.6rem 1.4rem; }
#adelantadasModal .list-group-item { font-size:1.1rem; padding:1rem 1.2rem; line-height:1.4; }
#adelantadasModal .list-group-item small { font-size:1rem !important; }
#adelantadasModal .close { font-size:2rem; opacity:.9; }

/* Month grid inside modal */
.months-grid {
  display:grid !important;
  grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
  gap:14px !important;
}
@media (max-width: 992px) { .months-grid { grid-template-columns: repeat(2, minmax(0,1fr)) !important; } }
@media (max-width: 576px) { .months-grid { grid-template-columns: 1fr !important; } }

.month-item-card {
  display:flex !important; align-items:flex-start !important;
  padding:1rem 1.15rem !important; background:#fff; border:1px solid #e5e7eb;
  border-radius:10px; cursor:pointer; min-width:0;
}
.month-item-card:hover { border-color:#2563eb; background:#f0f7ff; }
.month-item-card.checked { border-color:#2563eb; background:#eff6ff; border-left:5px solid #2563eb; }
.month-item-card input[type="checkbox"] {
  width:24px; height:24px; margin:2px 14px 0 0; flex:0 0 24px; cursor:pointer;
}
.month-item-card label {
  margin:0; font-size:1.25rem; color:#1f2937; cursor:pointer;
  white-space:normal; word-break:break-word; line-height:1.35; flex:1; font-weight:500;
}
.month-limit-box {
  background:#f8fafc; border-left:5px solid #2563eb; padding:1.1rem 1.35rem;
  border-radius:8px; margin-bottom:1.35rem; font-size:1.1rem; line-height:1.5; color:#334155;
}

/* Section-level helpers used inline in the view */
.cw-page .alert { font-size:1.1rem; padding:1rem 1.25rem; }
.cw-page .btn { font-size:1.15rem; padding:.6rem 1.35rem; }
.cw-page .btn-sm { font-size:.95rem; padding:.4rem .85rem; }
.cw-page .form-control, .cw-page .custom-select { font-size:1.15rem; height:auto; padding:.6rem .85rem; }
.cw-page .text-muted.small,
.cw-page small { font-size:1rem !important; }
.cw-page .badge { font-size:.9rem; padding:.3rem .7rem; }
CSS);
?>

<div class="cuota-web-index cw-page">

    <!-- ======================= HEADER ======================= -->
    <div class="cw-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1><i class="fas fa-sliders-h text-primary mr-2"></i>Panel de Gestión de Cuotas</h1>
                <div class="subtitle">Ejecute manualmente las funciones de mantenimiento y verificación del sistema de cuotas.</div>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleDiagnosticDrawer()">
                    <i class="fas fa-stethoscope"></i> Diagnóstico
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-3">

        <!-- ======================= KPI STRIP ======================= -->
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="cw-kpi">
                    <div class="cw-kpi-icon bg-primary"><i class="fas fa-file-contract"></i></div>
                    <div>
                        <div class="cw-kpi-value" id="total-contratos">–</div>
                        <div class="cw-kpi-label">Contratos Activos</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="cw-kpi">
                    <div class="cw-kpi-icon bg-success"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div class="cw-kpi-value" id="cuotas-pendientes">–</div>
                        <div class="cw-kpi-label">Cuotas Pendientes</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="cw-kpi">
                    <div class="cw-kpi-icon bg-warning"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="cw-kpi-value" id="cuotas-vencidas">–</div>
                        <div class="cw-kpi-label">Cuotas Vencidas</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="cw-kpi">
                    <div class="cw-kpi-icon bg-danger"><i class="fas fa-user-slash"></i></div>
                    <div>
                        <div class="cw-kpi-value" id="contratos-suspendidos">–</div>
                        <div class="cw-kpi-label">Contratos Suspendidos</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= RESULT PANEL ======================= -->
        <div id="result-area" class="cw-section" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="cw-section-title mb-0">
                    <i class="fas fa-terminal"></i> Resultado de la ejecución
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearResults()">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
            <div id="result-content" class="cw-result"></div>
        </div>

        <!-- ======================= SECTION: OPERACIONES ======================= -->
        <div class="cw-section">
            <div class="cw-section-title"><i class="fas fa-play-circle"></i> Operaciones principales</div>
            <div class="row">

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="cw-tile">
                        <div class="cw-tile-head">
                            <div class="cw-tile-icon bg-success"><i class="fas fa-plus"></i></div>
                            <div>
                                <p class="cw-tile-title">Generación de cuotas</p>
                                <p class="cw-tile-sub">Cree cuotas nuevas para contratos activos</p>
                            </div>
                        </div>
                        <button type="button" class="cw-action is-success" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/generar']) ?>" data-action-name="generar">
                            <i class="fas fa-layer-group"></i> Generar todas las cuotas
                        </button>
                        <button type="button" class="cw-action is-info" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/generar-mensual']) ?>" data-action-name="generar-mensual">
                            <i class="fas fa-calendar-plus"></i> Generar cuotas mensuales
                        </button>
                        <button type="button" class="cw-action is-warning" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/generar-atrasadas']) ?>" data-action-name="generar-atrasadas">
                            <i class="fas fa-clock"></i> Generar cuotas atrasadas
                        </button>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="cw-tile">
                        <div class="cw-tile-head">
                            <div class="cw-tile-icon bg-warning"><i class="fas fa-forward"></i></div>
                            <div>
                                <p class="cw-tile-title">Cuotas adelantadas</p>
                                <p class="cw-tile-sub">Genere cuotas futuras para pagos por adelantado</p>
                            </div>
                        </div>
                        <button type="button" class="cw-action is-success" onclick="showAdelantadasModal()">
                            <i class="fas fa-calendar-plus"></i> Generar cuotas adelantadas
                        </button>
                        <small class="text-muted d-block mt-2" style="font-size:.78rem;">
                            <i class="fas fa-info-circle"></i> Máximo 1 año desde el inicio del contrato.
                        </small>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="cw-tile">
                        <div class="cw-tile-head">
                            <div class="cw-tile-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
                            <div>
                                <p class="cw-tile-title">Vencidas y duplicados</p>
                                <p class="cw-tile-sub">Suspenda contratos y limpie duplicados</p>
                            </div>
                        </div>
                        <button type="button" class="cw-action is-danger" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-vencidas']) ?>" data-action-name="verificar-vencidas">
                            <i class="fas fa-user-slash"></i> Verificar y suspender vencidas
                        </button>
                        <button type="button" class="cw-action is-warning" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-duplicados']) ?>" data-action-name="verificar-duplicados">
                            <i class="fas fa-search"></i> Verificar cuotas duplicadas
                        </button>
                        <button type="button" class="cw-action is-danger" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/eliminar-duplicados']) ?>" data-action-name="eliminar-duplicados">
                            <i class="fas fa-trash-alt"></i> Eliminar cuotas duplicadas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= SECTION: REPORTES ======================= -->
        <div class="cw-section">
            <div class="cw-section-title"><i class="fas fa-chart-bar"></i> Verificaciones y reportes</div>
            <div class="row">

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="cw-tile">
                        <div class="cw-tile-head">
                            <div class="cw-tile-icon bg-primary"><i class="fas fa-check-double"></i></div>
                            <div>
                                <p class="cw-tile-title">Verificaciones del sistema</p>
                                <p class="cw-tile-sub">Ejecute chequeos completos</p>
                            </div>
                        </div>
                        <button type="button" class="cw-action is-info" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-diario']) ?>" data-action-name="verificar-diario">
                            <i class="fas fa-calendar-day"></i> Verificación diaria completa
                        </button>
                        <button type="button" class="cw-action is-info" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-todo']) ?>" data-action-name="verificar-todo">
                            <i class="fas fa-check-double"></i> Verificar todo el sistema
                        </button>
                        <button type="button" class="cw-action is-secondary" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-espera']) ?>" data-action-name="verificar-espera">
                            <i class="fas fa-pause-circle"></i> Verificar contratos en espera
                        </button>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="cw-tile">
                        <div class="cw-tile-head">
                            <div class="cw-tile-icon bg-info"><i class="fas fa-list-alt"></i></div>
                            <div>
                                <p class="cw-tile-title">Resúmenes</p>
                                <p class="cw-tile-sub">Consulte el estado de cuotas y contratos</p>
                            </div>
                        </div>
                        <button type="button" class="cw-action is-success" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/resumen-proximos-vencer']) ?>" data-action-name="resumen-proximos-vencer">
                            <i class="fas fa-hourglass-half"></i> Próximos a vencer
                        </button>
                        <button type="button" class="cw-action is-warning" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/resumen-atrasadas']) ?>" data-action-name="resumen-atrasadas">
                            <i class="fas fa-list-alt"></i> Cuotas atrasadas
                        </button>
                        <button type="button" class="cw-action is-danger" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-contratos-vencidos']) ?>" data-action-name="verificar-contratos-vencidos">
                            <i class="fas fa-file-contract"></i> Contratos vencidos
                        </button>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="cw-tile">
                        <div class="cw-tile-head">
                            <div class="cw-tile-icon bg-secondary"><i class="fas fa-bolt"></i></div>
                            <div>
                                <p class="cw-tile-title">Acciones rápidas</p>
                                <p class="cw-tile-sub">Atajos de uso frecuente</p>
                            </div>
                        </div>
                        <?= Html::a(
                            '<i class="fas fa-play-circle"></i> Verificar cuotas y contratos',
                            ['daily-check-page'],
                            ['class' => 'cw-action is-success']
                        ) ?>
                        <button type="button" class="cw-action is-info" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/generar']) ?>" data-action-name="generar-refresh">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                        <button type="button" class="cw-action is-success" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/verificar-diario']) ?>" data-action-name="verificar-diario-quick">
                            <i class="fas fa-check"></i> Daily check
                        </button>
                        <div class="mt-2 text-muted" style="font-size:.78rem;">
                            Última ejecución: <span id="last-execution">Nunca</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================= SECTION: MANTENIMIENTO ======================= -->
        <div class="cw-section">
            <div class="cw-section-title"><i class="fas fa-tools"></i> Mantenimiento y reparación</div>
            <div class="row">

                <div class="col-lg-6 mb-3">
                    <div class="cw-tile">
                        <div class="cw-tile-head">
                            <div class="cw-tile-icon bg-warning"><i class="fas fa-wrench"></i></div>
                            <div>
                                <p class="cw-tile-title">Herramientas de mantenimiento</p>
                                <p class="cw-tile-sub">Repare inconsistencias en cuotas y pagos</p>
                            </div>
                        </div>
                        <button type="button" class="cw-action is-secondary" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/actualizar-montos']) ?>" data-action-name="actualizar-montos">
                            <i class="fas fa-dollar-sign"></i> Actualizar montos de cuotas
                        </button>
                        <button type="button" class="cw-action is-info" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/diagnostico-inconsistencias']) ?>" data-action-name="diagnostico-inconsistencias">
                            <i class="fas fa-stethoscope"></i> Diagnóstico de inconsistencias
                        </button>
                        <button type="button" class="cw-action is-success" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/generar-cuotas-faltantes']) ?>" data-action-name="generar-cuotas-faltantes">
                            <i class="fas fa-plus-circle"></i> Generar cuotas faltantes
                        </button>
                        <button type="button" class="cw-action is-info" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/reparar-relacion-pagos']) ?>" data-action-name="reparar-relacion-pagos">
                            <i class="fas fa-link"></i> Reparar relación pagos–cuotas
                        </button>
                        <button type="button" class="cw-action is-danger" onclick="executeAction(this)"
                            data-action-url="<?= Url::to(['cuota-web/eliminar-incorrectas']) ?>" data-action-name="eliminar-incorrectas">
                            <i class="fas fa-trash-alt"></i> Eliminar cuotas incorrectas (149,150,147)
                        </button>
                    </div>
                </div>

                <!-- Migration card -->
                <div class="col-lg-6 mb-3">
                    <div class="cw-tile cw-migration">
                        <div class="cw-tile-head">
                            <div class="cw-tile-icon bg-dark"><i class="fas fa-database"></i></div>
                            <div>
                                <p class="cw-tile-title">Migración de contratos antiguos</p>
                                <p class="cw-tile-sub">Migre al nuevo sistema de 12 cuotas</p>
                            </div>
                        </div>

                        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.82rem;">
                            <i class="fas fa-info-circle"></i>
                            Siempre haga backup antes de migrar. Use <strong>Check</strong> para ver qué contratos necesitan migración.
                        </div>

                        <div class="form-group mb-2">
                            <label class="mb-1" style="font-size:.82rem; color:#374151;">Seleccionar contrato</label>
                            <select class="form-control form-control-sm" id="migration-contrato-select">
                                <option value="">Cargando contratos...</option>
                            </select>
                            <small class="cw-stat d-block mt-1">
                                <i class="fas fa-sync-alt fa-spin" id="migration-loading" style="display:none;"></i>
                                <span id="migration-stats"></span>
                            </small>
                        </div>

                        <div class="row no-gutters">
                            <div class="col-6 pr-1">
                                <button type="button" class="cw-action is-info" onclick="runMigrationAction('check', true)">
                                    <i class="fas fa-search"></i> Check todos
                                </button>
                            </div>
                            <div class="col-6 pl-1">
                                <button type="button" class="cw-action is-info" onclick="runMigrationAction('check', false)">
                                    <i class="fas fa-search"></i> Check seleccionado
                                </button>
                            </div>
                            <div class="col-6 pr-1">
                                <button type="button" class="cw-action is-secondary" onclick="runMigrationAction('preview', false)">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                            </div>
                            <div class="col-6 pl-1">
                                <button type="button" class="cw-action is-success" onclick="runMigrationAction('backup', false)">
                                    <i class="fas fa-save"></i> Backup
                                </button>
                            </div>
                            <div class="col-6 pr-1">
                                <button type="button" class="cw-action is-secondary" onclick="runMigrationAction('show-backup', false)">
                                    <i class="fas fa-list"></i> Show backup
                                </button>
                            </div>
                            <div class="col-6 pl-1">
                                <button type="button" class="cw-action is-warning" onclick="runMigrationAction('migrate-contrato', false)">
                                    <i class="fas fa-play"></i> Migrar seleccionado
                                </button>
                            </div>
                            <div class="col-12 mt-1">
                                <button type="button" class="cw-action is-warning" onclick="runMigrationAction('migrate-all', true)">
                                    <i class="fas fa-forward"></i> Migrar TODOS los contratos
                                </button>
                            </div>
                            <div class="col-12 mt-1">
                                <button type="button" class="cw-action is-danger" onclick="runMigrationAction('rollback', false)">
                                    <i class="fas fa-undo"></i> Rollback
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /container-fluid -->
</div><!-- /cw-page -->


<!-- ======================= GLOBAL LOADING ======================= -->
<div id="global-loading">
    <div class="spinner-border text-light" role="status" style="width:3rem;height:3rem;">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>


<!-- ======================= OFF-CANVAS DIAGNOSTIC DRAWER ======================= -->
<div class="cw-drawer-backdrop" id="diagnostic-backdrop" onclick="toggleDiagnosticDrawer(false)"></div>
<aside class="cw-drawer" id="diagnostic-drawer" aria-hidden="true">
    <div class="cw-drawer-head">
        <h5><i class="fas fa-stethoscope text-warning mr-1"></i> Diagnóstico del sistema</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleDiagnosticDrawer(false)">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="cw-drawer-body">
        <p class="text-muted" style="font-size:.85rem;">
            Herramientas para validar que la ejecución de comandos y el entorno funcionan correctamente.
        </p>
        <button type="button" class="btn btn-outline-info btn-sm btn-block text-left mb-2" onclick="testCommandExecution()">
            <i class="fas fa-vial"></i> Probar ejecución de comandos
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm btn-block text-left mb-2" onclick="showSystemInfo()">
            <i class="fas fa-info-circle"></i> Mostrar info del sistema
        </button>
        <div id="diagnostic-result" class="cw-result mt-2" style="max-height:340px;"></div>
    </div>
</aside>


<!-- ======================= MODAL: CUOTAS ADELANTADAS ======================= -->
<div class="modal fade" id="adelantadasModal" tabindex="-1" role="dialog" aria-labelledby="adelantadasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="adelantadasModalLabel">
                    <i class="fas fa-forward"></i> Generar cuotas adelantadas
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <!-- STEP 1 -->
                <div id="adelantadas-step-1">
                    <div class="form-group">
                        <label>Buscar afiliado</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="user-search"
                                placeholder="ID, nombre, apellido o cédula">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="button" onclick="searchUser()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Ingrese al menos 3 caracteres para buscar.</small>
                    </div>

                    <div id="user-results" class="mb-3" style="display:none;">
                        <h6>Resultados:</h6>
                        <div id="users-list" class="list-group" style="max-height:200px; overflow-y:auto;"></div>
                    </div>

                    <div id="selected-user" class="mb-3" style="display:none;">
                        <div class="card">
                            <div class="card-body py-2">
                                <h6 class="mb-1">Afiliado seleccionado:</h6>
                                <p id="selected-user-info" class="mb-2"></p>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectUser()">
                                    <i class="fas fa-times"></i> Cambiar afiliado
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="contracts-section" class="mb-3" style="display:none;">
                        <label>Seleccionar contrato</label>
                        <select class="form-control" id="contrato-select" onchange="loadContractInfo()">
                            <option value="">Seleccione un contrato...</option>
                        </select>
                    </div>

                    <div id="contract-info" class="mb-3" style="display:none;">
                        <div class="card">
                            <div class="card-body py-2">
                                <h6>Información del contrato:</h6>
                                <div id="contract-details"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div id="adelantadas-step-2" style="display:none;">
                    <div class="form-group">
                        <label>Modo de generación</label>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" name="generation-mode" id="mode-cantidad"
                                value="cantidad" checked onchange="toggleGenerationMode()">
                            <label class="custom-control-label" for="mode-cantidad">Por cantidad de cuotas</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" name="generation-mode" id="mode-meses"
                                value="meses" onchange="toggleGenerationMode()">
                            <label class="custom-control-label" for="mode-meses">Por meses específicos</label>
                        </div>
                    </div>

                    <div id="mode-cantidad-fields">
                        <div class="form-group">
                            <label>Número de cuotas</label>
                            <input type="number" class="form-control" id="num-cuotas" min="1" max="12" value="3">
                            <small class="form-text text-muted">Máximo 12 cuotas (1 año).</small>
                        </div>
                    </div>

                    <div id="mode-meses-fields" style="display:none;">
                        <div class="form-group">
                            <label>Seleccionar meses</label>
                            <div id="months-container" style="max-height:300px; overflow-y:auto; border:1px solid #dee2e6; border-radius:6px; padding:10px; background:#f8f9fa;">
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-calendar-alt"></i> Seleccione un contrato para ver los meses disponibles.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Fecha de inicio (opcional)</label>
                        <input type="date" class="form-control" id="fecha-inicio">
                        <small class="form-text text-muted">Si se deja vacío, se usa la última cuota o la fecha del contrato.</small>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div id="adelantadas-step-3" style="display:none;">
                    <div id="preview-results" class="mb-3">
                        <h6>Previsualización de cuotas:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Mes</th>
                                        <th>Vencimiento</th>
                                        <th>Monto (USD)</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="preview-table-body"></tbody>
                            </table>
                        </div>
                        <div class="alert alert-info py-2">
                            <strong>Total:</strong> <span id="preview-total">0.00</span> USD
                        </div>
                    </div>

                    <div class="alert alert-warning py-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Advertencia:</strong> Esta acción generará cuotas futuras. Asegúrese de que el afiliado haya pagado por adelantado.
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-primary" id="prev-step-btn" onclick="prevStep()" style="display:none;">
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>
                <button type="button" class="btn btn-primary" id="next-step-btn" onclick="nextStep()">
                    Siguiente <i class="fas fa-arrow-right"></i>
                </button>
                <button type="button" class="btn btn-success" id="generate-btn" onclick="generateAdelantadas()" style="display:none;">
                    <i class="fas fa-check"></i> Generar cuotas
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    /* =====================================================================
   Cuota Web Panel — JavaScript
   (consolidated; the previous duplicated / "emergency" versions removed)
   ===================================================================== */
    const csrfToken = '<?= Yii::$app->request->csrfToken ?>';
    let isLoading = false;

    /* -------- helpers -------- */
    function showGlobalLoading() {
        document.getElementById('global-loading').classList.add('is-on');
    }

    function hideGlobalLoading() {
        document.getElementById('global-loading').classList.remove('is-on');
    }

    function clearResults() {
        document.getElementById('result-area').style.display = 'none';
        document.getElementById('result-content').innerHTML = '';
    }

    /* -------- generic action executor -------- */
    function executeAction(button) {
        const action = button.getAttribute('data-action-name');
        const url = button.getAttribute('data-action-url');

        if (isLoading) return;
        if (!url) {
            document.getElementById('result-content').innerHTML =
                '<div class="err"><i class="fas fa-times-circle"></i> Error de configuración: URL no válida.</div>';
            document.getElementById('result-area').style.display = 'block';
            return;
        }

        isLoading = true;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ejecutando...';
        button.disabled = true;
        showGlobalLoading();

        document.getElementById('result-area').style.display = 'block';
        document.getElementById('result-content').innerHTML =
            '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Ejecutando comando...</div>';

        const formData = new FormData();
        formData.append('_csrf', csrfToken);

        fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status} ${r.statusText} — ${url}`);
                return r.json();
            })
            .then(res => {
                document.getElementById('last-execution').textContent = new Date().toLocaleString();
                let html = '';
                if (res.success) {
                    html += '<div class="ok mb-2"><i class="fas fa-check-circle"></i> ' + res.message + '</div>';
                    if (res.returnCode !== undefined) html += '<div class="text-muted small mb-2">Código: ' + res.returnCode + '</div>';
                    html += '<div>' + (res.output || 'Sin salida') + '</div>';
                } else {
                    html += '<div class="err mb-2"><i class="fas fa-exclamation-circle"></i> ' + res.message + '</div>';
                    if (res.returnCode !== undefined) html += '<div class="text-muted small mb-2">Código: ' + res.returnCode + '</div>';
                    html += '<div class="err">' + (res.output || 'Sin detalles') + '</div>';
                }
                document.getElementById('result-content').innerHTML = html;
                document.getElementById('result-area').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            })
            .catch(err => {
                document.getElementById('result-content').innerHTML =
                    '<div class="err"><i class="fas fa-times-circle"></i> Error de red/ruta: ' + err.message + '</div>';
            })
            .finally(() => {
                isLoading = false;
                hideGlobalLoading();
                button.innerHTML = originalText;
                button.disabled = false;
            });
    }

    /* =====================================================================
       MIGRATION
       ===================================================================== */
    function loadMigrationContracts() {
        const select = document.getElementById('migration-contrato-select');
        const loading = document.getElementById('migration-loading');
        const stats = document.getElementById('migration-stats');

        loading.style.display = 'inline-block';
        select.innerHTML = '<option value="">Cargando contratos...</option>';

        fetch('<?= Url::to(['cuota-web/migration-list-contracts']) ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.success) {
                    let needs = 0,
                        migrated = 0;
                    let opts = '<option value="">Seleccione un contrato...</option>';
                    data.contratos.forEach(c => {
                        const isMigrated = c.cuotas_migradas > 0 && c.cuotas_migradas == c.total_cuotas;
                        if (isMigrated) migrated++;
                        else needs++;
                        const icon = isMigrated ? '✅' : '⚠️';
                        const name = c.nombres ? c.nombres + ' ' + c.apellidos : 'N/A';
                        opts += `<option value="${c.id}">${icon} #${c.id} — ${name} (${c.fecha_ini}) — Pagos: ${c.cuotas_pagadas}</option>`;
                    });
                    select.innerHTML = opts;
                    stats.innerHTML = `✅ Migrados: ${migrated} · ⚠️ Pendientes: ${needs}`;
                } else {
                    select.innerHTML = '<option value="">Error cargando contratos</option>';
                    stats.textContent = '❌ ' + data.error;
                }
            })
            .catch(err => {
                loading.style.display = 'none';
                select.innerHTML = '<option value="">Error de conexión</option>';
                stats.textContent = '❌ ' + err.message;
            });
    }

    function runMigrationAction(action, isAll = false) {
        const select = document.getElementById('migration-contrato-select');
        const contratoId = isAll ? null : select.value;
        let message = '';
        let confirmRequired = true;

        if (!isAll && !contratoId) {
            alert('Por favor seleccione un contrato');
            return;
        }

        switch (action) {
            case 'migrate-all':
                message = '⚠️ ¿Está seguro de migrar TODOS los contratos?\n\nSe crearán backups automáticos.';
                break;
            case 'migrate-contrato':
                message = '⚠️ ¿Migrar el contrato seleccionado?\n\nSe preservarán los pagos existentes.';
                break;
            case 'rollback':
                message = '⚠️ ¿Rollback del contrato seleccionado?\n\nSe restaurará al estado original.';
                break;
            case 'backup':
                message = '¿Crear backup del contrato seleccionado?';
                confirmRequired = false;
                break;
            default:
                confirmRequired = false;
        }

        if (confirmRequired && !confirm(message)) return;

        showGlobalLoading();
        let url = '<?= Url::to(['cuota-web/migration-']) ?>' + action;
        if (!isAll && contratoId) url += '?contratoId=' + contratoId;

        fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                hideGlobalLoading();
                document.getElementById('result-area').style.display = 'block';
                let html = data.success ?
                    '<div class="ok mb-2"><i class="fas fa-check-circle"></i> ' + (data.message || 'OK') + '</div>' :
                    '<div class="err mb-2"><i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Error') + '</div>';
                html += '<pre style="margin:0; white-space:pre-wrap;">' + (data.output || 'Sin salida') + '</pre>';
                document.getElementById('result-content').innerHTML = html;
                document.getElementById('result-area').scrollIntoView({
                    behavior: 'smooth'
                });

                if (['migrate-contrato', 'migrate-all', 'rollback', 'backup'].includes(action)) {
                    setTimeout(loadMigrationContracts, 2000);
                }
            })
            .catch(err => {
                hideGlobalLoading();
                document.getElementById('result-area').style.display = 'block';
                document.getElementById('result-content').innerHTML =
                    '<div class="err"><i class="fas fa-times-circle"></i> ' + err.message + '</div>';
            });
    }

    /* =====================================================================
       OFF-CANVAS DIAGNOSTIC DRAWER
       ===================================================================== */
    function toggleDiagnosticDrawer(force) {
        const drawer = document.getElementById('diagnostic-drawer');
        const backdrop = document.getElementById('diagnostic-backdrop');
        const open = (typeof force === 'boolean') ? force : !drawer.classList.contains('is-open');
        drawer.classList.toggle('is-open', open);
        backdrop.classList.toggle('is-open', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    function testCommandExecution() {
        const el = document.getElementById('diagnostic-result');
        el.innerHTML = '<div><i class="fas fa-spinner fa-spin"></i> Probando ejecución de comandos...</div>';
        fetch('<?= Url::to(['cuota-web/test-command']) ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    el.innerHTML = '<div class="ok"><i class="fas fa-check-circle"></i> Comandos funcionando</div>' +
                        '<div class="mt-2"><strong>Salida:</strong><br>' + (data.output || '') + '</div>';
                } else {
                    el.innerHTML = '<div class="err"><i class="fas fa-exclamation-circle"></i> Error en ejecución</div>' +
                        '<div class="mt-2"><strong>Error:</strong><br>' + (data.output || '') + '</div>' +
                        '<div class="mt-1"><strong>Código:</strong> ' + data.returnCode + '</div>';
                }
            })
            .catch(err => {
                el.innerHTML = '<div class="err">Error: ' + err.message + '</div>';
            });
    }

    function showSystemInfo() {
        const el = document.getElementById('diagnostic-result');
        el.innerHTML =
            '<div><strong>User Agent:</strong> ' + navigator.userAgent + '</div>' +
            '<div><strong>Online:</strong> ' + navigator.onLine + '</div>' +
            '<div><strong>Timestamp:</strong> ' + new Date().toISOString() + '</div>';
    }

    /* =====================================================================
       ADELANTADAS MODAL
       ===================================================================== */
    let currentStep = 1;
    let selectedUserId = null;
    let selectedContractId = null;
    let previewData = null;

    function showAdelantadasModal() {
        resetModal();
        $('#adelantadasModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
    }

    function resetModal() {
        currentStep = 1;
        selectedUserId = null;
        selectedContractId = null;
        previewData = null;

        $('#adelantadas-step-1').show();
        $('#adelantadas-step-2').hide();
        $('#adelantadas-step-3').hide();
        $('#prev-step-btn').hide();
        $('#next-step-btn').show();
        $('#generate-btn').hide();

        $('#user-search').val('');
        $('#users-list').empty();
        $('#user-results').hide();
        $('#selected-user').hide();
        $('#contracts-section').hide();
        $('#contract-info').hide();
        $('#contrato-select').html('<option value="">Seleccione un contrato...</option>');
        $('#preview-table-body').empty();
        $('#preview-total').text('0.00');

        document.getElementById('mode-cantidad').checked = true;
        document.getElementById('mode-meses').checked = false;
        document.getElementById('mode-cantidad-fields').style.display = 'block';
        document.getElementById('mode-meses-fields').style.display = 'none';

        const fechaGroup = document.querySelector('#fecha-inicio').closest('.form-group');
        if (fechaGroup) fechaGroup.style.display = 'block';

        // Enforce 12-cuota max
        const numCuotas = document.getElementById('num-cuotas');
        numCuotas.max = 12;
        numCuotas.value = 3;
    }

    function searchUser() {
        const term = document.getElementById('user-search').value.trim();
        if (term.length < 1) {
            alert('Ingrese un término para buscar');
            return;
        }

        showGlobalLoading();
        const fd = new FormData();
        fd.append('_csrf', csrfToken);
        fd.append('search', term);

        fetch('<?= Url::to(['cuota-web/search-user']) ?>', {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                hideGlobalLoading();
                if (!data.success) {
                    alert('Error: ' + data.error);
                    return;
                }

                const list = document.getElementById('users-list');
                list.innerHTML = '';
                if (data.users.length === 0) {
                    list.innerHTML = '<div class="list-group-item text-muted">No se encontraron afiliados</div>';
                } else {
                    data.users.forEach(u => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action';
                        btn.innerHTML = `<strong>${u.nombres||''} ${u.apellidos||''}</strong><br>
                    <small class="text-muted">ID: ${u.id} · Cédula: ${u.cedula||'N/A'} · Email: ${u.email||'N/A'}</small><br>
                    <small>Contratos activos: ${u.active_contracts||0}</small>`;
                        btn.onclick = () => selectUser(u);
                        list.appendChild(btn);
                    });
                }
                document.getElementById('user-results').style.display = 'block';
            })
            .catch(err => {
                hideGlobalLoading();
                alert('Error en la búsqueda: ' + err.message);
            });
    }

    function selectUser(user) {
        selectedUserId = user.id;
        document.getElementById('selected-user-info').innerHTML =
            `<strong>${user.nombres} ${user.apellidos}</strong><br>
         <small>Cédula: ${user.cedula||'N/A'} · ID: ${user.id}</small>`;
        document.getElementById('user-results').style.display = 'none';
        document.getElementById('selected-user').style.display = 'block';
        loadUserContracts();
    }

    function deselectUser() {
        selectedUserId = null;
        selectedContractId = null;
        document.getElementById('selected-user').style.display = 'none';
        document.getElementById('contracts-section').style.display = 'none';
        document.getElementById('contract-info').style.display = 'none';
        document.getElementById('user-results').style.display = 'block';
    }

    function loadUserContracts() {
        if (!selectedUserId) return;
        showGlobalLoading();
        const fd = new FormData();
        fd.append('_csrf', csrfToken);
        fd.append('user_id', selectedUserId);

        fetch('<?= Url::to(['cuota-web/get-user-contracts']) ?>', {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                hideGlobalLoading();
                if (!data.success) {
                    alert('Error: ' + data.error);
                    return;
                }

                const select = document.getElementById('contrato-select');
                select.innerHTML = '<option value="">Seleccione un contrato...</option>';
                if (data.contratos.length === 0) {
                    select.innerHTML += '<option value="" disabled>No hay contratos activos</option>';
                } else {
                    data.contratos.forEach(c => {
                        const info = c.last_cuota ? `(Última: ${c.last_cuota.fecha} · ${c.last_cuota.estatus})` : '(Sin cuotas)';
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = `Contrato ${c.nrocontrato} — ${c.estatus} ${info}`;
                        opt.setAttribute('data-monto', c.monto);
                        opt.setAttribute('data-fecha-ini', c.fecha_ini);
                        select.appendChild(opt);
                    });
                }
                document.getElementById('contracts-section').style.display = 'block';
            })
            .catch(err => {
                hideGlobalLoading();
                alert('Error cargando contratos: ' + err.message);
            });
    }

    function loadContractInfo() {
        const select = document.getElementById('contrato-select');
        selectedContractId = select.value;
        if (!selectedContractId) {
            document.getElementById('contract-info').style.display = 'none';
            return;
        }
        const opt = select.options[select.selectedIndex];
        const monto = opt.getAttribute('data-monto') || '0.00';
        const fechaIni = opt.getAttribute('data-fecha-ini') || '';
        const fechaFmt = fechaIni ? new Date(fechaIni).toLocaleDateString('es-ES') : 'No disponible';

        document.getElementById('contract-details').innerHTML =
            `<strong>Contrato:</strong> ${opt.textContent}<br>
         <strong>Monto cuota:</strong> ${parseFloat(monto).toFixed(2)} USD<br>
         <strong>Fecha inicio:</strong> ${fechaFmt}`;
        document.getElementById('contract-info').style.display = 'block';

        if (!document.getElementById('mode-cantidad').checked) populateMonths();
    }

    function nextStep() {
        if (currentStep === 1) {
            if (!selectedUserId || !selectedContractId) {
                alert('Seleccione un afiliado y un contrato');
                return;
            }
            $('#adelantadas-step-1').hide();
            $('#adelantadas-step-2').show();
            $('#prev-step-btn').show();
            currentStep = 2;
        } else if (currentStep === 2) {
            const modeCantidad = document.getElementById('mode-cantidad').checked;
            let valid;
            if (modeCantidad) {
                const n = parseInt(document.getElementById('num-cuotas').value, 10);
                valid = n > 0 && n <= 12;
            } else {
                valid = document.querySelectorAll('.month-checkbox:checked').length > 0;
            }
            if (!valid) {
                alert('Complete correctamente la configuración');
                return;
            }
            $('#adelantadas-step-2').hide();
            $('#adelantadas-step-3').show();
            $('#next-step-btn').hide();
            $('#generate-btn').show();
            currentStep = 3;
            previewCuotas();
        }
    }

    function prevStep() {
        if (currentStep === 2) {
            $('#adelantadas-step-2').hide();
            $('#adelantadas-step-1').show();
            $('#prev-step-btn').hide();
            currentStep = 1;
        } else if (currentStep === 3) {
            $('#adelantadas-step-3').hide();
            $('#adelantadas-step-2').show();
            $('#next-step-btn').show();
            $('#generate-btn').hide();
            currentStep = 2;
        }
    }

    function toggleGenerationMode() {
        const modeCantidad = document.getElementById('mode-cantidad').checked;
        const fechaGroup = document.querySelector('#fecha-inicio').closest('.form-group');

        if (modeCantidad) {
            document.getElementById('mode-cantidad-fields').style.display = 'block';
            document.getElementById('mode-meses-fields').style.display = 'none';
            if (fechaGroup) fechaGroup.style.display = 'block';
        } else {
            document.getElementById('mode-cantidad-fields').style.display = 'none';
            document.getElementById('mode-meses-fields').style.display = 'block';
            if (fechaGroup) fechaGroup.style.display = 'none';
            setTimeout(populateMonths, 50);
        }
    }

    function populateMonths() {
        const container = document.getElementById('months-container');
        if (!container) return;
        container.innerHTML = '';

        if (!selectedContractId) {
            container.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-exclamation-circle"></i> Seleccione un contrato</div>';
            return;
        }

        const select = document.getElementById('contrato-select');
        const opt = select.options[select.selectedIndex];
        if (!opt || opt.value === '') {
            container.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-exclamation-circle"></i> Contrato no seleccionado</div>';
            return;
        }

        const fechaIni = opt.getAttribute('data-fecha-ini');
        if (!fechaIni) {
            container.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-exclamation-circle"></i> Fecha inicio no disponible</div>';
            return;
        }

        const inicio = new Date(fechaIni);
        const limite = new Date(inicio);
        limite.setFullYear(limite.getFullYear() + 1);
        const hoy = new Date();

        const months = [];
        let cur = new Date(inicio);
        cur.setMonth(cur.getMonth() + 1);
        for (let i = 0; i < 12; i++) {
            if (cur > limite) break;
            if (cur >= hoy) {
                const name = cur.toLocaleString('es-ES', {
                    month: 'long',
                    year: 'numeric'
                });
                const value = `${cur.getFullYear()}-${(cur.getMonth()+1).toString().padStart(2,'0')}`;
                months.push({
                    name: name.charAt(0).toUpperCase() + name.slice(1),
                    value,
                    date: new Date(cur)
                });
            }
            cur.setMonth(cur.getMonth() + 1);
        }
        months.sort((a, b) => a.date - b.date);

        if (months.length === 0) {
            container.innerHTML = '<div class="text-center text-warning py-3"><i class="fas fa-calendar-times"></i> No hay meses disponibles en el período permitido</div>';
            return;
        }

        const fmt = d => d.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });

        let html = `
        <div class="month-limit-box">
            <div><strong><i class="fas fa-calendar-alt"></i> Límite:</strong> Máximo 1 año desde inicio del contrato</div>
            <div><strong>Inicio:</strong> ${fmt(inicio)} &nbsp;·&nbsp; <strong>Límite:</strong> ${fmt(limite)}</div>
        </div>
        <div class="custom-control custom-checkbox mb-2">
            <input type="checkbox" class="custom-control-input" id="select-all-months">
            <label class="custom-control-label" for="select-all-months" style="cursor:pointer;">
                <i class="fas fa-check-double text-primary"></i> Seleccionar todos (${months.length})
            </label>
        </div>
        <div class="months-grid">`;

        months.forEach((m, i) => {
            const id = `month-${i}`;
            html += `
            <div class="month-item-card" data-for="${id}">
                <input type="checkbox" class="month-checkbox" id="${id}" value="${m.value}">
                <label for="${id}">${m.name}</label>
            </div>`;
        });
        html += `</div>`;
        container.innerHTML = html;

        // Wire up click-on-card and select-all
        container.querySelectorAll('.month-item-card').forEach(card => {
            card.addEventListener('click', e => {
                if (e.target.tagName === 'INPUT') return;
                const cb = card.querySelector('input');
                cb.checked = !cb.checked;
                card.classList.toggle('checked', cb.checked);
            });
            const cb = card.querySelector('input');
            cb.addEventListener('change', () => card.classList.toggle('checked', cb.checked));
        });
        const selectAll = document.getElementById('select-all-months');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                container.querySelectorAll('.month-checkbox').forEach(cb => {
                    cb.checked = this.checked;
                    cb.closest('.month-item-card').classList.toggle('checked', this.checked);
                });
            });
        }
    }

    function previewCuotas() {
        if (!selectedContractId) {
            alert('No hay contrato seleccionado');
            return;
        }

        const modeCantidad = document.getElementById('mode-cantidad').checked;
        let numCuotas, meses = '';
        if (modeCantidad) {
            numCuotas = parseInt(document.getElementById('num-cuotas').value, 10);
            if (!numCuotas || numCuotas <= 0 || numCuotas > 12) {
                alert('Ingrese un número válido de cuotas (1-12)');
                return;
            }
        } else {
            const sel = Array.from(document.querySelectorAll('.month-checkbox:checked')).map(cb => cb.value);
            numCuotas = sel.length;
            meses = sel.join(',');
            if (!numCuotas) {
                alert('Seleccione al menos un mes');
                return;
            }
        }

        showGlobalLoading();
        const fd = new FormData();
        fd.append('_csrf', csrfToken);
        fd.append('contrato_id', selectedContractId);
        fd.append('num_cuotas', numCuotas);
        if (meses) fd.append('meses', meses);
        const fi = document.getElementById('fecha-inicio').value;
        if (fi) fd.append('fecha_inicio', fi);

        fetch('<?= Url::to(['cuota-web/preview-adelantadas']) ?>', {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                hideGlobalLoading();
                if (data.success) {
                    previewData = data;
                    displayPreview(data);
                } else alert('Error en previsualización: ' + (data.error || 'desconocido'));
            })
            .catch(err => {
                hideGlobalLoading();
                alert('Error en previsualización: ' + err.message);
            });
    }

    function displayPreview(data) {
        const tbody = document.getElementById('preview-table-body');
        tbody.innerHTML = '';
        let total = 0;

        data.preview.forEach(c => {
            const tr = document.createElement('tr');
            if (c.existe) tr.className = 'table-warning';
            tr.innerHTML = `
            <td>${c.numero}</td>
            <td>${c.mes}</td>
            <td>${c.fecha_vencimiento}</td>
            <td>${c.monto.toFixed(2)}</td>
            <td>${c.existe
                ? '<span class="badge badge-warning">Ya existe</span>'
                : '<span class="badge badge-success">Nueva</span>'}</td>`;
            tbody.appendChild(tr);
            total += c.monto;
        });
        document.getElementById('preview-total').textContent = total.toFixed(2);
    }

    function generateAdelantadas() {
        if (!previewData || !selectedContractId) {
            alert('No hay datos para generar');
            return;
        }
        if (!confirm('¿Está seguro de generar estas cuotas adelantadas?')) return;

        showGlobalLoading();
        const modeCantidad = document.getElementById('mode-cantidad').checked;
        const fd = new FormData();
        fd.append('_csrf', csrfToken);
        fd.append('user_id', selectedUserId);
        fd.append('contrato_id', selectedContractId);
        fd.append('num_cuotas', document.getElementById('num-cuotas').value);
        fd.append('fecha_inicio', document.getElementById('fecha-inicio').value);
        if (!modeCantidad) {
            fd.append('modo', 'meses');
            fd.append('meses', Array.from(document.querySelectorAll('.month-checkbox:checked')).map(cb => cb.value).join(','));
        }

        fetch('<?= Url::to(['cuota-web/generar-adelantadas']) ?>', {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                hideGlobalLoading();
                if (data.success) {
                    document.getElementById('result-content').innerHTML =
                        '<div class="ok mb-2"><i class="fas fa-check-circle"></i> ' + data.message + '</div>' +
                        '<div>' + data.output + '</div>';
                    document.getElementById('result-area').style.display = 'block';
                    $('#adelantadasModal').modal('hide');
                    resetModal();
                } else {
                    alert('Error: ' + data.output);
                }
            })
            .catch(err => {
                hideGlobalLoading();
                alert('Error generando cuotas: ' + err.message);
            });
    }

    /* =====================================================================
       INIT
       ===================================================================== */
    document.addEventListener('DOMContentLoaded', function() {
        // Preload migration contract list
        loadMigrationContracts();

        // ESC closes drawer / clears results
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                toggleDiagnosticDrawer(false);
            }
        });
    });
</script>