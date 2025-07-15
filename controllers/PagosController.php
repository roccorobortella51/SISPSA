<?php

namespace app\controllers;

use Yii;
use app\models\Pagos;
use app\models\TasaCambio;
use app\models\UserDatos;
use app\models\PagosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\web\Response;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

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
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }
    private $s3Config;

    public function init()
    {
        parent::init();
        $this->s3Config = Yii::$app->params['supabaseS3'];
    }

    /**
     * Lists all Pagos models.
     *
     * @return string
     */
    public function actionIndex($user_id = "")
    {
        $searchModel = new PagosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if ($user_id !== "") {
            $afiliado = UserDatos::find()->where(['id' => $user_id])->one();
            $dataProvider->query->andFilterWhere(['=', 'user_id', $afiliado->id]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'afiliado' => $afiliado,
            'user_id' => $user_id,
        ]);
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
    public function actionCreate($user_id = "")
    {
        $model = new Pagos();
        $tasa = TasaCambio::find()->orderBy(['created_at' => SORT_DESC])->one();
        $model->tasa = round($tasa->tasa_cambio,5);
        $model->user_id = $user_id;
        $model->estatus = 'pendiente';


        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->imagen_prueba_file = UploadedFile::getInstance($model, 'imagen_prueba_file');
                if ($model->validate()) {
                    if ($model->imagen_prueba_file) {
                        $fileName = 'uploads/' . uniqid() . '.' . $model->imagen_prueba_file->extension;
                        $fullPath = Yii::getAlias('@webroot') . '/' . $fileName;
                        if ($model->imagen_prueba_file->saveAs($fullPath)) {
                            $S3 = $this->uploadfileS3($fileName,$model->imagen_prueba_file->extension);
                            var_dump($S3);
                            exit();
                            $model->imagen_prueba = $fileName;
                        }
                       
                    if ($model->save(false)) {
                        return $this->redirect(['contratos/index', 'user_id' => $user_id]);
                    }
                }
            }
            } else {
                $model->loadDefaultValues();
            }

        }
        return $this->render('create', [
            'model' => $model,
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
        $model = $this->findModel($id);
        $tasa = TasaCambio::find()->orderBy(['created_at' => SORT_DESC])->one();
        $model->tasa = round($tasa->tasa_cambio,5);

        // Determine if the payment is editable based on status
        $isEditable = ($model->estatus === 'pendiente');

        if ($this->request->isPost && $isEditable) {
            if ($model->load($this->request->post())) {
                $model->imagen_prueba_file = UploadedFile::getInstance($model, 'imagen_prueba_file');
                if ($model->validate()) {
                    if ($model->imagen_prueba_file) {
                        $fileName = 'uploads/' . uniqid() . '.' . $model->imagen_prueba_file->extension;
                        $fullPath = Yii::getAlias('@webroot') . '/' . $fileName;
                        if ($model->imagen_prueba_file->saveAs($fullPath)) {
                            $model->imagen_prueba = $fileName;
                        }
                    }
                    if ($model->save(false)) {
                        return $this->redirect(['contratos/index', 'user_id' => $model->user_id]);
                    }
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'isEditable' => $isEditable,
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
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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
        
    public function uploadfileS3(string $localTempFilePath, string $mimeType, string $fileNameInSupabase){
        try {
            $s3Client = new S3Client([
                        'version' => 'latest',
                        'region' => $this->s3Config['region'],
                        'endpoint' => $this->s3Config['endpoint'], // **MUY IMPORTANTE: Este es el endpoint S3 de Supabase**
                        'credentials' => [
                            'key' => $this->s3Config['key'],
                            'secret' => $this->s3Config['secret'],
                        ],
                        'force_path_style' => true, // Importante para la compatibilidad con Supabase/MinIO
                    ]);

                    // Subir el archivo a S3
            $bucketName = $this->s3Config['bucket']; // Esto debería ser 'usuarios' según tu params.php

            // Construir la CLAVE S3 correctamente: subcarpeta/nombre_de_archivo
            // Usaremos 'pago/' como prefijo de carpeta, y luego el nombre de archivo final.
            $s3Key = 'pago/' . $fileNameInSupabase; 

            $result = $s3Client->putObject([
                'Bucket'     => $bucketName,
                'Key'        => $s3Key, // Clave S3 final (ej. 'pago/nombre_archivo.png')
                'SourceFile' => $localTempFilePath, // Ruta real al archivo en tu servidor
                'ContentType' => $mimeType, // Tipo MIME correcto (ej. 'image/jpeg')
                // 'ACL'        => 'public-read', // Solo si tu bucket lo permite y lo deseas.
                                                // Supabase recomienda RLS en la base de datos para controlar el acceso,
                                                // y los archivos son públicos por defecto si no tienen RLS.
            ]);

            // Si la subida fue exitosa, construye la URL pública del archivo
            // La URL pública es diferente del endpoint S3.
            // Es la URL de tu proyecto base + /storage/v1/object/public/ + bucket + / + key
            $projectBaseUrl = str_replace('/storage/v1/s3', '', $this->s3Config['endpoint']);
            $publicUrl = "{$projectBaseUrl}/storage/v1/object/public/{$bucketName}/{$s3Key}";

            return $publicUrl; // Devuelve la URL pública del archivo
        } catch (\Throwable $e) {
            Yii::error("General S3 Upload Exception: " . $e->getMessage(), __METHOD__);
        }
    }
}
