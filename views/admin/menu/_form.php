<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mdm\admin\models\Menu;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\Menu */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="menu-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php
    // Obtener el nombre del padre actual si existe
    $currentParentName = '';
    if (!empty($model->parent)) {
        $parentMenu = Menu::findOne($model->parent);
        if ($parentMenu) {
            $currentParentName = $parentMenu->name;
        }
    }
    ?>
    
    <div class="form-group">
        <label class="control-label">Menú Padre</label>
        <?= Html::dropDownList('Menu[parent_name]', $currentParentName, 
            \yii\helpers\ArrayHelper::map(Menu::find()->all(), 'name', 'name'),
            [
                'class' => 'form-control',
                'prompt' => 'Seleccionar menú padre (opcional)'
            ]
        ) ?>
    </div>

    <?= $form->field($model, 'route')->dropDownList(
        array_combine(Menu::getSavedRoutes(), Menu::getSavedRoutes()),
        ['prompt' => 'Seleccionar ruta']
    ) ?>

    <?= $form->field($model, 'order')->textInput() ?>

    <?php
    // Extraer datos del campo data si es un recurso
    $currentIcon = 'fas fa-circle';
    $currentVisible = true;
    
    if (!empty($model->data)) {
        $menuData = is_resource($model->data) ? stream_get_contents($model->data) : $model->data;
        if ($menuData !== false) {
            $decoded = @json_decode($menuData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $currentIcon = isset($decoded['icon']) ? $decoded['icon'] : 'fas fa-circle';
                $currentVisible = isset($decoded['visible']) ? (bool)$decoded['visible'] : true;
            }
        }
    }
    ?>

    <div class="form-group">
        <label class="control-label">Icono</label>
        <?= Html::textInput('Menu[icon]', $currentIcon, [
            'class' => 'form-control',
            'placeholder' => 'ej: fas fa-users, fas fa-cog, fas fa-home',
            'id' => 'menu-icon'
        ]) ?>
        <div class="help-block">
            Ingrese la clase CSS del icono FontAwesome (ej: fas fa-users, fas fa-cog, fas fa-home)
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">Visible</label>
        <?= Html::checkbox('Menu[visible]', $currentVisible, [
            'id' => 'menu-visible'
        ]) ?>
        <div class="help-block">
            Marque si el menú debe ser visible
        </div>
    </div>

    <?= Html::hiddenInput('Menu[data]', '', ['id' => 'menu-data-hidden']) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<<JS
document.addEventListener('DOMContentLoaded', function() {
    var iconInput = document.getElementById('menu-icon');
    var visibleInput = document.getElementById('menu-visible');
    var dataInput = document.getElementById('menu-data-hidden');
    
    function updateDataField() {
        var data = {
            icon: iconInput.value || 'fas fa-circle',
            visible: visibleInput.checked
        };
        dataInput.value = JSON.stringify(data);
    }
    
    if (iconInput && visibleInput && dataInput) {
        iconInput.addEventListener('input', updateDataField);
        visibleInput.addEventListener('change', updateDataField);
        updateDataField(); // Inicializar
    }
});
JS;
$this->registerJs($script);
?>
