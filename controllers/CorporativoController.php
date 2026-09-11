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
     * Lists all Contratos models for users related to a specific Corporativo.
     * @param int $id Corporativo ID
     * @return string
     * @throws NotFoundHttpException if the corporativo cannot be found
     */
    public function actionContracts($id)
    {
        $corporativo = $this->findModel($id);

        // Obtener IDs de usuarios asociados al corporativo
        $userIds = CorporativoUser::find()
            ->select('user_id')
            ->where(['corporativo_id' => $id])
            ->column();

        $searchModel = new ContratosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        // Filtrar contratos por usuarios asociados
        if (!empty($userIds)) {
            $dataProvider->query->andWhere(['in', 'user_id', $userIds]);
        } else {
            // Si no hay usuarios, no mostrar contratos
            $dataProvider->query->where('0=1');
        }

        return $this->render('contracts', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'corporativo' => $corporativo,
        ]);
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
                    // Handle file upload
                    $model->imagen_prueba_file = \yii\web\UploadedFile::getInstance($model, 'imagen_prueba_file');

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
                                    //$affiliatePayment->recibo_id = $model->recibo_id;
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
                                    $cuota->id_pago = $mainPaymentId;; // Link to INDIVIDUAL payment

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


// ------------------- Controlador de carga masiva de corporativos -------------------------

    /**
     * Acción principal para mostrar y procesar el formulario de carga masiva de afiliados.
     * @return string|\yii\web\Response
     */
    public function actionCargaMasivaAfiliados()
    {
        $model = new MasivoAfiliadosForm();

        // 1. Obtener los corporativos activos para el dropdown
        $corporativosData = Corporativo::find()
            ->select(['id', 'nombre'])
            ->where(['estatus' => 'Activo'])
            ->asArray()
            ->all();

        $corporativos = ArrayHelper::map($corporativosData, 'id', 'nombre');

        // Get plans for dropdown (initially empty, loaded via AJAX)
        $planes = [];

        // Get asesores list (only active ones)
        $asesores = UserHelper::getAgenteFuerzaList();

        if ($model->load(Yii::$app->request->post())) {
            $model->masivoFile = UploadedFile::getInstance($model, 'masivoFile');

            if ($model->validate()) {
                $filePath = $model->masivoFile->tempName;
                $corporativoId = $model->corporativo_id;
                $planId = $model->plan_id;
                $asesorId = $model->asesor_id;

                // Get the selected plan to determine the clinic
                $plan = Planes::findOne($planId);
                if (!$plan) {
                    Yii::$app->session->setFlash('error', 'El plan seleccionado no existe.');
                    return $this->render('carga-masiva-afiliados', [
                        'model' => $model,
                        'corporativos' => $corporativos,
                        'planes' => $planes,
                        'asesores' => $asesores,
                    ]);
                }

                $resultados = $this->procesarCSV(
                    $filePath,
                    $corporativoId,
                    $model->fecha_ini,
                    $model->fecha_ven,
                    $planId,        // Pass the plan ID
                    $plan->clinica_id, // Pass the clinic ID from the plan
                    $asesorId       // Pass the asesor ID (may be null)
                );

                // 3. Mostrar el resumen del proceso
                $errorCount = count($resultados['errors']);
                $successCount = $resultados['successCount'];

                $messageType = $errorCount > 0 ? 'warning' : 'success';
                $messageText = 'Proceso de carga finalizado con ' .
                    $successCount . ' éxitos y ' .
                    $errorCount . ' errores.' .
                    ($errorCount > 0 ? ' Revise el detalle.' : ' Todo cargado correctamente.');

                Yii::$app->session->setFlash($messageType, $messageText);

                // Get the corporativo model for the view
                $corporativoModel = Corporativo::findOne($corporativoId);

                return $this->render('carga-masiva-resumen', [
                    'model' => $model,
                    'resultados' => $resultados,
                    'corporativo' => $corporativoModel,
                    'selectedPlan' => $plan,
                ]);
            } else {
                $validationErrors = ArrayHelper::flatten($model->getErrors());
                Yii::$app->session->setFlash(
                    'error',
                    'Error de validación del formulario de carga: ' . implode('; ', $validationErrors)
                );
            }
        }

        // Mostrar el formulario inicial
        return $this->render('carga-masiva-afiliados', [
            'model' => $model,
            'corporativos' => $corporativos,
            'planes' => $planes,
            'asesores' => $asesores,
        ]);
    }

    /**
     * Lógica principal para leer el archivo CSV y procesar los afiliados,
     * asegurando el cumplimiento de las reglas de validación de UserDatos.
     * 
     * ACTUALIZACIÓN IMPORTANTE: 
     * - plan_id, clinica_id y asesor_id ya NO se leen del CSV
     * - Se reciben como parámetros del formulario
     * - Genera 12 cuotas por afiliado utilizando el método generateCuotasAnniversaryBased()
     * - Validaciones mejoradas para manejar datos del mundo real (teléfonos sin 0, fechas mixtas, etc.)
     * 
     * @param string $filePath Ruta temporal del archivo CSV.
     * @param int $corporativoId ID del corporativo destino.
     * @param string $fechaIniGlobal Fecha de inicio del contrato.
     * @param string $fechaVenGlobal Fecha de vencimiento del contrato.
     * @param int $planId ID del plan seleccionado en el formulario.
     * @param int $clinicaId ID de la clínica asociada al plan.
     * @param int|null $asesorId ID del asesor seleccionado (opcional).
     * @return array Array con el conteo de éxitos, los errores encontrados y detalles de éxito.
     */
    private function procesarCSV($filePath, $corporativoId, $fechaIniGlobal, $fechaVenGlobal, $planId, $clinicaId, $asesorId = null)
    {
        $handle = fopen($filePath, "r");
        if ($handle === false) {
            return [
                'successCount' => 0,
                'errors' => ['No se pudo abrir el archivo.'],
                'successDetails' => []
            ];
        }

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

                // === VALIDACIÓN DE DUPLICADOS ===
                if (UserDatos::find()->where(['cedula' => $cedulaLimpia])->exists()) {
                    throw new \Exception("Ya existe un afiliado con la cédula {$cedulaLimpia} registrado.");
                }

                if (User::find()->where(['email' => $email])->exists()) {
                    throw new \Exception("Ya existe un usuario con el email {$email} registrado.");
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

                // === CREAR USER LOGIN ===
                $userLogin = new User();
                $userLogin->email = $email;
                $userLogin->username = $email;
                $userLogin->password_hash = Yii::$app->security->generatePasswordHash(Yii::$app->security->generateRandomString(12));
                $userLogin->generateAuthKey();
                $userLogin->status = 10;

                if (!$userLogin->save()) {
                    Yii::error(['Error_User_Login' => $userLogin->getErrors()], __METHOD__);
                    throw new \Exception('Error al crear User Login: ' . implode(', ', ArrayHelper::flatten($userLogin->getErrors())));
                }

                // === CREAR AFILIADO (UserDatos) ===
                $afiliado = new UserDatos();
                $afiliado->user_login_id = $userLogin->id;

                // Datos básicos
                $afiliado->tipo_cedula = trim($data[$headerMap['tipo_cedula']]);
                $afiliado->cedula = $cedulaLimpia;
                $afiliado->nombres = trim($data[$headerMap['nombres']]);
                $afiliado->apellidos = trim($data[$headerMap['apellidos']]);

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

                $afiliado->fechanac = $dateObject->format('Y-m-d');

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
                $afiliado->created_at = date('Y-m-d H:i:s');
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

                // === GENERAR 12 CUOTAS ===
                Yii::info("Generando 12 cuotas para el contrato #{$modelContrato->id} del afiliado {$afiliado->cedula}", __METHOD__);

                $cuotaGenerationResult = Cuotas::generateCuotasAnniversaryBased(
                    $modelContrato->id,
                    $modelContrato->fecha_ini,
                    $modelContrato->monto
                );

                if (!$cuotaGenerationResult['success']) {
                    throw new \Exception('Error al generar las 12 cuotas: ' . $cuotaGenerationResult['error']);
                }

                $cuotasGeneradas = count($cuotaGenerationResult['cuotas']);
                Yii::info(
                    "✅ Generadas {$cuotasGeneradas} cuotas para el contrato #{$modelContrato->id} " .
                        "(Afiliado: {$afiliado->cedula} - {$afiliado->nombres} {$afiliado->apellidos})",
                    __METHOD__
                );

                // === CREAR RELACIÓN CORPORATIVO USER ===
                $corporativoUser = new CorporativoUser();
                $corporativoUser->corporativo_id = $corporativoId;
                $corporativoUser->user_id = $afiliado->id;
                $corporativoUser->fecha_vinculacion = new Expression('NOW()');

                if (!$corporativoUser->save()) {
                    Yii::error(['Error_CorporativoUser' => $corporativoUser->getErrors()], __METHOD__);
                    throw new \Exception('Error al vincular con CorporativoUser: ' . implode(', ', ArrayHelper::flatten($corporativoUser->getErrors())));
                }

                // === ASIGNAR ROL ===
                $auth = Yii::$app->authManager;
                $role = $auth->getRole('afiliado');
                if ($role) {
                    $auth->assign($role, $userLogin->id);
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
     * @return array JSON con la lista de planes
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

            // Get all clinics linked to this corporate
            $clinicasIds = CorporativoClinica::find()
                ->select('clinica_id')
                ->where(['corporativo_id' => $id])
                ->column();

            if (empty($clinicasIds)) {
                return ['success' => true, 'data' => [], 'message' => 'Este corporativo no tiene clínicas asociadas.'];
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
                return ['success' => true, 'data' => [], 'message' => 'No hay planes activos en las clínicas asociadas.'];
            }

            return ['success' => true, 'data' => $planes];
        } catch (\Exception $e) {
            Yii::error('Error al obtener planes por corporativo: ' . $e->getMessage(), __METHOD__);
            return ['success' => false, 'error' => 'Error interno: ' . $e->getMessage(), 'data' => []];
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
                    // Handle file upload
                    $model->imagen_prueba_file = \yii\web\UploadedFile::getInstance($model, 'imagen_prueba_file');

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
                                    //$affiliatePayment->recibo_id = $model->recibo_id;
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
     * Process CSV upload with progress tracking via AJAX
     */
    public function actionCargaMasivaAfiliadosProgress()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = new MasivoAfiliadosForm();
        $model->load(Yii::$app->request->post());

        if (!$model->validate()) {
            return [
                'success' => false,
                'errors' => $model->getErrors()
            ];
        }

        // Get the uploaded file
        $model->masivoFile = UploadedFile::getInstance($model, 'masivoFile');
        if (!$model->masivoFile) {
            return [
                'success' => false,
                'error' => 'No se recibió ningún archivo.'
            ];
        }

        // Process the CSV with progress tracking
        $filePath = $model->masivoFile->tempName;
        $resultados = $this->procesarCSVWithProgress(
            $filePath,
            $model->corporativo_id,
            $model->fecha_ini,
            $model->fecha_ven,
            $model->plan_id,
            null, // clinica_id will be determined from plan
            $model->asesor_id
        );

        return [
            'success' => true,
            'resultados' => $resultados
        ];
    }

    /**
     * Process CSV with progress tracking
     */
    private function procesarCSVWithProgress($filePath, $corporativoId, $fechaIniGlobal, $fechaVenGlobal, $planId, $clinicaId = null, $asesorId = null)
    {
        // Determine clinica_id from plan
        if (!$clinicaId) {
            $plan = Planes::findOne($planId);
            if (!$plan) {
                return [
                    'success' => false,
                    'error' => 'El plan seleccionado no existe.'
                ];
            }
            $clinicaId = $plan->clinica_id;
        }

        // Use the existing procesarCSV method
        // You can modify it to emit progress events via session or cache
        return $this->procesarCSV($filePath, $corporativoId, $fechaIniGlobal, $fechaVenGlobal, $planId, $clinicaId, $asesorId);
    }
}
