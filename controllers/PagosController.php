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
        $model->tasa = number_format($tasa_completa, 2, '.', '');
        $model->user_id = $user_id;
        $fileName = null;
        $tempFilePath = null;
        $folder = 'Pago';
        $model->estatus = 'Por Conciliar';
        $modelCuotas = new Cuotas();
        
        // Use the new method to get pending cuotas - FIXED: Make sure we call the static method correctly
        $cuotas = Cuotas::getPendingCuotasForUser($user_id);
            
        // DEBUG: Verificar qué estamos obteniendo
        Yii::info("=== PAGOS CREATE DEBUG ===");
        Yii::info("User ID passed: " . $user_id);
        Yii::info("Number of cuotas found: " . count($cuotas));
        foreach ($cuotas as $cuota) {
            Yii::info("Cuota ID: " . $cuota->id . ", Contrato ID: " . $cuota->contrato_id . ", Monto USD: " . $cuota->monto_usd . ", Monto: " . $cuota->monto);
        }
        Yii::info("=== END DEBUG ===");

        $total = 0;
        foreach ($cuotas as $cuota) {
            // Use monto_usd if available, otherwise fall back to monto
            $monto = !empty($cuota->monto_usd) ? $cuota->monto_usd : $cuota->monto;
            $total += (float)$monto;
        }
        $model->monto_pagado = $total;

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                
                // --- ADDED POST HANDLING LOGIC: START ---
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
                        } else {
                            Yii::$app->session->setFlash('error', 'Fallo la subida de la imagen a Supabase Storage.');
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal.');
                    }
                }
                
                // VALIDATION: Ensure selected cuotas match payment amount
                $selectedCuotaIds = Yii::$app->request->post('selected_cuotas', []);
                if (empty($selectedCuotaIds)) {
                    Yii::$app->session->setFlash('error', 'Debe seleccionar al menos una cuota para pagar.');
                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $user_id,
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                    ]);
                }
                
                // Calculate total from selected cuotas
                $selectedCuotasTotal = 0;
                $selectedCuotas = Cuotas::find()->where(['id' => $selectedCuotaIds])->all();
                foreach ($selectedCuotas as $cuota) {
                    $selectedCuotasTotal += $cuota->monto_usd ?: $cuota->monto;
                }
                
                // Validate payment amount matches selected cuotas total
                if (abs($model->monto_pagado - $selectedCuotasTotal) > 0.01) {
                    Yii::$app->session->setFlash('error', 
                        "El monto pagado ({$model->monto_pagado}) no coincide con el total de cuotas seleccionadas ({$selectedCuotasTotal})."
                    );
                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $user_id,
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                    ]);
                }
                
                if ($model->save()) {
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
                    
                    // Run solvent reconciliation after payment creation
                    $this->runSolventReconciliation($model->user_id);
                    
                    Yii::$app->session->setFlash('success', 
                        "Pago registrado con éxito. Se actualizaron {$updatedCount} cuotas."
                    );
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Error al guardar el pago en la base de datos.');
                    // If save fails, re-render the form with error messages
                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $user_id,
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                    ]);
                }
                // --- ADDED POST HANDLING LOGIC: END ---
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
        $model->tasa = number_format($tasa_completa, 2, '.', '');

        $oldImagePath = $model->imagen_prueba;
        $tempFilePath = null;

        if ($this->request->isPost && $model->load($this->request->post())) {
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
                // Run solvent reconciliation after payment update
                $this->runSolventReconciliation($model->user_id);
                
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
        $user_id = $model->user_id;

        if ($model->imagen_prueba) {
            UserHelper::deleteFileFromSupabaseApi($model->imagen_prueba, $folder);
        }

        if ($model->delete()) {
            // Run solvent reconciliation after payment deletion
            $this->runSolventReconciliation($user_id);
            
            Yii::$app->session->setFlash('success', 'Pago y archivo asociados eliminados con éxito.');
        } else {
            Yii::$app->session->setFlash('error', 'Error al eliminar el pago.');
        }

        return $this->redirect(['/contratos/index', 'user_id' => $user_id]);
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
            return ['success' => false, 'error' => 'Parámetros requeridos: id='.$id.', status='.$status];
        }
        
        $model = Pagos::findOne($id);
        if (!$model) {
            return ['success' => false, 'error' => 'Registro no encontrado'];
        }
        
        $model->estatus = ($status == '1') ? 'Conciliado' : 'Por Conciliar';
        $model->updated_at = date('Y-m-d H:i:s');
        $model->fecha_conciliacion = ($status == '1') ? date('Y-m-d') : null;
        $model->conciliador_id = Yii::$app->user->id;
        
        if ($model->save(false)) {
            $user = UserDatos::findOne(['id' => $model->user_id]);
            if ($user) {
                // Only mark as solvent if payment is conciliated AND covers current obligations
                if ($model->estatus == 'Conciliado') {
                    // Check if this payment covers all pending cuotas
                    $pendingCuotas = Cuotas::getPendingCuotasForUser($model->user_id);
                    $totalPending = 0;
                    foreach ($pendingCuotas as $cuota) {
                        $totalPending += $cuota->monto_usd ?: $cuota->monto;
                    }
                    
                    // If payment covers pending amount OR payment is for current month, mark as solvent
                    $currentMonthStart = date('Y-m-01');
                    if ($model->monto_usd >= $totalPending || 
                        (strtotime($model->fecha_pago) >= strtotime($currentMonthStart))) {
                        $user->estatus_solvente = 'SI';
                        $user->save(false);
                        
                        // Also activate any suspended contracts
                        $contratos = Contratos::find()->where(['user_id' => $model->user_id])->all();
                        foreach ($contratos as $contrato) {
                            if ($contrato->estatus == 'suspendido') {
                                $contrato->estatus = 'activo';
                                $contrato->save(false);
                            }
                        }
                    }
                } else {
                    // If payment is de-conciliated, run reconciliation to determine solvent status
                    $this->runSolventReconciliation($model->user_id);
                }
            }
            return ['success' => true, 'new_status' => $model->estatus];
        }
        
        return ['success' => false, 'error' => 'Error al guardar'];
    }

    /**
     * Runs solvent reconciliation for a specific user
     */
    private function runSolventReconciliation($user_id)
    {
        $user = UserDatos::findOne(['id' => $user_id]);
        if (!$user) return;
        
        // Check if user has conciliated payments in current month
        $currentMonthStart = date('Y-m-01');
        $hasCurrentPayment = Pagos::find()
            ->where(['user_id' => $user_id, 'estatus' => 'Conciliado'])
            ->andWhere(['>=', 'fecha_pago', $currentMonthStart])
            ->exists();
        
        // Check if user has pending overdue cuotas
        $hasOverdueCuotas = Cuotas::find()
            ->joinWith(['contrato'])
            ->where(['contratos.user_id' => $user_id])
            ->andWhere(['cuotas.estatus' => 'pendiente'])
            ->andWhere(['<', 'cuotas.fecha_vencimiento', date('Y-m-d')])
            ->exists();
        
        // Determine solvent status
        if ($hasCurrentPayment && !$hasOverdueCuotas) {
            $user->estatus_solvente = 'SI';
        } else {
            $user->estatus_solvente = 'No';
        }
        
        $user->save(false);
        
        Yii::info("Solvent reconciliation for user {$user_id}: currentPayment={$hasCurrentPayment}, overdueCuotas={$hasOverdueCuotas}, newStatus={$user->estatus_solvente}");
    }

    public function actionUpdatesolvente()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_id = \Yii::$app->request->post('user_id');
        $status = \Yii::$app->request->post('status');

        \Yii::info('Datos recibidos para solvente - user_id: ' . $user_id . ', status: ' . $status);

        if (empty($user_id) || $status === null) {
            return ['success' => false, 'error' => 'Parámetros requeridos: user_id='.$user_id.', status='.$status];
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
            'contratos_details' => array_map(function($contrato) {
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
            'pending_cuotas' => array_map(function($cuota) {
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
            'all_cuotas' => array_map(function($cuota) {
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
            'cuotas' => array_map(function($cuota) {
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
            Yii::$app->session->setFlash('error', 
                "El monto pagado ({$model->monto_pagado} USD) no coincide con el total de cuotas seleccionadas ({$selectedCuotasTotal} USD)."
            );
            return false;
        }
        
        return true;
    }

    /**
     * Fixes solvent status for all users
     * Uso: `yii pagos/fix-solvent-status`
     */
    public function actionFixSolventStatus()
    {
        $this->stdout("=== CORRIGIENDO ESTATUS SOLVENTE INCONSISTENTE ===\n\n");
        
        $fixedCount = 0;
        $allUsers = UserDatos::find()->all();
        
        foreach ($allUsers as $user) {
            $currentStatus = $user->estatus_solvente;
            
            // Check if user should be solvent
            $shouldBeSolvent = false;
            
            // Check for current month conciliated payments
            $currentMonthStart = date('Y-m-01');
            $hasCurrentPayment = Pagos::find()
                ->where(['user_id' => $user->id, 'estatus' => 'Conciliado'])
                ->andWhere(['>=', 'fecha_pago', $currentMonthStart])
                ->exists();
            
            // Check for no overdue cuotas
            $hasOverdueCuotas = Cuotas::find()
                ->joinWith(['contrato'])
                ->where(['contratos.user_id' => $user->id])
                ->andWhere(['cuotas.estatus' => 'pendiente'])
                ->andWhere(['<', 'cuotas.fecha_vencimiento', date('Y-m-d')])
                ->exists();
            
            $shouldBeSolvent = $hasCurrentPayment && !$hasOverdueCuotas;
            $correctStatus = $shouldBeSolvent ? 'SI' : 'No';
            
            if ($currentStatus !== $correctStatus) {
                $user->estatus_solvente = $correctStatus;
                if ($user->save(false)) {
                    $fixedCount++;
                    $this->stdout("✅ Corregido: {$user->nombres} {$user->apellidos} - {$currentStatus} → {$correctStatus}\n");
                }
            }
        }
        
        $this->stdout("\n✅ Proceso completado. Se corrigieron {$fixedCount} usuarios.\n");
        return ExitCode::OK;
    }
}