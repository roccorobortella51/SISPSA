<?php
// controllers/ReporteVentasController.php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\data\ArrayDataProvider;
use app\models\Cuotas;
use app\models\Contratos;
use app\models\RmClinica;
use app\models\UserDatos;
use app\models\Planes;
use app\components\UserHelper;

class ReporteVentasController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionCuotasSeguimiento()
    {
        $request = Yii::$app->request;
        $filtro = $request->get('filtro', 'todas');
        $clinicaId = $request->get('clinica_id');
        $search = $request->get('search');

        $today = date('Y-m-d');
        $todayMinus7 = date('Y-m-d', strtotime('-7 days'));
        $todayPlus3 = date('Y-m-d', strtotime('+3 days'));
        $todayPlus7 = date('Y-m-d', strtotime('+7 days'));

        // Get all cuotas with their relations
        $query = Cuotas::find()
            ->with(['contrato', 'contrato.user', 'contrato.plan', 'contrato.clinica'])
            ->where(['in', 'cuotas.estatus', ['pendiente', 'en_gracias', 'vencida']])
            ->joinWith(['contrato c'])
            ->andWhere(['!=', 'c.estatus', Contratos::STATUS_ANULADO])
            ->andWhere(['c.deleted_at' => null]);

        // Apply clinic filtering
        if ($clinicaId) {
            $query->andWhere(['c.clinica_id' => $clinicaId]);
        } elseif (UserHelper::hasClinicAccess()) {
            $userClinicId = UserHelper::getMyClinicaId();
            if ($userClinicId) {
                $query->andWhere(['c.clinica_id' => $userClinicId]);
            }
        }

        $cuotas = $query->orderBy(['cuotas.fecha_vencimiento' => SORT_ASC])->all();

        // Group by user_id
        $grouped = [];
        foreach ($cuotas as $cuota) {
            $userId = $cuota->contrato->user_id;
            $fechaVencimiento = $cuota->fecha_vencimiento;
            $monto = $cuota->monto_usd ?? $cuota->monto ?? 0;
            $numeroCuota = $cuota->numero_cuota;
            $estatus = $cuota->estatus;

            $mesAnio = $fechaVencimiento ? date('F Y', strtotime($fechaVencimiento)) : '';

            if (!isset($grouped[$userId])) {
                $user = $cuota->contrato->user;
                $clinica = $cuota->contrato->clinica;
                $plan = $cuota->contrato->plan;

                $grouped[$userId] = [
                    'user_id' => $userId,
                    'user_nombres' => $user->nombres ?? 'N/A',
                    'user_apellidos' => $user->apellidos ?? 'N/A',
                    'tipo_cedula' => $user->tipo_cedula ?? 'V',
                    'cedula' => $user->cedula ?? 'N/A',
                    'telefono' => $user->telefono ?? 'N/A',
                    'email' => $user->email ?? 'N/A',
                    'clinica_nombre' => $clinica->nombre ?? 'N/A',
                    'plan_nombre' => $plan->nombre ?? 'N/A',
                    'contrato_id' => $cuota->contrato->id,
                    'nrocontrato' => $cuota->contrato->nrocontrato,
                    'estatus_solvente' => $user->estatus_solvente ?? 'N/A',
                    'criticas_list' => [],
                    'gracia_list' => [],
                    'por_vencer_3d_list' => [],
                    'por_vencer_7d_list' => [],
                    'criticas_count' => 0,
                    'gracia_count' => 0,
                    'por_vencer_3d_count' => 0,
                    'por_vencer_7d_count' => 0,
                    'total_adeudado' => 0,
                    'total_pendiente' => 0,
                ];
            }

            // ============================================================
            // CLASSIFY CUOTA - FIXED: en_gracias goes to grace category
            // ============================================================

            if ($estatus == 'vencida') {
                // VENCIDA cuotas - already expired
                if ($fechaVencimiento < $todayMinus7) {
                    // Critical: more than 7 days overdue
                    $grouped[$userId]['criticas_count']++;
                    $grouped[$userId]['total_adeudado'] += $monto;
                    $grouped[$userId]['criticas_list'][] = [
                        'numero' => $numeroCuota,
                        'mes_anio' => $mesAnio,
                        'monto' => $monto,
                        'dias_vencidos' => (strtotime($today) - strtotime($fechaVencimiento)) / 86400,
                    ];
                } else {
                    // Grace period: 0-7 days overdue
                    $grouped[$userId]['gracia_count']++;
                    $grouped[$userId]['total_adeudado'] += $monto;
                    $grouped[$userId]['gracia_list'][] = [
                        'numero' => $numeroCuota,
                        'mes_anio' => $mesAnio,
                        'monto' => $monto,
                        'dias_vencidos' => (strtotime($today) - strtotime($fechaVencimiento)) / 86400,
                    ];
                }
            } elseif ($estatus == 'en_gracias') {
                // EN_GRACIAS cuotas - these are already expired but within grace period
                // They should go to grace category, NOT pending
                $grouped[$userId]['gracia_count']++;
                $grouped[$userId]['total_adeudado'] += $monto;
                $grouped[$userId]['gracia_list'][] = [
                    'numero' => $numeroCuota,
                    'mes_anio' => $mesAnio,
                    'monto' => $monto,
                    'dias_vencidos' => (strtotime($today) - strtotime($fechaVencimiento)) / 86400,
                ];
            } elseif ($estatus == 'pendiente') {
                // PENDIENTE cuotas - not yet due
                if ($fechaVencimiento <= $todayPlus3 && $fechaVencimiento >= $today) {
                    $grouped[$userId]['por_vencer_3d_count']++;
                    $grouped[$userId]['total_pendiente'] += $monto;
                    $grouped[$userId]['por_vencer_3d_list'][] = [
                        'numero' => $numeroCuota,
                        'mes_anio' => $mesAnio,
                        'monto' => $monto,
                        'dias_restantes' => (strtotime($fechaVencimiento) - strtotime($today)) / 86400,
                    ];
                } elseif ($fechaVencimiento <= $todayPlus7 && $fechaVencimiento > $todayPlus3) {
                    $grouped[$userId]['por_vencer_7d_count']++;
                    $grouped[$userId]['total_pendiente'] += $monto;
                    $grouped[$userId]['por_vencer_7d_list'][] = [
                        'numero' => $numeroCuota,
                        'mes_anio' => $mesAnio,
                        'monto' => $monto,
                        'dias_restantes' => (strtotime($fechaVencimiento) - strtotime($today)) / 86400,
                    ];
                }
            }
        }

        $results = array_values($grouped);

        // Determine priority (for row coloring only - highest priority wins)
        foreach ($results as &$result) {
            if ($result['criticas_count'] > 0) {
                $result['priority'] = 'critical';
                $result['status_message'] = $result['criticas_count'] . ' cuota(s) crítica(s) (>7 días)';
                if ($result['gracia_count'] > 0) {
                    $result['status_message'] .= ' + ' . $result['gracia_count'] . ' en gracia';
                }
                if ($result['por_vencer_3d_count'] > 0) {
                    $result['status_message'] .= ' + ' . $result['por_vencer_3d_count'] . ' por vencer (≤3d)';
                }
            } elseif ($result['gracia_count'] > 0) {
                $result['priority'] = 'grace';
                $result['status_message'] = $result['gracia_count'] . ' cuota(s) en período de gracia (0-7 días)';
                if ($result['por_vencer_3d_count'] > 0) {
                    $result['status_message'] .= ' + ' . $result['por_vencer_3d_count'] . ' por vencer (≤3d)';
                }
            } elseif ($result['por_vencer_3d_count'] > 0) {
                $result['priority'] = 'urgent';
                $result['status_message'] = $result['por_vencer_3d_count'] . ' cuota(s) por vencer en ≤3 días';
            } elseif ($result['por_vencer_7d_count'] > 0) {
                $result['priority'] = 'warning';
                $result['status_message'] = $result['por_vencer_7d_count'] . ' cuota(s) por vencer en 4-7 días';
            } else {
                $result['priority'] = 'normal';
                $result['status_message'] = 'Cuota(s) pendiente(s)';
            }
        }

        // Apply search filter
        if ($search) {
            $searchLower = strtolower($search);
            $results = array_filter($results, function ($item) use ($searchLower) {
                return strpos(strtolower($item['user_nombres'] . ' ' . $item['user_apellidos']), $searchLower) !== false
                    || strpos(strtolower($item['cedula']), $searchLower) !== false
                    || strpos(strtolower($item['nrocontrato']), $searchLower) !== false;
            });
            $results = array_values($results);
        }

        // Apply filter type
        if ($filtro === 'por_vencer') {
            $results = array_filter($results, function ($item) {
                return $item['por_vencer_3d_count'] > 0;
            });
            $results = array_values($results);
        } elseif ($filtro === 'vencidas') {
            $results = array_filter($results, function ($item) {
                return $item['criticas_count'] > 0 || $item['gracia_count'] > 0;
            });
            $results = array_values($results);
        } elseif ($filtro === 'en_gracia') {
            $results = array_filter($results, function ($item) {
                return $item['gracia_count'] > 0 && $item['criticas_count'] == 0;
            });
            $results = array_values($results);
        }

        // Calculate stats
        $stats = [
            'total_afiliados' => count($results),
            'total_adeudado' => array_sum(array_column($results, 'total_adeudado')),
            'total_pendiente' => array_sum(array_column($results, 'total_pendiente')),
            'critical_count' => count(array_filter($results, function ($r) {
                return $r['criticas_count'] > 0;
            })),
            'grace_count' => count(array_filter($results, function ($r) {
                return $r['gracia_count'] > 0;
            })),  // Changed: now counts ALL with gracia
            'urgent_count' => count(array_filter($results, function ($r) {
                return $r['por_vencer_3d_count'] > 0 && $r['criticas_count'] == 0 && $r['gracia_count'] == 0;
            })),
        ];

        // Get clinics for dropdown
        $clinicas = [];
        if (UserHelper::hasClinicAccess()) {
            $userClinicId = UserHelper::getMyClinicaId();
            if ($userClinicId) {
                $clinicas = RmClinica::find()->where(['id' => $userClinicId, 'estatus' => 'Activo'])->all();
            }
        } else {
            $clinicas = RmClinica::find()->where(['estatus' => 'Activo'])->orderBy(['nombre' => SORT_ASC])->all();
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $results,
            'sort' => [
                'attributes' => ['user_nombres', 'clinica_nombre', 'total_adeudado'],
                'defaultOrder' => ['total_adeudado' => SORT_DESC],
            ],
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('cuotas-seguimiento', [
            'dataProvider' => $dataProvider,
            'stats' => $stats,
            'filtro' => $filtro,
            'clinicaId' => $clinicaId,
            'search' => $search,
            'clinicas' => $clinicas,
        ]);
    }

    public function actionExportarCsv()
    {
        $request = Yii::$app->request;
        $filtro = $request->get('filtro', 'todas');
        $clinicaId = $request->get('clinica_id');

        $today = date('Y-m-d');

        $query = Cuotas::find()
            ->with(['contrato', 'contrato.user'])
            ->where(['in', 'cuotas.estatus', ['pendiente', 'en_gracias', 'vencida']])
            ->joinWith(['contrato c'])
            ->andWhere(['!=', 'c.estatus', Contratos::STATUS_ANULADO]);

        if ($clinicaId) {
            $query->andWhere(['c.clinica_id' => $clinicaId]);
        } elseif (UserHelper::hasClinicAccess()) {
            $userClinicId = UserHelper::getMyClinicaId();
            if ($userClinicId) {
                $query->andWhere(['c.clinica_id' => $userClinicId]);
            }
        }

        $cuotas = $query->all();

        // Group by user - only count expired cuotas for total_adeudado
        $grouped = [];
        foreach ($cuotas as $cuota) {
            $userId = $cuota->contrato->user_id;
            $user = $cuota->contrato->user;
            $mesAnio = date('F Y', strtotime($cuota->fecha_vencimiento));
            $isExpired = ($cuota->estatus == 'vencida' || $cuota->estatus == 'en_gracias');

            if (!isset($grouped[$userId])) {
                $grouped[$userId] = [
                    'user' => $user,
                    'total_adeudado' => 0,
                    'cuotas_count' => 0,
                    'detalles' => [],
                ];
            }
            if ($isExpired) {
                $grouped[$userId]['total_adeudado'] += ($cuota->monto_usd ?? $cuota->monto ?? 0);
            }
            $grouped[$userId]['cuotas_count']++;
            $grouped[$userId]['detalles'][] = 'Cuota #' . $cuota->numero_cuota . ' (' . $mesAnio . ') - $' . number_format($cuota->monto_usd ?? $cuota->monto ?? 0, 2) . ' - ' . $cuota->estatus;
        }

        $results = [];
        foreach ($grouped as $userId => $data) {
            $user = $data['user'];
            $contrato = Contratos::find()->where(['user_id' => $userId])->one();
            $results[] = [
                'Afiliado' => $user->nombres . ' ' . $user->apellidos,
                'Cédula' => ($user->tipo_cedula ?? 'V') . '-' . $user->cedula,
                'Teléfono' => $user->telefono ?? 'N/A',
                'Email' => $user->email ?? 'N/A',
                'Contrato' => $contrato->nrocontrato ?? 'N/A',
                'Total Adeudado (USD)' => number_format($data['total_adeudado'], 2),
                'Detalle Cuotas' => implode('; ', $data['detalles']),
            ];
        }

        $filename = "reporte_cobros_pendientes_" . date('Y-m-d') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if (!empty($results)) {
            fputcsv($output, array_keys($results[0]));
            foreach ($results as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }
}
