<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\UserHelper;
/** @var yii\web\View $this */
/** @var app\models\Baremo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ms-panel-body">
<?php $form = ActiveForm::begin(); ?>
<div class="row">
    <div class="col-md-2">
        <?= $form->field($model, 'nombre_servicio')->textInput() ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'descripcion')->textInput() ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'costo')->textInput() ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'precio')->textInput() ?>
    </div>
    <div class="col-md-2">
        <?php echo $form->field($model, 'area_id')->widget(\kartik\select2\Select2::classname(), [
                'language' => 'es',
                'theme' => \kartik\select2\Select2::THEME_KRAJEE_BS4,
                'data' => UserHelper::getAreaList(),
                'options' => ['placeholder' => 'Seleccione'], // Wrap the placeholder option within an options array
                                            'pluginOptions' => [
                                                'allowClear' => true // Set the allowClear option to true
                                            ],
                    ])->label("Area");
        ?>
    </div>
    <div class="col-md-12">
    <div class="form-group text-rigth mt-4" style="margin-right:10px;">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('Cancelar', ['index', 'clinica_id' => $model->clinica_id], ['class' => 'btn btn-lg btn-warning']); ?>
    </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
</div>
