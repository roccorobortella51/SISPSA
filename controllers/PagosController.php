<?php

namespace app\controllers;

use Yii;
use app\models\Pagos;
use app\models\PagosSearch; // Asumo que tienes una clase de búsqueda para tu modelo Pagos
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile; // Necesario para manejar la subida de archivos
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
        $tasa_bcv = $this->actionTasacambio();
        $model = new Pagos();
        //$model->tasa = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one()->tasa_cambio;
        $tasa_completa = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one()->tasa_cambio;

        // Usa number_format() para obtener una cadena de texto con 2 decimales
        // Los parámetros son: (número, decimales, separador_decimal, separador_miles)
        $model->tasa = number_format($tasa_completa, 2, '.', ''); 

        $model->user_id = $user_id;
        $fileName = null;
        $tempFilePath = null; // Inicializamos la ruta temporal a null
        $folder = 'Pago';
        $model->estatus = 'Por Conciliar';
        $modelCuotas = new Cuotas();
        $cuotas = Cuotas::find()
            ->select('cuotas.*') // Seleccionar todas las columnas de cuotas
            ->innerJoin('contratos', 'contratos.id = cuotas.contrato_id')
            ->where(['contratos.user_id' => $user_id,'cuotas.Estatus' => 'pendiente'])
            ->orderBy(['cuotas.fecha_vencimiento' => SORT_ASC])
            ->all();
        $total = 0;
        foreach ($cuotas as $cuota) {
            $total += $cuota->monto_usd;
        }
        $model->monto_pagado = $total;

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // Obtener cuotas seleccionadas desde POST (si se enviaron)
                $selectedCuotas = Yii::$app->request->post('selected_cuotas', []);

                // VALIDACIÓN SERVER-SIDE: sumar montos de cuotas seleccionadas y comparar con monto_pagado
                $sumSelected = 0.0;
                if (!empty($selectedCuotas)) {
                    $cuotasForSum = Cuotas::find()->where(['id' => $selectedCuotas])->all();
                    foreach ($cuotasForSum as $c) {
                        // usar monto_usd si existe, si no usar monto
                        $m = null;
                        if (isset($c->monto_usd)) {
                            $m = $c->monto_usd;
                        } elseif (isset($c->monto)) {
                            $m = $c->monto;
                        }
                        $sumSelected += (float)($m ?: 0);
                    }
                }   

                // Si no hay cuotas seleccionadas sumSelected será 0.0 — el JS establece monto_pagado a 0 en ese caso
                //DE ROMMEL
                $montoPagadoPosted = (float)($model->monto_pagado ?: 0);
                if (abs($sumSelected - $montoPagadoPosted) > 0.01) {
                    /*$model->addError('monto_pagado', 'La suma de las cuotas seleccionadas no coincide con el Monto a Pagar.');
                    Yii::$app->session->setFlash('warning', 'El AFILIADO No tiene cuotas pendientes seleccionadas o el monto no coincide.');
                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $this->request->get('user_id'),
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                    ]);*/
                    $model->observacion = 'El pago tiene una diferencia entre el monto pagado y la suma de las cuotas seleccionadas igual a: '.number_format($montoPagadoPosted - $sumSelected, 2, '.', '');
                }

                // Obtiene el monto que el usuario ingresó para pagar. Si no hay, es 0.
                $montoPagadoPosted = (float)($model->monto_pagado ?: 0);

                // Calcula la suma de las cuotas que están disponibles y no pagadas.
                // Asumo que tienes un método para obtener las cuotas disponibles.
                // Este es el monto real que el usuario debería pagar si va a cubrir todas las cuotas pendientes.
                $sumPending = 0;
                foreach ($cuotas as $cuota) {
                    if ($cuota->estado !== 'Pagado') { // Asume que 'Pagado' es el estado de una cuota pagada
                        $sumPending += $cuota->monto;
                    }
                }

                // Ahora, valida si el monto ingresado es 0 cuando hay cuotas pendientes
                if ($montoPagadoPosted == 0 && $sumPending > 0) {
                    $model->addError('monto_pagado', 'No puede procesar un pago de 0 cuando hay cuotas pendientes.');
                    Yii::$app->session->setFlash('error', 'Debe ingresar un monto válido para pagar las cuotas pendientes.');
                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $this->request->get('user_id'),
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                    ]);
                }


                //MARCOS
                // Si el usuario ingresa un monto, valida que coincida con la suma total de cuotas pendientes.
                // Esto es opcional, pero ayuda a evitar pagos parciales si no los manejas.
                /*if ($montoPagadoPosted > 0 && abs($sumPending - $montoPagadoPosted) > 0.01) {
                    $model->addError('monto_pagado', 'El monto ingresado no coincide con la suma total de las cuotas pendientes.');
                    Yii::$app->session->setFlash('error', 'El monto ingresado no coincide con el total de las cuotas pendientes.');
                    return $this->render('create', [
                        'model' => $model,
                        'user_id' => $this->request->get('user_id'),
                        'cuotas' => $cuotas,
                        'modelCuotas' => $modelCuotas,
                        'total' => $total,
                    ]);
                }*/

                // Obtenemos la instancia del archivo subido desde el formulario
                $model->imagen_prueba_file = UploadedFile::getInstance($model, 'imagen_prueba_file');

                if ($model->imagen_prueba_file) {
                    // Generamos un nombre de archivo único para evitar colisiones
                    $fileName = uniqid('pago_') . '.' . $model->imagen_prueba_file->extension;
                    // Definimos la ruta temporal en el directorio @runtime (fuera del acceso web directo por seguridad)
                    $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                    // Guardamos el archivo subido en la ruta temporal del servidor
                    if ($model->imagen_prueba_file->saveAs($tempFilePath)) {
                        Yii::info("Archivo temporal guardado en: " . $tempFilePath, __METHOD__);

                        // La "clave" del archivo en Supabase Storage (su nombre y ruta dentro del bucket).
                        // En este caso, solo es el nombre del archivo para que se guarde en la raíz del bucket 'usuarios'.
                        // Si quisieras una subcarpeta, sería por ejemplo 'pagos_imagenes/' . $fileName;
                        $fileKeyInBucket = $fileName;

                        // Llamamos a la función dedicada a subir el archivo a Supabase Storage via API
                        $publicUrl = UserHelper::uploadFileToSupabaseApi(
                            $tempFilePath,
                            $model->imagen_prueba_file->type,
                            $fileKeyInBucket,
                            $folder
                        );

                        // Eliminamos el archivo temporal del servidor DESPUÉS de que la operación de subida
                        // a Supabase haya concluido (ya sea con éxito o error). Esto evita "Stream is detached".
                        if (file_exists($tempFilePath)) {
                            unlink($tempFilePath);
                            Yii::info("Archivo temporal eliminado: " . $tempFilePath, __METHOD__);
                        }

                        if ($publicUrl) {
                            // Si la subida a Supabase fue exitosa, guardamos la URL pública en el modelo del pago
                            $model->imagen_prueba = $publicUrl;

                            // Usar transacción para asegurar consistencia al actualizar pago y cuotas
                            $transaction = Yii::$app->db->beginTransaction();
                            try {
                                if ($model->save(false)) { // Guardamos el modelo de pago en la base de datos
                                    // Actualizar sólo las cuotas seleccionadas (si el array está vacío, actualizar TODAS las cargadas)
                                    $cuotasToUpdate = [];
                                    if (!empty($selectedCuotas)) {
                                        $cuotasToUpdate = Cuotas::find()->where(['id' => $selectedCuotas])->all();
                                    } else {
                                        $cuotasToUpdate = null; // todas las pendientes cargadas inicialmente
                                    }
                                    if ($cuotasToUpdate != null) {
                                        // Si no hay cuotas seleccionadas, actualizar todas las pendientes cargadas inicialmente
                                        foreach ($cuotasToUpdate as $cuota) {
                                            if ($cuota->Estatus === 'pendiente') {
                                                $cuota->Estatus = 'pagado';
                                                $cuota->fecha_pago = $model->fecha_pago ?: date('Y-m-d');
                                                $cuota->rate_usd_bs = $model->tasa;
                                                $cuota->id_pago = $model->id; // Guardar ID del pago para rastreo
                                                if (!$cuota->save(false)) {
                                                    throw new \Exception('Error al actualizar cuota ID: ' . $cuota->id);
                                                }
                                            }
                                        }
                                    }
                                    

                                    $transaction->commit();
                                    Yii::$app->session->setFlash('success', 'Pago y archivo subido con éxito. Cuotas actualizadas.');
                                    return $this->redirect(['view', 'id' => $model->id]);
                                } else {
                                    throw new \Exception('Error al guardar el pago en la base de datos.');
                                }
                            } catch (\Exception $e) {
                                $transaction->rollBack();
                                Yii::error($e->getMessage(), __METHOD__);
                                Yii::$app->session->setFlash('error', 'Error al guardar el pago o actualizar cuotas: ' . $e->getMessage());
                            }
                        } else {
                            // Si la subida a Supabase falló, el mensaje de error ya se estableció en la función de subida.
                            Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                        }
                    } else {
                        Yii::error("Error al guardar el archivo temporal: " . $model->imagen_prueba_file->error, __METHOD__);
                        Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'No se ha subido ningún archivo o hubo un error en la carga.');
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'user_id' => $this->request->get('user_id'), // Pasamos user_id si es necesario para la vista
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
        $tasa_bcv = $this->actionTasacambio();
        $model = $this->findModel($id);

        $t = $ultimaTasa = TasaCambio::find()
            ->orderBy(['fecha' => SORT_DESC])
            ->one();

        
        $model->tasa = $t->tasa_cambio;

        $oldImagePath = $model->imagen_prueba; // Guardar la URL de la imagen existente
        $tempFilePath = null; // Inicializar a null

        if ($this->request->isPost && $model->load($this->request->post())) {
            // Obtener la instancia del archivo subido
            $folder = 'Pago';
            $uploadedFileInstance = UploadedFile::getInstance($model, 'imagen_prueba_file');

            if ($uploadedFileInstance) {
                // Hay un nuevo archivo para subir, procesarlo
                $fileName = uniqid('pago_') . '.' . $uploadedFileInstance->extension;
                $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                if ($uploadedFileInstance->saveAs($tempFilePath)) {
                    Yii::info("Archivo temporal guardado en: " . $tempFilePath, __METHOD__);

                    // Primero, intentar subir el nuevo archivo
                    $fileKeyInBucket = $fileName;
                    $publicUrl = UserHelper::uploadFileToSupabaseApi(
                        $tempFilePath,
                        $uploadedFileInstance->type, // Usamos el tipo MIME del archivo subido
                        $fileKeyInBucket,
                        $folder
                    );

                    // Eliminar el archivo temporal DESPUÉS de intentar la subida
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                        Yii::info("Archivo temporal eliminado: " . $tempFilePath, __METHOD__);
                    }

                    if ($publicUrl) {
                        // Si la nueva subida fue exitosa, actualizar la URL en el modelo
                        $model->imagen_prueba = $publicUrl;
                        // Y eliminar la imagen antigua de Supabase si existía
                        if ($oldImagePath) {
                            UserHelper::deleteFileFromSupabaseApi($oldImagePath);
                        }
                    } else {
                        // Si la nueva subida falla, restaurar la URL de la imagen antigua
                        // para no perder el dato si el antiguo archivo sigue siendo válido.
                        $model->imagen_prueba = $oldImagePath;
                        Yii::$app->session->setFlash('error', 'Fallo la subida de la nueva imagen a Supabase Storage.');
                    }
                } else {
                    Yii::error("Error al guardar el archivo temporal para la actualización: " . $uploadedFileInstance->error, __METHOD__);
                    Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal para la actualización.');
                    // Mantener la imagen antigua si falló guardar el temporal
                    $model->imagen_prueba = $oldImagePath;
                }
            } else {
                // No se subió un nuevo archivo. Mantener la URL de la imagen existente.
                // Esto es crucial para que el campo `imagen_prueba` no se sobrescriba a `null`
                // si el campo de subida de archivo en el formulario se dejó vacío.
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

        // Primero, intentar eliminar el archivo de Supabase si existe una URL en el modelo
        if ($model->imagen_prueba) {
            UserHelper::deleteFileFromSupabaseApi($model->imagen_prueba,$folder);
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
            return ['success' => false, 'error' => 'Parámetros requeridos: id='.$id.', status='.$status];
        }
        
        $model = Pagos::findOne($id);
        if (!$model) {
            return ['success' => false, 'error' => 'Registro no encontrado'];
        }
        
        // Convertir a valor adecuado
        $model->estatus = ($status == '1') ? 'Conciliado' : 'Por Conciliar';
        $model->updated_at = date('Y-m-d');
        $model->fecha_conciliacion = date('Y-m-d');
        $model->conciliador_id = Yii::$app->user->id;
        
        if ($model->save(false)) {
            $user = UserDatos::findOne(['id' => $model->user_id]);
            $user->estatus_solvente = ($model->estatus == 'Conciliado') ? 'Si' : 'No';
            $user->save(false);
            
            if($model->estatus == 'Conciliado') {
                $contrato = Contratos::find()->where(['user_id' => $model->user_id])->one();
                if ($contrato) {
                    // Check if contract was previously suspended (apply penalty only for previously suspended contracts)
                    $wasSuspended = ($contrato->estatus === 'suspendido' || $contrato->estatus === 'Suspendido');
                    
                    if ($wasSuspended) {
                        // Apply 7-day penalty for previously suspended contracts
                        $contrato->estatus = 'Esperar';
                        $contrato->fecha_reactivacion = date('Y-m-d', strtotime('+7 days'));
                        Yii::$app->session->setFlash('info', 'El contrato ha sido puesto en espera por 7 días debido a previa suspensión por falta de pago.');
                    } else {
                        // For contracts that were never suspended, activate immediately
                        $contrato->estatus = 'Activo';
                        $contrato->fecha_reactivacion = null;
                        Yii::$app->session->setFlash('success', 'Contrato activado inmediatamente.');
                    }
                    $contrato->save(false);
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
            return ['success' => false, 'error' => 'Parámetros requeridos: user_id='.$user_id.', status='.$status];
        }

        $userDatos = UserDatos::findOne(['user_login_id' => $user_id]);
        if (!$userDatos) {
            return ['success' => false, 'error' => 'Usuario no encontrado'];
        }

        // Convertir a valor adecuado
        $userDatos->estatus_solvente = ($status == '1') ? 'SI' : 'No';
        $userDatos->updated_at = date('Y-m-d');

        if ($userDatos->save(false)) {
            return ['success' => true, 'new_status' => $userDatos->estatus_solvente];
        }

        return ['success' => false, 'error' => 'Error al guardar'];
    }

    /*public function actionEjecutar($user_id = null)
    {
        // Permitir llamada con user_id via GET (la vista create pasa este parámetro)
        $user_id = $user_id ?: Yii::$app->request->get('user_id');

        $yiiPath = Yii::getAlias('@app/yii');

        // Verificar que tenemos permisos de ejecución
        if (!is_executable($yiiPath)) {
            @chmod($yiiPath, 0755);
        }

        $command = "php " . escapeshellarg($yiiPath) . " cuota/generar 2>&1";

        // Ejecutar y capturar exit code
        $output = [];
        $exitCode = 0;
        
        // Aquí está la corrección: llama a la función exec directamente
        exec($command, $output, $exitCode);
        
        $outputStr = implode("\n", $output);

        // Si es petición AJAX, devolver JSON (mantener compatibilidad)
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => $exitCode === 0,
                'exitCode' => $exitCode,
                'output' => $outputStr,
                'command' => $command
            ];
        }

        // Para peticiones normales, mostrar un flash con el resultado y redirigir a create
        if ($exitCode === 0) {
            Yii::$app->session->setFlash('success', "Cuotas generadas correctamente.");
        } else {
            Yii::$app->session->setFlash('error', "Error generando cuotas (exitCode={$exitCode}).\nSalida: " . substr($outputStr, 0, 1000));
        }

        // Redirigir de vuelta a la pantalla de creación de pago si se dispone de user_id,
        // si no, intentar volver al referer o al index de pagos.
        if (!empty($user_id)) {
            return $this->redirect(['create', 'user_id' => $user_id]);
        }

        $referrer = Yii::$app->request->referrer;
        if ($referrer) {
            return $this->redirect($referrer);
        }

        return $this->redirect(['index']);
    }*/

    public function actionActivateSuspended()
    {
        $currentDate = new \DateTime();
        $decemberFirst = new \DateTime('2024-12-01');
        
        if ($currentDate >= $decemberFirst) {
            Yii::$app->session->setFlash('warning', 'La regla temporal ya ha expirado. No se pueden activar contratos suspendidos automáticamente.');
            return $this->redirect(['index']);
        }
        
        $suspendedContracts = Contratos::find()
            ->where(['estatus' => 'suspendido'])
            ->orWhere(['estatus' => 'Suspendido'])
            ->all();
        
        $updatedCount = 0;
        foreach ($suspendedContracts as $contract) {
            $contract->estatus = 'Activo';
            $contract->fecha_reactivacion = null;
            if ($contract->save(false)) {
                $updatedCount++;
            }
        }
        
        Yii::$app->session->setFlash('success', "Se activaron {$updatedCount} contratos que estaban suspendidos.");
        return $this->redirect(['index']);
    }
    
    public function actionEjecutar($user_id = null)
    {
        $user_id = $user_id ?: Yii::$app->request->get('user_id');
        $yiiPath = Yii::getAlias('@app/yii');

        // Verificar permisos de ejecución (ya lo tienes)
        if (!is_executable($yiiPath)) {
            @chmod($yiiPath, 0755);
        }

        $command = "php " . escapeshellarg($yiiPath) . " cuota/generar 2>&1";

        // Reemplazo de exec() por proc_open()
        $descriptorspec = [
            0 => ["pipe", "r"],   // stdin
            1 => ["pipe", "w"],   // stdout
            2 => ["pipe", "w"]    // stderr
        ];

        $process = proc_open($command, $descriptorspec, $pipes);

        $outputStr = '';
        $exitCode = -1;

        if (is_resource($process)) {
            // Leer la salida y errores
            $outputStr = stream_get_contents($pipes[1]);
            $outputStr .= stream_get_contents($pipes[2]);
            
            // Cerrar los pipes
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            // Obtener el código de salida
            $exitCode = proc_close($process);
        }
        
        // Si la llamada es por AJAX, se devuelve una respuesta en JSON
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return [
                'success' => $exitCode === 0,
                'exitCode' => $exitCode,
                'output' => $outputStr,
                'command' => $command
            ];
        }
        
        // Para peticiones normales, mostrar un flash y redirigir
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

    
}