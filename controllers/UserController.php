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
use app\components\UserHelper;

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

    public function actionIndexUserByAgente()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->searchAgentes($this->request->queryParams);

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

    public function actionCreate()
    {
        $model = new User();
        $model2 = new UserDatos();

        // Retrieve cedula and role from GET or POST
        $cedula = Yii::$app->request->get('cedula');
        $role = Yii::$app->request->get('role');

        // Pre-fill the cedula if provided
        if ($cedula) {
            $model2->cedula = $cedula;
            // You might want to set a session flash or pre-fill the search
            Yii::$app->session->setFlash('info', 'Se ha pre-cargado la cédula: ' . $cedula);
        }

        // Pre-select the role if provided
        if ($role) {
            $model2->role = $role;
            $model->roles = $role;
        }
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
                    if ($rol) {
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

        if ($model2 == "" || $model2 == null) {
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
            } else {
                $model->password_hash = User::setPassword($model->password);
            }
            if ($model->save(false)) {

                $model2->user_login_id = $model->id;
                //$model2->user_id = $model->id;
                $model2->save(false);


                $auth->revokeAll($model->id);
                $rol = $auth->getRole($model->roles);
                if ($rol) {
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

    // Add these methods to your existing UserController class

    /**
     * Finds user by cedula
     * @return \yii\web\Response
     */
    /**
     * Finds user by cedula for the agente form
     * @return \yii\web\Response
     */
    public function actionFindByCedula()  // ← Remove the extra 'c' - should be 'Cedula' not 'Ccedula'
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $cedula = Yii::$app->request->get('cedula');

        if (!$cedula) {
            return ['success' => false, 'message' => 'Cédula no proporcionada'];
        }

        try {
            Yii::info("Searching for user with cedula: " . $cedula);

            // Clean and validate the cedula input
            $cedula = trim($cedula);

            // Remove any non-numeric characters (in case user enters formatted cedula)
            $cedula = preg_replace('/[^0-9]/', '', $cedula);

            if (empty($cedula)) {
                return ['success' => false, 'message' => 'Cédula no válida'];
            }

            // Convert to integer for database search
            $cedulaInt = (int)$cedula;

            Yii::info("Cleaned cedula for search: " . $cedulaInt);

            // Find user by cedula in UserDatos
            $userDatos = UserDatos::find()
                ->where(['cedula' => $cedulaInt])
                ->andWhere(['IS NOT', 'user_login_id', null])
                ->with('userLogin')
                ->one();

            Yii::info("UserDatos found: " . ($userDatos ? 'Yes' : 'No'));

            if ($userDatos && $userDatos->userLogin) {
                return [
                    'success' => true,
                    'user' => [
                        'id' => $userDatos->userLogin->id,
                        'nombres' => $userDatos->nombres,
                        'apellidos' => $userDatos->apellidos,
                        'tipo_cedula' => $userDatos->tipo_cedula,
                        'cedula' => $userDatos->cedula,
                        'email' => $userDatos->userLogin->email,
                    ]
                ];
            }

            return ['success' => false, 'message' => 'Usuario no encontrado'];
        } catch (\Exception $e) {
            Yii::error('Error in actionFindByCedula: ' . $e->getMessage());
            Yii::error('Stack trace: ' . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()];
        }
    }

    /**
     * Creates a new user with Agente role for the agencia form
     * @return \yii\web\Response
     */
    public function actionCreateAgenteUser()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $post = Yii::$app->request->post();

        try {
            // Create User model
            $user = new User();
            $user->scenario = 'create';
            $user->username = $post['email']; // Use email as username
            $user->email = $post['email'];
            $user->password = $post['password'];
            $user->status = User::STATUS_ACTIVE;
            $user->roles = 'Agente'; // Predefined role

            // Create UserDatos model - ADD ALL THE NEW FIELDS
            $userDatos = new UserDatos();
            $userDatos->nombres = $post['nombres'];
            $userDatos->apellidos = $post['apellidos'];
            $userDatos->tipo_cedula = $post['tipo_cedula'];
            $userDatos->cedula = $post['cedula'];
            $userDatos->telefono = $post['telefono'] ?? null;
            $userDatos->email = $post['email'];
            $userDatos->fechanac = $post['fechanac'] ?? null;        // ADD THIS
            $userDatos->sexo = $post['sexo'] ?? null;                // ADD THIS
            $userDatos->estado = $post['estado'] ?? null;            // ADD THIS
            $userDatos->direccion = $post['direccion'] ?? null;      // ADD THIS
            $userDatos->role = 'Agente'; // Predefined role

            // Validate and save both models
            if ($user->validate() && $userDatos->validate()) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    if ($user->save()) {
                        // Assign RBAC role
                        $auth = Yii::$app->authManager;
                        $auth->revokeAll($user->id);
                        $rol = $auth->getRole($user->roles);
                        if ($rol) {
                            $auth->assign($rol, $user->id);
                            Yii::$app->cache->flush();
                        }

                        // Save UserDatos
                        $userDatos->user_login_id = $user->id;
                        if ($userDatos->save(false)) {
                            $transaction->commit();

                            return [
                                'success' => true,
                                'user' => [
                                    'id' => $user->id,
                                    'nombres' => $userDatos->nombres,
                                    'apellidos' => $userDatos->apellidos,
                                    'email' => $user->email
                                ]
                            ];
                        }
                    }

                    $transaction->rollBack();
                    return ['success' => false, 'message' => 'Error al guardar los datos del usuario'];
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::error('Error creating agente user: ' . $e->getMessage());
                    return ['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()];
                }
            } else {
                $errors = array_merge($user->errors, $userDatos->errors);
                return ['success' => false, 'message' => 'Errores de validación: ' . json_encode($errors)];
            }
        } catch (\Exception $e) {
            Yii::error('Error in createAgenteUser: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()];
        }
    }

    // Add this temporary method to your UserController for testing
    public function actionTestConnection()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['success' => true, 'message' => 'Controller is accessible', 'timestamp' => date('Y-m-d H:i:s')];
    }
    public function actionDebugRole()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;
        $isGuest = Yii::$app->user->isGuest;
        $roles = Yii::$app->authManager->getRolesByUser($userId);
        $roleNames = array_keys($roles);
        $userHelperRole = UserHelper::getMyRol();

        return [
            'success' => true,
            'user_id' => $userId,
            'is_guest' => $isGuest,
            'rbac_roles' => $roleNames,
            'user_helper_role' => $userHelperRole,
            'session_id' => session_id(),
        ];
    }
}
