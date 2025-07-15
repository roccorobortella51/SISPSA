<?php

namespace app\controllers;

use Yii; 
use app\models\AgenteFuerza;
use app\models\AgenteFuerzaSearch;
use app\models\Agente; 
use app\models\User; 
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AgenteFuerzaController implements the CRUD actions for AgenteFuerza model.
 */
class AgenteFuerzaController extends Controller
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
     * Lists all AgenteFuerza models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AgenteFuerzaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AgenteFuerza model.
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
     * Creates a new AgenteFuerza model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($agente_id)
    {
        $model = new AgenteFuerza();
        $agente = Agente::findOne($agente_id);

        if ($agente === null) {
            throw new NotFoundHttpException('El agente especificado no existe.');
        }

        $model->agente_id = $agente->id;

        if ($model->load($this->request->post())) {
            if ($model->save()) {
                // Éxito: Guardado y validación correctos
                Yii::$app->session->setFlash('success', 'La asignación de agente/fuerza ha sido creada exitosamente.');
                return $this->redirect(['index-by-agente', 'agente_id' => $agente->id]);
            } else {
                // FALLO: El modelo no se guardó (puede ser por validación, como la de unicidad)

                // 1. Obtener los errores del modelo
                $errors = $model->getErrors();

                // 2. Construir un mensaje de error flash
                // Puedes mostrar un mensaje genérico, o los errores específicos
                $errorMessage = '';
                foreach ($errors as $attribute => $attributeErrors) {
                    $errorMessage .= implode('<br>', $attributeErrors) . '<br>';
                }

                Yii::$app->session->setFlash('error', $errorMessage);

                // 3. Renderizar la vista de nuevo para que el usuario vea el formulario y el mensaje flash
                // No uses exit; aquí.
                return $this->render('create', [
                    'model' => $model,
                    'agente' => $agente,
                ]);
            }
        } else {
            // Primera carga del formulario (GET request)
            return $this->render('create', [
                'model' => $model,
                'agente' => $agente,
            ]);
        }
    }

    /**
     * Updates an existing AgenteFuerza model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $agente = Agente::findOne($model->agente_id);


        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'agente' => $agente,
            'agente_id' => $model->agente_id
        ]);
    }

    /**
     * Deletes an existing AgenteFuerza model.
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
     * Lista todos los modelos AgenteFuerza asociados a un Agente específico.
     * @param int $id_agente El ID del Agente.
     * @return string
     * @throws NotFoundHttpException si el ID del Agente es inválido.
     */
    
     public function actionIndexByAgente($agente_id) // <-- Usamos $agente_id aquí
    {
        $agente = Agente::findOne($agente_id); // <-- Usamos $agente_id
        if ($agente === null) {
            throw new NotFoundHttpException('El agente especificado no existe.');
        }

        $searchModel = new AgenteFuerzaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'agente_id', $agente_id]);

        
        // ¡CORRECCIÓN CLAVE AQUÍ! Usamos $agente_id para el filtro
        //$dataProvider->query->andWhere(['agente_id' => $agente_id]); 

        $this->view->title = 'Fuerza de Venta para Agente: ' . $agente->nom;
        
        return $this->render('index_by_agente', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // ¡CORRECCIÓN CLAVE AQUÍ! Pasamos $agente_id a la vista
            'id_agente' => $agente_id, // La vista espera 'id_agente', así que le pasamos $agente_id con ese nombre
            'agente' => $agente,
        ]);
    }


    /**
     * Finds the AgenteFuerza model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return AgenteFuerza the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AgenteFuerza::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
