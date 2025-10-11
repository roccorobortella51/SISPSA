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
     * El archivo tiene dos hojas: "PLANS" y "SERVICES".
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
        $sheetPlans->setTitle('PLANS');

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

        $exampleDataPlans = [
            'A2' => 'Bronce Individual',
            'B2' => 'Plan Básico para Individuales',
            'C2' => 16.00,
            'D2' => 'Activo', // Valores válidos: 'Activo', 'Inactivo'
            'E2' => 59,
            'F2' => 0,
            'G2' => 15,
            'H2' => 10000,
        ];

        // Aplicar encabezados y datos
        foreach ($headersPlans as $cell => $value) {
            $sheetPlans->setCellValue($cell, $value);
        }
        foreach ($exampleDataPlans as $cell => $value) {
            $sheetPlans->setCellValue($cell, $value);
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
        // HOJA 2: SERVICES (Ítems de Cobertura)
        // ------------------------------------
        $sheetServices = $spreadsheet->createSheet();
        $sheetServices->setTitle('SERVICES');

        // Encabezados de 2 filas
        $sheetServices->setCellValue('A1', 'Área');
        $sheetServices->setCellValue('B1', 'Nombre del Servicio');
        $sheetServices->setCellValue('C1', 'Descripción');
        $sheetServices->setCellValue('D1', 'Costo');
        $sheetServices->setCellValue('E1', 'Precio');

        // Bloque de Planes (Ejemplo con 2 planes)
        // Se utilizan dos columnas por plan: Límite y Plazo (meses)
        $sheetServices->setCellValue('F1', 'Nombre Plan 1 (Ejemplo: Bronce Individual)');
        $sheetServices->mergeCells('F1:G1');
        $sheetServices->setCellValue('H1', 'Nombre Plan 2 (Ejemplo: Plata Individual)');
        $sheetServices->mergeCells('H1:I1');

        $sheetServices->setCellValue('F2', 'Límite');
        $sheetServices->setCellValue('G2', 'Plazo (meses)');
        $sheetServices->setCellValue('H2', 'Límite');
        $sheetServices->setCellValue('I2', 'Plazo (meses)');

        // Datos de Ejemplo de Servicios
        $exampleServices = [
            // Área, Servicio, Descripción, Costo, Precio, Límite P1, Plazo P1, Límite P2, Plazo P2
            ['CIRUGÍA', 'Cirugías de Electivas', 'Hemorroidectomía', '1507.88', '1794.93', 'N/A', 'N/A', 'S/L', 12],
            ['CONSULTAS', 'Consultas Especializadas', 'Medicina Interna', '20', '25', 'S/L', 0, 'S/L', 0],
            ['LABORATORIO', 'Exámenes de Laboratorio', 'Hematología Completa', '2.50', '3.50', 2, 0, 4, 0], // Límite de 2 o 4 veces/año
            ['ODONTOLOGÍA', 'Tratamiento odontológico', 'Tartrectomía (Limpieza Dental)', '35', '50', 1, 4, 2, 4], // Límite de 1 o 2 veces cada 4 meses
        ];

        $row = 3;
        foreach ($exampleServices as $data) {
            $sheetServices->fromArray($data, null, 'A' . $row++);
        }

        // Formato para SERVICES
        $sheetServices->getStyle('A1:I2')->applyFromArray($headerStyle);
        $sheetServices->getStyle('A1:E2')->getFill()->getStartColor()->setARGB('FF1ABC9C'); // Color verde para Baremo
        $sheetServices->getStyle('F1:I1')->getFill()->getStartColor()->setARGB('FF9B59B6'); // Color morado para Planes

        // Alineación central para las celdas de Límite/Plazo
        $sheetServices->getStyle('F:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Autoajustar columnas A a I
        foreach (range('A', 'I') as $column) {
            $sheetServices->getColumnDimension($column)->setAutoSize(true);
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

public function actionImport()
{
    // Set response format to JSON
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    // Get clinica_id from POST
    $clinica_id = Yii::$app->request->post('clinica_id');
    
    if (empty($clinica_id)) {
        return ['success' => false, 'message' => 'Clinic ID not specified'];
    }
    
    // Get the uploaded file
    $file = UploadedFile::getInstanceByName('excelFile'); 
    
    if (!$file) {
        return ['success' => false, 'message' => 'No file selected'];
    }

    try {
        // 1. Load the Excel file
        $spreadsheet = IOFactory::load($file->tempName);
        
        // 2. Get the Plans sheet
        $plansWorksheet = $spreadsheet->getSheetByName('Plans');
        
        if (!$plansWorksheet) {
            return ['success' => false, 'message' => 'The "Plans" sheet was not found in the file'];
        }
        
        $plansRows = $plansWorksheet->toArray();
        
        if (empty($plansRows) || count($plansRows) < 2) {
            return ['success' => false, 'message' => 'The "Plans" sheet is empty or only contains a header.'];
        }
        
        // 3. Get the Services sheet
        $servicesWorksheet = $spreadsheet->getSheetByName('Services');
        
        if (!$servicesWorksheet) {
            return ['success' => false, 'message' => 'The "Services" sheet was not found in the file'];
        }
        
        $servicesRows = $servicesWorksheet->toArray();
        
        if (empty($servicesRows) || count($servicesRows) < 3) {
            return ['success' => false, 'message' => 'The "Services" sheet is empty or does not have enough rows.'];
        }

        // 4. Map Headers for Plans sheet - FIXED: Handle null values
        $plansHeader = array_map(function($value) {
            return $value === null ? '' : trim($value);
        }, $plansRows[0]);
        $expectedPlansHeaders = ['Nombre Plan', 'Descripción', 'Precio', 'Estatus', 'Edad Límite', 'Edad Mínima', 'Comisión', 'Cobertura'];

        $plansMap = [];
        foreach ($expectedPlansHeaders as $expected) {
            $index = array_search($expected, $plansHeader);
            if ($index === false) {
                return ['success' => false, 'message' => 'Required column not found in Plans sheet: "' . $expected . '"'];
            }
            $plansMap[$expected] = $index;
        }

        // 5. Map Headers for Services sheet - FIXED MAPPING with null handling
        $servicesHeaderRow1 = array_map(function($value) {
            return $value === null ? '' : trim($value);
        }, $servicesRows[0]); // First header row (Area, Nombre del Servicio, etc.)
        $servicesHeaderRow2 = array_map(function($value) {
            return $value === null ? '' : trim($value);
        }, $servicesRows[1]); // Second header row (Limite, plazo, etc.)
        
        // Map the main headers from first row
        $servicesMap = [
            'Area' => array_search('Area', $servicesHeaderRow1),
            'Nombre del Servicio' => array_search('Nombre del Servicio', $servicesHeaderRow1),
            'Descripción' => array_search('Descripción', $servicesHeaderRow1),
            'Costo' => array_search('Costo', $servicesHeaderRow1),
            'Precio' => array_search('Precio', $servicesHeaderRow1),
        ];
        
        // FIXED: Manually map the plan types based on column positions
        $planTypes = [
            'Bronce Individual' => [
                'Limite' => 5,  // Column F (index 5)
                'plazo' => 6    // Column G (index 6)
            ],
            'Plata Individual' => [
                'Limite' => 7,  // Column H (index 7)
                'plazo' => 8    // Column I (index 8)
            ],
            'Oro Individual' => [
                'Limite' => 9,  // Column J (index 9)
                'plazo' => 10   // Column K (index 10)
            ],
            'Esmeralda Plus Individual' => [
                'Limite' => 11, // Column L (index 11)
                'plazo' => 12   // Column M (index 12)
            ]
        ];

        // Validate that we have the required plan types
        $requiredPlanTypes = ['Bronce Individual', 'Plata Individual', 'Oro Individual', 'Esmeralda Plus Individual'];
        foreach ($requiredPlanTypes as $planType) {
            if (!isset($planTypes[$planType]) || !isset($planTypes[$planType]['Limite']) || !isset($planTypes[$planType]['plazo'])) {
                return ['success' => false, 'message' => 'Required plan type not found or incomplete in Services sheet: "' . $planType . '"'];
            }
        }

        $importedCount = 0;
        $servicesImportedCount = 0;
        $servicesSkippedCount = 0;
        $errors = [];
        $warnings = [];
        
        // Start transaction
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // 6. First, import all plans and store them in an array for reference
            $importedPlans = []; // [plan_name => plan_id]
            
            for ($i = 1; $i < count($plansRows); $i++) {
                $row = $plansRows[$i];
                $rowNumber = $i + 1;
                
                // Skip empty rows with null handling
                $nombrePlan = $row[$plansMap['Nombre Plan']] ?? '';
                $nombrePlan = $nombrePlan === null ? '' : trim($nombrePlan);
                if (empty($nombrePlan)) {
                    continue;
                }
                
                // Check if plan already exists by name and clinic ID
                $plan = Planes::find()
                    ->where(['clinica_id' => $clinica_id, 'nombre' => $nombrePlan])
                    ->one();

                // If plan doesn't exist, create a new instance
                if ($plan === null) {
                    $plan = new Planes();
                }
                
                // Mapping and casting data types with null handling
                $plan->nombre = $nombrePlan;
                $plan->descripcion = $row[$plansMap['Descripción']] ?? '';
                $plan->descripcion = $plan->descripcion === null ? '' : trim($plan->descripcion);
                $plan->precio = floatval($row[$plansMap['Precio']] ?? 0);
                $plan->estatus = $row[$plansMap['Estatus']] ?? 'Activo';
                $plan->estatus = $plan->estatus === null ? 'Activo' : trim($plan->estatus);
                $plan->edad_limite = intval($row[$plansMap['Edad Límite']] ?? 99);
                $plan->edad_minima = intval($row[$plansMap['Edad Mínima']] ?? 0);
                $plan->comision = floatval($row[$plansMap['Comisión']] ?? 0);
                $plan->cobertura = $row[$plansMap['Cobertura']] ?? '';
                $plan->cobertura = $plan->cobertura === null ? '' : trim($plan->cobertura);
                $plan->clinica_id = $clinica_id;

                if (!$plan->save()) {
                    $errorMessages = implode(', ', ArrayHelper::getColumn($plan->getErrors(), 0, false));
                    $errors[] = "Row $rowNumber (Plan: {$plan->nombre}): " . $errorMessages;
                    continue;
                }
                
                // Store plan reference for services association
                $importedPlans[$plan->nombre] = $plan->id;
                $importedCount++;
            }
            
            // 7. Now import services for each plan from Services sheet
            for ($i = 2; $i < count($servicesRows); $i++) { // Start from row 3 (index 2)
                $row = $servicesRows[$i];
                $rowNumber = $i + 1;
                
                // Extract service information with null handling
                $area = $row[$servicesMap['Area']] ?? '';
                $area = $area === null ? '' : trim($area);
                $serviceName = $row[$servicesMap['Nombre del Servicio']] ?? '';
                $serviceName = $serviceName === null ? '' : trim($serviceName);
                $description = $row[$servicesMap['Descripción']] ?? '';
                $description = $description === null ? '' : trim($description);
                
                // Skip if service name is empty
                if (empty($serviceName)) {
                    continue;
                }
                
                // Process each plan type for this service
                foreach ($requiredPlanTypes as $planType) {
                    $planName = $planType;
                    
                    // Check if plan was imported
                    if (!isset($importedPlans[$planName])) {
                        $warn = "Row $rowNumber: Plan '$planName' not found. Service '$serviceName' skipped for this plan.";
                        $warnings[] = $warn;
                        Yii::warning($warn, 'import');
                        $servicesSkippedCount++;
                        continue;
                    }
                    
                    $planId = $importedPlans[$planName];
                    
                    // Get limit and plazo values for this plan type using fixed column indices with null handling
                    $limitIndex = $planTypes[$planType]['Limite'];
                    $plazoIndex = $planTypes[$planType]['plazo'];
                    
                    $limitValue = isset($row[$limitIndex]) ? $row[$limitIndex] : '';
                    $limitValue = $limitValue === null ? '' : trim($limitValue);
                    $plazoValue = isset($row[$plazoIndex]) ? $row[$plazoIndex] : '';
                    $plazoValue = $plazoValue === null ? '' : trim($plazoValue);
                    
                    // NEW CONDITION: Skip if BOTH Límite and Plazo are 'N/A'
                    if ($limitValue === 'N/A' && $plazoValue === 'N/A') {
                        Yii::info("⏭️ Skipping service '$serviceName' for plan '$planName' - both Límite and Plazo are 'N/A'", 'import');
                        $servicesSkippedCount++;
                        continue;
                    }
                    
                    Yii::info("=== PROCESSING SERVICE ===", 'import');
                    Yii::info("Service: '$serviceName'", 'import');
                    Yii::info("Plan: $planType", 'import');
                    Yii::info("Limit: '$limitValue', Plazo: '$plazoValue'", 'import');
                    
                    // Find existing baremo - use more flexible matching
$baremo = Baremo::find()
    ->where(['clinica_id' => $clinica_id])
    ->andWhere(['estatus' => 'Activo'])
    ->andWhere([
        'nombre_servicio' => $serviceName,
        'descripcion' => $description
    ])
    ->one();

// Only create new baremo if NO existing one found AND it's really necessary
if (!$baremo && $this->isServiceRequired($serviceName)) {
    $baremo = new Baremo();
    $baremo->clinica_id = $clinica_id;
    $baremo->nombre_servicio = $serviceName;
    $baremo->descripcion = $description;
    
    // Process cost and price values (remove $ and commas) with null handling
    $costoValue = isset($row[$servicesMap['Costo']]) ? $row[$servicesMap['Costo']] : '0';
    $costoValue = $costoValue === null ? '0' : trim($costoValue);
    $precioValue = isset($row[$servicesMap['Precio']]) ? $row[$servicesMap['Precio']] : '0';
    $precioValue = $precioValue === null ? '0' : trim($precioValue);
    
    $baremo->costo = $this->parseCurrency($costoValue);
    $baremo->precio = $this->parseCurrency($precioValue);
    $baremo->estatus = 'Activo';
    
    if (!$baremo->save()) {
        $warn = "Row $rowNumber: Could not create baremo for service '$serviceName'. Error: " . implode(', ', $baremo->getFirstErrors());
        $warnings[] = $warn;
        Yii::warning($warn, 'import');
        $servicesSkippedCount++;
        continue;
    }
    
    Yii::info("✅ Created new baremo: $serviceName", 'import');
} elseif (!$baremo) {
    // Skip if no baremo found and not required
    $warn = "Row $rowNumber: No existing baremo found for service '$serviceName' and service not required. Skipped.";
    $warnings[] = $warn;
    Yii::warning($warn, 'import');
    $servicesSkippedCount++;
    continue;
}
                    
                    // Delete any existing service for this plan+baremo combination
                    PlanesItemsCobertura::deleteAll(['plan_id' => $planId, 'baremo_id' => $baremo->id]);
                    
                    // Create new service association
                    $item = new PlanesItemsCobertura();
                    
                    $item->plan_id = $planId;
                    $item->baremo_id = $baremo->id;
                    $item->nombre_servicio = $baremo->nombre_servicio;
                    
                    // Process values with the NEW conditions
                    $item->plazo_espera = $this->processPlazoValue($plazoValue);
                    $item->cantidad_limite = $this->processLimitValue($limitValue);
                    $item->porcentaje_cobertura = 100;

                    if (!$item->save()) {
                        $errorMessages = implode(', ', ArrayHelper::getColumn($item->getErrors(), 0, false));
                        $errors[] = "Row $rowNumber: Error associating service '$serviceName' to plan '$planName': " . $errorMessages;
                        Yii::error("❌ Save error: " . $errorMessages, 'import');
                        $servicesSkippedCount++;
                        continue;
                    }
                    
                    $servicesImportedCount++;
                    Yii::info("✅ Successfully imported service '$serviceName' for plan '$planName'", 'import');
                }
            }
            
            // 8. Handle results
            if (!empty($errors)) {
                $transaction->rollBack();
                $message = "Errors found. Importation reverted: " . implode('; ', array_slice($errors, 0, 5));
                return ['success' => false, 'message' => $message];
            }
            
            $transaction->commit();
            
            // Build success message with all counts
            $successMessage = "¡Importación Exitosa!<br>";
            $successMessage .= "Se importaron {$importedCount} planes correctamente.<br>";
            $successMessage .= "Se importaron {$servicesImportedCount} servicios en planes_items_cobertura correctamente.<br>";
            $successMessage .= "Se omitieron {$servicesSkippedCount} combinaciones servicio-plan.";
            
            if (!empty($warnings)) {
                $successMessage .= "<br>Advertencias: " . count($warnings) . " servicios tuvieron problemas durante la importación.";
                Yii::warning("Import warnings count: " . count($warnings), 'import');
            }
            
            return [
                'success' => true,
                'imported' => $importedCount,
                'services_imported' => $servicesImportedCount,
                'services_skipped' => $servicesSkippedCount,
                'warnings_count' => count($warnings),
                'message' => $successMessage
            ];
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("❌ Transaction error: " . $e->getMessage(), 'import');
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
        
    } catch (\Exception $e) {
        Yii::error("❌ File load error: " . $e->getMessage(), 'import');
        return ['success' => false, 'message' => 'File read error: ' . $e->getMessage()];
    }
}

/**
 * Determine if a service should be created if not found
 * You can customize this logic based on your business rules
 */
private function isServiceRequired($serviceName)
{
    // List of services that should always be created
    $requiredServices = [
        // Add critical service names here that must exist
    ];
    
    // Or create all services by default (current behavior)
    return true;
    
    // Or be more restrictive:
    // return in_array($serviceName, $requiredServices);
}

/**
 * Process limit value from the new format - UPDATED WITH NEW CONDITIONS
 */
private function processLimitValue($limitValue)
{
    $limitValue = trim($limitValue);
    
    // NEW CONDITIONS:
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
    
    // New processLimitValue (AFTER the last fix)
    if ($limitValue === '') {
        return null; // 💡 Signal to the main import loop to SKIP
}
    
    if (is_numeric($limitValue)) {
        return intval($limitValue);
    }
    
    return 1; // Default value for unknown text
}

/**
 * Process plazo value from the new format - UPDATED WITH NEW CONDITIONS
 */
private function processPlazoValue($plazoValue)
{
    $plazoValue = trim($plazoValue);
    
    // NEW CONDITIONS:
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
 * Parse currency values (remove $ and commas) Test
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