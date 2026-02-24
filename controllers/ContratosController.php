<?php

namespace app\controllers;

use Yii;
use app\models\Contratos;
use app\models\UserDatos;
use app\models\ContratosSearch;
use app\models\Cuotas;
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
     */
    public function actionIndex($user_id = "")
    {
        $afiliado = UserDatos::find()->where(['id' => $user_id])->one();
        $searchModel = new ContratosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'user_id', $user_id]);

        // REMOVE OR COMMENT OUT THIS LINE - Don't update status on index
        // $this->updateAllContractStatuses($user_id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'afiliado' => $afiliado,
        ]);
    }

    /**
     * Displays a single Contratos model.
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Check if contract is Anulado
        if ($model->estatus === 'Anulado') {
            Yii::$app->session->setFlash('warning', 'Este contrato ha sido anulado y no se pueden realizar acciones sobre él.');
        }

        // REMOVE OR COMMENT OUT THIS LINE - Don't update status on view
        // $model->updateStatus();

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

    // In ContratosController.php - Enhance delete action

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $userId = $model->user_id;

        // Check if contract has payments
        $hasPayments = Cuotas::find()
            ->where(['contrato_id' => $id])
            ->andWhere(['IS NOT', 'id_pago', null])
            ->exists();

        if ($hasPayments) {
            Yii::$app->session->setFlash('error', 'No se puede eliminar un contrato con pagos asociados.');
            return $this->redirect(['/user-datos/update', 'id' => $userId]);
        }

        // Soft delete or hard delete?
        $model->delete();

        Yii::$app->session->setFlash('success', 'Contrato eliminado correctamente.');
        return $this->redirect(['/user-datos/update', 'id' => $userId]);
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
    /**
     * Anular (Cancel) a contract
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionAnular($id)
    {
        $model = $this->findModel($id);

        // Check if user has permission (Superadmin or GERENTE-COMERCIALIZACION)
        if (!Yii::$app->user->can('superadmin') && !Yii::$app->user->can('GERENTE-COMERCIALIZACION')) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para anular contratos.');
            return $this->redirect(['index', 'user_id' => $model->user_id]);
        }

        // Check if contract is already anulado
        if ($model->estatus === Contratos::STATUS_ANULADO) {
            Yii::$app->session->setFlash('error', 'Este contrato ya está anulado.');
            return $this->redirect(['index', 'user_id' => $model->user_id]);
        }

        // Begin transaction
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Update contract with annulment information
            $model->estatus = Contratos::STATUS_ANULADO;
            $model->anulado_por = Yii::$app->user->id;
            $model->anulado_fecha = date('Y-m-d H:i:s');
            $model->anulado_motivo = 'Anulado por solicitud administrativa'; // Default reason

            if (!$model->save(false)) {
                throw new \Exception('Error al guardar la anulación del contrato.');
            }

            // Update any pending cuotas to "cancelada"
            \app\models\Cuotas::updateAll(
                ['estatus' => 'cancelada'],
                [
                    'contrato_id' => $model->id,
                    'estatus' => 'pendiente'
                ]
            );

            $transaction->commit();

            Yii::$app->session->setFlash('success', 'Contrato anulado exitosamente.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Error al anular el contrato: ' . $e->getMessage());
        }

        return $this->redirect(['index', 'user_id' => $model->user_id]);
    }

    /**
     * Check and update user solvent status based on active contracts
     * @param int $user_id
     */
    private function checkUserSolventStatus($user_id)
    {
        // Check if user has any active non-anulled contracts
        $activeContracts = Contratos::find()
            ->where(['user_id' => $user_id])
            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
            ->andWhere(['in', 'estatus', ['Activo', 'Registrado']])
            ->count();

        $user = UserDatos::findOne($user_id);
        if ($user) {
            // If no active contracts, mark as no solvente
            if ($activeContracts == 0) {
                $user->estatus_solvente = 'No';
                $user->save(false);
                Yii::info("Usuario #{$user_id} marcado como No solvente (sin contratos activos)", 'contratos');
            }
        }
    }
    /**
     * Displays annulment form
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionAnularForm($id)
    {
        $model = $this->findModel($id);

        // Check if user has permission
        if (!Yii::$app->user->can('superadmin') && !Yii::$app->user->can('GERENTE-COMERCIALIZACION')) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para anular contratos.');
            return $this->redirect(['index', 'user_id' => $model->user_id]);
        }

        if ($model->estatus === Contratos::STATUS_ANULADO) {
            Yii::$app->session->setFlash('error', 'Este contrato ya está anulado.');
            return $this->redirect(['index', 'user_id' => $model->user_id]);
        }

        if (Yii::$app->request->isPost) {
            $motivo = Yii::$app->request->post('motivo');

            if (!$motivo) {
                Yii::$app->session->setFlash('error', 'Debe indicar el motivo de la anulación.');
                return $this->render('anular', ['model' => $model]);
            }

            $transaction = Yii::$app->db->beginTransaction();

            try {
                $model->estatus = Contratos::STATUS_ANULADO;
                $model->anulado_por = Yii::$app->user->id;
                $model->anulado_fecha = date('Y-m-d H:i:s');
                $model->anulado_motivo = $motivo;

                if (!$model->save(false)) {
                    throw new \Exception('Error al guardar la anulación.');
                }

                \app\models\Cuotas::updateAll(
                    ['estatus' => 'cancelada'],
                    [
                        'contrato_id' => $model->id,
                        'estatus' => 'pendiente'
                    ]
                );

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Contrato anulado exitosamente.');
                return $this->redirect(['index', 'user_id' => $model->user_id]);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error al anular el contrato: ' . $e->getMessage());
            }
        }

        return $this->render('anular', ['model' => $model]);
    }
    /**
     * Check if contract has payments
     */
    public function actionHasPayments($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $contrato = $this->findModel($id);

        // Check if contract has any paid cuotas
        $hasPayments = Cuotas::find()
            ->where(['contrato_id' => $id])
            ->andWhere(['in', 'estatus', ['pagada', 'pagado']])
            ->exists();

        return [
            'hasPayments' => $hasPayments,
            'originalDate' => $contrato->fecha_ini
        ];
    }

    /**
     * Returns expiring contracts for the current user's clinic
     * @return array
     */
    public function actionExpiringSoon()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;

        // Get the user's clinic
        $userDatos = \app\models\UserDatos::find()
            ->where(['user_login_id' => $user->id])
            ->one();

        if (!$userDatos || !$userDatos->clinica_id) {
            return ['success' => false, 'message' => 'No clinic associated'];
        }

        $clinicaId = $userDatos->clinica_id;

        // Get contracts expiring in the next 30 days
        $contracts = \app\models\Contratos::find()
            ->alias('c')
            ->select([
                'c.id',
                'c.fecha_ven',
                'c.nrocontrato',
                'p.nombre as plan_nombre',
                'ud.nombres',
                'ud.apellidos',
                'EXTRACT(DAY FROM (c.fecha_ven - NOW())) as dias_restantes'
            ])
            ->innerJoin('user_datos ud', 'ud.id = c.user_id')
            ->innerJoin('planes p', 'p.id = c.plan_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['between', 'c.fecha_ven', date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))])
            ->andWhere(['c.estatus' => 'Activo'])
            ->orderBy(['c.fecha_ven' => SORT_ASC])
            ->limit(10)
            ->asArray()
            ->all();

        $formatted = [];
        foreach ($contracts as $contract) {
            $formatted[] = [
                'id' => $contract['id'],
                'afiliado' => $contract['nombres'] . ' ' . $contract['apellidos'],
                'plan' => $contract['plan_nombre'],
                'fecha_ven' => Yii::$app->formatter->asDate($contract['fecha_ven'], 'dd/MM/yyyy'),
                'dias_restantes' => (int)$contract['dias_restantes']
            ];
        }

        return [
            'success' => true,
            'data' => $formatted
        ];
    }
}
