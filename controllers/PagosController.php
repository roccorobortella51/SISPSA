<?php

namespace app\controllers;

use Yii;
use yii\helpers\Html;
use app\models\Pagos;
use app\models\PagosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\models\TasaCambio;
use app\components\UserHelper;
use app\models\RmClinica;
use app\models\Cuotas;
use app\models\UserDatos;
use app\models\Contratos;
use app\models\Receipt;
use app\components\ReceiptGenerator;
use app\components\NotificationHelper;

/**
 * PagosController implements the CRUD actions for Pagos model.
 */
class PagosController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Obtiene la tasa de cambio actual
     */
    private function getTasaCambio()
    {
        $tasa = TasaCambio::find()
            ->where(['fecha' => date('Y-m-d')])
            ->one();

        if ($tasa) {
            return $tasa->tasa_cambio;
        }

        // Si no hay tasa para hoy, buscar la más reciente
        $ultimaTasa = TasaCambio::find()
            ->orderBy(['fecha' => SORT_DESC])
            ->one();

        return $ultimaTasa ? $ultimaTasa->tasa_cambio : 1.0;
    }

    public function actionClinica($id)
    {
        $searchModel = new PagosSearch();
        $dataProvider = $searchModel->searchClinica($this->request->queryParams, null, $id);
        $clinica = RmClinica::findOne($id);
        return $this->render('clinica', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'clinica' => $clinica,
        ]);
    }

    /**
     * Lists all Pagos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PagosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionTasacambio()
    {
        // Ejecuta el action de otro controlador sin redirección
        $resultado = Yii::$app->runAction('site/tasacambio');

        // Puedes usar el resultado
        return $resultado;
    }

    /**
     * Displays a single Pagos model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate($user_id = null, $contrato_id = null)
    {
        $model = new Pagos();
        $model->user_id = $user_id;

        // ===== SET DEFAULT TASA WITH 2 DECIMALS =====
        $tasaCambio = $this->getTasaCambio();
        $model->tasa = number_format($tasaCambio, 2, '.', '');

        // Get contract info
        $selectedContrato = null;
        $cuotas = [];

        if ($contrato_id) {
            $selectedContrato = Contratos::findOne($contrato_id);
            if ($selectedContrato) {
                // Get pending installments for this contract
                $cuotas = Cuotas::find()
                    ->where(['contrato_id' => $contrato_id])
                    ->andWhere(['in', 'estatus', ['pendiente', 'en_gracias', 'vencida']])
                    ->orderBy(['numero_cuota' => SORT_ASC])
                    ->all();
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Set additional fields
                $model->fecha_pago = $model->fecha_pago ?: date('Y-m-d');
                $model->estatus = 'Por Conciliar';

                // Ensure tasa has 2 decimals
                if ($model->tasa) {
                    $model->tasa = number_format((float)$model->tasa, 2, '.', '');
                }

                // Handle file upload
                if ($model->imagen_prueba_file) {
                    $model->imagen_prueba = $model->upload();
                }

                if ($model->save()) {
                    // ==== UPDATE INSTALLMENTS ====
                    $selectedCuotas = Yii::$app->request->post('selected_cuotas', []);

                    // Get the exchange rate from the payment
                    $tasaCambio = $model->tasa ?? 0;

                    if (!empty($selectedCuotas)) {
                        foreach ($selectedCuotas as $cuotaId) {
                            $cuota = Cuotas::findOne($cuotaId);
                            if ($cuota) {
                                $cuota->id_pago = $model->id;
                                $cuota->estatus = 'pagada';
                                $cuota->fecha_pago = $model->fecha_pago;
                                $cuota->rate_usd_bs = $tasaCambio;
                                $cuota->save(false);
                            }
                        }
                    }

                    // ==== GENERATE RECEIPTS ====
                    $generatedReceipts = ReceiptGenerator::generateForPayment($model);

                    // ==== SEND ONE COMBINED EMAIL WITH ALL RECEIPTS ====
                    $emailSent = false;
                    $user = null;

                    if (!empty($generatedReceipts)) {
                        $user = UserDatos::findOne($model->user_id);
                        if ($user && $user->email) {
                            try {
                                $emailSent = $this->sendCombinedReceiptEmail($generatedReceipts, $user, $model);
                            } catch (\Exception $e) {
                                Yii::error("Error sending email notifications: " . $e->getMessage());
                                Yii::$app->session->setFlash('warning', 'El pago se registró correctamente pero hubo un error al enviar el correo: ' . $e->getMessage());
                            }
                        }
                    }

                    $transaction->commit();

                    $receiptCount = count($generatedReceipts);
                    $successMessage = '✅ Pago registrado exitosamente. Se han generado <strong style="color: #0078d4; font-size: 1.2em; background: #e3f2fd; padding: 2px 12px; border-radius: 4px;">' . $receiptCount . '</strong> recibo(s).';

                    if ($emailSent && $user) {
                        $successMessage .= ' Se ha enviado un correo electrónico con los recibos a <strong>' . Html::encode($user->email) . '</strong>.';

                        // Add EYE-CATCHING SPAM warning
                        $spamWarning = '<br><br>
    <style>
        @keyframes spamPulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(245, 124, 0, 0.3); transform: scale(1); }
            50% { box-shadow: 0 4px 40px rgba(245, 124, 0, 0.5); transform: scale(1.005); }
        }
        @keyframes spamShake {
            0%, 100% { transform: rotate(0deg); }
            5% { transform: rotate(8deg); }
            10% { transform: rotate(-8deg); }
            15% { transform: rotate(5deg); }
            20% { transform: rotate(-5deg); }
            25% { transform: rotate(0deg); }
        }
        @keyframes spamBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        @keyframes spamGlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes spamPatternMove {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(40px) translateY(40px); }
        }
        @keyframes spamLetterPulse {
            0%, 100% { transform: scale(1) rotate(0deg); }
            25% { transform: scale(1.1) rotate(-5deg); }
            50% { transform: scale(1) rotate(0deg); }
            75% { transform: scale(1.1) rotate(5deg); }
        }
        
        .spam-warning-container {
            margin: 15px 0 0 0;
            padding: 0;
            width: 100%;
            box-sizing: border-box;
        }
        
        .spam-warning {
            background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%) !important;
            border-left: 6px solid #f57c00 !important;
            border-radius: 12px !important;
            padding: 20px 28px !important;
            display: flex !important;
            align-items: center !important;
            gap: 20px !important;
            box-shadow: 0 4px 20px rgba(245, 124, 0, 0.25) !important;
            position: relative !important;
            overflow: hidden !important;
            animation: spamPulse 2.5s ease-in-out infinite !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .spam-warning::before {
            content: "" !important;
            position: absolute !important;
            top: -50% !important;
            left: -50% !important;
            width: 200% !important;
            height: 200% !important;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 20px,
                rgba(245, 124, 0, 0.05) 20px,
                rgba(245, 124, 0, 0.05) 40px
            ) !important;
            animation: spamPatternMove 8s linear infinite !important;
            pointer-events: none !important;
        }
        
        .spam-warning::after {
            content: "" !important;
            position: absolute !important;
            inset: -2px !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, #f57c00, #ff9800, #f57c00, #e65100) !important;
            background-size: 300% 300% !important;
            animation: spamGlow 3s ease-in-out infinite !important;
            z-index: -1 !important;
            opacity: 0.3 !important;
        }
        
        .spam-warning .spam-icon {
            background: linear-gradient(135deg, #f57c00, #e65100) !important;
            width: 54px !important;
            height: 54px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
            box-shadow: 0 4px 16px rgba(245, 124, 0, 0.4) !important;
            position: relative !important;
            z-index: 1 !important;
            animation: spamPulse 1.5s ease-in-out infinite !important;
        }
        
        .spam-warning .spam-icon i {
            color: white !important;
            font-size: 26px !important;
            animation: spamShake 2.5s ease-in-out infinite !important;
        }
        
        .spam-warning .spam-content {
            flex: 1 !important;
            position: relative !important;
            z-index: 1 !important;
        }
        
        .spam-warning .spam-title {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #e65100 !important;
            margin-bottom: 4px !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        
        .spam-warning .spam-title i {
            font-size: 18px !important;
            animation: spamShake 2s ease-in-out infinite !important;
        }
        
        .spam-warning .spam-text {
            font-size: 14px !important;
            font-weight: 500 !important;
            color: #4a3000 !important;
            line-height: 1.5 !important;
        }
        
        .spam-warning .spam-text strong {
            color: #e65100 !important;
        }
        
        spam-warning .spam-highlight {
            background: #ffc107 !important;
            color: #000000 !important;
            padding: 3px 14px !important;
            border-radius: 6px !important;
            font-weight: 800 !important;
            font-size: 14px !important;
            display: inline-block !important;
            box-shadow: 0 0 0 2px #ffc107, 0 0 20px rgba(255, 193, 7, 0.3) !important;
            animation: spamPulse 1.5s ease-in-out infinite !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
}
        
        .spam-warning .spam-email-icon {
            font-size: 36px !important;
            color: #e65100 !important;
            flex-shrink: 0 !important;
            position: relative !important;
            z-index: 1 !important;
            animation: spamBounce 2s ease-in-out infinite !important;
        }
        
        .spam-warning .spam-letter-icon {
            display: inline-block !important;
            animation: spamLetterPulse 2s ease-in-out infinite !important;
        }
        
        @media (max-width: 768px) {
            .spam-warning {
                flex-direction: column !important;
                text-align: center !important;
                padding: 20px !important;
            }
            .spam-warning .spam-title {
                justify-content: center !important;
            }
            .spam-warning .spam-text {
                font-size: 13px !important;
            }
            .spam-warning .spam-email-icon {
                font-size: 28px !important;
            }
            .spam-warning .spam-icon {
                width: 46px !important;
                height: 46px !important;
            }
            .spam-warning .spam-icon i {
                font-size: 22px !important;
            }
        }
    </style>
    
    <div class="spam-warning-container">
        <div class="spam-warning">
            <div class="spam-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="spam-content">
                <div class="spam-title">
                    <i class="fas fa-envelope"></i> 📧 ¡IMPORTANTE!
                </div>
                <div class="spam-text">
                    Informe a la Afiliada o Afiliado que el correo puede haber llegado a la carpeta de <strong class="spam-highlight">SPAM</strong>. 
                    Por favor, revise su bandeja de correo no deseado y marque el mensaje como 
                    <strong>"No es spam"</strong> para asegurar la entrega de futuros recibos.
                </div>
            </div>
            <div class="spam-email-icon">
                <span class="spam-letter-icon">📧</span>
            </div>
        </div>
    </div>';

                        $successMessage .= $spamWarning;
                    } elseif ($user && !$user->email) {
                        $successMessage .= ' El usuario no tiene email registrado para enviar la notificación.';
                    } else {
                        $successMessage .= ' No se pudo enviar el correo electrónico.';
                    }

                    Yii::$app->session->setFlash('success', $successMessage);

                    return $this->redirect(['pagos/view-receipts', 'payment_id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error al registrar el pago: ' . $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'cuotas' => $cuotas,
            'selectedContrato' => $selectedContrato,
        ]);
    }

    /**
     * Send ONE combined email with ALL receipts attached as PDFs
     * @param array $receipts
     * @param UserDatos $user
     * @param Pagos $payment
     * @return bool
     */
    private function sendCombinedReceiptEmail($receipts, $user, $payment)
    {
        if (!$user || !$user->email || empty($receipts)) {
            return false;
        }

        try {
            $receiptCount = count($receipts);
            $subject = "Recibos de Pago SISPSA - {$receiptCount} recibo(s) generado(s)";

            // Build email body
            $fullName = trim($user->nombres . ' ' . $user->apellidos);
            $date = date('d/m/Y', strtotime($payment->fecha_pago));
            $amountUSDFormatted = number_format((float)$payment->monto_pagado, 2);
            $amountBsFormatted = number_format((float)$payment->monto_usd, 2);

            $htmlBody = "<!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; background: #1a3a6e; color: white; padding: 20px; border-radius: 10px;'>
                <h1>Confirmación de Pago</h1>
                <p>Recibos de Afiliación</p>
            </div>
            
            <p>Estimado(a) <strong>{$fullName}</strong>,</p>
            <p>Su pago ha sido registrado exitosamente. Se han generado <strong>{$receiptCount}</strong> recibo(s).</p>
            
            <div style='background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 8px;'>
                <h3 style='margin-top: 0;'>Detalles del Pago:</h3>
                <p><strong>Fecha de Pago:</strong> {$date}</p>
                <p><strong>Monto Total:</strong> $ {$amountUSDFormatted} USD / Bs. {$amountBsFormatted}</p>
                <p><strong>Método de Pago:</strong> {$payment->metodo_pago}</p>
                <p><strong>Número de Recibos:</strong> {$receiptCount}</p>
            </div>
            
            <div style='background: #e8f0fe; padding: 15px; margin: 20px 0; border-radius: 8px;'>
                <h3 style='margin-top: 0;'>📄 Recibos Generados:</h3>
                <ul style='padding-left: 20px;'>";

            foreach ($receipts as $receipt) {
                $receiptNumber = $receipt->receipt_number;
                $viewUrl = Yii::$app->urlManager->createAbsoluteUrl([
                    'pagos/view-receipt-html',
                    'id' => $receipt->id
                ]);
                $htmlBody .= "<li><strong>{$receiptNumber}</strong> - <a href='{$viewUrl}'>Ver en línea</a></li>";
            }

            $htmlBody .= "
                </ul>
                <p style='margin-bottom: 0;'><strong>⚠️ Todos los recibos están adjuntos en formato PDF.</strong></p>
            </div>
            
            <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 15px; margin: 15px 0; border-radius: 5px;'>
                <strong>📧 ¡IMPORTANTE!</strong> Si no encuentra los recibos adjuntos, revise la carpeta de <strong>SPAM</strong> de su correo.
            </div>
            
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            <p style='font-size: 11px; color: #888; text-align: center;'>SISPSA - Sistema Integral de Salud Programado<br>Este es un mensaje automático, por favor no responder a este correo.</p>
        </body>
        </html>";

            // Plain text version
            $plainTextBody = "Confirmación de Pago SISPSA\n\n";
            $plainTextBody .= "Estimado(a) {$fullName},\n\n";
            $plainTextBody .= "Su pago ha sido registrado exitosamente. Se han generado {$receiptCount} recibo(s).\n\n";
            $plainTextBody .= "Detalles del Pago:\n";
            $plainTextBody .= "- Fecha: {$date}\n";
            $plainTextBody .= "- Monto Total: $ {$amountUSDFormatted} USD / Bs. {$amountBsFormatted}\n";
            $plainTextBody .= "- Método: {$payment->metodo_pago}\n\n";
            $plainTextBody .= "Recibos generados:\n";
            foreach ($receipts as $receipt) {
                $plainTextBody .= "- {$receipt->receipt_number}\n";
            }
            $plainTextBody .= "\nLos recibos están adjuntos en formato PDF.\n\n";
            $plainTextBody .= "SISPSA - Sistema Integral de Salud Programado";

            // Create email
            $mail = Yii::$app->mailer->compose()
                ->setFrom(['sispsa.notificaciones@gmail.com' => 'SISPSA Notificaciones'])
                ->setTo($user->email)
                ->setSubject($subject)
                ->setHtmlBody($htmlBody)
                ->setTextBody($plainTextBody);

            // Attach ALL receipts as PDFs using INDIVIDUAL amounts
            foreach ($receipts as $receipt) {
                // Get contract and plan info
                $contract = $receipt->contract;
                $planName = $contract && $contract->plan ? $contract->plan->nombre : 'N/A';

                // Get logo
                $logoBase64 = '';
                $logoPath = Yii::getAlias('@webroot') . '/img/sispsalogo.jpg';
                if (file_exists($logoPath)) {
                    $logoData = file_get_contents($logoPath);
                    $logoBase64 = 'data:image/jpeg;base64,' . base64_encode($logoData);
                }

                // ===== FIX: Use INDIVIDUAL amounts from the receipt =====
                $receiptNumber = $receipt->receipt_number;

                // Get individual USD amount from the installment
                $installment = Cuotas::findOne($receipt->installment_id);
                $amountUSD = $installment ? ($installment->monto_usd ?: $installment->monto) : $payment->monto_pagado;
                $amountUSDFormattedSingle = number_format((float)$amountUSD, 2);  // ✅ INDIVIDUAL USD

                // Use the receipt's stored amount (individual Bs amount)
                $amountBsFormattedSingle = number_format((float)$receipt->amount, 2);  // ✅ INDIVIDUAL Bs

                $coverageFrom = $receipt->coverage_start_date ? date('d/m/Y', strtotime($receipt->coverage_start_date)) : 'N/A';
                $coverageTo = $receipt->coverage_end_date ? date('d/m/Y', strtotime($receipt->coverage_end_date)) : 'N/A';
                $contractNumber = $contract ? $contract->nrocontrato : ($contract ? $contract->id : 'N/A');
                $installmentNumber = $receipt->installment ? $receipt->installment->numero_cuota : 'N/A';
                $fullNameSingle = trim($user->nombres . ' ' . $user->apellidos);
                $clinicaName = $contract && $contract->clinica ? $contract->clinica->nombre : 'N/A';

                // Generate HTML for PDF using INDIVIDUAL amounts
                $pdfHtml = NotificationHelper::generateSimplePDFHtml(
                    $logoBase64,
                    $fullNameSingle,
                    $receiptNumber,
                    $contractNumber,
                    $installmentNumber,
                    $amountUSDFormattedSingle,   // ✅ INDIVIDUAL USD
                    $amountBsFormattedSingle,    // ✅ INDIVIDUAL Bs
                    $date,
                    $coverageFrom,
                    $coverageTo,
                    $planName,
                    $clinicaName,
                    $user,
                    $contract,
                    $payment
                );

                // Generate PDF
                $pdfContent = NotificationHelper::generatePDFFromHtml($pdfHtml);

                if ($pdfContent) {
                    $mail->attachContent($pdfContent, [
                        'fileName' => "Recibo_{$receiptNumber}.pdf",
                        'contentType' => 'application/pdf',
                    ]);
                }
            }

            return $mail->send();
        } catch (\Exception $e) {
            Yii::error("Error sending combined receipt email: " . $e->getMessage(), 'email');
            return false;
        }
    }

    /**
     * Send INDIVIDUAL emails for each receipt (use this if you want separate emails)
     * @param array $receipts
     * @param UserDatos $user
     * @param Pagos $payment
     * @return bool
     */
    private function sendIndividualReceiptEmails($receipts, $user, $payment)
    {
        if (!$user || !$user->email || empty($receipts)) {
            return false;
        }

        $allSent = true;

        try {
            // Send a summary email first
            $summarySent = $this->sendPaymentConfirmationEmail($receipts, $user, $payment);

            // Then send each receipt individually
            foreach ($receipts as $receipt) {
                $result = NotificationHelper::sendReceiptNotification($receipt, $user, $payment);
                if (!$result) {
                    $allSent = false;
                    Yii::warning("Failed to send receipt {$receipt->id} to {$user->email}", 'email');
                }
            }

            return $allSent && $summarySent;
        } catch (\Exception $e) {
            Yii::error("Error sending individual receipt emails: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment confirmation email (summary)
     * @param array $receipts
     * @param UserDatos $user
     * @param Pagos $payment
     * @return bool
     */
    private function sendPaymentConfirmationEmail($receipts, $user, $payment)
    {
        if (!$user || !$user->email || empty($receipts)) {
            return false;
        }

        try {
            $subject = "Confirmación de Pago SISPSA";

            $htmlBody = "<h2>Confirmación de Pago</h2>";
            $htmlBody .= "<p>Estimado(a) <strong>" . Html::encode($user->nombres . ' ' . $user->apellidos) . "</strong>,</p>";
            $htmlBody .= "<p>Su pago ha sido registrado exitosamente. Se han generado <strong>" . count($receipts) . "</strong> recibo(s).</p>";

            $htmlBody .= "<h3>Detalles del Pago:</h3>";
            $htmlBody .= "<ul>";
            $htmlBody .= "<li><strong>Fecha de Pago:</strong> " . date('d/m/Y', strtotime($payment->fecha_pago)) . "</li>";
            $htmlBody .= "<li><strong>Monto:</strong> $" . number_format($payment->monto_pagado, 2) . "</li>";
            $htmlBody .= "<li><strong>Método de Pago:</strong> {$payment->metodo_pago}</li>";
            $htmlBody .= "</ul>";

            $htmlBody .= "<h3>Recibos Generados:</h3>";
            $htmlBody .= "<ul>";
            foreach ($receipts as $receipt) {
                $htmlBody .= "<li>Recibo N° {$receipt->receipt_number}</li>";
            }
            $htmlBody .= "</ul>";

            $htmlBody .= "<p>Gracias por confiar en SISPSA.</p>";

            return Yii::$app->mailer->compose()
                ->setTo($user->email)
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setSubject($subject)
                ->setHtmlBody($htmlBody)
                ->send();
        } catch (\Exception $e) {
            Yii::error("Error sending payment confirmation email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an existing Pagos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        // CORRECCIÓN: Usar método privado
        $tasa_completa = $this->getTasaCambio();

        $model = $this->findModel($id);
        $model->scenario = 'update'; // SET THE UPDATE SCENARIO
        $model->tasa = number_format($tasa_completa, 2, '.', '');

        // Calculate monto_usd based on the new formula for existing records
        if (!empty($model->monto_pagado) && !empty($model->tasa)) {
            $model->monto_usd = round($model->monto_pagado * $model->tasa, 4);
        }

        $oldImagePath = $model->imagen_prueba;
        $tempFilePath = null;

        if ($this->request->isPost && $model->load($this->request->post())) {
            // For ALL payment methods, calculate monto_usd using the same formula
            // monto_usd = monto_pagado × tasa
            if (!empty($model->monto_pagado) && !empty($model->tasa) && $model->tasa > 0) {
                $model->monto_usd = round($model->monto_pagado * $model->tasa, 4);
                Yii::info("Update: Calculated monto_usd={$model->monto_usd} for {$model->metodo_pago} with tasa={$model->tasa}");
            }

            $folder = 'Pago';
            $uploadedFileInstance = UploadedFile::getInstance($model, 'imagen_prueba_file');

            if ($uploadedFileInstance) {
                $fileName = uniqid('pago_') . '.' . $uploadedFileInstance->extension;
                $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                if ($uploadedFileInstance->saveAs($tempFilePath)) {
                    $fileKeyInBucket = $fileName;
                    $publicUrl = UserHelper::uploadFileToSupabaseApi(
                        $tempFilePath,
                        $uploadedFileInstance->type,
                        $fileKeyInBucket,
                        $folder
                    );

                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }

                    if ($publicUrl) {
                        $model->imagen_prueba = $publicUrl;
                        if ($oldImagePath) {
                            UserHelper::deleteFileFromSupabaseApi($oldImagePath);
                        }
                    } else {
                        $model->imagen_prueba = $oldImagePath;
                        Yii::$app->session->setFlash('error', 'Fallo la subida de la nueva imagen a Supabase Storage.');
                    }
                } else {
                    $model->imagen_prueba = $oldImagePath;
                    Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal para la actualización.');
                }
            } else {
                $model->imagen_prueba = $oldImagePath;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Pago actualizado con éxito.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar el pago en la base de datos.');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Pagos model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $folder = 'Pago';

        if ($model->imagen_prueba) {
            UserHelper::deleteFileFromSupabaseApi($model->imagen_prueba, $folder);
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Pago y archivo asociados eliminados con éxito.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al eliminar el pago.');
        }

        return $this->redirect(['/contratos/index', 'user_id' => $model->user_id]);
    }

    /**
     * Finds the Pagos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Pagos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pagos::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdatestatus()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = \Yii::$app->request->post('id');
        $status = \Yii::$app->request->post('status');

        Yii::info("========== PAYMENT RECONCILIATION ==========", 'pagos');
        Yii::info("Payment ID: {$id}, New Status: " . ($status == '1' ? 'Conciliado' : 'Por Conciliar'), 'pagos');

        $model = Pagos::findOne($id);
        if (!$model) {
            Yii::error("Payment {$id} not found!", 'pagos');
            return ['success' => false, 'error' => 'Registro no encontrado'];
        }

        // Only proceed if we're changing to Conciliado
        $isBeingConciliated = ($status == '1' && $model->estatus != 'Conciliado');

        $oldStatus = $model->estatus;
        $model->estatus = ($status == '1') ? 'Conciliado' : 'Por Conciliar';
        $model->updated_at = date('Y-m-d');
        $model->fecha_conciliacion = ($status == '1') ? date('Y-m-d') : null;
        $model->conciliador_id = ($status == '1') ? Yii::$app->user->id : null;

        if ($model->save(false)) {
            Yii::info("Payment {$id} saved with status: {$model->estatus}", 'pagos');

            // ONLY update contracts if this payment was JUST reconciled
            if ($isBeingConciliated) {
                Yii::info("Payment was JUST reconciled - updating affected contracts", 'pagos');

                // Get all contracts affected by this payment
                $contratoIds = Cuotas::find()
                    ->select('contrato_id')
                    ->distinct()
                    ->where(['id_pago' => $model->id])
                    ->column();

                Yii::info("Payment {$id} affects contracts: " . implode(', ', $contratoIds), 'pagos');

                foreach ($contratoIds as $contratoId) {
                    Yii::info("========== PROCESSING CONTRACT #{$contratoId} ==========", 'pagos');

                    // Verificar cuotas del contrato antes de updateStatus
                    $cuotasAntes = Cuotas::find()
                        ->where(['contrato_id' => $contratoId])
                        ->select(['numero_cuota', 'estatus', 'fecha_vencimiento'])
                        ->orderBy('numero_cuota')
                        ->asArray()
                        ->all();
                    Yii::info("Cuotas ANTES de updateStatus: " . print_r($cuotasAntes, true), 'pagos');

                    $contrato = Contratos::findOne($contratoId);
                    if ($contrato) {
                        $oldContractStatus = $contrato->estatus;
                        Yii::info("Contract #{$contratoId} BEFORE updateStatus: {$oldContractStatus}", 'pagos');

                        $result = $contrato->updateStatus();

                        // Recargar el contrato para obtener el estado actualizado
                        $contrato->refresh();
                        Yii::info("Contract #{$contratoId} AFTER updateStatus: {$contrato->estatus}", 'pagos');
                        Yii::info("Contract #{$contratoId} update result: " . ($result ? 'true' : 'false'), 'pagos');

                        // Verificar cuotas después de updateStatus
                        $cuotasDespues = Cuotas::find()
                            ->where(['contrato_id' => $contratoId])
                            ->select(['numero_cuota', 'estatus', 'fecha_vencimiento'])
                            ->orderBy('numero_cuota')
                            ->asArray()
                            ->all();
                        Yii::info("Cuotas DESPUÉS de updateStatus: " . print_r($cuotasDespues, true), 'pagos');
                    } else {
                        Yii::error("Contract #{$contratoId} not found!", 'pagos');
                    }
                }
            } else {
                Yii::info("Payment status changed but not to Conciliado - no contract updates needed", 'pagos');
            }

            // Update user solvent status
            $user = UserDatos::findOne(['id' => $model->user_id]);
            if ($user) {
                $hasPendingPayments = Pagos::find()
                    ->where(['user_id' => $model->user_id, 'estatus' => 'Por Conciliar'])
                    ->exists();

                $oldSolventStatus = $user->estatus_solvente;
                $user->estatus_solvente = $hasPendingPayments ? 'No' : 'Si';
                $user->save(false);

                Yii::info("User #{$model->user_id} solvent status changed from {$oldSolventStatus} to {$user->estatus_solvente}", 'pagos');
            }

            return ['success' => true, 'new_status' => $model->estatus];
        }

        Yii::error("Failed to save payment {$id}", 'pagos');
        return ['success' => false, 'error' => 'Error al guardar'];
    }

    public function actionUpdatesolvente()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_id = \Yii::$app->request->post('user_id');
        $status = \Yii::$app->request->post('status');

        \Yii::info('Datos recibidos para solvente - user_id: ' . $user_id . ', status: ' . $status);

        if (empty($user_id) || $status === null) {
            return ['success' => false, 'error' => 'Parámetros requeridos: user_id=' . $user_id . ', status=' . $status];
        }

        $userDatos = UserDatos::findOne(['user_login_id' => $user_id]);
        if (!$userDatos) {
            return ['success' => false, 'error' => 'Usuario no encontrado'];
        }

        $userDatos->estatus_solvente = ($status == '1') ? 'SI' : 'No';
        $userDatos->updated_at = date('Y-m-d');

        if ($userDatos->save(false)) {
            return ['success' => true, 'new_status' => $userDatos->estatus_solvente];
        }

        return ['success' => false, 'error' => 'Error al guardar'];
    }

    public function actionEjecutar($user_id = null)
    {
        $user_id = $user_id ?: Yii::$app->request->get('user_id');
        $yiiPath = Yii::getAlias('@app/yii');

        if (!is_executable($yiiPath)) {
            @chmod($yiiPath, 0755);
        }

        $command = "php " . escapeshellarg($yiiPath) . " cuota/generar 2>&1";

        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        $outputStr = '';
        $exitCode = -1;

        if (is_resource($process)) {
            $outputStr = stream_get_contents($pipes[1]);
            $outputStr .= stream_get_contents($pipes[2]);

            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => $exitCode === 0,
                'exitCode' => $exitCode,
                'output' => $outputStr,
                'command' => $command
            ];
        }

        if ($exitCode === 0) {
            Yii::$app->session->setFlash('success', "Cuotas generadas correctamente.");
        } else {
            Yii::$app->session->setFlash('error', "Error generando cuotas (exitCode={$exitCode}).\nSalida: " . substr($outputStr, 0, 1000));
        }

        if (!empty($user_id)) {
            return $this->redirect(['create', 'user_id' => $user_id]);
        }

        $referrer = Yii::$app->request->referrer;
        if ($referrer) {
            return $this->redirect($referrer);
        }

        return $this->redirect(['index']);
    }

    /**
     * Debug method to check user contracts and cuotas in detail
     */
    public function actionDebugUser101()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_id = 101;

        // Find user contracts
        $contratos = Contratos::find()
            ->where(['user_id' => $user_id])
            ->all();

        $contratoIds = [];
        foreach ($contratos as $contrato) {
            $contratoIds[] = $contrato->id;
        }

        // Find pending cuotas for these contracts
        $cuotas = Cuotas::find()
            ->where(['IN', 'contrato_id', $contratoIds])
            ->andWhere(['estatus' => 'pendiente'])
            ->all();

        // Also check all cuotas for these contracts regardless of status
        $allCuotas = Cuotas::find()
            ->where(['IN', 'contrato_id', $contratoIds])
            ->all();

        return [
            'user_id' => $user_id,
            'contratos_found' => count($contratos),
            'contrato_ids' => $contratoIds,
            'contratos_details' => array_map(function ($contrato) {
                return [
                    'id' => $contrato->id,
                    'nrocontrato' => $contrato->nrocontrato,
                    'estatus' => $contrato->estatus,
                    'user_id' => $contrato->user_id,
                    'fecha_ini' => $contrato->fecha_ini,
                    'fecha_ven' => $contrato->fecha_ven,
                ];
            }, $contratos),
            'pending_cuotas_found' => count($cuotas),
            'all_cuotas_found' => count($allCuotas),
            'pending_cuotas' => array_map(function ($cuota) {
                return [
                    'id' => $cuota->id,
                    'contrato_id' => $cuota->contrato_id,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'monto' => $cuota->monto,
                    'monto_usd' => $cuota->monto_usd,
                    'estatus' => $cuota->estatus,
                    'rate_usd_bs' => $cuota->rate_usd_bs,
                ];
            }, $cuotas),
            'all_cuotas' => array_map(function ($cuota) {
                return [
                    'id' => $cuota->id,
                    'contrato_id' => $cuota->contrato_id,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'monto' => $cuota->monto,
                    'monto_usd' => $cuota->monto_usd,
                    'estatus' => $cuota->estatus,
                ];
            }, $allCuotas),
        ];
    }

    // Add this to PagosController for testing
    public function actionTestUser101()
    {
        $user_id = 101;

        // Test the cuotas query directly
        $cuotas = Cuotas::getPendingCuotasForUser($user_id);

        echo "<h1>Testing User 101 Cuotas</h1>";
        echo "<p>User ID: " . $user_id . "</p>";
        echo "<p>Cuotas Found: " . count($cuotas) . "</p>";

        foreach ($cuotas as $cuota) {
            echo "<p>Cuota ID: " . $cuota->id . ", Contract: " . $cuota->contrato_id . ", Monto USD: " . $cuota->monto_usd . ", Monto: " . $cuota->monto . "</p>";
        }

        // Test the view rendering
        $model = new Pagos();
        $model->user_id = $user_id;
        $modelCuotas = new Cuotas();
        $total = 58.00; // Expected total

        return $this->render('create', [
            'model' => $model,
            'user_id' => $user_id,
            'cuotas' => $cuotas,
            'modelCuotas' => $modelCuotas,
            'total' => $total,
        ]);
    }

    /**
     * Debug method to check user contracts and cuotas
     */
    public function actionDebugUserCuotas($user_id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Find user contracts
        $contratos = Contratos::find()
            ->where(['user_id' => $user_id])
            ->all();

        $contratoIds = [];
        foreach ($contratos as $contrato) {
            $contratoIds[] = $contrato->id;
        }

        // Find pending cuotas
        $cuotas = Cuotas::find()
            ->where(['IN', 'contrato_id', $contratoIds])
            ->andWhere(['estatus' => 'pendiente'])
            ->all();

        return [
            'user_id' => $user_id,
            'contratos_found' => count($contratos),
            'contrato_ids' => $contratoIds,
            'cuotas_found' => count($cuotas),
            'cuotas' => array_map(function ($cuota) {
                return [
                    'id' => $cuota->id,
                    'contrato_id' => $cuota->contrato_id,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'monto' => $cuota->monto,
                    'monto_usd' => $cuota->monto_usd,
                    'estatus' => $cuota->estatus,
                ];
            }, $cuotas)
        ];
    }

    /**
     * Valida que el pago coincida con las cuotas seleccionadas
     */
    private function validatePaymentCuotas($model, $selectedCuotaIds)
    {
        if (empty($selectedCuotaIds)) {
            Yii::$app->session->setFlash('error', 'Debe seleccionar al menos una cuota para pagar.');
            return false;
        }

        $selectedCuotas = Cuotas::find()->where(['id' => $selectedCuotaIds])->all();
        $selectedCuotasTotal = 0;

        foreach ($selectedCuotas as $cuota) {
            // Skip cuotas that are already paid
            if ($cuota->estatus === 'pagada') {
                Yii::$app->session->setFlash('error', "La cuota #{$cuota->id} ya está pagada.");
                return false;
            }
            $selectedCuotasTotal += $cuota->monto_usd ?: $cuota->monto;
        }

        // Allow small rounding differences
        if (abs($model->monto_pagado - $selectedCuotasTotal) > 0.01) {
            Yii::$app->session->setFlash(
                'error',
                "El monto pagado ({$model->monto_pagado} USD) no coincide con el total de cuotas seleccionadas ({$selectedCuotasTotal} USD)."
            );
            return false;
        }

        return true;
    }

    /**
     * Generate receipts for an existing payment
     * @param int $id Payment ID
     * @return \yii\web\Response
     */
    public function actionGenerateReceipts($id)
    {
        $payment = $this->findModel($id);

        // When showing existing receipts or generating new ones
        $existingReceipts = Receipt::find()
            ->select('receipts.*')
            ->where(['payment_id' => $payment->id])
            ->leftJoin('cuotas', 'cuotas.id = receipts.installment_id')
            ->orderBy(['cuotas.numero_cuota' => SORT_ASC])
            ->all();

        if (!empty($existingReceipts)) {
            Yii::$app->session->setFlash('warning', 'Este pago ya tiene ' . count($existingReceipts) . ' recibos generados.');
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index', 'user_id' => $payment->user_id]);
        }

        // Check if payment has installments
        $installments = Cuotas::find()
            ->where(['id_pago' => $payment->id])
            ->all();

        if (empty($installments)) {
            Yii::$app->session->setFlash('error', 'Este pago no tiene cuotas asociadas. No se pueden generar recibos.');
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index', 'user_id' => $payment->user_id]);
        }

        // Generate receipts
        $receipts = ReceiptGenerator::generateForPayment($payment);

        if (!empty($receipts)) {
            $count = count($receipts);
            $user = UserDatos::findOne($payment->user_id);

            if ($user && $user->email) {
                try {
                    foreach ($receipts as $receipt) {
                        NotificationHelper::sendReceiptNotification($receipt, $user, $payment);
                    }
                    Yii::$app->session->setFlash('success', "✅ Se generaron {$count} recibo(s) correctamente. Se ha enviado un correo electrónico a <strong>{$user->email}</strong>.");
                } catch (\Exception $e) {
                    Yii::$app->session->setFlash('success', "✅ Se generaron {$count} recibo(s) correctamente. El envío del correo falló: {$e->getMessage()}");
                }
            } else {
                Yii::$app->session->setFlash('success', "✅ Se generaron {$count} recibo(s) correctamente. El usuario no tiene email registrado.");
            }
        } else {
            Yii::$app->session->setFlash('error', 'No se pudieron generar los recibos. Verifique que el pago tenga cuotas asociadas.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index', 'user_id' => $payment->user_id]);
    }

    public function actionViewReceipts($payment_id)
    {
        $payment = $this->findModel($payment_id);

        // Order by the cuota number (numero_cuota) to show receipts in correct order
        $receipts = Receipt::find()
            ->select('receipts.*')
            ->where(['payment_id' => $payment_id])
            ->leftJoin('cuotas', 'cuotas.id = receipts.installment_id')
            ->orderBy(['cuotas.numero_cuota' => SORT_ASC])  // ✅ Order by cuota number
            ->all();

        $user = UserDatos::findOne($payment->user_id);

        return $this->render('view-receipts', [
            'payment' => $payment,
            'receipts' => $receipts,
            'user' => $user,
        ]);
    }

    /**
     * View a single receipt as HTML in the browser
     * @param int $id Receipt ID
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionViewReceiptHtml($id)
    {
        $receipt = Receipt::findOne($id);
        if (!$receipt) {
            throw new NotFoundHttpException('Recibo no encontrado.');
        }

        // Return the HTML content directly
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'text/html');

        return $receipt->html_content;
    }

    /**
     * Download a single receipt as HTML
     * @param int $id Receipt ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDownloadReceipt($id)
    {
        $receipt = Receipt::findOne($id);
        if (!$receipt) {
            throw new NotFoundHttpException('Recibo no encontrado.');
        }

        // Set the response headers for HTML download
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'text/html');
        Yii::$app->response->headers->set('Content-Disposition', 'attachment; filename="recibo_' . $receipt->receipt_number . '.html"');

        return $receipt->html_content;
    }

    /**
     * Download a single receipt as PDF
     * @param int $id Receipt ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDownloadReceiptPdf($id)
    {
        $receipt = Receipt::findOne($id);
        if (!$receipt) {
            throw new NotFoundHttpException('Recibo no encontrado.');
        }

        // Get the HTML content from the receipt
        $originalHtml = $receipt->html_content;

        // Extract only the body content from the HTML (skip the DOCTYPE and header)
        preg_match('/<body[^>]*>(.*?)<\/body>/is', $originalHtml, $matches);
        $bodyContent = isset($matches[1]) ? $matches[1] : $originalHtml;

        // Create a clean, optimized HTML for PDF
        $htmlContent = $this->prepareReceiptPdfHtml($bodyContent, $receipt);

        // Create PDF using Dompdf
        $pdf = new \Dompdf\Dompdf();
        $pdf->setPaper('A4', 'portrait');

        // Set options
        if (method_exists($pdf, 'setOptions')) {
            $options = new \Dompdf\Options();
            $options->setDefaultFont('dejavu sans');
            $options->setIsHtml5ParserEnabled(true);
            $options->setIsRemoteEnabled(true);
            $options->setIsFontSubsettingEnabled(true);
            $options->setIsPhpEnabled(false);
            // Increase memory limit for large tables
            $options->setDpi(96);
            $pdf->setOptions($options);
        }

        try {
            $pdf->loadHtml($htmlContent);
            $pdf->render();

            // Output the PDF as a download
            $filename = 'recibo_' . $receipt->receipt_number . '.pdf';
            return Yii::$app->response->sendContentAsFile(
                $pdf->output(),
                $filename,
                [
                    'mimeType' => 'application/pdf',
                    'inline' => false,
                ]
            );
        } catch (\Exception $e) {
            Yii::error("Error generating PDF: " . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error al generar el PDF: ' . $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index']);
        }
    }

    /**
     * Prepare clean HTML for PDF generation
     * @param string $bodyContent
     * @param Receipt $receipt
     * @return string
     */
    private function prepareReceiptPdfHtml($bodyContent, $receipt)
    {
        return '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Recibo ' . $receipt->receipt_number . '</title>
        <style>
            /* ===== RESET ===== */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            /* ===== PAGE SETUP ===== */
            @page {
                margin: 0.6cm;
                size: A4 portrait;
            }
            
            /* ===== BODY ===== */
            body {
                font-family: "DejaVu Sans", Arial, sans-serif;
                font-size: 9px;
                line-height: 1.3;
                color: #000;
                background: #fff;
            }
            
            /* ===== TABLES - CRITICAL FIX ===== */
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-bottom: 6px !important;
                font-size: 8.5px !important;
                page-break-inside: auto !important;
                page-break-after: auto !important;
            }
            
            table table {
                margin-bottom: 0 !important;
            }
            
            td, th {
                border: 1px solid #000 !important;
                padding: 3px 5px !important;
                vertical-align: top !important;
                page-break-inside: auto !important;
                word-wrap: break-word !important;
            }
            
            /* ===== ROW BREAK CONTROL ===== */
            tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }
            
            /* ===== SUBTITLES ===== */
            .subtitle {
                background: #e8e8e8 !important;
                padding: 4px 10px !important;
                margin: 8px 0 4px 0 !important;
                font-size: 10px !important;
                font-weight: bold !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }
            
            /* ===== TEXT STYLES ===== */
            .text-center {
                text-align: center !important;
            }
            .text-right {
                text-align: right !important;
            }
            .text-left {
                text-align: left !important;
            }
            .text-justify {
                text-align: justify !important;
            }
            
            /* ===== LOGO ===== */
            .logo-container {
                text-align: center !important;
                padding: 6px 0 !important;
                border-bottom: 1px solid #e8e8e8 !important;
            }
            .logo-container img {
                max-width: 120px !important;
                height: auto !important;
            }
            
            /* ===== REGISTRATION INFO ===== */
            .registration-info {
                text-align: center !important;
                padding: 4px 0 !important;
                background: #f8f9fa !important;
                border-bottom: 1px solid #e0e0e0 !important;
            }
            .reg-line {
                font-size: 7.5px !important;
                color: #495057 !important;
            }
            .rif-line {
                font-size: 8.5px !important;
                color: #0066cc !important;
                font-weight: 600 !important;
            }
            
            /* ===== TITLE ===== */
            .title-container {
                text-align: center !important;
                padding: 5px 0 !important;
                background: #1a3a6e !important;
                color: white !important;
            }
            .title-container h1 {
                font-size: 13px !important;
                margin: 0 !important;
                color: white !important;
            }
            .title-container h2 {
                font-size: 10px !important;
                margin: 2px 0 0 0 !important;
                opacity: 0.9 !important;
                color: white !important;
            }
            
            /* ===== LEGAL ===== */
            .legal {
                font-size: 7px !important;
                text-align: justify !important;
                margin: 6px 0 !important;
                line-height: 1.2 !important;
            }
            .footer {
                font-size: 6px !important;
                text-align: center !important;
                margin-top: 6px !important;
                padding-top: 4px !important;
                border-top: 1px solid #ccc !important;
                color: #666 !important;
            }
            .signature-line {
                margin-top: 12px !important;
                border-top: 1px solid #000 !important;
                padding-top: 3px !important;
            }
            
            /* ===== IMAGES ===== */
            img {
                max-width: 100% !important;
                height: auto !important;
            }
            
            /* ===== WRAPPER ===== */
            .receipt-wrapper {
                page-break-inside: auto !important;
                page-break-after: auto !important;
                page-break-before: auto !important;
            }
            
            /* ===== SERVICES TABLE ===== */
            .services-table td {
                font-size: 7.5px !important;
                padding: 2px 4px !important;
            }
            .services-table th {
                font-size: 7.5px !important;
                padding: 2px 4px !important;
                background: #f0f0f0 !important;
            }
            
            /* ===== RECEIPT INFO ===== */
            .receipt-info-table td {
                font-size: 8px !important;
                padding: 2px 4px !important;
            }
            
            /* ===== EXCLUSIONS ===== */
            .exclusions-text {
                font-size: 7px !important;
                line-height: 1.1 !important;
                padding: 3px 5px !important;
            }
            
            /* ===== KEEP SECTIONS TOGETHER ===== */
            .keep-together {
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
            
            /* ===== FIX FOR BROKEN TABLES ===== */
            td[colspan] {
                text-align: center !important;
            }
            
            /* ===== PRINT-OPTIMIZED ===== */
            .no-print {
                display: none !important;
            }
            
            /* ===== RESPONSIVE ===== */
            @media print {
                body {
                    margin: 0.5cm;
                }
                .no-print {
                    display: none !important;
                }
            }
        </style>
    </head>
    <body>
        <div class="receipt-wrapper">
            ' . $bodyContent . '
        </div>
    </body>
    </html>';
    }

    /**
     * Download all receipts for a payment as a single PDF
     * @param int $payment_id Payment ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDownloadAllReceiptsPdf($payment_id)
    {
        $payment = $this->findModel($payment_id);
        $receipts = Receipt::find()
            ->where(['payment_id' => $payment_id])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        if (empty($receipts)) {
            throw new NotFoundHttpException('No se encontraron recibos para este pago.');
        }

        // Combine all receipts HTML
        $combinedBody = '';
        foreach ($receipts as $index => $receipt) {
            if ($index > 0) {
                $combinedBody .= '<div style="page-break-before: always; margin-top: 20px; border-top: 2px dashed #ccc; padding-top: 20px;"></div>';
            }
            // Extract body content
            preg_match('/<body[^>]*>(.*?)<\/body>/is', $receipt->html_content, $matches);
            $bodyContent = isset($matches[1]) ? $matches[1] : $receipt->html_content;
            $combinedBody .= $bodyContent;
        }

        $htmlContent = $this->prepareReceiptPdfHtml($combinedBody, $receipts[0]);

        // Create PDF using Dompdf
        $pdf = new \Dompdf\Dompdf();
        $pdf->setPaper('A4', 'portrait');

        if (method_exists($pdf, 'setOptions')) {
            $options = new \Dompdf\Options();
            $options->setDefaultFont('dejavu sans');
            $options->setIsHtml5ParserEnabled(true);
            $options->setIsRemoteEnabled(true);
            $options->setIsFontSubsettingEnabled(true);
            $options->setDpi(96);
            $pdf->setOptions($options);
        }

        try {
            $pdf->loadHtml($htmlContent);
            $pdf->render();

            $filename = 'recibos_pago_' . $payment->id . '.pdf';
            return Yii::$app->response->sendContentAsFile(
                $pdf->output(),
                $filename,
                [
                    'mimeType' => 'application/pdf',
                    'inline' => false,
                ]
            );
        } catch (\Exception $e) {
            Yii::error("Error generating PDF: " . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error al generar el PDF: ' . $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index']);
        }
    }

    /**
     * Resend a receipt via email
     * @param int $id Receipt ID
     * @return \yii\web\Response
     */
    public function actionResendReceiptEmail($id)
    {
        $receipt = Receipt::findOne($id);
        if (!$receipt) {
            Yii::$app->session->setFlash('error', 'Recibo no encontrado.');
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index']);
        }

        $payment = Pagos::findOne($receipt->payment_id);
        if (!$payment) {
            Yii::$app->session->setFlash('error', 'Pago no encontrado.');
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index']);
        }

        $user = UserDatos::findOne($payment->user_id);
        if (!$user) {
            Yii::$app->session->setFlash('error', 'Usuario no encontrado.');
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index']);
        }

        if (!$user->email) {
            Yii::$app->session->setFlash('error', 'El usuario no tiene email registrado.');
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index']);
        }

        try {
            // Send the receipt via email
            $result = NotificationHelper::sendReceiptNotification($receipt, $user, $payment);

            if ($result) {
                Yii::$app->session->setFlash('success', '✅ Recibo reenviado exitosamente a <strong>' . Html::encode($user->email) . '</strong>.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al enviar el correo. Por favor, verifique la configuración del servidor de correo.');
            }
        } catch (\Exception $e) {
            Yii::error("Error resending receipt email: " . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error al enviar el correo: ' . $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index', 'user_id' => $payment->user_id]);
    }

    /**
     * Resend all receipts for a payment via email
     * @param int $payment_id Payment ID
     * @return \yii\web\Response
     */
    public function actionResendAllReceiptsEmail($payment_id)
    {
        $payment = $this->findModel($payment_id);
        // When resending all receipts
        $receipts = Receipt::find()
            ->select('receipts.*')
            ->where(['payment_id' => $payment_id])
            ->leftJoin('cuotas', 'cuotas.id = receipts.installment_id')
            ->orderBy(['cuotas.numero_cuota' => SORT_ASC])
            ->all();

        if (empty($receipts)) {
            Yii::$app->session->setFlash('error', 'No se encontraron recibos para este pago.');
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index']);
        }

        $user = UserDatos::findOne($payment->user_id);
        if (!$user) {
            Yii::$app->session->setFlash('error', 'Usuario no encontrado.');
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index']);
        }

        if (!$user->email) {
            Yii::$app->session->setFlash('error', 'El usuario no tiene email registrado.');
            return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index']);
        }

        try {
            $sentCount = 0;
            foreach ($receipts as $receipt) {
                $result = NotificationHelper::sendReceiptNotification($receipt, $user, $payment);
                if ($result) {
                    $sentCount++;
                }
            }

            if ($sentCount > 0) {
                Yii::$app->session->setFlash('success', '✅ ' . $sentCount . ' recibo(s) reenviados exitosamente a <strong>' . Html::encode($user->email) . '</strong>.');
            } else {
                Yii::$app->session->setFlash('error', 'Error al enviar los correos. Por favor, verifique la configuración del servidor de correo.');
            }
        } catch (\Exception $e) {
            Yii::error("Error resending all receipts email: " . $e->getMessage());
            Yii::$app->session->setFlash('error', 'Error al enviar los correos: ' . $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['contratos/index', 'user_id' => $payment->user_id]);
    }
}
