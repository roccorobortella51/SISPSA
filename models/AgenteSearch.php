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
            [['nom', 'rif', 'created_at', 'updated_at', 'deleted_at', 'propietarioEmail', 'propietarioCedula', 'propietarioNombreCompleto'], 'safe'],
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
        $query->joinWith(['propietario userDatos', 'propietario.user', 'agenteFuerzas']);

        $query->select([
            't.id',
            't.nom',
            't.idusuariopropietario',
            'user.email as propietarioEmail',
            'userDatos.cedula as propietarioCedula',
            'userDatos.nombres',
            'userDatos.apellidos',
        ]);

        if ($filtro_gente) {
            $query->andFilterWhere(['t.idusuariopropietario' => UserHelper::getUserDatosId()]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
                'pageSizeLimit' => [1, 100],
                'defaultPageSize' => 20,
                'pageParam' => 'agente-page', // Unique page parameter
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC, 'nom' => SORT_ASC], // ✅ Two-column sort
                'attributes' => [
                    'id' => [
                        'asc' => ['t.id' => SORT_ASC, 't.nom' => SORT_ASC],
                        'desc' => ['t.id' => SORT_DESC, 't.nom' => SORT_ASC],
                        'label' => 'ID',
                    ],
                    'nom' => [
                        'asc' => ['t.nom' => SORT_ASC, 't.id' => SORT_ASC],
                        'desc' => ['t.nom' => SORT_DESC, 't.id' => SORT_ASC],
                        'label' => 'AGENCIAS',
                    ],
                    'propietarioEmail' => [
                        'asc' => ['user.email' => SORT_ASC, 't.id' => SORT_ASC],
                        'desc' => ['user.email' => SORT_DESC, 't.id' => SORT_ASC],
                        'label' => 'Correo del Propietario',
                    ],
                    'propietarioCedula' => [
                        'asc' => ['userDatos.cedula' => SORT_ASC, 't.id' => SORT_ASC],
                        'desc' => ['userDatos.cedula' => SORT_DESC, 't.id' => SORT_ASC],
                        'label' => 'Cédula del Propietario',
                    ],
                    'propietarioNombreCompleto' => [
                        'asc' => ['userDatos.nombres' => SORT_ASC, 'userDatos.apellidos' => SORT_ASC, 't.id' => SORT_ASC],
                        'desc' => ['userDatos.nombres' => SORT_DESC, 'userDatos.apellidos' => SORT_DESC, 't.id' => SORT_ASC],
                        'label' => 'Propietario',
                    ],
                ],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Your existing filtering conditions...
        $query->andFilterWhere(['t.id' => $this->id]);
        $query->andFilterWhere(['ilike', 't.nom', $this->nom]);
        $query->andFilterWhere(['ilike', 't.rif', $this->rif]);
        $query->andFilterWhere(['ilike', 'user.email', $this->propietarioEmail])
            ->andFilterWhere(['ilike', 'CAST("userDatos"."cedula" AS TEXT)', $this->propietarioCedula]);

        if (!empty($this->propietarioNombreCompleto)) {
            $search = '%' . strtolower($this->propietarioNombreCompleto) . '%';
            $query->andWhere(new Expression("LOWER(\"userDatos\".nombres || ' ' || \"userDatos\".apellidos) LIKE :search", [':search' => $search]));
        }

        return $dataProvider;
    }
}
