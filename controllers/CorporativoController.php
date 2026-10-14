<?php

namespace app\controllers;

use Yii;
use app\models\Corporativo;
use app\models\CorporativoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use app\models\CorporativoUser;
use app\models\UserDatos;
use app\models\ContratosSearch;
use app\models\Contratos;
use app\models\Pagos;
use app\models\TasaCambio;
use app\models\RmClinica;
use app\models\Cuotas;
use app\components\UserHelper;
use app\models\MasivoAfiliadosForm;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\UploadedFile;
use app\models\Planes;
use app\models\CorporativoClinica;
use app\models\User;
use app\models\RmEstado;
use \yii\db\Expression;
use app\models\AgenteFuerza;
use app\models\Receipt;
use kartik\mpdf\Pdf;
use app\models\ContratoCorporativo;
use app\models\ContratoCorporativoAfiliado;
use app\components\ContractHelper;
use yii\helpers\Html;


/**
 * CorporativoController implements the CRUD actions for Corporativo model.
 */
class CorporativoController extends Controller
{

    /**
     * Mapeo de nombres de estados a IDs (para optimización y validación).
     * @var array
     */
    private $estadoNameToIdMap = [];
    private $estadoNormToCanonicalName = [];
    private $estadoCanonicalNamesText = '';

    // ========== NEW PROPERTY: Allowed roles with clinic access ==========
    private $allowedClinicaRoles = [
        "Administrador-clinica",
        "CONTROL DE CITAS",
        "ADMISIÓN",
        "ATENCIÓN",
        "COORDINADOR-CLINICA",
        "GERENTE-CLINICA"
    ];
// ========== END OF NEW PROPERTY ==========

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                        'tasacambio-referencial' => ['POST'], // Permitir POST para la llamada AJAX
                    ],
                ],
            ]
        );
    }

    /**
     * Obtiene la tasa de cambio actual (tasa_cambio) o la más reciente.
     * Es utilizada para inicializar el campo de Tasa en el formulario de Pagos.
     * @return float
     */
    private function getTasaCambioReferencial(): float
    {
        // Busca la tasa de cambio para la fecha actual
        $tasaModel = TasaCambio::find()
            ->where(['fecha' => date('Y-m-d')])
            ->one();

        if (!$tasaModel) {
            // Fallback: Si no existe la de hoy, busca la más reciente
            $tasaModel = TasaCambio::find()
                ->orderBy(['fecha' => SORT_DESC])
                ->one();
        }

        // Usa 'tasa_cambio' según el modelo TasaCambio.php
        return $tasaModel ? (float)$tasaModel->tasa_cambio : 0.00;
    }

    /**
     * Acción AJAX para obtener la tasa de cambio referencial para una fecha específica.
     * @return string Tasa formateada o '0' si no se encuentra.
     */
    public function actionTasacambioReferencial(): string
    {
        // No configuramos formato JSON, devolvemos el valor como texto plano (string).
        $fecha = \Yii::$app->request->post('fecha');

        if (empty($fecha)) {
            return '0';
        }

        // Buscar la tasa_cambio para la fecha dada, ordenando por hora para obtener la más reciente.
        $tasa = TasaCambio::find()
            ->select('tasa_cambio')
            ->where(['fecha' => $fecha])
            ->orderBy(['hora' => SORT_DESC])
            ->scalar();

        // Devolvemos el valor (o '0' si es null) como string para que JavaScript lo maneje.
        return $tasa ? number_format((float)$tasa, 2, '.', '') : '0';
    }

    /**
     * Lists all Corporativo models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CorporativoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        // ── Precompute affiliate counts for every corporate on this page ──
        $corpIds = array_map(
            fn($m) => (int)$m->id,
            $dataProvider->getModels()
        );

        $activeCounts   = [];
        $annulledCounts = [];

        if (!empty($corpIds)) {
            // Active: affiliates with at least one non-annulled contract
            $activeRows = (new \yii\db\Query())
                ->select([
                    'corp_id' => 'ud.afiliado_corporativo_id',
                    'cnt'     => 'COUNT(DISTINCT ud.id)',
                ])
                ->from(['ud' => 'user_datos'])
                ->innerJoin(['c' => 'contratos'], 'c.user_id = ud.id')
                ->where(['ud.afiliado_corporativo_id' => $corpIds])
                ->andWhere(['ud.user_datos_type_id' => 2])
                ->andWhere(['<>', 'c.estatus', \app\models\Contratos::STATUS_ANULADO])
                ->groupBy('ud.afiliado_corporativo_id')
                ->all(\Yii::$app->db);

            foreach ($activeRows as $row) {
                $activeCounts[(int)$row['corp_id']] = (int)$row['cnt'];
            }

            // Annulled: affiliates whose only contracts (if any) are all annulled
            $annulledRows = (new \yii\db\Query())
                ->select([
                    'corp_id' => 'ud.afiliado_corporativo_id',
                    'cnt'     => 'COUNT(DISTINCT ud.id)',
                ])
                ->from(['ud' => 'user_datos'])
                ->where(['ud.afiliado_corporativo_id' => $corpIds])
                ->andWhere(['ud.user_datos_type_id' => 2])
                ->andWhere([
                    'NOT EXISTS',
                    (new \yii\db\Query())
                        ->from(['c' => 'contratos'])
                        ->where('c.user_id = ud.id')
                        ->andWhere(['<>', 'c.estatus', \app\models\Contratos::STATUS_ANULADO])
                ])
                ->groupBy('ud.afiliado_corporativo_id')
                ->all(\Yii::$app->db);

            foreach ($annulledRows as $row) {
                $annulledCounts[(int)$row['corp_id']] = (int)$row['cnt'];
            }
        }

        return $this->render('index', [
            'searchModel'    => $searchModel,
            'dataProvider'   => $dataProvider,
            'activeCounts'   => $activeCounts,
            'annulledCounts' => $annulledCounts,
        ]);

        // Asegurar que las relaciones se carguen
        // el query del dataProvider se declara como QueryInterface,
        // comprobar que es un ActiveQuery antes de usar with()
        $query = $dataProvider->query;
        if ($query instanceof \yii\db\ActiveQuery) {
            $query->with(['users', 'clinicas']);
        }

        // ========== OPTIONAL: You could also add a check here to verify ==========
        // ========== that the user has access to at least one corporate ==========
        $userRole = UserHelper::getMyRol();
        if (in_array($userRole, $this->allowedClinicaRoles)) {
            $clinicaId = UserHelper::getMyClinicaId();
            if (empty($clinicaId)) {
                Yii::$app->session->setFlash('warning', 'No tiene una clínica asociada. No se mostrarán registros.');
            }
        }
        // ========== END OF OPTIONAL CHECK ==========

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Corporativo model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // ========== NEW: Check if user has access to this corporate ==========
        if (!$this->userHasAccessToCorporativo($model->id)) {
            Yii::$app->session->setFlash('error', 'No tiene permiso para ver este corporativo.');
            return $this->redirect(['index']);
        }
        // ========== END OF NEW CHECK ==========

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Lists ALL corporate contracts for a given corporate, grouped by
     * nrocontrato (one business-level contract per year). This is the
     * entry point for viewing, creating, downloading, and annulling
     * corporate contracts — instead of jumping straight to the PDF drawer.
     *
     * @param  int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionContracts($id)
    {
        $corporativo = $this->findModel($id);

        if (!$this->userHasAccessToCorporativo($id)) {
            Yii::$app->session->setFlash('error', 'No tiene permiso para ver los contratos de este corporativo.');
            return $this->redirect(['index']);
        }

        // Load every ContratoCorporativo row for this corporate, including
        // annulled ones for the historic view, ordered newest first.
        $flatContracts = ContratoCorporativo::find()
            ->with(['plan', 'clinica', 'creator'])
            ->where(['corporativo_id' => $id])
            ->orderBy(['fecha_ini' => SORT_DESC, 'plan_id' => SORT_ASC])
            ->all();

        // ── Group into consistent summaries (anchor, is_annulled, etc.) ─────
        $contractGroups = ContractHelper::buildContractGroupSummaries($flatContracts);

        // Check whether a contract already exists for the CURRENT year — if
        // yes, the "Nuevo Contrato" button will be disabled by the helper.
        $currentYear = (int) date('Y');

        return $this->render('contracts', [
            'corporativo'         => $corporativo,
            'contractGroups'      => $contractGroups,
            'hasContractThisYear' => $this->hasContractForYear($contractGroups, $currentYear),
            'currentYear'         => $currentYear,
        ]);
    }

    /**
     * Returns true when at least one non-annulled group has a fecha_ini
     * falling inside $year.
     *
     * @param  array $contractGroups
     * @param  int   $year
     * @return bool
     */
    private function hasContractForYear(array $contractGroups, int $year): bool
    {
        foreach ($contractGroups as $group) {
            if (!empty($group['is_annulled'])) {
                continue;
            }
            if (empty($group['anchor']->fecha_ini)) {
                continue;
            }
            if ((int) date('Y', strtotime($group['anchor']->fecha_ini)) === $year) {
                return true;
            }
        }
        return false;
    }

    /**
     * Realiza un pago corporativo para los afiliados del corporativo.
     * Carga cuotas pendientes por afiliado y renderiza el formulario.
     * @param int $id Corporativo ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the corporativo cannot be found
     */
    public function actionPagos($id)
    {
        $corporativo = $this->findModel($id);
        $model = new Pagos();
        $model->loadDefaultValues();
        $model->user_id = null;
        $model->estatus = 'Por Conciliar';
        $model->tipo_pago = 'corporativo';

        // Obtener IDs de usuarios asociados
        $userIds = CorporativoUser::find()
            ->select('user_id')
            ->where(['corporativo_id' => $id])
            ->column();

        $allCuotas = [];
        $grandTotal = 0;
        $userAmounts = [];

        if (!empty($userIds)) {
            foreach ($userIds as $userId) {
                $user = UserDatos::findOne($userId);
                if ($user) {
                    $cuotas = Cuotas::find()
                        ->select('cuotas.*')
                        ->innerJoinWith(['contrato'])
                        ->where(['contratos.user_id' => $userId])
                        ->andWhere(['in', 'cuotas.estatus', ['pendiente', 'en_gracias']])
                        ->orderBy(['cuotas.fecha_vencimiento' => SORT_ASC])
                        ->all();

                    $userTotal = 0;
                    foreach ($cuotas as $cuota) {
                        $monto = $cuota->monto;
                        if ($monto > 0) {
                            $allCuotas[] = $cuota;
                            $userTotal += $monto;
                        }
                    }
                    $grandTotal += $userTotal;
                    $userAmounts[$userId] = $userTotal;
                }
            }
        }

        // --- FIXED: PROPERLY INITIALIZE TASA AND FECHA_PAGO ---
        $model->fecha_pago = date('Y-m-d'); // Set default date

        // Get current exchange rate and format it properly
        $currentTasa = $this->getTasaCambioReferencial();
        $model->tasa = number_format($currentTasa, 2, '.', '');

        // Pre-fill the payment amount with the calculated total
        $model->monto_pagado = $grandTotal;

        // Calculate initial monto_usd (Bs) based on the current rate
        if ($grandTotal > 0 && $currentTasa > 0) {
            $model->monto_usd = $grandTotal * $currentTasa;
        }
        // --- END FIX ---

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $montoPagadoPosted = (float)($model->monto_pagado ?: 0);

                if (abs($grandTotal - $montoPagadoPosted) > 0.01) {
                    $model->addError('monto_pagado', 'El monto a pagar debe coincidir con el total de cuotas pendientes.');
                    Yii::$app->session->setFlash('warning', 'El monto no coincide con el total de cuotas pendientes.');
                } else {
                    // ============================================================
                    // MANDATORY COMPROBANTE — hard server-side guard.
                    //
                    // `imagen_prueba_file` is only required when the payment does
                    // not already carry a saved comprobante URL (edit flow). This
                    // closes the gap left by the model rule's skipOnEmpty=true.
                    // ============================================================
                    $model->imagen_prueba_file = \yii\web\UploadedFile::getInstance($model, 'imagen_prueba_file');

                    if (!$model->imagen_prueba_file && empty($model->imagen_prueba)) {
                        Yii::$app->session->setFlash(
                            'error',
                            'Debe adjuntar el comprobante de pago antes de guardar el registro.'
                        );
                        return $this->render('pagos', [
                            'model'       => $model,
                            'corporativo' => $corporativo,
                            'allCuotas'   => $allCuotas,
                            'grandTotal'  => $grandTotal,
                        ]);
                    }

                    // Handle file upload
                    if ($model->imagen_prueba_file) {
                        $folder = 'Pago';
                        $fileName = uniqid('pago_corp_') . '.' . $model->imagen_prueba_file->extension;
                        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                        if ($model->imagen_prueba_file->saveAs($tempFilePath)) {
                            $publicUrl = UserHelper::uploadFileToSupabaseApi(
                                $tempFilePath,
                                $model->imagen_prueba_file->type,
                                $fileName,
                                $folder
                            );

                            if (file_exists($tempFilePath)) {
                                unlink($tempFilePath);
                            }

                            if ($publicUrl) {
                                $model->imagen_prueba = $publicUrl;
                            }
                        }
                    }

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $model->corporativo_id = $corporativo->id;
                        $post = $this->request->post('Pagos');
                        $model->monto_usd = $post['monto_usd'] ?? null;

                        if ($model->save(false)) {
                            $mainPaymentId = $model->id;
                            $affiliatePaymentsCount = 0;
                            $affiliatePaymentMap = []; // userId => affiliatePaymentId

                            // Create individual payment records for each affiliate
                            foreach ($userAmounts as $userId => $userAmount) {
                                if ($userAmount > 0) {
                                    $affiliatePayment = new Pagos();

                                    // Copy only the safe attributes, excluding the ID
                                    $affiliatePayment->created_at = $model->created_at;
                                    $affiliatePayment->fecha_pago = $model->fecha_pago;
                                    $affiliatePayment->monto_pagado = $userAmount; // User-specific amount
                                    $affiliatePayment->metodo_pago = $model->metodo_pago;
                                    $affiliatePayment->estatus = $model->estatus;
                                    $affiliatePayment->numero_referencia_pago = $model->numero_referencia_pago;
                                    $affiliatePayment->imagen_prueba = $model->imagen_prueba;
                                    $affiliatePayment->tasa = $model->tasa;
                                    $affiliatePayment->monto_usd = $userAmount * $model->tasa; // Calculate user-specific amount in Bs
                                    $affiliatePayment->observacion = $model->observacion;

                                    // Set the relationship fields
                                    $affiliatePayment->user_id = $userId; // Specific affiliate
                                    $affiliatePayment->corporativo_id = $corporativo->id;
                                    $affiliatePayment->pago_corporativo_id = $mainPaymentId; // Link to main payment
                                    $affiliatePayment->tipo_pago = 'afiliado_corporativo';

                                    if ($affiliatePayment->save(false)) {
                                        $affiliatePaymentsCount++;
                                        $affiliatePaymentMap[$userId] = $affiliatePayment->id;
                                        \Yii::info("Created affiliate payment for user {$userId} with amount {$userAmount}, ID: {$affiliatePayment->id}");
                                    } else {
                                        \Yii::error("Failed to create affiliate payment for user {$userId}: " . print_r($affiliatePayment->errors, true));
                                        throw new \Exception("Failed to create affiliate payment for user {$userId}");
                                    }
                                }
                            }

                            $contratosActualizados = [];
                            $cuotasUpdatedCount = 0;

                            // Update cuotas - NOW USING INDIVIDUAL PAYMENT IDs
                            foreach ($allCuotas as $cuota) {
                                if ($cuota->estatus === 'pendiente') {
                                    $userId = $cuota->contrato->user_id;

                                    // Use the individual payment ID for this specific affiliate
                                    $affiliatePaymentId = $affiliatePaymentMap[$userId] ?? $mainPaymentId;

                                    // Get the individual payment for its specific rate
                                    $individualPayment = Pagos::findOne($affiliatePaymentId);
                                    $tasaCuota = $individualPayment ? ($individualPayment->monto_usd / $individualPayment->monto_pagado) : $model->tasa;

                                    $cuota->estatus = 'pagada';
                                    $cuota->fecha_pago = $individualPayment->fecha_pago ?? $model->fecha_pago;
                                    $cuota->rate_usd_bs = $tasaCuota;
                                    $cuota->id_pago = $mainPaymentId; // Link to INDIVIDUAL payment

                                    if ($cuota->save(false)) {
                                        $cuotasUpdatedCount++;
                                        if (!in_array($cuota->contrato_id, $contratosActualizados)) {
                                            $contratosActualizados[] = $cuota->contrato_id;
                                        }
                                    }
                                }
                            }

                            $contractsActivatedCount = 0;
                            foreach ($contratosActualizados as $contratoId) {
                                $contrato = Contratos::findOne($contratoId);
                                if ($contrato) {
                                    $oldStatus = $contrato->estatus;

                                    // Call updateStatus() to re-evaluate contract status
                                    $contrato->updateStatus();

                                    // Refresh to get the latest status
                                    $contrato->refresh();

                                    // Check if contract was activated (changed to Activo)
                                    if ($contrato->estatus === 'Activo' && $oldStatus !== 'Activo') {
                                        $contractsActivatedCount++;
                                        Yii::info("Contract #{$contratoId} activated from {$oldStatus} to Activo", 'corporativo');
                                    }
                                }
                            }

                            $transaction->commit();

                            Yii::$app->session->setFlash(
                                'success',
                                'Pago corporativo registrado exitosamente. ' .
                                    $affiliatePaymentsCount . ' afiliados procesados. ' .
                                    $cuotasUpdatedCount . ' cuotas actualizadas. ' .
                                    $contractsActivatedCount . ' contratos activados.'
                            );

                            return $this->redirect(['view', 'id' => $corporativo->id]);
                        } else {
                            throw new \Exception('Error al guardar el pago corporativo principal.');
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Error al procesar el pago: ' . $e->getMessage());
                    }
                }
            }
        }

        return $this->render('pagos', [
            'model' => $model,
            'corporativo' => $corporativo,
            'allCuotas' => $allCuotas,
            'grandTotal' => $grandTotal,
        ]);
    }

    /**
     * Creates a new Corporativo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Corporativo();
        $model->created_at = date('Y-m-d H:i:s');

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                // ========== NEW: For users with clinic roles, auto-assign their clinic ==========
                $userRole = UserHelper::getMyRol();
                if (in_array($userRole, $this->allowedClinicaRoles)) {
                    $clinicaId = UserHelper::getMyClinicaId();
                    if (!empty($clinicaId) && empty($model->clinicas_ids)) {
                        $model->clinicas_ids = [$clinicaId];
                    }
                }
                // ========== END OF NEW CODE ==========

                if ($model->save()) {
                    $usersIds = array_filter((array) $model->users_ids); // Elimina vacíos y nulls
                    foreach ($usersIds as $user_datos_id) {
                        if (empty($user_datos_id)) continue; // Extra seguridad

                        $afiliado = UserDatos::findOne($user_datos_id);
                        if ($afiliado) {
                            $modelCorporativoUser = new CorporativoUser();
                            $modelCorporativoUser->corporativo_id = $model->id;
                            $modelCorporativoUser->user_id = $afiliado->id;
                            $modelCorporativoUser->fecha_vinculacion = date('Y-m-d H:i:s');
                            $modelCorporativoUser->rol_en_corporativo = 'afiliado';

                            if (!$modelCorporativoUser->save()) {
                                Yii::error("Error al guardar CorporativoUser: " . json_encode($modelCorporativoUser->errors), __METHOD__);
                                Yii::$app->session->setFlash('error', 'Error al guardar la relación con el afiliado.');
                            }
                        }
                    }

                    Yii::$app->session->setFlash('success', 'Corporativo creado exitosamente.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();

            // ========== NEW: Pre-select clinic for users with clinic roles ==========
            $userRole = UserHelper::getMyRol();
            if (in_array($userRole, $this->allowedClinicaRoles)) {
                $clinicaId = UserHelper::getMyClinicaId();
                if (!empty($clinicaId)) {
                    $model->clinicas_ids = [$clinicaId];
                }
            }
            // ========== END OF NEW CODE ==========
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Corporativo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // ========== NEW: Check if user has access to update this corporate ==========
        if (!$this->userHasAccessToCorporativo($id)) {
            Yii::$app->session->setFlash('error', 'No tiene permiso para editar este corporativo.');
            return $this->redirect(['index']);
        }
        // ========== END OF NEW CHECK ==========

        // Obtener los IDs de los afiliados que ya están asociados con este corporativo
        $afiliadosActuales = CorporativoUser::find()
            ->where(['corporativo_id' => $model->id])
            ->select('user_id')
            ->column();

        if ($this->request->isPost && $model->load($this->request->post())) {

            // ========== NEW: For users with clinic roles, ensure they keep their clinic ==========
            $userRole = UserHelper::getMyRol();
            if (in_array($userRole, $this->allowedClinicaRoles)) {
                $clinicaId = UserHelper::getMyClinicaId();
                if (!empty($clinicaId)) {
                    // Make sure the user's clinic is always included
                    if (empty($model->clinicas_ids)) {
                        $model->clinicas_ids = [$clinicaId];
                    } elseif (!in_array($clinicaId, $model->clinicas_ids)) {
                        $model->clinicas_ids[] = $clinicaId;
                    }
                }
            }
            // ========== END OF NEW CODE ==========

            $afiliadosSeleccionados = (array)($this->request->post('Corporativo')['users_ids'] ?? []);
            $afiliadosSeleccionados = array_filter($afiliadosSeleccionados);

            $afiliadosParaBorrar = array_diff($afiliadosActuales, $afiliadosSeleccionados);
            $afiliadosParaAnadir = array_diff($afiliadosSeleccionados, $afiliadosActuales);

            // --- LÓGICA PARA BORRAR RELACIONES Y ACTUALIZAR USER_DATOS ---
            foreach ($afiliadosParaBorrar as $userId) {
                // 1. Borrar la relación de la tabla intermedia
                $relacion = CorporativoUser::findOne(['corporativo_id' => $model->id, 'user_id' => $userId]);
                if ($relacion) {
                    $relacion->delete();
                }

                // 2. Actualizar el registro en la tabla user_datos (desvincular)
                $userDatos = UserDatos::findOne(['user_login_id' => $userId]);
                if ($userDatos) {
                    $userDatos->afiliado_corporativo_id = null;
                    $userDatos->user_datos_type_id = 1; // Asumimos 1 es "Simple"
                    $userDatos->save(false);
                }
            }

            // --- LÓGICA PARA AÑADIR NUEVAS RELACIONES Y ACTUALIZAR USER_DATOS ---
            foreach ($afiliadosParaAnadir as $userId) {
                // 1. Crear la nueva relación en la tabla intermedia
                $nuevaRelacion = new CorporativoUser();
                $nuevaRelacion->corporativo_id = $model->id;
                $nuevaRelacion->user_id = $userId;
                $nuevaRelacion->fecha_vinculacion = date('Y-m-d H:i:s');
                $nuevaRelacion->rol_en_corporativo = 'afiliado';
                $nuevaRelacion->save(false);

                // 2. Actualizar el registro en la tabla user_datos (vincular)
                $userDatos = UserDatos::findOne(['user_login_id' => $userId]);
                if ($userDatos) {
                    $userDatos->afiliado_corporativo_id = $model->id;
                    $userDatos->user_datos_type_id = 2; // Asumimos 2 es "Corporativo"
                    $userDatos->save(false);
                }
            }

            // --- LÓGICA PARA GUARDAR EL MODELO PRINCIPAL (CORPORATIVO) ---
            if ($model->save()) {

                // ============================================================
                // NEW: Recompute aggregates on contratos_corporativos.
                //
                // `contratos_corporativos.cantidad_afiliados` and `monto_total`
                // are frozen at the moment the corporate contract is created.
                // Any change to the affiliate set (add/remove here, annul an
                // individual contract elsewhere, unlink an affiliate, etc.)
                // would otherwise leave those columns stale — which is exactly
                // what caused the "25 vs 24" mismatch in `corporativo/view.php`.
                //
                // We recompute them from the junction table, which IS the
                // single source of truth for "who belongs to this corporate
                // contract".
                // ============================================================
                if (!empty($afiliadosParaBorrar) || !empty($afiliadosParaAnadir)) {
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
                ", [':cid' => $model->id])->execute();

                    // Zero-out any corporate-contract row that lost all its
                    // affiliates (e.g. the affiliate was the only one on that
                    // plan). Otherwise those rows would keep their old
                    // cantidad_afiliados forever.
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
                ", [':cid' => $model->id])->execute();
                }
                // ============================================================
                // END NEW
                // ============================================================

                Yii::$app->session->setFlash('success', 'Corporativo actualizado exitosamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar el corporativo: ' . implode(', ', array_map(function ($errors) {
                    return implode(', ', $errors);
                }, $model->getErrors())));
            }
        }

        return $this->render('update', [
            'model' => $model,
            'afiliadosActuales' => $afiliadosActuales,
        ]);
    }

    /**
     * Acción principal para mostrar y procesar el formulario de carga masiva de afiliados.
     *
     * The contract vigencia (fecha_ini / fecha_ven) is now captured on this form
     * and passed down to procesarCSV() → persistCorporateContract().
     *
     * @return string|\yii\web\Response
     */
    public function actionCargaMasivaAfiliados()
    {
        $model = new MasivoAfiliadosForm();

        // Corporativos activos for the dropdown
        $corporativosData = Corporativo::find()
            ->select(['id', 'nombre'])
            ->where(['estatus' => 'Activo'])
            ->asArray()
            ->all();

        $corporativos = ArrayHelper::map($corporativosData, 'id', 'nombre');

        $planes   = [];
        $asesores = UserHelper::getAgenteFuerzaList();

        if ($model->load(Yii::$app->request->post())) {
            $model->masivoFile = UploadedFile::getInstance($model, 'masivoFile');

            if ($model->validate()) {
                $filePath      = $model->masivoFile->tempName;
                $corporativoId = $model->corporativo_id;
                $planId        = $model->plan_id;
                $asesorId      = $model->asesor_id;
                $fechaIni      = $model->fecha_ini;
                $fechaVen      = $model->fecha_ven;

                // Get the selected plan to determine the clinic
                $plan = Planes::findOne($planId);
                if (!$plan) {
                    Yii::$app->session->setFlash('error', 'El plan seleccionado no existe.');
                    return $this->render('carga-masiva-afiliados', [
                        'model'        => $model,
                        'corporativos' => $corporativos,
                        'planes'       => $planes,
                        'asesores'     => $asesores,
                    ]);
                }

                // Pass the user-supplied vigencia down to the CSV processor.
                $resultados = $this->procesarCSV(
                    $filePath,
                    $corporativoId,
                    $planId,
                    $plan->clinica_id,
                    $asesorId,
                    $fechaIni,
                    $fechaVen
                );

                $errorCount   = count($resultados['errors']);
                $successCount = $resultados['successCount'];

                $messageType = $errorCount > 0 ? 'warning' : 'success';
                $messageText = 'Proceso de carga finalizado con ' .
                    $successCount . ' éxitos y ' .
                    $errorCount . ' errores.' .
                    ($errorCount > 0 ? ' Revise el detalle.' : ' Todo cargado correctamente.');

                Yii::$app->session->setFlash($messageType, $messageText);

                $corporativoModel = Corporativo::findOne($corporativoId);

                return $this->render('carga-masiva-resumen', [
                    'model'         => $model,
                    'resultados'    => $resultados,
                    'corporativo'   => $corporativoModel,
                    'selectedPlan'  => $plan,
                ]);
            } else {
                $validationErrors = ArrayHelper::flatten($model->getErrors());
                Yii::$app->session->setFlash(
                    'error',
                    'Error de validación del formulario de carga: ' . implode('; ', $validationErrors)
                );
            }
        }

        return $this->render('carga-masiva-afiliados', [
            'model'        => $model,
            'corporativos' => $corporativos,
            'planes'       => $planes,
            'asesores'     => $asesores,
        ]);
    }

    /**
     * Lógica principal para leer el archivo CSV y procesar los afiliados,
     * asegurando el cumplimiento de las reglas de validación de UserDatos.
     *
     * ACTUALIZACIÓN IMPORTANTE (v2 — PROMOTION-AWARE):
     * - plan_id, clinica_id y asesor_id ya NO se leen del CSV
     * - Se reciben como parámetros del formulario
     * - Las fechas del contrato (fecha_ini / fecha_ven) se leen AHORA del corporativo
     * - Genera 12 cuotas por afiliado utilizando el método generateCuotasAnniversaryBased()
     * - Duplicate handling is now three-layered, mirroring UserDatosController::actionCreate():
     *     1. Live affiliate  → block row
     *     2. Promotable user → promote if names match, else block with identity conflict
     *     3. Fresh cédula    → normal create
     *
     * @param string $filePath Ruta temporal del archivo CSV.
     * @param int $corporativoId ID del corporativo destino.
     * @param int $planId ID del plan seleccionado en el formulario.
     * @param int $clinicaId ID de la clínica asociada al plan.
     * @param int|null $asesorId ID del asesor seleccionado (opcional).
     * @param string|null $fechaIni
     * @param string|null $fechaVen
     * @return array Array con el conteo de éxitos, los errores encontrados y detalles de éxito.
     */
    private function procesarCSV($filePath, $corporativoId, $planId, $clinicaId, $asesorId = null, $fechaIni = null, $fechaVen = null)
    {
        $handle = fopen($filePath, "r");
        if ($handle === false) {
            return [
                'successCount' => 0,
                'errors' => ['No se pudo abrir el archivo.'],
                'successDetails' => []
            ];
        }

        // ============================================================
        // 0. Load the corporate and validate the caller-supplied vigencia.
        // ============================================================
        $corporativo = Corporativo::findOne($corporativoId);

        if (!$corporativo) {
            fclose($handle);
            return [
                'successCount' => 0,
                'errors' => ["El corporativo con ID {$corporativoId} no existe."],
                'successDetails' => []
            ];
        }

        if (empty($fechaIni) || empty($fechaVen)) {
            fclose($handle);
            return [
                'successCount' => 0,
                'errors' => ['Debe indicar la vigencia del contrato (fecha de inicio y fin).'],
                'successDetails' => []
            ];
        }

        if ($fechaVen <= $fechaIni) {
            fclose($handle);
            return [
                'successCount' => 0,
                'errors' => ['La fecha de vencimiento debe ser posterior a la fecha de inicio.'],
                'successDetails' => []
            ];
        }

        $fechaIniGlobal = $fechaIni;
        $fechaVenGlobal = $fechaVen;

        Yii::info(
            "Carga masiva para corporativo #{$corporativoId}: vigencia {$fechaIniGlobal} → {$fechaVenGlobal}",
            __METHOD__
        );

        // Campos requeridos - PLAN_ID, CLINICA_ID Y ASESOR_ID YA NO SON REQUERIDOS EN EL CSV
        $requiredFields = [
            'tipo_cedula',
            'cedula',
            'nombres',
            'apellidos',
            'fechanac',
            'sexo',
            'telefono',
            'email',
            'direccion',
            'estado'
        ];

        // Manejo de BOM (Byte Order Mark) y lectura de cabeceras
        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);
        $headerLine = fgets($handle, 1000);
        if (strpos($headerLine, $bom) === 0) {
            $headerLine = substr($headerLine, 3);
        }
        $headers = str_getcsv($headerLine, ",");

        $successCount = 0;
        $errors = [];
        $successDetails = [];
        $lineNumber = 1;

        if ($headers === false) {
            return [
                'successCount' => 0,
                'errors' => ['El archivo CSV está vacío o ilegible.'],
                'successDetails' => []
            ];
        }
        $headerMap = array_flip(array_map('trim', $headers));

        // Validación de cabeceras requeridas
        $missingHeaders = array_diff($requiredFields, array_keys($headerMap));
        if (!empty($missingHeaders)) {
            return [
                'successCount' => 0,
                'errors' => ['Línea 1 (Cabecera): Faltan las siguientes columnas requeridas: ' . implode(', ', $missingHeaders)],
                'successDetails' => []
            ];
        }

        // Pre-carga y mapeo de estados para validación y mensajes de ayuda
        if (empty($this->estadoNameToIdMap)) {
            $allEstados = RmEstado::find()->select(['id', 'nombre'])->asArray()->all();
            $canonicos = [];
            foreach ($allEstados as $estado) {
                // Normalizamos para búsqueda sin acentos/case-sensitive
                $normalizedName = strtolower($this->_normalizeString($estado['nombre']));
                $this->estadoNameToIdMap[$normalizedName] = $estado['id'];
                $this->estadoNormToCanonicalName[$normalizedName] = $estado['nombre'];
                $canonicos[] = $estado['nombre'];
            }
            $this->estadoCanonicalNamesText = implode(', ', $canonicos);
        }

        // Validar que el plan y la clínica existen
        $plan = Planes::findOne($planId);
        if (!$plan) {
            fclose($handle);
            return [
                'successCount' => 0,
                'errors' => ['El Plan ID ' . $planId . ' no existe o no está activo.'],
                'successDetails' => []
            ];
        }

        // Verificar que la clínica está vinculada al corporativo
        if (!CorporativoClinica::find()->where(['corporativo_id' => $corporativoId])->andWhere(['clinica_id' => $clinicaId])->exists()) {
            fclose($handle);
            return [
                'successCount' => 0,
                'errors' => ["La Clínica ID {$clinicaId} NO está vinculada al Corporativo ID {$corporativoId} seleccionado."],
                'successDetails' => []
            ];
        }

        // Procesar cada línea del CSV
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if (empty(array_filter($data, function ($value) {
                return $value !== '';
            }))) {
                continue;
            }

            $lineNumber++;
            $transaction = Yii::$app->db->beginTransaction();

            $cedulaCsv = trim($data[$headerMap['cedula']] ?? '');
            $logPrefix = "Línea {$lineNumber} (Cédula: " . ($cedulaCsv ?: 'N/A') . "): ";
            $userLogin = null;

            try {
                // === VALIDACIÓN DE CÉDULA ===
                if (empty($cedulaCsv)) {
                    throw new \Exception('El campo cédula está vacío.');
                }

                $cedulaLimpia = $this->limpiarSoloNumeros($cedulaCsv);
                if (empty($cedulaLimpia)) {
                    throw new \Exception("La cédula '{$cedulaCsv}' no contiene números válidos.");
                }

                // === VALIDACIÓN DE EMAIL ===
                $email = trim($data[$headerMap['email']] ?? '');
                if (empty($email)) {
                    throw new \Exception('El campo email está vacío.');
                }

                // Auto-corregir errores comunes en emails
                $email = str_replace(' ', '', $email);
                $email = str_replace('@gamil.com', '@gmail.com', $email);
                $email = str_replace('@gamil.com', '@gmail.com', $email);
                $email = str_replace('@gmai.com', '@gmail.com', $email);

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception("El email '{$email}' no tiene un formato válido.");
                }

                // ============================================================
                // === DUPLICATE HANDLING — PROMOTION-AWARE (v2) ===
                // ------------------------------------------------------------
                // Mirrors the logic in UserDatosController::actionCreate():
                //
                //   1. LIVE AFFILIATE?        → hard block (throw)
                //   2. PROMOTABLE RECORD?     → promote if names match, else block
                //   3. NO EXISTING RECORD?    → normal create path
                //
                // The name-match guard prevents the silent identity overwrite
                // that was previously possible when a cédula collided with a
                // different person.
                // ============================================================

                // Extract the incoming identity from the CSV
                $incomingNombres   = trim($data[$headerMap['nombres']]   ?? '');
                $incomingApellidos = trim($data[$headerMap['apellidos']] ?? '');
                $incomingTipoCed   = trim($data[$headerMap['tipo_cedula']] ?? '');

                // Build a transient UserDatos so we can reuse its methods
                $probe = new UserDatos();
                $probe->role        = 'afiliado';
                $probe->tipo_cedula = $incomingTipoCed;
                $probe->cedula      = $cedulaLimpia;
                $probe->nombres     = $incomingNombres;
                $probe->apellidos   = $incomingApellidos;
                $probe->email       = $email;

                // ── Step 1: LIVE affiliate duplicate? ──
                $liveDup = $probe->getDuplicateDetails(false);
                if ($liveDup && isset($liveDup['record'])) {
                    /** @var UserDatos $existing */
                    $existing = $liveDup['record'];
                    throw new \Exception(
                        "Ya existe un afiliado activo con la cédula {$cedulaLimpia} "
                            . "({$existing->nombres} {$existing->apellidos}). "
                            . "No se puede volver a registrar."
                    );
                }

                // ── Step 2: Promotable (non-affiliate) record? ──
                $promotable     = UserDatos::findPromotableRecordByCedula($incomingTipoCed, $cedulaLimpia);
                $promotedRecord = null;

                if ($promotable) {
                    // 2a. Names must match — otherwise block, to prevent identity overwrite.
                    if (!UserDatos::namesMatch(
                        $promotable->nombres,
                        $promotable->apellidos,
                        $incomingNombres,
                        $incomingApellidos
                    )) {
                        throw new \Exception(
                            "La cédula {$incomingTipoCed}-{$cedulaLimpia} ya está registrada a nombre de "
                                . "{$promotable->nombres} {$promotable->apellidos} (rol: {$promotable->role}). "
                                . "Los datos del CSV ({$incomingNombres} {$incomingApellidos}) no coinciden. "
                                . "Verifique la cédula o el nombre antes de reintentar."
                        );
                    }

                    // 2b. Warn (not block) if the CSV email differs from the login email.
                    if (!empty($promotable->user_login_id)) {
                        $existingUserLogin = User::findOne($promotable->user_login_id);
                        if ($existingUserLogin && strcasecmp($existingUserLogin->email, $email) !== 0) {
                            Yii::warning(
                                "Carga masiva: se promueve la cédula {$incomingTipoCed}-{$cedulaLimpia}. "
                                    . "El email del CSV ({$email}) difiere del login existente "
                                    . "({$existingUserLogin->email}); se conservará el login original.",
                                __METHOD__
                            );
                        }
                    }

                    // 2c. Safe to promote.
                    Yii::info(
                        "Carga masiva: promoviendo UserDatos #{$promotable->id} "
                            . "(rol actual: {$promotable->role}) a afiliado para cédula "
                            . "{$incomingTipoCed}-{$cedulaLimpia}",
                        __METHOD__
                    );

                    $promotedRecord = $promotable;
                }

                // ── Step 3: Email duplicate check (only when NOT promoting) ──
                // If we're promoting, the email belongs to the staff member's
                // existing login. If we're creating fresh, the email must not collide.
                if (!$promotedRecord) {
                    if (User::find()->where(['email' => $email])->exists()) {
                        throw new \Exception(
                            "Ya existe un usuario con el email {$email} registrado. "
                                . "Use un email diferente o verifique si el afiliado ya está registrado."
                        );
                    }
                }

                // === EXTRACCIÓN Y SANEAMIENTO DE DATOS ===
                $telefonoCelularCsv = trim($data[$headerMap['telefono']] ?? '');
                $direccionResidencia = trim($data[$headerMap['direccion']] ?? '');
                $estadoNameCsv = trim($data[$headerMap['estado']] ?? '');

                // Nuevos campos opcionales
                $nacionalidad = trim($data[$headerMap['nacionalidad']] ?? '');
                $estadoCivilCsv = trim($data[$headerMap['estado_civil']] ?? '');
                $lugarNacimiento = trim($data[$headerMap['lugar_nacimiento']] ?? '');
                $profesion = trim($data[$headerMap['profesion']] ?? '');
                $ocupacion = trim($data[$headerMap['ocupacion']] ?? '');
                $actividadEconomicaCsv = trim($data[$headerMap['actividad_economica']] ?? '');
                $ramoComercial = trim($data[$headerMap['ramo_comercial']] ?? '');
                $descripcionActividadCsv = trim($data[$headerMap['descripcion_actividad']] ?? '');
                $ingresoAnualCsv = trim($data[$headerMap['ingreso_anual']] ?? '');
                $direccionCobro = trim($data[$headerMap['direccion_cobro']] ?? '');
                $telefonoResidenciaCsv = trim($data[$headerMap['telefono_residencia']] ?? '');
                $direccionOficina = isset($headerMap['direccion_oficina']) ? trim($data[$headerMap['direccion_oficina']] ?? '') : null;
                $telefonoOficinaCsv = isset($headerMap['telefono_oficina']) ? trim($data[$headerMap['telefono_oficina']] ?? '') : null;
                $tipoSangre = isset($headerMap['tipo_sangre']) ? trim($data[$headerMap['tipo_sangre']] ?? '') : null;

                // === VALIDACIÓN DE TELÉFONOS ===
                $telefonoCelularLimpio = $this->limpiarTelefono($telefonoCelularCsv);
                $telefonoResidenciaLimpio = $this->limpiarTelefono($telefonoResidenciaCsv);
                $telefonoOficinaLimpio = $this->limpiarTelefono($telefonoOficinaCsv);

                // === VALIDACIÓN DE ESTADO ===
                if (empty($estadoNameCsv)) {
                    throw new \Exception("El campo 'estado' está vacío. Use exactamente uno de: " . $this->estadoCanonicalNamesText);
                }

                $normalizedCsvName = strtolower(trim($this->_normalizeString($estadoNameCsv)));

                if (!isset($this->estadoNameToIdMap[$normalizedCsvName])) {
                    // Intentar búsqueda parcial
                    $found = false;
                    foreach ($this->estadoNameToIdMap as $key => $id) {
                        if (strpos($key, $normalizedCsvName) !== false || strpos($normalizedCsvName, $key) !== false) {
                            $this->estadoNameToIdMap[$normalizedCsvName] = $id;
                            $this->estadoNormToCanonicalName[$normalizedCsvName] = $this->estadoNormToCanonicalName[$key];
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        throw new \Exception("El nombre del estado '{$estadoNameCsv}' no fue encontrado. Use exactamente uno de: " . $this->estadoCanonicalNamesText);
                    }
                }

                $estadoNombreParaUserDatos = $this->estadoNormToCanonicalName[$normalizedCsvName] ?? $estadoNameCsv;

                // ============================================================
                // === USER LOGIN — reuse on promotion, create otherwise ===
                // ============================================================
                $afiliado  = null;

                if ($promotedRecord) {
                    // ── PROMOTION PATH ──
                    // Reuse the existing User login. Do NOT create a new one.
                    $userLogin = User::findOne($promotedRecord->user_login_id);

                    if (!$userLogin) {
                        throw new \Exception(
                            "El registro existente (cédula {$incomingTipoCed}-{$cedulaLimpia}) "
                                . "no tiene un usuario de login asociado. Contacte al administrador."
                        );
                    }

                    // Ensure the login is active.
                    if ((int)$userLogin->status !== User::STATUS_ACTIVE) {
                        $userLogin->status = User::STATUS_ACTIVE;
                        $userLogin->save(false);
                    }

                    $afiliado = $promotedRecord;
                } else {
                    // ── NORMAL CREATE PATH ──
                    $userLogin = new User();
                    $userLogin->email = $email;
                    $userLogin->username = $email;
                    $userLogin->password_hash = Yii::$app->security->generatePasswordHash(
                        Yii::$app->security->generateRandomString(12)
                    );
                    $userLogin->generateAuthKey();
                    $userLogin->status = 10;

                    if (!$userLogin->save()) {
                        Yii::error(['Error_User_Login' => $userLogin->getErrors()], __METHOD__);
                        throw new \Exception(
                            'Error al crear User Login: '
                                . implode(', ', ArrayHelper::flatten($userLogin->getErrors()))
                        );
                    }

                    $afiliado = new UserDatos();
                    $afiliado->user_login_id = $userLogin->id;
                }

                // ============================================================
                // IDENTITY FIELDS — preserve on promotion, set on create
                // ============================================================
                if (!$promotedRecord) {
                    $afiliado->tipo_cedula = trim($data[$headerMap['tipo_cedula']]);
                    $afiliado->cedula      = $cedulaLimpia;
                    $afiliado->nombres     = trim($data[$headerMap['nombres']]);
                    $afiliado->apellidos   = trim($data[$headerMap['apellidos']]);
                } else {
                    // Sanity: the promoted record must match the CSV cédula
                    if ((string)$afiliado->cedula !== (string)$cedulaLimpia) {
                        throw new \Exception(
                            "Inconsistencia: la cédula promovida ({$afiliado->cedula}) "
                                . "no coincide con la del CSV ({$cedulaLimpia})."
                        );
                    }
                }

                // === FECHA DE NACIMIENTO (MÚLTIPLES FORMATOS) ===
                $fechaNacString = trim($data[$headerMap['fechanac']]);
                $dateObject = null;

                // Define los formatos de fecha soportados
                $formats = [
                    'd/m/Y',      // 19/07/1995
                    'd/m/y',      // 17/10/89  → se convierte a 1989
                    'Y-m-d',      // 1986-09-02
                    'Y/m/d',      // 1986/09/02
                    'm/d/Y',      // 07/19/1995
                    'd-m-Y',      // 19-07-1995
                    'd.m.Y',      // 19.07.1995
                    'd.m.y',      // 19.07.89
                    'd-m-y',      // 19-07-89
                    'm/d/y',      // 07/19/89
                ];

                // Intentar cada formato
                foreach ($formats as $format) {
                    $dateObject = \DateTime::createFromFormat($format, $fechaNacString);
                    if ($dateObject !== false) {
                        break;
                    }
                }

                if (!$dateObject) {
                    throw new \Exception("La fecha de nacimiento '{$fechaNacString}' no tiene un formato de fecha válido (Ej: DD/MM/AAAA, DD/MM/AA, o YYYY-MM-DD).");
                }

                $year = (int)$dateObject->format('Y');
                $currentYear = (int)date('Y');

                // Para años de 2 dígitos (ej: 89), DateTime::createFromFormat('d/m/y') ya los convierte a 1989
                // Pero debemos validar que el año sea razonable (entre 1900 y el año actual)
                if ($year < 1900 || $year > $currentYear) {
                    // Si el año es 89, significa que no se pudo convertir correctamente
                    // Intentar convertir manualmente añadiendo el siglo
                    if ($year < 100) {
                        // Asumir que es un año del siglo XX (1900-1999)
                        $year = 1900 + $year;
                        // Reconstruir la fecha con el año corregido
                        $month = (int)$dateObject->format('m');
                        $day = (int)$dateObject->format('d');
                        $dateObject = \DateTime::createFromFormat('Y-m-d', "{$year}-{$month}-{$day}");

                        if ($dateObject === false) {
                            throw new \Exception("La fecha de nacimiento '{$fechaNacString}' es inválida después de corregir el año.");
                        }
                    } else {
                        throw new \Exception("La fecha de nacimiento '{$fechaNacString}' es inválida (año: {$year}). La fecha debe ser entre 1900 y {$currentYear}.");
                    }
                }

                // Only set fechanac if the promoted record doesn't already have one
                if (!$promotedRecord || empty($afiliado->fechanac)) {
                    $afiliado->fechanac = $dateObject->format('Y-m-d');
                }

                // === SEXO (NORMALIZACIÓN) ===
                $sexoCsv = trim($data[$headerMap['sexo']]);
                $sexoNormalizado = null;

                if (!empty($sexoCsv)) {
                    $sexoUpper = strtoupper($sexoCsv);
                    if (in_array($sexoUpper, ['M', 'MASCULINO'])) {
                        $sexoNormalizado = 'Masculino';
                    } elseif (in_array($sexoUpper, ['F', 'FEMENINO'])) {
                        $sexoNormalizado = 'Femenino';
                    } else {
                        $sexoNormalizado = $sexoCsv;
                    }
                }

                $afiliado->sexo = $sexoNormalizado;

                // === TELÉFONOS Y DIRECCIÓN ===
                $afiliado->telefono_celular = $telefonoCelularLimpio;
                $afiliado->telefono = $telefonoCelularLimpio;
                $afiliado->direccion_residencia = $direccionResidencia;
                $afiliado->direccion = $direccionResidencia;
                $afiliado->estado = $estadoNombreParaUserDatos;

                // === CAMPOS OPCIONALES (CON VALIDACIÓN NO BLOQUEANTE) ===
                $afiliado->nacionalidad = !empty($nacionalidad) ? $nacionalidad : null;
                $afiliado->lugar_nacimiento = !empty($lugarNacimiento) ? $lugarNacimiento : null;
                $afiliado->profesion = !empty($profesion) ? $profesion : null;
                $afiliado->ocupacion = !empty($ocupacion) ? $ocupacion : null;
                $afiliado->ramo_comercial = !empty($ramoComercial) ? $ramoComercial : null;
                $afiliado->direccion_cobro = !empty($direccionCobro) ? $direccionCobro : null;
                $afiliado->direccion_oficina = !empty($direccionOficina) ? $direccionOficina : null;
                $afiliado->telefono_residencia = $telefonoResidenciaLimpio;
                $afiliado->telefono_oficina = $telefonoOficinaLimpio;

                // === ESTADO CIVIL (NORMALIZACIÓN) ===
                if (!empty($estadoCivilCsv)) {
                    $estadoCivilNormalizado = ucfirst(strtolower($estadoCivilCsv));
                    $validEstadosCivil = ['Soltero', 'Casado', 'Divorciado', 'Viudo'];
                    if (!in_array($estadoCivilNormalizado, $validEstadosCivil)) {
                        Yii::warning("Estado civil no válido: '{$estadoCivilCsv}', usando valor original", __METHOD__);
                        $afiliado->estado_civil = $estadoCivilCsv;
                    } else {
                        $afiliado->estado_civil = $estadoCivilNormalizado;
                    }
                }

                // === ACTIVIDAD ECONÓMICA (NORMALIZACIÓN) ===
                if (!empty($actividadEconomicaCsv)) {
                    $actividadNormalizada = ucfirst(strtolower($actividadEconomicaCsv));
                    $validActividades = ['Industrial', 'Comercial', 'Profesional', 'Gubernamental'];
                    if (!in_array($actividadNormalizada, $validActividades)) {
                        Yii::warning("Actividad económica no válida: '{$actividadEconomicaCsv}', usando valor original", __METHOD__);
                        $afiliado->actividad_economica = $actividadEconomicaCsv;
                    } else {
                        $afiliado->actividad_economica = $actividadNormalizada;
                    }
                }

                // === DESCRIPCIÓN DE ACTIVIDAD (NORMALIZACIÓN) ===
                if (!empty($descripcionActividadCsv)) {
                    $descripcionNormalizada = ucfirst(strtolower($descripcionActividadCsv));
                    $validDescripciones = ['Independiente', 'Dependiente', 'Societaria'];
                    if (!in_array($descripcionNormalizada, $validDescripciones)) {
                        Yii::warning("Descripción de actividad no válida: '{$descripcionActividadCsv}', usando valor original", __METHOD__);
                        $afiliado->descripcion_actividad = $descripcionActividadCsv;
                    } else {
                        $afiliado->descripcion_actividad = $descripcionNormalizada;
                    }
                }

                // === INGRESO ANUAL (NORMALIZACIÓN) ===
                if (!empty($ingresoAnualCsv)) {
                    $validIngresos = [
                        'De 1 a 5 Salarios mínimos',
                        'De 6 a 10 Salarios mínimos',
                        'De 11 a 20 Salarios mínimos',
                        'De 20 Salarios mínimos en adelante'
                    ];

                    $ingresoLower = strtolower(trim($ingresoAnualCsv));
                    $found = false;
                    foreach ($validIngresos as $valid) {
                        if (strtolower($valid) === $ingresoLower) {
                            $afiliado->ingreso_anual = $valid;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        Yii::warning("Ingreso anual no válido: '{$ingresoAnualCsv}', usando valor original", __METHOD__);
                        $afiliado->ingreso_anual = $ingresoAnualCsv;
                    }
                }

                // === TIPO DE SANGRE (NORMALIZACIÓN) ===
                if (!empty($tipoSangre)) {
                    $tipoSangre = strtoupper($tipoSangre);
                    $validTiposSangre = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                    if (!in_array($tipoSangre, $validTiposSangre)) {
                        Yii::warning("Tipo de sangre no válido: '{$tipoSangre}', usando valor original", __METHOD__);
                    }
                    $afiliado->tipo_sangre = $tipoSangre;
                }

                // === CAMPOS FIJOS ===
                $afiliado->user_datos_type_id = 2;
                $afiliado->afiliado_corporativo_id = $corporativoId;
                $afiliado->email = $email;
                $afiliado->role = 'afiliado';
                $afiliado->estatus = 'Creado';
                $afiliado->estatus_solvente = 'Si';
                $afiliado->codigoValidacion = UserHelper::getInstance()->generarCodigoValidacion();
                // Preserve created_at on promotion; set it fresh otherwise
                $afiliado->created_at = $afiliado->created_at ?: date('Y-m-d H:i:s');
                $afiliado->updated_at = date('Y-m-d H:i:s');
                $afiliado->plan_id = $planId;
                $afiliado->clinica_id = $clinicaId;

                if (!empty($asesorId)) {
                    $afiliado->asesor_id = (int) $asesorId;
                }

                if (!$afiliado->save()) {
                    $errorMessages = ArrayHelper::flatten($afiliado->getErrors());
                    Yii::error([
                        'ERROR_VALIDACION_MASIVA' => $lineNumber,
                        'Modelo' => 'UserDatos',
                        'Errores_Detalle' => $afiliado->getErrors(),
                    ], __METHOD__);
                    throw new \Exception('Error al crear UserDatos (Validación): ' . implode('; ', $errorMessages));
                }

                // === CREAR CONTRATO ===
                $modelContrato = new Contratos();
                $modelContrato->user_id = $afiliado->id;
                $modelContrato->estatus = 'Registrado';
                $modelContrato->clinica_id = $afiliado->clinica_id;
                $modelContrato->plan_id = $afiliado->plan_id;
                $modelContrato->monto = $plan ? $plan->precio : 0;
                $modelContrato->fecha_ini = $fechaIniGlobal;
                $modelContrato->fecha_ven = $fechaVenGlobal;

                if (!$modelContrato->save()) {
                    Yii::error(['Error_Contrato' => $modelContrato->getErrors()], __METHOD__);
                    throw new \Exception('Error al crear Contrato: ' . implode(', ', ArrayHelper::flatten($modelContrato->getErrors())));
                }

                // === GENERAR NÚMERO DE CONTRATO ===
                $anio_actual = date('Y');
                $modelContrato->nrocontrato = $afiliado->cedula . '-' . $anio_actual . '-' . $modelContrato->id;
                $afiliado->contrato_id = $modelContrato->id;

                if (!$modelContrato->save(false) || !$afiliado->save(false)) {
                    throw new \Exception('Error al guardar NroContrato o contrato_id.');
                }

                // ============================================================
                // === GENERAR CUOTAS ALINEADAS AL CICLO DEL CORPORATIVO ===
                // ------------------------------------------------------------
                // Corporate affiliates MUST be billed on the same day-of-month
                // as the corporate contract (the "anchor day").
                //
                // When an affiliate joins mid-cycle, a PRORATED first cuota is
                // emitted for the gap between their join date and the corporate
                // anchor day, followed by regular aligned monthly cuotas through
                // the corporate contract's end date.
                //
                // This mirrors exactly what the single-affiliate flow does in
                // UserDatosController::actionCreate() via
                // Cuotas::generateCorporateAlignedCuotasWithProration(), so both
                // paths now produce identical schedules for the same affiliate.
                // ============================================================

                // Resolve the corporate contract that will anchor the billing cycle.
                // Priority:
                //   1. A contract whose vigencia contains TODAY
                //   2. A contract anchored in the CURRENT calendar year
                $today       = date('Y-m-d');
                $currentYear = (int) date('Y');

                $corpContract = \app\models\ContratoCorporativo::find()
                    ->where(['corporativo_id' => $corporativoId])
                    ->andWhere(['!=', 'estatus', \app\models\ContratoCorporativo::STATUS_ANULADO])
                    ->andWhere(['<=', 'fecha_ini', $today])
                    ->andWhere(['>=', 'fecha_ven', $today])
                    ->orderBy(['fecha_ini' => SORT_DESC])
                    ->one();

                if (!$corpContract) {
                    $corpContract = \app\models\ContratoCorporativo::find()
                        ->where(['corporativo_id' => $corporativoId])
                        ->andWhere(['!=', 'estatus', \app\models\ContratoCorporativo::STATUS_ANULADO])
                        ->andWhere(['>=', 'fecha_ini', $currentYear . '-01-01'])
                        ->andWhere(['<=', 'fecha_ini', $currentYear . '-12-31'])
                        ->orderBy(['fecha_ini' => SORT_DESC])
                        ->one();
                }

                // The billing anchor day comes from the corporate contract's fecha_ini.
                // Fallback: use the affiliate's own fecha_ini so we don't hard-fail if
                // the corporate contract hasn't been persisted yet (it will be, right
                // after this loop, by ContractHelper::persistCorporateContract).
                $anchorIni = $corpContract ? $corpContract->fecha_ini : $modelContrato->fecha_ini;

                Yii::info(
                    "Corporate-aligned cuota generation for contract #{$modelContrato->id}: " .
                        "affiliate fecha_ini={$modelContrato->fecha_ini}, " .
                        "anchor fecha_ini={$anchorIni}, " .
                        "corporate fecha_ven={$modelContrato->fecha_ven}",
                    __METHOD__
                );

                $cuotaGenerationResult = Cuotas::generateCorporateAlignedCuotasWithProration(
                    $modelContrato->id,
                    $modelContrato->fecha_ini,   // affiliate's join date
                    $anchorIni,                  // corporate contract's fecha_ini → anchor day
                    $modelContrato->fecha_ven,   // corporate contract's fecha_ven → hard stop
                    $modelContrato->monto
                );

                if (!$cuotaGenerationResult['success']) {
                    throw new \Exception(
                        'Error al generar las cuotas alineadas al ciclo del corporativo: '
                            . $cuotaGenerationResult['error']
                    );
                }

                $cuotasGeneradas = count($cuotaGenerationResult['cuotas']);

                Yii::info(
                    "✅ Generadas {$cuotasGeneradas} cuotas alineadas al ciclo del corporativo " .
                        "para el contrato #{$modelContrato->id} " .
                        "(Afiliado: {$afiliado->cedula} - {$afiliado->nombres} {$afiliado->apellidos}). " .
                        "Prorrateada: " . (!empty($cuotaGenerationResult['has_proration']) ? 'SÍ' : 'NO'),
                    __METHOD__
                );

                // === CREAR RELACIÓN CORPORATIVO USER ===
                // Delete any pre-existing relation first (idempotent on promotion)
                CorporativoUser::deleteAll([
                    'corporativo_id' => $corporativoId,
                    'user_id'        => $afiliado->id,
                ]);

                $corporativoUser = new CorporativoUser();
                $corporativoUser->corporativo_id = $corporativoId;
                $corporativoUser->user_id = $afiliado->id;
                $corporativoUser->fecha_vinculacion = new Expression('NOW()');
                $corporativoUser->rol_en_corporativo = 'afiliado';

                if (!$corporativoUser->save()) {
                    Yii::error(['Error_CorporativoUser' => $corporativoUser->getErrors()], __METHOD__);
                    throw new \Exception('Error al vincular con CorporativoUser: ' . implode(', ', ArrayHelper::flatten($corporativoUser->getErrors())));
                }

                // ============================================================
                // === ASSIGN ROLE ===
                // ------------------------------------------------------------
                // On a fresh record, assign 'afiliado'.
                // On a promotion, ADD 'afiliado' without removing existing roles.
                // ============================================================
                $auth = Yii::$app->authManager;
                $role = $auth->getRole('afiliado');
                if ($role) {
                    try {
                        $alreadyAssigned = $auth->getAssignment('afiliado', $userLogin->id);
                        if (!$alreadyAssigned) {
                            $auth->assign($role, $userLogin->id);
                            Yii::$app->cache->flush();
                        }
                    } catch (\Throwable $e) {
                        Yii::error(
                            "Error asignando rol 'afiliado' a User #{$userLogin->id}: " . $e->getMessage(),
                            __METHOD__
                        );
                        // Non-fatal — the affiliate record is still created.
                    }
                }

                // === RECOLECTAR DETALLES PARA EL RESUMEN ===
                $successDetails[] = [
                    'user_datos_id' => $afiliado->id,
                    'user_login_id' => $userLogin->id,
                    'tipo_cedula' => $afiliado->tipo_cedula,
                    'cedula' => $afiliado->cedula,
                    'nombres' => $afiliado->nombres,
                    'apellidos' => $afiliado->apellidos,
                    'email' => $afiliado->email,
                    'nrocontrato' => $modelContrato->nrocontrato,
                    'created_at' => $afiliado->created_at,
                    'plan_id' => $planId,
                    'plan_nombre' => $plan->nombre,
                    'clinica_id' => $clinicaId,
                    'cuotas_generadas' => $cuotasGeneradas,
                    'asesor_id' => $asesorId,
                    'promoted'           => ($promotedRecord !== null),
                    'promoted_from_role' => $promotedRecord ? $promotedRecord->role : null,
                ];

                $transaction->commit();
                $successCount++;
            } catch (\Exception $e) {
                $transaction->rollBack();
                $errors[] = $logPrefix . $e->getMessage();

                if (isset($userLogin) && !$userLogin->isNewRecord) {
                    try {
                        $userLogin->delete();
                    } catch (\Throwable $th) {
                        Yii::error("Error de limpieza del User Login: " . $th->getMessage(), __METHOD__);
                    }
                }
                Yii::error("Carga Masiva Error General en Línea {$lineNumber} - " . $e->getMessage(), __METHOD__);
            }
        }

        fclose($handle);

        // ============================================================
        // 4. Persist the CORPORATE CONTRACT for this batch.
        //    One row per plan — all sharing the same nrocontrato.
        //    Only runs if at least one affiliate was created successfully.
        // ============================================================
        if ($successCount > 0) {
            $createdIds = array_column($successDetails, 'user_datos_id');

            // Fetch the freshly-created UserDatos and group them by plan.
            $newEmpleados = UserDatos::find()
                ->where(['id' => $createdIds])
                ->andWhere(['afiliado_corporativo_id' => $corporativoId])
                ->with(['plan', 'clinica'])
                ->all();

            $employeesByPlan = [];
            foreach ($newEmpleados as $emp) {
                if (!$emp->plan_id || !$emp->plan) {
                    continue;
                }
                $employeesByPlan[(int)$emp->plan_id][] = $emp;
            }

            $persistedPlans  = [];
            $failedPlans     = [];

            foreach ($employeesByPlan as $planId => $empleados) {
                $plan = $empleados[0]->plan;

                $contractModel = \app\components\ContractHelper::persistCorporateContract(
                    $corporativo,
                    $plan,
                    $empleados,
                    $fechaIniGlobal,
                    $fechaVenGlobal
                );

                if ($contractModel === null) {
                    $failedPlans[] = $plan->nombre;
                    continue;
                }

                $persistedPlans[] = $plan->nombre;
            }

            if (!empty($persistedPlans)) {
                Yii::info(
                    "Corporate contract persisted after mass upload for corporate #{$corporativoId}: "
                        . implode(', ', $persistedPlans),
                    __METHOD__
                );
            }

            if (!empty($failedPlans)) {
                $errors[] = 'Los siguientes planes no pudieron registrar su contrato corporativo: '
                    . implode(', ', $failedPlans);
            }
        }

        return [
            'successCount' => $successCount,
            'errors' => $errors,
            'successDetails' => $successDetails
        ];
    }

    /**
     * Genera y fuerza la descarga de un archivo CSV de ejemplo (plantilla).
     * Se han añadido los campos: nacionalidad, estado_civil, lugar_nacimiento, profesion,
     * ocupacion, actividad_economica, ramo_comercial, descripcion_actividad,
     * ingreso_anual, direccion_cobro, y telefono_residencia.
     * @return \yii\web\Response
     */
    public function actionDescargarPlantilla()
    {
        $headers = [
            'tipo_cedula',
            'cedula',
            'nombres',
            'apellidos',
            'fechanac',
            'sexo',
            'telefono',
            'email',
            'direccion',
            'estado',           // ← Keep estado
            // 'plan_id' REMOVED - plan is selected in the form
            // 'clinica_id' REMOVED - clinic is determined automatically

            // New optional fields (unchanged)
            'nacionalidad',
            'estado_civil',
            'lugar_nacimiento',
            'profesion',
            'ocupacion',
            'actividad_economica',
            'ramo_comercial',
            'descripcion_actividad',
            'ingreso_anual',
            'direccion_cobro',
            'telefono_residencia',
            'direccion_oficina',
            'telefono_oficina',
            'tipo_sangre'
        ];

        $sampleData = [
            'V',
            '19088456',
            'JUAN PABLO',
            'ROJAS PEREZ',
            '1990-05-15',
            'Masculino',
            '04121234567',
            'juan.pablo@yopmail.com',
            'CALLE SOL #123',
            'MIRANDA',
            // plan_id REMOVED
            // clinica_id REMOVED

            // New fields (unchanged)
            'VENEZOLANA',
            'Casado',
            'CARACAS',
            'INGENIERO',
            'EMPLEADO',
            'Gubernamental',
            'SERVICIOS',
            'Dependiente',
            'De 6 a 10 Salarios mínimos',
            'DIRECCION PARA ENVIAR ESTADOS DE CUENTA',
            '02125551234',
            'AV. PRINCIPAL, EDIF. AZUL, PISO 3',
            '2125871425',
            'A+'
        ];

        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF"); // BOM para compatibilidad con Excel

        fputcsv($output, $headers, ',');
        fputcsv($output, $sampleData, ',');

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return Yii::$app->response->sendContentAsFile($content, 'plantilla_afiliados_corporativos.csv', [
            'mimeType' => 'text/csv; charset=UTF-8',
            'inline' => false
        ]);
    }

    public function actionDescargarCatalogoEstados()
    {
        $estados = RmEstado::find()->select(['id', 'nombre'])->orderBy(['nombre' => SORT_ASC])->asArray()->all();
        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['id', 'nombre'], ',');
        foreach ($estados as $e) {
            fputcsv($output, [$e['id'], $e['nombre']], ',');
        }
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        return Yii::$app->response->sendContentAsFile($content, 'catalogo_estados.csv', [
            'mimeType' => 'text/csv; charset=UTF-8',
            'inline' => false
        ]);
    }

    public function actionDescargarCatalogoAsesores()
    {
        $map = UserHelper::getAgenteFuerzaList(); // id => name
        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['id', 'Nombre'], ',');
        foreach ($map as $id => $name) {
            fputcsv($output, [$id, $name], ',');
        }
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        return Yii::$app->response->sendContentAsFile($content, 'catalogo_asesores.csv', [
            'mimeType' => 'text/csv; charset=UTF-8',
            'inline' => false
        ]);
    }

    /**
     * Limpia y formatea un número de teléfono (celular o fijo).
     * Asegura que el formato sea de 11 dígitos, cumpliendo la validación de UserDatos.
     *
     * MEJORADO: Ahora maneja números con y sin código de país,
     * con o sin el 0 inicial, y con diferentes separadores.
     *
     * @param string $telefono El número de teléfono del CSV.
     * @return string|null El número de teléfono saneado (11 dígitos) o null si está vacío.
     */
    protected function limpiarTelefono(string $telefono): ?string
    {
        // Si está vacío, retornar null
        if (empty($telefono) || trim($telefono) === '') {
            return null;
        }

        // 1. Quitar todos los caracteres que no sean dígitos
        $numeroLimpio = preg_replace('/[^0-9]/', '', $telefono);

        // 2. Si está vacío después de limpiar, retornar null
        if (empty($numeroLimpio)) {
            return null;
        }

        // 3. Si tiene 10 dígitos y no empieza con '0', se asume que le falta el '0' inicial
        if (strlen($numeroLimpio) === 10 && substr($numeroLimpio, 0, 1) !== '0') {
            $numeroLimpio = '0' . $numeroLimpio;
        }

        // 4. Si tiene 11 dígitos pero empieza con código de país (ej: 58), extraer el número local
        if (strlen($numeroLimpio) > 11) {
            // Si empieza con 58 (código de Venezuela), quitar el 58 y ajustar
            if (substr($numeroLimpio, 0, 2) === '58') {
                $numeroLimpio = substr($numeroLimpio, 2);
                // Si tiene 10 dígitos después de quitar el 58, agregar el 0
                if (strlen($numeroLimpio) === 10) {
                    $numeroLimpio = '0' . $numeroLimpio;
                }
            } else {
                // Si es más largo, se toman los últimos 11 (para manejar códigos de país si los hubiera)
                $numeroLimpio = substr($numeroLimpio, -11);
            }
        }

        // 5. Validar que tenga exactamente 11 dígitos
        if (strlen($numeroLimpio) !== 11) {
            Yii::warning("Teléfono no válido: {$telefono} -> {$numeroLimpio} (longitud: " . strlen($numeroLimpio) . ")", __METHOD__);
            return null; // Retornar null para que no falle la validación
        }

        return $numeroLimpio;
    }

    /**
     * Limpia una cadena para dejar únicamente caracteres numéricos.
     * Requerido porque el campo 'cedula' es INTEGER en la DB.
     *
     * MEJORADO: Maneja casos como "V-19.088.456" y "19088456"
     *
     * @param string $input La cédula con posibles prefijos (V, E, J, G, guiones, puntos).
     * @return string Solo los dígitos de la cédula.
     */
    protected function limpiarSoloNumeros(string $input): string
    {
        // Si está vacío, retornar vacío
        if (empty($input)) {
            return '';
        }

        // Quitar todo lo que no sea número
        $output = preg_replace('/[^0-9]/', '', $input);

        return $output;
    }

    /**
     * Normaliza una cadena quitando acentos y caracteres especiales (para buscar estados).
     *
     * MEJORADO: Ahora también maneja espacios múltiples y caracteres especiales adicionales.
     *
     * @param string $string La cadena a normalizar.
     * @return string La cadena normalizada.
     */
    private function _normalizeString($string)
    {
        // Remover espacios múltiples y trim
        $string = trim(preg_replace('/\s+/', ' ', $string));

        $unwanted_array = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ñ' => 'n',
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ñ' => 'N',
            'ä' => 'a',
            'ë' => 'e',
            'ï' => 'i',
            'ö' => 'o',
            'ü' => 'u',
            'ç' => 'c',
            'Ç' => 'C',
            ' ' => '',
        ];

        return strtr($string, $unwanted_array);
    }

    /**
     * Obtiene la lista de clínicas asociadas a un corporativo dado,
     * incluyendo la relación de Planes de cada clínica.
     * @param int $id ID del Corporativo
     * @return array
     */
    public function actionObtenerClinicasConPlanesPorCorporativo($id)
    {
        // 1. Configurar la respuesta como JSON
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // 2. Validar que el ID no esté vacío
        if (empty($id)) {
            return ['success' => false, 'error' => 'ID del corporativo no proporcionado', 'data' => []];
        }

        try {
            // 3. Verificar que el corporativo existe
            $corporativo = Corporativo::findOne($id);
            if (!$corporativo) {
                return ['success' => false, 'error' => 'El corporativo con ID ' . $id . ' no existe', 'data' => []];
            }

            // 4. Consulta para obtener las clínicas asociadas al corporativo
            $clinicas = RmClinica::find()
                ->innerJoin('corporativo_clinica', 'corporativo_clinica.clinica_id = rm_clinica.id')
                ->where(['corporativo_clinica.corporativo_id' => $id])
                ->andWhere(['rm_clinica.estatus' => 'Activo'])
                ->all();

            if (empty($clinicas)) {
                return [
                    'success' => true,
                    'data' => [],
                    'message' => 'No se encontraron clínicas activas asociadas a este corporativo.'
                ];
            }

            // 5. Construir el array de datos con clínicas y sus planes
            $data = [];
            foreach ($clinicas as $clinica) {
                // Obtener planes asociados a esta clínica
                $planes = Planes::find()
                    ->where(['clinica_id' => $clinica->id])
                    ->andWhere(['estatus' => 'Activo'])
                    ->orderBy(['nombre' => SORT_ASC])
                    ->all();

                $planesData = [];
                foreach ($planes as $plan) {
                    $planesData[] = [
                        'id' => $plan->id,
                        'nombre' => $plan->nombre,
                        'precio' => $plan->precio,
                    ];
                }

                $data[] = [
                    'id' => $clinica->id,
                    'nombre' => $clinica->nombre,
                    'direccion' => $clinica->direccion,
                    'telefono' => $clinica->telefono,
                    'planes' => $planesData,
                ];
            }

            // 6. Respuesta exitosa
            return ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            Yii::error('Error al obtener clínicas con planes: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Obtiene la lista de planes asociados a un corporativo a través de sus clínicas.
     *
     * @param int $id ID del Corporativo
     * @return array JSON con la lista de planes y las fechas de vigencia del corporativo
     */
    public function actionObtenerPlanesPorCorporativo($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (empty($id)) {
            return ['success' => false, 'error' => 'ID del corporativo no proporcionado', 'data' => []];
        }

        try {
            $corporativo = Corporativo::findOne($id);
            if (!$corporativo) {
                return ['success' => false, 'error' => 'El corporativo no existe', 'data' => []];
            }

            // ============================================================
            // 4c-i: expose the corporate's APPLICABLE CONTRACT dates so the
            //       affiliate form can pre-fill its fecha_ven. The corporate
            //       itself no longer stores dates — they live on the corporate
            //       contract (one row per plan, one contract per year).
            //
            // Priority:
            //   1. The contract that is ACTIVE today (fecha_ini <= today <= fecha_ven)
            //   2. The contract anchored in the CURRENT calendar year
            //   3. None → both values are null (the caller falls back to a
            //      computed fecha_ini + 1 year − 1 day)
            // ============================================================
            $today       = date('Y-m-d');
            $currentYear = (int) date('Y');

            $contractForDates = ContratoCorporativo::find()
                ->where(['corporativo_id' => $id])
                ->andWhere(['!=', 'estatus', ContratoCorporativo::STATUS_ANULADO])
                ->andWhere(['<=', 'fecha_ini', $today])
                ->andWhere(['>=', 'fecha_ven', $today])
                ->orderBy(['fecha_ini' => SORT_DESC])
                ->one();

            if (!$contractForDates) {
                $contractForDates = ContratoCorporativo::find()
                    ->where(['corporativo_id' => $id])
                    ->andWhere(['!=', 'estatus', ContratoCorporativo::STATUS_ANULADO])
                    ->andWhere(['>=', 'fecha_ini', $currentYear . '-01-01'])
                    ->andWhere(['<=', 'fecha_ini', $currentYear . '-12-31'])
                    ->orderBy(['fecha_ini' => SORT_DESC])
                    ->one();
            }

            $corporateDates = [
                'fecha_ini' => $contractForDates ? $contractForDates->fecha_ini : null,
                'fecha_ven' => $contractForDates ? $contractForDates->fecha_ven : null,
            ];

            // Get all clinics linked to this corporate
            $clinicasIds = CorporativoClinica::find()
                ->select('clinica_id')
                ->where(['corporativo_id' => $id])
                ->column();

            if (empty($clinicasIds)) {
                return [
                    'success'          => true,
                    'data'             => [],
                    'message'          => 'Este corporativo no tiene clínicas asociadas.',
                    'corporate_dates'  => $corporateDates,
                ];
            }

            // Get all plans from those clinics
            $planes = Planes::find()
                ->select(['planes.id', 'planes.nombre', 'planes.precio', 'planes.clinica_id', 'rm_clinica.nombre as clinica_nombre'])
                ->innerJoin('rm_clinica', 'rm_clinica.id = planes.clinica_id')
                ->where(['planes.clinica_id' => $clinicasIds])
                ->andWhere(['planes.estatus' => 'Activo'])
                ->orderBy(['planes.nombre' => SORT_ASC])
                ->asArray()
                ->all();

            if (empty($planes)) {
                return [
                    'success'          => true,
                    'data'             => [],
                    'message'          => 'No hay planes activos en las clínicas asociadas.',
                    'corporate_dates'  => $corporateDates,
                ];
            }

            return [
                'success'          => true,
                'data'             => $planes,
                'corporate_dates'  => $corporateDates,
            ];
        } catch (\Exception $e) {
            Yii::error('Error al obtener planes por corporativo: ' . $e->getMessage(), __METHOD__);
            return [
                'success'          => false,
                'error'            => 'Error interno: ' . $e->getMessage(),
                'data'             => [],
                'corporate_dates'  => null,
            ];
        }
    }


    /** ---------------------------------------- Fin de Carga Masiva -------------------------------------- */


    /**
     * Deletes an existing Corporativo model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        // ========== NEW: Check if user has access to delete this corporate ==========
        if (!$this->userHasAccessToCorporativo($id)) {
            Yii::$app->session->setFlash('error', 'No tiene permiso para eliminar este corporativo.');
            return $this->redirect(['index']);
        }
        // ========== END OF NEW CHECK ==========

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Corporativo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Corporativo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Corporativo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // ========== NEW HELPER METHOD: Check if user has access to a corporate ==========
    /**
     * Checks if the current user has access to a specific corporate
     * @param int $corporativoId
     * @return bool
     */
    private function userHasAccessToCorporativo($corporativoId)
    {
        $userRole = UserHelper::getMyRol();

        // Admin has access to all
        if ($userRole === 'Administrador') {
            return true;
        }

        // If user has clinic role, check if corporate is linked to their clinic
        if (in_array($userRole, $this->allowedClinicaRoles)) {
            $clinicaId = UserHelper::getMyClinicaId();

            if (empty($clinicaId)) {
                return false;
            }

            // Check if the corporate is linked to the user's clinic
            $exists = CorporativoClinica::find()
                ->where(['corporativo_id' => $corporativoId, 'clinica_id' => $clinicaId])
                ->exists();

            return $exists;
        }

        // For other roles, you can define their access logic here
        // For now, default to true
        return true;
    }
    // ========== END OF NEW HELPER METHOD ==========

    /**
     * Displays debt and payment history for a corporativo.
     * @param int $id Corporativo ID
     * @return string
     * @throws NotFoundHttpException if the corporativo cannot be found
     */
    public function actionDeuda($id)
    {
        $model = $this->findModel($id);

        // Check if user has access
        if (!$this->userHasAccessToCorporativo($id)) {
            Yii::$app->session->setFlash('error', 'No tiene permiso para ver la deuda de este corporativo.');
            return $this->redirect(['index']);
        }

        // Get ALL user IDs for this corporate (BOTH methods)
        $allUserIds = $this->getAllCorporateUserIds($id);

        $allCuotas = [];
        $grandTotal = 0;

        if (!empty($allUserIds)) {
            // Use INNER JOIN approach - NOW INCLUDING 'pendiente' AND 'en_gracias'
            $allCuotas = \app\models\Cuotas::find()
                ->select('cuotas.*')
                ->innerJoinWith(['contrato' => function ($query) {
                    $query->innerJoinWith(['user']);
                    $query->andWhere(['!=', 'contratos.estatus', 'Anulado']);  // <-- ADD THIS LINE

                }])
                ->where(['contratos.user_id' => $allUserIds])
                ->andWhere(['in', 'cuotas.estatus', ['pendiente', 'en_gracias', 'vencida']])
                ->andWhere(['>', 'cuotas.monto', 0])
                ->orderBy([
                    'cuotas.fecha_vencimiento' => SORT_ASC,
                    'user_datos.nombres' => SORT_ASC,
                ])
                ->all();

            // Calculate grand total
            foreach ($allCuotas as $cuota) {
                $amount = floatval($cuota->monto);
                $grandTotal += $amount;
            }
        }

        // ===== Get payment history for this corporation =====
        $paymentHistory = \app\models\Pagos::find()
            ->where(['corporativo_id' => $id])
            ->andWhere(['tipo_pago' => 'corporativo'])
            ->orderBy(['fecha_pago' => SORT_DESC, 'created_at' => SORT_DESC])
            ->all();

        // Also get individual payments made by affiliates of this corporation
        $affiliatePaymentIds = \app\models\Pagos::find()
            ->select('id')
            ->where(['user_id' => $allUserIds])
            ->andWhere(['tipo_pago' => 'afiliado_corporativo'])
            ->column();

        $affiliatePayments = \app\models\Pagos::find()
            ->where(['id' => $affiliatePaymentIds])
            ->orderBy(['fecha_pago' => SORT_DESC, 'created_at' => SORT_DESC])
            ->all();

        // Calculate payment statistics
        $totalPaid = 0;
        $totalPayments = count($paymentHistory);
        foreach ($paymentHistory as $payment) {
            $totalPaid += floatval($payment->monto_pagado);
        }

        // Get last payment date
        $lastPaymentDate = null;
        if (!empty($paymentHistory)) {
            $lastPaymentDate = $paymentHistory[0]->fecha_pago;
        }

        Yii::debug("Corporate ID={$id}: Total users=" . count($allUserIds) .
            ", Total fees=" . count($allCuotas) .
            ", Total amount={$grandTotal}" .
            ", Total payments={$totalPayments}" .
            ", Total paid={$totalPaid}");

        return $this->render('deuda', [
            'corporativo' => $model,
            'allCuotas' => $allCuotas,
            'grandTotal' => $grandTotal,
            'paymentHistory' => $paymentHistory,
            'affiliatePayments' => $affiliatePayments,
            'totalPaid' => $totalPaid,
            'totalPayments' => $totalPayments,
            'lastPaymentDate' => $lastPaymentDate,
        ]);
    }

    /**
     * Helper method to get ALL user IDs for a corporate (both direct and indirect)
     */
    private function getAllCorporateUserIds($corporativoId)
    {
        // Cast INTEGER to TEXT/BIGINT for consistent comparison
        $directUserIds = CorporativoUser::find()
            ->select('CAST(user_id AS BIGINT) as user_id')
            ->where(['corporativo_id' => $corporativoId])
            ->column();

        $indirectUserIds = UserDatos::find()
            ->select('id')
            ->where(['afiliado_corporativo_id' => $corporativoId])
            ->column();

        // Convert all to strings for safe comparison
        $directUserIds = array_map('strval', $directUserIds);
        $indirectUserIds = array_map('strval', $indirectUserIds);

        return array_unique(array_merge($directUserIds, $indirectUserIds));
    }

    /**
     * Realiza un pago corporativo PARCIAL para cuotas específicas seleccionadas.
     * @param int $id Corporativo ID
     * @param string $cuotas Comma-separated list of cuota IDs
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the corporativo cannot be found
     */
    public function actionPagosParcial($id, $cuotas)
    {
        $corporativo = $this->findModel($id);
        $model = new Pagos();
        $model->loadDefaultValues();
        $model->user_id = null;
        $model->estatus = 'Por Conciliar';
        $model->tipo_pago = 'corporativo';

        // Parse comma-separated cuota IDs
        $selectedCuotaIds = explode(',', $cuotas);

        // Fetch only the selected cuotas
        $allCuotas = [];
        $grandTotal = 0;
        $userAmounts = [];

        if (!empty($selectedCuotaIds)) {
            foreach ($selectedCuotaIds as $cuotaId) {
                $cuota = Cuotas::find()
                    ->select('cuotas.*')
                    ->innerJoinWith(['contrato'])
                    ->where(['cuotas.id' => $cuotaId])
                    ->andWhere(['in', 'cuotas.estatus', ['pendiente', 'en_gracias', 'vencida']])
                    ->one();

                if ($cuota && $cuota->contrato) {
                    $userId = $cuota->contrato->user_id;

                    if (!isset($userAmounts[$userId])) {
                        $userAmounts[$userId] = 0;
                    }

                    $monto = $cuota->monto ?: 0;
                    if ($monto > 0) {
                        $allCuotas[] = $cuota;
                        $userAmounts[$userId] += $monto;
                        $grandTotal += $monto;
                    }
                }
            }
        }

        // --- FIXED: PROPERLY INITIALIZE TASA AND FECHA_PAGO ---
        $model->fecha_pago = date('Y-m-d'); // Set default date

        // Get current exchange rate and format it properly
        $currentTasa = $this->getTasaCambioReferencial();
        $model->tasa = number_format($currentTasa, 2, '.', '');

        // Pre-fill the payment amount with the calculated total
        $model->monto_pagado = $grandTotal;

        // Calculate initial monto_usd (Bs) based on the current rate
        if ($grandTotal > 0 && $currentTasa > 0) {
            $model->monto_usd = $grandTotal * $currentTasa;
        }
        // --- END FIX ---

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $montoPagadoPosted = (float)($model->monto_pagado ?: 0);

                if (abs($grandTotal - $montoPagadoPosted) > 0.01) {
                    $model->addError('monto_pagado', 'El monto a pagar debe coincidir con el total de cuotas seleccionadas.');
                    Yii::$app->session->setFlash('warning', 'El monto no coincide con el total de cuotas seleccionadas.');
                } else {
                    // ============================================================
                    // MANDATORY COMPROBANTE — hard server-side guard.
                    // ============================================================
                    $model->imagen_prueba_file = \yii\web\UploadedFile::getInstance($model, 'imagen_prueba_file');

                    if (!$model->imagen_prueba_file && empty($model->imagen_prueba)) {
                        Yii::$app->session->setFlash(
                            'error',
                            'Debe adjuntar el comprobante de pago antes de guardar el registro.'
                        );
                        return $this->render('pagos', [
                            'model'       => $model,
                            'corporativo' => $corporativo,
                            'allCuotas'   => $allCuotas,
                            'grandTotal'  => $grandTotal,
                            'isParcial'   => true,
                        ]);
                    }

                    // Handle file upload
                    if ($model->imagen_prueba_file) {
                        $folder = 'Pago';
                        $fileName = uniqid('pago_corp_') . '.' . $model->imagen_prueba_file->extension;
                        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                        if ($model->imagen_prueba_file->saveAs($tempFilePath)) {
                            $publicUrl = UserHelper::uploadFileToSupabaseApi(
                                $tempFilePath,
                                $model->imagen_prueba_file->type,
                                $fileName,
                                $folder
                            );

                            if (file_exists($tempFilePath)) {
                                unlink($tempFilePath);
                            }

                            if ($publicUrl) {
                                $model->imagen_prueba = $publicUrl;
                            }
                        }
                    }

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $model->corporativo_id = $corporativo->id;
                        $post = $this->request->post('Pagos');
                        $model->monto_usd = $post['monto_usd'] ?? null;

                        if ($model->save(false)) {
                            $mainPaymentId = $model->id;
                            $affiliatePaymentsCount = 0;
                            $affiliatePaymentMap = []; // userId => affiliatePaymentId

                            // Create individual payment records for each affiliate
                            foreach ($userAmounts as $userId => $userAmount) {
                                if ($userAmount > 0) {
                                    $affiliatePayment = new Pagos();

                                    // Copy only the safe attributes, excluding the ID
                                    $affiliatePayment->created_at = $model->created_at;
                                    $affiliatePayment->fecha_pago = $model->fecha_pago;
                                    $affiliatePayment->monto_pagado = $userAmount; // User-specific amount
                                    $affiliatePayment->metodo_pago = $model->metodo_pago;
                                    $affiliatePayment->estatus = $model->estatus;
                                    $affiliatePayment->numero_referencia_pago = $model->numero_referencia_pago;
                                    $affiliatePayment->imagen_prueba = $model->imagen_prueba;
                                    $affiliatePayment->tasa = $model->tasa;
                                    $affiliatePayment->monto_usd = $userAmount * $model->tasa; // Calculate user-specific amount in Bs
                                    $affiliatePayment->observacion = $model->observacion;

                                    // Set the relationship fields
                                    $affiliatePayment->user_id = $userId; // Specific affiliate
                                    $affiliatePayment->corporativo_id = $corporativo->id;
                                    $affiliatePayment->pago_corporativo_id = $mainPaymentId; // Link to main payment
                                    $affiliatePayment->tipo_pago = 'afiliado_corporativo';

                                    if ($affiliatePayment->save(false)) {
                                        $affiliatePaymentsCount++;
                                        $affiliatePaymentMap[$userId] = $affiliatePayment->id;
                                        \Yii::info("Created affiliate payment for user {$userId} with amount {$userAmount}, ID: {$affiliatePayment->id}");
                                    } else {
                                        \Yii::error("Failed to create affiliate payment for user {$userId}: " . print_r($affiliatePayment->errors, true));
                                        throw new \Exception("Failed to create affiliate payment for user {$userId}");
                                    }
                                }
                            }

                            $contratosActualizados = [];
                            $cuotasUpdatedCount = 0;

                            // Update cuotas - NOW USING INDIVIDUAL PAYMENT IDs
                            foreach ($allCuotas as $cuota) {
                                // Update ANY unpaid cuota (pendiente, en_gracias, or vencida)
                                if (in_array($cuota->estatus, ['pendiente', 'en_gracias', 'vencida'])) {
                                    $userId = $cuota->contrato->user_id;

                                    // Use the individual payment ID for this specific affiliate
                                    $affiliatePaymentId = $affiliatePaymentMap[$userId] ?? $mainPaymentId;

                                    // Get the individual payment for its specific rate
                                    $individualPayment = Pagos::findOne($affiliatePaymentId);
                                    $tasaCuota = $individualPayment ? ($individualPayment->monto_usd / $individualPayment->monto_pagado) : $model->tasa;

                                    $cuota->estatus = 'pagada';
                                    $cuota->fecha_pago = $individualPayment->fecha_pago ?? $model->fecha_pago;
                                    $cuota->rate_usd_bs = $tasaCuota;
                                    $cuota->id_pago = $mainPaymentId;

                                    if ($cuota->save(false)) {
                                        $cuotasUpdatedCount++;
                                        if (!in_array($cuota->contrato_id, $contratosActualizados)) {
                                            $contratosActualizados[] = $cuota->contrato_id;
                                        }
                                    }
                                }
                            }

                            // Update contract statuses using updateStatus() method
                            $contractsActivatedCount = 0;
                            foreach ($contratosActualizados as $contratoId) {
                                $contrato = Contratos::findOne($contratoId);
                                if ($contrato) {
                                    $oldStatus = $contrato->estatus;

                                    // Let updateStatus() handle all the rules
                                    $contrato->updateStatus();

                                    // Refresh to get the updated status
                                    $contrato->refresh();

                                    // Check if contract was activated
                                    if ($contrato->estatus === 'Activo' && $oldStatus !== 'Activo') {
                                        $contractsActivatedCount++;
                                        Yii::info("Contract #{$contratoId} activated from {$oldStatus} to Activo", 'corporativo');
                                    }
                                }
                            }

                            $transaction->commit();

                            Yii::$app->session->setFlash(
                                'success',
                                'Pago corporativo PARCIAL registrado exitosamente. ' .
                                    $affiliatePaymentsCount . ' afiliados procesados. ' .
                                    $cuotasUpdatedCount . ' cuotas actualizadas. ' .
                                    $contractsActivatedCount . ' contratos activados.'
                            );

                            return $this->redirect(['view', 'id' => $corporativo->id]);
                        } else {
                            throw new \Exception('Error al guardar el pago corporativo principal.');
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Error al procesar el pago: ' . $e->getMessage());
                    }
                }
            }
        }

        return $this->render('pagos', [
            'model' => $model,
            'corporativo' => $corporativo,
            'allCuotas' => $allCuotas,
            'grandTotal' => $grandTotal,
            'isParcial' => true, // Flag to indicate partial payment
        ]);
    }

    /**
     * Renders the off-canvas drawer used to generate the corporation-wide
     * contract PDF.
     *
     * The drawer shows:
     *   - A per-plan breakdown of employees, prices and subtotal
     *   - Two date inputs for the new vigencia
     *   - An info banner when a previous contract exists, informing the
     *     admin that the new contract will reuse the same affiliate set
     *
     * DATE PRE-FILL (per business rules):
     *   - If a previous (latest non-annulled) contract exists → suggested
     *     fecha_ini = previous.fecha_ven + 1 day  (no overlap)
     *   - Otherwise                                           → today
     *
     * GROUP SHAPE:
     *   Uses ContractHelper::buildContractGroupSummaries() so the groups
     *   exposed to the drawer have the same shape as those in
     *   actionContracts() — 'anchor', 'is_annulled', 'rows', etc.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionContractSelector($id)
    {
        $corporativo = $this->findModel($id);

        if (!$this->userHasAccessToCorporativo($id)) {
            throw new NotFoundHttpException('No tiene permiso para acceder a este corporativo.');
        }

        // ── 1. Load employees (as before) ───────────────────────────────────
        $empleados = $corporativo->getEmpleadosAsociados()
            ->with(['plan', 'clinica'])
            ->all();

        $totalEmpleados = count($empleados);
        $sinPlanCount   = 0;
        $grandTotal     = 0.0;

        $planBreakdown = [];
        foreach ($empleados as $empleado) {
            if (!$empleado->plan_id || !$empleado->plan) {
                $sinPlanCount++;
                continue;
            }

            $planId = (int)$empleado->plan_id;
            if (!isset($planBreakdown[$planId])) {
                $planBreakdown[$planId] = [
                    'plan'     => $empleado->plan,
                    'clinica'  => $empleado->clinica,
                    'count'    => 0,
                    'subtotal' => 0.0,
                ];
            }

            $precio = (float)$empleado->plan->precio;
            $planBreakdown[$planId]['count']++;
            $planBreakdown[$planId]['subtotal'] += $precio;
            $grandTotal += $precio;
        }

        uasort($planBreakdown, function ($a, $b) {
            return strcmp((string)$a['plan']->nombre, (string)$b['plan']->nombre);
        });

        // ── 2. Load contract groups using the SHARED summary builder ────────
        //  This ensures every entry has 'anchor', 'is_annulled', 'rows', etc.
        $flatContracts  = ContractHelper::findCorporateContracts($corporativo->id);
        $contractGroups = ContractHelper::buildContractGroupSummaries($flatContracts);

        // ── 3. Identify the latest non-annulled contract ────────────────────
        $previousContract  = null;
        $previousGroup     = null;
        $carriedAffiliates = [];

        if (!empty($contractGroups)) {
            $latestIniTs = null;
            foreach ($contractGroups as $group) {
                if (!empty($group['is_annulled'])) {
                    continue;
                }
                if (empty($group['anchor']->fecha_ini)) {
                    continue;
                }
                $ts = strtotime($group['anchor']->fecha_ini);
                if ($latestIniTs === null || $ts > $latestIniTs) {
                    $latestIniTs   = $ts;
                    $previousGroup = $group;
                }
            }

            if ($previousGroup !== null) {
                $previousContract = $previousGroup['anchor'];

                if (!empty($previousContract->nrocontrato)) {
                    $carriedAffiliates = ContractHelper::getPreviousContractAffiliates(
                        $corporativo->id,
                        $previousContract->nrocontrato
                    );
                }
            }
        }

        // ── 4. Compute suggested dates ──────────────────────────────────────
        if ($previousContract && !empty($previousContract->fecha_ven)) {
            // Start the day AFTER the previous contract expires (no overlap)
            $suggestedIni = date(
                'Y-m-d',
                strtotime($previousContract->fecha_ven . ' +1 day')
            );
        } else {
            // No previous contract → today
            $suggestedIni = date('Y-m-d');
        }

        $suggestedVen = date('Y-m-d', strtotime($suggestedIni . ' +1 year -1 day'));

        // ── 5. Detect if a current-year contract already exists ─────────────
        $currentYear    = (int)date('Y');
        $hasCurrentYear = $this->hasContractForYear($contractGroups, $currentYear);

        // ── 6. Compute carry-forward summary (per plan, for display) ────────
        $carriedByPlan = [];
        foreach ($carriedAffiliates as $emp) {
            if (!$emp->plan_id) {
                continue;
            }
            $planId = (int)$emp->plan_id;
            if (!isset($carriedByPlan[$planId])) {
                $carriedByPlan[$planId] = 0;
            }
            $carriedByPlan[$planId]++;
        }
        $contractCount = count($contractGroups);

        return $this->renderAjax('_contract_selector', [
            'corporativo'       => $corporativo,
            'planBreakdown'     => $planBreakdown,
            'totalEmpleados'    => $totalEmpleados,
            'sinPlanCount'      => $sinPlanCount,
            'grandTotal'        => $grandTotal,
            'suggestedIni'      => $suggestedIni,
            'suggestedVen'      => $suggestedVen,
            'hasCurrentYear'    => $hasCurrentYear,
            'previousContract'  => $previousContract,
            'previousGroup'     => $previousGroup,
            'carriedAffiliates' => $carriedAffiliates,
            'carriedByPlan'     => $carriedByPlan,
            'carriedTotal'      => count($carriedAffiliates),
            'contractCount'     => $contractCount,   // ← NEW

        ]);
    }


    /**
     * Handles the corporate-contract drawer submission AND the PDF request
     * from an existing row. The action distinguishes the two by the presence
     * of a `cid` query parameter:
     *
     *   ┌─────────────────────────────────────────────────────────────────┐
     *   │ POST /generar-contrato-pdf?id=8           (drawer → new contract)│
     *   │   → validates, persists, redirects to contracts?id=8 + flash    │
     *   ├─────────────────────────────────────────────────────────────────┤
     *   │ GET  /generar-contrato-pdf?id=8&cid=6    (row → existing PDF)   │
     *   │   → validates, persists (idempotent), renders the PDF inline    │
     *   └─────────────────────────────────────────────────────────────────┘
     *
     * Both paths run the same validation and persistence logic; only the
     * final step differs. Persistence is idempotent, so re-clicking the
     * PDF button on an existing row simply re-writes the same values.
     *
     * @param  int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionGenerarContratoPdf($id)
    {
        $corporativo = $this->findModel($id);

        if (!$this->userHasAccessToCorporativo($id)) {
            Yii::$app->session->setFlash('error', 'No tiene permiso para generar el contrato de este corporativo.');
            return $this->redirect(['index']);
        }

        // ── 1. Read POST dates (drawer flow) ────────────────────────────────
        $fechaIni = trim((string)$this->request->post('fecha_ini', ''));
        $fechaVen = trim((string)$this->request->post('fecha_ven', ''));

        // ── 2. FALLBACK: resolve from a persisted contract ──────────────────
        //  Used when the PDF link is clicked from the actions rail (a GET
        //  with no POST body). We anchor on the row indicated by `cid`,
        //  or — if `cid` is missing — on the most recent non-annulled contract.
        if ($fechaIni === '' || $fechaVen === '') {
            $anchorId = (int)$this->request->get('cid', 0);

            $anchor = $anchorId
                ? ContratoCorporativo::findOne([
                    'id'             => $anchorId,
                    'corporativo_id' => $corporativo->id,
                ])
                : ContratoCorporativo::find()
                ->where(['corporativo_id' => $corporativo->id])
                ->andWhere(['!=', 'estatus', ContratoCorporativo::STATUS_ANULADO])
                ->orderBy(['fecha_ini' => SORT_DESC, 'id' => SORT_ASC])
                ->one();

            if ($anchor) {
                if ($fechaIni === '' && !empty($anchor->fecha_ini)) {
                    $fechaIni = (string)$anchor->fecha_ini;
                }
                if ($fechaVen === '' && !empty($anchor->fecha_ven)) {
                    $fechaVen = (string)$anchor->fecha_ven;
                }
            }
        }

        // ── 3. Validate dates ───────────────────────────────────────────────
        if ($fechaIni === '' || $fechaVen === '') {
            Yii::$app->session->setFlash(
                'error',
                'No se pudo determinar la vigencia del contrato. Regístrelo primero desde el botón "Nuevo Contrato".'
            );
            return $this->redirect(['view', 'id' => $id]);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaIni) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaVen)) {
            Yii::$app->session->setFlash('error', 'Las fechas deben tener el formato AAAA-MM-DD.');
            return $this->redirect(['view', 'id' => $id]);
        }

        if ($fechaVen <= $fechaIni) {
            Yii::$app->session->setFlash('error', 'La fecha de vencimiento debe ser posterior a la fecha de inicio.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // ── 3.5. Overlap guard ──────────────────────────────────────────────
        //  A corporation's non-annulled contracts may not overlap in time.
        //  When re-persisting an existing contract (cid present), exclude
        //  ALL of its sibling rows from the check.
        $anchorIdToExclude = (int)$this->request->get('cid', 0) ?: null;
        $excludeIds        = [];

        if ($anchorIdToExclude) {
            $anchorRow = ContratoCorporativo::findOne($anchorIdToExclude);
            if ($anchorRow) {
                $siblings = ContractHelper::findCorporateContractGroup(
                    (int)$corporativo->id,
                    $anchorRow->nrocontrato
                );
                $excludeIds = array_map(fn($r) => (int)$r->id, $siblings);
            }
        }

        $overlapCheck = ContractHelper::validateNoOverlap(
            (int)$corporativo->id,
            $fechaIni,
            $excludeIds
        );

        if ($overlapCheck !== null) {
            Yii::$app->session->setFlash('error', $overlapCheck['message']);
            return $this->redirect(['contracts', 'id' => $id]);
        }

        // ============================================================
        // ── 4. Load ACTIVE employees only, grouped by plan ─────────
        // ------------------------------------------------------------
        // Business rule: the corporate contract PDF must list ONLY
        // affiliates who currently have at least one non-annulled
        // contract. Affiliates whose only contracts are annulled
        // (or who have no contract at all) are excluded.
        //
        // This mirrors the exact logic used by:
        //   - Corporativo::getActiveAffiliates()
        //   - the "Afiliados Activos" KPI on corporativo/index.php
        //   - the "Afiliados Activos" section in corporativo/view.php
        //
        // The filter is applied BEFORE grouping by plan, so a plan
        // whose entire affiliate set has been annulled will simply
        // not appear in the PDF.
        // ============================================================
        $allEmpleados = $corporativo->getEmpleadosAsociados()
            ->alias('ud')
            ->with(['plan', 'clinica'])
            ->where([
                'EXISTS',
                (new \yii\db\Query())
                    ->from(['c' => 'contratos'])
                    ->where('c.user_id = ud.id')
                    ->andWhere(['<>', 'c.estatus', Contratos::STATUS_ANULADO])
            ])
            ->orderBy(['ud.apellidos' => SORT_ASC, 'ud.nombres' => SORT_ASC])
            ->all();

        $employeesByPlan = [];
        foreach ($allEmpleados as $emp) {
            if (!$emp->plan_id || !$emp->plan) {
                continue;
            }
            $employeesByPlan[(int)$emp->plan_id][] = $emp;
        }

        if (empty($employeesByPlan)) {
            Yii::$app->session->setFlash('warning', 'Ninguno de los afiliados de este corporativo tiene un plan asignado.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // ── 4.5. Carry-forward: if a previous contract exists, reuse its affiliate set ──
        //  This is the rule: a new-year contract inherits the exact same affiliate
        //  set as the previous (expired) contract, not whoever is currently linked.
        $previousAffiliates = [];

        if (empty($anchorIdToExclude)) {
            // Only do this on CREATE (no `cid`) — the PDF path reuses the same set anyway
            $latestGroup = null;
            $latestIniTs = null;

            foreach (
                ContractHelper::buildContractGroupSummaries(
                    ContractHelper::findCorporateContracts($corporativo->id)
                ) as $group
            ) {
                if (!empty($group['is_annulled']) || empty($group['anchor']->fecha_ini)) {
                    continue;
                }
                $ts = strtotime($group['anchor']->fecha_ini);
                if ($latestIniTs === null || $ts > $latestIniTs) {
                    $latestIniTs = $ts;
                    $latestGroup = $group;
                }
            }

            if ($latestGroup !== null && !empty($latestGroup['anchor']->nrocontrato)) {
                $previousAffiliates = ContractHelper::getPreviousContractAffiliates(
                    $corporativo->id,
                    $latestGroup['anchor']->nrocontrato
                );
            }
        }

        // If we have a carried-forward set, group it by plan and prefer it
        // over the freshly-queried employeesByPlan.
        if (!empty($previousAffiliates)) {
            $carriedByPlan = [];
            foreach ($previousAffiliates as $emp) {
                if (!$emp->plan_id) {
                    continue;
                }

                // ── Skip affiliates whose only contracts are annulled ──
                $hasLive = Contratos::find()
                    ->where(['user_id' => $emp->id])
                    ->andWhere(['<>', 'estatus', Contratos::STATUS_ANULADO])
                    ->exists();

                if (!$hasLive) {
                    continue;
                }
                // ────────────────────────────────────────────────────

                $carriedByPlan[(int)$emp->plan_id][] = $emp;
            }
            if (!empty($carriedByPlan)) {
                $employeesByPlan = $carriedByPlan;
            }
        }

        // ── 5. Persist one ContratoCorporativo row per plan ────────────────
        $persistedContracts = [];
        $failedPlans        = [];

        foreach ($employeesByPlan as $planId => $empleados) {
            $plan = $empleados[0]->plan ?? null;
            if (!$plan) {
                continue;
            }

            $contractModel = ContractHelper::persistCorporateContract(
                $corporativo,
                $plan,
                $empleados,
                $fechaIni,
                $fechaVen,
                $empleados   // ← affiliate override (carry-forward)
            );

            if ($contractModel === null) {
                $failedPlans[] = $plan->nombre;
                continue;
            }

            $persistedContracts[$planId] = $contractModel;
        }

        if (empty($persistedContracts)) {
            Yii::$app->session->setFlash(
                'error',
                'No se pudo registrar ningún contrato corporativo. Revise los registros de error.'
            );
            return $this->redirect(['view', 'id' => $id]);
        }

        if (!empty($failedPlans)) {
            Yii::$app->session->setFlash(
                'warning',
                'Algunos planes no se pudieron registrar: ' . implode(', ', $failedPlans)
            );
        }

        // ── 6. Anchor contract (all siblings share these) ───────────────────
        $anchor         = reset($persistedContracts);
        $contractNumber = $anchor->nrocontrato;

        // ── 7. Build per-plan sections (only needed for the PDF path) ──────
        $planSections    = [];
        $grandSubtotal   = 0.0;
        $grandAffiliates = 0;

        foreach ($persistedContracts as $planId => $contractModel) {
            $plan      = $contractModel->plan;
            $empleados = $employeesByPlan[$planId];

            // ============================================================
            // PLAN SERVICES — enriched with continuous-coverage P/E
            // ------------------------------------------------------------
            // Corporate affiliates still have their own individual
            // Contratos rows (one per plan, linked via
            // ContratoCorporativoAfiliado), so the CoverageHelper chain
            // logic works exactly the same as for individual affiliates.
            //
            // ANCHOR CHOICE: we use the EARLIEST-JOINED affiliate in this
            // plan group as the representative, so "Sin espera" is only
            // shown when the ENTIRE group has been continuously covered
            // long enough. Using $empleados[0] directly would be
            // non-deterministic (depends on query order) and could surface
            // "Sin espera" for a service the group as a whole hasn't
            // reached yet.
            // ============================================================
            $planServices = [];
            if ($plan->id) {

                // ── Pick the earliest-joined affiliate as the anchor ──
                $earliestEmployee = null;
                foreach ($empleados as $emp) {
                    if (
                        $earliestEmployee === null
                        || strtotime((string)$emp->created_at) < strtotime((string)$earliestEmployee->created_at)
                    ) {
                        $earliestEmployee = $emp;
                    }
                }

                $representativeUserId     = $earliestEmployee ? (int)$earliestEmployee->id : null;
                $representativeContractId = null;

                // ── Look up the affiliate's individual contract that matches
                //    this corporate contract's vigencia. Fallback to the most
                //    recent non-annulled individual contract if not found. ──
                if ($representativeUserId) {
                    $repContract = Contratos::find()
                        ->where(['user_id' => $representativeUserId])
                        ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                        ->andWhere(['fecha_ini' => $contractModel->fecha_ini])
                        ->andWhere(['fecha_ven' => $contractModel->fecha_ven])
                        ->orderBy(['id' => SORT_DESC])
                        ->one();

                    if (!$repContract) {
                        $repContract = Contratos::find()
                            ->where(['user_id' => $representativeUserId])
                            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                            ->orderBy(['fecha_ini' => SORT_DESC, 'id' => SORT_DESC])
                            ->one();
                    }

                    if ($repContract) {
                        $representativeContractId = (int)$repContract->id;
                    }
                }

                // ── Load the plan's services and enrich each with its display state ──
                $dbServices = \app\models\PlanServicios::getServicesForPlan($plan->id, $plan->clinica_id);

                foreach ($dbServices as $svc) {

                    $esperaDisplay = $svc->plazo_espera ?? 'NO APLICA';
                    $esperaStatus  = 'raw';

                    if ($representativeUserId && $representativeContractId) {
                        $plazoDisplay = \app\components\CoverageHelper::getPlazoEsperaDisplay(
                            $representativeUserId,
                            $representativeContractId,
                            $svc->plazo_espera,
                            (string)($svc->servicio_nombre ?? '')
                        );

                        $esperaDisplay = $plazoDisplay['label'];
                        $esperaStatus  = $plazoDisplay['status'];
                    }

                    $planServices[] = [
                        'servicio'       => $svc->servicio_nombre ?? '',
                        'espera'         => $svc->plazo_espera ?? 'NO APLICA',
                        'espera_display' => $esperaDisplay,
                        'espera_status'  => $esperaStatus,
                        'descripcion'    => $svc->descripcion ?? 'Servicio incluido en el plan',
                    ];
                }
            }

            $empleadosData = [];
            $subtotal      = 0.0;
            $precioPlan    = (float)($plan->precio ?? 0);

            foreach ($empleados as $emp) {
                $empleadosData[] = [
                    'id'              => $emp->id,
                    'nombre_completo' => trim($emp->nombres . ' ' . $emp->apellidos),
                    'cedula'          => ($emp->tipo_cedula ?: 'V') . '-' . ($emp->cedula ?: 'N/A'),
                    'fechanac'        => $emp->fechanac,
                    'sexo'            => $emp->sexo,
                    'email'           => $emp->email,
                    'telefono'        => $emp->telefono,
                    'precio'          => $precioPlan,
                ];
                $subtotal += $precioPlan;
            }

            $planCobertura          = $plan->cobertura ?? 'N/A';
            $planCoberturaFormatted = ($planCobertura !== 'N/A' && $planCobertura > 0)
                ? '$' . number_format((float)$planCobertura, 2, ',', '.')
                : 'N/A';

            $planSections[] = [
                'contract_id'         => $contractModel->id,
                'plan_name'           => $plan->nombre ?: 'N/A',
                'plan_currency'       => !empty($plan->moneda) ? $plan->moneda : 'USD',
                'plan_deductible'     => !empty($plan->deducible) ? $plan->deducible : 'NO APLICA',
                'plan_coverage_limit' => $planCoberturaFormatted,
                'plan_services'       => $planServices,
                'exclusiones'         => $plan->exclusiones ?? '',
                'empleados'           => $empleadosData,
                'precio_plan'         => $precioPlan,
                'subtotal'            => $subtotal,
                'count'               => count($empleadosData),
            ];

            $grandSubtotal   += $subtotal;
            $grandAffiliates += count($empleadosData);
        }

        // ── 8. Decide the tail behavior ─────────────────────────────────────
        //  cid present  → render the PDF for the requested row
        //  no cid       → the drawer just created a contract; redirect + flash
        $cid          = (int)$this->request->get('cid', 0);
        $isPdfRequest = ($cid > 0);

        if (!$isPdfRequest) {
            // ── Drawer flow: contract created. Redirect to the list. ──
            Yii::$app->session->setFlash(
                'success',
                'Contrato ' . Html::encode($contractNumber) . ' creado correctamente. '
                    . 'Puede descargar el PDF desde el botón rojo de la fila correspondiente.'
            );

            return $this->redirect(['contracts', 'id' => $id]);
        }

        // ── PDF button flow: build and stream the PDF ──────────────────────
        $logo   = Yii::getAlias('@webroot/img/sispsalogo.jpg');
        $firmas = Yii::getAlias('@webroot/img/firmas.png');


        // Receipt number (provisional)
        $receipt = Receipt::find()
            ->where(['user_id' => null])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        $receiptNumber = $receipt
            ? $receipt->receipt_number
            : Receipt::generateReceiptNumber();

        // Location + intermediary
        $estadoNombre    = $corporativo->getRmEstado()->one()->nombre ?? '';
        $municipioNombre = $corporativo->getRmMunicipio()->one()->nombre ?? '';
        $parroquiaNombre = $corporativo->getRmParroquia()->one()->nombre ?? '';
        $ciudadNombre    = $corporativo->getRmCiudad()->one()->nombre ?? '';

        $addressParts = [];
        if (!empty($corporativo->direccion)) $addressParts[] = $corporativo->direccion;
        if (!empty($parroquiaNombre))        $addressParts[] = $parroquiaNombre;
        if (!empty($municipioNombre))        $addressParts[] = $municipioNombre;
        if (!empty($ciudadNombre))           $addressParts[] = $ciudadNombre;
        if (!empty($estadoNombre))           $addressParts[] = $estadoNombre;
        $fullCorporateAddress = implode(', ', array_filter($addressParts));

        $intermediarioNombre = '';
        $intermediarioCodigo = '';
        $intermediarioCi     = '';

        $firstAsesorId = null;
        foreach ($allEmpleados as $emp) {
            if (!empty($emp->asesor_id)) {
                $firstAsesorId = $emp->asesor_id;
                break;
            }
        }

        if ($firstAsesorId) {
            $agenteFuerza = AgenteFuerza::findOne($firstAsesorId);
            if ($agenteFuerza) {
                $asesorUserDatos = UserDatos::findOne($agenteFuerza->idusuario);
                if ($asesorUserDatos) {
                    $intermediarioNombre = trim($asesorUserDatos->nombres . ' ' . $asesorUserDatos->apellidos);
                    $intermediarioCodigo = $agenteFuerza->registro_corredor_actividad_aseguradora ?? '';
                    $intermediarioCi     = ($asesorUserDatos->tipo_cedula ?? '') . '-' . ($asesorUserDatos->cedula ?? '');
                }
            }
        }

        $data = [
            'contract_number'          => $contractNumber,
            'contract_status'          => $anchor->estatus,
            'contract_start_date'      => $anchor->fecha_ini,
            'contract_end_date'        => $anchor->fecha_ven,
            'fecha_emision'            => date('d/m/Y'),
            'receipt_number'           => $receiptNumber,
            'affiliation_type_name'    => 'COLECTIVO',
            'tipo_persona_contratante' => 'Jurídica',

            'corporate_name'              => $corporativo->nombre,
            'corporate_rif'               => $corporativo->rif,
            'corporate_email'             => $corporativo->email,
            'corporate_phone'             => $corporativo->telefono,
            'corporate_address'           => $fullCorporateAddress ?: $corporativo->direccion,
            'corporate_domicilio_fiscal'  => $corporativo->domicilio_fiscal,
            'corporate_lugar_registro'    => $corporativo->lugar_registro,
            'corporate_fecha_registro'    => $corporativo->fecha_registro_mercantil,
            'corporate_tomo'              => $corporativo->tomo_registro,
            'corporate_folio'             => $corporativo->folio_registro,
            'corporate_contacto_nombre'   => $corporativo->contacto_nombre,
            'corporate_contacto_cedula'   => $corporativo->contacto_cedula,
            'corporate_contacto_telefono' => $corporativo->contacto_telefono,
            'corporate_contacto_cargo'    => $corporativo->contacto_cargo,

            'corporate_legal_representative_name'                 => $corporativo->nombre_representante,
            'corporate_legal_representative_ci'                   => $corporativo->cedula_representante,
            'corporate_legal_representative_nationality'          => $corporativo->nacionalidad_representante,
            'corporate_legal_representative_marital_status'       => $corporativo->estado_civil_representante,
            'corporate_legal_representative_birthplace'           => $corporativo->lugar_nacimiento_representante,
            'corporate_legal_representative_birthdate'            => $corporativo->fecha_nacimiento_representante,
            'corporate_legal_representative_sex'                  => $corporativo->sexo_representante,
            'corporate_legal_representative_profession'           => $corporativo->profesion_representante,
            'corporate_legal_representative_occupation'           => $corporativo->ocupacion_representante,
            'corporate_legal_representative_activity_description' => $corporativo->descripcion_actividad_representante,
            'corporate_legal_representative_address'              => $corporativo->direccion_representante,
            'corporate_legal_representative_phone'                => $corporativo->telefono_representante,

            'corporate_economic_activity' => $corporativo->actividad_economica,
            'corporate_products_services' => $corporativo->productos_servicios,
            'corporate_profit'            => $corporativo->utilidad_ejercicio_anterior,
            'corporate_equity'            => $corporativo->patrimonio,

            'plan_sections'    => $planSections,
            'total_plan_count' => count($planSections),
            'total_empleados'  => $grandAffiliates,
            'grand_subtotal'   => $grandSubtotal,

            'intermediary_name' => $intermediarioNombre,
            'intermediary_code' => $intermediarioCodigo,
            'intermediary_ci'   => $intermediarioCi,

            'declaration_place'      => $ciudadNombre,
            'declaration_date'       => date('d/m/Y'),
            'declaration_date_long'  => \Yii::$app->formatter->asDate('now', 'php:j \d\e F \d\e Y'),
        ];

        $content = $this->renderPartial('_contrato_corporativo_pdf', [
            'data'   => $data,
            'logo'   => $logo,
            'firmas' => $firmas,
        ]);

        $url_css = Yii::getAlias('@webroot') . '/css/affiliation-pdf.css';

        $pdf = new Pdf([
            'mode'        => Pdf::MODE_UTF8,
            'format'      => Pdf::FORMAT_LETTER,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'content'     => $content,
            'cssFile'     => $url_css,
            'options'     => [
                'title' => 'Contrato Corporativo - ' . $corporativo->nombre,
            ],
            'methods' => [
                'SetHeader' => false,
                'SetFooter' => ['{PAGENO}'],
            ],
        ]);

        return $pdf->render();
    }

    /**
     * Renders the annul form for a corporate contract.
     *
     * GET  → shows the confirmation page (anular-contrato-corporativo.php)
     * POST → performs the annulment and redirects back to the corporate view
     *
     * @param  int $id  Any ContratoCorporativo row of the group.
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionAnularContratoCorporativo($id)
    {
        $contract = ContratoCorporativo::findOne($id);

        if (!$contract) {
            throw new NotFoundHttpException('El contrato corporativo no existe.');
        }

        $corporativoId = (int)$contract->corporativo_id;

        if (!$this->userHasAccessToCorporativo($corporativoId)) {
            Yii::$app->session->setFlash('error', 'No tiene permiso para anular este contrato corporativo.');
            return $this->redirect(['index']);
        }

        // ── POST: perform the annulment ────────────────────────────────────
        if ($this->request->isPost) {
            $motivo = trim((string)$this->request->post('motivo', ''));

            if ($motivo === '') {
                Yii::$app->session->setFlash('error', 'El motivo de la anulación es obligatorio.');
                return $this->redirect(['anular-contrato-corporativo', 'id' => $contract->id]);
            }

            $ok = ContractHelper::annulCorporateContract($contract->id, $motivo);

            if ($ok) {
                Yii::$app->session->setFlash(
                    'success',
                    'El contrato corporativo ' . $contract->nrocontrato . ' ha sido anulado correctamente.'
                );
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    'No se pudo anular el contrato corporativo. Revise los registros de error.'
                );
            }

            return $this->redirect(['view', 'id' => $corporativoId]);
        }

        // ── GET: render the confirmation form ──────────────────────────────
        return $this->render('anular-contrato-corporativo', [
            'contract'    => $contract,
            'siblings'    => ContractHelper::findCorporateContractGroup(
                $corporativoId,
                $contract->nrocontrato
            ),
        ]);
    }
}
