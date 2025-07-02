<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\AuthItem */
/* @var $context mdm\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = Yii::t('rbac-admin', 'Update ' . $labels['Item']) . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('rbac-admin', $labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = Yii::t('rbac-admin', 'Update');
?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header">
            <h1><?= Html::encode($this->title); ?></h1>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>        
        </div>
    </div>
</div>
