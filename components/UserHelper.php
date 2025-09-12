<?php
namespace app\components;
use app\components\DateUtils;
use Yii;
use yii\web\Request;
use yii\helpers\Url;
use yii\db\Expression;
use yii\db\Query;
use app\models\Area;
use app\models\RmEstado;
use app\models\RmMunicipio;
use app\models\RmParroquia;
use app\models\RmClinica;
use app\models\Agente;
use app\models\Asesores;
use app\models\User;
use app\models\Planes; 
use app\models\Contratos; 
use app\models\AuthItem;
use yii\helpers\ArrayHelper;
use yii\rbac\DbManager;
use app\models\AuthAssignment;
use app\models\UserDatos;
use app\models\Corporativo;
use app\models\AgenteFuerza;
use yii\httpclient\Client; 
use yii\helpers\FileHelper;


class UserHelper
{

    // Hold the class instance.
    private static $instance = null;

    // The constructor is private
    // to prevent initiation with outer code.
    private function __construct()
    {
        // The expensive process (e.g.,db connection) goes here.
    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new UserHelper();
        }

        return self::$instance;
    }

     public static function getLayoutIndex(){

        return '{items}
                    <div class="row">
                        <div class="col-md-4 col-sm-2" style="padding-top: 15px;">
                            <div class="dataTables_info text-muted">{summary}</div>
                        </div>
                        <div class="col-md-4 col-sm-6 text-right">
                        <div class="dataTables_paginate paging_simple_numbers">
                            <div class="pull-left">{pager}</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-3 text-right">
                    <div class="dataTables_paginate paging_simple_numbers">
                        <div class="pull-right" style="padding-top: 10px;">';
                            
    }

    public static function getLayoutIndex2(){
                    return '</div>
                    <div class="pull-right text-muted" style="padding-top: 15px;">Registros por página:</div>
                </div>
            </div>
        </div>';
    }

    public static function getPager(){

        return [
                'options' => ['class' => 'pagination justify-content-center'],
                'firstPageLabel' => '<',
                'lastPageLabel' => '>',
                'maxButtonCount' => 3, // Esto limita el número de botones visibles a 3
                'linkContainerOptions' => ['class' => 'page-item'],
                'linkOptions' => ['class' => 'page-link'],
            ];
    }

    public static function getAreaList()
    {
        return \yii\helpers\ArrayHelper::map(
            Area::find()->select(['id', 'nombre as name'])->asArray()->all(),
            'id',
            'name'
        );
    }

    public static function getEstadosList()
    {
        return \yii\helpers\ArrayHelper::map(
            RmEstado::find()->select(['id', 'nombre as name'])->asArray()->all(),
            'id',
            'name'
        );
    }

    public static function getMunicipiosList($estado_id = null)
    {
        $query = RmMunicipio::find()->select(['id', 'nombre as name'])->asArray();

        if ($estado_id !== null) {
            // Filtra por el código del estado (que es el ID del estado en RmEstado)
            $query->where(['estado_codigo' => $estado_id]);
        }

        return ArrayHelper::map(
            $query->all(),
            'id',
            'name'
        );
    }

    public static function getParroquiasList($municipio_id = null)
    {
        $query = RmParroquia::find()->select(['id', 'nombre as name'])->asArray();

        if ($municipio_id !== null) {
            // Filtra por el código del municipio (que es el ID del municipio en RmMunicipio)
            $query->where(['muni_codigo' => $municipio_id]);
        }

        return ArrayHelper::map(
            $query->all(),
            'id',
            'name'
        );
    }

    public static function getTotalClinicas()
    {
        $totalClinicas = RmClinica::find()->count();

        // Puedes pasar este total a una vista
        return $totalClinicas;
    }

    public static function getClinicasList()
    {
        return \yii\helpers\ArrayHelper::map(
                RmClinica::find()->select(['id', 'nombre as name'])->asArray()->all(),
                'id',
                'name'
            );        
    }

    public static function getTotalAsesores()
    {
        $totalClinicas = Asesores::find()->count();

        // Puedes pasar este total a una vista
        return $totalClinicas;
    }

    public static function getTotalAgentes()
    {
        $totalClinicas = Agente::find()->count();

        // Puedes pasar este total a una vista
        return $totalClinicas;
    }

    public static function getTotalUsuarios()
    {
        $totalClinicas = User::find()->count();

        // Puedes pasar este total a una vista
        return $totalClinicas;
    }

    public static function getRolesList()
    {
        return \yii\helpers\ArrayHelper::map(
            AuthItem::find()->select(['name', 'name'])->where(['type' => '1'])->asArray()->all(),
            'name',
            'name'
        );
    }

    public static function generarCodigoValidacion($longitud = 6) {
        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $codigo = '';
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        return $codigo;
    }

    public static function getRolesAllRoles()
    {
        if(self::getMyRol() == "Administrador"){
            return \yii\helpers\ArrayHelper::map(
                AuthItem::find()->select(['name', 'name'])->andWhere(['type' => 1])->asArray()->all(),
                'name',
                'name'
            );
        }

        if(self::getMyRol() == "GERENTE-COMERCIALIZACION"){

            //listar agente y asesor
           return \yii\helpers\ArrayHelper::map(
                AuthItem::find()
                    ->select(['name', 'name'])
                    ->andWhere(['type' => 1])
                    ->andWhere(['name' => 'Asesor'])
                    ->orWhere(['name' => 'Agente'])
                    ->asArray()
                    ->all(),
                'name',
                'name'
            );
        }


        return \yii\helpers\ArrayHelper::map(
            AuthItem::find()->select(['name', 'name'])->andWhere(['type' => 1])->asArray()->all(),
            'name',
            'name'
        );
    }

    public static function getAgentesList()
    {
        // 1. Obtener los agentes reales de la base de datos
        $agentes = User::find()
            ->select([
                    new \yii\db\Expression("CONCAT(nombres, ' ', apellidos, ', Documento: ',tipo_cedula , cedula) AS name"),
                    'user_datos.id AS id'
                ])   
            ->leftJoin('auth_assignment', '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
            ->leftJoin('user_datos', '"user"."id" = "user_datos"."user_login_id"')
            ->where(['auth_assignment.item_name' => "Agente"])
            ->asArray()
            ->all();

        // 2. Mapear los resultados a un array ID => Nombre
        $list = ArrayHelper::map($agentes, 'id', 'name');

        // 3. Añadir la opción "No Asignado" al principio del array
        $defaultOption = ['0' => 'No Asignado']; // Usamos 0 como clave para "No Asignado"

        // Fusionar la opción predeterminada con la lista de agentes reales
        $finalList = $defaultOption + $list; // El operador '+' fusiona arrays manteniendo las claves.

        return $finalList;
    }

    public static function getAsesor()
    {
        return \yii\helpers\ArrayHelper::map(
            User::find()
                ->select([
                    new \yii\db\Expression("CONCAT(nombres, ' ', apellidos, ', Documento: ',tipo_cedula , cedula) AS name"),
                    'user_datos.id AS id'
                ])                
                ->joinWith('userDatos')
                ->leftJoin('auth_assignment', '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
                ->where(['auth_assignment.item_name' => "Asesor"])
                ->asArray()
                ->all(),
            'id',
            'name'
        );
    }

     public static function getAgenteFuerzaList()
    {
       return \yii\helpers\ArrayHelper::map(
            User::find()
                ->select([
                    new \yii\db\Expression("CONCAT('N° de Vendedor/Asesor: ', agente_fuerza.id, ' - ' , nombres, '  ', apellidos, ', Documento: ',tipo_cedula , cedula) AS name"),
                    'agente_fuerza.id AS id'
                ])
                ->joinWith('userDatos')
                ->leftJoin('auth_assignment', '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
                ->leftJoin('agente_fuerza', '"agente_fuerza"."idusuario" = "user_datos"."id"')
                ->where(['auth_assignment.item_name' => "Asesor"])
                ->andWhere(['is not', 'agente_fuerza.idusuario', null])
                ->asArray()
                ->all(),
            'id',
            'name'
        );
    }

     /**
     * Obtiene la información de contacto (rif, email, telefono, direccion)
     * de los UserDatos del propietario de una agencia.
     *
     * @param int $agenteId El ID de la agencia.
     * @return array Un array asociativo con 'rif', 'email', 'telefono', 'direccion' o valores 'N/A' si no se encuentran.
     */
    public static function getAgenteOwnerContactInfo($agenteId)
    {
        // 1) Buscar la agencia
        $agente = \app\models\Agente::findOne($agenteId);
        if ($agente === null) {
            return [
                'rif' => 'N/A',
                'email' => 'N/A',
                'telefono' => 'N/A',
                'direccion' => 'N/A',
            ];
        }

        // 2) idusuariopropietario apunta a user_datos.id, así que buscamos UserDatos directamente
        $ownerDatos = \app\models\UserDatos::findOne($agente->idusuariopropietario);
        if ($ownerDatos === null) {
            return [
                'rif' => 'N/A',
                'email' => 'N/A',
                'telefono' => 'N/A',
                'direccion' => 'N/A',
            ];
        }

        // 3) Devolver los datos de contacto desde UserDatos
        // RIF: si no existe columna/valor rif, construir desde tipo_cedula + cedula
        $rif = null;
        if (isset($ownerDatos->rif) && !empty($ownerDatos->rif)) {
            $rif = $ownerDatos->rif;
        } elseif (!empty($ownerDatos->tipo_cedula) && !empty($ownerDatos->cedula)) {
            $rif = $ownerDatos->tipo_cedula . '-' . $ownerDatos->cedula;
        }

        return [
            'rif' => $rif ?? 'N/A',
            'email' => $ownerDatos->email ?? 'N/A',
            'telefono' => $ownerDatos->telefono ?? 'N/A',
            'direccion' => $ownerDatos->direccion ?? 'N/A',
        ];
    }



    public static function getAfiliadosList()
    {
    $query = User::find()
        ->leftJoin(AuthAssignment::tableName(), '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
        ->leftJoin(UserDatos::tableName(), '"user"."id" = "user_datos"."user_login_id"')
        ->select([
            'user_datos.id AS id',
            new \yii\db\Expression("CONCAT(user_datos.nombres, ' ', user_datos.apellidos) AS name")
        ])
        // Asegúrate que el rol es 'afiliado' (en minúsculas, como confirmaste)
        ->where(['auth_assignment.item_name' => 'afiliado'])
        ->andWhere(['user.status' => User::STATUS_ACTIVE])
        ->orderBy('user_datos.nombres, user_datos.apellidos')
        ->asArray();

    // --- INICIO DE DEPURACIÓN ---
    // Descomenta las siguientes líneas para ver el SQL y los resultados
    // Esto DETENDRÁ la ejecución de la página y mostrará la información.

    // Imprime la consulta SQL generada
    // \Yii::warning("SQL for getAfiliadosList: " . $query->createCommand()->rawSql);
    // var_dump($query->createCommand()->rawSql);
    // die(); // Detiene la ejecución aquí para ver el SQL

    // Imprime el resultado real de la consulta antes de mapearlo
    $afiliados = $query->all();
    // \Yii::warning("Afiliados data: " . json_encode($afiliados));
    // var_dump($afiliados);
    // die(); // Detiene la ejecución aquí para ver los datos

    // --- FIN DE DEPURACIÓN ---


    $list = ArrayHelper::map($afiliados, 'id', 'name');

    return $list;
}

    public static function generateUniqueUsername($baseUsername)
    {
        $username = $baseUsername;
        $counter = 1;

        while (User::find()->where(['username' => $username])->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        return $username;
    }

    public static function getMyRol(){

        $userId = Yii::$app->user->id;
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($userId); 

        $roleName = null; 
        if (!empty($roles)) {
            $firstRole = reset($roles); 
            $roleName = $firstRole->name;
        }

        if ($roleName) {
            return $roleName;
        } else {
            return "Sin rol";
        }
    }

    //funcion para mostrar roles en el index de user mientras tanto*

    public static function getRolNameByUserId($userId)
    {
        if (empty($userId)) {
            return "N/A"; 
        }

        $auth = Yii::$app->authManager;
        
        if (!$auth instanceof DbManager) {
            
            return "Error de Configuración Auth";
        }

        $roles = $auth->getRolesByUser($userId);

        if (!empty($roles)) {
            
            $firstRole = reset($roles);
            return $firstRole->name;
        } else {
            return "Sin rol";
        }
    }

    /**
     * Sube un archivo a Supabase Storage directamente a través de su API REST.
     * Utiliza yii\httpclient\Client para realizar la solicitud HTTP.
     *
     * @param string $localFilePath Ruta completa del archivo temporal en el servidor.
     * @param string $mimeType Tipo MIME del archivo (ej. 'image/png', 'application/pdf').
     * @param string $fileKeyInBucket La clave o ruta del archivo deseada dentro del bucket de Supabase (ej. 'mi_imagen.jpg' o 'docs/reporte.pdf').
     * @return string|null La URL pública del archivo si la subida fue exitosa, o null si hubo un error.
     */
    /*public static function uploadFileToSupabaseApi(string $localFilePath, string $mimeType, string $fileKeyInBucket, string $folder = null): ?string
    {
                
        $supabaseConfig = Yii::$app->params['supabase'];
        $supabaseUrl = $supabaseConfig['url'];
        $supabaseAnonKey = $supabaseConfig['anon_key'];
        $bucketName = $supabaseConfig['bucket_name'];

        // Construimos la URL del endpoint de la API de Storage para la operación de subida (PUT/POST)
        $uploadUrl = "{$supabaseUrl}/storage/v1/object/{$bucketName}/{$folder}/{$fileKeyInBucket}";
        // Construimos la URL pública esperada para acceder al archivo una vez subido
        $publicUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucketName}/{$folder}/{$fileKeyInBucket}";
        
        Yii::info("Supabase Upload URL: " . $uploadUrl, __METHOD__);
        Yii::info("Supabase Public URL (esperada): " . $publicUrl, __METHOD__);
        Yii::info("File Key in Bucket: " . $fileKeyInBucket, __METHOD__);
        Yii::info("Local File Path: " . $localFilePath, __METHOD__);
        Yii::info("MIME Type: " . $mimeType, __METHOD__);

        try {
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('POST') // Usamos POST para subir nuevos archivos. Puedes usar 'PUT' para sobrescribir.
                ->setUrl($uploadUrl)
                ->addHeaders([
                    'Authorization' => "Bearer {$supabaseAnonKey}",
                    'Content-Type' => $mimeType,
                    'x-upsert' => 'true', // Opcional: para sobrescribir si el archivo ya existe con la misma clave
                ])
                ->setContent(file_get_contents($localFilePath)) // Leemos el contenido del archivo temporal
                ->send();

            if ($response->isOk) {
                Yii::info("Archivo subido exitosamente a Supabase Storage via API. Respuesta: " . $response->getContent(), __METHOD__);
                return $publicUrl;
            } else {
                $errorContent = $response->getContent();
                Yii::error("Error al subir archivo a Supabase Storage via API. Código: {$response->getStatusCode()}, Error: {$errorContent}", __METHOD__);
                Yii::$app->session->setFlash('error', "Error al subir archivo a Supabase Storage: " . ($errorContent ?: "Desconocido"));
                return null;
            }

        } catch (\yii\httpclient\Exception $e) {
            Yii::error("Excepción del cliente HTTP al subir a Supabase: " . $e->getMessage() . " - Stack Trace: " . $e->getTraceAsString(), __METHOD__);
            Yii::$app->session->setFlash('error', "Error de conexión al subir archivo: " . $e->getMessage());
            return null;
        } catch (\Throwable $e) {
            Yii::error("Excepción general al subir a Supabase: " . $e->getMessage() . " - Stack Trace: " . $e->getTraceAsString(), __METHOD__);
            Yii::$app->session->setFlash('error', "Ocurrió un error inesperado al subir archivo: " . $e->getMessage());
            return null;
        }
    }*/

    public static function uploadFileToSupabaseApi(string $localFilePath, string $mimeType, string $fileKeyInBucket, string $folder = null): ?string
    {
        // 1. Intentar subir a Supabase
        $supabaseUrl = null;
        try {
            $supabaseConfig = Yii::$app->params['supabase'];
            $supabaseUrl = $supabaseConfig['url'];
            $supabaseAnonKey = $supabaseConfig['anon_key'];
            $bucketName = $supabaseConfig['bucket_name'];

            $uploadUrl = "{$supabaseUrl}/storage/v1/object/{$bucketName}/{$folder}/{$fileKeyInBucket}";
            $publicUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucketName}/{$folder}/{$fileKeyInBucket}";

            Yii::info("Supabase Upload URL: " . $uploadUrl, __METHOD__);

            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl($uploadUrl)
                ->addHeaders([
                    'Authorization' => "Bearer {$supabaseAnonKey}",
                    'Content-Type' => $mimeType,
                    'x-upsert' => 'true',
                ])
                ->setContent(file_get_contents($localFilePath))
                ->send();

            if ($response->isOk) {
                Yii::info("Archivo subido exitosamente a Supabase Storage via API.", __METHOD__);
                return $publicUrl;
            } else {
                $errorContent = $response->getContent();
                Yii::error("Error al subir archivo a Supabase Storage. Código: {$response->getStatusCode()}, Error: {$errorContent}", __METHOD__);
                Yii::$app->session->setFlash('error', "Error al subir archivo a Supabase Storage: " . ($errorContent ?: "Desconocido"));
                // El error de Supabase no es crítico, continuar al siguiente paso (guardado local)
            }

        } catch (\yii\httpclient\Exception $e) {
            Yii::error("Excepción del cliente HTTP al subir a Supabase: " . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', "Error de conexión al subir archivo: " . $e->getMessage());
            // El error de conexión no es crítico, continuar al siguiente paso (guardado local)
        } catch (\Throwable $e) {
            Yii::error("Excepción general al subir a Supabase: " . $e->getMessage(), __METHOD__);
            //Yii::$app->session->setFlash('error', "Ocurrió un error inesperado al subir archivo: " . $e->getMessage());
            // El error general no es crítico, continuar al siguiente paso (guardado local)
        }

        // 2. Si la subida a Supabase falló, guardar el archivo localmente
        Yii::info("La subida a Supabase falló. Guardando el archivo localmente.", __METHOD__);
        $localUploadPath = Yii::getAlias('@webroot/img/payment');

        // Asegurarse de que el directorio existe, si no, crearlo.
        if (!is_dir($localUploadPath)) {
            FileHelper::createDirectory($localUploadPath, 0775, true);
        }

        $localFileName = pathinfo($localFilePath, PATHINFO_BASENAME);
        $destinationPath = $localUploadPath . '/' . $localFileName;

        if (copy($localFilePath, $destinationPath)) {
            Yii::info("Archivo guardado localmente en: " . $destinationPath, __METHOD__);
            // Retornar la ruta local para el acceso web
            $webPath = Yii::getAlias('@web/img/payment') . '/' . $localFileName;
            return $webPath;
        } else {
            Yii::error("Error al guardar el archivo localmente en: " . $destinationPath, __METHOD__);
            Yii::$app->session->setFlash('error', "Error al guardar el archivo localmente.");
            return null; // Si todo falla, retornar null
        }
    }

    /**
     * Elimina un archivo de Supabase Storage usando su API REST.
     *
     * @param string $fileUrl La URL pública completa del archivo en Supabase.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     */
    public static function deleteFileFromSupabaseApi(string $fileUrl, string $folder = null): bool
    {
        $supabaseConfig = Yii::$app->params['supabase'];
        $supabaseUrl = $supabaseConfig['url'];
        $supabaseAnonKey = $supabaseConfig['anon_key'];
        $bucketName = $supabaseConfig['bucket_name'];

        // Extraer la clave del archivo de la URL pública
        // La URL es del tipo: [URL_PROYECTO]/storage/v1/object/public/[BUCKET]/[FOLDER]/[CLAVE_ARCHIVO]
        // Necesitamos la parte [FOLDER]/[CLAVE_ARCHIVO]
        $prefix = "{$supabaseUrl}/storage/v1/object/public/{$bucketName}/{$folder}/";
        if (strpos($fileUrl, $prefix) === 0) {
            $fileKeyToDelete = substr($fileUrl, strlen($prefix));
        } else {
            Yii::warning("No se pudo extraer la clave del archivo de la URL para eliminar: {$fileUrl}", __METHOD__);
            return false; // La URL no tiene el formato esperado
        }

        // El endpoint correcto para eliminar es: [URL_PROYECTO]/storage/v1/object/[BUCKET]
        $deleteUrl = "{$supabaseUrl}/storage/v1/object/{$bucketName}/{$folder}";

        Yii::info("Supabase Delete URL: " . $deleteUrl, __METHOD__);
        Yii::info("File Key to Delete: " . $fileKeyToDelete, __METHOD__);

        try {
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('DELETE')
                ->setUrl($deleteUrl)
                ->addHeaders([
                    'Authorization' => "Bearer {$supabaseAnonKey}",
                    'Content-Type' => 'application/json',
                ])
                ->setContent(json_encode(['prefixes' => [$fileKeyToDelete]])) // Usar 'prefixes' en lugar de 'name'
                ->send();

            if ($response->isOk) {
                Yii::info("Archivo eliminado exitosamente de Supabase Storage. Respuesta: " . $response->getContent(), __METHOD__);
                return true;
            } else {
                $errorContent = $response->getContent();
                Yii::error("Error al eliminar archivo de Supabase Storage. Código: {$response->getStatusCode()}, Error: {$errorContent}", __METHOD__);
                Yii::$app->session->setFlash('error', "Error al eliminar archivo de Supabase Storage: " . ($errorContent ?: "Desconocido"));
                return false;
            }
        } catch (\yii\httpclient\Exception $e) {
            Yii::error("Excepción del cliente HTTP al eliminar de Supabase: " . $e->getMessage() . " - Stack Trace: " . $e->getTraceAsString(), __METHOD__);
            Yii::$app->session->setFlash('error', "Error de conexión al eliminar archivo: " . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            Yii::error("Excepción general al eliminar de Supabase: " . $e->getMessage() . " - Stack Trace: " . $e->getTraceAsString(), __METHOD__);
            Yii::$app->session->setFlash('error', "Ocurrió un error inesperado al eliminar archivo: " . $e->getMessage());
            return false;
        }
    }

    public static function getCorporativoList()
    {
        // 1. Obtener los agentes reales de la base de datos
        $corporativo = Corporativo::find()
            ->select(['id AS id', 'nombre AS name'])
            ->where(['estatus' => "Activo"])
            ->asArray()
            ->all();

        // 2. Mapear los resultados a un array ID => Nombre
        $list = ArrayHelper::map($corporativo, 'id', 'name');

        // 3. Añadir la opción "No Asignado" al principio del array
        $defaultOption = ['' => 'No Asignado']; // Usamos 0 como clave para "No Asignado"

        // Fusionar la opción predeterminada con la lista de agentes reales
        $finalList = $defaultOption + $list; // El operador '+' fusiona arrays manteniendo las claves.

        return $finalList;
    }



    public static function getMyClinicaId()
    {
        $clinica_id = '';
        $rol = self::getMyRol();
        

        if ($rol == "Administrador-clinica" || $rol == "CONTROL DE CITAS" || $rol == "ADMISIÓN" || $rol == "ATENCIÓN") {

            $userdatos = UserDatos::find()->where(['user_login_id' => Yii::$app->user->id])->one();
            if ($userdatos) {
                $clinica_id = $userdatos->clinica_id;
            }
       
        } 

        return $clinica_id;
    }

    public static function getMyClinicaName()
    {
        $clinica_id = '';
        $rol = self::getMyRol();
        

        if ($rol == "Administrador-clinica") {

            $userdatos = UserDatos::find()->where(['user_login_id' => Yii::$app->user->id])->one();
            if ($userdatos) {
                $clinica_id = $userdatos->clinica->nombre;
            }
       
        } 

        return $clinica_id;
    }

    public static function getUserId()
    {
        return Yii::$app->user->identity->id;
    }

    public static function getUserDatosId()
    {
        $userdatos = UserDatos::find()->where(['user_login_id' => Yii::$app->user->id])->one();
        if ($userdatos) {
            return $userdatos->id;
        }
    }

    public static function getAgenteId()
    {
        $userdatos = UserDatos::find()->where(['user_login_id' => Yii::$app->user->id])->one();

        if ($userdatos) {
            $agencia = Agente::find()->where(['idusuariopropietario' => $userdatos->id])->one();
            return $agencia->id;
        }
    }

    public static function getAgenteFuerzaId()
    {
        $userdatos = UserDatos::find()->where(['user_login_id' => Yii::$app->user->id])->one();

        if ($userdatos) {
            $agencia = AgenteFuerza::find()->where(['idusuario' => $userdatos->id])->one();
            return $agencia->id;
        }
    }
      
}
