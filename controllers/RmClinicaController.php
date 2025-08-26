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
use app\models\Pagos;

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
        $totalPagosAfiliados = Pagos::find()
            ->joinWith('userDatos')
            ->where(['user_datos.clinica_id' => $id])
            ->count();
        $montoTotalPagosAfiliados = Pagos::find()
            ->joinWith('userDatos')
            ->where(['user_datos.clinica_id' => $id])
            ->sum('monto_pagado');
        $model = $this->findModel($id);
        return $this->render('indicator', [
            'model' => $model,
            'totalAfiliados' => $totalAfiliados,
            'totalSiniestrosAfiliados' => $totalSiniestrosAfiliados,
            'totalPagosAfiliados' => $totalPagosAfiliados,
            'montoTotalPagosAfiliados' => $montoTotalPagosAfiliados,
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

            $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
            if($estado){

                $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
                if($estado){

            $municipiosList = ArrayHelper::map(
                RmMunicipio::find()
                    ->where(['estado_codigo' => $estado->id])
                    ->asArray()
                    ->all(),
                'codigo_muni', // ¡Mapeamos por 'codigo_muni' aquí!
                'nombre'
            );}
            }
        }
        // ------------------------------------

        // Obtener la lista de parroquias (desde UserHelper)
        $parroquiasList = $model->municipio ? UserHelper::getParroquiasList($model->municipio) : [];

        // Obtener la lista de ciudades (desde RmCiudad)
        $ciudadesList = [];
        if (!empty($model->estado)) {
            $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
            $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
                if($estado){

                $ciudadesList = ArrayHelper::map(
                    RmCiudad::find()
                        ->where(['estado_codigo' => $estado->id])
                        ->asArray()
                        ->all(),
                    'id',
                    'nombre'
                );
            }
        
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

    public function actionViewClinica()
    {
        $id = UserHelper::getMyClinicaId();
        
        if ($id == null) {
            return $this->redirect(['site/index']);
        }
        
        $model = $this->findModel($id);

        

        // --- CÓDIGO PARA OBTENER LAS LISTAS DE UBICACIÓN ---
        // Obtener la lista de estados (desde UserHelper)
        $estadosList = UserHelper::getEstadosList();
        
        // --- ¡CÓDIGO AJUSTADO PARA MUNICIPIOS! ---
        // Obtenemos la lista de municipios directamente de RmMunicipio,
        // mapeando por 'codigo_muni' para que coincida con $model->municipio.
        $municipiosList = [];
        if (!empty($model->estado)) {

            $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
            if($estado){

                $municipiosList = ArrayHelper::map(
                    RmMunicipio::find()
                        ->where(['estado_codigo' => $estado->id])
                        ->asArray()
                        ->all(),
                    'codigo_muni', // ¡Mapeamos por 'codigo_muni' aquí!
                    'nombre'
                );
            }
        }
        // ------------------------------------

        // Obtener la lista de parroquias (desde UserHelper)
        $parroquiasList = $model->municipio ? UserHelper::getParroquiasList($model->municipio) : [];

        // Obtener la lista de ciudades (desde RmCiudad)
        $ciudadesList = [];
        if (!empty($model->estado)) {

            $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
                if($estado){

                    $ciudadesList = ArrayHelper::map(
                        RmCiudad::find()
                            ->where(['estado_codigo' => $estado->id])
                            ->asArray()
                            ->all(),
                        'id',
                        'nombre'
                    );
            }
        
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

            if ($this->request->isPost && $model->load($this->request->post())) {

                $estado = RmEstado::find()->where(['id' => $model->estado])->one();

                if($estado){
                    $model->estado = $estado->nombre;
                }

                $model->estatus = "Activo";

                if($model->save()){
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

    public function actionFondo()
    {
        // --- Datos Ficticios para la Demostración ---
        // Estos valores deben ser reemplazados por datos reales de tu base de datos o configuraciones.
        $fondoAnualTotal = 1200000.00; // Ejemplo: $1,200,000
        $fondoMensualTotal = 100000.00; // Ejemplo: $100,000
        $consumoMensualActual = 65000.00; // Ejemplo: $65,000 (consumido del mensual)

        // Calcular el porcentaje consumido del fondo mensual
        $porcentajeConsumido = ($fondoMensualTotal > 0) ? ($consumoMensualActual / $fondoMensualTotal) * 100 : 0;
        $porcentajeConsumido = round($porcentajeConsumido, 2); // Redondear a dos decimales

        // --- Límites de los Colores (futura configuración de administrador) ---
        // Estos límites son para la representación visual de la barra de "gasolina".
        // Por ahora, son fijos. En el futuro, un administrador podría configurarlos desde una interfaz.
        $limiteVerde = 70; // Hasta 70% de consumo es verde (Ideal)
        $limiteAmarillo = 90; // De 70% a 90% de consumo es amarillo (Advertencia)
        // Más del 90% es rojo (Peligro)

        // Determinar el color actual de la barra de progreso
        $colorClase = 'bg-success'; // Verde por defecto
        if ($porcentajeConsumido >= $limiteAmarillo) {
            $colorClase = 'bg-danger'; // Rojo si supera el límite de amarillo
        } elseif ($porcentajeConsumido >= $limiteVerde) {
            $colorClase = 'bg-warning'; // Amarillo si supera el límite de verde
        }
        
        // ----- Envío de Correos (para futura implementación) ----
        // Aquí iría la lógica para verificar si se deben enviar correos.
        // Esto requeriría almacenar el estado de los correos enviados (por ejemplo, en la DB)
        // para evitar enviar múltiples correos por el mismo evento dentro de un periodo.
        /*
        // Ejemplo de lógica futura:
        if ($porcentajeConsumido >= $limiteAmarillo && $porcentajeConsumido < $limiteRojo && !$fondoModel->warningEmailSentThisMonth) {
            // Lógica para enviar correo de advertencia al administrador
            // Yii::$app->mailer->compose(...)
            // Actualizar $fondoModel->warningEmailSentThisMonth = true; $fondoModel->save();
        }
        if ($porcentajeConsumido >= $limiteRojo && !$fondoModel->dangerEmailSentThisMonth) {
            // Lógica para enviar correo de peligro al administrador
            // Yii::$app->mailer->compose(...)
            // Actualizar $fondoModel->dangerEmailSentThisMonth = true; $fondoModel->save();
        }
        */

        return $this->render('fondo', [ // La vista ahora se llama 'fondo'
            'fondoAnualTotal' => $fondoAnualTotal,
            'fondoMensualTotal' => $fondoMensualTotal,
            'consumoMensualActual' => $consumoMensualActual,
            'porcentajeConsumido' => $porcentajeConsumido,
            'limiteVerde' => $limiteVerde,
            'limiteAmarillo' => $limiteAmarillo,
            'colorClase' => $colorClase,
        ]);
    }
}
