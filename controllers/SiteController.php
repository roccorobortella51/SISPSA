<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\RmMunicipio;
use app\models\RmParroquia;
use app\models\RmCiudad;
use app\models\Planes;
use yii\helpers\Json;


class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        
        return $this->render('sispsa');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        // Cambiamos el layout para que la página de login no muestre el menú lateral ni la barra superior.
        $this->layout = 'main-login';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionMunicipio(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
        $parents = $_POST['depdrop_parents'];
        if ($parents != null) {
            $est_id = $parents[0];
            if($est_id == ''){return ['output'=>'', 'selected'=>''];}
            $out = RmMunicipio::find()->select(['codigo_muni as id', 'nombre as name'])->where(['estado_codigo'=>$est_id])->asArray()->all(); 
            return ['output'=>$out, 'selected'=>''];
        }
        }
        return ['output'=>'', 'selected'=>''];
    }

    public function actionParroquia(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
        $parents = $_POST['depdrop_parents'];
        if ($parents != null) {
            $mun_id = $parents[0];
            if($mun_id == ''){return ['output'=>'', 'selected'=>''];}
            $out = RmParroquia::find()->select(['id', 'nombre as name'])->where(['muni_codigo'=>$mun_id])->asArray()->all(); 
            return ['output'=>$out, 'selected'=>''];
        }
        }
        return ['output'=>'', 'selected'=>''];
    }

    public function actionCiudad(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
        $parents = $_POST['depdrop_parents'];
        if ($parents != null) {
            $est_id = $parents[0];
            if($est_id == ''){return ['output'=>'', 'selected'=>''];}
            $out = RmCiudad::find()->select(['id', 'nombre as name'])->where(['estado_codigo'=>$est_id])->asArray()->all(); 
            return ['output'=>$out, 'selected'=>''];
        }
        }
        return ['output'=>'', 'selected'=>''];
    }
    public function actionPlanes(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
        $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cli_id = $parents[0];
                if($cli_id == ''){return ['output'=>'', 'selected'=>''];}
                $planes = Planes::find()->where(['clinica_id'=>$cli_id])->all(); 
                foreach ($planes as $plan) {
                    // ¡IMPORTANTE! Añade el monto a la salida
                    $out[] = [
                        'id' => $plan->id, 
                        'name' => $plan->nombre, 
                        'monto' => $plan->precio // Asegúrate de que tu modelo Plan tenga un atributo 'monto'
                    ]; 
                }
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output'=>'', 'selected'=>''];
    }

    public function actionPlanmonto($id){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; // Establece el formato de respuesta a JSON
        if(!is_numeric($id)){return ['output'=>'', 'selected'=>''];}
        $plan = Planes::findOne($id);

        if ($plan) {
            return ['monto' => (float)$plan->precio]; // Devuelve el monto del plan
        } else {
            return ['monto' => 0]; // Si no se encuentra, devuelve 0 o un valor por defecto
        }
    }

}
