<?php

namespace app\controllers;

use app\models\RmClinica;
use app\models\RmClinicaSearch;
use app\models\RmEstado;
use app\models\RmMunicipio;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;
use app\components\UserHelper;
use app\models\RmCiudad;
use app\models\CheckListClinicas;
use yii\helpers\ArrayHelper;
use app\models\UserDatos;
use app\models\SisSiniestro;
use app\models\Pagos;
use app\models\AgenteFuerza;

/**
 * RmClinicaController implements the CRUD actions for RmClinica model.
 */
class RmClinicaController extends Controller
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

    // Add this method to RmClinicaController.php
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // For COORDINADOR-CLINICA role, redirect view action to view-clinica
        if ($action->id === 'view' && UserHelper::getMyRol() === 'COORDINADOR-CLINICA') {
            return $this->redirect(['view-clinica']);
        }

        return true;
    }

    public function actionIndicator($id)
    {
        $totalAfiliados = UserDatos::find()->where(['clinica_id' => $id])->count();
        $totalSiniestrosAfiliados = SisSiniestro::find()->where(['idclinica' => $id])->count();
        $totalPagosAfiliados = Pagos::find()
            ->joinWith('userDatos')
            ->where(['user_datos.clinica_id' => $id])
            ->count();
        $montoTotalPagosAfiliados = Pagos::find()
            ->joinWith('userDatos')
            ->where(['user_datos.clinica_id' => $id])
            ->sum('monto_pagado');
        $montoTotalSiniestrosAfiliados = SisSiniestro::find()
            ->joinWith('afiliado')
            ->where(['user_datos.clinica_id' => $id])
            ->sum('costo_total');
        $model = $this->findModel($id);
        return $this->render('indicator', [
            'model' => $model,
            'totalAfiliados' => $totalAfiliados,
            'totalSiniestrosAfiliados' => $totalSiniestrosAfiliados,
            'totalPagosAfiliados' => $totalPagosAfiliados,
            'montoTotalPagosAfiliados' => $montoTotalPagosAfiliados,
            'montoTotalSiniestrosAfiliados' => $montoTotalSiniestrosAfiliados,
        ]);
    }

    /**
     * Lists all RmClinica models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new RmClinicaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        // *** AQUI OBTENEMOS LOS DATOS DEL GRAFICO ***
        $chartData = CheckListClinicas::getLastChecklistsByClinic();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Displays a single RmClinica model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // --- CÓDIGO PARA OBTENER LAS LISTAS DE UBICACIÓN ---
        // Obtener la lista de estados (desde UserHelper)
        $estadosList = UserHelper::getEstadosList();

        // --- ¡CÓDIGO AJUSTADO PARA MUNICIPIOS! ---
        // Obtenemos la lista de municipios directamente de RmMunicipio,
        // mapeando por 'codigo_muni' para que coincida con $model->municipio.
        $municipiosList = [];
        if (!empty($model->estado)) {

            $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
            if ($estado) {

                $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
                if ($estado) {

                    $municipiosList = ArrayHelper::map(
                        RmMunicipio::find()
                            ->where(['estado_codigo' => $estado->id])
                            ->asArray()
                            ->all(),
                        'codigo_muni', // ¡Mapeamos por 'codigo_muni' aquí!
                        'nombre'
                    );
                }
            }
        }
        // ------------------------------------

        // Obtener la lista de parroquias (desde UserHelper)
        $parroquiasList = $model->municipio ? UserHelper::getParroquiasList($model->municipio) : [];

        // Obtener la lista de ciudades (desde RmCiudad)
        $ciudadesList = [];
        if (!empty($model->estado)) {
            $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
            $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
            if ($estado) {

                $ciudadesList = ArrayHelper::map(
                    RmCiudad::find()
                        ->where(['estado_codigo' => $estado->id])
                        ->asArray()
                        ->all(),
                    'id',
                    'nombre'
                );
            }
        }
        // ------------------------------------


        return $this->render('view', [
            'model' => $model,
            'estadosList' => $estadosList,
            'municipiosList' => $municipiosList, // ¡Esta es la lista correctamente mapeada!
            'parroquiaList' => $parroquiasList,
            'ciudadesList' => $ciudadesList,
            'listaEstatus' => ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'],
        ]);
    }

    public function actionViewClinica()
    {
        $id = UserHelper::getMyClinicaId();
        $userId = Yii::$app->user->id;
        $rol = UserHelper::getMyRol();

        if ($id == null) {
            Yii::$app->session->setFlash('error', 'No tiene una clínica asociada. Por favor, contacte al administrador.');
            return $this->redirect(['site/index']);
        }

        $model = $this->findModel($id);

        // --- CÓDIGO PARA OBTENER LAS LISTAS DE UBICACIÓN ---
        // Obtener la lista de estados (desde UserHelper)
        $estadosList = UserHelper::getEstadosList();

        // --- ¡CÓDIGO AJUSTADO PARA MUNICIPIOS! ---
        // Obtenemos la lista de municipios directamente de RmMunicipio,
        // mapeando por 'codigo_muni' para que coincida con $model->municipio.
        $municipiosList = [];
        if (!empty($model->estado)) {

            $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
            if ($estado) {

                $municipiosList = ArrayHelper::map(
                    RmMunicipio::find()
                        ->where(['estado_codigo' => $estado->id])
                        ->asArray()
                        ->all(),
                    'codigo_muni', // ¡Mapeamos por 'codigo_muni' aquí!
                    'nombre'
                );
            }
        }
        // ------------------------------------

        // Obtener la lista de parroquias (desde UserHelper)
        $parroquiasList = $model->municipio ? UserHelper::getParroquiasList($model->municipio) : [];

        // Obtener la lista de ciudades (desde RmCiudad)
        $ciudadesList = [];
        if (!empty($model->estado)) {

            $estado = RmEstado::find()->where(['nombre' => $model->estado])->one();
            if ($estado) {

                $ciudadesList = ArrayHelper::map(
                    RmCiudad::find()
                        ->where(['estado_codigo' => $estado->id])
                        ->asArray()
                        ->all(),
                    'id',
                    'nombre'
                );
            }
        }
        // ------------------------------------


        return $this->render('view', [
            'model' => $model,
            'estadosList' => $estadosList,
            'municipiosList' => $municipiosList, // ¡Esta es la lista correctamente mapeada!
            'parroquiaList' => $parroquiasList,
            'ciudadesList' => $ciudadesList,
            'listaEstatus' => ['Activo' => 'Activo', 'Inactivo' => 'Inactivo'],
        ]);
    }

    /**
     * Creates a new RmClinica model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new RmClinica();

        if ($this->request->isPost && $model->load($this->request->post())) {

            $estado = RmEstado::find()->where(['id' => $model->estado])->one();

            if ($estado) {
                $model->estado = $estado->nombre;
            }

            $model->estatus = "Activo";

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {

                echo "MODEL NOT SAVED";
                print_r($model->getAttributes());
                print_r($model->getErrors());
                exit;
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing RmClinica model.
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
        ]);
    }

    /**
     * Deletes an existing RmClinica model.
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
     * Finds the RmClinica model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return RmClinica the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = RmClinica::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdatestatus()
    {
        if (Yii::$app->request->isAjax and Yii::$app->request->post()) {
            $variables = Yii::$app->request->post();

            $model = RmClinica::find()->where(['id' => $variables['id']])->one();

            if ($model->estatus == "Activo") {
                $model->estatus = "Inactivo";
                $model->save(false);
            } else {
                $model->estatus = "Activo";
                $model->save(false);
            }
        }
    }

    public function actionFondo()
    {
        // --- Datos Ficticios para la Demostración ---
        // Estos valores deben ser reemplazados por datos reales de tu base de datos o configuraciones.
        $fondoAnualTotal = 1200000.00; // Ejemplo: $1,200,000
        $fondoMensualTotal = 100000.00; // Ejemplo: $100,000
        $consumoMensualActual = 65000.00; // Ejemplo: $65,000 (consumido del mensual)

        // Calcular el porcentaje consumido del fondo mensual
        $porcentajeConsumido = ($fondoMensualTotal > 0) ? ($consumoMensualActual / $fondoMensualTotal) * 100 : 0;
        $porcentajeConsumido = round($porcentajeConsumido, 2); // Redondear a dos decimales

        // --- Límites de los Colores (futura configuración de administrador) ---
        // Estos límites son para la representación visual de la barra de "gasolina".
        // Por ahora, son fijos. En el futuro, un administrador podría configurarlos desde una interfaz.
        $limiteVerde = 70; // Hasta 70% de consumo es verde (Ideal)
        $limiteAmarillo = 90; // De 70% a 90% de consumo es amarillo (Advertencia)
        // Más del 90% es rojo (Peligro)

        // Determinar el color actual de la barra de progreso
        $colorClase = 'bg-success'; // Verde por defecto
        if ($porcentajeConsumido >= $limiteAmarillo) {
            $colorClase = 'bg-danger'; // Rojo si supera el límite de amarillo
        } elseif ($porcentajeConsumido >= $limiteVerde) {
            $colorClase = 'bg-warning'; // Amarillo si supera el límite de verde
        }

        // ----- Envío de Correos (para futura implementación) ----
        // Aquí iría la lógica para verificar si se deben enviar correos.
        // Esto requeriría almacenar el estado de los correos enviados (por ejemplo, en la DB)
        // para evitar enviar múltiples correos por el mismo evento dentro de un periodo.
        /*
        // Ejemplo de lógica futura:
        if ($porcentajeConsumido >= $limiteAmarillo && $porcentajeConsumido < $limiteRojo && !$fondoModel->warningEmailSentThisMonth) {
            // Lógica para enviar correo de advertencia al administrador
            // Yii::$app->mailer->compose(...)
            // Actualizar $fondoModel->warningEmailSentThisMonth = true; $fondoModel->save();
        }
        if ($porcentajeConsumido >= $limiteRojo && !$fondoModel->dangerEmailSentThisMonth) {
            // Lógica para enviar correo de peligro al administrador
            // Yii::$app->mailer->compose(...)
            // Actualizar $fondoModel->dangerEmailSentThisMonth = true; $fondoModel->save();
        }
        */

        return $this->render('fondo', [ // La vista ahora se llama 'fondo'
            'fondoAnualTotal' => $fondoAnualTotal,
            'fondoMensualTotal' => $fondoMensualTotal,
            'consumoMensualActual' => $consumoMensualActual,
            'porcentajeConsumido' => $porcentajeConsumido,
            'limiteVerde' => $limiteVerde,
            'limiteAmarillo' => $limiteAmarillo,
            'colorClase' => $colorClase,
        ]);
    }

    /**
     * Shows a list of clinics to select for viewing intermediarios
     * 
     * @return string
     */
    public function actionSeleccionarClinica()
    {
        // Check permissions - CASE INSENSITIVE
        $rol = UserHelper::getMyRol();
        $rolLower = strtolower($rol);

        // Check if user has admin/superadmin roles (case insensitive)
        $allowedRoles = ['superadmin', 'admin', 'coordinador'];
        $hasPermission = in_array($rolLower, $allowedRoles);

        // If not an admin, check via authManager
        if (!$hasPermission) {
            $authManager = Yii::$app->authManager;
            $userRoles = $authManager->getRolesByUser(Yii::$app->user->id);
            $roleNames = array_map('strtolower', array_keys($userRoles));
            $hasPermission = !empty(array_intersect($roleNames, $allowedRoles));
        }

        // If user is COORDINADOR-CLINICA, redirect directly to their clinic
        if (!$hasPermission && $rolLower === 'coordinador-clinica') {
            $clinicId = UserHelper::getMyClinicaId();
            if ($clinicId) {
                return $this->redirect(['intermediarios', 'id' => $clinicId]);
            }
        }

        // Final permission check
        if (!$hasPermission) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para ver este reporte. Su rol es: ' . $rol);
            return $this->goHome();
        }

        // Get all clinics for selection
        $clinicas = RmClinica::find()
            ->select(['id', 'nombre', 'codigo_clinica', 'estado', 'telefono', 'correo', 'rif'])
            ->where(['IS', 'deleted_at', null])
            ->orderBy(['nombre' => SORT_ASC])
            ->all();

        // Ensure $clinicas is always an array
        if ($clinicas === null) {
            $clinicas = [];
        }

        return $this->render('seleccionar-clinica', [
            'clinicas' => $clinicas,
        ]);
    }

    /**
     * Displays a report of all intermediarios (AgenteFuerza) that belong to a clinic.
     * 
     * @param int $id The clinic ID
     * @return string
     * @throws NotFoundHttpException if the clinic is not found
     */
    public function actionIntermediarios($id)
    {
        // First, find the clinic
        $clinic = $this->findModel($id);

        // Check permissions - CASE INSENSITIVE
        $rol = UserHelper::getMyRol();
        $rolLower = strtolower($rol);

        // Check if user has admin/superadmin roles (case insensitive)
        $allowedRoles = ['superadmin', 'admin', 'coordinador'];
        $hasPermission = in_array($rolLower, $allowedRoles);

        // If not an admin, check via authManager
        if (!$hasPermission) {
            $authManager = Yii::$app->authManager;
            $userRoles = $authManager->getRolesByUser(Yii::$app->user->id);
            $roleNames = array_map('strtolower', array_keys($userRoles));
            $hasPermission = !empty(array_intersect($roleNames, $allowedRoles));
        }

        // Check if user is COORDINADOR-CLINICA with access to this specific clinic
        $canViewClinic = false;
        if ($rolLower === 'coordinador-clinica') {
            $userClinicId = UserHelper::getMyClinicaId();
            $canViewClinic = ($userClinicId == $id);
        }

        // Final permission check
        if (!$hasPermission && !$canViewClinic) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para ver este reporte. Su rol es: ' . $rol);
            return $this->redirect(['seleccionar-clinica']);
        }

        // Create the search model
        $searchModel = new \app\models\IntermediariosByClinicaSearch();

        // Get the data provider with search applied
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id);

        // Get summary statistics
        $summary = $searchModel->getSummary($id);

        // Pass ALL variables to the view
        return $this->render('intermediarios', [
            'clinic' => $clinic,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'summary' => $summary,
        ]);
    }

    /**
     * Export intermediarios report to Excel
     * 
     * @param int $id The clinic ID
     * @return \yii\web\Response
     */
    public function actionExportarIntermediariosExcel($id)
    {
        $clinic = $this->findModel($id);

        // Check permissions - CASE INSENSITIVE
        $rol = UserHelper::getMyRol();
        $allowedRoles = ['superadmin', 'admin', 'coordinador', 'SuperAdmin', 'Admin', 'Coordinador'];
        $canViewAll = in_array(strtolower($rol), array_map('strtolower', $allowedRoles));

        if (!$canViewAll) {
            $authManager = Yii::$app->authManager;
            $userRoles = $authManager->getRolesByUser(Yii::$app->user->id);
            $roleNames = array_keys($userRoles);
            $canViewAll = in_array('superadmin', $roleNames) || in_array('admin', $roleNames);
        }

        $canViewClinic = false;
        $rolLower = strtolower($rol);
        if ($rolLower === 'coordinador-clinica') {
            $userClinicId = UserHelper::getMyClinicaId();
            $canViewClinic = ($userClinicId == $id);
        }

        if (!$canViewAll && !$canViewClinic) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para exportar este reporte.');
            return $this->redirect(['index']);
        }

        $searchModel = new \app\models\IntermediariosByClinicaSearch();
        $query = $searchModel->getIntermediariosQuery($id, Yii::$app->request->queryParams);
        $intermediarios = $query->all();
        $summary = $searchModel->getSummary($id);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator("SISPSA")
            ->setTitle("Intermediarios - " . $clinic->nombre)
            ->setSubject("Listado de Intermediarios")
            ->setDescription("Reporte de intermediarios para la clínica " . $clinic->nombre);

        // ============================================
        // HEADER SECTION
        // ============================================
        $currentRow = 1;

        // Title
        $sheet->setCellValue('A' . $currentRow, 'REPORTE DE INTERMEDIARIOS POR CLÍNICA');
        $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        // Clinic info
        $sheet->setCellValue('A' . $currentRow, 'Clínica: ' . $clinic->nombre);
        $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        if ($clinic->codigo_clinica) {
            $sheet->setCellValue('A' . $currentRow, 'Código: ' . $clinic->codigo_clinica);
            $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $currentRow++;
        }

        $sheet->setCellValue('A' . $currentRow, 'Generado: ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $currentRow += 2;

        // Summary stats - ONLY Total Intermediarios and Agencias Asociadas
        $sheet->setCellValue('A' . $currentRow, 'RESUMEN:');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $summaryData = [
            ['Total Intermediarios', number_format($summary['total_intermediarios'])],
            ['Agencias Asociadas', number_format($summary['total_agencias'])],
        ];

        foreach ($summaryData as $item) {
            $sheet->setCellValue('A' . $currentRow, $item[0]);
            $sheet->setCellValue('B' . $currentRow, $item[1]);
            $currentRow++;
        }

        $currentRow += 2;

        // ============================================
        // HEADERS
        // ============================================
        $headers = [
            'A' => '#',
            'B' => 'Nombre Completo',
            'C' => 'Cédula',
            'D' => 'Email',
            'E' => 'Teléfono',
            'F' => 'Agencia',
            'G' => 'Código SUDEASEG',
            'H' => 'Afiliados',
            'I' => '% Venta',
            'J' => 'Fecha Creación',
        ];

        $headerRow = $currentRow;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2c3e50'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->applyFromArray($headerStyle);

        // ============================================
        // DATA
        // ============================================
        $dataRow = $headerRow + 1;
        $counter = 1;

        foreach ($intermediarios as $intermediario) {
            $userDatos = $intermediario->userDatos;

            // Count affiliates for this intermediario
            $affiliateCount = \app\models\UserDatos::find()
                ->where(['asesor_id' => $intermediario->id])
                ->andWhere(['role' => 'afiliado'])
                ->andWhere(['is', 'deleted_at', null])
                ->count();

            $sheet->setCellValue('A' . $dataRow, $counter);
            $sheet->setCellValue('B' . $dataRow, $userDatos ? $userDatos->nombres . ' ' . $userDatos->apellidos : 'N/A');
            $sheet->setCellValue('C' . $dataRow, $userDatos ? $userDatos->tipo_cedula . '-' . $userDatos->cedula : 'N/A');
            $sheet->setCellValue('D' . $dataRow, $userDatos ? $userDatos->email : 'N/A');
            $sheet->setCellValue('E' . $dataRow, $userDatos ? $userDatos->telefono : 'N/A');
            $sheet->setCellValue('F' . $dataRow, $intermediario->agente ? $intermediario->agente->nom : 'N/A');
            $sheet->setCellValue('G' . $dataRow, $intermediario->registro_corredor_actividad_aseguradora ?: 'N/A');
            $sheet->setCellValue('H' . $dataRow, $affiliateCount);
            $sheet->setCellValue('I' . $dataRow, number_format($intermediario->por_venta, 1) . '%');
            $sheet->setCellValue('J' . $dataRow, Yii::$app->formatter->asDate($intermediario->created_at, 'php:d/m/Y'));

            $dataRow++;
            $counter++;
        }

        // ============================================
        // STYLING
        // ============================================

        // Apply borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A' . $headerRow . ':J' . ($dataRow - 1))->applyFromArray($styleArray);

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Center align numeric columns
        foreach (['A', 'H', 'I', 'J'] as $col) {
            $sheet->getStyle($col . $headerRow . ':' . $col . ($dataRow - 1))
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Filename
        $filename = 'Intermediarios_' . str_replace(' ', '_', $clinic->nombre) . '_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export intermediarios report to PDF
     * 
     * @param int $id The clinic ID
     * @return \yii\web\Response
     */
    public function actionExportarIntermediariosPdf($id)
    {
        $clinic = $this->findModel($id);

        // Check permissions - CASE INSENSITIVE
        $rol = UserHelper::getMyRol();
        $allowedRoles = ['superadmin', 'admin', 'coordinador', 'SuperAdmin', 'Admin', 'Coordinador'];
        $canViewAll = in_array(strtolower($rol), array_map('strtolower', $allowedRoles));

        if (!$canViewAll) {
            $authManager = Yii::$app->authManager;
            $userRoles = $authManager->getRolesByUser(Yii::$app->user->id);
            $roleNames = array_keys($userRoles);
            $canViewAll = in_array('superadmin', $roleNames) || in_array('admin', $roleNames);
        }

        $canViewClinic = false;
        $rolLower = strtolower($rol);
        if ($rolLower === 'coordinador-clinica') {
            $userClinicId = UserHelper::getMyClinicaId();
            $canViewClinic = ($userClinicId == $id);
        }

        if (!$canViewAll && !$canViewClinic) {
            Yii::$app->session->setFlash('error', 'No tiene permisos para exportar este reporte.');
            return $this->redirect(['index']);
        }

        $searchModel = new \app\models\IntermediariosByClinicaSearch();
        $query = $searchModel->getIntermediariosQuery($id, Yii::$app->request->queryParams);
        $intermediarios = $query->all();
        $summary = $searchModel->getSummary($id);

        // Build an array with affiliate counts for each intermediario
        $intermediariosWithCounts = [];
        foreach ($intermediarios as $intermediario) {
            $affiliateCount = \app\models\UserDatos::find()
                ->where(['asesor_id' => $intermediario->id])
                ->andWhere(['role' => 'afiliado'])
                ->andWhere(['is', 'deleted_at', null])
                ->count();

            $intermediariosWithCounts[] = [
                'model' => $intermediario,
                'affiliateCount' => $affiliateCount,
            ];
        }

        $logo = Yii::getAlias('@webroot/img/sispsalogo.jpg');

        $content = $this->renderPartial('_reporte_intermediarios_pdf', [
            'clinic' => $clinic,
            'intermediariosWithCounts' => $intermediariosWithCounts,
            'summary' => $summary,
            'logo' => $logo,
            'fecha_generacion' => date('d/m/Y H:i:s'),
        ]);

        $pdf = new \kartik\mpdf\Pdf([
            'mode' => \kartik\mpdf\Pdf::MODE_UTF8,
            'format' => \kartik\mpdf\Pdf::FORMAT_A4,
            'orientation' => \kartik\mpdf\Pdf::ORIENT_LANDSCAPE,
            'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
            'content' => $content,
            'cssInline' => '
                body {
                    font-family: Arial, sans-serif;
                    font-size: 9pt;
                    margin: 0;
                    padding: 10px;
                    color: #333;
                }
                .header {
                    text-align: center;
                    margin-bottom: 15px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #2c3e50;
                }
                .title {
                    font-size: 14pt;
                    font-weight: bold;
                    margin-bottom: 3px;
                    color: #2c3e50;
                }
                .subtitle {
                    font-size: 11pt;
                    margin-bottom: 3px;
                    color: #7f8c8d;
                }
                .date {
                    font-size: 9pt;
                    color: #95a5a6;
                    margin-bottom: 10px;
                }
                .summary-cards {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-between;
                    margin-bottom: 15px;
                    padding: 10px;
                    background-color: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 4px;
                }
                .summary-item {
                    flex: 1;
                    min-width: 120px;
                    text-align: center;
                    padding: 5px 10px;
                }
                .summary-item .number {
                    font-size: 16pt;
                    font-weight: bold;
                    color: #2c3e50;
                }
                .summary-item .label {
                    font-size: 8pt;
                    color: #6c757d;
                    display: block;
                }
                .table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                    font-size: 8pt;
                }
                .table th {
                    background-color: #2c3e50;
                    color: white;
                    border: 1px solid #ddd;
                    padding: 5px;
                    text-align: center;
                    font-weight: bold;
                }
                .table td {
                    border: 1px solid #ddd;
                    padding: 4px 5px;
                    text-align: left;
                }
                .table tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .text-center {
                    text-align: center;
                }
                .footer {
                    margin-top: 15px;
                    text-align: center;
                    font-size: 8pt;
                    color: #6c757d;
                    border-top: 1px solid #dee2e6;
                    padding-top: 10px;
                }
            ',
            'options' => [
                'title' => 'Intermediarios - ' . $clinic->nombre,
                'default_font_size' => 9,
                'default_font' => 'Arial',
            ],
            'methods' => [
                'SetHeader' => ['SISPSA - Intermediarios por Clínica|{DATE j-m-Y}|'],
                'SetFooter' => ['|Página {PAGENO} de {nbpg}|'],
            ]
        ]);

        return $pdf->render();
    }
}
