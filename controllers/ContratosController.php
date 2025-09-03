<?php

namespace app\controllers;

use Yii;
use app\models\Contratos;
use app\models\UserDatos;
use app\models\ContratosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ContratosController implements the CRUD actions for Contratos model.
 */
class ContratosController extends Controller
{
    public function beforeAction($action)
    {
        if ($action->id === 'detalle-pagos-ajax') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }


    /**
     * Lists all Contratos models.
     * @return mixed
     */
    public function actionIndex($user_id = "")
    {
        $afiliado = UserDatos::find()->where(['id' => $user_id])->one();
        $searchModel = new ContratosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'user_id', $user_id]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'afiliado' => $afiliado,
        ]);
    }

    /**
     * Displays a single Contratos model.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Contratos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Contratos();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Generar cuota inicial automáticamente
            $this->generarCuotaInicial($model);
            
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Contratos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $fechaInicioAnterior = $model->fecha_ini;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Si se cambió la fecha de inicio, verificar si se necesita generar cuota inicial
            if ($fechaInicioAnterior !== $model->fecha_ini) {
                $this->verificarCuotaInicial($model);
            }
            
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Contratos model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    // controllers/UserController.php



    public function actionDetallePagosAjax()
    {
        $id = $_POST['expandRowKey'];
        $model = Contratos::findOne($id);

        if (!$model) {
            throw new \yii\web\NotFoundHttpException("Contrato no encontrado.");
        }

        return $this->renderPartial('_detalle-pagos-ajax', [
            'model' => $model,
        ]);
    }

    /**
     * Verifica si se necesita generar o actualizar la cuota inicial después de cambiar la fecha de inicio.
     * 
     * @param Contratos $contrato
     * @return bool True si se generó o actualizó la cuota
     */
    protected function verificarCuotaInicial($contrato)
    {
        try {
            // Buscar la cuota inicial existente
            $cuotaInicial = \app\models\Cuotas::find()
                ->where(['contrato_id' => $contrato->id])
                ->orderBy(['fecha_vencimiento' => SORT_ASC])
                ->one();
                
            if ($cuotaInicial) {
                // Actualizar la fecha de vencimiento de la cuota inicial
                $fechaInicio = new \DateTime($contrato->fecha_ini);
                $nuevaFechaVencimiento = $fechaInicio->format('Y-m-01');
                
                if ($cuotaInicial->fecha_vencimiento !== $nuevaFechaVencimiento) {
                    $cuotaInicial->fecha_vencimiento = $nuevaFechaVencimiento;
                    if ($cuotaInicial->save()) {
                        Yii::info("Cuota inicial actualizada para contrato #{$contrato->id} - Nuevo vencimiento: {$nuevaFechaVencimiento}", 'contratos');
                        return true;
                    }
                }
            } else {
                // Si no existe cuota inicial, generarla
                return $this->generarCuotaInicial($contrato);
            }
            
            return false;
        } catch (\Exception $e) {
            Yii::error("Excepción al verificar cuota inicial para contrato #{$contrato->id}: " . $e->getMessage(), 'contratos');
            return false;
        }
    }
    
    /**
     * Genera la cuota inicial para un contrato recién creado.
     * 
     * @param Contratos $contrato
     * @return bool True si se generó la cuota
     */
    protected function generarCuotaInicial($contrato)
    {
        try {
            // Verificar si ya existe una cuota inicial
            $existeCuotaInicial = \app\models\Cuotas::find()
                ->where(['contrato_id' => $contrato->id])
                ->exists();
                
            if ($existeCuotaInicial) {
                return false; // Ya existe una cuota
            }
            
            // Generar cuota inicial para el mes de inicio del contrato
            $fechaInicio = new \DateTime($contrato->fecha_ini);
            $fechaVencimiento = $fechaInicio->format('Y-m-01');
            
            $cuota = new \app\models\Cuotas([
                'contrato_id' => $contrato->id,
                'fecha_vencimiento' => $fechaVencimiento,
                'monto_usd' => $contrato->monto,
                'Estatus' => 'pendiente',
                'rate_usd_bs' => $this->obtenerTasaCambioActual(),
            ]);
            
            if ($cuota->save()) {
                Yii::info("Cuota inicial generada para contrato #{$contrato->id} - Vencimiento: {$fechaVencimiento}", 'contratos');
                return true;
            } else {
                Yii::error("Error al generar cuota inicial para contrato #{$contrato->id}: " . print_r($cuota->errors, true), 'contratos');
                return false;
            }
        } catch (\Exception $e) {
            Yii::error("Excepción al generar cuota inicial para contrato #{$contrato->id}: " . $e->getMessage(), 'contratos');
            return false;
        }
    }
    
    /**
     * Obtiene la tasa de cambio actual.
     * 
     * @return float Tasa de cambio actual
     */
    protected function obtenerTasaCambioActual()
    {
        try {
            $tasaCambio = \app\models\TasaCambio::find()
                ->orderBy(['fecha' => SORT_DESC, 'hora' => SORT_DESC])
                ->one();
                
            if ($tasaCambio) {
                return (float)$tasaCambio->tasa_cambio;
            }
            
            return 1.0; // Valor por defecto
        } catch (\Exception $e) {
            Yii::error("Error al obtener tasa de cambio: " . $e->getMessage(), 'contratos');
            return 1.0; // Valor por defecto
        }
    }
    
    /**
     * Finds the Contratos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Contratos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Contratos::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
