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
use app\models\TasaCambio;
use app\components\UserHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use Yii;

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

    /**
     * Lists all SisSiniestro models.
     * @param integer $user_id El ID del usuario
     * @return mixed
     */
    public function actionIndex($user_id)
    {
        $searchModel = new SisSiniestroSearch();
        $searchModel->iduser = $user_id;
        
        // Cargar los datos del afiliado
        $afiliado = UserDatos::findOne($user_id);
        
        // Configurar el dataProvider para cargar la relación con baremos
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        // Depurar la consulta principal
        $query = $dataProvider->query;
        $sql = $query->createCommand()->rawSql;
        Yii::info('CONSULTA PRINCIPAL: ' . $sql, 'app');
        
        // Cargar todos los siniestros con sus baremos en una sola consulta
        $models = $dataProvider->getModels();
        $siniestroIds = [];
        
        // Obtener todos los IDs de siniestros
        foreach ($models as $model) {
            $siniestroIds[] = $model->id;
        }
        
        // Cargar todos los baremos para estos siniestros en una sola consulta
        $baremosPorSiniestro = [];
        if (!empty($siniestroIds)) {
            $baremos = (new \yii\db\Query())
                ->select(['sb.siniestro_id', 'b.*'])
                ->from(['sb' => 'sis_siniestro_baremo'])
                ->leftJoin(['b' => 'baremo'], 'sb.baremo_id = b.id')
                ->where(['sb.siniestro_id' => $siniestroIds])
                ->all();
            
            // Organizar los baremos por siniestro_id
            foreach ($baremos as $baremo) {
                $baremosPorSiniestro[$baremo['siniestro_id']][] = $baremo;
            }
        }
        
        // Asignar los baremos a cada modelo
        foreach ($models as $model) {
            $baremos = isset($baremosPorSiniestro[$model->id]) ? $baremosPorSiniestro[$model->id] : [];
            $model->populateRelation('baremos', $baremos);
        }
        
        $dataProvider->setModels($models);
        

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'user_id' => $user_id,
            'afiliado' => $afiliado
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
public function actionCreate($user_id)
{
    $model = new SisSiniestro();
    $model->iduser = $user_id;
    $model->fecha = date('Y-m-d');
    $model->hora = date('H:i:s');
    $afiliado = UserDatos::find()->where(['id' => $user_id])->one();

    if ($model->load($this->request->post())) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            
            // VALIDAR BAREMOS ANTES DE GUARDAR
            $baremoIds = Yii::$app->request->post('SisSiniestro')['idbaremo'] ?? [];
            if (!is_array($baremoIds)) {
                $baremoIds = [];
            }
            
            $validacion = SisSiniestro::validarBaremosConPlan($baremoIds, $user_id);
            
            if (!$validacion['valid']) {
                $transaction->rollBack();
                foreach ($validacion['errors'] as $error) {
                    Yii::$app->session->setFlash('error', $error);
                }
                return $this->render('create', [
                    'model' => $model,
                    'afiliado' => $afiliado,
                    'user_id' => $user_id,
                ]);
            }
    
            // Guardar el modelo, incluyendo las URLs de las imágenes
            if ($model->save()) { 

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
                                if ($model->save(false)) {
                                    Yii::$app->session->setFlash('success', 'Archivo subido con éxito.');
                                } else {
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
                                if ($model->save(false)) {
                                    Yii::$app->session->setFlash('success', 'Archivo subido con éxito.');
                                } else {
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


                // Guardar la relación muchos a muchos
                $baremoIds = Yii::$app->request->post('SisSiniestro')['idbaremo'] ?? [];
                if (!is_array($baremoIds)) {
                    $baremoIds = [];
                }
                if (!$model->saveBaremos($baremoIds)) {
                    throw new \Exception('Error al guardar los baremos');
                }
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Siniestro creado correctamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                throw new \Exception('Error al guardar los datos principales del siniestro.');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
            Yii::error('Error al crear siniestro: ' . $e->getMessage(), __METHOD__);
        }
    }

    return $this->render('create', [
        'model' => $model,
        'afiliado' => $afiliado,
        'user_id' => $user_id,
    ]);
}

    /**
     * Updates an existing SisSiniestro model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
public function actionUpdate($id)
{
    $model = $this->findModel($id);
    $afiliado = UserDatos::find()->where(['id' => $model->iduser])->one();
    $baremos = $model->baremos;

    if ($model->load(Yii::$app->request->post())) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            
            // NO VALIDAR BAREMOS EN UPDATE - Solo se valida en CREATE
            // La validación de límites solo aplica al crear nuevos siniestros
            
            if ($model->save(false)) { 


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
                                if ($model->save(false)) {
                                    Yii::$app->session->setFlash('success', 'Archivo subido con éxito.');
                                } else {
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
                                if ($model->save(false)) {
                                    Yii::$app->session->setFlash('success', 'Archivo subido con éxito.');
                                } else {
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

                $baremoIds = Yii::$app->request->post('SisSiniestro')['idbaremo'] ?? [];
                if (!is_array($baremoIds)) {
                    $baremoIds = [];
                }
                
                if (!$model->saveBaremos($baremoIds)) {
                    throw new \Exception('Error al actualizar los baremos');
                }
                
                $model->refresh();
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Siniestro actualizado correctamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                throw new \Exception('Error al guardar los datos principales del siniestro.');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
            Yii::error('Error al actualizar siniestro: ' . $e->getMessage(), __METHOD__);
        }
    }

    return $this->render('update', [
        'model' => $model,
        'afiliado' => $afiliado,
        'baremos' => $baremos
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
     * Deletes an existing SisSiniestro model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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

    /*public function actionCalcularTotal()
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
    }*/

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
        
        return $this->render('por-clinica', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'estadisticas' => $estadisticas,
            'clinicas' => $clinicas,
            'clinicaSeleccionada' => $clinica_id,
        ]);
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

}