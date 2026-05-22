<?php
// views/sis-siniestro/_delete_confirmation.php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\Modal;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\models\SisSiniestro $model
 * @var string $termino
 * @var string $terminoLower
 */

$modalId = 'delete-confirmation-modal';
?>

<?php Modal::begin([
    'id' => $modalId,
    'title' => '<i class="fas fa-exclamation-triangle text-danger mr-2"></i> Confirmar Eliminación',
    'size' => 'modal-md',
    'closeButton' => false,
    'clientOptions' => [
        'backdrop' => 'static',
        'keyboard' => false,
    ],
]); ?>

<div class="delete-confirmation-content">
    <div class="alert alert-warning border-left-warning mb-4">
        <div class="d-flex">
            <div class="mr-3">
                <i class="fas fa-ban fa-2x text-danger"></i>
            </div>
            <div>
                <h5 class="alert-heading mb-2">¡Advertencia!</h5>
                <p class="mb-0">Está a punto de eliminar esta <strong><?= Html::encode($termino) ?></strong>.</p>
            </div>
        </div>
    </div>

    <div class="bg-light p-3 rounded mb-4">
        <h6 class="font-weight-bold text-dark mb-3">
            <i class="fas fa-info-circle text-info mr-2"></i>Detalles del registro:
        </h6>
        <table class="table table-sm table-borderless">
            <tr>
                <td class="pl-0" style="width: 35%;"><strong>Fecha:</strong></td>
                <td><?= Yii::$app->formatter->asDate($model->fecha) ?></td>
            </tr>
            <tr>
                <td class="pl-0"><strong>Hora:</strong></td>
                <td><?= Yii::$app->formatter->asTime($model->hora) ?></td>
            </tr>
            <tr>
                <td class="pl-0"><strong>Clínica:</strong></td>
                <td><?= Html::encode($model->clinica->nombre ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td class="pl-0"><strong>Costo Total:</strong></td>
                <td class="font-weight-bold text-danger">$<?= number_format($model->costo_total, 2) ?></td>
            </tr>
            <tr>
                <td class="pl-0"><strong>Servicios:</strong></td>
                <td>
                    <?php if ($model->baremos): ?>
                        <ul class="mb-0 pl-3">
                            <?php foreach ($model->baremos as $baremo): ?>
                                <li><?= Html::encode($baremo->nombre_servicio) ?> - $<?= number_format($baremo->precio, 2) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <em class="text-muted">Sin servicios</em>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="alert alert-danger mb-4">
        <div class="d-flex">
            <div class="mr-3">
                <i class="fas fa-dollar-sign fa-2x"></i>
            </div>
            <div>
                <strong>Impacto en el seguro:</strong> La eliminación de este registro afectará el límite de cobertura del afiliado,
                restaurando los servicios utilizados al límite disponible.
            </div>
        </div>
    </div>

    <?php $form = ActiveForm::begin([
        'action' => ['delete', 'id' => $model->id],
        'method' => 'post',
        'id' => 'delete-confirmation-form',
    ]); ?>

    <div class="form-group">
        <label class="font-weight-bold text-danger">
            <i class="fas fa-pen-alt mr-2"></i>Motivo de eliminación <span class="text-danger">*</span>
        </label>
        <?= Html::textarea('reason', '', [
            'class' => 'form-control',
            'rows' => 3,
            'placeholder' => 'Por favor, explique detalladamente el motivo por el cual está eliminando este registro...',
            'required' => true,
            'maxlength' => 500,
        ]) ?>
        <small class="form-text text-muted">
            <i class="fas fa-info-circle mr-1"></i>Este motivo será registrado en el log de auditoría del sistema.
        </small>
    </div>

    <div class="form-group d-flex justify-content-between align-items-center mb-0">
        <button type="button" class="btn btn-outline-secondary" onclick="$('#<?= $modalId ?>').modal('hide');">
            <i class="fas fa-times mr-2"></i>Cancelar
        </button>
        <?= Html::submitButton('<i class="fas fa-trash-alt mr-2"></i>Confirmar Eliminación', [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => false,
            ],
            'onclick' => 'return confirm("¿Está completamente seguro de eliminar este registro? Esta acción no se puede deshacer.");'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
// Auto-show the modal
$js = <<<JS
    $('#{$modalId}').modal('show');
JS;
$this->registerJs($js);
?>

<?php Modal::end(); ?>