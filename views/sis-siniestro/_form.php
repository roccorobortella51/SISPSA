<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus


/* @var $this yii\web\View */
/* @var $model app\models\SisSiniestro */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sis-siniestro-form">

        
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-12" style="display: none;">
                     <?= $form->field($model, 'idclinica')->textInput(['value' => $afiliado->clinica_id]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'fecha')->textInput(['type' => 'date']) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'hora')->textInput(['type' => 'time']) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'idbaremo')->widget(Select2::classname(), [
                        'data' =>  \yii\helpers\ArrayHelper::map(\app\models\Baremo::find(['clinica_id' => $afiliado->clinica_id])->andWhere(['estatus' => 'Activo'])->all(), 'id', 'nombre_servicio'),
                        'options' => [
                            'placeholder' => 'Seleccione', // Placeholder adaptado
                            'class' => 'form-control form-control-lg',
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                        ],
                    ])->label('Baremo') // Etiqueta adaptada
                ?> 
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'atendido')->dropDownList(
                        [0 => 'No', 1 => 'Sí'],
                        ['prompt' => 'Seleccione estado']
                    ) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'fecha_atencion')->textInput(['type' => 'date']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'hora_atencion')->textInput(['type' => 'time']) ?>
                </div>
            </div>

            <?= $form->field($model, 'descripcion')->textarea(['rows' => 1]) ?>

            <div class="form-group">
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-lg btn-success']) ?>
                <?= Html::a('Cancelar', ['index', 'user_id' => $afiliado->id], ['class' => 'btn btn-warning btn-lg me-2']); ?>
                <?php if ($model->isNewRecord): ?>
                    <?= Html::a('Limpiar', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-outline-dark btn-lg']); ?>
                <?php endif; ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>