<?php

namespace app\controllers;

use Yii;
use mdm\admin\models\Menu;
use app\models\MenuExtended;
use mdm\admin\components\Helper;

/**
 * AdminMenuController extends the MenuController from mdm\admin to fix parent menu assignment
 */
class AdminMenuController extends \mdm\admin\controllers\MenuController
{
    /**
     * Creates a new Menu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MenuExtended;

        if ($model->load(Yii::$app->request->post())) {
            // Procesar los datos del formulario
            $post = Yii::$app->request->post();
            
            // Obtener el nombre del modelo para el formulario
            $modelName = (new \ReflectionClass($model))->getShortName();
            
            // Si se recibió data directamente del campo oculto, usarla
            if (isset($post['Menu']['data']) && !empty($post['Menu']['data'])) {
                $model->data = $post['Menu']['data'];
            } elseif (isset($post[$modelName]['data']) && !empty($post[$modelName]['data'])) {
                $model->data = $post[$modelName]['data'];
            } else {
                // Si no, construir data desde los campos individuales
                $icon = $post['Menu']['icon'] ?? $post[$modelName]['icon'] ?? 'fas fa-circle';
                $visible = isset($post['Menu']['visible']) ? (bool)$post['Menu']['visible'] : 
                          (isset($post[$modelName]['visible']) ? (bool)$post[$modelName]['visible'] : true);
                $model->setDataFromParams($icon, $visible);
            }
            
            // Procesar parent_name
            $parent_name = $post['Menu']['parent_name'] ?? $post[$modelName]['parent_name'] ?? null;
            if (!empty($parent_name)) {
                $parentMenu = Menu::find()
                    ->where(['name' => $parent_name])
                    ->one();
                if ($parentMenu) {
                    $model->parent = (int)$parentMenu->id;
                } else {
                    $model->addError('parent_name', 'Menu padre no encontrado.');
                    return $this->render('create', ['model' => $model]);
                }
            } else {
                $model->parent = null;
            }

            // Intentar guardar
            if ($model->save()) {
                Helper::invalidate();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Menu model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = MenuExtended::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('Menu no encontrado.');
        }

        if ($model->load(Yii::$app->request->post())) {
            // Procesar los datos del formulario
            $post = Yii::$app->request->post();
            // Obtener el nombre del modelo para el formulario
            $modelName = (new \ReflectionClass($model))->getShortName();
            
            // Si se recibió data directamente del campo oculto, usarla
            if (isset($post['Menu']['data']) && !empty($post['Menu']['data'])) {
                $model->data = $post['Menu']['data'];
            } elseif (isset($post[$modelName]['data']) && !empty($post[$modelName]['data'])) {
                $model->data = $post[$modelName]['data'];
            } else {
                // Si no, construir data desde los campos individuales
                $icon = $post['Menu']['icon'] ?? $post[$modelName]['icon'] ?? 'fas fa-circle';
                $visible = isset($post['Menu']['visible']) ? (bool)$post['Menu']['visible'] : 
                          (isset($post[$modelName]['visible']) ? (bool)$post[$modelName]['visible'] : true);
                $model->setDataFromParams($icon, $visible);
            }
            
            // Procesar parent_name
            $parent_name = $post['Menu']['parent_name'] ?? $post[$modelName]['parent_name'] ?? null;
            if (!empty($parent_name)) {
                $parentMenu = Menu::find()
                    ->where(['name' => $parent_name])
                    ->andWhere(['!=', 'id', $model->id]) // Evitar auto-referencia
                    ->one();
                if ($parentMenu) {
                    $model->parent = (int)$parentMenu->id;
                } else {
                    $model->addError('parent_name', 'Menu padre no encontrado o inválido.');
                    return $this->render('update', ['model' => $model]);
                }
            } else {
                $model->parent = null;
            }

            // Validar que no haya ciclos en la jerarquía
            if (!$this->validateMenuHierarchy($model)) {
                $model->addError('parent_name', 'La relación padre-hijo crearía un ciclo.');
                return $this->render('update', ['model' => $model]);
            }

            // Intentar guardar
            if ($model->save()) {
                Helper::invalidate();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        // Cargar el nombre del padre para el formulario
        if ($model->menuParent) {
            $model->parent_name = $model->menuParent->name;
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Validates that the menu hierarchy won't create a cycle
     * @param Menu $model
     * @return boolean
     */
    protected function validateMenuHierarchy($model)
    {
        if (!$model->parent) {
            return true;
        }

        $parent = $model->parent;
        $visited = [$model->id];

        while ($parent) {
            if (in_array($parent, $visited)) {
                return false;
            }
            $visited[] = $parent;
            $parentModel = Menu::findOne($parent);
            if (!$parentModel) {
                return false;
            }
            $parent = $parentModel->parent;
        }

        return true;
    }
}
