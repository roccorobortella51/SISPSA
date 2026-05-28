<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\AgenteFuerza;
use app\models\ContactForm;
use app\models\RmMunicipio;
use app\models\RmParroquia;
use app\models\RmCiudad;
use app\models\Planes;
use app\models\Contratos;
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

            // If user has COORDINADOR-CLINICA role, redirect to coordinador dashboard
            if (isset($roles['COORDINADOR-CLINICA'])) {
                return $this->redirect(['site/dashboard-coordinador']);
            }

            // If user has ASESOR role, redirect to asesor dashboard
            if (isset($roles['Asesor'])) {
                return $this->redirect(['site/dashboard-asesor']);
            }

            // ADD THIS: If user has FINANZAS role, redirect to finanzas dashboard
            if (isset($roles['FINANZAS'])) {
                return $this->redirect(['site/dashboard-finanzas']);
            }
        }

        // Otherwise show the regular welcome page for guests
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

            if (isset($roles['COORDINADOR-CLINICA'])) {
                return $this->redirect(['site/dashboard-coordinador']);
            }

            if (isset($roles['Asesor'])) {
                return $this->redirect(['site/dashboard-asesor']);
            }

            // ADD THIS: Redirect FINANZAS role to finanzas dashboard
            if (isset($roles['FINANZAS'])) {
                return $this->redirect(['site/dashboard-finanzas']);
            }

            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Dashboard for FINANZAS role
     * Shows financial statistics, payments, invoices, and reports
     * @return string
     */
    public function actionDashboardFinanzas()
    {
        // Check if user has FINANZAS role
        $user = Yii::$app->user->identity;

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $authManager = Yii::$app->authManager;
        $roles = $authManager->getRolesByUser($user->id);

        if (!isset($roles['FINANZAS'])) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para acceder a este dashboard.');
            return $this->goHome();
        }

        // Get the clinic ID for the logged-in user (if applicable)
        $userDatos = UserDatos::findOne(['user_login_id' => $user->id]);
        $clinicaId = $userDatos ? $userDatos->clinica_id : null;
        $clinica = $clinicaId ? RmClinica::findOne($clinicaId) : null;

        // ========== FINANCIAL KPIs ==========

        // Total revenue from all contracts (active contracts)
        $totalRevenue = Contratos::find()
            ->where(['estatus' => 'Activo'])
            ->sum('monto') ?? 0;

        // Monthly recurring revenue - sum of all active contract amounts
        $mrr = Contratos::find()
            ->where(['estatus' => 'Activo'])
            ->sum('monto') ?? 0;

        // Count DISTINCT users with active contracts
        $activeAffiliateIds = Contratos::find()
            ->select(['user_id'])
            ->where(['estatus' => 'Activo'])
            ->andWhere(['is not', 'user_id', null])
            ->distinct()
            ->column();

        $totalActiveAffiliates = count($activeAffiliateIds);

        // Contracts expiring in next 30 days
        $expiringContracts = Contratos::find()
            ->where(['estatus' => 'Activo'])
            ->andWhere(['>=', 'fecha_ven', date('Y-m-d')])
            ->andWhere(['<=', 'fecha_ven', date('Y-m-d', strtotime('+30 days'))])
            ->count();

        // Expired contracts
        $expiredContracts = Contratos::find()
            ->where(['estatus' => 'Activo'])
            ->andWhere(['<', 'fecha_ven', date('Y-m-d')])
            ->count();

        // Calculate average contract value
        $avgContractValue = Contratos::find()
            ->where(['estatus' => 'Activo'])
            ->average('monto') ?? 0;

        // ========== FIXED: Get revenue by plan with specific colors for each plan type ==========
        // First, get all active contracts with their plan information
        $planRevenueData = Contratos::find()
            ->select(['planes.nombre as plan_name', 'SUM(contratos.monto) as total_revenue', 'COUNT(*) as contract_count'])
            ->innerJoin('planes', 'contratos.plan_id = planes.id')
            ->where(['contratos.estatus' => 'Activo'])
            ->groupBy('planes.id', 'planes.nombre')
            ->orderBy(['total_revenue' => SORT_DESC])
            ->asArray()
            ->all();

        // Define plan categories and their display names
        $planCategories = [
            'Bronce' => ['display' => 'Bronce', 'color' => '#cd7f32', 'order' => 1],      // Bronze color
            'Plata' => ['display' => 'Plata', 'color' => '#c0c0c0', 'order' => 2],        // Silver color
            'Oro' => ['display' => 'Oro', 'color' => '#ffc107', 'order' => 3],            // Yellow/Gold color
            'Esmeralda Plus' => ['display' => 'Esmeralda Plus', 'color' => '#2ecc71', 'order' => 4], // Emerald green
        ];

        // Group and aggregate revenue by plan category
        $revenueByPlan = [];
        foreach ($planRevenueData as $item) {
            $planName = $item['plan_name'];
            $found = false;

            // Check if this plan belongs to one of our categories
            foreach ($planCategories as $key => $category) {
                if (stripos($planName, $key) !== false) {
                    if (!isset($revenueByPlan[$key])) {
                        $revenueByPlan[$key] = [
                            'plan_name' => $category['display'],
                            'total_revenue' => 0,
                            'contract_count' => 0,
                            'color' => $category['color'],
                            'order' => $category['order']
                        ];
                    }
                    $revenueByPlan[$key]['total_revenue'] += $item['total_revenue'];
                    $revenueByPlan[$key]['contract_count'] += $item['contract_count'];
                    $found = true;
                    break;
                }
            }

            // If plan doesn't match categories, group as "Otros"
            if (!$found) {
                if (!isset($revenueByPlan['Otros'])) {
                    $revenueByPlan['Otros'] = [
                        'plan_name' => 'Otros Planes',
                        'total_revenue' => 0,
                        'contract_count' => 0,
                        'color' => '#95a5a6',
                        'order' => 99
                    ];
                }
                $revenueByPlan['Otros']['total_revenue'] += $item['total_revenue'];
                $revenueByPlan['Otros']['contract_count'] += $item['contract_count'];
            }
        }

        // Sort by order
        usort($revenueByPlan, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        // Convert to indexed array for view
        $revenueByPlan = array_values($revenueByPlan);

        // Monthly revenue data for chart (last 12 months)
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-$i months"));
            $monthEnd = date('Y-m-t', strtotime("-$i months"));
            $monthName = date('M Y', strtotime($monthStart));

            $revenue = Contratos::find()
                ->where(['estatus' => 'Activo'])
                ->andWhere(['>=', 'fecha_ini', $monthStart])
                ->andWhere(['<=', 'fecha_ini', $monthEnd])
                ->sum('monto') ?? 0;

            $monthlyRevenue[] = [
                'month' => $monthName,
                'revenue' => (float)$revenue
            ];
        }

        // Contract status distribution
        $contractStatus = [
            'activos' => (int)Contratos::find()->where(['estatus' => 'Activo'])->count(),
            'registrados' => (int)Contratos::find()->where(['estatus' => 'Registrado'])->count(),
            'suspendidos' => (int)Contratos::find()->where(['estatus' => 'Suspendido'])->count(),
            'creados' => (int)Contratos::find()->where(['estatus' => 'Creado Manual'])->count(),
            'vencidos' => (int)Contratos::find()->where(['estatus' => 'Vencido'])->count(),
            'anulados' => (int)Contratos::find()->where(['estatus' => 'Anulado'])->count(),
        ];

        // Get recent contracts (last 10)
        $recentContracts = Contratos::find()
            ->with(['plan', 'user'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // Ensure $recentContracts is an array
        if (empty($recentContracts)) {
            $recentContracts = [];
        }

        // Attention needed = expiring contracts + expired contracts
        $attentionNeeded = $expiringContracts + $expiredContracts;

        // Total active contracts
        $totalActiveContracts = (int)Contratos::find()->where(['estatus' => 'Activo'])->count();

        $kpis = [
            'total_revenue' => $totalRevenue,
            'mrr' => $mrr,
            'total_active_affiliates' => $totalActiveAffiliates,
            'expiring_contracts' => $expiringContracts,
            'expired_contracts' => $expiredContracts,
            'avg_contract_value' => $avgContractValue,
            'attention_needed' => $attentionNeeded,
            'total_contracts' => $totalActiveContracts,
        ];

        return $this->render('dashboard-finanzas', [
            'clinica' => $clinica,
            'kpis' => $kpis,
            'revenueByPlan' => $revenueByPlan,
            'monthlyRevenue' => $monthlyRevenue,
            'contractStatus' => $contractStatus,
            'recentContracts' => $recentContracts,
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
            ->andWhere(['c.estatus' => 'Suspendido'])
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
            'suspendidos' => (int)$contratosSuspendidos,
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
            'contract_status' => $contractStatus,
            'monthly_growth' => $monthlyData,
            'plan_distribution' => $planData
        ];
    }

    /**
     * Dashboard for COORDINADOR-CLINICA role
     * @return string
     */
    public function actionDashboardCoordinador()
    {
        // Check if user has COORDINADOR-CLINICA role
        $user = Yii::$app->user->identity;

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $authManager = Yii::$app->authManager;
        $roles = $authManager->getRolesByUser($user->id);

        if (!isset($roles['COORDINADOR-CLINICA'])) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para acceder a este dashboard.');
            return $this->goHome();
        }

        // Get the clinic ID for the logged-in user
        $userDatos = UserDatos::findOne(['user_login_id' => $user->id]);

        if (!$userDatos || !$userDatos->clinica_id) {
            Yii::$app->session->setFlash('error', 'No tiene una clínica asociada.');
            return $this->goHome();
        }

        $clinicaId = $userDatos->clinica_id;
        $clinica = RmClinica::findOne($clinicaId);

        // Fetch KPIs
        $totalAfiliados = UserDatos::find()
            ->where(['clinica_id' => $clinicaId])
            ->andWhere(['role' => 'afiliado'])
            ->count();

        // Get contract status counts from the contratos table
        $contratosActivos = Contratos::find()
            ->innerJoin('user_datos ud', 'ud.id = contratos.user_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['ud.role' => 'afiliado'])
            ->andWhere(['contratos.estatus' => 'Activo'])
            ->count();

        // FIXED: Use 'Suspendido' with capital S
        $contratosSuspendidos = Contratos::find()
            ->innerJoin('user_datos ud', 'ud.id = contratos.user_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['ud.role' => 'afiliado'])
            ->andWhere(['contratos.estatus' => 'Suspendido'])  // ← FIXED HERE
            ->count();

        // Get affiliate type counts
        $individuales = UserDatos::find()
            ->where(['clinica_id' => $clinicaId])
            ->andWhere(['role' => 'afiliado'])
            ->andWhere(['user_datos_type_id' => 1])
            ->count();

        $corporativos = UserDatos::find()
            ->where(['clinica_id' => $clinicaId])
            ->andWhere(['role' => 'afiliado'])
            ->andWhere(['user_datos_type_id' => 2])
            ->count();

        // Get contract expiration counts
        $expiringSoon = Contratos::find()
            ->innerJoin('user_datos ud', 'ud.id = contratos.user_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['ud.role' => 'afiliado'])
            ->andWhere(['>=', 'contratos.fecha_ven', date('Y-m-d')])
            ->andWhere(['<=', 'contratos.fecha_ven', date('Y-m-d', strtotime('+30 days'))])
            ->count();

        $expired = Contratos::find()
            ->innerJoin('user_datos ud', 'ud.id = contratos.user_id')
            ->where(['ud.clinica_id' => $clinicaId])
            ->andWhere(['ud.role' => 'afiliado'])
            ->andWhere(['<', 'contratos.fecha_ven', date('Y-m-d')])
            ->count();

        // Get new affiliates this month
        $newThisMonth = UserDatos::find()
            ->where(['clinica_id' => $clinicaId])
            ->andWhere(['role' => 'afiliado'])
            ->andWhere(['>=', 'created_at', date('Y-m-01 00:00:00')])
            ->count();

        $kpis = [
            'total' => $totalAfiliados,
            'contratosActivos' => $contratosActivos,
            'contratosSuspendidos' => $contratosSuspendidos,
            'individuales' => $individuales,
            'corporativos' => $corporativos,
            'expiringSoon' => $expiringSoon,
            'expired' => $expired,
            'newThisMonth' => $newThisMonth,
        ];

        return $this->render('dashboard-coordinador', [
            'clinicaId' => $clinicaId,
            'clinicaNombre' => $clinica ? $clinica->nombre : 'Su clínica',
            'kpis' => $kpis,
        ]);
    }

    /**
     * Dashboard for ASESOR role
     * Shows affiliate statistics and plan information
     * 
     * @return string
     */
    public function actionDashboardAsesor()
    {
        // Check if user has ASESOR role
        $user = Yii::$app->user->identity;

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $authManager = Yii::$app->authManager;
        $roles = $authManager->getRolesByUser($user->id);

        if (!isset($roles['Asesor'])) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para acceder a este dashboard.');
            return $this->goHome();
        }

        // Get the Asesor's own UserDatos record
        $asesorUser = UserDatos::findOne(['user_login_id' => $user->id]);

        if (!$asesorUser) {
            Yii::$app->session->setFlash('error', 'No se encontró su perfil de asesor.');
            return $this->goHome();
        }

        // Get the agente_fuerza record to find the asesor_id
        $agenteFuerza = AgenteFuerza::findOne(['idusuario' => $asesorUser->id]);

        if (!$agenteFuerza) {
            Yii::$app->session->setFlash('error', 'No se encontró su registro como asesor en el sistema.');
            return $this->goHome();
        }

        $asesorId = $agenteFuerza->id;
        $asesorName = $asesorUser->nombres . ' ' . $asesorUser->apellidos;

        // ============================================
        // Get ALL affiliates for this asesor (regardless of contract status)
        // ============================================
        $allAffiliates = UserDatos::find()
            ->where(['asesor_id' => $asesorId, 'role' => 'afiliado'])
            ->all();

        $totalAfiliados = count($allAffiliates);

        // ============================================
        // Get contract statuses for debugging and counting
        // ============================================
        $activos = 0;
        $registrados = 0;
        $suspendidos = 0;
        $vencidos = 0;
        $anulados = 0;
        $sinContrato = 0;
        $contractStatusDebug = [];

        foreach ($allAffiliates as $affiliate) {
            $contract = Contratos::find()
                ->where(['user_id' => $affiliate->id])
                ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();

            if ($contract) {
                $status = $contract->estatus;
                $contractStatusDebug[] = [
                    'affiliate_id' => $affiliate->id,
                    'affiliate_name' => $affiliate->nombres . ' ' . $affiliate->apellidos,
                    'contract_id' => $contract->id,
                    'contract_status' => $status,
                    'fecha_ini' => $contract->fecha_ini,
                    'fecha_ven' => $contract->fecha_ven,
                ];

                switch ($status) {
                    case 'Activo':
                        $activos++;
                        break;
                    case 'Registrado':
                        $registrados++;
                        break;
                    case 'Suspendido':
                        $suspendidos++;
                        break;
                    case 'Vencido':
                        $vencidos++;
                        break;
                    case 'Anulado':
                        $anulados++;
                        break;
                    default:
                        // Other status
                        break;
                }
            } else {
                $sinContrato++;
                $contractStatusDebug[] = [
                    'affiliate_id' => $affiliate->id,
                    'affiliate_name' => $affiliate->nombres . ' ' . $affiliate->apellidos,
                    'contract_status' => 'SIN CONTRATO',
                ];
            }
        }

        // ============================================
        // Get clinics where this Asesor has affiliates
        // ============================================
        $clinicasConAfiliados = UserDatos::find()
            ->select(['clinica_id', 'rm_clinica.nombre as clinica_nombre'])
            ->innerJoin('rm_clinica', 'user_datos.clinica_id = rm_clinica.id')
            ->where(['user_datos.asesor_id' => $asesorId])
            ->andWhere(['user_datos.role' => 'afiliado'])
            ->andWhere(['IS NOT', 'user_datos.clinica_id', null])
            ->groupBy(['clinica_id', 'rm_clinica.nombre'])
            ->orderBy(['rm_clinica.nombre' => SORT_ASC])
            ->asArray()
            ->all();

        $clinicaIds = array_column($clinicasConAfiliados, 'clinica_id');
        $clinicas = RmClinica::find()
            ->where(['id' => $clinicaIds])
            ->andWhere(['IS', 'deleted_at', null])
            ->orderBy(['nombre' => SORT_ASC])
            ->all();

        $clinicaNombre = !empty($clinicas) ? (count($clinicas) == 1 ? $clinicas[0]->nombre : 'Múltiples Clínicas') : 'Sin clínicas asignadas';

        // ============================================
        // STATISTICS BY CLINIC (for chart)
        // ============================================
        $statsByClinic = [];
        foreach ($clinicas as $clinica) {
            // Get affiliates for this clinic
            $clinicAffiliates = UserDatos::find()
                ->where(['asesor_id' => $asesorId, 'clinica_id' => $clinica->id, 'role' => 'afiliado'])
                ->all();

            $total = count($clinicAffiliates);
            $activosCount = 0;
            $suspendidosCount = 0;

            foreach ($clinicAffiliates as $affiliate) {
                $contract = Contratos::find()
                    ->where(['user_id' => $affiliate->id])
                    ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->one();

                if ($contract) {
                    if ($contract->estatus == 'Activo') {
                        $activosCount++;
                    } elseif ($contract->estatus == 'Suspendido') {
                        $suspendidosCount++;
                    }
                }
            }

            $statsByClinic[] = [
                'clinica_nombre' => $clinica->nombre,
                'total' => $total,
                'activos' => $activosCount,
                'suspendidos' => $suspendidosCount,
            ];
        }

        // ============================================
        // Build statistics arrays
        // ============================================
        $clinicaStats = [
            'total_afiliados' => $totalAfiliados,
            'activos' => $activos,
            'registrados' => $registrados,
            'suspendidos' => $suspendidos,
            'vencidos' => $vencidos,
            'anulados' => $anulados,
            'sin_contrato' => $sinContrato,
            'nuevos_este_mes' => UserDatos::find()
                ->where(['asesor_id' => $asesorId, 'role' => 'afiliado'])
                ->andWhere(['>=', 'created_at', date('Y-m-01 00:00:00')])
                ->count(),
            'tasa_actividad' => $totalAfiliados > 0 ? round(($activos / $totalAfiliados) * 100, 1) : 0,
        ];

        $asesorStats = [
            'total_afiliados' => $totalAfiliados,
            'activos' => $activos,
            'registrados' => $registrados,
            'suspendidos' => $suspendidos,
            'vencidos' => $vencidos,
            'anulados' => $anulados,
            'sin_contrato' => $sinContrato,
            'nuevos_este_mes' => $clinicaStats['nuevos_este_mes'],
            'tasa_actividad' => $totalAfiliados > 0 ? round(($activos / $totalAfiliados) * 100, 1) : 0,
        ];

        // Monthly performance data for chart
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $startDate = date('Y-m-01', strtotime("-$i months"));
            $endDate = date('Y-m-t', strtotime("-$i months"));
            $monthName = date('M Y', strtotime($startDate));

            $count = UserDatos::find()
                ->where(['asesor_id' => $asesorId, 'role' => 'afiliado'])
                ->andWhere(['>=', 'created_at', $startDate . ' 00:00:00'])
                ->andWhere(['<=', 'created_at', $endDate . ' 23:59:59'])
                ->count();

            $monthlyData[] = ['month' => $monthName, 'count' => $count];
        }

        // Plan data
        $planesData = [
            'individual' => [
                ['name' => 'Plan Bronce', 'price' => 16, 'currency' => 'USD', 'coverage' => 10000, 'age_range' => '0 a 59 años', 'maternity' => '+$11 (Opcional)'],
                ['name' => 'Plan Plata', 'price' => 20, 'currency' => 'USD', 'coverage' => 15000, 'age_range' => '0 a 59 años', 'maternity' => '+$11 (Opcional)'],
                ['name' => 'Plan Oro', 'price' => 24, 'currency' => 'USD', 'coverage' => 20000, 'age_range' => '0 a 59 años', 'maternity' => '+$11 (Opcional)'],
            ],
            'senior' => [
                ['name' => 'Plan Esmeralda Básico', 'price' => 13, 'currency' => 'USD', 'coverage' => 14000, 'age_range' => '60 a 80 años', 'maternity' => 'No aplica'],
                ['name' => 'Plan Esmeralda Plus', 'price' => 29, 'currency' => 'USD', 'coverage' => 16000, 'age_range' => '60 a 80 años', 'maternity' => 'No aplica'],
                ['name' => 'Plan Diamante', 'price' => 34, 'currency' => 'USD', 'coverage' => 50000, 'age_range' => '60 a 80 años', 'maternity' => 'No aplica'],
            ],
            'benefits_summary' => [
                'emergency' => 'Sin plazos de espera. Atención primaria, exámenes diagnósticos (Hematología, Glicemia, Rayos X), medicación analgésica, sala de cura menor, hospitalización por 48 hrs.',
                'consultas_basicas' => 'Sin plazos de espera. Medicina General y Pediatría.',
                'consultas_especializadas' => 'Con plazos de espera de 2 a 4 meses. Especialidades como Cardiología, Ginecología, Traumatología, etc.',
                'examenes' => 'Laboratorio, Rayos X, Ecogramas. Con plazos de espera variables.',
                'cirugias_electivas' => 'A partir de 12 meses de espera. Cubre procedimientos como hernia umbilical, vesícula, etc. (En planes Premium).',
                'maternidad' => 'Opcional en planes individuales. 12 meses de espera. Cubre consultas, ecografías, parto/cesárea y atención del recién nacido.',
            ]
        ];

        // Get recent affiliates (last 10) with their contract status
        $recientes = UserDatos::find()
            ->select([
                'user_datos.id',
                'user_datos.nombres',
                'user_datos.apellidos',
                'user_datos.tipo_cedula',
                'user_datos.cedula',
                'user_datos.email',
                'user_datos.telefono',
                'user_datos.created_at',
                'user_datos.clinica_id',
                'rm_clinica.nombre as clinica_nombre',
                'user_datos.asesor_id'
            ])
            ->leftJoin('rm_clinica', 'user_datos.clinica_id = rm_clinica.id')
            ->where(['user_datos.asesor_id' => $asesorId, 'user_datos.role' => 'afiliado'])
            ->orderBy(['user_datos.created_at' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();

        // Add contract status to each recent affiliate
        foreach ($recientes as &$afiliado) {
            $contract = Contratos::find()
                ->where(['user_id' => $afiliado['id']])
                ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();

            $afiliado['contrato_estatus'] = $contract ? $contract->estatus : 'Sin Contrato';
        }

        // Debug info
        $debugInfo = [
            'asesor_id_used' => $asesorId,
            'user_login_id' => $user->id,
            'user_datos_id' => $asesorUser->id,
            'total_affiliates' => $totalAfiliados,
            'activos_count' => $activos,
            'sin_contrato_count' => $sinContrato,
            'contract_status_debug' => $contractStatusDebug,
            'clinicas_count' => count($clinicas),
            'recientes_count' => count($recientes),
        ];

        return $this->render('dashboard-asesor', [
            'asesorName' => $asesorName,
            'clinicaNombre' => $clinicaNombre,
            'clinicas' => $clinicas,
            'statsByClinic' => $statsByClinic,
            'clinicaStats' => $clinicaStats,
            'asesorStats' => $asesorStats,
            'monthlyData' => $monthlyData,
            'planesData' => $planesData,
            'recientes' => $recientes,
            'debugInfo' => $debugInfo,
        ]);
    }

    /**
     * Get municipios by estado id - GET method (for profile update)
     */
    public function actionGetMunicipios($estado_id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = [];
        if ($estado_id) {
            $out = RmMunicipio::find()
                ->select(['codigo_muni as id', 'nombre as name'])
                ->where(['estado_codigo' => $estado_id])
                ->orderBy('nombre')
                ->asArray()
                ->all();
        }
        return ['output' => $out, 'selected' => ''];
    }

    /**
     * Get parroquias by municipio id - GET method (for profile update)
     */
    public function actionGetParroquias($municipio_id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = [];
        if ($municipio_id) {
            $out = RmParroquia::find()
                ->select(['id', 'nombre as name'])
                ->where(['muni_codigo' => $municipio_id])
                ->orderBy('nombre')
                ->asArray()
                ->all();
        }
        return ['output' => $out, 'selected' => ''];
    }

    /**
     * Get ciudades by estado id - GET method (for profile update)
     */
    public function actionGetCiudades($estado_id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = [];
        if ($estado_id) {
            $out = RmCiudad::find()
                ->select(['codigo_ciudad as id', 'nombre as name'])
                ->where(['estado_codigo' => $estado_id])
                ->orderBy('nombre')
                ->asArray()
                ->all();
        }
        return ['output' => $out, 'selected' => ''];
    }
    /**
     * Test email configuration
     * Access: https://sispsatest.com/index.php?r=site/test-email
     * or http://localhost/sipsa/web/index.php?r=site/test-email
     */
    public function actionTestEmail($email = null)
    {
        // Only allow in production for users with admin role
        if (YII_ENV_PROD && !Yii::$app->user->isGuest) {
            $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
            if (!isset($roles['Administrador']) && !isset($roles['admin']) && !isset($roles['ADMIN'])) {
                return 'Email test is disabled in production. Contact administrator.';
            }
        } elseif (YII_ENV_PROD) {
            return 'Email test is disabled in production. Contact administrator.';
        }

        // Use provided email or default test email
        $testEmail = $email ?: 'your-email@gmail.com'; // Change this to your email

        // Get user info if logged in
        $userEmail = !Yii::$app->user->isGuest ? Yii::$app->user->identity->email : null;

        $result = Yii::$app->mailer->compose()
            ->setFrom('sispsa.notificaciones@gmail.com')
            ->setTo($testEmail)
            ->setSubject('SISPSA Test Email - ' . date('Y-m-d H:i:s'))
            ->setHtmlBody('
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background: #f4f7fc; padding: 20px; }
                    .container { max-width: 500px; margin: 0 auto; background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    .header { background: #1a3a6e; color: white; padding: 15px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; text-align: center; }
                    .success { color: #28a745; font-weight: bold; }
                    .info { background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 15px 0; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>SISPSA - Prueba de Correo</h2>
                    </div>
                    <p>✅ <strong class="success">¡Configuración de correo funcionando correctamente!</strong></p>
                    <div class="info">
                        <p><strong>Detalles de la prueba:</strong></p>
                        <p>📅 Fecha y hora: ' . date('d/m/Y H:i:s') . '</p>
                        <p>🌐 Servidor: ' . $_SERVER['SERVER_NAME'] . '</p>
                        <p>📧 Email de destino: ' . $testEmail . '</p>
                        ' . ($userEmail ? '<p>👤 Usuario autenticado: ' . $userEmail . '</p>' : '') . '
                    </div>
                    <p>Este es un mensaje de prueba para verificar que el sistema de notificaciones por correo electrónico está funcionando correctamente.</p>
                    <hr>
                    <p style="font-size: 12px; color: #666;">SISPSA - Sistema Integral de Salud Programado</p>
                </div>
            </body>
            </html>
        ')
            ->setTextBody("SISPSA Test Email\n\n" .
                "✅ Configuración de correo funcionando correctamente!\n\n" .
                "Fecha y hora: " . date('d/m/Y H:i:s') . "\n" .
                "Servidor: " . $_SERVER['SERVER_NAME'] . "\n" .
                "Email de destino: " . $testEmail . "\n\n" .
                "Este es un mensaje de prueba para verificar que el sistema de notificaciones por correo electrónico está funcionando correctamente.\n\n" .
                "SISPSA - Sistema Integral de Salud Programado")
            ->send();

        if ($result) {
            Yii::$app->session->setFlash('success', "✓ Email sent successfully to {$testEmail}! Check your inbox (and spam folder).");
        } else {
            Yii::$app->session->setFlash('error', "✗ Failed to send email. Check logs at runtime/logs/app.log");
        }

        // Redirect back to dashboard or show result page
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(Yii::$app->request->referrer ?: ['site/dashboard-finanzas']);
        } else {
            return $this->renderContent("
            <html>
            <head><title>Email Test Result</title></head>
            <body style='font-family: Arial, sans-serif; padding: 50px; text-align: center;'>
                <div style='max-width: 500px; margin: 0 auto; background: " . ($result ? "#d4edda" : "#f8d7da") . "; padding: 20px; border-radius: 8px;'>
                    <h2>" . ($result ? "✅ Email Sent!" : "❌ Email Failed") . "</h2>
                    <p>" . ($result ? "Test email sent to {$testEmail}" : "Failed to send email to {$testEmail}") . "</p>
                    <p><a href='/'>Return to Home</a></p>
                </div>
            </body>
            </html>
        ");
        }
    }

    /**
     * Test notification helper with a real receipt
     * Access: https://sispsatest.com/index.php?r=site/test-notification&receipt_id=1
     */
    public function actionTestNotification($receipt_id = null)
    {
        // Only allow in development or for logged-in users
        if (YII_ENV_PROD && Yii::$app->user->isGuest) {
            return 'Test notification is restricted. Please log in.';
        }

        if (!$receipt_id) {
            // Get the most recent receipt
            $receipt = \app\models\Receipt::find()->orderBy(['id' => SORT_DESC])->one();
            if ($receipt) {
                $receipt_id = $receipt->id;
            } else {
                return 'No receipt found. Please provide a receipt_id parameter. Example: ?r=site/test-notification&receipt_id=1';
            }
        }

        $receipt = \app\models\Receipt::findOne($receipt_id);
        if (!$receipt) {
            return "Receipt not found with ID: {$receipt_id}";
        }

        $user = \app\models\UserDatos::findOne($receipt->user_id);
        $payment = \app\models\Pagos::findOne($receipt->payment_id);

        if (!$user || !$payment) {
            return "User or payment not found for receipt {$receipt_id}";
        }

        echo "<h1>Testing Notification Helper</h1>";
        echo "<p><strong>Receipt:</strong> {$receipt->receipt_number}</p>";
        echo "<p><strong>User:</strong> {$user->nombres} {$user->apellidos} ({$user->email})</p>";
        echo "<p><strong>Payment:</strong> {$payment->monto_pagado} USD / {$payment->monto_usd} Bs</p>";
        echo "<hr>";

        $result = \app\components\NotificationHelper::sendReceiptNotification($receipt, $user, $payment);

        if ($result) {
            echo "<p style='color: green; font-weight: bold;'>✓ Notification sent successfully to {$user->email}!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>✗ Failed to send notification. Check logs.</p>";
        }

        echo "<p><a href='" . Yii::$app->request->referrer . "'>Go Back</a></p>";
    }
}
