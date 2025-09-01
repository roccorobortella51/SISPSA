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
        $model->tasa = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one()->tasa_cambio;
        $model->user_id = $user_id;
        $fileName = null;
        $tempFilePath = null; // Inicializamos la ruta temporal a null
        $folder = 'Pago';
        $model->estatus = 'Por Conciliar';


        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
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
                            if ($model->save(false)) { // Guardamos el modelo de pago en la base de datos
                                Yii::$app->session->setFlash('success', 'Pago y archivo subido con éxito.');
                                return $this->redirect(['view', 'id' => $model->id]);
                            } else {
                                Yii::$app->session->setFlash('error', 'Error al guardar el pago en la base de datos.');
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
        $model->tasa = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one()->tasa_cambio;

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

    
}