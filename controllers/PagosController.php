<?php

namespace app\controllers;

use Yii;
use app\models\Pagos;
use app\models\TasaCambio;
use app\models\UserDatos;
use app\models\PagosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

/**
 * PagosController implements the CRUD actions for Pagos model.
 */
class PagosController extends Controller
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
     * Lists all Pagos models.
     *
     * @return string
     */
    public function actionIndex($user_id = "")
    {
        $searchModel = new PagosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if ($user_id !== "") {
            $afiliado = UserDatos::find()->where(['id' => $user_id])->one();
            $dataProvider->query->andFilterWhere(['=', 'user_id', $afiliado->id]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'afiliado' => $afiliado,
            'user_id' => $user_id,
        ]);
    }

    /**
     * Displays a single Pagos model.
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
     * Creates a new Pagos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($user_id = "")
    {
        $model = new Pagos();
        $tasa = TasaCambio::find()->orderBy(['created_at' => SORT_DESC])->one();
        $model->tasa = round($tasa->tasa_cambio,5);
        $model->user_id = $user_id;
        $model->estatus = 'pendiente';


        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->imagen_prueba_file = UploadedFile::getInstance($model, 'imagen_prueba_file');
                if ($model->validate()) {
                    if ($model->imagen_prueba_file) {
                        $fileName = 'uploads/' . uniqid() . '.' . $model->imagen_prueba_file->extension;
                        $fullPath = Yii::getAlias('@webroot') . '/' . $fileName;
                        if ($model->imagen_prueba_file->saveAs($fullPath)) {
                            $model->imagen_prueba = $fileName;
                        }
                    }
                    if ($model->save(false)) {
                        return $this->redirect(['contratos/index', 'user_id' => $user_id]);
                    }
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Pagos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $tasa = TasaCambio::find()->orderBy(['created_at' => SORT_DESC])->one();
        $model->tasa = round($tasa->tasa_cambio,5);

        // Determine if the payment is editable based on status
        $isEditable = ($model->estatus === 'pendiente');

        if ($this->request->isPost && $isEditable) {
            if ($model->load($this->request->post())) {
                $model->imagen_prueba_file = UploadedFile::getInstance($model, 'imagen_prueba_file');
                if ($model->validate()) {
                    if ($model->imagen_prueba_file) {
                        $fileName = 'uploads/' . uniqid() . '.' . $model->imagen_prueba_file->extension;
                        $fullPath = Yii::getAlias('@webroot') . '/' . $fileName;
                        if ($model->imagen_prueba_file->saveAs($fullPath)) {
                            $model->imagen_prueba = $fileName;
                        }
                    }
                    if ($model->save(false)) {
                        return $this->redirect(['contratos/index', 'user_id' => $model->user_id]);
                    }
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'isEditable' => $isEditable,
        ]);
    }

    /**
     * Deletes an existing Pagos model.
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
     * Finds the Pagos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Pagos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pagos::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
