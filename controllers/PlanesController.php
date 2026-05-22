<?php

namespace app\controllers;

use app\models\Planes;
use app\models\PlanesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\RmClinica;
use app\models\Baremo;
use app\models\PlanesItemsCobertura;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\db\Expression;
use yii\db\Transaction;
use Yii;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * PlanesController implements the CRUD actions for Planes model.
 */
class PlanesController extends Controller
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
                        'import' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Planes models.
     *
     * @param string $clinica_id
     * @return string
     */
    public function actionIndex($clinica_id = "")
    {
        $clinica = RmClinica::find()->where(['id' => $clinica_id])->andWhere(['is', 'deleted_at', null])->one();
        $searchModel = new PlanesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'clinica_id', $clinica_id]);
        $model = new Planes();

        if ($model->load($this->request->post())) {

            $model->clinica_id = $clinica_id;
            $model->estatus = "Activo";
            if ($model->save()) {
            } else {
                var_dump($model->errors);
                die();
            };
            return $this->redirect(['index', 'clinica_id' => $clinica->id]);
        }


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'clinica' => $clinica,
            'model' => $model
        ]);
    }

    /**
     * Genera y descarga la plantilla de Excel para la carga masiva de Planes.
     * El archivo tiene hojas dinámicas basadas en los nombres de los planes.
     * @param string $clinica_id
     * @return yii\web\Response
     */
    public function actionDownloadTemplate($clinica_id)
    {
        // 1. Crear un nuevo objeto Spreadsheet
        $spreadsheet = new Spreadsheet();

        // ------------------------------------
        // HOJA 1: PLANS (Detalles de los Planes)
        // ------------------------------------
        $sheetPlans = $spreadsheet->getActiveSheet();
        $sheetPlans->setTitle('Plans');

        $headersPlans = [
            'A1' => 'Nombre Plan',
            'B1' => 'Descripción',
            'C1' => 'Precio',
            'D1' => 'Estatus',
            'E1' => 'Edad Límite',
            'F1' => 'Edad Mínima',
            'G1' => 'Comisión',
            'H1' => 'Cobertura',
        ];

        // Example plans - user can modify these as needed
        $exampleDataPlans = [
            [
                'A2' => 'Bronce',
                'B2' => 'Plan Básico para Individuales',
                'C2' => 16.00,
                'D2' => 'Activo',
                'E2' => 59,
                'F2' => 0,
                'G2' => 15,
                'H2' => 10000,
            ],
            [
                'A3' => 'Plata',
                'B3' => 'Plan Intermedio para Individuales',
                'C3' => 25.00,
                'D3' => 'Activo',
                'E3' => 59,
                'F3' => 0,
                'G3' => 15,
                'H3' => 15000,
            ]
        ];

        // Aplicar encabezados
        foreach ($headersPlans as $cell => $value) {
            $sheetPlans->setCellValue($cell, $value);
        }

        // Aplicar datos de ejemplo
        foreach ($exampleDataPlans as $rowData) {
            foreach ($rowData as $cell => $value) {
                $sheetPlans->setCellValue($cell, $value);
            }
        }

        // Formato para PLANS
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3498DB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheetPlans->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheetPlans->getStyle('C:C')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheetPlans->getStyle('G:G')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        // Autoajustar columnas A a H
        foreach (range('A', 'H') as $column) {
            $sheetPlans->getColumnDimension($column)->setAutoSize(true);
        }

        // ------------------------------------
        // HOJAS DE SERVICIOS DINÁMICAS (basadas en los nombres de planes)
        // ------------------------------------
        $serviceHeaders = [
            'A1' => 'Área',
            'B1' => 'Nombre del Servicio',
            'C1' => 'Descripción',
            'D1' => 'Límite',
            'E1' => 'Plazo'
        ];

        $exampleServices = [
            ['CIRUGÍA', 'Cirugías de Electivas', 'Hemorroidectomía', 'S/L', 12],
            ['CONSULTAS', 'Consultas Especializadas', 'Medicina Interna', 2, 4],
            ['CONSULTAS', 'Consultas Básicas', 'Pediatría', 'N/A', 'N/A'],
            ['LABORATORIO', 'Exámenes de Laboratorio', 'Hematología Completa', 1, 2],
        ];

        // Create service sheets for each example plan
        foreach ($exampleDataPlans as $planData) {
            $planName = $planData['A2'] ?? '';
            if (!empty($planName)) {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($planName);

                // Aplicar encabezados
                foreach ($serviceHeaders as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                }

                // Aplicar datos de ejemplo
                $row = 2;
                foreach ($exampleServices as $data) {
                    $sheet->fromArray($data, null, 'A' . $row++);
                }

                // Formato
                $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

                // Autoajustar columnas
                foreach (range('A', 'E') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            }
        }

        // 2. Guardar, Transmitir y Limpiar
        $writer = new Xlsx($spreadsheet);
        $tempFile = Yii::getAlias('@runtime/plantilla_planes_' . time() . '.xlsx');
        $writer->save($tempFile);

        $fileName = 'plantilla_planes_y_coberturas.xlsx';

        return Yii::$app->response->sendFile($tempFile, $fileName, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false
        ])
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function ($event) use ($tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            });
    }

    /**
     * Displays a single Planes model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $itemsCobertura = $model->planesItemsCoberturas;

        // Get used baremo IDs
        $usedBaremoIds = ArrayHelper::getColumn($itemsCobertura, 'baremo_id');

        // Get missing baremos
        $baremosFaltantes = Baremo::find()
            ->where(['clinica_id' => $model->clinica_id])
            ->andWhere(['not in', 'id', $usedBaremoIds])
            ->all();

        return $this->render('view', [
            'model' => $model,
            'itemsCobertura' => $itemsCobertura,
            'baremosFaltantes' => $baremosFaltantes,
        ]);
    }

    /**
     * Creates a new Planes model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Planes();
        $itemsModels = [];

        // Get baremos for the clinic if the parameter is present
        if (Yii::$app->request->get('clinica_id')) {
            $baremos = Baremo::find()->where(['clinica_id' => Yii::$app->request->get('clinica_id')])->andWhere(['estatus' => 'Activo'])->all();
            $clinica = RmClinica::find()->where(['id' => Yii::$app->request->get('clinica_id')])->one();

            // Create models for each baremo
            foreach ($baremos as $baremo) {
                $item = new PlanesItemsCobertura();
                $item->baremo_id = $baremo->id;
                $item->nombre_servicio = $baremo->nombre_servicio;
                $itemsModels[] = $item;
            }
        }

        if ($model->load(Yii::$app->request->post())) {

            $model->clinica_id = $clinica->id;

            // Save the main plan
            if ($model->save()) {
                // Process coverage items
                $itemsData = Yii::$app->request->post('PlanesItemsCobertura', []);

                foreach ($itemsData as $itemData) {

                    // Create a new instance of the model in each iteration
                    $item = new PlanesItemsCobertura();

                    // Assign model attributes directly
                    $item->cantidad_limite = $itemData['cantidad_limite'];
                    $item->plazo_espera = $itemData['plazo_espera'];
                    $item->plan_id = $model->id;
                    $item->nombre_servicio = $itemData['nombre_servicio'];
                    $item->baremo_id = $itemData['baremo_id'];

                    if (!$item->save()) {
                        echo "MODEL NOT SAVED";
                        print_r($item->getAttributes());
                        print_r($item->getErrors());
                        exit;
                    }
                }

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
            'itemsModels' => $itemsModels,
            'clinica' => $clinica
        ]);
    }

    /**
     * Updates an existing Planes model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // ONLY get existing coverage items - NOT missing ones
        $itemsModels = $model->planesItemsCoberturas;
        $clinica = RmClinica::find()->where(['id' => $model->clinica_id])->one();

        // ===== REMOVED: This block that adds ALL missing baremos =====
        // $baremosFaltantes = Baremo::find()
        //     ->where(['clinica_id' => $model->clinica_id])
        //     ->andWhere(['not in', 'id', ArrayHelper::getColumn($itemsModels, 'baremo_id')])
        //     ->all();
        //
        // foreach ($baremosFaltantes as $baremo) {
        //     $item = new PlanesItemsCobertura([
        //         'baremo_id' => $baremo->id,
        //         'nombre_servicio' => $baremo->nombre_servicio,
        //         'porcentaje_cobertura' => 80,
        //     ]);
        //     $itemsModels[] = $item;
        // }
        // ===== END REMOVED BLOCK =====

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Delete existing coverage first
                    PlanesItemsCobertura::deleteAll(['plan_id' => $model->id]);

                    // Save the new coverage items
                    $itemsData = Yii::$app->request->post('PlanesItemsCobertura', []);

                    foreach ($itemsData as $itemData) {
                        // Skip items with BOTH cantidad_limite and plazo_espera empty/NULL
                        // This prevents saving services that should NOT be in the plan
                        $cantidadLimite = !empty($itemData['cantidad_limite']) ? $itemData['cantidad_limite'] : null;
                        $plazoEspera = !empty($itemData['plazo_espera']) ? $itemData['plazo_espera'] : null;

                        // If BOTH are empty/NULL, skip saving this service (not added to plan)
                        if (empty($cantidadLimite) && empty($plazoEspera)) {
                            continue;
                        }

                        $item = new PlanesItemsCobertura();
                        $item->cantidad_limite = $cantidadLimite;
                        $item->plazo_espera = $plazoEspera;
                        $item->plan_id = $model->id;
                        $item->nombre_servicio = $itemData['nombre_servicio'];
                        $item->baremo_id = $itemData['baremo_id'];

                        if (!$item->save()) {
                            $transaction->rollBack();
                            Yii::error("Failed to save coverage item: " . print_r($item->getErrors(), true));
                            Yii::$app->session->setFlash('error', 'Error saving coverage for service: ' . $itemData['nombre_servicio']);
                            return $this->render('update', [
                                'model' => $model,
                                'itemsModels' => $itemsModels,
                                'clinica' => $clinica
                            ]);
                        }
                    }

                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Plan updated successfully');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Update error: ' . $e->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'itemsModels' => $itemsModels,
            'clinica' => $clinica
        ]);
    }

    /**
     * Deletes an existing Planes model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $clinica_id = $model->clinica_id;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            PlanesItemsCobertura::deleteAll(['plan_id' => $id]);
            $model->delete();
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Plan deleted successfully.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'Error deleting plan: ' . $e->getMessage());
        }

        return $this->redirect(['index', 'clinica_id' => $clinica_id]);
    }

    /**
     * Finds the Planes model based on its primary key value.
     * @param int $id ID
     * @return Planes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Planes::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Updates the status of an existing Planes model via AJAX.
     * @return void
     */
    public function actionUpdatestatus()
    {
        if (Yii::$app->request->isAjax and Yii::$app->request->post()) {
            $variables = Yii::$app->request->post();

            $model = Planes::find()->where(['id' => $variables['id']])->one();

            if ($model->estatus == "Activo") {
                $model->estatus = "Inactivo";
                $model->save(false);
            } else {
                $model->estatus = "Activo";
                $model->save(false);
            }
        }
    }

    /**
     * Adds a new coverage (baremo service) to an existing plan
     * @param int $plan_id ID of the plan to add coverage to
     * @param int $baremo_id ID of the baremo (service) to add
     * @return \yii\web\Response
     * @throws NotFoundHttpException If the plan or baremo do not exist
     */
    public function actionAddCobertura($plan_id, $baremo_id)
    {
        $plan = $this->findModel($plan_id);
        $baremo = Baremo::findOne($baremo_id);

        if (!$baremo) {
            Yii::$app->session->setFlash('error', 'The requested service does not exist in the system.');
            return $this->redirect(['view', 'id' => $plan_id]);
        }

        if ($baremo->clinica_id != $plan->clinica_id) {
            Yii::$app->session->setFlash('warning', 'The service does not belong to the clinic associated with this plan.');
            return $this->redirect(['view', 'id' => $plan_id]);
        }

        $existente = PlanesItemsCobertura::find()
            ->where(['plan_id' => $plan_id, 'baremo_id' => $baremo_id])
            ->one();

        if ($existente) {
            Yii::$app->session->setFlash('info', 'This service is already included in the plan.');
            return $this->redirect(['view', 'id' => $plan_id]);
        }

        $model = new PlanesItemsCobertura([
            'plan_id' => $plan_id,
            'baremo_id' => $baremo_id,
            'nombre_servicio' => $baremo->nombre_servicio,
            'porcentaje_cobertura' => 80,
            'cantidad_limite' => 1,
        ]);

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    Yii::$app->session->setFlash(
                        'success',
                        "The service <strong>{$baremo->nombre_servicio}</strong> was added to the plan successfully."
                    );
                    return $this->redirect(['view', 'id' => $plan_id]);
                } else {
                    Yii::$app->session->setFlash(
                        'error',
                        'Error saving coverage: ' . implode(', ', $model->firstErrors)
                    );
                }
            }
        } else {
            if ($model->save()) {
                Yii::$app->session->setFlash(
                    'success',
                    "The service <strong>{$baremo->nombre_servicio}</strong> was added to the plan with default values."
                );
            } else {
                Yii::$app->session->setFlash(
                    'error',
                    'Error adding service to the plan: ' . implode(', ', $model->firstErrors)
                );
            }
            return $this->redirect(['view', 'id' => $plan_id]);
        }

        return $this->render('add-cobertura', [
            'model' => $model,
            'plan' => $plan,
            'baremo' => $baremo,
        ]);
    }

    /**
     * Normalize N/A values to handle case sensitivity, spaces, and language variations
     * @param string $value The raw value from Excel
     * @return string Normalized value
     */
    private function normalizeNAValue($value)
    {
        if (empty($value)) {
            return '';
        }

        // Remove any extra spaces and convert to uppercase for consistent comparison
        $normalized = trim(strtoupper($value));

        // Common variations of N/A in different languages/notations
        $naVariations = [
            'N/A',
            'N/A ',
            ' N/A',
            'NA',
            'N.A',
            'N.A.',
            'N/A.',
            'N/D',
            'ND',
            'N.D',
            'N.D.',
            '---',
            '—',
            '-',
            'NULL',
            'NONE',
            'NINGUNO',
            'NINGUNA',
            'SIN DATO',
            'NO APLICA',
            'NO APLICABLE',
            'NOT APPLICABLE',
            'N.A.',
            'N A',
            'N-A',
            'N_A',
        ];

        // Check if the normalized value matches any N/A variation
        foreach ($naVariations as $na) {
            if ($normalized === $na) {
                return 'N/A';
            }
        }

        // Return original trimmed value (not normalized to N/A)
        return trim($value);
    }

    /**
     * Process limit value from the new format
     */
    private function processLimitValue($limitValue)
    {
        $limitValue = trim($limitValue);

        // Check if it's N/A (normalized)
        if ($limitValue === 'N/A') {
            return 0;
        }

        if ($limitValue === 'S/L') {
            return 99;
        }

        if (strpos($limitValue, '1 x Emerg') !== false) {
            return 99;
        }

        if ($limitValue === 'Criterio Med.') {
            return 99;
        }

        if ($limitValue === 'Plan Opcional') {
            return 0;
        }

        if ($limitValue === '') {
            return 0;
        }

        if (is_numeric($limitValue)) {
            return intval($limitValue);
        }

        return 1;
    }

    /**
     * Process plazo value from the new format
     */
    private function processPlazoValue($plazoValue)
    {
        $plazoValue = trim($plazoValue);

        // Check if it's N/A (normalized)
        if ($plazoValue === 'N/A') {
            return '99';
        }

        if ($plazoValue === 'Sin P/E') {
            return '0';
        }

        if ($plazoValue === 'Criterio Med.') {
            return '0';
        }

        if ($plazoValue === 'Plan Opcional') {
            return '0';
        }

        if (empty($plazoValue)) {
            return '0';
        }

        if (is_numeric($plazoValue)) {
            return (string)intval($plazoValue);
        }

        return $plazoValue;
    }

    /**
     * Parse currency values (remove $ and commas)
     */
    private function parseCurrency($value)
    {
        if (empty($value)) {
            return 0;
        }

        $cleaned = preg_replace('/[^\d.]/', '', $value);
        return floatval($cleaned);
    }

    /**
     * NEW: Initiates the import process and returns a task ID for progress tracking
     */
    public function actionImport()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $clinica_id = Yii::$app->request->post('clinica_id');
        if (empty($clinica_id)) {
            return ['success' => false, 'message' => 'Clinic ID not specified'];
        }

        $file = UploadedFile::getInstanceByName('excelFile');
        if (!$file) {
            return ['success' => false, 'message' => 'No file selected'];
        }

        $taskId = 'import_' . uniqid();
        $cache = Yii::$app->cache;

        $tempPath = Yii::getAlias('@runtime/temp_uploads/');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0777, true);
        }
        $filePath = $tempPath . $taskId . '.' . $file->extension;

        $cache->set($taskId, [
            'progress' => 5,
            'message' => 'Uploading file...',
            'finished' => false,
            'result' => null,
            'details' => [
                'plans_processed' => 0,
                'plans_total' => 0,
                'services_processed' => 0,
                'services_total' => 0,
                'current_plan' => '',
                'current_sheet' => ''
            ]
        ], 3600);

        if ($file->saveAs($filePath)) {
            $cache->set($taskId, [
                'progress' => 10,
                'message' => 'File uploaded. Starting import process...',
                'finished' => false,
                'result' => null,
                'details' => [
                    'plans_processed' => 0,
                    'plans_total' => 0,
                    'services_processed' => 0,
                    'services_total' => 0,
                    'current_plan' => '',
                    'current_sheet' => ''
                ]
            ], 3600);
        } else {
            return ['success' => false, 'message' => 'Failed to save uploaded file'];
        }

        $this->startBackgroundImport($taskId, $filePath, $clinica_id);

        return $this->asJson(['success' => true, 'taskId' => $taskId]);
    }

    /**
     * NEW: Starts the background import process
     */
    private function startBackgroundImport($taskId, $filePath, $clinica_id)
    {
        if (Yii::$app->session->isActive) {
            Yii::$app->session->close();
        }

        register_shutdown_function([$this, 'processImportBackground'], $taskId, $filePath, $clinica_id);
    }

    /**
     * NEW: Process import in background - this runs after the response is sent
     */
    public function processImportBackground($taskId, $filePath, $clinica_id)
    {
        try {
            set_time_limit(300);
            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');

            Yii::info("=== STARTING DYNAMIC IMPORT (Task: $taskId) ===", 'import');

            $this->updateProgress($taskId, 15, 'Loading spreadsheet...', [
                'current_sheet' => 'Loading file...'
            ]);

            sleep(1);

            $spreadsheet = IOFactory::load($filePath);

            $plansWorksheet = $spreadsheet->getSheetByName('Plans');
            if (!$plansWorksheet) {
                throw new \Exception('Sheet "Plans" not found');
            }

            $plansRows = $plansWorksheet->toArray();
            if (count($plansRows) < 2) {
                throw new \Exception('Plans sheet is empty');
            }

            $this->updateProgress($taskId, 20, 'Processing plans...', [
                'current_sheet' => 'Plans'
            ]);

            $importedPlans = [];
            $planNamesForServices = [];

            $headerMap = [
                'nombre' => 0,
                'descripcion' => 1,
                'precio' => 2,
                'estatus' => 3,
                'edad_limite' => 4,
                'edad_minima' => 5,
                'comision' => 6,
                'cobertura' => 7,
            ];

            $totalPlans = count($plansRows) - 1;
            $this->updateProgress($taskId, 20, "Processing $totalPlans plans...", [
                'plans_total' => $totalPlans,
                'plans_processed' => 0
            ]);

            $transaction = Yii::$app->db->beginTransaction();
            try {
                for ($i = 1; $i < count($plansRows); $i++) {
                    $row = $plansRows[$i];
                    $rowNumber = $i + 1;

                    $nombrePlan = trim($row[$headerMap['nombre']] ?? '');
                    if (empty($nombrePlan)) {
                        Yii::info("Skipping empty plan name at row $rowNumber", 'import');
                        continue;
                    }

                    $progress = 20 + round((($i / $totalPlans) * 25));
                    $this->updateProgress($taskId, $progress, "Processing plan: " . $nombrePlan, [
                        'plans_processed' => $i,
                        'current_plan' => $nombrePlan
                    ]);

                    Yii::info("Processing plan: $nombrePlan", 'import');

                    $plan = Planes::find()
                        ->where(['clinica_id' => $clinica_id, 'nombre' => $nombrePlan])
                        ->one();

                    if (!$plan) {
                        $plan = new Planes();
                        Yii::info("Creating new plan: $nombrePlan", 'import');
                    } else {
                        Yii::info("Updating existing plan: $nombrePlan", 'import');
                    }

                    $plan->nombre = $nombrePlan;
                    $plan->descripcion = trim($row[$headerMap['descripcion']] ?? '');
                    $plan->precio = floatval($row[$headerMap['precio']] ?? 0);
                    $plan->estatus = trim($row[$headerMap['estatus']] ?? 'Activo');
                    $plan->edad_limite = intval($row[$headerMap['edad_limite']] ?? 99);
                    $plan->edad_minima = intval($row[$headerMap['edad_minima']] ?? 0);
                    $plan->comision = floatval($row[$headerMap['comision']] ?? 0);
                    $plan->cobertura = trim($row[$headerMap['cobertura']] ?? '');
                    $plan->clinica_id = $clinica_id;

                    if ($plan->save()) {
                        $importedPlans[$plan->nombre] = $plan->id;
                        $planNamesForServices[] = $plan->nombre;
                        Yii::info("✅ Saved plan: {$plan->nombre} (ID: {$plan->id})", 'import');
                    } else {
                        $errors = implode(', ', $plan->getFirstErrors());
                        Yii::error("❌ Failed to save plan {$plan->nombre}: $errors", 'import');
                        throw new \Exception("Error saving plan {$plan->nombre}: $errors");
                    }

                    if ($i % 2 === 0) {
                        usleep(100000);
                    }
                }

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }

            $this->updateProgress($taskId, 45, 'Starting service processing...', [
                'plans_processed' => $totalPlans,
                'services_total' => 0,
                'services_processed' => 0
            ]);

            $servicesResult = [
                'imported' => 0,
                'skipped' => 0,
                'warnings' => []
            ];

            $totalPlansToProcessServices = count($planNamesForServices);
            $planCounter = 0;

            $totalServices = 0;
            foreach ($planNamesForServices as $planName) {
                $sheetName = $planName;
                $worksheet = $spreadsheet->getSheetByName($sheetName);
                if ($worksheet) {
                    $servicesRows = $worksheet->toArray();
                    $totalServices += max(0, count($servicesRows) - 1);
                }
            }

            $this->updateProgress($taskId, 45, "Processing $totalServices services...", [
                'services_total' => $totalServices,
                'services_processed' => 0
            ]);

            $servicesProcessedSoFar = 0;

            foreach ($planNamesForServices as $planName) {
                $sheetName = $planName;
                $planCounter++;

                Yii::info("Looking for service sheet: '$sheetName' for plan: '$planName'", 'import');

                $worksheet = $spreadsheet->getSheetByName($sheetName);

                if (!$worksheet) {
                    $servicesResult['warnings'][] = "Worksheet '$sheetName' for plan '$planName' not found in Excel file";
                    Yii::warning("Worksheet '$sheetName' for plan '$planName' not found", 'import');
                    continue;
                }

                Yii::info("Processing services for plan: $planName from sheet: $sheetName", 'import');

                $servicesRows = $worksheet->toArray();
                $servicesInThisSheet = max(0, count($servicesRows) - 1);

                $planServicesResult = $this->processPlanServicesWithProgress(
                    $servicesRows,
                    $importedPlans[$planName],
                    $clinica_id,
                    $planName,
                    $sheetName,
                    $taskId,
                    $servicesProcessedSoFar,
                    $totalServices,
                    $servicesResult
                );

                $servicesProcessedSoFar += $servicesInThisSheet;
                $servicesResult['imported'] += $planServicesResult['imported'];
                $servicesResult['skipped'] += $planServicesResult['skipped'];
                $servicesResult['warnings'] = array_merge($servicesResult['warnings'], $planServicesResult['warnings']);

                $this->cleanupMemory();
                Yii::info("Completed $planName from $sheetName: " . json_encode($planServicesResult), 'import');
            }

            $message = "¡Importación completada!<br>";
            $message .= "Planes importados: " . count($importedPlans) . "<br>";
            $message .= "Servicios en cobertura: {$servicesResult['imported']}<br>";
            $message .= "Servicios omitidos: {$servicesResult['skipped']}";

            if (!empty($servicesResult['warnings'])) {
                $message .= "<br>Advertencias: " . count($servicesResult['warnings']);
            }

            $finalResult = [
                'success' => true,
                'message' => $message,
                'debug_info' => [
                    'imported_plans' => array_keys($importedPlans),
                    'services_result' => $servicesResult
                ]
            ];

            $this->updateProgress($taskId, 100, 'Import completed successfully!', true, $finalResult);
        } catch (\Exception $e) {
            Yii::error("❌ Import error (Task: $taskId): " . $e->getMessage(), 'import');
            Yii::error("Stack trace: " . $e->getTraceAsString(), 'import');
            $finalResult = [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'detailed_error' => $e->getTraceAsString()
            ];
            $this->updateProgress($taskId, 100, 'An error occurred during import.', true, $finalResult);
        } finally {
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $finalCache = Yii::$app->cache->get($taskId);
            if ($finalCache && !$finalCache['finished']) {
                Yii::info("Force finishing task $taskId in finally block", 'import');
                $finalCache['finished'] = true;
                Yii::$app->cache->set($taskId, $finalCache, 3600);
            }
        }
    }

    /**
     * NEW: Checks the status of an ongoing import task
     */
    public function actionImportStatus($taskId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $status = Yii::$app->cache->get($taskId);

        if ($status === false) {
            return [
                'progress' => 100,
                'message' => 'Task not found or expired.',
                'finished' => true,
                'result' => [
                    'success' => false,
                    'message' => 'Task ID not found or expired.'
                ]
            ];
        }

        return $status;
    }

    /**
     * NEW: Helper function to update the progress in the cache
     */
    private function updateProgress($taskId, $progress, $message, $finished = false, $result = null, $details = [])
    {
        $currentStatus = Yii::$app->cache->get($taskId) ?: [];

        $data = [
            'progress' => min(100, intval($progress)),
            'message' => $message,
            'finished' => $finished,
            'result' => $result,
            'details' => array_merge($currentStatus['details'] ?? [], $details)
        ];
        Yii::$app->cache->set($taskId, $data, 3600);

        Yii::info("Progress Update (Task: $taskId): $progress% - $message", 'import');
    }

    /**
     * NEW: Process services for a specific plan with progress tracking
     * IMPORTANT: This method does NOT create new Baremo records - only uses existing ones
     */
    private function processPlanServicesWithProgress($servicesRows, $planId, $clinicaId, $planName, $sheetName, $taskId, $servicesProcessedSoFar, $totalServices, &$servicesResult)
    {
        $planServicesResult = [
            'imported' => 0,
            'skipped' => 0,
            'warnings' => []
        ];

        if (count($servicesRows) < 2) {
            $planServicesResult['warnings'][] = "Services sheet for '$planName' has insufficient data";
            return $planServicesResult;
        }

        $totalRows = count($servicesRows);

        // Load ONLY existing baremos for this clinic - NO CREATION ALLOWED
        $existingBaremos = Baremo::find()
            ->where(['clinica_id' => $clinicaId])
            ->indexBy(function ($baremo) {
                return $baremo->nombre_servicio . '|' . $baremo->descripcion;
            })
            ->all();

        // Pre-load existing plan services
        $existingPlanServices = PlanesItemsCobertura::find()
            ->where(['plan_id' => $planId])
            ->indexBy('baremo_id')
            ->all();

        for ($j = 1; $j < $totalRows; $j++) {
            $row = $servicesRows[$j];
            $rowNumber = $j + 1;

            $currentServiceCount = $servicesProcessedSoFar + $j;
            if ($totalServices > 0) {
                $progress = 45 + round(($currentServiceCount / $totalServices) * 50);
            } else {
                $progress = 95;
            }

            if ($j % 3 === 0 || $j === 1 || $j === $totalRows - 1) {
                $this->updateProgress($taskId, $progress, "Processing services for: $planName", [
                    'services_processed' => $currentServiceCount,
                    'current_plan' => $planName,
                    'current_sheet' => $sheetName
                ]);

                usleep(50000);
            }

            $area = trim($row[0] ?? '');
            $serviceName = trim($row[1] ?? '');
            $description = trim($row[2] ?? '');

            // Normalize limit and plazo values (handle case, spaces, language variations)
            $limitValue = $this->normalizeNAValue(trim($row[3] ?? ''));
            $plazoValue = $this->normalizeNAValue(trim($row[4] ?? ''));

            // Log the normalized values for debugging
            Yii::info("Row $rowNumber - limitValue normalized: '{$limitValue}', plazoValue normalized: '{$plazoValue}'", 'import');

            if (empty($serviceName)) {
                Yii::info("Row $rowNumber - SKIPPED: Empty service name", 'import');
                $planServicesResult['skipped']++;
                continue;
            }

            // Check if both values are N/A (after normalization)
            $isLimitNA = ($limitValue === 'N/A');
            $isPlazoNA = ($plazoValue === 'N/A');

            Yii::info("Service '{$serviceName}' - Limit is N/A: {$isLimitNA}, Plazo is N/A: {$isPlazoNA}", 'import');

            // ONLY skip (don't add to plan) if BOTH are N/A
            // This means services with N/A, N/A go to "Servicios Disponibles"
            if ($isLimitNA && $isPlazoNA) {
                Yii::info("SKIPPING service '{$serviceName}' - both values are N/A (goes to Servicios Disponibles)", 'import');
                $planServicesResult['skipped']++;
                continue;
            }

            // Otherwise, add to plan (Coberturas Incluidas)
            Yii::info("Adding service '{$serviceName}' to plan - has specific limit/plazo values", 'import');

            try {
                // ONLY use existing baremo - DO NOT CREATE NEW ONE
                $baremoKey = $serviceName . '|' . $description;
                $baremo = $existingBaremos[$baremoKey] ?? null;

                if (!$baremo) {
                    // CRITICAL: Do NOT create new baremo - just skip and warn
                    $warningMsg = "Service '{$serviceName}' with description '{$description}' not found in Baremo catalog. Please add it to Baremos first, then re-import this plan.";
                    $planServicesResult['warnings'][] = $warningMsg;
                    $planServicesResult['skipped']++;
                    Yii::warning($warningMsg, 'import');
                    continue;  // ← IMPORTANT: Skip this service entirely
                }

                // Check if this service-plan combination already exists
                $existingItem = $existingPlanServices[$baremo->id] ?? null;

                if ($existingItem) {
                    // Update existing instead of creating new
                    $item = $existingItem;
                    Yii::info("Updating existing coverage for service '{$serviceName}' in plan '{$planName}'", 'import');
                } else {
                    // Create new service coverage
                    $item = new PlanesItemsCobertura();
                    $item->plan_id = $planId;
                    $item->baremo_id = $baremo->id;
                    $item->nombre_servicio = $baremo->nombre_servicio;
                    Yii::info("Creating new coverage for service '{$serviceName}' in plan '{$planName}'", 'import');
                }

                // Set the coverage values from Excel
                $item->plazo_espera = $this->processPlazoValue($plazoValue);
                $item->cantidad_limite = $this->processLimitValue($limitValue);
                $item->porcentaje_cobertura = 100;

                if ($item->save()) {
                    $planServicesResult['imported']++;

                    // Add to cache if it's a new item
                    if (!$existingItem) {
                        $existingPlanServices[$baremo->id] = $item;
                    }
                    Yii::info("✅ Successfully added '{$serviceName}' to plan '{$planName}'", 'import');
                } else {
                    $errors = implode(', ', $item->getFirstErrors());
                    $planServicesResult['warnings'][] = "Failed to add '$serviceName' to '$planName': $errors";
                    $planServicesResult['skipped']++;
                    Yii::error("❌ Failed to add '{$serviceName}' to plan: $errors", 'import');
                }
            } catch (\Exception $e) {
                $errorMsg = "Error processing row $rowNumber for '$serviceName': " . $e->getMessage();
                $planServicesResult['warnings'][] = $errorMsg;
                $planServicesResult['skipped']++;
                Yii::error("❌ $errorMsg", 'import');
                continue;
            }

            if ($rowNumber % 10 === 0) {
                $this->cleanupMemory();
            }
        }

        Yii::info("✅ Completed processing services for plan '$planName': " . json_encode($planServicesResult), 'import');
        return $planServicesResult;
    }

    /**
     * Force memory cleanup and garbage collection
     */
    private function cleanupMemory()
    {
        if (function_exists('gc_mem_caches')) {
            gc_mem_caches();
        }
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        if (isset($GLOBALS['_SESSION'])) {
            unset($GLOBALS['_SESSION']['temp_data']);
        }
    }
}
