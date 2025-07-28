<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SisSiniestro $model */
/** @var app\models\UserDatos $afiliado */

$this->title = 'Crear Atención: ' . Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula);
$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="sis-siniestro-create">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header d-flex justify-content-between align-items-center">
            <h1><?= Html::encode($this->title); ?></h1>
            
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'afiliado' => $afiliado,
            ]) ?>        
        </div>
    </div>
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
