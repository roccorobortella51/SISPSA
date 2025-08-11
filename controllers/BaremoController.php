<?php

namespace app\controllers;

use app\models\Baremo;
use app\models\BaremoSearch;
use app\models\RmClinica;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * BaremoController implements the CRUD actions for Baremo model.
 */
class BaremoController extends Controller
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
     * Lists all Baremo models.
     *
     * @return string
     */
    public function actionIndex($clinica_id = "")
    {
        $clinica = RmClinica::find()->where(['id' => $clinica_id])->one();
        $searchModel = new BaremoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'clinica_id', $clinica_id]);
        $model = new Baremo();

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
     * Displays a single Baremo model.
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
     * Creates a new Baremo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
   

    /**
     * Updates an existing Baremo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['index', 'clinica_id' => $model->clinica_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Baremo model.
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
     * Finds the Baremo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Baremo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Baremo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdatestatus(){
        if (Yii::$app->request->isAjax and Yii::$app->request->post()) {
            $variables = Yii::$app->request->post();

            $model = Baremo::find()->where(['id' => $variables['id']])->one();

            if($model->estatus == "Activo"){
                $model->estatus = "Inactivo";
                $model->save(false);
            }else{
                $model->estatus = "Activo";
                $model->save(false);
            }
        }
    }

   
        public function actionExportExcel()
        {
            // Obtén los datos del dataProvider. Aquí asumo que tienes una forma de obtener el dataProvider
            // con los filtros aplicados. Esto es un ejemplo.
            $searchModel = new \app\models\BaremoSearch(); // O el nombre de tu SearchModel
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->pagination = false; // Desactivar la paginación para obtener todos los registros

            // Nombres de las columnas para el archivo CSV
            $headers = [
                'Area',
                'Nombre del Servicio',
                'Descripción',
                'Costo',
                'Precio',
                'Estatus'
            ];

            // Abre un stream de memoria para escribir el archivo CSV
            $fileName = 'baremo-export-' . date('Y-m-d') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');

            $out = fopen('php://output', 'w');
            fputs($out, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); // Agregar BOM para compatibilidad con Excel
            fputcsv($out, $headers);

            // Iterar sobre los datos y escribir en el archivo
            foreach ($dataProvider->getModels() as $model) {
                $areaName = $model->area ? $model->area->nombre : "";
                $row = [
                    $areaName,
                    $model->nombre_servicio,
                    $model->descripcion,
                    $model->costo,
                    $model->precio,
                    $model->estatus === 1 ? 'Activo' : 'Inactivo',
                ];
                fputcsv($out, $row);
            }
            
            fclose($out);
            exit();
        }
}
