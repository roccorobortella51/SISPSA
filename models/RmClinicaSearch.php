<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\RmClinica;
use Yii;
use app\components\UserHelper;

/**
 * RmClinicaSearch represents the model behind the search form of `app\models\RmClinica`.
 */
class RmClinicaSearch extends RmClinica
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['created_at', 'rif', 'nombre', 'estado', 'direccion', 'telefono', 'correo', 'estatus', 'webpage', 'rs_instagram', 'QRCode', 'codigo_clinica', 'deleted_at', 'updated_at', 'private_key'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
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
        $query = RmClinica::find()->alias('t');

        // Apply clinic filtering - users with clinic roles only see their own clinic
        if (UserHelper::hasClinicAccess()) {
            $clinicId = UserHelper::getMyClinicaId();
            if ($clinicId) {
                $query->andWhere(['t.id' => $clinicId]);
            }
        }

        // For superadmin, admin, and other roles with full access, no additional filter needed

        $query->select([
            't.id',
            't.nombre',
            't.rif',
            't.telefono',
            't.correo',
            't.estado',
            't.estatus',
            't.created_at',
        ]);

        // Pagination setup
        $pageSize = Yii::$app->request->get('per_page', 20);
        $pageSize = is_numeric($pageSize) && $pageSize > 0 ? (int)$pageSize : 20;

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
            'pagination' => ['pageSize' => $pageSize],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Grid filtering conditions
        $query->andFilterWhere([
            't.id' => $this->id,
            't.created_at' => $this->created_at,
            't.deleted_at' => $this->deleted_at,
            't.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['ilike', 't.rif', $this->rif])
            ->andFilterWhere(['ilike', 't.nombre', $this->nombre])
            ->andFilterWhere(['ilike', 't.estado', $this->estado])
            ->andFilterWhere(['ilike', 't.direccion', $this->direccion])
            ->andFilterWhere(['ilike', 't.telefono', $this->telefono])
            ->andFilterWhere(['ilike', 't.correo', $this->correo])
            ->andFilterWhere(['ilike', 't.estatus', $this->estatus])
            ->andFilterWhere(['ilike', 't.webpage', $this->webpage])
            ->andFilterWhere(['ilike', 't.rs_instagram', $this->rs_instagram])
            ->andFilterWhere(['ilike', 't.QRCode', $this->QRCode])
            ->andFilterWhere(['ilike', 't.codigo_clinica', $this->codigo_clinica])
            ->andFilterWhere(['ilike', 't.private_key', $this->private_key]);

        return $dataProvider;
    }
}
