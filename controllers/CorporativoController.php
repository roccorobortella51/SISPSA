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
use app\models\Pagos; // Requerido para manejar el pago
use app\models\TasaCambio; // Requerido para obtener la tasa
use app\models\Cuotas; // Requerido para manejar las cuotas
use app\components\UserHelper; // Requerido para subir archivos
use app\models\MasivoAfiliadosForm;
use yii\helpers\ArrayHelper;
use yii\web\Response;

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
        $model->user_id = null; // Pago corporativo, no específico de user
        $model->estatus = 'Por Conciliar';

        // Obtener IDs de usuarios asociados
        $userIds = CorporativoUser::find()
            ->select('user_id')
            ->where(['corporativo_id' => $id])
            ->column();

        $allCuotas = [];
        $grandTotal = 0;
        if (!empty($userIds)) {
            foreach ($userIds as $userId) {
                $user = UserDatos::findOne($userId);
                if ($user) {
                    // Cargar cuotas pendientes
                    $cuotas = Cuotas::find()
                        ->select('cuotas.*')
                        ->innerJoin('contratos', 'contratos.id = cuotas.contrato_id')
                        ->where(['contratos.user_id' => $userId, 'cuotas.estatus' => 'pendiente'])
                        ->orderBy(['cuotas.fecha_vencimiento' => SORT_ASC])
                        ->all();

                    foreach ($cuotas as $cuota) {
                        if (($cuota->monto_usd ?? 0) > 0) {
                            $allCuotas[] = $cuota;
                            $grandTotal += $cuota->monto_usd;
                        }
                    }
                }
            }
        }
        
        // --- INICIALIZACIÓN DE LA TASA Y FECHA DE PAGO ---
        $model->fecha_pago = date('Y-m-d'); // Set default date
        // Formatear a 2 decimales para la vista
        $model->tasa = number_format($this->getTasaCambioReferencial(), 2, '.', '');
        // --- FIN DE LA INICIALIZACIÓN ---

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // Para pago corporativo total: marcar TODAS las cuotas pendientes como pagadas
                $montoPagadoPosted = (float)($model->monto_pagado ?: 0);
                
                // Validar que el monto coincida con el total
                if (abs($grandTotal - $montoPagadoPosted) > 0.01) {
                    $model->addError('monto_pagado', 'El monto a pagar debe coincidir con el total de cuotas pendientes.');
                    Yii::$app->session->setFlash('warning', 'El monto no coincide con el total de cuotas pendientes.');
                } else {
                    // Manejar archivo y guardar
                    $model->imagen_prueba_file = \yii\web\UploadedFile::getInstance($model, 'imagen_prueba_file');

                    if ($model->imagen_prueba_file) {
                        // Lógica de subida (similar a PagosController)
                        $folder = 'Pago';
                        $fileName = uniqid('pago_corp_') . '.' . $model->imagen_prueba_file->extension;
                        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                        if ($model->imagen_prueba_file->saveAs($tempFilePath)) {
                            // Usamos UserHelper para la subida
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
                        // El campo monto_usd en Pagos en realidad está guardando el monto en Bolívares (Bs)
                        $post = $this->request->post('Pagos');
                        $model->monto_usd = $post['monto_usd'] ?? null;
                        
                        if ($model->save(false)) {
                            $contratosActualizados = [];
                            
                            // Marcar TODAS las cuotas pendientes del corporativo como pagadas
                            foreach ($allCuotas as $cuota) {
                                if ($cuota->estatus === 'pendiente') {
                                    $cuota->estatus = 'pagado';
                                    $cuota->fecha_pago = $model->fecha_pago ?: date('Y-m-d');
                                    // Aseguramos que la tasa guardada es la del modelo de pago
                                    $cuota->rate_usd_bs = $model->tasa; 
                                    $cuota->id_pago = $model->id; // Guardar ID del pago para rastreo
                                    $cuota->save(false);

                                    // Recopilar contratos únicos para actualizar estatus
                                    if (!in_array($cuota->contrato_id, $contratosActualizados)) {
                                        $contratosActualizados[] = $cuota->contrato_id;
                                    }
                                }
                            }
                            
                            // Actualizar estatus de contratos
                            foreach ($contratosActualizados as $contratoId) {
                                $contrato = Contratos::findOne($contratoId);
                                if ($contrato) {
                                    // Verificar si el contrato tiene cuotas pendientes restantes
                                    $cuotasPendientes = Cuotas::find()
                                        ->where(['contrato_id' => $contratoId, 'estatus' => 'pendiente'])
                                        ->count();
                                    
                                    // Si no hay cuotas pendientes, activar el contrato
                                    if ($cuotasPendientes == 0 && $contrato->estatus !== 'activo') {
                                        $contrato->estatus = 'activo';
                                        $contrato->save(false);
                                    }
                                }
                            }

                            $transaction->commit();
                            Yii::$app->session->setFlash('success', 'Pago corporativo registrado exitosamente. Las cuotas han sido actualizadas.');
                            return $this->redirect(['view', 'id' => $corporativo->id]);
                        } else {
                            throw new \Exception('Error al guardar el pago.');
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

    return $this->render('update', [
        'model' => $model,
        'afiliadosActuales' => $afiliadosActuales, // Es útil pasar esto a la vista para la selección por defecto
    ]);
}

// controlador de carga masiva de corporativos

public function actionCargaMasivaAfiliados()
    {
        $model = new MasivoAfiliadosForm();
        
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
                
                Yii::$app->session->setFlash('success', 'Proceso de carga finalizado. ' . $resultados['successCount'] . ' afiliados cargados con éxito, ' . count($resultados['errors']) . ' filas con errores.');
                
                return $this->render('carga-masiva-resumen', [
                    'resultados' => $resultados,
                    'corporativo' => Corporativo::findOne($corporativoId),
                ]);
            } else {
                Yii::$app->session->setFlash('error', 'Error de validación del formulario de carga.');
            }
        }

        return $this->render('carga-masiva-afiliados', [
            'model' => $model,
            'corporativos' => $corporativos,
        ]);
    }

        private function procesarCSV($filePath, $corporativoId)
    {
        $handle = fopen($filePath, "r");
        if ($handle === false) {
            return ['successCount' => 0, 'errors' => ['No se pudo abrir el archivo.']];
        }

        $requiredFields = ['tipo_cedula', 'cedula', 'nombres', 'apellidos', 'fechanac', 'sexo', 'telefono', 'email', 'direccion', 'plan_id'];
        
        $headers = fgetcsv($handle, 1000, ","); 
        $successCount = 0;
        $errors = [];
        $lineNumber = 1;
        
        // Campos nuevos que deben buscarse en el CSV
        $validFields = array_merge($requiredFields, [
            'asesor_id', 
            'asesor_nombre',
            'fecha_inicio_contrato', 
            'fecha_vencimiento_contrato', 
            'direccion_oficina', 
            'telefono_oficina', 
            'tipo_sangre',
            'rol_en_corporativo'
        ]);

        if (array_diff($requiredFields, $headers)) {
            return ['successCount' => 0, 'errors' => ['Línea 1 (Cabecera): Las columnas requeridas están incompletas o mal nombradas.']];
        }
        
        // Mapeo inverso de cabeceras para acceder a los datos por nombre de columna
        $headerMap = array_flip($headers);

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if (empty(array_filter($data))) {
                continue;
            }
            
            $lineNumber++;
            $transaction = Yii::$app->db->beginTransaction();
            $logPrefix = "Línea {$lineNumber} (Cédula: " . (isset($data[$headerMap['cedula']]) ? $data[$headerMap['cedula']] : 'N/A') . "): ";
            
            $userLogin = null; 

            try {
                // --- Validación de Plan y Clínica (Lógica de su sistema) ---
                if (!isset($headerMap['plan_id'])) {
                     throw new \Exception('Columna plan_id no encontrada.');
                }
                
                $planId = (int) $data[$headerMap['plan_id']];
                $plan = Planes::findOne($planId);
                
                if (!$plan || !$plan->clinica_id) {
                    throw new \Exception('El Plan ID ' . $planId . ' no existe o no tiene una clínica asociada.');
                }
                
                $isAuthorized = CorporativoClinica::find()
                    ->where(['corporativo_id' => $corporativoId])
                    ->andWhere(['clinica_id' => $plan->clinica_id])
                    ->exists();

                if (!$isAuthorized) {
                    throw new \Exception("La clínica asociada al Plan ID {$planId} NO está autorizada para el Corporativo seleccionado.");
                }

                // VALIDACIÓN DE CEDULA Y EMAIL: Evitar duplicados
                $cedula = trim($data[$headerMap['cedula']]);
                $email = trim($data[$headerMap['email']]);

                if (UserDatos::find()->where(['cedula' => $cedula])->exists()) {
                     throw new \Exception("Ya existe un afiliado con la cédula {$cedula} registrado.");
                }

                if (User::find()->where(['email' => $email])->exists()) {
                    throw new \Exception("Ya existe un usuario con el email {$email} registrado.");
                }

                // 1. Crear el User Login
                $userLogin = new User();
                $userLogin->email = $email;
                $userLogin->username = $email; 
                $userLogin->setPassword(Yii::$app->security->generateRandomString(12)); 
                $userLogin->generateAuthKey();
                $userLogin->status = 10; 
                
                if (!$userLogin->save()) {
                    throw new \Exception('Error al crear User Login: ' . implode(', ', $userLogin->getErrorSummary(true)));
                }

                // 2. Crear el registro UserDatos (Afiliado)
                $afiliado = new UserDatos();
                $afiliado->user_login_id = $userLogin->id; 
                
                // Campos Personales y Requeridos
                $afiliado->tipo_cedula = trim($data[$headerMap['tipo_cedula']]);
                $afiliado->cedula = $cedula; 
                $afiliado->nombres = trim($data[$headerMap['nombres']]);
                $afiliado->apellidos = trim($data[$headerMap['apellidos']]);
                $afiliado->fechanac = date('Y-m-d', strtotime(trim($data[$headerMap['fechanac']])));
                $afiliado->sexo = trim($data[$headerMap['sexo']]);
                $afiliado->telefono = trim($data[$headerMap['telefono']]);
                $afiliado->email = $email;
                $afiliado->direccion = trim($data[$headerMap['direccion']]); 
                $afiliado->plan_id = $planId;
                $afiliado->clinica_id = $plan->clinica_id; 
                
                // --- CAMPOS CORPORATIVOS CLAVE (FIX AÑADIDO) ---
                $afiliado->user_datos_type_id = 2; // Tipo: Afiliado Corporativo
                // LA CORRECCIÓN CLAVE: Asignar el ID del Corporativo al campo que utiliza su actionCreate
                $afiliado->afiliado_corporativo_id = $corporativoId; 
                
                // Direccion y Teléfono de Oficina 
                if (isset($headerMap['direccion_oficina'])) {
                    $afiliado->direccion_oficina = trim($data[$headerMap['direccion_oficina']]);
                } else {
                     $afiliado->direccion_oficina = trim($data[$headerMap['direccion']]); 
                }

                if (isset($headerMap['telefono_oficina'])) {
                    $afiliado->telefono_oficina = trim($data[$headerMap['telefono_oficina']]);
                }
                
                // Tipo de Sangre
                if (isset($headerMap['tipo_sangre'])) {
                    $afiliado->tipo_sangre = trim($data[$headerMap['tipo_sangre']]);
                }
                
                $afiliado->estado = 'Activo'; 
                $afiliado->role = 'afiliado'; 
                $afiliado->estatus = 'Activo'; 
                $afiliado->paso = 1.0; 
                
                if (!$afiliado->save()) {
                    throw new \Exception('Error al crear UserDatos: ' . implode(', ', $afiliado->getErrorSummary(true)));
                }
                
                // 3. Crear la relación CorporativoUser (Tabla Intermedia)
                $corporativoUser = new CorporativoUser();
                $corporativoUser->corporativo_id = $corporativoId;
                $corporativoUser->user_id = $afiliado->id; // ID del registro UserDatos
                $corporativoUser->fecha_vinculacion = new \yii\db\Expression('NOW()');

                // Campos de Contrato y Asesor
                if (isset($headerMap['asesor_id'])) {
                    $corporativoUser->asesor_id = (int) $data[$headerMap['asesor_id']];
                }
                if (isset($headerMap['asesor_nombre'])) {
                    $corporativoUser->asesor_nombre = trim($data[$headerMap['asesor_nombre']]);
                }

                if (isset($headerMap['fecha_inicio_contrato'])) {
                    $dateString = trim($data[$headerMap['fecha_inicio_contrato']]);
                    if (!empty($dateString)) {
                         $corporativoUser->fecha_inicio_contrato = date('Y-m-d', strtotime($dateString));
                    }
                }
                if (isset($headerMap['fecha_vencimiento_contrato'])) {
                    $dateString = trim($data[$headerMap['fecha_vencimiento_contrato']]);
                    if (!empty($dateString)) {
                         $corporativoUser->fecha_vencimiento_contrato = date('Y-m-d', strtotime($dateString));
                    }
                }
                
                if (isset($headerMap['rol_en_corporativo'])) {
                    $corporativoUser->rol_en_corporativo = trim($data[$headerMap['rol_en_corporativo']]);
                }

                if (!$corporativoUser->save()) {
                    throw new \Exception('Error al vincular con CorporativoUser: ' . implode(', ', $corporativoUser->getErrorSummary(true)));
                }
                
                $transaction->commit();
                $successCount++;

            } catch (\Exception $e) {
                $transaction->rollBack();
                $errors[] = $logPrefix . $e->getMessage();
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
     * Incluye todos los campos requeridos para la carga masiva corporativa.
     * @return Response
     */
    public function actionDescargarPlantilla()
    {
        // Definición explícita de las cabeceras en orden (Requeridos + Opcionales)
        $headers = [
            'tipo_cedula', 'cedula', 'nombres', 'apellidos', 'fechanac', 'sexo', 'telefono', 
            'email', 'direccion', 'plan_id', 
            // -- CAMPOS ADICIONALES --
            'asesor_id', 'asesor_nombre', 'fecha_inicio_contrato', 'fecha_vencimiento_contrato', 
            'direccion_oficina', 'telefono_oficina', 'tipo_sangre', 'rol_en_corporativo'
        ];
        
        // Definición explícita de los datos de ejemplo (misma cantidad y orden que headers)
        $sampleData = [
            'C', '100234567', 'JUAN PABLO', 'ROJAS PEREZ', '1990-05-15', 'M', '8091234567', 
            'juan.pablo@ejemplo.com', 'CALLE SOL #123', '5', 
            '15', 'MARIA GOMEZ', '2024-01-01', '2024-12-31', 
            'AV. PRINCIPAL, EDIF. AZUL, PISO 3', '8098765432', 'A+', 'Gerente' 
        ];

        // Iniciar el buffer de memoria para generar el CSV
        $output = fopen('php://temp', 'r+'); 
        
        // Añadir BOM de UTF-8 para compatibilidad con Excel (maneja acentos y ñ)
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
}