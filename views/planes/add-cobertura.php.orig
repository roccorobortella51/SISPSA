<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PlanesItemsCobertura */
/* @var $plan app\models\Planes */
/* @var $baremo app\models\Baremo */

$this->title = "Agregar Servicio al Plan: {$plan->nombre}";
$this->params['breadcrumbs'][] = ['label' => 'Planes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $plan->nombre, 'url' => ['view', 'id' => $plan->id]];
$this->params['breadcrumbs'][] = 'Agregar Servicio';
?>

<div class="planes-items-cobertura-create">

    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h1 class="card-title mb-0">
                <i class="fas fa-plus-circle"></i> <?= Html::encode($this->title) ?>
            </h1>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                Estás agregando el servicio: <strong><?= Html::encode($baremo->nombre_servicio) ?></strong>
            </div>
            
            <?php $form = ActiveForm::begin(); ?>
            
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'porcentaje_cobertura')->textInput([
                        'type' => 'number',
                        'min' => 0,
                        'max' => 100
                    ])->hint('Porcentaje de cobertura (0-100)') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'cantidad_limite')->textInput([
                        'type' => 'number',
                        'min' => 0
                    ])->hint('Cantidad máxima cubierta (0 para ilimitado)') ?>
                </div>
            </div>
            
            <?= $form->field($model, 'plazo_espera')->textInput([
                'placeholder' => 'Ej: 30 días, 6 meses, etc.'
            ]) ?>
            
            <?= $form->field($model, 'nombre_servicio')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'baremo_id')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'plan_id')->hiddenInput()->label(false) ?>
            
            <div class="form-group mt-4">
                <?= Html::submitButton('<i class="fas fa-check"></i> Guardar Servicio', [
                    'class' => 'btn btn-success btn-lg'
                ]) ?>
                <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['view', 'id' => $plan->id], [
                    'class' => 'btn btn-danger btn-lg'
                ]) ?>
            </div>
            
            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>

<style>
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    .card-header {
        font-size: 1.25rem;
    }
    .form-control {
        border-radius: 0.5rem;
    }
</style>