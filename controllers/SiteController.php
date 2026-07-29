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
use app\models\Agente;
use app\models\Cuotas;
use yii\db\Expression;



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

            // ADD THIS: If user has AGENTE role, redirect to agencia dashboard
            if (isset($roles['Agente'])) {
                return $this->redirect(['site/dashboard-agencia']);
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

            // Redirect to agencia dashboard for Agente role - NEW
            if (isset($roles['Agente'])) {
                return $this->redirect(['site/dashboard-agencia']);
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
     * Dashboard for AGENTE role
     * Shows agency statistics, asesor performance, and affiliate metrics
     * 
     * @return string
     */
    public function actionDashboardAgencia()
    {
        // Check if user has AGENTE role
        $user = Yii::$app->user->identity;

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $authManager = Yii::$app->authManager;
        $roles = $authManager->getRolesByUser($user->id);

        if (!isset($roles['Agente'])) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para acceder a este dashboard.');
            return $this->goHome();
        }

        // Get the agency ID for this agente
        $agenteId = UserHelper::getAgenteId();

        if (!$agenteId) {
            Yii::$app->session->setFlash('error', 'No se encontró la agencia asociada a su usuario.');
            return $this->goHome();
        }

        // Get agency details
        $agencia = Agente::findOne($agenteId);
        $agenciaName = $agencia ? $agencia->nom : 'Mi Agencia';

        // ============================================
        // Get all AgenteFuerza records (asesores/intermediarios) belonging to this agency
        // ============================================
        $agenteFuerzaRecords = AgenteFuerza::find()
            ->where(['agente_id' => $agenteId])
            ->all();

        $agenteFuerzaIds = array_column($agenteFuerzaRecords, 'id');
        $asesorUserIds = array_column($agenteFuerzaRecords, 'idusuario');

        // ============================================
        // Get ALL AgenteFuerza IDs for these asesores (from ANY agency)
        // This ensures we get affiliates created under ANY relationship of the asesor
        // ============================================
        $allAgenteFuerzaIds = [];
        if (!empty($asesorUserIds)) {
            $allAgenteFuerzaIds = AgenteFuerza::find()
                ->where(['idusuario' => $asesorUserIds])
                ->select('id')
                ->column();
        }

        // ============================================
        // Build the OR condition for affiliates (SAME as UserDatosSearch)
        // ============================================
        $affiliateCondition = ['or'];

        // Condition 1: Affiliates assigned to asesores of this agency (using ALL their AgenteFuerza IDs)
        if (!empty($allAgenteFuerzaIds)) {
            $affiliateCondition[] = ['user_datos.asesor_id' => $allAgenteFuerzaIds];
        }

        // Condition 2: Affiliates directly assigned to this agency (agencia_id)
        $affiliateCondition[] = ['user_datos.agencia_id' => $agenteId];

        // Get ALL affiliates for this agency using the OR condition
        $affiliatesQuery = UserDatos::find()
            ->where(['role' => 'afiliado'])
            ->andWhere($affiliateCondition);

        $totalAfiliados = (clone $affiliatesQuery)->count();

        // ============================================
        // Get affiliates with contract statuses (including all statuses)
        // ============================================
        $activos = 0;
        $suspendidos = 0;
        $registrados = 0;
        $vencidos = 0;
        $anulados = 0;
        $sinContrato = 0;

        foreach ($affiliatesQuery->all() as $affiliate) {
            $contract = Contratos::find()
                ->where(['user_id' => $affiliate->id])
                ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();

            if ($contract) {
                switch ($contract->estatus) {
                    case 'Activo':
                        $activos++;
                        break;
                    case 'Suspendido':
                        $suspendidos++;
                        break;
                    case 'Registrado':
                        $registrados++;
                        break;
                    case 'Vencido':
                        $vencidos++;
                        break;
                    case 'Anulado':
                        $anulados++;
                        break;
                    default:
                        break;
                }
            } else {
                $sinContrato++;
            }
        }

        // New affiliates this month
        $nuevosEsteMes = (clone $affiliatesQuery)
            ->andWhere(['>=', 'created_at', date('Y-m-01 00:00:00')])
            ->count();

        // Tasa de actividad (active contracts only)
        $tasaActividad = $totalAfiliados > 0 ? round(($activos / $totalAfiliados) * 100, 1) : 0;

        // ============================================
        // ASESORES STATISTICS
        // ============================================
        $totalAsesores = count($agenteFuerzaIds);

        // Count asesores activos (have created at least one affiliate visible to this agency)
        $asesoresActivos = 0;
        foreach ($agenteFuerzaIds as $afId) {
            $hasAffiliates = UserDatos::find()
                ->where(['role' => 'afiliado'])
                ->andWhere($affiliateCondition)
                ->andWhere(['asesor_id' => $afId])
                ->exists();
            if ($hasAffiliates) {
                $asesoresActivos++;
            }
        }

        // ============================================
        // Create a map of ALL AgenteFuerza IDs to Asesor name for quick lookup
        // This includes IDs from ALL agencies these asesores belong to
        // ============================================
        $asesorNameMap = [];

        // Get ALL AgenteFuerza records for these asesores (from ANY agency)
        $allAgenteFuerzaRecordsForAsesores = AgenteFuerza::find()
            ->where(['idusuario' => $asesorUserIds])
            ->all();

        foreach ($allAgenteFuerzaRecordsForAsesores as $afRecord) {
            $asesorUser = UserDatos::findOne($afRecord->idusuario);
            if ($asesorUser) {
                $asesorNameMap[$afRecord->id] = $asesorUser->nombres . ' ' . $asesorUser->apellidos;
            }
        }

        // ============================================
        // FIXED: STATISTICS BY ASESOR - Using ALL AgenteFuerza IDs for each asesor
        // This ensures we count ALL affiliates of the asesor person, not just those
        // created under the specific agency relationship
        // ============================================
        $statsByAsesor = [];
        $topAsesores = [];

        foreach ($agenteFuerzaIds as $afId) {
            $agenteFuerza = AgenteFuerza::findOne($afId);
            if (!$agenteFuerza) continue;

            $asesorUser = UserDatos::findOne($agenteFuerza->idusuario);
            if (!$asesorUser) continue;

            // CRITICAL FIX: Get ALL AgenteFuerza IDs for THIS asesor person
            $thisAsesorAllAfIds = AgenteFuerza::find()
                ->where(['idusuario' => $agenteFuerza->idusuario])
                ->select('id')
                ->column();

            // Get ALL affiliates for THIS asesor person (using ALL their AgenteFuerza IDs)
            // This matches the filtering logic from UserDatosSearch
            $asesorAffiliates = UserDatos::find()
                ->where(['role' => 'afiliado'])
                ->andWhere($affiliateCondition)
                ->andWhere(['asesor_id' => $thisAsesorAllAfIds])
                ->all();

            $totalAsesor = count($asesorAffiliates);
            $activosAsesor = 0;
            $suspendidosAsesor = 0;
            $registradosAsesor = 0;
            $vencidosAsesor = 0;

            foreach ($asesorAffiliates as $affiliate) {
                $contract = Contratos::find()
                    ->where(['user_id' => $affiliate->id])
                    ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->one();

                if ($contract) {
                    switch ($contract->estatus) {
                        case 'Activo':
                            $activosAsesor++;
                            break;
                        case 'Suspendido':
                            $suspendidosAsesor++;
                            break;
                        case 'Registrado':
                            $registradosAsesor++;
                            break;
                        case 'Vencido':
                            $vencidosAsesor++;
                            break;
                    }
                }
            }

            // Get last registration date for this asesor person
            $ultimoRegistro = UserDatos::find()
                ->where(['asesor_id' => $thisAsesorAllAfIds])
                ->max('created_at');

            // Calculate estimated commission (example: $5 per active affiliate)
            $comisionEstimada = $activosAsesor * 5;

            $statsByAsesor[] = [
                'id' => $afId,
                'user_id' => $agenteFuerza->idusuario,
                'nombre_completo' => $asesorUser->nombres . ' ' . $asesorUser->apellidos,
                'codigo_asesor' => 'AF-' . str_pad($afId, 4, '0', STR_PAD_LEFT),
                'total' => $totalAsesor,
                'activos' => $activosAsesor,
                'suspendidos' => $suspendidosAsesor,
                'registrados' => $registradosAsesor,
                'vencidos' => $vencidosAsesor,
                'ultimo_registro' => $ultimoRegistro,
                'comision_estimada' => $comisionEstimada,
            ];

            $topAsesores[] = [
                'id' => $afId,
                'nombre_completo' => $asesorUser->nombres . ' ' . $asesorUser->apellidos,
                'total' => $totalAsesor,
                'activos' => $activosAsesor,
                'comision_estimada' => $comisionEstimada,
            ];
        }

        // Sort top asesores by total affiliates (descending)
        usort($topAsesores, function ($a, $b) {
            return $b['total'] - $a['total'];
        });
        $topAsesores = array_slice($topAsesores, 0, 5);

        // Sort stats by asesor name
        usort($statsByAsesor, function ($a, $b) {
            return strcmp($a['nombre_completo'], $b['nombre_completo']);
        });

        // ============================================
        // FIXED: STATISTICS FOR CHART (Afiliados por Asesor)
        // Using ALL AgenteFuerza IDs for each asesor to match filtering logic
        // ============================================
        $statsForChart = [];
        foreach ($agenteFuerzaIds as $afId) {
            $agenteFuerza = AgenteFuerza::findOne($afId);
            if (!$agenteFuerza) continue;

            $asesorUser = UserDatos::findOne($agenteFuerza->idusuario);
            if (!$asesorUser) continue;

            // Get ALL AgenteFuerza IDs for THIS asesor person
            $thisAsesorAllAfIds = AgenteFuerza::find()
                ->where(['idusuario' => $agenteFuerza->idusuario])
                ->select('id')
                ->column();

            // Count affiliates for this asesor person using ALL their AgenteFuerza IDs
            $totalForChart = UserDatos::find()
                ->where(['role' => 'afiliado'])
                ->andWhere($affiliateCondition)
                ->andWhere(['asesor_id' => $thisAsesorAllAfIds])
                ->count();

            $activosForChart = 0;
            $afiliadosQuery = UserDatos::find()
                ->where(['role' => 'afiliado'])
                ->andWhere($affiliateCondition)
                ->andWhere(['asesor_id' => $thisAsesorAllAfIds])
                ->all();

            foreach ($afiliadosQuery as $affiliate) {
                $contract = Contratos::find()
                    ->where(['user_id' => $affiliate->id])
                    ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->one();

                if ($contract && $contract->estatus == 'Activo') {
                    $activosForChart++;
                }
            }

            $statsForChart[] = [
                'nombre_completo' => $asesorUser->nombres . ' ' . $asesorUser->apellidos,
                'total' => $totalForChart,
                'activos' => $activosForChart,
            ];
        }

        // Sort stats for chart by name
        usort($statsForChart, function ($a, $b) {
            return strcmp($a['nombre_completo'], $b['nombre_completo']);
        });

        // ============================================
        // MONTHLY GROWTH DATA (using the same OR condition)
        // ============================================
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $startDate = date('Y-m-01', strtotime("-$i months"));
            $endDate = date('Y-m-t', strtotime("-$i months"));
            $monthName = date('M Y', strtotime($startDate));

            $count = (clone $affiliatesQuery)
                ->andWhere(['>=', 'created_at', $startDate . ' 00:00:00'])
                ->andWhere(['<=', 'created_at', $endDate . ' 23:59:59'])
                ->count();

            $monthlyData[] = ['month' => $monthName, 'count' => $count];
        }

        // ============================================
        // RECENT AFFILIATES (with correct asesor name using the complete map)
        // ============================================
        $recientes = UserDatos::find()
            ->select([
                'user_datos.id',
                'user_datos.nombres',
                'user_datos.apellidos',
                'user_datos.tipo_cedula',
                'user_datos.cedula',
                'user_datos.created_at',
                'user_datos.clinica_id',
                'user_datos.asesor_id',
                'user_datos.agencia_id',
                'rm_clinica.nombre as clinica_nombre',
            ])
            ->leftJoin('rm_clinica', 'user_datos.clinica_id = rm_clinica.id')
            ->where(['user_datos.role' => 'afiliado'])
            ->andWhere($affiliateCondition)
            ->orderBy(['user_datos.created_at' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();

        // Add contract status and asesor name to each recent affiliate
        foreach ($recientes as &$afiliado) {
            $contract = Contratos::find()
                ->where(['user_id' => $afiliado['id']])
                ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();

            $afiliado['contrato_estatus'] = $contract ? $contract->estatus : 'Sin Contrato';

            // Get the asesor name from the COMPLETE map using asesor_id
            $asesorId = $afiliado['asesor_id'];
            if ($asesorId && isset($asesorNameMap[$asesorId])) {
                $afiliado['asesor_nombre'] = $asesorNameMap[$asesorId];
            } else {
                $afiliado['asesor_nombre'] = null;
            }
        }

        // ============================================
        // AGENCY STATS ARRAY
        // ============================================
        $agenciaStats = [
            'agencia_id' => $agenteId,
            'total_afiliados' => $totalAfiliados,
            'activos' => $activos,
            'suspendidos' => $suspendidos,
            'registrados' => $registrados,
            'vencidos' => $vencidos,
            'anulados' => $anulados,
            'sin_contrato' => $sinContrato,
            'nuevos_este_mes' => $nuevosEsteMes,
            'tasa_actividad' => $tasaActividad,
            'total_asesores' => $totalAsesores,
            'asesores_activos' => $asesoresActivos,
            'comision_estimada_mensual' => $activos * 5,
            'comision_total_acumulada' => $activos * 5 * 12,
            'meta_anual' => 500,
        ];

        $asesoresStats = [
            'total_asesores' => $totalAsesores,
            'asesores_activos' => $asesoresActivos,
        ];

        // Get all clinics where this agency has affiliates (using OR condition)
        $clinicaIds = UserDatos::find()
            ->select(['clinica_id'])
            ->where(['role' => 'afiliado'])
            ->andWhere($affiliateCondition)
            ->andWhere(['not', ['clinica_id' => null]])
            ->distinct()
            ->column();

        $agencias = RmClinica::find()
            ->where(['id' => $clinicaIds])
            ->orderBy(['nombre' => SORT_ASC])
            ->all();

        // Debug info
        $debugInfo = [
            'agente_id' => $agenteId,
            'agente_fuerza_ids' => $agenteFuerzaIds,
            'all_agente_fuerza_ids' => $allAgenteFuerzaIds,
            'asesor_name_map_keys' => array_keys($asesorNameMap),
            'stats_for_chart' => $statsForChart,
            'total_asesores' => $totalAsesores,
            'total_afiliados' => $totalAfiliados,
            'activos' => $activos,
            'suspendidos' => $suspendidos,
            'registrados' => $registrados,
            'sin_contrato' => $sinContrato,
            'stats_by_asesor_count' => count($statsByAsesor),
        ];

        // Render the view with all necessary data
        return $this->render('dashboard-agencia', [
            'agenciaName' => $agenciaName,
            'agenciaStats' => $agenciaStats,
            'asesoresStats' => $asesoresStats,
            'statsByAsesor' => $statsByAsesor,
            'statsForChart' => $statsForChart,
            'monthlyData' => $monthlyData,
            'topAsesores' => $topAsesores,
            'recientes' => $recientes,
            'agencias' => $agencias,
            'debugInfo' => $debugInfo,
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

    /**
     * Endpoint for ciudades dropdown (DepDrop)
     * Returns ciudades using codigo_ciudad as the value
     */
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
                // FIXED: Use 'codigo_ciudad as id' instead of just 'id'
                $out = RmCiudad::find()
                    ->select(['codigo_ciudad as id', 'nombre as name'])
                    ->where(['estado_codigo' => $est_id])
                    ->orderBy(['nombre' => SORT_ASC])
                    ->asArray()
                    ->all();
                return ['output' => $out, 'selected' => $selected];
            }
        }
        return ['output' => '', 'selected' => $selected];
    }

    /**
     * Endpoint for municipios dropdown (DepDrop)
     * Returns municipios using codigo_muni as the value
     */
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
                // FIXED: Use 'codigo_muni as id' instead of just 'id'
                $out = RmMunicipio::find()
                    ->select(['codigo_muni as id', 'nombre as name'])
                    ->where(['estado_codigo' => $est_id])
                    ->orderBy(['nombre' => SORT_ASC])
                    ->asArray()
                    ->all();
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    /**
     * Endpoint for parroquias dropdown (DepDrop)
     * Returns parroquias using codigo_parro as the value
     */
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
                // FIXED: Use 'codigo_parro as id' instead of just 'id'
                $out = RmParroquia::find()
                    ->select(['codigo_parro as id', 'nombre as name'])
                    ->where(['muni_codigo' => $mun_id])
                    ->orderBy(['nombre' => SORT_ASC])
                    ->asArray()
                    ->all();
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
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
