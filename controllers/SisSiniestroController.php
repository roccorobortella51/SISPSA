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
            // Obtener las instancias de los archivos subidos ANTES de guardar el modelo
            $imagenRecipeFile = \yii\web\UploadedFile::getInstance($model, 'imagen_recipe');
            $imagenInformeFile = \yii\web\UploadedFile::getInstance($model, 'imagen_informe');

            // Subir el recibo si existe
            if ($imagenRecipeFile) {
                $folder = 'Recipes';
                $fileName = uniqid('recipe_') . '.' . $imagenRecipeFile->extension;
                $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                if ($imagenRecipeFile->saveAs($tempFilePath)) {
                    // Usamos la nueva función corregida
                    $publicUrl = UserHelper::uploadFileToSupabaseSiniestro($tempFilePath, $imagenRecipeFile->type, $fileName, $folder);
                    if ($publicUrl) {
                        $model->imagen_recipe = $publicUrl;
                    } else {
                        throw new \Exception('Falló la subida del recibo a Supabase Storage.');
                    }
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }
                } else {
                    throw new \Exception('Error al guardar el archivo temporal del recibo en el servidor.');
                }
            }

            // Subir el informe si existe
            if ($imagenInformeFile) {
                $folder = 'Informes';
                $fileName = uniqid('informe_') . '.' . $imagenInformeFile->extension;
                $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                if ($imagenInformeFile->saveAs($tempFilePath)) {
                    // Usamos la nueva función corregida
                    $publicUrl = UserHelper::uploadFileToSupabaseSiniestro($tempFilePath, $imagenInformeFile->type, $fileName, $folder);
                    if ($publicUrl) {
                        $model->imagen_informe = $publicUrl;
                    } else {
                        throw new \Exception('Falló la subida del informe a Supabase Storage.');
                    }
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }
                } else {
                    throw new \Exception('Error al guardar el archivo temporal del informe en el servidor.');
                }
            }
            
            // Guardar el modelo, incluyendo las URLs de las imágenes
            if ($model->save()) { 
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
            $imagenRecipeFile = UploadedFile::getInstance($model, 'imagen_recipe');
            $imagenInformeFile = UploadedFile::getInstance($model, 'imagen_informe');

            if ($imagenRecipeFile) {
                $folder = 'documentos';
                $fileName = uniqid('recipe_') . '.' . $imagenRecipeFile->extension;
                $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                if ($imagenRecipeFile->saveAs($tempFilePath)) {
                    // Usamos la función original que validaste
                    $publicUrl = UserHelper::uploadFileToSupabaseApi($tempFilePath, $imagenRecipeFile->type, $fileName, $folder);
                    
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }
                    if ($publicUrl) {
                        $model->imagen_recipe = $publicUrl;
                    } else {
                        throw new \Exception('Fallo la subida del recibo a Supabase Storage.');
                    }
                } else {
                    throw new \Exception('Error al guardar el archivo temporal del recibo en el servidor.');
                }
            }

            if ($imagenInformeFile) {
                $folder = 'FotoPerfil';
                $fileName = uniqid('informe_') . '.' . $imagenInformeFile->extension;
                $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                if ($imagenInformeFile->saveAs($tempFilePath)) {
                    // Usamos la función original que validaste
                    $publicUrl = UserHelper::uploadFileToSupabaseApi($tempFilePath, $imagenInformeFile->type, $fileName, $folder);
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }
                    if ($publicUrl) {
                        $model->imagen_informe = $publicUrl;
                    } else {
                        throw new \Exception('Fallo la subida del informe a Supabase Storage.');
                    }
                } else {
                    throw new \Exception('Error al guardar el archivo temporal del informe en el servidor.');
                }
            }

            if ($model->save(false)) { 
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