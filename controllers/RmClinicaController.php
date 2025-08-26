<?php

namespace app\controllers;

use app\models\RmClinica;
use app\models\RmClinicaSearch;
use app\models\RmEstado;
use app\models\RmMunicipio;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
use app\components\UserHelper; 
use app\models\RmCiudad;
use app\models\CheckListClinicas;
use yii\helpers\ArrayHelper;
use app\models\UserDatos;
use app\models\SisSiniestro;

/**
 * 
 * RmClinicaController implements the CRUD actions for RmClinica model.
 */
class RmClinicaController extends Controller
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

    public function actionIndicator($id)
    {
        $totalAfiliados = UserDatos::find()->where(['clinica_id' => $id])->count();
        $totalSiniestrosAfiliados = SisSiniestro::find()->where(['idclinica' => $id])->count();
        $totalPagosAfiliados = SisPago::find()->where(['idclinica' => $id])->count();
        $model = $this->findModel($id);
        return $this->render('indicator', [
            'model' => $model,
            'totalAfiliados' => $totalAfiliados,
            'totalSiniestrosAfiliados' => $totalSiniestrosAfiliados,
        ]);
    }

    /**
     * Lists all RmClinica models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new RmClinicaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

         // *** AQUI OBTENEMOS LOS DATOS DEL GRAFICO ***
        $chartData = CheckListClinicas::getLastChecklistsByClinic();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartData' => $chartData, 
        ]);
    }

    /**
     * Displays a single RmClinica model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
     public function actionView($id)
    {
        $model = $this->findModel($id);

        // --- CÓDIGO PARA OBTENER LAS LISTAS DE UBICACIÓN ---
        // Obtener la lista de estados (desde UserHelper)
        $estadosList = UserHelper::getEstadosList();
        
        // --- ¡CÓDIGO AJUSTADO PARA MUNICIPIOS! ---
        // Obtenemos la lista de municipios directamente de RmMunicipio,
        // mapeando por 'codigo_muni' para que coincida con $model->municipio.
        $municipiosList = [];
        if (!empty($model->estado)) {
            $municipiosList = ArrayHelper::map(
                RmMunicipio::find()
                    ->where(['estado_codigo' => $model->estado])
                    ->asArray()
                    ->all(),
                'codigo_muni', // ¡Mapeamos por 'codigo_muni' aquí!
                'nombre'
            );
        }
        // ------------------------------------

        // Obtener la lista de parroquias (desde UserHelper)
        $parroquiasList = $model->municipio ? UserHelper::getParroquiasList($model->municipio) : [];

        // Obtener la lista de ciudades (desde RmCiudad)
        $ciudadesList = [];
        if (!empty($model->estado)) {
            $ciudadesList = ArrayHelper::map(
                RmCiudad::find()
                    ->where(['estado_codigo' => $model->estado])
                    ->asArray()
                    ->all(),
                'id',
                'nombre'
            );
        
        }
        // ------------------------------------

       
        return $this->render('view', [
            'model' => $model,
            'estadosList' => $estadosList,
            'municipiosList' => $municipiosList, // ¡Esta es la lista correctamente mapeada!
            'parroquiaList' => $parroquiasList,
            'ciudadesList' => $ciudadesList,
            'listaEstatus' => ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'],
        ]);
    }

    /**
     * Creates a new RmClinica model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new RmClinica();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $estado = RmEstado::find()->where(['id' => $model->estado])->one();

                if($estado){
                    $model->estado = $estado->nombre;
                }

                $model->estatus = "Activo";
                $model->save();

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing RmClinica model.
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
        ]);
    }

    /**
     * Deletes an existing RmClinica model.
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
     * Finds the RmClinica model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return RmClinica the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = RmClinica::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdatestatus(){
        if (Yii::$app->request->isAjax and Yii::$app->request->post()) {
            $variables = Yii::$app->request->post();

            $model = RmClinica::find()->where(['id' => $variables['id']])->one();

            if($model->estatus == "Activo"){
                $model->estatus = "Inactivo";
                $model->save(false);
            }else{
                $model->estatus = "Activo";
                $model->save(false);
            }
        }
    }
}
