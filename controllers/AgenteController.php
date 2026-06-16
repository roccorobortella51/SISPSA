<?php

namespace app\controllers;

use Yii;
use app\models\Agente;
use app\models\AgenteSearch;
use app\models\AgenteFuerza;
use app\models\UserDatos;
use app\models\Contratos;
use app\components\UserHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Html;

/**
 * AgenteController implements the CRUD actions for Agente model.
 */
class AgenteController extends Controller
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
     * Lists all Agente models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AgenteSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        // COMPLETELY DISABLE PAGINATION - Show all records
        $dataProvider->pagination = false;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Agente model.
     * @param int $id ID
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
     * Creates a new Agente model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Agente();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                if ($model->save()) {

                    Yii::$app->session->setFlash('success', 'La agencia ha sido creada exitosamente.');
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {

                    // Obtener los errores del modelo.
                    $errors = $model->getErrors();

                    // mensaje de error flash.

                    $errorMessage = 'No se pudo crear la agencia. Por favor, revise los siguientes errores:<br>';
                    foreach ($errors as $attribute => $attributeErrors) {

                        foreach ($attributeErrors as $error) {
                            $errorMessage .= Html::encode($error) . '<br>';
                        }
                    }

                    Yii::$app->session->setFlash('error', $errorMessage);

                    return $this->render('create', [
                        'model' => $model,
                        'isNewRecord' => true,
                    ]);
                }
            }
        } else {

            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'isNewRecord' => true,
        ]);
    }

    /**
     * Updates an existing Agente model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'isNewRecord' => false,
        ]);
    }

    /**
     * Deletes an existing Agente model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Asesores Performance View - CRM Sales Module for Agency
     * Shows all asesores belonging to the current agency with KPIs, alerts, and performance metrics
     * 
     * @return string
     */
    public function actionAsesoresPerformance()
    {
        // Check if user has AGENTE role
        $user = Yii::$app->user->identity;

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $authManager = Yii::$app->authManager;
        $roles = $authManager->getRolesByUser($user->id);

        if (!isset($roles['Agente'])) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para acceder a esta sección.');
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

        // Get all AgenteFuerza records (asesores) belonging to this agency
        $agenteFuerzaRecords = AgenteFuerza::find()
            ->where(['agente_id' => $agenteId])
            ->all();

        $agenteFuerzaIds = array_column($agenteFuerzaRecords, 'id');
        $asesorUserIds = array_column($agenteFuerzaRecords, 'idusuario');

        // Get ALL AgenteFuerza IDs for these asesores (from ANY agency)
        $allAgenteFuerzaIds = [];
        if (!empty($asesorUserIds)) {
            $allAgenteFuerzaIds = AgenteFuerza::find()
                ->where(['idusuario' => $asesorUserIds])
                ->select('id')
                ->column();
        }

        // Build the OR condition for affiliates (SAME as UserDatosSearch)
        $affiliateCondition = ['or'];
        if (!empty($allAgenteFuerzaIds)) {
            $affiliateCondition[] = ['user_datos.asesor_id' => $allAgenteFuerzaIds];
        }
        $affiliateCondition[] = ['user_datos.agencia_id' => $agenteId];

        // ============================================
        // Collect performance data for each asesor
        // ============================================
        $asesoresData = [];
        $totalAfiliadosGeneral = 0;
        $totalActivosGeneral = 0;
        $totalSuspendidosGeneral = 0;
        $totalVencidosGeneral = 0;
        $totalComisionGeneral = 0;

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

            // Get ALL affiliates for THIS asesor person
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
            $nuevosEsteMes = 0;

            foreach ($asesorAffiliates as $affiliate) {
                // Check if created this month
                if ($affiliate->created_at && strtotime($affiliate->created_at) >= strtotime(date('Y-m-01'))) {
                    $nuevosEsteMes++;
                }

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

            // Calculate previous month affiliates for trend
            $previousMonthCount = UserDatos::find()
                ->where(['role' => 'afiliado'])
                ->andWhere($affiliateCondition)
                ->andWhere(['asesor_id' => $thisAsesorAllAfIds])
                ->andWhere(['>=', 'created_at', date('Y-m-01', strtotime('-1 month'))])
                ->andWhere(['<', 'created_at', date('Y-m-01')])
                ->count();

            // Calculate growth percentage
            $growth = 0;
            if ($previousMonthCount > 0) {
                $growth = round((($totalAsesor - $previousMonthCount) / $previousMonthCount) * 100, 1);
            } elseif ($totalAsesor > 0) {
                $growth = 100;
            }

            // Calculate activity rate
            $tasaActividad = $totalAsesor > 0 ? round(($activosAsesor / $totalAsesor) * 100, 1) : 0;

            // Calculate estimated commission (example: $5 per active affiliate)
            $comisionEstimada = $activosAsesor * 5;

            // Determine status color and alert level
            $statusColor = 'success';
            $alertLevel = 'none';
            $alertMessage = '';

            if ($tasaActividad < 30 && $totalAsesor > 5) {
                $statusColor = 'danger';
                $alertLevel = 'high';
                $alertMessage = 'Tasa de actividad crítica (<30%)';
            } elseif ($tasaActividad < 50 && $totalAsesor > 3) {
                $statusColor = 'warning';
                $alertLevel = 'medium';
                $alertMessage = 'Tasa de actividad baja (<50%)';
            } elseif ($suspendidosAsesor > $activosAsesor && $totalAsesor > 3) {
                $statusColor = 'warning';
                $alertLevel = 'medium';
                $alertMessage = 'Más suspendidos que activos';
            } elseif ($vencidosAsesor > 2) {
                $statusColor = 'warning';
                $alertLevel = 'medium';
                $alertMessage = "{$vencidosAsesor} contrato(s) vencido(s)";
            } elseif ($nuevosEsteMes == 0 && $totalAsesor > 0) {
                $statusColor = 'info';
                $alertLevel = 'low';
                $alertMessage = 'Sin nuevos afiliados este mes';
            }

            // Get last activity date (most recent affiliate created)
            $lastActivity = UserDatos::find()
                ->where(['asesor_id' => $thisAsesorAllAfIds])
                ->max('created_at');

            // Calculate days since last activity
            $daysSinceLastActivity = $lastActivity ? floor((time() - strtotime($lastActivity)) / (60 * 60 * 24)) : null;

            // Alert for inactivity
            if ($daysSinceLastActivity !== null && $daysSinceLastActivity > 30 && $totalAsesor > 0) {
                $alertLevel = 'high';
                $alertMessage = $alertMessage ?: "Inactivo por {$daysSinceLastActivity} días";
                $statusColor = 'danger';
            } elseif ($daysSinceLastActivity !== null && $daysSinceLastActivity > 15 && $totalAsesor > 0) {
                if ($alertLevel == 'none') {
                    $alertLevel = 'medium';
                    $alertMessage = "Sin actividad por {$daysSinceLastActivity} días";
                    $statusColor = 'warning';
                }
            }

            // Get percentage of affiliates without contracts
            $sinContrato = $totalAsesor - ($activosAsesor + $suspendidosAsesor + $registradosAsesor + $vencidosAsesor);
            $porcentajeSinContrato = $totalAsesor > 0 ? round(($sinContrato / $totalAsesor) * 100, 1) : 0;

            $asesoresData[] = [
                'id' => $afId,
                'user_id' => $agenteFuerza->idusuario,
                'nombre_completo' => $asesorUser->nombres . ' ' . $asesorUser->apellidos,
                'email' => $asesorUser->email,
                'telefono' => $asesorUser->telefono,
                'cedula' => $asesorUser->tipo_cedula . '-' . $asesorUser->cedula,
                'total' => $totalAsesor,
                'activos' => $activosAsesor,
                'suspendidos' => $suspendidosAsesor,
                'registrados' => $registradosAsesor,
                'vencidos' => $vencidosAsesor,
                'sin_contrato' => $sinContrato,
                'porcentaje_sin_contrato' => $porcentajeSinContrato,
                'nuevos_este_mes' => $nuevosEsteMes,
                'tasa_actividad' => $tasaActividad,
                'comision_estimada' => $comisionEstimada,
                'growth' => $growth,
                'status_color' => $statusColor,
                'alert_level' => $alertLevel,
                'alert_message' => $alertMessage,
                'last_activity' => $lastActivity,
                'days_since_last_activity' => $daysSinceLastActivity,
                'porcentajes' => [
                    'por_venta' => $agenteFuerza->por_venta ?? 0,
                    'por_asesor' => $agenteFuerza->por_asesor ?? 0,
                    'por_cobranza' => $agenteFuerza->por_cobranza ?? 0,
                    'por_post_venta' => $agenteFuerza->por_post_venta ?? 0,
                    'por_registrar' => $agenteFuerza->por_registrar ?? 0,
                ],
                'permisos' => [
                    'puede_vender' => $agenteFuerza->puede_vender ?? 0,
                    'puede_asesorar' => $agenteFuerza->puede_asesorar ?? 0,
                    'puede_cobrar' => $agenteFuerza->puede_cobrar ?? 0,
                    'puede_post_venta' => $agenteFuerza->puede_post_venta ?? 0,
                    'puede_registrar' => $agenteFuerza->puede_registrar ?? 0,
                ],
                'codigo_asesor' => 'AF-' . str_pad($afId, 4, '0', STR_PAD_LEFT),
            ];

            // Update totals
            $totalAfiliadosGeneral += $totalAsesor;
            $totalActivosGeneral += $activosAsesor;
            $totalSuspendidosGeneral += $suspendidosAsesor;
            $totalVencidosGeneral += $vencidosAsesor;
            $totalComisionGeneral += $comisionEstimada;
        }

        // Sort asesores by total affiliates (descending)
        usort($asesoresData, function ($a, $b) {
            return $b['total'] - $a['total'];
        });

        // Calculate agency-level KPIs
        $tasaActividadGeneral = $totalAfiliadosGeneral > 0 ? round(($totalActivosGeneral / $totalAfiliadosGeneral) * 100, 1) : 0;
        $asesoresConAlertas = count(array_filter($asesoresData, function ($a) {
            return $a['alert_level'] != 'none';
        }));
        $totalComisionMensual = $totalComisionGeneral;
        $totalComisionAnual = $totalComisionGeneral * 12;

        // Top performers
        $topPerformers = array_slice($asesoresData, 0, 3);

        // Asesores needing attention (alerts)
        $asesoresNecesitanAtencion = array_filter($asesoresData, function ($a) {
            return $a['alert_level'] == 'high' || $a['alert_level'] == 'medium';
        });

        // Inactive asesores (no affiliates)
        $asesoresInactivos = array_filter($asesoresData, function ($a) {
            return $a['total'] == 0;
        });

        return $this->render('asesores-performance', [
            'agenciaName' => $agenciaName,
            'agenciaId' => $agenteId,
            'asesoresData' => $asesoresData,
            'totalAsesores' => count($agenteFuerzaIds),
            'totalAfiliadosGeneral' => $totalAfiliadosGeneral,
            'totalActivosGeneral' => $totalActivosGeneral,
            'totalSuspendidosGeneral' => $totalSuspendidosGeneral,
            'totalVencidosGeneral' => $totalVencidosGeneral,
            'totalComisionMensual' => $totalComisionMensual,
            'totalComisionAnual' => $totalComisionAnual,
            'tasaActividadGeneral' => $tasaActividadGeneral,
            'asesoresConAlertas' => $asesoresConAlertas,
            'topPerformers' => $topPerformers,
            'asesoresNecesitanAtencion' => $asesoresNecesitanAtencion,
            'asesoresInactivos' => $asesoresInactivos,
        ]);
    }

    /**
     * Finds the Agente model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Agente the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Agente::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    /**
     * Displays the current agency information for the logged-in Agente user.
     * Shows agency details, commission percentages, and associated asesores.
     * 
     * @return string
     * @throws NotFoundHttpException if the agency is not found
     */
    public function actionMiAgencia()
    {
        // Check if user has AGENTE role
        $user = Yii::$app->user->identity;

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        // DEBUG: Let's see what roles the user has
        $authManager = Yii::$app->authManager;
        $roles = $authManager->getRolesByUser($user->id);
        $roleNames = array_keys($roles);

        // Log the roles for debugging
        Yii::info("User roles: " . print_r($roleNames, true), 'debug');

        // Check if user has Agente role (case-sensitive)
        // The role might be stored as 'Agente' or 'agente'
        $hasAgenteRole = false;
        foreach ($roleNames as $roleName) {
            if (strtolower($roleName) === 'agente') {
                $hasAgenteRole = true;
                break;
            }
        }

        if (!$hasAgenteRole) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para acceder a esta sección. Roles detectados: ' . implode(', ', $roleNames));
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

        if (!$agencia) {
            throw new NotFoundHttpException('La agencia no existe.');
        }

        // Get the propietario (owner) information
        $propietario = UserDatos::findOne($agencia->idusuariopropietario);

        // Get all asesores (AgenteFuerza) belonging to this agency
        $asesoresRecords = AgenteFuerza::find()
            ->where(['agente_id' => $agenteId])
            ->all();

        $asesores = [];
        foreach ($asesoresRecords as $record) {
            $asesorUser = UserDatos::findOne($record->idusuario);
            if ($asesorUser) {
                $asesores[] = [
                    'id' => $record->id,
                    'nombre_completo' => $asesorUser->nombres . ' ' . $asesorUser->apellidos,
                    'email' => $asesorUser->email,
                    'telefono' => $asesorUser->telefono,
                    'cedula' => $asesorUser->tipo_cedula . '-' . $asesorUser->cedula,
                    'porcentajes' => [
                        'por_venta' => $record->por_venta ?? 0,
                        'por_asesor' => $record->por_asesor ?? 0,
                        'por_cobranza' => $record->por_cobranza ?? 0,
                        'por_post_venta' => $record->por_post_venta ?? 0,
                        'por_registrar' => $record->por_registrar ?? 0,
                    ],
                    'permisos' => [
                        'puede_vender' => $record->puede_vender ?? 0,
                        'puede_asesorar' => $record->puede_asesorar ?? 0,
                        'puede_cobrar' => $record->puede_cobrar ?? 0,
                        'puede_post_venta' => $record->puede_post_venta ?? 0,
                        'puede_registrar' => $record->puede_registrar ?? 0,
                    ],
                ];
            }
        }

        // Calculate totals for summary
        $totalAsesores = count($asesores);
        $totalComisionPromedio = 0;
        if ($totalAsesores > 0) {
            $sumVenta = array_sum(array_column($asesores, 'porcentajes.por_venta'));
            $totalComisionPromedio = round($sumVenta / $totalAsesores, 2);
        }

        // Get contact info from propietario
        $propietarioNombre = $propietario ? $propietario->nombres . ' ' . $propietario->apellidos : 'N/A';
        $propietarioCedula = $propietario ? $propietario->tipo_cedula . '-' . $propietario->cedula : 'N/A';
        $propietarioEmail = $propietario ? $propietario->email : 'N/A';
        $propietarioTelefono = $propietario ? $propietario->telefono : 'N/A';
        $propietarioDireccion = $propietario ? $propietario->direccion : 'N/A';

        // Build RIF from propietario data or use default
        $rif = $propietario ? ($propietario->rif ?? $propietarioCedula) : 'N/A';

        return $this->render('mi-agencia', [
            'agencia' => $agencia,
            'agenciaId' => $agenteId,
            'propietario' => $propietario,
            'propietarioNombre' => $propietarioNombre,
            'propietarioCedula' => $propietarioCedula,
            'propietarioEmail' => $propietarioEmail,
            'propietarioTelefono' => $propietarioTelefono,
            'propietarioDireccion' => $propietarioDireccion,
            'rif' => $rif,
            'asesores' => $asesores,
            'totalAsesores' => $totalAsesores,
            'totalComisionPromedio' => $totalComisionPromedio,
        ]);
    }
}
