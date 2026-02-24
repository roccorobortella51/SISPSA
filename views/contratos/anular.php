<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Contratos $model */

$this->title = 'Anular Contrato #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Contratos', 'url' => ['index', 'user_id' => $model->user_id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="contratos-anular">

    <div class="card border-danger">
        <div class="card-header bg-danger text-white">
            <h1 class="card-title mb-0" style="font-size: 2.0rem; font-weight: 700;">
                <i class="fas fa-exclamation-triangle mr-3" style="font-size: 2.5rem;"></i>
                <?= Html::encode($this->title) ?>
            </h1>
        </div>

        <div class="card-body">
            <div class="alert alert-warning" style="font-size: 1.2rem; padding: 20px;">
                <i class="fas fa-info-circle mr-2" style="font-size: 2.5rem;"></i>
                <strong>¡Atención!</strong> Está a punto de anular este contrato. Esta acción no se puede deshacer.
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h5 style="font-size: 1.3rem; font-weight: 600; margin-bottom: 15px;">Información del Contrato</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th style="font-size: 1.1rem;">ID</th>
                            <td style="font-size: 1.1rem;"><?= $model->id ?></td>
                        </tr>
                        <tr>
                            <th style="font-size: 1.1rem;">N° Contrato</th>
                            <td style="font-size: 1.1rem;"><?= $model->nrocontrato ?: 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th style="font-size: 1.1rem;">Fecha Inicio</th>
                            <td style="font-size: 1.1rem;"><?= Yii::$app->formatter->asDate($model->fecha_ini) ?></td>
                        </tr>
                        <tr>
                            <th style="font-size: 1.1rem;">Fecha Vencimiento</th>
                            <td style="font-size: 1.1rem;"><?= $model->fecha_ven ? Yii::$app->formatter->asDate($model->fecha_ven) : 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th style="font-size: 1.1rem;">Monto</th>
                            <td style="font-size: 1.1rem;">$<?= number_format($model->monto, 2) ?></td>
                        </tr>
                        <tr>
                            <th style="font-size: 1.1rem;">Estatus Actual</th>
                            <td>
                                <?php
                                $statusClass = [
                                    'Registrado' => 'badge-primary',
                                    'Activo' => 'badge-success',
                                    'Vencido' => 'badge-warning',
                                    'suspendido' => 'badge-secondary',
                                    'Pendiente' => 'badge-info',
                                ];
                                $class = $statusClass[$model->estatus] ?? 'badge-light';
                                echo '<span class="badge ' . $class . '" style="font-size: 1.1rem; padding: 8px 12px;">' . $model->estatus . '</span>';
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <h5 style="font-size: 1.3rem; font-weight: 600; margin-bottom: 15px;">Información del Afiliado</h5>
                    <?php if ($model->user): ?>
                        <table class="table table-bordered">
                            <tr>
                                <th style="font-size: 1.1rem;">Nombre</th>
                                <td style="font-size: 1.1rem;"><?= $model->user->nombres . ' ' . $model->user->apellidos ?></td>
                            </tr>
                            <tr>
                                <th style="font-size: 1.1rem;">Cédula</th>
                                <td style="font-size: 1.1rem;"><?= $model->user->tipo_cedula . ' ' . $model->user->cedula ?></td>
                            </tr>
                            <tr>
                                <th style="font-size: 1.1rem;">Email</th>
                                <td style="font-size: 1.1rem;"><?= $model->user->email ?></td>
                            </tr>
                            <tr>
                                <th style="font-size: 1.1rem;">Teléfono</th>
                                <td style="font-size: 1.1rem;"><?= $model->user->telefono ?></td>
                            </tr>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            $pendingCuotas = \app\models\Cuotas::find()
                ->where(['contrato_id' => $model->id])
                ->andWhere(['estatus' => 'pendiente'])
                ->count();

            if ($pendingCuotas > 0):
            ?>
                <div class="alert alert-danger mt-3" style="font-size: 1.2rem; padding: 20px;">
                    <i class="fas fa-exclamation-circle mr-2" style="font-size: 1.5rem;"></i>
                    <strong>¡Importante!</strong> Este contrato tiene <strong><?= $pendingCuotas ?></strong> cuota(s) pendiente(s) de pago.
                    Al anular el contrato, estas cuotas quedarán sin efecto.
                </div>
            <?php endif; ?>

            <?php $form = ActiveForm::begin(); ?>

            <div class="form-group">
                <label for="motivo" class="font-weight-bold" style="font-size: 1.2rem; margin-top: 15px">Motivo de la Anulación <span class="text-danger">*</span></label>
                <?= Html::textarea('motivo', null, [
                    'class' => 'form-control',
                    'rows' => 4,
                    'required' => true,
                    'placeholder' => 'Indique la razón por la cual se anula este contrato...',
                    'style' => 'font-size: 1.1rem; padding: 12px;'
                ]) ?>
            </div>

            <div class="form-group">
                <div style="display: flex; align-items: flex-start; gap: 25px; padding: 10px 0; background-color: #fff3f3; border-radius: 8px; padding: 15px;">
                    <input type="checkbox" id="confirm" required
                        style="margin-top: 5px; width: 24px; height: 24px; flex-shrink: 0; margin-left: 10px;">
                    <label for="confirm" class="text-danger font-weight-bold"
                        style="margin-left: 5px; line-height: 1.6; font-size: 1.3rem; letter-spacing: 0.3px;">
                        Confirmo que deseo ANULAR este contrato y entiendo que esta acción es irreversible
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-top: 30px;">
                <?= Html::a('Cancelar', ['index', 'user_id' => $model->user_id], [
                    'class' => 'btn btn-secondary',
                    'style' => 'font-size: 1.2rem; padding: 10px 25px; margin-right: 15px;'
                ]) ?>
                <?= Html::submitButton('Anular Contrato', [
                    'class' => 'btn btn-danger',
                    'style' => 'font-size: 1.2rem; padding: 10px 25px;',
                    'data' => [
                        'confirm' => '¿Está completamente seguro? Esta acción no se puede deshacer.',
                    ]
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>