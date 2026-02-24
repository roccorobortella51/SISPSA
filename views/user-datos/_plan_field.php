<?php

use kartik\depdrop\DepDrop;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $modelContrato app\models\Contratos */
?>

<?= $form->field($modelContrato, 'plan_id')->widget(DepDrop::classname(), [
    'type' => DepDrop::TYPE_SELECT2,
    'options' => [
        'id' => 'plan_id',
        'placeholder' => 'Seleccione el Plan',
        'class' => 'form-control form-control-lg conditional-required',
        'allowClear' => true,
    ],
    'pluginOptions' => [
        'depends' => ['clinica_id'],
        'url' => Url::to(['/site/planes']),
        'initialize' => true,
    ],
    'pluginEvents' => [
        "change" => "function(e) {
            datosplan($(this).val());
        }",
    ]
])->label('Plan <span class=\"text-danger conditional-asterisk\">*</span>') ?>