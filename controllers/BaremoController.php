<?php

namespace app\controllers;

use app\models\Baremo;
use app\models\BaremoSearch;
use app\models\RmClinica;
use app\models\Area; 
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile; 
use yii\web\BadRequestHttpException; 
use PhpOffice\PhpSpreadsheet\IOFactory; 
use Yii;

/**
 * BaremoController implements the CRUD actions for Baremo model.
 */
class BaremoController extends Controller
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
     * Lists all Baremo models.
     *
     * @return string
     */
    public function actionIndex($clinica_id = "")
    {
        $clinica = RmClinica::find()->where(['id' => $clinica_id])->one();
        $searchModel = new BaremoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'clinica_id', $clinica_id]);
        $model = new Baremo();

        if ($model->load($this->request->post())) {

            $model->clinica_id = $clinica_id;
            $model->estatus = "Activo";
            if($model->save()){
            }else{
                Yii::$app->session->setFlash('error', 'Error al crear el baremo');
            return $this->redirect(['index', 'clinica_id' => $clinica->id]);
            };
            Yii::$app->session->setFlash('success', 'Baremo created successfully');
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
     * Displays a single Baremo model.
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
     * Updates an existing Baremo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['index', 'clinica_id' => $model->clinica_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Baremo model.
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
     * Finds the Baremo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Baremo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Baremo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdatestatus(){
        if (Yii::$app->request->isAjax and Yii::$app->request->post()) {
            $variables = Yii::$app->request->post();

            $model = Baremo::find()->where(['id' => $variables['id']])->one();

            if($model->estatus == "Activo"){
                $model->estatus = "Inactivo";
                $model->save(false);
            }else{
                $model->estatus = "Activo";
                $model->save(false);
            }
        }
    }

    public function actionExportExcel()
    {
        $searchModel = new \app\models\BaremoSearch(); 
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false; 

        $headers = [
            'Area',
            'Nombre del Servicio',
            'Descripción',
            'Costo',
            'Precio',
            'Estatus'
        ];

        $fileName = 'baremo-export-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        $out = fopen('php://output', 'w');
        fputs($out, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); 
        fputcsv($out, $headers);

        foreach ($dataProvider->getModels() as $model) {
            $areaName = $model->area ? $model->area->nombre : "";
            $row = [
                $areaName,
                $model->nombre_servicio,
                $model->descripcion,
                $model->costo,
                $model->precio,
                $model->estatus === 1 ? 'Activo' : 'Inactivo',
            ];
            fputcsv($out, $row);
        }
        
        fclose($out);
        exit();
    }

  /**
 * Handles the upload and processing of the Excel file.
 * @param int $clinica_id
 * @return mixed
 */
/**
 * Handles the upload and processing of the Excel file with duplicate prevention.
 * @param int $clinica_id
 * @return mixed
 */
public function actionImportExcel($clinica_id)
{
    if (Yii::$app->request->isPost) {
        $file = UploadedFile::getInstanceByName('excelFile');
        if ($file) {
            // Start transaction to ensure data consistency
            $transaction = Yii::$app->db->beginTransaction();
            try {
                // --- 1. Area ID Lookup ---
                $areaMap = Area::find()
                    ->select(['id', 'nombre'])
                    ->asArray()
                    ->all();
                
                $areaIdLookup = [];
                foreach ($areaMap as $area) {
                    $areaIdLookup[strtoupper(trim($area['nombre']))] = $area['id'];
                }
                
                // --- 2. Numeric Cleaning Helper Function ---
                $cleanNumber = function ($value) {
                    if (is_numeric($value)) {
                        return (float)$value;
                    }
                    if (empty($value) || trim(strtoupper($value)) === 'NA') {
                         return 0.00; 
                    }
                    
                    // Remove currency symbols and spaces
                    $clean = trim(str_replace(['$', ' ', '€', ','], '', $value));
                    
                    return is_numeric($clean) ? (float)$clean : 0.00;
                };
                
                // --- 3. File Processing ---
                $spreadsheet = IOFactory::load($file->tempName);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, true); 

                $importedCount = 0;
                $errorCount = 0;
                $skippedCount = 0;
                $errors = [];
                
                // Skip first row (header) - your data starts at row 1
                $dataRows = array_slice($rows, 1); 
                $rowNumber = 1; // Start counting from row 2

                foreach ($dataRows as $row) {
                    $rowNumber++; 

                    // Convert to zero-indexed array
                    $row = array_values($row ?? []);

                    // Skip completely empty rows
                    if (empty(array_filter($row))) {
                        $skippedCount++;
                        continue;
                    }

                    // ⚡️ CORRECTED COLUMN MAPPING FOR YOUR EXCEL FILE ⚡️
                    $excelAreaName = strtoupper(trim($row[0] ?? ''));
                    $area_id = $areaIdLookup[$excelAreaName] ?? null;
                    $serviceName = trim($row[2] ?? '');
                    $category = trim($row[1] ?? '');
                    $description = !empty($category) ? "{$category} - {$serviceName}" : $serviceName;
                    $costo = $cleanNumber($row[3] ?? 0);
                    $precio = $cleanNumber($row[4] ?? 0);

                    // --- DUPLICATE CHECK: Check if identical record already exists ---
                    $existingBaremo = Baremo::find()
                        ->where([
                            'clinica_id' => $clinica_id,
                            'area_id' => $area_id,
                            'nombre_servicio' => $serviceName,
                            'costo' => $costo,
                            'precio' => $precio
                        ])
                        ->one();

                    if ($existingBaremo) {
                        $skippedCount++;
                        $errors[] = "Fila {$rowNumber}: Ya existe un baremo idéntico ('{$serviceName}'). Skipped.";
                        continue; // Skip this row - duplicate found
                    }

                    $baremo = new Baremo();
                    $baremo->clinica_id = $clinica_id; 
                    $baremo->area_id = $area_id;
                    $baremo->nombre_servicio = $serviceName;
                    $baremo->descripcion = $description;
                    $baremo->costo = $costo;
                    $baremo->precio = $precio;
                    $baremo->estatus = 'Activo';

                    // --- VALIDATION ---
                    if ($baremo->area_id === null && !empty($excelAreaName)) {
                         $baremo->addError('area_id', "El Área '{$excelAreaName}' no existe en la base de datos.");
                    }
                    
                    if (empty($baremo->nombre_servicio)) {
                        $baremo->addError('nombre_servicio', "El nombre del servicio está vacío.");
                    }
                    
                    if ($baremo->precio <= 0) {
                        $baremo->addError('precio', "El precio debe ser mayor a cero.");
                    }
                    
                    if ($baremo->costo < 0) {
                        $baremo->addError('costo', "El costo no puede ser negativo.");
                    }

                    if ($baremo->validate()) {
                        if ($baremo->save(false)) { 
                            $importedCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Fila " . $rowNumber . ": Error al guardar. " . implode(", ", $baremo->getFirstErrors());
                        }
                    } else {
                        $errorCount++;
                        $errors[] = "Fila " . $rowNumber . ": " . implode(", ", $baremo->getFirstErrors());
                    }
                }
                
                // Commit transaction if everything went well
                $transaction->commit();
                
                // --- Flash Messages ---
                if ($importedCount > 0 && $errorCount === 0 && $skippedCount === 0) {
                    Yii::$app->session->setFlash('success', "Se importaron correctamente {$importedCount} baremos. 🎉");
                } elseif ($importedCount > 0) {
                    $message = "Resultado de la importación:\n";
                    $message .= "✅ {$importedCount} baremos importados\n";
                    
                    if ($skippedCount > 0) {
                        $message .= "⚠️ {$skippedCount} baremos omitidos (duplicados)\n";
                    }
                    
                    if ($errorCount > 0) {
                        $message .= "❌ {$errorCount} errores encontrados\n";
                    }
                    
                    // Show first 3 errors if any
                    if ($errorCount > 0) {
                        $truncatedErrors = array_slice($errors, 0, 3);
                        $message .= "\nErrores principales:\n- " . implode("\n- ", $truncatedErrors);
                    }
                    
                    Yii::$app->session->setFlash('info', $message);
                } else {
                    Yii::$app->session->setFlash('error', "No se pudo importar ningún baremo. " . 
                        ($skippedCount > 0 ? "{$skippedCount} duplicados encontrados. " : "") .
                        "Verifique el formato del archivo.");
                }
                
            } catch (\Exception $e) {
                // Rollback transaction on error
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        } else {
            Yii::$app->session->setFlash('error', 'No se pudo cargar el archivo.');
        }
        return $this->redirect(['index', 'clinica_id' => $clinica_id]);
    }
    throw new BadRequestHttpException('Petición inválida.');
}
}