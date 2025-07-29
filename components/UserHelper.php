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
            ->leftJoin('auth_assignment', '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
            ->leftJoin('user_datos', '"user"."id" = "user_datos"."user_login_id"')
            ->select(['user.id AS id', 'user_datos.nombres AS name'])
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

    public static function getAgenteFuerzaList()
    {
        return \yii\helpers\ArrayHelper::map(
            User::find()
                
                ->leftJoin('auth_assignment', '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
                ->select(['user.id AS id', 'username AS name'])
                ->where(['auth_assignment.item_name' => "Asesor"])
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
        // 1. Encontrar el modelo Agente por su ID
        $agente = Agente::findOne($agenteId);

        if ($agente === null) {
            return [
                'rif' => 'N/A',
                'email' => 'N/A',
                'telefono' => 'N/A',
                'direccion' => 'N/A',
            ];
        }

        // 2. Encontrar el modelo User (propietario) usando el idusuariopropietario del agente
        $ownerUser = User::findOne($agente->idusuariopropietario);

        if ($ownerUser === null) {
            return [
                'rif' => 'N/A',
                'email' => 'N/A',
                'telefono' => 'N/A',
                'direccion' => 'N/A',
            ];
        }

        // 3. Encontrar el modelo UserDatos asociado a ese User
        // Asume que UserDatos tiene una columna 'user_login_id' que es la FK al 'id' de la tabla User
        $ownerDatos = UserDatos::findOne(['user_login_id' => $ownerUser->id]);

        if ($ownerDatos === null) {
            return [
                'rif' => 'N/A',
                'email' => 'N/A',
                'telefono' => 'N/A',
                'direccion' => 'N/A',
            ];
        }

        // 4. Devolver los datos de contacto
        return [
            'rif' => $ownerDatos->rif ?? 'N/A',
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
            'user.id AS id',
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
        $defaultOption = ['0' => 'No Asignado']; // Usamos 0 como clave para "No Asignado"

        // Fusionar la opción predeterminada con la lista de agentes reales
        $finalList = $defaultOption + $list; // El operador '+' fusiona arrays manteniendo las claves.

        return $finalList;
    }



    public static function getMyClinicaId()
    {
        $clinica_id = '';
        $rol = self::getMyRol();
        

        if ($rol == "Administrador-clinica") {

            $userdatos = UserDatos::find()->where(['user_login_id' => Yii::$app->user->id])->one();
            if ($userdatos) {
                $clinica_id = $userdatos->clinica_id;
            }
       
        } 

        return $clinica_id;
    }
      
}