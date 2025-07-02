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
    public function actionCreate()
    {
        $model = new User();
        $model2 = new UserDatos();
        $modelAuthAssignment = new AuthAssignment();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->password_hash = User::setPassword($model->password);
            $model->auth_key = User::generateAuthKey();
            $auth = Yii::$app->authManager;

            $model2->load($this->request->post());

            if ($model->save()) {
                $auth->revokeAll($model->id);
                $rol = $auth->getRole($model->roles);
                if ($rol){
                    $auth->assign($rol, $model->id);
                    Yii::$app->cache->flush();
                }

                $model2->user_login_id = $model->id; //id-user

                $model2->save(false);
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                \Yii::error('User create failed: ' . json_encode($model->errors));
                \Yii::error('POST data: ' . json_encode($this->request->post()));
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'model2' => $model2
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

        $model2 = UserDatos::find()->where(['user_id' => $model->id])->one();

        if($model2 == "" || $model2 == null){
            $model2 = new UserDatos();
            $model2->user_id = $model->id;
            $model2->save(false);
        }

        
        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model2->load($this->request->post());
            

            if ($model->password == '') {
                $model->password_hash = $pass;
            }
            else {
                $model->password_hash = User::setPassword($model->password);
            }
            if ($model->save()) {

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
