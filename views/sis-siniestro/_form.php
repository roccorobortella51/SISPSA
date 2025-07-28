<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model app\models\SisSiniestro */
/* @var $form yii\widgets\ActiveForm */
/* @var $afiliado app\models\UserDatos */

?>

<div class="sis-siniestro-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="ms-panel">
        <div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-file-alt text-blue-600"></i> Datos del Siniestro
            </h3>
        </div>
        <div class="ms-panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-md-12" style="display: none;">
                             <?= $form->field($model, 'idclinica')->textInput(['value' => $afiliado->clinica_id]) ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'fecha')->textInput([ // Cambiado a input type="date"
                                'type' => 'date',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Seleccione la fecha',
                                'autocomplete' => 'off',
                                'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha, 'yyyy-MM-dd')
                            ])->label('Fecha del Siniestro') ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'hora')->textInput(['type' => 'time', 'class' => 'form-control form-control-lg'])->label('Hora del Siniestro') ?>
                        </div>
                        
                        <div class="col-md-12">
                            <?= $form->field($model, 'idbaremo')->widget(Select2::class, [
                                'data' =>  \yii\helpers\ArrayHelper::map(\app\models\Baremo::find()->where(['clinica_id' => $afiliado->clinica_id])->andWhere(['estatus' => 'Activo'])->all(), 'id', 'nombre_servicio'),
                                'options' => [
                                    'placeholder' => 'Seleccione un Baremo',
                                    'class' => 'form-control form-control-lg',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label('Baremo') ?>
                        </div>
                        
                        <div class="col-md-12">
                            <?= $form->field($model, 'atendido')->dropDownList(
                                [0 => 'No', 1 => 'Sí'],
                                ['prompt' => 'Seleccione estado', 'class' => 'form-control form-control-lg']
                            )->label('Atendido') ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'fecha_atencion')->textInput([ // Cambiado a input type="date"
                                'type' => 'date',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Seleccione la fecha',
                                'autocomplete' => 'off',
                                'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha, 'yyyy-MM-dd')
                            ])->label('Fecha de atencion') ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'hora_atencion')->textInput(['type' => 'time', 'class' => 'form-control form-control-lg'])->label('Hora de Atención') ?>
                        </div>
                        
                        <div class="col-md-12">
                            <?= $form->field($model, 'descripcion')->textarea(['rows' => 3, 'class' => 'form-control form-control-lg'])->label('Descripción del Siniestro') ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="ms-panel">
                        <div class="ms-panel-header">
                            <h3 class="section-title">
                                <i class="fas fa-user text-blue-600"></i> Datos del Afiliado
                            </h3>
                        </div>
                        <div class="ms-panel-body">
                            <?php echo $this->render('/user-datos/view', ['model' => $afiliado]); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group text-end mt-4">
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg me-2']) ?>
                <?= Html::a('Cancelar', ['index', 'user_id' => $afiliado->id], ['class' => 'btn btn-warning btn-lg me-2']); ?>
                <?php if ($model->isNewRecord): ?>
                    <?= Html::a('Limpiar', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-outline-dark btn-lg']); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
