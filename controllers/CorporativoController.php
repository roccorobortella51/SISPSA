<?php

namespace app\controllers;

use Yii;
use app\models\Corporativo;
use app\models\CorporativoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use app\models\CorporativoUser;
use app\models\UserDatos;

/**
 * CorporativoController implements the CRUD actions for Corporativo model.
 */
class CorporativoController extends Controller
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
     * Lists all Corporativo models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CorporativoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Corporativo model.
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
     * Creates a new Corporativo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
{
    $model = new Corporativo();
    $model->created_at = date('Y-m-d H:i:s');

    if ($this->request->isPost) {
        if ($model->load($this->request->post())) {
            // El modelo se carga con los datos del formulario, incluyendo el user_datos_type_id
            
            // Si el modelo se guarda con éxito
            if ($model->save()) {
                
                $afiliadoId = $this->request->post('Corporativo')['user_login_id'] ?? null;
                
                if ($afiliadoId) {
                    $modelCorporativoUser = new CorporativoUser();
                    $modelCorporativoUser->corporativo_id = $model->id; // El ID del corporativo recién creado
                    $modelCorporativoUser->user_id = $afiliadoId;      // El ID del afiliado
                    $modelCorporativoUser->fecha_vinculacion = date('Y-m-d H:i:s');
                    $modelCorporativoUser->rol_en_corporativo = 'afiliado';
                    
                    if (!$modelCorporativoUser->save()) {
                        Yii::error("Error al guardar CorporativoUser: " . json_encode($modelCorporativoUser->errors), __METHOD__);
                        Yii::$app->session->setFlash('error', 'Error al guardar la relación con el afiliado.');
                    }
                }
               

                Yii::$app->session->setFlash('success', 'Corporativo creado exitosamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
    } else {
        $model->loadDefaultValues();
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}

    /**
     * Updates an existing Corporativo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
   public function actionUpdate($id)
{
    $model = $this->findModel($id);
    
    // Obtener los IDs de los afiliados que ya están asociados con este corporativo
    $afiliadosActuales = CorporativoUser::find()
        ->where(['corporativo_id' => $model->id])
        ->select('user_id')
        ->column();

    if ($this->request->isPost && $model->load($this->request->post())) {

        // Obtener los IDs de los afiliados seleccionados en el formulario
        $afiliadosSeleccionados = (array)($this->request->post('Corporativo')['users_ids'] ?? []);
        
        // --- INICIO DE LA LÍNEA CORREGIDA ---
        // Filtrar cualquier valor nulo o vacío que pueda venir del formulario
        $afiliadosSeleccionados = array_filter($afiliadosSeleccionados);
        // --- FIN DE LA LÍNEA CORREGIDA ---

        // Identificar qué afiliados se deben añadir y cuáles se deben borrar
        $afiliadosParaBorrar = array_diff($afiliadosActuales, $afiliadosSeleccionados);
        $afiliadosParaAnadir = array_diff($afiliadosSeleccionados, $afiliadosActuales);

        // --- LÓGICA PARA BORRAR RELACIONES Y ACTUALIZAR USER_DATOS ---
        foreach ($afiliadosParaBorrar as $userId) {
            // 1. Borrar la relación de la tabla intermedia
            $relacion = CorporativoUser::findOne(['corporativo_id' => $model->id, 'user_id' => $userId]);
            if ($relacion) {
                $relacion->delete();
            }

            // 2. Actualizar el registro en la tabla user_datos
            $userDatos = UserDatos::findOne(['user_login_id' => $userId]);
            if ($userDatos) {
                $userDatos->afiliado_corporativo_id = null;
                $userDatos->user_datos_type_id = 1; // Asumimos que 1 es el ID para "Simple"
                $userDatos->save();
            }
        }
        
        // --- LÓGICA PARA AÑADIR NUEVAS RELACIONES Y ACTUALIZAR USER_DATOS ---
        foreach ($afiliadosParaAnadir as $userId) {
            // 1. Crear la nueva relación en la tabla intermedia
            $nuevaRelacion = new CorporativoUser();
            $nuevaRelacion->corporativo_id = $model->id;
            $nuevaRelacion->user_id = $userId;
            $nuevaRelacion->fecha_vinculacion = date('Y-m-d H:i:s');
            $nuevaRelacion->rol_en_corporativo = 'afiliado';
            $nuevaRelacion->save();

            // 2. Actualizar el registro en la tabla user_datos
            $userDatos = UserDatos::findOne(['user_login_id' => $userId]);
            if ($userDatos) {
                $userDatos->afiliado_corporativo_id = $model->id;
                $userDatos->user_datos_type_id = 2; // Asumimos que 2 es el ID para "Corporativo"
                $userDatos->save();
            }
        }

        // --- LÓGICA PARA GUARDAR EL MODELO PRINCIPAL (CORPORATIVO) ---
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Corporativo actualizado exitosamente.');
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            Yii::$app->session->setFlash('error', 'Error al actualizar el corporativo: ' . implode(', ', array_map(function($errors) {
                return implode(', ', $errors);
            }, $model->getErrors())));
        }
    }

    return $this->render('update', [
        'model' => $model,
        'afiliadosActuales' => $afiliadosActuales, // Es útil pasar esto a la vista para la selección por defecto
    ]);
}

    /**
     * Deletes an existing Corporativo model.
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
     * Finds the Corporativo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Corporativo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Corporativo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
