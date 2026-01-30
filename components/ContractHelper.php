<?php

namespace app\components;

use Yii;
use yii\helpers\Html;
use app\models\Contratos;
use app\models\Cuotas;
use app\models\UserDatos;

/**
 * ContractHelper provides helper methods for contract-related functionality
 */
class ContractHelper
{
    /**
     * Get CSS class for contract status badge
     * 
     * @param string $status
     * @return string
     */
    public static function getContractStatusClass($status)
    {
        $status = strtolower($status);
        $classes = [
            'registrado' => 'badge badge-primary',
            'activo' => 'badge badge-success',
            'anulado' => 'badge badge-danger',
            'vencido' => 'badge badge-warning',
            'pendiente' => 'badge badge-info',
            'suspendido' => 'badge badge-secondary',
        ];

        return $classes[$status] ?? 'badge badge-light';
    }

    /**
     * Get icon for contract status
     * 
     * @param string $status
     * @return string
     */
    public static function getStatusIcon($status)
    {
        $status = strtolower($status);
        $icons = [
            'registrado' => '📋',
            'activo' => '✅',
            'anulado' => '❌',
            'vencido' => '⏰',
            'pendiente' => '⏳',
            'suspendido' => '⏸️',
        ];

        return $icons[$status] ?? '📄';
    }

    /**
     * Generate detailed tooltip for contracts
     * 
     * @param Contratos $contrato
     * @return string HTML tooltip
     */
    public static function generateContractTooltip($contrato)
    {
        if (!$contrato) {
            return 'Contrato no disponible';
        }

        $status = strtolower($contrato->estatus);
        $today = date('Y-m-d');

        // Base contract info
        $tooltip = "<div class='contract-tooltip'>";
        $tooltip .= "<strong>Contrato #{$contrato->id}</strong><br>";

        if ($contrato->nrocontrato) {
            $tooltip .= "Número: <strong>{$contrato->nrocontrato}</strong><br>";
        }

        $tooltip .= "Inicio: " . Yii::$app->formatter->asDate($contrato->fecha_ini, 'd/M/Y') . "<br>";

        if ($contrato->fecha_ven) {
            $vencimiento = Yii::$app->formatter->asDate($contrato->fecha_ven, 'd/M/Y');
            $tooltip .= "Vence: <strong>{$vencimiento}</strong><br>";

            // Calculate days remaining/overdue
            $daysDiff = floor((strtotime($contrato->fecha_ven) - strtotime($today)) / (60 * 60 * 24));
            if ($daysDiff > 0) {
                $tooltip .= "<small>Vence en <strong>{$daysDiff} días</strong></small><br>";
            } elseif ($daysDiff < 0) {
                $tooltip .= "<small>Vencido hace <strong>" . abs($daysDiff) . " días</strong></small><br>";
            }
        }

        $tooltip .= "Monto: <strong>$" . number_format($contrato->monto, 2) . "</strong><br>";

        // Status-specific explanations
        $tooltip .= "<hr style='margin: 5px 0; border-color: #ccc;'>";

        switch ($status) {
            case 'activo':
                $tooltip .= "<div style='color: #28a745;'><strong>✓ CONTRATO ACTIVO</strong></div>";
                $tooltip .= "<small>El afiliado tiene acceso completo a los servicios médicos.</small>";
                break;

            case 'registrado':
                $tooltip .= "<div style='color: #007bff;'><strong>📝 CONTRATO REGISTRADO</strong></div>";
                $tooltip .= "<small>Contrato registrado pero aún no activo. Se activará automáticamente en la fecha de inicio.</small>";
                break;

            case 'vencido':
                $tooltip .= "<div style='color: #ffc107;'><strong>⏰ CONTRATO VENCIDO</strong></div>";
                $tooltip .= "<small>La fecha de vencimiento ha pasado. El afiliado ya no tiene acceso a los servicios.</small><br>";
                $tooltip .= "<small><em>Acción requerida: Renovar o generar nuevo contrato.</em></small>";
                break;

            case 'suspendido':
                $tooltip .= "<div style='color: #6c757d;'><strong>⏸️ CONTRATO SUSPENDIDO</strong></div>";
                $tooltip .= "<small>Contrato suspendido por falta de pago o incumplimiento.</small><br>";

                // Check for pending cuotas
                $pendingCuotas = Cuotas::find()
                    ->where(['contrato_id' => $contrato->id])
                    ->andWhere(['estatus' => 'pendiente'])
                    ->count();

                if ($pendingCuotas > 0) {
                    $tooltip .= "<small><strong>{$pendingCuotas} cuota(s) pendiente(s)</strong></small><br>";
                }

                $tooltip .= "<small><em>Acción requerida: Regularizar pagos pendientes.</em></small>";
                break;

            case 'anulado':
                $tooltip .= "<div style='color: #dc3545;'><strong>❌ CONTRATO ANULADO</strong></div>";
                $tooltip .= "<small>Contrato cancelado permanentemente.</small><br>";

                if ($contrato->anulado_fecha) {
                    $tooltip .= "<small>Fecha de anulación: " . Yii::$app->formatter->asDate($contrato->anulado_fecha, 'd/M/Y') . "</small><br>";
                }

                if ($contrato->anulado_motivo) {
                    $tooltip .= "<small>Motivo: <em>{$contrato->anulado_motivo}</em></small><br>";
                }

                $tooltip .= "<small><em>No se pueden realizar acciones sobre este contrato.</em></small>";
                break;

            case 'pendiente':
                $tooltip .= "<div style='color: #17a2b8;'><strong>⏳ CONTRATO PENDIENTE</strong></div>";
                $tooltip .= "<small>Contrato en proceso de aprobación o con documentación pendiente.</small><br>";
                $tooltip .= "<small><em>Acción requerida: Completar proceso de aprobación.</em></small>";
                break;

            default:
                $tooltip .= "<div><strong>Estado: " . ucfirst($status) . "</strong></div>";
                break;
        }

        // Add plan info if available
        if ($contrato->plan) {
            $tooltip .= "<hr style='margin: 5px 0; border-color: #ccc;'>";
            $tooltip .= "<small>Plan: <strong>{$contrato->plan->nombre}</strong></small><br>";
            $tooltip .= "<small>Clínica: <strong>" . ($contrato->clinica ? $contrato->clinica->nombre : 'N/A') . "</strong></small>";
        }

        $tooltip .= "</div>";

        return htmlspecialchars_decode($tooltip, ENT_QUOTES);
    }

    /**
     * Generate tooltip for "No Contract"
     * 
     * @param \app\models\UserDatos $user
     * @return string HTML tooltip
     */
    public static function generateNoContractTooltip($user)
    {
        $tooltip = "<div class='contract-tooltip'>";
        $tooltip .= "<strong>AFILIADO SIN CONTRATO</strong><br>";
        $tooltip .= "Nombre: <strong>{$user->nombres} {$user->apellidos}</strong><br>";
        $tooltip .= "Cédula: <strong>{$user->tipo_cedula}-{$user->cedula}</strong><br>";
        $tooltip .= "<hr style='margin: 5px 0; border-color: #ccc;'>";
        $tooltip .= "<div style='color: #6c757d;'><strong>⚠️ SITUACIÓN ACTUAL</strong></div>";
        $tooltip .= "<small>Este afiliado no tiene ningún contrato activo o válido registrado en el sistema.</small><br><br>";
        $tooltip .= "<small><strong>Posibles causas:</strong></small><br>";
        $tooltip .= "<small>• Contrato aún no creado</small><br>";
        $tooltip .= "<small>• Todos los contratos están anulados</small><br>";
        $tooltip .= "<small>• Error en el registro del contrato</small><br><br>";
        $tooltip .= "<small><em>Acción requerida: Crear nuevo contrato para este afiliado.</em></small>";
        $tooltip .= "</div>";

        return $tooltip;
    }

    /**
     * Generate contract status badge with tooltip
     * 
     * @param \app\models\UserDatos $user
     * @return string HTML badge
     */
    public static function generateContractStatusBadge($user)
    {
        // Get the most recent active contract for this affiliate
        $contrato = Contratos::getContratoActivo($user->id);

        if (!$contrato) {
            // If no active contract, try to get any valid contract
            $contratosValidos = Contratos::getContratosValidos($user->id);
            if (!empty($contratosValidos)) {
                $contrato = $contratosValidos[0]; // Get the most recent one
            }
        }

        if ($contrato) {
            // Create detailed tooltip based on contract status
            $tooltip = self::generateContractTooltip($contrato);

            // Get status display text and class
            $statusText = $contrato->getStatusLabel();
            $statusClass = self::getContractStatusClass($contrato->estatus);

            // Add status indicator icon
            $statusIcon = self::getStatusIcon($contrato->estatus);

            // Return badge with detailed tooltip
            return Html::tag(
                'div',
                Html::tag('span', $statusIcon . ' ' . $statusText, [
                    'class' => $statusClass,
                    'title' => $tooltip,
                    'data-toggle' => 'tooltip',
                    'data-html' => 'true',
                    'data-placement' => 'top',
                    'style' => 'cursor: help; white-space: nowrap;'
                ]),
                ['style' => 'display: inline-block;']
            );
        }

        // No contract found
        return self::generateNoContractBadge($user);
    }

    /**
     * Generate "No Contract" badge
     * 
     * @param \app\models\UserDatos $user
     * @return string HTML badge
     */
    public static function generateNoContractBadge($user)
    {
        return Html::tag('span', '📭 Sin Contrato', [
            'class' => 'badge badge-light',
            'title' => self::generateNoContractTooltip($user),
            'data-toggle' => 'tooltip',
            'data-html' => 'true',
            'data-placement' => 'top',
            'style' => 'cursor: help;'
        ]);
    }

    /**
     * Get contract status cell CSS classes
     * 
     * @param \app\models\UserDatos $user
     * @return array
     */
    public static function getContractStatusCellClasses($user)
    {
        $contrato = Contratos::getContratoActivo($user->id);
        if ($contrato) {
            return [
                'style' => 'text-align: center; padding: 10 !important;',
                'class' => 'contract-status-cell ' . strtolower($contrato->estatus)
            ];
        }
        return ['style' => 'text-align: center; padding: 10 !important;'];
    }

    /**
     * Generate contract status badge from status string (for filter compatibility)
     * 
     * @param string $status
     * @param \app\models\UserDatos|null $user
     * @return string HTML badge
     */
    public static function generateContractStatusBadgeFromStatus($status, $user = null)
    {
        if (empty($status) || $status === 'sin_contrato') {
            if ($user) {
                return self::generateNoContractBadge($user);
            }
            return Html::tag('span', '📭 Sin Contrato', [
                'class' => 'badge badge-light',
                'title' => 'Afiliado sin contrato activo',
                'data-toggle' => 'tooltip',
                'data-html' => 'true',
                'data-placement' => 'top',
                'style' => 'cursor: help;'
            ]);
        }

        $statusClass = self::getContractStatusClass($status);
        $statusIcon = self::getStatusIcon($status);
        $statusLabel = self::getStatusLabel($status);

        return Html::tag('span', $statusIcon . ' ' . $statusLabel, [
            'class' => $statusClass,
            'title' => self::getStatusTooltip($status),
            'data-toggle' => 'tooltip',
            'data-html' => 'true',
            'data-placement' => 'top',
            'style' => 'cursor: help; white-space: nowrap;'
        ]);
    }

    /**
     * Get status label for display
     * 
     * @param string $status
     * @return string
     */
    public static function getStatusLabel($status)
    {
        $labels = [
            'activo' => 'Activo',
            'registrado' => 'Registrado',
            'suspendido' => 'Suspendido',
            'vencido' => 'Vencido',
            'pendiente' => 'Pendiente',
            'anulado' => 'Anulado',
        ];

        return $labels[strtolower($status)] ?? ucfirst($status);
    }

    /**
     * Get status tooltip
     * 
     * @param string $status
     * @return string HTML tooltip
     */
    public static function getStatusTooltip($status)
    {
        $status = strtolower($status);
        $tooltips = [
            'activo' => '✅ <strong>CONTRATO ACTIVO</strong><br><small>El afiliado tiene acceso completo a los servicios médicos.</small>',
            'registrado' => '📋 <strong>CONTRATO REGISTRADO</strong><br><small>Contrato registrado pero aún no activo. Se activará automáticamente en la fecha de inicio.</small>',
            'suspendido' => '⏸️ <strong>CONTRATO SUSPENDIDO</strong><br><small>Contrato suspendido por falta de pago o incumplimiento.</small>',
            'vencido' => '⏰ <strong>CONTRATO VENCIDO</strong><br><small>La fecha de vencimiento ha pasado. El afiliado ya no tiene acceso a los servicios.</small>',
            'pendiente' => '⏳ <strong>CONTRATO PENDIENTE</strong><br><small>Contrato en proceso de aprobación o con documentación pendiente.</small>',
            'anulado' => '❌ <strong>CONTRATO ANULADO</strong><br><small>Contrato cancelado permanentemente.</small>',
        ];

        return $tooltips[$status] ?? 'Estado: ' . ucfirst($status);
    }

    /**
     * Get contract status options for dropdown filter
     * 
     * @return array
     */
    public static function getFilterOptions()
    {
        return [
            'activo' => '✅ Activo',
            'registrado' => '📋 Registrado',
            'suspendido' => '⏸️ Suspendido',
            'vencido' => '⏰ Vencido',
            'pendiente' => '⏳ Pendiente',
            'anulado' => '❌ Anulado',
            'sin_contrato' => '📭 Sin Contrato'
        ];
    }

    /**
     * Get contract status options with raw values (for Select2)
     * 
     * @return array
     */
    public static function getFilterOptionsRaw()
    {
        return [
            'activo' => 'Activo',
            'registrado' => 'Registrado',
            'suspendido' => 'Suspendido',
            'vencido' => 'Vencido',
            'pendiente' => 'Pendiente',
            'anulado' => 'Anulado',
            'sin_contrato' => 'Sin Contrato'
        ];
    }

    /**
     * Get contract status color for CSS
     * 
     * @param string $status
     * @return string CSS color
     */
    public static function getStatusColor($status)
    {
        $status = strtolower($status);
        $colors = [
            'activo' => '#28a745',
            'registrado' => '#007bff',
            'suspendido' => '#6c757d',
            'vencido' => '#ffc107',
            'pendiente' => '#17a2b8',
            'anulado' => '#dc3545',
        ];

        return $colors[$status] ?? '#6c757d';
    }

    /**
     * Check if contract status is problematic (needs attention)
     * 
     * @param string $status
     * @return bool
     */
    public static function isProblematicStatus($status)
    {
        $status = strtolower($status);
        $problematic = ['suspendido', 'vencido', 'anulado'];

        return in_array($status, $problematic);
    }

    /**
     * Check if contract status is active/valid
     * 
     * @param string $status
     * @return bool
     */
    public static function isActiveStatus($status)
    {
        $status = strtolower($status);
        $active = ['activo', 'registrado'];

        return in_array($status, $active);
    }

    /**
     * Get status priority for sorting
     * 
     * @param string $status
     * @return int
     */
    public static function getStatusPriority($status)
    {
        $status = strtolower($status);
        $priority = [
            'suspendido' => 1,    // Highest priority (needs immediate attention)
            'vencido' => 2,
            'anulado' => 3,
            'pendiente' => 4,
            'registrado' => 5,
            'activo' => 6,        // Lowest priority (everything is fine)
        ];

        return $priority[$status] ?? 99;
    }
}
