<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\components\UserHelper;
use yii\helpers\Url;
use app\models\Agente;

$mode = $mode ?? 'create';
$isNewRecord = $isNewRecord ?? true;

if ($model->isNewRecord) {
    $readOnly = false;
}else{
    $readOnly = true;
}
?>

<div class="rm-clinica-form">
    <div class="ms-panel-body">
        <?php $form = ActiveForm::begin(); ?>

        <?php if (!$model->isNewRecord) { ?>
<div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
    <div class="col">
        <?= Html::a(
            '<i class="fas fa-undo mr-2"></i> Volver',
            ['index'],
            [
                'class' => 'btn btn-primary btn-lg w-100', // Mantengo btn-lg y w-100
                'style' => 'padding: 2rem 3rem; font-size: 1.5rem;', // Estilos en línea para hacerlo más grande y grueso
            ]
        ) ?>
    </div>

    <div class="col">
        <?= Html::a(
            '<i class="fas fa-users mr-2"></i> FUERZA DE VENTA',
            ['agente-fuerza/index-by-agente', 'agente_id' => $model->id],
            [
                'class' => 'btn btn-primary btn-lg w-100', // Mantengo btn-lg y w-100
                'style' => 'padding: 2rem 3rem; font-size: 1.5rem;', // Estilos en línea para hacerlo más grande y grueso
            ]
        ) ?>
    </div>
</div>
<?php } ?>

        <br>

        <div class="row mb-3">
            <div class="col-md-4">
                <?= $form->field($model, 'nom')->label('NOMBRE DE LA AGENCIA')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Nombre completo del agente',
                    'autofocus' => true,
                ]) ?>
            </div>

            <div class="col-md-4">
                <?= $form->field($model, 'sudeaseg')->label('CÓDIGO SUDEASEG')->textInput([
                    'maxlength' => true,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Ingrese el código SUDEASEG',
                ]) ?>
            </div>
<div class="col-md-4">
    <?php
    $agentesList = UserHelper::getAgenteFuerzaList();

    $hasRealAgents = (count($agentesList) > 1) || (count($agentesList) === 1 && !isset($agentesList['0']) && !isset($agentesList['']));

    $select2Options = [
        'data' => $agentesList,
        'options' => [
            'placeholder' => 'Seleccione',
            'class' => 'form-control form-control-lg'
        ],
        'pluginOptions' => [
            'allowClear' => false,
        ],
    ];

    if (!$hasRealAgents) {
        $select2Options['options']['placeholder'] = 'No Disponible';
        $select2Options['options']['disabled'] = true;
    }

    echo $form->field($model, 'idusuariopropietario')->label('NOMBRE DEL PROPIETARIO')->widget(Select2::classname(), $select2Options);
    ?>
</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <?= $form->field($model, 'por_venta')->label('PORCENTAJE POR VENTA')->textInput([
                    'class' => 'form-control',
                    'placeholder' => '% Venta',
                    'type' => 'number',
                    'step' => '0.01',
                    'class' => 'form-control form-control-lg'
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'por_asesor')->label('PORCENTAJE DE ASESORÍA')->textInput([
                    'class' => 'form-control',
                    'placeholder' => '% Asesoría',
                    'type' => 'number',
                    'step' => '0.01',
                    'class' => 'form-control form-control-lg'
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'por_cobranza')->label('PORCENTAJE POR COBRANZA')->textInput([
                    'class' => 'form-control',
                    'placeholder' => '% Cobranza',
                    'type' => 'number',
                    'step' => '0.01',
                    'class' => 'form-control form-control-lg'
                ]) ?>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <?= $form->field($model, 'por_post_venta')->label('PORCENTAJE POST VENTA')->textInput([
                    'class' => 'form-control',
                    'placeholder' => '% Post-Venta',
                    'type' => 'number',
                    'step' => '0.01',
                    'class' => 'form-control form-control-lg'
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'por_agente')->label('PORCENTAJE DE AGENCIA')->textInput([
                    'class' => 'form-control',
                    'placeholder' => '% Agente',
                    'type' => 'number',
                    'step' => '0.01',
                    'class' => 'form-control form-control-lg'
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'por_max')->label('PORCENTAJE MÁXIMO')->textInput([
                    'class' => 'form-control',
                    'placeholder' => '% Máximo',
                    'type' => 'number',
                    'step' => '0.01',
                    'class' => 'form-control form-control-lg'
                ]) ?>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-start">
                <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar', ['class' => 'btn btn-success btn-lg mr-3']) ?>
                
                <?= Html::a(
                        '<i class="fas fa-undo mr-2"></i> Volver', 
                        '#',
                        [
                            'class' => 'btn btn-secondary btn-lg mr-3',
                            'onclick' => 'window.history.back(); return false;', 
                            'title' => 'Volver a la página anterior', 
                        ]
                    ) ?>

                <?php
                if (isset($isNewRecord) && $isNewRecord) { 
                    echo Html::button('<i class="fas fa-sync-alt mr-2"></i> Refrescar', [
                        'class' => 'btn btn-info btn-lg',
                        'id' => 'btn-refrescar-form'
                    ]);
                }
                ?>
            </div>
        </div>


        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<<JS
$('#btn-refrescar-form').on('click', function() {
    $(this).closest('form')[0].reset(); 
});
JS;
$this->registerJs($js);
?>
