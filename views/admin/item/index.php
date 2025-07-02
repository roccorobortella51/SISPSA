<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use mdm\admin\components\RouteRule;
use mdm\admin\components\Configs;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel mdm\admin\models\searchs\AuthItem */
/* @var $context mdm\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = Yii::t('rbac-admin', $labels['Items']);
$this->params['breadcrumbs'][] = $this->title;

$rules = array_keys(Configs::authManager()->getRules());
$rules = array_combine($rules, $rules);
unset($rules[RouteRule::RULE_NAME]);
?>

<div class=row style="margin:3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header row">
                <span class="col-md-10"><h1><?= $this->title = 'Gestión de '; ?><?= Yii::t('rbac-admin', $labels['Items']) ?></h1></span>
                <span class="col-md-2" style="padding-left: 11rem;"><?= Html::a('<i class="fas fa-plus"></i> ' . Yii::t('rbac-admin', 'Create ' . $labels['Item']), ['create'], ['class' => 'btn btn-outline-primary btn-lg']) ?></span>
            </div>
            <div class="ms-panel-body">
                <div class="table-responsive">
                     <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'columns' => [
                                ['class' => 'yii\grid\SerialColumn'],
                                [
                                    'attribute' => 'name',
                                    'label' => Yii::t('rbac-admin', 'Name'),
                                ],
                                [
                                    'attribute' => 'ruleName',
                                    'label' => Yii::t('rbac-admin', 'Rule Name'),
                                    'filter' => $rules
                                ],
                                [
                                    'attribute' => 'description',
                                    'label' => Yii::t('rbac-admin', 'Description'),
                                ],
                                ['class' => 'yii\grid\ActionColumn',],
                            ],
                        ])
                    ?>      
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
