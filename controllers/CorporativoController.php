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
use app\models\ContratosSearch;
use app\models\Contratos;

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

        // Asegurar que las relaciones se carguen
        $dataProvider->query->with(['users', 'clinicas']);

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
     * Lists all Contratos models for users related to a specific Corporativo.
     * @param int $id Corporativo ID
     * @return string
     * @throws NotFoundHttpException if the corporativo cannot be found
     */
    public function actionContracts($id)
    {
        $corporativo = $this->findModel($id);

        // Obtener IDs de usuarios asociados al corporativo
        $userIds = CorporativoUser::find()
            ->select('user_id')
            ->where(['corporativo_id' => $id])
            ->column();

        $searchModel = new ContratosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        // Filtrar contratos por usuarios asociados
        if (!empty($userIds)) {
            $dataProvider->query->andWhere(['in', 'user_id', $userIds]);
        } else {
            // Si no hay usuarios, no mostrar contratos
            $dataProvider->query->where('0=1');
        }

        return $this->render('contracts', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'corporativo' => $corporativo,
        ]);
    }

    /**
     * Realiza un pago corporativo para los afiliados del corporativo.
     * Carga cuotas pendientes por afiliado y renderiza el formulario.
     * @param int $id Corporativo ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the corporativo cannot be found
     */
    public function actionPagos($id)
    {
        $corporativo = $this->findModel($id);
        $model = new \app\models\Pagos();
        $model->loadDefaultValues();
        $model->user_id = null; // Pago corporativo, no específico de user
        $model->estatus = 'Por Conciliar';

        // Obtener IDs de usuarios asociados
        $userIds = \app\models\CorporativoUser::find()
            ->select('user_id')
            ->where(['corporativo_id' => $id])
            ->column();

        $allCuotas = [];
        $grandTotal = 0;
        if (!empty($userIds)) {
            foreach ($userIds as $userId) {
                $user = \app\models\UserDatos::findOne($userId);
                if ($user) {
                    // Cargar cuotas pendientes como en PagosController
                    $cuotas = \app\models\Cuotas::find()
                        ->select('cuotas.*')
                        ->innerJoin('contratos', 'contratos.id = cuotas.contrato_id')
                        ->where(['contratos.user_id' => $userId, 'cuotas.Estatus' => 'pendiente'])
                        ->orderBy(['cuotas.fecha_vencimiento' => SORT_ASC])
                        ->all();

                    foreach ($cuotas as $cuota) {
                        if (($cuota->monto_usd ?? 0) > 0) {
                            $allCuotas[] = $cuota;
                            $grandTotal += $cuota->monto_usd;
                        }
                    }
                }
            }
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // Para pago corporativo total: marcar TODAS las cuotas pendientes como pagadas
                $montoPagadoPosted = (float)($model->monto_pagado ?: 0);
                
                // Validar que el monto coincida con el total
                if (abs($grandTotal - $montoPagadoPosted) > 0.01) {
                    $model->addError('monto_pagado', 'El monto a pagar debe coincidir con el total de cuotas pendientes.');
                    Yii::$app->session->setFlash('warning', 'El monto no coincide con el total de cuotas pendientes.');
                } else {
                    // Manejar archivo y guardar (adaptado de PagosController)
                    $model->tasa = \app\models\TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one()->tasa_cambio ?? 1;
                    $model->imagen_prueba_file = \yii\web\UploadedFile::getInstance($model, 'imagen_prueba_file');

                    if ($model->imagen_prueba_file) {
                        // Lógica de subida similar a PagosController (simplificada)
                        $folder = 'Pago';
                        $fileName = uniqid('pago_corp_') . '.' . $model->imagen_prueba_file->extension;
                        $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;

                        if ($model->imagen_prueba_file->saveAs($tempFilePath)) {
                            $publicUrl = \app\components\UserHelper::uploadFileToSupabaseApi(
                                $tempFilePath,
                                $model->imagen_prueba_file->type,
                                $fileName,
                                $folder
                            );

                            if (file_exists($tempFilePath)) {
                                unlink($tempFilePath);
                            }

                            if ($publicUrl) {
                                $model->imagen_prueba = $publicUrl;
                            }
                        }
                    }

                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($model->save(false)) {
                            $contratosActualizados = [];
                            
                            // Marcar TODAS las cuotas pendientes del corporativo como pagadas
                            foreach ($allCuotas as $cuota) {
                                if ($cuota->Estatus === 'pendiente') {
                                    $cuota->Estatus = 'pagado';
                                    $cuota->fecha_pago = $model->fecha_pago ?: date('Y-m-d');
                                    $cuota->rate_usd_bs = $model->tasa;
                                    $cuota->id_pago = $model->id; // Guardar ID del pago para rastreo
                                    $cuota->save(false);

                                    // Recopilar contratos únicos para actualizar estatus
                                    if (!in_array($cuota->contrato_id, $contratosActualizados)) {
                                        $contratosActualizados[] = $cuota->contrato_id;
                                    }
                                }
                            }
                            
                            // Actualizar estatus de contratos a 'activo' una vez pagadas todas sus cuotas
                            foreach ($contratosActualizados as $contratoId) {
                                $contrato = \app\models\Contratos::findOne($contratoId);
                                if ($contrato) {
                                    // Verificar si el contrato tiene cuotas pendientes restantes
                                    $cuotasPendientes = \app\models\Cuotas::find()
                                        ->where(['contrato_id' => $contratoId, 'Estatus' => 'pendiente'])
                                        ->count();
                                    
                                    // Si no hay cuotas pendientes, activar el contrato
                                    if ($cuotasPendientes == 0 && $contrato->estatus !== 'activo') {
                                        $contrato->estatus = 'activo';
                                        $contrato->save(false);
                                    }
                                }
                            }

                            $transaction->commit();
                            Yii::$app->session->setFlash('success', 'Pago corporativo registrado exitosamente. Todas las cuotas pendientes han sido marcadas como pagadas y los contratos activados.');
                            return $this->redirect(['view', 'id' => $corporativo->id]);
                        } else {
                            throw new \Exception('Error al guardar el pago.');
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Error al procesar el pago: ' . $e->getMessage());
                    }
                }
            }
        }

        return $this->render('pagos', [
            'model' => $model,
            'corporativo' => $corporativo,
            'allCuotas' => $allCuotas,
            'grandTotal' => $grandTotal,
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
