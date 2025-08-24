<?php

namespace app\controllers;

use Yii;
use app\models\SisSiniestro;
use app\models\SisSiniestroSearch;
use app\models\UserDatos;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SisSiniestroController implements the CRUD actions for SisSiniestro model.
 */
class SisSiniestroController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all SisSiniestro models.
     * @param integer $user_id El ID del usuario
     * @return mixed
     */
    public function actionIndex($user_id)
    {
        $searchModel = new SisSiniestroSearch();
        $searchModel->iduser = $user_id;
        
        // Cargar los datos del afiliado
        $afiliado = UserDatos::findOne($user_id);
        
        // Configurar el dataProvider para cargar la relación con baremos
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        // Depurar la consulta principal
        $query = $dataProvider->query;
        $sql = $query->createCommand()->rawSql;
        Yii::info('CONSULTA PRINCIPAL: ' . $sql, 'app');
        
        // Cargar todos los siniestros con sus baremos en una sola consulta
        $models = $dataProvider->getModels();
        $siniestroIds = [];
        
        // Obtener todos los IDs de siniestros
        foreach ($models as $model) {
            $siniestroIds[] = $model->id;
        }
        
        // Cargar todos los baremos para estos siniestros en una sola consulta
        $baremosPorSiniestro = [];
        if (!empty($siniestroIds)) {
            $baremos = (new \yii\db\Query())
                ->select(['sb.siniestro_id', 'b.*'])
                ->from(['sb' => 'sis_siniestro_baremo'])
                ->leftJoin(['b' => 'baremo'], 'sb.baremo_id = b.id')
                ->where(['sb.siniestro_id' => $siniestroIds])
                ->all();
            
            // Organizar los baremos por siniestro_id
            foreach ($baremos as $baremo) {
                $baremosPorSiniestro[$baremo['siniestro_id']][] = $baremo;
            }
        }
        
        // Asignar los baremos a cada modelo
        foreach ($models as $model) {
            $baremos = isset($baremosPorSiniestro[$model->id]) ? $baremosPorSiniestro[$model->id] : [];
            $model->populateRelation('baremos', $baremos);
        }
        
        $dataProvider->setModels($models);
        

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'user_id' => $user_id,
            'afiliado' => $afiliado
        ]);
    }

    /**
     * Displays a single SisSiniestro model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $afiliado = UserDatos::find()->where(['id' => $model->iduser])->one();
        
        // Cargar los baremos a través de la relación muchos a muchos
        $baremos = $model->baremos;

        return $this->render('view', [
            'model' => $model,
            'afiliado' => $afiliado,
            'baremos' => $baremos
        ]);
    }

    /**
     * Creates a new SisSiniestro model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $user_id El ID del usuario
     * @return mixed
     */
    public function actionCreate($user_id)
    {
        $model = new SisSiniestro();
        $model->iduser = $user_id;
        $model->fecha = date('Y-m-d');
        $model->hora = date('H:i:s');
        $afiliado = UserDatos::find()->where(['id' => $user_id])->one();

        if ($model->load($this->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Guardar la relación muchos a muchos
                    if (isset($_POST['SisSiniestro']['idbaremo']) && is_array($_POST['SisSiniestro']['idbaremo'])) {
                        if (!$model->saveBaremos($_POST['SisSiniestro']['idbaremo'])) {
                            throw new \Exception('Error al guardar los baremos');
                        }
                    }
                    $transaction->commit();
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'afiliado' => $afiliado,
            'user_id' => $user_id,
        ]);
    }

    /**
     * Updates an existing SisSiniestro model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $afiliado = UserDatos::find()->where(['id' => $model->iduser])->one();
        // Cargar los baremos de la misma manera que en actionView
        $baremos = $model->baremos;

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    // Guardar la relación muchos a muchos
                    $baremoIds = Yii::$app->request->post('SisSiniestro')['idbaremo'] ?? [];
                    if (!is_array($baremoIds)) {
                        $baremoIds = [];
                    }
                    
                    if (!$model->saveBaremos($baremoIds)) {
                        throw new \Exception('Error al actualizar los baremos');
                    }
                    
                    // Forzar recarga de la relación baremos
                    $model->refresh();
                    
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Siniestro actualizado correctamente.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
                Yii::error('Error al actualizar siniestro: ' . $e->getMessage(), __METHOD__);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'afiliado' => $afiliado,
            'baremos' => $baremos
        ]);
    }

    /**
     * Deletes an existing SisSiniestro model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SisSiniestro model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SisSiniestro the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SisSiniestro::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}