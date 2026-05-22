<?php
// Create this as a console command: commands/GenerateExistingReceiptsController.php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\models\Pagos;
use app\components\ReceiptGenerator;

class GenerateExistingReceiptsController extends Controller
{
    /**
     * Generate receipts for all existing payments that don't have receipts
     * Usage: php yii generate-existing-receipts/index
     */
    public function actionIndex()
    {
        echo "Starting to generate receipts for existing payments...\n";

        // Find all payments that don't have receipts
        $sql = "SELECT p.* FROM pagos p 
                LEFT JOIN receipts r ON r.payment_id = p.id 
                WHERE r.id IS NULL";

        $payments = Pagos::findBySql($sql)->all();

        echo "Found " . count($payments) . " payments without receipts.\n";

        $generated = 0;
        $failed = 0;

        foreach ($payments as $payment) {
            echo "Processing payment #{$payment->id}... ";

            // Check if this payment has any installments linked
            $installments = \app\models\Cuotas::find()
                ->where(['id_pago' => $payment->id])
                ->all();

            if (empty($installments)) {
                echo "SKIPPED - No installments linked.\n";
                continue;
            }

            // Generate receipts
            $receipts = ReceiptGenerator::generateForPayment($payment);

            if (!empty($receipts)) {
                echo "SUCCESS - Generated " . count($receipts) . " receipts.\n";
                $generated++;
            } else {
                echo "FAILED - Could not generate receipts.\n";
                $failed++;
            }
        }

        echo "\n=== SUMMARY ===\n";
        echo "Total payments processed: " . count($payments) . "\n";
        echo "Receipts generated: {$generated}\n";
        echo "Failed: {$failed}\n";

        return 0;
    }

    /**
     * Generate receipts for a specific payment
     * Usage: php yii generate-existing-receipts/for-payment <payment_id>
     */
    public function actionForPayment($paymentId)
    {
        $payment = Pagos::findOne($paymentId);

        if (!$payment) {
            echo "Payment #{$paymentId} not found.\n";
            return 1;
        }

        // Check if receipts already exist
        $existingReceipts = \app\models\Receipt::find()
            ->where(['payment_id' => $paymentId])
            ->all();

        if (!empty($existingReceipts)) {
            echo "Payment #{$paymentId} already has " . count($existingReceipts) . " receipts.\n";
            echo "Do you want to regenerate? (y/n): ";
            $confirm = trim(fgets(STDIN));

            if (strtolower($confirm) !== 'y') {
                echo "Skipped.\n";
                return 0;
            }

            // Delete existing receipts
            foreach ($existingReceipts as $receipt) {
                $receipt->delete();
            }
            echo "Deleted existing receipts.\n";
        }

        echo "Generating receipts for payment #{$paymentId}...\n";
        $receipts = ReceiptGenerator::generateForPayment($payment);

        if (!empty($receipts)) {
            echo "SUCCESS - Generated " . count($receipts) . " receipts.\n";
            foreach ($receipts as $receipt) {
                echo "  - Receipt #{$receipt->receipt_number}\n";
            }
        } else {
            echo "FAILED - Could not generate receipts.\n";
            return 1;
        }

        return 0;
    }

    /**
     * Generate receipts for a specific user's payments
     * Usage: php yii generate-existing-receipts/for-user <user_id>
     */
    public function actionForUser($userId)
    {
        echo "Finding payments for user #{$userId}...\n";

        $payments = Pagos::find()
            ->where(['user_id' => $userId])
            ->all();

        echo "Found " . count($payments) . " payments.\n";

        $generated = 0;

        foreach ($payments as $payment) {
            // Check if receipts already exist
            $existingReceipts = \app\models\Receipt::find()
                ->where(['payment_id' => $payment->id])
                ->exists();

            if ($existingReceipts) {
                echo "Payment #{$payment->id} already has receipts. Skipping.\n";
                continue;
            }

            echo "Processing payment #{$payment->id}... ";

            $installments = \app\models\Cuotas::find()
                ->where(['id_pago' => $payment->id])
                ->all();

            if (empty($installments)) {
                echo "SKIPPED - No installments linked.\n";
                continue;
            }

            $receipts = ReceiptGenerator::generateForPayment($payment);

            if (!empty($receipts)) {
                echo "SUCCESS - Generated " . count($receipts) . " receipts.\n";
                $generated++;
            } else {
                echo "FAILED.\n";
            }
        }

        echo "\nGenerated receipts for {$generated} payments.\n";

        return 0;
    }
}
