<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\UserDatos;
use Yii;
use app\components\UserHelper;
use DateTime;

/**
 * UserDatosSearch represents the model behind the search form of `app\models\UserDatos`.
 */
class UserDatosSearch extends UserDatos
{
    public $user_datos_type_id;
    public $afiliado_corporativo_id;
    public $clinica_nombre;
    public $consecutivo_menor;
    // ADDED: Property for contract status filter
    public $contrato_estatus;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'clinica_id', 'plan_id', 'contrato_id', 'asesor_id', 'cedula', 'user_login_id', 'user_datos_type_id', 'afiliado_corporativo_id', 'consecutivo_menor'], 'integer'],
            // ADDED: 'contrato_estatus' to safe array
            [['created_at', 'user_id', 'nombres', 'fechanac', 'sexo', 'selfie', 'telefono', 'estado', 'role', 'estatus', 'imagen_identificacion', 'qr', 'video', 'ciudad', 'municipio', 'parroquia', 'direccion', 'codigoValidacion', 'apellidos', 'email', 'deleted_at', 'updated_at', 'ver_cedula', 'ver_foto', 'session_id', 'tipo_cedula', 'tipo_sangre', 'estatus_solvente', 'clinica_nombre', 'contrato_estatus'], 'safe'],
            [['paso'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['contrato_estatus'] = 'Estatus Contrato';
        return $labels;
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

        // INICIO DE LA OPTIMIZACIÓN
        $query->select([
            // Columnas DIRECTAS de la tabla user_datos
            'user_datos.id',
            'user_datos.created_at',
            'user_datos.nombres',
            'user_datos.apellidos',
            'user_datos.cedula',
            'user_datos.tipo_cedula',
            'user_datos.telefono',
            'user_datos.email',
            'user_datos.estatus_solvente',
            'user_datos.user_datos_type_id',
            'user_datos.clinica_id',
            'user_datos.asesor_id',
            'user_datos.deleted_at',
            'user_datos.consecutivo_menor',
            'user_datos_type.nombre as userDatosTypeNombre',
            'rm_clinica.nombre as clinicaNombre',
            'ud_asesor.nombres as asesorNombres',
            'ud_asesor.apellidos as asesorApellidos',
            // ADDED: Contract status for sorting/filtering
            'contratos.estatus as contrato_estatus',
        ]);

        $query->joinWith(['userDatosType']);

        // Eager loading para evitar N+1 y asegurar acceso a asesor (persona) y clínica en el Grid
        $query->joinWith([
            'asesor.userDatos' => function ($q) {
                $q->from(['ud_asesor' => 'user_datos']);
            },
            'clinica'
        ]);

        // Explicit LEFT JOIN for corporativos to avoid ambiguity
        $query->leftJoin('corporativos', 'user_datos.afiliado_corporativo_id = corporativos.id');

        // ADDED: Join with contracts - LEFT JOIN to include users without contracts
        $query->leftJoin('contratos', 'contratos.user_id = user_datos.id AND contratos.estatus != :anulado', [
            ':anulado' => Contratos::STATUS_ANULADO
        ]);

        // Group by user to avoid duplicates if a user has multiple contracts
        $query->groupBy([
            'user_datos.id',
            'user_datos.created_at',
            'user_datos.nombres',
            'user_datos.apellidos',
            'user_datos.cedula',
            'user_datos.tipo_cedula',
            'user_datos.telefono',
            'user_datos.email',
            'user_datos.estatus_solvente',
            'user_datos.user_datos_type_id',
            'user_datos.clinica_id',
            'user_datos.asesor_id',
            'user_datos.deleted_at',
            'user_datos.consecutivo_menor',
            'user_datos_type.nombre',
            'rm_clinica.nombre',
            'ud_asesor.nombres',
            'ud_asesor.apellidos',
            'contratos.estatus'
        ]);

        if ($rol == "Asesor") {
            $query->where(['user_datos.asesor_id' => UserHelper::getAgenteFuerzaId()]);
        }

        if ($rol == "Administrador-clinica" || $rol == "CONTROL DE CITAS" || $rol == "ADMISIÓN" || $rol == "ATENCIÓN" || $rol == "COORDINADOR-CLINICA" || $rol == "GERENTE-CLINICA") {
            $query->andFilterWhere(['user_datos.clinica_id' => UserHelper::getMyClinicaId()]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        // ADDED: Enable sorting for contract status
        $dataProvider->sort->attributes['contrato_estatus'] = [
            'asc' => ['contratos.estatus' => SORT_ASC],
            'desc' => ['contratos.estatus' => SORT_DESC],
        ];

        // campo 'Tipo Afiliado'
        $dataProvider->sort->attributes['user_datos_type_id'] = [
            'asc' => ['user_datos_type.nombre' => SORT_ASC],
            'desc' => ['user_datos_type.nombre' => SORT_DESC],
        ];

        // ADDED: Enable sorting for corporativo ID and name
        $dataProvider->sort->attributes['afiliado_corporativo_id'] = [
            'asc' => ['user_datos.afiliado_corporativo_id' => SORT_ASC],
            'desc' => ['user_datos.afiliado_corporativo_id' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['corporativo'] = [
            'asc' => ['corporativos.nombre' => SORT_ASC],
            'desc' => ['corporativos.nombre' => SORT_DESC],
        ];

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user_datos.id' => $this->id,
            'user_datos.paso' => $this->paso,
            'user_datos.clinica_id' => $this->clinica_id,
            'user_datos.plan_id' => $this->plan_id,
            'user_datos.contrato_id' => $this->contrato_id,
            'user_datos.asesor_id' => $this->asesor_id,
            'user_datos.updated_at' => $this->updated_at,
            'user_datos.user_login_id' => $this->user_login_id,
            'user_datos.user_datos_type_id' => $this->user_datos_type_id,
            'user_datos.afiliado_corporativo_id' => $this->afiliado_corporativo_id,
            'user_datos.consecutivo_menor' => $this->consecutivo_menor,
        ]);

        // Date range filter for created_at
        if (isset($this->created_at) && !empty($this->created_at)) {
            $dates = explode(" a ", $this->created_at);
            if (count($dates) == 2) {
                $d1 = DateTime::createFromFormat('d/m/Y', trim($dates[0]));
                $d2 = DateTime::createFromFormat('d/m/Y', trim($dates[1]));
                if ($d1 && $d2) {
                    $date1 = $d1->format('Y-m-d') . ' 00:00:00';
                    $date2 = $d2->format('Y-m-d') . ' 23:59:59';
                    $query->andFilterWhere(['between', 'user_datos.created_at', $date1, $date2]);
                }
            }
        }

        // Date range filter for fechanac
        if (isset($this->fechanac) && !empty($this->fechanac)) {
            $dates = explode("-", $this->fechanac);
            $query->andFilterWhere(['between', 'user_datos.fechanac', $dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
        }

        // ADDED: Contract status filter
        if (!empty($this->contrato_estatus)) {
            if ($this->contrato_estatus === 'sin_contrato') {
                // Filter for users without any valid contract
                $query->andWhere(['contratos.id' => null]);
            } else {
                // Filter for specific contract status
                $query->andFilterWhere(['contratos.estatus' => $this->contrato_estatus]);
            }
        }

        // Text filters
        $query->andFilterWhere(['ilike', 'user_id', $this->user_id])
            ->andFilterWhere(['ilike', 'user_datos.nombres', $this->nombres])
            ->andFilterWhere(['ilike', 'user_datos.sexo', $this->sexo])
            ->andFilterWhere(['ilike', 'user_datos.selfie', $this->selfie])
            ->andFilterWhere(['ilike', 'user_datos.telefono', $this->telefono])
            ->andFilterWhere(['ilike', 'user_datos.estado', $this->estado])
            ->andFilterWhere(['ilike', 'user_datos.role', $this->role])
            ->andFilterWhere(['ilike', 'user_datos.estatus', $this->estatus])
            ->andFilterWhere(['ilike', 'user_datos.imagen_identificacion', $this->imagen_identificacion])
            ->andFilterWhere(['ilike', 'user_datos.qr', $this->qr])
            ->andFilterWhere(['ilike', 'user_datos.video', $this->video])
            ->andFilterWhere(['ilike', 'user_datos.ciudad', $this->ciudad])
            ->andFilterWhere(['ilike', 'user_datos.municipio', $this->municipio])
            ->andFilterWhere(['ilike', 'user_datos.parroquia', $this->parroquia])
            ->andFilterWhere(['ilike', 'user_datos.direccion', $this->direccion])
            ->andFilterWhere(['ilike', 'user_datos.codigoValidacion', $this->codigoValidacion])
            ->andFilterWhere(['ilike', 'user_datos.apellidos', $this->apellidos])
            ->andFilterWhere(['ilike', 'user_datos.email', $this->email])
            ->andFilterWhere(['ilike', 'user_datos.ver_cedula', $this->ver_cedula])
            ->andFilterWhere(['ilike', 'user_datos.ver_foto', $this->ver_foto])
            ->andFilterWhere(['ilike', 'user_datos.session_id', $this->session_id])
            ->andFilterWhere(['ilike', 'user_datos.tipo_cedula', $this->tipo_cedula])
            ->andFilterWhere(['ilike', 'user_datos.tipo_sangre', $this->tipo_sangre])
            ->andFilterWhere(['ilike', 'user_datos.estatus_solvente', $this->estatus_solvente])
            ->andFilterWhere(['ilike', 'rm_clinica.nombre', $this->clinica_nombre])
            ->andFilterWhere(['ilike', 'CAST(user_datos.cedula AS TEXT)', $this->cedula])
            ->andWhere(['is', 'user_datos.deleted_at', null]);

        return $dataProvider;
    }
}
