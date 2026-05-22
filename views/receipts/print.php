<?php
// app/views/receipts/print.php

use yii\helpers\Html;

$this->title = 'Recibo ' . ($model ? $model->receipt_number : '');
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

        .print-controls button:hover {
            background: #0d2b4f;
        }
    </style>
</head>

<body>
    <div class="print-controls no-print">
        <button onclick="window.print();">🖨️ Imprimir Recibo</button>
        <button onclick="window.close();">✖️ Cerrar</button>
    </div>
    <?= isset($html) ? $html : '' ?>
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