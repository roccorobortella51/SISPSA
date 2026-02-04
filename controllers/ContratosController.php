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

        // Update status of all contracts before displaying
        $this->updateAllContractStatuses($user_id);

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
        $model = $this->findModel($id);

        // Check if contract is Anulado
        if ($model->estatus === 'Anulado') {
            Yii::$app->session->setFlash('warning', 'Este contrato ha sido anulado y no se pueden realizar acciones sobre él.');
        }

        // Update status before displaying
        $model->updateStatus();

        return $this->render('view', [
            'model' => $model,
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

        // Set default status to Registrado
        $model->estatus = Contratos::STATUS_REGISTRADO;

        if ($model->load(Yii::$app->request->post())) {
            // Update status based on dates
            $model->updateStatus();

            if ($model->save()) {
                // Generar cuota inicial automáticamente
                $this->generarCuotaInicial($model);

                return $this->redirect(['view', 'id' => $model->id]);
            }
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

        // Prevent updates on Anulado contracts
        if ($model->estatus === 'Anulado') {
            Yii::$app->session->setFlash('error', 'No se puede editar un contrato anulado.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $fechaInicioAnterior = $model->fecha_ini;

        if ($model->load(Yii::$app->request->post())) {
            // Update status based on dates
            $model->updateStatus();

            if ($model->save()) {
                // Si se cambió la fecha de inicio, verificar si se necesita generar cuota inicial
                if ($fechaInicioAnterior !== $model->fecha_ini) {
                    $this->verificarCuotaInicial($model);
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
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

    /**
     * Action to change contract status
     */
    public function actionChangeStatus($id, $status)
    {
        $model = $this->findModel($id);

        // Prevent status changes on already Anulado contracts (except to un-anulate)
        if ($model->estatus === 'Anulado' && $status !== 'Anulado') {
            Yii::$app->session->setFlash('error', 'No se puede cambiar el estatus de un contrato anulado.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Validate status
        $validStatuses = [
            Contratos::STATUS_REGISTRADO,
            Contratos::STATUS_ACTIVO,
            Contratos::STATUS_ANULADO,
            Contratos::STATUS_VENCIDO,
            Contratos::STATUS_PENDIENTE,
            Contratos::STATUS_SUSPENDIDO,
        ];

        if (in_array($status, $validStatuses)) {
            $model->estatus = $status;
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', "Estatus cambiado a: {$status}");

                // If status changed to Anulado, log the action
                if ($status === 'Anulado') {
                    $model->anulado_por = Yii::$app->user->id;
                    $model->anulado_fecha = date('Y-m-d H:i:s');
                    $model->save(false);
                }
            } else {
                Yii::$app->session->setFlash('error', 'Error al cambiar el estatus');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Estatus inválido');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionDetallePagosAjax()
    {
        \Yii::error("Starting detalle-pagos-ajax action", 'application');

        if (!Yii::$app->request->isPost) {
            \Yii::error("Request is not POST", 'application');
            return "Invalid request method";
        }

        $id = Yii::$app->request->post('expandRowKey');

        if (!$id) {
            \Yii::error("No expandRowKey provided in POST: " . print_r(Yii::$app->request->post(), true), 'application');
            return "Missing contract ID";
        }

        \Yii::error("Looking for contract ID: $id", 'application');

        $model = Contratos::findOne($id);

        if (!$model) {
            \Yii::error("Contract not found: $id", 'application');
            throw new \yii\web\NotFoundHttpException("Contrato no encontrado.");
        }

        try {
            \Yii::error("Getting pagos for contract: $id", 'application');
            $pagos = $model->getPagosDelContrato();
            \Yii::error("Query object: " . get_class($pagos), 'application');

            $pagosDelContrato = $pagos->all();
            \Yii::error("Found " . count($pagosDelContrato) . " pagos", 'application');
        } catch (\Exception $e) {
            \Yii::error("Exception in getPagosDelContrato: " . $e->getMessage(), 'application');
            \Yii::error("Stack trace: " . $e->getTraceAsString(), 'application');
            return "Error retrieving payments: " . $e->getMessage();
        }

        return $this->renderPartial('_detalle-pagos-ajax', [
            'model' => $model,
        ]);
    }

    /**
     * Update status for all contracts of a user
     */
    protected function updateAllContractStatuses($user_id)
    {
        try {
            $contratos = Contratos::find()
                ->where(['user_id' => $user_id])
                ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO]) // Don't update annulled contracts
                ->all();

            foreach ($contratos as $contrato) {
                $contrato->updateStatus();
            }

            return true;
        } catch (\Exception $e) {
            Yii::error("Error updating contract statuses: " . $e->getMessage(), 'contratos');
            return false;
        }
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
            // VERIFICACIÓN MEJORADA: Buscar cuotas existentes para el mes de inicio
            $fechaInicio = new \DateTime($contrato->fecha_ini);
            $mesInicio = $fechaInicio->format('Y-m');

            $existeCuota = \app\models\Cuotas::find()
                ->where(['contrato_id' => $contrato->id])
                ->andWhere(['>=', 'fecha_vencimiento', $mesInicio . '-01'])
                ->andWhere(['<=', 'fecha_vencimiento', $mesInicio . '-31'])
                ->exists();

            if ($existeCuota) {
                Yii::info("Ya existe cuota para el mes de inicio en contrato #{$contrato->id}", 'contratos');
                return false; // Ya existe una cuota para este mes
            }

            // Generar cuota inicial
            $fechaVencimiento = $fechaInicio->format('Y-m-07'); // Día 7 del mes

            $montoCuota = round($contrato->monto, 2); // ← ADD ROUND TO 2 DECIMALS

            $cuota = new \app\models\Cuotas([
                'contrato_id' => $contrato->id,
                'fecha_vencimiento' => $fechaVencimiento,
                'monto_usd' => $montoCuota, // ← USE ROUNDED VALUE
                'estatus' => 'pendiente',
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
