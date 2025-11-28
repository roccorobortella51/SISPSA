<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\ContratosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Corporativo $corporativo */

$this->title = 'Contratos de Afiliados Asociados a: ' . Html::encode($corporativo->nombre);
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($corporativo->nombre), 'url' => ['view', 'id' => $corporativo->id]];
$this->params['breadcrumbs'][] = 'Contratos';

// Calculate total amount from all contracts
$totalMonto = 0;
if ($dataProvider->getModels()) {
    foreach ($dataProvider->getModels() as $model) {
        $totalMonto += floatval($model->monto);
    }
}

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
            <?= Html::a(
                '<i class="fas fa-credit-card"></i> Realizar Pago Corporativo',
                ['pagos', 'id' => $corporativo->id],
                ['class' => 'btn btn-primary']
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
                    'nrocontrato',
                    [
                        'attribute' => 'user_id',
                        'label' => 'Afiliado',
                        'value' => function ($model) {
                            $userDatos = $model->user;
                            return $userDatos ? Html::encode($userDatos->nombres . ' ' . $userDatos->apellidos) : 'N/A';
                        },
                        'filter' => false,
                    ],
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

    <!-- Sección de Resumen -->
    <div class="ms-panel">
        <div class="ms-panel-header bg-primary text-white">
            <h3><i class="fas fa-chart-bar"></i> Resumen</h3>
        </div>
        <div class="ms-panel-body">
            <div class="row">
                <!-- Resumen Section -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Total de Contratos:</strong> <?= $dataProvider->getTotalCount() ?></p>
                                    <p><strong>Corporativo:</strong> <?= Html::encode($corporativo->nombre) ?></p>
                                    <p><strong>Calculado el:</strong> <?= date('d/m/Y H:i:s') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="h5"><strong>Total Pago Mensual:</strong></p>
                                    <h3 class="text-primary"><?= number_format($totalMonto, 2, ',', '.') ?> USD</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botón de pago en la sección de resumen -->
            <div class="text-center mt-4">
                <?= Html::a(
                    '<i class="fas fa-credit-card"></i> Realizar Pago Corporativo por ' . number_format($totalMonto, 2, ',', '.') . ' USD',
                    ['pagos', 'id' => $corporativo->id],
                    ['class' => 'btn btn-success btn-lg']
                ) ?>
            </div>
        </div>
    </div>
</div>