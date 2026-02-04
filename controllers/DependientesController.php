<?php
// controllers/DependientesController.php

namespace app\controllers;

use Yii;
use app\models\Dependientes;
use app\models\UserDatos;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\helpers\Json;

/**
 * DependientesController implements the CRUD actions for Dependientes model.
 */
class DependientesController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['POST'],
                    'update' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionCheckRoles()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $auth = Yii::$app->authManager;
        $userId = Yii::$app->user->id;

        $roles = [];
        if ($userId) {
            $roles = $auth->getRolesByUser($userId);
        }

        return [
            'user_id' => $userId,
            'roles' => array_keys($roles),
            'all_roles' => array_keys($auth->getRoles()),
        ];
    }

    /**
     * Creates a new Dependientes model.
     * @return mixed
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Dependientes();

        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            return [
                'success' => true,
                'message' => 'Dependiente agregado exitosamente',
                'data' => $model->attributes
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al agregar dependiente',
            'errors' => $model->getErrors()
        ];
    }

    /**
     * Updates an existing Dependientes model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            return [
                'success' => true,
                'message' => 'Dependiente actualizado exitosamente',
                'data' => $model->attributes
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al actualizar dependiente',
            'errors' => $model->getErrors()
        ];
    }

    /**
     * Deletes an existing Dependientes model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Dependiente eliminado exitosamente'
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al eliminar dependiente'
        ];
    }

    /**
     * Finds the Dependientes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Dependientes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Dependientes::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('El dependiente solicitado no existe.');
    }
}
