<?php

namespace app\controllers;

use app\models\DeclaracionDeSalud;
use app\models\DeclaracionDeSaludSearch;
use app\models\UserDatos;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf; // Asegúrate de que el namespace sea correcto
use Yii;

/**
 * DeclaracionDeSaludController implements the CRUD actions for DeclaracionDeSalud model.
 */
class DeclaracionDeSaludController extends Controller
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
     * Lists all DeclaracionDeSalud models.
     *
     * @return string
     */
   public function actionIndex($user_id = "")
    {
        $afiliado = UserDatos::find()->where(['id' => $user_id])->one();

        $model = new DeclaracionDeSalud();

        $searchModel = new DeclaracionDeSaludSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'user_id', $user_id]);

            if ($model->load($this->request->post())) {

                $model->user_id = $user_id;
                if($model->save()){
                }else{
                     var_dump($model->errors); die();
                };
                return $this->redirect(['index', 'user_id' => $afiliado->id]);
            }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'afiliado' => $afiliado,
            'model' => $model
        ]);
    }

    /**
     * Displays a single DeclaracionDeSalud model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
         $model = $this->findModel($id);
         $afiliado = UserDatos::find()->where(['id' => $model->user_id])->one();

        return $this->render('view', [
            'model' => $model,
            'afiliado' => $afiliado
        ]);
    }

    /**
     * Creates a new DeclaracionDeSalud model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($user_id)
    {
        $model = new DeclaracionDeSalud();
        $afiliado = UserDatos::find()->where(['id' => $user_id])->one();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['index', 'user_id' => $afiliado->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'afiliado' => $afiliado
        ]);
    }

    /**
     * Updates an existing DeclaracionDeSalud model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
         $afiliado = UserDatos::find()->where(['id' => $model->user_id])->one();

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'afiliado' => $afiliado
        ]);
    }

    /**
     * Deletes an existing DeclaracionDeSalud model.
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
     * Finds the DeclaracionDeSalud model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return DeclaracionDeSalud the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DeclaracionDeSalud::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGenerarPdf($id)
    {
        $model = $this->findModel($id);
        $afiliado = UserDatos::find()->where(['id' => $model->user_id])->one();
        
        // Obtener las preguntas (puedes copiar el mismo array de tu vista)
        $preguntas = [
            1 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES CARDIOVASCULARES: Hipertensión Arterial, infarto al Miocardio, Arritmia Cardíaca, Aneurisma, Palpitaciones, Angina de Pecho, Fiebre Reumática, Arteriesclerosis, Trastornos Valvulares, Tromboflebitis, Várices.',
            2 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES VASCULARES: Accidentes Vasculares, Hemiplejia, Parálisis, Hemorragias Cerebrales, Epilepsia o similares.',
            3 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES DE LA SANGRE: Leucemia, Sida o similares. Trastornos Valvulares, Tromboflebitis, Várices.',
            4 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES DE LAS VÍAS RESPIRATORIAS: Ronquera, tos persistente, bronquitis, asma, enfisema, tuberculosis, pleuresía, neumonía, bronconeumonía.',
            5 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES DE LAS VÍAS DIGESTIVAS: Gastritis, Úlceras, Hepatitis, Cirrosis, Hemorroides o similares, Apendicitis, colitis, Litiasis Vesicular, hernias hiatales, fisura anal.',
            6 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES DEL SISTEMA ENDOCRINO: Diabetes, Obesidad, Tiroides, Paratiroides, Bocio, Hipófisis, Alteraciones del Colesterol y Triglicéridos.',
            7 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES OSTEOMUSCULARES: Neuritis, Ciática, Reumatismo, Hernias Discales, Artritis, Osteoporosis, Desviación de la Columna Vertebral, Problemas en las Articulaciones.',
            8 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES GENITO-URINARIAS: Cálculos u otra alteración en los riñones, vejiga o próstata; prostatitis, varicocele, fimosis, parafimosis. Albúmina, sangre, pus, o infecciones en la orina.',
            9 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES DE LA PIEL, OJOS, OÍDOS, NARÍZ, GARGANTA: Desviación del Tabique Nasal, Sinusitis, Amigdalitis, Rinitis, Otitis, Cataratas, Hipertrofia de Cornetes, Timpanocentesis, Timpanoplastia o similares.',
            10 => 'Has sido diagnosticado con alguna ENFERMEDAD O DESORDEN MENTAL, DEFECTOS CONGÉNITOS O ADQUIRIDOS O SIMILARES.',
            11 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES TRANSITORIAS CRÓNICAS O ALGÚN DEFECTO NO MENCIONADOS ANTERIORMENTE.',
            12 => 'Has sido diagnosticado con alguna de las siguientes enfermedades: CÁNCER, TUMORES, GANGLIOS LINFÁTICOS INFLAMADOS, QUISTES, TUMORES BENIGNOS, ADENOMAS BENIGNOS DE LA MAMA O SIMILARES.',
            13 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES PROPIAS DE LA MUJER: Sangramiento Genital, Fibroma Uterino, Prolapso, Obstrucción en las Trompas, Ovarios Poliquísticos, Patologías Mamarias, Endometriosis, Incontinencia Urinaria, afecciones de las Glándulas Mamarias, Osteoporosis, Enfermedad Inflamatoria Pélvica, Pólipos Endometriales.',
            14 => 'Ha recibido TRANSFUSIONES DE SANGRE, QUIMIOTERAPIA, RADIOTERAPIA O SIMILARES.',
            15 => 'Le ha sido indicada o practicada alguna INTERVENCIÓN QUIRÚRGICA O SE HA SOMETIDO A TRATAMIENTO MÉDICO POR ALGUNA ENFERMEDAD O LESIÓN ADICIONAL A LAS ANTERIORES.',
            16 => 'Le ha sido diagnosticada alguna otra enfermedad o patología no mencionada anteriormente.',
        ];
        // Datos para el QR
        $qrData = "Declaración de Salud\n";
        $qrData .= "Fecha: " . date('d/m/Y') . "\n";
        $qrData .= "Afiliado: " . $afiliado->nombres . ' ' . $afiliado->apellidos . "\n";
        $qrData .= "Cédula: " . $afiliado->cedula . "\n";
        $qrData .= "ID Declaración: " . $model->id;

        $logo = Yii::getAlias('@webroot/img/sispsalogo.jpg'); 
        
        $content = $this->renderPartial('_pdf', [
            'model' => $model,
            'afiliado' => $afiliado,
            'qrData' => $qrData,
            'preguntas' => $preguntas,
            'logo' => $logo
        ]);
        
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => 'A4',
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $content,
            'cssInline' => '
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .titulo-principal { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 20px; }
                .subtitulo { font-weight: bold; margin: 15px 0 5px 0; }
                .tabla-declaracion { width: 100%; border-collapse: collapse; margin: 10px 0; }
                .tabla-declaracion td, .tabla-declaracion th { border: 1px solid #000; padding: 5px; }
                .tabla-declaracion th { background-color: #f2f2f2; text-align: center; }
                .tabla-firma { width: 100%; margin-top: 20px; }
                .tabla-firma td { padding: 5px; }
                .texto-justificado { text-align: justify; }
                .margen-superior { margin-top: 5px; }
                .negrita { font-weight: bold; }
                .centrado { text-align: center; }
                .borde-inferior { border-bottom: 1px solid #000; }
                .numero-enfermedad { font-weight: bold; }
                .qr-container { position: absolute; right: 20px; top: 20px; border: 1px solid #ddd; padding: 5px; }
                
                /* --- NUEVO ESTILO PARA EL LOGO --- */
                .logo-superior-izquierda {
                    position: absolute; /* Permite posicionar la imagen con respecto al documento */
                    top: 15px; /* Ajusta la distancia desde la parte superior */
                    left: 15px; /* Ajusta la distancia desde la parte izquierda */
                    max-width: 150px; /* Tamaño máximo para el logo */
                    height: auto; /* Mantiene la proporción */
                    z-index: 1000; /* Asegura que el logo esté por encima de otros elementos */
                }
                /* --- FIN NUEVO ESTILO --- */
            ',
            'options' => ['title' => 'Declaración de Salud'],
            'methods' => [
                'SetHeader' => ['Sistema Integral de Salud Programado Medicina Prepagada'],
                'SetFooter' => ['{PAGENO}'],
            ]
        ]);
        
        return $pdf->render();
    }


}
