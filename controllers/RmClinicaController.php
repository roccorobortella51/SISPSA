<?php

namespace app\controllers;

use app\models\RmClinica;
use app\models\RmClinicaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

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

    /**
     * Lists all RmClinica models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new RmClinicaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
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
        return $this->render('view', [
            'model' => $this->findModel($id),
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

        // >>>>>>>>>>>>>>>>>>> REVISA ESTAS LÍNEAS <<<<<<<<<<<<<<<<<<<<<<
        // Inicializa estas variables como arrays vacíos.
        // Esto evita el error "Undefined variable" en la vista.
        $listaEstados = [];
        $listaEstatus = [];
        // >>>>>>>>>>>>>>>>>>> FIN DE REVISIÓN <<<<<<<<<<<<<<<<<<<<<<<<<<

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Clínica creada exitosamente.');
                // Después de crear, puedes redirigir al 'update' para ver la clínica creada con todas las pestañas,
                // o al 'index' si quieres volver a la lista principal.
                // Por ahora, te sugiero redirigir a 'update' ya que tienes la funcionalidad allí.
                return $this->redirect(['update', 'id' => $model->id, 'tab' => 0]);
            } else {
                Yii::$app->session->setFlash('danger', 'Error al crear la clínica. Por favor, revise los datos.');
            }
        } else {
            // Carga los valores por defecto del modelo si es la primera vez que se carga el formulario
            $model->loadDefaultValues();
        }

        return $this->render('create', [ // <--- Aquí le decimos que use la vista 'create.php'
            'model' => $model,
            'listaEstados' => $listaEstados, // Se pasan a la vista
            'listaEstatus' => $listaEstatus,   // Se pasan a la vista
            'mode' => 'create', // Pasamos un 'modo' para uso en el formulario si es necesario
            'isNewRecord' => true, // Indicamos que es un nuevo registro
        ]);
    }

    /**
     * Updates an existing RmClinica model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    /**
     * Actualiza un modelo RmClinica existente y maneja las pestañas de edición.
     * @param int $id El ID de la clínica a actualizar.
     * @param int $tab El índice de la pestaña a mostrar (por defecto 0 para 'Datos de la Clínica').
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException si el modelo no puede ser encontrado.
     */
    public function actionUpdate($id, $tab = 0)
    {
        $model = $this->findModel($id);

        // --- Carga de datos comunes o necesarios para las pestañas ---
        // (Estas listas son específicamente para la pestaña 0, pero las cargamos aquí
        // porque la vista principal update.php se las pasa al _form)
        $listaEstados = [
            'Distrito Capital' => 'Distrito Capital',
            'Miranda' => 'Miranda',
            'La Guaira' => 'La Guaira',
            // ... Agrega todos tus estados reales aquí
        ];
        $listaEstatus = [
            'ACTIVO' => 'Activo',
            'INACTIVO' => 'Inactivo',
            'PENDIENTE' => 'Pendiente',
            // ... Agrega otros estatus si los tienes
        ];

        // --- Lógica de procesamiento de formularios por pestaña ---
        // Pestaña 0: Datos de la Clínica (el formulario principal)
        if ($tab == 0 && $this->request->isPost && $model->load($this->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Datos de la clínica actualizados exitosamente.');
                return $this->redirect(['update', 'id' => $model->id, 'tab' => 0]); // Redirige a la misma pestaña
            } else {
                Yii::$app->session->setFlash('danger', 'Error al actualizar los datos de la clínica. Revise el formulario.');
            }
        }
        // Puedes añadir más lógica POST aquí para otras pestañas a medida que las desarrolles.
        // Por ejemplo:
        // if ($tab == 1 && $this->request->isPost && $someOtherModel->load($this->request->post())) { ... }

        // --- Renderiza la vista principal con los datos necesarios ---
        return $this->render('update', [
            'model' => $model,
            'tab' => $tab, // Pasa el índice de la pestaña activa a la vista
            'listaEstados' => $listaEstados, // Pasa listas para el _form
            'listaEstatus' => $listaEstatus, // Pasa listas para el _form
            // Puedes añadir aquí otros datos que necesites pasar a las sub-vistas
            // 'dataForBaremoTab' => $someBaremoData,
            // 'dataForPlanesTab' => $somePlanesData,
        ]);
    }

    /**
     * Finds the RmClinica model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return RmClinica the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */

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
}
