<?php

namespace app\components;

use Yii;
use yii\helpers\Html;
use yii\db\Expression;
use app\models\Contratos;
use app\models\Cuotas;
use app\models\Pagos;
use app\models\UserDatos;
use app\models\ContratoCorporativo;
use app\models\ContratoCorporativoAfiliado;
use app\models\CorporativoUser;


/**
 * ContractHelper
 *
 * Central helper for all contract-related presentation, persistence,
 * and lifecycle logic.
 *
 * Sections:
 *   1. INDIVIDUAL CONTRACT HELPERS      (badge, tooltip, filter, colors, priority)
 *   2. CORPORATE CONTRACT HELPERS       (badge, tooltip, filter, colors, priority)
 *   3. CORPORATE CONTRACT PERSISTENCE   (upsert, annul, listing)
 *   4. CORPORATE CONTRACT LIFECYCLE     (status sync)
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * CORPORATE CONTRACT SEMANTICS (updated):
 *
 *   A corporation has ONE contract, but the DB stores ONE ROW PER PLAN it
 *   uses. Every row shares the SAME `nrocontrato` (e.g. "CO-J158118181-2026-4")
 *   and the SAME vigencia, so plan A and plan B of the same corporation form
 *   a single business-level contract.
 *
 *   - Uniqueness:   (corporativo_id, plan_id, year(fecha_ini))
 *   - Status:       synchronized across siblings (most-restrictive wins)
 *   - Annulment:    cascades across siblings + every individual contract
 *   - PDF:          one unified PDF per corporation (all plans in one doc)
 *
 *   The corporate's vigencia is supplied by the CALLER (drawer form or mass
 *   upload form) — the Corporativo model no longer stores dates. This lets
 *   a corporation hold multiple contracts, one per year.
 *
 *   Use findCorporateContractGroup() / groupCorporateContractsByNumber() to
 *   work with the "contract" as a whole.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class ContractHelper
{
    /**
     * Days within which the "Renovar Contrato" button appears.
     * Tune this single constant to change the UX across the whole app.
     */
    public const RENEWAL_WINDOW_DAYS = 60;

    // ============================================================
    // 1. INDIVIDUAL CONTRACT HELPERS
    // ============================================================

    public static function getContractStatusClass($status)
    {
        $status = strtolower((string)$status);
        $classes = [
            'registrado'   => 'badge badge-primary',
            'activo'       => 'badge badge-success',
            'anulado'      => 'badge badge-danger',
            'vencido'      => 'badge badge-warning',
            'pendiente'    => 'badge badge-info',
            'suspendido'   => 'badge badge-suspendido',
            'sin_contrato' => 'badge badge-light',
        ];

        return $classes[$status] ?? 'badge badge-light';
    }

    public static function getStatusIcon($status)
    {
        $status = strtolower((string)$status);
        $icons = [
            'registrado'   => '📋',
            'activo'       => '✅',
            'anulado'      => '❌',
            'vencido'      => '⏰',
            'pendiente'    => '⏳',
            'suspendido'   => '⏸️',
            'sin_contrato' => '📭',
        ];

        return $icons[$status] ?? '📄';
    }

    public static function getStatusLabel($status)
    {
        $labels = [
            'activo'       => 'Activo',
            'registrado'   => 'Registrado',
            'suspendido'   => 'Suspendido',
            'vencido'      => 'Vencido',
            'pendiente'    => 'Pendiente',
            'anulado'      => 'Anulado',
            'sin_contrato' => 'Sin Contrato',
        ];

        return $labels[strtolower((string)$status)] ?? ucfirst((string)$status);
    }

    public static function getStatusTooltip($status)
    {
        $status = strtolower((string)$status);
        $tooltips = [
            'activo'       => '✅ <strong>CONTRATO ACTIVO</strong><br><small>El afiliado tiene acceso completo a los servicios médicos.</small>',
            'registrado'   => '📋 <strong>CONTRATO REGISTRADO</strong><br><small>Contrato registrado pero aún no activo. Se activará automáticamente en la fecha de inicio.</small>',
            'suspendido'   => '⏸️ <strong>CONTRATO SUSPENDIDO</strong><br><small>Contrato suspendido por falta de pago o incumplimiento.</small>',
            'vencido'      => '⏰ <strong>CONTRATO VENCIDO</strong><br><small>La fecha de vencimiento ha pasado. El afiliado ya no tiene acceso a los servicios.</small>',
            'pendiente'    => '⏳ <strong>CONTRATO PENDIENTE</strong><br><small>Contrato en proceso de aprobación o con documentación pendiente.</small>',
            'anulado'      => '❌ <strong>CONTRATO ANULADO</strong><br><small>Contrato cancelado permanentemente.</small>',
            'sin_contrato' => '📭 <strong>SIN CONTRATO</strong><br><small>Afiliado sin contrato activo registrado.</small>',
        ];

        return $tooltips[$status] ?? 'Estado: ' . ucfirst($status);
    }

    public static function getFilterOptions()
    {
        return [
            'activo'       => '✅ Activo',
            'registrado'   => '📋 Registrado',
            'suspendido'   => '⏸️ Suspendido',
            'vencido'      => '⏰ Vencido',
            'pendiente'    => '⏳ Pendiente',
            'anulado'      => '❌ Anulado',
            'sin_contrato' => '📭 Sin Contrato',
        ];
    }

    public static function getFilterOptionsRaw()
    {
        return [
            'activo'       => 'Activo',
            'registrado'   => 'Registrado',
            'suspendido'   => 'Suspendido',
            'vencido'      => 'Vencido',
            'pendiente'    => 'Pendiente',
            'anulado'      => 'Anulado',
            'sin_contrato' => 'Sin Contrato',
        ];
    }

    public static function getStatusColor($status)
    {
        $status = strtolower((string)$status);
        $colors = [
            'activo'       => '#28a745',
            'registrado'   => '#007bff',
            'suspendido'   => '#6c757d',
            'vencido'      => '#ffc107',
            'pendiente'    => '#17a2b8',
            'anulado'      => '#dc3545',
            'sin_contrato' => '#f8f9fa',
        ];

        return $colors[$status] ?? '#6c757d';
    }

    public static function isProblematicStatus($status)
    {
        return in_array(strtolower((string)$status), ['suspendido', 'vencido', 'anulado'], true);
    }

    public static function isActiveStatus($status)
    {
        return in_array(strtolower((string)$status), ['activo', 'registrado'], true);
    }

    public static function getStatusPriority($status)
    {
        $status = strtolower((string)$status);
        $priority = [
            'suspendido'   => 1,
            'vencido'      => 2,
            'anulado'      => 3,
            'pendiente'    => 4,
            'registrado'   => 5,
            'activo'       => 6,
            'sin_contrato' => 7,
        ];

        return $priority[$status] ?? 99;
    }

    public static function getPendingPaymentStatus($userId)
    {
        if (!$userId) {
            return null;
        }

        $pendingPayments = Pagos::find()
            ->where(['user_id' => $userId, 'estatus' => 'Por Conciliar'])
            ->orderBy(['fecha_pago' => SORT_DESC])
            ->all();

        if (empty($pendingPayments)) {
            return null;
        }

        $totalAmount    = 0;
        $paymentDetails = [];

        foreach ($pendingPayments as $payment) {
            $amount       = $payment->monto_usd ?: $payment->monto_pagado;
            $totalAmount += $amount;

            $paymentDetails[] = [
                'id'        => $payment->id,
                'amount'    => $amount,
                'date'      => Yii::$app->formatter->asDate($payment->fecha_pago, 'd/M/Y'),
                'method'    => $payment->metodo_pago,
                'reference' => $payment->numero_referencia_pago,
            ];
        }

        return [
            'count'              => count($pendingPayments),
            'total_amount'       => round($totalAmount, 2),
            'details'            => $paymentDetails,
            'first_payment_date' => $paymentDetails[0]['date'] ?? null,
            'has_pending'        => true,
        ];
    }

    public static function getMostRecentContract($userId)
    {
        if (!$userId) {
            return null;
        }

        return Contratos::find()
            ->where(['user_id' => $userId])
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }

    public static function generateContractTooltip($contrato, $pendingPaymentStatus = null)
    {
        if (!$contrato) {
            return 'Contrato no disponible';
        }

        $status = strtolower($contrato->estatus);
        $today  = date('Y-m-d');

        $tooltip  = "<div class='contract-tooltip'>";
        $tooltip .= "<strong>Contrato #{$contrato->id}</strong><br>";

        if ($contrato->nrocontrato) {
            $tooltip .= "Número: <strong>{$contrato->nrocontrato}</strong><br>";
        }

        $tooltip .= "Inicio: " . Yii::$app->formatter->asDate($contrato->fecha_ini, 'd/M/Y') . "<br>";

        if ($contrato->fecha_ven) {
            $vencimiento = Yii::$app->formatter->asDate($contrato->fecha_ven, 'd/M/Y');
            $tooltip .= "Vence: <strong>{$vencimiento}</strong><br>";

            $daysDiff = (int) floor((strtotime($contrato->fecha_ven) - strtotime($today)) / 86400);
            if ($daysDiff > 0) {
                $tooltip .= "<small>Vence en <strong>{$daysDiff} días</strong></small><br>";
            } elseif ($daysDiff < 0) {
                $tooltip .= "<small>Vencido hace <strong>" . abs($daysDiff) . " días</strong></small><br>";
            }
        }

        $tooltip .= "Monto: <strong>$" . number_format($contrato->monto, 2) . "</strong><br>";

        if ($contrato->plan) {
            $tooltip .= "Plan: <strong>{$contrato->plan->nombre}</strong><br>";
        }

        $tooltip .= "<hr style='margin: 5px 0; border-color: #ccc;'>";

        switch ($status) {
            case 'activo':
                $tooltip .= "<div style='color: #28a745;'><strong>✓ CONTRATO ACTIVO</strong></div>";
                $tooltip .= "<small>El afiliado tiene acceso completo a los servicios médicos.</small>";
                break;
            case 'registrado':
                $tooltip .= "<div style='color: #007bff;'><strong>📝 CONTRATO REGISTRADO</strong></div>";
                $tooltip .= "<small>Contrato registrado pero aún no activo.</small>";
                break;
            case 'vencido':
                $tooltip .= "<div style='color: #ffc107;'><strong>⏰ CONTRATO VENCIDO</strong></div>";
                $tooltip .= "<small>La fecha de vencimiento ha pasado.</small>";
                break;
            case 'suspendido':
                $tooltip .= "<div style='color: #6c757d;'><strong>⏸️ CONTRATO SUSPENDIDO</strong></div>";
                $tooltip .= "<small>Contrato suspendido por falta de pago o incumplimiento.</small>";
                break;
            case 'anulado':
                $tooltip .= "<div style='color: #dc3545;'><strong>❌ CONTRATO ANULADO</strong></div>";
                $tooltip .= "<small>Contrato cancelado permanentemente.</small>";
                break;
            default:
                $tooltip .= "<div><strong>Estado: " . ucfirst($status) . "</strong></div>";
                break;
        }

        if ($pendingPaymentStatus) {
            $tooltip .= "<hr style='margin: 5px 0; border-color: #f39c12;'>";
            $tooltip .= "<div style='color: #f39c12;'><strong>💰 PAGOS PENDIENTES</strong></div>";
            $tooltip .= "<small>Cantidad: <strong>{$pendingPaymentStatus['count']}</strong> | "
                . "Monto: <strong>$" . number_format($pendingPaymentStatus['total_amount'], 2) . "</strong></small>";
        }

        $tooltip .= "</div>";

        return htmlspecialchars_decode($tooltip, ENT_QUOTES);
    }

    public static function generateNoContractTooltip($user, $pendingPaymentStatus = null)
    {
        $tooltip  = "<div class='contract-tooltip'>";
        $tooltip .= "<strong>AFILIADO SIN CONTRATO</strong><br>";
        $tooltip .= "Nombre: <strong>{$user->nombres} {$user->apellidos}</strong><br>";
        $tooltip .= "Cédula: <strong>{$user->tipo_cedula}-{$user->cedula}</strong><br>";
        $tooltip .= "<hr style='margin: 5px 0; border-color: #ccc;'>";
        $tooltip .= "<div style='color: #6c757d;'><strong>⚠️ SIN CONTRATO ACTIVO</strong></div>";

        if ($pendingPaymentStatus) {
            $tooltip .= "<hr style='margin: 5px 0; border-color: #f39c12;'>";
            $tooltip .= "<div style='color: #f39c12;'><strong>💰 PAGOS PENDIENTES</strong></div>";
            $tooltip .= "<small>Cantidad: <strong>{$pendingPaymentStatus['count']}</strong></small>";
        }

        $tooltip .= "</div>";

        return htmlspecialchars_decode($tooltip, ENT_QUOTES);
    }

    public static function generateContractStatusBadge($user)
    {
        $pendingPaymentStatus = self::getPendingPaymentStatus($user->id);
        $anyContract          = self::getMostRecentContract($user->id);

        if ($anyContract && $anyContract->estatus === Contratos::STATUS_ANULADO) {
            $tooltip = self::generateContractTooltip($anyContract, $pendingPaymentStatus);
            return Html::tag('span', '❌ Anulado', [
                'class'          => 'badge badge-danger',
                'title'          => $tooltip,
                'data-toggle'    => 'tooltip',
                'data-html'      => 'true',
                'data-placement' => 'top',
                'style'          => 'cursor: help; white-space: nowrap; display: inline-block;',
            ]);
        }

        $contrato = Contratos::getContratoActivo($user->id);

        if (!$contrato) {
            $contratosValidos = Contratos::getContratosValidos($user->id);
            if (!empty($contratosValidos)) {
                $contrato = $contratosValidos[0];
            }
        }

        if ($contrato) {
            $tooltip     = self::generateContractTooltip($contrato, $pendingPaymentStatus);
            $statusText  = $contrato->getStatusLabel();
            $statusClass = self::getContractStatusClass($contrato->estatus);
            $statusIcon  = self::getStatusIcon($contrato->estatus);

            $pendingIndicator = '';
            if ($pendingPaymentStatus && !empty($pendingPaymentStatus['has_pending'])) {
                $pendingIndicator = Html::tag('span', '⏳ ' . $pendingPaymentStatus['count'], [
                    'class'          => 'pending-payments-badge',
                    'title'          => $pendingPaymentStatus['count'] . ' pago(s) pendiente(s)',
                    'data-toggle'    => 'tooltip',
                    'data-placement' => 'top',
                    'data-html'      => 'true',
                    'style'          => 'cursor: help; margin-left: 5px;',
                ]);
            }

            return Html::tag('span', $statusIcon . ' ' . $statusText, [
                'class'          => $statusClass,
                'title'          => $tooltip,
                'data-toggle'    => 'tooltip',
                'data-html'      => 'true',
                'data-placement' => 'top',
                'style'          => 'cursor: help; white-space: nowrap; display: inline-block;',
            ]) . ($pendingIndicator ? ' ' . $pendingIndicator : '');
        }

        return self::generateNoContractBadge($user, $pendingPaymentStatus);
    }

    public static function generateNoContractBadge($user, $pendingPaymentStatus = null)
    {
        if ($pendingPaymentStatus) {
            $tooltip = self::generateNoContractTooltip($user, $pendingPaymentStatus);
            return Html::tag('span', '⚠️ ' . $pendingPaymentStatus['count'] . ' pago(s) sin contrato', [
                'class'          => 'badge badge-warning',
                'title'          => $tooltip,
                'data-toggle'    => 'tooltip',
                'data-html'      => 'true',
                'data-placement' => 'top',
                'style'          => 'cursor: help; white-space: nowrap; display: inline-block;',
            ]);
        }

        return Html::tag('span', '📭 Sin Contrato', [
            'class'          => 'badge badge-light',
            'title'          => self::generateNoContractTooltip($user),
            'data-toggle'    => 'tooltip',
            'data-html'      => 'true',
            'data-placement' => 'top',
            'style'          => 'cursor: help; display: inline-block;',
        ]);
    }

    public static function getContractStatusCellClasses($user)
    {
        $anyContract = self::getMostRecentContract($user->id);

        if ($anyContract && $anyContract->estatus === Contratos::STATUS_ANULADO) {
            return ['class' => 'contract-status-cell anulado text-center'];
        }

        $contrato             = Contratos::getContratoActivo($user->id);
        $pendingPaymentStatus = self::getPendingPaymentStatus($user->id);

        $classes = ['style' => 'text-align: center; padding: 10 !important;'];

        if ($contrato) {
            $classes['class'] = 'contract-status-cell ' . strtolower($contrato->estatus);
        }

        if ($pendingPaymentStatus) {
            $classes['class'] = ($classes['class'] ?? '') . ' has-pending-payments';
        }

        return $classes;
    }

    public static function generateContractStatusBadgeFromStatus($status, $user = null)
    {
        if (empty($status) || $status === 'sin_contrato') {
            if ($user) {
                return self::generateNoContractBadge($user);
            }
            return Html::tag('span', '📭 Sin Contrato', [
                'class'          => 'badge badge-light',
                'title'          => 'Afiliado sin contrato activo',
                'data-toggle'    => 'tooltip',
                'data-html'      => 'true',
                'data-placement' => 'top',
                'style'          => 'cursor: help;',
            ]);
        }

        return Html::tag('span', self::getStatusIcon($status) . ' ' . self::getStatusLabel($status), [
            'class'          => self::getContractStatusClass($status),
            'title'          => self::getStatusTooltip($status),
            'data-toggle'    => 'tooltip',
            'data-html'      => 'true',
            'data-placement' => 'top',
            'style'          => 'cursor: help; white-space: nowrap;',
        ]);
    }

    // ============================================================
    // 2. CORPORATE CONTRACT HELPERS
    // ============================================================

    /**
     * Build the corporation-wide contract number: CO-{rif}-{year}-{corpId}
     *
     * The plan is intentionally NOT part of the number — one corporation-wide
     * contract covers every plan it uses. All ContratoCorporativo rows for
     * the same corporation/year share this number.
     *
     * Example: CO-J158118181-2026-4
     *
     * @param  \app\models\Corporativo $corporativo
     * @param  int|null                $year
     * @return string
     */
    public static function buildCorporateContractNumber($corporativo, $year = null)
    {
        $year      = $year ?: date('Y');
        $rifLimpio = preg_replace('/[^A-Za-z0-9]/', '', (string)$corporativo->rif);

        return 'CO-' . $rifLimpio . '-' . $year . '-' . $corporativo->id;
    }

    public static function generateCorporateContractTooltip($contract)
    {
        if (!$contract) {
            return 'Contrato corporativo no disponible';
        }

        $status = strtolower($contract->estatus ?: 'registrado');
        $today  = date('Y-m-d');

        $tooltip  = "<div class='contract-tooltip'>";
        $tooltip .= "<strong>Contrato Colectivo</strong><br>";
        $tooltip .= "N°: <strong>" . Html::encode($contract->nrocontrato ?: 'N/A') . "</strong><br>";

        if ($contract->plan) {
            $tooltip .= "Plan: <strong>" . Html::encode($contract->plan->nombre) . "</strong><br>";
        }

        if (!empty($contract->cantidad_afiliados)) {
            $tooltip .= "Afiliados cubiertos: <strong>" . (int)$contract->cantidad_afiliados . "</strong><br>";
        }

        if (!empty($contract->monto_total)) {
            $tooltip .= "Monto total mensual: <strong>$"
                . number_format((float)$contract->monto_total, 2, ',', '.')
                . "</strong><br>";
        }

        if ($contract->fecha_ini) {
            $tooltip .= "Inicio: " . Yii::$app->formatter->asDate($contract->fecha_ini, 'd/M/Y') . "<br>";
        }

        if ($contract->fecha_ven) {
            $vencimiento = Yii::$app->formatter->asDate($contract->fecha_ven, 'd/M/Y');
            $tooltip .= "Vence: <strong>{$vencimiento}</strong><br>";

            $daysDiff = (int) floor((strtotime($contract->fecha_ven) - strtotime($today)) / 86400);
            if ($daysDiff > 0) {
                $tooltip .= "<small>Vence en <strong>{$daysDiff} días</strong></small><br>";
            } elseif ($daysDiff < 0) {
                $tooltip .= "<small>Vencido hace <strong>" . abs($daysDiff) . " días</strong></small><br>";
            }
        }

        $tooltip .= "<hr style='margin: 5px 0; border-color: #ccc;'>";

        switch ($status) {
            case 'activo':
                $tooltip .= "<div style='color: #28a745;'><strong>✓ CONTRATO COLECTIVO ACTIVO</strong></div>";
                $tooltip .= "<small>Los afiliados cubiertos tienen acceso completo.</small>";
                break;
            case 'registrado':
                $tooltip .= "<div style='color: #007bff;'><strong>📝 CONTRATO COLECTIVO REGISTRADO</strong></div>";
                $tooltip .= "<small>Se activará en la fecha de inicio.</small>";
                break;
            case 'vencido':
                $tooltip .= "<div style='color: #ffc107;'><strong>⏰ CONTRATO COLECTIVO VENCIDO</strong></div>";
                $tooltip .= "<small>La fecha de vencimiento ha pasado.</small>";
                break;
            case 'suspendido':
                $tooltip .= "<div style='color: #6c757d;'><strong>⏸️ CONTRATO COLECTIVO SUSPENDIDO</strong></div>";
                $tooltip .= "<small>Suspendido por incumplimiento del corporativo.</small>";
                break;
            case 'anulado':
                $tooltip .= "<div style='color: #dc3545;'><strong>❌ CONTRATO COLECTIVO ANULADO</strong></div>";
                $tooltip .= "<small>Los contratos individuales vinculados también fueron anulados.</small><br>";

                if ($contract->anulado_fecha) {
                    $tooltip .= "<small>Anulado el: "
                        . Yii::$app->formatter->asDate($contract->anulado_fecha, 'd/M/Y')
                        . "</small><br>";
                }
                if ($contract->anulado_motivo) {
                    $tooltip .= "<small>Motivo: <em>" . Html::encode($contract->anulado_motivo) . "</em></small><br>";
                }
                break;
        }

        $tooltip .= "</div>";

        return htmlspecialchars_decode($tooltip, ENT_QUOTES);
    }

    /**
     * Renders the corporate-contract status badge.
     *
     * FIXES:
     *   - Tooltip was showing raw HTML tags because the tooltip string was
     *     pre-decoded and then re-escaped by Html::tag(). We now pass the
     *     RAW HTML directly and let Html::tag() escape it exactly ONCE.
     *     Bootstrap (data-html="true") then un-escapes it into real DOM.
     *   - The affiliate-count pill (👥 N) was removed entirely. It was
     *     misleading on per-plan rows because the business contract spans
     *     multiple plans; the count is already shown in the group summary.
     *
     * @param  \app\models\ContratoCorporativo|null $contract
     * @return string
     */
    public static function getCorporateContractStatusBadge($contract)
    {
        if (!$contract) {
            return Html::tag('span', '📭 Sin Contrato', [
                'class' => 'badge badge-light',
                'style' => 'cursor: help;',
            ]);
        }

        // Build the RAW HTML tooltip (real <div>, <br>, <strong>).
        // Do NOT pre-decode — Html::tag() will escape it exactly once,
        // and Bootstrap's data-html="true" will un-escape it back.
        $tooltipHtml = self::generateCorporateContractTooltip($contract);

        $statusClass = self::getContractStatusClass($contract->estatus);
        $statusIcon  = self::getStatusIcon($contract->estatus);
        $statusLabel = self::getStatusLabel($contract->estatus);

        return Html::tag('span', $statusIcon . ' ' . $statusLabel, [
            'class'          => $statusClass,
            'data-toggle'    => 'tooltip',
            'data-html'      => 'true',
            'data-placement' => 'top',
            'title'          => $tooltipHtml,
            'style'          => 'cursor: help; white-space: nowrap; display: inline-block;',
        ]);
    }

    /**
     * Computes a human-friendly "remaining duration" descriptor for a
     * corporate contract, plus the fields needed to render a progress bar.
     *
     * Returns an array:
     *   [
     *     'state'        => 'active' | 'upcoming' | 'expired' | 'unknown',
     *     'label'        => 'Vigente · 214 días restantes',
     *     'short_label'  => '214 días',
     *     'days'         => 214 (signed: negative = days overdue),
     *     'total_days'   => 365,
     *     'elapsed_days' => 151,
     *     'percent'      => 41 (0..100, elapsed % of total),
     *     'badge_class'  => 'remaining-badge remaining-active',
     *     'bar_class'    => 'progress-bar bg-success',
     *   ]
     *
     * @param  \app\models\ContratoCorporativo $contract
     * @return array
     */
    public static function getCorporateContractRemainingMeta($contract): array
    {
        $unknown = [
            'state'        => 'unknown',
            'label'        => 'Sin vigencia definida',
            'short_label'  => '—',
            'days'         => null,
            'total_days'   => 0,
            'elapsed_days' => 0,
            'percent'      => 0,
            'badge_class'  => 'remaining-badge remaining-unknown',
            'bar_class'    => 'progress-bar bg-secondary',
        ];

        if (empty($contract->fecha_ini) || empty($contract->fecha_ven)) {
            return $unknown;
        }

        $iniTs = strtotime($contract->fecha_ini);
        $venTs = strtotime($contract->fecha_ven);

        if ($iniTs === false || $venTs === false || $venTs <= $iniTs) {
            return $unknown;
        }

        $todayTs = strtotime(date('Y-m-d'));

        $totalDays   = (int) round(($venTs - $iniTs) / 86400);
        $elapsedDays = (int) round(($todayTs - $iniTs) / 86400);
        $days        = (int) floor(($venTs - $todayTs) / 86400);

        // Clamp elapsed for the progress bar
        $elapsedClamped = max(0, min($elapsedDays, $totalDays));
        $percent        = $totalDays > 0
            ? (int) round(($elapsedClamped / $totalDays) * 100)
            : 0;

        // Status pill
        if ($contract->estatus === ContratoCorporativo::STATUS_ANULADO) {
            $state       = 'expired';
            $label       = 'Contrato anulado';
            $shortLabel  = 'Anulado';
            $badgeClass  = 'remaining-badge remaining-annulled';
            $barClass    = 'progress-bar bg-danger';
        } elseif ($days < 0) {
            $state       = 'expired';
            $label       = 'Vencido hace ' . abs($days) . ' día(s)';
            $shortLabel  = abs($days) . 'd vencido';
            $badgeClass  = 'remaining-badge remaining-expired';
            $barClass    = 'progress-bar bg-danger';
        } elseif ($days === 0) {
            $state       = 'active';
            $label       = 'Vence hoy';
            $shortLabel  = 'Vence hoy';
            $badgeClass  = 'remaining-badge remaining-warning';
            $barClass    = 'progress-bar bg-warning';
        } elseif ($days <= 30) {
            $state       = 'active';
            $label       = 'Por vencer · ' . $days . ' día(s) restantes';
            $shortLabel  = $days . 'd restantes';
            $badgeClass  = 'remaining-badge remaining-warning';
            $barClass    = 'progress-bar bg-warning';
        } elseif ($elapsedDays < 0) {
            // fecha_ini is in the future
            $state       = 'upcoming';
            $untilStart  = abs($elapsedDays);
            $label       = 'Comienza en ' . $untilStart . ' día(s)';
            $shortLabel  = 'En ' . $untilStart . 'd';
            $badgeClass  = 'remaining-badge remaining-upcoming';
            $barClass    = 'progress-bar bg-info';
        } else {
            $state       = 'active';
            $label       = 'Vigente · ' . $days . ' día(s) restantes';
            $shortLabel  = $days . 'd restantes';
            $badgeClass  = 'remaining-badge remaining-active';
            $barClass    = 'progress-bar bg-success';
        }

        return [
            'state'        => $state,
            'label'        => $label,
            'short_label'  => $shortLabel,
            'days'         => $days,
            'total_days'   => $totalDays,
            'elapsed_days' => $elapsedClamped,
            'percent'      => $percent,
            'badge_class'  => $badgeClass,
            'bar_class'    => $barClass,
        ];
    }

    /**
     * Decides what "new contract" action should be offered.
     *
     * RULES (per business decision):
     *   - No contract exists              → CREATE current-year
     *   - Latest contract is ACTIVE       → DISABLED, tooltip says when it expires
     *   - Latest contract is EXPIRED      → CREATE next-year
     *   - Latest contract is ANNULLED     → CREATE next-year (or current, if no other)
     *
     * The button is only enabled AFTER fecha_ven has passed. No early renewal.
     *
     * @param  array $contractGroups
     * @param  int   $currentYear
     * @return array
     */
    public static function resolveNewContractAction(array $contractGroups, int $currentYear): array
    {
        // ── Case 1: no contracts at all → create current-year
        if (empty($contractGroups)) {
            return [
                'action'         => 'create',
                'target_year'    => $currentYear,
                'current_group'  => null,
                'days_remaining' => null,
                'reason'         => '',
                'label'          => 'Nuevo Contrato ' . $currentYear,
                'style'          => 'primary',
            ];
        }

        // ── Find the LATEST non-annulled group (highest fecha_ini)
        $latestGroup = null;
        $latestIniTs = null;

        foreach ($contractGroups as $group) {
            if (!empty($group['is_annulled'])) {
                continue;
            }
            if (empty($group['anchor']->fecha_ini)) {
                continue;
            }
            $ts = strtotime($group['anchor']->fecha_ini);
            if ($latestIniTs === null || $ts > $latestIniTs) {
                $latestIniTs = $ts;
                $latestGroup = $group;
            }
        }

        // ── Case 2: all contracts are annulled → create current-year
        if ($latestGroup === null) {
            return [
                'action'         => 'create',
                'target_year'    => $currentYear,
                'current_group'  => null,
                'days_remaining' => null,
                'reason'         => '',
                'label'          => 'Nuevo Contrato ' . $currentYear,
                'style'          => 'primary',
            ];
        }

        $anchor   = $latestGroup['anchor'];
        $iniYear  = (int) date('Y', strtotime($anchor->fecha_ini));

        // ── Compute days remaining
        $daysRemaining = null;
        if (!empty($anchor->fecha_ven)) {
            $today = new \DateTimeImmutable('today');
            $ven   = \DateTimeImmutable::createFromFormat('Y-m-d', $anchor->fecha_ven);
            if ($ven) {
                $daysRemaining = (int) $today->diff($ven)->format('%r%a');
            }
        }

        // ── Case 3: no expiry date → disabled (safe default)
        if ($daysRemaining === null) {
            return [
                'action'         => 'none',
                'target_year'    => $iniYear + 1,
                'current_group'  => $latestGroup,
                'days_remaining' => null,
                'reason'         => 'El contrato actual no tiene fecha de vencimiento definida. Contacte a un administrador.',
                'label'          => 'Contrato activo',
                'style'          => 'disabled',
            ];
        }

        // ── Case 4: contract is still active → DISABLED
        if ($daysRemaining >= 0) {
            $expiryPretty = !empty($anchor->fecha_ven)
                ? date('d/m/Y', strtotime($anchor->fecha_ven))
                : 'N/A';

            return [
                'action'         => 'none',
                'target_year'    => $iniYear + 1,
                'current_group'  => $latestGroup,
                'days_remaining' => $daysRemaining,
                'reason'         => 'El contrato vigente vence el ' . $expiryPretty
                    . ' (' . $daysRemaining . ' día' . ($daysRemaining === 1 ? '' : 's') . ' restantes). '
                    . 'Podrá crear un nuevo contrato una vez que expire.',
                'label'          => 'Vigente — quedan ' . $daysRemaining . ' día' . ($daysRemaining === 1 ? '' : 's'),
                'style'          => 'disabled',
            ];
        }

        // ── Case 5: contract is expired → CREATE next-year
        $nextYear = $iniYear + 1;

        return [
            'action'         => 'create',
            'target_year'    => $nextYear,
            'current_group'  => $latestGroup,
            'days_remaining' => $daysRemaining,
            'reason'         => 'El contrato anterior venció el '
                . date('d/m/Y', strtotime($anchor->fecha_ven))
                . ' (hace ' . abs($daysRemaining) . ' día'
                . (abs($daysRemaining) === 1 ? '' : 's') . ').',
            'label'          => 'Crear Contrato ' . $nextYear,
            'style'          => 'primary',
        ];
    }

    /**
     * Checks whether a proposed new contract would overlap with any
     * existing non-annulled contract for the same corporate.
     *
     * Overlap is forbidden per business rules.
     *
     * @param  int         $corporativoId
     * @param  string      $fechaIni      (YYYY-MM-DD)
     * @param  string|null $excludeContractId  If editing, the row to ignore
     * @return array|null  Null if OK; otherwise ['conflicts' => [...], 'message' => '...']
     */
    public static function validateNoOverlap(
        int $corporativoId,
        string $fechaIni,
        $excludeContractIds = null   // int | int[] | null
    ): ?array {
        if (empty($fechaIni)) {
            return null;
        }

        // Normalize to array
        if ($excludeContractIds === null) {
            $excludeContractIds = [];
        } elseif (!is_array($excludeContractIds)) {
            $excludeContractIds = [(int)$excludeContractIds];
        }
        $excludeContractIds = array_filter(array_map('intval', $excludeContractIds));

        $query = ContratoCorporativo::find()
            ->where(['corporativo_id' => $corporativoId])
            ->andWhere(['!=', 'estatus', ContratoCorporativo::STATUS_ANULADO])
            ->andWhere(['IS NOT', 'fecha_ini', null])
            ->andWhere(['IS NOT', 'fecha_ven', null]);

        if (!empty($excludeContractIds)) {
            $query->andWhere(['NOT IN', 'id', $excludeContractIds]);
        }

        $existing = $query->all();
        $conflicts = [];

        foreach ($existing as $row) {
            if ($fechaIni <= $row->fecha_ven) {
                $conflicts[] = $row;
            }
        }

        if (empty($conflicts)) {
            return null;
        }

        // Collect distinct nrocontrato values for the message
        $conflictingContracts = [];
        foreach ($conflicts as $c) {
            $conflictingContracts[$c->nrocontrato] = $c->fecha_ven;
        }

        $msgs = [];
        foreach ($conflictingContracts as $nro => $ven) {
            $msgs[] = $nro . ' (vigente hasta ' . date('d/m/Y', strtotime($ven)) . ')';
        }

        return [
            'conflicts' => $conflicts,
            'message'   => 'La fecha de inicio (' . date('d/m/Y', strtotime($fechaIni))
                . ') solapa con el contrato ' . implode(', ', $msgs)
                . '. Los contratos no pueden solaparse.',
        ];
    }

    /**
     * Returns the UserDatos records linked to a given contract group
     * (identified by nrocontrato), regardless of whether those users
     * are still affiliated with the corporate today.
     *
     * Used when creating a new year's contract so it inherits the same
     * affiliate set as the previous one.
     *
     * @param  int    $corporativoId
     * @param  string $nrocontrato
     * @return \app\models\UserDatos[]
     */
    public static function getPreviousContractAffiliates(int $corporativoId, string $nrocontrato): array
    {
        $rows = ContratoCorporativo::find()
            ->select('id')
            ->where([
                'corporativo_id' => $corporativoId,
                'nrocontrato'    => $nrocontrato,
            ])
            ->column();

        if (empty($rows)) {
            return [];
        }

        $userIds = ContratoCorporativoAfiliado::find()
            ->select('user_datos_id')
            ->where(['contrato_corporativo_id' => $rows])
            ->distinct()
            ->column();

        if (empty($userIds)) {
            return [];
        }

        return UserDatos::find()
            ->where(['id' => $userIds])
            ->orderBy(['apellidos' => SORT_ASC, 'nombres' => SORT_ASC])
            ->all();
    }

    public static function getCorporateFilterOptions()
    {
        return [
            'registrado' => '📋 Registrado',
            'activo'     => '✅ Activo',
            'suspendido' => '⏸️ Suspendido',
            'vencido'    => '⏰ Vencido',
            'anulado'    => '❌ Anulado',
        ];
    }

    public static function getCorporateFilterOptionsRaw()
    {
        return [
            'registrado' => 'Registrado',
            'activo'     => 'Activo',
            'suspendido' => 'Suspendido',
            'vencido'    => 'Vencido',
            'anulado'    => 'Anulado',
        ];
    }

    // ============================================================
    // 3. CORPORATE CONTRACT PERSISTENCE
    // ============================================================

    /**
     * Find all non-annulled corporate contracts for a given corporate.
     *
     * NOTE: Because a corporation now has one DB row per plan (all sharing
     * the same `nrocontrato`), the result may contain multiple rows for the
     * same business-level contract. Use groupCorporateContractsByNumber()
     * when you want one entry per contract.
     *
     * @param int  $corpId
     * @param bool $includeAnnulled
     * @return ContratoCorporativo[]
     */
    public static function findCorporateContracts($corpId, $includeAnnulled = false)
    {
        $query = ContratoCorporativo::find()
            ->with(['plan', 'clinica', 'creator'])
            ->where(['corporativo_id' => $corpId])
            ->orderBy(['fecha_ini' => SORT_DESC, 'plan_id' => SORT_ASC, 'id' => SORT_ASC]);

        if (!$includeAnnulled) {
            $query->andWhere(['!=', 'estatus', ContratoCorporativo::STATUS_ANULADO]);
        }

        return $query->all();
    }

    /**
     * Load every ContratoCorporativo row that belongs to the same
     * corporation-wide contract (i.e., shares the same `nrocontrato`).
     *
     * @param  int    $corporativoId
     * @param  string $nrocontrato
     * @return ContratoCorporativo[]
     */
    public static function findCorporateContractGroup(int $corporativoId, string $nrocontrato): array
    {
        return ContratoCorporativo::find()
            ->with(['plan', 'clinica', 'creator'])
            ->where([
                'corporativo_id' => $corporativoId,
                'nrocontrato'    => $nrocontrato,
            ])
            ->orderBy(['plan_id' => SORT_ASC])
            ->all();
    }

    /**
     * Groups a flat list of ContratoCorporativo rows by `nrocontrato`,
     * so callers can present one "contract" per corporation even though
     * the DB has one row per plan.
     *
     * @param  ContratoCorporativo[] $contracts
     * @return array<string, ContratoCorporativo[]>  keyed by nrocontrato
     */
    public static function groupCorporateContractsByNumber(array $contracts): array
    {
        $grouped = [];
        foreach ($contracts as $c) {
            $key = (string)($c->nrocontrato ?: '');
            if ($key === '') {
                $key = '__row_' . (int)$c->id;
            }
            $grouped[$key][] = $c;
        }

        foreach ($grouped as &$rows) {
            usort($rows, fn($a, $b) => (int)$a->plan_id <=> (int)$b->plan_id);
        }
        unset($rows);

        return $grouped;
    }

    /**
     * All active corporate-contract rows for a corporation.
     *
     * @param  int $corpId
     * @return ContratoCorporativo[]
     */
    public static function findActiveCorporateContracts($corpId): array
    {
        $today = date('Y-m-d');

        return ContratoCorporativo::find()
            ->with(['plan', 'clinica'])
            ->where(['corporativo_id' => $corpId])
            ->andWhere(['!=', 'estatus', ContratoCorporativo::STATUS_ANULADO])
            ->andWhere(['<=', 'fecha_ini', $today])
            ->andWhere(['>=', 'fecha_ven', $today])
            ->orderBy(['plan_id' => SORT_ASC])
            ->all();
    }

    /**
     * @deprecated Use findActiveCorporateContracts() — a corporation now has
     *             one row per plan. This method returns the row with the
     *             lowest plan_id for backward compatibility only.
     *
     * @param  int      $corpId
     * @param  int|null $planId
     * @return ContratoCorporativo|null
     */
    public static function findActiveCorporateContract($corpId, $planId = null)
    {
        if ($planId !== null) {
            $today = date('Y-m-d');

            return ContratoCorporativo::find()
                ->with(['plan', 'clinica'])
                ->where(['corporativo_id' => $corpId, 'plan_id' => $planId])
                ->andWhere(['!=', 'estatus', ContratoCorporativo::STATUS_ANULADO])
                ->andWhere(['<=', 'fecha_ini', $today])
                ->andWhere(['>=', 'fecha_ven', $today])
                ->orderBy(['fecha_ini' => SORT_DESC])
                ->one();
        }

        $all = self::findActiveCorporateContracts($corpId);
        return $all[0] ?? null;
    }

    /**
     * Idempotent upsert of a corporate contract for (corporate, plan, year).
     *
     * Because a corporation now has ONE contract shared across plans, every
     * row created here shares the same `nrocontrato` produced by
     * buildCorporateContractNumber(). The year comes from $fechaIni.
     *
     * ─────────────────────────────────────────────────────────────────
     * INDIVIDUAL CONTRACTS — ONE ROW PER COVERAGE PERIOD
     * ─────────────────────────────────────────────────────────────────
     * Each affiliate gets a SEPARATE `contratos` row for each coverage
     * period. When a new-year corporate contract is created, the previous
     * period's individual contracts are LEFT UNTOUCHED (they remain
     * `Vencido`, with their own dates), and NEW individual contracts are
     * created for the new period. This preserves the affiliate's coverage
     * history and keeps cuotas/audit trails consistent.
     *
     * On re-persist (same dates), the lookup-or-create finds the existing
     * matching individual contract and reuses it — no duplicates.
     *
     * AFFILIATE SOURCE (priority):
     *   1. `$empleadosOverride` — if supplied, used verbatim.
     *      Used when creating a NEW-YEAR contract that should reuse the
     *      affiliate set of a previous (expired) contract.
     *   2. `$empleados` — the caller's own array, used by default.
     *
     * STATUS SEMANTICS (per Option A fix):
     *   - Corporate contract status is DATE-DRIVEN (Registrado / Activo /
     *     Vencido) — reflects the corporate-level relationship.
     *   - Individual contract status is INDEPENDENT — always starts as
     *     Registrado on creation and is promoted later by the normal
     *     activation flow (payment processing, manual action, etc.).
     *   - The two are NOT coupled. Re-persist does NOT overwrite an
     *     existing individual contract's status with the corporate one.
     *
     * @param \app\models\Corporativo       $corporativo
     * @param \app\models\Planes            $plan
     * @param \app\models\UserDatos[]       $empleados
     * @param string                        $fechaIni          (YYYY-MM-DD)
     * @param string                        $fechaVen          (YYYY-MM-DD)
     * @param \app\models\UserDatos[]|null  $empleadosOverride
     * @return ContratoCorporativo|null
     * @throws \InvalidArgumentException when $fechaIni is empty
     */
    public static function persistCorporateContract(
        $corporativo,
        $plan,
        array $empleados,
        $fechaIni,
        $fechaVen,
        ?array $empleadosOverride = null
    ) {
        if (empty($fechaIni)) {
            throw new \InvalidArgumentException('fecha_ini is required to persist a corporate contract.');
        }

        $year = (int) date('Y', strtotime($fechaIni));

        if (empty($fechaVen)) {
            $fechaVen = date('Y-m-d', strtotime($fechaIni . ' +1 year -1 day'));
        }

        // ── Affiliate source resolution ─────────────────────────────────
        if ($empleadosOverride !== null) {
            // Defensively re-filter to guarantee every element is a UserDatos
            $empleados = array_values(array_filter(
                $empleadosOverride,
                static fn($e) => $e instanceof UserDatos
            ));
        }

        $contract = ContratoCorporativo::find()
            ->where(['corporativo_id' => $corporativo->id, 'plan_id' => $plan->id])
            ->andWhere(['!=', 'estatus', ContratoCorporativo::STATUS_ANULADO])
            ->andWhere(['>=', 'fecha_ini', $year . '-01-01'])
            ->andWhere(['<=', 'fecha_ini', $year . '-12-31'])
            ->one();

        $precioPlan = (float)$plan->precio;
        $cantidad   = count($empleados);
        $total      = $precioPlan * $cantidad;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $isNew = false;

            if (!$contract) {
                $contract                  = new ContratoCorporativo();
                $contract->corporativo_id  = $corporativo->id;
                $contract->plan_id         = $plan->id;
                $contract->clinica_id      = $plan->clinica_id;
                $contract->nrocontrato     = self::buildCorporateContractNumber($corporativo, $year);
                $contract->created_at      = date('Y-m-d H:i:s');
                $contract->created_by      = Yii::$app->user->id ?: null;
                $isNew                     = true;
            } else {
                $contract->updated_at = date('Y-m-d H:i:s');
            }

            $contract->fecha_ini          = $fechaIni;
            $contract->fecha_ven          = $fechaVen;
            $contract->monto_por_afiliado = $precioPlan;
            $contract->cantidad_afiliados = $cantidad;
            $contract->monto_total        = $total;

            // ────────────────────────────────────────────────────────────────
            // CORPORATE CONTRACT STATUS — date-driven.
            //
            // A corporate contract whose vigencia is currently in effect IS
            // "Activo" at the corporate level. That is intentional and correct.
            // ────────────────────────────────────────────────────────────────
            $resolvedStatus = self::resolveInitialCorporateStatus($fechaIni, $fechaVen);

            if ($isNew) {
                $contract->estatus = $resolvedStatus;
            } elseif (in_array($contract->estatus, [
                ContratoCorporativo::STATUS_REGISTRADO,
                ContratoCorporativo::STATUS_ACTIVO,
                ContratoCorporativo::STATUS_VENCIDO,
            ], true)) {
                $contract->estatus = $resolvedStatus;
            }

            // ────────────────────────────────────────────────────────────────
            // INDIVIDUAL CONTRACT STATUS — always "Registrado" on creation.
            //
            // The individual affiliate contract's lifecycle is INDEPENDENT of
            // the corporate contract's lifecycle:
            //
            //   * Corporate contract status → reflects the corporate-level
            //     relationship (Activo / Vencido / Registrado).
            //
            //   * Individual contract status → reflects the affiliate's own
            //     contract. It starts as "Registrado" and is promoted later
            //     by the normal activation flow (payment processing, manual
            //     activation, or the date-based sync when that is wired up).
            //
            // Coupling the two caused every mass-uploaded affiliate to be
            // created as "Activo" the moment the corporate contract's vigencia
            // was already in effect — which is wrong: they were never
            // individually activated.
            // ────────────────────────────────────────────────────────────────
            $individualInitialStatus = Contratos::STATUS_REGISTRADO;

            if (!$contract->save(false)) {
                throw new \Exception('No se pudo guardar el contrato corporativo: '
                    . json_encode($contract->getErrors()));
            }

            // Drop stale link rows and rebuild
            ContratoCorporativoAfiliado::deleteAll([
                'contrato_corporativo_id' => $contract->id,
            ]);

            $now  = date('Y-m-d H:i:s');
            $rows = [];

            foreach ($empleados as $emp) {

                // ─────────────────────────────────────────────────────
                // LOOKUP-OR-CREATE by exact vigencia match.
                //
                // Old behaviour (bug):
                //   *picked the most recent non-annulled row and mutated
                //    its dates/status, erasing the previous period.*
                //
                // New behaviour:
                //   *find a row with the SAME (user_id, fecha_ini, fecha_ven).
                //    If one exists → reuse (idempotent re-persist).
                //    If none exists → create a fresh row for the new period,
                //    leaving old rows untouched.*
                // ─────────────────────────────────────────────────────
                $individualContract = Contratos::find()
                    ->where(['user_id'   => $emp->id])
                    ->andWhere(['fecha_ini' => $fechaIni])
                    ->andWhere(['fecha_ven' => $fechaVen])
                    ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                    ->one();

                $isNewIndividual = false;

                if (!$individualContract) {
                    // ── Guard: an annulled contract with the SAME vigencia already exists.
                    //    That means the affiliate was deliberately given de baja for this
                    //    period. Do NOT re-create the contract, do NOT regenerate cuotas,
                    //    and do NOT link them to the corporate contract group.
                    $annulledSameVigencia = Contratos::find()
                        ->where(['user_id'    => $emp->id])
                        ->andWhere(['fecha_ini' => $fechaIni])
                        ->andWhere(['fecha_ven' => $fechaVen])
                        ->andWhere(['=', 'estatus', Contratos::STATUS_ANULADO])
                        ->exists();

                    if ($annulledSameVigencia) {
                        Yii::warning(
                            "persistCorporateContract: skipping affiliate {$emp->id} — "
                                . "an annulled contract for vigencia {$fechaIni} → {$fechaVen} "
                                . "already exists (baja deliberada). Not recreating.",
                            'contrato-corporativo'
                        );
                        continue;
                    }

                    $individualContract = new Contratos();
                    $individualContract->user_id    = $emp->id;
                    $individualContract->plan_id    = $plan->id;
                    $individualContract->clinica_id = $plan->clinica_id;
                    $individualContract->monto      = $precioPlan;
                    $individualContract->fecha_ini  = $fechaIni;
                    $individualContract->fecha_ven  = $fechaVen;

                    // ★ FIX: always create fresh individual contracts as
                    //   "Registrado", regardless of the corporate contract's
                    //   own status.
                    $individualContract->estatus    = $individualInitialStatus;

                    $individualContract->created_at = $now;
                    $isNewIndividual                = true;
                }

                // ★ REMOVED: the block that mirrored the corporate contract's
                //   status onto the individual row on re-persist. The two
                //   lifecycles are now fully decoupled.

                if (!$individualContract->save(false)) {
                    throw new \Exception(
                        'No se pudo guardar el contrato individual para cédula '
                            . $emp->cedula . ': ' . json_encode($individualContract->getErrors())
                    );
                }

                // Set the contract number on create (needs the id, so a
                // second save is required).
                if ($isNewIndividual && empty($individualContract->nrocontrato)) {
                    $individualContract->nrocontrato =
                        $emp->cedula . '-' . $year . '-' . $individualContract->id;
                    $individualContract->save(false);
                }

                // Generate the 12 cuotas ONLY when the individual contract
                // is fresh. Re-persisting must never regenerate cuotas —
                // existing ones may have been paid.
                if ($isNewIndividual) {
                    $cuotaResult = Cuotas::generateCuotasAnniversaryBased(
                        $individualContract->id,
                        $fechaIni,
                        $precioPlan
                    );

                    if (!$cuotaResult['success']) {
                        throw new \Exception(
                            'Error generando cuotas para cédula ' . $emp->cedula
                                . ': ' . ($cuotaResult['error'] ?? 'desconocido')
                        );
                    }

                    Yii::info(
                        "Created individual contract #{$individualContract->id} "
                            . "for cédula {$emp->cedula}, "
                            . "vigencia {$fechaIni} → {$fechaVen}, "
                            . "cuotas generadas: " . count($cuotaResult['cuotas']),
                        'contrato-corporativo'
                    );
                }

                $rows[] = [
                    $contract->id,
                    $emp->id,
                    $individualContract->id,
                    $precioPlan,
                    $now,
                ];
            }

            if (!empty($rows)) {
                Yii::$app->db->createCommand()->batchInsert(
                    ContratoCorporativoAfiliado::tableName(),
                    ['contrato_corporativo_id', 'user_datos_id', 'contrato_individual_id', 'precio_individual', 'created_at'],
                    $rows
                )->execute();
            }

            $transaction->commit();

            Yii::info(
                ($isNew ? 'Created' : 'Updated') . " corporate contract #{$contract->id} "
                    . "({$contract->nrocontrato}) for corporate #{$corporativo->id}, "
                    . "plan #{$plan->id}, affiliates={$cantidad}, "
                    . "vigencia {$fechaIni} → {$fechaVen}",
                'contrato-corporativo'
            );

            return $contract;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('persistCorporateContract failed: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Annuls a corporation-wide contract.
     *
     * Because the DB stores one row per plan (all sharing the same
     * `nrocontrato`), this method flips every sibling row, plus every
     * individual contract linked to any of them, and cancels their cuotas.
     *
     * @param int    $contractId  Any row of the group — the group is resolved
     *                            via its `nrocontrato`.
     * @param string $motivo
     * @return bool
     */
    public static function annulCorporateContract($contractId, $motivo)
    {
        $anchor = ContratoCorporativo::findOne($contractId);
        if (!$anchor) {
            return false;
        }

        if ($anchor->estatus === ContratoCorporativo::STATUS_ANULADO) {
            return false;
        }

        $group = self::findCorporateContractGroup($anchor->corporativo_id, $anchor->nrocontrato);
        if (empty($group)) {
            $group = [$anchor];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $now       = date('Y-m-d H:i:s');
            $currentId = Yii::$app->user->id ?: null;

            $groupIds = array_map(fn($c) => (int)$c->id, $group);

            foreach ($group as $cc) {
                if ($cc->estatus === ContratoCorporativo::STATUS_ANULADO) {
                    continue;
                }

                $cc->estatus        = ContratoCorporativo::STATUS_ANULADO;
                $cc->anulado_por    = $currentId;
                $cc->anulado_fecha  = $now;
                $cc->anulado_motivo = $motivo;
                $cc->updated_at     = $now;

                if (!$cc->save(false)) {
                    throw new \Exception("No se pudo anular el contrato corporativo #{$cc->id}.");
                }
            }

            $individualIds = ContratoCorporativoAfiliado::find()
                ->select('contrato_individual_id')
                ->where(['contrato_corporativo_id' => $groupIds])
                ->andWhere(['IS NOT', 'contrato_individual_id', null])
                ->column();

            $childrenUpdated = 0;

            if (!empty($individualIds)) {
                $children = Contratos::find()->where(['id' => $individualIds])->all();

                foreach ($children as $child) {
                    $child->estatus        = Contratos::STATUS_ANULADO;
                    $child->anulado_por    = $currentId;
                    $child->anulado_fecha  = $now;
                    $child->anulado_motivo = 'Anulado por anulación del contrato corporativo '
                        . $anchor->nrocontrato . ': ' . $motivo;
                    $child->updated_at     = $now;

                    if ($child->save(false)) {
                        $childrenUpdated++;
                    }
                }

                Cuotas::updateAll(
                    ['estatus' => 'cancelada'],
                    [
                        'and',
                        ['contrato_id' => $individualIds],
                        ['in', 'estatus', ['pendiente', 'en_gracias', 'vencida']],
                    ]
                );
            }
            // ── Unlink affiliates that no longer have any live contract ──
            if (!empty($individualIds)) {
                $orphanUserIds = Contratos::find()
                    ->select('user_id')
                    ->where(['id' => $individualIds])
                    ->column();

                foreach (array_unique($orphanUserIds) as $uid) {
                    $stillLive = Contratos::find()
                        ->where(['user_id' => $uid])
                        ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                        ->exists();

                    if ($stillLive) {
                        continue;
                    }

                    UserDatos::updateAll(
                        [
                            'afiliado_corporativo_id' => null,
                            'user_datos_type_id'      => 1,
                            'estatus_solvente'        => 'No',
                            'updated_at'              => $now,
                        ],
                        [
                            'id'                      => $uid,
                            'afiliado_corporativo_id' => $anchor->corporativo_id,
                        ]
                    );

                    CorporativoUser::deleteAll([
                        'corporativo_id' => $anchor->corporativo_id,
                        'user_id'        => $uid,
                    ]);
                }
            }

            self::refreshCorporateSolvency($anchor->corporativo_id);

            $transaction->commit();

            Yii::info(
                "Annulled corporate contract group {$anchor->nrocontrato} "
                    . "(rows: " . implode(',', $groupIds) . "), {$childrenUpdated} children",
                'contrato-corporativo'
            );

            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('annulCorporateContract failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Recompute a corporate's estatus_solvente based on active contracts.
     */
    public static function refreshCorporateSolvency($corpId)
    {
        $hasActive = ContratoCorporativo::find()
            ->where(['corporativo_id' => $corpId])
            ->andWhere(['!=', 'estatus', ContratoCorporativo::STATUS_ANULADO])
            ->exists();

        $affiliateIds = ContratoCorporativoAfiliado::find()
            ->alias('cca')
            ->select('cca.user_datos_id')
            ->innerJoin('contratos_corporativos cc', 'cc.id = cca.contrato_corporativo_id')
            ->where(['cc.corporativo_id' => $corpId])
            ->column();

        if (!empty($affiliateIds)) {
            UserDatos::updateAll(
                ['estatus_solvente' => $hasActive ? 'Si' : 'No'],
                ['id' => $affiliateIds]
            );
        }
    }

    // ============================================================
    // 4. CORPORATE CONTRACT LIFECYCLE
    // ============================================================

    /**
     * Flips Registrado→Activo→Vencido based on today's date, then synchronizes
     * the status ACROSS siblings that share a `nrocontrato` — so all plans of
     * the same corporation contract move together.
     *
     * Status priority (most restrictive wins):
     *   Vencido > Suspendido > Activo > Registrado
     *
     * @param  int|null $corpId
     * @param  bool     $dryRun
     * @return array ['activated'=>int,'expired'=>int,'children'=>int,'details'=>array]
     */
    public static function syncCorporateContractStatuses($corpId = null, $dryRun = false)
    {
        $today = date('Y-m-d');
        $now   = date('Y-m-d H:i:s');

        $query = ContratoCorporativo::find()
            ->where(['!=', 'estatus', ContratoCorporativo::STATUS_ANULADO])
            ->andWhere(['!=', 'estatus', ContratoCorporativo::STATUS_SUSPENDIDO]);

        if ($corpId !== null) {
            $query->andWhere(['corporativo_id' => (int)$corpId]);
        }

        $contracts = $query->all();

        $activated = 0;
        $expired   = 0;
        $children  = 0;
        $details   = [];

        $groups = self::groupCorporateContractsByNumber($contracts);

        $priority = [
            ContratoCorporativo::STATUS_REGISTRADO => 1,
            ContratoCorporativo::STATUS_ACTIVO     => 2,
            ContratoCorporativo::STATUS_SUSPENDIDO => 3,
            ContratoCorporativo::STATUS_VENCIDO    => 4,
        ];

        foreach ($groups as $nrocontrato => $rows) {

            $groupWinner = null;
            foreach ($rows as $cc) {
                $resolved = self::resolveCorporateStatus($cc, $today);
                if ($resolved === null) {
                    continue;
                }

                if (
                    $groupWinner === null
                    || $priority[$resolved] > $priority[$groupWinner]
                ) {
                    $groupWinner = $resolved;
                }
            }

            if ($groupWinner === null) {
                continue;
            }

            foreach ($rows as $cc) {
                $oldStatus = $cc->estatus;

                if ($oldStatus === $groupWinner) {
                    continue;
                }

                if ($groupWinner === ContratoCorporativo::STATUS_ACTIVO) {
                    $activated++;
                } elseif ($groupWinner === ContratoCorporativo::STATUS_VENCIDO) {
                    $expired++;
                }

                $details[] = "Contract {$nrocontrato} plan #{$cc->plan_id} "
                    . "(row #{$cc->id}): {$oldStatus} → {$groupWinner}";

                if (!$dryRun) {
                    $cc->estatus    = $groupWinner;
                    $cc->updated_at = $now;
                    if (!$cc->save(false)) {
                        Yii::error(
                            "Failed to save corporate contract #{$cc->id}: "
                                . json_encode($cc->getErrors()),
                            __METHOD__
                        );
                        continue;
                    }
                }

                $individualIds = ContratoCorporativoAfiliado::find()
                    ->select('contrato_individual_id')
                    ->where(['contrato_corporativo_id' => $cc->id])
                    ->andWhere(['IS NOT', 'contrato_individual_id', null])
                    ->column();

                if (empty($individualIds)) {
                    continue;
                }

                foreach ($individualIds as $childId) {
                    $child = Contratos::findOne($childId);
                    if (!$child) {
                        continue;
                    }

                    if (in_array(
                        $child->estatus,
                        [Contratos::STATUS_ANULADO, Contratos::STATUS_SUSPENDIDO],
                        true
                    )) {
                        continue;
                    }

                    if ($child->estatus === $groupWinner) {
                        continue;
                    }

                    if (!$dryRun) {
                        $child->estatus    = $groupWinner;
                        $child->updated_at = $now;
                        if (!$child->save(false)) {
                            Yii::error("Failed to save child contract #{$childId}", __METHOD__);
                            continue;
                        }
                    }

                    $children++;
                }
            }
        }

        return [
            'activated' => $activated,
            'expired'   => $expired,
            'children'  => $children,
            'details'   => $details,
        ];
    }

    /**
     * Resolve the correct status for a corporate contract given today's date.
     */
    private static function resolveCorporateStatus(ContratoCorporativo $cc, string $today): ?string
    {
        $ini = $cc->fecha_ini;
        $ven = $cc->fecha_ven;

        if (empty($ini) && empty($ven)) {
            return null;
        }

        if (!empty($ven) && $today > $ven) {
            return ContratoCorporativo::STATUS_VENCIDO;
        }

        if (!empty($ini) && !empty($ven) && $today >= $ini && $today <= $ven) {
            return ContratoCorporativo::STATUS_ACTIVO;
        }

        if (!empty($ini) && $today < $ini) {
            return ContratoCorporativo::STATUS_REGISTRADO;
        }

        if (!empty($ini) && empty($ven) && $today >= $ini) {
            return ContratoCorporativo::STATUS_ACTIVO;
        }

        return null;
    }

    /**
     * Resolve the INITIAL status of a corporate contract at creation time.
     *
     * Mirrors the rules used by resolveCorporateStatus() during sync, but
     * applies them immediately so a freshly created contract doesn't linger
     * as "Registrado" when its fecha_ini is already in the past.
     *
     *   today >  fecha_ven                  → Vencido
     *   fecha_ini <= today <= fecha_ven     → Activo
     *   today <  fecha_ini                  → Registrado
     *   no dates                            → Registrado (safe default)
     *
     * @param  string|null $fechaIni
     * @param  string|null $fechaVen
     * @return string
     */
    private static function resolveInitialCorporateStatus($fechaIni, $fechaVen): string
    {
        $today = date('Y-m-d');

        if (empty($fechaIni) && empty($fechaVen)) {
            return ContratoCorporativo::STATUS_REGISTRADO;
        }

        if (!empty($fechaVen) && $today > $fechaVen) {
            return ContratoCorporativo::STATUS_VENCIDO;
        }

        if (!empty($fechaIni) && $today < $fechaIni) {
            return ContratoCorporativo::STATUS_REGISTRADO;
        }

        return ContratoCorporativo::STATUS_ACTIVO;
    }
    // In ContractHelper.php
    public static function buildContractGroupSummaries(array $flatContracts): array
    {
        $grouped = self::groupCorporateContractsByNumber($flatContracts);
        $out = [];

        foreach ($grouped as $nrocontrato => $rows) {
            $anchor = $rows[0];

            $totalAffiliates = 0;
            $totalAmount     = 0.0;
            $planNames       = [];
            $planCounts      = [];

            foreach ($rows as $row) {
                $totalAffiliates += (int)$row->cantidad_afiliados;
                $totalAmount     += (float)$row->monto_total;

                if ($row->plan) {
                    $planName = $row->plan->nombre;
                    $planNames[] = $planName;
                    $planCounts[$planName] = (int)$row->cantidad_afiliados;
                }
            }

            $out[] = [
                'nrocontrato'      => $nrocontrato,
                'anchor'           => $anchor,
                'rows'             => $rows,
                'plan_names'       => $planNames,
                'plan_counts'      => $planCounts,
                'total_affiliates' => $totalAffiliates,
                'total_amount'     => $totalAmount,
                'is_annulled'      => $anchor->estatus === ContratoCorporativo::STATUS_ANULADO,
            ];
        }

        return $out;
    }
}
