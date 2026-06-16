<?php
// app/components/ReceiptGenerator.php - ADAPTED TO MATCH AR.xlsx EXACTLY

namespace app\components;

use Yii;
use app\components\NotificationHelper;

use app\models\Receipt;
use app\models\Cuotas;
use app\models\Pagos;
use app\models\Contratos;
use app\models\UserDatos;
use app\models\AgenteFuerza;
use app\models\PlanServicios;

class ReceiptGenerator
{
    public static function generateForPayment($payment)
    {
        $generatedReceipts = [];

        if (!$payment || !$payment->id) {
            return $generatedReceipts;
        }

        $installments = Cuotas::find()->where(['id_pago' => $payment->id])->all();

        if (empty($installments)) {
            return $generatedReceipts;
        }

        $contract = $installments[0]->contrato;
        $user = UserDatos::findOne($payment->user_id);

        $totalCuotas = Cuotas::find()->where(['contrato_id' => $contract->id])->count();

        foreach ($installments as $installment) {
            $receipt = self::generateSingleReceipt($payment, $installment, $contract, $user, $totalCuotas);
            if ($receipt) {
                $generatedReceipts[] = $receipt;
            }
        }

        return $generatedReceipts;
    }

    private static function generateSingleReceipt($payment, $installment, $contract, $user, $totalCuotas)
    {
        $montoUSD = $payment->monto_pagado;
        $montoBs = $payment->monto_usd;

        $tasa = 0;
        if ($montoUSD > 0) {
            $tasa = $montoBs / $montoUSD;
        }
        $tasaFormatted = number_format($tasa, 2, ',', '.');

        $coverageStart = $installment->coverage_start ?: $contract->fecha_ini;
        $coverageEnd = $installment->coverage_end;
        if (!$coverageEnd && $contract->fecha_ini && $installment->numero_cuota) {
            $startDate = new \DateTime($contract->fecha_ini);
            $startDate->modify('+' . $installment->numero_cuota . ' months');
            $coverageEnd = $startDate->format('Y-m-d');
        } elseif (!$coverageEnd) {
            $coverageEnd = $contract->fecha_ven;
        }

        $planNombre = $contract->plan ? $contract->plan->nombre : 'ORO';
        $coberturaValor = $contract->plan ? number_format($contract->plan->cobertura, 2) : '20000';
        $sucursal = $contract->clinica ? $contract->clinica->nombre : 'N/A';

        $fullName = trim($user->nombres . ' ' . $user->apellidos);
        $idNumber = ($user->tipo_cedula ?: 'V') . '-' . $user->cedula;
        $direccionCobro = $user->direccion_cobro ?: ($user->direccion ?: 'N/A');
        $telefono = $user->telefono ?: 'N/A';
        $email = $user->email ?: 'N/A';
        $sexo = $user->sexo ?: 'No especificado';
        $nacionalidad = $user->nacionalidad ?: 'Venezolana';
        $estadoCivil = $user->estado_civil ?: 'No especificado';
        $profesion = $user->profesion ?: 'N/A';
        $tipoPersona = 'Natural';


        // ============================================================
        // CHECK IF CONTRATANTE DIFERENTE IS ENABLED
        // ============================================================
        $tieneContratanteDiferente = $user->tiene_contratante_diferente;

        // Default values (from the user itself)
        $contratanteNombre = $fullName;
        $contratanteApellido = '';
        $contratanteIdNumber = $idNumber;
        $contratanteNacionalidad = $nacionalidad;
        $contratanteTipoPersona = $tipoPersona;
        $contratanteRazonSocial = '-';
        $contratanteDireccionCobro = $direccionCobro;
        $contratanteTelefono = $telefono;
        $contratanteEmail = $email;

        // If contratante diferente is true, use the contratante fields
        if ($tieneContratanteDiferente) {
            $contratanteNombre = !empty($user->nombre_contratante) ? $user->nombre_contratante : $fullName;
            $contratanteApellido = !empty($user->apellido_contratante) ? $user->apellido_contratante : '';
            $contratanteIdNumber = $user->tipo_cedula_contratante && $user->cedula_contratante
                ? $user->tipo_cedula_contratante . '-' . $user->cedula_contratante
                : $idNumber;
            $contratanteNacionalidad = $user->nacionalidad_contratante ?: $nacionalidad;
            $contratanteTipoPersona = 'Natural'; // Could be from user_datos_type_id
            $contratanteRazonSocial = $user->razon_social ?: '-';
            $contratanteDireccionCobro = $user->direccion_cobro_contratante ?: $direccionCobro;
            $contratanteTelefono = $user->telefono_celular_contratante ?: ($user->telefono_oficina_contratante ?: $telefono);
            $contratanteEmail = $user->email_contratante ?: $email;
        }

        // Full contratante name
        $contratanteFullName = trim($contratanteNombre . ' ' . $contratanteApellido);

        $intermediarioNombre = 'N/A';
        $intermediarioCodigo = 'N/A';
        if ($user && $user->asesor_id) {
            $agenteFuerza = AgenteFuerza::findOne($user->asesor_id);
            if ($agenteFuerza && $agenteFuerza->userDatos) {
                $asesorUserDatos = $agenteFuerza->userDatos;
                $intermediarioNombre = trim($asesorUserDatos->nombres . ' ' . $asesorUserDatos->apellidos);
                $intermediarioCodigo = $agenteFuerza->id;
            }
        }

        $contratoNumero = $contract->nrocontrato ?: $contract->id;
        $installmentNumber = $installment->numero_cuota ?: '1';
        $receiptNumber = Receipt::generateReceiptNumber();

        $fechaEmisionContrato = date('d/m/Y', strtotime($contract->created_at));
        $fechaEmision = date('d/m/Y', strtotime($receipt->issue_date ?? 'now'));
        $fechaPago = date('d/m/Y', strtotime($payment->fecha_pago));
        $fechaInicio = $contract ? date('d/m/Y', strtotime($contract->fecha_ini)) : 'N/A';
        $fechaVencimiento = ($contract && $contract->fecha_ven) ? date('d/m/Y', strtotime($contract->fecha_ven)) : 'Sin fecha';
        $coverageFrom = date('d/m/Y', strtotime($coverageStart));
        $coverageTo = date('d/m/Y', strtotime($coverageEnd));

        $montoUSDFormatted = number_format($montoUSD, 2, ',', '.');
        $montoBsFormatted = number_format($montoBs, 2, ',', '.');

        $logoBase64 = '';
        $logoPath = Yii::getAlias('@webroot') . '/img/sispsalogo.jpg';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode($logoData);
        }

        // Dynamic plan service data (from database)
        $planServices = [];
        if ($contract && $contract->plan_id) {
            $dbServices = PlanServicios::getServicesForPlan($contract->plan_id, $contract->clinica_id);
            foreach ($dbServices as $svc) {
                $planServices[] = [
                    'servicio' => $svc->servicio_nombre,
                    'espera' => $svc->plazo_espera ?: 'NO APLICA',
                    'descripcion' => $svc->descripcion ?: 'Servicio incluido en el plan',
                ];
            }
        }
        // If no data from database, use empty array (table will have no rows)

        $exclusionesTexto = $contract->plan && !empty($contract->plan->exclusiones)
            ? $contract->plan->exclusiones
            : '';
        $servicesHtml = '';
        foreach ($planServices as $service) {
            $servicesHtml .= '<tr>
                <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">' . htmlspecialchars($service['servicio']) . '</td>
                <td style="border: 1px solid #000; padding: 6px; vertical-align: top; text-align: center;">' . htmlspecialchars($service['espera']) . '</td>
                <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">' . htmlspecialchars($service['descripcion']) . '</td>
            </tr>';
        }

        $html = self::generateHTML(
            $receiptNumber,
            $contratoNumero,
            $montoBsFormatted,
            $fullName,
            $idNumber,
            $direccionCobro,
            $telefono,
            $email,
            $sexo,
            $nacionalidad,
            $estadoCivil,
            $profesion,
            $tipoPersona,
            $intermediarioNombre,
            $intermediarioCodigo,
            $planNombre,
            $coberturaValor,
            $sucursal,
            $fechaEmision,
            $fechaInicio,
            $fechaVencimiento,
            $fechaPago,
            $coverageFrom,
            $coverageTo,
            $montoUSDFormatted,
            $montoBsFormatted,
            $tasaFormatted,
            $payment->metodo_pago,
            $payment->numero_referencia_pago,
            $logoBase64,
            $servicesHtml,
            $exclusionesTexto,
            $installmentNumber,
            $totalCuotas,
            $receiptNumber,
            // NEW PARAMETERS for contratante diferente
            $tieneContratanteDiferente,
            $contratanteFullName,
            $contratanteIdNumber,
            $contratanteNacionalidad,
            $contratanteTipoPersona,
            $contratanteRazonSocial,
            $contratanteDireccionCobro,
            $contratanteTelefono,
            $contratanteEmail
        );

        $receipt = new Receipt();
        $receipt->payment_id = $payment->id;
        $receipt->installment_id = $installment->id;
        $receipt->contract_id = $contract->id;
        $receipt->user_id = $payment->user_id;
        $receipt->receipt_number = $receiptNumber;
        $receipt->issue_date = date('Y-m-d');
        $receipt->coverage_start_date = $coverageStart;
        $receipt->coverage_end_date = $coverageEnd;
        $receipt->amount = $montoBs;
        $receipt->currency = 'Bs';
        $receipt->payment_method = self::mapPaymentMethod($payment->metodo_pago);
        $receipt->reference_number = $payment->numero_referencia_pago;
        $receipt->payment_place = 'Oficinas SISPSA';
        $receipt->status = Receipt::STATUS_GENERATED;
        $receipt->html_content = $html;

        if ($receipt->save()) {
            // Send notification to affiliate
            NotificationHelper::sendReceiptNotification($receipt, $user, $payment);
            return $receipt;
        }
        return null;
    }

    private static function mapPaymentMethod($method)
    {
        $map = [
            'transferencia' => 'Transferencia Bancaria',
            'transferencia_bancaria' => 'Transferencia Bancaria',
            'pago_movil' => 'Pago Móvil',
            'pagomovil' => 'Pago Móvil',
            'zelle' => 'Zelle',
            'efectivo' => 'Efectivo',
            'efectivo_dolar' => 'Efectivo',
            'Efectivo - Dólar ($)' => 'Efectivo',
            'paypal' => 'PayPal',
        ];
        $key = strtolower(trim($method));
        return isset($map[$key]) ? $map[$key] : ($method ?: 'N/A');
    }

    private static function generateHTML(
        $receiptNumber,
        $contratoNumero,
        $montoBsFormatted,
        $fullName,
        $idNumber,
        $direccionCobro,
        $telefono,
        $email,
        $sexo,
        $nacionalidad,
        $estadoCivil,
        $profesion,
        $tipoPersona,
        $intermediarioNombre,
        $intermediarioCodigo,
        $planNombre,
        $coberturaValor,
        $sucursal,
        $fechaEmision,
        $fechaInicio,
        $fechaVencimiento,
        $fechaPago,
        $coverageFrom,
        $coverageTo,
        $montoUSDFormatted,
        $montoBsFormattedDatosPago,
        $tasaFormatted,
        $metodoPago,
        $referencia,
        $logoBase64,
        $servicesHtml,
        $exclusionesTexto,
        $installmentNumber,
        $totalCuotas,
        $receiptNum,
        // NEW PARAMETERS
        $tieneContratanteDiferente,
        $contratanteFullName,
        $contratanteIdNumber,
        $contratanteNacionalidad,
        $contratanteTipoPersona,
        $contratanteRazonSocial,
        $contratanteDireccionCobro,
        $contratanteTelefono,
        $contratanteEmail
    ) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recibo de Afiliación {$receiptNumber}</title>
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #e9ecef; padding: 30px; display: flex; justify-content: center; }
    .receipt { max-width: 1200px; width: 100%; background: white; border: 1px solid #d0d0d0; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
    
    /* Logo Container Styles */
    .logo-container {
        text-align: center;
        padding: 25px 20px 15px 20px;
        border-bottom: 1px solid #e8e8e8;
        background: linear-gradient(to bottom, #ffffff, #fafafa);
    }
    .logo-container img {
        max-width: 200px;
        height: auto;
        transition: transform 0.3s ease;
    }
    .logo-container img:hover {
        transform: scale(1.02);
    }
    
    /* Registration Information Styles */
    .registration-info {
        text-align: center;
        padding: 12px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    .reg-line {
        font-size: 10px;
        color: #495057;
        letter-spacing: 0.3px;
        font-weight: 500;
        margin-bottom: 4px;
    }
    .rif-line {
        font-size: 11px;
        color: #0066cc;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    /* Title Container Styles */
    .title-container {
        text-align: center;
        padding: 10px 20px;
        background: #1a3a6e;
        color: white;
    }
    .title-container h1 {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }
    .title-container h2 {
        font-size: 14px;
        font-weight: 500;
        margin: 5px 0 0 0;
        opacity: 0.9;
    }
    
    /* Content Styles */
    .content {
        padding: 20px;
    }
    
    /* Table Styles */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
        font-size: 10px;
    }
    td, th {
        border: 1px solid #000;
        padding: 6px;
        vertical-align: top;
    }
    th {
        background: #f0f0f0;
        font-weight: 600;
        text-align: center;
    }
    
    /* Subtitle Styles */
    .subtitle {
    background: #e8e8e8 !important;
    padding: 6px 10px;
    margin: 15px 0 10px 0;
    font-size: 12px;
    font-weight: bold;
    border-radius: 4px;
}
    
    /* Utility Classes */
    .text-center {
        text-align: center;
    }
    .text-right {
        text-align: right;
    }
    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }
    
    /* Signature Styles */
    .signature-line {
        margin-top: 30px;
        border-top: 1px solid #000;
        padding-top: 5px;
    }
    
    /* Footer Styles */
    .footer {
        font-size: 8px;
        text-align: center;
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px solid #ccc;
        color: #666;
    }
    
    /* Legal Text Styles */
    .legal {
        font-size: 9px;
        text-align: justify;
        margin: 15px 0;
        line-height: 1.4;
    }
    
    /* Print Button Styles */
    .btn-print {
        background: #1a3a6e;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        margin-bottom: 15px;
        float: right;
    }
    .btn-print:hover {
        background: #0d2b4f;
    }
    
    /* Print Media Styles */
    @media print {
        body {
            background: white;
            padding: 0;
            margin: 0;
        }
        .btn-print {
            display: none;
        }
        .receipt {
            box-shadow: none;
            border: none;
        }
        .logo-container {
            background: white;
            border-bottom: 1px solid #ccc;
        }
        .registration-info {
            background: white;
            border-bottom: 1px solid #ccc;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .rif-line {
            color: #0066cc;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
        body {
            padding: 15px;
        }
        .receipt {
            max-width: 100%;
        }
        .logo-container img {
            max-width: 150px;
        }
        .reg-line {
            font-size: 9px;
        }
        .rif-line {
            font-size: 10px;
        }
    }
</style>
</head>
<body>
<div class="receipt">
    <div class="logo-container">
    <img src="{$logoBase64}" alt="SISPSA Logo">
</div>
<div class="registration-info">
    <div class="reg-line">Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013</div>
    <div class="rif-line">R.I.F.: J-506549220</div>
</div>
    <div class="title-container">
        <h1>Contrato de Servicios de Medicina Prepagada</h1>
        <h2>CUADRO RECIBO DE AFILIACIÓN</h2>
    </div>
    <div class="content">
        <div class="clearfix"><button class="btn-print" onclick="window.print();">IMPRIMIR RECIBO</button></div>

        

        <table class="receipt-table">
    <tr>
        <td width="25%"><strong>Contrato Nº.:</strong> {$contratoNumero}</td
        ><td width="25%"><strong>Recibo Nº.:</strong> {$receiptNum}</td
        ><td width="25%"><strong>Cuota Nº.:</strong> {$installmentNumber} de {$totalCuotas}</td
        ><td width="25%"><strong>Total Cuota de Afiliación a Cobrar:</strong> Bs. {$montoBsFormatted}</td>
    </tr>
</table>

        <div class="subtitle">CONTRATANTE</div>
<table class="receipt-table">
    <table>
        <td width="50%"><strong>Nombres y Apellidos:</strong> {$contratanteFullName}</td
        ><td width="50%"><strong>C.I. / R.I.F./ Pasaporte:</strong> {$contratanteIdNumber}</td
        ><td width="50%"><strong>Nacionalidad:</strong> {$contratanteNacionalidad}</td
        ><td width="50%"><strong>Tipo de persona:</strong> {$contratanteTipoPersona}</td
        ><td width="50%"><strong>Razón Social:</strong> {$contratanteRazonSocial}</td
    </tr>
    <tr>
        <td colspan="5"><strong>Dirección de Cobro:</strong> {$contratanteDireccionCobro}</td
    >
    </tr>
    <tr>
        <td width="50%"><strong>Teléfono:</strong> {$contratanteTelefono}</td
        ><td colspan="4"><strong>Correo Electrónico:</strong> {$contratanteEmail}</td
    >
    </tr>
</table>

<?php if ($tieneContratanteDiferente): ?>
<div style="background: #fff3cd; border: 1px solid #ffeeba; padding: 5px; margin-top: 5px; font-size: 9px; text-align: center; color: #856404;">
    <i class="fas fa-info-circle"></i> El contratante es diferente al afiliado titular
</div>

        <div class="subtitle">AFILIADO TITULAR</div>
        <table>
            <tr><td width="33%"><strong>Nombres y Apellidos:</strong> {$fullName}</td>
            <td><strong>C.I. / R.I.F./ Pasaporte:</strong> {$idNumber}</td>
            <td><strong>Nacionalidad:</strong> {$nacionalidad}</td>
            <td><strong>Sexo:</strong> {$sexo}</td>
            <td><strong>Estado Civil:</strong> {$estadoCivil}</td>
            <td><strong>Profesión:</strong> {$profesion}</td></tr>
            <tr><td colspan="3"><strong>Dirección:</strong> {$direccionCobro}</td>
            <td><strong>Teléfono:</strong> {$telefono}</td>
            <td colspan="2"><strong>Correo Electrónico:</strong> {$email}</td></tr>
        </table>

        <div class="subtitle">DATOS DEL CONTRATO</div>
        <table>
            <tr><td width="25%"><strong>Fecha Emisión:</strong> {$fechaEmision}</td>
            <td width="25%"><strong>Vigencia:</strong> Desde {$fechaInicio}</td>
            <td width="25%"><strong>Hasta:</strong> {$fechaVencimiento}</td>
            <td width="25%"><strong>Frecuencia de Pago:</strong> MENSUAL</td></tr>
            <tr><td colspan="2"><strong>Sucursal:</strong> {$sucursal}</td>
            <td colspan="2"><strong>Moneda:</strong> Bs.</td></tr>
            <tr><td colspan="4"><strong>Apellido y Nombre del Intermediario de la Actividad Aseguradora:</strong> {$intermediarioNombre}<br>
            <strong>Código de Intermediario:</strong> {$intermediarioCodigo}</td></tr>
        </table>

        <div class="subtitle">PLAN CONTRATADO</div>
        <table>
            <tr><th width="20%">NOMBRE DEL PLAN</th><th width="20%">Deducible</th><th width="20%">Límite de Cobertura</th><th width="40%">Descripción de los Servicios</th></tr>
            <tr><td class="text-center">{$planNombre}</td><td class="text-center">NO APLICA</td><td class="text-center">{$coberturaValor}</td><td class="text-center">{$planNombre}</td></tr>
        </table>

        <table>
            <tr><th width="30%">DESCRIPCIÓN DE SERVICIOS</th><th width="15%">PLAZO DE ESPERA (P/E)</th><th width="55%">DESCRIPCIÓN</th></tr>
            {$servicesHtml}
<tr>
                <th colspan="3" style="background: #f0f0f0; font-weight: 600; text-align: center;">DE LAS EXCLUSIONES</th>
            </tr>
            <tr><td colspan="3">{$exclusionesTexto}</td
            </tr>        </table>

        <div class="subtitle">AFILIADOS</div>
        <table>
            <tr><th>Apellidos y Nombres</th><th>C.I. Nº</th><th>Parentesco</th><th>Cuota Anual</th></tr>
            <tr><td>{$fullName}</td><td>{$idNumber}</td><td class="text-center">Titular</td><td class="text-center">Bs. {$montoBsFormatted}</td></td>
            <tr><td colspan="3" class="text-right"><strong>TOTAL CUOTA A COBRAR POR EL PERÍODO:</strong></td><td class="text-center"><strong>Bs. {$montoBsFormatted}</strong></td></tr>
        </table>

        <div class="subtitle">BENEFICIARIO EN CASO DE MUERTE DEL AFILIADO TITULAR</div>
        <table>
            <tr><td width="50%"><strong>Apellidos y Nombres:</strong> {$fullName}</td>
            <td width="25%"><strong>C.I. Nº:</strong> {$idNumber}</td>
            <td width="25%"><strong>Parentesco:</strong> Titular</td></tr>
            <tr><td colspan="3">En caso de muerte de algún otro AFILIADO en este contrato el BENEFICIARIO es el AFILIADO TITULAR</td></tr>
        </table>

        <div class="subtitle">ANEXOS QUE FORMAN PARTE DEL CONTRATO</div>
        <table><tr><td>1. Anexo de Servicios de Maternidad (opcional)</td></tr></table>

        <div class="subtitle">DATOS DEL RECIBO Nº. {$receiptNum}</div>
        <table>
            <tr><td width="25%"><strong>Fecha Emisión:</strong> {$fechaEmision}</td>
            <td width="25%"><strong>Vigencia del Recibo:</strong> Desde {$coverageFrom}</td>
            <td width="25%"><strong>Hasta:</strong> {$coverageTo}</td>
            <td width="25%"><strong>Total a Cobrar:</strong> Bs. {$montoBsFormattedDatosPago}</td></tr>
            <tr><td><strong>Fecha de Pago:</strong> {$fechaPago}</td>
            <td><strong>Forma de Pago:</strong> {$metodoPago}</td>
            <td><strong>Moneda de Pago:</strong> Bs.</td>
            <td><strong>Banco:</strong> -</td></tr>
            <tr><td><strong>No. Cheque o Tarjeta:</strong> {$referencia}</td>
            <td colspan="3"><strong>Lugar de Pago:</strong> Oficinas SISPSA</td></tr>
        </table>

        <div class="subtitle">DECLARACIÓN Y FIRMAS</div>
        <div class="legal"><strong>El CONTRATANTE</strong> {$fullName}, C.I. {$idNumber}</div>
        <div class="legal">Conjuntamente con este documento deben entregarse al CONTRATANTE o AFILIADO TITULAR las condiciones generales, las condiciones particulares, los anexos, si los hubiere, copia de la solicitud de afiliación y demás documentos que formen parte del contrato.</div>
        <div class="legal">La Empresa se obliga a atender y resolver cualquier denuncia, queja, reclamo o sugerencia que presente el Contratante, Afiliado, Titular o Beneficiario, con ocasión de las controversias derivadas de la ejecución del presente contrato de medicina prepagada, a través de la figura del Defensor del Tomador, Asegurado o Beneficiario, Contratante, Usuario y Afiliado. A tales fines, el Contratante, Afiliado Titular o Beneficiario, podrá acudir a la respectiva Unidad de Defensa, o comunicarse a través de los mecanismos dispuestos para ello.</div>

        <table style="margin-top: 20px;">
            <tr><td width="50%" style="border: none; text-align: center;"><strong>EL CONTRATANTE</strong><br>Nombres y Apellidos: {$fullName}<br>C.I. Nº: {$idNumber}<br><div class="signature-line">Firma:</div></td>
            <td width="50%" style="border: none; text-align: center;"><strong>Representante de SISPSA</strong><br>Nombres y Apellidos:<br>C.I. Nº:<br><div class="signature-line">Firma:</div></td>
            </tr>
        </table>

        <div class="legal">Autorizado según documento registrado en: Registro Mercantil Primero del Estado Zulia Fecha: 28/01/2025 bajo el Nº: 1 Folio:</div>
        <div class="footer">Este CUADRO RECIBO DE AFILIACIÓN sólo tendrá validez si está fechado y firmado por la persona autorizada<br>Aprobado por la Superintendencia de la Actividad Aseguradora según Providencia Nº SAA-SUT-34169 de fecha 13/02/2025<br>Generado: {$fechaEmision} - Recibo N° {$receiptNum}</div>
    </div>
</div>
</body>
</html>
HTML;
    }

    public static function getReceiptsForPayment($paymentId)
    {
        return Receipt::find()->where(['payment_id' => $paymentId])->orderBy(['id' => SORT_ASC])->all();
    }

    public static function getReceiptForInstallment($installmentId)
    {
        return Receipt::findOne(['installment_id' => $installmentId]);
    }
}
