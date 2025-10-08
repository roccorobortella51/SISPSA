<?php

use app\models\Pagos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\switchinput\SwitchInput; // ← CORRECCIÓN IMPORT

/** @var yii\web\View $this */
/** @var app\models\PagosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'PAGOS';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pagos-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            //'id',
            [
                'attribute' => 'user_id',
                'value' => function ($model) {
                    return $model->userDatos ? $model->userDatos->nombres . ' ' . $model->userDatos->apellidos : 'N/A';
                },
                'label' => 'Usuario'
            ],
            [
                'label' => 'Solvente',
                'value' => function ($model) {
                    return $model->userDatos ? $model->userDatos->estatus_solvente : 'N/A';
                }
            ],
            'numero_referencia_pago:ntext',
            'fecha_pago',
            
            [
                'attribute' => 'monto_pagado',
                'value' => function ($model) {
                    return $model->monto_pagado . ' USD';
                },
                'label' => 'monto pagado Usd'
            ],
            
            [
                'attribute' => 'monto_usd',
                'value' => function ($model) {
                    return $model->monto_usd . ' Bs';
                },
                'label' => 'monto pagado Bs'
            ],
            
            // SwitchInput CORREGIDO
            [
                'attribute' => 'estatus',
                'format' => 'raw',
                'value' => function ($model) {
                    // Convertir el valor de texto a booleano
                    $isActive = ($model->estatus == 'Conciliado' || $model->estatus == '1' || $model->estatus == 'Activo');
                    
                    return SwitchInput::widget([
                        'name' => 'estatus_' . $model->id,
                        'value' => $isActive,
                        'pluginOptions' => [
                            'size' => 'large',
                            'onText' => 'Conciliado',
                            'offText' => 'Por Conciliar',
                            'onColor' => 'success',
                            'offColor' => 'danger',
                        ],
                        'pluginEvents' => [
                            'switchChange.bootstrapSwitch' => "function(event, state) {
                                var currentRow = $(event.target).closest('tr');
                                var solventeCell = currentRow.find('td').eq(2); // Tercera columna (índice 2) es Solvente

                                $.ajax({
                                    url: '" . Url::to(['/pagos/updatestatus']) . "',
                                    type: 'POST',
                                    data: {
                                        id: " . $model->id . ",
                                        status: state ? 1 : 0,
                                        _csrf: '" . Yii::$app->request->getCsrfToken() . "'
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            // Actualizar la columna de solvente en tiempo real
                                            var newSolventeStatus = state ? 'SI' : 'No';
                                            solventeCell.text(newSolventeStatus);
                                        } else {
                                            // Revertir el cambio si falla
                                            $(event.target).bootstrapSwitch('state', !state, true);
                                            alert('Error: ' + response.error);
                                        }
                                    },
                                    error: function(xhr) {
                                        $(event.target).bootstrapSwitch('state', !state, true);
                                        alert('Error del servidor: ' + xhr.responseText);
                                    }
                                });
                            }"
                        ]
                    ]);
                },
                'label' => 'Conciliacion'
            ],
            
            //'metodo_pago:ntext',
            //'estatus:ntext', // ← QUITA ESTA LÍNEA porque duplica la información
            
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Pagos $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

</div>