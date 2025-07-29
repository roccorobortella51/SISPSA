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

        // Obtener los IDs de ubicación del modelo
        $estadoId = (int) $model->estado;
        $municipioId = (int) $model->municipio;
        $parroquiaId = (int) $model->parroquia;
        $ciudadId = (int) $model->ciudad;

        // Buscar los nombres correspondientes a los IDs
        // Usamos findOne()->nombre para obtener el nombre directamente de la base de datos
        // Si el ID es 0 o nulo, o no se encuentra, se asigna una cadena vacía
        $estadoNombre = RmEstado::findOne($estadoId)->nombre ?? '';
        $municipioNombre = RmMunicipio::findOne($municipioId)->nombre ?? '';
        $parroquiaNombre = RmParroquia::findOne($parroquiaId)->nombre ?? '';
        $ciudadNombre = RmCiudad::findOne($ciudadId)->nombre ?? '';

        // Elimina el var_dump y die() que usaste para depurar
        // var_dump ($estadoNombre); die();

        // Construir la dirección de residencia completa
        $residenceAddressParts = [];
        if (!empty($model->direccion)) $residenceAddressParts[] = $model->direccion;
        if (!empty($parroquiaNombre)) $residenceAddressParts[] = $parroquiaNombre;
        if (!empty($municipioNombre)) $residenceAddressParts[] = $municipioNombre;
        if (!empty($ciudadNombre)) $residenceAddressParts[] = $ciudadNombre;
        if (!empty($estadoNombre)) $residenceAddressParts[] = $estadoNombre;
        $fullResidenceAddress = implode(', ', array_filter($residenceAddressParts));


        // Preparar los datos para el PDF
        $data = [
            // Datos del Afiliado Propuesto
            'affiliation_type' => $model->userDatosType ? $model->userDatosType->nombre : '', // Asume relación userDatosType y campo nombre_tipo
            'proposed_affiliate_name' => $model->nombres . " " . $model->apellidos,
            'proposed_affiliate_ci' => $model->tipo_cedula . "-" . $model->cedula, // Usa tipo_cedula y cedula directamente
            'proposed_affiliate_nationality' => '', // No en UserDatos, se deja vacío
            'proposed_affiliate_marital_status' => '', // No en UserDatos, se deja vacío
            'proposed_affiliate_birthplace' => $ciudadNombre, // Usa el nombre de la ciudad resuelto
            'proposed_affiliate_birthdate' => Yii::$app->formatter->asDate($model->fechanac, 'yyyy-MM-dd'), // Formato YYYY-MM-DD
            'proposed_affiliate_sex' => $model->sexo,
            'proposed_affiliate_profession' => '', // No en UserDatos, se deja vacío
            'proposed_affiliate_occupation' => '', // No en UserDatos, se deja vacío
            'proposed_affiliate_economic_activity' => '', // No en UserDatos, se deja vacío
            'proposed_affiliate_annual_income' => '', // No en UserDatos, se deja vacío
            'proposed_affiliate_residence_address' => $fullResidenceAddress, // Dirección completa construida
            'proposed_affiliate_phone_residence' => $model->telefono, // Asume que afterFind ya lo formateó para visualización
            'proposed_affiliate_office_address' => '', // No en UserDatos, se deja vacío
            'proposed_affiliate_phone_office' => '', // No en UserDatos, se deja vacío
            'proposed_affiliate_billing_address' => $fullResidenceAddress, // Se asume igual que la de residencia si no hay campo específico
            'proposed_affiliate_cell_phone' => $model->telefono, // Se asume igual que el teléfono de residencia
            'proposed_affiliate_email' => $model->email,

            // Datos de la Parte Contratante (se dejan vacíos si no hay campos en UserDatos)
            'contracting_party_name' => '',
            'contracting_party_ci' => '',
            'contracting_party_nationality' => '',
            'contracting_party_marital_status' => '',
            'contracting_party_birthplace' => '',
            'contracting_party_birthdate' => '',
            'contracting_party_sex' => '',
            'contracting_party_profession' => '',
            'contracting_party_occupation' => '',
            'contracting_party_economic_activity' => '',
            'contracting_party_annual_income' => '',
            'contracting_party_residence_address' => '',
            'contracting_party_phone_residence' => '',
            'contracting_party_office_address' => '',
            'contracting_party_phone_office' => '',
            'contracting_party_billing_address' => '',
            'contracting_party_cell_phone' => '',
            'contracting_party_email' => '',

            // Información Corporativa (se dejan vacíos si no hay campos en UserDatos)
            'corporate_social_reason' => '',
            'corporate_rif' => '',
            'corporate_mercantile_register_number' => '',
            'corporate_tomo_number' => '',
            'corporate_registration_date' => '',
            'corporate_economic_activity' => '',
            'corporate_address' => '',
            'corporate_phone' => '',
            'corporate_products_services' => '',
            'corporate_previous_year_utility' => '',
            'corporate_net_worth' => '',

            // Representante Legal (se dejan vacíos si no hay campos en UserDatos)
            'legal_representative_name' => '',
            'legal_representative_ci' => '',
            'legal_representative_nationality' => '',
            'legal_representative_marital_status' => '',
            'legal_representative_birthplace' => '',
            'legal_representative_birthdate' => '',
            'legal_representative_sex' => '',
            'legal_representative_profession' => '',
            'legal_representative_occupation' => '',
            'legal_representative_activity_description' => '',
            'legal_representative_address' => '',
            'legal_representative_phone' => '',

            // Datos del Plan (se usan del modelo Plan relacionado)
            'plan_selected' => $model->plan ? $model->plan->nombre_plan : '', // Asume 'nombre_plan' en el modelo Planes
            'plan_currency' => '', // No en UserDatos/Planes, se deja vacío
            'plan_deductible' => '', // No en UserDatos/Planes, se deja vacío
            'plan_coverage_limit' => '', // No en UserDatos/Planes, se deja vacío
            'maternity_coverage' => false, // No en UserDatos/Planes, se deja false
            'maternity_deductible' => '', // No en UserDatos/Planes, se deja vacío
            'maternity_coverage_limit' => '', // No en UserDatos/Planes, se deja vacío

            // Grupo Familiar (se deja array vacío si no hay tabla o relación específica)
            'family_group' => [],

            // Beneficiario (se dejan vacíos si no hay campos en UserDatos)
            'beneficiary_name' => '',
            'beneficiary_ci' => '',
            'beneficiary_relationship' => '',
            'beneficiary_sex' => '',
            'beneficiary_birthdate' => '',

            // Cuenta Bancaria (se dejan vacíos si no hay campos en UserDatos)
            'bank_account_holder_name' => '',
            'bank_account_ci' => '',
            'bank_account_email' => '',
            'bank_account_type' => '',
            'bank_account_number' => '',
            'bank_name' => '',

            // Declaración
            'declaration_proposed_affiliate_name' => $model->nombres . " " . $model->apellidos,
            'declaration_proposed_affiliate_ci' => $model->tipo_cedula . "-" . $model->cedula,
            'declaration_contracting_party_name' => '', // No en UserDatos, se deja vacío
            'declaration_contracting_party_ci' => '', // No en UserDatos, se deja vacío
            'declaration_place' => $ciudadNombre, // Usa el nombre de la ciudad resuelto
            'declaration_date' => date('d/m/Y'), // Fecha actual en formato DD/MM/YYYY
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
