
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\UserHelper;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\models\Baremo $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
    /* En tu archivo CSS o en la vista */
.card {
    margin-bottom: 20px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card-header {
    font-weight: bold;
}

.table th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeeba;
}
</style>

<div class="baremo-form">
    <div class="ms-panel-body">
    <?php $form = ActiveForm::begin(['id' => 'plan-form']); ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Información Básica del Plan</h3>
            </div>
            <div class="card-body">
                <?= $form->field($model, 'nombre')->textInput(['maxlength' => true]) ?>
                
                <?= $form->field($model, 'descripcion')->textInput() ?>
                
                <?= $form->field($model, 'precio')->textInput(['type' => 'number', 'step' => '0.01']) ?>
                
                <?= $form->field($model, 'estatus')->dropDownList([
                    'Activo' => 'Activo', 
                    'Inactivo' => 'Inactivo'
                ], ['prompt' => 'Seleccione...']) ?>
                
                <?= $form->field($model, 'edad_limite')->textInput(['type' => 'number']) ?>
                <?= $form->field($model, 'edad_minima')->textInput(['type' => 'number']) ?>
                
                
                <?= $form->field($model, 'comision')->textInput(['type' => 'number', 'step' => '0.01']) ?>
                <?= $form->field($model, 'cobertura')->textInput(['type' => 'number', 'step' => '0.01']) ?>

            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Coberturas del Plan</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style=" overflow-y: auto;">
                <?php if (!empty($itemsModels)): ?>
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Servicio (Baremos)</th>
                                <!--<th width="15%">% Cobertura</th> -->
                                <th width="50%">Límite</th>
                                <th width="50%">Plazo Espera Mes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itemsModels as $index => $item): ?>
                                <tr>
                                    <td>
                                        <?= Html::activeHiddenInput($item, "[$index]baremo_id") ?>
                                        <?= Html::activeHiddenInput($item, "[$index]nombre_servicio") ?>
                                        <b>Servicio:</b> <?= $item->baremo->nombre_servicio ?><br>
                                        <b>Descripcion:</b> <?= $item->baremo->descripcion ?><br>
                                        <b>Area:</b> <?= $item->baremo->area->nombre ?>
                                    </td>
                                    <?php /*
                                    <td>
                                        <?= $form->field($item, "[$index]porcentaje_cobertura")
                                            ->textInput(['type' => 'number', 'min' => 0, 'max' => 100])
                                            ->label(false) ?>
                                    </td>
                                    */?>
                                    <td>
                                        <?= $form->field($item, "[$index]cantidad_limite")
                                            ->textInput(['type' => 'number', 'min' => 0])
                                            ->label(false) ?>
                                    </td>
                                    <td>
                                        <?= $form->field($item, "[$index]plazo_espera")
                                            ->textInput(['type' => 'number', 'min' => 0, 'max' => 100])
                                            ->label(false) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No se encontraron baremos para esta clínica. 
                        <?= Html::a('Agregar baremos', ['/baremo/index', 'clinica_id' => $clinica->id], [
                            'class' => 'alert-link'
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="form-group mt-3">
    <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Plan', ['class' => 'btn btn-success btn-lg']) ?>
    <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['index', 'clinica_id' => $clinica->id], ['class' => 'btn btn-danger btn-lg']) ?>
</div>

<?php ActiveForm::end(); ?>
    </div>
</div>
<?php 
// En tu vista
$js = <<<JS
// Validar que edad mínima < edad límite
$('#plan-edad_minima, #plan-edad_limite').on('change', function() {
    var min = parseInt($('#plan-edad_minima').val());
    var max = parseInt($('#plan-edad_limite').val());
    
    if (min && max && min >= max) {
        alert('La edad mínima debe ser menor que la edad límite');
        $(this).val('');
    }
});

// Validar porcentajes entre 0-100
$('input[id*="porcentaje_cobertura"]').on('change', function() {
    var val = parseInt($(this).val());
    if (val < 0 || val > 100) {
        alert('El porcentaje debe estar entre 0 y 100');
        $(this).val('');
    }
});
JS;
$this->registerJs($js);

?>

