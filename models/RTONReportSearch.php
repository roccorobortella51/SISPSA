<?php
// app/models/RTONReportSearch.php

namespace app\models;

use Yii;
use yii\base\Model;
use app\components\UserHelper;

class RTONReportSearch extends Model
{
    public $date_range_type;
    public $date_from;
    public $date_to;

    const DATE_RANGE_CUSTOM = 'custom';
    const DATE_RANGE_TODAY = 'today';
    const DATE_RANGE_YESTERDAY = 'yesterday';
    const DATE_RANGE_THIS_WEEK = 'this_week';
    const DATE_RANGE_LAST_WEEK = 'last_week';
    const DATE_RANGE_THIS_MONTH = 'this_month';
    const DATE_RANGE_LAST_MONTH = 'last_month';
    const DATE_RANGE_LAST_30_DAYS = 'last_30_days';
    const DATE_RANGE_LAST_90_DAYS = 'last_90_days';
    const DATE_RANGE_THIS_YEAR = 'this_year';
    const DATE_RANGE_LAST_YEAR = 'last_year';

    public function rules()
    {
        return [
            [['date_range_type'], 'string'],
            [['date_from', 'date_to'], 'date', 'format' => 'php:Y-m-d'],
            [['date_from', 'date_to'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'date_range_type' => 'Rango de Fechas',
            'date_from' => 'Fecha Desde',
            'date_to' => 'Fecha Hasta',
        ];
    }

    public static function getDateRangeOptions()
    {
        return [
            self::DATE_RANGE_CUSTOM => 'Personalizado',
            self::DATE_RANGE_TODAY => 'Hoy',
            self::DATE_RANGE_YESTERDAY => 'Ayer',
            self::DATE_RANGE_THIS_WEEK => 'Esta Semana',
            self::DATE_RANGE_LAST_WEEK => 'Semana Pasada',
            self::DATE_RANGE_THIS_MONTH => 'Este Mes',
            self::DATE_RANGE_LAST_MONTH => 'Mes Pasado',
            self::DATE_RANGE_LAST_30_DAYS => 'Últimos 30 Días',
            self::DATE_RANGE_LAST_90_DAYS => 'Últimos 90 Días',
            self::DATE_RANGE_THIS_YEAR => 'Este Año',
            self::DATE_RANGE_LAST_YEAR => 'Año Pasado',
        ];
    }

    public function applyDateRange($query)
    {
        $today = date('Y-m-d');

        switch ($this->date_range_type) {
            case self::DATE_RANGE_TODAY:
                $this->date_from = $today;
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_YESTERDAY:
                $this->date_from = date('Y-m-d', strtotime('-1 day'));
                $this->date_to = $this->date_from;
                break;
            case self::DATE_RANGE_THIS_WEEK:
                $this->date_from = date('Y-m-d', strtotime('monday this week'));
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_LAST_WEEK:
                $this->date_from = date('Y-m-d', strtotime('monday last week'));
                $this->date_to = date('Y-m-d', strtotime('sunday last week'));
                break;
            case self::DATE_RANGE_THIS_MONTH:
                $this->date_from = date('Y-m-01');
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_LAST_MONTH:
                $this->date_from = date('Y-m-01', strtotime('first day of previous month'));
                $this->date_to = date('Y-m-t', strtotime('last day of previous month'));
                break;
            case self::DATE_RANGE_LAST_30_DAYS:
                $this->date_from = date('Y-m-d', strtotime('-30 days'));
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_LAST_90_DAYS:
                $this->date_from = date('Y-m-d', strtotime('-90 days'));
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_THIS_YEAR:
                $this->date_from = date('Y-01-01');
                $this->date_to = $today;
                break;
            case self::DATE_RANGE_LAST_YEAR:
                $this->date_from = date('Y-01-01', strtotime('-1 year'));
                $this->date_to = date('Y-12-31', strtotime('-1 year'));
                break;
            case self::DATE_RANGE_CUSTOM:
            default:
                break;
        }

        if ($this->date_from && $this->date_to) {
            $query->andWhere(['between', 'fecha_pago', $this->date_from, $this->date_to]);
        } elseif ($this->date_from) {
            $query->andWhere(['>=', 'fecha_pago', $this->date_from]);
        } elseif ($this->date_to) {
            $query->andWhere(['<=', 'fecha_pago', $this->date_to]);
        }
    }

    /**
     * Map estado name to code from Tabla de Códigos N°9 (Estados)
     */
    private function mapEstadoToCode($estadoNombre)
    {
        $estadosMap = [
            'Distrito Capital' => 1,
            'Anzoátegui' => 2,
            'Apure' => 3,
            'Aragua' => 4,
            'Barinas' => 5,
            'Bolívar' => 6,
            'Carabobo' => 7,
            'Cojedes' => 8,
            'Falcón' => 9,
            'Guárico' => 10,
            'Lara' => 11,
            'Mérida' => 12,
            'Miranda' => 13,
            'Monagas' => 14,
            'Nueva Esparta' => 15,
            'Portuguesa' => 16,
            'Sucre' => 17,
            'Táchira' => 18,
            'Trujillo' => 19,
            'Yaracuy' => 20,
            'Zulia' => 21,
            'Amazonas' => 22,
            'Delta Amacuro' => 23,
            'Vargas' => 24,
        ];

        $normalized = trim($estadoNombre ?? '');

        foreach ($estadosMap as $name => $code) {
            if (stripos($normalized, $name) !== false || stripos($name, $normalized) !== false) {
                return (string)$code;
            }
        }

        return '1';
    }

    /**
     * Map payment method to code from Tabla de Códigos N°6
     */
    private function mapFormaPagoToCode($metodoPago)
    {
        $normalized = trim($metodoPago ?? '');

        if (stripos($normalized, 'Pago Móvil') !== false || stripos($normalized, 'Pago Movil') !== false) {
            return '3';
        }

        if (stripos($normalized, 'Efectivo') !== false) {
            return '1';
        }

        if (stripos($normalized, 'Cheque') !== false) {
            return '2';
        }

        if (stripos($normalized, 'Transferencia') !== false || stripos($normalized, 'Transfer') !== false) {
            return '3';
        }

        if (stripos($normalized, 'Depósito') !== false || stripos($normalized, 'Deposito') !== false) {
            return '4';
        }

        if (stripos($normalized, 'Tarjeta de Crédito') !== false || stripos($normalized, 'Credito') !== false) {
            return '5';
        }

        if (stripos($normalized, 'Financiado') !== false) {
            return '6';
        }

        if (stripos($normalized, 'Tarjeta de Débito') !== false || stripos($normalized, 'Debito') !== false) {
            return '7';
        }

        if (stripos($normalized, 'Combinado') !== false) {
            return '8';
        }

        if (stripos($normalized, 'Compensación') !== false || stripos($normalized, 'Compensacion') !== false) {
            return '9';
        }

        if (stripos($normalized, 'Cargo en Cuenta') !== false) {
            return '10';
        }

        if (stripos($normalized, 'Primas Fraccionadas') !== false) {
            return '11';
        }

        if (stripos($normalized, 'Cuotas') !== false || stripos($normalized, 'Cuota') !== false) {
            return '12';
        }

        if (stripos($normalized, 'Pago Único') !== false || stripos($normalized, 'Unico') !== false) {
            return '13';
        }

        if (stripos($normalized, 'Mensual') !== false) {
            return '14';
        }

        if (stripos($normalized, 'Trimestral') !== false) {
            return '15';
        }

        if (stripos($normalized, 'Anual') !== false) {
            return '16';
        }

        return '1';
    }

    /**
     * Get all RTON data for export - Organized by Clinic
     * MODIFIED: Now includes "Anulado" contracts (removed the exclusion filter)
     */
    public function getRTONData($params)
    {
        $this->load($params);

        $query = Pagos::find()
            ->alias('p')
            ->select([
                'p.id',
                'p.created_at',
                'p.fecha_pago',
                'p.monto_pagado',
                'p.monto_usd',
                'p.metodo_pago',
                'p.estatus',
                'p.user_id',
                'p.numero_referencia_pago',
                'p.imagen_prueba',

                'ud.id as user_datos_id',
                'ud.nombres',
                'ud.apellidos',
                'ud.tipo_cedula',
                'ud.cedula',
                'ud.telefono',
                'ud.telefono_residencia',
                'ud.direccion',
                'ud.profesion',
                'ud.ocupacion',
                'ud.estado',
                'ud.tiene_contratante_diferente',
                'ud.nombre_contratante',
                'ud.apellido_contratante',
                'ud.cedula_contratante',
                'ud.tipo_cedula_contratante',
                'ud.profesion_contratante',
                'ud.ocupacion_contratante',
                'ud.direccion_residencia_contratante',
                'ud.clinica_id',
                'ud.plan_id',
                'ud.asesor_id',
                'ud.estatus_solvente',

                'c.id as contrato_id',
                'c.nrocontrato',
                'c.fecha_ini',
                'c.fecha_ven',
                'c.created_at as contrato_created_at',

                'pl.cobertura as plan_cobertura',
                'pl.nombre as plan_nombre',

                'clin.id as clinica_id',
                'clin.nombre as clinica_nombre',
                'clin.codigo_clinica as clinica_codigo',
            ])
            ->innerJoin(['ud' => 'user_datos'], 'ud.id = p.user_id')
            // 🔧 MODIFIED: Removed "AND c.estatus != :anulado" to include ALL contracts
            ->leftJoin(['c' => 'contratos'], 'c.user_id = ud.id')
            ->leftJoin(['pl' => 'planes'], 'pl.id = COALESCE(ud.plan_id, c.plan_id)')
            ->leftJoin(['clin' => 'rm_clinica'], 'clin.id = ud.clinica_id')
            ->where(['!=', 'p.estatus', 'Deleted'])
            ->orderBy(['clin.nombre' => SORT_ASC, 'p.fecha_pago' => SORT_DESC])
            ->asArray();

        // Apply clinic access restriction if user has it
        if (UserHelper::hasClinicAccess()) {
            $clinicId = UserHelper::getMyClinicaId();
            if ($clinicId) {
                $query->andWhere(['ud.clinica_id' => $clinicId]);
            }
        }

        $this->applyDateRange($query);

        $payments = $query->all();
        $dataByClinic = [];

        foreach ($payments as $payment) {
            $clinicId = $payment['clinica_id'] ?? 'Sin Clínica';
            $clinicName = $payment['clinica_nombre'] ?? 'Sin Clínica Asignada';
            $clinicCode = $payment['clinica_codigo'] ?: $clinicId;

            // Get emergency contact phone
            $emergencyContact = ContactosEmergencia::find()
                ->where(['user_id' => $payment['user_id']])
                ->one();
            $emergencyPhone = $emergencyContact ? $emergencyContact->telefono : '';

            // Get asesor/intermediary info from AgenteFuerza
            $intermediaryName = '';
            $intermediaryCode = '';
            if (!empty($payment['asesor_id'])) {
                $agenteFuerza = AgenteFuerza::findOne($payment['asesor_id']);
                if ($agenteFuerza && $agenteFuerza->userDatos) {
                    $intermediaryName = $agenteFuerza->userDatos->nombres . ' ' . $agenteFuerza->userDatos->apellidos;
                    $intermediaryCode = $agenteFuerza->registro_corredor_actividad_aseguradora ?? '';
                }
            }

            // Determine contratante data
            $hasDiferentContractor = ($payment['tiene_contratante_diferente'] == 1);

            if ($hasDiferentContractor) {
                $contractorId = ($payment['tipo_cedula_contratante'] ?: 'V') . '-' . $payment['cedula_contratante'];
                $contractorName = trim(($payment['nombre_contratante'] ?? '') . ' ' . ($payment['apellido_contratante'] ?? ''));
                $contractorProfession = $payment['profesion_contratante'] ?? '';
                $contractorOccupation = $payment['ocupacion_contratante'] ?? '';
                $contractorAddress = $payment['direccion_residencia_contratante'] ?? '';
            } else {
                $contractorId = ($payment['tipo_cedula'] ?: 'V') . '-' . $payment['cedula'];
                $contractorName = trim(($payment['nombres'] ?? '') . ' ' . ($payment['apellidos'] ?? ''));
                $contractorProfession = $payment['profesion'] ?? '';
                $contractorOccupation = $payment['ocupacion'] ?? '';
                $contractorAddress = $payment['direccion'] ?? '';
            }

            $estadoCode = $this->mapEstadoToCode($payment['estado'] ?? '');
            $formaPagoCode = $this->mapFormaPagoToCode($payment['metodo_pago'] ?? '');

            $row = [
                'cod_sucursal' => $clinicCode,
                'clinica_nombre' => $clinicName,
                'fecha_emision' => !empty($payment['contrato_created_at']) ? date('d-m-Y', strtotime($payment['contrato_created_at'])) : date('d-m-Y', strtotime($payment['created_at'])),
                'numero_poliza' => $payment['nrocontrato'] ?? '',
                'ramo' => '1',
                'fecha_ini_vigencia' => !empty($payment['fecha_ini']) ? date('d-m-Y', strtotime($payment['fecha_ini'])) : '',
                'fecha_fin_vigencia' => !empty($payment['fecha_ven']) ? date('d-m-Y', strtotime($payment['fecha_ven'])) : '',
                'monto_cobertura' => number_format((float)($payment['plan_cobertura'] ?? 0), 2, '.', ''),
                'monto_prima' => number_format((float)($payment['monto_usd'] ?? 0), 2, '.', ''),
                'monto_pagado' => number_format((float)($payment['monto_pagado'] ?? 0), 2, '.', ''),
                'moneda' => '3',
                'forma_pago' => $formaPagoCode,
                'primer_intermediario' => $intermediaryName,
                'cod_autorizacion_1' => $intermediaryCode,
                'segundo_intermediario' => $intermediaryName,
                'cod_autorizacion_2' => $intermediaryCode,
                'identificacion_contratante' => $contractorId,
                'nombre_contratante' => $contractorName,
                'telefono_celular' => $payment['telefono'] ?? '',
                'telefono_hab' => $payment['telefono_residencia'] ?? '',
                'telefono_emergencia' => $emergencyPhone,
                'tipo_cliente' => '2',
                'profesion_contratante' => $contractorProfession,
                'ocupacion_contratante' => $contractorOccupation,
                'estado' => $estadoCode,
                'direccion_contratante' => $contractorAddress,
                'beneficiario' => '0',
                'estado_pago' => $payment['estatus'] ?? 'Por Conciliar',
            ];

            // Initialize clinic array if not exists
            if (!isset($dataByClinic[$clinicId])) {
                $dataByClinic[$clinicId] = [
                    'clinica_id' => $clinicId,
                    'clinica_nombre' => $clinicName,
                    'clinica_codigo' => $clinicCode,
                    'transactions' => [],
                    'summary' => [
                        'total_transactions' => 0,
                        'total_amount' => 0,
                        'total_usd_amount' => 0,
                        'total_coverage' => 0,
                    ]
                ];
            }

            // Add transaction to clinic
            $dataByClinic[$clinicId]['transactions'][] = $row;
            $dataByClinic[$clinicId]['summary']['total_transactions']++;
            $dataByClinic[$clinicId]['summary']['total_amount'] += (float)$row['monto_prima'];
            $dataByClinic[$clinicId]['summary']['total_usd_amount'] += (float)($payment['monto_pagado'] ?? 0);
            $dataByClinic[$clinicId]['summary']['total_coverage'] += (float)$row['monto_cobertura'];
        }

        // Convert to indexed array and sort by clinic name
        $result = array_values($dataByClinic);
        usort($result, function ($a, $b) {
            return strcmp($a['clinica_nombre'], $b['clinica_nombre']);
        });

        return $result;
    }

    /**
     * Get all transactions as flat array for Excel export (not grouped)
     * MODIFIED: Now includes "Anulado" contracts
     */
    public function getRTONDataFlat($params)
    {
        $groupedData = $this->getRTONData($params);
        $flatData = [];

        foreach ($groupedData as $clinic) {
            foreach ($clinic['transactions'] as $transaction) {
                $flatData[] = $transaction;
            }
        }

        return $flatData;
    }

    /**
     * Get summary statistics for the report
     * MODIFIED: Now includes "Anulado" contracts in count
     */
    public function getSummary($params)
    {
        $groupedData = $this->getRTONData($params);

        $totalPayments = 0;
        $totalAmount = 0;
        $totalUsdAmount = 0;
        $totalCoverage = 0;
        $totalCancelled = 0;
        $clinicsCount = count($groupedData);

        foreach ($groupedData as $clinic) {
            $totalPayments += $clinic['summary']['total_transactions'];
            $totalAmount += $clinic['summary']['total_amount'];
            $totalUsdAmount += $clinic['summary']['total_usd_amount'] ?? 0;
            $totalCoverage += $clinic['summary']['total_coverage'];
            $totalCancelled += $clinic['summary']['total_cancelled'] ?? 0;
        }

        return [
            'total_payments' => $totalPayments,
            'total_amount' => $totalAmount,
            'total_usd_amount' => $totalUsdAmount,
            'total_coverage' => $totalCoverage,
            'unique_clinics' => $clinicsCount,
            'total_cancelled' => $totalCancelled,  // ← ADDED
        ];
    }
}
