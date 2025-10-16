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
                        'import' => ['POST'], // Ensure import only accepts POST
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
        $clinica = RmClinica::find()->where(['id' => $clinica_id])->andWhere(['is','deleted_at', null])->one();
        $searchModel = new PlanesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'clinica_id', $clinica_id]);
        $model = new Planes();

        if ($model->load($this->request->post())) {

            $model->clinica_id = $clinica_id;
            $model->estatus = "Activo";
            if($model->save()){
            }else{
                var_dump($model->errors); die();
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
            $planName = $planData['A2'] ?? ''; // Get plan name from column A
            if (!empty($planName)) {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($planName); // Use plan name as sheet name
                
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
        // Crear archivo temporal con nombre único
        $tempFile = Yii::getAlias('@runtime/plantilla_planes_' . time() . '.xlsx');
        $writer->save($tempFile);

        $fileName = 'plantilla_planes_y_coberturas.xlsx';

        // Transmitir el archivo y configurar la limpieza
        return Yii::$app->response->sendFile($tempFile, $fileName, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false // Forzar la descarga
        ])
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function ($event) use ($tempFile) {
                // Eliminar el archivo temporal después de enviarlo
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

                    // Assign model attributes directly. Removed redundant empty string assignment for porcentaje_cobertura.
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
            }else{

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
        $itemsModels = $model->planesItemsCoberturas;
        $clinica = RmClinica::find()->where(['id' => $model->clinica_id])->one();

        
        // Get missing baremos
        $baremosFaltantes = Baremo::find()
            ->where(['clinica_id' => $model->clinica_id])
            ->andWhere(['not in', 'id', ArrayHelper::getColumn($itemsModels, 'baremo_id')])
            ->all();
        
        // Create models for missing baremos
        foreach ($baremosFaltantes as $baremo) {
            $item = new PlanesItemsCobertura([
                'baremo_id' => $baremo->id,
                'nombre_servicio' => $baremo->nombre_servicio,
                'porcentaje_cobertura' => 80, // Default value
            ]);
            $itemsModels[] = $item;
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Delete existing coverage first
                    PlanesItemsCobertura::deleteAll(['plan_id' => $model->id]);
                    
                    // Save the new coverage items
                    $itemsData = Yii::$app->request->post('PlanesItemsCobertura', []);
                    
                    foreach ($itemsData as $itemData) {

                        // Create a new instance of the model in each iteration
                        $item = new PlanesItemsCobertura();

                        // Assign model attributes directly. Removed redundant empty string assignment for porcentaje_cobertura.
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
        
        // Start transaction to ensure data consistency
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // First, delete all related services in planes_items_cobertura
            PlanesItemsCobertura::deleteAll(['plan_id' => $id]);
            
            // Then delete the plan
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
     * If the model is not found, a 404 HTTP exception will be thrown.
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
    public function actionUpdatestatus(){
        if (Yii::$app->request->isAjax and Yii::$app->request->post()) {
            $variables = Yii::$app->request->post();

            $model = Planes::find()->where(['id' => $variables['id']])->one();

            if($model->estatus == "Activo"){
                $model->estatus = "Inactivo";
                $model->save(false);
            }else{
                $model->estatus = "Activo";
                $model->save(false);
            }
        }
    }

    /**
     * Adds a new coverage (baremo service) to an existing plan
     * * @param int $plan_id ID of the plan to add coverage to
     * @param int $baremo_id ID of the baremo (service) to add
     * @return \yii\web\Response
     * @throws NotFoundHttpException If the plan or baremo do not exist
     */
    public function actionAddCobertura($plan_id, $baremo_id)
    {
        // Find the plan and check for existence
        $plan = $this->findModel($plan_id);
        $baremo = Baremo::findOne($baremo_id);
        
        if (!$baremo) {
            Yii::$app->session->setFlash('error', 'The requested service does not exist in the system.');
            return $this->redirect(['view', 'id' => $plan_id]);
        }
        
        // Check if the baremo belongs to the same clinic as the plan
        if ($baremo->clinica_id != $plan->clinica_id) {
            Yii::$app->session->setFlash('warning', 'The service does not belong to the clinic associated with this plan.');
            return $this->redirect(['view', 'id' => $plan_id]);
        }
        
        // Check if this coverage already exists in the plan
        $existente = PlanesItemsCobertura::find()
            ->where(['plan_id' => $plan_id, 'baremo_id' => $baremo_id])
            ->one();
            
        if ($existente) {
            Yii::$app->session->setFlash('info', 'This service is already included in the plan.');
            return $this->redirect(['view', 'id' => $plan_id]);
        }
        
        // Create the new coverage item
        $model = new PlanesItemsCobertura([
            'plan_id' => $plan_id,
            'baremo_id' => $baremo_id,
            'nombre_servicio' => $baremo->nombre_servicio, 
            'porcentaje_cobertura' => 80, // Default value
            'cantidad_limite' => 1, // Default value
        ]);
        
        // Redirect directly or show form to complete data
        if (Yii::$app->request->isPost) {
            // If it comes via POST (creation form)
            if ($model->load(Yii::$app->request->post())) { 
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 
                        "The service <strong>{$baremo->nombre_servicio}</strong> was added to the plan successfully.");
                    return $this->redirect(['view', 'id' => $plan_id]);
                } else {
                    Yii::$app->session->setFlash('error', 
                        'Error saving coverage: ' . implode(', ', $model->firstErrors)); 
                }
            }
        } else {
            // If it comes via GET (simple link)
            if ($model->save()) {
                Yii::$app->session->setFlash('success',
                    "The service <strong>{$baremo->nombre_servicio}</strong> was added to the plan with default values.");
            } else {
                Yii::$app->session->setFlash('error',
                    'Error adding service to the plan: ' . implode(', ', $model->firstErrors));
            }
            return $this->redirect(['view', 'id' => $plan_id]);
        }
        
        // Show form to complete data if needed
        return $this->render('add-cobertura', [
            'model' => $model,
            'plan' => $plan,
            'baremo' => $baremo,
        ]);
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

        // 1. Generate a unique task ID for this import process
        $taskId = 'import_' . uniqid();
        $cache = Yii::$app->cache;

        // 2. Save the file to a temporary location
        $tempPath = Yii::getAlias('@runtime/temp_uploads/');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0777, true);
        }
        $filePath = $tempPath . $taskId . '.' . $file->extension;
        
        // 3. Set initial status in cache - IMMEDIATE FEEDBACK
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
        ], 3600); // Cache for 1 hour

        // Save file and update progress immediately
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

        // 4. Start background processing immediately
        $this->startBackgroundImport($taskId, $filePath, $clinica_id);

        return $this->asJson(['success' => true, 'taskId' => $taskId]);
    }

    /**
     * NEW: Starts the background import process
     */
    private function startBackgroundImport($taskId, $filePath, $clinica_id)
    {
        // Close the session to allow other requests
        if (Yii::$app->session->isActive) {
            Yii::$app->session->close();
        }

        // Start background processing
        register_shutdown_function([$this, 'processImportBackground'], $taskId, $filePath, $clinica_id);
    }

    /**
     * NEW: Process import in background - this runs after the response is sent
     */
    public function processImportBackground($taskId, $filePath, $clinica_id)
    {
        try {
            // Set reasonable limits for large imports
            set_time_limit(300); // 5 minutes
            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');

            Yii::info("=== STARTING DYNAMIC IMPORT (Task: $taskId) ===", 'import');
            
            $this->updateProgress($taskId, 15, 'Loading spreadsheet...', [
                'current_sheet' => 'Loading file...'
            ]);
            
            // Small delay to ensure client gets the initial progress
            sleep(1);
            
            $spreadsheet = IOFactory::load($filePath);
            
            // Get all sheets
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
            
            // Process plans first and collect their names for service sheet processing
            $importedPlans = [];
            $planNamesForServices = [];
            
            // Map headers for plans sheet
            $headerMap = [
                'nombre' => 0, // A - Nombre Plan
                'descripcion' => 1, // B - Descripción
                'precio' => 2, // C - Precio
                'estatus' => 3, // D - Estatus
                'edad_limite' => 4, // E - Edad Límite
                'edad_minima' => 5, // F - Edad Mínima
                'comision' => 6, // G - Comisión
                'cobertura' => 7, // H - Cobertura
            ];
            
            // Calculate total plans for progress tracking
            $totalPlans = count($plansRows) - 1;
            $this->updateProgress($taskId, 20, "Processing $totalPlans plans...", [
                'plans_total' => $totalPlans,
                'plans_processed' => 0
            ]);
            
            // Use transaction for plans
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Process each plan row
                for ($i = 1; $i < count($plansRows); $i++) {
                    $row = $plansRows[$i];
                    $rowNumber = $i + 1;
                    
                    $nombrePlan = trim($row[$headerMap['nombre']] ?? '');
                    if (empty($nombrePlan)) {
                        Yii::info("Skipping empty plan name at row $rowNumber", 'import');
                        continue;
                    }
                    
                    // Update progress after each plan - MORE FREQUENT UPDATES
                    $progress = 20 + round((($i / $totalPlans) * 25)); // Plans processing is ~25% of the work
                    $this->updateProgress($taskId, $progress, "Processing plan: " . $nombrePlan, [
                        'plans_processed' => $i,
                        'current_plan' => $nombrePlan
                    ]);
                    
                    Yii::info("Processing plan: $nombrePlan", 'import');
                    
                    // Find or create plan
                    $plan = Planes::find()
                        ->where(['clinica_id' => $clinica_id, 'nombre' => $nombrePlan])
                        ->one();

                    if (!$plan) {
                        $plan = new Planes();
                        Yii::info("Creating new plan: $nombrePlan", 'import');
                    } else {
                        Yii::info("Updating existing plan: $nombrePlan", 'import');
                    }

                    // Set plan attributes
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
                    
                    // Small delay to show progress more smoothly
                    if ($i % 2 === 0) {
                        usleep(100000); // 0.1 second delay every 2 plans
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

            // Process services for each plan that was imported
            $servicesResult = [
                'imported' => 0,
                'skipped' => 0,
                'warnings' => []
            ];

            $totalPlansToProcessServices = count($planNamesForServices);
            $planCounter = 0;

            // Calculate total services for progress tracking
            $totalServices = 0;
            foreach ($planNamesForServices as $planName) {
                $sheetName = $planName;
                $worksheet = $spreadsheet->getSheetByName($sheetName);
                if ($worksheet) {
                    $servicesRows = $worksheet->toArray();
                    $totalServices += max(0, count($servicesRows) - 1); // Subtract header row
                }
            }

            $this->updateProgress($taskId, 45, "Processing $totalServices services...", [
                'services_total' => $totalServices,
                'services_processed' => 0
            ]);

            $servicesProcessedSoFar = 0;

            foreach ($planNamesForServices as $planName) {
                // Use the exact plan name as the sheet name
                $sheetName = $planName;
                $planCounter++;

                Yii::info("Looking for service sheet: '$sheetName' for plan: '$planName'", 'import');

                // Get the worksheet
                $worksheet = $spreadsheet->getSheetByName($sheetName);
                
                if (!$worksheet) {
                    $servicesResult['warnings'][] = "Worksheet '$sheetName' for plan '$planName' not found in Excel file";
                    Yii::warning("Worksheet '$sheetName' for plan '$planName' not found", 'import');
                    continue;
                }

                Yii::info("Processing services for plan: $planName from sheet: $sheetName", 'import');
                
                // Get worksheet data
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
            
            // Build success message
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
            
            // Final progress update
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
            // Clean up the temporary file
            if (file_exists($filePath)) {
                unlink($filePath);
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
            return ['progress' => 100, 'message' => 'Task not found or expired.', 'finished' => true, 'result' => ['success' => false, 'message' => 'Task ID not found.']];
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
        Yii::$app->cache->set($taskId, $data, 3600); // Cache for 1 hour
        
        // Log progress for debugging
        Yii::info("Progress Update (Task: $taskId): $progress% - $message", 'import');
    }

    /**
     * NEW: Process services for a specific plan with progress tracking
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
        
        // Pre-load existing baremos for this clinic to reduce database queries
        $existingBaremos = Baremo::find()
            ->where(['clinica_id' => $clinicaId])
            ->indexBy(function($baremo) {
                return $baremo->nombre_servicio . '|' . $baremo->descripcion;
            })
            ->all();
        
        // Pre-load existing plan services to reduce database queries
        $existingPlanServices = PlanesItemsCobertura::find()
            ->where(['plan_id' => $planId])
            ->indexBy('baremo_id')
            ->all();

        // Start from row 2 (index 1) - skip header row
        for ($j = 1; $j < $totalRows; $j++) {
            $row = $servicesRows[$j];
            $rowNumber = $j + 1;

            // Update progress for each service - MORE FREQUENT UPDATES
            $currentServiceCount = $servicesProcessedSoFar + $j;
            if ($totalServices > 0) {
                $progress = 45 + round(($currentServiceCount / $totalServices) * 50); // Services processing is ~50% of the work
            } else {
                $progress = 95; // If no services, jump to near completion
            }

            // Update progress every 3 rows or at important milestones
            if ($j % 3 === 0 || $j === 1 || $j === $totalRows - 1) {
                $this->updateProgress($taskId, $progress, "Processing services for: $planName", [
                    'services_processed' => $currentServiceCount,
                    'current_plan' => $planName,
                    'current_sheet' => $sheetName
                ]);
                
                // Small delay to show progress more smoothly
                usleep(50000); // 0.05 second delay
            }

            $area = trim($row[0] ?? ''); // Column A - Área
            $serviceName = trim($row[1] ?? ''); // Column B - Nombre del Servicio
            $description = trim($row[2] ?? ''); // Column C - Descripción
            $limitValue = trim($row[3] ?? ''); // Column D - Límite
            $plazoValue = trim($row[4] ?? ''); // Column E - Plazo
            
            // Skip empty service names
            if (empty($serviceName)) {
                $planServicesResult['skipped']++;
                continue;
            }

            // Skip if both are N/A
            if ($limitValue === 'N/A' && $plazoValue === 'N/A') {
                $planServicesResult['skipped']++;
                continue;
            }

            try {
                // Find or create baremo using pre-loaded data
                $baremoKey = $serviceName . '|' . $description;
                $baremo = $existingBaremos[$baremoKey] ?? null;

                if (!$baremo) {
                    $baremo = new Baremo();
                    $baremo->clinica_id = $clinicaId;
                    $baremo->nombre_servicio = $serviceName;
                    $baremo->descripcion = $description;
                    $baremo->costo = 0;
                    $baremo->precio = 0;
                    $baremo->estatus = 'Activo';
                    
                    if (!$baremo->save()) {
                        $errors = implode(', ', $baremo->getFirstErrors());
                        $planServicesResult['warnings'][] = "Failed to create baremo for '$serviceName': $errors";
                        $planServicesResult['skipped']++;
                        continue;
                    }
                    
                    // Add to cache for future use in this batch
                    $existingBaremos[$baremoKey] = $baremo;
                }

                // Check if this service-plan combination already exists
                $existingItem = $existingPlanServices[$baremo->id] ?? null;
                    
                if ($existingItem) {
                    // Update existing instead of creating new
                    $item = $existingItem;
                } else {
                    // Create new service coverage
                    $item = new PlanesItemsCobertura();
                    $item->plan_id = $planId;
                    $item->baremo_id = $baremo->id;
                    $item->nombre_servicio = $baremo->nombre_servicio;
                }
                
                $item->plazo_espera = $this->processPlazoValue($plazoValue);
                $item->cantidad_limite = $this->processLimitValue($limitValue);
                $item->porcentaje_cobertura = 100;

                if ($item->save()) {
                    $planServicesResult['imported']++;
                    
                    // Add to cache if it's a new item
                    if (!$existingItem) {
                        $existingPlanServices[$baremo->id] = $item;
                    }
                    
                } else {
                    $errors = implode(', ', $item->getFirstErrors());
                    $planServicesResult['warnings'][] = "Failed to add '$serviceName' to '$planName': $errors";
                    $planServicesResult['skipped']++;
                }
                
            } catch (\Exception $e) {
                $errorMsg = "Error processing row $rowNumber for '$serviceName': " . $e->getMessage();
                $planServicesResult['warnings'][] = $errorMsg;
                $planServicesResult['skipped']++;
                Yii::error("❌ $errorMsg", 'import');
                continue;
            }
            
            // Memory management every 10 rows
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
        
        // Clear some global arrays if they exist
        if (isset($GLOBALS['_SESSION'])) {
            unset($GLOBALS['_SESSION']['temp_data']);
        }
    }

    /**
     * Process limit value from the new format
     */
    private function processLimitValue($limitValue)
    {
        $limitValue = trim($limitValue);
        
        if ($limitValue === 'N/A') {
            return 0; // 'N/A' for Límite → Límite = 0
        }
        
        if ($limitValue === 'S/L') {
            return 99; // 'S/L' → Límite = 99
        }
        
        if (strpos($limitValue, '1 x Emerg') !== false) {
            return 99; // '1 x Emerg' → Límite = 99
        }
        
        if ($limitValue === 'Criterio Med.') {
            return 99; // 'Criterio Med.' → Límite = 99
        }
        
        if ($limitValue === 'Plan Opcional') {
            return 0; // 'Plan Opcional' → Límite = 0
        }
        
        if ($limitValue === '') {
            return 0; // Empty → Límite = 0
        }
        
        if (is_numeric($limitValue)) {
            return intval($limitValue);
        }
        
        return 1; // Default value for unknown text
    }

    /**
     * Process plazo value from the new format
     */
    private function processPlazoValue($plazoValue)
    {
        $plazoValue = trim($plazoValue);
        
        if ($plazoValue === 'Sin P/E') {
            return '0'; // 'Sin P/E' → Plazo = 0
        }
        
        if ($plazoValue === 'N/A') {
            return '99'; // 'N/A' for Plazo → Plazo = 99
        }
        
        if ($plazoValue === 'Criterio Med.') {
            return '0'; // Medical criteria = no waiting period
        }
        
        if ($plazoValue === 'Plan Opcional') {
            return '0'; // Optional plan = no waiting period
        }
        
        if (empty($plazoValue)) {
            return '0'; // Default value for empty
        }
        
        if (is_numeric($plazoValue)) {
            return (string)intval($plazoValue);
        }
        
        return $plazoValue; // Keep original text value
    }

    /**
     * Parse currency values (remove $ and commas)
     */
    private function parseCurrency($value)
    {
        if (empty($value)) {
            return 0;
        }
        
        // Remove currency symbols and commas
        $cleaned = preg_replace('/[^\d.]/', '', $value);
        return floatval($cleaned);
    }
}