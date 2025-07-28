<?php

namespace app\controllers;

use Yii;
use app\models\UserDatos;
use app\models\User;
use app\models\UserDatosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\UserHelper;
use app\models\RmMunicipio;
use app\models\RmParroquia;
use app\models\RmCiudad;
use app\models\RmEstado;
use app\models\Contratos;
use app\models\RmClinica;
use app\models\Planes;
use yii\base\Security;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile; // Necesario para manejar la subida de archivos
use PhpOffice\PhpSpreadsheet\IOFactory; // Importa la clase principal
use PhpOffice\PhpSpreadsheet\Reader\Exception; // Para manejar excepciones del lector
use DateTime;


/**
 * UserDatosController implements the CRUD actions for UserDatos model.
 */
class UserDatosController extends Controller
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

    /*public function actionMasivo()
    {
        $modelContrato = new Contratos();
        $model = new UserDatos();
        if ($this->request->isPost && $model->load($this->request->post()) ) {
            $masivoFiles = UploadedFile::getInstancesByName('UserDatos[masivoFile]');
            if ($masivoFiles) {
                $filePath = Yii::getAlias('@app/web/uploads/masivoFiles/' . $masivoFiles[0]->baseName . '.' . $masivoFiles[0]->extension);
                $masivoFiles[0]->saveAs($filePath);
            } 
            else {
                Yii::$app->session->setFlash('error', 'No se ha subido ningún archivo.');
                //return $this->redirect(['index']);
                return $this->render('masivo', [
                    'model' => $model,
                    'modelContrato' => $modelContrato,
        
                ]);
            }
            //leer archivo .xlsx
            $objPHPExcel = IOFactory::load($filePath);
            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            $sheetData = array_slice($sheetData, 1);

            var_dump($sheetData);exit;

        }    
        return $this->render('masivo', [
            'model' => $model,
            'modelContrato' => $modelContrato,

        ]);
    }*/

    public function actionMasivo()
    {
        $modelContrato = new Contratos();
        $model = new UserDatos();

        if ($this->request->isPost && $model->load($this->request->post()) && $modelContrato->load($this->request->post())) {
            // Obtener el archivo subido
            $masivoFiles = UploadedFile::getInstancesByName('UserDatos[masivoFile]');

            if (empty($masivoFiles) || !$masivoFiles[0]->tempName) {
                // Si no se subió ningún archivo o el archivo está vacío
                Yii::$app->session->setFlash('error', 'No se ha subido ningún archivo o el archivo está corrupto.');
                return $this->render('masivo', [
                    'model' => $model,
                    'modelContrato' => $modelContrato,
                ]);
            }

            $uploadedFile = $masivoFiles[0];
            $filePath = Yii::getAlias('@app/web/uploads/masivoFiles/' . $uploadedFile->baseName . '.' . $uploadedFile->extension);

            if (!$uploadedFile->saveAs($filePath)) {
                Yii::$app->session->setFlash('error', 'Error al guardar el archivo subido.');
                return $this->render('masivo', [
                    'model' => $model,
                    'modelContrato' => $modelContrato,
                ]);
            }
            $clinica_id = $model->clinica_id;
            $plan_id = $model->plan_id;
            $monto = $modelContrato->monto;
            $fecha_ini = $modelContrato->fecha_ini;
            $fecha_ven = $modelContrato->fecha_ven;
            $fechaCreacion = date('Y-m-d H:i:s');   
            try {
                // Leer archivo .xlsx
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();

                // Establecer el rango de columnas a leer (de A a N)
                // Usamos getHighestRow() para encontrar la última fila con datos
                $highestRow = $sheet->getHighestDataRow(); // Obtiene la última fila con cualquier dato
                $range = 'A1:N' . $highestRow; // Rango de A1 hasta la columna N de la última fila con datos

                // Obtener los datos del rango especificado
                $sheetData = $sheet->rangeToArray(
                    $range,     // El rango de celdas a leer
                    null,       // No aplicar pre-casteo de valores
                    true,       // Formatear celdas (por ejemplo, fechas)
                    true,       // Incluir celdas nulas (vacías en el rango)
                    true        // Incluir las columnas como claves si TRUE (A, B, C...)
                );

                // Filtrar filas vacías (todas las columnas de A a N están vacías)
                $filteredData = [];
                foreach ($sheetData as $row) {
                    // Revisa si TODAS las celdas en el rango A-N de la fila están vacías
                    $isEmptyRow = true;
                    foreach ($row as $cellValue) {
                        // Si encuentra cualquier valor no nulo o no una cadena vacía, la fila no está vacía
                        if ($cellValue !== null && $cellValue !== '') {
                            $isEmptyRow = false;
                            break;
                        }
                    }
                    if (!$isEmptyRow) {
                        $filteredData[] = $row;
                    }
                }

                // Si la primera fila es un encabezado, la dejamos fuera del array principal de datos
                // y la manejamos por separado si es necesario.
                // Aquí, asumimos que la primera fila podría ser el encabezado y ya fue incluida
                // en $filteredData si tenía datos. Si quieres ignorar el encabezado, puedes:
                if (!empty($filteredData)) {
                    $headers = array_shift($filteredData); // Si la primera fila es el encabezado
                }


                // Aquí $filteredData contendrá solo las filas de la A a la N que tienen datos
                foreach ($filteredData as $row) {
                    $contrato = new Contratos();
                    $contrato->clinica_id = $clinica_id;
                    $contrato->plan_id = $plan_id;
                    $contrato->monto = $monto;
                    $contrato->fecha_ini = $fecha_ini;
                    $contrato->fecha_ven = $fecha_ven;
                    $contrato->created_at = $fechaCreacion;
                    $guardadoContrato = $contrato->save();
                    if ($guardadoContrato) {
                        Yii::$app->session->setFlash('success', 'Contrato guardado correctamente.');
                    } else {
                        Yii::$app->session->setFlash('error', 'Error al guardar el contrato.');
                        print_r($contrato->getErrors());
                        exit;
                    }
                    $model = new UserDatos();
                    $model->email = $row['A'];
                    $model->telefono = $row['B'];
                    $model->nombres = $row['C'];
                    $model->apellidos = $row['D'];
                    $model->tipo_cedula = $row['E'];
                    $model->cedula = $row['F'];
                    $fechaNacimiento = DateTime::createFromFormat('d/m/Y', $row['G']);
                    $model->fechanac = $fechaNacimiento->format('Y-m-d');
                    $model->sexo = $row['H'];
                    $model->tipo_sangre = $row['I'];
                    $model->estado = $row['J'];
                    $model->municipio = $row['K'];
                    $model->parroquia = $row['L'];
                    $model->ciudad = $row['M'];
                    $model->direccion = $row['N'];
                    $model->contrato_id = $contrato->id;
                    $guardo = $model->save();
                    if ($guardo) {
                        Yii::$app->session->setFlash('success', 'Afiliado guardado correctamente.');
                    } else {
                        Yii::$app->session->setFlash('error', 'Error al guardar el afiliado.');
                        print_r($model->getErrors());
                        exit;
                    }
                    $modelContrato->user_id = $model->id;
                    $modelContrato->save();
                }
                Yii::$app->session->setFlash('success', 'Afiliados guardados correctamente.');
                return $this->redirect(['index']);

            } catch (Exception $e) {
                Yii::error('Error al procesar el archivo Excel: ' . $e->getMessage());
                Yii::$app->session->setFlash('error', 'Error al leer el archivo Excel: ' . $e->getMessage());
                // Asegúrate de eliminar el archivo subido si hubo un error al leerlo
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } catch (\Exception $e) { // Captura otras excepciones generales
                Yii::error('Un error inesperado ocurrió: ' . $e->getMessage());
                Yii::$app->session->setFlash('error', 'Un error inesperado ocurrió al procesar el archivo: ' . $e->getMessage());
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            return $this->render('masivo', [
                'model' => $model,
                'modelContrato' => $modelContrato,
            ]);
        }

        return $this->render('masivo', [
            'model' => $model,
            'modelContrato' => $modelContrato,
        ]);
    }

    /**
     * Lists all UserDatos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['ilike', 'role', 'Afiliado']);
        

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexClinicas($clinica_id = "")
    {
        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'clinica_id', $clinica_id]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single UserDatos model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $estado = $model->estado;
        $municipio = $model->municipio;
        $parroquia = $model->parroquia;
        $ciudad = $model->ciudad;

        if (!empty($estado) && is_numeric($estado)) {
            $estadoModel = RmEstado::findOne($estado);
            $estado = $estadoModel ? $estadoModel->nombre : $estado;
        }
        if (!empty($municipio) && is_numeric($municipio)) {
            $municipioModel = RmMunicipio::findOne($municipio);
            $municipio = $municipioModel ? $municipioModel->nombre : $municipio;
        }
        if (!empty($parroquia) && is_numeric($parroquia)) {
            $parroquiaModel = RmParroquia::findOne($parroquia);
            $parroquia = $parroquiaModel ? $parroquiaModel->nombre : $parroquia;
        }
        if (!empty($ciudad) && is_numeric($ciudad)) {
            $ciudadModel = RmCiudad::findOne($ciudad);
            $ciudad = $ciudadModel ? $ciudadModel->nombre : $ciudad;
        }
        

        return $this->render('view', [
            'model' => $model,
            'estado' => $estado,
            'municipio' => $municipio,
            'parroquia' => $parroquia,
            'ciudad' => $ciudad,    
        ]);
    }

    /**
     * Creates a new UserDatos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $modelUser = new User();
        $model = new UserDatos();
        $modelContrato = new Contratos();
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->codigoValidacion = UserHelper::getInstance()->generarCodigoValidacion(); //generar codigo de validacion de 6 digitos
        $model->role = 'afiliado';
        $model->estatus = 'Creado';
        

        if($model->estatus_solvente == "" || $model->estatus_solvente == null){
            $model->estatus_solvente = "No";
        }


        //if ($this->request->isPost) {
            if ($model->load($this->request->post()) ) {


                if($model->save()){
            
                    // Asignar el username generado al modelo de usuario
                    $modelUser->username = $model->email;;
                    //var_dump($modelUser->username);exit;
                    $pass = 'sispsa'.$model->cedula;//Yii::$app->security->generateRandomString(8);
                    $modelUser->password_hash = User::setPassword($pass);
                    $modelUser->auth_key = User::generateAuthKey();
                    $modelUser->email = $model->email;
                    $modelUser->status = 1;
                    // Obtener archivos directamente del formulario
                    $imagenIdentificacionFiles = UploadedFile::getInstancesByName('UserDatos[imagenIdentificacionFile]');
                    $selfieFiles = UploadedFile::getInstancesByName('UserDatos[selfieFile]');

                    $model->imagenIdentificacionFile = !empty($imagenIdentificacionFiles) ? reset($imagenIdentificacionFiles) : null;
                    $model->selfieFile = !empty($selfieFiles) ? reset($selfieFiles) : null;

                   
                    if ($imagenIdentificacionFiles[0]->size > 0) {
                        $folder = 'documentos';
                        // Generamos un nombre de archivo único para evitar colisiones
                        $fileName = uniqid('imagen_identificacion_') . '.' . $model->imagenIdentificacionFile->extension;
                        // Definimos la ruta temporal en el directorio @runtime (fuera del acceso web directo por seguridad)
                        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                        if ($model->imagenIdentificacionFile->saveAs($tempFilePath)) {
                            Yii::info("Archivo temporal guardado en: " . $tempFilePath, __METHOD__);

                            // La "clave" del archivo en Supabase Storage (su nombre y ruta dentro del bucket).
                            // En este caso, solo es el nombre del archivo para que se guarde en la raíz del bucket 'usuarios'.
                            // Si quisieras una subcarpeta, sería por ejemplo 'pagos_imagenes/' . $fileName;
                            $fileKeyInBucket = $fileName;

                            // Llamamos a la función dedicada a subir el archivo a Supabase Storage via API
                            Yii::info("Subiendo archivo a Supabase Storage: " . $fileName, __METHOD__);
                            $publicUrl = UserHelper::uploadFileToSupabaseApi(
                                $tempFilePath,
                                $model->imagenIdentificacionFile->type,
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
                                $model->imagen_identificacion = $publicUrl;
                                if ($model->save(false)) { // Guardamos el modelo de pago en la base de datos
                                    Yii::$app->session->setFlash('success', 'Identificacion subido con éxito.');
                                } else {
                                    Yii::$app->session->setFlash('error', 'Error al guardar identificacion en la base de datos.');
                                }
                            } else {
                                // Si la subida a Supabase falló, el mensaje de error ya se estableció en la función de subida.
                                Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                            }
                        } else {
                            Yii::error("Error al guardar el archivo temporal: " . $model->imagenIdentificacionFile->error, __METHOD__);
                            Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
                        }

                    }
                    if ($selfieFiles[0]->size > 0) {
                        $folder = 'FotoPerfil';
                        // Generamos un nombre de archivo único para evitar colisiones
                        $fileName = uniqid('selfie_') . '.' . $model->selfieFile->extension;
                        // Definimos la ruta temporal en el directorio @runtime (fuera del acceso web directo por seguridad)
                        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                        if ($model->selfieFile->saveAs($tempFilePath)) {
                            Yii::info("Archivo temporal guardado en: " . $tempFilePath, __METHOD__);

                            // La "clave" del archivo en Supabase Storage (su nombre y ruta dentro del bucket).
                            // En este caso, solo es el nombre del archivo para que se guarde en la raíz del bucket 'usuarios'.
                            // Si quisieras una subcarpeta, sería por ejemplo 'pagos_imagenes/' . $fileName;
                            $fileKeyInBucket = $fileName;

                            // Llamamos a la función dedicada a subir el archivo a Supabase Storage via API
                            $publicUrl = UserHelper::uploadFileToSupabaseApi(
                                $tempFilePath,
                                $model->selfieFile->type,
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
                                $model->selfie = $publicUrl;
                                if ($model->save(false)) { // Guardamos el modelo de pago en la base de datos
                                    Yii::$app->session->setFlash('success', 'Selfie subido con éxito.');
                                } else {
                                    Yii::$app->session->setFlash('error', 'Error al guardar selfie en la base de datos.');
                                }
                            } else {
                                // Si la subida a Supabase falló, el mensaje de error ya se estableció en la función de subida.
                                Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                            }
                        } else {
                            Yii::error("Error al guardar el archivo temporal: " . $model->selfieFile->error, __METHOD__);
                            Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
                        }

                    }
                    if($modelUser->save()){
                        
                        
                        $modelContrato->user_id = $model->id;
                        $modelContrato->estatus = 'Registrado';
                        $modelContrato->clinica_id = $model->clinica_id;
                        $modelContrato->save();                     
                        $auth = Yii::$app->authManager;
                        $roleName = 'afiliado';
                        $role = $auth->getRole($roleName);
                        if ($role) {
                            try {
                                $auth->revokeAll($modelUser->id);
                                $auth->assign($role, $modelUser->id);
                                Yii::$app->cache->flush();
                                $model->user_login_id = $modelUser->id;
                                $model->save();
                                
                            } catch (\Exception $e) {
                                Yii::error("Error al asignar el rol: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
                            }
                        } else {
                            Yii::$app->session->setFlash('warning', "El rol '$roleName' no existe. Usuario creado, pero el rol no pudo ser asignado.");
                        }
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                    else{
                        var_dump($modelUser->errors);
                        exit;
                    }
                }else{
                    var_dump($model->errors);
                    exit;
                }
            }
                
           // }
        /*} else {
            $model->loadDefaultValues();
        }*/

        return $this->render('create', [
            'model' => $model,
            'modelContrato' => $modelContrato,
        ]);
    }

    /**
     * Updates an existing UserDatos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {   
        $model = $this->findModel($id);
        $modelContrato = Contratos::find()->where(['user_id' => $id])->one();
        if ($modelContrato === null) {
            $modelContrato = new Contratos();
            // Puedes asignar otros valores por defecto si es necesario para un nuevo contrato
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $modelContrato->load($this->request->post())) {



                if($model->user_login_id == "" || $model->user_login_id == null){

                    $modelUser = new User();
                    $modelUser->username = $model->email;
                    $pass = 'sispsa'.$model->cedula;
                    $modelUser->password_hash = User::setPassword($pass);
                    $modelUser->auth_key = User::generateAuthKey();
                    $modelUser->email = $model->email;
                    $modelUser->status = 1;
                    $modelUser->save();
                    $model->user_login_id = $modelUser->id;
                }


                if($model->estatus_solvente == "" || $model->estatus_solvente == null){
                    $model->estatus_solvente = "No";
                }

                $model->role = 'afiliado';
                $model->estatus = 'Registrado';

                $model->updated_at = date('Y-m-d H:i:s');


                if($model->save()){
                    $modelContrato->user_id = $id;
                    $modelContrato->estatus = 'Creado';
                    $modelContrato->clinica_id = $model->clinica_id;


                    if($modelContrato->save()){

                        $auth = Yii::$app->authManager;
                        $roleName = 'afiliado';
                        $role = $auth->getRole($roleName);
                        if ($role) {
                            try {
                                $auth->revokeAll($modelUser->id);
                                $auth->assign($role, $modelUser->id);
                                Yii::$app->cache->flush();
                                $model->user_login_id = $modelUser->id;
                                $model->save();
                                
                            } catch (\Exception $e) {
                                Yii::error("Error al asignar el rol: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
                            }
                        } else {
                            Yii::$app->session->setFlash('warning', "El rol '$roleName' no existe. Usuario creado, pero el rol no pudo ser asignado.");
                        }

                        return $this->redirect(['update', 'id' => $model->id]);
                    }else{

                        echo "MODEL CONTRATO NOT SAVED";
                      print_r($modelContrato->getAttributes());
                      print_r($modelContrato->getErrors());
                      exit;

                    }
                }else{
                    echo "MODEL NOT SAVED";
                      print_r($model->getAttributes());
                      print_r($model->getErrors());
                      exit;
                }
        }

        return $this->render('update', [
            'model' => $model,
            'modelContrato' => $modelContrato,
        ]);
    }

    /**
     * Deletes an existing UserDatos model.
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
     * Finds the UserDatos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return UserDatos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserDatos::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionIndexByAfiliado($asesor_id = "")
    {
        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'asesor_id', $asesor_id]);
    
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGetCorporativeAffiliates($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = UserDatos::find()
                ->where(['user_datos_type_id' => 2]) // Asume que ID 2 es 'Corporativo'
                ->andFilterWhere(['ilike', 'nombres', $q])
                ->orFilterWhere(['ilike', 'apellidos', $q])
                ->limit(20); // Limita los resultados

            $command = $query->createCommand();
            $data = $command->queryAll();

            $out['results'] = array_values(ArrayHelper::map($data, 'id', function($item) {
                return $item['nombres'] . ' ' . $item['apellidos'] . ' (' . $item['cedula'] . ')';
            }));
        }
        return $out;
    }
    
}
