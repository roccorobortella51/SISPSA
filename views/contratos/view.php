<?php

use yii\helpers\Html;
use kartik\detail\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Contratos */


$css = <<<CSS
@media (min-width: 992px) {
    .kv-detail-content {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    .kv-detail-content {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
}

@media (max-width: 767px) {
    .kv-detail-content {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }
}
CSS;

$this->registerCss($css);

$attributes = [
    [
        'group'=>true,
        'label'=>'Información General',
        'rowOptions'=>['class'=>'table-info']
    ],
    [
        'columns' => [
            [
                'attribute'=>'id',
                'label'=>'ID Contrato',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'plan_id',
                'label'=>'Plan',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'ente_id',
                'label'=>'Ente',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
        ],
    ],
    [
        'columns' => [
            [
                'attribute'=>'clinica_id',
                'label'=>'Clínica',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'fecha_ini',
                'label'=>'Fecha Inicio',
                'format'=>['date', 'php:d-m-Y'],
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'fecha_ven',
                'label'=>'Fecha Vencimiento',
                'format'=>['date', 'php:d-m-Y'],
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
        ],
    ],
    [
        'group'=>true,
        'label'=>'Detalles de Pago',
        'rowOptions'=>['class'=>'table-info']
    ],
    [
        'columns' => [
            [
                'attribute'=>'monto',
                'label'=>'Monto',
                'format'=>['decimal', 2],
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'frecuencia_pago',
                'label'=>'Frecuencia de Pago',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'moneda',
                'label'=>'Moneda',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
        ],
    ],
    [
        'group'=>true,
        'label'=>'Estado y Otros',
        'rowOptions'=>['class'=>'table-info']
    ],
    [
        'columns' => [
            [
                'attribute'=>'estatus',
                'label'=>'Estatus',
                'format'=>'ntext',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'nrocontrato',
                'label'=>'Número de Contrato',
                'format'=>'ntext',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'sucursal',
                'label'=>'Sucursal',
                'format'=>'ntext',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
        ],
    ],
    [
        'columns' => [
            [
                'attribute'=>'anulado_por',
                'label'=>'Anulado Por',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'anulado_fecha',
                'label'=>'Fecha Anulación',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'anulado_motivo',
                'label'=>'Motivo Anulación',
                'format'=>'ntext',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
        ],
    ],
    [
        'columns' => [
            [
                'attribute'=>'user_id',
                'label'=>'Usuario',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
            [
                'attribute'=>'PDF',
                'label'=>'PDF',
                'format'=>'ntext',
                'displayOnly'=>true,
                'valueColOptions'=>['class'=>'col-md-4']
            ],
        ],
    ],
];

?>



<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <!-- agregar boton para eliminar este contrato -->
                     
                    <?= DetailView::widget([
                        'model' => $model,
                        'condensed'=>true,
                        'hover'=>true,
                        'mode'=>DetailView::MODE_VIEW,
                        'buttons1' => '',
                        'buttons2' => '',
                        'panel'=>[
                            'heading'=>'Contrato # ' . $model->id,
                            'type'=>DetailView::TYPE_INFO,
                        ],
                        'attributes' => $attributes,
                    ]) ?>
                </div>
                <!--.col-md-12-->
            </div>
            <!--.row-->
        </div>
        <!--.card-body-->
    </div>
    <!--.card-->
</div>
