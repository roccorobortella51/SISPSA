<?php

namespace app\controllers;

use Yii;
use app\models\Pagos;
use app\models\PagosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\models\TasaCambio;
use app\components\UserHelper;
use app\models\RmClinica;
use app\models\Cuotas;
use app\models\UserDatos;
use app\models\Contratos;

/**
 * PagosController implements the CRUD actions for Pagos model.
 */
class PagosController extends Controller
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
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Obtiene la tasa de cambio actual
     */
    private function getTasaCambio()
    {
        $tasa = TasaCambio::find()
            ->where(['fecha' => date('Y-m-d')])
            ->one();

        if ($tasa) {
            return $tasa->tasa_cambio;
        }

        // Si no hay tasa para hoy, buscar la más reciente
        $ultimaTasa = TasaCambio::find()
            ->orderBy(['fecha' => SORT_DESC])
            ->one();

        return $ultimaTasa ? $ultimaTasa->tasa_cambio : 1.0;
    }

    public function actionClinica($id)
    {
        $searchModel = new PagosSearch();
        $dataProvider = $searchModel->searchClinica($this->request->queryParams, null, $id);
        $clinica = RmClinica::findOne($id);
        return $this->render('clinica', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'clinica' => $clinica,
        ]);
    }

    /**
     * Lists all Pagos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PagosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionTasacambio()
    {
        // Ejecuta el action de otro controlador sin redirección
        $resultado = Yii::$app->runAction('site/tasacambio');

        // Puedes usar el resultado
        return $resultado;
    }

    /**
     * Displays a single Pagos model.
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
     * Creates a new Pagos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */

    public function actionCreate($user_id = null)
    {
        // CORRECCIÓN: Usar método privado en lugar de llamar a la acción
        $tasa_completa = $this->getTasaCambio();

        $model = new Pagos();
        $model->scenario = 'create'; // ADD THIS LINE to use the 'create' scenario

        $model->tasa = number_format($tasa_completa, 2, '.', '');
        $model->user_id = $user_id;
        $fileName = null;
        $tempFilePath = null;
        $folder = 'Pago';
        $model->estatus = 'Por Conciliar';
        $modelCuotas = new Cuotas();

        // --- ADD DEBUG LOGGING ---
        Yii::info("=== PAGOS CREATE ACTION STARTED ===");
        Yii::info("User ID: " . $user_id);
        Yii::info("Request method: " . Yii::$app->request->method);
        Yii::info("Model scenario: " . $model->scenario);
        // --- END DEBUG ---

        // --- ADD CONTRACT CONTEXT LOGIC ---
        $contratoActivo = null;
        $contratosInfo = [];

        if ($user_id) {
            // Find ALL active/valid contracts for this user
            $contratos = Contratos::find()
                ->where(['user_id' => $user_id])
                ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO]) // Exclude annulled contracts
                ->orderBy(['fecha_ini' => SORT_DESC])
                ->all();

            foreach ($contratos as $contrato) {
                $contratosInfo[] = [
                    'id' => $contrato->id,
                    'nrocontrato' => $contrato->nrocontrato,
                    'estatus' => $contrato->estatus,
                    'fecha_ini' => $contrato->fecha_ini,
                    'fecha_ven' => $contrato->fecha_ven,
                    'plan' => $contrato->plan ? $contrato->plan->nombre : 'N/A',
                    'clinica' => $contrato->clinica ? $contrato->clinica->nombre : 'N/A',
                    'monto' => $contrato->monto,
                ];

                // Check if this contract is currently active (today is within its date range)
                $today = date('Y-m-d');
                if (
                    $contrato->fecha_ini <= $today &&
                    ($contrato->fecha_ven === null || $contrato->fecha_ven >= $today) &&
                    $contrato->estatus !== Contratos::STATUS_ANULADO
                ) {
                    $contratoActivo = $contrato;
                }
            }

            // If no contract is currently active, use the most recent one
            if (!$contratoActivo && !empty($contratos)) {
                $contratoActivo = $contratos[0];
            }
        }
        // --- END CONTRACT CONTEXT LOGIC ---

        // Use the new method to get pending cuotas
        $cuotas = Cuotas::getPendingCuotasForUser($user_id);

        $total = 0;
        foreach ($cuotas as $cuota) {
            // Use monto_usd if available, otherwise fall back to monto
            $monto = !empty($cuota->monto_usd) ? round($cuota->monto_usd, 2) : round($cuota->monto, 2);
            $total += (float)$monto;
        }
        $model->monto_pagado = round($total, 2);

        if ($this->request->isPost) {
            Yii::info("=== FORM SUBMITTED VIA POST ===");
            Yii::info("Post data: " . print_r($this->request->post(), true));

            if ($model->load($this->request->post())) {
                Yii::info("=== MODEL LOADED FROM POST ===");
                Yii::info("Model attributes after load: " . print_r($model->attributes, true));

                // CRITICAL: Initialize values for Efectivo - Dólar ($) IMMEDIATELY after load()
                // This is BEFORE validation to avoid "required" field errors
                if ($model->metodo_pago === 'Efectivo - Dólar ($)') {
                    $model->tasa = 1; // Set tasa to 1 for cash dollar

                    // Set monto_usd = monto_pagado for cash dollar
                    if (empty($model->monto_usd) && !empty($model->monto_pagado)) {
                        $model->monto_usd = $model->monto_pagado;
                    }
                    Yii::info("Post-load init: Cash Dollar payment - tasa=1, monto_usd={$model->monto_usd}");
                }

                // --- ADDED POST HANDLING LOGIC: START ---
                $uploadedFileInstance = UploadedFile::getInstance($model, 'imagen_prueba_file');

                // Skip file upload validation for cash dollar payments
                if ($model->metodo_pago !== 'Efectivo - Dólar ($)') {
                    Yii::info("Non-cash payment, checking for file upload");

                    // ADD THIS VALIDATION for the file
                    if (!$uploadedFileInstance) {
                        Yii::info("No file uploaded for non-cash payment");
                        Yii::$app->session->setFlash('error', 'Debe adjuntar un comprobante de pago.');
                        return $this->render('create', [
                            'model' => $model,
                            'user_id' => $user_id,
                            'cuotas' => $cuotas,
                            'modelCuotas' => $modelCuotas,
                            'total' => $total,
                            'contratoActivo' => $contratoActivo,
                            'contratosInfo' => $contratosInfo,
                        ]);
                    }
                }

                // Only handle file upload for non-cash dollar payments
                if ($model->metodo_pago !== 'Efectivo - Dólar ($)' && $uploadedFileInstance) {
                    Yii::info("Processing file upload for non-cash payment");
                    $fileName = uniqid('pago_') . '.' . $uploadedFileInstance->extension;
                    $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                    if ($uploadedFileInstance->saveAs($tempFilePath)) {
                        $fileKeyInBucket = $fileName;
                        $publicUrl = UserHelper::uploadFileToSupabaseApi(
                            $tempFilePath,
                            $uploadedFileInstance->type,
                            $fileKeyInBucket,
                            $folder
                        );

                        if (file_exists($tempFilePath)) {
                            unlink($tempFilePath);
                        }

                        if ($publicUrl) {
                            $model->imagen_prueba = $publicUrl;
                            Yii::info("File uploaded successfully to: " . $publicUrl);
                        } else {
                            Yii::$app->session->setFlash('error', 'Fallo la subida de la imagen a Supabase Storage.');
                            Yii::error("Failed to upload file to Supabase");
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal.');
                        Yii::error("Failed to save temporary file");
                    }
                } else {
                    Yii::info("No file upload needed for cash dollar payment or no file provided");
                }

                // VALIDATION: Ensure selected cuotas match payment amount
                $selectedCuotaIds = Yii::$app->request->post('selected_cuotas', []);
                Yii::info("Selected cuota IDs: " . print_r($selectedCuotaIds, true));

                if (empty($selectedCuotaIds)) {
                    Yii::info("No cuotas selected");
                    Yii::$app->session->setFlash('error', 'Debe seleccionar al menos una cuota para pagar.');
                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $user_id,
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                        'contratoActivo' => $contratoActivo,
                        'contratosInfo' => $contratosInfo,
                    ]);
                }

                // Calculate total from selected cuotas
                $selectedCuotasTotal = 0;
                $selectedCuotas = Cuotas::find()->where(['id' => $selectedCuotaIds])->all();
                foreach ($selectedCuotas as $cuota) {
                    $selectedCuotasTotal += $cuota->monto_usd ?: $cuota->monto;
                }
                Yii::info("Selected cuotas total: " . $selectedCuotasTotal);
                Yii::info("Model monto_pagado: " . $model->monto_pagado);

                // Validate payment amount matches selected cuotas total
                if (abs($model->monto_pagado - $selectedCuotasTotal) > 0.01) {
                    Yii::info("Payment amount mismatch");
                    Yii::$app->session->setFlash(
                        'error',
                        "El monto pagado ({$model->monto_pagado}) no coincide con el total de cuotas seleccionadas ({$selectedCuotasTotal})."
                    );
                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $user_id,
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                        'contratoActivo' => $contratoActivo,
                        'contratosInfo' => $contratosInfo,
                    ]);
                }

                // Skip reference validation for cash dollar payments
                if ($model->metodo_pago !== 'Efectivo - Dólar ($)' && empty($model->numero_referencia_pago)) {
                    Yii::info("Reference number missing for non-cash payment");
                    Yii::$app->session->setFlash('error', 'El número de referencia es obligatorio.');
                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $user_id,
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                        'contratoActivo' => $contratoActivo,
                        'contratosInfo' => $contratosInfo,
                    ]);
                }

                // Validate the model before saving
                Yii::info("Validating model before save");
                if (!$model->validate()) {
                    Yii::info("Model validation failed: " . print_r($model->errors, true));
                    Yii::$app->session->setFlash('error', 'Error de validación: ' . implode(', ', array_map(function ($errors) {
                        return implode(', ', $errors);
                    }, $model->errors)));

                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $user_id,
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                        'contratoActivo' => $contratoActivo,
                        'contratosInfo' => $contratosInfo,
                    ]);
                }

                Yii::info("Attempting to save model");
                if ($model->save()) {
                    Yii::info("=== MODEL SAVED SUCCESSFULLY ===");
                    Yii::info("Model ID: " . $model->id);

                    // IMPROVED: Mark selected cuotas as paid and link to payment
                    $updatedCount = Cuotas::updateAll(
                        [
                            'id_pago' => $model->id,
                            'estatus' => 'pagada',
                            'fecha_pago' => $model->fecha_pago
                        ],
                        ['id' => $selectedCuotaIds]
                    );

                    Yii::info("Updated {$updatedCount} cuotas for payment ID: {$model->id}", 'pagos');

                    if ($updatedCount != count($selectedCuotaIds)) {
                        Yii::error("Mismatch in cuota update: expected " . count($selectedCuotaIds) . ", updated {$updatedCount}", 'pagos');
                    }

                    Yii::$app->session->setFlash(
                        'success',
                        "Pago registrado con éxito. Se actualizaron {$updatedCount} cuotas."
                    );
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    // Show validation errors
                    Yii::info("=== MODEL SAVE FAILED ===");
                    Yii::info("Errors: " . print_r($model->errors, true));

                    $errorMessages = [];
                    foreach ($model->errors as $attributeErrors) {
                        $errorMessages = array_merge($errorMessages, $attributeErrors);
                    }

                    Yii::$app->session->setFlash('error', 'Error al guardar el pago: ' . implode(', ', $errorMessages));
                    // If save fails, re-render the form with error messages
                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $user_id,
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                        'contratoActivo' => $contratoActivo,
                        'contratosInfo' => $contratosInfo,
                    ]);
                }
                // --- ADDED POST HANDLING LOGIC: END ---
            } else {
                Yii::info("=== MODEL LOAD FAILED ===");
                Yii::info("Post data keys: " . print_r(array_keys(Yii::$app->request->post()), true));
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'user_id' => $user_id,
            'cuotas' => $cuotas,
            'modelCuotas' => $modelCuotas,
            'total' => $total,
            'contratoActivo' => $contratoActivo, // Pass contract info to view
            'contratosInfo' => $contratosInfo,   // Pass all contracts info
        ]);
    }

    /**
     * Updates an existing Pagos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        // CORRECCIÓN: Usar método privado
        $tasa_completa = $this->getTasaCambio();

        $model = $this->findModel($id);
        $model->scenario = 'update'; // SET THE UPDATE SCENARIO
        $model->tasa = number_format($tasa_completa, 2, '.', '');

        $oldImagePath = $model->imagen_prueba;
        $tempFilePath = null;

        if ($this->request->isPost && $model->load($this->request->post())) {
            // For cash dollar payments, set tasa to 1
            if ($model->metodo_pago === 'Efectivo - Dólar ($)') {
                $model->tasa = 1;
            }

            $folder = 'Pago';
            $uploadedFileInstance = UploadedFile::getInstance($model, 'imagen_prueba_file');

            if ($uploadedFileInstance) {
                $fileName = uniqid('pago_') . '.' . $uploadedFileInstance->extension;
                $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                if ($uploadedFileInstance->saveAs($tempFilePath)) {
                    $fileKeyInBucket = $fileName;
                    $publicUrl = UserHelper::uploadFileToSupabaseApi(
                        $tempFilePath,
                        $uploadedFileInstance->type,
                        $fileKeyInBucket,
                        $folder
                    );

                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }

                    if ($publicUrl) {
                        $model->imagen_prueba = $publicUrl;
                        if ($oldImagePath) {
                            UserHelper::deleteFileFromSupabaseApi($oldImagePath);
                        }
                    } else {
                        $model->imagen_prueba = $oldImagePath;
                        Yii::$app->session->setFlash('error', 'Fallo la subida de la nueva imagen a Supabase Storage.');
                    }
                } else {
                    $model->imagen_prueba = $oldImagePath;
                    Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal para la actualización.');
                }
            } else {
                $model->imagen_prueba = $oldImagePath;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Pago actualizado con éxito.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Error al actualizar el pago en la base de datos.');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Pagos model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $folder = 'Pago';

        if ($model->imagen_prueba) {
            UserHelper::deleteFileFromSupabaseApi($model->imagen_prueba, $folder);
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Pago y archivo asociados eliminados con éxito.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al eliminar el pago.');
        }

        return $this->redirect(['/contratos/index', 'user_id' => $model->user_id]);
    }

    /**
     * Finds the Pagos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Pagos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pagos::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdatestatus()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = \Yii::$app->request->post('id');
        $status = \Yii::$app->request->post('status');

        \Yii::info('Datos recibidos - id: ' . $id . ', status: ' . $status);

        if (empty($id) || $status === null) {
            return ['success' => false, 'error' => 'Parámetros requeridos: id=' . $id . ', status=' . $status];
        }

        $model = Pagos::findOne($id);
        if (!$model) {
            return ['success' => false, 'error' => 'Registro no encontrado'];
        }

        $model->estatus = ($status == '1') ? 'Conciliado' : 'Por Conciliar';
        $model->updated_at = date('Y-m-d');
        $model->fecha_conciliacion = date('Y-m-d');
        $model->conciliador_id = Yii::$app->user->id;

        if ($model->save(false)) {
            $user = UserDatos::findOne(['id' => $model->user_id]);
            if ($user) {
                $user->estatus_solvente = ($model->estatus == 'Conciliado') ? 'Si' : 'No';
                $user->save(false);

                if ($user->estatus_solvente == 'Si') {
                    $contrato = Contratos::find()->where(['user_id' => $model->user_id])->one();
                    if ($contrato) {
                        $contrato->estatus = 'Activo';
                        $contrato->save(false);
                    }
                }
            }
            return ['success' => true, 'new_status' => $model->estatus];
        }

        return ['success' => false, 'error' => 'Error al guardar'];
    }

    public function actionUpdatesolvente()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_id = \Yii::$app->request->post('user_id');
        $status = \Yii::$app->request->post('status');

        \Yii::info('Datos recibidos para solvente - user_id: ' . $user_id . ', status: ' . $status);

        if (empty($user_id) || $status === null) {
            return ['success' => false, 'error' => 'Parámetros requeridos: user_id=' . $user_id . ', status=' . $status];
        }

        $userDatos = UserDatos::findOne(['user_login_id' => $user_id]);
        if (!$userDatos) {
            return ['success' => false, 'error' => 'Usuario no encontrado'];
        }

        $userDatos->estatus_solvente = ($status == '1') ? 'SI' : 'No';
        $userDatos->updated_at = date('Y-m-d');

        if ($userDatos->save(false)) {
            return ['success' => true, 'new_status' => $userDatos->estatus_solvente];
        }

        return ['success' => false, 'error' => 'Error al guardar'];
    }

    public function actionEjecutar($user_id = null)
    {
        $user_id = $user_id ?: Yii::$app->request->get('user_id');
        $yiiPath = Yii::getAlias('@app/yii');

        if (!is_executable($yiiPath)) {
            @chmod($yiiPath, 0755);
        }

        $command = "php " . escapeshellarg($yiiPath) . " cuota/generar 2>&1";

        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        $outputStr = '';
        $exitCode = -1;

        if (is_resource($process)) {
            $outputStr = stream_get_contents($pipes[1]);
            $outputStr .= stream_get_contents($pipes[2]);

            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            $exitCode = proc_close($process);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => $exitCode === 0,
                'exitCode' => $exitCode,
                'output' => $outputStr,
                'command' => $command
            ];
        }

        if ($exitCode === 0) {
            Yii::$app->session->setFlash('success', "Cuotas generadas correctamente.");
        } else {
            Yii::$app->session->setFlash('error', "Error generando cuotas (exitCode={$exitCode}).\nSalida: " . substr($outputStr, 0, 1000));
        }

        if (!empty($user_id)) {
            return $this->redirect(['create', 'user_id' => $user_id]);
        }

        $referrer = Yii::$app->request->referrer;
        if ($referrer) {
            return $this->redirect($referrer);
        }

        return $this->redirect(['index']);
    }

    /**
     * Debug method to check user contracts and cuotas in detail
     */
    public function actionDebugUser101()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_id = 101;

        // Find user contracts
        $contratos = Contratos::find()
            ->where(['user_id' => $user_id])
            ->all();

        $contratoIds = [];
        foreach ($contratos as $contrato) {
            $contratoIds[] = $contrato->id;
        }

        // Find pending cuotas for these contracts
        $cuotas = Cuotas::find()
            ->where(['IN', 'contrato_id', $contratoIds])
            ->andWhere(['estatus' => 'pendiente'])
            ->all();

        // Also check all cuotas for these contracts regardless of status
        $allCuotas = Cuotas::find()
            ->where(['IN', 'contrato_id', $contratoIds])
            ->all();

        return [
            'user_id' => $user_id,
            'contratos_found' => count($contratos),
            'contrato_ids' => $contratoIds,
            'contratos_details' => array_map(function ($contrato) {
                return [
                    'id' => $contrato->id,
                    'nrocontrato' => $contrato->nrocontrato,
                    'estatus' => $contrato->estatus,
                    'user_id' => $contrato->user_id,
                    'fecha_ini' => $contrato->fecha_ini,
                    'fecha_ven' => $contrato->fecha_ven,
                ];
            }, $contratos),
            'pending_cuotas_found' => count($cuotas),
            'all_cuotas_found' => count($allCuotas),
            'pending_cuotas' => array_map(function ($cuota) {
                return [
                    'id' => $cuota->id,
                    'contrato_id' => $cuota->contrato_id,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'monto' => $cuota->monto,
                    'monto_usd' => $cuota->monto_usd,
                    'estatus' => $cuota->estatus,
                    'rate_usd_bs' => $cuota->rate_usd_bs,
                ];
            }, $cuotas),
            'all_cuotas' => array_map(function ($cuota) {
                return [
                    'id' => $cuota->id,
                    'contrato_id' => $cuota->contrato_id,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'monto' => $cuota->monto,
                    'monto_usd' => $cuota->monto_usd,
                    'estatus' => $cuota->estatus,
                ];
            }, $allCuotas),
        ];
    }

    // Add this to PagosController for testing
    public function actionTestUser101()
    {
        $user_id = 101;

        // Test the cuotas query directly
        $cuotas = Cuotas::getPendingCuotasForUser($user_id);

        echo "<h1>Testing User 101 Cuotas</h1>";
        echo "<p>User ID: " . $user_id . "</p>";
        echo "<p>Cuotas Found: " . count($cuotas) . "</p>";

        foreach ($cuotas as $cuota) {
            echo "<p>Cuota ID: " . $cuota->id . ", Contract: " . $cuota->contrato_id . ", Monto USD: " . $cuota->monto_usd . ", Monto: " . $cuota->monto . "</p>";
        }

        // Test the view rendering
        $model = new Pagos();
        $model->user_id = $user_id;
        $modelCuotas = new Cuotas();
        $total = 58.00; // Expected total

        return $this->render('create', [
            'model' => $model,
            'user_id' => $user_id,
            'cuotas' => $cuotas,
            'modelCuotas' => $modelCuotas,
            'total' => $total,
        ]);
    }

    /**
     * Debug method to check user contracts and cuotas
     */
    public function actionDebugUserCuotas($user_id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Find user contracts
        $contratos = Contratos::find()
            ->where(['user_id' => $user_id])
            ->all();

        $contratoIds = [];
        foreach ($contratos as $contrato) {
            $contratoIds[] = $contrato->id;
        }

        // Find pending cuotas
        $cuotas = Cuotas::find()
            ->where(['IN', 'contrato_id', $contratoIds])
            ->andWhere(['estatus' => 'pendiente'])
            ->all();

        return [
            'user_id' => $user_id,
            'contratos_found' => count($contratos),
            'contrato_ids' => $contratoIds,
            'cuotas_found' => count($cuotas),
            'cuotas' => array_map(function ($cuota) {
                return [
                    'id' => $cuota->id,
                    'contrato_id' => $cuota->contrato_id,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'monto' => $cuota->monto,
                    'monto_usd' => $cuota->monto_usd,
                    'estatus' => $cuota->estatus,
                ];
            }, $cuotas)
        ];
    }

    /**
     * Valida que el pago coincida con las cuotas seleccionadas
     */
    private function validatePaymentCuotas($model, $selectedCuotaIds)
    {
        if (empty($selectedCuotaIds)) {
            Yii::$app->session->setFlash('error', 'Debe seleccionar al menos una cuota para pagar.');
            return false;
        }

        $selectedCuotas = Cuotas::find()->where(['id' => $selectedCuotaIds])->all();
        $selectedCuotasTotal = 0;

        foreach ($selectedCuotas as $cuota) {
            // Skip cuotas that are already paid
            if ($cuota->estatus === 'pagada') {
                Yii::$app->session->setFlash('error', "La cuota #{$cuota->id} ya está pagada.");
                return false;
            }
            $selectedCuotasTotal += $cuota->monto_usd ?: $cuota->monto;
        }

        // Allow small rounding differences
        if (abs($model->monto_pagado - $selectedCuotasTotal) > 0.01) {
            Yii::$app->session->setFlash(
                'error',
                "El monto pagado ({$model->monto_pagado} USD) no coincide con el total de cuotas seleccionadas ({$selectedCuotasTotal} USD)."
            );
            return false;
        }

        return true;
    }
}
