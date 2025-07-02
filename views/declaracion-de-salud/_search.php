<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\DeclaracionDeSaludSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="declaracion-de-salud-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'p1_sino') ?>

    <?= $form->field($model, 'p1_especifica') ?>

    <?= $form->field($model, 'p2_sino') ?>

    <?php // echo $form->field($model, 'p2_especifica') ?>

    <?php // echo $form->field($model, 'p3_sino') ?>

    <?php // echo $form->field($model, 'p3_especifica') ?>

    <?php // echo $form->field($model, 'p4_sino') ?>

    <?php // echo $form->field($model, 'p4_especifica') ?>

    <?php // echo $form->field($model, 'p5_sino') ?>

    <?php // echo $form->field($model, 'p5_especifica') ?>

    <?php // echo $form->field($model, 'p6_sino') ?>

    <?php // echo $form->field($model, 'p6_especifica') ?>

    <?php // echo $form->field($model, 'p7_sino') ?>

    <?php // echo $form->field($model, 'p7_especifica') ?>

    <?php // echo $form->field($model, 'p8_sino') ?>

    <?php // echo $form->field($model, 'p8_especifica') ?>

    <?php // echo $form->field($model, 'p9_sino') ?>

    <?php // echo $form->field($model, 'p9_especifica') ?>

    <?php // echo $form->field($model, 'p10_sino') ?>

    <?php // echo $form->field($model, 'p10_especifica') ?>

    <?php // echo $form->field($model, 'p11_sino') ?>

    <?php // echo $form->field($model, 'p11_especifica') ?>

    <?php // echo $form->field($model, 'p12_sino') ?>

    <?php // echo $form->field($model, 'p12_especifica') ?>

    <?php // echo $form->field($model, 'p13_sino') ?>

    <?php // echo $form->field($model, 'p13_especifica') ?>

    <?php // echo $form->field($model, 'p14_sino') ?>

    <?php // echo $form->field($model, 'p14_especifica') ?>

    <?php // echo $form->field($model, 'p15_sino') ?>

    <?php // echo $form->field($model, 'p15_especifica') ?>

    <?php // echo $form->field($model, 'p16_sino') ?>

    <?php // echo $form->field($model, 'p16_especifica') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'ver_usuario_id') ?>

    <?php // echo $form->field($model, 'ver_observacion') ?>

    <?php // echo $form->field($model, 'ver_si_no') ?>

    <?php // echo $form->field($model, 'ver_fecha') ?>

    <?php // echo $form->field($model, 'url_video_declaracion') ?>

    <?php // echo $form->field($model, 'estatus') ?>

    <?php // echo $form->field($model, 'user_id') ?>

    <?php // echo $form->field($model, 'estatura') ?>

    <?php // echo $form->field($model, 'peso') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
