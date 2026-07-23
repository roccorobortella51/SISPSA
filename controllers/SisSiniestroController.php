<?php

namespace app\controllers;

use app\models\SisSiniestro;
use app\models\SisSiniestroSearch;
use app\models\UserDatos;
use app\models\CorporativoUser;
use app\models\User;
use app\models\Contratos;
use app\models\Planes;
use app\models\Cuotas;
use app\models\PlanesItemsCobertura;
use app\models\TasaCambio;
use app\components\UserHelper;
use app\models\SisSiniestroAuditLog;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use Yii;
use yii\helpers\Html;

/**
 * SisSiniestroController implements the CRUD actions for SisSiniestro model.
 */
class SisSiniestroController extends Controller
{
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

    public function actionIndex($user_id)
    {
        // 1. CAPTURAR EL MODO
        $modo = Yii::$app->request->get('modo', 'siniestro');
        $esCitaValue = ($modo === 'cita') ? 1 : 0;

        $searchModel = new SisSiniestroSearch();
        $searchModel->iduser = $user_id;

        // Cargar los datos del afiliado
        $afiliado = UserDatos::findOne($user_id);

        if ($afiliado === null) {
            throw new \yii\web\NotFoundHttpException('El usuario afiliado no existe.');
        }

        // Configurar el dataProvider
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // APLICAR FILTRO es_cita
        $dataProvider->query->andWhere(['es_cita' => $esCitaValue]);

        // Cargar baremos usando eager loading cuando el query es ActiveQuery
        $query = $dataProvider->query;
        if ($query instanceof \yii\db\ActiveQuery) {
            $query->with('baremos');
        }

        // Obtener modelos y volver a asignarlos si el provider no lo hace automáticamente.
        $models = $dataProvider->getModels();
        $dataProvider->setModels($models);

        // RETORNAR VISTA CON EL MODO
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'user_id' => $user_id,
            'afiliado' => $afiliado,
            'modo' => $modo,
        ]);
    }

    /**
     * Displays a single SisSiniestro model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $afiliado = UserDatos::find()->where(['id' => $model->iduser])->one();

        // Cargar los baremos a través de la relación muchos a muchos
        $baremos = $model->baremos;

        return $this->render('view', [
            'model' => $model,
            'afiliado' => $afiliado,
            'baremos' => $baremos
        ]);
    }

    /**
     * Creates a new SisSiniestro model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $user_id El ID del usuario
     * @return mixed
     */
    public function actionCreate($user_id, $es_cita = 0)
    {
        // 1. CHECK CONTRACT STATUS BEFORE PROCEEDING
        $afiliado = UserDatos::find()->where(['id' => $user_id])->one();

        if (!$afiliado) {
            throw new \yii\web\NotFoundHttpException('El usuario afiliado no existe.');
        }

        // Check for active contract with "suspendido" status
        $contratoSuspendido = Contratos::find()
            ->where(['user_id' => $user_id])
            ->andWhere(['estatus' => 'suspendido'])
            ->andWhere(['<=', 'fecha_ini', date('Y-m-d')])
            ->andWhere(['>=', 'fecha_ven', date('Y-m-d')])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        // If there's a suspended contract in the current date range
        if ($contratoSuspendido) {
            // Ensure session is open
            if (!Yii::$app->session->isActive) {
                Yii::$app->session->open();
            }
            Yii::$app->session->setFlash(
                'error',
                '<span class="attention-alert">¡ATENCIÓN!</span>' . "\n" .
                    'No se puede crear una nueva atención para el afiliado ' .
                    Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos) . '.' . "\n" .
                    'Motivo: <span class="contract-alert">Contrato SUSPENDIDO</span> (#' . ($contratoSuspendido->nrocontrato ?: $contratoSuspendido->id) . ')' . "\n" .
                    'Período: ' . Yii::$app->formatter->asDate($contratoSuspendido->fecha_ini) . ' al ' .
                    Yii::$app->formatter->asDate($contratoSuspendido->fecha_ven) . "\n" .
                    'Contacte al departamento administrativo para regularizar la situación.'
            );

            // Force session save
            Yii::$app->session->close();

            // Redirect back to the index view with the appropriate mode
            $modo = ($es_cita == 1) ? 'cita' : 'siniestro';
            return $this->redirect(['index', 'user_id' => $user_id, 'modo' => $modo]);
        }

        $model = new SisSiniestro();
        $model->iduser = $user_id;
        $model->fecha = date('Y-m-d');
        $model->hora = date('H:i');

        // 1. ASIGNAR VALOR DE es_cita AL MODELO
        $model->es_cita = (int) $es_cita;

        $afiliado = UserDatos::find()->where(['id' => $user_id])->one();

        if ($model->load($this->request->post())) {
            // DEBUG: Check what is being received
            $postData = Yii::$app->request->post('SisSiniestro');

            $transaction = Yii::$app->db->beginTransaction();
            try {

                // VALIDAR BAREMOS ANTES DE GUARDAR
                $baremoIds = Yii::$app->request->post('SisSiniestro')['idbaremo'] ?? [];
                if (!is_array($baremoIds)) {
                    $baremoIds = [];
                }

                // 2. PASAR es_cita A LA FUNCIÓN DE VALIDACIÓN DEL MODELO
                $validacion = SisSiniestro::validarBaremosConPlan($baremoIds, $user_id, $model->es_cita, $model);

                if (!$validacion['valid']) {
                    $transaction->rollBack();
                    foreach ($validacion['errors'] as $error) {
                        Yii::$app->session->setFlash('error', $error);
                    }
                    return $this->render('create', [
                        'model' => $model,
                        'afiliado' => $afiliado,
                        'user_id' => $user_id,
                        'es_cita' => $model->es_cita,
                    ]);
                }

                $model->appointment_status = SisSiniestro::APPOINTMENT_STATUS_SCHEDULED;
                $model->no_show_processed = false;

                // Guardar el modelo
                if ($model->save()) {

                    // Force save admission_analyst and nombre_doctor directly to database
                    Yii::$app->db->createCommand()
                        ->update(
                            'sis_siniestro',
                            ['admission_analyst' => $model->admission_analyst],
                            ['id' => $model->id]
                        )
                        ->execute();

                    Yii::$app->db->createCommand()
                        ->update(
                            'sis_siniestro',
                            ['nombre_doctor' => $model->nombre_doctor],
                            ['id' => $model->id]
                        )
                        ->execute();

                    // --- Bloque de Subida de Recibo ---
                    $imagenRecipeFile = UploadedFile::getInstancesByName('SisSiniestro[imagenRecipeFile]');
                    $model->imagenRecipeFile = !empty($imagenRecipeFile) ? reset($imagenRecipeFile) : null;

                    // Subir el recibo si existe
                    if (!empty($imagenRecipeFile) && $imagenRecipeFile[0]->size > 0) {
                        $folder = 'documentos';
                        $fileName = uniqid('imagen_recipe') . '.' . $model->imagenRecipeFile->extension;
                        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                        if ($model->imagenRecipeFile->saveAs($tempFilePath)) {
                            Yii::info("Archivo temporal guardado en: " . $tempFilePath, __METHOD__);

                            $fileKeyInBucket = $fileName;

                            Yii::info("Subiendo archivo a Supabase Storage: " . $fileName, __METHOD__);
                            $publicUrl = UserHelper::uploadFileToSupabaseApi(
                                $tempFilePath,
                                $model->imagenRecipeFile->type,
                                $fileKeyInBucket,
                                $folder
                            );

                            if (file_exists($tempFilePath)) {
                                unlink($tempFilePath);
                                Yii::info("Archivo temporal eliminado: " . $tempFilePath, __METHOD__);
                            }

                            if ($publicUrl) {
                                $model->imagen_recipe = $publicUrl;
                                if (!$model->save(false)) {
                                    Yii::$app->session->setFlash('error', 'Error al guardar identificacion en la base de datos.');
                                }
                            } else {
                                Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                            }
                        } else {
                            Yii::error("Error al guardar el archivo temporal: " . $model->imagenRecipeFile->error, __METHOD__);
                            Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
                        }
                    }

                    // --- Bloque de Subida de Informe ---
                    $imagenInformeFile = UploadedFile::getInstancesByName('SisSiniestro[imagenInformeFile]');
                    $model->imagenInformeFile = !empty($imagenInformeFile) ? reset($imagenInformeFile) : null;

                    if (!empty($imagenInformeFile) && $imagenInformeFile[0]->size > 0) {
                        $folder = 'documentos';
                        $fileName = uniqid('selfie_') . '.' . $model->imagenInformeFile->extension;
                        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                        if ($model->imagenInformeFile->saveAs($tempFilePath)) {
                            Yii::info("Archivo temporal guardado en: " . $tempFilePath, __METHOD__);

                            $fileKeyInBucket = $fileName;

                            $publicUrl = UserHelper::uploadFileToSupabaseApi(
                                $tempFilePath,
                                $model->imagenInformeFile->type,
                                $fileKeyInBucket,
                                $folder
                            );

                            if (file_exists($tempFilePath)) {
                                unlink($tempFilePath);
                                Yii::info("Archivo temporal eliminado: " . $tempFilePath, __METHOD__);
                            }

                            if ($publicUrl) {
                                $model->imagen_informe = $publicUrl;
                                if (!$model->save(false)) {
                                    Yii::$app->session->setFlash('error', 'Error al guardar selfie en la base de datos.');
                                }
                            } else {
                                Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                            }
                        } else {
                            Yii::error("Error al guardar el archivo temporal: " . $model->imagenInformeFile->error, __METHOD__);
                            Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
                        }
                    }

                    // ============ PROCESAR DOCUMENTOS ADICIONALES (OTROS) ============
                    $otrosDocumentosData = Yii::$app->request->post('OtrosDocumentos', []);
                    $this->processOtrosDocumentos($model, $otrosDocumentosData);
                    // ============ END NEW CODE ============

                    // Guardar la relación muchos a muchos
                    $baremoIds = Yii::$app->request->post('SisSiniestro')['idbaremo'] ?? [];
                    if (!is_array($baremoIds)) {
                        $baremoIds = [];
                    }
                    if (!$model->saveBaremos($baremoIds)) {
                        throw new \Exception('Error al guardar los baremos');
                    }

                    $transaction->commit();
                    // Determine the correct success message based on es_cita value
                    $successMessage = $model->es_cita == 1 ? 'Cita creada correctamente.' : 'Atención creada correctamente.';
                    Yii::$app->session->setFlash('success', $successMessage);
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    throw new \Exception('Error al guardar los datos principales de la atención.');
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
                Yii::error('Error al crear Atención/cita: ' . $e->getMessage(), __METHOD__);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'afiliado' => $afiliado,
            'user_id' => $user_id,
            'es_cita' => (int) $es_cita,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $afiliado = UserDatos::find()->where(['id' => $model->iduser])->one();

        // Obtener el modo (cita o siniestro) de la URL o del modelo
        $esCita = (int)Yii::$app->request->get('es_cita', $model->es_cita);

        // Determinar los términos para mensajes dinámicos
        $termino = $esCita == 1 ? 'Cita' : 'Atención';
        $terminoLower = strtolower($termino);

        // Actualizar el modelo con el valor de es_cita si se proporcionó en la URL
        if (Yii::$app->request->get('es_cita') !== null) {
            $model->es_cita = $esCita;
        }

        // Obtener los baremos actuales del modelo
        $baremosActuales = $model->getBaremoIds();
        $baremos = $model->baremos; // Asegurarse de cargar la relación de baremos

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Obtener los baremos del formulario
                $baremoIds = Yii::$app->request->post('SisSiniestro')['idbaremo'] ?? [];

                // Si no se seleccionó ningún baremo, mantener los existentes
                if (empty($baremoIds)) {
                    $baremoIds = $baremosActuales;
                }

                // Si aún no hay baremos, mostrar error
                if (empty($baremoIds)) {
                    Yii::$app->session->setFlash('error', 'Debe seleccionar al menos un servicio médico.');
                    return $this->refresh();
                }

                // Establecer el primer baremo como idbaremo para compatibilidad
                $model->idbaremo = is_array($baremoIds) ? reset($baremoIds) : $baremoIds;

                if ($model->save(false)) {
                    // Actualizar la relación con los baremos
                    if (!$model->saveBaremos($baremoIds)) {
                        throw new \Exception('Error al actualizar los servicios médicos');
                    }

                    $imagenRecipeFile = UploadedFile::getInstancesByName('SisSiniestro[imagenRecipeFile]');
                    $imagenInformeFile = UploadedFile::getInstancesByName('SisSiniestro[imagenInformeFile]');

                    $model->imagenRecipeFile = !empty($imagenRecipeFile) ? reset($imagenRecipeFile) : null;
                    $model->imagenInformeFile = !empty($imagenInformeFile) ? reset($imagenInformeFile) : null;

                    // Subir el recibo si existe
                    if (!empty($imagenRecipeFile) && $imagenRecipeFile[0]->size > 0) {
                        $folder = 'documentos';
                        $fileName = uniqid('imagen_recipe') . '.' . $model->imagenRecipeFile->extension;
                        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                        if ($model->imagenRecipeFile->saveAs($tempFilePath)) {
                            Yii::info("Archivo temporal guardado en: " . $tempFilePath, __METHOD__);

                            $fileKeyInBucket = $fileName;

                            Yii::info("Subiendo archivo a Supabase Storage: " . $fileName, __METHOD__);
                            $publicUrl = UserHelper::uploadFileToSupabaseApi(
                                $tempFilePath,
                                $model->imagenRecipeFile->type,
                                $fileKeyInBucket,
                                $folder
                            );

                            if (file_exists($tempFilePath)) {
                                unlink($tempFilePath);
                                Yii::info("Archivo temporal eliminado: " . $tempFilePath, __METHOD__);
                            }

                            if ($publicUrl) {
                                $model->imagen_recipe = $publicUrl;
                                if (!$model->save(false)) {
                                    throw new \Exception('Error al guardar la ruta de la imagen de receta');
                                }
                            } else {
                                throw new \Exception('Error al subir la imagen de receta a Supabase');
                            }
                        } else {
                            throw new \Exception('Error al guardar el archivo temporal de la receta');
                        }
                    }

                    // Subir informe médico si existe
                    if (!empty($imagenInformeFile) && $imagenInformeFile[0]->size > 0) {
                        $folder = 'documentos';
                        $fileName = uniqid('selfie_') . '.' . $model->imagenInformeFile->extension;
                        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                        if ($model->imagenInformeFile->saveAs($tempFilePath)) {
                            Yii::info("Archivo temporal guardado en: " . $tempFilePath, __METHOD__);

                            $fileKeyInBucket = $fileName;

                            $publicUrl = UserHelper::uploadFileToSupabaseApi(
                                $tempFilePath,
                                $model->imagenInformeFile->type,
                                $fileKeyInBucket,
                                $folder
                            );

                            if (file_exists($tempFilePath)) {
                                unlink($tempFilePath);
                                Yii::info("Archivo temporal eliminado: " . $tempFilePath, __METHOD__);
                            }

                            if ($publicUrl) {
                                $model->imagen_informe = $publicUrl;
                                if (!$model->save(false)) {
                                    throw new \Exception('Error al guardar la ruta del informe médico');
                                }
                            } else {
                                throw new \Exception('Error al subir el informe médico a Supabase');
                            }
                        } else {
                            throw new \Exception('Error al guardar el archivo temporal del informe');
                        }
                    }

                    // Force save nombre_doctor directly to database
                    Yii::$app->db->createCommand()
                        ->update(
                            'sis_siniestro',
                            ['nombre_doctor' => $model->nombre_doctor],
                            ['id' => $model->id]
                        )
                        ->execute();

                    $transaction->commit();

                    // Dynamic success message
                    $successMessage = $esCita == 1 ? 'Cita actualizada correctamente.' : 'Atención actualizada correctamente.';
                    Yii::$app->session->setFlash('success', $successMessage);
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    throw new \Exception('Error al guardar los datos principales de la ' . $terminoLower . '.');
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Error al actualizar ' . $terminoLower . ': ' . $e->getMessage(), __METHOD__);

                // Dynamic error message
                $errorMessage = 'Error al actualizar la ' . $terminoLower . ': ' . $e->getMessage();
                Yii::$app->session->setFlash('error', $errorMessage);

                // En caso de error, volver a cargar la vista con los datos actuales
                return $this->render('update', [
                    'model' => $model,
                    'afiliado' => $afiliado,
                    'baremos' => $baremos,
                    'baremosActuales' => $baremosActuales,
                    'es_cita' => $esCita,
                ]);
            }
        }

        // Cargar la vista con los datos del modelo
        return $this->render('update', [
            'model' => $model,
            'afiliado' => $afiliado,
            'baremos' => $baremos,
            'baremosActuales' => $baremosActuales,
            'es_cita' => $esCita,
        ]);
    }

    /**
     * Obtiene el tamaño de un archivo en una URL.
     * @param string $url La URL del archivo.
     * @return string El tamaño del archivo formateado.
     */
    public function getFileSize($url)
    {
        // Usa cURL para obtener el tamaño del archivo de la cabecera Content-Length
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        $data = curl_exec($ch);
        $size = 0;
        if ($data) {
            $matches = [];
            preg_match('/Content-Length: (\d+)/', $data, $matches);
            if (isset($matches[1])) {
                $size = (int)$matches[1];
            }
        }
        curl_close($ch);

        // Si no se pudo obtener el tamaño con cURL, intenta con el sistema de archivos local
        if ($size === 0 && strpos($url, Yii::getAlias('@web')) !== false) {
            $path = Yii::getAlias('@webroot') . str_replace(Yii::getAlias('@web'), '', $url);
            if (file_exists($path)) {
                $size = filesize($path);
            }
        }

        // Formatear el tamaño en KB o MB
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return round($size / 1048576, 2) . ' MB';
        }
    }

    /**
     * Deletes an existing SisSiniestro model with audit logging.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $esCita = $model->es_cita;
        $termino = $esCita == 1 ? 'Cita' : 'Atención';
        $terminoLower = strtolower($termino);

        // Get current user role
        $userRole = UserHelper::getMyRol();

        // Define allowed roles for deletion
        $allowedRoles = ['superadmin', 'GERENTE-OPERACIONES', 'GERENTE-CLINICA', 'COORDINADOR-CLINICA'];

        // Check if user has permission to delete
        if (!in_array($userRole, $allowedRoles)) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['error' => true, 'message' => 'No tiene permisos para eliminar ' . $terminoLower . 's.'];
            }

            Yii::$app->session->setFlash('error', 'No tiene permisos para eliminar ' . $terminoLower . 's. Solo los Gerentes y Coordinadores pueden realizar esta acción.');
            $modo = $esCita == 1 ? 'cita' : 'siniestro';
            return $this->redirect(['index', 'user_id' => $model->iduser, 'modo' => $modo]);
        }

        // Handle GET request - show confirmation modal
        if (Yii::$app->request->isGet && !Yii::$app->request->isPost) {
            return $this->renderAjax('_delete_confirmation', [
                'model' => $model,
                'termino' => $termino,
                'terminoLower' => $terminoLower,
            ]);
        }

        // Handle POST request - process deletion
        if (Yii::$app->request->isPost) {
            // Get deletion reason from POST
            $reason = Yii::$app->request->post('reason');

            if (empty($reason)) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return ['error' => true, 'message' => 'Debe proporcionar un motivo para la eliminación.'];
                }
                Yii::$app->session->setFlash('error', 'Debe proporcionar un motivo para la eliminación.');
                return $this->redirect(['index', 'user_id' => $model->iduser, 'modo' => $esCita == 1 ? 'cita' : 'siniestro']);
            }

            // Prepare data for audit log
            $deletedData = [
                'id' => $model->id,
                'fecha' => $model->fecha,
                'hora' => $model->hora,
                'idclinica' => $model->idclinica,
                'clinica_nombre' => $model->clinica ? $model->clinica->nombre : null,
                'costo_total' => $model->costo_total,
                'atendido' => $model->atendido,
                'descripcion' => $model->descripcion,
                'es_cita' => $model->es_cita,
                'appointment_status' => $model->appointment_status,
                'baremos' => [],
            ];

            // Get baremos information
            foreach ($model->baremos as $baremo) {
                $deletedData['baremos'][] = [
                    'id' => $baremo->id,
                    'nombre_servicio' => $baremo->nombre_servicio,
                    'precio' => $baremo->precio,
                ];
            }

            // Start transaction
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Log the deletion
                if (!SisSiniestroAuditLog::logDeletion($model->id, $deletedData, $reason)) {
                    throw new \Exception('Error al registrar la auditoría de eliminación.');
                }

                // Delete the record
                if (!$model->delete()) {
                    throw new \Exception('Error al eliminar el registro.');
                }

                $transaction->commit();

                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return ['success' => true, 'message' => $termino . ' eliminada correctamente.'];
                }

                // Set dynamic success message
                $successMessage = $esCita == 1 ? 'Cita eliminada correctamente.' : 'Atención eliminada correctamente.';
                Yii::$app->session->setFlash('success', $successMessage);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error('Error al eliminar ' . $terminoLower . ': ' . $e->getMessage(), __METHOD__);

                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return ['error' => true, 'message' => 'Error al eliminar la ' . $terminoLower . ': ' . $e->getMessage()];
                }

                Yii::$app->session->setFlash('error', 'Error al eliminar la ' . $terminoLower . ': ' . $e->getMessage());
            }

            // Redirect to index with the correct mode
            $modo = $esCita == 1 ? 'cita' : 'siniestro';
            return $this->redirect(['index', 'user_id' => $model->iduser, 'modo' => $modo]);
        }

        // If neither GET nor POST, return 405
        throw new \yii\web\MethodNotAllowedHttpException('This action only supports GET and POST requests.');
    }

    /**
     * Finds the SisSiniestro model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SisSiniestro the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SisSiniestro::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionCalcularTotal()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $baremosIds = Yii::$app->request->post('baremos', []);

            if (empty($baremosIds)) {
                return ['success' => true, 'total' => 0];
            }

            // Calcular la suma de los precios de los baremos seleccionados
            $total = \app\models\Baremo::find()
                ->where(['id' => $baremosIds])
                ->sum('precio');

            return ['success' => true, 'total' => $total ?: 0];
        } catch (\Exception $e) {
            Yii::error("Error al calcular total: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Muestra siniestros por clínica con gráficos
     */
    public function actionPorClinica($clinica_id = null)
    {
        $searchModel = new SisSiniestroSearch();

        // Si se proporciona un ID de clínica, filtrar por esa clínica
        if ($clinica_id) {
            $searchModel->idclinica = $clinica_id;
        }

        $dataProvider = $searchModel->searchClinica(Yii::$app->request->queryParams);

        // Obtener estadísticas para el gráfico
        $estadisticas = $this->obtenerEstadisticasSiniestros($clinica_id);

        // Obtener lista de clínicas para el filtro
        $clinicas = \app\models\RmClinica::find()
            ->where(['estatus' => 'Activo'])
            ->orderBy('nombre')
            ->all();

        // ============ Obtener datos para gráficos ============

        // 1. Datos de tendencia mensual - Últimos 12 meses
        $siniestrosPorMes = $this->obtenerSiniestrosPorMes($clinica_id);

        // ============ FIN ============

        return $this->render('por-clinica', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'estadisticas' => $estadisticas,
            'clinicas' => $clinicas,
            'clinicaSeleccionada' => $clinica_id,
            'siniestrosPorMes' => $siniestrosPorMes,
        ]);
    }

    /**
     * Obtiene estadísticas de siniestros agrupados por mes
     */
    private function obtenerSiniestrosPorMes($clinica_id = null)
    {
        $query = (new \yii\db\Query())
            ->select([
                "TO_CHAR(fecha, 'YYYY-MM') as mes",
                'COUNT(*) as total'
            ])
            ->from('sis_siniestro');

        if ($clinica_id) {
            $query->andWhere(['idclinica' => $clinica_id]);
        }

        $resultados = $query
            ->groupBy('mes')
            ->orderBy('mes ASC')
            ->limit(12)
            ->all();

        $siniestrosPorMes = [];
        foreach ($resultados as $row) {
            $siniestrosPorMes[$row['mes']] = (int)$row['total'];
        }

        return $siniestrosPorMes;
    }
    /**
     * Obtiene estadísticas de siniestros por clínica
     */
    private function obtenerEstadisticasSiniestros($clinica_id = null)
    {
        $query = SisSiniestro::find();

        if ($clinica_id) {
            $query->andWhere(['idclinica' => $clinica_id]);
        }

        // Contar total de siniestros
        $totalSiniestros = $query->count();

        // Contar siniestros atendidos
        $atendidos = (clone $query)->andWhere(['atendido' => 1])->count();

        // Contar siniestros no atendidos
        $noAtendidos = (clone $query)->andWhere(['atendido' => 0])->orWhere(['atendido' => null])->count();

        // Obtener datos por clínica (si no se filtró por una clínica específica)
        $porClinica = [];
        if (!$clinica_id) {
            $porClinica = \app\models\RmClinica::find()
                ->select([
                    'rm_clinica.id',
                    'rm_clinica.nombre',
                    'COUNT(sis_siniestro.id) as total',
                    'SUM(CASE WHEN sis_siniestro.atendido = 1 THEN 1 ELSE 0 END) as atendidos',
                    'SUM(CASE WHEN sis_siniestro.atendido = 0 OR sis_siniestro.atendido IS NULL THEN 1 ELSE 0 END) as no_atendidos'
                ])
                ->leftJoin('sis_siniestro', 'rm_clinica.id = sis_siniestro.idclinica')
                ->groupBy('rm_clinica.id, rm_clinica.nombre')
                ->orderBy('rm_clinica.nombre')
                ->asArray()
                ->all();
        }

        return [
            'total' => $totalSiniestros,
            'atendidos' => $atendidos,
            'no_atendidos' => $noAtendidos,
            'por_clinica' => $porClinica,
        ];
    }

    /**
     * Sube un archivo a Supabase Storage y retorna la URL pública.
     * @param \yii\web\UploadedFile $uploadedFile El objeto de archivo subido.
     * @param string $folder La carpeta de destino en Supabase.
     * @return string|null La URL pública del archivo, o null en caso de fallo.
     */
    private function uploadFileToSupabase(\yii\web\UploadedFile $uploadedFile, $folder)
    {
        $fileName = uniqid('file_') . '.' . $uploadedFile->extension;
        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

        if ($uploadedFile->saveAs($tempFilePath)) {
            $publicUrl = UserHelper::uploadFileToSupabaseApi(
                $tempFilePath,
                $uploadedFile->type,
                $fileName,
                $folder
            );

            // Eliminar el archivo temporal
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            if ($publicUrl) {
                return $publicUrl;
            } else {
                Yii::$app->session->setFlash('error', "Fallo la subida a Supabase Storage.");
            }
        } else {
            Yii::$app->session->setFlash('error', "Error al guardar el archivo temporal en el servidor.");
        }

        return null;
    }

    /**
     * Display audit logs for deletions
     * @return string
     */
    public function actionAuditLogs()
    {
        $userRole = UserHelper::getMyRol();
        $allowedRoles = ['superadmin', 'GERENTE-OPERACIONES'];

        if (!in_array($userRole, $allowedRoles)) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para ver los logs de auditoría.');
            return $this->redirect(['index']);
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => SisSiniestroAuditLog::find()->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('audit-logs', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * View audit log details
     * @param int $id
     * @return string
     */
    public function actionAuditDetails($id)
    {
        $auditLog = SisSiniestroAuditLog::findOne($id);
        if (!$auditLog) {
            return '<div class="alert alert-danger">Registro no encontrado.</div>';
        }

        $deletedData = json_decode($auditLog->deleted_data, true);

        return $this->renderPartial('_audit_details', [
            'auditLog' => $auditLog,
            'deletedData' => $deletedData,
        ]);
    }

    private function processOtrosDocumentos($model, $otrosDocumentos)
    {
        if (empty($otrosDocumentos) || !is_array($otrosDocumentos)) {
            return true;
        }

        $uploadedDocs = [];
        $uploadedFiles = UploadedFile::getInstances($model, 'otrosDocumentosFile');

        if (empty($uploadedFiles)) {
            return true;
        }

        $fileIndex = 0;
        foreach ($otrosDocumentos as $index => $docInfo) {
            if (!isset($uploadedFiles[$fileIndex]) || !($uploadedFiles[$fileIndex] instanceof UploadedFile) || $uploadedFiles[$fileIndex]->size == 0) {
                $fileIndex++;
                continue;
            }

            $file = $uploadedFiles[$fileIndex];
            $folder = 'documentos/otros';
            $fileName = uniqid('doc_otros_' . $model->id . '_') . '.' . $file->extension;
            $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

            if ($file->saveAs($tempFilePath)) {
                $publicUrl = UserHelper::uploadFileToSupabaseApi($tempFilePath, $file->type, $fileName, $folder);
                if (file_exists($tempFilePath)) unlink($tempFilePath);

                if ($publicUrl) {
                    $uploadedDocs[] = [
                        'url' => $publicUrl,
                        'tipo' => $docInfo['tipo'] ?? 'Otro',
                        'descripcion' => $docInfo['descripcion'] ?? '',
                        'nombre_archivo' => $file->name,
                        'tamano' => $file->size,
                        'fecha_subida' => date('Y-m-d H:i:s'),
                    ];
                }
            }
            $fileIndex++;
        }

        $existingDocs = json_decode($model->otros_documentos, true) ?: [];
        $model->otros_documentos = json_encode(array_merge($existingDocs, $uploadedDocs));
        return $model->save(false);
    }

    /**
     * Prints a medical attention/cita with all details
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $afiliado = UserDatos::find()->where(['id' => $model->iduser])->one();
        $baremos = $model->baremos;

        // Calculate total
        $total = $model->costo_total ?: 0;

        // Render a print-friendly view
        return $this->renderPartial('print', [
            'model' => $model,
            'afiliado' => $afiliado,
            'baremos' => $baremos,
            'total' => $total,
        ]);
    }

    /**
     * Cancel an appointment (Cita only)
     * @param int $id
     * @return mixed
     */
    public function actionCancelAppointment($id)
    {
        $model = $this->findModel($id);

        // Only allow cancellation for Citas
        if ($model->es_cita != 1) {
            Yii::$app->session->setFlash('error', 'Solo las citas pueden ser canceladas.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Only allow cancellation for scheduled appointments
        if ($model->appointment_status != SisSiniestro::APPOINTMENT_STATUS_SCHEDULED) {
            Yii::$app->session->setFlash('error', 'Esta cita ya fue procesada o cancelada anteriormente.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Calculate hours until appointment
        $canCancelWithoutPenalty = $model->canBeCancelledWithoutPenalty();
        $hoursUntil = $model->getHoursUntilAppointment();

        if (Yii::$app->request->isPost) {
            $reason = Yii::$app->request->post('reason');

            if (empty($reason)) {
                Yii::$app->session->setFlash('error', 'Debe especificar un motivo para la cancelación.');
                return $this->render('cancel-appointment', [
                    'model' => $model,
                    'canCancelWithoutPenalty' => $canCancelWithoutPenalty,
                    'hoursUntil' => $hoursUntil,
                ]);
            }

            if ($model->cancelAppointment($reason, Yii::$app->user->identity->username)) {
                $message = $canCancelWithoutPenalty
                    ? 'Cita cancelada exitosamente. La cobertura ha sido restaurada.'
                    : 'Cita cancelada fuera del período permitido. La cobertura no será restaurada.';

                Yii::$app->session->setFlash('success', $message);
            } else {
                Yii::$app->session->setFlash('error', 'Error al cancelar la cita.');
            }

            return $this->redirect(['index', 'user_id' => $model->iduser, 'modo' => 'cita']);
        }

        return $this->render('cancel-appointment', [
            'model' => $model,
            'canCancelWithoutPenalty' => $canCancelWithoutPenalty,
            'hoursUntil' => $hoursUntil,
        ]);
    }

    /**
     * Mark appointment as attended (simplified - one click)
     * @param int $id
     * @return mixed
     */
    public function actionAttend($id)
    {
        $model = $this->findModel($id);

        // Only for Citas
        if ($model->es_cita != 1) {
            Yii::$app->session->setFlash('error', '⚠️ Solo aplicable para citas médicas.');
            return $this->redirect(['index', 'user_id' => $model->iduser, 'modo' => 'cita']);
        }

        // Only for scheduled appointments
        if ($model->appointment_status != SisSiniestro::APPOINTMENT_STATUS_SCHEDULED) {
            $statusLabels = [
                'scheduled' => 'Agendada',
                'completed' => 'Completada',
                'cancelled' => 'Cancelada',
            ];
            $statusText = $statusLabels[$model->appointment_status] ?? $model->appointment_status;
            Yii::$app->session->setFlash('error', '⚠️ Esta cita no está en estado Agendada. Estado actual: ' . $statusText);
            return $this->redirect(['index', 'user_id' => $model->iduser, 'modo' => 'cita']);
        }

        $patientName = $model->afiliado ? $model->afiliado->nombres . ' ' . $model->afiliado->apellidos : 'Paciente';
        $serviceCount = count($model->baremos);

        $model->appointment_status = SisSiniestro::APPOINTMENT_STATUS_COMPLETED;
        $model->atendido = 1;
        $model->checked_in_at = date('Y-m-d H:i:s');
        $model->checked_out_at = date('Y-m-d H:i:s');

        if ($model->save(false)) {
            // Bootstrap 4 styled success message
            $successMessage = '<div class="d-flex align-items-center">' .
                '<div class="flex-shrink-0 mr-3">' .
                '<i class="fas fa-check-circle fa-2x" style="color: #28a745;"></i>' .
                '</div>' .
                '<div class="flex-grow-1">' .
                '<strong class="d-block" style="font-size: 1.6rem;">¡Cita Completada Exitosamente!</strong>' .
                '<span>' . Html::encode($patientName) . ' - ' . $serviceCount . ' servicio(s) prestado(s)</span>' .
                '</div>' .
                '</div>';
            Yii::$app->session->setFlash('success', $successMessage);
        } else {
            Yii::$app->session->setFlash('error', '❌ Error al marcar la cita como completada. Por favor, intente nuevamente.');
        }

        return $this->redirect(['index', 'user_id' => $model->iduser, 'modo' => 'cita']);
    }

    /**
     * Build professional success message HTML
     */
    private function buildSuccessMessage($patientName, $date, $time, $serviceCount, $totalCost)
    {
        return '<div class="appointment-complete-message">
        <div class="success-header">
            <i class="fas fa-check-circle"></i>
            <strong>Cita Completada Exitosamente</strong>
        </div>
        <div class="success-body">
            <div class="patient-info">
                <i class="fas fa-user"></i>
                <span>' . Html::encode($patientName) . '</span>
            </div>
            <div class="appointment-details">
                <div class="detail-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>' . $date . ' - ' . $time . '</span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-stethoscope"></i>
                    <span>' . $serviceCount . ' servicio(s) prestado(s)</span>
                </div>
                <div class="detail-item total-cost">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Total: $' . number_format($totalCost, 2) . '</span>
                </div>
            </div>
        </div>
        <div class="success-footer">
            <i class="fas fa-info-circle"></i>
            <span>El paciente ha sido marcado como atendido. La cobertura ha sido descontada.</span>
        </div>
    </div>';
    }

    /**
     * Build professional error message HTML
     */
    private function buildErrorMessage($patientName)
    {
        return '<div class="appointment-error-message">
        <div class="error-header">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Error al Completar la Cita</strong>
        </div>
        <div class="error-body">
            <i class="fas fa-user"></i>
            <span>' . Html::encode($patientName) . '</span>
        </div>
        <div class="error-footer">
            <i class="fas fa-info-circle"></i>
            <span>No se pudo marcar la cita como completada. Por favor, intente nuevamente o contacte a soporte.</span>
        </div>
    </div>';
    }

    /**
     * Get human-readable status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'scheduled' => 'Agendada',
            'confirmed' => 'Confirmada',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            'no_show' => 'No Asistió',
        ];
        return $labels[$status] ?? $status;
    }
}
