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
use app\models\CorporativoUser;
use app\models\ContratoCorporativoAfiliado;

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

        $listedUserIds = array_values(array_unique(array_filter(array_map(
            static function ($contract) {
                return (int)$contract->user_id;
            },
            $dataProvider->getModels()
        ))));
        $usersWithMultipleContracts = [];
        if ($listedUserIds) {
            $usersWithMultipleContracts = array_fill_keys(array_map('intval', Contratos::find()
                ->select('user_id')
                ->where(['user_id' => $listedUserIds])
                ->groupBy('user_id')
                ->having('COUNT(*) > 1')
                ->column()), true);
        }

        // REMOVE OR COMMENT OUT THIS LINE - Don't update status on index
        // $this->updateAllContractStatuses($user_id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'afiliado' => $afiliado,
            'usersWithMultipleContracts' => $usersWithMultipleContracts,
        ]);
    }

    /**
     * Lists an affiliate's contracts without the payment dashboard.
     * @param int $user_id Affiliate ID
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndexList($user_id)
    {
        $afiliado = UserDatos::findOne((int)$user_id);
        if (!$afiliado) {
            throw new NotFoundHttpException('El afiliado solicitado no existe.');
        }

        $searchModel = new ContratosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->andWhere(['user_id' => (int)$afiliado->id])
            ->orderBy(['fecha_ini' => SORT_DESC, 'id' => SORT_DESC]);

        $hasMultipleContracts = Contratos::find()
            ->where(['user_id' => (int)$afiliado->id])
            ->count() > 1;

        if ($dataProvider->pagination) {
            $dataProvider->pagination->pageSize = 15;
        }

        return $this->render('index-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'afiliado' => $afiliado,
            'hasMultipleContracts' => $hasMultipleContracts,
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
     * Starts a new individual contract from an expired contract's terms.
     * @param int $id Expired contract ID
     * @return mixed
     */
    public function actionRenew($id)
    {
        $source = $this->findModel($id);
        $indexRoute = ['index', 'user_id' => $source->user_id];
        $today = date('Y-m-d');

        if (
            $source->estatus === Contratos::STATUS_ANULADO ||
            empty($source->fecha_ven) ||
            $source->fecha_ven >= $today
        ) {
            Yii::$app->session->setFlash('warning', 'Solo se pueden renovar contratos vencidos que no estén anulados.');
            return $this->redirect($indexRoute);
        }

        $hasAnotherContract = Contratos::find()
            ->where(['user_id' => $source->user_id])
            ->andWhere(['<>', 'id', $source->id])
            ->exists();
        if ($hasAnotherContract) {
            Yii::$app->session->setFlash('warning', 'Este afiliado ya tiene otro contrato registrado.');
            return $this->redirect($indexRoute);
        }

        $affiliate = $source->user;
        if (!$affiliate) {
            Yii::$app->session->setFlash('error', 'No se encontró el afiliado asociado al contrato.');
            return $this->redirect($indexRoute);
        }

        $model = new Contratos();
        $model->setAttributes([
            'plan_id' => $source->plan_id,
            'ente_id' => $source->ente_id,
            'clinica_id' => $source->clinica_id,
            'monto' => $source->monto,
            'frecuencia_pago' => $source->frecuencia_pago,
            'sucursal' => $source->sucursal,
            'moneda' => $source->moneda,
            'user_id' => $source->user_id,
            'estatus' => Contratos::STATUS_REGISTRADO,
        ], false);

        $model->fecha_ini = date('Y-m-d');
        $model->fecha_ven = (new \DateTimeImmutable($model->fecha_ini))
            ->modify('+1 year')
            ->format('Y-m-d');

        if ($model->load(Yii::$app->request->post())) {
            // Keep contract terms from the source record; only the start date is user-editable.
            $model->setAttributes([
                'plan_id' => $source->plan_id,
                'ente_id' => $source->ente_id,
                'clinica_id' => $source->clinica_id,
                'monto' => $source->monto,
                'frecuencia_pago' => $source->frecuencia_pago,
                'sucursal' => $source->sucursal,
                'moneda' => $source->moneda,
                'user_id' => $source->user_id,
                'estatus' => Contratos::STATUS_REGISTRADO,
            ], false);

            try {
                $startDate = new \DateTimeImmutable($model->fecha_ini);
                $model->fecha_ven = $startDate->modify('+1 year')->format('Y-m-d');
            } catch (\Exception $e) {
                $model->addError('fecha_ini', 'Ingrese una fecha de inicio válida.');
            }

            if (!$model->hasErrors() && $model->save()) {
                $model->nrocontrato = $affiliate->cedula . '-' . date('Y') . '-' . $model->id;
                $model->save(false);

                $cuotaGeneration = Cuotas::generateCuotasSimple(
                    $model->id,
                    $model->fecha_ini,
                    $model->monto
                );

                if (!empty($cuotaGeneration['success'])) {
                    Yii::$app->session->setFlash('success', 'El contrato se renovó correctamente y se generaron 12 cuotas.');
                } else {
                    Yii::error(
                        'El contrato renovado #' . $model->id . ' no pudo generar sus cuotas: '
                            . ($cuotaGeneration['error'] ?? 'Error desconocido'),
                        __METHOD__
                    );
                    Yii::$app->session->setFlash(
                        'warning',
                        'El contrato se renovó, pero no se pudieron generar las 12 cuotas. Contacte al administrador para completar la generación.'
                    );
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'isRenewal' => true,
            'sourceContract' => $source,
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
                    $this->detachCorporateAffiliateWithoutValidContracts($model->user_id);
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
     * After a contract is annulled, if the affiliate is linked to a corporate
     * AND has no other non-annulled contract, unlink them from the corporate.
     *
     *   - user_datos.afiliado_corporativo_id = NULL
     *   - user_datos.user_datos_type_id      = 1 (Individual)
     *   - user_datos.estatus_solvente        = 'No'
     *   - delete corporativo_user row
     *   - delete contratos_corporativos_afiliados row
     *   - recompute parent contratos_corporativos aggregates
     *
     * MUST be called inside an open DB transaction.
     *
     * @param  Contratos $contract  The contract that was just annulled
     * @return void
     */
    private function maybeUnlinkCorporateAffiliate(Contratos $contract): void
    {
        $affiliate = UserDatos::findOne($contract->user_id);

        // Not an affiliate, or not linked to a corporate → nothing to do.
        if (!$affiliate || empty($affiliate->afiliado_corporativo_id)) {
            return;
        }

        $corporativoId = (int) $affiliate->afiliado_corporativo_id;

        // Still has another non-annulled contract? Keep the corporate link.
        $stillLive = Contratos::find()
            ->where(['user_id' => $affiliate->id])
            ->andWhere(['!=', 'id', $contract->id])
            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
            ->exists();

        if ($stillLive) {
            Yii::info(
                "Anulación: afiliado #{$affiliate->id} conserva otro contrato vivo; "
                    . "NO se desvincula del corporativo #{$corporativoId}.",
                'contratos'
            );
            return;
        }

        // 1. Unlink from corporate.
        $affiliate->afiliado_corporativo_id = null;
        $affiliate->user_datos_type_id      = 1;
        $affiliate->estatus_solvente        = 'No';
        $affiliate->updated_at              = date('Y-m-d H:i:s');

        if (!$affiliate->save(false)) {
            throw new \Exception(
                'No se pudo desvincular al afiliado del corporativo: '
                    . json_encode($affiliate->getErrors())
            );
        }

        // 2. Remove the legacy junction row (safe: no-op if absent).
        CorporativoUser::deleteAll([
            'corporativo_id' => $corporativoId,
            'user_id'        => $affiliate->id,
        ]);

        // 3. Remove the corporate-contract junction row.
        ContratoCorporativoAfiliado::deleteAll([
            'user_datos_id' => $affiliate->id,
        ]);

        // 4. Recompute aggregates on affected contratos_corporativos.
        $this->recomputeCorporateContractAggregates($corporativoId);

        Yii::info(
            "Anulación: afiliado #{$affiliate->id} desvinculado del corporativo "
                . "#{$corporativoId} (último contrato vivo anulado).",
            'contratos'
        );
    }

    /**
     * Recompute cantidad_afiliados / monto_total on contratos_corporativos
     * rows belonging to a corporate. Idempotent.
     */
    private function recomputeCorporateContractAggregates(int $corporativoId): void
    {
        Yii::$app->db->createCommand("
        UPDATE contratos_corporativos cc
        SET cantidad_afiliados = agg.cnt,
            monto_total        = agg.total,
            updated_at         = NOW()
        FROM (
            SELECT
                cca.contrato_corporativo_id,
                COUNT(DISTINCT cca.user_datos_id)       AS cnt,
                COALESCE(SUM(cca.precio_individual), 0) AS total
            FROM contratos_corporativos_afiliados cca
            INNER JOIN contratos_corporativos c
                ON c.id = cca.contrato_corporativo_id
            WHERE c.corporativo_id = :cid
            GROUP BY cca.contrato_corporativo_id
        ) agg
        WHERE cc.id = agg.contrato_corporativo_id
    ", [':cid' => $corporativoId])->execute();

        // Zero-out any corporate-contract row that lost all its affiliates.
        Yii::$app->db->createCommand("
        UPDATE contratos_corporativos cc
        SET cantidad_afiliados = 0,
            monto_total        = 0,
            updated_at         = NOW()
        WHERE cc.corporativo_id = :cid
          AND NOT EXISTS (
              SELECT 1 FROM contratos_corporativos_afiliados cca
              WHERE cca.contrato_corporativo_id = cc.id
          )
    ", [':cid' => $corporativoId])->execute();
    }
    /**
     * Anular (Cancel) a contract with reason via POST
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionAnular($id)
    {
        $model = $this->findModel($id);
        $returnRoute = Yii::$app->request->post('return_to_index_list')
            ? ['index-list', 'user_id' => $model->user_id]
            : ['index', 'user_id' => $model->user_id];

        // Permissions Check
        if (
            !Yii::$app->user->can('superadmin') &&
            !Yii::$app->user->can('GERENTE-COMERCIALIZACION') &&
            !Yii::$app->user->can('GERENTE-CLINICA') &&
            !Yii::$app->user->can('GERENTE-OPERACIONES')
        ) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para anular contratos.');
            return $this->redirect($returnRoute);
        }

        if ($model->estatus === Contratos::STATUS_ANULADO) {
            Yii::$app->session->setFlash('error', 'Este contrato ya se encuentra anulado.');
            return $this->redirect($returnRoute);
        }

        if (Yii::$app->request->isPost) {
            $motivo = Yii::$app->request->post('motivo')
                ?? Yii::$app->request->post('anulado_motivo');
            if (empty(trim($motivo))) {
                Yii::$app->session->setFlash('error', 'Debe indicar un motivo válido para la anulación.');
                return $this->redirect($returnRoute);
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->estatus = Contratos::STATUS_ANULADO;
                $model->anulado_por = Yii::$app->user->id;
                $model->anulado_fecha = date('Y-m-d H:i:s');
                $model->anulado_motivo = \yii\helpers\Html::encode($motivo);

                if (!$model->save(false)) {
                    throw new \Exception('Error al guardar la anulación del contrato.');
                }

                // Update pending cuotas to cancelada
                \app\models\Cuotas::updateAll(
                    ['estatus' => 'cancelada'],
                    ['contrato_id' => $model->id, 'estatus' => 'pendiente']
                );

                // ── Unlink from corporate if this was the affiliate's last live contract ──
                $this->maybeUnlinkCorporateAffiliate($model);
                // ──────────────────────────────────────────────────────────────────────────

                $this->detachCorporateAffiliateWithoutValidContracts($model->user_id);

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'El contrato #' . $model->id . ' fue anulado exitosamente.');
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error al anular el contrato: ' . $e->getMessage());
            }
        }

        return $this->redirect($returnRoute);
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
     * Remove a corporate affiliate from employee lists when no valid contracts remain.
     *
     * @param int|null $userId
     */
    private function detachCorporateAffiliateWithoutValidContracts($userId)
    {
        if (!$userId) {
            return;
        }

        $hasValidContract = Contratos::getContratoActivo((int)$userId) !== null;

        if (!$hasValidContract) {
            UserDatos::updateAll(
                ['afiliado_corporativo_id' => null],
                ['id' => (int)$userId, 'user_datos_type_id' => 2]
            );
        }
    }

    /**
     * Displays annulment form
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionAnularForm($id, $return_to_index_list = false)
    {
        $model = $this->findModel($id);
        $returnRoute = Yii::$app->request->post('return_to_index_list') || $return_to_index_list
            ? ['index-list', 'user_id' => $model->user_id]
            : ['index', 'user_id' => $model->user_id];

        // Check if user has permission (Superadmin, GERENTE-COMERCIALIZACION, or GERENTE-CLINICA)
        if (
            !Yii::$app->user->can('superadmin') &&
            !Yii::$app->user->can('GERENTE-COMERCIALIZACION') &&
            !Yii::$app->user->can('GERENTE-CLINICA') &&
            !Yii::$app->user->can('GERENTE-OPERACIONES')
        ) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para anular contratos.');
            return $this->redirect($returnRoute);
        }

        if ($model->estatus === Contratos::STATUS_ANULADO) {
            Yii::$app->session->setFlash('error', 'Este contrato ya está anulado.');
            return $this->redirect($returnRoute);
        }

        if (Yii::$app->request->isPost) {
            $motivo = Yii::$app->request->post('motivo');

            if (!$motivo) {
                Yii::$app->session->setFlash('error', 'Debe indicar el motivo de la anulación.');
                return $this->render('anular', [
                    'model' => $model,
                    'returnToIndexList' => (bool)$return_to_index_list,
                ]);
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
                // ── Unlink from corporate if this was the affiliate's last live contract ──
                $this->maybeUnlinkCorporateAffiliate($model);
                // ──────────────────────────────────────────────────────────────────────────
                $this->detachCorporateAffiliateWithoutValidContracts($model->user_id);

                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Contrato anulado exitosamente.');
                return $this->redirect($returnRoute);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error al anular el contrato: ' . $e->getMessage());
            }
        }

        return $this->render('anular', [
            'model' => $model,
            'returnToIndexList' => (bool)$return_to_index_list,
        ]);
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
