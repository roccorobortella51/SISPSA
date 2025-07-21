<?php

namespace app\controllers;

use Yii;
use app\models\UserDatos;
use app\models\User;
use app\models\UserDatosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\UserHelper;
use app\models\RmMunicipio;
use app\models\RmParroquia;
use app\models\RmCiudad;
use app\models\RmEstado;
use app\models\Contratos;
use app\models\RmClinica;
use app\models\Planes;
use yii\base\Security;
use kartik\mpdf\Pdf;
use yii\helpers\ArrayHelper;

/**
 * UserDatosController implements the CRUD actions for UserDatos model.
 */
class UserDatosController extends Controller
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
     * Lists all UserDatos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['ilike', 'role', 'Afiliado']);
        

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexClinicas($clinica_id = "")
    {
        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'clinica_id', $clinica_id]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single UserDatos model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $estado = $model->estado;
        $municipio = $model->municipio;
        $parroquia = $model->parroquia;
        $ciudad = $model->ciudad;

        if (!empty($estado) && is_numeric($estado)) {
            $estadoModel = RmEstado::findOne($estado);
            $estado = $estadoModel ? $estadoModel->nombre : $estado;
        }
        if (!empty($municipio) && is_numeric($municipio)) {
            $municipioModel = RmMunicipio::findOne($municipio);
            $municipio = $municipioModel ? $municipioModel->nombre : $municipio;
        }
        if (!empty($parroquia) && is_numeric($parroquia)) {
            $parroquiaModel = RmParroquia::findOne($parroquia);
            $parroquia = $parroquiaModel ? $parroquiaModel->nombre : $parroquia;
        }
        if (!empty($ciudad) && is_numeric($ciudad)) {
            $ciudadModel = RmCiudad::findOne($ciudad);
            $ciudad = $ciudadModel ? $ciudadModel->nombre : $ciudad;
        }
        

        return $this->render('view', [
            'model' => $model,
            'estado' => $estado,
            'municipio' => $municipio,
            'parroquia' => $parroquia,
            'ciudad' => $ciudad,    
        ]);
    }

    /**
     * Creates a new UserDatos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $modelUser = new User();
        $model = new UserDatos();
        $modelContrato = new Contratos();
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->codigoValidacion = UserHelper::getInstance()->generarCodigoValidacion(); //generar codigo de validacion de 6 digitos
        $model->role = 'afiliado';
        $model->estatus = 'Creado';

        if($model->estatus_solvente == "" || $model->estatus_solvente == null){
            $model->estatus_solvente = "No";
        }


        //if ($this->request->isPost) {
            if ($model->load($this->request->post()) ) {


                if($model->save()){
            
                    // Asignar el username generado al modelo de usuario
                    $modelUser->username = $model->email;;
                    //var_dump($modelUser->username);exit;
                    $pass = 'sispsa'.$model->cedula;//Yii::$app->security->generateRandomString(8);
                    $modelUser->password_hash = User::setPassword($pass);
                    $modelUser->auth_key = User::generateAuthKey();
                    $modelUser->email = $model->email;
                    $modelUser->status = 1;
                    if($modelUser->save()){
                        
                        
                        $modelContrato->user_id = $model->id;
                        $modelContrato->estatus = 'Registrado';
                        $modelContrato->clinica_id = $model->clinica_id;
                        $modelContrato->save();                     
                        $auth = Yii::$app->authManager;
                        $roleName = 'afiliado';
                        $role = $auth->getRole($roleName);
                        if ($role) {
                            try {
                                $auth->revokeAll($modelUser->id);
                                $auth->assign($role, $modelUser->id);
                                Yii::$app->cache->flush();
                                $model->user_login_id = $modelUser->id;
                                $model->save();
                                
                            } catch (\Exception $e) {
                                Yii::error("Error al asignar el rol: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
                            }
                        } else {
                            Yii::$app->session->setFlash('warning', "El rol '$roleName' no existe. Usuario creado, pero el rol no pudo ser asignado.");
                        }
                        return $this->redirect(['update', 'id' => $model->id]);
                    }
                    else{
                        var_dump($modelUser->errors);
                        exit;
                    }
                }else{
                    var_dump($model->errors);
                    exit;
                }
                }
                
           // }
        /*} else {
            $model->loadDefaultValues();
        }*/

        return $this->render('create', [
            'model' => $model,
            'modelContrato' => $modelContrato,
        ]);
    }

    /**
     * Updates an existing UserDatos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {   
        $model = $this->findModel($id);
        $modelContrato = Contratos::find()->where(['user_id' => $id])->one();
        if ($modelContrato === null) {
            $modelContrato = new Contratos();
            // Puedes asignar otros valores por defecto si es necesario para un nuevo contrato
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $modelContrato->load($this->request->post())) {



                if($model->user_login_id == "" || $model->user_login_id == null){

                    $modelUser = new User();
                    $modelUser->username = $model->email;
                    $pass = 'sispsa'.$model->cedula;
                    $modelUser->password_hash = User::setPassword($pass);
                    $modelUser->auth_key = User::generateAuthKey();
                    $modelUser->email = $model->email;
                    $modelUser->status = 1;
                    $modelUser->save();
                    $model->user_login_id = $modelUser->id;
                }


                if($model->estatus_solvente == "" || $model->estatus_solvente == null){
                    $model->estatus_solvente = "No";
                }

                $model->role = 'afiliado';
                $model->estatus = 'Registrado';

                $model->updated_at = date('Y-m-d H:i:s');


                if($model->save()){
                    $modelContrato->user_id = $id;
                    $modelContrato->estatus = 'Creado';
                    $modelContrato->clinica_id = $model->clinica_id;


                    if($modelContrato->save()){

                        $auth = Yii::$app->authManager;
                        $roleName = 'afiliado';
                        $role = $auth->getRole($roleName);
                        if ($role) {
                            try {
                                $auth->revokeAll($modelUser->id);
                                $auth->assign($role, $modelUser->id);
                                Yii::$app->cache->flush();
                                $model->user_login_id = $modelUser->id;
                                $model->save();
                                
                            } catch (\Exception $e) {
                                Yii::error("Error al asignar el rol: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
                            }
                        } else {
                            Yii::$app->session->setFlash('warning', "El rol '$roleName' no existe. Usuario creado, pero el rol no pudo ser asignado.");
                        }

                        return $this->redirect(['update', 'id' => $model->id]);
                    }else{

                        echo "MODEL CONTRATO NOT SAVED";
                      print_r($modelContrato->getAttributes());
                      print_r($modelContrato->getErrors());
                      exit;

                    }
                }else{
                    echo "MODEL NOT SAVED";
                      print_r($model->getAttributes());
                      print_r($model->getErrors());
                      exit;
                }
        }

        return $this->render('update', [
            'model' => $model,
            'modelContrato' => $modelContrato,
        ]);
    }

    /**
     * Deletes an existing UserDatos model.
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
     * Finds the UserDatos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return UserDatos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserDatos::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionIndexByAfiliado($asesor_id = "")
    {
        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'asesor_id', $asesor_id]);
    
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionGenerarContratov($id)
    {

        $model = $this->findModel($id);
         // Get your data, for example, from a form submission or database
        $data = [
            'affiliation_type' => '',
            'proposed_affiliate_name' => $model->nombres." ".$model->apellidos,
            'proposed_affiliate_ci' => $model->tipo_cedula."-".$model->cedula,
            'proposed_affiliate_nationality' => 'Venezuelan',
            'proposed_affiliate_marital_status' => 'Single',
            'proposed_affiliate_birthplace' => 'Caracas',
            'proposed_affiliate_birthdate' => '1990-01-01',
            'proposed_affiliate_sex' => 'Male',
            'proposed_affiliate_profession' => 'Engineer',
            'proposed_affiliate_occupation' => 'Software Engineer',
            'proposed_affiliate_economic_activity' => 'Independent',
            'proposed_affiliate_annual_income' => 'De 6 a 10 Salarios mínimos',
            'proposed_affiliate_residence_address' => 'Av. Francisco de Miranda, Edificio XYZ, Caracas',
            'proposed_affiliate_phone_residence' => '0212-1234567',
            'proposed_affiliate_office_address' => 'Calle A, Edificio B, Oficina C, Caracas',
            'proposed_affiliate_phone_office' => '0212-7654321',
            'proposed_affiliate_billing_address' => 'Av. Francisco de Miranda, Edificio XYZ, Caracas',
            'proposed_affiliate_cell_phone' => '0412-9876543',
            'proposed_affiliate_email' => 'john.doe@example.com',

            'contracting_party_name' => 'Jane Smith',
            'contracting_party_ci' => 'V-87654321',
            'contracting_party_nationality' => 'Venezuelan',
            'contracting_party_marital_status' => 'Married',
            'contracting_party_birthplace' => 'Maracaibo',
            'contracting_party_birthdate' => '1985-05-10',
            'contracting_party_sex' => 'Female',
            'contracting_party_profession' => 'Doctor',
            'contracting_party_occupation' => 'General Practitioner',
            'contracting_party_economic_activity' => 'Professional',
            'contracting_party_annual_income' => 'De 11 a 20 Salarios mínimos',
            'contracting_party_residence_address' => 'Av. Libertador, Edificio ABC, Caracas',
            'contracting_party_phone_residence' => '0212-2345678',
            'contracting_party_office_address' => 'Calle D, Edificio E, Oficina F, Caracas',
            'contracting_party_phone_office' => '0212-8765432',
            'contracting_party_billing_address' => 'Av. Libertador, Edificio ABC, Caracas',
            'contracting_party_cell_phone' => '0414-1234567',
            'contracting_party_email' => 'jane.smith@example.com',

            'corporate_social_reason' => 'Empresa Ejemplo C.A.',
            'corporate_rif' => 'J-123456789',
            'corporate_mercantile_register_number' => '12345',
            'corporate_tomo_number' => '123-A',
            'corporate_registration_date' => '2000-01-01',
            'corporate_economic_activity' => 'Comercial',
            'corporate_address' => 'Av. Principal, Edificio GHI, Caracas',
            'corporate_phone' => '0212-3456789',
            'corporate_products_services' => 'Consulting and IT Services',
            'corporate_previous_year_utility' => '1,500,000.00',
            'corporate_net_worth' => '5,000,000.00',

            'legal_representative_name' => 'Roberto Perez',
            'legal_representative_ci' => 'V-98765432',
            'legal_representative_nationality' => 'Venezuelan',
            'legal_representative_marital_status' => 'Married',
            'legal_representative_birthplace' => 'Valencia',
            'legal_representative_birthdate' => '1975-11-20',
            'legal_representative_sex' => 'Male',
            'legal_representative_profession' => 'Lawyer',
            'legal_representative_occupation' => 'Legal Manager',
            'legal_representative_activity_description' => 'Dependiente',
            'legal_representative_address' => 'Calle Larga, Casa 10, Valencia',
            'legal_representative_phone' => '0424-5678901',

            'plan_selected' => 'DIAMANTE', // Or ORO, PLATA, PLATINO, ESMERALDA, BASICO, BRONCE, ESMERALDA PLUS
            'plan_currency' => 'BS',
            'plan_deductible' => '500',
            'plan_coverage_limit' => '100000',
            'maternity_coverage' => true, // true or false
            'maternity_deductible' => '100',
            'maternity_coverage_limit' => '5000',

            'family_group' => [
                ['name' => 'Child One', 'ci' => 'V-11111111', 'relationship' => 'Son', 'sex' => 'Male', 'birthdate' => '2010-03-15'],
                ['name' => 'Child Two', 'ci' => 'V-22222222', 'relationship' => 'Daughter', 'sex' => 'Female', 'birthdate' => '2012-07-20'],
            ],

            'beneficiary_name' => 'Another Beneficiary',
            'beneficiary_ci' => 'V-33333333',
            'beneficiary_relationship' => 'Spouse',
            'beneficiary_sex' => 'Female',
            'beneficiary_birthdate' => '1990-01-01',

            'bank_account_holder_name' => 'John Doe',
            'bank_account_ci' => 'V-12345678',
            'bank_account_email' => 'john.doe@example.com',
            'bank_account_type' => 'Cuenta Corriente', // Or Cuenta Ahorro, Tarjeta Crédito Visa, Tarjeta Crédito MasterCard
            'bank_account_number' => '01020304050607080910',
            'bank_name' => 'Banco Nacional de Crédito',

            'declaration_proposed_affiliate_name' => 'John Doe',
            'declaration_proposed_affiliate_ci' => 'V-12345678',
            'declaration_contracting_party_name' => 'Jane Smith',
            'declaration_contracting_party_ci' => 'V-87654321',
            'declaration_place' => 'Caracas',
            'declaration_date' => '19/07/2025',
        ];

        $logo = Yii::getAlias('@webroot/img/sispsalogo.jpg'); 

        $firmas = Yii::getAlias('@webroot/img/firmas.png'); 

        // Render the HTML content for the PDF
        $content = $this->renderPartial('_contrato_pdf', [
            'data' => $data,
            'logo' => $logo,
            'firmas' => $firmas
        ]);

         $url_css = Yii::getAlias('@webroot') . '/css/affiliation-pdf.css';


      

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_LETTER,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $content,
            'cssFile' => $url_css, // Pasa la ruta absoluta aquí
            'options' => [
                'title' => 'Solicitud de Afiliación SISPSA',
            ],
            'methods' => [
                'SetHeader' => false,
                'SetFooter' => ['{PAGENO}'],
            ]
        ]);

        return $pdf->render();
    }

    public function actionGetCorporativeAffiliates($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = UserDatos::find()
                ->where(['user_datos_type_id' => 2]) // Asume que ID 2 es 'Corporativo'
                ->andFilterWhere(['ilike', 'nombres', $q])
                ->orFilterWhere(['ilike', 'apellidos', $q])
                ->limit(20); // Limita los resultados

            $command = $query->createCommand();
            $data = $command->queryAll();

            $out['results'] = array_values(ArrayHelper::map($data, 'id', function($item) {
                return $item['nombres'] . ' ' . $item['apellidos'] . ' (' . $item['cedula'] . ')';
            }));
        }
        return $out;
    }
    
}
