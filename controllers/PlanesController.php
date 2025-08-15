<?php

namespace app\controllers;

use app\models\Planes;
use app\models\PlanesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\RmClinica;
use Yii;
use app\models\Baremo;
use app\models\PlanesItemsCobertura;
use yii\helpers\ArrayHelper;
/**
 * PlanesController implements the CRUD actions for Planes model.
 */
class PlanesController extends Controller
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
     * Lists all Planes models.
     *
     * @return string
     */
    public function actionIndex($clinica_id = "")
    {
        $clinica = RmClinica::find()->where(['id' => $clinica_id])->andWhere(['is','deleted_at', null])->one();
        $searchModel = new PlanesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'clinica_id', $clinica_id]);
        $model = new Planes();

            if ($model->load($this->request->post())) {

                $model->clinica_id = $clinica_id;
                $model->estatus = "Activo";
                if($model->save()){
                }else{
                     var_dump($model->errors); die();
                };
                return $this->redirect(['index', 'clinica_id' => $clinica->id]);
            }
       

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'clinica' => $clinica,
            'model' => $model
        ]);
    }

    /**
     * Displays a single Planes model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $itemsCobertura = $model->planesItemsCoberturas;
        
        // Obtener baremos faltantes
        $baremosFaltantes = Baremo::find()
            ->where(['clinica_id' => $model->clinica_id])
            ->andWhere(['not in', 'id', ArrayHelper::getColumn($itemsCobertura, 'baremo_id')])
            ->all();
        
        return $this->render('view', [
            'model' => $model,
            'itemsCobertura' => $itemsCobertura,
            'baremosFaltantes' => $baremosFaltantes,
        ]);
    }

    /**
     * Creates a new Planes model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    /*public function actionCreate()
    {
        $model = new Planes();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }*/

    // En tu controlador PlanesController.php

    public function actionCreate()
    {
        $model = new Planes();
        $itemsModels = [];

        // Obtener baremos de la clínica si viene el parámetro
        if (Yii::$app->request->get('clinica_id')) {
            $baremos = Baremo::find()->where(['clinica_id' => Yii::$app->request->get('clinica_id')])->andWhere(['estatus' => 'Activo'])->all();
            $clinica = RmClinica::find()->where(['id' => Yii::$app->request->get('clinica_id')])->one();
            
            // Crear modelos para cada baremo
            foreach ($baremos as $baremo) {
                $item = new PlanesItemsCobertura();
                $item->baremo_id = $baremo->id;
                $item->nombre_servicio = $baremo->nombre_servicio;
                $itemsModels[] = $item;
            }
        }

        if ($model->load(Yii::$app->request->post())) {

                $model->clinica_id = $clinica->id;
            
                // Guardar el plan principal
                if ($model->save()) {
                    // Procesar items de cobertura
                    $itemsData = Yii::$app->request->post('PlanesItemsCobertura', []);
                    
                    foreach ($itemsData as $itemData) {
                        $item = new PlanesItemsCobertura();
                        $item->load($itemData, '');
                        $item->plan_id = $model->id;
                        
                        if (!$item->save()) {
                            throw new \Exception('Error al guardar items de cobertura');
                        }else{

                            var_dump($item->getErrors); die();
                        }
                    }
                    
                    return $this->redirect(['view', 'id' => $model->id]);
                }else{

                    echo "MODEL NOT SAVED";
                      print_r($model->getAttributes());
                      print_r($model->getErrors());
                      exit;

                }
            
        }

        return $this->render('create', [
            'model' => $model,
            'itemsModels' => $itemsModels,
            'clinica' => $clinica
        ]);
    }

    /**
     * Updates an existing Planes model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    // En PlanesController.php
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $itemsModels = $model->planesItemsCoberturas;
        $clinica = RmClinica::find()->where(['id' => $model->clinica_id])->one();

        
        // Obtener baremos faltantes
        $baremosFaltantes = Baremo::find()
            ->where(['clinica_id' => $model->clinica_id])
            ->andWhere(['not in', 'id', ArrayHelper::getColumn($itemsModels, 'baremo_id')])
            ->all();
        
        // Crear modelos para baremos faltantes
        foreach ($baremosFaltantes as $baremo) {
            $item = new PlanesItemsCobertura([
                'baremo_id' => $baremo->id,
                'nombre_servicio' => $baremo->nombre_servicio,
                'porcentaje_cobertura' => 80, // Valor por defecto
            ]);
            $itemsModels[] = $item;
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Eliminar coberturas existentes primero
                    PlanesItemsCobertura::deleteAll(['plan_id' => $model->id]);
                    
                    // Guardar las coberturas
                    $itemsData = Yii::$app->request->post('PlanesItemsCobertura', []);
                    
                    foreach ($itemsData as $itemData) {
                        if (!empty($itemData['porcentaje_cobertura'])) { // Solo guardar si tiene porcentaje
                            $item = new PlanesItemsCobertura();
                            $item->load($itemData, '');
                            $item->plan_id = $model->id;
                            
                            if (!$item->save()) {
                                throw new \Exception('Error al guardar items de cobertura: ' . print_r($item->errors, true));
                            }
                        }
                    }
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Plan actualizado correctamente');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error al actualizar: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'itemsModels' => $itemsModels,
            'clinica' => $clinica
        ]);
    }

    /**
     * Deletes an existing Planes model.
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
     * Finds the Planes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Planes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Planes::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdatestatus(){
        if (Yii::$app->request->isAjax and Yii::$app->request->post()) {
            $variables = Yii::$app->request->post();

            $model = Planes::find()->where(['id' => $variables['id']])->one();

            if($model->estatus == "Activo"){
                $model->estatus = "Inactivo";
                $model->save(false);
            }else{
                $model->estatus = "Activo";
                $model->save(false);
            }
        }
    }

    // En PlanesController.php

/**
 * Agrega una nueva cobertura (servicio de baremo) a un plan existente
 * 
 * @param int $plan_id ID del plan al que se agregará la cobertura
 * @param int $baremo_id ID del baremo (servicio) a agregar
 * @return \yii\web\Response
 * @throws NotFoundHttpException Si el plan o baremo no existen
 */
   public function actionAddCobertura($plan_id, $baremo_id)
{
    // Buscar el plan y verificar existencia
    $plan = $this->findModel($plan_id);
    $baremo = Baremo::findOne($baremo_id);
    
    if (!$baremo) {
        Yii::$app->session->setFlash('error', 'El servicio solicitado no existe en el sistema.');
        return $this->redirect(['view', 'id' => $plan_id]);
    }
    
    // Verificar que el baremo pertenezca a la misma clínica que el plan
    if ($baremo->clinica_id != $plan->clinica_id) {
        Yii::$app->session->setFlash('warning', 'El servicio no pertenece a la clínica asociada a este plan.');
        return $this->redirect(['view', 'id' => $plan_id]);
    }
    
    // Verificar que no exista ya esta cobertura en el plan
    $existente = PlanesItemsCobertura::find()
        ->where(['plan_id' => $plan_id, 'baremo_id' => $baremo_id])
        ->one();
        
    if ($existente) {
        Yii::$app->session->setFlash('info', 'Este servicio ya está incluido en el plan.');
        return $this->redirect(['view', 'id' => $plan_id]);
    }
    
    // Crear el nuevo item de cobertura
    $model = new PlanesItemsCobertura([
        'plan_id' => $plan_id,
        'baremo_id' => $baremo_id,
        'nombre_servicio' => $baremo->nombre_servicio, // Corregido: era $baremo-snombre_servicio
        'porcentaje_cobertura' => 80, // Valor por defecto (corregido typo "porcentaje")
        'cantidad_limite' => 1, // Valor por defecto (corregido typo "cantidad_linite")
    ]);
    
    // Redirigir directamente o mostrar formulario para completar datos
    if (Yii::$app->request->isPost) {
        // Si viene por POST (formulario de creación)
        if ($model->load(Yii::$app->request->post())) { // Corregido: Yli::$app a Yii::$app
            if ($model->save()) {
                Yii::$app->session->setFlash('success', // Corregido: Yli::$app a Yii::$app
                    "El servicio <strong>{$baremo->nombre_servicio}</strong> se agregó al plan correctamente.");
                return $this->redirect(['view', 'id' => $plan_id]);
            } else {
                Yii::$app->session->setFlash('error', // Corregido: Yli::$app a Yii::$app
                    'Error al guardar la cobertura: ' . implode(', ', $model->firstErrors)); // Corregido comillas y concatenación
            }
        }
    } else {
        // Si viene por GET (enlace simple)
        if ($model->save()) {
            Yii::$app->session->setFlash('success',
                "El servicio <strong>{$baremo->nombre_servicio}</strong> se agregó al plan con valores por defecto.");
        } else {
            Yii::$app->session->setFlash('error',
                'Error al agregar el servicio al plan: ' . implode(', ', $model->firstErrors));
        }
        return $this->redirect(['view', 'id' => $plan_id]);
    }
    
    // Mostrar formulario para completar datos si es necesario
    return $this->render('add-cobertura', [
        'model' => $model,
        'plan' => $plan,
        'baremo' => $baremo,
    ]);
}
}
