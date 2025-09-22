<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\ContratosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Corporativo $corporativo */

$this->title = 'Contratos de Usuarios Asociados a: ' . Html::encode($corporativo->nombre);
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($corporativo->nombre), 'url' => ['view', 'id' => $corporativo->id]];
$this->params['breadcrumbs'][] = 'Contratos';

?>
<div class="main-container">

    <!-- Encabezado -->
    <div class="ms-panel-header">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="button-group-spacing">
            <?= Html::a(
                '<i class="fas fa-undo"></i> Volver',
                ['view', 'id' => $corporativo->id],
                ['class' => 'btn btn-secondary']
            ) ?>
        </div>
    </div>

    <!-- GridView de Contratos -->
    <div class="ms-panel">
        <div class="ms-panel-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'attribute' => 'user_id',
                        'label' => 'Usuario',
                        'value' => function ($model) {
                            $userDatos = $model->user;
                            return $userDatos ? Html::encode($userDatos->nombres . ' ' . $userDatos->apellidos) : 'N/A';
                        },
                        'filter' => false, // No filter for this column
                    ],
                    'nrocontrato',
                    [
                        'attribute' => 'fecha_ini',
                        'format' => ['date', 'php:d/m/Y'],
                    ],
                    [
                        'attribute' => 'fecha_ven',
                        'format' => ['date', 'php:d/m/Y'],
                    ],
                    [
                        'attribute' => 'monto',
                        'format' => ['decimal', 2],
                        'contentOptions' => ['class' => 'text-right'],
                    ],
                    'estatus',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'urlCreator' => function ($action, $model, $key, $index) {
                            return ['contratos/view', 'id' => $model->id];
                        },
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>