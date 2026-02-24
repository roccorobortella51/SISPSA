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

        // Check if user is logged in and has GERENTE-CLINICA role
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            $auth = Yii::$app->authManager;
            $roles = $auth->getRolesByUser($user->id);

            // Check if user has GERENTE-CLINICA role
            if (isset($roles['GERENTE-CLINICA'])) {
                return $this->render('gerente-dashboard');
            }
        }

        // For all other users (including guests), show the welcome page
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




    public function actionMunicipio()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $est_id = $parents[0];
                if ($est_id == '') {
                    return ['output' => '', 'selected' => ''];
                }
                $out = RmMunicipio::find()->select(['codigo_muni as id', 'nombre as name'])->where(['estado_codigo' => $est_id])->asArray()->all();
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionParroquia()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $mun_id = $parents[0];
                if ($mun_id == '') {
                    return ['output' => '', 'selected' => ''];
                }
                $out = RmParroquia::find()->select(['id', 'nombre as name'])->where(['muni_codigo' => $mun_id])->asArray()->all();
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionCiudad()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        $selected = isset($_POST['depdrop_selected']) ? $_POST['depdrop_selected'] : '';
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $est_id = $parents[0];
                if ($est_id == '') {
                    return ['output' => '', 'selected' => $selected];
                }
                $out = RmCiudad::find()->select(['id', 'nombre as name'])->where(['estado_codigo' => $est_id])->asArray()->all();
                return ['output' => $out, 'selected' => $selected];
            }
        }
        return ['output' => '', 'selected' => $selected];
    }




    public function actionPlanes()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $cli_id = $parents[0];
                if ($cli_id == '') {
                    return ['output' => '', 'selected' => ''];
                }
                $planes = Planes::find()->where(['clinica_id' => $cli_id])->all();
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
        return ['output' => '', 'selected' => ''];
    }

    public function actionPlanmonto($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; // Establece el formato de respuesta a JSON
        if (!is_numeric($id)) {
            return ['output' => '', 'selected' => ''];
        }
        $plan = Planes::findOne($id);

        if ($plan) {
            return ['monto' => (float)$plan->precio]; // Devuelve el monto del plan
        } else {
            return ['monto' => 0]; // Si no se encuentra, devuelve 0 o un valor por defecto
        }
    }

    public function actionTasacambio($fecha = null)
    {
        $fecha = Yii::$app->request->post('fecha');

        if ($fecha == null) {
            $fecha = date('Y-m-d');
        }

        $tasacambio = Tasacambio::find()->select(['tasa_cambio'])->where(['fecha' => $fecha])->one();

        if ($tasacambio == null) {
            $tasacambio = new Tasacambio();
            $tasacambio->fecha = $fecha;
            $tasacambio->tasa_cambio = $this->explorartasabcv();
            $tasacambio->save();
        }
        $tasacambio = TasaCambio::find()->select(['tasa_cambio'])->where(['fecha' => $fecha])->one();
        return $tasacambio->tasa_cambio;
    }

    private function explorartasabcv()
    {
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
    private function getDefaultExchangeRate()
    {
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

    /**
     * Endpoint para municipios con nombre e ID para ayuda masivo
     */
    public function actionMunicipioIds()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $parents = Yii::$app->request->post('depdrop_parents');
        $out = [];
        if ($parents && isset($parents[0])) {
            $estado_id = $parents[0];
            $municipios = \app\models\RmMunicipio::find()->orderBy(['nombre' => SORT_ASC])->where(['estado_codigo' => $estado_id])->all();
            $out = [];
            foreach ($municipios as $muni) {
                $out[] = [
                    'id' => $muni->codigo_muni,
                    'name' => $muni->nombre . ' (ID: ' . $muni->id . ')'
                ];
            }
        }
        return ['output' => $out, 'selected' => ''];
    }

    /**
     * Endpoint para parroquias con nombre e ID para ayuda masivo
     */
    public function actionParroquiaIds()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $parents = Yii::$app->request->post('depdrop_parents');
        $out = [];
        if ($parents && isset($parents[0])) {
            $municipio_codigo = $parents[0];
            $parroquias = \app\models\RmParroquia::find()->where(['muni_codigo' => $municipio_codigo])->orderBy(['nombre' => SORT_ASC])->all();
            foreach ($parroquias as $parro) {
                $out[] = [
                    'id' => $parro->id,
                    'name' => $parro->nombre . ' (ID: ' . $parro->id . ')'
                ];
            }
        }
        return ['output' => $out, 'selected' => ''];
    }

    /**
     * Endpoint para ciudades con nombre e ID para ayuda masivo
     */
    public function actionCiudadIds()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $parents = Yii::$app->request->post('depdrop_parents');
        $out = [];
        if ($parents && isset($parents[0])) {
            $estado_id = $parents[0];
            $ciudades = \app\models\RmCiudad::find()->where(['estado_codigo' => $estado_id])->orderBy(['nombre' => SORT_ASC])->all();
            foreach ($ciudades as $ciudad) {
                $out[] = ['id' => $ciudad->id, 'name' => $ciudad->nombre . ' (ID: ' . $ciudad->id . ')'];
            }
        }
        return ['output' => $out, 'selected' => ''];
    }

    /**
     * Returns list of planes for Select2 widget
     */
    public function actionPlanesList($q = null, $clinica_id = null, $page = 1)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $query = Planes::find();

        if ($clinica_id) {
            $query->andWhere(['clinica_id' => $clinica_id]);
        }

        if (!empty($q)) {
            $query->andWhere(['like', 'nombre', $q]);
        }

        // Pagination
        $pageSize = 20;
        $offset = ($page - 1) * $pageSize;

        $totalCount = $query->count();
        $results = $query->offset($offset)
            ->limit($pageSize)
            ->all();

        $formattedResults = [];
        foreach ($results as $plan) {
            $formattedResults[] = [
                'id' => $plan->id,
                'text' => $plan->nombre . ' ($' . number_format($plan->monto, 2) . ')',
            ];
        }

        return [
            'results' => $formattedResults,
            'pagination' => [
                'more' => ($page * $pageSize) < $totalCount
            ]
        ];
    }

    /**
     * Gets clinic statistics for the GERENTE-CLINICA dashboard
     * @return array|\yii\web\Response
     */
    public function actionGetClinicaStats()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;

        // Get the clinica_id from the logged-in user's related data
        $userDatos = \app\models\UserDatos::find()
            ->where(['user_login_id' => $user->id])
            ->one();

        if (!$userDatos || !$userDatos->clinica_id) {
            return ['error' => 'No clinic associated with this user'];
        }

        $clinicaId = $userDatos->clinica_id;
        $clinica = \app\models\RmClinica::findOne($clinicaId);

        // Base query for affiliates of this clinic
        $query = \app\models\UserDatos::find()
            ->where(['clinica_id' => $clinicaId])
            ->andWhere(['role' => 'afiliado']);

        // Total affiliates
        $totalAfiliados = $query->count();

        // Status breakdown (affiliate status)
        $activos = (clone $query)->andWhere(['estatus' => 'Activo'])->count();
        $suspendidos = (clone $query)->andWhere(['estatus' => 'Suspendido'])->count();
        $pendientes = (clone $query)->andWhere(['estatus' => 'Pendiente'])->count();
        $inactivos = (clone $query)->andWhere(['estatus' => 'Inactivo'])->count();

        // Solvency status
        $solventes = (clone $query)->andWhere(['estatus_solvente' => 'Si'])->count();
        $insolventes = (clone $query)->andWhere(['estatus_solvente' => 'No'])->count();

        // Gender distribution
        $masculinos = (clone $query)->andWhere(['sexo' => 'Masculino'])->count();
        $femeninos = (clone $query)->andWhere(['sexo' => 'Femenino'])->count();

        // Affiliation type
        $individuales = (clone $query)->andWhere(['user_datos_type_id' => 1])->count();
        $corporativos = (clone $query)->andWhere(['user_datos_type_id' => 2])->count();

        // Recent affiliates (last 30 days)
        $recientes = (clone $query)
            ->andWhere(['>=', 'created_at', date('Y-m-d H:i:s', strtotime('-30 days'))])
            ->count();

        // Monthly growth data for chart
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $startDate = date('Y-m-01', strtotime("-$i months"));
            $endDate = date('Y-m-t', strtotime("-$i months"));

            $count = (clone $query)
                ->andWhere(['>=', 'created_at', $startDate . ' 00:00:00'])
                ->andWhere(['<=', 'created_at', $endDate . ' 23:59:59'])
                ->count();

            $monthlyData[] = [
                'month' => date('M Y', strtotime($month . '-01')),
                'count' => (int)$count
            ];
        }

        // Contracts expiring soon (next 30 days)
        $contratosPorVencer = \app\models\Contratos::find()
            ->alias('c')
            ->innerJoin('user_datos ud', 'ud.id = c.user_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['between', 'c.fecha_ven', date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))])
            ->count();

        // ===== NEW: CONTRACT STATUS DISTRIBUTION =====
        // Get contract status counts for this clinic
        $contratosActivos = \app\models\Contratos::find()
            ->alias('c')
            ->innerJoin('user_datos ud', 'ud.id = c.user_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['c.estatus' => 'Activo'])
            ->count();

        $contratosCreados = \app\models\Contratos::find()
            ->alias('c')
            ->innerJoin('user_datos ud', 'ud.id = c.user_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['c.estatus' => 'Creado'])
            ->count();

        $contratosAnulados = \app\models\Contratos::find()
            ->alias('c')
            ->innerJoin('user_datos ud', 'ud.id = c.user_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['c.estatus' => 'Anulado'])
            ->count();

        $contratosVencidos = \app\models\Contratos::find()
            ->alias('c')
            ->innerJoin('user_datos ud', 'ud.id = c.user_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['<', 'c.fecha_ven', date('Y-m-d')])
            ->andWhere(['not', ['c.estatus' => 'Anulado']]) // Exclude already anulados
            ->count();

        $contractStatus = [
            'activos' => (int)$contratosActivos,
            'creados' => (int)$contratosCreados,
            'anulados' => (int)$contratosAnulados,
            'vencidos' => (int)$contratosVencidos,
        ];

        // Get plan distribution
        $planesPopulares = (clone $query)
            ->select(['plan_id', 'COUNT(*) as count'])
            ->where(['not', ['plan_id' => null]])
            ->groupBy('plan_id')
            ->orderBy(['count' => SORT_DESC])
            ->limit(5)
            ->with('plan')
            ->asArray()
            ->all();

        $planData = [];
        foreach ($planesPopulares as $item) {
            if ($item['plan_id']) {
                $plan = \app\models\Planes::findOne($item['plan_id']);
                $planData[] = [
                    'name' => $plan ? $plan->nombre : 'Unknown',
                    'count' => (int)$item['count']
                ];
            }
        }

        return [
            'success' => true,
            'clinica' => $clinica ? $clinica->nombre : 'Unknown',
            'stats' => [
                'total' => (int)$totalAfiliados,
                'activos' => (int)$activos,
                'suspendidos' => (int)$suspendidos,
                'pendientes' => (int)$pendientes,
                'inactivos' => (int)$inactivos,
                'solventes' => (int)$solventes,
                'insolventes' => (int)$insolventes,
                'masculinos' => (int)$masculinos,
                'femeninos' => (int)$femeninos,
                'individuales' => (int)$individuales,
                'corporativos' => (int)$corporativos,
                'recientes' => (int)$recientes,
                'contratos_por_vencer' => (int)$contratosPorVencer,
                'tasa_actividad' => $totalAfiliados > 0 ? round(($activos / $totalAfiliados) * 100, 1) : 0,
                'tasa_solvencia' => $totalAfiliados > 0 ? round(($solventes / $totalAfiliados) * 100, 1) : 0,
            ],
            'contract_status' => $contractStatus, // New contract status data
            'monthly_growth' => $monthlyData,
            'plan_distribution' => $planData
        ];
    }
}
