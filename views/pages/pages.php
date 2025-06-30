
<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;


if(Yii::$app->request->get('per_page') == NULL || Yii::$app->request->get('per_page') == ""){
            $pagina = 10; 
        }else{
            $pagina = Yii::$app->request->get('per_page');
        }

        if(isset($type)){
            $pagina = 10; 
            if($type == 10){
                $action = 'ordenesatrasadas';
            }elseif($type == 11){
                $action = 'ordenesdevolucion';
            }elseif($type == 12){
                $action = 'ordenesnovedadcliente';
            }elseif($type == 13){
                $action = 'ordenesnovedad';
            }elseif($type == 14){
                $action = 'ordenesprogramadas';
            }else{
                $action = 'index';
            }
        }else{
            $pagina = Yii::$app->request->get('type');
          
                $action = 'index';
        }

?>
<div id="search-form" align="center">
    <?php $form = ActiveForm::begin([
        'action' => [$action],
        'method' => 'get',
        //'layout' => 'center',
        'options' => ['data-pjax' => '1'],
    ]); ?><?= html::hiddenInput('type', Yii::$app->request->get('type')) ?><?= html::hiddenInput('page', Yii::$app->request->get('page')) ?>

   <div class="input-group">
    <?= Html::dropDownList('per_page', $pagina,[10 => 10, 20 => 20, 30 => 30, 50 => 50], [
        'onchange' => '$(this).submit()',
        'class' => 'form-control form-control-sm text-muted',
        'style' => 'width: 60px; text-align: center; border-color:white;'
    ]) ?>
</div>
    <?php ActiveForm::end(); ?>
    <div class="clearfix"></div>
</div>