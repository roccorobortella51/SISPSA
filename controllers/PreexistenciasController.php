<?php

namespace app\controllers;

use Yii;
use app\models\Preexistencias;
use app\models\PreexistenciasSearch;
use app\models\UserDatos;
use app\components\UserHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * PreexistenciasController implements the CRUD actions for Preexistencias model.
 */
class PreexistenciasController extends Controller
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
                    'toggle-status' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Authenticated users only
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Preexistencias models.
     * @param int|null $user_id
     * @return string
     */
    public function actionIndex($user_id = null)
    {
        $searchModel = new PreexistenciasSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $user_id);

        // Get affiliate info if user_id is provided
        $afiliado = null;
        if ($user_id) {
            $afiliado = UserDatos::findOne($user_id);
        }

        // Get summary statistics
        $summary = $searchModel->getSummaryStatistics($user_id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'user_id' => $user_id,
            'afiliado' => $afiliado,
            'summary' => $summary,
        ]);
    }

    /**
     * Displays a single Preexistencias model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $afiliado = UserDatos::findOne($model->user_id);

        return $this->render('view', [
            'model' => $model,
            'afiliado' => $afiliado,
        ]);
    }

    /**
     * Creates a new Preexistencias model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param int|null $user_id
     * @param int|null $return_url The URL to return to after saving
     * @return string|\yii\web\Response
     */
    public function actionCreate($user_id = null, $return_url = null)
    {
        $model = new Preexistencias();

        if ($user_id) {
            $model->user_id = $user_id;
        }

        // Get the affiliated user
        $afiliado = null;
        if ($model->user_id) {
            $afiliado = UserDatos::findOne($model->user_id);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', $this->buildSuccessMessage(
                'Pre-existencia Registrada Exitosamente',
                'La pre-existencia "' . $model->nombre . '" ha sido registrada correctamente.'
            ));

            // ============================================================
            // FIX: Redirect to return_url if provided, otherwise go to view
            // ============================================================
            if ($return_url) {
                return $this->redirect($return_url);
            }

            // Check if we should return to sis-siniestro form
            $referrer = Yii::$app->request->referrer;
            if ($referrer && strpos($referrer, 'sis-siniestro') !== false) {
                parse_str(parse_url($referrer, PHP_URL_QUERY), $queryParams);
                $userId = $queryParams['user_id'] ?? $user_id;
                $esCita = $queryParams['es_cita'] ?? 0;
                $id = $queryParams['id'] ?? null;

                if ($userId) {
                    $url = ['/sis-siniestro/create', 'user_id' => $userId, 'es_cita' => $esCita];
                    if ($id) {
                        $url['id'] = $id;
                    }
                    return $this->redirect($url);
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'afiliado' => $afiliado,
            'user_id' => $user_id,
            'return_url' => $return_url,
        ]);
    }

    /**
     * Updates an existing Preexistencias model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @param string|null $return_url The URL to return to after saving
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $return_url = null)
    {
        $model = $this->findModel($id);
        $afiliado = UserDatos::findOne($model->user_id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', $this->buildSuccessMessage(
                'Pre-existencia Actualizada',
                'La pre-existencia "' . $model->nombre . '" ha sido actualizada correctamente.'
            ));

            // ============================================================
            // FIX: Redirect to return_url if provided, otherwise go to view
            // ============================================================
            if ($return_url) {
                return $this->redirect($return_url);
            }

            // Check if we should return to sis-siniestro form
            $referrer = Yii::$app->request->referrer;
            if ($referrer && strpos($referrer, 'sis-siniestro') !== false) {
                parse_str(parse_url($referrer, PHP_URL_QUERY), $queryParams);
                $userId = $queryParams['user_id'] ?? $model->user_id;
                $esCita = $queryParams['es_cita'] ?? 0;
                $id = $queryParams['id'] ?? null;

                if ($userId) {
                    $url = ['/sis-siniestro/create', 'user_id' => $userId, 'es_cita' => $esCita];
                    if ($id) {
                        $url['id'] = $id;
                    }
                    return $this->redirect($url);
                }
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'afiliado' => $afiliado,
            'return_url' => $return_url,
        ]);
    }

    /**
     * Deletes an existing Preexistencias model.
     * @param int $id
     * @param string|null $return_url The URL to return to after deleting
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id, $return_url = null)
    {
        $model = $this->findModel($id);
        $user_id = $model->user_id;

        // Store the name for the flash message
        $nombre = $model->nombre;

        // Soft delete - set deleted_at timestamp
        $model->deleted_at = date('Y-m-d H:i:s');

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', $this->buildSuccessMessage(
                'Pre-existencia Eliminada',
                'La pre-existencia "' . $nombre . '" ha sido eliminada correctamente.'
            ));
        } else {
            Yii::$app->session->setFlash('error', $this->buildErrorMessage(
                'Error al Eliminar',
                'No se pudo eliminar la pre-existencia. Por favor, intente nuevamente.'
            ));
        }

        // ============================================================
        // FIX: Redirect to return_url if provided, otherwise go to index
        // ============================================================
        if ($return_url) {
            return $this->redirect($return_url);
        }

        // Check if we should return to sis-siniestro form
        $referrer = Yii::$app->request->referrer;
        if ($referrer && strpos($referrer, 'sis-siniestro') !== false) {
            parse_str(parse_url($referrer, PHP_URL_QUERY), $queryParams);
            $userId = $queryParams['user_id'] ?? $user_id;
            $esCita = $queryParams['es_cita'] ?? 0;
            $id = $queryParams['id'] ?? null;

            if ($userId) {
                $url = ['/sis-siniestro/create', 'user_id' => $userId, 'es_cita' => $esCita];
                if ($id) {
                    $url['id'] = $id;
                }
                return $this->redirect($url);
            }
        }

        return $this->redirect(['index', 'user_id' => $user_id]);
    }

    /**
     * Toggle status of a pre-existence
     * @param int $id
     * @param string|null $return_url The URL to return to after toggling
     * @return \yii\web\Response
     */
    public function actionToggleStatus($id, $return_url = null)
    {
        $model = $this->findModel($id);
        $user_id = $model->user_id;

        if ($model->estatus === Preexistencias::ESTATUS_ACTIVO) {
            $model->estatus = Preexistencias::ESTATUS_INACTIVO;
        } else {
            $model->estatus = Preexistencias::ESTATUS_ACTIVO;
        }

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', $this->buildSuccessMessage(
                'Estatus Actualizado',
                'El estatus de la pre-existencia ha sido actualizado a: ' . $model->estatus
            ));
        }

        // ============================================================
        // FIX: Redirect to return_url if provided, otherwise go to view
        // ============================================================
        if ($return_url) {
            return $this->redirect($return_url);
        }

        // Check if we should return to sis-siniestro form
        $referrer = Yii::$app->request->referrer;
        if ($referrer && strpos($referrer, 'sis-siniestro') !== false) {
            parse_str(parse_url($referrer, PHP_URL_QUERY), $queryParams);
            $userId = $queryParams['user_id'] ?? $user_id;
            $esCita = $queryParams['es_cita'] ?? 0;
            $id = $queryParams['id'] ?? null;

            if ($userId) {
                $url = ['/sis-siniestro/create', 'user_id' => $userId, 'es_cita' => $esCita];
                if ($id) {
                    $url['id'] = $id;
                }
                return $this->redirect($url);
            }
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the Preexistencias model based on its primary key value.
     * @param int $id
     * @return Preexistencias the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Preexistencias::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La página solicitada no existe.');
    }

    /**
     * Build professional success message
     */
    private function buildSuccessMessage($title, $message)
    {
        return '
        <div style="
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-left: 6px solid #28a745;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.15);
        ">
            <div style="
                background: #28a745;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            ">
                <i class="fas fa-check-circle" style="color: white; font-size: 20px;"></i>
            </div>
            <div>
                <strong style="color: #155724; font-size: 16px;">' . $title . '</strong>
                <p style="color: #155724; margin: 0; font-size: 14px;">' . $message . '</p>
            </div>
        </div>';
    }

    /**
     * Build professional error message
     */
    private function buildErrorMessage($title, $message)
    {
        return '
        <div style="
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-left: 6px solid #dc3545;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.15);
        ">
            <div style="
                background: #dc3545;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            ">
                <i class="fas fa-exclamation-circle" style="color: white; font-size: 20px;"></i>
            </div>
            <div>
                <strong style="color: #721c24; font-size: 16px;">' . $title . '</strong>
                <p style="color: #721c24; margin: 0; font-size: 14px;">' . $message . '</p>
            </div>
        </div>';
    }
}
