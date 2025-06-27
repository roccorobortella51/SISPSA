<?php

use app\models\RmClinica;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
//use kartik\grid\GridView; 
/** @var yii\web\View $this */
/** @var app\models\RmClinicaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Rm Clinicas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rm-clinica-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Rm Clinica', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="ms-panel">
    <div class="ms-panel-header ms-panel-custome">
        <h6>Clinicas</h6>
        <?= Html::a('Create Baremo', ['create'], ['class' => 'btn btn-success']) ?>
    </div>
    <div class="ms-panel-body">
        
    <div class="rm-clinica-index">

    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'kartik\grid\SerialColumn'], // Usa el SerialColumn de Kartik

            'id',
            'created_at',
            'rif:ntext',
            'nombre:ntext',
            'estado:ntext',
        ],
        // --- PROPIEDADES PARA APLICAR LAS CLASES CSS ---
        'tableOptions' => [
            'class' => 'table table-striped thead-primary w-100'
        ],
       
    ]);
    ?>


</div>
        </div>
    </div>
</div>





    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    


</div>
