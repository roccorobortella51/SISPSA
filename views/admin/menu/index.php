<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel mdm\admin\models\searchs\Menu */

$this->title = Yii::t('rbac-admin', 'Menus');
$this->params['breadcrumbs'][] = $this->title;
?>


<div class=row style="margin:3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
        <div class="float-right" style="margin-bottom:10px;">
             
        </div>
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header row">
                <span class="col-md-10"><h1><?= $this->title = 'Gestión de Menus'; ?></h1></span>
                <span class="col-md-2" style="padding-left: 1rem;"><?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVO ELEMENTO DE MENU', ['create'], ['class' => 'btn btn-outline-primary btn-lg']) ?></span>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                    <?php Pjax::begin(); ?>
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'columns' => [
                                ['class' => 'yii\grid\SerialColumn'],
                                [
                                    'attribute' => 'name',
                                    'format' => 'html',
                                ],
                                [
                                    'attribute' => 'menuParent.name',
                                    'format' => 'html',
                                    'value' => function($model) {
                                        return '<b>' . htmlspecialchars($model->menuParent->name ?? '') . '</b>';
                                    },
                                    'filter' => Html::activeTextInput($searchModel, 'parent_name', [
                                        'class' => 'form-control', 'id' => null
                                    ]),
                                    'label' => Yii::t('rbac-admin', 'Parent'),
                                ],
                                [
                                    'attribute' => 'route',
                                    'format' => 'html',
                                    'value' => function($model) {
                                        if($model->route){
                                             // Aplicar la corrección por seguridad, aunque ya hay un if,
                                            // la lógica de Yii puede cambiar el flujo.
                                            return '<b>' . htmlspecialchars($model->route ?? '') . '</b>';
                                        }
                                    },
                                ],
                                [
                                    'attribute' => 'order',
                                    'format' => 'html',
                                    'value' => function($model) {
                                       return '<b>' . htmlspecialchars($model->order ?? '') . '</b>';
                                    },
                                ],
                                ['class' => 'yii\grid\ActionColumn'],
                            ],
                        ]);
                        ?>
                    <?php Pjax::end(); ?>       
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
