<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\UserDatos;
use Yii;
use app\components\UserHelper;

/**
 * UserDatosSearch represents the model behind the search form of `app\models\UserDatos`.
 */
class UserDatosSearch extends UserDatos
{

    public $user_datos_type_id; 
    public $afiliado_corporativo_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'clinica_id', 'plan_id', 'contrato_id', 'asesor_id', 'cedula', 'user_login_id', 'user_datos_type_id', 'afiliado_corporativo_id'], 'integer'],
            [['created_at', 'user_id', 'nombres', 'fechanac', 'sexo', 'selfie', 'telefono', 'estado', 'role', 'estatus', 'imagen_identificacion', 'qr', 'video', 'ciudad', 'municipio', 'parroquia', 'direccion', 'codigoValidacion', 'apellidos', 'email', 'deleted_at', 'updated_at', 'ver_cedula', 'ver_foto', 'session_id', 'tipo_cedula', 'tipo_sangre', 'estatus_solvente'], 'safe'],
            [['paso'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {

        $rol = UserHelper::getMyRol();
        $query = UserDatos::find();
        $query->joinWith(['userDatosType']);
        // Eager loading para evitar N+1 y asegurar acceso a asesor (persona) y clínica en el Grid
        // Importante: alias para user_datos del asesor para no colisionar con la tabla principal
        $query->joinWith([
            'asesor.userDatos' => function ($q) {
                // alias de la tabla user_datos relacionada al asesor
                $q->from(['ud_asesor' => 'user_datos']);
            },
            'clinica'
        ]);

        // Si necesitas filtrar por el afiliado corporativo principal (afiliado_corporativo_id)
        // en el GridView de UserDatos, deberías añadir un joinWith aquí.
        // Esto depende de cómo esté modelada esa relación en la base de datos.
        // Si 'afiliado_corporativo_id' es una columna en user_datos que apunta a otro user_datos,
        // necesitarías una relación en UserDatos.php para ello.
        // Por ejemplo:
        /*
        $query->joinWith([
            'afiliadoCorporativo' => function ($q) {
                // Si la relación se llama getAfiliadoCorporativo() en UserDatos
            }
        ]);
        */

        if($rol == "Asesor"){
            $query->where(['asesor_id' => UserHelper::getAgenteFuerzaId()]);
        }

        if ($rol == "Administrador-clinica" || $rol == "CONTROL DE CITAS" || $rol == "ADMISIÓN" || $rol == "ATENCIÓN" || $rol == "COORDINADOR-CLINICA") {

            $query->andFilterWhere(['user_datos.clinica_id' => UserHelper::getMyClinicaId()]);
        }


        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC, 
                ],
               
            ],
        ]);
        // campo 'Tipo Afiliado'
        $dataProvider->sort->attributes['user_datos_type_id'] = [
            'asc' => ['user_datos_type.nombre' => SORT_ASC],
            'desc' => ['user_datos_type.nombre' => SORT_DESC],
        ];

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            //'created_at' => $this->created_at,
            //'fechanac' => $this->fechanac,
            'paso' => $this->paso,
            'clinica_id' => $this->clinica_id,
            'plan_id' => $this->plan_id,
            'contrato_id' => $this->contrato_id,
            'asesor_id' => $this->asesor_id,
            'updated_at' => $this->updated_at,
            'cedula' => $this->cedula,
            'user_login_id' => $this->user_login_id,
            'user_datos_type_id' => $this->user_datos_type_id, 
            'afiliado_corporativo_id' => $this->afiliado_corporativo_id,
        ]);

        if (isset($this->created_at) && !empty($this->created_at)) {
            $dates = explode("-", $this->created_at);
            $query->andFilterWhere(['between', 'user_datos.created_at', $dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
        }

        if (isset($this->fechanac) && !empty($this->fechanac)) {
            $dates = explode("-", $this->fechanac);
            $query->andFilterWhere(['between', 'user_datos.fechanac', $dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
        }


        $query->andFilterWhere(['ilike', 'user_id', $this->user_id])
            ->andFilterWhere(['ilike', 'nombres', $this->nombres])
            ->andFilterWhere(['ilike', 'sexo', $this->sexo])
            ->andFilterWhere(['ilike', 'selfie', $this->selfie])
            ->andFilterWhere(['ilike', 'telefono', $this->telefono])
            ->andFilterWhere(['ilike', 'estado', $this->estado])
            ->andFilterWhere(['ilike', 'role', $this->role])
            ->andFilterWhere(['ilike', 'estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'imagen_identificacion', $this->imagen_identificacion])
            ->andFilterWhere(['ilike', 'qr', $this->qr])
            ->andFilterWhere(['ilike', 'video', $this->video])
            ->andFilterWhere(['ilike', 'ciudad', $this->ciudad])
            ->andFilterWhere(['ilike', 'municipio', $this->municipio])
            ->andFilterWhere(['ilike', 'parroquia', $this->parroquia])
            ->andFilterWhere(['ilike', 'direccion', $this->direccion])
            ->andFilterWhere(['ilike', 'codigoValidacion', $this->codigoValidacion])
            ->andFilterWhere(['ilike', 'apellidos', $this->apellidos])
            ->andFilterWhere(['ilike', 'email', $this->email])
            ->andFilterWhere(['ilike', 'ver_cedula', $this->ver_cedula])
            ->andFilterWhere(['ilike', 'ver_foto', $this->ver_foto])
            ->andFilterWhere(['ilike', 'session_id', $this->session_id])
            ->andFilterWhere(['ilike', 'tipo_cedula', $this->tipo_cedula])
            ->andFilterWhere(['ilike', 'tipo_sangre', $this->tipo_sangre])
            ->andFilterWhere(['ilike', 'estatus_solvente', $this->estatus_solvente])
            ->andWhere(['is', 'user_datos.deleted_at', null]);

        return $dataProvider;
    }
}
