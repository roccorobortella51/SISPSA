<?php

use yii\helpers\Html;
use yii\grid\GridView;
use mdm\admin\models\Menu;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Menús';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Crear Menú', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'parent',
                'value' => function($model) {
                    $parent = Menu::findOne($model->parent);
                    return $parent ? $parent->name : '-';
                }
            ],
            'route',
            'order',
            [
                'attribute' => 'data',
                'format' => 'raw',
                'value' => function($model) {
                    try {
                        if (empty($model->data)) {
                            return '-';
                        }
                        
                        // Si es un recurso, obtener el contenido
                        if (is_resource($model->data)) {
                            $menuData = @stream_get_contents($model->data);
                            if ($menuData === false) {
                                return '<span class="text-warning">Error al leer datos</span>';
                            }
                        } else {
                            $menuData = $model->data;
                        }
                        
                        // Intentar decodificar JSON
                        $decoded = @json_decode($menuData, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $icon = $decoded['icon'] ?? 'fas fa-circle';
                            $visible = isset($decoded['visible']) ? ($decoded['visible'] ? 'Sí' : 'No') : 'Sí';
                            return "
                                <div style='display: flex; align-items: center; gap: 10px;'>
                                    <i class='{$icon}' style='font-size: 1.2em;'></i>
                                    <div>
                                        <div>{$icon}</div>
                                        <small class='text-muted'>Visible: {$visible}</small>
                                    </div>
                                </div>";
                        }
                        
                        // Si no es JSON válido, mostrar el contenido raw
                        return '<span class="text-warning">Formato inválido: ' . htmlspecialchars($menuData) . '</span>';
                    } catch (\Exception $e) {
                        return '<span class="text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                    }
                }
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
