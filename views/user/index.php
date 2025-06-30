<?php
// Importaciones necesarias
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use yii\grid\ActionColumn;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Gestión de Usuarios';
$this->params['breadcrumbs'][] = ['label' => $this->title , 'url' => ['index']];

?>
<div class=row style="margin:3px !important;">
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
            <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVO USUARIO', ['create'], ['class' => 'btn btn-outline-primary btn-lg']) ?> 
        </div>
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= $this->title = 'Gestión de Usuarios'; ?></h1>
            </div>
            <div class="ms-panel-body">
                        <div class="table-responsive">
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'columns' => [
                                    ['class' => 'yii\grid\SerialColumn'],

                                    'id',
                                    'username',
                                    'email',
                                    [
                                        'label' => 'Nombres',
                                        'value' => function($model) {
                                            return $model->userDatos ? $model->userDatos->nombres : '';
                                        }
                                    ],
                                    [
                                        'label' => 'Apellidos',
                                        'value' => function($model) {
                                            return $model->userDatos ? $model->userDatos->apellidos : '';
                                        }
                                    ],

                                    ['class' => 'yii\grid\ActionColumn'],
                                ],
                            ]); ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="clearfix"></div>
</div>