<?php

/**
 * ✅ CHECKPOINT #3 (Design 2) — Off-canvas cancellation drawer
 * with explicit responsibility selector (clinic / patient).
 *
 * @var yii\web\View $this
 * @var app\models\SisSiniestro $model
 * @var bool $isClinicActor
 * @var string|null $actorRole
 * @var bool $canCancelWithoutPenalty
 * @var float|null $hoursUntil
 */

use yii\helpers\Html;
use yii\helpers\Url;

$isPast = ($hoursUntil !== null && $hoursUntil < 0);

// Default responsibility = clinic (safe default: no penalty)
$defaultResponsible = 'clinic';

// Precompute the "patient" state for the JS to switch to
$patientEarlyMsg = 'Aún puede cancelar esta cita sin que se descuente de su cobertura.';
$patientLateMsg  = $isPast
    ? 'Esta cita ya ocurrió. Al cancelar se aplicará penalidad.'
    : 'Ya no es posible cancelar sin penalidad (faltan menos de 48 horas).';
$patientState    = ($canCancelWithoutPenalty && !$isPast) ? 'early' : 'late';

$totalCost   = (float) ($model->costo_total ?? 0);
$patientName = $model->afiliado
    ? $model->afiliado->nombres . ' ' . $model->afiliado->apellidos
    : 'Paciente';
?>

<!-- ✅ Overlay backdrop -->
<div id="msCancelOverlay" class="ms-drawer-overlay" aria-hidden="true"></div>

<!-- ✅ Off-canvas drawer -->
<aside id="msCancelDrawer"
    class="ms-drawer"
    role="dialog"
    aria-modal="true"
    aria-labelledby="msDrawerTitle"
    aria-hidden="true"
    data-can-early="<?= $canCancelWithoutPenalty ? '1' : '0' ?>"
    data-is-past="<?= $isPast ? '1' : '0' ?>">

    <!-- Header -->
    <header class="ms-drawer-header">
        <div class="ms-drawer-header-left">
            <div class="ms-drawer-icon" id="msDrawerIcon" style="background:#0078d4;">
                <i class="fas fa-hospital"></i>
            </div>
            <div>
                <h3 id="msDrawerTitle" class="ms-drawer-title">Cancelar Cita Médica</h3>
                <small class="ms-drawer-subtitle">Esta acción es irreversible</small>
            </div>
        </div>
        <button type="button"
            class="ms-drawer-close"
            data-drawer-close
            aria-label="Cerrar">
            <i class="fas fa-times"></i>
        </button>
    </header>

    <!-- Body -->
    <div class="ms-drawer-body">

        <!-- State alert (updated live by JS) -->
        <div class="ms-state-card" id="msStateCard" style="border-left-color:#0078d4;">
            <div class="ms-state-card-head">
                <i class="fas fa-hospital" id="msStateIcon" style="color:#0078d4;"></i>
                <strong id="msStateTitle" style="color:#0078d4;">Cancelación por Clínica</strong>
                <span id="msStateBadge" class="badge" style="background:#0078d4;color:#fff;">SIN PENALIDAD</span>
            </div>
            <p class="ms-state-card-msg" id="msStateMsg">
                Su cita fue cancelada por la clínica. No se aplicará penalidad.
            </p>
            <small class="text-muted" id="msStateHours" style="display:none;">
                <i class="fas fa-hourglass-half mr-1"></i>
                <span id="msStateHoursValue"></span> horas restantes para la cita
            </small>
        </div>

        <!-- ✅ Responsibility selector -->
        <section class="ms-section">
            <h4 class="ms-section-title">
                <i class="fas fa-user-shield mr-1"></i> ¿Quién es responsable de la cancelación?
                <span class="text-danger">*</span>
            </h4>

            <div class="ms-responsible-group" role="radiogroup" aria-label="Responsable de la cancelación">
                <label class="ms-responsible-option is-selected" data-value="clinic">
                    <input type="radio" name="responsible" value="clinic" checked>
                    <span class="ms-responsible-radio"></span>
                    <span class="ms-responsible-content">
                        <span class="ms-responsible-title">
                            <i class="fas fa-hospital mr-2" style="color:#0078d4;"></i>
                            La clínica / el doctor
                        </span>
                        <span class="ms-responsible-desc">
                            No se aplicará penalidad. La cobertura será restaurada.
                        </span>
                    </span>
                </label>

                <label class="ms-responsible-option" data-value="patient">
                    <input type="radio" name="responsible" value="patient">
                    <span class="ms-responsible-radio"></span>
                    <span class="ms-responsible-content">
                        <span class="ms-responsible-title">
                            <i class="fas fa-user mr-2" style="color:#605e5c;"></i>
                            El paciente
                        </span>
                        <span class="ms-responsible-desc">
                            Se aplica la política de 48 horas. Puede haber penalidad.
                        </span>
                    </span>
                </label>
            </div>
        </section>

        <!-- Appointment summary -->
        <section class="ms-section">
            <h4 class="ms-section-title">
                <i class="fas fa-info-circle mr-1"></i> Información de la Cita
            </h4>

            <div class="ms-info-grid">
                <div class="ms-info-row">
                    <span class="ms-info-label">Paciente</span>
                    <span class="ms-info-value">
                        <i class="fas fa-user text-primary mr-1"></i>
                        <?= Html::encode($patientName) ?>
                    </span>
                </div>
                <div class="ms-info-row">
                    <span class="ms-info-label">Fecha y hora</span>
                    <span class="ms-info-value">
                        <i class="fas fa-calendar-alt text-primary mr-1"></i>
                        <?= Yii::$app->formatter->asDate($model->fecha_atencion) ?>
                        <span class="mx-1">•</span>
                        <i class="fas fa-clock text-primary mr-1"></i>
                        <?= $model->hora_atencion ? substr($model->hora_atencion, 0, 5) : '—' ?>
                    </span>
                </div>
                <div class="ms-info-row">
                    <span class="ms-info-label">Clínica</span>
                    <span class="ms-info-value">
                        <i class="fas fa-building text-primary mr-1"></i>
                        <?= Html::encode($model->clinica ? $model->clinica->nombre : 'N/A') ?>
                    </span>
                </div>
                <div class="ms-info-row">
                    <span class="ms-info-label">Costo total</span>
                    <span class="ms-info-value" id="msInfoCost"
                        style="font-size:1.25rem;font-weight:700;color:#0078d4;">
                        $<?= number_format($totalCost, 2) ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($model->baremos)): ?>
                <div class="ms-services">
                    <div class="ms-services-title">
                        <i class="fas fa-stethoscope mr-1"></i> Servicios agendados
                    </div>
                    <ul class="ms-services-list">
                        <?php foreach ($model->baremos as $baremo): ?>
                            <li>
                                <span><?= Html::encode($baremo->nombre_servicio) ?></span>
                                <strong>$<?= number_format((float) $baremo->precio, 2) ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </section>

        <!-- Reason -->
        <section class="ms-section">
            <h4 class="ms-section-title">
                <i class="fas fa-edit mr-1"></i> Motivo de Cancelación <span class="text-danger">*</span>
            </h4>
            <textarea id="msCancelReason"
                class="form-control ms-textarea"
                rows="4"
                placeholder="Describa el motivo de la cancelación..."
                maxlength="1000"></textarea>
            <small class="form-text text-muted">
                <i class="fas fa-info-circle mr-1"></i>
                Este motivo quedará registrado para futuras referencias.
            </small>
            <div id="msCancelError" class="ms-error mt-2" style="display:none;"></div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="ms-drawer-footer">
        <button type="button" class="btn btn-light btn-lg" data-drawer-close>
            <i class="fas fa-times mr-1"></i> Cerrar
        </button>
        <button type="button"
            id="msCancelConfirm"
            class="btn btn-lg ms-btn-danger"
            data-url="<?= Url::to(['cancel-appointment', 'id' => $model->id]) ?>">
            <i class="fas fa-check-circle mr-1"></i> Confirmar Cancelación
        </button>
    </footer>
</aside>

<?php
// ============================================================
// Scoped CSS
// ============================================================
$this->registerCss(<<<CSS
/* Overlay */
.ms-drawer-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(2px);
    opacity: 0; visibility: hidden;
    transition: opacity .25s ease, visibility .25s ease;
    z-index: 1040;
}
.ms-drawer-overlay.ms-drawer-open { opacity: 1; visibility: visible; }

/* Drawer */
.ms-drawer {
    position: fixed; top: 0; right: 0; bottom: 0;
    width: min(520px, 92vw);
    background: #fff;
    box-shadow: -8px 0 32px rgba(0,0,0,0.18);
    transform: translateX(100%);
    transition: transform .32s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1045;
    display: flex; flex-direction: column;
}
.ms-drawer.ms-drawer-open { transform: translateX(0); }

/* Header */
.ms-drawer-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px;
    border-bottom: 1px solid #edebe9;
    background: #faf9f8;
}
.ms-drawer-header-left { display: flex; align-items: center; gap: 12px; }
.ms-drawer-icon {
    width: 40px; height: 40px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 18px;
    transition: background .2s ease;
}
.ms-drawer-title { margin: 0; font-size: 1.15rem; font-weight: 600; color: #201f1e; }
.ms-drawer-subtitle { color: #605e5c; font-size: .82rem; }
.ms-drawer-close {
    border: 0; background: transparent;
    width: 36px; height: 36px; border-radius: 6px;
    color: #605e5c; cursor: pointer;
    transition: background .15s ease;
}
.ms-drawer-close:hover { background: #edebe9; color: #201f1e; }

/* Body */
.ms-drawer-body { flex: 1 1 auto; overflow-y: auto; padding: 20px 22px; }

/* State card */
.ms-state-card {
    background: #faf9f8;
    border-left: 4px solid #0078d4;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 20px;
    transition: border-color .25s ease;
}
.ms-state-card-head {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 6px; flex-wrap: wrap;
}
.ms-state-card-head strong { font-size: 1rem; }
.ms-state-card-msg { margin: 0 0 4px 0; color: #323130; font-size: .95rem; }

/* Section */
.ms-section { margin-bottom: 22px; }
.ms-section-title {
    font-size: .95rem; font-weight: 600; color: #201f1e;
    margin: 0 0 10px 0; padding-bottom: 6px;
    border-bottom: 1px solid #edebe9;
}

/* ✅ Responsibility radio group */
.ms-responsible-group {
    display: flex; flex-direction: column; gap: 10px;
}
.ms-responsible-option {
    display: flex; align-items: flex-start; gap: 12px;
    border: 1.5px solid #edebe9; border-radius: 8px;
    padding: 12px 14px; cursor: pointer;
    background: #faf9f8;
    transition: all .18s ease;
    margin: 0;
}
.ms-responsible-option:hover { border-color: #c7c6c4; background: #f3f2f1; }
.ms-responsible-option.is-selected {
    border-color: #0078d4; background: #eff6fc;
    box-shadow: 0 0 0 1px #0078d4 inset;
}
.ms-responsible-option input[type="radio"] { display: none; }
.ms-responsible-radio {
    flex: 0 0 auto; width: 20px; height: 20px; border-radius: 50%;
    border: 2px solid #8a8886; position: relative; margin-top: 2px;
    transition: border-color .18s ease;
}
.ms-responsible-option.is-selected .ms-responsible-radio { border-color: #0078d4; }
.ms-responsible-option.is-selected .ms-responsible-radio::after {
    content: ''; position: absolute; inset: 3px;
    border-radius: 50%; background: #0078d4;
}
.ms-responsible-content { display: flex; flex-direction: column; gap: 3px; }
.ms-responsible-title { font-weight: 600; color: #201f1e; font-size: .95rem; }
.ms-responsible-desc  { font-size: .82rem; color: #605e5c; line-height: 1.35; }

/* Info grid */
.ms-info-grid { display: grid; grid-template-columns: 1fr; gap: 8px; }
.ms-info-row {
    display: flex; justify-content: space-between; align-items: center;
    gap: 12px; padding: 8px 0;
    border-bottom: 1px dashed #f3f2f1;
}
.ms-info-row:last-child { border-bottom: 0; }
.ms-info-label { color: #605e5c; font-size: .82rem; text-transform: uppercase; letter-spacing: .4px; }
.ms-info-value { color: #201f1e; font-size: .95rem; text-align: right; }

/* Services */
.ms-services { margin-top: 12px; }
.ms-services-title {
    font-size: .82rem; color: #605e5c;
    text-transform: uppercase; letter-spacing: .4px;
    margin-bottom: 6px;
}
.ms-services-list { list-style: none; padding: 0; margin: 0; }
.ms-services-list li {
    display: flex; justify-content: space-between;
    padding: 6px 0; border-bottom: 1px dashed #f3f2f1;
    font-size: .9rem;
}
.ms-services-list li:last-child { border-bottom: 0; }

/* Textarea */
.ms-textarea {
    border-radius: 6px; resize: vertical;
    border-color: #edebe9;
}
.ms-textarea:focus {
    border-color: #0078d4;
    box-shadow: 0 0 0 .2rem rgba(0,120,212,.15);
}

/* Error */
.ms-error {
    color: #d13438; background: #fdf3f4;
    border: 1px solid #f3d4d4; border-radius: 6px;
    padding: 8px 12px; font-size: .85rem;
}

/* Footer */
.ms-drawer-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 22px;
    border-top: 1px solid #edebe9;
    background: #faf9f8;
}
.ms-btn-danger {
    background: #d13438; border-color: #d13438; color: #fff; font-weight: 600;
}
.ms-btn-danger:hover { background: #a4262c; border-color: #a4262c; color: #fff; }
.ms-btn-danger:disabled { opacity: .6; cursor: not-allowed; }
CSS);
?>

<?php
// ============================================================
// Scoped JS
// ============================================================
$this->registerJs(<<<JS
(function () {
    var drawer  = document.getElementById('msCancelDrawer');
    var overlay = document.getElementById('msCancelOverlay');
    if (!drawer || !overlay) { return; }

    var canEarly = drawer.getAttribute('data-can-early') === '1';
    var isPast   = drawer.getAttribute('data-is-past')   === '1';

    // -------- Open --------
    window.msOpenCancelDrawer = function () {
        requestAnimationFrame(function () {
            drawer.classList.add('ms-drawer-open');
            overlay.classList.add('ms-drawer-open');
            drawer.setAttribute('aria-hidden', 'false');
            overlay.setAttribute('aria-hidden', 'false');
            var ta = document.getElementById('msCancelReason');
            if (ta) { setTimeout(function () { ta.focus(); }, 320); }
        });
    };

    // -------- Close --------
    function closeDrawer() {
        drawer.classList.remove('ms-drawer-open');
        overlay.classList.remove('ms-drawer-open');
        drawer.setAttribute('aria-hidden', 'true');
        overlay.setAttribute('aria-hidden', 'true');
        setTimeout(function () {
            if (drawer.parentNode)  { drawer.parentNode.removeChild(drawer); }
            if (overlay.parentNode) { overlay.parentNode.removeChild(overlay); }
        }, 320);
    }
    drawer.querySelectorAll('[data-drawer-close]').forEach(function (el) {
        el.addEventListener('click', closeDrawer);
    });
    overlay.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && drawer.classList.contains('ms-drawer-open')) {
            closeDrawer();
        }
    });

    // -------- Live state update when responsibility changes --------
    var stateCard  = document.getElementById('msStateCard');
    var stateIcon  = document.getElementById('msStateIcon');
    var stateTitle = document.getElementById('msStateTitle');
    var stateBadge = document.getElementById('msStateBadge');
    var stateMsg   = document.getElementById('msStateMsg');
    var stateHours = document.getElementById('msStateHours');
    var stateHoursValue = document.getElementById('msStateHoursValue');
    var infoCost   = document.getElementById('msInfoCost');
    var drawerIcon = document.getElementById('msDrawerIcon');

    function applyClinicState() {
        var blue = '#0078d4';
        stateCard.style.borderLeftColor = blue;
        stateIcon.className = 'fas fa-hospital';
        stateIcon.style.color = blue;
        stateTitle.textContent = 'Cancelación por Clínica';
        stateTitle.style.color = blue;
        stateBadge.textContent = 'SIN PENALIDAD';
        stateBadge.className = 'badge';
        stateBadge.style.background = blue;
        stateBadge.style.color = '#fff';
        stateMsg.textContent = 'Su cita fue cancelada por la clínica. No se aplicará penalidad.';
        stateHours.style.display = 'none';
        infoCost.style.color = blue;
        drawerIcon.style.background = blue;
    }

    function applyPatientState() {
        var isEarly = canEarly && !isPast;
        var color   = isEarly ? '#28a745' : '#d13438';
        var icon    = isEarly ? 'fa-check-circle' : 'fa-exclamation-triangle';

        stateCard.style.borderLeftColor = color;
        stateIcon.className = 'fas ' + icon;
        stateIcon.style.color = color;
        stateTitle.textContent = isEarly ? 'Cancelación sin Penalidad' : 'Cancelación con Penalidad';
        stateTitle.style.color = color;
        stateBadge.textContent = isEarly ? 'SIN PENALIDAD' : 'CON PENALIDAD';
        stateBadge.className = isEarly ? 'badge badge-success' : 'badge badge-danger';
        stateBadge.style.background = '';
        stateBadge.style.color = '';
        stateMsg.textContent = isEarly
            ? 'Aún puede cancelar esta cita sin que se descuente de su cobertura.'
            : (isPast
                ? 'Esta cita ya ocurrió. Al cancelar se aplicará penalidad.'
                : 'Ya no es posible cancelar sin penalidad (faltan menos de 48 horas).');
        infoCost.style.color = color;
        drawerIcon.style.background = color;

        if (isEarly) {
            stateHours.style.display = 'none';
        } else {
            stateHours.style.display = '';
            // Keep existing hours value if any
        }
    }

    function applySelected() {
        var selected = drawer.querySelector('input[name="responsible"]:checked');
        var value = selected ? selected.value : 'clinic';
        if (value === 'clinic') { applyClinicState(); } else { applyPatientState(); }
    }

    // Attach radio change handlers
    drawer.querySelectorAll('.ms-responsible-option').forEach(function (opt) {
        opt.addEventListener('click', function (e) {
            e.preventDefault();
            var input = opt.querySelector('input[type="radio"]');
            if (input) { input.checked = true; }
            drawer.querySelectorAll('.ms-responsible-option').forEach(function (o) {
                o.classList.toggle('is-selected', o === opt);
            });
            applySelected();
        });
    });

    // Initial state
    applySelected();

    // -------- Submit --------
    var btn    = document.getElementById('msCancelConfirm');
    var errBox = document.getElementById('msCancelError');
    if (btn) {
        btn.addEventListener('click', function () {
            var reason = (document.getElementById('msCancelReason').value || '').trim();
            var selected = drawer.querySelector('input[name="responsible"]:checked');
            var responsible = selected ? selected.value : 'clinic';

            errBox.style.display = 'none';
            errBox.textContent = '';

            if (reason.length < 3) {
                errBox.textContent = 'Debe especificar un motivo (mínimo 3 caracteres).';
                errBox.style.display = 'block';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...';

            fetch(btn.getAttribute('data-url'), {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': (document.getElementById('csrf-token') || {}).value || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: 'reason=' + encodeURIComponent(reason)
                    + '&responsible=' + encodeURIComponent(responsible)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    closeDrawer();
                    if (window.location && window.location.reload) {
                        window.location.reload();
                    }
                } else {
                    errBox.textContent = (data && data.message) ? data.message : 'Error inesperado.';
                    errBox.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Confirmar Cancelación';
                }
            })
            .catch(function () {
                errBox.textContent = 'Error de red. Intente nuevamente.';
                errBox.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Confirmar Cancelación';
            });
        });
    }
})();
JS);
