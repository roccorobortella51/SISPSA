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
            <?= Html::a('<i class="fas fa-plus"></i> CREAR NUEVO ELEMENTO DE MENU', ['create'], ['class' => 'btn btn-outline-primary btn-lg']) ?> 
        </div>
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header">
                <h1><?= $this->title = 'Gestión de Menus'; ?></h1>
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
                                'name',
                                [
                                    'attribute' => 'menuParent.name',
                                    'filter' => Html::activeTextInput($searchModel, 'parent_name', [
                                        'class' => 'form-control', 'id' => null
                                    ]),
                                    'label' => Yii::t('rbac-admin', 'Parent'),
                                ],
                                'route',
                                'order',
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
