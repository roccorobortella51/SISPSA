<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // Para campos con máscaras como RIF y teléfono
use app\components\UserHelper;
use kartik\widgets\SwitchInput


?>

<div class="user-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'needs-validation']]); ?>

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
            <?= $form->field($model, 'password')->label('CONTRASEÑA')->passwordInput([ // ¡CAMBIO AQUÍ: 'password' en lugar de 'password_hash'!
                'maxlength' => true,
                'class' => 'form-control form-control-lg', 
                'placeholder' => 'Contraseña',
                'label' => 'Contraseña', // Este label es redundante si ya lo pones en label()
                'autofocus' => true,
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model2, 'role')->label('ROLES')->widget(Select2::classname(), [
                    'data' => UserHelper::getRolesAllRoles(),
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
    

    <?= $form->field($model, 'status')->label('ESTATUS')->widget(SwitchInput::classname(), [
    'options' => [
        'label' => false,
    ],
    'pluginOptions' => [
        'onText' => 'Activo',
        'offText' => 'Inactivo',
        'onColor' => 'success',
        'offColor' => 'danger',
        'size' => 'large',
        'onValue' => 1,  // Envía 1 desde el formulario (se convertirá a 10 en beforeSave)
        'offValue' => 0, // Envía 0 desde el formulario (se convertirá a 9 o 0 en beforeSave)
    ],
]); ?>

    <br><br>
    <h1>Datos Personales del usuario</h1>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model2, 'nombres')->textarea([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ingrese sus nombres completos',
                'rows' => 1
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model2, 'apellidos')->textarea([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ingrese sus apellidos completos',
                'rows' => 1
            ]) ?>
        </div>
    </div>

    <div class="row">
    
    <div class="col-md-1">
        <?= $form->field($model2, 'tipo_cedula')->widget(Select2::class, [ // Changed field to tipo_cedula
            'data' => [ // Updated data options
                'V' => 'V',
                'E' => 'E',
                'J' => 'J',
                'P' => 'P',
                'N' => 'N',
                'M' => 'M',
            ],
            'options' => [
                'placeholder' => 'Tipo', // Updated placeholder for brevity
                'class' => 'form-control form-control-lg', 
            ],
            'pluginOptions' => [
                'allowClear' => true, 
            ],
        ])->label('Tipo') // Updated label for brevity
        ?>
    </div>

   
    <div class="col-md-5">
        <?= $form->field($model2, 'cedula')->textInput([
            'class' => 'form-control form-control-lg',
            'placeholder' => 'Ejemplo: 12345678'
        ])->label('Cédula de Identidad') // Added label for clarity
        ?>
    </div>
    
   
    <div class="col-md-3">
        <?= $form->field($model2, 'fechanac')->textInput([
            'class' => 'form-control form-control-lg',
            'type' => 'date',
            'placeholder' => 'Fecha de Nacimiento' // Updated placeholder for brevity
        ])->label('Fecha de Nacimiento') ?>
    </div>

   
    <div class="col-md-3">
        <?= $form->field($model2, 'sexo')->widget(Select2::class, [
            'data' => [
                'Masculino' => 'Masculino',
                'Femenino' => 'Femenino',
            ],
            'options' => [
                'placeholder' => 'Sexo...', // Updated placeholder for brevity
                'class' => 'form-control form-control-lg', 
            ],
            'pluginOptions' => [
                'allowClear' => true, 
            ],
        ])->label('Sexo')
        ?>
    </div>

</div>
    <div class="row">
    
        <div class="col-md-4">
            <?= $form->field($model2, 'telefono')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ejemplo: 04121234567'
            ]) ?>
        </div>

        <div class="col-md-4">
            <?= $form->field($model2, 'email')->textInput([
                'class' => 'form-control form-control-lg',
                'type' => 'email',
                'placeholder' => 'Ejemplo: usuario@dominio.com'
            ])->label("Correo electrónico") ?>
        </div>

        <div class="col-md-4">
            <?= $form->field($model2, 'estado')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ejemplo: Distrito Capital'
            ]) ?>
        </div>

    </div>


    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model2, 'ciudad')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ejemplo: Caracas'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model2, 'municipio')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ejemplo: Libertador'
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model2, 'parroquia')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ejemplo: El Recreo'
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model2, 'direccion')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ingrese su dirección completa',
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model2, 'codigoValidacion')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Código de validación (si aplica)'
            ]) ?>
        </div>
    </div>

    <div class="form-group text-rigth mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Usuario', ['class' => 'btn btn-success btn-lg']); ?>
       
        <?= Html::a('<i class="fas fa-undo"></i> Volver', ['index'], ['class' => 'btn btn-primary btn-lg']); ?>

        <?php if ($model->isNewRecord) { echo Html::a('Limpiar', ['create'], ['class' => 'btn btn-lg btn-outline-dark']); } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
