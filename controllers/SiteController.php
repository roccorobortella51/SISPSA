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
use app\models\UserDatos;
use app\models\RmClinica;
use app\models\SisSiniestro;
use app\components\UserHelper;
use app\models\Baremo;

use app\models\SisSiniestroBaremo;



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

        // Check if user is logged in
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            $authManager = Yii::$app->authManager;
            $roles = $authManager->getRolesByUser($user->id);

            // If user has GERENTE-CLINICA role, redirect to dashboard
            if (isset($roles['GERENTE-CLINICA'])) {
                return $this->redirect(['site/dashboard']);
            }
        }

        // Otherwise show the regular welcome page
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

            // After successful login, check role and redirect accordingly
            $user = Yii::$app->user->identity;
            $authManager = Yii::$app->authManager;
            $roles = $authManager->getRolesByUser($user->id);

            if (isset($roles['GERENTE-CLINICA'])) {
                return $this->redirect(['site/dashboard']);
            }

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

    /**
     * Main dashboard for GERENTE-CLINICA with tabs
     * @return string
     */
    public function actionDashboard()
    {
        // Check if user has GERENTE-CLINICA role
        $user = Yii::$app->user->identity;
        $authManager = Yii::$app->authManager;
        $roles = $authManager->getRolesByUser($user->id);

        if (!isset($roles['GERENTE-CLINICA'])) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para acceder a este dashboard.');
            return $this->goHome();
        }

        // Get the clinic ID for the logged-in user
        $clinicaId = UserHelper::getMyClinicaId();

        if (!$clinicaId) {
            Yii::$app->session->setFlash('error', 'No tiene una clínica asociada.');
            return $this->goHome();
        }

        // Get clinic name
        $clinica = RmClinica::findOne($clinicaId);

        // Get active tab from request (default to 'general')
        $activeTab = Yii::$app->request->get('tab', 'general');

        return $this->render('dashboard', [
            'clinicaId' => $clinicaId,
            'clinicaNombre' => $clinica ? $clinica->nombre : 'Su clínica',
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * AJAX endpoint to load general dashboard data
     */
    public function actionGetDashboardData()
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

        // Contract status distribution
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

        $contratosSuspendidos = \app\models\Contratos::find()
            ->alias('c')
            ->innerJoin('user_datos ud', 'ud.id = c.user_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['c.estatus' => 'suspendido']) // Assuming 'Suspendido' is the status value
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
            ->andWhere(['not', ['c.estatus' => 'Anulado']])
            ->count();

        $contractStatus = [
            'activos' => (int)$contratosActivos,
            'creados' => (int)$contratosCreados,
            'suspendidos' => (int)$contratosSuspendidos, // NEW
            'anulados' => (int)$contratosAnulados,
            'vencidos' => (int)$contratosVencidos,
        ];

        // Get plan distribution - FIXED VERSION
        $planesPopulares = (clone $query)
            ->select([
                'user_datos.plan_id',
                'COUNT(*) as count',
                'planes.nombre as plan_nombre'
            ])
            ->innerJoin('planes', 'user_datos.plan_id = planes.id')
            ->where(['not', ['user_datos.plan_id' => null]])
            ->andWhere(['planes.clinica_id' => $clinicaId])
            ->groupBy(['user_datos.plan_id', 'planes.nombre'])
            ->orderBy(['count' => SORT_DESC])
            ->limit(5)
            ->asArray()
            ->all();

        $planData = [];
        foreach ($planesPopulares as $item) {
            $planData[] = [
                'name' => $item['plan_nombre'] ?? 'Unknown',
                'count' => (int)$item['count']
            ];
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
            'contract_status' => $contractStatus,
            'monthly_growth' => $monthlyData,
            'plan_distribution' => $planData
        ];
    }

    /**
     * AJAX endpoint to load atenciones KPI data
     * @return array
     */
    public function actionGetAtencionesData()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Aumentar límites para producción
        set_time_limit(120);
        ini_set('memory_limit', '256M');

        try {
            $user = Yii::$app->user->identity;
            if (!$user) {
                return ['success' => false, 'message' => 'Usuario no autenticado'];
            }

            // Get the clinica_id from the logged-in user's related data
            $userDatos = \app\models\UserDatos::find()
                ->where(['user_login_id' => $user->id])
                ->one();

            if (!$userDatos || !$userDatos->clinica_id) {
                Yii::error("No clinic associated with user ID: " . $user->id, 'atenciones');
                return ['success' => false, 'message' => 'No clinic associated with this user'];
            }

            $clinicaId = $userDatos->clinica_id;

            // Get date range from request
            $dateFrom = Yii::$app->request->get('date_from', date('Y-m-01'));
            $dateTo = Yii::$app->request->get('date_to', date('Y-m-t'));

            // Validar fechas
            if (!strtotime($dateFrom) || !strtotime($dateTo)) {
                return ['success' => false, 'message' => 'Fechas inválidas'];
            }

            Yii::info("Generando atenciones KPI - Clínica: $clinicaId, Desde: $dateFrom, Hasta: $dateTo", 'atenciones');

            // Base query for this clinic
            $query = SisSiniestro::find()
                ->where(['idclinica' => $clinicaId])
                ->andWhere(['>=', 'fecha', $dateFrom])
                ->andWhere(['<=', 'fecha', $dateTo]);

            // ===== ESTADÍSTICAS GENERALES =====

            // Total atenciones
            $totalAtenciones = (clone $query)->count();

            // Siniestros vs Citas (campos boolean)
            $siniestros = (clone $query)->andWhere(['es_cita' => false])->count();
            $citas = (clone $query)->andWhere(['es_cita' => true])->count();

            // Atenciones por estatus (campos boolean)
            $atendidas = (clone $query)->andWhere(['atendido' => true])->count();
            $pendientes = (clone $query)->andWhere(['atendido' => false])->orWhere(['atendido' => null])->count();

            // Tasa de atención
            $tasaAtencion = $totalAtenciones > 0 ? round(($atendidas / $totalAtenciones) * 100, 1) : 0;

            // Costos
            $costoTotal = (clone $query)->sum('costo_total') ?: 0;
            $costoPromedio = $totalAtenciones > 0 ? round($costoTotal / $totalAtenciones, 2) : 0;

            // Pacientes únicos
            $pacientesUnicos = (clone $query)
                ->select('iduser')
                ->distinct()
                ->count();

            // Promedio de atenciones por paciente
            $promedioPorPaciente = $pacientesUnicos > 0 ? round($totalAtenciones / $pacientesUnicos, 1) : 0;

            // ===== DATOS DIARIOS PARA GRÁFICOS =====
            // Usando boolean directamente
            $dailyData = (new \yii\db\Query())
                ->select([
                    'fecha',
                    'COUNT(*) as total',
                    'SUM(CASE WHEN es_cita = TRUE THEN 1 ELSE 0 END) as citas',
                    'SUM(CASE WHEN es_cita = FALSE THEN 1 ELSE 0 END) as siniestros',
                    'SUM(CASE WHEN atendido = TRUE THEN 1 ELSE 0 END) as atendidas',
                    'SUM(costo_total) as costo'
                ])
                ->from('sis_siniestro')
                ->where(['idclinica' => $clinicaId])
                ->andWhere(['>=', 'fecha', $dateFrom])
                ->andWhere(['<=', 'fecha', $dateTo])
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->all();

            // ===== DATOS POR DÍA DE LA SEMANA =====
            $dayOfWeekData = [];
            try {
                $dayOfWeekData = (new \yii\db\Query())
                    ->select([
                        'EXTRACT(DOW FROM fecha) as day_of_week',
                        'COUNT(*) as total',
                        'AVG(costo_total) as avg_cost'
                    ])
                    ->from('sis_siniestro')
                    ->where(['idclinica' => $clinicaId])
                    ->andWhere(['>=', 'fecha', $dateFrom])
                    ->andWhere(['<=', 'fecha', $dateTo])
                    ->groupBy(['EXTRACT(DOW FROM fecha)'])
                    ->orderBy('day_of_week')
                    ->all();
            } catch (\Exception $e) {
                Yii::error("Error en EXTRACT(DOW): " . $e->getMessage(), 'atenciones');
                $dayOfWeekData = [];
            }

            // Mapear días de la semana
            $daysMap = [
                0 => 'Domingo',
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado'
            ];

            $formattedDayData = [];
            foreach ($dayOfWeekData as $item) {
                $dayNum = is_numeric($item['day_of_week']) ? (int)$item['day_of_week'] : 0;
                $formattedDayData[] = [
                    'day' => $daysMap[$dayNum] ?? 'Desconocido',
                    'total' => (int)$item['total'],
                    'avg_cost' => round((float)$item['avg_cost'], 2)
                ];
            }

            // ===== TOP BAREMOS =====
            $topBaremos = [];
            try {
                // Verificar si hay datos
                $hasData = (new \yii\db\Query())
                    ->from('sis_siniestro')
                    ->where(['idclinica' => $clinicaId])
                    ->andWhere(['>=', 'fecha', $dateFrom])
                    ->andWhere(['<=', 'fecha', $dateTo])
                    ->exists();

                if ($hasData) {
                    // Consulta optimizada para top baremos
                    $sql = "
                    SELECT 
                        b.nombre_servicio as baremo_nombre,
                        COUNT(*) as uso_count,
                        COALESCE(SUM(sb.costo), 0) as costo_total,
                        COALESCE(AVG(sb.costo), 0) as costo_promedio
                    FROM sis_siniestro s
                    INNER JOIN sis_siniestro_baremo sb ON s.id = sb.siniestro_id
                    INNER JOIN baremo b ON sb.baremo_id = b.id
                    WHERE s.idclinica = :clinica_id
                    AND s.fecha >= :date_from
                    AND s.fecha <= :date_to
                    GROUP BY b.id, b.nombre_servicio
                    ORDER BY uso_count DESC
                    LIMIT 10
                ";

                    $topBaremos = Yii::$app->db->createCommand($sql, [
                        ':clinica_id' => $clinicaId,
                        ':date_from' => $dateFrom,
                        ':date_to' => $dateTo,
                    ])->queryAll();

                    Yii::info("Top baremos encontrados: " . count($topBaremos), 'atenciones');
                }
            } catch (\Exception $e) {
                Yii::error("Error en top baremos: " . $e->getMessage(), 'atenciones');
                $topBaremos = [];
            }

            // ===== RESPUESTA =====
            return [
                'success' => true,
                'total_atenciones' => (int)$totalAtenciones,
                'siniestros' => (int)$siniestros,
                'citas' => (int)$citas,
                'atendidas' => (int)$atendidas,
                'pendientes' => (int)$pendientes,
                'tasa_atencion' => (float)$tasaAtencion,
                'costo_total' => (float)$costoTotal,
                'costo_promedio' => (float)$costoPromedio,
                'pacientes_unicos' => (int)$pacientesUnicos,
                'promedio_por_paciente' => (float)$promedioPorPaciente,
                'daily_data' => $dailyData,
                'day_of_week_data' => $formattedDayData,
                'top_baremos' => $topBaremos,
                'date_range' => [
                    'from' => $dateFrom,
                    'to' => $dateTo
                ]
            ];
        } catch (\Exception $e) {
            Yii::error("EXCEPCIÓN GENERAL en actionGetAtencionesData: " . $e->getMessage(), 'atenciones');
            Yii::error("Stack trace: " . $e->getTraceAsString(), 'atenciones');

            return [
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ];
        }
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
                'text' => $plan->nombre . ' ($' . number_format($plan->precio, 2) . ')',
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

        // ===== CONTRACT STATUS DISTRIBUTION =====
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
