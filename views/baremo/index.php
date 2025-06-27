<?php

use app\models\Baremo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\BaremoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Baremos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="baremo-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Baremo', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

     <div class="ms-content-wrapper">
            <div class="row">
               <div class="col-md-12">
                  <nav aria-label="breadcrumb">
                     <ol class="breadcrumb pl-0">
                        <li class="breadcrumb-item"><a href="#"><i class="material-icons">home</i> Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Tables</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Data Tables</li>
                     </ol>
                  </nav>
                  <div class="ms-panel">
                     <div class="ms-panel-header">
                        <h6>Hoverable Rows Datatable</h6>
                     </div>
                     <div class="ms-panel-body">
                        <p class="ms-directions">Check <code>/assets/js/data-tables.js</code> for reference</p>
                        <div class="table-responsive">
                           <table id="data-table-1" class="table table-hover w-100"></table>
                        </div>
                     </div>
                  </div>
                  <div class="ms-panel">
                     <div class="ms-panel-header">
                        <h6>Datatable With Header and Stripes</h6>
                     </div>
                     <div class="ms-panel-body">
                        <div class="table-responsive">
                           <table id="data-table-2" class="table table-striped thead-primary w-100"></table>
                        </div>
                     </div>
                  </div>
                  <div class="ms-panel">
                     <div class="ms-panel-header">
                        <h6>Datatable With Scroll</h6>
                     </div>
                     <div class="ms-panel-body">
                        <div class="table-responsive">
                           <table id="data-table-3" class="table w-100"></table>
                        </div>
                     </div>
                  </div>
                  <div class="ms-panel">
                     <div class="ms-panel-header">
                        <h6>Responsive Datatable</h6>
                     </div>
                     <div class="ms-panel-body">
                        <div class="table-responsive">
                           <table id="data-table-4" class="table w-100 thead-primary"></table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at',
            'nombre_servicio:ntext',
            'descripcion:ntext',
            'estatus:ntext',
            //'deleted_at',
            //'updated_at',
            //'precio',
            //'clinica_id',
            //'costo',
            //'area_id',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Baremo $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
