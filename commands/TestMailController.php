<?php

namespace app\commands;

use yii\console\Controller;
use Yii;

class TestMailController extends Controller
{
    public function actionIndex($to = 'rocco.robortella@gmail.com')
    {
        echo "Sending test email to {$to}...\n";

        try {
            $mail = Yii::$app->mailer->compose()
                ->setFrom(['notificaciones@sispsatest.com' => 'SISPSA Test'])
                ->setTo($to)
                ->setSubject('Test Email - ' . date('Y-m-d H:i:s'))
                ->setHtmlBody('<h1>Success!</h1><p>Your Gmail SMTP configuration is working!</p><p>Time: ' . date('Y-m-d H:i:s') . '</p>')
                ->send();

            if ($mail) {
                echo "✅ Email sent successfully!\n";
                echo "Check {$to} inbox (and spam folder)\n";
            } else {
                echo "❌ Failed to send email.\n";
            }
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
}
