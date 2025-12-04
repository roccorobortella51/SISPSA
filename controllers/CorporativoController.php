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
    
        if ($model->load(Yii::$app->request->post())) {
            
            $model->masivoFile = UploadedFile::getInstance($model, 'masivoFile');
            
            if ($model->validate()) {
                
                $filePath = $model->masivoFile->tempName;
                $corporativoId = $model->corporativo_id;
                
                $resultados = $this->procesarCSV($filePath, $corporativoId);
                
                // 3. Mostrar el resumen del proceso
                $errorCount = count($resultados['errors']);
                $successCount = $resultados['successCount'];
    
                $messageType = $errorCount > 0 ? 'warning' : 'success';
                $messageText = 'Proceso de carga finalizado con ' . 
                                $successCount . ' éxitos y ' . 
                                $errorCount . ' errores.' . 
                                ($errorCount > 0 ? ' Revise el detalle.' : ' Todo cargado correctamente.');

                Yii::$app->session->setFlash($messageType, $messageText);
                
                return $this->render('carga-masiva-resumen', [
                    'model' => $model,
                    'resultados' => $resultados,
                    'corporativo' => Corporativo::findOne($corporativoId),
                ]);
            } else {
                
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
     * Lógica principal para leer el archivo CSV y procesar los afiliados,
     * asegurando el cumplimiento de las reglas de validación de UserDatos.
     * * SE HAN AÑADIDO: nacionalidad, estado_civil, lugar_nacimiento, profesion, ocupacion,
     * actividad_economica, ramo_comercial, descripcion_actividad, ingreso_anual,
     * direccion_cobro, y telefono_residencia.
     * * @param string $filePath Ruta temporal del archivo CSV.
     * @param int $corporativoId ID del corporativo destino.
     * @return array Array con el conteo de éxitos y los errores encontrados.
     */
    private function procesarCSV($filePath, $corporativoId)
    {
        $handle = fopen($filePath, "r");
        if ($handle === false) {
            return ['successCount' => 0, 'errors' => ['No se pudo abrir el archivo.']];
        }

        // Campos requeridos originales
        $requiredFields = [
            'tipo_cedula', 'cedula', 'nombres', 'apellidos', 'fechanac', 'sexo', 
            'telefono', 'email', 'direccion', 'plan_id', 'clinica_id', 'estado' 
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
        $lineNumber = 1;
        
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
        
        // Pre-carga y mapeo de estados para validación
        if (empty($this->estadoNameToIdMap)) {
            $allEstados = RmEstado::find()->select(['id', 'nombre'])->asArray()->all();
            foreach ($allEstados as $estado) {
                // Normalizamos para búsqueda sin acentos/case-sensitive
                $normalizedName = strtolower($this->_normalizeString($estado['nombre']));
                $this->estadoNameToIdMap[$normalizedName] = $estado['id'];
            }
        }

        // Definición de rangos de valores válidos para nuevos campos
        $validRanges = [
            'estado_civil' => ['Soltero', 'Casado', 'Divorciado', 'Viudo'],
            'actividad_economica' => ['Industrial', 'Comercial', 'Profesional', 'Gubernamental'],
            'descripcion_actividad' => ['Independiente', 'Dependiente', 'Societaria'],
            'ingreso_anual' => [
                'De 1 a 5 Salarios mínimos', 
                'De 6 a 10 Salarios mínimos', 
                'De 11 a 20 Salarios mínimos', 
                'De 20 Salarios mínimos en adelante'
            ]
        ];

        // Procesar cada línea del CSV
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if (empty(array_filter($data, function($value) { return $value !== ''; }))) {
                continue;
            }
            
            $lineNumber++;
            $transaction = Yii::$app->db->beginTransaction();
            
            $cedulaCsv = trim($data[$headerMap['cedula']] ?? '');
            
            // --- CORRECCIÓN CRÍTICA: LIMPIEZA DE CÉDULA NUMÉRICA ---
            $cedulaLimpia = $this->limpiarSoloNumeros($cedulaCsv);
            // -------------------------------------------------------

            $logPrefix = "Línea {$lineNumber} (Cédula: " . ($cedulaCsv ?: 'N/A') . "): ";
            $userLogin = null; 

            try {
                // Extracción y saneamiento de datos clave
                $planId = (int) trim($data[$headerMap['plan_id']] ?? 0);
                $clinicaId = (int) trim($data[$headerMap['clinica_id']] ?? 0);
                $email = trim($data[$headerMap['email']] ?? '');
                
                $telefonoCelularCsv = trim($data[$headerMap['telefono']] ?? ''); 
                $direccionResidencia = trim($data[$headerMap['direccion']] ?? ''); 
                $estadoNameCsv = trim($data[$headerMap['estado']] ?? ''); 

                // Extracción y saneamiento de NUEVOS CAMPOS
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

                // Validación de datos principales
                if (empty($cedulaLimpia) || empty($email) || $planId <= 0 || $clinicaId <= 0) {
                     throw new \Exception('Datos principales (cédula, email, plan_id, o clinica_id) están incompletos o inválidos.');
                }
                
                // Validación del Teléfono Celular (se limpia a 11 dígitos, ej. 04121234567)
                $telefonoCelularLimpio = $this->limpiarTelefono($telefonoCelularCsv);
                
                // Validación del Teléfono de Residencia
                $telefonoResidenciaLimpio = !empty($telefonoResidenciaCsv) ? $this->limpiarTelefono($telefonoResidenciaCsv) : null;

                // Validación de Estado (Nombre a ID)
                if (empty($estadoNameCsv)) {
                    throw new \Exception("El campo 'estado' está vacío.");
                }

                $normalizedCsvName = strtolower($this->_normalizeString($estadoNameCsv));

                if (!isset($this->estadoNameToIdMap[$normalizedCsvName])) {
                    throw new \Exception("El nombre del estado '{$estadoNameCsv}' no fue encontrado en el catálogo. Verifique la ortografía.");
                }
                
                // Nombre del estado para asignación a UserDatos
                $estadoNombreParaUserDatos = $estadoNameCsv;

                // 2. Validar relaciones y existencia de Plan
                if (!CorporativoClinica::find()->where(['corporativo_id' => $corporativoId])->andWhere(['clinica_id' => $clinicaId])->exists()) {
                    throw new \Exception("La Clínica ID {$clinicaId} NO está vinculada al Corporativo ID {$corporativoId} seleccionado.");
                }

                $plan = Planes::findOne($planId);
                if (!$plan) {
                    throw new \Exception('El Plan ID ' . $planId . ' no existe o no está activo.');
                }
                
                // 3. Validación de Duplicados (Cédula y Email)
                if (UserDatos::find()->where(['cedula' => $cedulaLimpia])->exists()) {
                     throw new \Exception("Ya existe un afiliado con la cédula {$cedulaLimpia} registrado.");
                }

                if (User::find()->where(['email' => $email])->exists()) {
                    throw new \Exception("Ya existe un usuario con el email {$email} registrado.");
                }

                // 4. Validaciones de Rango para Nuevos Campos (si no están vacíos)
                if (!empty($estadoCivilCsv) && !in_array($estadoCivilCsv, $validRanges['estado_civil'])) {
                    throw new \Exception("Valor inválido para 'estado_civil': '{$estadoCivilCsv}'. Debe ser uno de: " . implode(', ', $validRanges['estado_civil']));
                }
                if (!empty($actividadEconomicaCsv) && !in_array($actividadEconomicaCsv, $validRanges['actividad_economica'])) {
                    throw new \Exception("Valor inválido para 'actividad_economica': '{$actividadEconomicaCsv}'. Debe ser uno de: " . implode(', ', $validRanges['actividad_economica']));
                }
                if (!empty($descripcionActividadCsv) && !in_array($descripcionActividadCsv, $validRanges['descripcion_actividad'])) {
                    throw new \Exception("Valor inválido para 'descripcion_actividad': '{$descripcionActividadCsv}'. Debe ser uno de: " . implode(', ', $validRanges['descripcion_actividad']));
                }
                if (!empty($ingresoAnualCsv) && !in_array($ingresoAnualCsv, $validRanges['ingreso_anual'])) {
                    throw new \Exception("Valor inválido para 'ingreso_anual': '{$ingresoAnualCsv}'. Debe ser uno de: " . implode(', ', $validRanges['ingreso_anual']));
                }
                
                // 5. Crear el User Login
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

                // 6. Crear el registro UserDatos (Afiliado)
                $afiliado = new UserDatos();
                $afiliado->user_login_id = $userLogin->id; 
                
                // Mapeo de campos del CSV (Existentes)
                $afiliado->tipo_cedula = trim($data[$headerMap['tipo_cedula']]);
                $afiliado->cedula = $cedulaLimpia; // Asignación del valor NUMÉRICO
                $afiliado->nombres = trim($data[$headerMap['nombres']]);
                $afiliado->apellidos = trim($data[$headerMap['apellidos']]);
                
                // Validar y Formatear fecha de nacimiento (YYYY-MM-DD)
                $fechaNacString = trim($data[$headerMap['fechanac']]);
                $dateObject = \DateTime::createFromFormat('d/m/Y', $fechaNacString) ?: \DateTime::createFromFormat('Y-m-d', $fechaNacString);
                
                if (!$dateObject) {
                    throw new \Exception("La fecha de nacimiento '{$fechaNacString}' no tiene un formato de fecha válido (Ej: DD/MM/AAAA o YYYY-MM-DD).");
                }
                $afiliado->fechanac = $dateObject->format('Y-m-d');
                
                // Mapeo de Sexo para cumplir con el range de validación: ['Masculino', 'Femenino', 'Otro']
                $sexoCsv = strtoupper(trim($data[$headerMap['sexo']]));
                if (in_array($sexoCsv, ['M', 'MASCULINO'])) {
                    $afiliado->sexo = 'Masculino';
                } elseif (in_array($sexoCsv, ['F', 'FEMENINO'])) {
                    $afiliado->sexo = 'Femenino';
                } else {
                    $afiliado->sexo = $sexoCsv;
                }
                
                // Asignación de Teléfono, Dirección y ESTADO (como string/nombre)
                $afiliado->telefono_celular = $telefonoCelularLimpio; 
                $afiliado->telefono = $telefonoCelularLimpio; 
                $afiliado->direccion_residencia = $direccionResidencia; 
                $afiliado->direccion = $direccionResidencia; 
                
                // ** IMPORTANTE: Asignación del NOMBRE del estado (string) - Cumple con la validación del modelo **
                $afiliado->estado = $estadoNombreParaUserDatos; 
                
                // Mapeo de campos del CSV (Nuevos Campos)
                $afiliado->nacionalidad = $nacionalidad ?: null;
                $afiliado->estado_civil = $estadoCivilCsv ?: null;
                $afiliado->lugar_nacimiento = $lugarNacimiento ?: null;
                $afiliado->profesion = $profesion ?: null;
                $afiliado->ocupacion = $ocupacion ?: null;
                $afiliado->actividad_economica = $actividadEconomicaCsv ?: null;
                $afiliado->ramo_comercial = $ramoComercial ?: null;
                $afiliado->descripcion_actividad = $descripcionActividadCsv ?: null;
                $afiliado->ingreso_anual = $ingresoAnualCsv ?: null;
                $afiliado->direccion_cobro = $direccionCobro ?: null;
                $afiliado->telefono_residencia = $telefonoResidenciaLimpio; // Limpiado o null
                
                // Campos Fijos y Opcionales (si existen en el CSV)
                $afiliado->user_datos_type_id = 2; // Tipo: Afiliado Corporativo
                $afiliado->afiliado_corporativo_id = $corporativoId; 
                $afiliado->email = $email; 

                $afiliado->direccion_oficina = (isset($headerMap['direccion_oficina']) && !empty(trim($data[$headerMap['direccion_oficina']] ?? ''))) ? trim($data[$headerMap['direccion_oficina']]) : null; 
                
                $telefonoOficinaCsv = (isset($headerMap['telefono_oficina']) && !empty(trim($data[$headerMap['telefono_oficina']] ?? ''))) ? trim($data[$headerMap['telefono_oficina']]) : null;
                $afiliado->telefono_oficina = $telefonoOficinaCsv ? $this->limpiarTelefono($telefonoOficinaCsv) : null;
                
                $afiliado->tipo_sangre = (isset($headerMap['tipo_sangre']) && !empty(trim($data[$headerMap['tipo_sangre']] ?? ''))) ? trim($data[$headerMap['tipo_sangre']]) : null;
                
                $afiliado->role = 'afiliado'; 
                $afiliado->estatus = 'Creado'; 
                $afiliado->estatus_solvente = 'Si';
                $afiliado->codigoValidacion = UserHelper::getInstance()->generarCodigoValidacion();
                $afiliado->created_at = date('Y-m-d H:i:s');
                $afiliado->updated_at = date('Y-m-d H:i:s');
                $afiliado->plan_id = $planId;
                $afiliado->clinica_id = $clinicaId; 
                
                if (!$afiliado->save()) {
                    $errorMessages = ArrayHelper::flatten($afiliado->getErrors());
                    
                    Yii::error([
                        'ERROR_VALIDACION_MASIVA' => $lineNumber,
                        'Modelo' => 'UserDatos',
                        'Errores_Detalle' => $afiliado->getErrors(), 
                    ], __METHOD__);
                    
                    throw new \Exception('Error al crear UserDatos (Validación): ' . implode('; ', $errorMessages));
                }
                
                // 7. Creación de Contrato, Cuota, CorporativoUser y Asignación de Rol
                $modelContrato = new Contratos();
                $modelContrato->user_id = $afiliado->id; 
                $modelContrato->estatus = 'Registrado';
                $modelContrato->clinica_id = $afiliado->clinica_id; 
                $modelContrato->plan_id = $afiliado->plan_id; 
                $modelContrato->monto = $plan ? $plan->precio : 0;
                
                $modelContrato->fecha_ini = date('Y-m-d'); 
                if (isset($headerMap['fecha_inicio_contrato'])) {
                    $fechaInicioContratoString = trim($data[$headerMap['fecha_inicio_contrato']] ?? '');
                    if (!empty($fechaInicioContratoString) && strtotime($fechaInicioContratoString) !== false) {
                        $modelContrato->fecha_ini = date('Y-m-d', strtotime($fechaInicioContratoString));
                    }
                }
                
                $modelContrato->fecha_ven = null; 
                if (isset($headerMap['fecha_vencimiento_contrato'])) {
                    $fechaFinContratoString = trim($data[$headerMap['fecha_vencimiento_contrato']] ?? '');
                    if (!empty($fechaFinContratoString) && strtotime($fechaFinContratoString) !== false) {
                        $modelContrato->fecha_ven = date('Y-m-d', strtotime($fechaFinContratoString));
                    }
                }
                
                if (!$modelContrato->save()) {
                    Yii::error(['Error_Contrato' => $modelContrato->getErrors()], __METHOD__);
                    throw new \Exception('Error al crear Contrato: ' . implode(', ', ArrayHelper::flatten($modelContrato->getErrors())));
                }

                $anio_actual = date('Y');
                $modelContrato->nrocontrato = $afiliado->cedula . '-' . $anio_actual . '-' . $modelContrato->id;
                $afiliado->contrato_id = $modelContrato->id;

                if (!$modelContrato->save(false) || !$afiliado->save(false)) { 
                    throw new \Exception('Error al guardar NroContrato o contrato_id.');
                }
                
                $modelCuota = new Cuotas();
                $modelCuota->contrato_id = $modelContrato->id;
                $modelCuota->fecha_vencimiento = $modelContrato->fecha_ini; 
                $modelCuota->monto = $modelContrato->monto;
                $modelCuota->estatus = 'pendiente';
                $modelCuota->rate_usd_bs = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one()->tasa_cambio ?? 1; 

                if (!$modelCuota->save()) {
                    Yii::error(['Error_Cuota' => $modelCuota->getErrors()], __METHOD__);
                    throw new \Exception('Error al crear Cuota inicial: ' . implode(', ', ArrayHelper::flatten($modelCuota->getErrors())));
                }

                $corporativoUser = new CorporativoUser();
                $corporativoUser->corporativo_id = $corporativoId;
                $corporativoUser->user_id = $afiliado->id; 
                $corporativoUser->fecha_vinculacion = new Expression('NOW()');

                $asesorIdData = isset($headerMap['asesor_id']) ? trim($data[$headerMap['asesor_id']] ?? '') : null;
                if (!empty($asesorIdData)) {
                    $corporativoUser->asesor_id = (int) $asesorIdData;
                } 

                $rolEnCorporativo = isset($headerMap['rol_en_corporativo']) ? trim($data[$headerMap['rol_en_corporativo']] ?? '') : null;
                if (!empty($rolEnCorporativo)) {
                    $corporativoUser->rol_en_corporativo = $rolEnCorporativo;
                }

                if (!$corporativoUser->save()) {
                    Yii::error(['Error_CorporativoUser' => $corporativoUser->getErrors()], __METHOD__);
                    throw new \Exception('Error al vincular con CorporativoUser: ' . implode(', ', ArrayHelper::flatten($corporativoUser->getErrors())));
                }
                
                // Asignar rol 'afiliado'
                $auth = Yii::$app->authManager;
                $role = $auth->getRole('afiliado');
                if ($role) {
                    $auth->assign($role, $userLogin->id); 
                }
                
                $transaction->commit();
                $successCount++;

            } catch (\Exception $e) {
                $transaction->rollBack();
                $errors[] = $logPrefix . $e->getMessage();
                
                if (isset($userLogin) && !$userLogin->isNewRecord) {
                     try {
                        $userLogin->delete(); // Limpiar User Login si falló algo posterior
                     } catch (\Throwable $th) {
                        Yii::error("Error de limpieza del User Login: " . $th->getMessage(), __METHOD__);
                     }
                }
                Yii::error("Carga Masiva Error General en Línea {$lineNumber} - " . $e->getMessage(), __METHOD__);
            }
        }

        fclose($handle);
        return ['successCount' => $successCount, 'errors' => $errors];
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
            'tipo_cedula', 'cedula', 'nombres', 'apellidos', 'fechanac', 'sexo', 'telefono', 
            'email', 'direccion', 'plan_id', 'clinica_id', 'estado', 
            
            // Nuevos campos
            'nacionalidad', 'estado_civil', 'lugar_nacimiento', 'profesion', 'ocupacion',
            'actividad_economica', 'ramo_comercial', 'descripcion_actividad', 'ingreso_anual',
            'direccion_cobro', 'telefono_residencia',

            // Campos opcionales existentes
            'asesor_id', 'fecha_inicio_contrato', 'fecha_vencimiento_contrato', 
            'direccion_oficina', 'telefono_oficina', 'tipo_sangre', 'rol_en_corporativo' 
        ];
        
        $sampleData = [
            'V', '19088456', 'JUAN PABLO', 'ROJAS PEREZ', '1990-05-15', 'M', '04121234567', 
            'juan.pablo@yopmail.com', 'CALLE SOL #123', '2', '2', 'MIRANDA', // ESTADO (NOMBRE)
            
            // Datos de muestra para nuevos campos
            'VENEZOLANA', 'Casado', 'CARACAS', 'INGENIERO', 'EMPLEADO',
            'Profesional', 'SERVICIOS', 'Dependiente', 'De 6 a 10 Salarios mínimos',
            'DIRECCION PARA ENVIAR ESTADOS DE CUENTA', '02125551234',

            // Datos de muestra para campos opcionales existentes
            '', '2025-12-01', '2026-12-01', 
            'AV. PRINCIPAL, EDIF. AZUL, PISO 3', '2125871425', 'A+', 'Afiliado' 
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

    /**
     * Limpia y formatea un número de teléfono (celular o fijo).
     * Asegura que el formato sea de 11 dígitos, cumpliendo la validación de UserDatos.
     * @param string $telefono El número de teléfono del CSV.
     * @return string El número de teléfono saneado (11 dígitos, ej. 04121234567).
     */
    protected function limpiarTelefono(string $telefono): string
    {
        // 1. Quitar todos los caracteres que no sean dígitos
        $numeroLimpio = preg_replace('/[^0-9]/', '', $telefono);

        // 2. Si tiene 10 dígitos y no empieza con '0', se asume que le falta el '0' inicial 
        if (strlen($numeroLimpio) === 10 && substr($numeroLimpio, 0, 1) !== '0') {
            $numeroLimpio = '0' . $numeroLimpio;
        }

        // Si es más largo, se toman los últimos 11 (para manejar códigos de país si los hubiera)
        if (strlen($numeroLimpio) > 11) {
             $numeroLimpio = substr($numeroLimpio, -11);
        }
        
        return $numeroLimpio;
    }
    
    /**
     * Limpia una cadena para dejar únicamente caracteres numéricos.
     * Requerido porque el campo 'cedula' es INTEGER en la DB.
     * @param string $input La cédula con posibles prefijos (V, E, J, G, guiones).
     * @return string Solo los dígitos de la cédula.
     */
    protected function limpiarSoloNumeros(string $input): string
    {
        // Esta es la función crítica que convierte "V-19.088.456" a "19088456"
        return preg_replace('/[^0-9]/', '', $input);
    }
    
    /**
     * Normaliza una cadena quitando acentos y caracteres especiales (para buscar estados).
     * @param string $string La cadena a normalizar.
     * @return string La cadena normalizada.
     */
    private function _normalizeString($string)
    {
        $unwanted_array = [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ñ'=>'n', 
            'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U', 'Ñ'=>'N', 
            'ä'=>'a', 'ë'=>'e', 'ï'=>'i', 'ö'=>'o', 'ü'=>'u',
            ' '=>'', 
        ];

        return strtr($string, $unwanted_array);
    }


    public function actionObtenerClinicasPorCorporativo($id)
{
    Yii::$app->response->format = Response::FORMAT_JSON;

    // 1. Obtener los modelos CorporativoClinica asociados
    $asociaciones = CorporativoClinica::find()
        ->where(['corporativo_id' => $id])
        ->all();
        
    $clinicasData = [];

    // 2. Iterar sobre las asociaciones para obtener los datos de la clínica
    foreach ($asociaciones as $asociacion) {
        // Asumiendo que el modelo CorporativoClinica tiene una relación 'clinica'
        // y que el modelo de Clínica tiene las propiedades 'id' y 'nombre'.
        $clinicasData[] = [
            'id' => $asociacion->clinica->id,
            'nombre' => $asociacion->clinica->nombre, // Asegúrate de que 'nombre' es el atributo correcto.
        ];
    }
    
    // Devolver el array JSON
    return $clinicasData;
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

        try {
            // 2. Consulta con Eager Loading (Carga anticipada de relaciones)
            // Se asume que \app\models\RmClinica tiene la relación 'planes'
            $clinicas = \app\models\RmClinica::find()
                // FILTRO: joinWith para asegurar que la clínica está asociada al corporativo ($id)
                ->joinWith(['corporativoClinicas' => function ($query) use ($id) {
                    $query->andWhere(['corporativo_id' => $id]);
                }])
                // CARGA: with() para cargar los planes asociados a CADA clínica encontrada.
                ->with('planes') 
                ->all();

            // 3. Mapeo a Array para generar la estructura JSON deseada
            $data = \yii\helpers\ArrayHelper::toArray($clinicas, [
                'app\models\RmClinica' => [
                    'id',
                    'nombre' => 'nombre',
                    
                    // Mapeo de la relación anidada 'planes'
                    'planes' => function ($clinica) {
                        return \yii\helpers\ArrayHelper::toArray($clinica->planes, [
                            'app\models\Planes' => [
                                'id',
                                'nombre',
                            ]
                        ]);
                    },
                ]
            ]);

            // Respuesta de éxito con los datos
            return ['success' => true, 'data' => $data];

        } catch (\Exception $e) {
            // Manejo de errores detallado
            Yii::error('Error al obtener clínicas con planes: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'error' => 'Error al obtener clínicas con planes: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }
    }


/** ----------------------------------------  Fin de Carga Masiva -------------------------------------- */ 

    
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
        
        // Get ALL user IDs for this corporate (BOTH methods)
        $allUserIds = $this->getAllCorporateUserIds($id);
        
        $allCuotas = [];
        $grandTotal = 0;

        if (!empty($allUserIds)) {
            // Use INNER JOIN approach (same as actionPagos())
            $allCuotas = \app\models\Cuotas::find()
            ->select('cuotas.*')
            ->innerJoinWith(['contrato' => function($query) {
                $query->innerJoinWith(['user']); // Join with user_datos for ordering by name
            }])
            ->where(['contratos.user_id' => $allUserIds])
            ->andWhere(['cuotas.estatus' => 'pendiente'])
            ->andWhere(['>', 'cuotas.monto', 0])
            // Order by expiration date (oldest first) and then by affiliate name
            ->orderBy([
                'cuotas.fecha_vencimiento' => SORT_ASC, // Oldest first
                'user_datos.nombres' => SORT_ASC, // Then by first name
            ])
            ->all();
                
            // Calculate grand total
            foreach ($allCuotas as $cuota) {
                $amount = floatval($cuota->monto);
                $grandTotal += $amount;
            }
        }

        Yii::debug("Corporate ID={$id}: Total users=" . count($allUserIds) . 
                ", Total fees=" . count($allCuotas) . 
                ", Total amount={$grandTotal}");

        return $this->render('deuda', [
            'corporativo' => $corporativo,
            'allCuotas' => $allCuotas,
            'grandTotal' => $grandTotal,
        ]);
    }

    /**
     * Helper method to get ALL user IDs for a corporate (both direct and indirect)
     */
    private function getAllCorporateUserIds($corporativoId)
    {
        // 1. Direct users (from corporativo_user table)
        $directUserIds = \app\models\CorporativoUser::find()
            ->select('user_id')
            ->where(['corporativo_id' => $corporativoId])
            ->column();
        
        // 2. Indirect users (from user_datos.afiliado_corporativo_id field)
        $indirectUserIds = \app\models\UserDatos::find()
            ->select('id')
            ->where(['afiliado_corporativo_id' => $corporativoId])
            ->column();
        
        // 3. Combine and remove duplicates
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
                    ->andWhere(['cuotas.estatus' => 'pendiente'])
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
}