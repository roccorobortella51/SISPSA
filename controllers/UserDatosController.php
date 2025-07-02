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
use app\models\Contratos;
use app\models\RmClinica;
use app\models\Planes;
use yii\base\Security;

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

    /**
     * Lists all UserDatos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

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
        return $this->render('view', [
            'model' => $this->findModel($id),
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




        if ($this->request->isPost) {
            if ($model->load($this->request->post())  && $modelContrato->load($this->request->post())) {
                $cel = explode("-",$model->cedula);
                $model->tipo_cedula = $cel[0];
                $model->cedula = $cel[1];
                if($model->save()){
                    $nombres = $model->nombres;
                    $apellidos = $model->apellidos;
                    // 1. Tomar las 3 primeras letras del nombre y convertir a minúsculas
                    $primerasTresNombre = substr($nombres, 0, 3);
                    $primerasTresNombre = mb_strtolower($primerasTresNombre, 'UTF-8'); // Usa mb_strtolower para caracteres UTF-8

                    // 2. Limpiar el apellido: quitar espacios y caracteres especiales, luego convertir a minúsculas
                    // Usamos preg_replace para mantener solo letras y números
                    $apellidoLimpio = preg_replace('/[^a-zA-Z0-9]/', '', $apellidos);
                    $apellidoLimpio = mb_strtolower($apellidoLimpio, 'UTF-8'); // Usa mb_strtolower

                    // 3. Combinar para formar el username base
                    $usernameBase = $primerasTresNombre . $apellidoLimpio;

                    // 4. Asegurar que el username sea único (es crucial en un sistema de usuarios)
                    $usernameFinal = UserHelper::generateUniqueUsername($usernameBase);

                    // Asignar el username generado al modelo de usuario
                    $modelUser->username = $usernameFinal;
                    //var_dump($modelUser->username);exit;
                    $pass = '123456789';//Yii::$app->security->generateRandomString(8);
                    $modelUser->password_hash = User::setPassword($pass);
                    $modelUser->auth_key = User::generateAuthKey();
                    $modelUser->email = $model->email;
                    $modelUser->status = 1;
                    if($modelUser->save()){
                        
                        
                        $modelContrato->user_id = $model->id;
                        $modelContrato->estatus = 'Creado';
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
                }
                else{
                    var_dump($model->errors);
                    exit;
                }
            }
        } else {
            $model->loadDefaultValues();
        }

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
                $cel = explode("-",$model->cedula);
                $model->tipo_cedula = $cel[0];
                $model->cedula = $cel[1];
                if($model->save()){
                    $modelContrato->user_id = $id;
                    $modelContrato->estatus = 'Creado';
                    $modelContrato->clinica_id = $model->clinica_id;
                    if($modelContrato->save()){
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
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
}
