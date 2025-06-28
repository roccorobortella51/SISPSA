<?php

use app\models\Baremo;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\ActionColumn; // Usa ActionColumn de Kartik-V si quieres sus mejoras
use kartik\grid\GridView; // ¡Cambiamos a GridView de Kartik-V!
use yii\helpers\ArrayHelper;
use app\models\RmClinica;
use app\models\Area;

/** @var yii\web\View $this */
/** @var app\models\BaremoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Baremos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="baremo-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(
            '<i class="fas fa-plus"></i> Crear Baremo',
            ['create'],
            ['class' => 'btn btn-success']
        ) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'], // Puedes usar la SerialColumn de Kartik también

            'id',
            'created_at',
            'nombre_servicio:ntext',
            'descripcion:ntext',
            'estatus:ntext',
            [
                'attribute' => 'precio',
                'format' => ['currency', ''], // Formato de moneda. Puedes especificar la moneda si quieres.
                'contentOptions' => ['style' => 'text-align: right;'],
            ],
            [
                'attribute' => 'costo',
                'format' => ['currency', ''],
                'contentOptions' => ['style' => 'text-align: right;'],
            ],
            [
                'attribute' => 'clinica_id',
                'label' => 'Clínica',
                'value' => function ($model) {
                    return $model->clinica->nombre ?? 'N/A';
                },
                'filterType' => GridView::FILTER_SELECT2, // Usa Select2 para el filtro
                'filterWidgetOptions' => [
                    'data' => ArrayHelper::map(RmClinica::find()->all(), 'id', 'nombre'),
                    'options' => ['placeholder' => 'Seleccionar Clínica...'],
                    'pluginOptions' => ['allowClear' => true],
                ],
            ],
            [
                'attribute' => 'area_id',
                'label' => 'Área',
                'value' => function ($model) {
                    return $model->area->nombre ?? 'N/A';
                },
                'filterType' => GridView::FILTER_SELECT2, // Usa Select2 para el filtro
                'filterWidgetOptions' => [
                    'data' => ArrayHelper::map(Area::find()->all(), 'id', 'nombre'),
                    'options' => ['placeholder' => 'Seleccionar Área...'],
                    'pluginOptions' => ['allowClear' => true],
                ],
            ],
            [
                'class' => ActionColumn::className(),
                // Puedes añadir más opciones de Kartik's ActionColumn aquí si lo deseas
                'urlCreator' => function ($action, Baremo $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
        // Opciones adicionales para GridView de Kartik-V
        'toolbar' => [
            '{export}', // Botón de exportación
            '{toggleData}', // Botón para alternar la vista de datos
        ],
        'panel' => [
            'heading' => '<h3 class="panel-title"><i class="fas fa-list"></i> Baremos</h3>',
            'type' => GridView::TYPE_PRIMARY,
            'after' => Html::a('<i class="fas fa-redo"></i> Reset Grid', ['index'], ['class' => 'btn btn-info']),
        ],
        'pjax' => true, // Habilita PJAX para actualizaciones asíncronas
        'bordered' => true,
        'striped' => false,
        'condensed' => true,
        'responsive' => true,
        'hover' => true,
        'showPageSummary' => false,
    ]); ?>

</div>