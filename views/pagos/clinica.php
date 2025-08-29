<?php

use app\models\Pagos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\DetailView;
use kartik\grid\ExpandRowColumn;


/** @var yii\web\View $this */
/** @var app\models\PagosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'PAGOS DE LA CLINICA';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pagos-index">
    <div class="header-section"> 
        <h1><?= Html::encode($this->title) . ' ' . $clinica->nombre ?></h1>
        <div class="header-buttons-group">
            <!-- Botón "Volver a Clínica" condicional -->
            <?php if ($clinica && $clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver a Clínica', 
                    ['/rm-clinica/indicator', 'id' => $clinica->id], 
                    [
                        'class' => 'btn-base btn-gray', 
                        'title' => 'Volver a los detalles de la clínica',
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="ms-panel ms-panel-fh border-indigo"> <!-- Usando ms-panel y borde indigo -->
        <div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-file-invoice-dollar mr-3 text-indigo-600"></i> Listado de Pagos por Afiliado
            </h3>
        </div>
        <div class="ms-panel-body">
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'numero_referencia_pago:ntext',
                        [
                            'attribute' => 'user_id',
                            'header' => 'Afiliado',
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                            'value' => function($model) {
                                return $model->userDatos->nombres . ' ' . $model->userDatos->apellidos;
                            },
                        ],
                        [
                            'attribute' => 'created_at',
                            'header' => 'Fecha de Pago',
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                            'value' => function($model) {
                                return $model->created_at;
                            },
                        ],
                        [
                            'attribute' => 'monto_pagado',
                            'header' => 'Monto en USD',
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                            'value' => function($model) {
                                return $model->monto_pagado . ' USD';
                            },
                        ],
                        [
                            'attribute' => 'monto_usd',
                            'format' => 'Html',
                            'header' => 'Monto en Bs',
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                            'value' => function($model) {
                                return $model->monto_usd . ' Bs';
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'ACCIONES',
                            'template' => '<div class="d-flex justify-content-center gap-0">{view}</div>',
                            'options' => ['style' => 'width:55px; min-width:55px;'],
                            'headerOptions' => ['style' => 'color: white!important;'],
                            'contentOptions' => ['style' => 'text-align: center; padding: 10 !important;'],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>


</div>
