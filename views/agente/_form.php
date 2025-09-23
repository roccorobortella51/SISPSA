<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\components\UserHelper;
use yii\helpers\Url;
use yii\web\View;

$mode = $mode ?? 'create';
$isNewRecord = $isNewRecord ?? true;

?>

<div class="agente-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">

        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0" style="font-size: 1.5rem !important;"><i class="fas fa-info-circle mr-2"></i> Información General de la Agencia</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'nom')->label('NOMBRE DE LA AGENCIA', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->textInput([
                                'maxlength' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Nombre completo del agente',
                                'autofocus' => true,
                                'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
                            ]) ?>
                        </div>

                        <div class="col-md-6">
                            <?= $form->field($model, 'sudeaseg')->label('CÓDIGO SUDEASEG', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->textInput([
                                'maxlength' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el código SUDEASEG',
                                'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            $agentesList = UserHelper::getAgentesList();
                            $select2Options = [
                                'data' => $agentesList,
                                'options' => [
                                    'placeholder' => 'Seleccione',
                                    'class' => 'form-control',
                                    'style' => 'font-size: 1.25rem !important;'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => false,
                                ],
                            ];
                            echo $form->field($model, 'idusuariopropietario')->label('NOMBRE DEL PROPIETARIO', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->widget(Select2::classname(), $select2Options);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="m-0" style="font-size: 1.5rem !important;"></i> Porcentajes (%) de Comisiónes</h5>
                </div>
                <div class="card-body">
<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'por_venta')->label('POR VENTA', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->textInput([
            'class' => 'form-control',
            'placeholder' => '% Venta',
            'type' => 'number',
            'step' => '0.01',
            'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
        ])->hint('Escriba el porcentaje de comisión para ventas.') ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'por_asesor')->label('POR ASESORÍA', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->textInput([
            'class' => 'form-control',
            'placeholder' => '% Asesoría',
            'type' => 'number',
            'step' => '0.01',
            'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
        ])->hint('Establece el porcentaje de comisión por servicios de asesoría.') ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'por_cobranza')->label('POR COBRANZA', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->textInput([
            'class' => 'form-control',
            'placeholder' => '% Cobranza',
            'type' => 'number',
            'step' => '0.01',
            'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
        ])->hint('Define la comisión para la gestión de cobranza.') ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'por_post_venta')->label('POR POST VENTA', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->textInput([
            'class' => 'form-control',
            'placeholder' => '% Post-Venta',
            'type' => 'number',
            'step' => '0.01',
            'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
        ])->hint('Ingrese el porcentaje para servicios de post-venta.') ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'por_agente')->label('PORCENTAJE DE AGENCIA', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->textInput([
            'class' => 'form-control',
            'placeholder' => '% Agente',
            'type' => 'number',
            'step' => '0.01',
            'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
        ])->hint('Este es el porcentaje total de comisión de la agencia.') ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'por_max')->label('PORCENTAJE MÁXIMO', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->textInput([
            'class' => 'form-control',
            'placeholder' => '% Máximo',
            'type' => 'number',
            'step' => '0.01',
            'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
        ])->hint('El porcentaje de comisión más alto que se puede asignar.') ?>
    </div>
</div>
                </div>
            </div>
        </div>
    </div>



    <div class="row mb-3">
        <div class="col-md-12 d-flex justify-content-start">
           
            <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar', ['class' => 'btn btn-success btn-lg mr-3']) ?>
            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver', 
                '#',
                [
                    'class' => 'btn btn-secondary btn-lg',
                    'onclick' => 'window.history.back(); return false;', 
                    'title' => 'Volver a la página anterior', 
                ]
            ) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>