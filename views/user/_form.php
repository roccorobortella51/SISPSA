<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // Para campos con máscaras como RIF y teléfono
/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $userDatos app\models\UserDatos */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form container">

    <?php $form = ActiveForm::begin(); ?>


    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'username')->label('Nombre de usuario')->textInput([
                'maxlength' => true, 
                'class' => 'form-control form-control-lg'
                ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'email')->label('Correo Electronico')->textInput([
                'maxlength' => true, 
                'class' => 'form-control form-control-lg'
                ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'class' => 'form-control form-control-lg'])->hint('Dejar en blanco para no cambiar la contraseña') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($userDatos, 'nombres')->textInput(['maxlength' => true, 'class' => 'form-control form-control-lg']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($userDatos, 'apellidos')->textInput(['maxlength' => true, 'class' => 'form-control form-control-lg']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($userDatos, 'telefono')->textInput(['maxlength' => true, 'class' => 'form-control form-control-lg']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($userDatos, 'email')->textInput(['maxlength' => true, 'class' => 'form-control form-control-lg']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($userDatos, 'sexo')->dropDownList(['M' => 'Male', 'F' => 'Female'], ['prompt' => 'Select Gender', 'class' => 'form-control form-control-lg']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($userDatos, 'fechanac')->input('date', ['class' => 'form-control form-control-lg']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($userDatos, 'ciudad')->textInput(['maxlength' => true, 'class' => 'form-control form-control-lg']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($userDatos, 'municipio')->textInput(['maxlength' => true, 'class' => 'form-control form-control-lg']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($userDatos, 'parroquia')->textInput(['maxlength' => true, 'class' => 'form-control form-control-lg']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($userDatos, 'direccion')->textInput(['maxlength' => true, 'class' => 'form-control form-control-lg']) ?>
        </div>
    </div>


    <div class="form-group text-right mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> ' . ($model->isNewRecord ? 'Crear' : 'Actualizar'), ['class' => $model->isNewRecord ? 'btn btn-success btn-lg' : 'btn btn-primary btn-lg']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
