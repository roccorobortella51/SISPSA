<?php

namespace app\components;

use Yii;
use kartik\mpdf\Pdf;
use app\models\PlanServicios;

class NotificationHelper
{
    public static function sendReceiptNotification($receipt, $user, $payment)
    {
        // Skip if no email
        if (empty($user->email)) {
            Yii::info("No email for user {$user->id}", 'notification');
            return false;
        }

        try {
            // Get contract and plan info
            $contract = $receipt->contract;
            $planName = $contract && $contract->plan ? $contract->plan->nombre : 'N/A';
            $clinicaName = $contract && $contract->clinica ? $contract->clinica->nombre : 'N/A';

            // Format data
            $fullName = trim($user->nombres . ' ' . $user->apellidos);
            $receiptNumber = $receipt->receipt_number;

            // Convert to float
            $amountUSD = (float)$payment->monto_pagado;
            $amountBs = (float)$payment->monto_usd;

            $date = date('d/m/Y', strtotime($payment->fecha_pago));
            $coverageFrom = $receipt->coverage_start_date ? date('d/m/Y', strtotime($receipt->coverage_start_date)) : 'N/A';
            $coverageTo = $receipt->coverage_end_date ? date('d/m/Y', strtotime($receipt->coverage_end_date)) : 'N/A';
            $contractNumber = $contract ? $contract->nrocontrato : ($contract ? $contract->id : 'N/A');
            $installmentNumber = $receipt->installment ? $receipt->installment->numero_cuota : 'N/A';

            // Get logo
            $logoBase64 = '';
            $logoPath = Yii::getAlias('@webroot') . '/img/sispsalogo.jpg';
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/jpeg;base64,' . base64_encode($logoData);
            }

            $subject = 'Su recibo ha sido generado - SISPSA';

            // Format numbers for display
            $amountUSDFormatted = number_format($amountUSD, 2);
            $amountBsFormatted = number_format($amountBs, 2);

            // Generate SIMPLE HTML for PDF (bulletproof version)
            $pdfHtml = self::generateSimplePDFHtml(
                $logoBase64,
                $fullName,
                $receiptNumber,
                $contractNumber,
                $installmentNumber,
                $amountUSDFormatted,
                $amountBsFormatted,
                $date,
                $coverageFrom,
                $coverageTo,
                $planName,
                $clinicaName,
                $user,
                $contract,
                $payment
            );

            // Generate PDF from simple HTML
            $pdfContent = self::generatePDFFromHtml($pdfHtml);

            // HTML message for email body
            $htmlMessage = self::getHtmlMessage(
                $logoBase64,
                $fullName,
                $receiptNumber,
                $amountUSDFormatted,
                $amountBsFormatted,
                $date,
                $planName
            );

            // Plain text version
            $plainTextMessage = self::getPlainTextMessage(
                $fullName,
                $receiptNumber,
                $amountUSDFormatted,
                $amountBsFormatted,
                $date,
                $planName
            );

            // Send email with PDF attachment
            $message = Yii::$app->mailer->compose()
                ->setFrom(['sispsa.notificaciones@gmail.com' => 'SISPSA Notificaciones'])
                ->setTo($user->email)
                ->setSubject($subject)
                ->setHtmlBody($htmlMessage)
                ->setTextBody($plainTextMessage);

            // Attach PDF file
            $message->attachContent($pdfContent, [
                'fileName' => "Recibo_{$receiptNumber}.pdf",
                'contentType' => 'application/pdf',
            ]);

            $result = $message->send();

            if ($result) {
                Yii::info("Email con PDF adjunto enviado a {$user->email} - Recibo {$receiptNumber}", 'notification');
            } else {
                Yii::error("Error al enviar email a {$user->email}", 'notification');
            }

            return $result;
        } catch (\Exception $e) {
            Yii::error("Error en notificacion: " . $e->getMessage(), 'notification');
            return false;
        }
    }

    private static function generateSimplePDFHtml($logoBase64, $fullName, $receiptNumber, $contractNumber, $installmentNumber, $amountUSDFormatted, $amountBsFormatted, $date, $coverageFrom, $coverageTo, $planName, $clinicaName, $user, $contract, $payment)
    {
        // ============================================================
        // VERIFICAR SI HAY CONTRATANTE DIFERENTE
        // ============================================================
        $tieneContratanteDiferente = !empty($user->tiene_contratante_diferente) && $user->tiene_contratante_diferente == true;

        if ($tieneContratanteDiferente) {
            // Usar datos del CONTRATANTE (diferente al afiliado)
            $contratanteNombre = trim(($user->nombre_contratante ?? '') . ' ' . ($user->apellido_contratante ?? ''));
            if (empty($contratanteNombre)) {
                $contratanteNombre = trim(($user->nombre_contratante ?? '') . ' ' . ($user->apellido_contratante ?? ''));
            }
            $contratanteIdNumber = ($user->tipo_cedula_contratante ?: 'V') . '-' . ($user->cedula_contratante ?? '');
            $contratanteNacionalidad = $user->nacionalidad_contratante ?: 'Venezolana';
            $contratanteDireccion = $user->direccion_cobro_contratante ?: 'N/A';
            $contratanteTelefono = $user->telefono_celular_contratante ?: 'N/A';
            $contratanteEmail = $user->email_contratante ?: 'N/A';
            $contratanteProfesion = $user->profesion_contratante ?: 'N/A';
            $contratanteEstadoCivil = $user->estado_civil_contratante ?: 'No especificado';

            // Tipo de persona (Natural o Juridica)
            $tipoPersona = !empty($user->razon_social) ? 'Juridica' : 'Natural';
            $razonSocial = $user->razon_social ?: '-';
            $rif = $user->rif ?: '-';
        } else {
            // Usar datos del AFILIADO TITULAR (comportamiento normal)
            $contratanteNombre = trim($user->nombres . ' ' . $user->apellidos);
            $contratanteIdNumber = ($user->tipo_cedula ?: 'V') . '-' . $user->cedula;
            $contratanteNacionalidad = $user->nacionalidad ?: 'Venezolana';
            $contratanteDireccion = $user->direccion_cobro ?: ($user->direccion ?: 'N/A');
            $contratanteTelefono = $user->telefono ?: 'N/A';
            $contratanteEmail = $user->email ?: 'N/A';
            $contratanteProfesion = $user->profesion ?: 'N/A';
            $contratanteEstadoCivil = $user->estado_civil ?: 'No especificado';
            $tipoPersona = 'Natural';
            $razonSocial = '-';
            $rif = '-';
        }

        // Datos del AFILIADO TITULAR (siempre se muestran en su seccion)
        $afiliadoNombre = trim($user->nombres . ' ' . $user->apellidos);
        $afiliadoIdNumber = ($user->tipo_cedula ?: 'V') . '-' . $user->cedula;
        $afiliadoNacionalidad = $user->nacionalidad ?: 'Venezolana';
        $afiliadoDireccion = $user->direccion_cobro ?: ($user->direccion ?: 'N/A');
        $afiliadoTelefono = $user->telefono ?: 'N/A';
        $afiliadoEmail = $user->email ?: 'N/A';
        $afiliadoSexo = $user->sexo ?: 'No especificado';
        $afiliadoEstadoCivil = $user->estado_civil ?: 'No especificado';
        $afiliadoProfesion = $user->profesion ?: 'N/A';

        $contratoNumero = $contract->nrocontrato ?: $contract->id;
        $fechaEmisionContrato = date('d/m/Y', strtotime($contract->created_at));
        $fechaInicio = $contract ? date('d/m/Y', strtotime($contract->fecha_ini)) : 'N/A';
        $fechaVencimiento = ($contract && $contract->fecha_ven) ? date('d/m/Y', strtotime($contract->fecha_ven)) : 'Sin fecha';
        $sucursal = $contract->clinica ? $contract->clinica->nombre : 'N/A';
        $metodoPago = $payment->metodo_pago ?: 'N/A';
        $referencia = $payment->numero_referencia_pago ?: 'N/A';

        // Get plan services HTML
        $servicesHtml = '';
        if ($contract && $contract->plan_id) {
            $dbServices = PlanServicios::getServicesForPlan($contract->plan_id, $contract->clinica_id);
            if (!empty($dbServices)) {
                foreach ($dbServices as $svc) {
                    $servicesHtml .= '<tr>';
                    $servicesHtml .= '<td style="border: 1px solid #000; padding: 6px;">' . htmlspecialchars($svc->servicio_nombre) . '</td>';
                    $servicesHtml .= '<td style="border: 1px solid #000; padding: 6px; text-align: center;">' . ($svc->plazo_espera ?: 'NO APLICA') . '</td>';
                    $servicesHtml .= '<td style="border: 1px solid #000; padding: 6px;">' . ($svc->descripcion ?: 'Servicio incluido en el plan') . '</td>';
                    $servicesHtml .= '</tr>';
                }
            }
        }

        if (empty($servicesHtml)) {
            $servicesHtml = '<tr><td colspan="3" style="text-align: center;">No hay servicios registrados</td></tr>';
        }

        $exclusionesTexto = $contract->plan && !empty($contract->plan->exclusiones) ? $contract->plan->exclusiones : 'No aplican exclusiones';
        $coberturaValor = $contract->plan ? number_format($contract->plan->cobertura, 2) : '0.00';

        // Construir HTML de manera segura
        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<title>Recibo de Afiliacion ' . $receiptNumber . '</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }';
        $html .= '.receipt { max-width: 1200px; margin: 0 auto; }';
        $html .= '.logo-container { text-align: center; padding: 20px; border-bottom: 1px solid #ccc; }';
        $html .= '.logo-container img { max-width: 150px; }';
        $html .= '.registration-info { text-align: center; padding: 10px; background: #f5f5f5; }';
        $html .= '.title-container { text-align: center; padding: 15px; background: #1a3a6e; color: white; }';
        $html .= '.title-container h1 { margin: 0; font-size: 18px; }';
        $html .= '.title-container h2 { margin: 5px 0 0 0; font-size: 14px; }';
        $html .= '.content { padding: 20px; }';
        $html .= '.subtitle { background: #e8e8e8; padding: 8px; margin: 20px 0 10px 0; font-weight: bold; font-size: 12px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10px; }';
        $html .= 'td, th { border: 1px solid #000; padding: 6px; vertical-align: top; }';
        $html .= 'th { background: #f0f0f0; text-align: center; }';
        $html .= '.text-center { text-align: center; }';
        $html .= '.text-right { text-align: right; }';
        $html .= '.footer { font-size: 8px; text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ccc; }';
        $html .= '.legal { font-size: 9px; margin: 15px 0; text-align: justify; }';
        $html .= '.signature-line { margin-top: 30px; border-top: 1px solid #000; padding-top: 5px; }';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<div class="receipt">';

        // Logo
        $html .= '<div class="logo-container">';
        $html .= '<img src="' . $logoBase64 . '" alt="SISPSA Logo">';
        $html .= '</div>';

        // Registration info
        $html .= '<div class="registration-info">';
        $html .= '<div>Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013</div>';
        $html .= '<div>R.I.F.: J-506549220</div>';
        $html .= '</div>';

        // Title
        $html .= '<div class="title-container">';
        $html .= '<h1>Contrato de Servicios de Medicina Prepagada</h1>';
        $html .= '<h2>CUADRO RECIBO DE AFILIACION</h2>';
        $html .= '</div>';

        $html .= '<div class="content">';

        // Header Table
        $html .= '<table class="receipt-table">';
        $html .= '<tr>';
        $html .= '<td width="25%"><strong>Contrato Nº.:</strong> ' . $contratoNumero . '</td>';
        $html .= '<td width="25%"><strong>Recibo Nº.:</strong> ' . $receiptNumber . '</td>';
        $html .= '<td width="25%"><strong>Cuota Nº.:</strong> ' . $installmentNumber . ' de 12</td>';
        $html .= '<td width="25%"><strong>Total Cuota de Afiliacion a Cobrar:</strong> Bs. ' . $amountBsFormatted . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // CONTRATANTE Section
        $html .= '<div class="subtitle">CONTRATANTE</div>';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td width="33%"><strong>Nombres y Apellidos:</strong> ' . $contratanteNombre . '</td>';
        $html .= '<td width="33%"><strong>C.I. / R.I.F./ Pasaporte:</strong> ' . $contratanteIdNumber . '</td>';
        $html .= '<td width="34%"><strong>Nacionalidad:</strong> ' . $contratanteNacionalidad . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td><strong>Tipo de persona:</strong> ' . $tipoPersona . '</td>';
        $html .= '<td><strong>Razon Social:</strong> ' . $razonSocial . '</td>';
        $html .= '<td>' . ($tipoPersona == 'Juridica' ? '<strong>R.I.F.:</strong> ' . $rif : '&nbsp;') . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="3"><strong>Direccion de Cobro:</strong> ' . $contratanteDireccion . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td><strong>Telefono:</strong> ' . $contratanteTelefono . '</td>';
        $html .= '<td colspan="2"><strong>Correo Electronico:</strong> ' . $contratanteEmail . '</td>';
        $html .= '</tr>';
        if ($tieneContratanteDiferente) {
            $html .= '<tr>';
            $html .= '<td><strong>Profesion:</strong> ' . $contratanteProfesion . '</td>';
            $html .= '<td><strong>Estado Civil:</strong> ' . $contratanteEstadoCivil . '</td>';
            $html .= '<td>&nbsp;</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        // AFILIADO TITULAR Section
        $html .= '<div class="subtitle">AFILIADO TITULAR</div>';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td width="25%"><strong>Nombres y Apellidos:</strong> ' . $afiliadoNombre . '</td>';
        $html .= '<td width="25%"><strong>C.I. / R.I.F./ Pasaporte:</strong> ' . $afiliadoIdNumber . '</td>';
        $html .= '<td width="25%"><strong>Nacionalidad:</strong> ' . $afiliadoNacionalidad . '</td>';
        $html .= '<td width="25%"><strong>Sexo:</strong> ' . $afiliadoSexo . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td><strong>Estado Civil:</strong> ' . $afiliadoEstadoCivil . '</td>';
        $html .= '<td><strong>Profesion:</strong> ' . $afiliadoProfesion . '</td>';
        $html .= '<td colspan="2">&nbsp;</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="3"><strong>Direccion:</strong> ' . $afiliadoDireccion . '</td>';
        $html .= '<td><strong>Telefono:</strong> ' . $afiliadoTelefono . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="4"><strong>Correo Electronico:</strong> ' . $afiliadoEmail . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // DATOS DEL CONTRATO Section
        $html .= '<div class="subtitle">DATOS DEL CONTRATO</div>';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td width="25%"><strong>Fecha Emision:</strong> ' . $fechaEmisionContrato . '</td>';
        $html .= '<td width="25%"><strong>Vigencia:</strong> Desde ' . $fechaInicio . '</td>';
        $html .= '<td width="25%"><strong>Hasta:</strong> ' . $fechaVencimiento . '</td>';
        $html .= '<td width="25%"><strong>Frecuencia de Pago:</strong> MENSUAL</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="2"><strong>Sucursal:</strong> ' . $sucursal . '</td>';
        $html .= '<td colspan="2"><strong>Moneda:</strong> Bs.</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // PLAN CONTRATADO Section
        $html .= '<div class="subtitle">PLAN CONTRATADO</div>';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="25%">NOMBRE DEL PLAN</th>';
        $html .= '<th width="25%">Deducible</th>';
        $html .= '<th width="25%">Limite de Cobertura</th>';
        $html .= '<th width="25%">Descripcion de los Servicios</th>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td class="text-center">' . $planName . '</td>';
        $html .= '<td class="text-center">NO APLICA</td>';
        $html .= '<td class="text-center">' . $coberturaValor . '</td>';
        $html .= '<td class="text-center">' . $planName . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // SERVICIOS Table
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="40%">DESCRIPCION DE SERVICIOS</th>';
        $html .= '<th width="20%">PLAZO DE ESPERA (P/E)</th>';
        $html .= '<th width="40%">DESCRIPCION</th>';
        $html .= '</tr>';
        $html .= $servicesHtml;
        $html .= '<tr>';
        $html .= '<th colspan="3" style="background: #f0f0f0; text-align: center;">DE LAS EXCLUSIONES</th>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="3">' . $exclusionesTexto . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // AFILIADOS Table
        $html .= '<div class="subtitle">AFILIADOS</div>';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th>Apellidos y Nombres</th>';
        $html .= '<th>C.I. Nº</th>';
        $html .= '<th>Parentesco</th>';
        $html .= '<th>Cuota Anual</th>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td>' . $afiliadoNombre . '</td>';
        $html .= '<td>' . $afiliadoIdNumber . '</td>';
        $html .= '<td class="text-center">Titular</td>';
        $html .= '<td class="text-center">Bs. ' . $amountBsFormatted . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="3" class="text-right"><strong>TOTAL CUOTA A COBRAR POR EL PERIODO:</strong></td>';
        $html .= '<td class="text-center"><strong>Bs. ' . $amountBsFormatted . '</strong></td>';
        $html .= '</tr>';
        $html .= '</table>';

        // BENEFICIARIO Section
        $html .= '<div class="subtitle">BENEFICIARIO EN CASO DE MUERTE DEL AFILIADO TITULAR</div>';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td width="50%"><strong>Apellidos y Nombres:</strong> ' . $afiliadoNombre . '</td>';
        $html .= '<td width="25%"><strong>C.I. Nº:</strong> ' . $afiliadoIdNumber . '</td>';
        $html .= '<td width="25%"><strong>Parentesco:</strong> Titular</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td colspan="3">En caso de muerte de algun otro AFILIADO en este contrato el BENEFICIARIO es el AFILIADO TITULAR</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // ANEXOS
        $html .= '<div class="subtitle">ANEXOS QUE FORMAN PARTE DEL CONTRATO</div>';
        $html .= '<table><tr><td>1. Anexo de Servicios de Maternidad (opcional)</td></tr></table>';

        // DATOS DEL RECIBO Section
        $html .= '<div class="subtitle">DATOS DEL RECIBO Nº. ' . $receiptNumber . '</div>';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td width="25%"><strong>Fecha Emision:</strong> ' . $date . '</td>';
        $html .= '<td width="25%"><strong>Vigencia del Recibo:</strong> Desde ' . $coverageFrom . '</td>';
        $html .= '<td width="25%"><strong>Hasta:</strong> ' . $coverageTo . '</td>';
        $html .= '<td width="25%"><strong>Total a Cobrar:</strong> Bs. ' . $amountBsFormatted . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td><strong>Fecha de Pago:</strong> ' . $date . '</td>';
        $html .= '<td><strong>Forma de Pago:</strong> ' . $metodoPago . '</td>';
        $html .= '<td><strong>Moneda de Pago:</strong> Bs.</td>';
        $html .= '<td><strong>Banco:</strong> -</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td><strong>No. Cheque o Tarjeta:</strong> ' . $referencia . '</td>';
        $html .= '<td colspan="3"><strong>Lugar de Pago:</strong> Oficinas SISPSA</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // DECLARACION Y FIRMAS
        $html .= '<div class="subtitle">DECLARACION Y FIRMAS</div>';
        $html .= '<div class="legal"><strong>El CONTRATANTE</strong> ' . $contratanteNombre . ', C.I. ' . $contratanteIdNumber . '</div>';
        $html .= '<div class="legal">Conjuntamente con este documento deben entregarse al CONTRATANTE o AFILIADO TITULAR las condiciones generales, las condiciones particulares, los anexos, si los hubiere, copia de la solicitud de afiliacion y demas documentos que formen parte del contrato.</div>';
        $html .= '<div class="legal">La Empresa se obliga a atender y resolver cualquier denuncia, queja, reclamo o sugerencia que presente el Contratante, Afiliado, Titular o Beneficiario, con ocasion de las controversias derivadas de la ejecucion del presente contrato de medicina prepagada, a traves de la figura del Defensor del Tomador, Asegurado o Beneficiario, Contratante, Usuario y Afiliado. A tales fines, el Contratante, Afiliado Titular o Beneficiario, podra acudir a la respectiva Unidad de Defensa, o comunicarse a traves de los mecanismos dispuestos para ello.</div>';

        $html .= '<table style="margin-top: 20px;">';
        $html .= '<tr>';
        $html .= '<td width="50%" style="border: none; text-align: center;">';
        $html .= '<strong>EL CONTRATANTE</strong><br>';
        $html .= 'Nombres y Apellidos: ' . $contratanteNombre . '<br>';
        $html .= 'C.I. Nº: ' . $contratanteIdNumber . '<br>';
        $html .= '<div class="signature-line">Firma:</div>';
        $html .= '</td>';
        $html .= '<td width="50%" style="border: none; text-align: center;">';
        $html .= '<strong>Representante de SISPSA</strong><br>';
        $html .= 'Nombres y Apellidos:<br>';
        $html .= 'C.I. Nº:<br>';
        $html .= '<div class="signature-line">Firma:</div>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $html .= '<div class="legal">Autorizado segun documento registrado en: Registro Mercantil Primero del Estado Zulia Fecha: 28/01/2025 bajo el Nº: 1 Folio:</div>';
        $html .= '<div class="footer">Este CUADRO RECIBO DE AFILIACION solo tendra validez si esta fechado y firmado por la persona autorizada<br>Aprobado por la Superintendencia de la Actividad Aseguradora segun Providencia Nº SAA-SUT-34169 de fecha 13/02/2025<br>Generado: ' . $date . ' - Recibo N° ' . $receiptNumber . '</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }

    private static function generatePDFFromHtml($html)
    {
        // Create mPDF instance with bulletproof settings
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'tempDir' => sys_get_temp_dir(),
        ]);

        // Set mPDF options to be more tolerant
        $mpdf->ignore_invalid_utf8 = true;
        $mpdf->allow_html_optional_endtags = true;
        $mpdf->use_kwt = true;
        $mpdf->showImageErrors = false;
        $mpdf->autoLangToFont = true;

        // Write HTML
        $mpdf->WriteHTML($html);

        // Return PDF as string
        return $mpdf->Output('', 'S');
    }

    private static function getHtmlMessage($logoBase64, $fullName, $receiptNumber, $amountUSDFormatted, $amountBsFormatted, $date, $planName)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Recibo SISPSA</title></head>
<body style="font-family: Arial, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; background: #1a3a6e; color: white; padding: 20px; border-radius: 10px;">
            <h1>Comprobante de Pago</h1>
            <p>Recibo de Afiliacion</p>
        </div>
        <p>Estimado(a) <strong>{$fullName}</strong>,</p>
        <p>Nos complace informarle que su pago ha sido registrado exitosamente.</p>
        <p><strong style="color: green;">Adjunto encontrara su recibo en formato PDF</strong></p>
        <div style="background: #f5f5f5; padding: 15px; margin: 20px 0;">
            <p><strong>Numero de Recibo:</strong> {$receiptNumber}</p>
            <p><strong>Monto Pagado:</strong> $ {$amountUSDFormatted} USD / Bs. {$amountBsFormatted}</p>
            <p><strong>Fecha de Pago:</strong> {$date}</p>
            <p><strong>Plan:</strong> {$planName}</p>
        </div>
        <hr>
        <p style="font-size: 11px; color: #888;">SISPSA - Sistema Integral de Salud Programado</p>
    </div>
</body>
</html>
HTML;
    }

    private static function getPlainTextMessage($fullName, $receiptNumber, $amountUSDFormatted, $amountBsFormatted, $date, $planName)
    {
        return "Estimado(a) {$fullName},\n\n"
            . "Nos complace informarle que su pago ha sido registrado exitosamente.\n\n"
            . "Adjunto encontrara su recibo en formato PDF.\n\n"
            . "RESUMEN:\n"
            . "- Recibo: {$receiptNumber}\n"
            . "- Monto: $ {$amountUSDFormatted} USD / Bs. {$amountBsFormatted}\n"
            . "- Fecha: {$date}\n"
            . "- Plan: {$planName}\n\n"
            . "SISPSA - Sistema Integral de Salud Programado";
    }
}
