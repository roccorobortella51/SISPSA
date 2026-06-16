<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Agente;
use app\components\UserHelper;
use yii\db\Expression;

/**
 * AgenteSearch represents the model behind the search form of `app\models\Agente`.
 */
class AgenteSearch extends Agente
{
    public $rif;
    public $propietarioEmail;
    public $propietarioCedula;
    public $propietarioNombreCompleto;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idusuariopropietario'], 'integer'],
            [['nom', 'rif', 'sudeaseg', 'created_at', 'updated_at', 'deleted_at', 'propietarioEmail', 'propietarioCedula', 'propietarioNombreCompleto'], 'safe'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente', 'por_max'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $rol = UserHelper::getMyRol();
        $filtro_gente = ($rol == 'Agente');

        $query = Agente::find()->alias('t');

        // Add distinct to prevent duplicate records from joins
        $query->distinct();

        // Use leftJoin instead of joinWith to avoid multiple rows
        $query->leftJoin(['userDatos' => 'user_datos'], '"userDatos"."id" = "t"."idusuariopropietario"');
        $query->leftJoin(['user' => 'user'], '"user"."id" = "userDatos"."user_login_id"');

        $query->select([
            't.id',
            't.nom',
            't.sudeaseg',
            't.idusuariopropietario',
            'user.email as propietarioEmail',
            'userDatos.cedula as propietarioCedula',
            'userDatos.nombres',
            'userDatos.apellidos',
        ]);

        if ($filtro_gente) {
            $query->andFilterWhere(['t.idusuariopropietario' => UserHelper::getUserDatosId()]);
        }

        // Create data provider WITHOUT pagination
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false, // Completely disable pagination
            'sort' => [
                'defaultOrder' => ['id' => SORT_ASC], // Force order by ID ascending
                'attributes' => [
                    'id' => [
                        'asc' => ['t.id' => SORT_ASC],
                        'desc' => ['t.id' => SORT_DESC],
                        'label' => 'ID',
                        'default' => SORT_ASC, // Set default to ascending
                    ],
                    'nom' => [
                        'asc' => ['t.nom' => SORT_ASC],
                        'desc' => ['t.nom' => SORT_DESC],
                        'label' => 'AGENCIAS',
                    ],
                    'sudeaseg' => [
                        'asc' => ['t.sudeaseg' => SORT_ASC],
                        'desc' => ['t.sudeaseg' => SORT_DESC],
                        'label' => 'Código SUDEASEG',
                    ],
                    'propietarioEmail' => [
                        'asc' => ['user.email' => SORT_ASC],
                        'desc' => ['user.email' => SORT_DESC],
                        'label' => 'Correo del Propietario',
                    ],
                    'propietarioCedula' => [
                        'asc' => ['userDatos.cedula' => SORT_ASC],
                        'desc' => ['userDatos.cedula' => SORT_DESC],
                        'label' => 'Cédula del Propietario',
                    ],
                    'propietarioNombreCompleto' => [
                        'asc' => ['userDatos.nombres' => SORT_ASC, 'userDatos.apellidos' => SORT_ASC],
                        'desc' => ['userDatos.nombres' => SORT_DESC, 'userDatos.apellidos' => SORT_DESC],
                        'label' => 'Propietario',
                    ],
                ],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Apply filters
        $query->andFilterWhere(['t.id' => $this->id]);

        if (!empty($this->nom)) {
            $query->andFilterWhere(['ilike', 't.nom', $this->nom]);
        }

        if (!empty($this->rif)) {
            $query->andFilterWhere(['ilike', 't.rif', $this->rif]);
        }

        if (!empty($this->sudeaseg)) {
            $query->andFilterWhere(['ilike', 't.sudeaseg', $this->sudeaseg]);
        }

        if (!empty($this->propietarioEmail)) {
            $query->andFilterWhere(['ilike', 'user.email', $this->propietarioEmail]);
        }

        if (!empty($this->propietarioCedula)) {
            $query->andFilterWhere(['ilike', 'CAST("userDatos"."cedula" AS TEXT)', $this->propietarioCedula]);
        }

        if (!empty($this->propietarioNombreCompleto)) {
            $search = '%' . strtolower($this->propietarioNombreCompleto) . '%';
            $query->andWhere(new Expression("LOWER(\"userDatos\".nombres || ' ' || \"userDatos\".apellidos) LIKE :search", [':search' => $search]));
        }

        // Force ORDER BY ID ASC at the query level as a fallback
        $query->orderBy(['t.id' => SORT_ASC]);

        return $dataProvider;
    }
}
