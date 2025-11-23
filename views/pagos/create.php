<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Pagos $model */
/** @var array $cuotas */

$this->title = 'Update Pagos: ' . $model->id;

// --- PREPARE DATA FOR DISPLAY ---
$nombres = $model->userDatos->nombres ?? 'N/A';
$apellidos = $model->userDatos->apellidos ?? 'N/A';
$cedula = $model->userDatos->cedula ?? 'N/A';
$tipoCedula = $model->userDatos->tipo_cedula ?? '';
$nombreCompleto = $nombres . ' ' . $apellidos;

// --- CREATE STYLIZED HTML FOR DISPLAY ---
$nombreCompletoStyled = Html::tag('span', Html::encode($nombreCompleto), [
    'style' => 'color: yellow; font-weight: bold;' 
]);

$cedulaStyled = Html::tag('span', Html::encode(' (C.I.: ' . $tipoCedula . $cedula . ')'), [
    'style' => 'color: white; font-weight: bold;' 
]);

$richDisplayTitle = 'Editar Pago para: ' . $nombreCompletoStyled . $cedulaStyled;

?>
<div class="pagos-update">
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-undo-alt"></i> Volver', Url::to(['contratos/index', 'user_id' => $model->user_id]), ['class' => 'btn btn-info btn-lg']) ?> 
        </div>
    </div>

    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header bg-primary text-white text-center py-3">
                <h3 class="card-title mb-0 display-3" style="font-size: 1.8em; line-height: 1.2;"> 
                    <i class="fas fa-edit me-2"></i> <?= $richDisplayTitle ?>
                </h3>
            </div>
            <div class="ms-panel-body">
                <?= $this->render('_form', [
                    'model' => $model,
                    'cuotas' => $cuotas, // THIS IS THE KEY LINE - pass cuotas to the form
                    'user_id' => $model->user_id,
                    'isEditable' => true,
                ]) ?>
            </div>
        </div>
    </div>
</div>