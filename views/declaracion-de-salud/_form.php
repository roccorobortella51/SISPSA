<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\DeclaracionDeSalud $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="declaracion-de-salud-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'p1_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p1_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p2_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p2_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p3_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p3_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p4_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p4_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p5_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p5_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p6_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p6_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p7_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p7_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p8_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p8_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p9_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p9_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p10_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p10_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p11_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p11_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p12_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p12_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p13_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p13_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p14_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p14_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p15_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p15_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p16_sino')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'p16_especifica')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'deleted_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'ver_usuario_id')->textInput() ?>

    <?= $form->field($model, 'ver_observacion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'ver_si_no')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'ver_fecha')->textInput() ?>

    <?= $form->field($model, 'url_video_declaracion')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'estatus')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'estatura')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'peso')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
