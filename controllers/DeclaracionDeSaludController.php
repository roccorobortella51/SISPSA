<?php

namespace app\controllers;

use app\models\DeclaracionDeSalud;
use app\models\DeclaracionDeSaludSearch;
use app\models\UserDatos;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DeclaracionDeSaludController implements the CRUD actions for DeclaracionDeSalud model.
 */
class DeclaracionDeSaludController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all DeclaracionDeSalud models.
     *
     * @return string
     */
   public function actionIndex($user_id = "")
    {
        $afiliado = UserDatos::find()->where(['id' => $user_id])->one();

        $model = new DeclaracionDeSalud();

        $searchModel = new DeclaracionDeSaludSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'user_id', $user_id]);

            if ($model->load($this->request->post())) {

                $model->user_id = $user_id;
                if($model->save()){
                }else{
                     var_dump($model->errors); die();
                };
                return $this->redirect(['index', 'user_id' => $afiliado->id]);
            }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'afiliado' => $afiliado,
            'model' => $model
        ]);
    }

    /**
     * Displays a single DeclaracionDeSalud model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new DeclaracionDeSalud model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($user_id)
    {
        $model = new DeclaracionDeSalud();
        $afiliado = UserDatos::find()->where(['id' => $user_id])->one();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['index', 'user_id' => $afiliado->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'afiliado' => $afiliado
        ]);
    }

    /**
     * Updates an existing DeclaracionDeSalud model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
         $afiliado = UserDatos::find()->where(['id' => $model->user_id])->one();

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'afiliado' => $afiliado
        ]);
    }

    /**
     * Deletes an existing DeclaracionDeSalud model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the DeclaracionDeSalud model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return DeclaracionDeSalud the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DeclaracionDeSalud::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
