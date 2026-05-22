<?php
// Alternative: app/controllers/ReceiptsController.php (Simplified - No AccessControl)

namespace app\controllers;

use Yii;
use app\models\Receipt;
use app\models\Pagos;
use app\components\ReceiptGenerator;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ReceiptsController handles receipt viewing and printing
 */
class ReceiptsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Disable CSRF validation for print actions
     */
    public function beforeAction($action)
    {
        // Allow all actions without CSRF validation for printing
        $this->enableCsrfValidation = false;

        // Allow all users to access receipt actions
        if (in_array($action->id, ['print', 'print-all', 'view'])) {
            // No access restrictions
            return parent::beforeAction($action);
        }

        return parent::beforeAction($action);
    }

    /**
     * View a single receipt
     * @param int $id
     * @return string
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Print a single receipt (HTML optimized for printing)
     * @param int $id
     * @return string
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);

        // Mark as printed if not already
        if ($model->status === Receipt::STATUS_GENERATED) {
            $model->status = Receipt::STATUS_PRINTED;
            $model->save(false);
        }

        // Return the HTML content for printing
        return $this->renderPartial('print', [
            'model' => $model,
            'html' => $model->html_content,
        ]);
    }

    /**
     * Print all receipts for a payment (batch print)
     * @param int $paymentId
     * @return string
     */
    public function actionPrintAll($paymentId)
    {
        $payment = Pagos::findOne($paymentId);
        if (!$payment) {
            throw new NotFoundHttpException('Pago no encontrado.');
        }

        $receipts = ReceiptGenerator::getReceiptsForPayment($paymentId);

        if (empty($receipts)) {
            Yii::$app->session->setFlash('warning', 'No hay recibos generados para este pago.');
            return $this->redirect(Yii::$app->request->referrer ?: ['/contratos/index']);
        }

        // Mark all as printed
        foreach ($receipts as $receipt) {
            if ($receipt->status === Receipt::STATUS_GENERATED) {
                $receipt->status = Receipt::STATUS_PRINTED;
                $receipt->save(false);
            }
        }

        // Combine all receipts HTML for batch printing
        $combinedHtml = $this->renderPartial('print-all', [
            'receipts' => $receipts,
            'payment' => $payment,
        ]);

        return $combinedHtml;
    }

    /**
     * Regenerate receipt HTML for a payment (if needed)
     * @param int $paymentId
     * @return \yii\web\Response
     */
    public function actionRegenerate($paymentId)
    {
        $payment = Pagos::findOne($paymentId);
        if (!$payment) {
            throw new NotFoundHttpException('Pago no encontrado.');
        }

        // Delete existing receipts
        Receipt::deleteAll(['payment_id' => $paymentId]);

        // Regenerate
        $receipts = ReceiptGenerator::generateForPayment($payment);

        if (!empty($receipts)) {
            Yii::$app->session->setFlash('success', 'Se regeneraron ' . count($receipts) . ' recibos correctamente.');
        } else {
            Yii::$app->session->setFlash('error', 'No se pudieron regenerar los recibos.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['/contratos/index']);
    }

    /**
     * Finds the Receipt model based on its primary key value.
     * @param int $id
     * @return Receipt
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Receipt::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El recibo solicitado no existe.');
    }
}
