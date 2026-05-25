<?php
// controllers/ProfileController.php - COMPLETE WORKING VERSION

namespace app\controllers;

use Yii;
use app\models\UserDatos;
use app\models\RmEstado;
use app\models\RmCiudad;
use app\models\RmMunicipio;
use app\models\RmParroquia;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\web\Response;

class ProfileController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $userId = Yii::$app->user->id;
        $userDatos = $this->findUserDatos($userId);

        if (!$userDatos) {
            $userDatos = new UserDatos();
            $userDatos->user_login_id = $userId;
            $userDatos->save(false);
        }

        // Get actual names from the codes
        $estadoNombre = $this->getEstadoNombre($userDatos->estado);
        $ciudadNombre = $this->getCiudadNombre($userDatos->ciudad);
        $municipioNombre = $this->getMunicipioNombre($userDatos->municipio);
        $parroquiaNombre = $this->getParroquiaNombre($userDatos->parroquia);

        return $this->render('index', [
            'userDatos' => $userDatos,
            'estadoNombre' => $estadoNombre,
            'ciudadNombre' => $ciudadNombre,
            'municipioNombre' => $municipioNombre,
            'parroquiaNombre' => $parroquiaNombre,
        ]);
    }

    public function actionUpdate()
    {
        $userId = Yii::$app->user->id;
        $userDatos = $this->findUserDatos($userId);

        if (!$userDatos) {
            $userDatos = new UserDatos();
            $userDatos->user_login_id = $userId;
        }

        $oldSelfie = $userDatos->selfie;

        if (Yii::$app->request->isPost) {
            // Load the posted data
            $userDatos->load(Yii::$app->request->post());

            // Get the uploaded file instance
            $userDatos->selfieFile = UploadedFile::getInstance($userDatos, 'selfieFile');

            // Save the model without validation
            if ($userDatos->save(false)) {

                // Handle file upload if present
                if ($userDatos->selfieFile) {
                    $uploadPath = Yii::getAlias('@webroot/uploads/selfie/');

                    // Create directory if it doesn't exist
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    // Generate unique filename
                    $filename = 'selfie_' . Yii::$app->security->generateRandomString(20) . '_' . time() . '.' . $userDatos->selfieFile->extension;
                    $filePath = $uploadPath . $filename;

                    // Save the file
                    if ($userDatos->selfieFile->saveAs($filePath)) {
                        // Delete old selfie file if exists
                        if ($oldSelfie && file_exists(Yii::getAlias('@webroot/' . $oldSelfie))) {
                            @unlink(Yii::getAlias('@webroot/' . $oldSelfie));
                        }

                        // Update the selfie field in database
                        $userDatos->selfie = 'uploads/selfie/' . $filename;
                        $userDatos->save(false);

                        Yii::$app->session->setFlash('success', '✅ Perfil actualizado correctamente. Foto actualizada.');
                    } else {
                        Yii::$app->session->setFlash('warning', '⚠️ Perfil actualizado, pero no se pudo guardar la foto.');
                    }
                } else {
                    Yii::$app->session->setFlash('success', '✅ Perfil actualizado correctamente.');
                }

                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', '❌ Error al actualizar el perfil.');
            }
        }

        return $this->render('update', [
            'userDatos' => $userDatos,
        ]);
    }

    public function actionUpdatePhoto()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;
        $userDatos = $this->findUserDatos($userId);

        if (!$userDatos) {
            $userDatos = new UserDatos();
            $userDatos->user_login_id = $userId;
        }

        $oldSelfie = $userDatos->selfie;
        $userDatos->selfieFile = UploadedFile::getInstanceByName('selfie');

        if ($userDatos->selfieFile) {
            $uploadPath = Yii::getAlias('@webroot/uploads/selfie/');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $filename = 'selfie_' . Yii::$app->security->generateRandomString(20) . '_' . time() . '.' . $userDatos->selfieFile->extension;
            $filePath = $uploadPath . $filename;

            if ($userDatos->selfieFile->saveAs($filePath)) {
                if ($oldSelfie && file_exists(Yii::getAlias('@webroot/' . $oldSelfie))) {
                    @unlink(Yii::getAlias('@webroot/' . $oldSelfie));
                }

                $userDatos->selfie = 'uploads/selfie/' . $filename;
                $userDatos->save(false);

                return [
                    'success' => true,
                    'photoUrl' => Yii::getAlias('@web/' . $userDatos->selfie) . '?v=' . time()
                ];
            }
        }

        return ['success' => false, 'error' => 'No se pudo subir la imagen'];
    }

    protected function findUserDatos($userId)
    {
        // ONLY use user_login_id (the integer foreign key from user table)
        return UserDatos::findOne(['user_login_id' => $userId]);
    }

    protected function getEstadoNombre($estadoCodigo)
    {
        if (empty($estadoCodigo)) return 'No especificado';
        $estado = RmEstado::findOne(['codigo' => $estadoCodigo]);
        return $estado ? $estado->nombre : 'ID: ' . $estadoCodigo;
    }

    protected function getCiudadNombre($ciudadId)
    {
        if (empty($ciudadId)) return 'No especificado';
        $ciudad = RmCiudad::findOne(['id' => $ciudadId]);
        return $ciudad ? $ciudad->nombre : 'ID: ' . $ciudadId;
    }

    protected function getMunicipioNombre($municipioCodigo)
    {
        if (empty($municipioCodigo)) return 'No especificado';
        $municipio = RmMunicipio::findOne(['codigo_muni' => $municipioCodigo]);
        return $municipio ? $municipio->nombre : 'ID: ' . $municipioCodigo;
    }

    protected function getParroquiaNombre($parroquiaId)
    {
        if (empty($parroquiaId)) return 'No especificado';
        $parroquia = RmParroquia::findOne(['id' => $parroquiaId]);
        return $parroquia ? $parroquia->nombre : 'ID: ' . $parroquiaId;
    }
}
