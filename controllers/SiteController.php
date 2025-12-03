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
use app\models\TasaCambio;



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
        $tasa_bcv = $this->actionTasacambio(date('Y-m-d'));

        return $this->render('welcome');
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
            $tasa_bcv = $this->actionTasacambio(date('Y-m-d'));
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

        // Use explicit redirect to the login action
        return $this->redirect(['site/login']);
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
        $selected = isset($_POST['depdrop_selected']) ? $_POST['depdrop_selected'] : '';
        if (isset($_POST['depdrop_parents'])) {
        $parents = $_POST['depdrop_parents'];
        if ($parents != null) {
            $est_id = $parents[0];
            if($est_id == ''){return ['output'=>'', 'selected'=>$selected];}
            $out = RmCiudad::find()->select(['id', 'nombre as name'])->where(['estado_codigo'=>$est_id])->asArray()->all();
            return ['output'=>$out, 'selected'=>$selected];
        }
        }
        return ['output'=>'', 'selected'=>$selected];
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

    public function actionTasacambio($fecha=null){
        $fecha = Yii::$app->request->post('fecha');

        if($fecha == null){
            $fecha = date('Y-m-d');
        }

        $tasacambio = Tasacambio::find()->select(['tasa_cambio'])->where(['fecha' => $fecha])->one();

        if($tasacambio == null){
            $tasacambio = new Tasacambio();
            $tasacambio->fecha = $fecha;
            $tasacambio->tasa_cambio = $this->explorartasabcv();
            $tasacambio->save();
        }
        $tasacambio = TasaCambio::find()->select(['tasa_cambio'])->where(['fecha' => $fecha])->one();
        return $tasacambio->tasa_cambio;


    }

    private function explorartasabcv() {
    $url = "https://www.bcv.org.ve/";
    
    // Add timeout context
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
        'http' => [
            'timeout' => 30, // Increase timeout to 30 seconds
        ]
    ]);
    
    // Use @ to suppress warnings and add error handling
    $html = @file_get_contents($url, false, $context);
    
    // Check if fetch failed
    if ($html === false) {
        // Log the error for debugging
        Yii::warning("Failed to fetch BCV data. Error: " . 
                     (error_get_last()['message'] ?? 'Unknown error'));
        
        // Return a default value instead of breaking the page
        return $this->getDefaultExchangeRate();
    }
    
    $dom = new \DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    $xpath = new \DOMXPath($dom);
    $tasa_bcv = $xpath->query("//*[@id='dolar']/div/div/div/strong");
    
    // Check if element was found
    if ($tasa_bcv->length > 0) {
        $valor = str_replace(',', '.', trim($tasa_bcv->item(0)->textContent));
        return (float) $valor;
    } else {
        Yii::warning("Could not find exchange rate element on BCV page");
        return $this->getDefaultExchangeRate();
    }
    }

    // Add this helper function to provide a default value
    private function getDefaultExchangeRate() {
        // Try to get the latest rate from your database
        $latestRate = TasaCambio::find()
            ->select(['tasa_cambio'])
            ->orderBy(['fecha' => SORT_DESC])
            ->limit(1)
            ->scalar();
        
        // If no rate in database, use a reasonable default
        return $latestRate ?: 36.00;
    }

    public function actionCuotaGenerar()
{
    // Disable CSRF validation
    $this->enableCsrfValidation = false;
    
    try {
        $cuotaController = new \app\commands\CuotaController('cuota', Yii::$app);
        ob_start();
        $exitCode = $cuotaController->actionGenerar();
        $output = ob_get_clean();
        
        $formattedOutput = nl2br(htmlspecialchars($output));
        
        echo "<h1>Generación de Cuotas</h1>";
        echo "<div style='background: #f5f5f5; padding: 15px; font-family: monospace;'>";
        echo $formattedOutput;
        echo "</div>";
        
        if ($exitCode === 0) {
            echo "<p style='color: green;'><strong>✅ Proceso completado exitosamente</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>❌ Error en el proceso</strong></p>";
        }
        
    } catch (\Exception $e) {
        echo "<h1>Error del Sistema</h1>";
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}

}