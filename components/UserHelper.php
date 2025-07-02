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
use app\models\RmClinica;
use app\models\Agente;
use app\models\Asesores;
use app\models\User;
use app\models\AuthItem;

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

    public function getAreaList()
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

    public static function getTotalClinicas()
    {
        $totalClinicas = RmClinica::find()->count();

        // Puedes pasar este total a una vista
        return $totalClinicas;
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

    public static function getRolesAllRoles()
    {
        return \yii\helpers\ArrayHelper::map(
            AuthItem::find()->select(['name', 'name'])->asArray()->all(),
            'name',
            'name'
        );
    }

    public static function getAgentesList()
    {
        return \yii\helpers\ArrayHelper::map(
            User::find()
                // Corregimos la sintaxis de la cláusula ON con comillas dobles
                ->leftJoin('auth_assignment', '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
                ->select(['user.id AS id', 'username AS name'])
                ->where(['auth_assignment.item_name' => "Agente"])
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
                // Corregimos la sintaxis de la cláusula ON con comillas dobles
                ->leftJoin('auth_assignment', '"user"."id" = CAST("auth_assignment"."user_id" AS INTEGER)')
                ->select(['user.id AS id', 'username AS name'])
                ->where(['auth_assignment.item_name' => "asesor"])
                ->asArray()
                ->all(),
            'id',
            'name'
        );
    }




    


}