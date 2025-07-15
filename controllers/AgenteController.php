<?php

namespace app\controllers;

use Yii;
use app\models\Agente;
use app\models\AgenteSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;

/**
 * AgenteController implements the CRUD actions for Agente model.
 */
class AgenteController extends Controller
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
     * Lists all Agente models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AgenteSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Agente model.
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
     * Creates a new Agente model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Agente();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
        
                if ($model->save()) {
                    
                    Yii::$app->session->setFlash('success', 'La agencia ha sido creada exitosamente.');
                    return $this->redirect(['view', 'id' => $model->id]); // Redirige a la vista de la agencia creada
                } else {

                    // Obtener los errores del modelo.
                    $errors = $model->getErrors();

                    // mensaje de error flash.
                
                    $errorMessage = 'No se pudo crear la agencia. Por favor, revise los siguientes errores:<br>';
                    foreach ($errors as $attribute => $attributeErrors) {
                      
                        foreach ($attributeErrors as $error) {
                            $errorMessage .= Html::encode($error) . '<br>';
                        }
                    }
                    
                    Yii::$app->session->setFlash('error', $errorMessage);

                  
                    return $this->render('create', [
                        'model' => $model,
                        'isNewRecord' => true,
                    ]);
                }
            }
            
        } else {
          
            $model->loadDefaultValues(); 
        }

    
        return $this->render('create', [
            'model' => $model,
            'isNewRecord' => true,
        ]);
    }

    /**
     * Updates an existing Agente model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'isNewRecord' => false,
        ]);
    }

    /**
     * Deletes an existing Agente model.
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
     * Finds the Agente model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Agente the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Agente::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
