<?php

namespace app\controllers;

use app\models\CheckListClinicas;
use app\models\RmClinica;
use app\models\CheckListClinicasSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * CheckListClinicasController implements the CRUD actions for CheckListClinicas model.
 */
class CheckListClinicasController extends Controller
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
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all CheckListClinicas models.
     *
     * @return string
     */
    public function actionIndex($clinica_id)
    {
        $clinica = RmClinica::find()->where(['id' => $clinica_id])->andWhere(['is','deleted_at', null])->one();
        $searchModel = new CheckListClinicasSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'clinica_id', $clinica_id]);

        $chartData = CheckListClinicas::getLastChecklistsByClinic();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'clinica' => $clinica,
            'chartData' => $chartData

        ]);
    }

    /**
     * Displays a single CheckListClinicas model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $clinica = RmClinica::find()->where(['id' => $model->clinica_id])->andWhere(['is','deleted_at', null])->one();
        return $this->render('view', [
            'model' =>$model,
            'clinica' => $clinica
        ]);
    }

    /**
     * Creates a new CheckListClinicas model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($clinica_id)
    {
        $model = new CheckListClinicas();
        $clinica = RmClinica::find()->where(['id' => $clinica_id])->andWhere(['is','deleted_at', null])->one();

            // Cargar los datos del formulario
            if ($model->load($this->request->post())) {
                

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Registro creado exitosamente.');
                    return $this->redirect(['index', 'clinica_id' => $clinica_id]);
                } else {
                    echo "MODEL NOT SAVED";
                      print_r($model->getAttributes());
                      print_r($model->getErrors());
                      exit;
                    Yii::$app->session->setFlash('error', 'No se pudo guardar el registro.');
                }
            }

        return $this->render('create', [
            'model' => $model,
            'clinica' => $clinica
        ]);
    }

    /**
     * Updates an existing CheckListClinicas model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $clinica = RmClinica::find()->where(['id' => $model->clinica_id])->one();

        //if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                
                $historico = new CheckListClinicas();

                $historico->setAttributes($model->attributes, [
                    'id',         
                    'created_at',  
                    'updated_at'
                ]);

                $historico->created_at = date('Y-m-d H:i:s');
                $historico->updated_at = date('Y-m-d H:i:s');

                if ($historico->save()) {
                    Yii::$app->session->setFlash('success', 'Registro actualizado exitosamente.');
                    return $this->redirect(['index', 'clinica_id' => $model->clinica_id]);
                } else {
                    Yii::$app->session->setFlash('error', 'No se pudo actualizar el registro.');
                }
            }
        //}

        return $this->render('update', [
            'model' => $model,
            'clinica' => $clinica
        ]);
    }

    /**
     * Deletes an existing CheckListClinicas model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $clinica_id = $model->clinica_id;
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Registro eliminado exitosamente.');
        return $this->redirect(['index', 'clinica_id' => $clinica_id]);
    }

    /**
     * Finds the CheckListClinicas model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return CheckListClinicas the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CheckListClinicas::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionChart()
    {
        $chartData = CheckListClinicas::getLastChecklistsByClinic();
        return $this->render('chart', [
            'chartData' => $chartData,
        ]);
    }
}