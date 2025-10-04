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
                        $item->porcentaje_cobertura = ""; 
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

                        // Assign model attributes directly
                        $item->porcentaje_cobertura = ""; 
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
 * Imports plans from an Excel file.
 * @return array JSON response
 */
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
        
        if (empty($servicesRows) || count($servicesRows) < 2) {
            return ['success' => false, 'message' => 'The "Services" sheet is empty or only contains a header.'];
        }

        // 4. Map Headers for Plans sheet
        $plansHeader = array_map('trim', $plansRows[0]);
        $expectedPlansHeaders = ['Nombre Plan', 'Descripción', 'Precio', 'Estatus', 'Edad Límite', 'Edad Mínima', 'Comisión', 'Cobertura'];
        
        $plansMap = [];
        foreach ($expectedPlansHeaders as $expected) {
            $index = array_search($expected, $plansHeader);
            if ($index === false) {
                return ['success' => false, 'message' => 'Required column not found in Plans sheet: "' . $expected . '"'];
            }
            $plansMap[$expected] = $index;
        }

        // 5. Map Headers for Services sheet
        $servicesHeader = array_map('trim', $servicesRows[0]);
        
        $expectedServicesHeaders = [
            'Nombre Plan',           // Plan name
            'Nombre del Servicio',   // Service name (maps to baremo.nombre_servicio)
            'Descripción',           // Service category/description (maps to baremo.descripcion)
            'Lapso',                 // Waiting period (plazo_espera)
            'Cantidad',              // Quantity limit (cantidad_limite)
        ];
        
        $servicesMap = [];
        foreach ($expectedServicesHeaders as $expected) {
            $index = array_search($expected, $servicesHeader);
            if ($index === false) {
                return ['success' => false, 'message' => 'Required column not found in Services sheet: "' . $expected . '"'];
            }
            $servicesMap[$expected] = $index;
        }

        $importedCount = 0;
        $servicesImportedCount = 0;
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
                
                // Skip empty rows
                $nombrePlan = trim($row[$plansMap['Nombre Plan']] ?? '');
                if (empty($nombrePlan)) {
                    continue;
                }
                
                // Create and save plan
                $plan = new Planes();
                
                // Mapping and casting data types
                $plan->nombre = $nombrePlan;
                $plan->descripcion = trim($row[$plansMap['Descripción']] ?? '');
                $plan->precio = floatval($row[$plansMap['Precio']] ?? 0);
                $plan->estatus = trim($row[$plansMap['Estatus']] ?? 'Activo');
                $plan->edad_limite = intval($row[$plansMap['Edad Límite']] ?? 99);
                $plan->edad_minima = intval($row[$plansMap['Edad Mínima']] ?? 0);
                $plan->comision = floatval($row[$plansMap['Comisión']] ?? 0);
                $plan->cobertura = trim($row[$plansMap['Cobertura']] ?? ''); 
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
            for ($i = 1; $i < count($servicesRows); $i++) {
                $row = $servicesRows[$i];
                $rowNumber = $i + 1;
                
                // Extract and assign variables, using the CORRECTED mapping.
                $planName = trim($row[$servicesMap['Nombre Plan']] ?? '');
                $lapso = trim($row[$servicesMap['Lapso']] ?? '0');
                $cantidad = trim($row[$servicesMap['Cantidad']] ?? '1');

                // MAPPING CORRECTION:
                // Column 'Nombre del Servicio' (Excel) -> baremo.nombre_servicio
                $serviceName = trim($row[$servicesMap['Nombre del Servicio']] ?? ''); 
                
                // Column 'Descripción' (Excel) -> baremo.descripcion (Category/Description)
                $serviceCategory = trim($row[$servicesMap['Descripción']] ?? ''); 
                
                if (empty($planName) || empty($serviceName)) {
                    continue;
                }
                
                // Find the plan ID
                if (!isset($importedPlans[$planName])) {
                    $warn = "Row $rowNumber: Plan '$planName' not found. This service was skipped.";
                    $warnings[] = $warn;
                    Yii::warning($warn, 'import');
                    continue;
                }
                
                $planId = $importedPlans[$planName];
                
                // 💡 CORRECTED Baremo Search using TRIM() and string-based condition
                $baremo = Baremo::find()
                    ->where(['clinica_id' => $clinica_id])
                    // Fix: Use string condition to compare TRIMMED column with bound parameter
                    ->andWhere('TRIM([[nombre_servicio]]) = :serviceName', [':serviceName' => $serviceName])
                    ->andWhere('TRIM([[descripcion]]) = :serviceCategory', [':serviceCategory' => $serviceCategory])
                    ->one();
                
                // Keep the warning logic if no EXACT match is found.
                if (!$baremo) {
                    $warn = "Row $rowNumber: Service '$serviceName' (Category: '$serviceCategory') not found in baremo with an EXACT match.";
                    $warnings[] = $warn;
                    Yii::warning($warn, 'import');
                    continue;
                }
                
                // Delete any existing service for this plan+baremo combination
                PlanesItemsCobertura::deleteAll(['plan_id' => $planId, 'baremo_id' => $baremo->id]);
                
                // Create new service association using data from Excel
                $item = new PlanesItemsCobertura();
                
                $item->plan_id = $planId;
                $item->baremo_id = $baremo->id;
                $item->nombre_servicio = $baremo->nombre_servicio;
                $item->plazo_espera = $lapso;
                $item->cantidad_limite = $cantidad;
                $item->porcentaje_cobertura = '100'; // Default value

                if (!$item->save()) {
                    $errorMessages = implode(', ', ArrayHelper::getColumn($item->getErrors(), 0, false));
                    $errors[] = "Row $rowNumber: Error associating service '$serviceName' to plan '$planName': " . $errorMessages;
                    Yii::error("❌ Save error: " . $errorMessages, 'import');
                    continue;
                }
                
                $servicesImportedCount++;
            }
            
            // 8. Handle results
            if (!empty($errors)) {
                $transaction->rollBack();
                $message = "Errors found. Importation reverted: " . implode('; ', array_slice($errors, 0, 5));
                return ['success' => false, 'message' => $message];
            }
            
            $transaction->commit();
            
            $successMessage = "✅ Importation completed. Plans: {$importedCount}, Services: {$servicesImportedCount}";
            
            if (!empty($warnings)) {
                $successMessage .= " | Warnings: " . count($warnings) . " services skipped due to lack of exact match in Baremo.";
                Yii::warning("Import warnings count: " . count($warnings), 'import');
            }
            
            return [
                'success' => true,
                'imported' => $importedCount,
                'services_imported' => $servicesImportedCount,
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

// REMOVE the old insertPlanServices method completely since we're now reading from Excel
}