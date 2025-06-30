<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserDatos;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for User and UserDatos models.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->with('userDatos'),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model along with UserDatos.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $user = $this->findModel($id);
        $userDatos = UserDatos::findOne(['user_login_id' => $id]);

        return $this->render('view', [
            'model' => $user,
            'userDatos' => $userDatos,
        ]);
    }

    /**
     * Creates a new User model and associated UserDatos.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $user = new User();
        $userDatos = new UserDatos();

        if ($user->load(Yii::$app->request->post()) && $userDatos->load(Yii::$app->request->post())) {
            $user->setPassword($user->password);
            $user->generateAuthKey();
            if ($user->save()) {
                $userDatos->user_login_id = $user->id;
                if ($userDatos->save()) {
                    return $this->redirect(['view', 'id' => $user->id]);
                }
            }
        }

        return $this->render('create', [
            'model' => $user,
            'userDatos' => $userDatos,
        ]);
    }

    /**
     * Updates an existing User model and associated UserDatos.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $user = $this->findModel($id);
        $userDatos = UserDatos::findOne(['user_login_id' => $id]);
        if ($userDatos === null) {
            $userDatos = new UserDatos();
            $userDatos->user_login_id = $id;
        }

        if ($user->load(Yii::$app->request->post()) && $userDatos->load(Yii::$app->request->post())) {
            if (!empty($user->password)) {
                $user->setPassword($user->password);
            } else {
                // Do not change password if empty
                unset($user->password);
            }
            if ($user->save() && $userDatos->save()) {
                return $this->redirect(['view', 'id' => $user->id]);
            }
        }

        return $this->render('update', [
            'model' => $user,
            'userDatos' => $userDatos,
        ]);
    }

    /**
     * Deletes an existing User model and associated UserDatos.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $userDatos = UserDatos::findOne(['user_login_id' => $id]);
        if ($userDatos !== null) {
            $userDatos->delete();
        }
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested user does not exist.');
    }
}
