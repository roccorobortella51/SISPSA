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
use app\models\Cuotas; 
use app\components\UserHelper; 
use app\models\MasivoAfiliadosForm;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\UploadedFile;
use app\models\Planes; 
use app\models\CorporativoClinica; 
use app\models\User;

 

/**
 * CorporativoController implements the CRUD actions for Corporativo model.
 */
class CorporativoController extends Controller
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
        $dataProvider->query->with(['users', 'clinicas']);

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
        return $this->render('view', [
            'model' => $this->findModel($id),
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
                        ->andWhere(['cuotas.estatus' => 'pendiente'])
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
                            
                            // Create individual payment records for each affiliate
                            foreach ($userAmounts as $userId => $userAmount) {
                                if ($userAmount > 0) {
                                    $affiliatePayment = new Pagos();
                                    
                                    // Copy only the safe attributes, excluding the ID
                                    $affiliatePayment->created_at = $model->created_at;
                                    $affiliatePayment->recibo_id = $model->recibo_id;
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
                                        \Yii::info("Created affiliate payment for user {$userId} with amount {$userAmount}");
                                    } else {
                                        \Yii::error("Failed to create affiliate payment for user {$userId}: " . print_r($affiliatePayment->errors, true));
                                        throw new \Exception("Failed to create affiliate payment for user {$userId}");
                                    }
                                }
                            }

                            $contratosActualizados = [];
                            $cuotasUpdatedCount = 0;
                            
                            foreach ($allCuotas as $cuota) {
                                if ($cuota->estatus === 'pendiente') {
                                    $cuota->estatus = 'pagado';
                                    $cuota->fecha_pago = $model->fecha_pago ?: date('Y-m-d');
                                    $cuota->rate_usd_bs = $model->tasa; 
                                    $cuota->id_pago = $mainPaymentId;
                                    
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
                                    $cuotasPendientes = Cuotas::find()
                                        ->where(['contrato_id' => $contratoId, 'estatus' => 'pendiente'])
                                        ->count();
                                    
                                    if ($cuotasPendientes == 0 && $contrato->estatus !== 'activo') {
                                        $contrato->estatus = 'activo';
                                        if ($contrato->save(false)) {
                                            $contractsActivatedCount++;
                                        }
                                    }
                                }
                            }

                            $transaction->commit();
                            
                            Yii::$app->session->setFlash('success', 
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
                

                if ($model->save()) {

                    
                   foreach($model->users_ids as $user_datos_id){

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
        
        // Obtener los IDs de los afiliados que ya están asociados con este corporativo
        $afiliadosActuales = CorporativoUser::find()
            ->where(['corporativo_id' => $model->id])
            ->select('user_id')
            ->column();

        if ($this->request->isPost && $model->load($this->request->post())) {

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
                Yii::$app->session->setFlash('error', 'Error al actualizar el corporativo: ' . implode(', ', array_map(function($errors) {
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
     * Acción principal para mostrar el formulario de carga masiva de afiliados 
     * y procesar el archivo CSV subido.
     */
    /**
     * Acción principal para mostrar el formulario de carga masiva de afiliados 
     * y procesar el archivo CSV subido.
     * @return string|Response
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

    if ($model->load(Yii::$app->request->post())) {
        
        // Recoger la instancia del archivo subido
        $model->masivoFile = UploadedFile::getInstance($model, 'masivoFile');
        
        
        if ($model->validate()) {
            // --- RUTA DE ÉXITO DE VALIDACIÓN ---
            $filePath = $model->masivoFile->tempName;
            $corporativoId = $model->corporativo_id;
            
            // 2. Procesar el archivo
            $resultados = $this->procesarCSV($filePath, $corporativoId);
            
            // 3. Mostrar el resumen del proceso
            $errorCount = count($resultados['errors']);
            $successCount = $resultados['successCount'];

            if ($errorCount > 0) {
                 Yii::$app->session->setFlash('warning', 
                    'Proceso de carga finalizado con ' . 
                    $successCount . ' éxitos y ' . 
                    $errorCount . ' errores. Revise el detalle.'
                );
            } else {
                Yii::$app->session->setFlash('success', 
                    'Proceso de carga finalizado con ' . 
                    $successCount . ' afiliados cargados con éxito.'
                );
            }
            
            return $this->render('carga-masiva-resumen', [
                'resultados' => $resultados,
                'corporativo' => Corporativo::findOne($corporativoId),
            ]);
        } else {
            // --- RUTA DE FALLO DE VALIDACIÓN (ESTO ES LO IMPORTANTE) ---
            
            // Capturamos y aplanamos todos los errores de validación
            $validationErrors = ArrayHelper::flatten($model->getErrors());
            
            Yii::$app->session->setFlash('error', 
                'Error de validación del formulario de carga: ' . implode('; ', $validationErrors)
            );
        }
    }

    // Mostrar el formulario inicial
    return $this->render('carga-masiva-afiliados', [
        'model' => $model,
        'corporativos' => $corporativos,
    ]);
}

    /**
     * Lógica para leer el archivo CSV y procesar los afiliados.
     * Incluye validaciones, creación de transacciones, Contrato y Cuotas.
     * @param string $filePath Ruta temporal del archivo CSV.
     * @param int $corporativoId ID del corporativo destino.
     * @return array Array con el conteo de éxitos y los errores encontrados.
     */
    private function procesarCSV($filePath, $corporativoId)
    {
        // Abrir el archivo en modo lectura
        $handle = fopen($filePath, "r");
        if ($handle === false) {
            return ['successCount' => 0, 'errors' => ['No se pudo abrir el archivo.']];
        }

        // Definición de campos REQUERIDOS, incluyendo el CRÍTICO clinica_id
        $requiredFields = [
            'tipo_cedula', 'cedula', 'nombres', 'apellidos', 'fechanac', 'sexo', 
            'telefono', 'email', 'direccion', 'plan_id', 'clinica_id' 
        ];
        
        $headers = fgetcsv($handle, 1000, ","); 
        $successCount = 0;
        $errors = [];
        $lineNumber = 1;
        
        // Mapeo inverso de cabeceras para acceder a los datos por nombre de columna
        if ($headers === false) {
             return ['successCount' => 0, 'errors' => ['El archivo CSV está vacío o ilegible.']];
        }
        $headerMap = array_flip(array_map('trim', $headers));

        // Validación de cabeceras requeridas
        $missingHeaders = array_diff($requiredFields, array_keys($headerMap));
        if (!empty($missingHeaders)) {
            return [
                'successCount' => 0, 
                'errors' => ['Línea 1 (Cabecera): Faltan las siguientes columnas requeridas: ' . implode(', ', $missingHeaders)]
            ];
        }

        // Procesar cada línea del CSV
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            // Saltar líneas vacías
            if (empty(array_filter($data))) {
                continue;
            }
            
            $lineNumber++;
            $transaction = Yii::$app->db->beginTransaction();
            $cedula = trim($data[$headerMap['cedula']] ?? '');
            $logPrefix = "Línea {$lineNumber} (Cédula: " . ($cedula ?: 'N/A') . "): ";
            
            $userLogin = null; 

            try {
                // Extracción de datos clave
                $planId = (int) trim($data[$headerMap['plan_id']] ?? 0);
                $clinicaId = (int) trim($data[$headerMap['clinica_id']] ?? 0);
                $email = trim($data[$headerMap['email']] ?? '');

                // Validación de datos básicos
                if (empty($cedula) || empty($email) || $planId <= 0 || $clinicaId <= 0) {
                     throw new \Exception('Datos principales (cédula, email, plan_id, o clinica_id) están incompletos o inválidos.');
                }
                
                // 1. Validar relación Corporativo-Clínica (CRÍTICO)
                $isAuthorized = CorporativoClinica::find()
                    ->where(['corporativo_id' => $corporativoId])
                    ->andWhere(['clinica_id' => $clinicaId])
                    ->exists();

                if (!$isAuthorized) {
                    throw new \Exception("La Clínica ID {$clinicaId} NO está vinculada al Corporativo ID {$corporativoId} seleccionado.");
                }

                // 2. Validar existencia de Plan
                $plan = Planes::findOne($planId);
                if (!$plan) {
                    throw new \Exception('El Plan ID ' . $planId . ' no existe.');
                }
                
                // 3. Validación de Duplicados (Cédula y Email)
                if (UserDatos::find()->where(['cedula' => $cedula])->exists()) {
                     throw new \Exception("Ya existe un afiliado con la cédula {$cedula} registrado.");
                }

                if (User::find()->where(['email' => $email])->exists()) {
                    throw new \Exception("Ya existe un usuario con el email {$email} registrado.");
                }

                // 4. Crear el User Login
                $userLogin = new User();
                $userLogin->email = $email;
                $userLogin->username = $email; 
                // Se asigna una contraseña aleatoria inicial
                $userLogin->password_hash = User::setPassword(Yii::$app->security->generateRandomString(12)); 
                $userLogin->generateAuthKey();
                $userLogin->status = 10; // Asumir status activo
                
                if (!$userLogin->save()) {
                    throw new \Exception('Error al crear User Login: ' . implode(', ', $userLogin->getErrorSummary(true)));
                }

                // 5. Crear el registro UserDatos (Afiliado)
                $afiliado = new UserDatos();
                $afiliado->user_login_id = $userLogin->id; 
                
                // Mapeo de campos del CSV a UserDatos
                $afiliado->tipo_cedula = trim($data[$headerMap['tipo_cedula']]);
                $afiliado->cedula = $cedula; 
                $afiliado->nombres = trim($data[$headerMap['nombres']]);
                $afiliado->apellidos = trim($data[$headerMap['apellidos']]);
                $fechaNacString = trim($data[$headerMap['fechanac']]);
                $afiliado->fechanac = date('Y-m-d', strtotime($fechaNacString));
                $afiliado->sexo = trim($data[$headerMap['sexo']]);
                $afiliado->telefono_celular = trim($data[$headerMap['telefono']]); 
                $afiliado->email = $email;
                $afiliado->direccion_residencia = trim($data[$headerMap['direccion']]); 
                $afiliado->plan_id = $planId;
                $afiliado->clinica_id = $clinicaId; // **ID de Clínica tomado del CSV y validado**
                
                // Campos Fijos y Opcionales (desde UserDatosController::actionCreate)
                $afiliado->user_datos_type_id = 2; // Tipo: Afiliado Corporativo
                $afiliado->afiliado_corporativo_id = $corporativoId; 
                $afiliado->direccion_oficina = (isset($headerMap['direccion_oficina']) && !empty(trim($data[$headerMap['direccion_oficina']] ?? ''))) ? trim($data[$headerMap['direccion_oficina']]) : null; 
                $afiliado->telefono_oficina = (isset($headerMap['telefono_oficina']) && !empty(trim($data[$headerMap['telefono_oficina']] ?? ''))) ? trim($data[$headerMap['telefono_oficina']]) : null;
                $afiliado->tipo_sangre = (isset($headerMap['tipo_sangre']) && !empty(trim($data[$headerMap['tipo_sangre']] ?? ''))) ? trim($data[$headerMap['tipo_sangre']]) : null;
                $afiliado->rol_en_corporativo = (isset($headerMap['rol_en_corporativo']) && !empty(trim($data[$headerMap['rol_en_corporativo']] ?? ''))) ? trim($data[$headerMap['rol_en_corporativo']]) : null;
                
                $afiliado->role = 'afiliado'; 
                $afiliado->estatus = 'Creado'; 
                $afiliado->estatus_solvente = 'Si';
                $afiliado->codigoValidacion = UserHelper::getInstance()->generarCodigoValidacion();
                $afiliado->created_at = date('Y-m-d H:i:s');
                $afiliado->updated_at = date('Y-m-d H:i:s');
                
                if (!$afiliado->save()) {
                    throw new \Exception('Error al crear UserDatos: ' . implode(', ', $afiliado->getErrorSummary(true)));
                }

                // 6. Crear el registro Contratos
                $modelContrato = new Contratos();
                $modelContrato->user_id = $afiliado->id;
                $modelContrato->estatus = 'Registrado';
                $modelContrato->clinica_id = $afiliado->clinica_id; 
                $modelContrato->plan_id = $afiliado->plan_id; 
                $modelContrato->monto = $plan ? $plan->precio : 0;

                // Fechas de Contrato (desde CSV u Hoy)
                $fechaInicioContratoString = isset($headerMap['fecha_inicio_contrato']) ? trim($data[$headerMap['fecha_inicio_contrato']] ?? '') : null;
                $modelContrato->fecha_ini = (!empty($fechaInicioContratoString) && strtotime($fechaInicioContratoString) !== false) 
                                         ? date('Y-m-d', strtotime($fechaInicioContratoString)) 
                                         : date('Y-m-d');
                
                $fechaFinContratoString = isset($headerMap['fecha_vencimiento_contrato']) ? trim($data[$headerMap['fecha_vencimiento_contrato']] ?? '') : null;
                $modelContrato->fecha_vencimiento = (!empty($fechaFinContratoString) && strtotime($fechaFinContratoString) !== false) 
                                         ? date('Y-m-d', strtotime($fechaFinContratoString)) 
                                         : null;

                if (!$modelContrato->save()) {
                    throw new \Exception('Error al crear Contrato: ' . implode(', ', $modelContrato->getErrorSummary(true)));
                }

                // 7. Actualizar NroContrato y vincular UserDatos
                $anio_actual = date('Y');
                $modelContrato->nrocontrato = $afiliado->cedula . '-' . $anio_actual . '-' . $modelContrato->id;
                $afiliado->contrato_id = $modelContrato->id;

                if (!$modelContrato->save(false)) { 
                    throw new \Exception('Error al guardar el NroContrato.');
                }
                
                if (!$afiliado->save(false)) {
                    throw new \Exception('Error al guardar el contrato_id en UserDatos.');
                }
                
                // 8. Creación de Cuota inicial
                $modelCuota = new Cuotas();
                $modelCuota->contrato_id = $modelContrato->id;
                $modelCuota->fecha_vencimiento = $modelContrato->fecha_ini; 
                $modelCuota->monto = $modelContrato->monto;
                $modelCuota->estatus = 'pendiente';
                
                // Obtener Tasa de Cambio
                $tasaCambio = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one();
                $modelCuota->rate_usd_bs = $tasaCambio ? $tasaCambio->tasa_cambio : 1; 

                if (!$modelCuota->save()) {
                    throw new \Exception('Error al crear Cuota inicial: ' . implode(', ', $modelCuota->getErrorSummary(true)));
                }

                // 9. Crear la relación CorporativoUser (Tabla Intermedia)
                $corporativoUser = new CorporativoUser();
                $corporativoUser->corporativo_id = $corporativoId;
                $corporativoUser->user_id = $afiliado->id; 
                $corporativoUser->fecha_vinculacion = new \yii\db\Expression('NOW()');

                $asesorIdData = isset($headerMap['asesor_id']) ? trim($data[$headerMap['asesor_id']] ?? '') : null;
                if (!empty($asesorIdData)) {
                    $corporativoUser->asesor_id = (int) $asesorIdData;
                } 

                $rolEnCorporativo = isset($headerMap['rol_en_corporativo']) ? trim($data[$headerMap['rol_en_corporativo']] ?? '') : null;
                if (!empty($rolEnCorporativo)) {
                    $corporativoUser->rol_en_corporativo = $rolEnCorporativo;
                }

                if (!$corporativoUser->save()) {
                    throw new \Exception('Error al vincular con CorporativoUser: ' . implode(', ', $corporativoUser->getErrorSummary(true)));
                }
                
                // 10. Asignación de rol
                $auth = Yii::$app->authManager;
                $role = $auth->getRole('afiliado');
                if ($role) {
                    $auth->assign($role, $userLogin->id); 
                }
                
                // 11. Commit Final
                $transaction->commit();
                $successCount++;

            } catch (\Exception $e) {
                // Si algo falla, se revierte la transacción
                $transaction->rollBack();
                $errors[] = $logPrefix . $e->getMessage();
                
                // Si el User Login se creó antes del error, se elimina para no dejar huérfanos
                if (isset($userLogin) && !$userLogin->isNewRecord) {
                     $userLogin->delete();
                }
                Yii::error("Carga Masiva Error - " . $e->getMessage(), __METHOD__);
            }
        }

        fclose($handle);
        return ['successCount' => $successCount, 'errors' => $errors];
    }

    /**
     * Genera y fuerza la descarga de un archivo CSV de ejemplo (plantilla).
     * Incluye todos los campos requeridos y opcionales.
     * @return Response
     */
    public function actionDescargarPlantilla()
    {
        // Definición explícita de las cabeceras en orden (Requeridos + Opcionales)
        $headers = [
            'tipo_cedula', 'cedula', 'nombres', 'apellidos', 'fechanac', 'sexo', 'telefono', 
            'email', 'direccion', 'plan_id', 'clinica_id', // ¡clinica_id incluido!
            // -- CAMPOS ADICIONALES --
            'asesor_id', 'fecha_inicio_contrato', 'fecha_vencimiento_contrato', 
            'direccion_oficina', 'telefono_oficina', 'tipo_sangre', 'rol_en_corporativo' 
        ];
        
        // Datos de ejemplo
        $sampleData = [
            'C', '100234567', 'JUAN PABLO', 'ROJAS PEREZ', '1990-05-15', 'M', '8091234567', 
            'juan.pablo@ejemplo.com', 'CALLE SOL #123', '5', '1', // Ejemplo: Plan ID 5, Clínica ID 1
            // asesor_id se deja vacío (opcional)
            '', '2024-01-01', '2024-12-31', 
            'AV. PRINCIPAL, EDIF. AZUL, PISO 3', '8098765432', 'A+', 'Gerente' 
        ];

        // Iniciar el buffer de memoria para generar el CSV
        $output = fopen('php://temp', 'r+'); 
        
        // Añadir BOM de UTF-8 para compatibilidad
        fwrite($output, "\xEF\xBB\xBF");
        
        // Escribir la cabecera
        fputcsv($output, $headers, ','); 
        
        // Escribir el dato de ejemplo
        fputcsv($output, $sampleData, ',');

        rewind($output); 
        $content = stream_get_contents($output); 
        fclose($output); 

        // Usar sendContentAsFile para forzar la descarga
        return Yii::$app->response->sendContentAsFile($content, 'plantilla_afiliados_corporativos.csv', [
            'mimeType' => 'text/csv; charset=UTF-8',
            'inline' => false // Forzar la descarga
        ]);
    }


/** ----------------------------------------  Fin de Carga Masiva ----------------------------- */ 

    
    /**
     * Deletes an existing Corporativo model.
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
   public function actionDeuda($id)
    {
        $corporativo = $this->findModel($id);
        
        // Get all users associated with this corporativo
        $userIds = \yii\helpers\ArrayHelper::getColumn($corporativo->users, 'id');
        
        $allCuotas = [];
        $grandTotal = 0;

        if (!empty($userIds)) {
            // Get all contracts for these users
            $contratos = \app\models\Contratos::find()
                ->where(['user_id' => $userIds])
                ->all();
                
            $contratoIds = \yii\helpers\ArrayHelper::getColumn($contratos, 'id');
            
            if (!empty($contratoIds)) {
                // Use the exact same query as the debug action
                $allCuotas = \app\models\Cuotas::find()
                    ->where(['contrato_id' => $contratoIds])
                    ->andWhere(['estatus' => 'pendiente']) // lowercase
                    ->andWhere(['>', 'monto', 0])
                    ->all();
                    
                // Calculate grand total - ensure we're using the same calculation
                foreach ($allCuotas as $cuota) {
                    // Make sure we're converting to float properly
                    $amount = floatval($cuota->monto);
                    $grandTotal += $amount;
                    Yii::debug("Adding cuota {$cuota->id}: {$cuota->monto} -> {$amount}, running total: {$grandTotal}");
                }
            }
        }

        Yii::debug("Final calculation: grandTotal = {$grandTotal}, cuotas count = " . count($allCuotas));

        return $this->render('deuda', [
            'corporativo' => $corporativo,
            'allCuotas' => $allCuotas,
            'grandTotal' => $grandTotal,
        ]);
    }
}