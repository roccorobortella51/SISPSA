<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Servicios por Plan';
$this->params['breadcrumbs'][] = $this->title;

if (!isset($dataProvider)) {
    $dataProvider = new \yii\data\ActiveDataProvider(['query' => \app\models\PlanServicios::find()]);
}
?>

<div class="plan-servicios-index">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">Gestión de Servicios por Plan</h3>
        </div>
        <div class="card-body">
            <?= Html::a('<i class="fas fa-plus"></i> Agregar Servicio', ['create'], ['class' => 'btn btn-success mb-3']) ?>

            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => isset($searchModel) ? $searchModel : null,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'plan_id',
                        'value' => 'plan.nombre',
                        'filter' => \yii\helpers\ArrayHelper::map(\app\models\Planes::find()->all(), 'id', 'nombre'),
                    ],
                    'servicio_nombre',
                    'plazo_espera',
                    [
                        'attribute' => 'descripcion',
                        'format' => 'ntext',
                        'contentOptions' => ['style' => 'max-width: 300px; white-space: normal;'],
                    ],
                    'orden',
                    ['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>