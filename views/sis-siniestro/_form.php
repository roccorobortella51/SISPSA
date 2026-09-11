<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;
use yii\bootstrap4\Modal;
use kartik\file\FileInput;
use kartik\file\FileInputAsset;
use yii\data\ActiveDataProvider;
use app\models\PreexistenciasSearch;
use app\models\Preexistencias;

/* @var $this yii\web\View */
/* @var $model app\models\SisSiniestro */
/* @var $form yii\widgets\ActiveForm */
/* @var $afiliado app\models\UserDatos */
/* @var $es_cita int */

$this->registerCssFile(Yii::getAlias('@web') . "/css/_formsiniestros.css", ['position' => View::POS_HEAD]);

FileInputAsset::register($this);

// ============================================================
// GUARDS: Ensure variables are defined
// ============================================================
if (!isset($model) || $model === null) {
    $model = new \app\models\SisSiniestro();
}

if (!isset($afiliado) || $afiliado === null) {
    if (isset($model) && $model->iduser) {
        $afiliado = \app\models\UserDatos::findOne($model->iduser);
    } else {
        $afiliado = null;
    }
}

if (!isset($es_cita)) {
    $es_cita = 0;
}

// Obtener información del plan del afiliado
if ($afiliado && is_object($afiliado)) {
    $planId = $afiliado->plan_id;
    $afiliadoObj = $afiliado;
} elseif (is_array($afiliado) && isset($afiliado['plan_id'])) {
    $planId = $afiliado['plan_id'];
    $afiliadoObj = (object)$afiliado;
} elseif ($afiliado && is_numeric($afiliado)) {
    $planId = (int)$afiliado;
    $afiliadoObj = \app\models\UserDatos::findOne($planId);
} else {
    $planId = null;
    $afiliadoObj = null;
}

if (!$afiliadoObj) {
    echo '<div class="alert alert-danger">Error: No se pudo cargar la información del afiliado.</div>';
    return;
}

$plan = \app\models\Planes::findOne($planId);
$afiliado = $afiliadoObj;
$precioPlan = $plan ? $plan->cobertura : 0;

// Obtener la sumatoria de siniestros del afiliado
$afiliadoId = is_object($afiliado) ? $afiliado->id : (is_array($afiliado) && isset($afiliado['id']) ? $afiliado['id'] : (int)$afiliado);
$sumatoriaSiniestros = \app\models\SisSiniestro::find()
    ->where(['iduser' => (int)$afiliadoId])
    ->andWhere(['not', ['costo_total' => null]])
    ->sum('costo_total');

$totalDisponible = $precioPlan - $sumatoriaSiniestros;

// OBTENER el parámetro 'es_cita'
$esCita = (int)$es_cita;

// Definir los modos y términos
$esCitaMode = ($esCita === 1);
$terminoPrincipal = $esCitaMode ? 'Cita' : 'Atención';
$tituloSeccion = 'Datos de la ' . $terminoPrincipal;

// Obtener el contrato activo del afiliado
$afiliadoIdContrato = is_object($afiliado) ? $afiliado->id : (is_array($afiliado) && isset($afiliado['id']) ? $afiliado['id'] : (int)$afiliado);
$contrato = \app\models\Contratos::find()
    ->where(['user_id' => $afiliadoIdContrato])
    ->andWhere(['estatus' => 'Activo'])
    ->orderBy(['created_at' => SORT_DESC])
    ->one();

// Get baremos data for historical section
$afiliadoIdHist = is_object($afiliado) ? $afiliado->id : (is_array($afiliado) && isset($afiliado['id']) ? $afiliado['id'] : (int)$afiliado);
$baremosUtilizados = \app\models\SisSiniestroBaremo::find()
    ->joinWith(['siniestro', 'baremo'])
    ->where(['sis_siniestro.iduser' => $afiliadoIdHist])
    ->andWhere(['baremo.estatus' => 'Activo'])
    ->orderBy(['sis_siniestro.fecha' => SORT_DESC])
    ->all();

// Separar en citas y siniestros
$baremosCitas = [];
$baremosSiniestros = [];

foreach ($baremosUtilizados as $siniestroBaremo) {
    if ($siniestroBaremo->siniestro) {
        $item = [
            'fecha' => $siniestroBaremo->siniestro->fecha,
            'nombre_servicio' => $siniestroBaremo->baremo->nombre_servicio,
            'area' => $siniestroBaremo->baremo->area ? $siniestroBaremo->baremo->area->nombre : 'Sin área',
            'descripcion' => $siniestroBaremo->baremo->descripcion,
            'precio' => $siniestroBaremo->baremo->precio ?? 0,
            'tipo' => $siniestroBaremo->siniestro->es_cita ? 'Cita' : 'Siniestro',
            'estado' => $siniestroBaremo->siniestro->estatus ?? 'Desconocido'
        ];

        if ($siniestroBaremo->siniestro->es_cita) {
            $baremosCitas[] = $item;
        } else {
            $baremosSiniestros[] = $item;
        }
    }
}

// ============================================================
// GET PRE-EXISTENCIAS FOR THE AFFILIATE
// ============================================================
$preexistenciasDataProvider = null;
$preexistenciasCount = 0;
$preexistenciasModels = [];

if ($afiliado && $afiliado->id) {
    $searchModel = new PreexistenciasSearch();
    $preexistenciasDataProvider = $searchModel->search(Yii::$app->request->queryParams, $afiliado->id);
    $preexistenciasCount = $preexistenciasDataProvider->getTotalCount();
    $preexistenciasModels = $preexistenciasDataProvider->getModels();
}

// Create a new pre-existencia model for the modal
$newPreexistencia = new Preexistencias();
$newPreexistencia->user_id = $afiliado->id;
$newPreexistencia->estatus = Preexistencias::ESTATUS_ACTIVO;

// ============================================================
// INITIALIZE ALL BAREMOS VARIABLES (BEFORE CONDITIONAL BLOCK)
// ============================================================
$baremosTotales = [];
$baremosHtml = [];
$baremosInfo = [];
$baremosRestringidosIDs = [];
$baremosForzados = [];
$baremosSinPlazo = [];
$baremosConPlazoCumplido = [];
$baremosPendientesPlazo = [];
$baremosAgotados = [];
$baremosDisponiblesInfo = [];
$selectedBaremos = [];
$emergencyExcluded = false;
$emergencyCount = 0;

// ============================================================
// BUILD BAREMOS DATA
// ============================================================
if ($contrato && $contrato->estatus === 'Activo') {
    $query = \app\models\PlanesItemsCobertura::find()
        ->joinWith('baremo')
        ->joinWith('plan')
        ->joinWith('baremo.area')
        ->where(['planes.clinica_id' => $afiliado->clinica_id])
        ->andWhere(['baremo.estatus' => 'Activo'])
        ->andWhere(['planes.id' => $afiliado->plan_id]);

    if ($esCitaMode) {
        $emergencyCountQuery = clone $query;
        $emergencyCount = $emergencyCountQuery
            ->andWhere(['ILIKE', 'area.nombre', 'EMERGENCIA'])
            ->count();

        $query->andWhere(['NOT ILIKE', 'area.nombre', 'EMERGENCIA']);
        $emergencyExcluded = ($emergencyCount > 0);
    }

    if ($esCitaMode) {
        $query->andWhere([
            'or',
            ['>', 'planes_items_cobertura.plazo_espera', 0],
            ['>', 'planes_items_cobertura.cantidad_limite', 0]
        ]);
    }

    $planesItemsCobertura = $query->all();

    if (isset($model) && $model !== null && !$model->isNewRecord && $model->id) {
        $baremosDirectos = (new \yii\db\Query())
            ->select(['baremo_id'])
            ->from('sis_siniestro_baremo')
            ->where(['siniestro_id' => $model->id])
            ->column();

        if (!empty($baremosDirectos)) {
            $selectedBaremos = $baremosDirectos;
        }
    }

    $fechaActual = new \DateTime();

    foreach ($planesItemsCobertura as $item) {
        if ($item->baremo) {
            $hasPlazoEver = (!empty($item->plazo_espera) && $item->plazo_espera > 0);
            $precioBaremo = $item->baremo->precio ?? 0;
            $area = $item->baremo->area ? $item->baremo->area->nombre : 'Sin área';
            $servicio = $item->baremo->nombre_servicio;
            $descripcion = $item->baremo->descripcion ?? '';

            $textoPlano = $servicio . " (" . $area . ")";
            if (!empty($descripcion)) {
                $textoPlano .= " - " . $descripcion;
            }

            $queryCount = \app\models\SisSiniestroBaremo::find()
                ->joinWith('siniestro')
                ->where(['baremo_id' => $item->baremo_id])
                ->andWhere(['iduser' => $afiliado->id]);

            // DO NOT filter by es_cita - count ALL events (both citas and atenciones)
            $vecesUsado = $queryCount->count();

            $remainingUses = 0;
            $excedeLimite = false;

            if ($item->cantidad_limite !== null && $item->cantidad_limite > 0) {
                $remainingUses = $item->cantidad_limite - $vecesUsado;
                if ($remainingUses <= 0) {
                    $excedeLimite = true;
                }
            } else {
                $remainingUses = 999;
            }

            $esBaremoGuardado = !$model->isNewRecord && in_array($item->baremo_id, $selectedBaremos);
            $isRestrictedByPlazo = false;

            if ($contrato && $hasPlazoEver) {
                $fechaContratoIni = new \DateTime($contrato->fecha_ini);
                $diff = $fechaContratoIni->diff($fechaActual);
                $mesesTranscurridos = $diff->y * 12 + $diff->m;
                $plazoRequerido = (int)$item->plazo_espera;

                if ($mesesTranscurridos < $plazoRequerido) {
                    $isRestrictedByPlazo = true;
                }
            }

            $hasValidLimit = ($item->cantidad_limite !== null && $item->cantidad_limite > 0);
            $hasValidPlazo = ($item->plazo_espera !== null && $item->plazo_espera > 0);

            if (!$hasValidLimit && !$hasValidPlazo) {
                continue;
            }

            $debeIncluirse = true;

            if ($excedeLimite) {
                $baremosAgotados[$item->baremo_id] = $textoPlano;
                $baremosInfo[$item->baremo_id]['es_agotado'] = true;
                if (!$esBaremoGuardado) {
                    $debeIncluirse = false;
                }
            } elseif ($isRestrictedByPlazo) {
                $baremosPendientesPlazo[$item->baremo_id] = $textoPlano;
                $baremosRestringidosIDs[] = $item->baremo_id;
                $baremosInfo[$item->baremo_id]['is_restricted_by_plazo'] = true;

                if ($contrato) {
                    $fechaContratoIni = new \DateTime($contrato->fecha_ini);
                    $plazoRequerido = (int)$item->plazo_espera;
                    $fechaTarget = clone $fechaContratoIni;
                    $fechaTarget->modify("+{$plazoRequerido} months");
                    $diff = $fechaActual->diff($fechaTarget);

                    $mesesRestantes = ($diff->y * 12) + $diff->m;
                    $diasRestantes = $diff->d;

                    $baremosInfo[$item->baremo_id]['remaining_months'] = $mesesRestantes;
                    $baremosInfo[$item->baremo_id]['remaining_days'] = $diasRestantes;

                    if ($mesesRestantes > 0 && $diasRestantes > 0) {
                        $tiempoRestanteTexto = $mesesRestantes . " mes" . ($mesesRestantes > 1 ? "es" : "") . " y " . $diasRestantes . " día" . ($diasRestantes > 1 ? "s" : "");
                    } elseif ($mesesRestantes > 0) {
                        $tiempoRestanteTexto = $mesesRestantes . " mes" . ($mesesRestantes > 1 ? "es" : "");
                    } elseif ($diasRestantes > 0) {
                        $tiempoRestanteTexto = $diasRestantes . " día" . ($diasRestantes > 1 ? "s" : "");
                    } else {
                        $tiempoRestanteTexto = "Próximamente";
                    }
                    $baremosInfo[$item->baremo_id]['remaining_text'] = $tiempoRestanteTexto;
                }

                if (!$esBaremoGuardado) {
                    $debeIncluirse = false;
                }
            } else {
                $hasRemainingUses = ($remainingUses > 0) || ($item->cantidad_limite === null || $item->cantidad_limite == 0);
                if ($hasRemainingUses) {
                    $baremosSinPlazo[$item->baremo_id] = $textoPlano;
                    $baremosDisponiblesInfo[$item->baremo_id] = [
                        'nombre' => $servicio,
                        'area' => $area,
                        'descripcion' => $descripcion,
                        'precio' => $precioBaremo,
                        'cantidad_limite' => (int)$item->cantidad_limite,
                        'veces_usado' => (int)$vecesUsado,
                        'disponibles' => ($remainingUses > 0 && $remainingUses < 999) ? $remainingUses : ($item->cantidad_limite > 0 ? $remainingUses : 'Ilimitado'),
                        'remaining' => $remainingUses,
                    ];
                }
            }

            $baremosInfo[$item->baremo_id] = array_merge($baremosInfo[$item->baremo_id] ?? [], [
                'nombre' => $servicio,
                'area' => $area,
                'descripcion' => $descripcion,
                'plazo_espera' => $item->plazo_espera,
                'cantidad_limite' => (int)$item->cantidad_limite,
                'veces_usado' => (int)$vecesUsado,
                'precio' => $precioBaremo,
                'has_plazo_ever' => $hasPlazoEver,
                'excede_limite' => $excedeLimite,
                'es_historico' => $esBaremoGuardado,
                'remaining_uses' => $remainingUses,
            ]);

            $disponibles = $remainingUses;
            $availabilityClass = '';
            $availabilityText = '';

            if ($item->cantidad_limite !== null && $item->cantidad_limite > 0) {
                if ($disponibles <= 0) {
                    $availabilityClass = 'none';
                    $availabilityText = 'Agotado';
                } elseif ($disponibles == 1) {
                    $availabilityClass = 'low';
                    $availabilityText = '1 de ' . $item->cantidad_limite . ' disponible';
                } else {
                    $availabilityClass = '';
                    $availabilityText = $disponibles . ' de ' . $item->cantidad_limite . ' disponibles';
                }
            } else {
                $availabilityClass = '';
                if ($vecesUsado > 0) {
                    $availabilityText = 'Usado ' . $vecesUsado . ' vez/veces (sin límite)';
                } else {
                    $availabilityText = 'Sin límite';
                }
            }

            $htmlFormateado = "<div class='baremo-dropdown-option'>";
            $htmlFormateado .= "<div class='baremo-first-row'>";
            $htmlFormateado .= "<div class='baremo-content-main'>";
            $htmlFormateado .= "<div class='baremo-area'><div class='baremo-area-label'>Área</div><div class='baremo-area-value'>" . Html::encode($area) . "</div></div>";
            $htmlFormateado .= "<div class='baremo-servicio'><div class='baremo-servicio-label'>Servicio</div><div class='baremo-servicio-value'>" . Html::encode($servicio) . "</div></div>";
            $htmlFormateado .= "<div class='baremo-descripcion'><div class='baremo-descripcion-label'>Descripción</div><div class='baremo-descripcion-value' title='" . Html::encode($descripcion ?: 'Sin descripción') . "'>" . Html::encode($descripcion ?: 'Sin descripción') . "</div></div>";
            $htmlFormateado .= "</div>";
            $htmlFormateado .= "<div class='baremo-status'>";
            if ($esBaremoGuardado && !$debeIncluirse) {
                $htmlFormateado .= "<span class='historico'>Histórico</span>";
            } elseif ($isRestrictedByPlazo) {
                $htmlFormateado .= "<span class='restringido'>Restringido</span>";
            } elseif ($excedeLimite) {
                $htmlFormateado .= "<span class='agotado'>Agotado</span>";
            } else {
                $htmlFormateado .= "<span class='disponible'>Disponible</span>";
            }
            $htmlFormateado .= "</div></div>";
            $htmlFormateado .= "<div class='baremo-second-row'>";
            $htmlFormateado .= "<div class='baremo-price-container'><span class='baremo-price'>$" . number_format($precioBaremo, 2) . "</span></div>";
            if ($isRestrictedByPlazo && isset($baremosInfo[$item->baremo_id]['remaining_text'])) {
                $htmlFormateado .= "<div class='baremo-waiting-period'><i class='fas fa-clock me-1'></i><span>Disponible en " . $baremosInfo[$item->baremo_id]['remaining_text'] . "</span></div>";
            } else {
                $htmlFormateado .= "<div class='baremo-availability " . $availabilityClass . "'>" . $availabilityText . "</div>";
            }
            $htmlFormateado .= "</div></div>";

            if ($debeIncluirse || $esBaremoGuardado) {
                $baremosHtml[$item->baremo_id] = $htmlFormateado;
                if (!$excedeLimite && !$isRestrictedByPlazo) {
                    $baremosTotales[$item->baremo_id] = $textoPlano;
                } elseif ($esBaremoGuardado) {
                    $baremosForzados[$item->baremo_id] = $textoPlano;
                    $baremosTotales[$item->baremo_id] = $textoPlano;
                }
            }
        }
    }

    $baremosTotales = $baremosForzados + $baremosSinPlazo;
}

// ============================================================
// GET STATUS OPTIONS FOR PRE-EXISTENCIAS
// ============================================================
$statusOptions = Preexistencias::getStatusOptions();

// ===== PROGRESS OVERLAY CSS =====
$this->registerCss("
/* ===== PROGRESS OVERLAY STYLES ===== */
.progress-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(4px);
    z-index: 99999;
    display: none;
    justify-content: center;
    align-items: center;
    transition: all 0.3s ease;
}

.progress-overlay.active {
    display: flex;
}

.progress-modal {
    background: #ffffff;
    border-radius: 16px;
    padding: 40px 50px 45px;
    max-width: 480px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.4s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.progress-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    position: relative;
}

.progress-icon .icon-wrapper {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #1a3a6b 0%, #2a5298 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: #ffffff;
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(26, 58, 107, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 15px rgba(26, 58, 107, 0);
    }
}

.progress-title {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 8px 0;
}

.progress-subtitle {
    font-size: 14px;
    color: #6c757d;
    margin: 0 0 25px 0;
    line-height: 1.5;
}

.progress-steps {
    display: flex;
    flex-direction: column;
    gap: 14px;
    text-align: left;
    margin-bottom: 28px;
}

.progress-step {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 16px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
    opacity: 0.5;
}

.progress-step.active {
    opacity: 1;
    background: #f0f4ff;
    border-left: 4px solid #2a5298;
}

.progress-step.done {
    opacity: 1;
    background: #f0faf0;
    border-left: 4px solid #28a745;
}

.progress-step .step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e8ecf1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #6c757d;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.progress-step.active .step-number {
    background: #2a5298;
    color: #ffffff;
}

.progress-step.done .step-number {
    background: #28a745;
    color: #ffffff;
}

.progress-step .step-icon {
    font-size: 18px;
    width: 28px;
    text-align: center;
    flex-shrink: 0;
}

.progress-step .step-text {
    flex: 1;
}

.progress-step .step-text .step-label {
    font-size: 14px;
    font-weight: 500;
    color: #1a1a2e;
}

.progress-step .step-text .step-desc {
    font-size: 12px;
    color: #6c757d;
}

.progress-step .step-status {
    font-size: 14px;
    color: #6c757d;
}

.progress-step.done .step-status {
    color: #28a745;
}

.progress-step.active .step-status {
    color: #2a5298;
}

.progress-bar-container {
    width: 100%;
    height: 6px;
    background: #e8ecf1;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 20px;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #1a3a6b, #2a5298);
    border-radius: 3px;
    transition: width 0.6s ease;
    width: 0%;
}

.progress-percentage {
    font-size: 13px;
    font-weight: 600;
    color: #2a5298;
}

.progress-loading-spinner {
    display: inline-block;
    width: 18px;
    height: 18px;
    border: 3px solid #e8ecf1;
    border-top: 3px solid #2a5298;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    vertical-align: middle;
    margin-left: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.progress-success-check {
    display: none;
    font-size: 48px;
    color: #28a745;
    animation: checkIn 0.5s ease;
}

@keyframes checkIn {
    0% {
        opacity: 0;
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.progress-error-icon {
    display: none;
    font-size: 48px;
    color: #dc3545;
    animation: checkIn 0.5s ease;
}

.progress-overlay.complete .progress-icon .icon-wrapper {
    animation: none;
    background: #28a745;
}

.progress-overlay.error .progress-icon .icon-wrapper {
    animation: none;
    background: #dc3545;
}

.progress-overlay.complete .progress-loading-spinner {
    display: none !important;
}

.progress-overlay.error .progress-loading-spinner {
    display: none !important;
}

/* ===== END PROGRESS OVERLAY STYLES ===== */

/* ===== TABBED LAYOUT STYLES ===== */
.tab-content-wrapper {
    background: #ffffff;
    border: 1px solid #edebe9;
    border-radius: 2px;
    overflow: hidden;
}

.tab-content-wrapper .nav-tabs {
    background: #faf9f8;
    border-bottom: 1px solid #edebe9;
    padding: 0 16px;
    display: flex;
    flex-wrap: wrap;
    overflow: visible;
    margin-bottom: 0;
}

.tab-content-wrapper .nav-tabs .nav-item {
    margin-bottom: -1px;
    flex-shrink: 0;
}

.tab-content-wrapper .nav-tabs .nav-link {
    border: none !important;
    border-bottom: 2px solid transparent !important;
    color: #605e5c !important;
    font-weight: 500;
    font-size: 14px;
    padding: 14px 20px !important;
    background: transparent !important;
    border-radius: 0 !important;
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
    text-decoration: none;
}

.tab-content-wrapper .nav-tabs .nav-link:hover {
    color: #323130 !important;
    background: rgba(0, 0, 0, 0.04) !important;
}

.tab-content-wrapper .nav-tabs .nav-link:focus {
    outline: none;
}

/* ACTIVE TAB - Use !important on all properties */
.tab-content-wrapper .nav-tabs .nav-link.active {
    color: #0078d4 !important;
    background: #e8f0fe !important;
    border-bottom: 3px solid #0078d4 !important;
    font-weight: 600 !important;
}

.tab-content-wrapper .nav-tabs .nav-link i {
    margin-right: 8px;
    font-size: 14px;
}

.tab-content-wrapper .nav-tabs .nav-link .badge {
    margin-left: 6px;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 12px;
    background: #e8ecf1;
    color: #605e5c;
}

.tab-content-wrapper .nav-tabs .nav-link.active .badge {
    background: #deecf9;
    color: #0078d4;
}

.tab-content-wrapper .tab-content {
    padding: 24px;
    background: #ffffff;
}

.tab-content-wrapper .tab-pane {
    display: none;
    animation: fadeIn 0.3s ease;
}

.tab-content-wrapper .tab-pane.active {
    display: block;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Tab badge counters */
.tab-badge-count {
    display: inline-block;
    background: #e8ecf1;
    color: #605e5c;
    border-radius: 12px;
    padding: 1px 8px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 6px;
}

.nav-link.active .tab-badge-count {
    background: #deecf9;
    color: #0078d4;
}

/* Make sure tabs don't overflow */
.tab-content-wrapper .nav-tabs .nav-item:last-child {
    margin-right: 0;
}

/* ===== END TABBED LAYOUT STYLES ===== */

/* ===== SECTION HEADER WITH TITLE AND ARROW INLINE ===== */
.section-header-inline {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.section-header-inline .section-title-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.section-header-inline .section-toggle-wrapper {
    display: flex;
    align-items: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.section-header-inline .section-toggle-wrapper .section-toggle-icon {
    transition: transform 0.3s ease;
    display: inline-block;
}

.section-header-inline .section-toggle-wrapper .section-toggle-icon.collapsed {
    transform: rotate(-90deg);
}

/* ===== BOTTOM ACTIONS BAR ===== */
.bottom-actions-bar {
    background: #ffffff;
    border: 1px solid #edebe9;
    border-radius: 2px;
    padding: 20px 24px;
    margin-top: 20px;
    box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
}

.bottom-actions-bar .action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: center;
    align-items: center;
}

.bottom-actions-bar .action-hint {
    text-align: center;
    margin-top: 12px;
    color: #6c757d;
    font-size: 13px;
}

.bottom-actions-bar .action-hint i {
    margin-right: 6px;
}

/* ===== PRE-EXISTENCIAS STYLES ===== */
.preexistencias-section .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.preexistencias-section .card-header .header-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.preexistencias-table {
    font-size: 14px;
}

.preexistencias-table thead th {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.preexistencias-table tbody tr:hover {
    background-color: #f8f9fa;
}

.preexistencias-table .badge {
    font-size: 12px;
    padding: 4px 10px;
}

.preexistencias-table .action-buttons .btn {
    padding: 2px 8px;
    font-size: 12px;
    margin: 0 2px;
}

.badge-status-activo {
    background: #28a745;
    color: white;
}

.badge-status-inactivo {
    background: #6c757d;
    color: white;
}

.badge-status-tratamiento {
    background: #17a2b8;
    color: white;
}

.badge-status-remision {
    background: #ffc107;
    color: #212529;
}

/* ===== PRE-EXISTENCIAS MODAL STYLES ===== */
.preexistencia-modal .modal-header {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    color: white;
}

.preexistencia-modal .modal-header .close {
    color: white;
    opacity: 0.8;
}

.preexistencia-modal .modal-header .close:hover {
    opacity: 1;
}

.preexistencia-modal .modal-body {
    padding: 24px;
}

.preexistencia-modal .modal-footer {
    border-top: 1px solid #edebe9;
}

/* ===== END PRE-EXISTENCIAS STYLES ===== */
");

// ===== JAVASCRIPT - Clean version =====
$jsCode = <<<'JS'
// ============================================================
// PROGRESS OVERLAY CONTROLS - WITH VALIDATION
// ============================================================
(function() {
    'use strict';
    
    // ============================================================
    // TAB CONTROLS - MUST BE DEFINED FIRST
    // ============================================================
    window.activateTab = function(tabId) {
        // Remove active from all tabs and panes
        jQuery('.nav-tabs .nav-link').removeClass('active');
        jQuery('.tab-pane').removeClass('active show');
        
        // Hide all panes explicitly
        jQuery('.tab-pane').css('display', 'none');
        
        // Activate the selected tab and pane
        jQuery('.nav-tabs .nav-link[data-tab="' + tabId + '"]').addClass('active');
        jQuery('#tab-' + tabId).addClass('active');
        
        // Force show the active pane
        jQuery('#tab-' + tabId).css('display', 'block');
        
        // Store active tab in localStorage
        try {
            localStorage.setItem('activeFormTab', tabId);
        } catch (e) {
            // Ignore localStorage errors
        }
    };
    
    // ============================================================
    // INIT TABS ON PAGE LOAD
    // ============================================================
    function initTabs() {
        var savedTab = null;
        try {
            savedTab = localStorage.getItem('activeFormTab');
        } catch (e) {
            // Ignore
        }
        
        var activeTab = savedTab || 'afiliado';
        
        // Check URL hash for tab (for server-side errors)
        var hash = window.location.hash;
        if (hash && hash.startsWith('#tab-')) {
            var tabFromHash = hash.replace('#tab-', '');
            if (jQuery('.nav-tabs .nav-link[data-tab="' + tabFromHash + '"]').length > 0) {
                activeTab = tabFromHash;
            }
        }
        
        // Make sure the tab exists
        if (jQuery('.nav-tabs .nav-link[data-tab="' + activeTab + '"]').length === 0) {
            activeTab = 'afiliado';
        }
        
        window.activateTab(activeTab);
    }
    
    // Click handler for tabs
    jQuery(document).on('click', '.nav-tabs .nav-link', function(e) {
        e.preventDefault();
        var tabId = jQuery(this).data('tab');
        if (tabId) {
            window.activateTab(tabId);
        }
    });
    
    // ============================================================
// VALIDATION FUNCTION - CLEAN VERSION (NO DUPLICATE ERRORS)
// ============================================================
window.validateForm = function() {
    var isValid = true;
    var errorMessages = [];
    var errorTabId = null;
    
    // Clear previous errors
    jQuery('.is-invalid').removeClass('is-invalid');
    jQuery('.invalid-feedback').remove();
    
    // 1. Validate Descripción de la Atención (Tab: atencion)
    var descripcionField = document.querySelector('#sissiniestro-descripcion');
    if (descripcionField) {
        var descripcionValue = descripcionField.value.trim();
        if (descripcionValue === '' || descripcionValue.length < 3) {
            isValid = false;
            // Let the server-side validation handle the error message
            // Just highlight the field and switch to the correct tab
            jQuery(descripcionField).addClass('is-invalid');
            errorTabId = 'atencion';
            // REMOVED: The client-side error message to avoid duplication
        }
    }
    
    // 2. Validate Medical Services (Tab: servicios)
    var baremosSelect = document.querySelector('#baremos-select');
    if (baremosSelect) {
        var selectedValues = jQuery(baremosSelect).val();
        if (!selectedValues || selectedValues.length === 0) {
            isValid = false;
            
            var select2Container = jQuery(baremosSelect).parent().find('.select2-container');
            if (select2Container.length) {
                select2Container.css({
                    'border': '2px solid #dc3545',
                    'border-radius': '4px',
                    'padding': '2px'
                });
            }
            
            if (!errorTabId) {
                errorTabId = 'servicios';
            }
            // REMOVED: The client-side error message to avoid duplication
        } else {
            var select2Container = jQuery(baremosSelect).parent().find('.select2-container');
            if (select2Container.length) {
                select2Container.css({
                    'border': '',
                    'border-radius': '',
                    'padding': ''
                });
            }
        }
    }
    
    // 3. Validate Fecha de Atención (Tab: atencion)
    var fechaField = document.querySelector('#sissiniestro-fecha_atencion');
    if (fechaField) {
        var fechaValue = fechaField.value.trim();
        if (!fechaValue) {
            isValid = false;
            jQuery(fechaField).addClass('is-invalid');
            if (!errorTabId) {
                errorTabId = 'atencion';
            }
            // REMOVED: The client-side error message to avoid duplication
        }
    }
    
    // If invalid, switch to the tab with errors and let server show the error messages
    if (!isValid) {
        // SWITCH TO THE TAB WITH ERRORS
        if (errorTabId) {
            window.activateTab(errorTabId);
        }
        
        // Remove any existing client-side error alert (let server handle it)
        var errorAlert = document.getElementById('validation-error-alert');
        if (errorAlert) {
            errorAlert.remove();
        }
        
        // Scroll to first error after tab switch animation
        setTimeout(function() {
            var firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 400);
        
        return false;
    }
    
    return true;
};
    
    // ============================================================
    // INTERCEPT FORM SUBMISSION
    // ============================================================
    jQuery(document).ready(function() {
        // Initialize tabs
        initTabs();
        
        var $form = jQuery('.sis-siniestro-form form');
        if ($form.length === 0) {
            $form = jQuery('#sis-siniestro-form');
        }
        if ($form.length === 0) {
            $form = jQuery('form');
        }
        
        $form.on('submit', function(e) {
            // RUN VALIDATION FIRST - BEFORE PROGRESS OVERLAY
            if (!window.validateForm()) {
                e.preventDefault();
                return false;
            }
            
            // If validation passes, show progress overlay
            var $thisForm = jQuery(this);
            var hasErrors = $thisForm.find('.has-error').length > 0;
            
            if (!hasErrors) {
                // Progress overlay show logic...
            }
        });
    });
    
    // ============================================================
    // CHECK FOR SERVER-SIDE ERRORS ON PAGE LOAD
    // ============================================================
    jQuery(document).ready(function() {
        // If there are flash errors or validation errors from server
        var hasServerError = jQuery('.alert-danger').length > 0 || jQuery('.has-error').length > 0;
        
        if (hasServerError) {
            // Check which tab has errors
            if (jQuery('#sissiniestro-descripcion').hasClass('has-error') || 
                jQuery('#sissiniestro-fecha_atencion').hasClass('has-error')) {
                window.activateTab('atencion');
            } else if (jQuery('#baremos-select').parent().find('.has-error').length > 0) {
                window.activateTab('servicios');
            }
        }
        
        // Also check for flash error messages that mention specific fields
        var flashError = jQuery('.alert-danger').text();
        if (flashError) {
            if (flashError.indexOf('Descripción') !== -1 || flashError.indexOf('fecha') !== -1) {
                window.activateTab('atencion');
            } else if (flashError.indexOf('servicio') !== -1 || flashError.indexOf('baremo') !== -1) {
                window.activateTab('servicios');
            }
        }
    });
    
})();

// ============================================================
// TAB CONTROLS - FORCE ACTIVE STYLES
// ============================================================
jQuery(document).ready(function() {
    
    function activateTab(tabId) {
        // Remove active from all tabs and panes
        jQuery('.nav-tabs .nav-link').removeClass('active');
        jQuery('.tab-pane').removeClass('active');
        
        // Hide all panes explicitly
        jQuery('.tab-pane').css('display', 'none');
        
        // Activate the selected tab and pane
        jQuery('.nav-tabs .nav-link[data-tab="' + tabId + '"]').addClass('active');
        jQuery('#tab-' + tabId).addClass('active');
        
        // Force show the active pane
        jQuery('#tab-' + tabId).css('display', 'block');
        
        // Apply active styles via jQuery directly (stronger than CSS)
        jQuery('#tab-' + tabId).css({
            'display': 'block !important',
            'background': '#f8faff !important',
            'border-left': '4px solid #0078d4 !important',
            'padding': '24px 24px 24px 28px !important'
        });
        
        // Store active tab in localStorage
        try {
            localStorage.setItem('activeFormTab', tabId);
        } catch (e) {
            // Ignore localStorage errors
        }
    }
    
    // Initialize tabs
    function initTabs() {
        // Check for saved tab preference
        var savedTab = null;
        try {
            savedTab = localStorage.getItem('activeFormTab');
        } catch (e) {
            // Ignore
        }
        
        var activeTab = savedTab || 'afiliado';
        
        // Make sure the tab exists
        if (jQuery('.nav-tabs .nav-link[data-tab="' + activeTab + '"]').length === 0) {
            activeTab = 'afiliado';
        }
        
        // Wait a tiny bit for DOM to be ready
        setTimeout(function() {
            activateTab(activeTab);
        }, 50);
    }
    
    // Click handler for tabs
    jQuery(document).on('click', '.nav-tabs .nav-link', function(e) {
        e.preventDefault();
        var tabId = jQuery(this).data('tab');
        if (tabId) {
            activateTab(tabId);
        }
    });
    
    // Initialize tabs
    initTabs();
    
    // Re-initialize after AJAX updates
    jQuery(document).on('ajaxComplete', function() {
        setTimeout(initTabs, 100);
    });
    
});

// ============================================================
// UPDATE SERVICIOS TAB BADGE COUNT
// ============================================================
function updateServiciosTabBadge() {
    var selectedCount = jQuery('#baremos-select').val() ? jQuery('#baremos-select').val().length : 0;
    var badge = jQuery('.nav-tabs .nav-link[data-tab="servicios"] .tab-badge-count');
    if (badge.length) {
        badge.text(selectedCount);
    }
}

// Update badge when Select2 changes
jQuery(document).on('change', '#baremos-select', function() {
    updateServiciosTabBadge();
});

// Also update when items are removed via Select2
jQuery(document).on('select2:unselect', '#baremos-select', function() {
    setTimeout(updateServiciosTabBadge, 100);
});

// Update when items are selected via Select2
jQuery(document).on('select2:select', '#baremos-select', function() {
    setTimeout(updateServiciosTabBadge, 100);
});

// Initial update after Select2 is initialized
setTimeout(updateServiciosTabBadge, 500);

JS;
$this->registerJs($jsCode, View::POS_END);
?>
<!-- ============================================================ -->
<!-- START HTML CONTENT -->
<!-- ============================================================ -->
<div class="sis-siniestro-form">
    <?php $form = ActiveForm::begin(['id' => 'sis-siniestro-form']); ?>

    <!-- Hidden input for error tab -->
    <?php
    $errorTab = isset($errorTab) ? $errorTab : null;
    if (Yii::$app->session->has('_errorTab')) {
        $errorTab = Yii::$app->session->get('_errorTab');
        Yii::$app->session->remove('_errorTab');
    }
    ?>
    <?= Html::hiddenInput('errorTab', $errorTab, ['id' => 'error-tab-input']) ?>

    <!-- ===== TABBED LAYOUT ===== -->
    <div class="tab-content-wrapper">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-tab="afiliado" href="#" role="tab">
                    <i class="fas fa-user-circle"></i> Afiliado
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-tab="historial" href="#" role="tab">
                    <i class="fas fa-history"></i> Historial
                    <span class="tab-badge-count"><?= count($baremosCitas) + count($baremosSiniestros) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-tab="preexistencias" href="#" role="tab">
                    <i class="fas fa-notes-medical"></i> Pre-existencias
                    <span class="tab-badge-count"><?= $preexistenciasCount ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-tab="atencion" href="#" role="tab">
                    <i class="fas fa-stethoscope"></i> <?= $terminoPrincipal ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-tab="servicios" href="#" role="tab">
                    <i class="fas fa-notes-medical"></i> Servicios Médicos
                    <span class="tab-badge-count"><?= count($selectedBaremos) ?></span>
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- ===== TAB 1: AFILIADO & PLAN ===== -->
            <div class="tab-pane active" id="tab-afiliado" role="tabpanel" style="display: block !important;">
                <div class="row">
                    <!-- Afiliado Card -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                                <i class="fas fa-user me-2"></i> Datos del Afiliado
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <p><strong>Nombre:</strong>
                                            <?= Html::encode(trim($afiliado->nombres . ' ' . $afiliado->apellidos)) ?>
                                        </p>
                                        <p><strong>Código:</strong> <?= Html::encode($afiliado->codigo ?? 'N/A') ?></p>
                                        <p><strong>Cédula:</strong>
                                            <?php
                                            $cedula = $afiliado->cedula;
                                            $tipoCedula = $afiliado->tipo_cedula ?? 'V';
                                            echo Html::encode($tipoCedula . '-' . str_pad($cedula, 8, '0', STR_PAD_LEFT));
                                            ?>
                                        </p>
                                        <p><strong>Teléfono:</strong> <?= Html::encode($afiliado->telefono) ?></p>
                                        <p><strong>Email:</strong> <?= Html::encode($afiliado->email) ?></p>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center justify-content-center">
                                        <?= Html::button(
                                            '<i class="fas fa-eye mr-2"></i> Ver Detalles',
                                            [
                                                'class' => 'btn btn-success',
                                                'id' => 'btn-abrir-afiliado-modal',
                                                'type' => 'button'
                                            ]
                                        ) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plan Info Card -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                                <i class="fas fa-file-contract me-2"></i> Información del Plan y Límites
                            </div>
                            <div class="card-body">
                                <div class="plan-info-summary">
                                    <div class="plan-info-item">
                                        <span class="plan-info-label">Plan:</span>
                                        <span class="plan-info-value"><?= $afiliado->plan->nombre ?? 'Sin plan' ?></span>
                                    </div>
                                    <div class="plan-info-item">
                                        <span class="plan-info-label">Cobertura del Plan:</span>
                                        <span class="plan-info-value">$<?= number_format($precioPlan, 2) ?></span>
                                    </div>
                                    <div class="plan-info-item">
                                        <span class="plan-info-label">Total Utilizado:</span>
                                        <span class="plan-info-value">$<?= number_format($sumatoriaSiniestros ?? 0, 2) ?></span>
                                    </div>
                                    <div class="plan-info-item plan-info-total">
                                        <span class="plan-info-label">Total Disponible:</span>
                                        <span class="plan-info-value">$<?= number_format($totalDisponible, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== TAB 2: HISTORIAL ===== -->
            <div class="tab-pane" id="tab-historial" role="tabpanel">
                <?php if ($esCita == 1 && !empty($baremosCitas)): ?>
                    <div class="card mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <i class="fas fa-calendar-check me-2"></i> Citas Realizadas
                            <span class="badge badge-success float-right"><?= count($baremosCitas) ?></span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-baremos">
                                    <thead>
                                        <tr>
                                            <th width="120">Fecha</th>
                                            <th>Área</th>
                                            <th>Servicio</th>
                                            <th>Descripción</th>
                                            <th width="100">Precio</th>
                                            <th width="100">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($baremosCitas as $cita): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($cita['fecha'])) ?></td>
                                                <td><strong><?= $cita['area'] ?></strong></td>
                                                <td><?= $cita['nombre_servicio'] ?></td>
                                                <td>
                                                    <?php if (!empty($cita['descripcion'])): ?>
                                                        <?= $cita['descripcion'] ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin descripción</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-success">
                                                    <strong>$<?= number_format($cita['precio'], 2) ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge badge-cita">Cita</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php elseif ($esCita == 1 && empty($baremosCitas)): ?>
                    <div class="card mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <i class="fas fa-calendar-check me-2"></i> Citas Realizadas
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info text-center mb-0">
                                <i class="fas fa-info-circle"></i> No se han realizado citas aún.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($esCita == 0 && !empty($baremosSiniestros)): ?>
                    <div class="card mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <i class="fas fa-file-medical me-2"></i> Atenciones Médicas Registradas
                            <span class="badge badge-info float-right"><?= count($baremosSiniestros) ?></span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-baremos">
                                    <thead>
                                        <tr>
                                            <th width="120">Fecha</th>
                                            <th>Área</th>
                                            <th>Servicio</th>
                                            <th>Descripción</th>
                                            <th width="100">Precio</th>
                                            <th width="100">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($baremosSiniestros as $siniestro): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($siniestro['fecha'])) ?></td>
                                                <td><strong><?= $siniestro['area'] ?></strong></td>
                                                <td><?= $siniestro['nombre_servicio'] ?></td>
                                                <td>
                                                    <?php if (!empty($siniestro['descripcion'])): ?>
                                                        <?= $siniestro['descripcion'] ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin descripción</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-success">
                                                    <strong>$<?= number_format($siniestro['precio'], 2) ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge badge-siniestro">Atención</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php elseif ($esCita == 0 && empty($baremosSiniestros)): ?>
                    <div class="card mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <i class="fas fa-file-medical me-2"></i> Atenciones Médicas Registradas
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info text-center mb-0">
                                <i class="fas fa-info-circle"></i> No se han registrado atenciones médicas aún.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <i class="fas fa-chart-bar me-2"></i> Resumen Estadístico
                    </div>
                    <div class="card-body">
                        <div class="stats-card">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="stats-number"><?= count($baremosCitas) + count($baremosSiniestros) ?></div>
                                    <div class="stats-label">Total Baremos Usados</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-number"><?= count($baremosCitas) ?></div>
                                    <div class="stats-label">Citas Realizadas</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-number"><?= count($baremosSiniestros) ?></div>
                                    <div class="stats-label">Atenciones Registradas</div>
                                </div>
                                <div class="col-md-3">
                                    <?php
                                    $totalUtilizado = array_sum(array_column(array_merge($baremosCitas, $baremosSiniestros), 'precio'));
                                    ?>
                                    <div class="stats-number">$<?= number_format($totalUtilizado, 2) ?></div>
                                    <div class="stats-label">Total Utilizado</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== TAB 3: PRE-EXISTENCIAS ===== -->
            <div class="tab-pane" id="tab-preexistencias" role="tabpanel">
                <div class="preexistencias-section">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                            <div>
                                <i class="fas fa-notes-medical me-2"></i> Pre-existencias del Afiliado
                                <span class="badge badge-warning ms-2"><?= $preexistenciasCount ?> registros</span>
                            </div>
                            <div class="header-actions">
                                <?php
                                // Build the return URL for the current page
                                $currentUrl = Yii::$app->request->getUrl();

                                // Create a URL with return_url parameter
                                $createUrl = Yii::$app->urlManager->createUrl([
                                    '/preexistencias/create',
                                    'user_id' => $afiliado->id,
                                    'return_url' => $currentUrl
                                ]);
                                ?>
                                <?= Html::a(
                                    '<i class="fas fa-plus"></i> Agregar Pre-existencia',
                                    $createUrl,
                                    [
                                        'class' => 'btn btn-sm btn-dark',
                                        'target' => '_blank',
                                    ]
                                ) ?>
                                <?php if ($preexistenciasCount > 0): ?>
                                    <?= Html::a(
                                        '<i class="fas fa-external-link-alt"></i> Ver todas',
                                        ['/preexistencias/index', 'user_id' => $afiliado->id],
                                        [
                                            'class' => 'btn btn-sm btn-outline-warning',
                                            'target' => '_blank',
                                        ]
                                    ) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if ($preexistenciasModels && count($preexistenciasModels) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover preexistencias-table">
                                        <thead>
                                            <tr>
                                                <th width="20%" style="color: white !important; background: #2a5298 !important;">Nombre</th>
                                                <th width="25%" style="color: white !important; background: #2a5298 !important;">Descripción</th>
                                                <th width="13%" style="color: white !important; background: #2a5298 !important;">Fecha Diagnóstico</th>
                                                <th width="15%" style="color: white !important; background: #2a5298 !important;">Médico Tratante</th>
                                                <th width="10%" style="color: white !important; background: #2a5298 !important;">Estatus</th>
                                                <th width="15%" style="color: white !important; background: #2a5298 !important;">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($preexistenciasModels as $preexistencia): ?>
                                                <tr>
                                                    <td><strong><?= Html::encode($preexistencia->nombre) ?></strong></td>
                                                    <td><?= Html::encode($preexistencia->descripcion ?: 'Sin descripción') ?></td>
                                                    <td><?= $preexistencia->fecha_diagnostico ? Yii::$app->formatter->asDate($preexistencia->fecha_diagnostico) : 'No especificada' ?></td>
                                                    <td><?= Html::encode($preexistencia->medico_tratante ?: 'No especificado') ?></td>
                                                    <td class="text-center">
                                                        <?php
                                                        $badgeClass = 'badge-status-';
                                                        switch ($preexistencia->estatus) {
                                                            case 'Activo':
                                                                $badgeClass .= 'activo';
                                                                break;
                                                            case 'Inactivo':
                                                                $badgeClass .= 'inactivo';
                                                                break;
                                                            case 'En Tratamiento':
                                                                $badgeClass .= 'tratamiento';
                                                                break;
                                                            case 'En Remisión':
                                                                $badgeClass .= 'remision';
                                                                break;
                                                            default:
                                                                $badgeClass .= 'activo';
                                                        }
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>" style="font-size: 11px; padding: 3px 8px;">
                                                            <?= Html::encode($preexistencia->estatus ?: 'Activo') ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center action-buttons">
                                                        <?= Html::a(
                                                            '<i class="fas fa-eye"></i>',
                                                            ['/preexistencias/view', 'id' => $preexistencia->id],
                                                            [
                                                                'class' => 'btn-action btn-action-view',
                                                                'title' => 'Ver detalles',
                                                                'target' => '_blank',
                                                            ]
                                                        ) ?>
                                                        <?= Html::a(
                                                            '<i class="fas fa-edit"></i>',
                                                            ['/preexistencias/update', 'id' => $preexistencia->id, 'return_url' => $currentUrl],
                                                            [
                                                                'class' => 'btn-action btn-action-edit',
                                                                'title' => 'Editar',
                                                                'target' => '_blank',
                                                            ]
                                                        ) ?>
                                                        <?= Html::a(
                                                            '<i class="fas fa-trash"></i>',
                                                            ['/preexistencias/delete', 'id' => $preexistencia->id, 'return_url' => $currentUrl],
                                                            [
                                                                'class' => 'btn-action btn-action-delete',
                                                                'title' => 'Eliminar',
                                                                'data-method' => 'post',
                                                                'data-confirm' => '¿Está seguro de eliminar esta pre-existencia? Esta acción no se puede deshacer.',
                                                                'target' => '_blank',
                                                            ]
                                                        ) ?>
                                                        <?= Html::a(
                                                            '<i class="fas fa-sync-alt"></i>',
                                                            ['/preexistencias/toggle-status', 'id' => $preexistencia->id, 'return_url' => $currentUrl],
                                                            [
                                                                'class' => 'btn-action btn-action-toggle',
                                                                'title' => 'Cambiar estatus',
                                                                'data-method' => 'post',
                                                                'data-confirm' => '¿Está seguro de cambiar el estatus de esta pre-existencia?',
                                                                'target' => '_blank',
                                                            ]
                                                        ) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if ($preexistenciasCount > 10): ?>
                                    <div class="text-center mt-3">
                                        <?= Html::a(
                                            '<i class="fas fa-external-link-alt"></i> Ver todas las pre-existencias (' . $preexistenciasCount . ')',
                                            ['/preexistencias/index', 'user_id' => $afiliado->id],
                                            [
                                                'class' => 'btn btn-outline-primary',
                                                'target' => '_blank',
                                            ]
                                        ) ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info text-center mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No se encontraron pre-existencias registradas para este afiliado.
                                    <br>
                                    <?= Html::a(
                                        '<i class="fas fa-plus"></i> Agregar Primera Pre-existencia',
                                        $createUrl,
                                        [
                                            'class' => 'btn btn-primary mt-3',
                                            'target' => '_blank',
                                        ]
                                    ) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== TAB 4: DATOS DE LA ATENCIÓN ===== -->
            <div class="tab-pane" id="tab-atencion" role="tabpanel">
                <div style="display: none;">
                    <?= $form->field($model, 'idclinica')->textInput(['value' => $afiliado->clinica_id]) ?>
                </div>

                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <i class="fas fa-stethoscope me-2"></i> <?= $esCitaMode ? 'Detalles de la Cita' : 'Detalles de la Atención' ?>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 field-with-icon">
                                <i class="fas fa-calendar-check"></i>
                                <?= $form->field($model, 'fecha_atencion')->textInput([
                                    'type' => 'date',
                                    'class' => 'form-control form-control-lg',
                                    'placeholder' => 'Seleccione la fecha',
                                    'autocomplete' => 'off',
                                    'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha_atencion, 'yyyy-MM-dd')
                                ])->label('Fecha de la ' . $terminoPrincipal) ?>
                            </div>

                            <div class="col-md-6 field-with-icon">
                                <i class="fas fa-clock"></i>
                                <?= $form->field($model, 'hora_atencion')->textInput([
                                    'type' => 'text',
                                    'class' => 'form-control form-control-lg time-input',
                                    'placeholder' => 'HH:MM (ejemplo: 14:30)',
                                    'maxlength' => 5,
                                    'autocomplete' => 'off'
                                ])->label('Hora de la ' . $terminoPrincipal) ?>
                                <small class="form-text text-muted">
                                    <i class="fas fa-clock"></i> Use formato 24 horas (HH:MM). Ejemplo: 14:30 o 09:15
                                </small>
                            </div>

                            <div class="col-md-12 field-with-icon">
                                <i class="fas fa-align-left"></i>
                                <?= $form->field($model, 'descripcion')->textarea([
                                    'rows' => 3,
                                    'class' => 'form-control form-control-lg',
                                    'placeholder' => 'Describa los detalles de la ' . strtolower($terminoPrincipal) . '...'
                                ])->label('Descripción de la ' . $terminoPrincipal) ?>
                            </div>

                            <!-- ===== DOCTOR AND ADMISSION ANALYST FIELDS - SAME ROW ===== -->
                            <div class="row">
                                <div class="col-md-6 field-with-icon">
                                    <i class="fas fa-user-md"></i>
                                    <?= $form->field($model, 'nombre_doctor')->textInput([
                                        'class' => 'form-control form-control-lg',
                                        'placeholder' => 'Médico Tratante',
                                        'maxlength' => true,
                                        'autocomplete' => 'off'
                                    ])->label('Nombre del Doctor') ?>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-user-md"></i> Médico que atendió al paciente
                                    </small>
                                </div>

                                <div class="col-md-6 field-with-icon">
                                    <i class="fas fa-user-tie"></i>
                                    <?= $form->field($model, 'admission_analyst')->textInput([
                                        'class' => 'form-control form-control-lg',
                                        'placeholder' => $esCitaMode ? 'Nombre del analista de citas' : 'Nombre del analista de admisión',
                                        'maxlength' => true,
                                        'autocomplete' => 'off'
                                    ])->label($esCitaMode ? 'Analista de Citas' : 'Analista de Admisión') ?>
                                    <small class="form-text text-muted" style="white-space: nowrap; overflow: visible;">
                                        <i class="fas fa-user-tie"></i> <?= $esCitaMode ? 'Persona responsable del registro de esta cita' : 'Persona responsable del registro de esta ' . strtolower($terminoPrincipal) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== SUBSECTION: DOCUMENTACIÓN ADJUNTA ===== -->
                <div class="card mb-4">
                    <div class="card-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <i class="fas fa-paperclip me-2"></i> Documentación Adjunta
                    </div>
                    <div class="card-body">
                        <p class="hint-block mb-4">
                            <i class="fas fa-info-circle"></i> Adjunte los documentos relacionados con esta <?= strtolower($terminoPrincipal) ?>
                        </p>

                        <div class="row">
                            <!-- Récipe Médico -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-file-prescription text-primary"></i> Récipe Médico
                                    </label>
                                    <?= $form->field($model, 'imagenRecipeFile')->fileInput([
                                        'accept' => 'image/*,application/pdf',
                                        'class' => 'form-control-file',
                                    ])->label(false) ?>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Formatos permitidos: JPG, JPEG, PNG, PDF (máx. 10MB)
                                    </small>
                                    <?php if ($model->imagen_recipe && !$model->isNewRecord): ?>
                                        <div class="mt-2">
                                            <a href="<?= $model->imagen_recipe ?>" target="_blank" class="btn btn-sm" style="background-color: #6f42c1; color: #fff;">
                                                <i class="fas fa-eye"></i> Ver archivo actual
                                            </a>
                                            <span class="text-muted ml-2">
                                                <i class="fas fa-check-circle text-success"></i> Archivo guardado
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Informe Médico -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-file-medical text-info"></i> Informe Médico
                                    </label>
                                    <?= $form->field($model, 'imagenInformeFile')->fileInput([
                                        'accept' => 'image/*,application/pdf',
                                        'class' => 'form-control-file',
                                    ])->label(false) ?>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Formatos permitidos: JPG, JPEG, PNG, PDF (máx. 10MB)
                                    </small>
                                    <?php if ($model->imagen_informe && !$model->isNewRecord): ?>
                                        <div class="mt-2">
                                            <a href="<?= $model->imagen_informe ?>" target="_blank" class="btn btn-sm" style="background-color: #6f42c1; color: #fff;">
                                                <i class="fas fa-eye"></i> Ver archivo actual
                                            </a>
                                            <span class="text-muted ml-2">
                                                <i class="fas fa-check-circle text-success"></i> Archivo guardado
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Additional info about existing files -->
                        <?php if (($model->imagen_recipe || $model->imagen_informe) && !$model->isNewRecord): ?>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-info-circle"></i>
                                <strong>Nota:</strong> Si selecciona nuevos archivos, reemplazarán los existentes.
                                Deje el campo vacío para mantener los archivos actuales.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?= $this->render('_form_documentos_adicionales', ['model' => $model, 'form' => $form]) ?>
            </div>

            <!-- ===== TAB 5: SELECCIÓN DE SERVICIOS MÉDICOS ===== -->
            <div class="tab-pane" id="tab-servicios" role="tabpanel">
                <!-- Badges -->
                <div class="section-badge-white d-flex flex-wrap gap-2 align-items-center mb-3">
                    <?php if ($esCitaMode && $emergencyExcluded): ?>
                        <span class="badge badge-pill stat-badge emergency-excluded-gray"
                            style="background: #6c757d !important; color: #ffffff !important;"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="Los servicios de EMERGENCIA no están disponibles para citas programadas. <?= $emergencyCount ?> servicio(s) excluido(s).">
                            <i class="fas fa-ambulance me-3" style="color: #ffffff !important;"></i>
                            <span style="color: #ffffff !important;">Emergencia Excluidos (<?= $emergencyCount ?>)</span>
                        </span>
                    <?php endif; ?>

                    <span class="badge badge-pill stat-badge disponible clickable-badge"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Servicios que cumplen todos los criterios y pueden ser seleccionados ahora mismo. Click para ver detalles.">
                        <i class="fas fa-check-circle me-3"></i>
                        <?php
                        $totalDisponibles = count($baremosSinPlazo);
                        echo $totalDisponibles;
                        ?> Disponibles
                    </span>

                    <?php if (count($baremosPendientesPlazo) > 0): ?>
                        <span class="badge badge-pill stat-badge restringido clickable-badge"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="Servicios con plazo de espera pendiente. No disponibles para selección. Click para ver detalles.">
                            <i class="fas fa-clock me-3"></i>
                            <span><?= count($baremosPendientesPlazo) ?> Restringidos</span>
                        </span>
                    <?php endif; ?>

                    <?php
                    $totalAgotados = count($baremosAgotados);
                    if ($totalAgotados > 0):
                    ?>
                        <span class="badge badge-pill stat-badge agotado clickable-badge"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="Servicios que han alcanzado su límite máximo de usos. No disponibles para selección. Click para ver detalles.">
                            <i class="fas fa-ban me-3"></i>
                            <span><?= $totalAgotados ?> Agotados</span>
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($baremosForzados)): ?>
                        <span class="badge badge-pill stat-badge historico"
                            data-toggle="tooltip"
                            data-placement="top"
                            title="Servicios previamente guardados que ya no cumplen criterios actuales, mostrados solo para referencia histórica."
                            style="color: #ffffff !important; background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;">
                            >
                            <i class="fas fa-history me-3"></i>
                            <span style="color: #ffffff !important;"><?= count($baremosForzados) ?> Históricos</span>
                        </span>
                        </span>
                    <?php endif; ?>

                    <span class="badge badge-pill stat-badge total"
                        data-toggle="tooltip"
                        data-placement="top"
                        title="Cantidad total de servicios médicos incluidos en el plan, independientemente de su disponibilidad.">
                        <i class="fas fa-layer-group me-3"></i>
                        <span>
                            <?php
                            $totalGeneral = count($baremosForzados) + $totalAgotados + count($baremosPendientesPlazo) + count($baremosSinPlazo);
                            echo $totalGeneral;
                            ?> Total
                        </span>
                    </span>
                </div>

                <?php if ($contrato && $contrato->estatus === 'Activo'): ?>
                    <div class="baremo-combined-container">
                        <div class="field-with-icon baremo-select-container">
                            <?php
                            echo $form->field($model, 'idbaremo[]', [
                                'options' => ['class' => 'm-0'],
                                'template' => "{input}"
                            ])->widget(Select2::class, [
                                'data' => $baremosTotales,
                                'options' => [
                                    'multiple' => true,
                                    'value' => $selectedBaremos,
                                    'placeholder' => 'Busca o selecciona los servicios del Baremo...',
                                    'class' => 'form-control form-control-lg baremo-master-select',
                                    'id' => 'baremos-select',
                                    'style' => 'padding-left: 0px;'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'closeOnSelect' => false,
                                    'tags' => false,
                                    'tokenSeparators' => [',', ' '],
                                    'minimumInputLength' => 0,
                                ],
                            ])->label(false)->hint(false);
                            ?>
                        </div>

                        <div id="baremos-tabla-container" class="summary-container mt-4" style="display: none;">
                            <div class="summary-header">
                                <div class="d-flex align-items-center">
                                    <div class="summary-icon">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-1 text-dark">Resumen de servicios seleccionados</h4>
                                        <p class="text-muted mb-0">Detalle de costos y restricciones</p>
                                    </div>
                                </div>
                                <div class="summary-total">
                                    <span class="total-label">Total:</span>
                                    <span class="total-amount" id="summary-total-amount">$0.00</span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-summary">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="25%" class="text-start">Servicio</th>
                                            <th width="20%" class="text-start">Área</th>
                                            <th width="30%" class="text-start">Descripción</th>
                                            <th width="15%" class="text-center">Restricciones</th>
                                            <th width="10%" class="text-end">Costo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="baremos-tabla-body"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="costo-total-container mt-4" id="costo-total-container" style="display: none;">
                            <div class="total-card">
                                <div class="total-content">
                                    <div class="total-icon">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                    <div class="total-details">
                                        <div class="total-label">Total estimado</div>
                                        <div class="total-value" id="costo-total-value">$0.00</div>
                                        <div class="total-hint">Esta cantidad será descontada de la cobertura disponible</div>
                                    </div>
                                </div>
                                <div class="total-actions">
                                    <?= $form->field($model, 'costo_total')->hiddenInput(['id' => 'costo-total-input'])->label(false) ?>
                                    <button type="button" class="btn btn-primary" id="recalculate-total">
                                        <i class="fas fa-redo me-1"></i> Recalcular
                                    </button>
                                </div>
                            </div>
                            <div id="cobertura-warning" class="coverage-warning mt-3" style="display: none;">
                                <div class="warning-content">
                                    <div class="warning-icon">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="warning-text">
                                        <strong>¡Advertencia!</strong> El costo total estimado supera la cobertura disponible del afiliado.
                                        <span id="cobertura-difference-text"></span>
                                    </div>
                                </div>
                            </div>
                            <?php if ($esCitaMode): ?>
                                <div id="cita-warning" class="info-message mt-3">
                                    <div class="info-content">
                                        <div class="info-icon">
                                            <i class="fas fa-info-circle"></i>
                                        </div>
                                        <div class="info-text">
                                            <strong>Registro como Cita:</strong> Los servicios con <strong>Plazo de Espera Pendiente</strong> no estarán disponibles para selección.
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div id="contrato-error-message" class="alert alert-warning alert-dismissible fade show" style="margin-top: 1.5rem;">
                        <div class="d-flex">
                            <div class="alert-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="alert-content">
                                <h5 class="alert-heading">Contrato no activo</h5>
                                <p class="mb-0">El contrato del afiliado no está activado. Por favor, active el contrato para poder seleccionar servicios médicos.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- ===== END TABBED LAYOUT ===== -->

    <!-- ===== BOTTOM ACTIONS BAR ===== -->
    <div class="row">
        <div class="col-12 text-center">
            <div class="form-actions-container">
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success']) ?>
                <?= Html::a('<i class="fas fa-undo"></i> Limpiar', ['create'], ['class' => 'btn btn-outline-dark']) ?>
                <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['index'], ['class' => 'btn btn-warning']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<!-- ===== PRE-EXISTENCIAS MODAL (ONLY ONE - AT THE BOTTOM) ===== -->
<div class="modal fade preexistencia-modal" id="preexistencia-modal" tabindex="-1" role="dialog" aria-labelledby="preexistencia-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                <h5 class="modal-title" id="preexistencia-modal-label">
                    <i class="fas fa-notes-medical me-2"></i> Nueva Pre-existencia
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php $formPreexistencia = ActiveForm::begin([
                'id' => 'preexistencia-form',
                'action' => Yii::$app->urlManager->createUrl(['preexistencias/create']),
                'method' => 'POST',
                'options' => ['class' => 'preexistencia-form']
            ]); ?>
            <div class="modal-body">
                <?= Html::hiddenInput('Preexistencias[id]', '', ['id' => 'preexistencia-id']) ?>
                <?= Html::hiddenInput('Preexistencias[user_id]', $afiliado->id, ['id' => 'preexistencia-user_id']) ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Nombre de la Pre-existencia <span class="text-danger">*</span></label>
                            <?= Html::textInput('Preexistencias[nombre]', '', [
                                'id' => 'preexistencia-nombre',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Ingrese el nombre de la pre-existencia',
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Descripción</label>
                            <?= Html::textarea('Preexistencias[descripcion]', '', [
                                'id' => 'preexistencia-descripcion',
                                'class' => 'form-control',
                                'rows' => 3,
                                'placeholder' => 'Describa los detalles de la pre-existencia'
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Fecha de Diagnóstico</label>
                            <?= Html::input('date', 'Preexistencias[fecha_diagnostico]', '', [
                                'id' => 'preexistencia-fecha_diagnostico',
                                'class' => 'form-control form-control-lg'
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Médico Tratante</label>
                            <?= Html::textInput('Preexistencias[medico_tratante]', '', [
                                'id' => 'preexistencia-medico_tratante',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Nombre del médico tratante'
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Medicamentos</label>
                            <?= Html::textarea('Preexistencias[medicamentos]', '', [
                                'id' => 'preexistencia-medicamentos',
                                'class' => 'form-control',
                                'rows' => 2,
                                'placeholder' => 'Medicamentos recetados (si aplica)'
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Observaciones</label>
                            <?= Html::textarea('Preexistencias[observaciones]', '', [
                                'id' => 'preexistencia-observaciones',
                                'class' => 'form-control',
                                'rows' => 2,
                                'placeholder' => 'Observaciones adicionales'
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Estatus <span class="text-danger">*</span></label>
                            <?= Html::dropDownList('Preexistencias[estatus]', 'Activo', $statusOptions, [
                                'id' => 'preexistencia-estatus',
                                'class' => 'form-control form-control-lg'
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-weight-bold">Siniestro ID</label>
                            <?= Html::textInput('Preexistencias[sis_siniestro_id]', $model->id ?: '', [
                                'class' => 'form-control form-control-lg',
                                'readonly' => true,
                                'placeholder' => 'Se asignará automáticamente'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Pre-existencia
                </button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
Modal::begin([
    'title' => '<h4>Detalles del Afiliado <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></h4>',
    'id' => 'afiliado-modal',
    'size' => Modal::SIZE_LARGE,
    'options' => ['tabindex' => false, 'class' => 'fade', 'role' => 'dialog'],
    'dialogOptions' => ['class' => 'modal-dialog-centered'],
]);
echo $this->render('/user-datos/view', ['model' => $afiliado]);
Modal::end();
?>

<?php
$this->registerJs(<<<'JS'
// ============================================
// AFILIADO MODAL TRIGGER
// ============================================
jQuery('#btn-abrir-afiliado-modal').on('click', function(e) {
    e.preventDefault();
    setTimeout(function() {
        jQuery('#afiliado-modal').modal('show');
    }, 50);
});

// ============================================
// TIME INPUT FORMATTING AND VALIDATION
// ============================================

// Validate time format (HH:MM with 00-23:00-59)
function validateTimeFormat(timeStr) {
    if (!timeStr || timeStr === '') return true;
    return /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(timeStr);
}

// Convert any time format to 24-hour HH:MM
function convertTo24Hour(timeStr) {
    if (!timeStr) return '';
    
    // Already in 24-hour format (HH:MM)
    if (/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(timeStr)) {
        return timeStr;
    }
    
    // Handle 12-hour format with AM/PM
    var match = timeStr.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (match) {
        var hour = parseInt(match[1]);
        var minute = match[2];
        var ampm = match[3].toUpperCase();
        
        if (ampm === 'PM' && hour !== 12) {
            hour += 12;
        } else if (ampm === 'AM' && hour === 12) {
            hour = 0;
        }
        
        return hour.toString().padStart(2, '0') + ':' + minute;
    }
    
    // Handle format with seconds (HH:MM:SS)
    match = timeStr.match(/^(\d{1,2}):(\d{2}):\d{2}$/);
    if (match) {
        return match[1].padStart(2, '0') + ':' + match[2];
    }
    
    return timeStr;
}

// Auto-format time when user leaves the field (on blur)
function autoFormatTime(input) {
    var value = input.value;
    if (!value) return;
    
    // Remove any non-digit characters for processing
    var digits = value.replace(/[^0-9]/g, '');
    
    if (digits.length >= 3) {
        var hour = digits.slice(0, 2);
        var minute = digits.slice(2, 4);
        
        // Validate ranges
        var hourInt = parseInt(hour);
        var minuteInt = parseInt(minute);
        
        if (hourInt > 23) hour = '23';
        if (hourInt < 0 || isNaN(hourInt)) hour = '00';
        if (minuteInt > 59) minute = '59';
        if (minuteInt < 0 || isNaN(minuteInt)) minute = '00';
        
        input.value = hour + ':' + minute;
    } else if (digits.length === 2) {
        input.value = digits;
    }
}

// Initialize time inputs when document is ready
jQuery(document).ready(function() {
    // Convert existing values to 24-hour format on page load
    jQuery('.time-input').each(function() {
        var converted = convertTo24Hour(jQuery(this).val());
        if (converted !== jQuery(this).val()) {
            jQuery(this).val(converted);
        }
    });
    
    // Auto-format when user leaves the field (NOT while typing)
    jQuery('.time-input').on('blur', function() {
        autoFormatTime(this);
        
        var $this = jQuery(this);
        var isValid = validateTimeFormat($this.val());
        
        if (!isValid && $this.val() !== '') {
            $this.addClass('is-invalid');
            if ($this.next('.invalid-feedback').length === 0) {
                $this.after('<div class="invalid-feedback">Formato inválido. Use HH:MM en formato 24 horas (ejemplo: 14:30)</div>');
            }
        } else {
            $this.removeClass('is-invalid');
            $this.next('.invalid-feedback').remove();
        }
    });
    
    // Allow normal typing - only validate, don't reformat
    jQuery('.time-input').on('input', function() {
        // Remove the invalid class while user is typing
        jQuery(this).removeClass('is-invalid');
        jQuery(this).next('.invalid-feedback').remove();
    });
    
    // Convert to 24-hour format before form submission
    jQuery('form').on('beforeSubmit', function() {
        var isValid = true;
        
        jQuery('.time-input').each(function() {
            var $thisField = jQuery(this);
            
            // Auto-format before validation
            autoFormatTime(this);
            
            var converted = convertTo24Hour($thisField.val());
            if (converted !== $thisField.val()) {
                $thisField.val(converted);
            }
            
            if (!validateTimeFormat($thisField.val()) && $thisField.val() !== '') {
                $thisField.addClass('is-invalid');
                isValid = false;
            }
        });
        
        if (!isValid) {
            if (jQuery('#time-validation-error').length === 0) {
                jQuery('.sis-siniestro-form').prepend('<div id="time-validation-error" class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-circle"></i> <strong>Error de validación:</strong> Por favor, corrija los errores en los campos de hora antes de guardar.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
            }
            return false;
        }
        
        jQuery('#time-validation-error').remove();
        return true;
    });
});

// ============================================
// END TIME INPUT FORMATTING AND VALIDATION
// ============================================

// ============================================
// CUSTOM FILE INPUT - Show selected file name
// ============================================
jQuery('#recipe-file-input, #informe-file-input').on('change', function() {
    var fileName = jQuery(this).val().split('\\').pop();
    var labelId = jQuery(this).attr('id') === 'recipe-file-input' ? '#recipe-file-label' : '#informe-file-label';
    
    if (fileName) {
        jQuery(labelId).html('<i class="fas fa-file"></i> ' + fileName);
        jQuery(labelId).addClass('selected');
    } else {
        jQuery(labelId).html('<i class="fas fa-upload"></i> Seleccionar archivo...');
        jQuery(labelId).removeClass('selected');
    }
});
// ============================================================
// CHECK FOR SERVER-SIDE ERROR TAB
// ============================================================
jQuery(document).ready(function() {
    var errorTab = jQuery('#error-tab-input').val();
    if (errorTab && errorTab !== '') {
        // Switch to the tab with errors
        setTimeout(function() {
            window.activateTab(errorTab);
        }, 300);
    }
});
JS, View::POS_END);

// ===== THE REST OF THE JAVASCRIPT FOR BAREMOS =====
if (isset($baremosTotales) && isset($baremosHtml) && isset($baremosInfo) && isset($baremosRestringidosIDs)) {
    $baremosTotalesJson = json_encode($baremosTotales);
    $baremosHtmlJson = json_encode($baremosHtml);
    $baremosInfoJson = json_encode($baremosInfo);
    $baremosRestringidosJson = json_encode($baremosRestringidosIDs);
    $baremosPendientesPlazoJson = json_encode($baremosPendientesPlazo ?? []);
    $baremosAgotadosJson = json_encode($baremosAgotados ?? []);
    $baremosDisponiblesInfoJson = json_encode($baremosDisponiblesInfo ?? []);
    $baremosPendientesInfoJson = json_encode(array_intersect_key($baremosInfo, $baremosPendientesPlazo));
    $baremosAgotadosInfoJson = json_encode(array_intersect_key($baremosInfo, $baremosAgotados));

    $jsCode = <<<JS
(function() {
    'use strict';
    
    const baremosTotales = {$baremosTotalesJson};
    const baremosHtml = {$baremosHtmlJson};
    const baremosInfo = {$baremosInfoJson};
    const baremosRestringidosIDs = {$baremosRestringidosJson};
    const baremosPendientesPlazo = {$baremosPendientesPlazoJson};
    const baremosAgotados = {$baremosAgotadosJson};
    const baremosDisponiblesInfo = {$baremosDisponiblesInfoJson};
    const baremosPendientesInfo = {$baremosPendientesInfoJson};
    const baremosAgotadosInfo = {$baremosAgotadosInfoJson};
    
    const baremosSelect = jQuery('#baremos-select');
    const form = baremosSelect.closest('form');
    const citaWarning = jQuery('#cita-warning');
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Add CSS styles for modals (only once)
    function addModalStyles() {
        if (jQuery('#servicios-modal-styles').length) return;
        var styles = '<style id="servicios-modal-styles">' +
            '.servicios-modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(3px);display:flex;justify-content:center;align-items:center;z-index:10000;animation:fadeIn 0.2s ease;}' +
            '.servicios-modal{background:white;border-radius:12px;width:90%;max-width:1100px;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 20px 40px rgba(0,0,0,0.3);animation:slideIn 0.3s ease;}' +
            '.servicios-modal-header{display:flex;align-items:center;padding:20px 24px;background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);color:white;border-radius:12px 12px 0 0;gap:16px;}' +
            '.servicios-modal-header.header-warning{background:linear-gradient(135deg,#f0ad4e 0%,#ec971f 100%);}' +
            '.servicios-modal-header.header-danger{background:linear-gradient(135deg,#d9534f 0%,#c9302c 100%);}' +
            '.servicios-modal-header-icon{width:48px;height:48px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;}' +
            '.servicios-modal-header-title h3{margin:0;font-size:20px;font-weight:600;}' +
            '.servicios-modal-header-title p{margin:4px 0 0;font-size:13px;opacity:0.85;}' +
            '.servicios-modal-close{margin-left:auto;background:rgba(255,255,255,0.2);border:none;color:white;width:32px;height:32px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;}' +
            '.servicios-modal-close:hover{background:rgba(255,255,255,0.3);transform:scale(1.05);}' +
            '.servicios-modal-body{flex:1;overflow-y:auto;padding:20px 24px;}' +
            '.servicios-info-banner{background:#e8f4fd;border-left:4px solid #1e3c72;padding:12px 16px;border-radius:8px;margin-bottom:16px;display:flex;align-items:flex-start;gap:12px;}' +
            '.servicios-info-banner i{color:#1e3c72;font-size:18px;margin-top:2px;}' +
            '.servicios-info-banner .info-text{font-size:13px;color:#2c3e50;line-height:1.4;}' +
            '.servicios-info-banner.info-warning{background:#fff8e7;border-left-color:#f0ad4e;}' +
            '.servicios-info-banner.info-warning i{color:#f0ad4e;}' +
            '.servicios-info-banner.info-danger{background:#fdf0ef;border-left-color:#d9534f;}' +
            '.servicios-info-banner.info-danger i{color:#d9534f;}' +
            '.servicios-selection-info{background:#e8f5e9;border-radius:8px;padding:10px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:13px;color:#2e7d32;}' +
            '.servicios-toolbar{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #e9ecef;margin-bottom:16px;}' +
            '.toolbar-actions{display:flex;gap:8px;}' +
            '.btn-toolbar{padding:6px 12px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;font-size:12px;cursor:pointer;transition:all 0.2s;}' +
            '.btn-toolbar:hover{background:#e9ecef;}' +
            '.toolbar-stats{font-size:13px;color:#6c757d;font-weight:500;}' +
            '.servicios-table-container{overflow-x:auto;border-radius:8px;border:1px solid #e9ecef;}' +
            '.servicios-table{width:100%;border-collapse:collapse;font-size:13px;}' +
            '.servicios-table thead th{background:#f8f9fa;padding:12px;text-align:left;font-weight:600;color:#495057;border-bottom:2px solid #dee2e6;}' +
            '.servicios-table tbody tr{border-bottom:1px solid #f0f0f0;transition:background 0.2s;cursor:pointer;}' +
            '.servicios-table tbody tr:hover{background:#f8f9fa;}' +
            '.servicios-table tbody td{padding:12px;vertical-align:middle;}' +
            '.checkbox-wrapper{position:relative;display:inline-block;}' +
            '.checkbox-wrapper input[type="checkbox"]{position:absolute;opacity:0;cursor:pointer;}' +
            '.checkbox-wrapper label{display:inline-block;width:18px;height:18px;border:2px solid #cbd5e0;border-radius:4px;background:white;cursor:pointer;transition:all 0.2s;}' +
            '.checkbox-wrapper input[type="checkbox"]:checked + label{background:#1e3c72;border-color:#1e3c72;}' +
            '.checkbox-wrapper input[type="checkbox"]:checked + label::after{content:"✓";display:block;color:white;font-size:12px;line-height:14px;text-align:center;}' +
            '.area-badge{background:#e8f4fd;color:#1e3c72;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:500;display:inline-block;}' +
            '.disponibilidad-badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;}' +
            '.disponibilidad-badge.disponible{background:#e8f5e9;color:#2e7d32;}' +
            '.servicio-nombre{font-weight:500;color:#2c3e50;}' +
            '.servicio-precio{font-weight:600;color:#28a745;text-align:right;}' +
            '.detail-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;font-size:12px;}' +
            '.detail-badge.detail-warning{background:#fff8e7;color:#856404;}' +
            '.usage-progress{width:100%;}' +
            '.usage-stats{text-align:center;margin-bottom:4px;font-size:12px;}' +
            '.usage-used{font-weight:700;color:#dc3545;}' +
            '.progress-bar-container{background:#e9ecef;border-radius:4px;height:4px;overflow:hidden;}' +
            '.progress-bar-fill{height:100%;border-radius:4px;transition:width 0.3s;}' +
            '.servicios-modal-footer{padding:16px 24px;border-top:1px solid #e9ecef;display:flex;justify-content:space-between;align-items:center;background:#fafbfc;border-radius:0 0 12px 12px;}' +
            '.footer-summary{display:flex;gap:24px;}' +
            '.summary-item{display:flex;align-items:baseline;gap:6px;}' +
            '.summary-label{font-size:12px;color:#6c757d;}' +
            '.summary-value{font-size:18px;font-weight:700;color:#2c3e50;}' +
            '.summary-price{color:#28a745;}' +
            '.footer-actions{display:flex;gap:12px;}' +
            '.btn-modal{padding:8px 20px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.2s;border:none;}' +
            '.btn-modal-primary{background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);color:white;}' +
            '.btn-modal-primary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(30,60,114,0.3);}' +
            '.btn-modal-secondary{background:#f8f9fa;border:1px solid #dee2e6;color:#495057;}' +
            '.btn-modal-secondary:hover{background:#e9ecef;}' +
            '@keyframes fadeIn{from{opacity:0;}to{opacity:1;}}' +
            '@keyframes slideIn{from{opacity:0;transform:translateY(-20px);}to{opacity:1;transform:translateY(0);}}' +
            '@media (max-width:768px){.servicios-modal{width:95%;max-height:90vh;}.servicios-modal-header{padding:16px;}.servicios-modal-body{padding:16px;}.footer-summary{flex-direction:column;gap:8px;}.summary-value{font-size:14px;}.servicios-table thead th{font-size:11px;padding:8px;}.servicios-table tbody td{padding:8px;font-size:11px;}}' +
        '</style>';
        jQuery('head').append(styles);
    }
    
    // Professional Modal for Available Services
    function showDisponiblesModal() {
        var currentlySelected = baremosSelect.val() || [];
        var items = {};
        Object.keys(baremosDisponiblesInfo).forEach(function(baremoId) {
            if (!currentlySelected.includes(baremoId.toString())) {
                items[baremoId] = baremosDisponiblesInfo[baremoId];
            }
        });
        var count = Object.keys(items).length;
        
        if (count === 0) {
            if (typeof toastr !== 'undefined') {
                toastr.info('No hay servicios disponibles adicionales. Todos los servicios ya han sido seleccionados.');
            } else {
                alert('No hay servicios disponibles adicionales. Todos los servicios ya han sido seleccionados.');
            }
            return;
        }
        
        var totalAvailablePrice = 0;
        Object.keys(items).forEach(function(baremoId) {
            totalAvailablePrice += parseFloat(items[baremoId].precio || 0);
        });
        
        var modalHtml = '<div class="servicios-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(3px); display: flex; justify-content: center; align-items: center; z-index: 10000;">' +
    '<div class="servicios-modal servicios-disponibles-modal" style="background: white; border-radius: 12px; width: 90%; max-width: 1100px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);">' +
        '<div class="servicios-modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 20px 24px; border-radius: 12px 12px 0 0; display: flex; align-items: center; gap: 16px; color: white;">' +
            '<div class="servicios-modal-header-icon" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="fas fa-check-circle" style="color: white; font-size: 24px;"></i></div>' +
            '<div class="servicios-modal-header-title" style="flex: 1;">' +
                '<h3 style="color: white; margin: 0; font-size: 20px; font-weight: 600;">Servicios M\u00e9dicos Disponibles</h3>' +
                '<p style="color: rgba(255, 255, 255, 0.9); margin: 4px 0 0; font-size: 13px;">' + count + ' servicio(s) disponible(s) para agregar</p>' +
            '</div>' +
            '<button type="button" class="servicios-modal-close" id="close-available-modal" style="background: rgba(255, 255, 255, 0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fas fa-times" style="color: white;"></i></button>' +
        '</div>' +
        '<div class="servicios-modal-body" style="flex: 1; overflow-y: auto; padding: 20px 24px; background: white;">' +
            '<div class="servicios-info-banner" style="background: #e8f4fd; border-left: 4px solid #1e3c72; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 12px;">' +
                '<i class="fas fa-info-circle" style="color: #1e3c72; font-size: 18px;"></i>' +
                '<div class="info-text" style="font-size: 13px; color: #2c3e50; line-height: 1.4;">' +
                    '<strong style="color: #2c3e50;">\u00bfQu\u00e9 significa "Disponible"?</strong>' +
                    ' Estos servicios cumplen con los criterios (sin plazo de espera pendiente y con usos disponibles) ' +
                '</div>' +
            '</div>';
        
        if (currentlySelected.length > 0) {
            modalHtml += '<div class="servicios-selection-info">' +
                '<i class="fas fa-check-square"></i>' +
                '<span>Actualmente tienes <strong>' + currentlySelected.length + '</strong> servicio(s) seleccionado(s). Los servicios que ya est\u00e1n agregados no se muestran en esta lista.</span>' +
            '</div>';
        }
        
        modalHtml += '<div class="servicios-toolbar">' +
    
'<div class="servicios-table-container">' +
    '<table class="servicios-table">' +
        '<thead>' +
            '<tr style="background: #e9ecef !important;">' +
                '<th width="5%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;"><div class="checkbox-wrapper"><input type="checkbox" id="available-select-all-checkbox"><label for="available-select-all-checkbox"></label></div></th>' +
                '<th width="25%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;">Servicio</th>' +
                '<th width="18%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;">\u00c1rea</th>' +
                '<th width="30%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;">Descripci\u00f3n</th>' +
                '<th width="12%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;">Disponibles</th>' +
                '<th width="10%" style="background: #e9ecef !important; color: #1a1a1a !important; font-weight: 700 !important; padding: 12px !important;">Precio</th>' +
            '</tr>' +
        '</thead>' +
        '<tbody>';
        
        Object.keys(items).forEach(function(baremoId) {
            var item = items[baremoId];
            if (!item) return;
            var disponibles = item.disponibles === 'Ilimitado' ? '\u221e' : item.disponibles;
            var precio = parseFloat(item.precio || 0).toFixed(2);
            var nombre = escapeHtml(item.nombre || 'Sin nombre');
            var area = escapeHtml(item.area || 'Sin \u00e1rea');
            var descripcion = escapeHtml(item.descripcion || 'Sin descripci\u00f3n');
            
            modalHtml += '<tr>' +
                '<td class="text-center"><div class="checkbox-wrapper"><input type="checkbox" class="service-checkbox-available" id="chk_' + baremoId + '" data-baremo-id="' + baremoId + '" data-price="' + precio + '"><label for="chk_' + baremoId + '"></label></div></td>' +
                '<td class="servicio-nombre"><strong>' + nombre + '</strong></td>' +
                '<td class="servicio-area"><span class="area-badge">' + area + '</span></td>' +
                '<td class="servicio-descripcion">' + descripcion + '</td>' +
                '<td class="text-center"><span class="disponibilidad-badge disponible">' + disponibles + '</span></td>' +
                '<td class="servicio-precio">$' + precio + '</td>' +
            '</tr>';
        });
        
        modalHtml += '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>' +
                '<div class="servicios-modal-footer">' +
                    '<div class="footer-summary">' +
                        '<div class="summary-item"><span class="summary-label">Total de servicios:</span><span class="summary-value" id="available-total-services">' + count + '</span></div>' +
                        '<div class="summary-item"><span class="summary-label">Valor total:</span><span class="summary-value summary-price">$' + totalAvailablePrice.toFixed(2) + '</span></div>' +
                        '<div class="summary-item"><span class="summary-label">Seleccionados:</span><span class="summary-value" id="available-selected-summary">0</span></div>' +
                    '</div>' +
                    '<div class="footer-actions">' +
                        '<button type="button" class="btn-modal btn-modal-secondary" id="cancel-available-modal"><i class="fas fa-times"></i> Cancelar</button>' +
                        '<button type="button" class="btn-modal btn-modal-primary" id="add-selected-available"><i class="fas fa-plus-circle"></i> Agregar Seleccionados</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
        
        jQuery('.servicios-modal-overlay').remove();
        jQuery('body').append(modalHtml);
        addModalStyles();
        
        function updateAvailableSelectedCount() {
            var selectedCount = jQuery('.service-checkbox-available:checked').length;
            jQuery('#available-selected-count').text(selectedCount + ' seleccionado' + (selectedCount !== 1 ? 's' : ''));
            jQuery('#available-selected-summary').text(selectedCount);
            jQuery('#available-select-all-checkbox').prop('checked', selectedCount === count && count > 0);
        }
        
        jQuery('#available-select-all-checkbox').off('change').on('change', function() {
            jQuery('.service-checkbox-available').prop('checked', jQuery(this).prop('checked'));
            updateAvailableSelectedCount();
        });
        jQuery(document).off('change', '.service-checkbox-available').on('change', '.service-checkbox-available', function() { updateAvailableSelectedCount(); });
        jQuery('#select-all-available').off('click').on('click', function() { jQuery('.service-checkbox-available').prop('checked', true); updateAvailableSelectedCount(); });
        jQuery('#deselect-all-available').off('click').on('click', function() { jQuery('.service-checkbox-available').prop('checked', false); updateAvailableSelectedCount(); });
        
        jQuery('#add-selected-available').off('click').on('click', function() {
            var selectedServices = [];
            jQuery('.service-checkbox-available:checked').each(function() {
                var baremoId = jQuery(this).data('baremo-id');
                if (baremoId && baremosTotales[baremoId]) selectedServices.push(baremoId);
            });
            if (selectedServices.length === 0) {
                if (typeof toastr !== 'undefined') toastr.warning('Por favor, seleccione al menos un servicio.');
                else alert('Por favor, seleccione al menos un servicio.');
                return;
            }
            var currentValues = baremosSelect.val() || [];
            var addedCount = 0;
            selectedServices.forEach(function(baremoId) {
                if (!currentValues.includes(baremoId.toString())) {
                    currentValues.push(baremoId.toString());
                    addedCount++;
                }
            });
            if (addedCount > 0) {
                baremosSelect.val(currentValues).trigger('change');
                if (typeof toastr !== 'undefined') toastr.success(addedCount + ' servicio(s) agregado(s) correctamente.');
            } else {
                if (typeof toastr !== 'undefined') toastr.info('Los servicios seleccionados ya est\u00e1n agregados.');
            }
            jQuery('.servicios-modal-overlay').fadeOut(300, function() { jQuery(this).remove(); });
        });
        
        jQuery('#close-available-modal, #cancel-available-modal').off('click').on('click', function() {
            jQuery('.servicios-modal-overlay').fadeOut(300, function() { jQuery(this).remove(); });
        });
        jQuery('.servicios-modal-overlay').off('click').on('click', function(e) {
            if (jQuery(e.target).hasClass('servicios-modal-overlay')) {
                jQuery(this).fadeOut(300, function() { jQuery(this).remove(); });
            }
        });
        jQuery('.servicios-modal-overlay').fadeIn(300);
    }
    
    // Professional Modal for Restricted/Agotados Services
    function showRestrictedAgotadosModal(type) {
        var isRestringidos = (type === 'restringidos');
        var title = isRestringidos ? 'Servicios con Plazo de Espera Pendiente' : 'Servicios Agotados';
        var icon = isRestringidos ? 'fa-clock' : 'fa-ban';
        var items = isRestringidos ? baremosPendientesInfo : baremosAgotadosInfo;
        var count = Object.keys(items).length;
        
        if (count === 0) return;
        
        var headerClass = isRestringidos ? 'header-warning' : 'header-danger';
        var bannerClass = isRestringidos ? 'info-warning' : 'info-danger';
        var iconClass2 = isRestringidos ? 'fa-hourglass-half' : 'fa-exclamation-triangle';
        var statusText = isRestringidos ? 'en per\u00edodo de espera' : 'con l\u00edmite de uso alcanzado';
        var infoText = isRestringidos ? '<strong>\u00bfQu\u00e9 significa "Plazo de Espera Pendiente"?</strong> Estos servicios requieren que haya transcurrido un per\u00edodo m\u00ednimo desde el inicio del contrato antes de poder ser utilizados. No est\u00e1n disponibles para selecci\u00f3n hasta que se cumpla el plazo.' : '<strong>\u00bfQu\u00e9 significa "Agotado"?</strong> Estos servicios han alcanzado su l\u00edmite m\u00e1ximo de usos permitidos por el plan. No pueden ser seleccionados nuevamente.';
        
        var modalHtml = '<div class="servicios-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(3px); display: flex; justify-content: center; align-items: center; z-index: 10000;">' +
    '<div class="servicios-modal servicios-' + (isRestringidos ? 'restringidos' : 'agotados') + '-modal" style="background: white; border-radius: 12px; width: 90%; max-width: 1100px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);">' +
        '<div class="servicios-modal-header ' + headerClass + '" style="padding: 20px 24px; border-radius: 12px 12px 0 0; display: flex; align-items: center; gap: 16px; color: white;">' +
            '<div class="servicios-modal-header-icon" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="fas ' + icon + '" style="color: white; font-size: 24px;"></i></div>' +
            '<div class="servicios-modal-header-title" style="flex: 1;">' +
                '<h3 style="color: white; margin: 0; font-size: 20px; font-weight: 600;">' + title + '</h3>' +
                '<p style="color: rgba(255, 255, 255, 0.9); margin: 4px 0 0; font-size: 13px;">' + count + ' servicio(s) ' + statusText + '</p>' +
            '</div>' +
            '<button type="button" class="servicios-modal-close" id="close-restricted-modal" style="background: rgba(255, 255, 255, 0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fas fa-times" style="color: white;"></i></button>' +
        '</div>' +
        '<div class="servicios-modal-body" style="flex: 1; overflow-y: auto; padding: 20px 24px; background: white;">' +
            '<div class="servicios-info-banner ' + bannerClass + '" style="padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 12px;">' +
                '<i class="fas ' + iconClass2 + '" style="font-size: 18px;"></i>' +
                '<div class="info-text" style="font-size: 13px; line-height: 1.4; color: #2c3e50;">' + infoText + '</div>' +
            '</div>' +
            '<div class="servicios-table-container" style="overflow-x: auto; border-radius: 8px; border: 1px solid #e9ecef;">' +
                '<table class="servicios-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">' +
                    '<thead>' +
                        '<tr style="background: #e9ecef;">' +
                            '<th width="25%" style="background: #58bef5; color: #1a1a1a; font-weight: 700; padding: 12px; text-align: left;">Servicio</th>' +
                            '<th width="18%" style="background: #58bef5; color: #1a1a1a; font-weight: 700; padding: 12px; text-align: left;">\u00c1rea</th>' +
                            '<th width="37%" style="background: #58bef5; color: #1a1a1a; font-weight: 700; padding: 12px; text-align: left;">Descripci\u00f3n</th>' +
                            '<th width="20%" style="background: #58bef5; color: #1a1a1a; font-weight: 700; padding: 12px; text-align: center;">' + (isRestringidos ? 'Tiempo Restante' : 'Uso Actual / L\u00edmite') + '</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>';
        
        Object.keys(items).forEach(function(baremoId) {
            var item = items[baremoId];
            if (!item) return;
            var nombre = escapeHtml(item.nombre || 'Sin nombre');
            var area = escapeHtml(item.area || 'Sin \u00e1rea');
            var descripcion = escapeHtml(item.descripcion || 'Sin descripci\u00f3n');
            var detailHtml = '';
            
            if (isRestringidos) {
                detailHtml = '<span class="detail-badge detail-warning"><i class="fas fa-hourglass-half"></i> ' + (item.remaining_text || 'No disponible') + '</span>';
            } else {
                var used = parseInt(item.veces_usado) || 0;
                var limit = parseInt(item.cantidad_limite) || 0;
                var percentage = limit > 0 ? (used / limit) * 100 : 0;
                detailHtml = '<div class="usage-progress"><div class="usage-stats"><span class="usage-used">' + used + '</span> / ' + limit + '</div><div class="progress-bar-container"><div class="progress-bar-fill" style="width: ' + percentage + '%; background-color: #dc3545;"></div></div></div>';
            }
            
            modalHtml += '<tr>' +
                '<td class="servicio-nombre"><strong>' + nombre + '</strong></td>' +
                '<td class="servicio-area"><span class="area-badge">' + area + '</span></td>' +
                '<td class="servicio-descripcion">' + descripcion + '</td>' +
                '<td class="text-center">' + detailHtml + '</td>' +
            '</tr>';
        });
        
        modalHtml += '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>' +
                '<div class="servicios-modal-footer">' +
                    '<div class="footer-summary"><div class="summary-item"><span class="summary-label">Total de servicios:</span><span class="summary-value">' + count + '</span></div></div>' +
                    '<div class="footer-actions"><button type="button" class="btn-modal btn-modal-secondary" id="close-restricted-modal-btn"><i class="fas fa-check"></i> Entendido</button></div>' +
                '</div>' +
            '</div>' +
        '</div>';
        
        jQuery('.servicios-modal-overlay').remove();
        jQuery('body').append(modalHtml);
        addModalStyles();
        
        jQuery('#close-restricted-modal, #close-restricted-modal-btn').off('click').on('click', function() {
            jQuery('.servicios-modal-overlay').fadeOut(300, function() { jQuery(this).remove(); });
        });
        jQuery('.servicios-modal-overlay').off('click').on('click', function(e) {
            if (jQuery(e.target).hasClass('servicios-modal-overlay')) {
                jQuery(this).fadeOut(300, function() { jQuery(this).remove(); });
            }
        });
        jQuery('.servicios-modal-overlay').fadeIn(300);
    }
    
    function initializeSelect2() {
        if (jQuery('#baremos-select').data('select2')) jQuery('#baremos-select').select2('destroy');
        jQuery('#baremos-select').select2({
            multiple: true,
            placeholder: 'Busca o selecciona los servicios del Baremo...',
            allowClear: true,
            closeOnSelect: false,
            tags: false,
            tokenSeparators: [',', ' '],
            minimumInputLength: 0,
            templateResult: function(data) {
                if (data.id && baremosHtml[data.id]) return jQuery(baremosHtml[data.id]);
                return data.text;
            },
            templateSelection: function(data) {
                if (data.id && baremosInfo[data.id]) return baremosInfo[data.id].nombre + ' (' + baremosInfo[data.id].area + ')';
                return data.text;
            },
            escapeMarkup: function(markup) { return markup; }
        });
        calcularTotalYTabla();
    }
    
    function calcularTotalYTabla() {
        var baremosSeleccionados = baremosSelect.val() || [];
        var total = 0;
        var tablaHtml = '';
        if (baremosSeleccionados.length === 0) {
            jQuery('#costo-total-container, #baremos-tabla-container').hide();
            jQuery('#costo-total-input').val('0.00');
            jQuery('#cobertura-warning').hide();
            return;
        }
        baremosSeleccionados.forEach(function(baremoId) {
            var item = baremosInfo[baremoId];
            if (item && item.precio !== undefined) {
                var precio = parseFloat(item.precio);
                total += precio;
                var restricciones = [];
                if (item.plazo_espera) restricciones.push('Plazo: ' + item.plazo_espera + ' meses');
                if (item.cantidad_limite > 0) {
                    var remaining = item.remaining_uses !== undefined ? item.remaining_uses : (item.cantidad_limite - item.veces_usado);
                    restricciones.push('L\u00edmite: ' + item.veces_usado + '/' + item.cantidad_limite + ' usos' + (remaining > 0 ? ' (' + remaining + ' restantes)' : ''));
                }
                tablaHtml += '<tr><td>' + item.nombre + '</td><td>' + item.area + '</td><td>' + (item.descripcion || 'Sin descripci\u00f3n') + '</td><td class="text-center">' + (restricciones.join('<br>') || 'Ninguna') + '</td><td class="text-end">$' + precio.toFixed(2) + '</td></tr>';
            }
        });
        jQuery('#baremos-tabla-body').html(tablaHtml);
        jQuery('#baremos-tabla-container').show();
        jQuery('#costo-total-value, #summary-total-amount').html('$' + total.toFixed(2));
        jQuery('#costo-total-input').val(total.toFixed(2));
        jQuery('#costo-total-container').show();
        var totalDisponible = parseFloat({$totalDisponible});
        if (total > totalDisponible) {
            jQuery('#cobertura-difference-text').text('Excede por $' + (total - totalDisponible).toFixed(2));
            jQuery('#cobertura-warning').show();
        } else {
            jQuery('#cobertura-warning').hide();
        }
    }
    
    function calculateTotal() {
        var selectedValues = baremosSelect.val() || [];
        var total = 0;
        selectedValues.forEach(function(baremoId) {
            var item = baremosInfo[baremoId];
            if (item && item.precio !== undefined) total += parseFloat(item.precio);
        });
        return total;
    }
    
    function validateFormBeforeSubmit() {
        var selectedValues = baremosSelect.val() || [];
        if (selectedValues.length === 0) {
            if (!jQuery('#empty-selection-error').length) {
                jQuery('.baremo-select-container').after('<div id="empty-selection-error" class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <strong>\u00a1Atenci\u00f3n!</strong> Debe seleccionar al menos un servicio m\u00e9dico para registrar la atenci\u00f3n.</div>');
            }
            jQuery('html, body').animate({ scrollTop: jQuery('#empty-selection-error').offset().top - 100 }, 500);
            return false;
        }
        var totalDisponible = parseFloat({$totalDisponible});
        if (calculateTotal() > totalDisponible) {
            jQuery('#cobertura-warning').show();
            jQuery('html, body').animate({ scrollTop: jQuery('#cobertura-warning').offset().top - 100 }, 500);
            return false;
        }
        jQuery('#empty-selection-error').remove();
        return true;
    }
    
    function setupEventHandlers() {
        jQuery('.stat-badge.disponible').off('click').on('click', function(e) { e.preventDefault(); e.stopPropagation(); showDisponiblesModal(); });
        jQuery('.stat-badge.restringido').off('click').on('click', function(e) { e.preventDefault(); e.stopPropagation(); showRestrictedAgotadosModal('restringidos'); });
        jQuery('.stat-badge.agotado').off('click').on('click', function(e) { e.preventDefault(); e.stopPropagation(); showRestrictedAgotadosModal('agotados'); });
        baremosSelect.off('change').on('change', function() { calcularTotalYTabla(); });
        jQuery('#recalculate-total').off('click').on('click', function() { calcularTotalYTabla(); jQuery(this).html('<i class="fas fa-check me-1"></i> \u00a1Recalculado!'); setTimeout(function() { jQuery('#recalculate-total').html('<i class="fas fa-redo me-1"></i> Recalcular'); }, 2000); });
        form.off('beforeSubmit').on('beforeSubmit', function(e) { if (!validateFormBeforeSubmit()) { e.preventDefault(); return false; } return true; });
    }
    
    jQuery(document).ready(function() {
        setTimeout(function() {
            initializeSelect2();
            if ({$esCita} == 1) citaWarning.show(); else citaWarning.hide();
            setupEventHandlers();
            jQuery('[data-toggle="tooltip"]').tooltip({ trigger: 'hover', placement: 'top', html: true });
        }, 300);
    });
})();
JS;
    $this->registerJs($jsCode, \yii\web\View::POS_END);
}
?>

<!-- ===== PROGRESS OVERLAY HTML ===== -->
<div id="progress-overlay" class="progress-overlay">
    <div class="progress-modal">
        <!-- Icon -->
        <div class="progress-icon">
            <div class="icon-wrapper">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="progress-success-check">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="progress-error-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
        </div>

        <!-- Title & Subtitle -->
        <h3 class="progress-title">Guardando Cita Médica</h3>
        <p class="progress-subtitle">Por favor, espere mientras procesamos su solicitud...</p>

        <!-- Steps -->
        <div class="progress-steps">
            <div class="progress-step">
                <span class="step-number">1</span>
                <span class="step-icon">📋</span>
                <div class="step-text">
                    <div class="step-label">Validando Información</div>
                    <div class="step-desc">Verificando los datos de la cita</div>
                </div>
                <span class="step-status"><i class="fas fa-check"></i></span>
            </div>
            <div class="progress-step">
                <span class="step-number">2</span>
                <span class="step-icon">💾</span>
                <div class="step-text">
                    <div class="step-label">Guardando Cita</div>
                    <div class="step-desc">Registrando la cita en el sistema</div>
                </div>
                <span class="step-status"><i class="fas fa-check"></i></span>
            </div>
            <div class="progress-step">
                <span class="step-number">3</span>
                <span class="step-icon">📧</span>
                <div class="step-text">
                    <div class="step-label">Enviando Notificación</div>
                    <div class="step-desc">Enviando correo de confirmación al afiliado</div>
                </div>
                <span class="step-status"><i class="fas fa-check"></i></span>
            </div>
            <div class="progress-step">
                <span class="step-number">4</span>
                <span class="step-icon">✅</span>
                <div class="step-text">
                    <div class="step-label">Finalizando</div>
                    <div class="step-desc">Preparando confirmación final</div>
                </div>
                <span class="step-status"><i class="fas fa-check"></i></span>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar-container">
            <div class="progress-bar-fill"></div>
        </div>

        <!-- Loading Spinner / Status -->
        <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
            <span class="progress-percentage">0%</span>
            <span class="progress-loading-spinner"></span>
            <span style="font-size: 13px; color: #6c757d;" class="progress-status-text">Validando información...</span>
        </div>
    </div>
</div>
<!-- ===== END PROGRESS OVERLAY ===== -->