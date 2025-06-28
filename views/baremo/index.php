<?php

use app\models\Baremo;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\ActionColumn;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\RmClinica;
use app\models\Area;
use app\components\UserHelper;
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
    <div class="table-responsive card">
                           <table class="table table-bordered thead-primary">
                              <thead>
                                 <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Buyer</th>
                                    <th scope="col">Service</th>
                                    <th scope="col">Product ID</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <tr>
                                    <th scope="row">1</th>
                                    <td>Chihoo Hwang</td>
                                    <td>Wordpress Template</td>
                                    <td>67384917</td>
                                 </tr>
                                 <tr>
                                    <th scope="row">2</th>
                                    <td>Ajay Suryavanash</td>
                                    <td>Business Card</td>
                                    <td>789393819</td>
                                 </tr>
                                 <tr>
                                    <th scope="row">3</th>
                                    <td>John Doe</td>
                                    <td>App Customization</td>
                                    <td>137893137</td>
                                 </tr>
                                 <tr>
                                    <th scope="row">4</th>
                                    <td>Alesdro Guitto</td>
                                    <td>Dashboard Design</td>
                                    <td>235193138</td>
                                 </tr>
                                 <tr>
                                    <th scope="row">5</th>
                                    <td>Manbir Sahwny</td>
                                    <td>Theme Development</td>
                                    <td>19938917</td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>

    <div class="ms-panel-body card">
        <div class="table-responsive ">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'kartik\grid\SerialColumn'],

                    'id',
                    'created_at',
                    'nombre_servicio:ntext',
                    'descripcion:ntext',
                    'estatus:ntext',
                    [
                        'attribute' => 'preciossss',
                        'format' => ['currency', ''],
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
                            return $model->rmclinica->nombre ?? 'N/A';
                        },
                        'filterType' => GridView::FILTER_SELECT2,
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
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'data' => ArrayHelper::map(Area::find()->all(), 'id', 'nombre'),
                            'options' => ['placeholder' => 'Seleccionar Área...'],
                            'pluginOptions' => ['allowClear' => true],
                        ],
                    ],
                    [
                        'class' => ActionColumn::className(),
                        'urlCreator' => function ($action, Baremo $model, $key, $index, $column) {
                            return Url::toRoute([$action, 'id' => $model->id]);
                         }
                    ],
                ],
                'toolbar' => [
                    '{export}',
                    '{toggleData}',
                ],
                // El panel se puede mantener o no, dependiendo si tu diseño lo necesita dentro de ms-panel-body
                /*'panel' => [
                    'heading' => '<h3 class="panel-title"><i class="fas fa-list"></i> Baremos</h3>',
                    'type' => GridView::TYPE_PRIMARY,
                    'after' => Html::a('<i class="fas fa-redo"></i> Reset Grid', ['index'], ['class' => 'btn btn-info']),
                ],*/
                /*'pjax' => true,
                'bordered' => true,
                'striped' => false, // Ya tienes 'table-striped' en tableOptions
                'condensed' => true,
                'responsive' => true, // La clase table-responsive ya maneja esto, pero puedes dejarlo
                'hover' => true,
                'showPageSummary' => false,*/
                'tableOptions' => [
                    'id' => 'data-table-4', // ¡Importante! Asigna el ID aquí
                    'class' => 'table w-100 thead-primary', // Clases que deseas para la tabla
                ],
                // Definimos el layout para insertar solo la tabla dentro de tu HTML
                //'layout' => "{items}\n{pager}", // Solo muestra los items (la tabla) y la paginación
                 'layout' => UserHelper::getLayoutIndex().$this->render('/pages/pages').UserHelper::getLayoutIndex2(),
            ]); ?>
        </div>
    </div>
</div>