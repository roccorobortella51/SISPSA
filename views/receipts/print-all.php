<?php
// app/views/receipts/print-all.php

use yii\helpers\Html;

$this->title = 'Recibos - Pago #' . $payment->id;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .page-break {
                page-break-before: always;
            }

            .no-print {
                display: none;
            }
        }

        .print-controls {
            text-align: center;
            padding: 20px;
            background: #f0f0f0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .print-controls button {
            margin: 0 10px;
            padding: 10px 20px;
            background: #1a3a6e;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .print-header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="print-controls no-print">
        <button onclick="window.print();">🖨️ Imprimir Todos los Recibos</button>
        <button onclick="window.close();">✖️ Cerrar</button>
    </div>

    <?php foreach ($receipts as $index => $receipt): ?>
        <?php if ($index > 0): ?>
            <div class="page-break"></div>
        <?php endif; ?>
        <?= $receipt->html_content ?>
    <?php endforeach; ?>

    <script>
        if (window.location.search.indexOf('auto_print=1') > -1) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>

</html>