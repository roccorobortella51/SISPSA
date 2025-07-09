<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserDatos;
use app\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\AuthAssignment;


/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
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
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexClinicas($clinica_id = "")
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'clinica_id', $clinica_id]);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Displays a single User model.
     * @param int $id
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
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    // public function actionCreate()
    // {
    //     $model = new User();
    //     $model2 = new UserDatos();
    //     //$modelAuthAssignment = new AuthAssignment();

    //     $auth = Yii::$app->authManager; // se accede al componente AuthManager para RBAC

    //     // se sincroniza el rol
    //     $model->roles = $model2->role;

    //     // 3. Establecer la contraseña y la clave de autenticación para el modelo User
    //     if (!empty($model->password)) { // Asumiendo que 'password' es un campo temporal en User para el input
    //         $model->password_hash = User::setPassword($model->password);
    //         $model->auth_key = User::generateAuthKey();
    //         // Si manejas verificación de email, podrías generar el token aquí:
    //         // $model->generateEmailVerificationToken();
    //     }

    //     if ($this->request->isPost) {
    //         $model->load($this->request->post());
    //         $model->password_hash = User::setPassword($model->password);
    //         $model->auth_key = User::generateAuthKey();
    //         $auth = Yii::$app->authManager;

    //         $model2->load($this->request->post());

    //         if ($model->save()) {
    //             $auth->revokeAll($model->id);
    //             $rol = $auth->getRole($model->roles);
    //             if ($rol){
    //                 $auth->assign($rol, $model->id);
    //                 Yii::$app->cache->flush();
    //             }

    //             $model2->user_login_id = $model->id; //id-user

    //             $model2->save(false);
    //             return $this->redirect(['view', 'id' => $model->id]);
    //         } else {
    //             \Yii::error('User create failed: ' . json_encode($model->errors));
    //             \Yii::error('POST data: ' . json_encode($this->request->post()));
    //         }
    //     } else {
    //         $model->loadDefaultValues();
    //     }

    //     return $this->render('create', [
    //         'model' => $model,
    //         'model2' => $model2
    //     ]);
    // }

    public function actionCreate()
{
    $model = new User();
    $model2 = new UserDatos();
    
    $auth = Yii::$app->authManager;

    if ($this->request->isPost) {
        $model->load($this->request->post());
        $model2->load($this->request->post());

        // Sincroniza el rol del formulario (model2) con el campo de User (model)
        $model->roles = $model2->role; 
       
        $model->scenario = 'create'; 
        
        // Las líneas de depuración que estaban aquí se han comentado o eliminado.
        // \Yii::info('Datos de $model ANTES de validar/guardar: ' . json_encode($model->attributes));
        // ... y las demás líneas de info/error para depuración ...
        
        if ($model->validate() && $model2->validate()) {
            if ($model->save()) { // Guarda el modelo User
                // El password_hash y auth_key ya se generaron en beforeSave()
                // y el status se convirtió al valor correcto.
                
                // Asignar el rol RBAC al usuario recién creado
                $auth->revokeAll($model->id); // Esto es redundante para un usuario nuevo pero inofensivo.
                $rol = $auth->getRole($model->roles);
                if ($rol){
                    $auth->assign($rol, $model->id);
                    Yii::$app->cache->flush(); // Limpiar la caché de RBAC si la usas
                } else {
                    // Considera registrar una advertencia si el rol no se encuentra
                    // \Yii::warning('El rol RBAC ' . $model->roles . ' no fue encontrado o asignado.');
                }

                // Enlazar y guardar el modelo UserDatos
                $model2->user_login_id = $model->id; // ¡CRUCIAL! Asigna el ID del User recién creado
                //$model2->user_id = $model->id; // Asigna también user_id si es el mismo que user_login_id
                
                if ($model2->save(false)) { // Usamos save(false) porque ya validamos $model2
                    Yii::$app->session->setFlash('success', 'Usuario y datos personales creados exitosamente.');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    // Error si UserDatos no se pudo guardar
                    \Yii::error('Error al guardar UserDatos: ' . json_encode($model2->errors));
                    Yii::$app->session->setFlash('error', 'Error al guardar los datos personales del usuario.');
                    // Considera una transacción para revertir la creación de User si UserDatos falla
                }
            } else {
                // Error si User no se pudo guardar
                \Yii::error('Error al guardar User: ' . json_encode($model->errors));
                Yii::$app->session->setFlash('error', 'Error al crear el usuario. Por favor, revise los errores.');
            }
        } else {
            // Errores de validación en los formularios
            \Yii::error('Errores de validación en Create (User): ' . json_encode($model->errors));
            \Yii::error('Errores de validación en Create (UserDatos): ' . json_encode($model2->errors));
            Yii::$app->session->setFlash('error', 'Por favor, corrija los errores en el formulario.');
        }
    } else {
        // Carga los valores por defecto si la página se muestra por primera vez
        $model->loadDefaultValues();
        $model2->loadDefaultValues(); 
    }

    return $this->render('create', [
        'model' => $model,
        'model2' => $model2,
    ]);
}
    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $pass = $model->password_hash;
        $auth = Yii::$app->authManager;

        $model2 = UserDatos::find()->where(['user_login_id' => $model->id])->one();

        if($model2 == "" || $model2 == null){
            $model2 = new UserDatos();
            $model2->save(false);
        }

        
        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model2->load($this->request->post());

             
        // Sincroniza el rol seleccionado del formulario (en model2) con el campo que usa el authManager (en model)
        $model->roles = $model2->role; 
        
            

            if ($model->password == '') {
                $model->password_hash = $pass;
            }
            else {
                $model->password_hash = User::setPassword($model->password);
            }
            if ($model->save(false)) {

                $model2->user_login_id = $model->id;
                //$model2->user_id = $model->id;
                $model2->save(false);


                $auth->revokeAll($model->id);
                $rol = $auth->getRole($model->roles);
                if ($rol){
                    $auth->assign($rol, $model->id);
                    Yii::$app->cache->flush();
                }
                return $this->redirect(['index']);
            } else {
                \Yii::error('User update failed: ' . json_encode($model->errors));
                \Yii::error('POST data: ' . json_encode($this->request->post()));
            }
        }

        return $this->render('update', [
            'model' => $model,
            'model2' => $model2,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
