<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // Para campos con máscaras como RIF y teléfono
use app\components\UserHelper;
use kartik\widgets\SwitchInput


?>

<div class="user-form">
    <?php $form = ActiveForm::begin([]); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'username')->label('NOMBRE DE USUARIO')->textInput([
                'maxlength' => true,
                'class' => 'form-control form-control-lg', // Usamos 'form-control' para tamaño estándar
                'placeholder' => 'Nombre de usuario',
                'label' => 'Nombre de usuario',
                'autofocus' => true,
            ]) ?>
        </div>
        <div class="col-md-4">
           <?= $form->field($model, 'email')->label('CORREO ELECTRÓNICO')->textInput([
                'maxlength' => true,
                'class' => 'form-control form-control-lg', // Usamos 'form-control' para tamaño estándar
                'placeholder' => 'Correo electrónico',
                'label' => 'Correo electrónico',
                'autofocus' => true,
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'password')->label('CONTRASEÑA')->passwordInput([
                'maxlength' => true,
                'class' => 'form-control form-control-lg', // Usamos 'form-control' para tamaño estándar
                'placeholder' => 'Contraseña',
                'label' => 'Contraseña',
                'autofocus' => true,
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'roles')->label('ROLES')->widget(Select2::classname(), [
                    'data' => UserHelper::getRolesList(),
                    'options' => [
                        'placeholder' => 'Seleccione',
                            'class' => 'form-control form-control-lg',
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                ],]); 
            ?>
        </div>
    </div>
    <?= $form->field($model, 'status')->widget(SwitchInput::classname(), [
    'options' => [
        'label' => false,
    ],
    'pluginOptions' => [
        'onText' => 'Activo',
        'offText' => 'Inactivo',
        'onColor' => 'success',
        'offColor' => 'danger',
        'size' => 'large',
    ],
]);
?>
    




    

    <div class="form-group text-rigth mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Usuario', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-lg btn-warning']); ?>

        <?php if ($model->isNewRecord) { echo Html::a('Limpiar', ['create'], ['class' => 'btn btn-lg btn-outline-dark']); } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
